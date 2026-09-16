<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    /**
     * Process employee clock-in with active session and late status check.
     */
    public function clockIn(?Employee $employee): Attendance
    {
        if (!$employee) {
            throw ValidationException::withMessages([
                'employee' => ['User is not linked to an employee record.'],
            ]);
        }

        $today = Carbon::today()->toDateString();

        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->whereNull('clock_out')
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'attendance' => ['You already have an active clock-in session.'],
            ]);
        }

        $now = Carbon::now();
        $status = $now->format('H:i') > '09:00' ? 'late' : 'present';

        return Attendance::create([
            'employee_id' => $employee->id,
            'date' => $today,
            'clock_in' => $now,
            'status' => $status,
        ]);
    }

    /**
     * Process employee clock-out.
     */
    public function clockOut(?Employee $employee): Attendance
    {
        if (!$employee) {
            throw ValidationException::withMessages([
                'employee' => ['User is not linked to an employee record.'],
            ]);
        }

        $activeAttendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if (!$activeAttendance) {
            throw ValidationException::withMessages([
                'attendance' => ['No active clock-in record found to clock out from.'],
            ]);
        }

        $activeAttendance->update([
            'clock_out' => Carbon::now(),
        ]);

        return $activeAttendance;
    }
}