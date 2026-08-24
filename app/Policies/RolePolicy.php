<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array(
            $user->role->code,
            ['SUPER_ADMIN', 'ADMIN'],
            true
        );
    }

    public function create(User $user): bool
    {
        return $user->role->code === 'SUPER_ADMIN';
    }

    public function update(User $user): bool
    {
        return $user->role->code === 'SUPER_ADMIN';
    }

    public function delete(User $user): bool
    {
        return $user->role->code === 'SUPER_ADMIN';
    }
}