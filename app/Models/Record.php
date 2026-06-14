<?php

namespace App\Models;

use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['title', 'description', 'start_date', 'due_date', 'visibility', 'created_by_user_id', 'owner_type', 'owner_id', 'archived_at'])]
class Record extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visibility' => Visibility::class,
            'start_date' => 'datetime',
            'due_date' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function recordable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return HasMany<VisibilityGrant, $this>
     */
    public function visibilityGrants(): HasMany
    {
        return $this->hasMany(VisibilityGrant::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query->where(function (Builder $query) use ($user): void {
                $query->whereIn('recordable_type', ['project', 'team'])
                    ->orWhere(fn (Builder $query) => $this->applyVisibleToQuery($query, $user));
            });
        }

        return $this->applyVisibleToQuery($query, $user);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function applyVisibleToQuery(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('visibility', Visibility::Public)
                ->orWhere(function (Builder $query) use ($user): void {
                    $query->where('visibility', Visibility::Private)
                        ->whereAccessibleTo($user);
                });
        });
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWhereAccessibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('created_by_user_id', $user->id)
                ->orWhere(function (Builder $query) use ($user): void {
                    $query->where('owner_type', 'user')
                        ->where('owner_id', $user->id);
                })
                ->orWhere(function (Builder $query) use ($user): void {
                    $query->where('owner_type', 'team')
                        ->whereIn('owner_id', $this->teamIdsFor($user));
                })
                ->orWhere(function (Builder $query) use ($user): void {
                    $query->where('owner_type', 'project')
                        ->whereIn('owner_id', $this->visibleProjectIdsFor($user));
                })
                ->orWhere(function (Builder $query) use ($user): void {
                    $query->where('recordable_type', 'task')
                        ->whereIn('recordable_id', $this->assignedTaskIdsFor($user));
                })
                ->orWhere(function (Builder $query) use ($user): void {
                    $query->where('recordable_type', 'team')
                        ->whereIn('recordable_id', $this->teamIdsFor($user));
                })
                ->orWhere(function (Builder $query) use ($user): void {
                    $query->whereIn('recordable_id', $this->teamableRecordableIdsFor($user, 'project'))
                        ->where('recordable_type', 'project');
                })
                ->orWhereHas('visibilityGrants', function (Builder $query) use ($user): void {
                    $query->where(function (Builder $query) use ($user): void {
                        $query->where('grantee_type', 'user')
                            ->where('grantee_id', $user->id);
                    })->orWhere(function (Builder $query) use ($user): void {
                        $query->where('grantee_type', 'team')
                            ->whereIn('grantee_id', $this->teamIdsFor($user));
                    });
                });
        });
    }

    protected function teamIdsFor(User $user): Builder
    {
        return Team::query()
            ->whereHas('members', fn (Builder $query) => $query->whereKey($user->id))
            ->select('id');
    }

    protected function assignedTaskIdsFor(User $user): Builder
    {
        return Task::query()
            ->withoutGlobalScopes()
            ->whereHas('assignees', fn (Builder $query) => $query->whereKey($user->id))
            ->select('id');
    }

    protected function visibleProjectIdsFor(User $user): Builder
    {
        return Record::query()
            ->where('recordable_type', 'project')
            ->where(function (Builder $query) use ($user): void {
                $query->where('visibility', Visibility::Public)
                    ->orWhere('created_by_user_id', $user->id)
                    ->orWhere(function (Builder $query) use ($user): void {
                        $query->where('owner_type', 'user')
                            ->where('owner_id', $user->id);
                    })
                    ->orWhereIn('recordable_id', $this->teamableRecordableIdsFor($user, 'project'));
            })
            ->select('recordable_id');
    }

    protected function teamableRecordableIdsFor(User $user, string $recordableType): Builder
    {
        return Team::query()
            ->whereHas('members', fn (Builder $query) => $query->whereKey($user->id))
            ->join('teamables', 'teamables.team_id', '=', 'teams.id')
            ->where('teamables.teamable_type', $recordableType)
            ->select('teamables.teamable_id');
    }
}
