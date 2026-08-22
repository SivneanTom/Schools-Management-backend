<?php

namespace App\Services\SchoolClass;

use App\Models\SchoolClass;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SchoolClassService
{

    public function create(array $data): SchoolClass
    {
        return DB::transaction(function () use ($data) {


            $grade = Grade::findOrFail(
                $data['gradeId']
            );


            $this->validateClassName(
                $grade,
                $data['nameEn']
            );


            $schoolClass = SchoolClass::create([

                'grade_id' =>
                $data['gradeId'],

                'academic_year_id' =>
                $data['academicYearId'],

                'homeroom_teacher_id' =>
                $data['homeroomTeacherId'] ?? null,

                'name_km' =>
                trim($data['nameKm']),

                'name_en' =>
                trim($data['nameEn']),

                'capacity' =>
                $data['capacity'],

                'status' =>
                $data['status'] ?? 'ACTIVE',
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


            $gradeId =
                $data['gradeId']
                ?? $schoolClass->grade_id;


            $nameEn =
                $data['nameEn']
                ?? $schoolClass->name_en;



            $grade = Grade::findOrFail(
                $gradeId
            );


            $this->validateClassName(
                $grade,
                $nameEn
            );



            if (
                array_key_exists(
                    'gradeId',
                    $data
                )
            ) {

                $schoolClass->grade_id =
                    $data['gradeId'];
            }



            if (
                array_key_exists(
                    'academicYearId',
                    $data
                )
            ) {

                $schoolClass->academic_year_id =
                    $data['academicYearId'];
            }



            if (
                array_key_exists(
                    'homeroomTeacherId',
                    $data
                )
            ) {

                $schoolClass->homeroom_teacher_id =
                    $data['homeroomTeacherId'];
            }



            if (
                array_key_exists(
                    'nameKm',
                    $data
                )
            ) {

                $schoolClass->name_km =
                    trim($data['nameKm']);
            }



            if (
                array_key_exists(
                    'nameEn',
                    $data
                )
            ) {

                $schoolClass->name_en =
                    trim($data['nameEn']);
            }



            if (
                array_key_exists(
                    'capacity',
                    $data
                )
            ) {

                $schoolClass->capacity =
                    $data['capacity'];
            }



            if (
                array_key_exists(
                    'status',
                    $data
                )
            ) {

                $schoolClass->status =
                    $data['status'];
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

                'status' =>
                $status,

            ]);



            return $schoolClass->load([

                'grade',

                'academicYear',

                'homeroomTeacher',

            ]);
        });
    }






    private function validateClassName(
        Grade $grade,
        string $className
    ): void {


        /*
        Example:

        Grade 1
        allowed:
        Grade 1 A

        Grade 7
        allowed:
        Grade 7 B
        */


        $expected =
            'grade ' .
            $grade->order_no;



        if (
            !str_contains(
                strtolower($className),
                strtolower($expected)
            )
        ) {


            throw ValidationException::withMessages([

                'nameEn' => [

                    "Class name must match selected grade {$grade->name_en}."

                ]

            ]);
        }
    }
}
