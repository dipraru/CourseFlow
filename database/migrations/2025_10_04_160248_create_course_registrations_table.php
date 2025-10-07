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
        Schema::create('course_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_course_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'advisor_approved', 'head_approved', 'rejected', 'completed'])->default('pending');
            $table->text('student_remarks')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->decimal('total_fee', 10, 2)->nullable(); // Calculated fee
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('advisor_approved_at')->nullable();
            $table->timestamp('head_approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            
            // Prevent duplicate registrations
            $table->unique(['student_id', 'semester_course_id', 'semester_id'], 'unique_student_course_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_registrations');
    }
};
