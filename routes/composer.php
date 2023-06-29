<?php

use App\Models\License;
use App\Models\Package;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

Route::get('/packages.json', function () {
    $packages = Package::all()
        ->mapWithKeys(function ($package) {
            $versions = $package
                ->versions
                ->mapWithKeys(function ($version) {
                    return [
                        $version->version_normalized => $version->json_file,
                    ];
                })
                ->toArray();

            return [
                $package->package_name => $versions,
            ];
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
