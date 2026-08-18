<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectStatusRequest;
use App\Http\Resources\Subject\SubjectListResource;
use App\Http\Resources\Subject\SubjectResource;
use App\Models\Subject;
use App\Services\Subject\SubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function __construct(
        private readonly SubjectService $subjectService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $subjects = Subject::query()
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request
                        ->string('search')
                        ->toString();

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('code', 'ilike', "%{$search}%")
                            ->orWhere('name_km', 'ilike', "%{$search}%")
                            ->orWhere('name_en', 'ilike', "%{$search}%")
                            ->orWhere('description_km', 'ilike', "%{$search}%")
                            ->orWhere('description_en', 'ilike', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->has('isActive'),
                fn ($query) => $query->where(
                    'is_active',
                    $request->boolean('isActive')
                )
            )
            ->orderBy('code')
            ->paginate(
                $request->integer('size', 20)
            );

        return response()->json([
            'success' => true,
            'data' => SubjectListResource::collection(
                $subjects->items()
            ),
            'pagination' => [
                'page' => $subjects->currentPage() - 1,
                'size' => $subjects->perPage(),
                'totalElements' => $subjects->total(),
                'totalPages' => $subjects->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreSubjectRequest $request
    ): JsonResponse {
        $subject = $this->subjectService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Subject created successfully.',
            'data' => new SubjectResource($subject),
        ], 201);
    }

    public function show(
        Subject $subject
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => new SubjectResource($subject),
        ]);
    }

    public function update(
        UpdateSubjectRequest $request,
        Subject $subject
    ): JsonResponse {
        $subject = $this->subjectService->update(
            $subject,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Subject updated successfully.',
            'data' => new SubjectResource($subject),
        ]);
    }

    public function updateStatus(
        UpdateSubjectStatusRequest $request,
        Subject $subject
    ): JsonResponse {
        $subject = $this->subjectService->updateStatus(
            $subject,
            $request->boolean('isActive')
        );

        return response()->json([
            'success' => true,
            'message' => 'Subject status updated successfully.',
            'data' => [
                'id' => $subject->id,
                'isActive' => $subject->is_active,
            ],
        ]);
    }
}
