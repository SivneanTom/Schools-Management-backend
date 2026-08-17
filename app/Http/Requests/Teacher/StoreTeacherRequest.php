<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
            ],

            'preferredLanguage' => [
                'nullable',
                Rule::in(['KM', 'EN']),
            ],

            'teacherCode' => [
                'required',
                'string',
                'max:50',
                'unique:teachers,teacher_code',
            ],

            'firstNameKm' => [
                'required',
                'string',
                'max:255',
            ],

            'lastNameKm' => [
                'required',
                'string',
                'max:255',
            ],

            'firstNameEn' => [
                'nullable',
                'string',
                'max:255',
            ],

            'lastNameEn' => [
                'nullable',
                'string',
                'max:255',
            ],

            'gender' => [
                'required',
                Rule::in([
                    'MALE',
                    'FEMALE',
                ]),
            ],

            'dateOfBirth' => [
                'nullable',
                'date',
                'before:today',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'addressKm' => [
                'nullable',
                'string',
            ],

            'addressEn' => [
                'nullable',
                'string',
            ],

            'hireDate' => [
                'nullable',
                'date',
            ],

            'qualification' => [
                'nullable',
                'string',
                'max:255',
            ],

            'specialization' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'ACTIVE',
                    'INACTIVE',
                    'SUSPENDED',
                ]),
            ],
        ];
    }
}