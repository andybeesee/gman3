<?php

namespace App\Models;

use App\Models\Concerns\HasAssignees;
use App\Models\Concerns\HasOwner;
use App\Models\Concerns\HasSchedulableDates;
use App\Models\Concerns\HasStatuses;
use App\Models\Concerns\HasTeams;
use App\Models\Scopes\VisibleToAuthenticatedUserScope;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[Fillable(['title', 'description', 'start_date', 'due_date', 'completed_at', 'completed_by_user_id'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasAssignees, HasFactory, HasOwner, HasSchedulableDates, HasStatuses, HasTeams {
        setStatus as protected setStatusFromTrait;
    }

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
