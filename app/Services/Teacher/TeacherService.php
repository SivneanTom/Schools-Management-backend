<?php

namespace App\Services\Teacher;

use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherService
{
    public function create(array $data): Teacher
    {
        return DB::transaction(function () use ($data) {

            $teacherRole = Role::where('code', 'TEACHER')
                ->where('is_active', true)
                ->firstOrFail();

            $status = $data['status'] ?? 'ACTIVE';

            $user = User::create([
                'role_id' => $teacherRole->id,
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'preferred_language' =>
                    $data['preferredLanguage'] ?? 'KM',
                'status' => $status,
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'teacher_code' => $data['teacherCode'],
                'first_name_km' => $data['firstNameKm'],
                'last_name_km' => $data['lastNameKm'],
                'first_name_en' =>
                    $data['firstNameEn'] ?? null,
                'last_name_en' =>
                    $data['lastNameEn'] ?? null,
                'gender' => $data['gender'],
                'date_of_birth' =>
                    $data['dateOfBirth'] ?? null,
                'phone' =>
                    $data['phone'] ?? null,
                'address_km' =>
                    $data['addressKm'] ?? null,
                'address_en' =>
                    $data['addressEn'] ?? null,
                'hire_date' =>
                    $data['hireDate'] ?? null,
                'qualification' =>
                    $data['qualification'] ?? null,
                'specialization' =>
                    $data['specialization'] ?? null,
                'status' => $status,
            ]);

            return $teacher->load('user.role');
        });
    }

    public function update(
        Teacher $teacher,
        array $data
    ): Teacher {
        return DB::transaction(function () use ($teacher, $data) {

            $user = $teacher->user;

            if (array_key_exists('username', $data)) {
                $user->username = $data['username'];
            }

            if (array_key_exists('email', $data)) {
                $user->email = $data['email'];
            }

            if (array_key_exists('preferredLanguage', $data)) {
                $user->preferred_language =
                    $data['preferredLanguage'];
            }

            $user->save();

            if (array_key_exists('teacherCode', $data)) {
                $teacher->teacher_code =
                    $data['teacherCode'];
            }

            if (array_key_exists('firstNameKm', $data)) {
                $teacher->first_name_km =
                    $data['firstNameKm'];
            }

            if (array_key_exists('lastNameKm', $data)) {
                $teacher->last_name_km =
                    $data['lastNameKm'];
            }

            if (array_key_exists('firstNameEn', $data)) {
                $teacher->first_name_en =
                    $data['firstNameEn'];
            }

            if (array_key_exists('lastNameEn', $data)) {
                $teacher->last_name_en =
                    $data['lastNameEn'];
            }

            if (array_key_exists('gender', $data)) {
                $teacher->gender = $data['gender'];
            }

            if (array_key_exists('dateOfBirth', $data)) {
                $teacher->date_of_birth =
                    $data['dateOfBirth'];
            }

            if (array_key_exists('phone', $data)) {
                $teacher->phone = $data['phone'];
            }

            if (array_key_exists('addressKm', $data)) {
                $teacher->address_km =
                    $data['addressKm'];
            }

            if (array_key_exists('addressEn', $data)) {
                $teacher->address_en =
                    $data['addressEn'];
            }

            if (array_key_exists('hireDate', $data)) {
                $teacher->hire_date =
                    $data['hireDate'];
            }

            if (array_key_exists('qualification', $data)) {
                $teacher->qualification =
                    $data['qualification'];
            }

            if (array_key_exists('specialization', $data)) {
                $teacher->specialization =
                    $data['specialization'];
            }

            $teacher->save();

            return $teacher->load('user');
        });
    }

    public function updateStatus(
        Teacher $teacher,
        string $status
    ): Teacher {
        return DB::transaction(function () use ($teacher, $status) {

            $teacher->update([
                'status' => $status,
            ]);

            $teacher->user->update([
                'status' => $status,
            ]);

            return $teacher->load('user');
        });
    }
}