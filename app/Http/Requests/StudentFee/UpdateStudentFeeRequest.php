<?php

namespace App\Http\Requests\StudentFee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['sometimes', 'required', 'integer', 'exists:students,id'],
            'fee_type_id' => ['sometimes', 'required', 'integer', 'exists:fee_types,id'],
            'academic_year_id' => ['sometimes', 'required', 'integer', 'exists:academic_years,id'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999999999.99'],
            'due_date' => ['nullable', 'date'],
            'status' => [
                'sometimes',
                'required',
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
