<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // This is used when creating a new Student

    public function rules(): array
    {
        return [

            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username',
            ],

            'email'=> [
                'required',
                'string',
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

            'studentCode' => [
                'required',
                'string',
                'max:50',
                'unique:students,student_code',
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

            'admissionDate' => [
                'nullable',
                'date',
            ],
            
            'status' => [
                'sometimes',
                Rule::in([
                    'ACTIVE',
                    'INACTIVE',
                    'GRADUATED',
                    'SUSPENDED',
                ]),
            ],

        ];
        
    }
}
