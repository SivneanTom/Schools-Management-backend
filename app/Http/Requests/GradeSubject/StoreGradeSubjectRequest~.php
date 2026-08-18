<?php

namespace App\Http\Requests\GradeSubject;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gradeId' => [
                'required',
                'integer',
                'exists:grades,id',
            ],

            'subjectId' => [
                'required',
                'integer',
                'exists:subjects,id',

                Rule::unique('grade_subjects', 'subject_id')
                    ->where(
                        fn ($query) => $query->where(
                            'grade_id',
                            $this->integer('gradeId')
                        )
                    ),
            ],

            'creditHours' => [
                'required',
                'integer',
                'min:1',
                'max:60',
            ],

            'isRequired' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'subjectId.unique' =>
                'This subject is already assigned to the selected grade.',
        ];
    }
}
