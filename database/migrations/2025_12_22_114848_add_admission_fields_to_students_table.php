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
            // Admission Info
            $table->string('registration_no')->nullable()->after('admission_no');
            $table->string('roll_no')->nullable()->after('registration_no');
            $table->string('receipt_no')->nullable()->after('roll_no');
            $table->decimal('admission_fee', 10, 2)->nullable()->after('receipt_no');
            $table->string('referred_by')->nullable()->after('admission_fee');

            // Personal Info
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('dob_certificate_no')->nullable()->after('blood_group');
            $table->string('place_of_birth')->nullable()->after('dob_certificate_no');
            $table->string('aadhaar_no')->nullable()->after('place_of_birth');
            $table->string('nationality')->nullable()->after('aadhaar_no');
            $table->string('mother_tongue')->nullable()->after('nationality');
            $table->string('special_needs')->nullable()->after('mother_tongue');
            $table->text('remarks')->nullable()->after('special_needs');
            $table->integer('number_of_brothers')->default(0)->after('remarks');
            $table->integer('number_of_sisters')->default(0)->after('number_of_brothers');
            $table->boolean('is_single_parent')->default(false)->after('number_of_sisters');
            $table->string('corresponding_relative')->nullable()->after('is_single_parent');
            $table->boolean('transport_required')->default(false)->after('corresponding_relative');
            $table->string('bus_stop')->nullable()->after('transport_required');
            $table->string('other_stop')->nullable()->after('bus_stop');
            $table->string('boarding_type')->nullable()->after('other_stop');

            // Father Details
            $table->string('father_occupation')->nullable()->after('father_name');
            $table->string('father_organization')->nullable()->after('father_occupation');
            $table->string('father_office_address')->nullable()->after('father_organization');
            $table->string('father_qualification')->nullable()->after('father_office_address');
            $table->string('father_email')->nullable()->after('father_qualification');
            $table->string('father_mobile')->nullable()->after('father_email');
            $table->string('father_landline')->nullable()->after('father_mobile');
            $table->string('father_aadhaar')->nullable()->after('father_landline');
            $table->decimal('father_annual_income', 15, 2)->nullable()->after('father_aadhaar');
            $table->string('father_designation')->nullable()->after('father_annual_income');

            // Mother Details
            $table->string('mother_occupation')->nullable()->after('mother_name');
            $table->string('mother_organization')->nullable()->after('mother_occupation');
            $table->string('mother_office_address')->nullable()->after('mother_organization');
            $table->string('mother_qualification')->nullable()->after('mother_office_address');
            $table->string('mother_email')->nullable()->after('mother_qualification');
            $table->string('mother_mobile')->nullable()->after('mother_email');
            $table->string('mother_landline')->nullable()->after('mother_mobile');
            $table->string('mother_aadhaar')->nullable()->after('mother_landline');
            $table->decimal('mother_annual_income', 15, 2)->nullable()->after('mother_aadhaar');
            $table->string('mother_designation')->nullable()->after('mother_annual_income');

            // Address Details
            $table->text('permanent_address')->nullable()->after('address');
            $table->string('permanent_country')->nullable()->after('permanent_address');
            $table->string('permanent_state')->nullable()->after('permanent_country');
            $table->string('permanent_city')->nullable()->after('permanent_state');
            $table->string('permanent_pin')->nullable()->after('permanent_city');
            $table->string('state_of_domicile')->nullable()->after('permanent_pin');
            $table->string('latitude')->nullable()->after('state_of_domicile');
            $table->string('longitude')->nullable()->after('latitude');
            
            $table->text('correspondence_address')->nullable()->after('longitude');
            $table->string('correspondence_country')->nullable()->after('correspondence_address');
            $table->string('correspondence_state')->nullable()->after('correspondence_country');
            $table->string('correspondence_city')->nullable()->after('correspondence_state');
            $table->string('correspondence_pin')->nullable()->after('correspondence_city');
            $table->string('distance_from_school')->nullable()->after('correspondence_pin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'registration_no', 'roll_no', 'receipt_no', 'admission_fee', 'referred_by',
                'middle_name', 'dob_certificate_no', 'place_of_birth', 'aadhaar_no', 'nationality',
                'mother_tongue', 'special_needs', 'remarks', 'number_of_brothers', 'number_of_sisters',
                'is_single_parent', 'corresponding_relative', 'transport_required', 'bus_stop', 'other_stop', 'boarding_type',
                'father_occupation', 'father_organization', 'father_office_address', 'father_qualification',
                'father_email', 'father_mobile', 'father_landline', 'father_aadhaar', 'father_annual_income', 'father_designation',
                'mother_occupation', 'mother_organization', 'mother_office_address', 'mother_qualification',
                'mother_email', 'mother_mobile', 'mother_landline', 'mother_aadhaar', 'mother_annual_income', 'mother_designation',
                'permanent_address', 'permanent_country', 'permanent_state', 'permanent_city', 'permanent_pin',
                'state_of_domicile', 'latitude', 'longitude',
                'correspondence_address', 'correspondence_country', 'correspondence_state', 'correspondence_city',
                'correspondence_pin', 'distance_from_school'
            ]);
        });
    }
};
