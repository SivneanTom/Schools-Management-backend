<?php

namespace App\Services\AttendanceRecord;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Services\Parent\ParentService;

class AttendanceRecordService
{
    public function __construct(
        private readonly ParentService $parentService
    ) {}

    public function getAll(
        array $filters = []
    ): LengthAwarePaginator {
        $query = AttendanceRecord::query()
            ->with([
                'attendanceSession',
                'student',
            ]);

        $query->when(
            $filters['attendance_session_id'] ?? null,
            fn(Builder $query, $id) =>
            $query->where(
                'attendance_session_id',
                $id
            )
        );

        $query->when(
            $filters['student_id'] ?? null,
            fn(Builder $query, $id) =>
            $query->where('student_id', $id)
        );

        $query->when(
            $filters['status'] ?? null,
            fn(Builder $query, string $status) =>
            $query->where('status', $status)
        );

        $query->when(
            $filters['attendance_date'] ?? null,
            fn(Builder $query, string $date) =>
            $query->whereHas(
                'attendanceSession',
                fn(Builder $q) =>
                $q->whereDate(
                    'attendance_date',
                    $date
                )
            )
        );

        $query->when(
            $filters['teacher_assignment_id'] ?? null,
            fn(Builder $query, $id) =>
            $query->whereHas(
                'attendanceSession',
                fn(Builder $q) =>
                $q->where(
                    'teacher_assignment_id',
                    $id
                )
            )
        );

        return $query
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->paginate(
                $filters['per_page'] ?? 15
            );
    }

    public function create(
        array $data
    ): AttendanceRecord {
        return DB::transaction(
            function () use ($data): AttendanceRecord {
                $this->validateBusinessRules($data);

                $data['recorded_at'] = now();

                $record =
                    AttendanceRecord::create($data);

                return $record->load([
                    'attendanceSession',
                    'student',
                ]);
            }
        );
    }

    public function update(
        AttendanceRecord $attendanceRecord,
        array $data
    ): AttendanceRecord {
        return DB::transaction(
            function () use (
                $attendanceRecord,
                $data
            ): AttendanceRecord {
                $merged = array_merge(
                    [
                        'attendance_session_id' =>
                        $attendanceRecord
                            ->attendance_session_id,

                        'student_id' =>
                        $attendanceRecord->student_id,

                        'status' =>
                        $attendanceRecord->status,

                        'remarks_km' =>
                        $attendanceRecord->remarks_km,

                        'remarks_en' =>
                        $attendanceRecord->remarks_en,
                    ],
                    $data
                );

                $this->validateBusinessRules(
                    $merged,
                    $attendanceRecord->id
                );

                /*
                 * recorded_at represents the latest time
                 * this attendance record was actually marked.
                 */
                $data['recorded_at'] = now();

                $attendanceRecord->update($data);

                return $attendanceRecord
                    ->refresh()
                    ->load([
                        'attendanceSession',
                        'student',
                    ]);
            }
        );
    }

    public function delete(
        AttendanceRecord $attendanceRecord
    ): void {
        DB::transaction(
            fn() => $attendanceRecord->delete()
        );
    }

    private function validateBusinessRules(
        array $data,
        ?int $ignoreRecordId = null
    ): void {
        $session = AttendanceSession::query()
            ->with('teacherAssignment')
            ->findOrFail(
                $data['attendance_session_id']
            );

        /*
         * Attendance can only be marked while the
         * attendance session is OPEN.
         */
        if (
            strtoupper(
                (string) $session->status
            ) !== 'OPEN'
        ) {
            throw ValidationException::withMessages([
                'attendance_session_id' => [
                    'Attendance records can only be changed while the attendance session is OPEN.',
                ],
            ]);
        }

        if (!$session->teacherAssignment) {
            throw ValidationException::withMessages([
                'attendance_session_id' => [
                    'The attendance session does not have a valid teacher assignment.',
                ],
            ]);
        }

        /*
         * Student must belong to the same class
         * as the teacher assignment for this session.
         *
         * We intentionally verify against enrollments,
         * because enrollment is the source of truth for
         * which class a student belongs to.
         */
        $studentIsInClass = DB::table('enrollments')
            ->where(
                'student_id',
                $data['student_id']
            )
            ->where(
                'class_id',
                $session
                    ->teacherAssignment
                    ->class_id
            )
            ->exists();

        if (!$studentIsInClass) {
            throw ValidationException::withMessages([
                'student_id' => [
                    'The selected student is not enrolled in the class for this attendance session.',
                ],
            ]);
        }

        /*
         * Friendly application-level duplicate check.
         * The database UNIQUE constraint is still the
         * final protection against duplicate records.
         */
        $duplicate = AttendanceRecord::query()
            ->where(
                'attendance_session_id',
                $data['attendance_session_id']
            )
            ->where(
                'student_id',
                $data['student_id']
            )
            ->when(
                $ignoreRecordId,
                fn(Builder $query) =>
                $query->where(
                    'id',
                    '!=',
                    $ignoreRecordId
                )
            )
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'student_id' => [
                    'Attendance has already been recorded for this student in this session.',
                ],
            ]);
        }
    }
    //  Parent role only
    public function getForParentChild(
        User $user,
        int $studentId
    ) {
        if (!$this->parentService->ownsChild(
            $user,
            $studentId
        )) {
            abort(
                403,
                'You can only view your linked child.'
            );
        }

        return AttendanceRecord::query()
            ->where(
                'student_id',
                $studentId
            )
            ->with([
                'attendanceSession.teacherAssignment',
            ])
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->get();
    }
}
