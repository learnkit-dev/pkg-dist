<?php

use App\Http\Controllers\Composer\ListPackagesController;
use App\Http\Controllers\Composer\DownloadVersionController;

Route::get('/packages.json', ListPackagesController::class)
    ->name('list-packages');

Route::get('/', fn () => 'pong')
    ->name('home');

Route::get('/dist/{version}.zip', DownloadVersionController::class)
    ->name('tarball')
    ->middleware('auth:license-api');
