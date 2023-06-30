<?php

namespace App\Jobs;

use App\Actions\DownloadDistFromSource;
use App\Models\Package;
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
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadReleaseForRepoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Package $repository, public string $tag)
    {
    }

    public function handle(): void
    {
        try {
            $io = $this->createIO();

            $repository = current(RepositoryFactory::defaultRepos($io, $this->createConfig($io)));
            $package = $repository->findPackage($this->repository->package_name, $this->tag);

            $data = (new ArrayDumper())->dump($package);

            unset($data['dist']);
            unset($data['source']);

            $this->repository
                ->versions()
                ->create([
                    'version' => $package->getPrettyVersion(),
                    'version_normalized' => $package->getVersion(),
                    'json_file' => $data,
                ]);

            $this->downloadTarball($package);
        } catch (\Throwable $exception) {
            ray($exception);
        }
    }

    private function createIO(): BufferIO
    {
        $io = new BufferIO('', OutputInterface::VERBOSITY_VERY_VERBOSE);

        $io->setAuthentication('github.com', config('repomap.github_personal_token'), 'x-oauth-basic');

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

    private function downloadTarball(PackageInterface $package): void
    {
        $path = storage_path('app/repos/' . Str::of($this->repository->name)->replace('/', '_') . '_' . Str::of($package->getVersion())->replace('.', '-') . '.zip');

        $status = DownloadDistFromSource::download(
            url: $package->getDistUrl(),
            path: $path,
        );
    }
}
