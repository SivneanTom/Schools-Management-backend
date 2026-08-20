<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],

            'username' => [
                'required',
                'string',
                'max:100',
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

            'preferred_language' => [
                'nullable',
                Rule::in(['KM', 'EN']),
            ],

            'status' => [
                'nullable',
                Rule::in(['ACTIVE', 'INACTIVE']),
            ],
        ];
    }
}