<?php

namespace App\Services\Enrollment;

use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use App\Services\Parent\ParentService;
use App\Models\User;

class EnrollmentService
{

    public function __construct(
        private readonly ParentService $parentService
    ) {}
    public function create(array $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            $enrollment = Enrollment::create([
                'student_id' => $data['studentId'],
                'class_id' => $data['classId'],
                'academic_year_id' => $data['academicYearId'],
                'enrolled_at' => $data['enrolledAt'],
                'status' => $data['status'] ?? 'ACTIVE',
            ]);

            return $this->load($enrollment);
        });
    }

    public function update(Enrollment $enrollment, array $data): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $data) {
            if (array_key_exists('studentId', $data)) {
                $enrollment->student_id = $data['studentId'];
            }
            if (array_key_exists('classId', $data)) {
                $enrollment->class_id = $data['classId'];
            }
            if (array_key_exists('academicYearId', $data)) {
                $enrollment->academic_year_id = $data['academicYearId'];
            }
            if (array_key_exists('enrolledAt', $data)) {
                $enrollment->enrolled_at = $data['enrolledAt'];
            }
            if (array_key_exists('status', $data)) {
                $enrollment->status = $data['status'];
            }

            $enrollment->save();
            return $this->load($enrollment);
        });
    }

    public function updateStatus(Enrollment $enrollment, string $status): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $status) {
            $enrollment->update(['status' => $status]);
            return $this->load($enrollment);
        });
    }

    private function load(Enrollment $enrollment): Enrollment
    {
        return $enrollment->load([
            'student',
            'schoolClass.grade',
            'schoolClass.academicYear',
            'academicYear',
        ]);
    }

    public function getForParentChild(User $user, int $studentId)
    {
        if (!$this->parentService->ownsChild($user, $studentId)) {
            abort(403, 'You can only view your linked child.');
        }

        return Enrollment::query()
            ->where('student_id', $studentId)
            ->with(['schoolClass.grade', 'academicYear'])
            ->orderByDesc('id')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'enrolledAt' => $e->enrolled_at,
                'status' => $e->status,
                'class' => [
                    'id' => $e->schoolClass?->id,
                    'nameKm' => $e->schoolClass?->name_km,
                    'nameEn' => $e->schoolClass?->name_en,
                    'grade' => [
                        'id' => $e->schoolClass?->grade?->id,
                        'code' => $e->schoolClass?->grade?->code,
                        'nameKm' => $e->schoolClass?->grade?->name_km,
                        'nameEn' => $e->schoolClass?->grade?->name_en,
                    ],
                ],
                'academicYear' => [
                    'id' => $e->academicYear?->id,
                    'name' => $e->academicYear?->name,
                ],
            ]);
    }
}
