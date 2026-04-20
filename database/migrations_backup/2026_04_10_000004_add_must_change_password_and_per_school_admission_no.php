<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WHY:
 * 1. must_change_password — student accounts are created with a generated password.
 *    We need to force a reset on first login. Without this column the flag silently
 *    drops on User::create() because it is not in $fillable.
 *
 * 2. unique(school_id, admission_no) — the original migration has a global unique on
 *    admission_no alone, which means two different schools cannot share the same number
 *    (e.g. both starting at 100001). The correct constraint is per-school uniqueness.
 *    We drop the global unique and replace it with a composite one.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Add must_change_password flag to users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('status');
        });

        // 2. Replace global admission_no unique with per-school composite unique.
        //    The original migration created: $table->string('admission_no')->unique()
        //    We drop that and add the composite version.
        Schema::table('students', function (Blueprint $table) {
            // Drop the global unique index (Laravel names it students_admission_no_unique)
            $table->dropUnique('students_admission_no_unique');

            // Per-school uniqueness: two schools can both have student 100001
            $table->unique(['school_id', 'admission_no'], 'students_school_admission_no_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_school_admission_no_unique');
            $table->unique('admission_no', 'students_admission_no_unique');
        });
    }
};
