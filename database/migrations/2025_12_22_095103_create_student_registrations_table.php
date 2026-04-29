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
            $table->integer('gender')->nullable()->comment('1=Male, 2=Female, 3=Other');
            $table->date('dob')->nullable();
            $table->string('email', 150)->nullable();
            $table->string('mobile_no', 20);
            $table->unsignedBigInteger('student_type_id')->nullable();
            $table->unsignedBigInteger('blood_group_id')->nullable();
            $table->string('dob_certificate_no', 100)->nullable();
            $table->string('aadhaar_no', 20)->nullable();
            $table->string('place_of_birth', 150)->nullable();
            $table->string('nationality', 50)->default('Indian');
            $table->unsignedBigInteger('religion_id')->nullable();
            $table->string('category', 100)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('special_needs')->nullable();
            $table->string('mother_tongue', 50)->nullable();
            $table->text('remarks')->nullable();

            // Family Information
            $table->integer('number_of_brothers')->default(0);
            $table->integer('number_of_sisters')->default(0);
            $table->boolean('is_single_parent')->default(false);
            $table->unsignedBigInteger('corresponding_relative_id')->nullable();
            $table->boolean('is_transport_required')->default(false);
            $table->string('bus_stop', 150)->nullable();
            $table->string('other_stop', 150)->nullable();
            $table->unsignedBigInteger('boarding_type_id')->nullable();

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
            $table->unsignedBigInteger('father_qualification_id')->nullable();
            $table->string('father_department', 150)->nullable();
            $table->string('father_designation', 150)->nullable();
            $table->string('father_aadhaar_no', 20)->nullable();
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
            $table->unsignedBigInteger('mother_qualification_id')->nullable();
            $table->string('mother_department', 150)->nullable();
            $table->string('mother_designation', 150)->nullable();
            $table->string('mother_aadhaar_no', 20)->nullable();
            $table->decimal('mother_annual_income', 12, 2)->nullable();
            $table->integer('mother_age')->nullable();

            // Permanent Address
            $table->string('permanent_latitude', 50)->nullable();
            $table->string('permanent_longitude', 50)->nullable();
            $table->text('permanent_address');
            $table->unsignedInteger('permanent_country_id')->default(1);
            $table->unsignedInteger('permanent_state_id')->nullable();
            $table->unsignedInteger('permanent_city_id')->nullable();
            $table->string('permanent_pin', 20);
            $table->string('permanent_state_of_domicile', 100)->nullable();
            $table->string('permanent_railway_airport', 150)->nullable();
            $table->text('permanent_correspondence_address')->nullable();

            // Correspondence Address
            $table->text('correspondence_address')->nullable();
            $table->unsignedInteger('correspondence_country_id')->default(1);
            $table->unsignedInteger('correspondence_state_id')->nullable();
            $table->unsignedInteger('correspondence_city_id')->nullable();
            $table->string('correspondence_pin', 20)->nullable();
            $table->string('correspondence_location', 150)->nullable();
            $table->text('correspondence_landmark')->nullable();
            $table->string('distance_from_school', 50)->nullable();

            // Photos & Signatures
            $table->text('father_photo')->nullable();
            $table->text('mother_photo')->nullable();
            $table->text('student_photo')->nullable();
            $table->text('father_signature')->nullable();
            $table->text('mother_signature')->nullable();
            $table->text('student_signature')->nullable();

            $table->unsignedTinyInteger('admission_status')->default(1)->comment('1=Pending, 2=Admitted, 3=Cancelled');
            $table->timestamps();

            $table->index('school_id');
            $table->index('enquiry_id');
            $table->index('academic_year_id');
            $table->index('class_id');
            $table->index('registration_no');
            $table->index('registration_date');
            $table->index('admission_status');
        });

        // Add the deferred FK from fees.registration_id now that student_registrations exists
        Schema::table('fees', function (Blueprint $table) {
            $table->foreign('registration_id')
                ->references('id')
                ->on('student_registrations')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['registration_id']);
        });

        Schema::dropIfExists('student_registrations');
    }
};
