<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_number',
        'user_id',
        'property_id',
        'room_id',
        'bed_id',
        'house_owner_id',
        'monthly_rent',
        'desired_move_in_date',
        'message',
        'status',
        'owner_response',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'desired_move_in_date' => 'date',
            'monthly_rent'         => 'decimal:2',
            'responded_at'         => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function houseOwner(): BelongsTo
    {
        return $this->belongsTo(HouseOwner::class);
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

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'bg-yellow-100 text-yellow-800',
            'approved'  => 'bg-green-100 text-green-800',
            'rejected'  => 'bg-red-100 text-red-800',
            'cancelled',
            'withdrawn' => 'bg-gray-100 text-gray-600',
            'expired'   => 'bg-orange-100 text-orange-800',
            default     => 'bg-gray-100 text-gray-600',
        };
    }

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $seq  = static::whereYear('created_at', $year)->count() + 1;
        return 'RR-' . $year . '-' . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }
}
