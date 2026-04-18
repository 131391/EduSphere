<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('from_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('to_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('from_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('to_class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('from_section_id')->constrained('sections')->onDelete('cascade');
            $table->foreignId('to_section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->tinyInteger('result')->default(1)->comment('1=Promoted, 2=Graduated, 3=Detained, 4=Transferred');
            $table->foreignId('promoted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'student_id', 'from_academic_year_id', 'to_academic_year_id'], 'unique_student_promotion');
            $table->index(['school_id', 'student_id']);
            $table->index(['from_academic_year_id', 'to_academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
    }
};
