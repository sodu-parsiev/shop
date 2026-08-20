<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AvailabilityStatus: string implements HasLabel
{
    case InStock = 'in_stock';
    case MadeToOrder = 'made_to_order';

    public function label(): string
    {
        return match ($this) {
            self::InStock => __('In stock'),
            self::MadeToOrder => __('Made to order'),
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}
