<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('fee_type_id')
                ->constrained('fee_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('amount', 12, 2);
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('UNPAID');
            $table->timestamps();

            $table->unique(
                ['student_id', 'fee_type_id', 'academic_year_id'],
                'student_fees_student_fee_year_unique'
            );

            $table->index(['student_id', 'status']);
            $table->index(['academic_year_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
