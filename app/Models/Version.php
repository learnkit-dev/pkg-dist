<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Version extends Model
{
    protected $guarded = [];

    protected $casts = [
        'json_file' => 'json',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
