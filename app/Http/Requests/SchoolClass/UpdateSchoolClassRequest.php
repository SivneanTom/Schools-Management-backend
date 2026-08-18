<?php

namespace App\Http\Requests\SchoolClass;

use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gradeId' => [
                'sometimes',
                'required',
                'integer',
                'exists:grades,id',
            ],

            'academicYearId' => [
                'sometimes',
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'homeroomTeacherId' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:teachers,id',
            ],

            'nameKm' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'nameEn' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'capacity' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:1000',
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

                /** @var SchoolClass|null $schoolClass */
                $schoolClass = $this->route('schoolClass');

                if (! $schoolClass) {
                    return;
                }

                $academicYearId = $this->has('academicYearId')
                    ? $this->integer('academicYearId')
                    : $schoolClass->academic_year_id;

                $gradeId = $this->has('gradeId')
                    ? $this->integer('gradeId')
                    : $schoolClass->grade_id;

                $nameKm = $this->has('nameKm')
                    ? $this->input('nameKm')
                    : $schoolClass->name_km;

                $nameEn = $this->has('nameEn')
                    ? $this->input('nameEn')
                    : $schoolClass->name_en;

                $existsKm = SchoolClass::query()
                    ->whereKeyNot($schoolClass->id)
                    ->where('academic_year_id', $academicYearId)
                    ->where('grade_id', $gradeId)
                    ->where('name_km', $nameKm)
                    ->exists();

                if ($existsKm) {
                    $validator->errors()->add(
                        'nameKm',
                        'This Khmer class name already exists for the selected grade and academic year.'
                    );
                }

                $existsEn = SchoolClass::query()
                    ->whereKeyNot($schoolClass->id)
                    ->where('academic_year_id', $academicYearId)
                    ->where('grade_id', $gradeId)
                    ->where('name_en', $nameEn)
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
