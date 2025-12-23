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
        Schema::table('students', function (Blueprint $table) {
            // Add country_id columns for both permanent and correspondence addresses
            $table->unsignedTinyInteger('permanent_country_id')->default(1)->nullable();
            $table->unsignedTinyInteger('correspondence_country_id')->default(1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['permanent_country_id', 'correspondence_country_id']);
        });
    }
};
