<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('teacher_code')->unique();

            $table->string('first_name_km');
            $table->string('last_name_km');

            $table->string('first_name_en')->nullable();
            $table->string('last_name_en')->nullable();

            $table->string('gender', 20);

            $table->date('date_of_birth')->nullable();

            $table->string('phone', 30)->nullable();

            $table->text('address_km')->nullable();
            $table->text('address_en')->nullable();

            $table->date('hire_date')->nullable();

            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();

            $table->string('status', 30)
                ->default('ACTIVE');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};