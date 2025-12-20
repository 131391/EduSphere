<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('name'); // e.g., "A", "B", "C"
            $table->integer('capacity')->default(50);
            $table->integer('current_strength')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'class_id', 'name']);
            $table->index('school_id');
            $table->index('class_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};

