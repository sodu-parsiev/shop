<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_order');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can('view_order');
    }

    public function create(User $user): bool
    {
        return $user->can('create_order');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('update_order');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('delete_order');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_order');
    }
}
