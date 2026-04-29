<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('post')->comment('1=Principal, 2=Teacher, 3=Assistant, 4=Counselor, 5=Crossing guard, 6=School bus driver, 7=Food service worker');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->string('email');
            $table->integer('gender')->nullable()->comment('1=Male, 2=Female, 3=Other');
            $table->integer('total_experience')->nullable();
            $table->decimal('previous_school_salary', 10, 2)->nullable();
            $table->decimal('current_salary', 10, 2)->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('state_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->string('zip_code')->nullable();
            $table->text('address')->nullable();
            $table->string('aadhaar_no')->nullable();
            $table->string('aadhaar_card')->nullable();
            $table->string('staff_image')->nullable();
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

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
