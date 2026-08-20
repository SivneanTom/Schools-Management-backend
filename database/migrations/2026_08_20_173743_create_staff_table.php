<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('staff_no', 50)->unique();
            $table->string('full_name_km', 255);
            $table->string('full_name_en', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('hire_date')->nullable();
            $table->string('position_title_km', 255)->nullable();
            $table->string('position_title_en', 255)->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->timestamps();

            $table->index('status');
            $table->index('hire_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
