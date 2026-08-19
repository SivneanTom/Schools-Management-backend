<?php

namespace App\Http\Requests\ExamSubject;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_id' => ['sometimes', 'required', 'integer', 'exists:exams,id'],
            'teacher_assignment_id' => ['sometimes', 'required', 'integer', 'exists:teacher_assignments,id'],
            'exam_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i'],
            'max_score' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'pass_score' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}
