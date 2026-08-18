<?php

namespace App\Http\Requests\Enrollment;

use App\Models\Enrollment;
use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId' => ['sometimes', 'integer', 'exists:students,id'],
            'classId' => ['sometimes', 'integer', 'exists:classes,id'],
            'academicYearId' => ['sometimes', 'integer', 'exists:academic_years,id'],
            'enrolledAt' => ['sometimes', 'date'],
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
                /** @var Enrollment|null $enrollment */
                $enrollment = $this->route('enrollment');
                if (!$enrollment instanceof Enrollment) {
                    return;
                }

                $studentId = (int) ($this->input('studentId', $enrollment->student_id));
                $classId = (int) ($this->input('classId', $enrollment->class_id));
                $academicYearId = (int) ($this->input('academicYearId', $enrollment->academic_year_id));

                $schoolClass = SchoolClass::find($classId);
                if ($schoolClass && (int) $schoolClass->academic_year_id !== $academicYearId) {
                    $validator->errors()->add(
                        'classId',
                        'The selected class does not belong to the selected academic year.'
                    );
                }

                $duplicate = Enrollment::query()
                    ->where('student_id', $studentId)
                    ->where('academic_year_id', $academicYearId)
                    ->whereKeyNot($enrollment->id)
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add(
                        'studentId',
                        'This student is already enrolled for the selected academic year.'
                    );
                }
            },
        ];
    }
}

