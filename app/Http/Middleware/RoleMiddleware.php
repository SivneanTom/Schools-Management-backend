<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        // 1. Check authenticated user
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHENTICATED',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // 2. Load user role
        $user->loadMissing('role');

        if (!$user->role) {
            return response()->json([
                'success' => false,
                'code' => 'ROLE_NOT_FOUND',
                'message' => 'User role was not found.',
            ], 403);
        }

        // 3. Normalize logged-in user's role
        $userRole = strtoupper(
            trim($user->role->code)
        );

        // 4. Normalize allowed roles from route
        $allowedRoles = array_map(
            fn ($role) => strtoupper(
                trim($role)
            ),
            $roles
        );

        // 5. Check permission
        if (!in_array(
            $userRole,
            $allowedRoles,
            true
        )) {
            return response()->json([
                'success' => false,
                'code' => 'FORBIDDEN',
                'message' =>
                    'You do not have permission to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}