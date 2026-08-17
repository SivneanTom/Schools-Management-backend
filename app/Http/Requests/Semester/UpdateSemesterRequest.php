<?php

namespace App\Http\Requests\Semester;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $semester = $this->route('semester');

        $academicYearId =
            $this->input(
                'academicYearId',
                $semester?->academic_year_id
            );

        return [
            'academicYearId' => [
                'sometimes',
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',

                Rule::unique('semesters', 'name')
                    ->where(
                        fn ($query) =>
                            $query->where(
                                'academic_year_id',
                                $academicYearId
                            )
                    )
                    ->ignore($semester?->id),
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