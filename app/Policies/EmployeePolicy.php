<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * Operational managers may view records in their own company. Employees
     * may only view their own linked record.
     */
    public function view(User $user, Employee $employee): bool
    {
        if ($user->hasAnyRole(['super_admin', 'hr_manager', 'finance_manager'])) {
            return $user->company_id !== null
                && (int) $user->company_id === (int) $employee->company_id;
        }

        // Employee users can only view their own linked record
        return $user->employee_id !== null
            && $user->company_id !== null
            && (int) $user->company_id === (int) $employee->company_id
            && (int) $user->employee_id === (int) $employee->id;
    }

    public function create(User $user): bool
    {
        return $user->isHrManager();
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->isHrManager()
            && $user->company_id !== null
            && (int) $user->company_id === (int) $employee->company_id;
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $this->update($user, $employee);
    }
}
