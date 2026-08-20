<?php

namespace App\Filament\Support;

use Illuminate\Support\Facades\Lang;

final class RoleNameTranslator
{
    public static function translate(string $englishName): string
    {
        $key = 'roles.'.$englishName;

        return Lang::has($key) ? __($key) : $englishName;
    }

    private function __construct() {}
}
