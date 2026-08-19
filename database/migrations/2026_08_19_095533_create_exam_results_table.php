<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_subject_id')
                ->constrained('exam_subjects')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained('students')
                ->restrictOnDelete();

            $table->decimal('score', 8, 2);
            $table->string('grade', 20)->nullable();

            $table->text('remarks_km')->nullable();
            $table->text('remarks_en')->nullable();

            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['exam_subject_id', 'student_id'],
                'exam_results_subject_student_unique'
            );

            $table->index('exam_subject_id');
            $table->index('student_id');
            $table->index('grade');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
