<?php

namespace App\Http\Controllers\Composer;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Package;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadVersionController extends Controller
{
    public function __invoke(Authenticatable $license, Package $package, string $version)
    {
        abort_unless($license instanceof License, 401);

        $version = base64_decode($version);

        // Stream the file from R2
        $filename = Str::of($package->name)->replace('/', '_') . '_' . Str::of($version)->replace('.', '-') . '.zip';

        return Storage::disk('r2')->download($filename);
    }
}
