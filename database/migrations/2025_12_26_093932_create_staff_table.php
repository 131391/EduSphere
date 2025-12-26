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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->integer('post')->comment('1=Principal, 2=Teacher, 3=Assistant, 4=Counselor, 5=Crossing guard, 6=School bus driver, 7=Food service worker');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->integer('gender')->nullable()->comment('1=Male, 2=Female, 3=Other');
            $table->integer('total_experience')->nullable()->comment('Total experience in years');
            $table->decimal('previous_school_salary', 10, 2)->nullable()->comment('Previous school salary per month');
            $table->decimal('current_salary', 10, 2)->nullable()->comment('Current salary per month');
            $table->integer('country_id')->nullable()->comment('Country ID from config');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->text('address')->nullable();
            $table->string('aadhar_no')->nullable();
            $table->string('aadhar_card')->nullable()->comment('Aadhar card file path');
            $table->string('staff_image')->nullable()->comment('Staff photo file path');
            $table->date('joining_date')->nullable();
            $table->foreignId('higher_qualification_id')->nullable()->constrained('qualifications')->onDelete('set null');
            $table->string('previous_school_company_name')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('post');
            $table->index('class_id');
            $table->index('section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
