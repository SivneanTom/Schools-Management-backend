<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::query()
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $role,
        ]);
    }
}