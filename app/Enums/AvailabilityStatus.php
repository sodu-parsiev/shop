<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case InStock = 'in_stock';
    case MadeToOrder = 'made_to_order';

    public function label(): string
    {
        return match ($this) {
            self::InStock => 'In stock',
            self::MadeToOrder => 'Made to order',
        };
    }
}
