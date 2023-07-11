<?php

namespace App\Jobs;

use App\Enums\VersionStatus;
use App\Models\Package;
use App\Models\Version;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\PackageInterface;
use Composer\Repository\RepositoryFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Composer\Config;
use Composer\Factory;
use Composer\IO\BufferIO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sentry\State\Scope;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadReleaseForRepoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Package $repository, public Version $version)
    {
    }

    public function handle(): void
    {
        \Sentry\configureScope(function (Scope $scope): void {
            $scope->setTag('package.id', $this->repository->id);
            $scope->setTag('package.name', $this->repository->package_name);
            $scope->setTag('package.version', $this->version->version_normalized);
        });

        try {
            $io = $this->createIO();

            $repository = current(RepositoryFactory::defaultRepos($io, $this->createConfig($io)));
            $package = $repository->findPackage($this->repository->package_name, $this->version->version_normalized);

            $data = (new ArrayDumper())->dump($package);

            unset($data['dist']);
            unset($data['source']);

            $size = $this->downloadTarball($package);

            $this->version
                ->update([
                    'status' => VersionStatus::Published,
                    'json_file' => $data,
                    'last_synced_at' => now(),
                    'size' => $size,
                ]);
        } catch (\Throwable $exception) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($exception);
            }
        }
    }

    private function createIO(): BufferIO
    {
        $io = new BufferIO('', OutputInterface::VERBOSITY_VERY_VERBOSE);

        $team = $this->repository->team;

        $io->setAuthentication('github.com', $team->gh_api_key, 'x-oauth-basic');

        return $io;
    }

    public function createConfig($io): Config
    {
        unset(Config::$defaultRepositories['packagist.org']);

        $config = Factory::createConfig($io);

        $config->merge([
            'repositories' => [
                [
                    'type' => 'vcs',
                    'url' => 'https://github.com/' . $this->repository->name,
                ]
            ],
        ]);

        return $config;
    }

    private function downloadTarball(PackageInterface $package)
    {
        $key = $this->repository->team?->gh_api_key;

        $name = Str::of($this->repository->name)->replace('/', '_') . '_' . Str::of($package->getVersion())->replace('.', '-');
        $filename = $name . '.zip';

        $path = storage_path('app/repos/' . $filename);

        Http::withHeaders([
            'Authorization' => 'token ' . $key
        ])
        ->withUserAgent(config('app.name') . ' / ' . env('APP_DOMAIN'))
        ->sink($path)
        ->get($package->getDistUrl());

        $size = Storage::disk('local')->size('repos/' . $filename);

        // Upload to R2
        $contents = Storage::disk('local')->get('repos/' . $filename);
        Storage::disk('r2')->put($filename, $contents);

        // Delete the old file
        if (file_exists($path)) {
            unlink($path);
        }

        return $size;
    }
}
