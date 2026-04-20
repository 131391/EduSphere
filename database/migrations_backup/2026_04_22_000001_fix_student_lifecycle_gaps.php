<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add enquiry_id to students for full traceability
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('enquiry_id')->nullable()->after('registration_no');
        });

        // 2. Add payment_method_id to admission so it's not hardcoded to Cash
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('admission_payment_method_id')->nullable()->after('admission_fee');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['enquiry_id', 'admission_payment_method_id']);
        });
    }
};
