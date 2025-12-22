<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, HasRoles, LogsActivity;

    protected $fillable = [
        'school_id',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'status'])
            ->logOnlyDirty();
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function parent()
    {
        return $this->hasOne(Parent::class);
    }

    // Scopes
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper methods
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin' && is_null($this->school_id);
    }

    public function isSchoolAdmin(): bool
    {
        return $this->role === 'school_admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    public function isLibrarian(): bool
    {
        return $this->role === 'librarian';
    }

    public function canAccessSchool($schoolId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->school_id === $schoolId;
    }
}

