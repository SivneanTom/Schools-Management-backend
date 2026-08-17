<?php

namespace App\Http\Requests\AcademicYear;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $academicYear = $this->route('academicYear');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('academic_years', 'name')
                    ->ignore($academicYear?->id),
            ],

            'startDate' => [
                'sometimes',
                'required',
                'date',
            ],

            'endDate' => [
                'sometimes',
                'required',
                'date',
            ],
        ];
    }
}