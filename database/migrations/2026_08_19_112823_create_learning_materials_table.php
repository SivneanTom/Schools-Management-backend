<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('learning_materials', function(Blueprint $table){
  $table->id();
  $table->foreignId('teacher_assignment_id')->constrained('teacher_assignments')->restrictOnDelete();
  $table->string('title_km',255); $table->string('title_en',255)->nullable();
  $table->text('description_km')->nullable(); $table->text('description_en')->nullable();
  $table->string('material_type',30); $table->text('file_url')->nullable(); $table->timestamp('published_at')->nullable();
  $table->timestamps(); $table->index('teacher_assignment_id'); $table->index('material_type'); $table->index('published_at');
 }); }
 public function down(): void { Schema::dropIfExists('learning_materials'); }
};
