<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalContract extends Model
{
    protected $fillable = [
        'contract_number',
        'tenant_id',
        'property_id',
        'room_id',
        'bed_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'security_deposit',
        'advance_payment',
        'due_day',
        'late_fee',
        'terms',
        'status',
        'document_path',
    ];

    protected function casts(): array
    {
        return [
            'start_date'       => 'date',
            'end_date'         => 'date',
            'monthly_rent'     => 'decimal:2',
            'security_deposit' => 'decimal:2',
            'advance_payment'  => 'decimal:2',
            'late_fee'         => 'decimal:2',
            'due_day'          => 'integer',
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

    public function rentBillings(): HasMany
    {
        return $this->hasMany(RentBilling::class, 'contract_id');
    }
}

