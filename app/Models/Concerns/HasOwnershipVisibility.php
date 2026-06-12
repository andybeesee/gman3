<?php

namespace App\Models\Concerns;

use App\Enums\ProjectOwnership;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasOwnershipVisibility
{
    /**
     * @return array<string, string>
     */
    protected function ownershipVisibilityCasts(): array
    {
        return [
            'ownership' => ProjectOwnership::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function isPersonallyOwned(): bool
    {
        return $this->ownership === ProjectOwnership::User;
    }

    public function isTeamOwned(): bool
    {
        return $this->ownership === ProjectOwnership::Team;
    }

    public function setPersonalOwnership(User $user): static
    {
        $this->forceFill([
            'ownership' => ProjectOwnership::User,
            'owner_user_id' => $user->id,
        ])->save();

        return $this;
    }

    public function setTeamOwnership(): static
    {
        $this->forceFill([
            'ownership' => ProjectOwnership::Team,
            'owner_user_id' => null,
        ])->save();

        return $this;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where(function (Builder $query) use ($user): void {
                $query->where('ownership', ProjectOwnership::User->value)
                    ->where('owner_user_id', $user->id);
            })->orWhere(function (Builder $query) use ($user): void {
                $query->where('ownership', ProjectOwnership::Team->value)
                    ->whereHas('teams.members', fn (Builder $query) => $query->whereKey($user->id));
            });
        });
    }
}
