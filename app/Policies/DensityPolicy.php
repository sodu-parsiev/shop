<?php

namespace App\Policies;

use App\Models\Catalog\Density;
use App\Models\User;

class DensityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_density');
    }

    public function view(User $user, Density $density): bool
    {
        return $user->can('view_density');
    }

    public function create(User $user): bool
    {
        return $user->can('create_density');
    }

    public function update(User $user, Density $density): bool
    {
        return $user->can('update_density');
    }

    public function delete(User $user, Density $density): bool
    {
        return $user->can('delete_density');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_density');
    }

    public function reorder(User $user): bool
    {
        return $user->can('update_density');
    }
}
