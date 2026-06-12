<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the user profile.
     */
    public function view(User $user, User $model): bool
    {
        return $model->isVisibleTo($user);
    }

    /**
     * Determine whether the user can update the supervision hierarchy.
     */
    public function updateHierarchy(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
