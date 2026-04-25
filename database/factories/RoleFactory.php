<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        $suffix = $this->faker->unique()->numerify('###');

        return [
            'name'        => 'Role-' . $suffix,
            'slug'        => 'role-' . $suffix,
            'guard_name'  => 'web',
            'description' => 'Test Role',
        ];
    }
}
