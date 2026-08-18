<?php

namespace App\Services\TeacherAssignment;

use App\Models\TeacherAssignment;
use Illuminate\Support\Facades\DB;

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

    public function update(TeacherAssignment $assignment, array $data): TeacherAssignment
    {
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

    public function updateStatus(TeacherAssignment $assignment, string $status): TeacherAssignment
    {
        return DB::transaction(function () use ($assignment, $status) {
            $assignment->update(['status' => $status]);
            return $this->load($assignment);
        });
    }

    private function load(TeacherAssignment $assignment): TeacherAssignment
    {
        return $assignment->load([
            'teacher',
            'schoolClass.grade',
            'schoolClass.academicYear',
            'subject',
            'semester.academicYear',
        ]);
    }
}
