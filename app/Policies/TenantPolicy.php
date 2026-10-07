<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Services\PrivacyAccessService;

class TenantPolicy
{
    public function __construct(private PrivacyAccessService $privacy) {}

    public function view(User $user, Tenant $tenant): bool
    {
        // 1. Owner owns this tenant
        if ($user->isOwner() && $user->houseOwner?->id === $tenant->house_owner_id) {
            return true;
        }

        // 2. Tenant self-view
        if ($user->isTenant() && $user->id === $tenant->user_id) {
            return true;
        }

        // 3. Super Admin with active approved VIEW_TENANTS session
        if ($user->isAdmin() && $tenant->houseOwner) {
            return $this->privacy->checkAdminHasAccess($user, $tenant->houseOwner, 'VIEW_TENANTS');
        }

        return false;
    }

    public function viewContact(User $user, Tenant $tenant): bool
    {
        if ($user->isOwner() && $user->houseOwner?->id === $tenant->house_owner_id) {
            return true;
        }

        if ($user->isTenant() && $user->id === $tenant->user_id) {
            return true;
        }

        if ($user->isAdmin() && $tenant->houseOwner) {
            return $this->privacy->checkAdminHasAccess($user, $tenant->houseOwner, 'VIEW_TENANT_CONTACT');
        }

        return false;
    }

    public function viewDocuments(User $user, Tenant $tenant): bool
    {
        if ($user->isOwner() && $user->houseOwner?->id === $tenant->house_owner_id) {
            return true;
        }

        if ($user->isTenant() && $user->id === $tenant->user_id) {
            return true;
        }

        if ($user->isAdmin() && $tenant->houseOwner) {
            return $this->privacy->checkAdminHasAccess($user, $tenant->houseOwner, 'VIEW_TENANT_DOCUMENTS');
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $tenant->house_owner_id;
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $tenant->house_owner_id;
    }
}

