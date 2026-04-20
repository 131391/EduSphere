<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_parent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('parents')->onDelete('cascade');
            $table->tinyInteger('relation')->default(3)->comment('1=Father, 2=Mother, 3=Guardian, 4=Other');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'parent_id']);
            $table->index('student_id');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_parent');
    }
};

