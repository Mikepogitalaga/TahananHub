<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use App\Services\PrivacyAccessService;

class ExpensePolicy
{
    public function __construct(private PrivacyAccessService $privacy) {}

    public function view(User $user, Expense $expense): bool
    {
        if ($user->isOwner() && $user->houseOwner?->id === $expense->house_owner_id) {
            return true;
        }

        if ($user->isAdmin() && $expense->houseOwner) {
            return $this->privacy->checkAdminHasAccess($user, $expense->houseOwner, 'VIEW_EXPENSES');
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $expense->house_owner_id;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->isOwner() && $user->houseOwner?->id === $expense->house_owner_id;
    }
}

