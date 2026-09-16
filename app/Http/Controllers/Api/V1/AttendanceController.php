<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ClockInRequest;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    /**
     * Clock in the authenticated employee.
     */
    public function clockIn(ClockInRequest $request): AttendanceResource
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            throw ValidationException::withMessages([
                'employee' => ['User is not linked to an employee record.'],
            ]);
        }

        $today = now()->format('Y-m-d');

        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->whereNull('clock_out')
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'attendance' => ['You already have an active clock-in session.'],
            ]);
        }

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => $today,
            'clock_in' => now(),
            'status' => now()->format('H:i') > '09:00' ? 'late' : 'present',
        ]);

        return new AttendanceResource($attendance->load('employee'));
    }

    /**
     * Clock out the authenticated employee.
     */
    public function clockOut(Request $request): AttendanceResource
    {
        $employee = $request->user()->employee;

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
            'clock_out' => now(),
        ]);

        return new AttendanceResource($activeAttendance->load('employee'));
    }
}