<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use App\Services\PrivacyAccessService;

class PropertyPolicy
{
    public function __construct(private PrivacyAccessService $privacy) {}

    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin();
    }

    public function view(User $user, Property $property): bool
    {
        // 1. Owner owns this property
        if ($user->isOwner() && $user->houseOwner?->id === $property->house_owner_id) {
            return true;
        }

        // 2. Tenant is assigned to this property
        if ($user->isTenant()) {
            return $user->tenantProfile?->currentAssignment?->property_id === $property->id;
        }

        // 3. Super Admin with active approved VIEW_PROPERTY session
        if ($user->isAdmin() && $property->houseOwner) {
            return $this->privacy->checkAdminHasAccess($user, $property->houseOwner, 'VIEW_PROPERTY');
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, Property $property): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $property->house_owner_id;
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $property->house_owner_id;
    }
}

