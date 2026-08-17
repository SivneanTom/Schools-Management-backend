<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grade\StoreGradeRequest;
use App\Http\Requests\Grade\UpdateGradeRequest;
use App\Http\Requests\Grade\UpdateGradeStatusRequest;
use App\Http\Resources\Grade\GradeListResource;
use App\Http\Resources\Grade\GradeResource;
use App\Models\Grade;
use App\Services\Grade\GradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function __construct(
        private readonly GradeService $gradeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $grades = Grade::query()

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {

                    $search =
                        $request
                            ->string('search')
                            ->toString();

                    $query->where(function ($query) use ($search) {

                        $query
                            ->where(
                                'code',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'name_km',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'name_en',
                                'ilike',
                                "%{$search}%"
                            );
                    });
                }
            )

            ->when(
                $request->filled('status'),
                fn ($query) =>
                    $query->where(
                        'status',
                        $request->status
                    )
            )

            ->orderByRaw(
                'order_no ASC NULLS LAST'
            )

            ->paginate(
                $request->integer('size', 10)
            );

        return response()->json([
            'success' => true,

            'data' =>
                GradeListResource::collection(
                    $grades->items()
                ),

            'pagination' => [
                'page' =>
                    $grades->currentPage() - 1,

                'size' =>
                    $grades->perPage(),

                'totalElements' =>
                    $grades->total(),

                'totalPages' =>
                    $grades->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreGradeRequest $request
    ): JsonResponse {

        $grade =
            $this->gradeService->create(
                $request->validated()
            );

        return response()->json([
            'success' => true,

            'message' =>
                'Grade created successfully.',

            'data' =>
                new GradeResource($grade),
        ], 201);
    }

    public function show(
        Grade $grade
    ): JsonResponse {

        return response()->json([
            'success' => true,

            'data' =>
                new GradeResource($grade),
        ]);
    }

    public function update(
        UpdateGradeRequest $request,
        Grade $grade
    ): JsonResponse {

        $grade =
            $this->gradeService->update(
                $grade,
                $request->validated()
            );

        return response()->json([
            'success' => true,

            'message' =>
                'Grade updated successfully.',

            'data' => [
                'id' =>
                    $grade->id,

                'code' =>
                    $grade->code,

                'nameKm' =>
                    $grade->name_km,

                'nameEn' =>
                    $grade->name_en,

                'orderNo' =>
                    $grade->order_no,

                'status' =>
                    $grade->status,
            ],
        ]);
    }

    public function updateStatus(
        UpdateGradeStatusRequest $request,
        Grade $grade
    ): JsonResponse {

        $grade =
            $this->gradeService->updateStatus(
                $grade,
                $request->validated('status')
            );

        return response()->json([
            'success' => true,

            'message' =>
                'Grade status updated successfully.',

            'data' => [
                'id' =>
                    $grade->id,

                'status' =>
                    $grade->status,
            ],
        ]);
    }
}