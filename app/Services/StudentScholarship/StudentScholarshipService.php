<?php

namespace App\Services\StudentScholarship;

use App\Models\AcademicYear;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\StudentScholarship;
use App\Models\User;
use App\Services\Parent\ParentService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentScholarshipService
{
    public function __construct(
        private readonly ParentService $parentService
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = StudentScholarship::query()
            ->with([
                'student',
                'scholarship',
                'academicYear',
            ]);

        if (!empty($filters['student_id'])) {
            $query->where(
                'student_id',
                $filters['student_id']
            );
        }

        if (!empty($filters['scholarship_id'])) {
            $query->where(
                'scholarship_id',
                $filters['scholarship_id']
            );
        }

        if (!empty($filters['academic_year_id'])) {
            $query->where(
                'academic_year_id',
                $filters['academic_year_id']
            );
        }

        if (!empty($filters['status'])) {
            $query->where(
                'status',
                strtoupper($filters['status'])
            );
        }

        return $query
            ->latest('id')
            ->paginate(
                (int) ($filters['per_page'] ?? 20)
            );
    }

    public function create(array $data): StudentScholarship
    {
        return DB::transaction(function () use ($data) {

            $student = Student::findOrFail(
                $data['student_id']
            );

            if ($student->status !== 'ACTIVE') {
                throw ValidationException::withMessages([
                    'student_id' =>
                        'The selected student is inactive.',
                ]);
            }

            AcademicYear::findOrFail(
                $data['academic_year_id']
            );

            $scholarship = Scholarship::findOrFail(
                $data['scholarship_id']
            );

            if (!$scholarship->is_active) {
                throw ValidationException::withMessages([
                    'scholarship_id' =>
                        'The selected scholarship is inactive.',
                ]);
            }

            $awardedAt = !empty($data['awarded_at'])
                ? Carbon::parse($data['awarded_at'])
                : now();

            $this->validateAwardDate(
                $scholarship,
                $awardedAt
            );

            $this->ensureUniqueAssignment(
                $data['student_id'],
                $data['scholarship_id'],
                $data['academic_year_id']
            );

            $data['awarded_at'] =
                $awardedAt->toDateString();

            $data['status'] =
                $data['status'] ?? 'ACTIVE';

            $studentScholarship =
                StudentScholarship::create($data);

            return $studentScholarship->load([
                'student',
                'scholarship',
                'academicYear',
            ]);
        });
    }

    public function update(
        StudentScholarship $studentScholarship,
        array $data
    ): StudentScholarship {
        return DB::transaction(function () use (
            $studentScholarship,
            $data
        ) {
            $studentId =
                $data['student_id']
                ?? $studentScholarship->student_id;

            $scholarshipId =
                $data['scholarship_id']
                ?? $studentScholarship->scholarship_id;

            $academicYearId =
                $data['academic_year_id']
                ?? $studentScholarship->academic_year_id;

            $student = Student::findOrFail(
                $studentId
            );

            if ($student->status !== 'ACTIVE') {
                throw ValidationException::withMessages([
                    'student_id' =>
                        'The selected student is inactive.',
                ]);
            }

            AcademicYear::findOrFail(
                $academicYearId
            );

            $scholarship = Scholarship::findOrFail(
                $scholarshipId
            );

            if (
                isset($data['scholarship_id'])
                && !$scholarship->is_active
            ) {
                throw ValidationException::withMessages([
                    'scholarship_id' =>
                        'The selected scholarship is inactive.',
                ]);
            }

            $awardedAt = array_key_exists(
                'awarded_at',
                $data
            )
                ? Carbon::parse($data['awarded_at'])
                : Carbon::parse(
                    $studentScholarship->awarded_at
                );

            $this->validateAwardDate(
                $scholarship,
                $awardedAt
            );

            $this->ensureUniqueAssignment(
                $studentId,
                $scholarshipId,
                $academicYearId,
                $studentScholarship->id
            );

            if (array_key_exists('awarded_at', $data)) {
                $data['awarded_at'] =
                    $awardedAt->toDateString();
            }

            $studentScholarship->update($data);

            return $studentScholarship
                ->refresh()
                ->load([
                    'student',
                    'scholarship',
                    'academicYear',
                ]);
        });
    }

    public function delete(
        StudentScholarship $studentScholarship
    ): void {
        if ($studentScholarship->status === 'ACTIVE') {
            throw ValidationException::withMessages([
                'student_scholarship' =>
                    'An active student scholarship should not be deleted. Cancel it first.',
            ]);
        }

        $studentScholarship->delete();
    }

    private function validateAwardDate(
        Scholarship $scholarship,
        Carbon $awardedAt
    ): void {
        if (
            $scholarship->start_date
            && $awardedAt->lt(
                Carbon::parse($scholarship->start_date)
                    ->startOfDay()
            )
        ) {
            throw ValidationException::withMessages([
                'awarded_at' =>
                    'Award date cannot be before the scholarship start date.',
            ]);
        }

        if (
            $scholarship->end_date
            && $awardedAt->gt(
                Carbon::parse($scholarship->end_date)
                    ->endOfDay()
            )
        ) {
            throw ValidationException::withMessages([
                'awarded_at' =>
                    'Award date cannot be after the scholarship end date.',
            ]);
        }
    }

    private function ensureUniqueAssignment(
        int $studentId,
        int $scholarshipId,
        int $academicYearId,
        ?int $ignoreId = null
    ): void {
        $query = StudentScholarship::query()
            ->where(
                'student_id',
                $studentId
            )
            ->where(
                'scholarship_id',
                $scholarshipId
            )
            ->where(
                'academic_year_id',
                $academicYearId
            );

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'student_scholarship' =>
                    'This scholarship is already assigned to this student for the selected academic year.',
            ]);
        }
    }

    // Parent role only
    public function getForParentChild(
        User $user,
        int $studentId
    ) {
        if (
            !$this->parentService->ownsChild(
                $user,
                $studentId
            )
        ) {
            abort(
                403,
                'You can only view scholarships for your linked child.'
            );
        }

        return StudentScholarship::query()
            ->where(
                'student_id',
                $studentId
            )
            ->with([
                'scholarship',
                'academicYear',
            ])
            ->latest('id')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'awardedAt' => $item->awarded_at,
                'status' => $item->status,

                'scholarship' => [
                    'id' =>
                        $item->scholarship?->id,
                    'nameKm' =>
                        $item->scholarship?->name_km,
                    'nameEn' =>
                        $item->scholarship?->name_en,
                    'discountType' =>
                        $item->scholarship?->discount_type,
                    'discountValue' =>
                        $item->scholarship?->discount_value,
                ],

                'academicYear' => [
                    'id' =>
                        $item->academicYear?->id,
                    'name' =>
                        $item->academicYear?->name,
                ],
            ]);
    }
}