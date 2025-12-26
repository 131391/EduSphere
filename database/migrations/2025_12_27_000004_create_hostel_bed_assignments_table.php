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
        Schema::create('hostel_bed_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('hostel_id')->constrained('hostels')->onDelete('cascade');
            $table->foreignId('hostel_floor_id')->constrained('hostel_floors')->onDelete('cascade');
            $table->foreignId('hostel_room_id')->constrained('hostel_rooms')->onDelete('cascade');
            $table->string('bed_no')->nullable();
            $table->decimal('rent', 10, 2)->nullable();
            $table->date('hostel_assign_date')->nullable();
            $table->string('starting_month')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('hostel_id');
            $table->index('hostel_floor_id');
            $table->index('hostel_room_id');
            $table->index('hostel_assign_date');
            
            // Note: Unique constraint for active assignments is handled in application logic
            // as MySQL doesn't support unique constraints with NULL values in deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_bed_assignments');
    }
};

