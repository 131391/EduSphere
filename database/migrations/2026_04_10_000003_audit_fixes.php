<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Unique receipt_no per school on fee_payments
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->unique(['school_id', 'receipt_no'], 'unique_school_receipt_no');
        });

        // 2. SoftDeletes on results
        Schema::table('results', function (Blueprint $table) {
            $table->softDeletes();
        });

        // 3. SoftDeletes on attendance (unique constraint already exists in original migration)
        Schema::table('attendance', function (Blueprint $table) {
            $table->softDeletes();
        });

        // 4. start_date / end_date on student_transport_assignments (used by facility controller)
        Schema::table('student_transport_assignments', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('academic_year_id');
            $table->date('end_date')->nullable()->after('start_date');
        });

        // 5. start_date / end_date on hostel_bed_assignments (used by facility controller)
        Schema::table('hostel_bed_assignments', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('status');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropUnique('unique_school_receipt_no');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('student_transport_assignments', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });

        Schema::table('hostel_bed_assignments', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
