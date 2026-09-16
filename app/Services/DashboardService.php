<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Enums\PayrollStatus;
use App\Models\Attendance;
use App\Models\Bonus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Cache key for dashboard metrics.
     */
    public const CACHE_KEY = 'dashboard_summary_stats';

    /**
     * Retrieve aggregated dashboard stats (cached for 10 minutes).
     */
    public function getSummaryStats(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function () {
            $today = Carbon::today()->toDateString();
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            return [
                'total_employees' => Employee::count(),
                'active_employees' => Employee::where('employment_status', 'active')->count(),
                'employees_on_leave' => LeaveRequest::where('status', LeaveStatus::APPROVED)
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->count(),
                'employees_present_today' => Attendance::where('date', $today)
                    ->whereIn('status', ['present', 'late'])
                    ->count(),
                'pending_leave_requests' => LeaveRequest::where('status', LeaveStatus::PENDING)->count(),
                'monthly_payroll' => (float) PayrollRun::where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->sum('total_amount'),
                'pending_payroll_approvals' => PayrollRun::where('status', PayrollStatus::PROCESSING)->count(),
                'total_bonuses_current_period' => (float) Bonus::whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear)
                    ->sum('amount'),
            ];
        });
    }

    /**
     * Clear dashboard cache when metrics change.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}