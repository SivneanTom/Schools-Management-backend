<?php

namespace App\Http\Controllers\Api\Room;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Services\Room\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(
        private readonly RoomService $roomService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'in:ACTIVE,INACTIVE'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $rooms = $this->roomService->getAll($filters);

        return response()->json([
            'message' => 'Rooms retrieved successfully.',
            'data' => RoomResource::collection($rooms->items()),
            'meta' => [
                'current_page' => $rooms->currentPage(),
                'last_page' => $rooms->lastPage(),
                'per_page' => $rooms->perPage(),
                'total' => $rooms->total(),
            ],
        ]);
    }

    public function store(StoreRoomRequest $request): JsonResponse
    {
        $room = $this->roomService->create($request->validated());

        return response()->json([
            'message' => 'Room created successfully.',
            'data' => new RoomResource($room),
        ], 201);
    }

    public function show(Room $room): JsonResponse
    {
        return response()->json([
            'message' => 'Room retrieved successfully.',
            'data' => new RoomResource($room),
        ]);
    }

    public function update(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        $room = $this->roomService->update($room, $request->validated());

        return response()->json([
            'message' => 'Room updated successfully.',
            'data' => new RoomResource($room),
        ]);
    }

    public function destroy(Room $room): JsonResponse
    {
        $this->roomService->delete($room);

        return response()->json([
            'message' => 'Room deleted successfully.',
        ]);
    }
}
