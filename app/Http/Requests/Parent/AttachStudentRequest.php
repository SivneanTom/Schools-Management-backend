<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    //  AttachStudentRequest
    // =
    // validate Parent ↔ Student relationship

    public function rules(): array
    {
        return [
            'relationship' => [
                'required',
                Rule::in([
                    'FATHER',
                    'MOTHER',
                    'GUARDIAN',
                    'OTHER',
                ]),
            ],

            'isPrimary' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}