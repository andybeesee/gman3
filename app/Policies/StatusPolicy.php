<?php

namespace App\Policies;

use App\Models\Status;
use App\Models\User;

class StatusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Status $status): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Status $status): bool
    {
        return $user->isSuperAdmin();
    }
}
