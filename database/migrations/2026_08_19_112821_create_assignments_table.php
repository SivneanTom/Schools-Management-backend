<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_assignment_id')->constrained('teacher_assignments')->restrictOnDelete();
            $table->string('title_km', 255);
            $table->string('title_en', 255)->nullable();
            $table->text('description_km')->nullable();
            $table->text('description_en')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamp('due_at');
            $table->decimal('max_score', 8, 2);
            $table->string('status', 20)->default('DRAFT');
            $table->timestamps();
            $table->index('teacher_assignment_id');
            $table->index('status');
            $table->index('assigned_at');
            $table->index('due_at');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
