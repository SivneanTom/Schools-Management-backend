<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_session_id')
                ->constrained('attendance_sessions')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained('students')
                ->restrictOnDelete();

            $table->string('status', 20);

            $table->text('remarks_km')->nullable();
            $table->text('remarks_en')->nullable();

            $table->timestamp('recorded_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['attendance_session_id', 'student_id'],
                'attendance_records_session_student_unique'
            );

            $table->index('attendance_session_id');
            $table->index('student_id');
            $table->index('status');
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
