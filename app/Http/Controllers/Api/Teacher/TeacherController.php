<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherStatusRequest;
use App\Http\Resources\Teacher\TeacherListResource;
use App\Http\Resources\Teacher\TeacherResource;
use App\Models\Teacher;
use App\Services\Teacher\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeacherController extends Controller
{
    public function __construct(
        private readonly TeacherService $teacherService
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Teacher::class);
        $teachers = Teacher::query()
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {

                    $search =
                        $request->string('search')->toString();

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where(
                                'teacher_code',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'first_name_km',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'last_name_km',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'first_name_en',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'last_name_en',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'phone',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'specialization',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'user',
                                function ($userQuery) use ($search) {
                                    $userQuery
                                        ->where(
                                            'email',
                                            'ilike',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'username',
                                            'ilike',
                                            "%{$search}%"
                                        );
                                }
                            );
                    });
                }
            )

            ->when(
                $request->filled('gender'),
                fn($query) =>
                $query->where(
                    'gender',
                    $request->gender
                )
            )
            ->when(
                $request->filled('status'),
                fn($query) =>
                $query->where(
                    'status',
                    $request->status
                )
            )
            ->latest()
            ->paginate(
                $request->integer('size', 10)
            );

        return response()->json([
            'success' => true,
            'data' => TeacherListResource::collection(
                $teachers->items()
            ),
            'pagination' => [
                'page' =>
                $teachers->currentPage() - 1,

                'size' =>
                $teachers->perPage(),

                'totalElements' =>
                $teachers->total(),

                'totalPages' =>
                $teachers->lastPage(),
            ],
        ]);
    }

    public function store( StoreTeacherRequest $request): JsonResponse {

        Gate::authorize('create', Teacher::class);
        $teacher = $this->teacherService->create(
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Teacher created successfully.',
            'data' => new TeacherResource($teacher),
        ], 201);
    }

    public function show(Teacher $teacher): JsonResponse {
        Gate::authorize('view', $teacher);
        $teacher->load('user.role');

        return response()->json([
            'success' => true,
            'data' => new TeacherResource($teacher),
        ]);
    }

    public function update( UpdateTeacherRequest $request,Teacher $teacher): JsonResponse {
        Gate::authorize('update', $teacher);
        $teacher = $this->teacherService->update(
            $teacher,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Teacher updated successfully.',

            'data' => [
                'id' => $teacher->id,
                'teacherCode' => $teacher->teacher_code,
                'phone' => $teacher->phone,
                'addressEn' => $teacher->address_en,
                'qualification' => $teacher->qualification,
                'specialization' => $teacher->specialization,
                'status' => $teacher->status,
            ],
        ]);
    }

    public function updateStatus(UpdateTeacherStatusRequest $request,Teacher $teacher
    ): JsonResponse {
        $teacher =
            $this->teacherService->updateStatus(
                $teacher,
                $request->validated('status')
            );

        return response()->json([
            'success' => true,
            'message' =>
            'Teacher status updated successfully.',

            'data' => [
                'id' => $teacher->id,
                'status' => $teacher->status,
                'userStatus' =>
                $teacher->user?->status,
            ],
        ]);
    }
}
