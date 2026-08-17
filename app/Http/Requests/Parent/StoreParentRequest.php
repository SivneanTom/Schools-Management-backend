<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // These files are mainly for validation.
    // Its job is to check new Parent data before creating anything.
    
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

            'parentCode' => [
                'required',
                'string',
                'max:50',
                'unique:parents,parent_code',
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
                'nullable',
                Rule::in([
                    'MALE',
                    'FEMALE',
                ]),
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