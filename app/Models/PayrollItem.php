<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_id', 'employee_id', 'company_id', 'gross_pay', 'basic_salary_snapshot',
        'overtime_pay', 'bpjs_tk_deduction', 'bpjs_kesehatan_deduction', 'pph21_deduction',
        'total_deduction', 'net_pay', 'status', 'disbursed_at', 'has_anomaly',
        'anomaly_type', 'anomaly_acknowledged',
    ];

    protected function casts(): array
    {
        return [
            'gross_pay' => 'decimal:2',
            'basic_salary_snapshot' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'bpjs_tk_deduction' => 'decimal:2',
            'bpjs_kesehatan_deduction' => 'decimal:2',
            'pph21_deduction' => 'decimal:2',
            'total_deduction' => 'decimal:2',
            'net_pay' => 'decimal:2',
            'disbursed_at' => 'datetime',
            'has_anomaly' => 'boolean',
            'anomaly_acknowledged' => 'boolean',
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
