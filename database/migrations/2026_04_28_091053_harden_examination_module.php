<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Widen Result mark columns so 4-digit full marks (and >100% percentages from
        //    practical/theory composites) cannot overflow at insert time.
        Schema::table('results', function (Blueprint $table) {
            $table->decimal('marks_obtained', 8, 2)->default(0)->change();
            $table->decimal('total_marks', 8, 2)->change();
        });

        // 2) Audit + lifecycle columns on results.
        Schema::table('results', function (Blueprint $table) {
            if (!Schema::hasColumn('results', 'is_absent')) {
                $table->boolean('is_absent')->default(false)->after('grade');
            }
            if (!Schema::hasColumn('results', 'entered_by')) {
                $table->foreignId('entered_by')
                    ->nullable()
                    ->after('is_absent')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('results', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('entered_by');
            }
        });

        // 3) ExamType soft-deletes — controller already guards with withTrashed() but
        //    the column was missing, making that guard a no-op.
        Schema::table('exam_types', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_types', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // 4) Allow exam subjects to be owned by a specific teacher (Phase 3 unlocks
        //    teacher-driven mark entry; column lands now so backfills are simple).
        Schema::table('exam_subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_subjects', 'teacher_id')) {
                $table->foreignId('teacher_id')
                    ->nullable()
                    ->after('subject_id')
                    ->constrained('teachers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_subjects', function (Blueprint $table) {
            if (Schema::hasColumn('exam_subjects', 'teacher_id')) {
                $table->dropForeign(['teacher_id']);
                $table->dropColumn('teacher_id');
            }
        });

        Schema::table('exam_types', function (Blueprint $table) {
            if (Schema::hasColumn('exam_types', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('results', function (Blueprint $table) {
            if (Schema::hasColumn('results', 'locked_at')) {
                $table->dropColumn('locked_at');
            }
            if (Schema::hasColumn('results', 'entered_by')) {
                $table->dropForeign(['entered_by']);
                $table->dropColumn('entered_by');
            }
            if (Schema::hasColumn('results', 'is_absent')) {
                $table->dropColumn('is_absent');
            }
        });

        Schema::table('results', function (Blueprint $table) {
            $table->decimal('marks_obtained', 5, 2)->default(0)->change();
            $table->decimal('total_marks', 5, 2)->change();
        });
    }
};
