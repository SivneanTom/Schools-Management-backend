<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Grade;
use App\Models\AcademicYear;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        // Get active academic year
        $academicYear = AcademicYear::where('status', 'ACTIVE')
            ->orderByDesc('start_date')
            ->first();

        if (!$academicYear) {
            throw new \Exception(
                'No ACTIVE academic year found.'
            );
        }


        $khmerNumbers = [
            1 => '១',
            2 => '២',
            3 => '៣',
            4 => '៤',
            5 => '៥',
            6 => '៦',
            7 => '៧',
            8 => '៨',
            9 => '៩',
            10 => '១០',
            11 => '១១',
            12 => '១២',
        ];


        $sections = [
            'A' => 'ក',
            'B' => 'ខ',
            'C' => 'គ',
        ];


        for ($gradeNo = 1; $gradeNo <= 12; $gradeNo++) {


            $grade = Grade::where(
                'code',
                'GRADE_'.$gradeNo
            )->first();


            if (!$grade) {
                throw new \Exception(
                    "GRADE_{$gradeNo} not found."
                );
            }


            foreach ($sections as $en => $km) {


                SchoolClass::updateOrCreate(
                    [
                        'grade_id' => $grade->id,

                        'academic_year_id' =>
                            $academicYear->id,

                        'name_en' =>
                            "Grade {$gradeNo} {$en}",
                    ],
                    [

                        'name_km' =>
                            "ថ្នាក់ទី{$khmerNumbers[$gradeNo]} {$km}",


                        'capacity' => 40,


                        'homeroom_teacher_id' => null,


                        'status' => 'ACTIVE',

                    ]
                );

            }
        }
    }
}