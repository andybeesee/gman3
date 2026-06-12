<?php

namespace App\Models\Concerns;

use App\Models\Status;
use App\Models\StatusChange;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;

trait HasStatuses
{
    public static function bootHasStatuses(): void
    {
        static::created(function (self $model): void {
            if ($model->currentStatusChange()->exists()) {
                return;
            }

            $defaultStatus = Status::query()->orderBy('sort_order')->first();

            if ($defaultStatus !== null) {
                $model->setStatus($defaultStatus);
            }
        });
    }

    /**
     * @return MorphMany<StatusChange, $this>
     */
    public function statusChanges(): MorphMany
    {
        return $this->morphMany(StatusChange::class, 'statusable');
    }

    /**
     * @return MorphOne<StatusChange, $this>
     */
    public function currentStatusChange(): MorphOne
    {
        return $this->morphOne(StatusChange::class, 'statusable')->latestOfMany();
    }

    public function setStatus(Status $status, ?User $user = null): StatusChange
    {
        $currentStatusChange = $this->currentStatusChange;
        $previousStatusId = $currentStatusChange?->status_id;

        if ($previousStatusId === $status->id) {
            return $currentStatusChange;
        }

        return $this->statusChanges()->create([
            'status_id' => $status->id,
            'from_status_id' => $previousStatusId,
            'user_id' => $user?->id ?? Auth::id(),
        ]);
    }

    public function scopeWhereStatus($query, Status|string $status)
    {
        $statusId = $status instanceof Status
            ? $status->id
            : Status::query()->where('slug', $status)->value('id');

        return $query->whereHas('currentStatusChange', function ($query) use ($statusId): void {
            $query->where('status_id', $statusId);
        });
    }
}
