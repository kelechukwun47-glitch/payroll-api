<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Clock in the authenticated employee.
     */
    public function clockIn(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'User is not linked to an employee record.'], 422);
        }

        $today = now()->format('Y-m-d');

        // Rule: Prevent double clock-in
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->whereNull('clock_out')
            ->first();

        if ($existing) {
            return response()->json(['status' => 'error', 'message' => 'You already have an active clock-in session.'], 422);
        }

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => $today,
            'clock_in' => now(),
            'status' => now()->format('H:i') > '09:00' ? 'late' : 'present',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Clocked in successfully.',
            'data' => $attendance
        ], 201);
    }

    /**
     * Clock out the authenticated employee.
     */
    public function clockOut(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        $activeAttendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('clock_out')
            ->latest()
            ->first();

        // Rule: Cannot clock out without active clock in
        if (!$activeAttendance) {
            return response()->json(['status' => 'error', 'message' => 'No active clock-in record found to clock out from.'], 422);
        }

        $activeAttendance->update([
            'clock_out' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Clocked out successfully.',
            'data' => $activeAttendance
        ]);
    }
}