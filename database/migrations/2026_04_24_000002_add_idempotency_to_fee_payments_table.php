<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->string('idempotency_key', 64)->nullable()->after('transaction_id');
            $table->unique(['school_id', 'idempotency_key'], 'uq_fee_payments_idempotency');
            $table->unique(['school_id', 'transaction_id'], 'uq_fee_payments_transaction');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropUnique('uq_fee_payments_transaction');
            $table->dropUnique('uq_fee_payments_idempotency');
            $table->dropColumn('idempotency_key');
        });
    }
};
