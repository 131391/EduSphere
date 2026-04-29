<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_transport_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained('transport_routes')->onDelete('cascade');
            $table->foreignId('bus_stop_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('fee_per_month', 10, 2);
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 2=Pending, 3=Completed, 4=Cancelled');
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_id', 'sta_student_id_idx');
            $table->index(['school_id', 'status', 'academic_year_id'], 'sta_school_status_year_idx');
            $table->index(['vehicle_id', 'academic_year_id', 'status'], 'sta_vehicle_year_status_idx');
            $table->index(['route_id', 'academic_year_id', 'status'], 'sta_route_year_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transport_assignments');
    }
};
