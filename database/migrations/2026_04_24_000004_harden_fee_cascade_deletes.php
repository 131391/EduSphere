<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Harden cascade deletes on financial tables.
     *
     * students → fees: CASCADE → RESTRICT (can't nuke paid history)
     * fees → fee_payments: CASCADE → RESTRICT (can't orphan payments)
     * fee_types → fees: CASCADE → RESTRICT (historical bills are immutable)
     * fee_names → fees: CASCADE → RESTRICT (historical bills are immutable)
     * fee_payments.student_id: CASCADE → SET NULL (nullable, safe)
     */
    public function up(): void
    {
        // fees.student_id: cascade → restrict
        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                ->references('id')->on('students')
                ->onDelete('restrict');
        });

        // fees.fee_type_id: cascade → restrict
        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['fee_type_id']);
            $table->foreign('fee_type_id')
                ->references('id')->on('fee_types')
                ->onDelete('restrict');
        });

        // fees.fee_name_id: cascade → restrict
        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['fee_name_id']);
            $table->foreign('fee_name_id')
                ->references('id')->on('fee_names')
                ->onDelete('restrict');
        });

        // fee_payments.fee_id: cascade → restrict
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['fee_id']);
            $table->foreign('fee_id')
                ->references('id')->on('fees')
                ->onDelete('restrict');
        });

        // fee_payments.student_id: cascade → set null
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                ->references('id')->on('students')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Revert to original cascade deletes
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                ->references('id')->on('students')
                ->onDelete('cascade');
        });

        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['fee_id']);
            $table->foreign('fee_id')
                ->references('id')->on('fees')
                ->onDelete('cascade');
        });

        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['fee_name_id']);
            $table->foreign('fee_name_id')
                ->references('id')->on('fee_names')
                ->onDelete('cascade');
        });

        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['fee_type_id']);
            $table->foreign('fee_type_id')
                ->references('id')->on('fee_types')
                ->onDelete('cascade');
        });

        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                ->references('id')->on('students')
                ->onDelete('cascade');
        });
    }
};
