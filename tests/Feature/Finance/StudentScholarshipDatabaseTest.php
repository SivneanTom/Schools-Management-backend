<?php

namespace Tests\Feature\Finance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentScholarshipDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_step_27_and_28_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('scholarships'));
        $this->assertTrue(Schema::hasTable('student_scholarships'));

        $this->assertTrue(Schema::hasColumns('scholarships', [
            'id',
            'name_km',
            'name_en',
            'description_km',
            'description_en',
            'discount_type',
            'discount_value',
            'start_date',
            'end_date',
            'is_active',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasColumns('student_scholarships', [
            'id',
            'student_id',
            'scholarship_id',
            'academic_year_id',
            'awarded_at',
            'status',
            'created_at',
            'updated_at',
        ]));
    }
}
