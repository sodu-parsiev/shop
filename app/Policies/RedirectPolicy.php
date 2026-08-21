<?php

namespace App\Policies;

use App\Models\Content\Redirect;
use App\Models\User;

class RedirectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_redirect');
    }

    public function view(User $user, Redirect $redirect): bool
    {
        return $user->can('view_redirect');
    }

    public function create(User $user): bool
    {
        return $user->can('create_redirect');
    }

    public function update(User $user, Redirect $redirect): bool
    {
        return $user->can('update_redirect');
    }

    public function delete(User $user, Redirect $redirect): bool
    {
        return $user->can('delete_redirect');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_redirect');
    }
}
