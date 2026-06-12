<?php

namespace Database\Seeders\Concerns;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

trait SeedsVisibilityGrants
{
    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Team>|null  $teams
     */
    protected function maybeSeedVisibilityGrants(
        Team|Project|Task $resource,
        Collection $users,
        ?Collection $teams = null,
    ): void {
        if ($resource->isPublic() || ! fake()->boolean(12)) {
            return;
        }

        $teams ??= Team::query()->get();

        if (fake()->boolean(60) && $users->isNotEmpty()) {
            $candidate = $users->random();

            if (! $resource->isVisibleTo($candidate)) {
                $resource->grantAccessTo($candidate);
            }
        }

        if (fake()->boolean(40) && $teams->isNotEmpty()) {
            $team = $teams->random();

            if ($team->is($resource)) {
                return;
            }

            $resource->grantAccessTo($team);
        }
    }
}
