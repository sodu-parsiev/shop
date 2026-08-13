<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_application');
    }

    public function view(User $user, Application $application): bool
    {
        return $user->can('view_application');
    }

    public function create(User $user): bool
    {
        return $user->can('create_application');
    }

    public function update(User $user, Application $application): bool
    {
        return $user->can('update_application');
    }

    public function delete(User $user, Application $application): bool
    {
        return $user->can('delete_application');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_application');
    }
}
