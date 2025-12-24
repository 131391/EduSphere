<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
            $table->foreignId('exam_type_id')->nullable()->constrained('exam_types')->onDelete('cascade');
            $table->string('month')->nullable();
            $table->string('name')->nullable(); // e.g., "Mid-Term Exam", "Final Exam"
            $table->string('code')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Scheduled, 2=Ongoing, 3=Completed, 4=Cancelled');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('academic_year_id');
            $table->index('class_id');
            $table->index('exam_type_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};

