<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'payroll_id', 'employee_id', 'company_id', 'days_present', 'overtime_hours', 'leave_days', 'work_days',
    ];

    protected function casts(): array
    {
        return [
            'days_present'  => 'integer',
            'overtime_hours'=> 'decimal:2',
            'leave_days'    => 'integer',
            'work_days'     => 'integer',
        ];
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
