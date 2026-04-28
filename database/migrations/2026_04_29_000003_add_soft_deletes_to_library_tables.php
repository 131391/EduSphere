<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_categories', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('books', function (Blueprint $table) {
            $table->softDeletes();

            // Change category FK: cascade → restrict so a category with books cannot be deleted
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')
                ->references('id')->on('book_categories')
                ->onDelete('restrict');
        });

        Schema::table('book_issues', function (Blueprint $table) {
            // Change book FK: cascade → restrict so a book with history cannot be hard-deleted
            $table->dropForeign(['book_id']);
            $table->foreign('book_id')
                ->references('id')->on('books')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            $table->dropForeign(['book_id']);
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('book_categories')->onDelete('cascade');
            $table->dropSoftDeletes();
        });

        Schema::table('book_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
