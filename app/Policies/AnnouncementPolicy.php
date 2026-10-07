<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function view(User $user, Announcement $announcement): bool
    {
        if ($announcement->is_platform_wide) {
            return true;
        }

        if ($user->isOwner() && $user->houseOwner?->id === $announcement->house_owner_id) {
            return true;
        }

        if ($user->isTenant()) {
            return $user->tenantProfile?->currentAssignment?->property_id === $announcement->property_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin();
    }

    public function update(User $user, Announcement $announcement): bool
    {
        if ($announcement->is_platform_wide && $user->isAdmin()) {
            return true;
        }
        return $user->isOwner() && $user->houseOwner?->id === $announcement->house_owner_id;
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        if ($announcement->is_platform_wide && $user->isAdmin()) {
            return true;
        }
        return $user->isOwner() && $user->houseOwner?->id === $announcement->house_owner_id;
    }
}

