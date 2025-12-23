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
        Schema::table('student_registrations', function (Blueprint $table) {
            // Add new country_id columns
            $table->unsignedTinyInteger('permanent_country_id')->default(1)->after('permanent_address');
            $table->unsignedTinyInteger('correspondence_country_id')->default(1)->after('correspondence_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_registrations', function (Blueprint $table) {
            $table->dropColumn(['permanent_country_id', 'correspondence_country_id']);
        });
    }
};
