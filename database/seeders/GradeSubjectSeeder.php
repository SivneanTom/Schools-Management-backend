<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\GradeSubject;

class GradeSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $curriculum = [

            /*
            |--------------------------------------------------------------------------
            | Grade 1-3
            |--------------------------------------------------------------------------
            */

            'GRADE_1' => [
                'KHMER'=>7,
                'MATH'=>5,
                'SCIENCE'=>3,
                'SOCIAL_STUDIES'=>3,
                'MORAL_CIVICS'=>2,
                'PE'=>2,
                'ART'=>1,
                'MUSIC'=>1,
                'HEALTH'=>1,
                'LIFE_SKILLS'=>1,
            ],

            'GRADE_2' => [
                'KHMER'=>7,
                'MATH'=>5,
                'SCIENCE'=>3,
                'SOCIAL_STUDIES'=>3,
                'MORAL_CIVICS'=>2,
                'PE'=>2,
                'ART'=>1,
                'MUSIC'=>1,
                'HEALTH'=>1,
                'LIFE_SKILLS'=>1,
            ],

            'GRADE_3' => [
                'KHMER'=>7,
                'MATH'=>5,
                'SCIENCE'=>3,
                'SOCIAL_STUDIES'=>3,
                'MORAL_CIVICS'=>2,
                'PE'=>2,
                'ART'=>1,
                'MUSIC'=>1,
                'HEALTH'=>1,
                'LIFE_SKILLS'=>1,
            ],


            /*
            |--------------------------------------------------------------------------
            | Grade 4-6
            |--------------------------------------------------------------------------
            */

            'GRADE_4' => [
                'KHMER'=>6,
                'MATH'=>5,
                'ENGLISH'=>4,
                'SCIENCE'=>3,
                'SOCIAL_STUDIES'=>3,
                'MORAL_CIVICS'=>2,
                'ICT'=>2,
                'PE'=>2,
                'ART'=>1,
                'HEALTH'=>1,
                'LIFE_SKILLS'=>1,
            ],

            'GRADE_5' => [
                'KHMER'=>6,
                'MATH'=>5,
                'ENGLISH'=>4,
                'SCIENCE'=>3,
                'SOCIAL_STUDIES'=>3,
                'MORAL_CIVICS'=>2,
                'ICT'=>2,
                'PE'=>2,
                'ART'=>1,
                'HEALTH'=>1,
                'LIFE_SKILLS'=>1,
            ],

            'GRADE_6' => [
                'KHMER'=>6,
                'MATH'=>5,
                'ENGLISH'=>4,
                'SCIENCE'=>3,
                'SOCIAL_STUDIES'=>3,
                'MORAL_CIVICS'=>2,
                'ICT'=>2,
                'PE'=>2,
                'ART'=>1,
                'HEALTH'=>1,
                'LIFE_SKILLS'=>1,
            ],


            /*
            |--------------------------------------------------------------------------
            | Grade 7-9
            |--------------------------------------------------------------------------
            */

            'GRADE_7' => [
                'KHMER'=>5,
                'MATH'=>5,
                'ENGLISH'=>4,
                'PHYSICS'=>3,
                'CHEMISTRY'=>3,
                'BIOLOGY'=>3,
                'HISTORY'=>3,
                'GEOGRAPHY'=>3,
                'MORAL_CIVICS'=>2,
                'ICT'=>2,
                'PE'=>2,
                'HEALTH'=>1,
                'LIFE_SKILLS'=>1,
            ],

            'GRADE_8' => [
                'KHMER'=>5,
                'MATH'=>5,
                'ENGLISH'=>4,
                'PHYSICS'=>3,
                'CHEMISTRY'=>3,
                'BIOLOGY'=>3,
                'HISTORY'=>3,
                'GEOGRAPHY'=>3,
                'MORAL_CIVICS'=>2,
                'ICT'=>2,
                'PE'=>2,
                'HEALTH'=>1,
                'LIFE_SKILLS'=>1,
            ],

            'GRADE_9' => [
                'KHMER'=>5,
                'MATH'=>5,
                'ENGLISH'=>4,
                'PHYSICS'=>3,
                'CHEMISTRY'=>3,
                'BIOLOGY'=>3,
                'HISTORY'=>3,
                'GEOGRAPHY'=>3,
                'MORAL_CIVICS'=>2,
                'ICT'=>2,
                'PE'=>2,
                'HEALTH'=>1,
                'LIFE_SKILLS'=>1,
            ],


            /*
            |--------------------------------------------------------------------------
            | Grade 10-12
            |--------------------------------------------------------------------------
            */

            'GRADE_10' => [
                'KHMER'=>5,
                'MATH'=>6,
                'ENGLISH'=>4,
                'PHYSICS'=>4,
                'CHEMISTRY'=>4,
                'BIOLOGY'=>4,
                'HISTORY'=>3,
                'GEOGRAPHY'=>3,
                'MORAL_CIVICS'=>2,
                'ICT'=>2,
                'PE'=>2,
                'ECONOMICS'=>3,
            ],

            'GRADE_11' => [
                'KHMER'=>5,
                'MATH'=>6,
                'ENGLISH'=>4,
                'PHYSICS'=>4,
                'CHEMISTRY'=>4,
                'BIOLOGY'=>4,
                'HISTORY'=>3,
                'GEOGRAPHY'=>3,
                'MORAL_CIVICS'=>2,
                'ICT'=>2,
                'PE'=>2,
                'ECONOMICS'=>3,
            ],

            'GRADE_12' => [
                'KHMER'=>5,
                'MATH'=>6,
                'ENGLISH'=>4,
                'PHYSICS'=>4,
                'CHEMISTRY'=>4,
                'BIOLOGY'=>4,
                'HISTORY'=>3,
                'GEOGRAPHY'=>3,
                'MORAL_CIVICS'=>2,
                'ICT'=>2,
                'PE'=>2,
                'ECONOMICS'=>3,
            ],

        ];


        foreach ($curriculum as $gradeCode => $subjects) {

            $grade = Grade::where('code',$gradeCode)->first();

            if (!$grade) {
                continue;
            }


            foreach ($subjects as $subjectCode => $creditHours) {

                $subject = Subject::where(
                    'code',
                    $subjectCode
                )->first();


                if (!$subject) {
                    continue;
                }


                GradeSubject::updateOrCreate(
                    [
                        'grade_id'=>$grade->id,
                        'subject_id'=>$subject->id,
                    ],
                    [
                        'credit_hours'=>$creditHours,
                        'is_required'=>true,
                    ]
                );

            }
        }
    }
}