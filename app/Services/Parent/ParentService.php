<?php

namespace App\Services\Parent;

use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentService
{
    public function create(array $data): ParentProfile
    {
        return DB::transaction(function () use ($data) {
            $parentRole = Role::where('code', 'PARENT')
                ->where('is_active', true)
                ->firstOrFail();

            $user = User::create([
                'role_id' => $parentRole->id,
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make(
                    $data['password']
                ),
                'preferred_language' =>
                $data['preferredLanguage'] ?? 'KM',
                'status' =>
                $data['status'] ?? 'ACTIVE',
            ]);

            $parent = ParentProfile::create([
                'user_id' => $user->id,
                'parent_code' => $data['parentCode'],
                'first_name_km' => $data['firstNameKm'],
                'last_name_km' => $data['lastNameKm'],
                'first_name_en' =>
                $data['firstNameEn'] ?? null,
                'last_name_en' =>
                $data['lastNameEn'] ?? null,
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address_km' =>
                $data['addressKm'] ?? null,
                'address_en' =>
                $data['addressEn'] ?? null,
                'status' =>
                $data['status'] ?? 'ACTIVE',
            ]);

            return $parent->load('user.role');
        });
    }

    public function update(
        ParentProfile $parent,
        array $data
    ): ParentProfile {
        return DB::transaction(function () use (
            $parent,
            $data
        ) {
            $user = $parent->user;

            if (array_key_exists('username', $data)) {
                $user->username = $data['username'];
            }

            if (array_key_exists('email', $data)) {
                $user->email = $data['email'];
            }

            if (array_key_exists(
                'preferredLanguage',
                $data
            )) {
                $user->preferred_language =
                    $data['preferredLanguage'];
            }

            if (
                array_key_exists('password', $data)
                && !empty($data['password'])
            ) {
                $user->password = Hash::make(
                    $data['password']
                );
            }

            $user->save();

            if (array_key_exists('parentCode', $data)) {
                $parent->parent_code =
                    $data['parentCode'];
            }

            if (array_key_exists('firstNameKm', $data)) {
                $parent->first_name_km =
                    $data['firstNameKm'];
            }

            if (array_key_exists('lastNameKm', $data)) {
                $parent->last_name_km =
                    $data['lastNameKm'];
            }

            if (array_key_exists('firstNameEn', $data)) {
                $parent->first_name_en =
                    $data['firstNameEn'];
            }

            if (array_key_exists('lastNameEn', $data)) {
                $parent->last_name_en =
                    $data['lastNameEn'];
            }

            if (array_key_exists('gender', $data)) {
                $parent->gender = $data['gender'];
            }

            if (array_key_exists('phone', $data)) {
                $parent->phone = $data['phone'];
            }

            if (array_key_exists('addressKm', $data)) {
                $parent->address_km =
                    $data['addressKm'];
            }

            if (array_key_exists('addressEn', $data)) {
                $parent->address_en =
                    $data['addressEn'];
            }

            $parent->save();

            return $parent->load('user.role');
        });
    }

    public function updateStatus(
        ParentProfile $parent,
        string $status
    ): ParentProfile {
        return DB::transaction(function () use (
            $parent,
            $status
        ) {
            $parent->update([
                'status' => $status,
            ]);

            $parent->user->update([
                'status' =>
                $status === 'ACTIVE'
                    ? 'ACTIVE'
                    : 'INACTIVE',
            ]);

            return $parent->load('user.role');
        });
    }

    public function attachStudent(
        ParentProfile $parent,
        Student $student,
        array $data
    ): ParentProfile {
        return DB::transaction(function () use (
            $parent,
            $student,
            $data
        ) {
            $parent->students()->syncWithoutDetaching([
                $student->id => [
                    'relationship' =>
                    $data['relationship'],
                    'is_primary' =>
                    $data['isPrimary'] ?? false,
                ],
            ]);

            return $parent->load([
                'user.role',
                'students',
            ]);
        });
    }

    public function detachStudent(
        ParentProfile $parent,
        Student $student
    ): ParentProfile {
        return DB::transaction(function () use (
            $parent,
            $student
        ) {
            $parent->students()->detach(
                $student->id
            );

            return $parent->load([
                'user.role',
                'students',
            ]);
        });
    }

    public function applyAccessScope(
        Builder $query,
        User $user
    ): Builder {
        $role = $user->role->code;

        if (in_array($role, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true)) {
            return $query;
        }

        // Parent sees only own parent profile.
        if ($role === 'PARENT') {
            return $query->where(
                'user_id',
                $user->id
            );
        }

        // Student sees only own linked parent(s).
        if ($role === 'STUDENT') {
            return $query->whereHas(
                'students',
                function ($q) use ($user) {
                    $q->where(
                        'students.user_id',
                        $user->id
                    );
                }
            );
        }

        // Teacher sees parents of students in
        // the classes assigned to that teacher.
        if ($role === 'TEACHER') {
            return $query->whereHas(
                'students.enrollments.schoolClass'
                    . '.teacherAssignments.teacher',
                function ($q) use ($user) {
                    $q->where(
                        'user_id',
                        $user->id
                    );
                }
            );
        }

        return $query->whereRaw('1 = 0');
    }
}
