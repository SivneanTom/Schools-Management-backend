<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimetableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher_assignment_id' => $this->teacher_assignment_id,
            'teacher_assignment' => $this->whenLoaded('teacherAssignment', fn () => [
                'id' => $this->teacherAssignment->id,
                'teacher_id' => $this->teacherAssignment->teacher_id,
                'class_id' => $this->teacherAssignment->class_id,
                'subject_id' => $this->teacherAssignment->subject_id,
                'semester_id' => $this->teacherAssignment->semester_id,
                'status' => $this->teacherAssignment->status,
            ]),
            'room_id' => $this->room_id,
            'room' => $this->whenLoaded('room', fn () => [
                'id' => $this->room->id,
                'name_km' => $this->room->name_km,
                'name_en' => $this->room->name_en,
                'building_km' => $this->room->building_km,
                'building_en' => $this->room->building_en,
                'capacity' => $this->room->capacity,
                'status' => $this->room->status,
            ]),
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}