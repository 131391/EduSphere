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
        Schema::table('schools', function (Blueprint $table) {
            // Drop old string columns
            $table->dropColumn(['city', 'state']);
            
            // Add new foreign key columns
            $table->unsignedInteger('city_id')->nullable()->after('address');
            $table->unsignedInteger('state_id')->nullable()->after('city_id');
            
            // Ensure country_id is unsigned integer (it was integer in original migration)
            $table->unsignedInteger('country_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['city_id', 'state_id']);
            $table->string('city')->nullable();
            $table->string('state')->nullable();
        });
    }
};
