<?php

namespace App\Policies;

use App\Models\Catalog\Color;
use App\Models\User;

class ColorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_color');
    }

    public function view(User $user, Color $color): bool
    {
        return $user->can('view_color');
    }

    public function create(User $user): bool
    {
        return $user->can('create_color');
    }

    public function update(User $user, Color $color): bool
    {
        return $user->can('update_color');
    }

    public function delete(User $user, Color $color): bool
    {
        return $user->can('delete_color');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_color');
    }

    public function reorder(User $user): bool
    {
        return $user->can('update_color');
    }
}
