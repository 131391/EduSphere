<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop any duplicate gateway_order_id rows before applying unique constraint.
        // Uses a cross-DB compatible DELETE subquery (works on both MySQL and SQLite).
        DB::statement("
            DELETE FROM online_transactions
            WHERE id NOT IN (
                SELECT min_id FROM (
                    SELECT MIN(id) AS min_id
                    FROM online_transactions
                    WHERE gateway_order_id IS NOT NULL
                    GROUP BY gateway_order_id
                ) AS keep
            )
            AND gateway_order_id IS NOT NULL
        ");

        Schema::table('online_transactions', function (Blueprint $table) {
            // Add columns only if they don't already exist (safe for re-runs
            // and for the case where the base migration was updated first).
            if (!Schema::hasColumn('online_transactions', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('online_transactions', 'failed_at')) {
                $table->timestamp('failed_at')->nullable();
            }
            if (!Schema::hasColumn('online_transactions', 'error_message')) {
                $table->text('error_message')->nullable();
            }

            // Replace plain indexes with unique constraints.
            // Wrap in try/catch because the indexes may not exist on a fresh DB.
            try {
                $table->dropIndex(['gateway_order_id']);
            } catch (\Throwable $e) {
                // Index may not exist
            }
            $table->unique('gateway_order_id', 'uq_online_txn_order_id');

            try {
                $table->dropIndex(['gateway_transaction_id']);
            } catch (\Throwable $e) {
                // Index may not exist
            }
            $table->unique('gateway_transaction_id', 'uq_online_txn_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('online_transactions', function (Blueprint $table) {
            $table->dropUnique('uq_online_txn_payment_id');
            $table->index('gateway_transaction_id');

            $table->dropUnique('uq_online_txn_order_id');
            $table->index('gateway_order_id');

            $table->dropColumn(['failed_at', 'error_message']);
            $table->dropSoftDeletes();
        });
    }
};
