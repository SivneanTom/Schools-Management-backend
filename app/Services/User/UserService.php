<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function create(array $data): User
    {
        $user = User::create([
            'role_id' => $data['role_id'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'preferred_language' =>
                $data['preferred_language'] ?? 'KM',
            'status' =>
                $data['status'] ?? 'ACTIVE',
        ]);

        return $user->load('role');
    }


    public function updateStatus(
        User $user,
        string $status
    ): User {

        $user->update([
            'status'=>$status
        ]);

        return $user->load('role');
    }


    public function find(User $user): User
    {
        return $user->load('role');
    }
}