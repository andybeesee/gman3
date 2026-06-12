<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

trait HasProjectOwnership
{
    use AppliesSupervisorVisibility;

    /**
     * @return BelongsTo<User, $this>
     */
    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function isUserOwned(): bool
    {
        return $this->owner_user_id !== null;
    }

    public function isTeamOwned(): bool
    {
        return $this->owner_user_id === null;
    }

    public function setUserOwnership(User $user): static
    {
        $this->forceFill([
            'owner_user_id' => $user->id,
            'created_by_user_id' => $this->created_by_user_id ?? $user->id,
        ])->save();

        return $this;
    }

    public function setTeamOwnership(?User $creator = null): static
    {
        $this->forceFill([
            'owner_user_id' => null,
            'created_by_user_id' => $this->created_by_user_id ?? $creator?->id,
        ])->save();

        return $this;
    }

    /**
     * @param  Builder<static>  $query
     */
    protected function applyOwnershipVisibilityAccess(Builder $query, User $user): void
    {
        $query->orWhere('owner_user_id', $user->id)
            ->orWhereHas('teams.members', fn (Builder $query) => $query->whereKey($user->id));

        $this->applySupervisorVisibilityAccess($query, $user);
    }

    /**
     * @param  Builder<static>  $query
     * @param  Collection<int, int>  $descendantIds
     */
    protected function extendQueryForSupervisorDescendants(Builder $query, Collection $descendantIds): void
    {
        $query->orWhereIn('owner_user_id', $descendantIds)
            ->orWhereIn('created_by_user_id', $descendantIds);
    }
}
