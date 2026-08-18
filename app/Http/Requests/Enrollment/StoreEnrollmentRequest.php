<?php

namespace App\Http\Requests\Enrollment;

use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId' => [
                'required',
                'integer',
                'exists:students,id',
                Rule::unique('enrollments', 'student_id')
                    ->where(fn ($query) => $query->where(
                        'academic_year_id',
                        $this->input('academicYearId')
                    )),
            ],
            'classId' => ['required', 'integer', 'exists:classes,id'],
            'academicYearId' => ['required', 'integer', 'exists:academic_years,id'],
            'enrolledAt' => ['required', 'date'],
            'status' => [
                'sometimes',
                'string',
                Rule::in(['ACTIVE', 'COMPLETED', 'TRANSFERRED', 'WITHDRAWN']),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (!$this->filled('classId') || !$this->filled('academicYearId')) {
                    return;
                }

                $schoolClass = SchoolClass::find($this->integer('classId'));

                if ($schoolClass &&
                    (int) $schoolClass->academic_year_id !== (int) $this->integer('academicYearId')) {
                    $validator->errors()->add(
                        'classId',
                        'The selected class does not belong to the selected academic year.'
                    );
                }
            },
        ];
    }
}
