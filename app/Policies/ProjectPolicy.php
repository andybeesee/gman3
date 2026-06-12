<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        if ($project->isPersonallyOwned()) {
            return $project->owner_user_id === $user->id;
        }

        return $project->teams()->whereHas('members', fn ($query) => $query->whereKey($user->id))->exists();
    }
}
