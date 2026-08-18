<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('grade_id')
                ->constrained('grades')
                ->restrictOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->restrictOnDelete();

            $table->foreignId('homeroom_teacher_id')
                ->nullable()
                ->constrained('teachers')
                ->nullOnDelete();

            $table->string('name_km', 100);
            $table->string('name_en', 100);

            $table->unsignedInteger('capacity');

            $table->string('status', 20)
                ->default('ACTIVE');

            $table->timestamps();

            $table->unique(
                [
                    'academic_year_id',
                    'grade_id',
                    'name_km',
                ],
                'classes_year_grade_name_km_unique'
            );

            $table->unique(
                [
                    'academic_year_id',
                    'grade_id',
                    'name_en',
                ],
                'classes_year_grade_name_en_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};