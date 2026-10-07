<?php

namespace App\Policies;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Services\PrivacyAccessService;

class MaintenanceRequestPolicy
{
    public function __construct(private PrivacyAccessService $privacy) {}

    public function view(User $user, MaintenanceRequest $request): bool
    {
        $owner = $request->property?->houseOwner;

        if ($user->isOwner() && $user->houseOwner?->id === $owner?->id) {
            return true;
        }

        if ($user->isTenant() && $user->tenantProfile?->id === $request->tenant_id) {
            return true;
        }

        if ($user->isAdmin() && $owner) {
            return $this->privacy->checkAdminHasAccess($user, $owner, 'VIEW_MAINTENANCE');
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isTenant() || $user->isOwner();
    }

    public function update(User $user, MaintenanceRequest $request): bool
    {
        $owner = $request->property?->houseOwner;
        return $user->isOwner() && $user->houseOwner?->id === $owner?->id;
    }

    public function delete(User $user, MaintenanceRequest $request): bool
    {
        $owner = $request->property?->houseOwner;
        return $user->isOwner() && $user->houseOwner?->id === $owner?->id;
    }
}

