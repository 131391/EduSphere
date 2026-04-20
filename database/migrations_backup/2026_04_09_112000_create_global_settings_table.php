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
        Schema::create('global_settings', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('key')->unique();
            $blueprint->text('value')->nullable();
            $blueprint->string('group')->default('general');
            $blueprint->timestamps();
        });

        // Insert default settings
        \Illuminate\Support\Facades\DB::table('global_settings')->insert([
            ['key' => 'site_name', 'value' => 'EduSphere School ERP', 'group' => 'general'],
            ['key' => 'support_email', 'value' => 'support@edusphere.example.com', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'system'],
            ['key' => 'default_package_limit', 'value' => '100', 'group' => 'schools'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
