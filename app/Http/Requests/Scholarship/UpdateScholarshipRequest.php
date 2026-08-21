<?php

namespace App\Http\Requests\Scholarship;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScholarshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_km' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_km' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'discount_type' => ['sometimes', 'required', Rule::in(['PERCENTAGE', 'FIXED'])],
            'discount_value' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
                'max:9999999999.99',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $scholarship = $this->route('scholarship');

                    $type = strtoupper((string) (
                        $this->input('discount_type')
                        ?? $scholarship?->discount_type
                    ));

                    if ($type === 'PERCENTAGE' && (float) $value > 100) {
                        $fail('The discount value may not be greater than 100 for percentage scholarships.');
                    }
                },
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => [
                'nullable',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === null) {
                        return;
                    }

                    $scholarship = $this->route('scholarship');
                    $startDate = $this->input('start_date')
                        ?? optional($scholarship?->start_date)->format('Y-m-d');

                    if ($startDate && $value < $startDate) {
                        $fail('The end date must be a date after or equal to start date.');
                    }
                },
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('discount_type')) {
            $this->merge([
                'discount_type' => strtoupper(trim((string) $this->discount_type)),
            ]);
        }
    }
}
