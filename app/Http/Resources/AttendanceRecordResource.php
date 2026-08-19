<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'attendance_session_id' =>
                $this->attendance_session_id,

            'attendance_session' =>
                $this->whenLoaded(
                    'attendanceSession',
                    fn () => [
                        'id' =>
                            $this->attendanceSession->id,

                        'teacher_assignment_id' =>
                            $this->attendanceSession
                                ->teacher_assignment_id,

                        'attendance_date' =>
                            $this->attendanceSession
                                ->attendance_date
                                ?->format('Y-m-d'),

                        'start_time' =>
                            $this->attendanceSession->start_time,

                        'end_time' =>
                            $this->attendanceSession->end_time,

                        'status' =>
                            $this->attendanceSession->status,
                    ]
                ),

            'student_id' => $this->student_id,

            'student' =>
                $this->whenLoaded(
                    'student',
                    fn () => [
                        'id' => $this->student->id,

                        'student_code' =>
                            $this->student->student_code ?? null,

                        'full_name_km' => trim(
                            ($this->student->first_name_km ?? '') . ' ' .
                            ($this->student->last_name_km ?? '')
                        ) ?: null,

                        'full_name_en' => trim(
                            ($this->student->first_name_en ?? '') . ' ' .
                            ($this->student->last_name_en ?? '')
                        ) ?: null,

                        'status' =>
                            $this->student->status ?? null,
                    ]
                ),

            'status' => $this->status,

            'remarks_km' => $this->remarks_km,
            'remarks_en' => $this->remarks_en,

            'recorded_at' =>
                $this->recorded_at?->toISOString(),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}