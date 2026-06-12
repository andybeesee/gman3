<?php

namespace App\Models\Concerns;

use App\Models\DateChange;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait HasSchedulableDates
{
    /**
     * @return list<string>
     */
    public function schedulableDateFields(): array
    {
        return ['start_date', 'due_date'];
    }

    public static function bootHasSchedulableDates(): void
    {
        static::created(function (self $model): void {
            $model->recordInitialSchedulableDates();
        });

        static::updating(function (self $model): void {
            $model->recordSchedulableDateChanges();
        });
    }

    /**
     * @return MorphMany<DateChange, $this>
     */
    public function dateChanges(): MorphMany
    {
        return $this->morphMany(DateChange::class, 'dateable');
    }

    protected function recordInitialSchedulableDates(): void
    {
        foreach ($this->schedulableDateFields() as $field) {
            if ($this->{$field} === null) {
                continue;
            }

            $this->dateChanges()->create([
                'field' => $field,
                'old_value' => null,
                'new_value' => $this->{$field},
                'user_id' => Auth::id(),
            ]);
        }
    }

    protected function recordSchedulableDateChanges(): void
    {
        foreach ($this->schedulableDateFields() as $field) {
            if (! $this->isDirty($field)) {
                continue;
            }

            $this->dateChanges()->create([
                'field' => $field,
                'old_value' => $this->getOriginal($field),
                'new_value' => $this->{$field},
                'user_id' => Auth::id(),
            ]);
        }
    }
}
