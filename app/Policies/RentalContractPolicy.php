<?php

namespace App\Policies;

use App\Models\RentalContract;
use App\Models\User;
use App\Services\PrivacyAccessService;

class RentalContractPolicy
{
    public function __construct(private PrivacyAccessService $privacy) {}

    public function view(User $user, RentalContract $contract): bool
    {
        $owner = $contract->property?->houseOwner;

        if ($user->isOwner() && $user->houseOwner?->id === $owner?->id) {
            return true;
        }

        if ($user->isTenant() && $user->tenantProfile?->id === $contract->tenant_id) {
            return true;
        }

        if ($user->isAdmin() && $owner) {
            return $this->privacy->checkAdminHasAccess($user, $owner, 'VIEW_CONTRACTS');
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, RentalContract $contract): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $contract->property?->house_owner_id;
    }

    public function delete(User $user, RentalContract $contract): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $contract->property?->house_owner_id;
    }
}

