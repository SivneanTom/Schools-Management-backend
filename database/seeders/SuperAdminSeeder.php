<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('code', 'SUPER_ADMIN')
            ->firstOrFail();

        User::updateOrCreate(
            [
                'email' => 'superadmin@school.com',
            ],
            [
                'role_id' => $role->id,
                'username' => 'superadmin',
                'password' => Hash::make('Password123!'),
                'preferred_language' => 'EN',
                'status' => 'ACTIVE',
            ]
        );
    }
}