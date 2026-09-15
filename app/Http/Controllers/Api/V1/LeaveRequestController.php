<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * Submit a new leave request.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $employee = $request->user()->employee;
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $daysRequested = $startDate->diffInDays($endDate) + 1;

        // Rule: Check leave type day limit
        if ($daysRequested > $leaveType->allowed_days) {
            return response()->json([
                'status' => 'error',
                'message' => "Requested days ({$daysRequested}) exceed allowed limit ({$leaveType->allowed_days}) for {$leaveType->name}."
            ], 422);
        }

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days_requested' => $daysRequested,
            'reason' => $validated['reason'] ?? null,
            'status' => LeaveStatus::PENDING,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave request submitted successfully.',
            'data' => $leaveRequest->load('leaveType')
        ], 201);
    }

    /**
     * Approve a leave request (Manager or HR).
     */
    public function approve(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        if ($leaveRequest->status === LeaveStatus::APPROVED) {
            return response()->json(['status' => 'error', 'message' => 'Leave request is already approved.'], 422);
        }

        $leaveRequest->update([
            'status' => LeaveStatus::APPROVED,
            'approved_by' => $request->user()->employee?->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave request approved successfully.',
            'data' => $leaveRequest
        ]);
    }
}