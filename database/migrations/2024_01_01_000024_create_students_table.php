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
            $table->string('admission_no');

            // Admission Info
            $table->unsignedBigInteger('enquiry_id')->nullable();
            $table->string('registration_no')->nullable();
            $table->string('roll_no')->nullable();
            $table->string('receipt_no')->nullable();
            $table->decimal('admission_fee', 10, 2)->nullable();
            $table->unsignedBigInteger('admission_payment_method_id')->nullable();
            $table->string('referred_by')->nullable();

            // Personal Info
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('dob');
            $table->string('dob_certificate_no')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->integer('gender')->nullable()->comment('1=Male, 2=Female, 3=Other');
            $table->unsignedBigInteger('blood_group_id')->nullable();
            $table->unsignedBigInteger('religion_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('aadhaar_no')->nullable();
            $table->string('nationality')->nullable();
            $table->string('mother_tongue')->nullable();
            $table->string('special_needs')->nullable();
            $table->text('remarks')->nullable();

            // Family Info
            $table->string('father_name');
            $table->string('father_aadhaar_no')->nullable();
            $table->string('father_pan')->nullable();
            $table->string('father_email')->nullable();
            $table->string('father_mobile_no')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_qualification')->nullable();
            $table->unsignedBigInteger('father_qualification_id')->nullable();
            $table->decimal('father_annual_income', 12, 2)->nullable();

            $table->string('mother_name');
            $table->string('mother_aadhaar_no')->nullable();
            $table->string('mother_pan')->nullable();
            $table->string('mother_email')->nullable();
            $table->string('mother_mobile_no')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_qualification')->nullable();
            $table->unsignedBigInteger('mother_qualification_id')->nullable();
            $table->decimal('mother_annual_income', 12, 2)->nullable();
            $table->integer('number_of_brothers')->nullable();
            $table->integer('number_of_sisters')->nullable();
            $table->boolean('is_single_parent')->default(false);
            $table->unsignedBigInteger('corresponding_relative_id')->nullable();

            // Address Info
            $table->text('address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->unsignedInteger('permanent_state_id')->nullable();
            $table->unsignedInteger('permanent_city_id')->nullable();
            $table->string('permanent_pin')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->unsignedInteger('permanent_country_id')->default(1)->nullable();
            $table->unsignedInteger('correspondence_country_id')->default(1)->nullable();
            $table->text('correspondence_address')->nullable();
            $table->unsignedInteger('correspondence_state_id')->nullable();
            $table->unsignedInteger('correspondence_city_id')->nullable();
            $table->string('correspondence_pin')->nullable();
            $table->string('correspondence_location')->nullable();
            $table->string('distance_from_school')->nullable();
            $table->string('state_of_domicile')->nullable();
            $table->string('railway_airport')->nullable();

            // Contact Info
            $table->string('mobile_no')->nullable();
            $table->string('email')->nullable();

            // Documents
            $table->string('student_photo')->nullable();
            $table->string('father_photo')->nullable();
            $table->string('mother_photo')->nullable();
            $table->string('student_signature')->nullable();
            $table->string('father_signature')->nullable();
            $table->string('mother_signature')->nullable();

            // Academic Info
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->unsignedBigInteger('student_type_id')->nullable();
            $table->unsignedBigInteger('boarding_type_id')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Active, 2=Graduated, 3=Transferred, 4=Inactive');
            $table->date('admission_date');

            // Transport Info
            $table->boolean('is_transport_required')->default(false);
            $table->string('bus_stop')->nullable();
            $table->string('other_stop')->nullable();

            $table->json('additional_info')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Per-school unique admission number
            $table->unique(['school_id', 'admission_no'], 'students_school_admission_no_unique');
            $table->index('school_id');
            $table->index('admission_no');
            $table->index('class_id');
            $table->index('section_id');
            $table->index('status');
            $table->index(['school_id', 'status'], 'idx_students_school_status');
            $table->index(['school_id', 'class_id', 'section_id'], 'idx_students_school_class_section');
            $table->index(['school_id', 'admission_no'], 'idx_students_school_admission');
            $table->index(['school_id', 'mobile_no'], 'idx_students_school_mobile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
