<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;
use App\Enums\AdmissionStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert existing string values to integers
        DB::table('student_registrations')->where('admission_status', 'Pending')->update(['admission_status' => 1]);
        DB::table('student_registrations')->where('admission_status', 'Admitted')->update(['admission_status' => 2]);
        DB::table('student_registrations')->where('admission_status', 'Cancelled')->update(['admission_status' => 3]);

        // Change column type
        Schema::table('student_registrations', function (Blueprint $table) {
            $table->unsignedTinyInteger('admission_status')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_registrations', function (Blueprint $table) {
            $table->string('admission_status')->default('Pending')->change();
        });

        // Convert integers back to strings
        DB::table('student_registrations')->where('admission_status', 1)->update(['admission_status' => 'Pending']);
        DB::table('student_registrations')->where('admission_status', 2)->update(['admission_status' => 'Admitted']);
        DB::table('student_registrations')->where('admission_status', 3)->update(['admission_status' => 'Cancelled']);
    }
};
