<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcademicContentDatabaseTest extends TestCase
{
    use RefreshDatabase;
    public function test_all_three_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('assignments'));
        $this->assertTrue(Schema::hasTable('assignment_submissions'));
        $this->assertTrue(Schema::hasTable('learning_materials'));
    }
    public function test_assignments_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('assignments', ['id', 'teacher_assignment_id', 'title_km', 'title_en', 'description_km', 'description_en', 'assigned_at', 'due_at', 'max_score', 'status', 'created_at', 'updated_at']));
    }
    public function test_assignment_submissions_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('assignment_submissions', ['id', 'assignment_id', 'student_id', 'submitted_at', 'content', 'file_url', 'score', 'feedback_km', 'feedback_en', 'status', 'created_at', 'updated_at']));
    }
    public function test_learning_materials_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('learning_materials', ['id', 'teacher_assignment_id', 'title_km', 'title_en', 'description_km', 'description_en', 'material_type', 'file_url', 'published_at', 'created_at', 'updated_at']));
    }
    public function test_main_relationships_exist(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Assignment())->teacherAssignment());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, (new Assignment())->submissions());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new AssignmentSubmission())->assignment());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new AssignmentSubmission())->student());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new LearningMaterial())->teacherAssignment());
    }
}
