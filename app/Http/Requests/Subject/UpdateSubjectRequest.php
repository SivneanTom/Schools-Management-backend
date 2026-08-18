<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
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
        $subject = $this->route('subject');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')
                    ->ignore($subject?->id),
            ],

            'nameKm' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'nameEn' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'descriptionKm' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'descriptionEn' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'creditHours' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
}
