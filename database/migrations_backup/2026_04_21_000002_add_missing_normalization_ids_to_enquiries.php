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
        Schema::table('student_enquiries', function (Blueprint $table) {
            $table->unsignedBigInteger('student_type_id')->nullable()->after('religion_id');
            $table->unsignedBigInteger('corresponding_relative_id')->nullable()->after('student_type_id');
            $table->unsignedBigInteger('boarding_type_id')->nullable()->after('corresponding_relative_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enquiries', function (Blueprint $table) {
            $table->dropColumn(['student_type_id', 'corresponding_relative_id', 'boarding_type_id']);
        });
    }
};
