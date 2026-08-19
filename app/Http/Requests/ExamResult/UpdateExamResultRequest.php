<?php

namespace App\Http\Requests\ExamResult;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_subject_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:exam_subjects,id',
            ],

            'student_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:students,id',
            ],

            'score' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],

            'grade' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
            ],

            'remarks_km' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],

            'remarks_en' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],

            'published_at' => [
                'sometimes',
                'nullable',
                'date',
            ],
        ];
    }
}
