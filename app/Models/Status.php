<?php

namespace App\Models;

use Database\Factories\StatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'icon', 'light_theme_color', 'dark_theme_color', 'sort_order', 'is_closed'])]
class Status extends Model
{
    /** @use HasFactory<StatusFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_closed' => 'boolean',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    /**
     * @return HasMany<StatusChange, $this>
     */
    public function statusChanges(): HasMany
    {
        return $this->hasMany(StatusChange::class);
    }

    public function fontAwesomeIcon(): string
    {
        return match ($this->icon) {
            'play-circle' => 'fa-circle-play',
            'check-circle' => 'fa-circle-check',
            'x-circle' => 'fa-circle-xmark',
            'exclamation-triangle' => 'fa-triangle-exclamation',
            default => 'fa-'.$this->icon,
        };
    }
}
