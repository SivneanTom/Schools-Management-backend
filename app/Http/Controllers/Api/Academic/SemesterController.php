<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Semester\StoreSemesterRequest;
use App\Http\Requests\Semester\UpdateSemesterRequest;
use App\Http\Requests\Semester\UpdateSemesterStatusRequest;
use App\Http\Resources\Semester\SemesterListResource;
use App\Http\Resources\Semester\SemesterResource;
use App\Models\Semester;
use App\Services\Semester\SemesterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function __construct(
        private readonly SemesterService $semesterService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $semesters = Semester::query()
            ->with('academicYear')

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search =
                        $request
                            ->string('search')
                            ->toString();

                    $query->where(
                        'name',
                        'ilike',
                        "%{$search}%"
                    );
                }
            )

            ->when(
                $request->filled('academicYearId'),
                fn ($query) =>
                    $query->where(
                        'academic_year_id',
                        $request->integer(
                            'academicYearId'
                        )
                    )
            )

            ->when(
                $request->filled('status'),
                fn ($query) =>
                    $query->where(
                        'status',
                        $request->status
                    )
            )

            ->orderByDesc('start_date')

            ->paginate(
                $request->integer('size', 10)
            );

        return response()->json([
            'success' => true,

            'data' =>
                SemesterListResource::collection(
                    $semesters->items()
                ),

            'pagination' => [
                'page' =>
                    $semesters->currentPage() - 1,

                'size' =>
                    $semesters->perPage(),

                'totalElements' =>
                    $semesters->total(),

                'totalPages' =>
                    $semesters->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreSemesterRequest $request
    ): JsonResponse {

        $semester =
            $this->semesterService->create(
                $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' =>
                'Semester created successfully.',

            'data' =>
                new SemesterResource($semester),
        ], 201);
    }

    public function show(
        Semester $semester
    ): JsonResponse {

        $semester->load('academicYear');

        return response()->json([
            'success' => true,

            'data' =>
                new SemesterResource($semester),
        ]);
    }

    public function update(
        UpdateSemesterRequest $request,
        Semester $semester
    ): JsonResponse {

        $semester =
            $this->semesterService->update(
                $semester,
                $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' =>
                'Semester updated successfully.',

            'data' => [
                'id' => $semester->id,

                'name' => $semester->name,

                'academicYearId' =>
                    $semester->academic_year_id,

                'startDate' =>
                    $semester->start_date
                        ?->format('Y-m-d'),

                'endDate' =>
                    $semester->end_date
                        ?->format('Y-m-d'),

                'status' =>
                    $semester->status,
            ],
        ]);
    }

    public function updateStatus(
        UpdateSemesterStatusRequest $request,
        Semester $semester
    ): JsonResponse {

        $semester =
            $this->semesterService->updateStatus(
                $semester,
                $request->validated('status')
            );

        return response()->json([
            'success' => true,
            'message' =>
                'Semester status updated successfully.',

            'data' => [
                'id' =>
                    $semester->id,

                'status' =>
                    $semester->status,
            ],
        ]);
    }
}