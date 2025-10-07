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
        Schema::create('payment_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->string('slip_number')->unique(); // Generated slip number
            $table->decimal('total_amount', 10, 2);
            $table->decimal('credit_hours', 3, 1);
            $table->json('fee_breakdown'); // Detailed breakdown of fees
            $table->json('registered_courses'); // List of course IDs and names
            $table->enum('status', ['generated', 'downloaded'])->default('generated');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamp('downloaded_at')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_slips');
    }
};
