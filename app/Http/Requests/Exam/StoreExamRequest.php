<?php
namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'semester_id' => ['required','integer','exists:semesters,id'],
            'name_km' => ['required','string','max:255'],
            'name_en' => ['nullable','string','max:255'],
            'exam_type' => ['required','string',Rule::in(['MIDTERM','FINAL','QUIZ','OTHER'])],
            'start_date' => ['required','date_format:Y-m-d'],
            'end_date' => ['required','date_format:Y-m-d','after_or_equal:start_date'],
            'status' => ['sometimes','string',Rule::in(['DRAFT','SCHEDULED','ONGOING','COMPLETED','CANCELLED'])],
        ];
    }
}
