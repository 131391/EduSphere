<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('gateway_name')->default('razorpay');
            $table->string('gateway_order_id')->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->string('status')->default('pending')->comment('pending, success, failed');
            $table->json('payload')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('gateway_order_id', 'uq_online_txn_order_id');
            $table->unique('gateway_transaction_id', 'uq_online_txn_payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_transactions');
    }
};
