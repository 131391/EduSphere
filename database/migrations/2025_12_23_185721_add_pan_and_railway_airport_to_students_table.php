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
            $table->string('father_pan')->nullable()->after('father_aadhaar');
            $table->string('mother_pan')->nullable()->after('mother_aadhaar');
            $table->string('railway_airport')->nullable()->after('state_of_domicile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['father_pan', 'mother_pan', 'railway_airport']);
        });
    }
};
