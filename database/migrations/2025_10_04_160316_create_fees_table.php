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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->decimal('per_credit_fee', 10, 2); // Fee per credit hour
            $table->decimal('admission_fee', 10, 2)->default(0); // One-time or recurring admission fee
            $table->decimal('library_fee', 10, 2)->default(0);
            $table->decimal('lab_fee', 10, 2)->default(0);
            $table->decimal('other_fees', 10, 2)->default(0);
            $table->text('fee_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
