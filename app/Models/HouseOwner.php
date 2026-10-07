<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HouseOwner extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'business_address',
        'subscription_plan',
        'subscription_expires_at',
        'privacy_require_approval',
        'privacy_notify_on_access',
    ];

    protected function casts(): array
    {
        return [
            'subscription_expires_at'  => 'datetime',
            'privacy_require_approval' => 'boolean',
            'privacy_notify_on_access' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function accessRequests(): HasMany
    {
        return $this->hasMany(OwnerAccessRequest::class);
    }

    public function accessSessions(): HasMany
    {
        return $this->hasMany(OwnerAccessSession::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function getFullNameAttribute(): string
    {
        return $this->business_name ?? $this->user->name ?? 'Unknown Owner';
    }
}

