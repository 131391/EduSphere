<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('author');
            $table->string('isbn')->nullable();
            $table->foreignId('category_id')->constrained('book_categories')->onDelete('restrict');
            $table->integer('quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'isbn'], 'books_school_isbn_unique');
            $table->index('school_id');
            $table->index('category_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE books ADD CONSTRAINT chk_books_available_lte_quantity CHECK (available_quantity <= quantity)');
            DB::statement('ALTER TABLE books ADD CONSTRAINT chk_books_quantity_gte_zero CHECK (quantity >= 0)');
            DB::statement('ALTER TABLE books ADD CONSTRAINT chk_books_available_gte_zero CHECK (available_quantity >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
