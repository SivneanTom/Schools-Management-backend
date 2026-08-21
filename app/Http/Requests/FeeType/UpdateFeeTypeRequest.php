<?php

namespace App\Http\Requests\FeeType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $feeTypeId = $this->route('feeType')?->id ?? $this->route('feeType');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('fee_types', 'code')->ignore($feeTypeId),
            ],
            'name_km' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_km' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'default_amount' => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999999999.99'],
            'frequency' => ['sometimes', 'required', Rule::in([
                'ONE_TIME',
                'MONTHLY',
                'QUARTERLY',
                'SEMESTER',
                'YEARLY',
            ])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->code)),
            ]);
        }

        if ($this->has('frequency')) {
            $this->merge([
                'frequency' => strtoupper(trim((string) $this->frequency)),
            ]);
        }
    }
}
