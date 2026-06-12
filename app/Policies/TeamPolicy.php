<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view any teams.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        return $team->isVisibleTo($user);
    }

    /**
     * Determine whether the user can update the team's members.
     */
    public function updateMembers(User $user, Team $team): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $team->members()->whereKey($user->id)->exists();
    }
}
