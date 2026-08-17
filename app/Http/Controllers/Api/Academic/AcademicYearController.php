<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYear\StoreAcademicYearRequest;
use App\Http\Requests\AcademicYear\UpdateAcademicYearRequest;
use App\Http\Requests\AcademicYear\UpdateAcademicYearStatusRequest;
use App\Http\Resources\AcademicYear\AcademicYearListResource;
use App\Http\Resources\AcademicYear\AcademicYearResource;
use App\Models\AcademicYear;
use App\Services\AcademicYear\AcademicYearService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function __construct(
        private readonly AcademicYearService $academicYearService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $academicYears = AcademicYear::query()

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search =
                        $request->string('search')->toString();

                    $query->where(
                        'name',
                        'ilike',
                        "%{$search}%"
                    );
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

            ->orderByDesc('start_date')

            ->paginate(
                $request->integer('size', 10)
            );

        return response()->json([
            'success' => true,

            'data' =>
                AcademicYearListResource::collection(
                    $academicYears->items()
                ),

            'pagination' => [
                'page' =>
                    $academicYears->currentPage() - 1,

                'size' =>
                    $academicYears->perPage(),

                'totalElements' =>
                    $academicYears->total(),

                'totalPages' =>
                    $academicYears->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreAcademicYearRequest $request
    ): JsonResponse {

        $academicYear =
            $this->academicYearService->create(
                $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' =>
                'Academic year created successfully.',

            'data' =>
                new AcademicYearResource($academicYear),
        ], 201);
    }

    public function show(
        AcademicYear $academicYear
    ): JsonResponse {

        return response()->json([
            'success' => true,

            'data' =>
                new AcademicYearResource($academicYear),
        ]);
    }

    public function update(
        UpdateAcademicYearRequest $request,
        AcademicYear $academicYear
    ): JsonResponse {

        $academicYear =
            $this->academicYearService->update(
                $academicYear,
                $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' =>
                'Academic year updated successfully.',

            'data' => [
                'id' => $academicYear->id,
                'name' => $academicYear->name,

                'startDate' =>
                    $academicYear->start_date
                        ?->format('Y-m-d'),

                'endDate' =>
                    $academicYear->end_date
                        ?->format('Y-m-d'),

                'status' =>
                    $academicYear->status,
            ],
        ]);
    }

    public function updateStatus(
        UpdateAcademicYearStatusRequest $request,
        AcademicYear $academicYear
    ): JsonResponse {

        $academicYear =
            $this->academicYearService->updateStatus(
                $academicYear,
                $request->validated('status')
            );

        return response()->json([
            'success' => true,
            'message' =>
                'Academic year status updated successfully.',

            'data' => [
                'id' => $academicYear->id,
                'status' =>
                    $academicYear->status,
            ],
        ]);
    }
}