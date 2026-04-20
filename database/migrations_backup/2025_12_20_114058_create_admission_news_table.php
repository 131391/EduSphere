<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->date('publish_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_news');
    }
};
