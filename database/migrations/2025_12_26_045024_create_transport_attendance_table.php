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
        Schema::create('transport_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained('transport_routes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->tinyInteger('attendance_type')->comment('1=PickupFromBusStop, 2=DropAtSchoolCampus, 3=PickupFromSchoolCampus, 4=DropAtBusStop');
            $table->boolean('is_present')->default(true);
            $table->time('time')->nullable()->comment('Actual time of pickup/drop');
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for performance
            $table->index('school_id');
            $table->index('student_id');
            $table->index('vehicle_id');
            $table->index('route_id');
            $table->index('academic_year_id');
            $table->index('attendance_date');
            $table->index('attendance_type');
            $table->index('is_present');
            
            // Composite indexes for common queries (with custom names to avoid MySQL 64-char limit)
            $table->index(['attendance_date', 'route_id', 'attendance_type'], 'ta_date_route_type_idx');
            $table->index(['student_id', 'attendance_date', 'attendance_type'], 'ta_student_date_type_idx');
            $table->index(['vehicle_id', 'attendance_date'], 'ta_vehicle_date_idx');
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['student_id', 'attendance_date', 'attendance_type'], 'unique_student_date_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_attendances');
    }
};
