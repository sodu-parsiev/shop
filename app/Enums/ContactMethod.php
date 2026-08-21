<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContactMethod: string implements HasLabel
{
    case Phone = 'phone';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::Phone => __('Phone'),
            self::Email => __('Email'),
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}
