<?php

namespace App\Models;

use App\Enums\Visibility;
use App\Models\Concerns\HasProjectOwnership;
use App\Models\Concerns\HasSchedulableDates;
use App\Models\Concerns\HasStatuses;
use App\Models\Concerns\HasTeams;
use App\Models\Concerns\HasVisibility;
use App\Models\Scopes\VisibleToAuthenticatedUserScope;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['title', 'description', 'start_date', 'due_date', 'visibility', 'created_by_user_id', 'owner_user_id'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasProjectOwnership, HasSchedulableDates, HasStatuses, HasTeams, HasVisibility {
        HasProjectOwnership::applyOwnershipVisibilityAccess insteadof HasVisibility;
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
        ];
    }

    public function getStatusAttribute(): ?Status
    {
        return $this->currentStatusChange?->status;
    }

    /**
     * @return MorphMany<Task, $this>
     */
    public function ownedTasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'owner');
    }
}
