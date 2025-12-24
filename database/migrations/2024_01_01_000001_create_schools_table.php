<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('subdomain')->unique()->nullable();
            $table->string('domain')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->integer('country_id')->default(1);
            $table->string('pincode')->nullable();
            $table->string('logo')->nullable();
            $table->string('site_icon')->nullable();
            $table->string('website')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Active, 2=Inactive, 3=Suspended');
            $table->date('subscription_start_date')->nullable();
            $table->date('subscription_end_date')->nullable();
            $table->json('settings')->nullable(); // School-specific settings
            $table->json('features')->nullable(); // Enabled features
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('subdomain');
            $table->index('domain');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};

