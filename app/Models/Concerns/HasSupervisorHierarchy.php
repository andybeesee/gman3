<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasSupervisorHierarchy
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    public function isVisibleTo(User $viewer): bool
    {
        if ($viewer->isSuperAdmin()) {
            return true;
        }

        if ($viewer->is($this)) {
            return true;
        }

        return $viewer->descendantIds()->contains($this->id);
    }

    /**
     * @return Collection<int, int>
     */
    public function descendantIds(): Collection
    {
        $ids = collect();
        $frontier = $this->subordinates()->pluck('id');

        while ($frontier->isNotEmpty()) {
            $ids = $ids->merge($frontier);
            $frontier = User::query()
                ->whereIn('supervisor_id', $frontier)
                ->pluck('id');
        }

        return $ids->unique()->values();
    }

    public function isDescendantOf(User $potentialAncestor): bool
    {
        if ($this->is($potentialAncestor)) {
            return false;
        }

        return $potentialAncestor->descendantIds()->contains($this->id);
    }

    public function wouldCreateSupervisorCycle(?int $supervisorId): bool
    {
        if ($supervisorId === null) {
            return false;
        }

        if ($supervisorId === $this->id) {
            return true;
        }

        $supervisor = User::query()->find($supervisorId);

        if ($supervisor === null) {
            return false;
        }

        return $supervisor->isDescendantOf($this);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        if ($viewer->isSuperAdmin()) {
            return $query;
        }

        $descendantIds = $viewer->descendantIds();

        return $query->where(function (Builder $query) use ($viewer, $descendantIds): void {
            $query->whereKey($viewer->id);

            if ($descendantIds->isNotEmpty()) {
                $query->orWhereIn($query->qualifyColumn('id'), $descendantIds);
            }
        });
    }
}
