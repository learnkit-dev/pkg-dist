<?php

use App\Models\License;
use App\Models\Package;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

Route::get('/packages.json', function () {
    $packages = Package::all()
        ->flatMap(function ($repository) {
            // Get the file
            $path = 'repos/' . Str::of($repository->name)->replace('/', '_') . '.json';

            $file = \Illuminate\Support\Facades\Storage::get($path);
            $json = json_decode($file, true);

            return collect($json['packages'])
                ->map(function ($package) use ($repository) {
                    return collect($package)
                        ->map(function ($version) use ($repository) {
                            $version['dist'] = [
                                'type' => 'zip',
                                'url' => route('composer.tarball', ['repository' => $repository, 'version' => base64_encode($version['version_normalized'])]),
                                'reference' => '',
                                'shasum' => '',
                            ];

                            return $version;
                        });
                });
        })
        ->toArray();

    $output = [
        'packages' => $packages,
    ];

    return $output;
});

Route::get('/repos/{repository}/{version}.zip', function (Authenticatable $license, Package $repository, string $version) {
    abort_unless($license instanceof License, 401);

    $version = base64_decode($version);

    $path = storage_path('/app/repos/' . Str::of($repository->name)->replace('/', '_') . '_' . Str::of($version)->replace('.', '-') . '.zip');

    return response()->file($path);
})->name('composer.tarball')->middleware('auth:license-api');
