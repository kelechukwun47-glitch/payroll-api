<?php

use App\Jobs\ProcessPayrollJob;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schedule;

/**
 * Daily Attendance Check: Flag records with missing clock-outs.
 */
Schedule::call(function () {
    Attendance::whereNull('clock_out')
        ->where('date', '<', Carbon::today()->toDateString())
        ->update(['status' => 'missing_clock_out']);
})->dailyAt('01:00');

/**
 * Daily Pending Leave Request Check.
 */
Schedule::call(function () {
    LeaveRequest::where('status', 'pending')
        ->where('created_at', '<=', Carbon::now()->subDays(3))
        ->get();
})->dailyAt('08:00');

/**
 * Monthly Automated Payroll Scheduled Job.
 * Runs on the 28th of every month at midnight to queue payroll processing.
 */
Schedule::call(function () {
    $now = Carbon::now();
    ProcessPayrollJob::dispatch($now->month, $now->year);
})->monthlyOn(28, '00:00');