<?php

namespace App\Models\Concerns;

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

    public function isPersonal(): bool
    {
        return $this->owner_type === 'user';
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('owner_type', '!=', 'user')
                ->orWhere('owner_id', $user->id)
                ->orWhereHas('assignees', fn (Builder $query) => $query->whereKey($user->id));
        });
    }
}
