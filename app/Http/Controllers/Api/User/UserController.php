<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->with('role')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->string('search')->toString();

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('username', 'ilike', "%{$search}%")
                            ->orWhere('email', 'ilike', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->filled('status'),
                fn ($query) =>
                    $query->where('status', $request->status)
            )
            ->when(
                $request->filled('role'),
                function ($query) use ($request) {
                    $query->whereHas(
                        'role',
                        fn ($roleQuery) =>
                            $roleQuery->where(
                                'code',
                                $request->role
                            )
                    );
                }
            )
            ->latest()
            ->paginate(
                $request->integer('size', 10)
            );

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'pagination' => [
                'page' => $users->currentPage() - 1,
                'size' => $users->perPage(),
                'totalElements' => $users->total(),
                'totalPages' => $users->lastPage(),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'role_id' => $data['role_id'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'preferred_language' => $data['preferred_language'] ?? 'KM',
            'status' => $data['status'] ?? 'ACTIVE',
        ]);

        $user->load('role');

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => $user,
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load('role');

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function updateStatus(
        UpdateUserStatusRequest $request,
        User $user
    ): JsonResponse {
        if (
            $request->user()->id === $user->id &&
            $request->validated('status') !== 'ACTIVE'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account.',
            ], 422);
        }

        $user->update([
            'status' => $request->validated('status'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.',
            'data' => $user->load('role'),
        ]);
    }
}