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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('registration_no')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->tinyInteger('fuel_type')->nullable(); // 1=Diesel, 2=Petrol, 3=CNG, 4=Electric
            $table->integer('capacity')->nullable();
            $table->integer('initial_reading')->nullable();
            $table->string('engine_no')->nullable();
            $table->string('chassis_no')->nullable();
            $table->string('vehicle_type')->nullable(); // Bus, Van, Car, etc.
            $table->string('model_no')->nullable();
            $table->date('date_of_purchase')->nullable();
            $table->string('vehicle_group')->nullable();
            $table->string('imei_gps_device')->nullable();
            $table->string('tracking_url')->nullable();
            $table->year('manufacturing_year')->nullable();
            $table->date('vehicle_create_date')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('school_id');
            $table->index('registration_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
