<?php

namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\AttachStudentRequest;
use App\Http\Requests\Parent\StoreParentRequest;
use App\Http\Requests\Parent\UpdateParentRequest;
use App\Http\Requests\Parent\UpdateParentStatusRequest;
use App\Http\Resources\Parent\ParentResource;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Services\Parent\ParentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\Parent\ParentListResource;
use Illuminate\Support\Facades\Gate;

class ParentController extends Controller
{
    public function __construct(
        private readonly ParentService $parentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ParentProfile::class);
        $parents = ParentProfile::query()
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->string('search')->toString();

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('parent_code', 'ilike', "%{$search}%")
                            ->orWhere('first_name_km', 'ilike', "%{$search}%")
                            ->orWhere('last_name_km', 'ilike', "%{$search}%")
                            ->orWhere('first_name_en', 'ilike', "%{$search}%")
                            ->orWhere('last_name_en', 'ilike', "%{$search}%")
                            ->orWhere('phone', 'ilike', "%{$search}%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery
                                    ->where('email', 'ilike', "%{$search}%")
                                    ->orWhere('username', 'ilike', "%{$search}%");
                            });
                    });
                }
            )

            ->when(
                $request->filled('gender'),
                fn($query) =>
                $query->where('gender', $request->gender)
            )

            ->when(
                $request->filled('status'),
                fn($query) =>
                $query->where('status', $request->status)
            )

            ->latest()
            ->paginate(
                $request->integer('size', 10)
            );

        return response()->json([
            'success' => true,

            'data' => ParentListResource::collection(
                $parents->items()
            ),

            'pagination' => [
                'page' => $parents->currentPage() - 1,
                'size' => $parents->perPage(),
                'totalElements' => $parents->total(),
                'totalPages' => $parents->lastPage(),
            ],
        ]);
    }

    public function store( StoreParentRequest $request): JsonResponse {
        Gate::authorize('create', ParentProfile::class);

        $parent = $this->parentService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Parent created successfully.',
            'data' => new ParentResource($parent),
        ], 201);
    }

    public function show( ParentProfile $parent ): JsonResponse 
    {
        Gate::authorize('view', $parent);
        $parent->load([
            'user.role',
            'students',
        ]);

        return response()->json([
            'success' => true,
            'data' => new ParentResource($parent),
        ]);
    }

    public function update(
        UpdateParentRequest $request,
        ParentProfile $parent
    ): JsonResponse {

        $parent = $this->parentService->update(
            $parent,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Parent updated successfully.',

            'data' => [
                'id' => $parent->id,
                'parentCode' => $parent->parent_code,

                'firstNameKm' => $parent->first_name_km,
                'lastNameKm' => $parent->last_name_km,

                'firstNameEn' => $parent->first_name_en,
                'lastNameEn' => $parent->last_name_en,

                'gender' => $parent->gender,
                'phone' => $parent->phone,

                'addressKm' => $parent->address_km,
                'addressEn' => $parent->address_en,

                'status' => $parent->status,

                'user' => [
                    'id' => $parent->user?->id,
                    'username' => $parent->user?->username,
                    'email' => $parent->user?->email,
                    'preferredLanguage' =>
                    $parent->user?->preferred_language,
                ],
            ],
        ]);
    }

    public function updateStatus( UpdateParentStatusRequest $request, ParentProfile $parent ): JsonResponse 
    {
        Gate::authorize('update', $parent);
        $parent = $this->parentService->updateStatus(
            $parent,
            $request->validated('status')
        );

        return response()->json([
            'success' => true,
            'message' => 'Parent status updated successfully.',

            'data' => [
                'id' => $parent->id,
                'status' => $parent->status,
                'userStatus' => $parent->user?->status,
            ],
        ]);
    }
    public function attachStudent( AttachStudentRequest $request, ParentProfile $parent,Student $student): JsonResponse 
    {

        $this->parentService->attachStudent(
            $parent,
            $student,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Student linked to parent successfully.',

            'data' => [
                'parentId' => $parent->id,
                'studentId' => $student->id,
                'relationship' =>
                $request->validated('relationship'),
                'isPrimary' =>
                $request->boolean('isPrimary'),
            ],
        ]);
    }

    public function detachStudent(ParentProfile $parent,Student $student): JsonResponse {
        Gate::authorize('delete', $parent);
        $this->parentService->detachStudent(
            $parent,
            $student
        );

        return response()->json([
            'success' => true,
            'message' => 'Student unlinked from parent successfully.',
        ], 200);
    }
}
