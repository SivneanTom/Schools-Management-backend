<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TimetableDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_timetables_table_and_columns_exist(): void
    {
        $this->assertTrue(Schema::hasTable('timetables'));

        $this->assertTrue(Schema::hasColumns('timetables', [
            'id',
            'teacher_assignment_id',
            'room_id',
            'day_of_week',
            'start_time',
            'end_time',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_room_has_timetables_relationship(): void
    {
        $room = new Room();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $room->timetables()
        );
    }
}
