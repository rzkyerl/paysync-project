<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;

class PayrollPolicy
{
    /**
     * Determine whether the user can view any payroll records.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'hr_manager', 'finance_manager']);
    }

    /**
     * Determine whether the user can view a specific payroll.
     *
     * Company-scoped: a user may only access payrolls belonging to
     * their own company. Demo super-admin users are exempt.
     */
    public function view(User $user, Payroll $payroll): bool
    {
        if ($user->hasAnyRole(['super_admin', 'hr_manager', 'finance_manager'])) {
            return $this->sameCompany($user, $payroll);
        }

        // Regular employees can only view payrolls that are approved/disbursed
        // and where they have a payroll item.
        if ($user->isEmployee()) {
            return $user->company_id !== null
                && (int) $user->company_id === (int) $payroll->company_id
                && in_array($payroll->status, ['approved', 'disbursed'], true);
        }

        return false;
    }

    /**
     * Determine whether the user can create payrolls for the company.
     */
    public function create(User $user): bool
    {
        return $user->isHrManager() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update payroll metadata.
     * Only HR managers may modify draft / needs_review payrolls.
     */
    public function update(User $user, Payroll $payroll): bool
    {
        if (! $user->isHrManager() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $this->sameCompany($user, $payroll)
            && in_array($payroll->status, ['draft', 'needs_review'], true);
    }

    /**
     * Determine whether the user can submit a payroll for approval.
     */
    public function submit(User $user, Payroll $payroll): bool
    {
        if (! $user->isHrManager() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $this->sameCompany($user, $payroll);
    }

    /**
     * Determine whether the user can approve (or reject) a payroll.
     */
    public function approve(User $user, Payroll $payroll): bool
    {
        if (! $user->hasAnyRole(['finance_manager', 'super_admin'])) {
            return false;
        }

        return $this->sameCompany($user, $payroll);
    }

    /**
     * Determine whether the user can disburse a payroll.
     */
    public function disburse(User $user, Payroll $payroll): bool
    {
        if (! $user->hasAnyRole(['finance_manager', 'super_admin'])) {
            return false;
        }

        return $this->sameCompany($user, $payroll);
    }

    /**
     * Determine whether the user can download a transfer file.
     */
    public function downloadTransferFile(User $user, Payroll $payroll): bool
    {
        if (! $user->hasAnyRole(['finance_manager', 'super_admin'])) {
            return false;
        }

        return $this->sameCompany($user, $payroll);
    }

    /**
     * Determine whether the user can view the audit report for a payroll.
     */
    public function report(User $user, Payroll $payroll): bool
    {
        if (! $user->hasAnyRole(['super_admin', 'hr_manager', 'finance_manager'])) {
            return false;
        }

        return $this->sameCompany($user, $payroll);
    }

    /**
     * Verify that the payroll belongs to the user's company.
     */
    private function sameCompany(User $user, Payroll $payroll): bool
    {
        return $user->company_id !== null
            && (int) $user->company_id === (int) $payroll->company_id;
    }
}
