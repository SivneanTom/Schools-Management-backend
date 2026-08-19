<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->string('name_km', 255);
            $table->string('name_en', 255)->nullable();
            $table->string('exam_type', 30);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('DRAFT');
            $table->timestamps();
            $table->index('semester_id');
            $table->index('exam_type');
            $table->index('status');
            $table->index(['start_date','end_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('exams'); }
};
