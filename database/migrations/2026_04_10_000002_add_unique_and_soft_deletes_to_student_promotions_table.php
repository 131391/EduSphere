<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_promotions', function (Blueprint $table) {
            $table->softDeletes();
            $table->unique(
                ['school_id', 'student_id', 'from_academic_year_id', 'to_academic_year_id'],
                'unique_student_promotion'
            );
        });
    }

    public function down(): void
    {
        Schema::table('student_promotions', function (Blueprint $table) {
            $table->dropUnique('unique_student_promotion');
            $table->dropSoftDeletes();
        });
    }
};
