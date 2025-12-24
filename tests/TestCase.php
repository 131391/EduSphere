<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\School;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Create a school for testing
     */
    protected function createSchool(array $attributes = []): School
    {
        return School::factory()->create($attributes);
    }

    /**
     * Create a user for testing
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * Create and authenticate a user
     */
    protected function actingAsUser(User $user = null, string $guard = 'web'): self
    {
        if (!$user) {
            $user = $this->createUser();
        }

        $this->actingAs($user, $guard);

        return $this;
    }

    /**
     * Set current school context
     */
    protected function setCurrentSchool(School $school): void
    {
        app()->instance('currentSchool', $school);
    }
}

