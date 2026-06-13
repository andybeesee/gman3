<?php

namespace App\Policies;

use App\Models\Checklist;
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
}
