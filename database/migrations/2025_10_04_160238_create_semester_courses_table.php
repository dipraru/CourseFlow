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
        Schema::create('semester_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->integer('max_students')->nullable(); // Maximum students allowed
            $table->integer('enrolled_students')->default(0); // Current enrolled count
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate course offerings in same semester
            $table->unique(['semester_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semester_courses');
    }
};
