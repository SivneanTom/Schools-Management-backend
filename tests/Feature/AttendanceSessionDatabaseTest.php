<?php

namespace Tests\Feature;

use App\Models\AttendanceSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceSessionDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_sessions_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('attendance_sessions')
        );
    }

    public function test_attendance_sessions_columns_exist(): void
    {
        $this->assertTrue(
            Schema::hasColumns(
                'attendance_sessions',
                [
                    'id',
                    'teacher_assignment_id',
                    'attendance_date',
                    'start_time',
                    'end_time',
                    'status',
                    'created_at',
                    'updated_at',
                ]
            )
        );
    }

    public function test_attendance_session_has_teacher_assignment_relationship(): void
    {
        $session = new AttendanceSession();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $session->teacherAssignment()
        );
    }
}
