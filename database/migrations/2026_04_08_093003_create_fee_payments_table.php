<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('fee_id')->constrained()->onDelete('restrict');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->foreignId('payment_method_id')->constrained()->onDelete('cascade');
            $table->string('receipt_no')->index();
            $table->string('transaction_id')->nullable();
            $table->string('idempotency_key', 64)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'receipt_no'], 'unique_school_receipt_no');
            $table->unique(['school_id', 'idempotency_key'], 'uq_fee_payments_idempotency');
            $table->unique(['school_id', 'transaction_id'], 'uq_fee_payments_transaction');
            $table->index(['school_id', 'student_id'], 'idx_fee_payments_school_student');
            $table->index(['school_id', 'receipt_no'], 'idx_fee_payments_school_receipt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
