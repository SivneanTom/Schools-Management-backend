<?php

namespace App\Policies;

use App\Models\User;

class LibraryPolicy
{
    public function accessLibrary(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
            'LIBRARIAN',
        ], true);
    }

    public function manageBooks(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
            'LIBRARIAN',
        ], true);
    }

    public function manageBorrowing(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'LIBRARIAN',
        ], true);
    }

    public function viewLibraryReports(User $user): bool
    {
        return in_array($user->role->code, [
            'SUPER_ADMIN',
            'ADMIN',
            'LIBRARIAN',
        ], true);
    }
}
