<?php

namespace App\Models\Pivots;

use App\Enums\TeamRole;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Teamable extends MorphPivot
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => TeamRole::class,
        ];
    }
}
