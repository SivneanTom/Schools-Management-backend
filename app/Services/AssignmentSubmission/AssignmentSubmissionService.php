<?php

namespace App\Services\AssignmentSubmission;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use App\Services\Parent\ParentService;
use App\Services\TeacherAssignment\TeacherAssignmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentSubmissionService
{
    public function __construct(
        private readonly ParentService $parentService,
        private readonly TeacherAssignmentService $teacherAssignmentService
    ) {}

    public function getAll(array $f = [], ?User $user = null): LengthAwarePaginator
    {
        $q = AssignmentSubmission::query()->with([
            'assignment.teacherAssignment.subject',
            'assignment.teacherAssignment.schoolClass',
            'student',
        ]);

        if ($user) $q = $this->applyAccessScope($q, $user);

        $q->when($f['assignment_id'] ?? null,
            fn(Builder $q, $id) => $q->where('assignment_id', $id));

        $q->when($f['student_id'] ?? null,
            fn(Builder $q, $id) => $q->where('student_id', $id));

        $q->when($f['status'] ?? null,
            fn(Builder $q, $v) => $q->where('status', $v));

        $result = $q->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate($f['per_page'] ?? 15);

        if ($user?->role->code === 'TEACHER') {
            $result->setCollection(
                $result->getCollection()->map(
                    fn($s) => $this->teacherResponse($s)
                )
            );
        }

        return $result;
    }

    public function create(array $d): AssignmentSubmission
    {
        return DB::transaction(function () use ($d) {
            $this->validateBusinessRules($d);

            $a = Assignment::findOrFail($d['assignment_id']);
            $submitted = $d['submitted_at'] ?? now();
            $d['submitted_at'] = $submitted;

            if (!isset($d['status'])) {
                $d['status'] = strtotime((string) $submitted) > $a->due_at->timestamp
                    ? 'LATE'
                    : 'SUBMITTED';
            }

            return AssignmentSubmission::create($d)
                ->load(['assignment', 'student']);
        });
    }

    public function update(
        AssignmentSubmission $s,
        array $d,
        ?User $user = null
    ): AssignmentSubmission {
        return DB::transaction(function () use ($s, $d, $user) {
            $s->loadMissing('assignment.teacherAssignment');

            if ($user && $user->role->code === 'TEACHER') {
                $this->teacherAssignmentService
                    ->ensureTeacherOwnsAssignment(
                        $user,
                        $s->assignment->teacherAssignment
                    );
            }

            $m = array_merge([
                'assignment_id' => $s->assignment_id,
                'student_id' => $s->student_id,
                'score' => $s->score,
            ], $d);

            $this->validateBusinessRules($m, $s->id);
            $s->update($d);

            return $s->refresh()->load([
                'assignment',
                'student',
            ]);
        });
    }

    public function grade(
        AssignmentSubmission $submission,
        array $data,
        User $user
    ): AssignmentSubmission {
        return DB::transaction(function () use ($submission, $data, $user) {
            $submission->loadMissing('assignment.teacherAssignment');

            $this->teacherAssignmentService
                ->ensureTeacherOwnsAssignment(
                    $user,
                    $submission->assignment->teacherAssignment
                );

            $maxScore = (float) $submission->assignment->max_score;

            if (isset($data['score']) && (float) $data['score'] > $maxScore) {
                throw ValidationException::withMessages([
                    'score' => ['The score cannot be greater than the assignment maximum score.'],
                ]);
            }

            if (isset($data['score']) && (float) $data['score'] < 0) {
                throw ValidationException::withMessages([
                    'score' => ['The score cannot be less than zero.'],
                ]);
            }

            $submission->update([
                'score' => $data['score'] ?? $submission->score,
                'feedback_km' => $data['feedback_km'] ?? $submission->feedback_km,
                'feedback_en' => $data['feedback_en'] ?? $submission->feedback_en,
                'status' => 'GRADED',
            ]);

            return $submission->refresh()->load([
                'assignment',
                'student',
            ]);
        });
    }

    public function delete(AssignmentSubmission $s): void
    {
        DB::transaction(fn() => $s->delete());
    }

    public function applyAccessScope(Builder $query, User $user): Builder
    {
        $role = $user->role->code;

        if (in_array($role, ['SUPER_ADMIN', 'ADMIN'], true)) {
            return $query;
        }

        if ($role === 'TEACHER') {
            return $query->whereHas(
                'assignment.teacherAssignment.teacher',
                fn(Builder $q) => $q->where('user_id', $user->id)
            );
        }

        if ($role === 'STUDENT') {
            return $query->whereHas(
                'student',
                fn(Builder $q) => $q->where('user_id', $user->id)
            );
        }

        return $query->whereRaw('1 = 0');
    }

    private function validateBusinessRules(array $d, ?int $ignore = null): void
    {
        $a = Assignment::with('teacherAssignment')
            ->findOrFail($d['assignment_id']);

        if (in_array(
            strtoupper((string) $a->status),
            ['DRAFT', 'CANCELLED'],
            true
        )) {
            throw ValidationException::withMessages([
                'assignment_id' => [
                    'Submissions are not allowed for a draft or cancelled assignment.',
                ],
            ]);
        }

        if (!$a->teacherAssignment) {
            throw ValidationException::withMessages([
                'assignment_id' => [
                    'The assignment does not have a valid teacher assignment.',
                ],
            ]);
        }

        $ok = DB::table('enrollments')
            ->where('student_id', $d['student_id'])
            ->where('class_id', $a->teacherAssignment->class_id)
            ->where('status', 'ACTIVE')
            ->exists();

        if (!$ok) {
            throw ValidationException::withMessages([
                'student_id' => [
                    'The selected student is not enrolled in the class for this assignment.',
                ],
            ]);
        }

        if (
            array_key_exists('score', $d)
            && $d['score'] !== null
            && (float) $d['score'] > (float) $a->max_score
        ) {
            throw ValidationException::withMessages([
                'score' => [
                    'The score cannot be greater than the assignment maximum score.',
                ],
            ]);
        }

        $dup = AssignmentSubmission::query()
            ->where('assignment_id', $d['assignment_id'])
            ->where('student_id', $d['student_id'])
            ->when(
                $ignore,
                fn(Builder $q) => $q->where('id', '!=', $ignore)
            )
            ->exists();

        if ($dup) {
            throw ValidationException::withMessages([
                'student_id' => [
                    'This student already has a submission for this assignment.',
                ],
            ]);
        }
    }

    private function teacherResponse(AssignmentSubmission $s): array
    {
        $student = $s->student;
        $assignment = $s->assignment;
        $teacherAssignment = $assignment?->teacherAssignment;
        $subject = $teacherAssignment?->subject;
        $class = $teacherAssignment?->schoolClass;

        return [
            'id' => $s->id,
            'submittedAt' => $s->submitted_at,
            'content' => $s->content,
            'fileUrl' => $s->file_url,
            'score' => $s->score,
            'feedbackKm' => $s->feedback_km,
            'feedbackEn' => $s->feedback_en,
            'status' => $s->status,

            'student' => $student ? [
                'id' => $student->id,
                'studentCode' => $student->student_code,
                'fullNameKm' => trim(
                    $student->first_name_km.' '.$student->last_name_km
                ),
                'fullNameEn' => trim(
                    $student->first_name_en.' '.$student->last_name_en
                ),
            ] : null,

            'assignment' => $assignment ? [
                'id' => $assignment->id,
                'titleKm' => $assignment->title_km,
                'titleEn' => $assignment->title_en,
                'dueAt' => $assignment->due_at,
                'maxScore' => $assignment->max_score,
            ] : null,

            'subject' => $subject ? [
                'id' => $subject->id,
                'code' => $subject->code,
                'nameKm' => $subject->name_km,
                'nameEn' => $subject->name_en,
            ] : null,

            'class' => $class ? [
                'id' => $class->id,
                'nameKm' => $class->name_km,
                'nameEn' => $class->name_en,
            ] : null,
        ];
    }

    // Parent: linked child submission history only.
    public function getForParentChild(User $user, int $studentId)
    {
        if (!$this->parentService->ownsChild($user, $studentId)) {
            abort(403, 'You can only view your linked child.');
        }

        return AssignmentSubmission::query()
            ->where('student_id', $studentId)
            ->with('assignment.teacherAssignment.subject')
            ->orderByDesc('submitted_at')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'submittedAt' => $s->submitted_at,
                'score' => $s->score,
                'feedbackKm' => $s->feedback_km,
                'feedbackEn' => $s->feedback_en,
                'status' => $s->status,

                'assignment' => [
                    'id' => $s->assignment?->id,
                    'titleKm' => $s->assignment?->title_km,
                    'titleEn' => $s->assignment?->title_en,
                    'dueAt' => $s->assignment?->due_at,
                    'maxScore' => $s->assignment?->max_score,

                    'subject' => [
                        'id' => $s->assignment?->teacherAssignment?->subject?->id,
                        'code' => $s->assignment?->teacherAssignment?->subject?->code,
                        'nameKm' => $s->assignment?->teacherAssignment?->subject?->name_km,
                        'nameEn' => $s->assignment?->teacherAssignment?->subject?->name_en,
                    ],
                ],
            ]);
    }
}