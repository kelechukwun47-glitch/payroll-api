<?php

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
 * Daily Pending Leave Request Check[cite: 1].
 */
Schedule::call(function () {
    LeaveRequest::where('status', 'pending')
        ->where('created_at', '<=', Carbon::now()->subDays(3))
        ->get();
})->dailyAt('08:00');