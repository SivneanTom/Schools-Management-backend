<?php

namespace App\Http\Requests\Grade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:grades,code',
            ],

            'nameKm' => [
                'required',
                'string',
                'max:100',
            ],

            'nameEn' => [
                'required',
                'string',
                'max:100',
            ],

            'orderNo' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'ACTIVE',
                    'INACTIVE',
                ]),
            ],
        ];
    }
}