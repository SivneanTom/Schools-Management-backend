<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [

            [
                'code' => 'KHMER',
                'name_km' => 'ភាសាខ្មែរ',
                'name_en' => 'Khmer Language',
                'description_km' => 'មុខវិជ្ជាភាសា និងអក្សរសាស្ត្រខ្មែរ',
                'description_en' => 'Khmer language and literature',
                'credit_hours' => 5,
                'is_active' => true,
            ],

            [
                'code' => 'ENGLISH',
                'name_km' => 'ភាសាអង់គ្លេស',
                'name_en' => 'English Language',
                'description_km' => 'មុខវិជ្ជាភាសាអង់គ្លេស',
                'description_en' => 'English language',
                'credit_hours' => 4,
                'is_active' => true,
            ],

            [
                'code' => 'MATH',
                'name_km' => 'គណិតវិទ្យា',
                'name_en' => 'Mathematics',
                'description_km' => 'មុខវិជ្ជាគណិតវិទ្យា',
                'description_en' => 'Mathematics',
                'credit_hours' => 5,
                'is_active' => true,
            ],

            [
                'code' => 'SCIENCE',
                'name_km' => 'វិទ្យាសាស្ត្រ',
                'name_en' => 'Science',
                'description_km' => 'មុខវិជ្ជាវិទ្យាសាស្ត្រទូទៅ',
                'description_en' => 'General science',
                'credit_hours' => 3,
                'is_active' => true,
            ],

            [
                'code' => 'SOCIAL_STUDIES',
                'name_km' => 'សិក្សាសង្គម',
                'name_en' => 'Social Studies',
                'description_km' => 'មុខវិជ្ជាសិក្សាសង្គម',
                'description_en' => 'Social studies',
                'credit_hours' => 3,
                'is_active' => true,
            ],


            [
                'code' => 'PHYSICS',
                'name_km' => 'រូបវិទ្យា',
                'name_en' => 'Physics',
                'description_km' => 'មុខវិជ្ជារូបវិទ្យា',
                'description_en' => 'Physics',
                'credit_hours' => 4,
                'is_active' => true,
            ],

            [
                'code' => 'CHEMISTRY',
                'name_km' => 'គីមីវិទ្យា',
                'name_en' => 'Chemistry',
                'description_km' => 'មុខវិជ្ជាគីមីវិទ្យា',
                'description_en' => 'Chemistry',
                'credit_hours' => 4,
                'is_active' => true,
            ],

            [
                'code' => 'BIOLOGY',
                'name_km' => 'ជីវវិទ្យា',
                'name_en' => 'Biology',
                'description_km' => 'មុខវិជ្ជាជីវវិទ្យា',
                'description_en' => 'Biology',
                'credit_hours' => 4,
                'is_active' => true,
            ],


            [
                'code' => 'HISTORY',
                'name_km' => 'ប្រវត្តិវិទ្យា',
                'name_en' => 'History',
                'description_km' => 'មុខវិជ្ជាប្រវត្តិវិទ្យា',
                'description_en' => 'History',
                'credit_hours' => 3,
                'is_active' => true,
            ],

            [
                'code' => 'GEOGRAPHY',
                'name_km' => 'ភូមិវិទ្យា',
                'name_en' => 'Geography',
                'description_km' => 'មុខវិជ្ជាភូមិវិទ្យា',
                'description_en' => 'Geography',
                'credit_hours' => 3,
                'is_active' => true,
            ],


            [
                'code' => 'MORAL_CIVICS',
                'name_km' => 'សីលធម៌ និងពលរដ្ឋវិជ្ជា',
                'name_en' => 'Moral and Civic Education',
                'description_km' => 'ការអប់រំសីលធម៌ និងពលរដ្ឋវិជ្ជា',
                'description_en' => 'Moral and civic education',
                'credit_hours' => 2,
                'is_active' => true,
            ],

            [
                'code' => 'ECONOMICS',
                'name_km' => 'សេដ្ឋកិច្ច',
                'name_en' => 'Economics',
                'description_km' => 'មុខវិជ្ជាសេដ្ឋកិច្ច',
                'description_en' => 'Economics',
                'credit_hours' => 3,
                'is_active' => true,
            ],


            [
                'code' => 'ICT',
                'name_km' => 'បច្ចេកវិទ្យាព័ត៌មាន និងសារគមនាគមន៍',
                'name_en' => 'Information and Communication Technology',
                'description_km' => 'មុខវិជ្ជាកុំព្យូទ័រ និងបច្ចេកវិទ្យាព័ត៌មាន',
                'description_en' => 'Computer and information technology',
                'credit_hours' => 2,
                'is_active' => true,
            ],


            [
                'code' => 'PE',
                'name_km' => 'អប់រំកាយ និងកីឡា',
                'name_en' => 'Physical Education',
                'description_km' => 'មុខវិជ្ជាអប់រំកាយ និងកីឡា',
                'description_en' => 'Physical education and sports',
                'credit_hours' => 2,
                'is_active' => true,
            ],

            [
                'code' => 'HEALTH',
                'name_km' => 'អប់រំសុខភាព',
                'name_en' => 'Health Education',
                'description_km' => 'មុខវិជ្ជាអប់រំសុខភាព',
                'description_en' => 'Health education',
                'credit_hours' => 1,
                'is_active' => true,
            ],


            [
                'code' => 'ART',
                'name_km' => 'សិល្បៈ',
                'name_en' => 'Arts',
                'description_km' => 'មុខវិជ្ជាសិល្បៈ',
                'description_en' => 'Arts education',
                'credit_hours' => 2,
                'is_active' => true,
            ],

            [
                'code' => 'MUSIC',
                'name_km' => 'តន្ត្រី',
                'name_en' => 'Music',
                'description_km' => 'មុខវិជ្ជាតន្ត្រី',
                'description_en' => 'Music education',
                'credit_hours' => 1,
                'is_active' => true,
            ],


            [
                'code' => 'LIFE_SKILLS',
                'name_km' => 'បំណិនជីវិត',
                'name_en' => 'Life Skills',
                'description_km' => 'ការអប់រំបំណិនជីវិត',
                'description_en' => 'Life skills education',
                'credit_hours' => 2,
                'is_active' => true,
            ],

        ];


        foreach ($subjects as $subject) {

            Subject::updateOrCreate(
                [
                    'code' => $subject['code']
                ],
                $subject
            );

        }
    }
}