<?php

namespace App\Services\Timetable;

use App\Models\TeacherAssignment;
use App\Models\Timetable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Services\Parent\ParentService;

class TimetableService
{

    public function __construct(
        private readonly ParentService $parentService
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Timetable::query()->with(['teacherAssignment', 'room']);

        $query->when(
            $filters['teacher_assignment_id'] ?? null,
            fn(Builder $q, $id) => $q->where('teacher_assignment_id', $id)
        );

        $query->when(
            $filters['room_id'] ?? null,
            fn(Builder $q, $id) => $q->where('room_id', $id)
        );

        $query->when(
            $filters['day_of_week'] ?? null,
            fn(Builder $q, $day) => $q->where('day_of_week', $day)
        );

        $query->when(
            $filters['teacher_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas(
                'teacherAssignment',
                fn(Builder $x) => $x->where('teacher_id', $id)
            )
        );

        $query->when(
            $filters['class_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas(
                'teacherAssignment',
                fn(Builder $x) => $x->where('class_id', $id)
            )
        );

        $query->when(
            $filters['semester_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas(
                'teacherAssignment',
                fn(Builder $x) => $x->where('semester_id', $id)
            )
        );

        return $query
            ->orderByRaw("CASE day_of_week
                WHEN 'MONDAY' THEN 1
                WHEN 'TUESDAY' THEN 2
                WHEN 'WEDNESDAY' THEN 3
                WHEN 'THURSDAY' THEN 4
                WHEN 'FRIDAY' THEN 5
                WHEN 'SATURDAY' THEN 6
                WHEN 'SUNDAY' THEN 7
                ELSE 8 END")
            ->orderBy('start_time')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Timetable
    {
        return DB::transaction(function () use ($data) {
            $this->validateBusinessRules($data);
            return Timetable::create($data)->load(['teacherAssignment', 'room']);
        });
    }

    public function update(Timetable $timetable, array $data): Timetable
    {
        return DB::transaction(function () use ($timetable, $data) {
            $merged = array_merge([
                'teacher_assignment_id' => $timetable->teacher_assignment_id,
                'room_id' => $timetable->room_id,
                'day_of_week' => $timetable->day_of_week,
                'start_time' => substr((string) $timetable->start_time, 0, 5),
                'end_time' => substr((string) $timetable->end_time, 0, 5),
            ], $data);

            $this->validateBusinessRules($merged, $timetable->id);

            $timetable->update($data);
            return $timetable->refresh()->load(['teacherAssignment', 'room']);
        });
    }

    public function delete(Timetable $timetable): void
    {
        DB::transaction(fn() => $timetable->delete());
    }

    private function validateBusinessRules(array $data, ?int $ignoreId = null): void
    {
        if ($data['start_time'] >= $data['end_time']) {
            throw ValidationException::withMessages([
                'end_time' => ['The end time must be after the start time.'],
            ]);
        }

        $assignment = TeacherAssignment::findOrFail($data['teacher_assignment_id']);

        if (isset($assignment->status) && strtoupper((string) $assignment->status) !== 'ACTIVE') {
            throw ValidationException::withMessages([
                'teacher_assignment_id' => ['The selected teacher assignment is not active.'],
            ]);
        }

        $overlap = function (Builder $q) use ($data, $ignoreId) {
            return $q->where('day_of_week', $data['day_of_week'])
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->when($ignoreId, fn(Builder $x) => $x->where('id', '!=', $ignoreId));
        };

        if ($overlap(Timetable::query()->where('room_id', $data['room_id']))->exists()) {
            throw ValidationException::withMessages([
                'room_id' => ['This room is already occupied during the selected time.'],
            ]);
        }

        if ($overlap(
            Timetable::query()->whereHas(
                'teacherAssignment',
                fn(Builder $q) => $q->where('teacher_id', $assignment->teacher_id)
            )
        )->exists()) {
            throw ValidationException::withMessages([
                'teacher_assignment_id' => ['This teacher already has another class during the selected time.'],
            ]);
        }

        if ($overlap(
            Timetable::query()->whereHas(
                'teacherAssignment',
                fn(Builder $q) => $q->where('class_id', $assignment->class_id)
            )
        )->exists()) {
            throw ValidationException::withMessages([
                'teacher_assignment_id' => ['This class already has another timetable during the selected time.'],
            ]);
        }
    }
    // Parent role Read only 
    // Parent role - Read only
    public function getForParentChild(User $user, int $studentId)
    {
        if (!$this->parentService->ownsChild($user, $studentId)) {
            abort(403, 'You can only view your linked child.');
        }
        $classIds = DB::table('enrollments')
            ->where('student_id', $studentId)
            ->where('status', 'ACTIVE')
            ->pluck('class_id');

        return Timetable::query()
            ->whereHas(
                'teacherAssignment',
                fn(Builder $q) => $q->whereIn('class_id', $classIds)
            )
            ->with([
                'teacherAssignment.teacher',
                'teacherAssignment.schoolClass',
                'teacherAssignment.subject',
                'room',
            ])
            ->orderByRaw("
            CASE day_of_week
                WHEN 'MONDAY' THEN 1
                WHEN 'TUESDAY' THEN 2
                WHEN 'WEDNESDAY' THEN 3
                WHEN 'THURSDAY' THEN 4
                WHEN 'FRIDAY' THEN 5
                WHEN 'SATURDAY' THEN 6
                WHEN 'SUNDAY' THEN 7
                ELSE 8
            END
        ")
            ->orderBy('start_time')
            ->get()
            ->map(function ($timetable) {
                $assignment = $timetable->teacherAssignment;
                $teacher = $assignment?->teacher;
                $subject = $assignment?->subject;
                $schoolClass = $assignment?->schoolClass;
                $room = $timetable->room;

                return [
                    'id' => $timetable->id,
                    'dayOfWeek' => $timetable->day_of_week,
                    'startTime' => $timetable->start_time,
                    'endTime' => $timetable->end_time,

                    'subject' => $subject ? [
                        'id' => $subject->id,
                        'code' => $subject->code,
                        'nameKm' => $subject->name_km,
                        'nameEn' => $subject->name_en,
                    ] : null,

                    'teacher' => $teacher ? [
                        'id' => $teacher->id,
                        'teacherCode' => $teacher->teacher_code,
                        'fullNameKm' => trim(
                            $teacher->first_name_km . ' ' . $teacher->last_name_km
                        ),
                        'fullNameEn' => trim(
                            $teacher->first_name_en . ' ' . $teacher->last_name_en
                        ),
                    ] : null,

                    'class' => $schoolClass ? [
                        'id' => $schoolClass->id,
                        'nameKm' => $schoolClass->name_km,
                        'nameEn' => $schoolClass->name_en,
                    ] : null,

                    'room' => $room ? [
                        'id' => $room->id,
                        'nameKm' => $room->name_km,
                        'nameEn' => $room->name_en,
                        'buildingKm' => $room->building_km,
                        'buildingEn' => $room->building_en,
                    ] : null,
                ];
            });
    }
}
