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
        // Students table indexes
        Schema::table('students', function (Blueprint $table) {
            $table->index(['school_id', 'status'], 'idx_students_school_status');
            $table->index(['school_id', 'class_id', 'section_id'], 'idx_students_school_class_section');
            $table->index(['school_id', 'admission_no'], 'idx_students_school_admission');
            $table->index(['school_id', 'mobile_no'], 'idx_students_school_mobile');
        });

        // Fees table indexes
        Schema::table('fees', function (Blueprint $table) {
            $table->index(['school_id', 'student_id'], 'idx_fees_school_student');
            $table->index(['school_id', 'academic_year_id', 'payment_status'], 'idx_fees_school_year_status');
            $table->index(['school_id', 'bill_no'], 'idx_fees_school_bill');
        });

        // Fee payments table indexes
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->index(['school_id', 'student_id'], 'idx_fee_payments_school_student');
            $table->index(['school_id', 'receipt_no'], 'idx_fee_payments_school_receipt');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['school_id', 'email'], 'idx_users_school_email');
            $table->index(['school_id', 'role_id'], 'idx_users_school_role');
        });

        // Attendance table indexes
        Schema::table('attendance', function (Blueprint $table) {
            $table->index(['school_id', 'student_id', 'date'], 'idx_attendance_school_student_date');
        });

        // Results table indexes
        Schema::table('results', function (Blueprint $table) {
            $table->index(['school_id', 'student_id', 'exam_id'], 'idx_results_school_student_exam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_school_status');
            $table->dropIndex('idx_students_school_class_section');
            $table->dropIndex('idx_students_school_admission');
            $table->dropIndex('idx_students_school_mobile');
        });

        Schema::table('fees', function (Blueprint $table) {
            $table->dropIndex('idx_fees_school_student');
            $table->dropIndex('idx_fees_school_year_status');
            $table->dropIndex('idx_fees_school_bill');
        });

        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropIndex('idx_fee_payments_school_student');
            $table->dropIndex('idx_fee_payments_school_receipt');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_school_email');
            $table->dropIndex('idx_users_school_role');
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropIndex('idx_attendance_school_student_date');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex('idx_results_school_student_exam');
        });
    }
};
