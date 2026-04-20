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
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('hostel_id')->constrained('hostels')->onDelete('cascade');
            $table->foreignId('hostel_floor_id')->constrained('hostel_floors')->onDelete('cascade');
            $table->string('room_name');
            $table->tinyInteger('ac')->default(0)->comment('0=No, 1=Yes');
            $table->tinyInteger('cooler')->default(0)->comment('0=No, 1=Yes');
            $table->tinyInteger('fan')->default(0)->comment('0=No, 1=Yes');
            $table->date('room_create_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('hostel_id');
            $table->index('hostel_floor_id');
            $table->index('room_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_rooms');
    }
};

