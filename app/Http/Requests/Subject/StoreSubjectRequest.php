<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->input('code'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:subjects,code',
            ],

            'nameKm' => [
                'required',
                'string',
                'max:150',
            ],

            'nameEn' => [
                'required',
                'string',
                'max:150',
            ],

            'descriptionKm' => [
                'nullable',
                'string',
            ],

            'descriptionEn' => [
                'nullable',
                'string',
            ],

            'creditHours' => [
                'required',
                'integer',
                'min:1',
            ],

            'isActive' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}
