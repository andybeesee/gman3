<?php

namespace App\Enums;

enum TeamRole: string
{
    case Leader = 'leader';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Leader => __('Team leader'),
            self::Member => __('Team member'),
        };
    }
}
