<?php

namespace App\Models\Concerns;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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

    public function isPersonal(): bool
    {
        if ($this->relationLoaded('teams')) {
            return $this->teams->isEmpty();
        }

        return ! $this->teams()->exists();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->whereHas('teams')
                ->orWhereHas('assignees', fn (Builder $query) => $query->whereKey($user->id));
        });
    }
}
