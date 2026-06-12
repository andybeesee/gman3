<?php

namespace App\Models\Concerns;

use App\Enums\Visibility;
use App\Models\Scopes\VisibleToAuthenticatedUserScope;
use App\Models\Team;
use App\Models\User;
use App\Models\VisibilityGrant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasVisibility
{
    /**
     * @return array<string, string>
     */
    protected function visibilityCasts(): array
    {
        return [
            'visibility' => Visibility::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return MorphMany<VisibilityGrant, $this>
     */
    public function visibilityGrants(): MorphMany
    {
        return $this->morphMany(VisibilityGrant::class, 'grantable');
    }

    public function isPublic(): bool
    {
        return $this->visibility === Visibility::Public;
    }

    public function isPrivate(): bool
    {
        return $this->visibility === Visibility::Private;
    }

    public function isVisibleTo(User $user): bool
    {
        if ($this->isPublic()) {
            return true;
        }

        return static::query()
            ->withoutGlobalScope(VisibleToAuthenticatedUserScope::class)
            ->whereKey($this->getKey())
            ->wherePrivate()
            ->whereAccessibleTo($user)
            ->exists();
    }

    public function grantAccessTo(User|Team $grantee): VisibilityGrant
    {
        return $this->visibilityGrants()->firstOrCreate([
            'grantee_type' => $grantee->getMorphClass(),
            'grantee_id' => $grantee->getKey(),
        ]);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('visibility', Visibility::Public)
                ->orWhere(function (Builder $query) use ($user): void {
                    $query->wherePrivate()
                        ->whereAccessibleTo($user);
                });
        });
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWherePrivate(Builder $query): Builder
    {
        return $query->where('visibility', Visibility::Private);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWhereAccessibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('created_by_user_id', $user->id);

            $this->extendVisibilityAccessQuery($query, $user);
            $this->extendVisibilityGrantQuery($query, $user);
        });
    }

    protected function extendVisibilityAccessQuery(Builder $query, User $user): void
    {
        $this->applyOwnershipVisibilityAccess($query, $user);
        $this->applyModelVisibilityAccess($query, $user);
    }

    /**
     * @param  Builder<static>  $query
     */
    protected function applyOwnershipVisibilityAccess(Builder $query, User $user): void
    {
        //
    }

    /**
     * @param  Builder<static>  $query
     */
    protected function applyModelVisibilityAccess(Builder $query, User $user): void
    {
        //
    }

    /**
     * @param  Builder<static>  $query
     */
    protected function extendVisibilityGrantQuery(Builder $query, User $user): void
    {
        $query->orWhereHas('visibilityGrants', function (Builder $query) use ($user): void {
            $query->where(function (Builder $query) use ($user): void {
                $query->where('grantee_type', 'user')
                    ->where('grantee_id', $user->id);
            })->orWhere(function (Builder $query) use ($user): void {
                $query->where('grantee_type', 'team')
                    ->whereIn('grantee_id', Team::query()
                        ->whereHas('members', fn (Builder $query) => $query->whereKey($user->id))
                        ->select('id'));
            });
        });
    }
}
