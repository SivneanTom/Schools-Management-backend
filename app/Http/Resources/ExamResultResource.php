<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'exam_subject_id' => $this->exam_subject_id,

            'exam_subject' => $this->whenLoaded(
                'examSubject',
                fn() => [
                    'id' => $this->examSubject->id,
                    'exam_id' => $this->examSubject->exam_id,
                    'teacher_assignment_id' =>
                    $this->examSubject->teacher_assignment_id,
                    'exam_date' =>
                    $this->examSubject->exam_date?->format('Y-m-d'),
                    'start_time' =>
                    $this->examSubject->start_time,
                    'end_time' =>
                    $this->examSubject->end_time,
                    'max_score' =>
                    $this->examSubject->max_score,
                    'pass_score' =>
                    $this->examSubject->pass_score,
                ]
            ),

            'student_id' => $this->student_id,

            'student' => $this->whenLoaded(
                'student',
                fn() => [
                    'id' => $this->student->id,

                    'student_code' =>
                    $this->student->student_code,

                    'first_name_km' =>
                    $this->student->first_name_km,

                    'last_name_km' =>
                    $this->student->last_name_km,

                    'full_name_km' =>
                    trim(
                        ($this->student->first_name_km ?? '') . ' ' .
                            ($this->student->last_name_km ?? '')
                    ),

                    'first_name_en' =>
                    $this->student->first_name_en,

                    'last_name_en' =>
                    $this->student->last_name_en,

                    'full_name_en' =>
                    trim(
                        ($this->student->first_name_en ?? '') . ' ' .
                            ($this->student->last_name_en ?? '')
                    ),

                    'status' =>
                    $this->student->status,
                ]
            ),

            'score' => $this->score,
            'grade' => $this->grade,
            'remarks_km' => $this->remarks_km,
            'remarks_en' => $this->remarks_en,
            'published_at' =>
            $this->published_at?->toISOString(),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
