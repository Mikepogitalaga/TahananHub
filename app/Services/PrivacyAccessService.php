<?php

namespace App\Services;

use App\Models\HouseOwner;
use App\Models\OwnerAccessRequest;
use App\Models\OwnerAccessSession;
use App\Models\PrivacyAuditLog;
use App\Models\User;
use App\Notifications\AccessRequestReceived;
use App\Notifications\AccessRequestResolved;
use App\Notifications\AccessSessionExpired;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class PrivacyAccessService
{
    // Granular permissions defined in specs
    public const PERMISSIONS = [
        'VIEW_PROPERTY'         => 'Property Information',
        'VIEW_ROOMS'            => 'Rooms & Capacities',
        'VIEW_BEDS'             => 'Beds & Occupancies',
        'VIEW_TENANTS'          => 'Tenant Personal Profiles',
        'VIEW_TENANT_CONTACT'   => 'Tenant Contact & Emergency Details',
        'VIEW_TENANT_DOCUMENTS' => 'Tenant Documents & Government IDs',
        'VIEW_CONTRACTS'        => 'Rental Contracts & Terms',
        'VIEW_BILLING'          => 'Billing Statements & Rates',
        'VIEW_PAYMENTS'         => 'Payment Records & Receipts',
        'VIEW_EXPENSES'         => 'Property Expenses & Outflows',
        'VIEW_INCOME'           => 'Financial Income & Net Revenue',
        'VIEW_MAINTENANCE'      => 'Maintenance Requests & Details',
        'VIEW_REPORTS'          => 'Business & Financial Reports',
    ];

    /**
     * Super Admin submits a temporary access request.
     */
    public function requestAccess(
        User $admin,
        HouseOwner $owner,
        array $permissions,
        string $reason,
        int $durationMinutes,
        ?string $notes = null
    ): OwnerAccessRequest {
        return DB::transaction(function () use ($admin, $owner, $permissions, $reason, $durationMinutes, $notes) {
            $request = OwnerAccessRequest::create([
                'requested_by_user_id'       => $admin->id,
                'house_owner_id'             => $owner->id,
                'reason'                     => $reason,
                'requested_duration_minutes' => $durationMinutes,
                'permissions'                => array_values($permissions),
                'status'                     => 'pending',
                'request_notes'              => $notes,
            ]);

            PrivacyAuditLog::create([
                'admin_user_id'     => $admin->id,
                'house_owner_id'    => $owner->id,
                'access_session_id' => null,
                'action'            => 'access_requested',
                'resource'          => 'OwnerAccessRequest',
                'resource_id'       => $request->id,
                'description'       => "Admin requested temporary access for {$durationMinutes} mins: " . implode(', ', $permissions),
                'ip_address'        => Request::ip(),
                'user_agent'        => Request::userAgent(),
            ]);

            // Notify the House Owner that the platform admin requested access.
            $owner->user?->notify(new AccessRequestReceived($request));

            return $request;
        });
    }

    /**
     * House Owner approves the access request, creating an active timed session.
     */
    public function approveAccess(OwnerAccessRequest $request, User $ownerUser, ?string $ownerNotes = null): OwnerAccessSession
    {
        return DB::transaction(function () use ($request, $ownerUser, $ownerNotes) {
            $now = Carbon::now();
            $expiresAt = $now->copy()->addMinutes($request->requested_duration_minutes);

            $request->update([
                'status'        => 'approved',
                'owner_notes'   => $ownerNotes,
                'responded_at'  => $now,
            ]);

            $session = OwnerAccessSession::create([
                'access_request_id' => $request->id,
                'admin_user_id'     => $request->requested_by_user_id,
                'house_owner_id'    => $request->house_owner_id,
                'permissions'       => $request->permissions,
                'granted_at'        => $now,
                'expires_at'        => $expiresAt,
                'is_active'         => true,
            ]);

            PrivacyAuditLog::create([
                'admin_user_id'     => $request->requested_by_user_id,
                'house_owner_id'    => $request->house_owner_id,
                'access_session_id' => $session->id,
                'action'            => 'access_approved',
                'resource'          => 'OwnerAccessSession',
                'resource_id'       => $session->id,
                'description'       => "Owner approved access session until {$expiresAt->toDateTimeString()}",
                'ip_address'        => Request::ip(),
                'user_agent'        => Request::userAgent(),
            ]);

            // Notify the requesting admin that access was approved.
            $request->requestedBy?->notify(new AccessRequestResolved($request, 'approved', $ownerNotes));

            return $session;
        });
    }

    /**
     * House Owner denies the access request.
     */
    public function denyAccess(OwnerAccessRequest $request, User $ownerUser, ?string $ownerNotes = null): void
    {
        DB::transaction(function () use ($request, $ownerNotes) {
            $request->update([
                'status'       => 'denied',
                'owner_notes'  => $ownerNotes,
                'responded_at' => Carbon::now(),
            ]);

            PrivacyAuditLog::create([
                'admin_user_id'     => $request->requested_by_user_id,
                'house_owner_id'    => $request->house_owner_id,
                'access_session_id' => null,
                'action'            => 'access_denied',
                'resource'          => 'OwnerAccessRequest',
                'resource_id'       => $request->id,
                'description'       => "Owner denied access request: " . ($ownerNotes ?: 'No notes provided'),
                'ip_address'        => Request::ip(),
                'user_agent'        => Request::userAgent(),
            ]);

            // Notify the requesting admin that access was denied.
            $request->requestedBy?->notify(new AccessRequestResolved($request, 'denied', $ownerNotes));
        });
    }

    /**
     * Revoke an active access session immediately.
     */
    public function revokeAccess(OwnerAccessSession $session, User $actor): void
    {
        DB::transaction(function () use ($session, $actor) {
            $session->update([
                'is_active'  => false,
                'revoked_at' => Carbon::now(),
            ]);

            PrivacyAuditLog::create([
                'admin_user_id'     => $session->admin_user_id,
                'house_owner_id'    => $session->house_owner_id,
                'access_session_id' => $session->id,
                'action'            => 'access_revoked',
                'resource'          => 'OwnerAccessSession',
                'resource_id'       => $session->id,
                'description'       => "Session revoked by user #{$actor->id} ({$actor->role})",
                'ip_address'        => Request::ip(),
                'user_agent'        => Request::userAgent(),
            ]);
        });
    }

    /**
     * Check if a Super Admin currently has active approved permission for a given owner.
     */
    public function checkAdminHasAccess(User $admin, HouseOwner $owner, string $permission): bool
    {
        if (! $admin->isAdmin()) {
            return false;
        }

        $session = $this->getActiveSession($admin, $owner);
        if (! $session) {
            return false;
        }

        $permissions = is_array($session->permissions) ? $session->permissions : [];
        return in_array($permission, $permissions, true);
    }

    /**
     * Get the active approved session for admin & owner if valid.
     */
    public function getActiveSession(User $admin, HouseOwner $owner): ?OwnerAccessSession
    {
        return OwnerAccessSession::where('admin_user_id', $admin->id)
            ->where('house_owner_id', $owner->id)
            ->where('is_active', true)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    /**
     * Log an action performed by an admin on owner protected data.
     */
    public function logProtectedAction(
        User $admin,
        HouseOwner $owner,
        string $action,
        string $resource,
        ?int $resourceId = null,
        ?string $description = null
    ): void {
        $session = $this->getActiveSession($admin, $owner);

        PrivacyAuditLog::create([
            'admin_user_id'     => $admin->id,
            'house_owner_id'    => $owner->id,
            'access_session_id' => $session?->id,
            'action'            => $action,
            'resource'          => $resource,
            'resource_id'       => $resourceId,
            'description'       => $description,
            'ip_address'        => Request::ip(),
            'user_agent'        => Request::userAgent(),
        ]);
    }

    /**
     * Expire sessions that have passed their expiration date.
     * Marks the linked request as expired, writes an audit entry,
     * and notifies the admin that access has been terminated.
     */
    public function expireOldSessions(): int
    {
        $expiring = OwnerAccessSession::where('is_active', true)
            ->where('expires_at', '<=', Carbon::now())
            ->get();

        foreach ($expiring as $session) {
            DB::transaction(function () use ($session) {
                $session->update(['is_active' => false]);

                $session->accessRequest?->update(['status' => 'expired']);

                PrivacyAuditLog::create([
                    'admin_user_id'     => $session->admin_user_id,
                    'house_owner_id'    => $session->house_owner_id,
                    'access_session_id' => $session->id,
                    'action'            => 'access_expired',
                    'resource'          => 'OwnerAccessSession',
                    'resource_id'       => $session->id,
                    'description'       => 'Access session expired automatically at ' . $session->expires_at->toDateTimeString(),
                    'ip_address'        => Request::ip(),
                    'user_agent'        => Request::userAgent(),
                ]);
            });

            $session->admin?->notify(new AccessSessionExpired(
                $session->house_owner_id,
                $session->houseOwner?->business_name ?? 'House Owner #' . $session->house_owner_id,
                is_array($session->permissions) ? $session->permissions : [],
            ));
        }

        return $expiring->count();
    }
}

