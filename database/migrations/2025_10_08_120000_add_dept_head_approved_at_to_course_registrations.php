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
        Schema::table('course_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('course_registrations', 'dept_head_approved_at')) {
                $table->timestamp('dept_head_approved_at')->nullable()->after('advisor_approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('course_registrations', 'dept_head_approved_at')) {
                $table->dropColumn('dept_head_approved_at');
            }
        });
    }
};
