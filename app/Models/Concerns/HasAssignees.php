<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasAssignees
{
    /**
     * @return MorphToMany<User, $this>
     */
    public function assignees(): MorphToMany
    {
        return $this->morphToMany(User::class, 'assignable');
    }

    public function syncAssignees(iterable $users): void
    {
        $userIds = collect($users)
            ->map(fn (User|int $user): int => $user instanceof User ? $user->id : $user)
            ->all();

        $this->assignees()->sync($userIds);
    }
}
