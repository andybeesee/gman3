<?php

namespace App\Models\Concerns;

use App\Enums\TeamRole;
use App\Models\Pivots\Teamable;
use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTeams
{
    /**
     * @return MorphToMany<Team, $this>
     */
    public function teams(): MorphToMany
    {
        return $this->morphToMany(Team::class, 'teamable')
            ->using(Teamable::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function syncTeams(iterable $teams): void
    {
        $teamIds = collect($teams)
            ->map(fn (Team|int $team): int => $team instanceof Team ? $team->id : $team)
            ->all();

        $this->teams()->sync($teamIds);
    }

    public function teamRole(Team $team): ?TeamRole
    {
        /** @var Team|null $membership */
        $membership = $this->teams()
            ->whereKey($team->id)
            ->first();

        return $membership?->pivot?->role;
    }

    public function isTeamLeader(Team $team): bool
    {
        return $this->teamRole($team) === TeamRole::Leader;
    }
}
