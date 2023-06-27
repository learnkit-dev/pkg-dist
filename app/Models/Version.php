<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Version extends Model
{
    protected $guarded = [];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
