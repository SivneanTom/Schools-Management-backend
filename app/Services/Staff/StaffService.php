<?php

namespace App\Services\Staff;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffService
{
    private const STAFF_ROLE_CODES = [
        'ADMIN',
        'PRINCIPAL',
        'ACCOUNTANT',
        'LIBRARIAN',
    ];

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Staff::query()->with(['user.role']);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function (Builder $query) use ($search) {
                $query->where('staff_no', 'ilike', "%{$search}%")
                    ->orWhere('full_name_km', 'ilike', "%{$search}%")
                    ->orWhere('full_name_en', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%")
                    ->orWhere('position_title_km', 'ilike', "%{$search}%")
                    ->orWhere('position_title_en', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['role_code'])) {
            $query->whereHas('user.role', function (Builder $query) use ($filters) {
                $query->where('code', strtoupper($filters['role_code']));
            });
        }

        return $query->orderByDesc('id')->paginate($filters['size'] ?? 20);
    }

    public function create(array $data): Staff
    {
        return DB::transaction(function () use ($data): Staff {
            $this->validateUserForStaff($data['user_id']);
            return Staff::create($data)->load(['user.role']);
        });
    }

    public function update(Staff $staff, array $data): Staff
    {
        return DB::transaction(function () use ($staff, $data): Staff {
            if (array_key_exists('user_id', $data)) {
                $this->validateUserForStaff($data['user_id'], $staff->id);
            }

            $staff->update($data);

            return $staff->refresh()->load(['user.role']);
        });
    }

    public function delete(Staff $staff): void
    {
        DB::transaction(fn () => $staff->delete());
    }

    private function validateUserForStaff(int $userId, ?int $ignoreStaffId = null): void
    {
        $user = User::with('role')->findOrFail($userId);

        if (!$user->role) {
            throw ValidationException::withMessages([
                'user_id' => ['The selected user does not have a role.'],
            ]);
        }

        $roleCode = strtoupper((string) $user->role->code);

        if (!in_array($roleCode, self::STAFF_ROLE_CODES, true)) {
            throw ValidationException::withMessages([
                'user_id' => [
                    'The selected user role cannot be used for a staff profile. Allowed roles: '
                    . implode(', ', self::STAFF_ROLE_CODES) . '.'
                ],
            ]);
        }

        $duplicate = Staff::query()
            ->where('user_id', $userId)
            ->when($ignoreStaffId, fn (Builder $query) => $query->where('id', '!=', $ignoreStaffId))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'user_id' => ['The selected user is already linked to a staff profile.'],
            ]);
        }
    }
}
