<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PayrollStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GeneratePayrollRequest;
use App\Http\Resources\Api\V1\PayrollRunResource;
use App\Jobs\ProcessPayrollJob;
use App\Models\PayrollRun;
use Illuminate\Validation\ValidationException;

class PayrollController extends Controller
{
    public function generate(GeneratePayrollRequest $request): PayrollRunResource
    {
        $validated = $request->validated();

        $existing = PayrollRun::where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'payroll' => ['Payroll for this period has already been generated.'],
            ]);
        }

        $payrollRun = PayrollRun::create([
            'month' => $validated['month'],
            'year' => $validated['year'],
            'status' => PayrollStatus::PROCESSING,
            'generated_by' => $request->user()->id,
        ]);

        ProcessPayrollJob::dispatch($payrollRun);

        return new PayrollRunResource($payrollRun->load('payslips'));
    }
}