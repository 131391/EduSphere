<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing string values to integers before changing column type
        DB::table('student_enquiries')->update([
            'transport_facility' => DB::raw("CASE WHEN transport_facility = 'Yes' THEN 1 ELSE 0 END"),
            'hostel_facility'    => DB::raw("CASE WHEN hostel_facility = 'Yes' THEN 1 ELSE 0 END"),
            'minority'           => DB::raw("CASE WHEN minority = 'Yes' THEN 1 ELSE 0 END"),
        ]);

        Schema::table('student_enquiries', function (Blueprint $table) {
            $table->tinyInteger('transport_facility')->default(0)->change();
            $table->tinyInteger('hostel_facility')->default(0)->change();
            $table->tinyInteger('minority')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_enquiries', function (Blueprint $table) {
            $table->string('transport_facility')->nullable()->change();
            $table->string('hostel_facility')->nullable()->change();
            $table->string('minority')->nullable()->change();
        });

        DB::table('student_enquiries')->update([
            'transport_facility' => DB::raw("CASE WHEN transport_facility = 1 THEN 'Yes' ELSE 'No' END"),
            'hostel_facility'    => DB::raw("CASE WHEN hostel_facility = 1 THEN 'Yes' ELSE 'No' END"),
            'minority'           => DB::raw("CASE WHEN minority = 1 THEN 'Yes' ELSE 'No' END"),
        ]);
    }
};
