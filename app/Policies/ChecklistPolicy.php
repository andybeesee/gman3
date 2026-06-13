<?php

namespace App\Policies;

use App\Models\Checklist;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;

class ChecklistPolicy
{
    /**
     * Determine whether the user can view any checklists.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the checklist.
     */
    public function view(User $user, Checklist $checklist): bool
    {
        return $checklist->isVisibleTo($user);
    }

    /**
     * Determine whether the user can reorder tasks in the checklist.
     */
    public function reorderTasks(User $user, Checklist $checklist): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($checklist->created_by_user_id === $user->id) {
            return true;
        }

        if ($checklist->owner instanceof User) {
            return $checklist->owner->is($user);
        }

        if ($checklist->owner instanceof Team) {
            return $checklist->owner->members()->whereKey($user->id)->exists();
        }

        if ($checklist->owner instanceof Project) {
            return $checklist->owner->created_by_user_id === $user->id
                || $checklist->owner->owner_user_id === $user->id
                || $checklist->owner->teams()->whereHas('members', fn ($query) => $query->whereKey($user->id))->exists();
        }

        return false;
    }
}
