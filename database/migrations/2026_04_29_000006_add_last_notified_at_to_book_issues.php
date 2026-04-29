<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('book_issues', 'last_notified_at')) {
            Schema::table('book_issues', function (Blueprint $table) {
                $table->timestamp('last_notified_at')->nullable()->after('fine_paid_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('book_issues', 'last_notified_at')) {
            Schema::table('book_issues', function (Blueprint $table) {
                $table->dropColumn('last_notified_at');
            });
        }
    }
};
