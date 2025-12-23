<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Constants\Gender;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, convert existing string values to integers in all tables
        
        // Students table
        DB::table('students')->where('gender', 'Male')->update(['gender' => Gender::MALE]);
        DB::table('students')->where('gender', 'Female')->update(['gender' => Gender::FEMALE]);
        DB::table('students')->where('gender', 'Other')->update(['gender' => Gender::OTHER]);
        DB::table('students')->where('gender', 'male')->update(['gender' => Gender::MALE]);
        DB::table('students')->where('gender', 'female')->update(['gender' => Gender::FEMALE]);
        DB::table('students')->where('gender', 'other')->update(['gender' => Gender::OTHER]);
        
        // Student Registrations table
        DB::table('student_registrations')->where('gender', 'Male')->update(['gender' => Gender::MALE]);
        DB::table('student_registrations')->where('gender', 'Female')->update(['gender' => Gender::FEMALE]);
        DB::table('student_registrations')->where('gender', 'Other')->update(['gender' => Gender::OTHER]);
        DB::table('student_registrations')->where('gender', 'male')->update(['gender' => Gender::MALE]);
        DB::table('student_registrations')->where('gender', 'female')->update(['gender' => Gender::FEMALE]);
        DB::table('student_registrations')->where('gender', 'other')->update(['gender' => Gender::OTHER]);
        
        // Student Enquiries table
        DB::table('student_enquiries')->where('gender', 'Male')->update(['gender' => Gender::MALE]);
        DB::table('student_enquiries')->where('gender', 'Female')->update(['gender' => Gender::FEMALE]);
        DB::table('student_enquiries')->where('gender', 'Other')->update(['gender' => Gender::OTHER]);
        DB::table('student_enquiries')->where('gender', 'male')->update(['gender' => Gender::MALE]);
        DB::table('student_enquiries')->where('gender', 'female')->update(['gender' => Gender::FEMALE]);
        DB::table('student_enquiries')->where('gender', 'other')->update(['gender' => Gender::OTHER]);
        
        // Now change the column type to integer
        Schema::table('students', function (Blueprint $table) {
            $table->integer('gender')->nullable()->change();
        });
        
        Schema::table('student_registrations', function (Blueprint $table) {
            $table->integer('gender')->nullable()->change();
        });
        
        Schema::table('student_enquiries', function (Blueprint $table) {
            $table->integer('gender')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change column type back to string
        Schema::table('students', function (Blueprint $table) {
            $table->string('gender')->nullable()->change();
        });
        
        Schema::table('student_registrations', function (Blueprint $table) {
            $table->string('gender')->nullable()->change();
        });
        
        Schema::table('student_enquiries', function (Blueprint $table) {
            $table->string('gender')->nullable()->change();
        });
        
        // Convert integers back to strings
        DB::table('students')->where('gender', Gender::MALE)->update(['gender' => 'Male']);
        DB::table('students')->where('gender', Gender::FEMALE)->update(['gender' => 'Female']);
        DB::table('students')->where('gender', Gender::OTHER)->update(['gender' => 'Other']);
        
        DB::table('student_registrations')->where('gender', Gender::MALE)->update(['gender' => 'Male']);
        DB::table('student_registrations')->where('gender', Gender::FEMALE)->update(['gender' => 'Female']);
        DB::table('student_registrations')->where('gender', Gender::OTHER)->update(['gender' => 'Other']);
        
        DB::table('student_enquiries')->where('gender', Gender::MALE)->update(['gender' => 'Male']);
        DB::table('student_enquiries')->where('gender', Gender::FEMALE)->update(['gender' => 'Female']);
        DB::table('student_enquiries')->where('gender', Gender::OTHER)->update(['gender' => 'Other']);
    }
};
