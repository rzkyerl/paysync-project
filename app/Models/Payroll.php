<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'period',
        'period_label',
        'status',
        'employee_count',
        'gross_total',
        'deduction_total',
        'net_total',
        'anomaly_count',
        'submitted_by',
        'approved_by',
        'rejection_note',
        'approved_at',
        'disbursed_at',
        'disbursement_proof',
        'disbursed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gross_total' => 'decimal:2',
            'deduction_total' => 'decimal:2',
            'net_total' => 'decimal:2',
            'employee_count' => 'integer',
            'anomaly_count' => 'integer',
            'approved_at' => 'datetime',
            'disbursed_at' => 'datetime',
        ];
    }

    /**
     * Get the user who submitted this payroll.
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who approved this payroll.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the company that owns the payroll.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function canTransitionTo(string $status): bool
    {
        $transitions = [
            'draft' => ['needs_review'],
            'needs_review' => ['pending_approval'],
            'pending_approval' => ['approved', 'needs_review'],
            'approved' => ['disbursed'],
            'disbursed' => [],
        ];

        return in_array($status, $transitions[$this->status] ?? [], true);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function transitionTo(string $status, array $meta = []): void
    {
        if (! $this->canTransitionTo($status)) {
            throw new \LogicException("Invalid payroll transition from {$this->status} to {$status}.");
        }

        $attributes = ['status' => $status];
        foreach (['submitted_by', 'approved_by', 'approved_at', 'disbursed_by', 'disbursed_at', 'rejection_note'] as $field) {
            if (array_key_exists($field, $meta)) {
                $attributes[$field] = $meta[$field];
            }
        }

        if ($status === 'approved' && ! array_key_exists('approved_at', $attributes)) {
            $attributes['approved_at'] = now();
        }

        if ($status === 'disbursed' && ! array_key_exists('disbursed_at', $attributes)) {
            $attributes['disbursed_at'] = now();
        }

        if ($status === 'needs_review' && array_key_exists('rejection_note', $meta)) {
            $attributes['rejection_note'] = $meta['rejection_note'];
        }

        $this->update($attributes);
    }
}
