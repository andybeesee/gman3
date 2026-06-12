<?php

namespace App\Models\Concerns;

use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTeams
{
    /**
     * @return MorphToMany<Team, $this>
     */
    public function teams(): MorphToMany
    {
        return $this->morphToMany(Team::class, 'teamable');
    }

    public function syncTeams(iterable $teams): void
    {
        $teamIds = collect($teams)
            ->map(fn (Team|int $team): int => $team instanceof Team ? $team->id : $team)
            ->all();

        $this->teams()->sync($teamIds);
    }
}
