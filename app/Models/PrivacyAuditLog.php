<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyAuditLog extends Model
{
    // Audit logs are immutable - no updates or deletes allowed
    protected $fillable = [
        'admin_user_id',
        'house_owner_id',
        'access_session_id',
        'action',
        'resource',
        'resource_id',
        'description',
        'ip_address',
        'user_agent',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function houseOwner(): BelongsTo
    {
        return $this->belongsTo(HouseOwner::class);
    }

    public function accessSession(): BelongsTo
    {
        return $this->belongsTo(OwnerAccessSession::class, 'access_session_id');
    }
}

