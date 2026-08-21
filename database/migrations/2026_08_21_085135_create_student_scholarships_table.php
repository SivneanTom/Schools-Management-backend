<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_scholarships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('scholarship_id')
                ->constrained('scholarships')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('awarded_at');
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();

            $table->unique(
                ['student_id', 'scholarship_id', 'academic_year_id'],
                'student_scholarships_student_scholarship_year_unique'
            );

            $table->index(['student_id', 'status']);
            $table->index(['academic_year_id', 'status']);
            $table->index('awarded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_scholarships');
    }
};
