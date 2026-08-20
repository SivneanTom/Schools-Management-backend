<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'assignment' => $this->whenLoaded('assignment', function () {
                return [
                    'id' => $this->assignment->id,
                    'teacher_assignment_id' => $this->assignment->teacher_assignment_id,
                    'title_km' => $this->assignment->title_km,
                    'title_en' => $this->assignment->title_en,
                    'due_at' => $this->assignment->due_at?->toISOString(),
                    'max_score' => $this->assignment->max_score,
                    'status' => $this->assignment->status
                ];
            }),
            'student_id' => $this->student_id,
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'student_code' => $this->student->student_code,

                    'full_name_km' => trim(
                        $this->student->first_name_km . ' ' .
                            $this->student->last_name_km
                    ),

                    'full_name_en' => trim(
                        $this->student->first_name_en . ' ' .
                            $this->student->last_name_en
                    ),
                ];
            }),
            'submitted_at' => $this->submitted_at?->toISOString(),
            'content' => $this->content,
            'file_url' => $this->file_url,
            'score' => $this->score,
            'feedback_km' => $this->feedback_km,
            'feedback_en' => $this->feedback_en,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
