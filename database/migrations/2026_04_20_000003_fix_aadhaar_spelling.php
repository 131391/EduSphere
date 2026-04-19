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
            $table->renameColumn('aadhar_no', 'aadhaar_no');
            $table->renameColumn('father_aadhar_no', 'father_aadhaar_no');
            $table->renameColumn('mother_aadhar_no', 'mother_aadhaar_no');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->renameColumn('aadhar_no', 'aadhaar_no');
            $table->renameColumn('aadhar_card', 'aadhaar_card');
        });

        Schema::table('student_enquiries', function (Blueprint $table) {
            $table->renameColumn('aadhar_no', 'aadhaar_no');
        });

        Schema::table('student_registrations', function (Blueprint $table) {
            $table->renameColumn('aadhar_no', 'aadhaar_no');
            $table->renameColumn('father_aadhar_no', 'father_aadhaar_no');
            $table->renameColumn('mother_aadhar_no', 'mother_aadhaar_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->renameColumn('aadhaar_no', 'aadhar_no');
            $table->renameColumn('father_aadhaar_no', 'father_aadhar_no');
            $table->renameColumn('mother_aadhaar_no', 'mother_aadhar_no');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->renameColumn('aadhaar_no', 'aadhar_no');
            $table->renameColumn('aadhaar_card', 'aadhar_card');
        });

        Schema::table('student_enquiries', function (Blueprint $table) {
            $table->renameColumn('aadhaar_no', 'aadhar_no');
        });

        Schema::table('student_registrations', function (Blueprint $table) {
            $table->renameColumn('aadhaar_no', 'aadhar_no');
            $table->renameColumn('father_aadhaar_no', 'father_aadhar_no');
            $table->renameColumn('mother_aadhaar_no', 'mother_aadhar_no');
        });
    }
};
