<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'house_owner_id',
        'name',
        'file_path',
        'file_type',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function houseOwner(): BelongsTo
    {
        return $this->belongsTo(HouseOwner::class);
    }
}

