<?php

namespace App\Http\Requests\ExamResult;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_subject_id' => [
                'required',
                'integer',
                'exists:exam_subjects,id',
            ],

            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],

            'score' => [
                'required',
                'numeric',
                'min:0',
            ],

            'grade' => [
                'nullable',
                'string',
                'max:20',
            ],

            'remarks_km' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'remarks_en' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}
