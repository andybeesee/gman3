<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasTeams;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'super_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasTeams, Notifiable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'super_admin' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'super_admin' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->super_admin;
    }

    /**
     * @return MorphToMany<Task, $this>
     */
    public function assignedTasks(): MorphToMany
    {
        return $this->morphedByMany(Task::class, 'assignable');
    }

    /**
     * @return MorphMany<Task, $this>
     */
    public function ownedTasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'owner');
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function ownedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_user_id');
    }

    /**
     * @return Builder<Task>
     */
    public function relatedTasksQuery(): Builder
    {
        return Task::query()->where(function (Builder $query): void {
            $query->whereHas('assignees', fn (Builder $query) => $query->whereKey($this->id))
                ->orWhere(function (Builder $query): void {
                    $query->where('owner_type', 'user')
                        ->where('owner_id', $this->id);
                })
                ->orWhere(function (Builder $query): void {
                    $query->where('owner_type', 'project')
                        ->whereHasMorph('owner', [Project::class], fn (Builder $query) => $query->where('owner_user_id', $this->id));
                });
        });
    }
}
