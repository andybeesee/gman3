<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        if (! $task->isPersonal()) {
            return true;
        }

        return $task->owner_id === $user->id
            || $task->assignees()->whereKey($user->id)->exists();
    }

    /**
     * Determine whether the user can update the task status.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        return $task->assignees()->whereKey($user->id)->exists();
    }
}
