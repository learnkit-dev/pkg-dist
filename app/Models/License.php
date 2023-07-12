<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class License extends Model implements Authenticatable
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function scopeActive($query)
    {
        return $query
            ->where(function ($query) {
                return $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where('is_revoked', false);
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (! filled($model->key)) {
                $model->key = Str::uuid();
            }
        });
    }

    public function getAuthIdentifierName()
    {
        // TODO: Implement getAuthIdentifierName() method.
    }

    public function getAuthIdentifier()
    {
        // TODO: Implement getAuthIdentifier() method.
    }

    public function getAuthPassword()
    {
        // TODO: Implement getAuthPassword() method.
    }

    public function getRememberToken()
    {
        // TODO: Implement getRememberToken() method.
    }

    public function setRememberToken($value)
    {
        // TODO: Implement setRememberToken() method.
    }

    public function getRememberTokenName()
    {
        // TODO: Implement getRememberTokenName() method.
    }
}
