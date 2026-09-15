<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PayrollStatus;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Generate monthly payroll run using Database Transactions for financial safety.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2024',
        ]);

        // Prevent duplicate payroll for same month/year
        $existing = PayrollRun::where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        if ($existing) {
            return response()->json(['status' => 'error', 'message' => 'Payroll for this period has already been generated.'], 422);
        }

        // Database Transaction ensures atomicity
        $payrollRun = DB::transaction(function () use ($validated, $request) {
            $payrollRun = PayrollRun::create([
                'month' => $validated['month'],
                'year' => $validated['year'],
                'status' => PayrollStatus::PROCESSING,
                'generated_by' => $request->user()->id,
            ]);

            $totalGross = 0;
            $totalNet = 0;

            $activeEmployees = Employee::active()->get();

            foreach ($activeEmployees as $employee) {
                $basicSalary = $employee->basic_salary;
                $allowances = 0.00; // Extendable for extra perks
                $deductions = $basicSalary * 0.05; // Standard 5% tax/deductions
                $gross = $basicSalary + $allowances;
                $net = $gross - $deductions;

                Payslip::create([
                    'payroll_run_id' => $payrollRun->id,
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

            $payrollRun->update([
                'total_gross' => $totalGross,
                'total_net' => $totalNet,
                'status' => PayrollStatus::PENDING_APPROVAL,
            ]);

            return $payrollRun;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Payroll generated successfully and pending approval.',
            'data' => $payrollRun->load('payslips')
        ], 201);
    }
}