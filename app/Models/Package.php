<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Package extends Model
{
    protected $guarded = [];

    public function versions(): HasMany
    {
        return $this->hasMany(Version::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
