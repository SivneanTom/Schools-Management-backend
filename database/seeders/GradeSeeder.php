<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            [
                'code' => 'GRADE_1',
                'name_km' => 'ថ្នាក់ទី១',
                'name_en' => 'Grade 1',
                'order_no' => 1,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GRADE_2',
                'name_km' => 'ថ្នាក់ទី២',
                'name_en' => 'Grade 2',
                'order_no' => 2,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GRADE_3',
                'name_km' => 'ថ្នាក់ទី៣',
                'name_en' => 'Grade 3',
                'order_no' => 3,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GRADE_4',
                'name_km' => 'ថ្នាក់ទី៤',
                'name_en' => 'Grade 4',
                'order_no' => 4,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GRADE_5',
                'name_km' => 'ថ្នាក់ទី៥',
                'name_en' => 'Grade 5',
                'order_no' => 5,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GRADE_6',
                'name_km' => 'ថ្នាក់ទី៦',
                'name_en' => 'Grade 6',
                'order_no' => 6,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GRADE_7',
                'name_km' => 'ថ្នាក់ទី៧',
                'name_en' => 'Grade 7',
                'order_no' => 7,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GRADE_8',
                'name_km' => 'ថ្នាក់ទី៨',
                'name_en' => 'Grade 8',
                'order_no' => 8,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GRADE_9',
                'name_km' => 'ថ្នាក់ទី៩',
                'name_en' => 'Grade 9',
                'order_no' => 9,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GRADE_10',
                'name_km' => 'ថ្នាក់ទី១០',
                'name_en' => 'Grade 10',
                'order_no' => 10,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GRADE_11',
                'name_km' => 'ថ្នាក់ទី១១',
                'name_en' => 'Grade 11',
                'order_no' => 11,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GRADE_12',
                'name_km' => 'ថ្នាក់ទី១២',
                'name_en' => 'Grade 12',
                'order_no' => 12,
                'status' => 'ACTIVE',
            ],
        ];


        foreach ($grades as $grade) {

            Grade::updateOrCreate(
                [
                    'code' => $grade['code']
                ],
                $grade
            );

        }
    }
}