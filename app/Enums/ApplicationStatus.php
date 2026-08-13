<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }
}
