<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can update the task status.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        return $task->assignees()->whereKey($user->id)->exists();
    }
}
