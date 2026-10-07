<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OwnerAccessRequest extends Model
{
    protected $fillable = [
        'requested_by_user_id',
        'house_owner_id',
        'reason',
        'requested_duration_minutes',
        'permissions',
        'status',
        'request_notes',
        'owner_notes',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions'  => 'array',
            'responded_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function houseOwner(): BelongsTo
    {
        return $this->belongsTo(HouseOwner::class);
    }

    public function accessSessions(): HasMany
    {
        return $this->hasMany(OwnerAccessSession::class, 'access_request_id');
    }

    /**
     * Alias used across views/controllers ("sessions" => granted sessions).
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(OwnerAccessSession::class, 'access_request_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}

