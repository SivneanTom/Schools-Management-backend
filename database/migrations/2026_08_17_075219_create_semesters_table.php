<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();

            $table->string('name', 100);

            $table->date('start_date');
            $table->date('end_date');

            $table->string('status', 30)
                ->default('INACTIVE');

            $table->timestamps();

            $table->unique(
                ['academic_year_id', 'name']
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};