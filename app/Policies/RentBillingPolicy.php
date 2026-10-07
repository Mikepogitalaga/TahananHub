<?php

namespace App\Policies;

use App\Models\RentBilling;
use App\Models\User;
use App\Services\PrivacyAccessService;

class RentBillingPolicy
{
    public function __construct(private PrivacyAccessService $privacy) {}

    public function view(User $user, RentBilling $billing): bool
    {
        $owner = $billing->contract?->property?->houseOwner;

        if ($user->isOwner() && $user->houseOwner?->id === $owner?->id) {
            return true;
        }

        if ($user->isTenant() && $user->tenantProfile?->id === $billing->tenant_id) {
            return true;
        }

        if ($user->isAdmin() && $owner) {
            return $this->privacy->checkAdminHasAccess($user, $owner, 'VIEW_BILLING');
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, RentBilling $billing): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $billing->contract?->property?->house_owner_id;
    }

    public function delete(User $user, RentBilling $billing): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $billing->contract?->property?->house_owner_id;
    }
}

