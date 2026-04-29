<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_categories', function (Blueprint $table) {
            // Closes the TOCTOU race in the controller's Rule::unique check.
            // Soft-deleted rows participate in this unique because we cannot
            // express partial uniques portably; deletes followed by re-create
            // with the same name should restore the soft-deleted row instead.
            $table->unique(['school_id', 'name'], 'book_categories_school_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('book_categories', function (Blueprint $table) {
            $table->dropUnique('book_categories_school_name_unique');
        });
    }
};
