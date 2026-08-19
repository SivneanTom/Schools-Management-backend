<?php

namespace App\Http\Controllers\Api\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\StoreTimetableRequest;
use App\Http\Requests\Timetable\UpdateTimetableRequest;
use App\Http\Resources\TimetableResource;
use App\Models\Timetable;
use App\Services\Timetable\TimetableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TimetableController extends Controller
{
    public function __construct(private readonly TimetableService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'teacher_assignment_id' => ['nullable', 'integer', 'exists:teacher_assignments,id'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'day_of_week' => [
                'nullable',
                'string',
                Rule::in(['MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY']),
            ],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $rows = $this->service->getAll($filters);

        return response()->json([
            'message' => 'Timetables retrieved successfully.',
            'data' => TimetableResource::collection($rows->items()),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ],
        ]);
    }

    public function store(StoreTimetableRequest $request): JsonResponse
    {
        $row = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Timetable created successfully.',
            'data' => new TimetableResource($row),
        ], 201);
    }

    public function show(Timetable $timetable): JsonResponse
    {
        $timetable->load(['teacherAssignment', 'room']);

        return response()->json([
            'message' => 'Timetable retrieved successfully.',
            'data' => new TimetableResource($timetable),
        ]);
    }

    public function update(UpdateTimetableRequest $request, Timetable $timetable): JsonResponse
    {
        $row = $this->service->update($timetable, $request->validated());

        return response()->json([
            'message' => 'Timetable updated successfully.',
            'data' => new TimetableResource($row),
        ]);
    }

    public function destroy(Timetable $timetable): JsonResponse
    {
        $this->service->delete($timetable);

        return response()->json([
            'message' => 'Timetable deleted successfully.',
        ]);
    }
}
