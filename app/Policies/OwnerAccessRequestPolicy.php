<?php

namespace App\Policies;

use App\Models\OwnerAccessRequest;
use App\Models\User;

class OwnerAccessRequestPolicy
{
    public function view(User $user, OwnerAccessRequest $request): bool
    {
        if ($user->isAdmin() && $user->id === $request->requested_by_user_id) {
            return true;
        }

        if ($user->isOwner() && $user->houseOwner?->id === $request->house_owner_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function respond(User $user, OwnerAccessRequest $request): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $request->house_owner_id;
    }
}

