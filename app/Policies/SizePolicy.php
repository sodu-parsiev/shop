<?php

namespace App\Policies;

use App\Models\Catalog\Size;
use App\Models\User;

class SizePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_size');
    }

    public function view(User $user, Size $size): bool
    {
        return $user->can('view_size');
    }

    public function create(User $user): bool
    {
        return $user->can('create_size');
    }

    public function update(User $user, Size $size): bool
    {
        return $user->can('update_size');
    }

    public function delete(User $user, Size $size): bool
    {
        return $user->can('delete_size');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_size');
    }

    public function reorder(User $user): bool
    {
        return $user->can('update_size');
    }
}
