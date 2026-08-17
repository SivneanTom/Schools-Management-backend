<?php

namespace App\Http\Requests\Grade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $grade = $this->route('grade');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',

                Rule::unique('grades', 'code')
                    ->ignore($grade?->id),
            ],

            'nameKm' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'nameEn' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'orderNo' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}