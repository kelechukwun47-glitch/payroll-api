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
    ) {}

    public function handle(): void
    {
        // Guard clause: Prevent duplicate execution on retries
        if ($this->payrollRun->payslips()->exists()) {
            return;
        }

        DB::transaction(function () {
            $totalGross = 0;
            $totalNet = 0;
            $activeEmployees = Employee::active()->get();
            $payslipsToInsert = [];
            $now = now();

            foreach ($activeEmployees as $employee) {
                $basicSalary = (float) $employee->basic_salary;
                $allowances = 0.00;
                $deductions = $basicSalary * 0.05; // 5% standard deductions
                $gross = $basicSalary + $allowances;
                $net = $gross - $deductions;

                $payslipsToInsert[] = [
                    'payroll_run_id' => $this->payrollRun->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $basicSalary,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'bonuses' => 0.00,
                    'gross_salary' => $gross,
                    'net_salary' => $net,
                    'status' => PayrollStatus::PROCESSING->value ?? 'processing',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $totalGross += $gross;
                $totalNet += $net;
            }

            // OPTIMIZATION: Chunk into batches of 100 to prevent thousands of single SQL INSERTs
            foreach (array_chunk($payslipsToInsert, 100) as $chunk) {
                DB::table('payslips')->insert($chunk);
            }

            $this->payrollRun->update([
                'total_gross' => $totalGross,
                'total_net' => $totalNet,
                'status' => PayrollStatus::PENDING_APPROVAL,
            ]);
        });
    }
}