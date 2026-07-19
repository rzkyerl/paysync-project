<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'invitation_token',
        'invitation_expires_at',
        'employee_id',
        'is_demo',
        'company_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_demo' => 'boolean',
            'invitation_expires_at' => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isHrManager(): bool
    {
        return $this->role === 'hr_manager';
    }

    public function isFinanceManager(): bool
    {
        return $this->role === 'finance_manager';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /** @param array<int, string> $roles */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function defaultDashboard(): string
    {
        return match ($this->role) {
            'super_admin', 'hr_manager' => 'dashboard-hr',
            'finance_manager' => 'dashboard-finance',
            'employee' => 'dashboard-employee',
            default => 'dashboard-hr',
        };
    }

    /**
     * Determine whether the user belongs to the demo experience.
     */
    public function isDemoUser(): bool
    {
        return (bool) $this->is_demo;
    }

    /**
     * Get the company workspace that owns the user.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employee profile linked to this user.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
