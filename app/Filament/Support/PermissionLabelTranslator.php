<?php

namespace App\Filament\Support;

final class PermissionLabelTranslator
{
    public static function translate(string $permissionName): string
    {
        $special = __('permissions.special');

        if (isset($special[$permissionName])) {
            return $special[$permissionName];
        }

        $abilities = __('permissions.abilities');
        $resources = __('permissions.resources');

        foreach ($abilities as $ability => $abilityLabel) {
            $prefix = $ability.'_';

            if (! str_starts_with($permissionName, $prefix)) {
                continue;
            }

            $resourceSlug = substr($permissionName, strlen($prefix));

            if (isset($resources[$resourceSlug])) {
                return "{$abilityLabel}: {$resources[$resourceSlug]}";
            }
        }

        return $permissionName;
    }

    private function __construct() {}
}
