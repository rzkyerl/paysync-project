<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'is_demo',
        'industry',
        'size',
        'payroll_cut_off',
        'pay_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_demo' => 'boolean',
            'payroll_cut_off' => 'integer',
            'pay_date' => 'integer',
        ];
    }

    /**
     * Get the users that belong to the company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the employees that belong to the company.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the payrolls that belong to the company.
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function settingsAuditLogs(): HasMany
    {
        return $this->hasMany(SettingsAuditLog::class);
    }
}
