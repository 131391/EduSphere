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
            $table->unsignedInteger('correspondence_state_id')->nullable()->after('correspondence_country_id');
            $table->unsignedInteger('correspondence_city_id')->nullable()->after('correspondence_state_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'permanent_state_id', 
                'permanent_city_id',
                'correspondence_country_id',
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
