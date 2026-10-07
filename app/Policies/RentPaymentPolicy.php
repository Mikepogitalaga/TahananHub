<?php

namespace App\Policies;

use App\Models\RentPayment;
use App\Models\User;
use App\Services\PrivacyAccessService;

class RentPaymentPolicy
{
    public function __construct(private PrivacyAccessService $privacy) {}

    public function view(User $user, RentPayment $payment): bool
    {
        $owner = $payment->billing?->contract?->property?->houseOwner;

        if ($user->isOwner() && $user->houseOwner?->id === $owner?->id) {
            return true;
        }

        if ($user->isTenant() && $user->tenantProfile?->id === $payment->tenant_id) {
            return true;
        }

        if ($user->isAdmin() && $owner) {
            return $this->privacy->checkAdminHasAccess($user, $owner, 'VIEW_PAYMENTS');
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function delete(User $user, RentPayment $payment): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $payment->billing?->contract?->property?->house_owner_id;
    }
}

