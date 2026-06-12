<?php

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[Fillable(['name', 'slug'])]
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

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
        return $this->morphedByMany(User::class, 'teamable');
    }

    /**
     * @return MorphToMany<Project, $this>
     */
    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'teamable');
    }
}
