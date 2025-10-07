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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Fall 2024", "Spring 2025"
            $table->enum('type', ['Spring', 'Summer', 'Fall']); // Semester type
            $table->year('year');
            $table->integer('semester_number'); // 1, 2, 3, etc.
            $table->date('registration_start_date');
            $table->date('registration_end_date');
            $table->date('semester_start_date');
            $table->date('semester_end_date');
            $table->boolean('is_active')->default(false); // Only one active semester for registration
            $table->boolean('is_current')->default(false); // Current running semester
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
