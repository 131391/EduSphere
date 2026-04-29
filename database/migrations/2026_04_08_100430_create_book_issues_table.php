<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('restrict');
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->timestamp('fine_paid_at')->nullable();
            $table->decimal('fine_paid_amount', 10, 2)->nullable();
            $table->string('fine_payment_method', 32)->nullable();
            $table->foreignId('fine_collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('fine_settlement_note', 500)->nullable();
            $table->enum('status', ['issued', 'returned', 'lost'])->default('issued');
            $table->unsignedSmallInteger('renewal_count')->default(0);
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('book_id');
            $table->index('student_id');
            $table->index('status');
            $table->index(['school_id', 'status', 'due_date'], 'bi_school_status_due');
            $table->index(['school_id', 'student_id', 'status'], 'bi_school_student_status');
        });

        // Generated columns for duplicate-active-issue prevention (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE book_issues
                ADD COLUMN active_student_id BIGINT UNSIGNED
                GENERATED ALWAYS AS (CASE WHEN status = "issued" THEN student_id ELSE NULL END) VIRTUAL');

            DB::statement('ALTER TABLE book_issues
                ADD COLUMN active_staff_id BIGINT UNSIGNED
                GENERATED ALWAYS AS (CASE WHEN status = "issued" THEN staff_id ELSE NULL END) VIRTUAL');

            Schema::table('book_issues', function ($table) {
                $table->unique(['school_id', 'book_id', 'active_student_id'], 'bi_unique_active_student_book');
                $table->unique(['school_id', 'book_id', 'active_staff_id'], 'bi_unique_active_staff_book');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('book_issues');
    }
};
