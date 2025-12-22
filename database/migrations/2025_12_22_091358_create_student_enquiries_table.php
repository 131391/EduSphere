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
        Schema::create('student_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('enquiry_no')->unique();
            $table->foreignId('academic_year_id')->nullable()->constrained();
            $table->foreignId('class_id')->nullable()->constrained('classes');
            
            // Enquiry Form
            $table->string('subject_name')->nullable();
            $table->string('student_name');
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->date('follow_up_date')->nullable();
            
            // Father's Details
            $table->string('father_name');
            $table->string('father_contact');
            $table->string('father_email')->nullable();
            $table->string('father_qualification')->nullable();
            $table->string('father_occupation')->nullable();
            $table->decimal('father_annual_income', 12, 2)->nullable();
            $table->string('father_organization')->nullable();
            $table->text('father_office_address')->nullable();
            $table->string('father_department')->nullable();
            $table->string('father_designation')->nullable();
            
            // Mother's Details
            $table->string('mother_name');
            $table->string('mother_contact');
            $table->string('mother_email')->nullable();
            $table->string('mother_qualification')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->decimal('mother_annual_income', 12, 2)->nullable();
            $table->string('mother_organization')->nullable();
            $table->text('mother_office_address')->nullable();
            $table->string('mother_department')->nullable();
            $table->string('mother_designation')->nullable();
            
            // Contact Details
            $table->string('contact_no');
            $table->string('whatsapp_no');
            $table->string('facebook_id')->nullable();
            $table->string('email_id')->nullable();
            $table->string('sms_no')->nullable();
            $table->string('twitter_id')->nullable();
            $table->string('emergency_contact_no')->nullable();
            
            // Personal Details
            $table->date('dob')->nullable();
            $table->string('aadhar_no')->nullable();
            $table->string('grand_father_name')->nullable();
            $table->decimal('annual_income', 12, 2)->nullable();
            $table->integer('no_of_brothers')->default(0);
            $table->integer('no_of_sisters')->default(0);
            $table->enum('category', ['General', 'OBC', 'SC', 'ST', 'Other'])->nullable();
            $table->enum('minority', ['Yes', 'No'])->nullable();
            $table->enum('religion', ['Hindu', 'Muslim', 'Christian', 'Sikh', 'Other'])->nullable();
            $table->enum('transport_facility', ['Yes', 'No'])->nullable();
            $table->enum('hostel_facility', ['Yes', 'No'])->nullable();
            $table->string('previous_class')->nullable();
            $table->text('identity_marks')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('previous_school_name')->nullable();
            $table->string('student_roll_no')->nullable();
            $table->string('passing_year')->nullable();
            $table->string('exam_name')->nullable();
            $table->string('board_university')->nullable();
            $table->boolean('only_child')->default(false);
            
            // Photos
            $table->string('father_photo')->nullable();
            $table->string('mother_photo')->nullable();
            $table->string('student_photo')->nullable();
            
            // Status & Dates
            $table->enum('form_status', ['pending', 'completed', 'cancelled', 'admitted'])->default('pending');
            $table->date('enquiry_date');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('enquiry_no');
            $table->index('form_status');
            $table->index('enquiry_date');
            $table->index(['school_id', 'form_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enquiries');
    }
};
