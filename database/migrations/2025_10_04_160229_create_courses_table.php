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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_code')->unique(); // e.g., "CSE101"
            $table->string('course_name');
            $table->text('description')->nullable();
            $table->decimal('credit_hours', 3, 1); // e.g., 3.0, 1.5
            $table->integer('intended_semester'); // Which semester this course is typically taken (1-8)
            $table->enum('course_type', ['theory', 'lab', 'theory_lab'])->default('theory');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
