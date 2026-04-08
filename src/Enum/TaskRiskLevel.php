<?php

declare(strict_types=1);

namespace App\Enum;

enum TaskRiskLevel: string
{
    case GREEN = 'GREEN';
    case YELLOW = 'YELLOW';
    case RED = 'RED';
}
