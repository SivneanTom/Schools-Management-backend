<?php

namespace App\Services\SchoolClass;

use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;

class SchoolClassService
{
    public function create(array $data): SchoolClass
    {
        return DB::transaction(function () use ($data) {

            $schoolClass = SchoolClass::create([
                'grade_id' => $data['gradeId'],
                'academic_year_id' => $data['academicYearId'],
                'homeroom_teacher_id' => $data['homeroomTeacherId'] ?? null,
                'name_km' => trim($data['nameKm']),
                'name_en' => trim($data['nameEn']),
                'capacity' => $data['capacity'],
                'status' => $data['status'] ?? 'ACTIVE',
            ]);

            return $schoolClass->load([
                'grade',
                'academicYear',
                'homeroomTeacher',
            ]);
        });
    }

    public function update(
        SchoolClass $schoolClass,
        array $data
    ): SchoolClass {
        return DB::transaction(function () use (
            $schoolClass,
            $data
        ) {

            if (array_key_exists('gradeId', $data)) {
                $schoolClass->grade_id = $data['gradeId'];
            }

            if (array_key_exists('academicYearId', $data)) {
                $schoolClass->academic_year_id = $data['academicYearId'];
            }

            if (array_key_exists('homeroomTeacherId', $data)) {
                $schoolClass->homeroom_teacher_id =
                    $data['homeroomTeacherId'];
            }

            if (array_key_exists('nameKm', $data)) {
                $schoolClass->name_km = trim($data['nameKm']);
            }

            if (array_key_exists('nameEn', $data)) {
                $schoolClass->name_en = trim($data['nameEn']);
            }

            if (array_key_exists('capacity', $data)) {
                $schoolClass->capacity = $data['capacity'];
            }

            $schoolClass->save();

            return $schoolClass->load([
                'grade',
                'academicYear',
                'homeroomTeacher',
            ]);
        });
    }

    public function updateStatus(
        SchoolClass $schoolClass,
        string $status
    ): SchoolClass {
        return DB::transaction(function () use (
            $schoolClass,
            $status
        ) {

            $schoolClass->update([
                'status' => $status,
            ]);

            return $schoolClass->load([
                'grade',
                'academicYear',
                'homeroomTeacher',
            ]);
        });
    }
}
