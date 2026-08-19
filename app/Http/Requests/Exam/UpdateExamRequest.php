<?php
namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'semester_id' => ['sometimes','required','integer','exists:semesters,id'],
            'name_km' => ['sometimes','required','string','max:255'],
            'name_en' => ['sometimes','nullable','string','max:255'],
            'exam_type' => ['sometimes','required','string',Rule::in(['MIDTERM','FINAL','QUIZ','OTHER'])],
            'start_date' => ['sometimes','required','date_format:Y-m-d'],
            'end_date' => ['sometimes','required','date_format:Y-m-d'],
            'status' => ['sometimes','required','string',Rule::in(['DRAFT','SCHEDULED','ONGOING','COMPLETED','CANCELLED'])],
        ];
    }
}
