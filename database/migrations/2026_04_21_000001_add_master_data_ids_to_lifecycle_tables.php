<?php
 
 use Illuminate\Database\Migrations\Migration;
 use Illuminate\Database\Schema\Blueprint;
 use Illuminate\Support\Facades\Schema;
 
 return new class extends Migration
 {
     /**
      * Add FK ID columns for all per-school master data across the student lifecycle.
      *
      * Strategy: add nullable *_id columns alongside the existing *name* string columns.
      * The name columns are kept as denormalized display cache (no join needed for lists).
      * On store/update we write BOTH the id and the name.
      */
     public function up(): void
     {
         // ── student_registrations ──────────────────────────────────────────────
         Schema::table('student_registrations', function (Blueprint $table) {
             $table->unsignedBigInteger('blood_group_id')->nullable()->after('blood_group');
             $table->unsignedBigInteger('religion_id')->nullable()->after('religion');
             $table->unsignedBigInteger('category_id')->nullable()->after('category');
             $table->unsignedBigInteger('student_type_id')->nullable()->after('student_type');
             $table->unsignedBigInteger('corresponding_relative_id')->nullable()->after('corresponding_relative');
             $table->unsignedBigInteger('boarding_type_id')->nullable()->after('boarding_type');
             $table->unsignedBigInteger('father_qualification_id')->nullable()->after('father_qualification');
             $table->unsignedBigInteger('mother_qualification_id')->nullable()->after('mother_qualification');
         });
 
         // ── students ──────────────────────────────────────────────────────────
         Schema::table('students', function (Blueprint $table) {
             $table->unsignedBigInteger('blood_group_id')->nullable()->after('blood_group');
             $table->unsignedBigInteger('religion_id')->nullable()->after('religion');
             $table->unsignedBigInteger('category_id')->nullable()->after('category');
             
             // student_type column is already bigint (ID) in students — rename to student_type_id for consistency
             $table->renameColumn('student_type', 'student_type_id');
             
             $table->unsignedBigInteger('corresponding_relative_id')->nullable()->after('corresponding_relative');
             $table->unsignedBigInteger('boarding_type_id')->nullable()->after('boarding_type');
             $table->unsignedBigInteger('father_qualification_id')->nullable()->after('father_qualification');
             $table->unsignedBigInteger('mother_qualification_id')->nullable()->after('mother_qualification');
         });
 
         // ── student_enquiries ─────────────────────────────────────────────────
         Schema::table('student_enquiries', function (Blueprint $table) {
             $table->unsignedBigInteger('religion_id')->nullable()->after('religion');
             $table->unsignedBigInteger('category_id')->nullable()->after('category');
             $table->unsignedBigInteger('father_qualification_id')->nullable()->after('father_qualification');
             $table->unsignedBigInteger('mother_qualification_id')->nullable()->after('mother_qualification');
         });
     }
 
     public function down(): void
     {
         Schema::table('student_enquiries', function (Blueprint $table) {
             $table->dropColumn([
                 'religion_id', 'category_id', 'father_qualification_id', 'mother_qualification_id'
             ]);
         });
 
         Schema::table('students', function (Blueprint $table) {
             $table->renameColumn('student_type_id', 'student_type');
             $table->dropColumn([
                 'blood_group_id', 'religion_id', 'category_id',
                 'corresponding_relative_id', 'boarding_type_id',
                 'father_qualification_id', 'mother_qualification_id',
             ]);
         });
 
         Schema::table('student_registrations', function (Blueprint $table) {
             $table->dropColumn([
                 'blood_group_id', 'religion_id', 'category_id', 'student_type_id',
                 'corresponding_relative_id', 'boarding_type_id',
                 'father_qualification_id', 'mother_qualification_id',
             ]);
         });
     }
 };
