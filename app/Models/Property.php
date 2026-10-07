<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Property extends Model
{
    protected $fillable = [
        'house_owner_id',
        'name',
        'code',
        'address',
        'barangay',
        'municipality',
        'province',
        'contact_number',
        'description',
        'status',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function houseOwner(): BelongsTo
    {
        return $this->belongsTo(HouseOwner::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function tenantAssignments(): HasMany
    {
        return $this->hasMany(TenantAssignment::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function getFullAddressAttribute(): string
    {
        return collect([$this->address, $this->barangay, $this->municipality, $this->province])
            ->filter()
            ->implode(', ');
    }

    public function getAvailableBedsCountAttribute(): int
    {
        return $this->rooms->flatMap->beds->where('status', 'available')->count();
    }

    public function getTotalBedsCountAttribute(): int
    {
        return $this->rooms->flatMap->beds->count();
    }
}

