<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $columns = [
            'father_photo' => 'distance_from_school',
            'mother_photo' => 'father_photo',
            'father_signature' => 'mother_photo',
            'mother_signature' => 'father_signature',
            'signature' => 'mother_signature'
        ];

        foreach ($columns as $column => $after) {
            if (Schema::hasColumn('students', $column)) {
                // Change to TEXT to save row space
                DB::statement("ALTER TABLE students MODIFY $column TEXT NULL");
            } else {
                // Add as TEXT
                DB::statement("ALTER TABLE students ADD $column TEXT NULL AFTER $after");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'signature'
            ]);
        });
    }
};
