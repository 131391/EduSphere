<?php

namespace App\Models;

use App\Notifications\TenantResetPassword;
use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Enums\UserStatus;
use App\Models\StudentParent;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, LogsActivity, Tenantable;

    // Status constants
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_SUSPENDED = 2;
    const STATUS_PENDING = 3;

    // Status labels for display
    const STATUS_LABELS = [
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_SUSPENDED => 'Suspended',
        self::STATUS_PENDING => 'Pending',
    ];

    protected $fillable = [
        'school_id',
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'status',
        'last_login_at',
        'last_login_ip',
        // Requires password reset on first login (set true for auto-created accounts)
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'password'             => 'hashed',
        'last_login_at'        => 'datetime',
        'status'               => UserStatus::class,
        'must_change_password' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role_id', 'status'])
            ->logOnlyDirty();
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
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
        return $this->hasOne(StudentParent::class);
    }

    // Scopes
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeByRole($query, $roleSlug)
    {
        return $query->whereHas('role', function ($q) use ($roleSlug) {
            $q->where('slug', $roleSlug);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::Active);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', UserStatus::Inactive);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', UserStatus::Suspended);
    }

    public function scopePending($query)
    {
        return $query->where('status', UserStatus::Pending);
    }

    // Status helper methods
    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isInactive(): bool
    {
        return $this->status === UserStatus::Inactive;
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended;
    }

    public function isPending(): bool
    {
        return $this->status === UserStatus::Pending;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status instanceof UserStatus ? $this->status->label() : 'Unknown';
    }

    // Role helper methods
    public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN) && is_null($this->school_id);
    }

    public function isSchoolAdmin(): bool
    {
        return $this->hasRole(Role::SCHOOL_ADMIN);
    }

    public function isTeacher(): bool
    {
        return $this->hasRole(Role::TEACHER);
    }

    public function isStudent(): bool
    {
        return $this->hasRole(Role::STUDENT);
    }

    public function isParent(): bool
    {
        return $this->hasRole(Role::PARENT);
    }

    public function isReceptionist(): bool
    {
        return $this->hasRole(Role::RECEPTIONIST);
    }

    public function isLibrarian(): bool
    {
        return $this->hasRole(Role::LIBRARIAN);
    }

    public function canAccessSchool($schoolId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->school_id === $schoolId;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new TenantResetPassword($token));
    }
}
