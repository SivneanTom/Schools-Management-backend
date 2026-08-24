<?php

namespace App\Services\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function create(array $data): User
    {
        /** @var User $actor */
        $actor = Auth::user();
        $role = Role::findOrFail(
            $data['role_id']
        );
        $this->ensureRoleCanBeAssigned(
            $actor,
            $role
        );
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'role_id' => $data['role_id'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make(
                    $data['password']
                ),
                'preferred_language' =>
                $data['preferred_language'] ?? 'KM',
                'status' =>
                $data['status'] ?? 'ACTIVE',
            ]);
            return $user->load('role');
        });
    }
    public function updateStatus(
        User $user,
        string $status
    ): User {
        /** @var User $actor */
        $actor = Auth::user();

        $this->ensureCanManageUser(
            $actor,
            $user
        );
        if ($actor->id === $user->id) {
            throw ValidationException::withMessages([
                'status' =>
                'You cannot change your own status.',
            ]);
        }
        $user->update([
            'status' => $status,
        ]);
        return $user->load('role');
    }
    public function find(User $user): User
    {
        /** @var User $actor */
        $actor = Auth::user();

        $this->ensureCanManageUser(
            $actor,
            $user,
            true
        );
        return $user->load('role');
    }
    private function ensureRoleCanBeAssigned(
        User $actor,
        Role $role
    ): void {
        if (
            $actor->role->code === 'ADMIN'
            && in_array(
                $role->code,
                [
                    'SUPER_ADMIN',
                    'ADMIN',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'role_id' =>
                'Admin cannot assign SUPER_ADMIN or ADMIN role.',
            ]);
        }
    }
    private function ensureCanManageUser(
        User $actor,
        User $target,
        bool $allowSuperAdmin = false
    ): void {
        if ($actor->role->code === 'SUPER_ADMIN') {
            return;
        }
        if (
            $actor->role->code === 'ADMIN'
            && in_array(
                $target->role->code,
                [
                    'SUPER_ADMIN',
                    'ADMIN',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'user' =>
                'Admin cannot manage SUPER_ADMIN or ADMIN accounts.',
            ]);
        }
    }
}