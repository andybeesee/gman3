<?php

namespace App\Enums;

enum ProjectOwnership: string
{
    case User = 'user';
    case Team = 'team';
}
