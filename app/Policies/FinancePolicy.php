<?php

namespace App\Policies;

use App\Models\User;

class FinancePolicy
{
    public function viewFinance(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
            'ACCOUNTANT',
        ], true);
    }

    public function manageFee(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true);
    }

    public function manageScholarship(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
            'ACCOUNTANT',
        ], true);
    }

    public function createPayment(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ACCOUNTANT',
        ], true);
    }

    public function createReceipt(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ACCOUNTANT',
        ], true);
    }
}