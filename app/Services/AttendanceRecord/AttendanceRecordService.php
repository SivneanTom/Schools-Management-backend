<?php

namespace App\Services\AttendanceRecord;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\User;
use App\Services\Parent\ParentService;
use App\Services\TeacherAssignment\TeacherAssignmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceRecordService
{
    public function __construct(
        private readonly ParentService $parentService,
        private readonly TeacherAssignmentService $teacherAssignmentService
    ) {}

    public function getAll(
        array $filters = [],
        ?User $user = null
    ): LengthAwarePaginator {
        $query = AttendanceRecord::query()->with([
            'attendanceSession',
            'student',
        ]);

        // Teacher role scope.
        if ($user) {
            $query = $this->applyAccessScope($query, $user);
        }

        $query->when(
            $filters['attendance_session_id'] ?? null,
            fn(Builder $q, $id) => $q->where('attendance_session_id', $id)
        );

        $query->when(
            $filters['student_id'] ?? null,
            fn(Builder $q, $id) => $q->where('student_id', $id)
        );

        $query->when(
            $filters['status'] ?? null,
            fn(Builder $q, string $status) => $q->where('status', $status)
        );

        $query->when(
            $filters['attendance_date'] ?? null,
            fn(Builder $q, string $date) =>
            $q->whereHas(
                'attendanceSession',
                fn(Builder $sub) =>
                $sub->whereDate('attendance_date', $date)
            )
        );

        $query->when(
            $filters['teacher_assignment_id'] ?? null,
            fn(Builder $q, $id) =>
            $q->whereHas(
                'attendanceSession',
                fn(Builder $sub) =>
                $sub->where('teacher_assignment_id', $id)
            )
        );

        return $query
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(
        array $data,
        User $user
    ): AttendanceRecord {
        return DB::transaction(function () use ($data, $user) {
            $session = AttendanceSession::query()
                ->with('teacherAssignment')
                ->findOrFail($data['attendance_session_id']);

            // Teacher role: Teacher must own the attendance session assignment.
            $this->teacherAssignmentService
                ->ensureTeacherOwnsAssignment(
                    $user,
                    $session->teacherAssignment
                );

            $this->validateBusinessRules($data);

            $data['recorded_at'] = now();

            return AttendanceRecord::create($data)->load([
                'attendanceSession',
                'student',
            ]);
        });
    }

    public function update(
        AttendanceRecord $attendanceRecord,
        array $data,
        User $user
    ): AttendanceRecord {
        return DB::transaction(function () use (
            $attendanceRecord,
            $data,
            $user
        ) {
            $attendanceRecord->loadMissing(
                'attendanceSession.teacherAssignment'
            );

            // Teacher role: Teacher may update only own class attendance.
            $this->teacherAssignmentService
                ->ensureTeacherOwnsAssignment(
                    $user,
                    $attendanceRecord
                        ->attendanceSession
                        ->teacherAssignment
                );

            $merged = array_merge([
                'attendance_session_id' =>
                $attendanceRecord->attendance_session_id,
                'student_id' =>
                $attendanceRecord->student_id,
                'status' =>
                $attendanceRecord->status,
                'remarks_km' =>
                $attendanceRecord->remarks_km,
                'remarks_en' =>
                $attendanceRecord->remarks_en,
            ], $data);

            // If attendance session changes, verify new session ownership too.
            if (
                array_key_exists('attendance_session_id', $data)
                && (int) $data['attendance_session_id']
                !== (int) $attendanceRecord->attendance_session_id
            ) {
                $newSession = AttendanceSession::query()
                    ->with('teacherAssignment')
                    ->findOrFail($data['attendance_session_id']);

                $this->teacherAssignmentService
                    ->ensureTeacherOwnsAssignment(
                        $user,
                        $newSession->teacherAssignment
                    );
            }

            $this->validateBusinessRules(
                $merged,
                $attendanceRecord->id
            );

            $data['recorded_at'] = now();

            $attendanceRecord->update($data);

            return $attendanceRecord
                ->refresh()
                ->load([
                    'attendanceSession',
                    'student',
                ]);
        });
    }

    public function delete(
        AttendanceRecord $attendanceRecord,
        User $user
    ): void {
        DB::transaction(function () use (
            $attendanceRecord,
            $user
        ) {
            $attendanceRecord->loadMissing(
                'attendanceSession.teacherAssignment'
            );

            // Teacher role: Teacher may delete only own class attendance.
            $this->teacherAssignmentService
                ->ensureTeacherOwnsAssignment(
                    $user,
                    $attendanceRecord
                        ->attendanceSession
                        ->teacherAssignment
                );

            $attendanceRecord->delete();
        });
    }

    private function validateBusinessRules(
        array $data,
        ?int $ignoreRecordId = null
    ): void {
        $session = AttendanceSession::query()
            ->with('teacherAssignment')
            ->findOrFail($data['attendance_session_id']);

        if (strtoupper((string) $session->status) !== 'OPEN') {
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

        // Student must belong to the same active class.
        $studentIsInClass = DB::table('enrollments')
            ->where('student_id', $data['student_id'])
            ->where(
                'class_id',
                $session->teacherAssignment->class_id
            )
            ->where('status', 'ACTIVE')
            ->exists();

        if (!$studentIsInClass) {
            throw ValidationException::withMessages([
                'student_id' => [
                    'The selected student is not enrolled in the class for this attendance session.',
                ],
            ]);
        }

        $duplicate = AttendanceRecord::query()
            ->where(
                'attendance_session_id',
                $data['attendance_session_id']
            )
            ->where('student_id', $data['student_id'])
            ->when(
                $ignoreRecordId,
                fn(Builder $q) =>
                $q->where('id', '!=', $ignoreRecordId)
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

    // Parent role: view only linked child's attendance.
    public function getForParentChild(
        User $user,
        int $studentId
    ) {
        if (!$this->parentService->ownsChild($user, $studentId)) {
            abort(
                403,
                'You can only view your linked child.'
            );
        }

        return AttendanceRecord::query()
            ->where('student_id', $studentId)
            ->with([
                'attendanceSession.teacherAssignment',
            ])
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->get();
    }

    // Teacher role access scope.
    public function applyAccessScope(
        Builder $query,
        User $user
    ): Builder {
        $role = $user->role->code;

        if (in_array($role, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true)) {
            return $query;
        }

        if ($role === 'TEACHER') {
            return $query->whereHas(
                'attendanceSession.teacherAssignment.teacher',
                fn(Builder $q) =>
                $q->where('user_id', $user->id)
            );
        }

        return $query->whereRaw('1 = 0');
    }
}
