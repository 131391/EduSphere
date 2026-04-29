<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            // Accession number is the physical-world identifier (often barcoded
            // and stuck on the inside cover). Unique within a school.
            $table->string('accession_number', 64);
            $table->enum('status', ['available', 'issued', 'lost', 'damaged', 'discarded'])
                ->default('available');
            $table->enum('condition', ['new', 'good', 'fair', 'poor'])->default('new');
            $table->string('shelf_location', 64)->nullable();
            $table->date('acquired_on')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'accession_number'], 'book_copies_school_accession_unique');
            $table->index(['book_id', 'status'], 'book_copies_book_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
