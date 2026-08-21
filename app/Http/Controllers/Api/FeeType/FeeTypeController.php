<?php

namespace App\Http\Controllers\Api\FeeType;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeeType\StoreFeeTypeRequest;
use App\Http\Requests\FeeType\UpdateFeeTypeRequest;
use App\Http\Resources\FeeTypeResource;
use App\Models\FeeType;
use App\Services\FeeType\FeeTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeTypeController extends Controller
{
    public function __construct(
        private readonly FeeTypeService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->service->paginate($request->only([
            'search',
            'frequency',
            'is_active',
            'per_page',
        ]));

        return response()->json([
            'success' => true,
            'data' => FeeTypeResource::collection($items->items()),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
            ],
        ]);
    }

    public function store(StoreFeeTypeRequest $request): JsonResponse
    {
        $feeType = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Fee type created successfully.',
            'data' => new FeeTypeResource($feeType),
        ], 201);
    }

    public function show(FeeType $feeType): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new FeeTypeResource($feeType),
        ]);
    }

    public function update(UpdateFeeTypeRequest $request, FeeType $feeType): JsonResponse
    {
        $feeType = $this->service->update($feeType, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Fee type updated successfully.',
            'data' => new FeeTypeResource($feeType),
        ]);
    }

    public function destroy(FeeType $feeType): JsonResponse
    {
        $this->service->delete($feeType);

        return response()->json([
            'success' => true,
            'message' => 'Fee type deleted successfully.',
        ]);
    }
}
