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
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('address');
            $table->string('father_name')->nullable()->after('gender');
            $table->string('mother_name')->nullable()->after('father_name');
            // date_of_birth already exists in original migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['gender', 'father_name', 'mother_name']);
        });
    }
};
