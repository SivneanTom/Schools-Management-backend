<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
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

    public function view(User $user, Student $student): bool
    {
        $role = $user->role->code;

        if (in_array($role, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true)) {
            return true;
        }

        if ($role === 'STUDENT') {
            return (int) $student->user_id === (int) $user->id;
        }

        if ($role === 'PARENT') {
            return $student->parents()
                ->where('parents.user_id', $user->id)
                ->exists();
        }

        if ($role === 'TEACHER') {
            return $student->enrollments()
                ->whereHas('schoolClass.teacherAssignments', function ($query) use ($user) {
                    $query->whereHas('teacher', function ($teacherQuery) use ($user) {
                        $teacherQuery->where('user_id', $user->id);
                    });
                })
                ->exists();
        }

        if (in_array($role, [
            'ACCOUNTANT',
            'LIBRARIAN',
        ], true)) {
            return true;
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

    public function update(User $user, Student $student): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }

    public function delete(User $user, Student $student): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }
}