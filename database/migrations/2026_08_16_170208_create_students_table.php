<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->foreignID('user_id')
                  ->unique()
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->string('student_code')->unique();

            $table->string('first_name_km');
            $table->string('last_name_km');

            $table->string('first_name_en')->nullable();
            $table->string('last_name_en')->nullable();

            $table->string('gender' , 20);

            $table->date('date_of_birth')->nullable();

            $table->string('phone', 30)->nullable();

            $table->text('address_km')->nullable();

            $table->text('address_en')->nullable();

            $table->date('admission_date')->nullable();

            $table->string('status',30)
                  ->default('ACTIVE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
