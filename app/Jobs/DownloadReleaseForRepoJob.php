<?php

namespace App\Jobs;

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
use Illuminate\Support\Facades\Storage;
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
            $json = ['packages' => []];
            $package = $repository->findPackage($this->repository->package_name, $this->tag);

            $data = (new ArrayDumper())->dump($package);

            $json['packages'][$package->getPrettyName()][$package->getPrettyVersion()] = $data;

            $this->repository
                ->versions()
                ->create([
                    'version' => $package->getPrettyVersion(),
                    'version_normalized' => $package->getVersion(),
                ]);

            $this->downloadTarball($package);

            $this->saveJsonFile($package, $json);
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

    private function downloadTarball(PackageInterface $package)
    {
        $path = storage_path('app/repos/' . Str::of($this->repository->name)->replace('/', '_') . '_' . Str::of($package->getVersion())->replace('.', '-') . '.zip');
        $zip = fopen($path, 'w');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $package->getDistUrl());
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 100);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_FILE, $zip);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Chrome/64.0.3282.186 Safari/537.36');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: token ' . config('repomap.github_personal_token')));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $httpcode = curl_getinfo($curl , CURLINFO_HTTP_CODE);
        $result = curl_exec($curl);
        curl_close($curl);
    }

    private function saveJsonFile(PackageInterface $package, array $json)
    {
        Storage::write('repos/' . Str::of($this->repository->name)->replace('/', '_') . '.json', json_encode($json));
    }
}
