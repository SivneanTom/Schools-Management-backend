<?php

namespace App\Http\Requests\StudentScholarship;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentScholarshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'scholarship_id' => ['required', 'integer', 'exists:scholarships,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'awarded_at' => ['nullable', 'date'],
            'status' => [
                'sometimes',
                Rule::in(['ACTIVE', 'EXPIRED', 'SUSPENDED', 'CANCELLED']),
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
