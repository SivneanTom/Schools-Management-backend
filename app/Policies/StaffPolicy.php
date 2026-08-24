<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;

class StaffPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
            'TEACHER',
            'ACCOUNTANT',
            'LIBRARIAN',
        ], true);
    }

    public function view(User $user, Staff $staff): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
            'TEACHER',
            'ACCOUNTANT',
            'LIBRARIAN',
        ], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }

    public function update(User $user, Staff $staff): bool
    {
        if (in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true)) {
            return true;
        }

        if (in_array($user->role->code, [
            'ACCOUNTANT',
            'LIBRARIAN',
        ], true)) {
            return (int) $staff->user_id === (int) $user->id;
        }

        return false;
    }

    public function delete(User $user, Staff $staff): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }
}