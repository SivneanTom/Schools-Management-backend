<?php

namespace App\Services\GradeSubject;

use App\Models\GradeSubject;
use Illuminate\Support\Facades\DB;

class GradeSubjectService
{
    public function create(array $data): GradeSubject
    {
        return DB::transaction(function () use ($data) {
            $gradeSubject = GradeSubject::create([
                'grade_id' => $data['gradeId'],
                'subject_id' => $data['subjectId'],
                'credit_hours' => $data['creditHours'],
                'is_required' => $data['isRequired'] ?? true,
            ]);

            return $gradeSubject->load([
                'grade',
                'subject',
            ]);
        });
    }

    public function update(
        GradeSubject $gradeSubject,
        array $data
    ): GradeSubject {
        return DB::transaction(function () use (
            $gradeSubject,
            $data
        ) {
            if (array_key_exists('gradeId', $data)) {
                $gradeSubject->grade_id = $data['gradeId'];
            }

            if (array_key_exists('subjectId', $data)) {
                $gradeSubject->subject_id = $data['subjectId'];
            }

            if (array_key_exists('creditHours', $data)) {
                $gradeSubject->credit_hours = $data['creditHours'];
            }

            if (array_key_exists('isRequired', $data)) {
                $gradeSubject->is_required = $data['isRequired'];
            }

            $gradeSubject->save();

            return $gradeSubject->load([
                'grade',
                'subject',
            ]);
        });
    }

    public function delete(GradeSubject $gradeSubject): void
    {
        DB::transaction(function () use ($gradeSubject) {
            $gradeSubject->delete();
        });
    }
}
