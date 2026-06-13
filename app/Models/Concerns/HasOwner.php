<?php

namespace App\Models\Concerns;

use App\Enums\Visibility;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait HasOwner
{
    /**
     * @return MorphTo<Model, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function setOwner(Model $owner): static
    {
        $this->owner()->associate($owner);
        $this->save();

        return $this;
    }

    public function isUserOwned(): bool
    {
        return $this->owner_type === 'user';
    }

    public function isTeamOwned(): bool
    {
        return $this->owner_type === 'team';
    }

    public function isProjectOwned(): bool
    {
        return $this->owner_type === 'project';
    }

    /**
     * @param  Builder<static>  $query
     */
    protected function applyOwnershipVisibilityAccess(Builder $query, User $user): void
    {
        $query->orWhere(function (Builder $query) use ($user): void {
            $query->where('owner_type', 'user')
                ->where('owner_id', $user->id);
        })->orWhereHas('assignees', fn (Builder $query) => $query->whereKey($user->id))
            ->orWhere(function (Builder $query) use ($user): void {
                $query->where('owner_type', 'team')
                    ->whereIn('owner_id', Team::query()
                        ->whereHas('members', fn (Builder $query) => $query->whereKey($user->id))
                        ->select('id'));
            })->orWhere(function (Builder $query) use ($user): void {
                $query->where('owner_type', 'project')
                    ->whereHasMorph('owner', [Project::class], function (Builder $query) use ($user): void {
                        $query->where(function (Builder $query) use ($user): void {
                            $query->where('visibility', Visibility::Public)
                                ->orWhere(function (Builder $query) use ($user): void {
                                    $query->where('visibility', Visibility::Private)
                                        ->where(function (Builder $query) use ($user): void {
                                            $query->where('created_by_user_id', $user->id)
                                                ->orWhere('owner_user_id', $user->id)
                                                ->orWhereHas('teams', function (Builder $query) use ($user): void {
                                                    $query->whereHas('members', fn (Builder $query) => $query->whereKey($user->id));
                                                });
                                        });
                                });
                        });
                    });
            });
    }
}
