<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasTeamVisibility
{
    /**
     * @param  Builder<static>  $query
     */
    protected function applyModelVisibilityAccess(Builder $query, User $user): void
    {
        $query->orWhereHas('members', fn (Builder $query) => $query->whereKey($user->id));
    }
}
