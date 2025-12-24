<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('visitor_no')->unique();
            $table->string('name');
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('visitor_type')->nullable(); // Parent, Vendor, etc.
            $table->string('visit_purpose')->nullable();
            $table->string('meeting_purpose')->nullable();
            $table->string('meeting_with')->nullable();
            $table->tinyInteger('priority')->default(2)->comment('1=Low, 2=Medium, 3=High, 4=Urgent');
            $table->integer('no_of_guests')->default(1);
            $table->tinyInteger('meeting_type')->default(2)->comment('1=Online, 2=Offline, 3=Office');
            $table->string('source')->nullable();
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->dateTime('meeting_scheduled')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Scheduled, 2=CheckedIn, 3=Completed, 4=Cancelled');
            $table->string('visitor_photo')->nullable();
            $table->string('id_proof')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('visitor_no');
            $table->index('status');
            $table->index('meeting_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
