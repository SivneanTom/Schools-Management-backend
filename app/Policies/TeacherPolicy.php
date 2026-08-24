<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
            'TEACHER',
            'ACCOUNTANT',
            'LIBRARIAN',
            'STUDENT',
            'PARENT',
        ], true);
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
            'TEACHER',
            'ACCOUNTANT',
            'LIBRARIAN',
            'STUDENT',
            'PARENT',
        ], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }

    public function update(User $user, Teacher $teacher): bool
    {
        if (in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true)) {
            return true;
        }

        if ($user->role->code === 'TEACHER') {
            return (int) $teacher->user_id === (int) $user->id;
        }

        return false;
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }
}