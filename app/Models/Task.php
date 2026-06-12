<?php

namespace App\Models;

use App\Models\Concerns\HasAssignees;
use App\Models\Concerns\HasSchedulableDates;
use App\Models\Concerns\HasStatuses;
use App\Models\Concerns\HasTeams;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'start_date', 'due_date'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasAssignees, HasFactory, HasSchedulableDates, HasStatuses, HasTeams;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'due_date' => 'datetime',
        ];
    }

    public function getStatusAttribute(): ?Status
    {
        return $this->currentStatusChange?->status;
    }
}
