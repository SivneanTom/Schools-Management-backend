<?php

namespace App\Services\AttendanceSession;

use App\Models\AttendanceSession;
use App\Models\TeacherAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceSessionService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = AttendanceSession::query()
            ->with('teacherAssignment');

        $query->when(
            $filters['teacher_assignment_id'] ?? null,
            fn (Builder $query, $id) =>
                $query->where('teacher_assignment_id', $id)
        );

        $query->when(
            $filters['teacher_id'] ?? null,
            fn (Builder $query, $id) =>
                $query->whereHas(
                    'teacherAssignment',
                    fn (Builder $q) =>
                        $q->where('teacher_id', $id)
                )
        );

        $query->when(
            $filters['class_id'] ?? null,
            fn (Builder $query, $id) =>
                $query->whereHas(
                    'teacherAssignment',
                    fn (Builder $q) =>
                        $q->where('class_id', $id)
                )
        );

        $query->when(
            $filters['subject_id'] ?? null,
            fn (Builder $query, $id) =>
                $query->whereHas(
                    'teacherAssignment',
                    fn (Builder $q) =>
                        $q->where('subject_id', $id)
                )
        );

        $query->when(
            $filters['semester_id'] ?? null,
            fn (Builder $query, $id) =>
                $query->whereHas(
                    'teacherAssignment',
                    fn (Builder $q) =>
                        $q->where('semester_id', $id)
                )
        );

        $query->when(
            $filters['attendance_date'] ?? null,
            fn (Builder $query, string $date) =>
                $query->whereDate('attendance_date', $date)
        );

        $query->when(
            $filters['date_from'] ?? null,
            fn (Builder $query, string $date) =>
                $query->whereDate('attendance_date', '>=', $date)
        );

        $query->when(
            $filters['date_to'] ?? null,
            fn (Builder $query, string $date) =>
                $query->whereDate('attendance_date', '<=', $date)
        );

        $query->when(
            $filters['status'] ?? null,
            fn (Builder $query, string $status) =>
                $query->where('status', $status)
        );

        return $query
            ->orderByDesc('attendance_date')
            ->orderBy('start_time')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): AttendanceSession
    {
        return DB::transaction(function () use ($data): AttendanceSession {
            $this->validateBusinessRules($data);

            $session = AttendanceSession::create($data);

            return $session->load('teacherAssignment');
        });
    }

    public function update(
        AttendanceSession $attendanceSession,
        array $data
    ): AttendanceSession {
        return DB::transaction(
            function () use ($attendanceSession, $data): AttendanceSession {
                $merged = array_merge(
                    [
                        'teacher_assignment_id' =>
                            $attendanceSession->teacher_assignment_id,
                        'attendance_date' =>
                            $attendanceSession->attendance_date
                                ->format('Y-m-d'),
                        'start_time' =>
                            substr(
                                (string) $attendanceSession->start_time,
                                0,
                                5
                            ),
                        'end_time' =>
                            substr(
                                (string) $attendanceSession->end_time,
                                0,
                                5
                            ),
                        'status' => $attendanceSession->status,
                    ],
                    $data
                );

                $this->validateBusinessRules(
                    $merged,
                    $attendanceSession->id
                );

                $attendanceSession->update($data);

                return $attendanceSession
                    ->refresh()
                    ->load('teacherAssignment');
            }
        );
    }

    public function delete(
        AttendanceSession $attendanceSession
    ): void {
        DB::transaction(
            function () use ($attendanceSession): void {
                /*
                 * Once Step 18 exists, do not allow deleting a session
                 * that already has attendance records.
                 */
                if ($attendanceSession->attendanceRecords()->exists()) {
                    abort(
                        409,
                        'Cannot delete an attendance session that already has attendance records.'
                    );
                }

                $attendanceSession->delete();
            }
        );
    }

    private function validateBusinessRules(
        array $data,
        ?int $ignoreSessionId = null
    ): void {
        if ($data['start_time'] >= $data['end_time']) {
            throw ValidationException::withMessages([
                'end_time' => [
                    'The end time must be after the start time.',
                ],
            ]);
        }

        $teacherAssignment = TeacherAssignment::query()
            ->findOrFail($data['teacher_assignment_id']);

        if (
            isset($teacherAssignment->status) &&
            strtoupper((string) $teacherAssignment->status) !== 'ACTIVE'
        ) {
            throw ValidationException::withMessages([
                'teacher_assignment_id' => [
                    'The selected teacher assignment is not active.',
                ],
            ]);
        }

        /*
         * Prevent duplicate/overlapping attendance sessions
         * for the same teacher assignment on the same date.
         */
        $conflict = AttendanceSession::query()
            ->where(
                'teacher_assignment_id',
                $data['teacher_assignment_id']
            )
            ->whereDate(
                'attendance_date',
                $data['attendance_date']
            )
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->when(
                $ignoreSessionId,
                fn (Builder $query) =>
                    $query->where('id', '!=', $ignoreSessionId)
            )
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => [
                    'An attendance session already exists for this teacher assignment during the selected time.'
                ],
            ]);
        }
    }
}
