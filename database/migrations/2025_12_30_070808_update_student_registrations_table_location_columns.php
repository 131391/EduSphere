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
            // Drop old string columns
            $table->dropColumn([
                'permanent_state', 
                'permanent_city',
                'correspondence_state',
                'correspondence_city'
            ]);
            
            // Add new foreign key columns
            $table->unsignedInteger('permanent_state_id')->nullable()->after('permanent_country_id');
            $table->unsignedInteger('permanent_city_id')->nullable()->after('permanent_state_id');
            
            $table->unsignedInteger('correspondence_state_id')->nullable()->after('correspondence_country_id');
            $table->unsignedInteger('correspondence_city_id')->nullable()->after('correspondence_state_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'permanent_state_id', 
                'permanent_city_id',
                'correspondence_state_id',
                'correspondence_city_id'
            ]);
            
            $table->string('permanent_state')->nullable();
            $table->string('permanent_city')->nullable();
            $table->string('correspondence_state')->nullable();
            $table->string('correspondence_city')->nullable();
        });
    }
};
