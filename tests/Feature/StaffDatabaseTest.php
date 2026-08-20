<?php

namespace Tests\Feature;

use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('staff'));
    }

    public function test_staff_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('staff', [
            'id',
            'user_id',
            'staff_no',
            'full_name_km',
            'full_name_en',
            'phone',
            'hire_date',
            'position_title_km',
            'position_title_en',
            'status',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_staff_user_relationship_exists(): void
    {
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            (new Staff())->user()
        );
    }
}
