<?php

namespace Tests\Feature;

use App\Models\ExamSubject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExamSubjectDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_subjects_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('exam_subjects'));
    }

    public function test_exam_subjects_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('exam_subjects', [
            'id',
            'exam_id',
            'teacher_assignment_id',
            'exam_date',
            'start_time',
            'end_time',
            'max_score',
            'pass_score',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_exam_subject_relationships_exist(): void
    {
        $model = new ExamSubject();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->exam()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->teacherAssignment()
        );
    }
}
