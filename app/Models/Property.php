<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'is_listed',
        'public_description',
        'public_contact',
        'amenities',
        'house_rules',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'is_listed' => 'boolean',
            'amenities' => 'array',
            'latitude'  => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePubliclyListed(Builder $query): Builder
    {
        return $query->where('status', 'active')->where('is_listed', true);
    }

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

    public function photos(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class)->orderBy('sort_order');
    }

    public function coverPhoto(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class)->where('is_cover', true)->limit(1);
    }

    public function rentalRequests(): HasMany
    {
        return $this->hasMany(RentalRequest::class);
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

    public function getStartingRentAttribute(): ?float
    {
        $rates = $this->rooms->flatMap->beds->whereNotNull('rental_rate')->pluck('rental_rate');
        return $rates->isNotEmpty() ? (float) $rates->min() : null;
    }

    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        $query = urlencode($this->full_address);
        return "https://www.google.com/maps/search/?api=1&query={$query}";
    }
}
