<?php

namespace App\Jobs;

use App\Enums\PayrollStatus;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessPayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public PayrollRun $payrollRun
    ) {
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $totalGross = 0;
            $totalNet = 0;
            $activeEmployees = Employee::active()->get();

            foreach ($activeEmployees as $employee) {
                $basicSalary = $employee->basic_salary;
                $allowances = 0.00;
                $deductions = $basicSalary * 0.05;
                $gross = $basicSalary + $allowances;
                $net = $gross - $deductions;

                Payslip::create([
                    'payroll_run_id' => $this->payrollRun->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $basicSalary,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'bonuses' => 0.00,
                    'gross_salary' => $gross,
                    'net_salary' => $net,
                    'status' => PayrollStatus::PROCESSING,
                ]);

                $totalGross += $gross;
                $totalNet += $net;
            }

            $this->payrollRun->update([
                'total_gross' => $totalGross,
                'total_net' => $totalNet,
                'status' => PayrollStatus::PENDING_APPROVAL,
            ]);
        });
    }
}