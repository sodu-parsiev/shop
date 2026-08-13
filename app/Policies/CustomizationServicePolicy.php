<?php

namespace App\Policies;

use App\Models\Catalog\CustomizationService;
use App\Models\User;

class CustomizationServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_customization_service');
    }

    public function view(User $user, CustomizationService $customizationService): bool
    {
        return $user->can('view_customization_service');
    }

    public function create(User $user): bool
    {
        return $user->can('create_customization_service');
    }

    public function update(User $user, CustomizationService $customizationService): bool
    {
        return $user->can('update_customization_service');
    }

    public function delete(User $user, CustomizationService $customizationService): bool
    {
        return $user->can('delete_customization_service');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_customization_service');
    }

    public function reorder(User $user): bool
    {
        return $user->can('update_customization_service');
    }
}
