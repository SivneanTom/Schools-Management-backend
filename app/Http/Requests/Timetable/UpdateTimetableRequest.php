<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTimetableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_assignment_id' => ['sometimes', 'required', 'integer', 'exists:teacher_assignments,id'],
            'room_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('rooms', 'id')->where('status', 'ACTIVE'),
            ],
            'day_of_week' => [
                'sometimes',
                'required',
                'string',
                Rule::in([
                    'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY',
                    'FRIDAY', 'SATURDAY', 'SUNDAY',
                ]),
            ],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i'],
        ];
    }
}
