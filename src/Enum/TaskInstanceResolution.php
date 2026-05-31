<?php

declare(strict_types=1);

namespace App\Enum;

enum TaskInstanceResolution: string
{
    case SHOT = 'SHOT';
    case GREEN_BASE_RESPAWN = 'GREEN_BASE_RESPAWN';
}
