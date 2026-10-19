<?php

namespace App\Policies;

use App\Models\RentalRequest;
use App\Models\User;

class RentalRequestPolicy
{
    public function view(User $user, RentalRequest $request): bool
    {
        if ($user->isTenant()) {
            return $request->user_id === $user->id;
        }

        if ($user->isOwner()) {
            return $request->house_owner_id === $user->houseOwner?->id;
        }

        return false;
    }

    public function cancel(User $user, RentalRequest $request): bool
    {
        return $user->isTenant()
            && $request->user_id === $user->id
            && $request->isPending();
    }

    public function respond(User $user, RentalRequest $request): bool
    {
        return $user->isOwner()
            && $request->house_owner_id === $user->houseOwner?->id
            && $request->isPending();
    }
}
