<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerAccessSession extends Model
{
    protected $fillable = [
        'access_request_id',
        'admin_user_id',
        'house_owner_id',
        'permissions',
        'granted_at',
        'expires_at',
        'revoked_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'granted_at'  => 'datetime',
            'expires_at'  => 'datetime',
            'revoked_at'  => 'datetime',
            'is_active'   => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function accessRequest(): BelongsTo
    {
        return $this->belongsTo(OwnerAccessRequest::class, 'access_request_id');
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    /**
     * Alias used across views/controllers ("admin" => requesting Super Admin).
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function houseOwner(): BelongsTo
    {
        return $this->belongsTo(HouseOwner::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('expires_at', '>', now())
            ->whereNull('revoked_at');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isValid(): bool
    {
        return $this->is_active
            && $this->expires_at > now()
            && is_null($this->revoked_at);
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }
}

