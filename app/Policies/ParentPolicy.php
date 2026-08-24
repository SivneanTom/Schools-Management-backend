<?php

namespace App\Policies;

use App\Models\ParentModel;
use App\Models\ParentProfile;
use App\Models\User;

class ParentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
            'TEACHER',
            'STUDENT',
            'PARENT',
        ], true);
    }

    public function view(
        User $user,
        ParentProfile $parent
    ): bool {
        $role = $user->role->code;

        if (in_array($role, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true)) {
            return true;
        }

        if ($role === 'PARENT') {
            return (int) $parent->user_id === (int) $user->id;
        }

        if ($role === 'STUDENT') {
            return $parent->students()
                ->where('students.user_id', $user->id)
                ->exists();
        }

        if ($role === 'TEACHER') {
            return $parent->students()
                ->whereHas('enrollments.schoolClass.teacherAssignments.teacher', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }

    public function update(
        User $user,
        ParentProfile $parent
    ): bool {
        if (in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true)) {
            return true;
        }

        if ($user->role->code === 'PARENT') {
            return (int) $parent->user_id === (int) $user->id;
        }

        return false;
    }

    public function delete(
        User $user,
        ParentProfile $parent
    ): bool {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }
}