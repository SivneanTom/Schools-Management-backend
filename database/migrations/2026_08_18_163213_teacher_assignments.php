<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->restrictOnDelete();

            $table->foreignId('class_id')
                ->constrained('classes')
                ->restrictOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->restrictOnDelete();

            $table->foreignId('semester_id')
                ->constrained('semesters')
                ->restrictOnDelete();

            $table->date('assigned_at');
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();

            $table->unique(
                ['teacher_id', 'class_id', 'subject_id', 'semester_id'],
                'teacher_assignments_unique'
            );

            $table->index(['class_id', 'semester_id', 'status']);
            $table->index(['teacher_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
    }
};
