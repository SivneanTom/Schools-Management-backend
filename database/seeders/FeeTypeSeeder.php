<?php

namespace Database\Seeders;

use App\Models\FeeType;
use Illuminate\Database\Seeder;

class FeeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $feeTypes = [
            [
                'code' => 'TUITION',
                'name_km' => 'ថ្លៃសិក្សា',
                'name_en' => 'Tuition Fee',
                'description_km' => 'ថ្លៃសិក្សាប្រចាំឆ្នាំ',
                'description_en' => 'Annual tuition fee',
                'default_amount' => 500.00,
                'frequency' => 'YEARLY',
                'is_active' => true,
            ],
            [
                'code' => 'REGISTRATION',
                'name_km' => 'ថ្លៃចុះឈ្មោះ',
                'name_en' => 'Registration Fee',
                'description_km' => 'ថ្លៃចុះឈ្មោះសិស្ស',
                'description_en' => 'Student registration fee',
                'default_amount' => 25.00,
                'frequency' => 'ONE_TIME',
                'is_active' => true,
            ],
            [
                'code' => 'EXAM',
                'name_km' => 'ថ្លៃប្រឡង',
                'name_en' => 'Exam Fee',
                'description_km' => 'ថ្លៃសម្រាប់ការប្រឡង',
                'description_en' => 'Fee for examinations',
                'default_amount' => 20.00,
                'frequency' => 'SEMESTER',
                'is_active' => true,
            ],
            [
                'code' => 'BOOK',
                'name_km' => 'ថ្លៃសៀវភៅ',
                'name_en' => 'Book Fee',
                'description_km' => 'ថ្លៃសៀវភៅសិក្សា',
                'description_en' => 'Fee for school books',
                'default_amount' => 50.00,
                'frequency' => 'YEARLY',
                'is_active' => true,
            ],
            [
                'code' => 'UNIFORM',
                'name_km' => 'ថ្លៃឯកសណ្ឋាន',
                'name_en' => 'Uniform Fee',
                'description_km' => 'ថ្លៃឯកសណ្ឋានសិស្ស',
                'description_en' => 'Student uniform fee',
                'default_amount' => 30.00,
                'frequency' => 'YEARLY',
                'is_active' => true,
            ],
            [
                'code' => 'TRANSPORT',
                'name_km' => 'ថ្លៃដឹកជញ្ជូន',
                'name_en' => 'Transportation Fee',
                'description_km' => 'ថ្លៃសេវាដឹកជញ្ជូនសិស្ស',
                'description_en' => 'Student transportation fee',
                'default_amount' => 40.00,
                'frequency' => 'MONTHLY',
                'is_active' => true,
            ],
            [
                'code' => 'MEAL',
                'name_km' => 'ថ្លៃអាហារ',
                'name_en' => 'Meal Fee',
                'description_km' => 'ថ្លៃអាហារសម្រាប់សិស្ស',
                'description_en' => 'Student meal fee',
                'default_amount' => 30.00,
                'frequency' => 'MONTHLY',
                'is_active' => true,
            ],
            [
                'code' => 'ACTIVITY',
                'name_km' => 'ថ្លៃសកម្មភាព',
                'name_en' => 'Activity Fee',
                'description_km' => 'ថ្លៃសម្រាប់សកម្មភាពសាលា',
                'description_en' => 'Fee for school activities',
                'default_amount' => 15.00,
                'frequency' => 'SEMESTER',
                'is_active' => true,
            ],
            [
                'code' => 'LAB',
                'name_km' => 'ថ្លៃមន្ទីរពិសោធន៍',
                'name_en' => 'Laboratory Fee',
                'description_km' => 'ថ្លៃប្រើប្រាស់មន្ទីរពិសោធន៍',
                'description_en' => 'Laboratory usage fee',
                'default_amount' => 25.00,
                'frequency' => 'SEMESTER',
                'is_active' => true,
            ],
            [
                'code' => 'LIBRARY',
                'name_km' => 'ថ្លៃបណ្ណាល័យ',
                'name_en' => 'Library Fee',
                'description_km' => 'ថ្លៃសេវាបណ្ណាល័យ',
                'description_en' => 'Library service fee',
                'default_amount' => 10.00,
                'frequency' => 'YEARLY',
                'is_active' => true,
            ],
        ];

        foreach ($feeTypes as $feeType) {
            FeeType::updateOrCreate(
                ['code' => $feeType['code']],
                $feeType
            );
        }
    }
}