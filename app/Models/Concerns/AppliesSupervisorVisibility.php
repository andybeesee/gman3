<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait AppliesSupervisorVisibility
{
    /**
     * @param  Builder<static>  $query
     */
    protected function applySupervisorVisibilityAccess(Builder $query, User $user): void
    {
        $descendantIds = $user->descendantIds();

        if ($descendantIds->isEmpty()) {
            return;
        }

        $this->extendQueryForSupervisorDescendants($query, $descendantIds);
    }

    /**
     * @param  Builder<static>  $query
     * @param  Collection<int, int>  $descendantIds
     */
    protected function extendQueryForSupervisorDescendants(Builder $query, Collection $descendantIds): void
    {
        //
    }
}
