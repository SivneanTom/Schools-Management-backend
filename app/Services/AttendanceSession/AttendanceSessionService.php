<?php

namespace App\Services\AttendanceSession;

use App\Models\AttendanceSession;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\TeacherAssignment\TeacherAssignmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceSessionService
{
    public function __construct(
        private readonly TeacherAssignmentService $teacherAssignmentService
    ) {}

    public function getAll(array $filters = [], ?User $user = null): LengthAwarePaginator
    {
        $query = AttendanceSession::query()->with('teacherAssignment');

        if ($user) $query = $this->applyAccessScope($query, $user);

        $query->when($filters['teacher_assignment_id'] ?? null,
            fn(Builder $q, $id) => $q->where('teacher_assignment_id', $id));

        $query->when($filters['teacher_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas('teacherAssignment',
                fn(Builder $sub) => $sub->where('teacher_id', $id)));

        $query->when($filters['class_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas('teacherAssignment',
                fn(Builder $sub) => $sub->where('class_id', $id)));

        $query->when($filters['subject_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas('teacherAssignment',
                fn(Builder $sub) => $sub->where('subject_id', $id)));

        $query->when($filters['semester_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas('teacherAssignment',
                fn(Builder $sub) => $sub->where('semester_id', $id)));

        $query->when($filters['attendance_date'] ?? null,
            fn(Builder $q, string $date) => $q->whereDate('attendance_date', $date));

        $query->when($filters['date_from'] ?? null,
            fn(Builder $q, string $date) => $q->whereDate('attendance_date', '>=', $date));

        $query->when($filters['date_to'] ?? null,
            fn(Builder $q, string $date) => $q->whereDate('attendance_date', '<=', $date));

        $query->when($filters['status'] ?? null,
            fn(Builder $q, string $status) => $q->where('status', $status));

        return $query->orderByDesc('attendance_date')
            ->orderBy('start_time')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data, User $user): AttendanceSession
    {
        return DB::transaction(function () use ($data, $user) {
            $teacherAssignment = TeacherAssignment::findOrFail($data['teacher_assignment_id']);

            $this->teacherAssignmentService
                ->ensureTeacherOwnsAssignment($user, $teacherAssignment);

            $this->validateBusinessRules($data);

            return AttendanceSession::create($data)
                ->load('teacherAssignment');
        });
    }

    public function update(
        AttendanceSession $attendanceSession,
        array $data,
        User $user
    ): AttendanceSession {
        return DB::transaction(function () use ($attendanceSession, $data, $user) {
            $attendanceSession->loadMissing('teacherAssignment');

            $this->teacherAssignmentService
                ->ensureTeacherOwnsAssignment($user, $attendanceSession->teacherAssignment);

            if (
                array_key_exists('teacher_assignment_id', $data)
                && (int)$data['teacher_assignment_id']
                    !== (int)$attendanceSession->teacher_assignment_id
            ) {
                $newAssignment = TeacherAssignment::findOrFail($data['teacher_assignment_id']);

                $this->teacherAssignmentService
                    ->ensureTeacherOwnsAssignment($user, $newAssignment);
            }

            $merged = array_merge([
                'teacher_assignment_id' => $attendanceSession->teacher_assignment_id,
                'attendance_date' => $attendanceSession->attendance_date->format('Y-m-d'),
                'start_time' => substr((string)$attendanceSession->start_time, 0, 5),
                'end_time' => substr((string)$attendanceSession->end_time, 0, 5),
                'status' => $attendanceSession->status,
            ], $data);

            $this->validateBusinessRules($merged, $attendanceSession->id);
            $attendanceSession->update($data);

            return $attendanceSession->refresh()->load('teacherAssignment');
        });
    }

    public function delete(AttendanceSession $attendanceSession, User $user): void
    {
        DB::transaction(function () use ($attendanceSession, $user) {
            $attendanceSession->loadMissing('teacherAssignment');

            $this->teacherAssignmentService
                ->ensureTeacherOwnsAssignment($user, $attendanceSession->teacherAssignment);

            if ($attendanceSession->attendanceRecords()->exists()) {
                abort(
                    409,
                    'Cannot delete an attendance session that already has attendance records.'
                );
            }

            $attendanceSession->delete();
        });
    }

    private function validateBusinessRules(array $data, ?int $ignoreSessionId = null): void
    {
        if ($data['start_time'] >= $data['end_time']) {
            throw ValidationException::withMessages([
                'end_time' => ['The end time must be after the start time.'],
            ]);
        }

        $teacherAssignment = TeacherAssignment::findOrFail(
            $data['teacher_assignment_id']
        );

        if (
            isset($teacherAssignment->status)
            && strtoupper((string)$teacherAssignment->status) !== 'ACTIVE'
        ) {
            throw ValidationException::withMessages([
                'teacher_assignment_id' => [
                    'The selected teacher assignment is not active.',
                ],
            ]);
        }

        $conflict = AttendanceSession::query()
            ->where('teacher_assignment_id', $data['teacher_assignment_id'])
            ->whereDate('attendance_date', $data['attendance_date'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->when(
                $ignoreSessionId,
                fn(Builder $q) => $q->where('id', '!=', $ignoreSessionId)
            )
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => [
                    'An attendance session already exists for this teacher assignment during the selected time.',
                ],
            ]);
        }
    }

    public function applyAccessScope(Builder $query, User $user): Builder
    {
        $role = $user->role->code;

        if (in_array($role, ['SUPER_ADMIN', 'ADMIN'], true)) return $query;

        if ($role === 'TEACHER') {
            return $query->whereHas(
                'teacherAssignment.teacher',
                fn(Builder $q) => $q->where('user_id', $user->id)
            );
        }

        return $query->whereRaw('1 = 0');
    }
}