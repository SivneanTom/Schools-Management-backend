<?php

namespace App\Http\Requests\Scholarship;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScholarshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_km' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_km' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::in(['PERCENTAGE', 'FIXED'])],
            'discount_value' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999.99',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (
                        strtoupper((string) $this->input('discount_type')) === 'PERCENTAGE'
                        && (float) $value > 100
                    ) {
                        $fail('The discount value may not be greater than 100 for percentage scholarships.');
                    }
                },
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
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
