<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Generated columns that mirror student_id / staff_id only while the
        // issue is active. Combined with a unique index this enforces "one active
        // issue per (school, book, borrower)" at the DB level, closing any
        // remaining race window after the service-level lockForUpdate.
        DB::statement('ALTER TABLE book_issues
            ADD COLUMN active_student_id BIGINT UNSIGNED
            GENERATED ALWAYS AS (CASE WHEN status = "issued" THEN student_id ELSE NULL END) VIRTUAL');

        DB::statement('ALTER TABLE book_issues
            ADD COLUMN active_staff_id BIGINT UNSIGNED
            GENERATED ALWAYS AS (CASE WHEN status = "issued" THEN staff_id ELSE NULL END) VIRTUAL');

        Schema::table('book_issues', function ($table) {
            $table->unique(
                ['school_id', 'book_id', 'active_student_id'],
                'bi_unique_active_student_book'
            );
            $table->unique(
                ['school_id', 'book_id', 'active_staff_id'],
                'bi_unique_active_staff_book'
            );
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('book_issues', function ($table) {
            $table->dropUnique('bi_unique_active_student_book');
            $table->dropUnique('bi_unique_active_staff_book');
        });

        DB::statement('ALTER TABLE book_issues DROP COLUMN active_student_id');
        DB::statement('ALTER TABLE book_issues DROP COLUMN active_staff_id');
    }
};
