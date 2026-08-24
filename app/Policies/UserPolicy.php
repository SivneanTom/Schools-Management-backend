<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array(
            $user->role->code,
            ['SUPER_ADMIN', 'ADMIN'],
            true
        );
    }

    public function view(
        User $user,
        User $target
    ): bool {
        return in_array(
            $user->role->code,
            ['SUPER_ADMIN', 'ADMIN'],
            true
        );
    }

    public function create(User $user): bool
    {
        return in_array(
            $user->role->code,
            ['SUPER_ADMIN', 'ADMIN'],
            true
        );
    }

    public function update(
        User $user,
        User $target
    ): bool {
        return in_array(
            $user->role->code,
            ['SUPER_ADMIN', 'ADMIN'],
            true
        );
    }

    public function delete(
        User $user,
        User $target
    ): bool {
        return $user->role->code === 'SUPER_ADMIN';
    }
}