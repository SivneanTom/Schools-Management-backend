<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')
                    ->ignore($teacher?->user_id),
            ],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($teacher?->user_id),
            ],

            'preferredLanguage' => [
                'sometimes',
                Rule::in(['KM', 'EN']),
            ],

            'teacherCode' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('teachers', 'teacher_code')
                    ->ignore($teacher?->id),
            ],

            'firstNameKm' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'lastNameKm' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'firstNameEn' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'lastNameEn' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'gender' => [
                'sometimes',
                Rule::in([
                    'MALE',
                    'FEMALE',
                ]),
            ],

            'dateOfBirth' => [
                'sometimes',
                'nullable',
                'date',
                'before:today',
            ],

            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],

            'addressKm' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'addressEn' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'hireDate' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'qualification' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'specialization' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}