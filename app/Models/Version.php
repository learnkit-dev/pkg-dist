<?php

namespace App\Models;

use App\Enums\VersionStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Version extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => VersionStatus::class,
        'json_file' => 'json',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function distUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return Str::of('')
                    ->append(storage_path('app/repos/'))
                    ->append(
                        Str::of($this->package->name)->replace('/', '_')
                    )
                    ->append('_')
                    ->append(
                        Str::of($this->version_normalized)->replace('.', '-')
                    )
                    ->append('.zip');
            }
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Version $model) {
            if (file_exists($model->distUrl)) {
                unlink($model->distUrl);
            }
        });
    }
}
