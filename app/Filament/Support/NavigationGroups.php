<?php

namespace App\Filament\Support;

final class NavigationGroups
{
    public static function catalog(): string
    {
        return __('Catalog');
    }

    public static function content(): string
    {
        return __('Content');
    }

    public static function sales(): string
    {
        return __('Sales');
    }

    public static function administration(): string
    {
        return __('Administration');
    }

    private function __construct() {}
}
