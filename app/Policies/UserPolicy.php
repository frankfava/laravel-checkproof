<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $auth)
    {
        // Admins and Managers can list all users
        return in_array($auth->role, [UserRole::Admin, UserRole::Manager]);
    }

    public function view(User $auth, User $resource)
    {
        // Admins and Managers can view anyone, and users can view themselves
        return $auth->is($resource) || in_array($auth->role, [UserRole::Admin, UserRole::Manager]);
    }

    public function create(User $auth)
    {
        // Admins and Managers can create any user
        return in_array($auth->role, [UserRole::Admin, UserRole::Manager]);
    }

    public function update(User $auth, User $resource)
    {
        // Admin can edit anyone
        if ($auth->role == UserRole::Admin) {
            return true;
        }
        // Managers can only edit Users
        if ($auth->role == UserRole::Manager && $resource->role == UserRole::User) {
            return true;
        }

        return false;
    }

    public function delete(User $auth, User $resource)
    {
        return $this->update($auth, $resource);
    }
}
