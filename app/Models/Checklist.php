<?php

namespace App\Models;

use App\Enums\Visibility;
use App\Models\Concerns\HasOwner;
use App\Models\Concerns\HasRecord;
use App\Models\Concerns\HasSchedulableDates;
use App\Models\Concerns\HasTeams;
use App\Models\Concerns\HasVisibility;
use App\Models\Scopes\VisibleToAuthenticatedUserScope;
use Database\Factories\ChecklistFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'visibility', 'created_by_user_id'])]
class Checklist extends Model
{
    /** @use HasFactory<ChecklistFactory> */
    use HasFactory, HasOwner, HasRecord, HasSchedulableDates, HasTeams, HasVisibility {
        HasOwner::applyOwnershipVisibilityAccess insteadof HasVisibility;
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

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)
            ->orderByRaw('checklist_position IS NULL')
            ->orderBy('checklist_position')
            ->orderBy('id');
    }

    public function syncTaskDateRollup(): static
    {
        $rollup = $this->tasks()
            ->withoutGlobalScopes()
            ->reorder()
            ->selectRaw('MIN(start_date) as start_date, MAX(due_date) as due_date')
            ->first();

        $this->forceFill([
            'start_date' => $rollup?->start_date,
            'due_date' => $rollup?->due_date,
        ])->save();

        return $this;
    }
}
