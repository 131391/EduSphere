<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'slug' => Role::SUPER_ADMIN,
                'guard_name' => 'web',
                'description' => 'Full system access with all permissions',
            ],
            [
                'name' => 'school_admin',
                'slug' => Role::SCHOOL_ADMIN,
                'guard_name' => 'web',
                'description' => 'School-level administrator with full school management access',
            ],
            [
                'name' => 'receptionist',
                'slug' => Role::RECEPTIONIST,
                'guard_name' => 'web',
                'description' => 'Front desk staff with visitor, enquiry, and admission management access',
            ],
            [
                'name' => 'teacher',
                'slug' => Role::TEACHER,
                'guard_name' => 'web',
                'description' => 'Teaching staff with class and student management access',
            ],
            [
                'name' => 'student',
                'slug' => Role::STUDENT,
                'guard_name' => 'web',
                'description' => 'Student user with limited access to their own data',
            ],
            [
                'name' => 'parent',
                'slug' => Role::PARENT,
                'guard_name' => 'web',
                'description' => 'Parent/guardian with access to their children\'s information',
            ],
        ];

        foreach ($roles as $role) {
            // Update existing role or create new one
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name'], 'guard_name' => $role['guard_name']],
                [
                    'slug' => $role['slug'],
                    'description' => $role['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
