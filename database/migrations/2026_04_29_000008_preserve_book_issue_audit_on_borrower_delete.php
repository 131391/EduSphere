<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['staff_id']);

            $table->foreign('student_id')
                ->references('id')->on('students')
                ->nullOnDelete();

            $table->foreign('staff_id')
                ->references('id')->on('staff')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['staff_id']);

            $table->foreign('student_id')
                ->references('id')->on('students')
                ->onDelete('cascade');

            $table->foreign('staff_id')
                ->references('id')->on('staff')
                ->onDelete('cascade');
        });
    }
};
