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
        Schema::table('students', function (Blueprint $table) {
            $table->text('aadhaar_no')->nullable()->change();
            $table->text('father_aadhaar_no')->nullable()->change();
            $table->text('mother_aadhaar_no')->nullable()->change();
            $table->text('father_pan')->nullable()->change();
            $table->text('mother_pan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('aadhaar_no', 191)->nullable()->change();
            $table->string('father_aadhaar_no', 191)->nullable()->change();
            $table->string('mother_aadhaar_no', 191)->nullable()->change();
            $table->string('father_pan', 191)->nullable()->change();
            $table->string('mother_pan', 191)->nullable()->change();
        });
    }
};
