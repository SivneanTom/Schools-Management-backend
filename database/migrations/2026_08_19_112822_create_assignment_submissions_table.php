<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assignment_id')
                ->constrained('assignments')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained('students')
                ->restrictOnDelete();

            $table->timestamp('submitted_at')->nullable();

            $table->text('content')->nullable();
            $table->text('file_url')->nullable();

            $table->decimal('score', 8, 2)->nullable();

            $table->text('feedback_km')->nullable();
            $table->text('feedback_en')->nullable();

            $table->string('status', 20)
                ->default('SUBMITTED');

            $table->timestamps();

            $table->unique(
                ['assignment_id', 'student_id'],
                'assignment_submissions_assignment_student_unique'
            );

            $table->index('assignment_id');
            $table->index('student_id');
            $table->index('status');
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};