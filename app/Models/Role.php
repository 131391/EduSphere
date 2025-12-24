<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Role slug constants
    const SUPER_ADMIN = 'super_admin';
    const SCHOOL_ADMIN = 'school_admin';
    const RECEPTIONIST = 'receptionist';
    const TEACHER = 'teacher';
    const STUDENT = 'student';
    const PARENT = 'parent';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'guard_name',
    ];

    /**
     * Get all users with this role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if this is a super admin role.
     */
    public function isSuperAdmin(): bool
    {
        return $this->slug === self::SUPER_ADMIN;
    }

    /**
     * Check if this is a school admin role.
     */
    public function isSchoolAdmin(): bool
    {
        return $this->slug === self::SCHOOL_ADMIN;
    }

    /**
     * Check if this is a receptionist role.
     */
    public function isReceptionist(): bool
    {
        return $this->slug === self::RECEPTIONIST;
    }

    /**
     * Check if this is a teacher role.
     */
    public function isTeacher(): bool
    {
        return $this->slug === self::TEACHER;
    }

    /**
     * Check if this is a student role.
     */
    public function isStudent(): bool
    {
        return $this->slug === self::STUDENT;
    }

    /**
     * Check if this is a parent role.
     */
    public function isParent(): bool
    {
        return $this->slug === self::PARENT;
    }
}
