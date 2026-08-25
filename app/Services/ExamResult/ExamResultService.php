<?php

namespace App\Services\ExamResult;

use App\Models\ExamResult;
use App\Models\ExamSubject;
use App\Models\User;
use App\Services\Parent\ParentService;
use App\Services\TeacherAssignment\TeacherAssignmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamResultService
{
    public function __construct(
        private readonly ParentService $parentService,
        private readonly TeacherAssignmentService $teacherAssignmentService
    ) {}

    public function getAll(array $filters = [], ?User $user = null): LengthAwarePaginator
    {
        $query = ExamResult::query()->with(['examSubject', 'student']);

        if ($user) {
            $query = $this->applyAccessScope($query, $user);
        }

        $query->when(
            $filters['exam_subject_id'] ?? null,
            fn(Builder $q, $id) => $q->where('exam_subject_id', $id)
        );

        $query->when(
            $filters['student_id'] ?? null,
            fn(Builder $q, $id) => $q->where('student_id', $id)
        );

        $query->when(
            $filters['exam_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas(
                'examSubject',
                fn(Builder $sub) => $sub->where('exam_id', $id)
            )
        );

        $query->when(
            $filters['grade'] ?? null,
            fn(Builder $q, string $grade) => $q->where('grade', $grade)
        );

        $query->when(
            array_key_exists('published', $filters),
            function (Builder $q) use ($filters): void {
                $filters['published']
                    ? $q->whereNotNull('published_at')
                    : $q->whereNull('published_at');
            }
        );

        return $query
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data, User $user): ExamResult
    {
        return DB::transaction(function () use ($data, $user) {
            $examSubject = ExamSubject::query()
                ->with('teacherAssignment')
                ->findOrFail($data['exam_subject_id']);

            $this->teacherAssignmentService->ensureTeacherOwnsAssignment(
                $user,
                $examSubject->teacherAssignment
            );

            $this->validateBusinessRules($data);

            return ExamResult::create($data)->load([
                'examSubject',
                'student',
            ]);
        });
    }

    public function update(
        ExamResult $examResult,
        array $data,
        User $user
    ): ExamResult {
        return DB::transaction(function () use ($examResult, $data, $user) {
            $examResult->loadMissing('examSubject.teacherAssignment');

            $this->teacherAssignmentService->ensureTeacherOwnsAssignment(
                $user,
                $examResult->examSubject->teacherAssignment
            );

            if (
                array_key_exists('exam_subject_id', $data)
                && (int) $data['exam_subject_id'] !== (int) $examResult->exam_subject_id
            ) {
                $newExamSubject = ExamSubject::query()
                    ->with('teacherAssignment')
                    ->findOrFail($data['exam_subject_id']);

                $this->teacherAssignmentService->ensureTeacherOwnsAssignment(
                    $user,
                    $newExamSubject->teacherAssignment
                );
            }

            $merged = array_merge([
                'exam_subject_id' => $examResult->exam_subject_id,
                'student_id' => $examResult->student_id,
                'score' => $examResult->score,
                'grade' => $examResult->grade,
                'remarks_km' => $examResult->remarks_km,
                'remarks_en' => $examResult->remarks_en,
                'published_at' => $examResult->published_at,
            ], $data);

            $this->validateBusinessRules($merged, $examResult->id);

            $examResult->update($data);

            return $examResult->refresh()->load([
                'examSubject',
                'student',
            ]);
        });
    }

    public function publish(
        ExamResult $examResult,
        User $user
    ): ExamResult {
        return DB::transaction(function () use ($examResult, $user) {
            $examResult->loadMissing('examSubject.teacherAssignment');

            $this->teacherAssignmentService->ensureTeacherOwnsAssignment(
                $user,
                $examResult->examSubject->teacherAssignment
            );

            $examResult->update([
                'published_at' => now(),
            ]);

            return $examResult->refresh()->load([
                'examSubject',
                'student',
            ]);
        });
    }

    public function unpublish(
        ExamResult $examResult,
        User $user
    ): ExamResult {
        return DB::transaction(function () use ($examResult, $user) {
            $examResult->loadMissing('examSubject.teacherAssignment');

            $this->teacherAssignmentService->ensureTeacherOwnsAssignment(
                $user,
                $examResult->examSubject->teacherAssignment
            );

            $examResult->update([
                'published_at' => null,
            ]);

            return $examResult->refresh()->load([
                'examSubject',
                'student',
            ]);
        });
    }

    public function delete(
        ExamResult $examResult,
        User $user
    ): void {
        DB::transaction(function () use ($examResult, $user) {
            $examResult->loadMissing('examSubject.teacherAssignment');

            $this->teacherAssignmentService->ensureTeacherOwnsAssignment(
                $user,
                $examResult->examSubject->teacherAssignment
            );

            $examResult->delete();
        });
    }

    private function validateBusinessRules(
        array $data,
        ?int $ignoreResultId = null
    ): void {
        $examSubject = ExamSubject::query()
            ->with('teacherAssignment')
            ->findOrFail($data['exam_subject_id']);

        if ((float) $data['score'] < 0) {
            throw ValidationException::withMessages([
                'score' => ['The score cannot be less than zero.'],
            ]);
        }

        if ((float) $data['score'] > (float) $examSubject->max_score) {
            throw ValidationException::withMessages([
                'score' => [
                    'The score cannot be greater than the maximum score for this exam subject.',
                ],
            ]);
        }

        if (!$examSubject->teacherAssignment) {
            throw ValidationException::withMessages([
                'exam_subject_id' => [
                    'The exam subject does not have a valid teacher assignment.',
                ],
            ]);
        }

        $studentIsInClass = DB::table('enrollments')
            ->where('student_id', $data['student_id'])
            ->where('class_id', $examSubject->teacherAssignment->class_id)
            ->where('status', 'ACTIVE')
            ->exists();

        if (!$studentIsInClass) {
            throw ValidationException::withMessages([
                'student_id' => [
                    'The selected student is not enrolled in the class for this exam subject.',
                ],
            ]);
        }

        $duplicate = ExamResult::query()
            ->where('exam_subject_id', $data['exam_subject_id'])
            ->where('student_id', $data['student_id'])
            ->when(
                $ignoreResultId,
                fn(Builder $q) => $q->where('id', '!=', $ignoreResultId)
            )
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'student_id' => [
                    'A result already exists for this student in this exam subject.',
                ],
            ]);
        }
    }

    // Parent role: linked child's published results only.
    public function getForParentChild(User $user, int $studentId)
    {
        if (!$this->parentService->ownsChild($user, $studentId)) {
            abort(
                403,
                'You can only view results for your linked child.'
            );
        }

        return ExamResult::query()
            ->where('student_id', $studentId)
            ->whereNotNull('published_at')
            ->with([
                'examSubject.exam',
                'examSubject.teacherAssignment.subject',
            ])
            ->orderByDesc('published_at')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'score' => $r->score,
                'grade' => $r->grade,
                'remarksKm' => $r->remarks_km,
                'remarksEn' => $r->remarks_en,
                'publishedAt' => $r->published_at,
                'examSubject' => [
                    'examDate' => $r->examSubject?->exam_date,
                    'maxScore' => $r->examSubject?->max_score,
                    'passScore' => $r->examSubject?->pass_score,
                    'subject' => [
                        'id' => $r->examSubject?->teacherAssignment?->subject?->id,
                        'code' => $r->examSubject?->teacherAssignment?->subject?->code,
                        'nameKm' => $r->examSubject?->teacherAssignment?->subject?->name_km,
                        'nameEn' => $r->examSubject?->teacherAssignment?->subject?->name_en,
                    ],
                    'exam' => [
                        'id' => $r->examSubject?->exam?->id,
                        'nameKm' => $r->examSubject?->exam?->name_km,
                        'nameEn' => $r->examSubject?->exam?->name_en,
                        'examType' => $r->examSubject?->exam?->exam_type,
                    ],
                ],
            ]);
    }

    // Teacher role: own assignment only.
    public function applyAccessScope(
        Builder $query,
        User $user
    ): Builder {
        $role = $user->role->code;

        if (in_array($role, ['SUPER_ADMIN', 'ADMIN'], true)) {
            return $query;
        }

        if ($role === 'TEACHER') {
            return $query->whereHas(
                'examSubject.teacherAssignment.teacher',
                fn(Builder $q) => $q->where('user_id', $user->id)
            );
        }

        return $query->whereRaw('1 = 0');
    }
}