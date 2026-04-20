<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->tinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 2=Pending, 3=Completed, 4=Cancelled');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('hostel_id');
            $table->index('hostel_floor_id');
            $table->index('hostel_room_id');
            $table->index('hostel_assign_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_bed_assignments');
    }
};
