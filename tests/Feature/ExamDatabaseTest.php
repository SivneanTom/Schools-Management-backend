<?php
namespace Tests\Feature;

use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExamDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_exams_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('exams'));
    }

    public function test_exams_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('exams',[
            'id','semester_id','name_km','name_en','exam_type',
            'start_date','end_date','status','created_at','updated_at'
        ]));
    }

    public function test_exam_has_semester_relationship(): void
    {
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            (new Exam())->semester()
        );
    }
}
