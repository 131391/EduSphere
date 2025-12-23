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
            // Change ENUM to UNSIGNED BIGINT (for foreign key compatibility)
            // We use raw SQL because doctrine/dbal might not be installed
            DB::statement("ALTER TABLE students MODIFY COLUMN student_type BIGINT UNSIGNED DEFAULT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Revert back to ENUM if needed
            // DB::statement("ALTER TABLE students MODIFY COLUMN student_type ENUM('regular', 'boarding', 'day_scholar') DEFAULT 'regular'");
        });
    }
};
