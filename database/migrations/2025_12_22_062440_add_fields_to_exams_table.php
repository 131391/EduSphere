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
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->after('academic_year_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('exam_type_id')->nullable()->after('class_id')->constrained('exam_types')->onDelete('cascade');
            $table->string('month')->nullable()->after('exam_type_id');
            
            // Make name nullable if we want to use Exam Type name as default
            $table->string('name')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropForeign(['exam_type_id']);
            $table->dropColumn(['class_id', 'exam_type_id', 'month']);
            
            $table->string('name')->nullable(false)->change();
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
        });
    }
};
