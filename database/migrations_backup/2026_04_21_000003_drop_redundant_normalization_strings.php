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
        $tables = ['student_enquiries', 'student_registrations', 'students'];
        $columns = [
            'blood_group',
            'religion',
            'category',
            'student_type',
            'corresponding_relative',
            'boarding_type',
            'father_qualification',
            'mother_qualification'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                // Filter to only drop columns that exist in the current table
                $colsToDrop = array_filter($columns, function($col) use ($table) {
                    return Schema::hasColumn($table->getTable(), $col);
                });
                
                if (!empty($colsToDrop)) {
                    $table->dropColumn($colsToDrop);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-adding as nullable strings for safety in case of rollback
        $tables = ['student_enquiries', 'student_registrations', 'students'];
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('blood_group')->nullable();
                $table->string('religion')->nullable();
                $table->string('category')->nullable();
                $table->string('student_type')->nullable();
                $table->string('corresponding_relative')->nullable();
                $table->string('boarding_type')->nullable();
                $table->string('father_qualification')->nullable();
                $table->string('mother_qualification')->nullable();
            });
        }
    }
};
