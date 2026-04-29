<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('book_issues', 'deleted_at')) {
            Schema::table('book_issues', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('book_issues', 'deleted_at')) {
            Schema::table('book_issues', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
