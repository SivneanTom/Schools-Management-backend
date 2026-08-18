<?php

namespace App\Http\Requests\TeacherAssignment;

use App\Models\GradeSubject;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\TeacherAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacherId' => ['sometimes', 'integer', 'exists:teachers,id'],
            'classId' => ['sometimes', 'integer', 'exists:classes,id'],
            'subjectId' => ['sometimes', 'integer', 'exists:subjects,id'],
            'semesterId' => ['sometimes', 'integer', 'exists:semesters,id'],
            'assignedAt' => ['sometimes', 'date'],
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
                /** @var TeacherAssignment|null $assignment */
                $assignment = $this->route('teacherAssignment');
                if (!$assignment instanceof TeacherAssignment) {
                    return;
                }

                $teacherId = (int) $this->input('teacherId', $assignment->teacher_id);
                $classId = (int) $this->input('classId', $assignment->class_id);
                $subjectId = (int) $this->input('subjectId', $assignment->subject_id);
                $semesterId = (int) $this->input('semesterId', $assignment->semester_id);

                $class = SchoolClass::find($classId);
                $semester = Semester::find($semesterId);

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
                        ->where('subject_id', $subjectId)
                        ->exists();

                    if (!$offered) {
                        $validator->errors()->add(
                            'subjectId',
                            'The selected subject is not assigned to the grade of the selected class.'
                        );
                    }
                }

                $duplicate = TeacherAssignment::query()
                    ->where('teacher_id', $teacherId)
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->where('semester_id', $semesterId)
                    ->whereKeyNot($assignment->id)
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
