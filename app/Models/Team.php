<?php

namespace App\Models;

use App\Enums\TeamRole;
use App\Enums\Visibility;
use App\Models\Concerns\HasTeamVisibility;
use App\Models\Concerns\HasVisibility;
use App\Models\Pivots\Teamable;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[Fillable(['name', 'slug', 'visibility', 'created_by_user_id'])]
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory, HasTeamVisibility, HasVisibility {
        HasTeamVisibility::applyModelVisibilityAccess insteadof HasVisibility;
    }

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'visibility' => Visibility::Private,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return $this->visibilityCasts();
    }

    /**
     * @return MorphToMany<Task, $this>
     */
    public function tasks(): MorphToMany
    {
        return $this->morphedByMany(Task::class, 'teamable');
    }

    /**
     * @return MorphMany<Task, $this>
     */
    public function ownedTasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'owner');
    }

    /**
     * @return MorphToMany<User, $this>
     */
    public function members(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'teamable')
            ->using(Teamable::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function addMember(User $user, TeamRole $role = TeamRole::Member): void
    {
        $this->members()->syncWithoutDetaching([
            $user->id => ['role' => $role->value],
        ]);
    }

    public function isLeader(User $user): bool
    {
        return $this->members()
            ->whereKey($user->id)
            ->wherePivot('role', TeamRole::Leader->value)
            ->exists();
    }

    public function roleFor(User $user): ?TeamRole
    {
        /** @var User|null $member */
        $member = $this->members()->whereKey($user->id)->first();

        return $member?->pivot?->role;
    }

    public function leaderCount(): int
    {
        return $this->members()
            ->wherePivot('role', TeamRole::Leader->value)
            ->count();
    }

    /**
     * @return MorphToMany<Project, $this>
     */
    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'teamable');
    }

    /**
     * @return Builder<Task>
     */
    public function relatedTasksQuery(): Builder
    {
        return Task::query()->forTeam($this);
    }

    protected static function superAdminSeesAll(): bool
    {
        return true;
    }
}
