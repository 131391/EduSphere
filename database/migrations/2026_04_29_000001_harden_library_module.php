<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add fine_paid_at for fine-settlement workflow
        Schema::table('book_issues', function (Blueprint $table) {
            $table->timestamp('fine_paid_at')->nullable()->after('fine_amount');
            // Composite index for overdue queries and history lookups
            $table->index(['school_id', 'status', 'due_date'], 'bi_school_status_due');
            $table->index(['school_id', 'student_id', 'status'], 'bi_school_student_status');
        });

        // Unique ISBN per school (nullable ISBNs are excluded by the partial approach;
        // MySQL treats multiple NULLs as distinct so the unique index is safe)
        Schema::table('books', function (Blueprint $table) {
            $table->unique(['school_id', 'isbn'], 'books_school_isbn_unique');
        });

        // DB-level guard: available_quantity must not exceed quantity
        // MySQL 8.0+ supports CHECK constraints; SQLite (used in tests) does not allow
        // adding CHECK via ALTER TABLE, so skip on non-MySQL drivers.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE books ADD CONSTRAINT chk_books_available_lte_quantity CHECK (available_quantity <= quantity)');
            DB::statement('ALTER TABLE books ADD CONSTRAINT chk_books_quantity_gte_zero CHECK (quantity >= 0)');
            DB::statement('ALTER TABLE books ADD CONSTRAINT chk_books_available_gte_zero CHECK (available_quantity >= 0)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE books DROP CONSTRAINT chk_books_available_lte_quantity');
            DB::statement('ALTER TABLE books DROP CONSTRAINT chk_books_quantity_gte_zero');
            DB::statement('ALTER TABLE books DROP CONSTRAINT chk_books_available_gte_zero');
        }

        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique('books_school_isbn_unique');
        });

        Schema::table('book_issues', function (Blueprint $table) {
            $table->dropIndex('bi_school_status_due');
            $table->dropIndex('bi_school_student_status');
            $table->dropColumn('fine_paid_at');
        });
    }
};
