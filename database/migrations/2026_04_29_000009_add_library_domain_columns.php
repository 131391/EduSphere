<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            if (!Schema::hasColumn('book_issues', 'renewal_count')) {
                $table->unsignedSmallInteger('renewal_count')->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            if (Schema::hasColumn('book_issues', 'renewal_count')) {
                $table->dropColumn('renewal_count');
            }
        });
    }
};
