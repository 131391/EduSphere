<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_registrations', function (Blueprint $table) {
            $table->id();
            
            // Registration Form Information
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('enquiry_id')->nullable()->constrained('student_enquiries')->onDelete('set null');
            $table->string('registration_no', 50)->unique();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->decimal('registration_fee', 10, 2)->nullable();
            $table->date('registration_date');
            
            // Personal Information
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->integer('gender')->nullable()->comment('1=Male, 2=Female, 3=Other'); // consolidated from change_gender
            $table->date('dob')->nullable();
            $table->string('email', 150)->nullable();
            $table->string('mobile_no', 20);
            $table->string('student_type', 100)->nullable();
            $table->string('blood_group', 20)->nullable();
            $table->string('dob_certificate_no', 100)->nullable();
            $table->string('aadhar_no', 20)->nullable();
            $table->string('place_of_birth', 150)->nullable();
            $table->string('nationality', 50)->default('Indian');
            $table->string('religion', 50)->nullable();
            $table->enum('category', ['General', 'OBC', 'SC', 'ST', 'Other'])->nullable();
            $table->text('special_needs')->nullable();
            $table->string('mother_tongue', 50)->nullable();
            $table->text('remarks')->nullable();
            
            // Family Information
            $table->integer('number_of_brothers')->default(0);
            $table->integer('number_of_sisters')->default(0);
            $table->boolean('is_single_parent')->default(false);
            $table->string('corresponding_relative', 100)->nullable();
            $table->boolean('is_transport_required')->default(false);
            $table->string('bus_stop', 150)->nullable();
            $table->string('other_stop', 150)->nullable();
            $table->string('boarding_type', 100)->nullable();
            
            // Father's Details
            $table->string('father_name_prefix', 20)->nullable();
            $table->string('father_first_name', 100);
            $table->string('father_middle_name', 100)->nullable();
            $table->string('father_last_name', 100);
            $table->string('father_email', 150)->nullable();
            $table->string('father_mobile_no', 20);
            $table->string('father_landline_no', 20)->nullable();
            $table->string('father_occupation', 150)->nullable();
            $table->string('father_organization', 150)->nullable();
            $table->text('father_office_address')->nullable();
            $table->text('father_qualification')->nullable();
            $table->string('father_department', 150)->nullable();
            $table->string('father_designation', 150)->nullable();
            $table->string('father_aadhar_no', 20)->nullable();
            $table->decimal('father_annual_income', 12, 2)->nullable();
            $table->integer('father_age')->nullable();
            
            // Mother's Details
            $table->string('mother_name_prefix', 20)->nullable();
            $table->string('mother_first_name', 100);
            $table->string('mother_middle_name', 100)->nullable();
            $table->string('mother_last_name', 100);
            $table->string('mother_email', 150)->nullable();
            $table->string('mother_mobile_no', 20);
            $table->string('mother_landline_no', 20)->nullable();
            $table->string('mother_occupation', 150)->nullable();
            $table->string('mother_organization', 150)->nullable();
            $table->text('mother_office_address')->nullable();
            $table->text('mother_qualification')->nullable();
            $table->string('mother_department', 150)->nullable();
            $table->string('mother_designation', 150)->nullable();
            $table->string('mother_aadhar_no', 20)->nullable();
            $table->decimal('mother_annual_income', 12, 2)->nullable();
            $table->integer('mother_age')->nullable();
            
            // Permanent Address
            $table->string('permanent_latitude', 50)->nullable();
            $table->string('permanent_longitude', 50)->nullable();
            $table->text('permanent_address');
            $table->unsignedTinyInteger('permanent_country_id')->default(1); // consolidated from add_country_id_fields
            $table->string('permanent_country', 100)->default('India');
            $table->string('permanent_state', 100);
            $table->string('permanent_city', 100);
            $table->string('permanent_pin', 20);
            $table->string('permanent_state_of_domicile', 100)->nullable();
            $table->string('permanent_railway_airport', 150)->nullable();
            $table->text('permanent_correspondence_address')->nullable();
            
            // Correspondence Address
            $table->text('correspondence_address')->nullable();
            $table->unsignedTinyInteger('correspondence_country_id')->default(1); // consolidated from add_country_id_fields
            $table->string('correspondence_country', 100)->nullable();
            $table->string('correspondence_state', 100)->nullable();
            $table->string('correspondence_city', 100)->nullable();
            $table->string('correspondence_pin', 20)->nullable();
            $table->string('correspondence_location', 150)->nullable();
            $table->text('correspondence_landmark')->nullable();
            $table->string('distance_from_school', 50)->nullable();
            
            // Photo Details
            $table->text('father_photo')->nullable();
            $table->text('mother_photo')->nullable();
            $table->text('student_photo')->nullable();
            
            // Signature Details
            $table->text('father_signature')->nullable();
            $table->text('mother_signature')->nullable();
            $table->text('student_signature')->nullable();
            
            // Status & Metadata
            $table->unsignedTinyInteger('admission_status')->default(1)->comment('1=Pending, 2=Admitted, 3=Cancelled'); // consolidated from change_admission_status
            $table->timestamps();
            
            // Indexes
            $table->index('school_id');
            $table->index('enquiry_id');
            $table->index('academic_year_id');
            $table->index('class_id');
            $table->index('registration_no');
            $table->index('registration_date');
            $table->index('admission_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_registrations');
    }
};
