<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->unique(
                ['school_id', 'student_id', 'academic_year_id', 'fee_type_id', 'fee_name_id', 'fee_period'],
                'uq_fees_student_period'
            );
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropUnique('uq_fees_student_period');
        });
    }
};
