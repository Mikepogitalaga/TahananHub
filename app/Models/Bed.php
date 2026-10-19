<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bed extends Model
{
    protected $fillable = [
        'room_id',
        'bed_number',
        'bed_code',
        'rental_rate',
        'status',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'rental_rate' => 'decimal:2',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tenantAssignments(): HasMany
    {
        return $this->hasMany(TenantAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(TenantAssignment::class)->where('is_current', true);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function getDisplayNameAttribute(): string
    {
        return $this->bed_code
            ? "Bed {$this->bed_number} ({$this->bed_code})"
            : "Bed {$this->bed_number}";
    }
}
