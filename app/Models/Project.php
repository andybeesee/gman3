<?php

namespace App\Models;

use App\Models\Concerns\HasOwnershipVisibility;
use App\Models\Concerns\HasSchedulableDates;
use App\Models\Concerns\HasStatuses;
use App\Models\Concerns\HasTeams;
use App\Models\Scopes\VisibleToAuthenticatedUserScope;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['title', 'description', 'start_date', 'due_date', 'ownership', 'owner_user_id'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasOwnershipVisibility, HasSchedulableDates, HasStatuses, HasTeams;

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
            ...$this->ownershipVisibilityCasts(),
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
