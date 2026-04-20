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
        Schema::create('hostel_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('hostel_id')->constrained('hostels')->onDelete('cascade');
            $table->date('attendance_date');
            $table->boolean('is_present')->default(true);
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for performance
            $table->index('school_id');
            $table->index('student_id');
            $table->index('hostel_id');
            $table->index('attendance_date');
            $table->index('is_present');
            
            // Composite indexes for common queries
            $table->index(['attendance_date', 'hostel_id'], 'ha_date_hostel_idx');
            $table->index(['student_id', 'attendance_date'], 'ha_student_date_idx');
            
            // Unique constraint to prevent duplicate entries for same student on same date
            $table->unique(['student_id', 'attendance_date'], 'unique_student_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_attendances');
    }
};
