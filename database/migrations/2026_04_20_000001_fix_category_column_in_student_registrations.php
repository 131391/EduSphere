<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change ENUM to string so any category name from the categories table is accepted
        DB::statement("ALTER TABLE student_registrations MODIFY COLUMN category VARCHAR(100) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE student_registrations MODIFY COLUMN category ENUM('General','OBC','SC','ST','Other') NULL");
    }
};
