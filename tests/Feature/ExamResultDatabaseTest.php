<?php

namespace Tests\Feature;

use App\Models\ExamResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExamResultDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_results_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('exam_results')
        );
    }

    public function test_exam_results_columns_exist(): void
    {
        $this->assertTrue(
            Schema::hasColumns(
                'exam_results',
                [
                    'id',
                    'exam_subject_id',
                    'student_id',
                    'score',
                    'grade',
                    'remarks_km',
                    'remarks_en',
                    'published_at',
                    'created_at',
                    'updated_at',
                ]
            )
        );
    }

    public function test_exam_result_relationships_exist(): void
    {
        $result = new ExamResult();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $result->examSubject()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $result->student()
        );
    }
}
