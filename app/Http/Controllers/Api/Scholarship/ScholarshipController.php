<?php

namespace App\Http\Controllers\Api\Scholarship;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scholarship\StoreScholarshipRequest;
use App\Http\Requests\Scholarship\UpdateScholarshipRequest;
use App\Http\Resources\ScholarshipResource;
use App\Models\Scholarship;
use App\Services\Scholarship\ScholarshipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    public function __construct(
        private readonly ScholarshipService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->service->paginate($request->only([
            'search',
            'discount_type',
            'is_active',
            'per_page',
        ]));

        return response()->json([
            'success' => true,
            'data' => ScholarshipResource::collection($items->items()),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
            ],
        ]);
    }

    public function store(StoreScholarshipRequest $request): JsonResponse
    {
        $scholarship = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Scholarship created successfully.',
            'data' => new ScholarshipResource($scholarship),
        ], 201);
    }

    public function show(Scholarship $scholarship): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new ScholarshipResource($scholarship),
        ]);
    }

    public function update(
        UpdateScholarshipRequest $request,
        Scholarship $scholarship
    ): JsonResponse {
        $scholarship = $this->service->update(
            $scholarship,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Scholarship updated successfully.',
            'data' => new ScholarshipResource($scholarship),
        ]);
    }

    public function destroy(Scholarship $scholarship): JsonResponse
    {
        $this->service->delete($scholarship);

        return response()->json([
            'success' => true,
            'message' => 'Scholarship deleted successfully.',
        ]);
    }
}
