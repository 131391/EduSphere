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
        Schema::table('fees', function (Blueprint $table) {
            // Make student_id nullable to support registration fees
            $table->foreignId('student_id')->nullable()->change();
            
            // Add registration_id to link fees paid before admission
            $table->foreignId('registration_id')
                ->nullable()
                ->after('student_id')
                ->constrained('student_registrations')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['registration_id']);
            $table->dropColumn('registration_id');
            $table->foreignId('student_id')->nullable(false)->change();
        });
    }
};
