<?php

namespace App\Http\Requests\TeacherAssignment;

use App\Models\GradeSubject;
use App\Models\SchoolClass;
use App\Models\Semester;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacherId' => ['required', 'integer', 'exists:teachers,id'],
            'classId' => ['required', 'integer', 'exists:classes,id'],
            'subjectId' => ['required', 'integer', 'exists:subjects,id'],
            'semesterId' => ['required', 'integer', 'exists:semesters,id'],
            'assignedAt' => ['required', 'date'],
            'status' => [
                'sometimes',
                'string',
                Rule::in(['ACTIVE', 'INACTIVE']),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (!$this->filled('classId') || !$this->filled('subjectId') || !$this->filled('semesterId')) {
                    return;
                }

                $class = SchoolClass::find($this->integer('classId'));
                $semester = Semester::find($this->integer('semesterId'));

                if ($class && $semester &&
                    (int) $class->academic_year_id !== (int) $semester->academic_year_id) {
                    $validator->errors()->add(
                        'semesterId',
                        'The selected semester does not belong to the same academic year as the selected class.'
                    );
                }

                if ($class) {
                    $offered = GradeSubject::query()
                        ->where('grade_id', $class->grade_id)
                        ->where('subject_id', $this->integer('subjectId'))
                        ->exists();

                    if (!$offered) {
                        $validator->errors()->add(
                            'subjectId',
                            'The selected subject is not assigned to the grade of the selected class.'
                        );
                    }
                }

                $duplicate = \App\Models\TeacherAssignment::query()
                    ->where('teacher_id', $this->integer('teacherId'))
                    ->where('class_id', $this->integer('classId'))
                    ->where('subject_id', $this->integer('subjectId'))
                    ->where('semester_id', $this->integer('semesterId'))
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add(
                        'teacherId',
                        'This teacher assignment already exists.'
                    );
                }
            },
        ];
    }
}
