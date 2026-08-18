<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_subjects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('grade_id')
                ->constrained('grades')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Grade-specific weekly/default hours. This can override the
            // generic/default value stored on subjects.credit_hours.
            $table->unsignedInteger('credit_hours');

            $table->boolean('is_required')->default(true);

            $table->timestamps();

            $table->unique(
                ['grade_id', 'subject_id'],
                'grade_subjects_grade_subject_unique'
            );

            $table->index('is_required');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_subjects');
    }
};
