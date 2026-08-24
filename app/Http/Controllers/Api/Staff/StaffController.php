<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\Staff;
use App\Services\Staff\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function __construct(
        private readonly StaffService $staffService
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Staff::class);

        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'nullable',
                Rule::in([
                    'ACTIVE',
                    'INACTIVE',
                ]),
            ],
            'role_code' => [
                'nullable',
                'string',
                'max:50',
            ],
            'size' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $staff = $this->staffService->getAll(
            $filters,
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => StaffResource::collection(
                $staff->items()
            ),
            'pagination' => [
                'page' => $staff->currentPage() - 1,
                'size' => $staff->perPage(),
                'totalElements' => $staff->total(),
                'totalPages' => $staff->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreStaffRequest $request
    ): JsonResponse {
        Gate::authorize(
            'create',
            Staff::class
        );

        return response()->json([
            'success' => true,
            'message' => 'Staff created successfully.',
            'data' => new StaffResource(
                $this->staffService->create(
                    $request->validated()
                )
            ),
        ], 201);
    }

    public function show(
        Staff $staff
    ): JsonResponse {
        Gate::authorize(
            'view',
            $staff
        );

        return response()->json([
            'success' => true,
            'data' => new StaffResource(
                $staff->load([
                    'user.role',
                ])
            ),
        ]);
    }

    public function update(
        UpdateStaffRequest $request,
        Staff $staff
    ): JsonResponse {
        Gate::authorize(
            'update',
            $staff
        );

        return response()->json([
            'success' => true,
            'message' => 'Staff updated successfully.',
            'data' => new StaffResource(
                $this->staffService->update(
                    $staff,
                    $request->validated()
                )
            ),
        ]);
    }

    public function destroy(
        Staff $staff
    ): JsonResponse {
        Gate::authorize(
            'delete',
            $staff
        );

        $this->staffService->delete(
            $staff
        );

        return response()->json([
            'success' => true,
            'message' => 'Staff deleted successfully.',
        ]);
    }
}
