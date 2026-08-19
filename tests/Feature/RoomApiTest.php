<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomApiTest extends TestCase
{
    use RefreshDatabase;

    /*
     * NOTE:
     * Your project protects these endpoints with Sanctum + role middleware.
     * If your User factory already supports roles, authenticate a SUPER_ADMIN,
     * ADMIN or PRINCIPAL before running write tests.
     *
     * These tests focus on room validation/model behavior and may need the
     * authentication setup copied from your existing protected API tests.
     */

    public function test_room_model_can_be_created(): void
    {
        $room = Room::create([
            'name_km' => 'បន្ទប់ ១០១',
            'name_en' => 'Room 101',
            'building_km' => 'អគារ A',
            'building_en' => 'Building A',
            'capacity' => 40,
            'status' => 'ACTIVE',
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name_en' => 'Room 101',
            'capacity' => 40,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_room_status_defaults_to_active(): void
    {
        $room = Room::create([
            'name_km' => 'បន្ទប់ ១០២',
            'capacity' => 30,
        ]);

        $this->assertSame('ACTIVE', $room->status);
    }
}
