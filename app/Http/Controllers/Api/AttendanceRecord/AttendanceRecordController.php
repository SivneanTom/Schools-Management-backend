<?php

namespace App\Http\Controllers\Api\AttendanceRecord;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRecord\StoreAttendanceRecordRequest;
use App\Http\Requests\AttendanceRecord\UpdateAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use App\Services\AttendanceRecord\AttendanceRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceRecordController extends Controller
{
    public function __construct(
        private readonly AttendanceRecordService $attendanceRecordService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $filters = $request->validate([
            'attendance_session_id' => [
                'nullable',
                'integer',
                'exists:attendance_sessions,id',
            ],

            'student_id' => [
                'nullable',
                'integer',
                'exists:students,id',
            ],

            'teacher_assignment_id' => [
                'nullable',
                'integer',
                'exists:teacher_assignments,id',
            ],

            'attendance_date' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'PRESENT',
                    'ABSENT',
                    'LATE',
                    'EXCUSED',
                ]),
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $records =
            $this->attendanceRecordService
                ->getAll($filters);

        return response()->json([
            'message' =>
                'Attendance records retrieved successfully.',

            'data' =>
                AttendanceRecordResource::collection(
                    $records->items()
                ),

            'meta' => [
                'current_page' =>
                    $records->currentPage(),

                'last_page' =>
                    $records->lastPage(),

                'per_page' =>
                    $records->perPage(),

                'total' =>
                    $records->total(),
            ],
        ]);
    }

    public function store(
        StoreAttendanceRecordRequest $request
    ): JsonResponse {
        $record =
            $this->attendanceRecordService
                ->create(
                    $request->validated()
                );

        return response()->json([
            'message' =>
                'Attendance record created successfully.',

            'data' =>
                new AttendanceRecordResource(
                    $record
                ),
        ], 201);
    }

    public function show(
        AttendanceRecord $attendanceRecord
    ): JsonResponse {
        $attendanceRecord->load([
            'attendanceSession',
            'student',
        ]);

        return response()->json([
            'message' =>
                'Attendance record retrieved successfully.',

            'data' =>
                new AttendanceRecordResource(
                    $attendanceRecord
                ),
        ]);
    }

    public function update(
        UpdateAttendanceRecordRequest $request,
        AttendanceRecord $attendanceRecord
    ): JsonResponse {
        $record =
            $this->attendanceRecordService
                ->update(
                    $attendanceRecord,
                    $request->validated()
                );

        return response()->json([
            'message' =>
                'Attendance record updated successfully.',

            'data' =>
                new AttendanceRecordResource(
                    $record
                ),
        ]);
    }

    public function destroy(
        AttendanceRecord $attendanceRecord
    ): JsonResponse {
        $this->attendanceRecordService
            ->delete($attendanceRecord);

        return response()->json([
            'message' =>
                'Attendance record deleted successfully.',
        ]);
    }
}
