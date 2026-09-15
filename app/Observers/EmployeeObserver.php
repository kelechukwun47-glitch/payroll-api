<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Employee;

class EmployeeObserver
{
    public function updated(Employee $employee): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'employee.updated',
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
            'old_values' => $employee->getOriginal(),
            'new_values' => $employee->getChanges(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}