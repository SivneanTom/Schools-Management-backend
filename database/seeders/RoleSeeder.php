<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $roles = [
            [
                'code' => 'SUPER_ADMIN',
                'name_km' => 'អ្នកគ្រប់គ្រងកំពូល',
                'name_en' => 'Super Admin',
            ],
            [
                'code' => 'ADMIN',
                'name_km' => 'អ្នកគ្រប់គ្រង',
                'name_en' => 'Admin',
            ],
            [
                'code' => 'TEACHER',
                'name_km' => 'គ្រូបង្រៀន',
                'name_en' => 'Teacher',
            ],
            [
                'code' => 'ACCOUNTANT',
                'name_km' => 'អ្នកគណនេយ្យ',
                'name_en' => 'Accountant',
            ],
            [
                'code' => 'LIBRARIAN',
                'name_km' => 'បណ្ណាល័យ',
                'name_en' => 'Librarian',
            ],
            [
                'code' => 'STUDENT',
                'name_km' => 'សិស្ស',
                'name_en' => 'Student',
            ],
            [
                'code' => 'PARENT',
                'name_km' => 'ឪពុកម្តាយ',
                'name_en' => 'Parent',
            ]
       ];

       foreach ($roles as $role){
        Role::updateOrCreate(
            [
                'code' => $role['code']
            ],
           $role
        );

       }
    }
}

// We use updateOrCreate() so running the seeder again does not create duplicate roles.
