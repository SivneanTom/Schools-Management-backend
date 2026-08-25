<?php

namespace App\Services\TeacherAssignment;

use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherAssignmentService
{
    public function create(array $data): TeacherAssignment
    {
        return DB::transaction(function () use ($data) {
            $assignment = TeacherAssignment::create([
                'teacher_id' => $data['teacherId'],
                'class_id' => $data['classId'],
                'subject_id' => $data['subjectId'],
                'semester_id' => $data['semesterId'],
                'assigned_at' => $data['assignedAt'],
                'status' => $data['status'] ?? 'ACTIVE',
            ]);

            return $this->load($assignment);
        });
    }

    public function update(
        TeacherAssignment $assignment,
        array $data
    ): TeacherAssignment {
        return DB::transaction(function () use ($assignment, $data) {
            if (array_key_exists('teacherId', $data)) {
                $assignment->teacher_id = $data['teacherId'];
            }

            if (array_key_exists('classId', $data)) {
                $assignment->class_id = $data['classId'];
            }

            if (array_key_exists('subjectId', $data)) {
                $assignment->subject_id = $data['subjectId'];
            }

            if (array_key_exists('semesterId', $data)) {
                $assignment->semester_id = $data['semesterId'];
            }

            if (array_key_exists('assignedAt', $data)) {
                $assignment->assigned_at = $data['assignedAt'];
            }

            if (array_key_exists('status', $data)) {
                $assignment->status = $data['status'];
            }

            $assignment->save();

            return $this->load($assignment);
        });
    }

    public function updateStatus(
        TeacherAssignment $assignment,
        string $status
    ): TeacherAssignment {
        return DB::transaction(function () use ($assignment, $status) {
            $assignment->update([
                'status' => $status,
            ]);

            return $this->load($assignment);
        });
    }

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
                'teacher',
                fn(Builder $q) =>
                $q->where('user_id', $user->id)
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public function ensureTeacherOwnsAssignment(
        User $user,
        TeacherAssignment $assignment
    ): void {
        if ($user->role->code !== 'TEACHER') {
            return;
        }

        $teacher = Teacher::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (
            (int) $assignment->teacher_id
            !== (int) $teacher->id
        ) {
            throw ValidationException::withMessages([
                'teacher_assignment_id' => [
                    'You are not assigned to this class and subject.',
                ],
            ]);
        }

        if (
            strtoupper((string) $assignment->status)
            !== 'ACTIVE'
        ) {
            throw ValidationException::withMessages([
                'teacher_assignment_id' => [
                    'This teacher assignment is not active.',
                ],
            ]);
        }
    }

    public function findAccessibleAssignment(
        User $user,
        int $assignmentId
    ): TeacherAssignment {
        $query = TeacherAssignment::query();

        $this->applyAccessScope(
            $query,
            $user
        );

        return $query
            ->whereKey($assignmentId)
            ->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | Teacher Role - Own Assignments
    |--------------------------------------------------------------------------
    |
    | Compact response only.
    | Do not return full Teacher/Class/Subject objects.
    |
    */

    public function getMyAssignments(User $user)
    {
        $teacher = Teacher::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        return TeacherAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'ACTIVE')
            ->with([
                'schoolClass.grade',
                'schoolClass.academicYear',
                'subject',
                'semester',
            ])
            ->orderBy('id')
            ->get()
            ->map(fn(TeacherAssignment $assignment) => [
                'id' => $assignment->id,
                'assignedAt' => $assignment->assigned_at,
                'status' => $assignment->status,

                'class' => $assignment->schoolClass
                    ? [
                        'id' =>
                        $assignment->schoolClass->id,
                        'nameKm' =>
                        $assignment->schoolClass->name_km,
                        'nameEn' =>
                        $assignment->schoolClass->name_en,

                        'grade' =>
                        $assignment->schoolClass->grade
                            ? [
                                'id' =>
                                $assignment->schoolClass
                                    ->grade->id,
                                'code' =>
                                $assignment->schoolClass
                                    ->grade->code,
                                'nameKm' =>
                                $assignment->schoolClass
                                    ->grade->name_km,
                                'nameEn' =>
                                $assignment->schoolClass
                                    ->grade->name_en,
                            ]
                            : null,

                        'academicYear' =>
                        $assignment->schoolClass
                            ->academicYear
                            ? [
                                'id' =>
                                $assignment->schoolClass
                                    ->academicYear->id,
                                'name' =>
                                $assignment->schoolClass
                                    ->academicYear->name,
                            ]
                            : null,
                    ]
                    : null,

                'subject' => $assignment->subject
                    ? [
                        'id' => $assignment->subject->id,
                        'code' => $assignment->subject->code,
                        'nameKm' =>
                        $assignment->subject->name_km,
                        'nameEn' =>
                        $assignment->subject->name_en,
                    ]
                    : null,

                'semester' => $assignment->semester
                    ? [
                        'id' => $assignment->semester->id,
                        'name' => $assignment->semester->name,
                    ]
                    : null,
            ]);
    }

    private function load(
        TeacherAssignment $assignment
    ): TeacherAssignment {
        return $assignment->load([
            'teacher',
            'schoolClass.grade',
            'schoolClass.academicYear',
            'subject',
            'semester.academicYear',
        ]);
    }
}
