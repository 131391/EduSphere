<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);
        
        // Seed geographic data
        $this->call([
            WorldSeeder::class,
        ]);




        // Create super admin only
        $this->createSuperAdmin();

        // Create test schools (DPS and DAV)
        $this->call(TestSchoolsSeeder::class);
    }




    protected function createSuperAdmin(): void
    {
        $superAdminRole = Role::where('slug', Role::SUPER_ADMIN)->first();

        User::firstOrCreate(
            ['email' => 'admin@edusphere.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role_id' => $superAdminRole->id,
                'status' => User::STATUS_ACTIVE,
            ]
        );
    }
}
