<?php

namespace App\Models;

use App\Enums\Visibility;
use App\Models\Concerns\HasAssignees;
use App\Models\Concerns\HasOwner;
use App\Models\Concerns\HasSchedulableDates;
use App\Models\Concerns\HasStatuses;
use App\Models\Concerns\HasTeams;
use App\Models\Concerns\HasVisibility;
use App\Models\Scopes\VisibleToAuthenticatedUserScope;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[Fillable(['title', 'description', 'start_date', 'due_date', 'visibility', 'created_by_user_id', 'completed_at', 'completed_by_user_id'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasAssignees, HasFactory, HasOwner, HasSchedulableDates, HasStatuses, HasTeams, HasVisibility {
        HasOwner::applyOwnershipVisibilityAccess insteadof HasVisibility;
        setStatus as protected setStatusFromTrait;
    }

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'visibility' => Visibility::Private,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new VisibleToAuthenticatedUserScope);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            ...$this->visibilityCasts(),
            'start_date' => 'datetime',
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function getStatusAttribute(): ?Status
    {
        return $this->currentStatusChange?->status;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForTeam(Builder $query, Team $team): Builder
    {
        return $query->forTeamId($team->id);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForTeamId(Builder $query, int $teamId): Builder
    {
        return $query->where(function (Builder $query) use ($teamId): void {
            $query->whereIn($query->qualifyColumn('id'), function ($subQuery) use ($teamId): void {
                $subQuery->from('teamables')
                    ->select('teamable_id')
                    ->where('teamable_type', 'task')
                    ->where('team_id', $teamId);
            })->orWhere(function (Builder $query) use ($teamId): void {
                $query->where('owner_type', 'team')
                    ->where('owner_id', $teamId);
            })->orWhere(function (Builder $query) use ($teamId): void {
                $query->where('owner_type', 'project')
                    ->whereIn('owner_id', function ($subQuery) use ($teamId): void {
                        $subQuery->from('teamables')
                            ->join('projects', 'projects.id', '=', 'teamables.teamable_id')
                            ->select('teamables.teamable_id')
                            ->where('teamables.teamable_type', 'project')
                            ->where('teamables.team_id', $teamId)
                            ->whereNull('projects.owner_user_id');
                    });
            });
        });
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForTeamColumn(Builder $query, string $teamIdColumn): Builder
    {
        return $query->where(function (Builder $query) use ($teamIdColumn): void {
            $query->whereExists(function ($subQuery) use ($teamIdColumn): void {
                $subQuery->from('teamables')
                    ->whereColumn('teamable_id', 'tasks.id')
                    ->where('teamable_type', 'task')
                    ->whereColumn('team_id', $teamIdColumn);
            })->orWhere(function (Builder $query) use ($teamIdColumn): void {
                $query->where('owner_type', 'team')
                    ->whereColumn('owner_id', $teamIdColumn);
            })->orWhere(function (Builder $query) use ($teamIdColumn): void {
                $query->where('owner_type', 'project')
                    ->whereExists(function ($subQuery) use ($teamIdColumn): void {
                        $subQuery->from('teamables')
                            ->join('projects', 'projects.id', '=', 'teamables.teamable_id')
                            ->whereColumn('teamables.teamable_id', 'tasks.owner_id')
                            ->where('teamables.teamable_type', 'project')
                            ->whereColumn('teamables.team_id', $teamIdColumn)
                            ->whereNull('projects.owner_user_id');
                    });
            });
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function setStatus(Status $status, ?User $user = null): StatusChange
    {
        $statusChange = $this->setStatusFromTrait($status, $user);

        $this->syncCompletionFromStatus($status, $user);

        return $statusChange;
    }

    protected function syncCompletionFromStatus(Status $status, ?User $user): void
    {
        if ($status->is_closed) {
            $this->forceFill([
                'completed_at' => now(),
                'completed_by_user_id' => $user?->id ?? Auth::id(),
            ])->save();

            return;
        }

        if ($this->completed_at === null) {
            return;
        }

        $this->forceFill([
            'completed_at' => null,
            'completed_by_user_id' => null,
        ])->save();
    }
}
