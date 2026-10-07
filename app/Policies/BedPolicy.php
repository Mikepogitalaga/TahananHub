<?php

namespace App\Policies;

use App\Models\Bed;
use App\Models\User;
use App\Services\PrivacyAccessService;

class BedPolicy
{
    public function __construct(private PrivacyAccessService $privacy) {}

    public function view(User $user, Bed $bed): bool
    {
        $owner = $bed->room?->property?->houseOwner;

        if ($user->isOwner() && $user->houseOwner?->id === $owner?->id) {
            return true;
        }

        if ($user->isTenant()) {
            return $user->tenantProfile?->currentAssignment?->bed_id === $bed->id;
        }

        if ($user->isAdmin() && $owner) {
            return $this->privacy->checkAdminHasAccess($user, $owner, 'VIEW_BEDS');
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, Bed $bed): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $bed->room?->property?->house_owner_id;
    }

    public function delete(User $user, Bed $bed): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $bed->room?->property?->house_owner_id;
    }
}

