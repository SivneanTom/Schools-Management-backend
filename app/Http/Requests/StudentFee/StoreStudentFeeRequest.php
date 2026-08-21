<?php

namespace App\Http\Requests\StudentFee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'fee_type_id' => ['required', 'integer', 'exists:fee_types,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'due_date' => ['nullable', 'date'],
            'status' => [
                'sometimes',
                Rule::in(['UNPAID', 'PARTIALLY_PAID', 'PAID', 'WAIVED', 'CANCELLED']),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => strtoupper(trim((string) $this->status)),
            ]);
        }
    }
}
