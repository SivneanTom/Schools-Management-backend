<?php

namespace App\Services\Student;

use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


// This contains the business logic

class StudentService
{
    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {

            $studentRole = Role::where('code', 'STUDENT')
                ->where('is_active', true)
                ->firstOrFail();

            $user = User::create([
                'role_id' => $studentRole->id,
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'preferred_language' => $data['preferredLanguage'] ?? 'KM',
                'status' => 'ACTIVE',
            ]);

            $student = Student::create([
                'user_id' => $user->id,

                'student_code' => $data['studentCode'],

                'first_name_km' => $data['firstNameKm'],
                'last_name_km' => $data['lastNameKm'],

                'first_name_en' => $data['firstNameEn'] ?? null,
                'last_name_en' => $data['lastNameEn'] ?? null,

                'gender' => $data['gender'],

                'date_of_birth' => $data['dateOfBirth'] ?? null,

                'phone' => $data['phone'] ?? null,

                'address_km' => $data['addressKm'] ?? null,
                'address_en' => $data['addressEn'] ?? null,

                'admission_date' => $data['admissionDate'] ?? null,

                'status' => $data['status'] ?? 'ACTIVE',
            ]);

            return $student->load('user.role');
        });
    }

    // Update Student

    public function update(
        Student $student,
        array $data
    ): Student {
        return DB::transaction(function () use ($student, $data) {

            $user = $student->user;

            if (array_key_exists('username', $data)) {
                $user->username = $data['username'];
            }

            if (array_key_exists('email', $data)) {
                $user->email = $data['email'];
            }

            if (array_key_exists('preferredLanguage', $data)) {
                $user->preferred_language = $data['preferredLanguage'];
            }

            $user->save();

            if (array_key_exists('studentCode', $data)) {
                $student->student_code = $data['studentCode'];
            }

            if (array_key_exists('firstNameKm', $data)) {
                $student->first_name_km = $data['firstNameKm'];
            }

            if (array_key_exists('lastNameKm', $data)) {
                $student->last_name_km = $data['lastNameKm'];
            }

            if (array_key_exists('firstNameEn', $data)) {
                $student->first_name_en = $data['firstNameEn'];
            }

            if (array_key_exists('lastNameEn', $data)) {
                $student->last_name_en = $data['lastNameEn'];
            }

            if (array_key_exists('gender', $data)) {
                $student->gender = $data['gender'];
            }

            if (array_key_exists('dateOfBirth', $data)) {
                $student->date_of_birth = $data['dateOfBirth'];
            }

            if (array_key_exists('phone', $data)) {
                $student->phone = $data['phone'];
            }

            if (array_key_exists('addressKm', $data)) {
                $student->address_km = $data['addressKm'];
            }

            if (array_key_exists('addressEn', $data)) {
                $student->address_en = $data['addressEn'];
            }

            if (array_key_exists('admissionDate', $data)) {
                $student->admission_date = $data['admissionDate'];
            }

            $student->save();

            return $student->load('user.role');
        });
    }

    //  Update Student Status

    public function updateStatus(
        Student $student,
        string $status
    ): Student {
        return DB::transaction(function () use ($student, $status) {

            $student->update([
                'status' => $status,
            ]);

            $student->user->update([
                'status' => $status,
            ]);

            return $student->load('user.role');
        });
    }
}
