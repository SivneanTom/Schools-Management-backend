<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    //  It checks data when editing Parent information.
    
    public function rules(): array
    {
        $parent = $this->route('parent');

        return [
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')
                    ->ignore($parent?->user_id),
            ],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($parent?->user_id),
            ],

            'preferredLanguage' => [
                'sometimes',
                Rule::in(['KM', 'EN']),
            ],

            'parentCode' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('parents', 'parent_code')
                    ->ignore($parent?->id),
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
                'nullable',
                Rule::in([
                    'MALE',
                    'FEMALE',
                ]),
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
        ];
    }
}