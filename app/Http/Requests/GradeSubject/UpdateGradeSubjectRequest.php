<?php

namespace App\Http\Requests\GradeSubject;

use App\Models\GradeSubject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateGradeSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gradeId' => [
                'sometimes',
                'required',
                'integer',
                'exists:grades,id',
            ],

            'subjectId' => [
                'sometimes',
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'creditHours' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:60',
            ],

            'isRequired' => [
                'sometimes',
                'required',
                'boolean',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $gradeSubject = $this->route('gradeSubject');

            if (! $gradeSubject) {
                return;
            }

            $targetGradeId = $this->has('gradeId')
                ? $this->integer('gradeId')
                : $gradeSubject->grade_id;

            $targetSubjectId = $this->has('subjectId')
                ? $this->integer('subjectId')
                : $gradeSubject->subject_id;

            $duplicateExists = GradeSubject::query()
                ->where('grade_id', $targetGradeId)
                ->where('subject_id', $targetSubjectId)
                ->whereKeyNot($gradeSubject->id)
                ->exists();

            if ($duplicateExists) {
                $validator->errors()->add(
                    'subjectId',
                    'This subject is already assigned to the selected grade.'
                );
            }
        });
    }
}
