<?php

namespace App\Http\Controllers\Composer;

use App\Http\Controllers\Controller;
use App\Models\Package;

class ListPackagesController extends Controller
{
    public function __invoke(Package $package)
    {
        $versions = $package
            ->versions
            ->mapWithKeys(function ($version) use ($package) {
                $config = $version->json_file;

                $route = route('composer.tarball', [
                    'package' => $package->slug,
                    'version' => base64_encode($version->version_normalized),
                ]);

                $config['dist'] = [
                    'url' => $route,
                    'type' => 'zip',
                ];

                return [
                    $version->version_normalized => $config,
                ];
            })
            ->toArray();

        return [
            'packages' => [
                $package->package_name => $versions,
            ],
        ];
    }
}
