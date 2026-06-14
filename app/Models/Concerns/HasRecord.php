<?php

namespace App\Models\Concerns;

use App\Enums\Visibility;
use App\Models\Record;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasRecord
{
    public static function bootHasRecord(): void
    {
        static::saved(function (Model $model): void {
            $model->syncRecord();
        });
    }

    /**
     * @return MorphOne<Record, $this>
     */
    public function record(): MorphOne
    {
        return $this->morphOne(Record::class, 'recordable');
    }

    public function syncRecord(): Record
    {
        return $this->record()->updateOrCreate([], $this->recordAttributes());
    }

    public function ensureRecord(): Record
    {
        return $this->record ?? $this->syncRecord();
    }

    /**
     * @return array<string, mixed>
     */
    protected function recordAttributes(): array
    {
        return [
            'title' => $this->recordTitle(),
            'description' => $this->recordDescription(),
            'start_date' => $this->recordStartDate(),
            'due_date' => $this->recordDueDate(),
            'visibility' => $this->recordVisibility(),
            'created_by_user_id' => $this->created_by_user_id,
            'owner_type' => $this->recordOwnerType(),
            'owner_id' => $this->recordOwnerId(),
        ];
    }

    protected function recordTitle(): string
    {
        return (string) ($this->title ?? $this->name);
    }

    protected function recordDescription(): ?string
    {
        return $this->description ?? null;
    }

    protected function recordStartDate(): mixed
    {
        return $this->start_date ?? null;
    }

    protected function recordDueDate(): mixed
    {
        return $this->due_date ?? null;
    }

    protected function recordVisibility(): Visibility|string
    {
        return $this->visibility ?? Visibility::Private;
    }

    protected function recordOwnerType(): ?string
    {
        return $this->owner_type;
    }

    protected function recordOwnerId(): ?int
    {
        return $this->owner_id;
    }
}
