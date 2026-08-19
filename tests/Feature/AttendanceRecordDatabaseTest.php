<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceRecordDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_records_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('attendance_records')
        );
    }

    public function test_attendance_records_columns_exist(): void
    {
        $this->assertTrue(
            Schema::hasColumns(
                'attendance_records',
                [
                    'id',
                    'attendance_session_id',
                    'student_id',
                    'status',
                    'remarks_km',
                    'remarks_en',
                    'recorded_at',
                    'created_at',
                    'updated_at',
                ]
            )
        );
    }

    public function test_attendance_record_relationships_exist(): void
    {
        $record = new AttendanceRecord();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $record->attendanceSession()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $record->student()
        );
    }

    public function test_attendance_session_has_attendance_records_relationship(): void
    {
        $session = new AttendanceSession();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $session->attendanceRecords()
        );
    }
}
