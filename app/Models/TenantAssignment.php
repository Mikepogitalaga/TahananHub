<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAssignment extends Model
{
    protected $fillable = [
        'tenant_id',
        'property_id',
        'room_id',
        'bed_id',
        'assigned_at',
        'vacated_at',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'vacated_at'  => 'datetime',
            'is_current'  => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }
}

