<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            if (!Schema::hasColumn('book_issues', 'fine_paid_amount')) {
                $table->decimal('fine_paid_amount', 10, 2)->nullable()->after('fine_paid_at');
            }
            if (!Schema::hasColumn('book_issues', 'fine_payment_method')) {
                $table->string('fine_payment_method', 32)->nullable()->after('fine_paid_amount');
            }
            if (!Schema::hasColumn('book_issues', 'fine_collected_by')) {
                $table->foreignId('fine_collected_by')
                    ->nullable()
                    ->after('fine_payment_method')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('book_issues', 'fine_settlement_note')) {
                $table->string('fine_settlement_note', 500)->nullable()->after('fine_collected_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            if (Schema::hasColumn('book_issues', 'fine_collected_by')) {
                $table->dropForeign(['fine_collected_by']);
            }
            $table->dropColumn([
                'fine_paid_amount',
                'fine_payment_method',
                'fine_collected_by',
                'fine_settlement_note',
            ]);
        });
    }
};
