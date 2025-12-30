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
        Schema::table('staff', function (Blueprint $table) {
            // Drop old string columns if they exist
            if (Schema::hasColumn('staff', 'state')) {
                $table->dropColumn('state');
            }
            if (Schema::hasColumn('staff', 'city')) {
                $table->dropColumn('city');
            }

            // Add new ID columns
            if (!Schema::hasColumn('staff', 'state_id')) {
                $table->foreignId('state_id')->nullable()->after('country_id')->constrained('states')->nullOnDelete();
            }
            if (!Schema::hasColumn('staff', 'city_id')) {
                $table->foreignId('city_id')->nullable()->after('state_id')->constrained('cities')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['state_id', 'city_id']);

            $table->string('state')->nullable();
            $table->string('city')->nullable();
        });
    }
};
