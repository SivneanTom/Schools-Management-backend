<?php

namespace App\Http\Requests\SchoolClass;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolClassRequest extends FormRequest
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

            'academicYearId' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'homeroomTeacherId' => [
                'nullable',
                'integer',
                'exists:teachers,id',
            ],

            'nameKm' => [
                'required',
                'string',
                'max:100',
            ],

            'nameEn' => [
                'required',
                'string',
                'max:100',
            ],

            'capacity' => [
                'required',
                'integer',
                'min:1',
                'max:1000',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'ACTIVE',
                    'INACTIVE',
                ]),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $existsKm = \App\Models\SchoolClass::query()
                    ->where('academic_year_id', $this->integer('academicYearId'))
                    ->where('grade_id', $this->integer('gradeId'))
                    ->where('name_km', $this->input('nameKm'))
                    ->exists();

                if ($existsKm) {
                    $validator->errors()->add(
                        'nameKm',
                        'This Khmer class name already exists for the selected grade and academic year.'
                    );
                }

                $existsEn = \App\Models\SchoolClass::query()
                    ->where('academic_year_id', $this->integer('academicYearId'))
                    ->where('grade_id', $this->integer('gradeId'))
                    ->where('name_en', $this->input('nameEn'))
                    ->exists();

                if ($existsEn) {
                    $validator->errors()->add(
                        'nameEn',
                        'This English class name already exists for the selected grade and academic year.'
                    );
                }
            },
        ];
    }
}
