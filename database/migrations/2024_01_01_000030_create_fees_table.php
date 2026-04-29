<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('student_id')->nullable()->constrained('students')->onDelete('restrict');
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('fee_type_id')->constrained('fee_types')->onDelete('restrict');
            $table->foreignId('fee_name_id')->nullable()->constrained('fee_names')->onDelete('restrict');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('bill_no')->unique();
            $table->string('fee_period');
            $table->decimal('payable_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->decimal('waiver_amount', 10, 2)->default(0);
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->tinyInteger('payment_status')->default(1)->comment('1=Pending, 2=Partial, 3=Paid, 4=Overdue');
            $table->enum('payment_mode', ['cash', 'online', 'cheque', 'bank_transfer'])->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['school_id', 'student_id', 'academic_year_id', 'fee_type_id', 'fee_name_id', 'fee_period'],
                'uq_fees_student_period'
            );
            $table->index('school_id');
            $table->index('student_id');
            $table->index('bill_no');
            $table->index('payment_status');
            $table->index('fee_period');
            $table->index(['school_id', 'student_id'], 'idx_fees_school_student');
            $table->index(['school_id', 'academic_year_id', 'payment_status'], 'idx_fees_school_year_status');
            $table->index(['school_id', 'bill_no'], 'idx_fees_school_bill');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
