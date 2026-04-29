<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // MySQL backs the student_id FK with the composite unique index.
            // Add the new index and drop the unique in one ALTER so the FK
            // never has a moment without a backing index.
            DB::statement('
                ALTER TABLE student_transport_assignments
                    ADD INDEX sta_student_id_idx (student_id),
                    DROP INDEX student_transport_assignments_student_id_academic_year_id_unique
            ');
        } else {
            Schema::table('student_transport_assignments', function (Blueprint $table) {
                $table->dropUnique('student_transport_assignments_student_id_academic_year_id_unique');
                $table->index('student_id', 'sta_student_id_idx');
            });
        }

        Schema::table('student_transport_assignments', function (Blueprint $table) {
            $table->index(['school_id', 'status', 'academic_year_id'], 'sta_school_status_year_idx');
            $table->index(['vehicle_id', 'academic_year_id', 'status'], 'sta_vehicle_year_status_idx');
            $table->index(['route_id', 'academic_year_id', 'status'], 'sta_route_year_status_idx');
        });

        Schema::table('transport_routes', function (Blueprint $table) {
            $table->index(['school_id', 'status'], 'tr_school_status_idx');
        });

        Schema::table('bus_stops', function (Blueprint $table) {
            $table->index(['school_id', 'route_id'], 'bs_school_route_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bus_stops', function (Blueprint $table) {
            $table->dropIndex('bs_school_route_idx');
        });

        Schema::table('transport_routes', function (Blueprint $table) {
            $table->dropIndex('tr_school_status_idx');
        });

        Schema::table('student_transport_assignments', function (Blueprint $table) {
            $table->dropIndex('sta_school_status_year_idx');
            $table->dropIndex('sta_vehicle_year_status_idx');
            $table->dropIndex('sta_route_year_status_idx');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('
                ALTER TABLE student_transport_assignments
                    ADD UNIQUE INDEX student_transport_assignments_student_id_academic_year_id_unique (student_id, academic_year_id),
                    DROP INDEX sta_student_id_idx
            ');
        } else {
            Schema::table('student_transport_assignments', function (Blueprint $table) {
                $table->dropIndex('sta_student_id_idx');
                $table->unique(['student_id', 'academic_year_id'], 'student_transport_assignments_student_id_academic_year_id_unique');
            });
        }
    }
};
