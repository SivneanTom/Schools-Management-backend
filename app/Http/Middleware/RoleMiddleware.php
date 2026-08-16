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

    //  Check Logged-in user
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHENTICATED',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        //  Check Allow roles 
        $user->loadMissing('role');

        if (!$user->role) {
            return response()->json([
                'success' => false,
                'code' => 'ROLE_NOT_FOUND',
                'message' => 'User role was not found.',
            ] , 403 );
        }

        // Allowed? ( Y -> Continuse  , N -> 403 )

        if (!in_array($user->role->code, $roles, true)) {
            return response()->json([
                'success' => false,
                'code' => 'FORBIDDEN',
                'message' => 'You do not have permission to perform this action. ',
            ], 403);
        }

        return $next($request);
    }
}
