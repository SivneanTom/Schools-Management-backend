<?php

namespace App\Http\Controllers\Api\AttendanceSession;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceSession\StoreAttendanceSessionRequest;
use App\Http\Requests\AttendanceSession\UpdateAttendanceSessionRequest;
use App\Http\Resources\AttendanceSessionResource;
use App\Models\AttendanceSession;
use App\Services\AttendanceSession\AttendanceSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceSessionController extends Controller
{
    public function __construct(
        private readonly AttendanceSessionService $attendanceSessionService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'teacher_assignment_id' => [
                'nullable',
                'integer',
                'exists:teacher_assignments,id',
            ],

            'teacher_id' => [
                'nullable',
                'integer',
                'exists:teachers,id',
            ],

            'class_id' => [
                'nullable',
                'integer',
                'exists:classes,id',
            ],

            'subject_id' => [
                'nullable',
                'integer',
                'exists:subjects,id',
            ],

            'semester_id' => [
                'nullable',
                'integer',
                'exists:semesters,id',
            ],

            'attendance_date' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'date_from' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'OPEN',
                    'CLOSED',
                    'CANCELLED',
                ]),
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $sessions =
            $this->attendanceSessionService->getAll($filters);

        return response()->json([
            'message' =>
                'Attendance sessions retrieved successfully.',

            'data' =>
                AttendanceSessionResource::collection(
                    $sessions->items()
                ),

            'meta' => [
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
            ],
        ]);
    }

    public function store(
        StoreAttendanceSessionRequest $request
    ): JsonResponse {
        $session =
            $this->attendanceSessionService->create(
                $request->validated()
            );

        return response()->json([
            'message' =>
                'Attendance session created successfully.',

            'data' =>
                new AttendanceSessionResource($session),
        ], 201);
    }

    public function show(
        AttendanceSession $attendanceSession
    ): JsonResponse {
        $attendanceSession->load('teacherAssignment');

        return response()->json([
            'message' =>
                'Attendance session retrieved successfully.',

            'data' =>
                new AttendanceSessionResource(
                    $attendanceSession
                ),
        ]);
    }

    public function update(
        UpdateAttendanceSessionRequest $request,
        AttendanceSession $attendanceSession
    ): JsonResponse {
        $session =
            $this->attendanceSessionService->update(
                $attendanceSession,
                $request->validated()
            );

        return response()->json([
            'message' =>
                'Attendance session updated successfully.',

            'data' =>
                new AttendanceSessionResource($session),
        ]);
    }

    public function destroy(
        AttendanceSession $attendanceSession
    ): JsonResponse {
        $this->attendanceSessionService->delete(
            $attendanceSession
        );

        return response()->json([
            'message' =>
                'Attendance session deleted successfully.',
        ]);
    }
}
