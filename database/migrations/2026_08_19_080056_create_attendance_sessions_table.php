<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_assignment_id')
                ->constrained('teacher_assignments')
                ->restrictOnDelete();

            $table->date('attendance_date');
            $table->time('start_time');
            $table->time('end_time');

            $table->string('status', 20)->default('OPEN');

            $table->timestamps();

            $table->index('teacher_assignment_id');
            $table->index('attendance_date');
            $table->index('status');

            $table->index([
                'teacher_assignment_id',
                'attendance_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
