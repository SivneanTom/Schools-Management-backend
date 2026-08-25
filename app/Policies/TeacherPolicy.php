<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    private function role(User $user): ?string
    {
        $user->loadMissing('role');
        return $user->role?->code;
    }

    public function viewAny(User $user): bool
    {
        return in_array($this->role($user), [
            'SUPER_ADMIN',
            'ADMIN',
            'TEACHER',
        ], true);
    }

    public function view(User $user, Teacher $teacher): bool
    {
        $role = $this->role($user);

        if (in_array($role, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true)) {
            return true;
        }

        return $role === 'TEACHER'
            && (int) $teacher->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($this->role($user), [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return in_array($this->role($user), [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return in_array($this->role($user), [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }

    public function viewOwnAcademicData(User $user): bool
    {
        return $this->role($user) === 'TEACHER';
    }
}
