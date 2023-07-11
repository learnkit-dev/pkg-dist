<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Team extends Model
{
    protected $guarded = [];

    protected $casts = [
        'gh_api_key' => 'encrypted',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function versions(): HasManyThrough
    {
        return $this->hasManyThrough(Version::class, Package::class);
    }

    public function licenses(): HasManyThrough
    {
        return $this->hasManyThrough(License::class, Package::class);
    }
}
