<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getAddRepositoryCommand(): ?string
    {
        $url = route('composer.home', ['package' => $this]);

        $packageName = $this->package_name;

        return "composer config repositories.$packageName composer $url";
    }
}
