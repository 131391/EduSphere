<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('admission_no')->unique();
            
            // Admission Info (consolidated from add_admission_fields)
            $table->string('registration_no')->nullable();
            $table->string('roll_no')->nullable();
            $table->string('receipt_no')->nullable();
            $table->decimal('admission_fee', 10, 2)->nullable();
            $table->string('referred_by')->nullable();
            
            // Personal Info
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('date_of_birth');
            $table->string('dob_certificate_no')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->integer('gender')->nullable()->comment('1=Male, 2=Female, 3=Other'); // consolidated from change_gender
            $table->string('blood_group')->nullable();
            $table->string('religion')->nullable();
            $table->string('category')->nullable();
            $table->string('aadhaar_no')->nullable();
            $table->string('nationality')->nullable();
            $table->string('mother_tongue')->nullable();
            $table->string('special_needs')->nullable();
            $table->text('remarks')->nullable();
            
            // Family Info
            $table->string('father_name');
            $table->string('father_aadhaar')->nullable();
            $table->string('father_pan')->nullable(); // consolidated from add_pan_and_railway
            $table->string('father_email')->nullable();
            $table->string('father_mobile')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_qualification')->nullable();
            $table->decimal('father_annual_income', 12, 2)->nullable();

            $table->string('mother_name');
            $table->string('mother_aadhaar')->nullable();
            $table->string('mother_pan')->nullable(); // consolidated from add_pan_and_railway
            $table->string('mother_email')->nullable();
            $table->string('mother_mobile')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_qualification')->nullable();
            $table->decimal('mother_annual_income', 12, 2)->nullable();
            $table->integer('number_of_brothers')->nullable();
            $table->integer('number_of_sisters')->nullable();
            $table->boolean('is_single_parent')->default(false);
            $table->string('corresponding_relative')->nullable();
            
            // Address Info
            $table->text('address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('permanent_state')->nullable();
            $table->string('permanent_city')->nullable();
            $table->string('permanent_pin')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->unsignedTinyInteger('permanent_country_id')->default(1)->nullable(); // consolidated from add_country_id
            $table->unsignedTinyInteger('correspondence_country_id')->default(1)->nullable(); // consolidated from add_country_id
            $table->text('correspondence_address')->nullable();
            $table->string('correspondence_state')->nullable();
            $table->string('correspondence_city')->nullable();
            $table->string('correspondence_pin')->nullable();
            $table->string('correspondence_location')->nullable();
            $table->string('distance_from_school')->nullable();
            $table->string('state_of_domicile')->nullable();
            $table->string('railway_airport')->nullable(); // consolidated from add_pan_and_railway
            
            // Contact Info
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            
            // Documents
            $table->string('photo')->nullable();
            $table->string('father_photo')->nullable();
            $table->string('mother_photo')->nullable();
            $table->string('signature')->nullable(); // consolidated from add_photo_and_signature
            $table->string('father_signature')->nullable();
            $table->string('mother_signature')->nullable();
            
            // Academic Info
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->unsignedBigInteger('student_type')->nullable(); // consolidated from change_student_type (changed from ENUM to BIGINT)
            $table->tinyInteger('status')->default(1)->comment('1=Active, 2=Graduated, 3=Transferred, 4=Inactive');
            $table->date('admission_date');
            
            // Transport Info
            $table->boolean('transport_required')->default(false);
            $table->string('bus_stop')->nullable();
            $table->string('other_stop')->nullable();
            $table->string('boarding_type')->nullable();
            
            $table->json('additional_info')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('admission_no');
            $table->index('class_id');
            $table->index('section_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
