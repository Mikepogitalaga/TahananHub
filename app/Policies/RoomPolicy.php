<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use App\Services\PrivacyAccessService;

class RoomPolicy
{
    public function __construct(private PrivacyAccessService $privacy) {}

    public function view(User $user, Room $room): bool
    {
        $owner = $room->property?->houseOwner;

        if ($user->isOwner() && $user->houseOwner?->id === $owner?->id) {
            return true;
        }

        if ($user->isTenant()) {
            return $user->tenantProfile?->currentAssignment?->room_id === $room->id;
        }

        if ($user->isAdmin() && $owner) {
            return $this->privacy->checkAdminHasAccess($user, $owner, 'VIEW_ROOMS');
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, Room $room): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $room->property?->house_owner_id;
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $room->property?->house_owner_id;
    }
}

