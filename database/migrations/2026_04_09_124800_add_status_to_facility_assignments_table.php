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
        Schema::table('student_transport_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('student_transport_assignments', 'status')) {
                $table->tinyInteger('status')->default(1)->after('academic_year_id')->comment('1=Active, 0=Inactive, 2=Pending, 3=Completed, 4=Cancelled');
            }
        });

        Schema::table('hostel_bed_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('hostel_bed_assignments', 'status')) {
                $table->tinyInteger('status')->default(1)->after('starting_month')->comment('1=Active, 0=Inactive, 2=Pending, 3=Completed, 4=Cancelled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_transport_assignments', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('hostel_bed_assignments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
