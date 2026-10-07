<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentBilling extends Model
{
    protected $fillable = [
        'billing_number',
        'tenant_id',
        'contract_id',
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'rent_amount',
        'previous_balance',
        'late_fee',
        'discount',
        'other_charges',
        'other_charges_description',
        'total_amount',
        'amount_paid',
        'balance',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'billing_period_start' => 'date',
            'billing_period_end'   => 'date',
            'due_date'             => 'date',
            'rent_amount'          => 'decimal:2',
            'previous_balance'     => 'decimal:2',
            'late_fee'             => 'decimal:2',
            'discount'             => 'decimal:2',
            'other_charges'        => 'decimal:2',
            'total_amount'         => 'decimal:2',
            'amount_paid'          => 'decimal:2',
            'balance'              => 'decimal:2',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(RentalContract::class, 'contract_id');
    }

    public function rentPayments(): HasMany
    {
        return $this->hasMany(RentPayment::class, 'billing_id');
    }

    /**
     * Alias used by views/controllers ("payments" => payments for this billing).
     */
    public function payments(): HasMany
    {
        return $this->hasMany(RentPayment::class, 'billing_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isOverdue(): bool
    {
        return $this->due_date < now()->toDateString() && in_array($this->status, ['unpaid', 'partial']);
    }
}

