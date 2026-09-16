<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreLeaveRequest;
use App\Http\Resources\Api\V1\LeaveRequestResource;
use App\Jobs\SendLeaveStatusNotificationJob;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    /**
     * Submit a new leave request.
     */
    public function store(StoreLeaveRequest $request): LeaveRequestResource
    {
        $validated = $request->validated();
        $employee = $request->user()->employee;
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $daysRequested = $startDate->diffInDays($endDate) + 1;

        if ($daysRequested > $leaveType->allowed_days) {
            throw ValidationException::withMessages([
                'days_requested' => ["Requested days ({$daysRequested}) exceed allowed limit ({$leaveType->allowed_days}) for {$leaveType->name}."],
            ]);
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

        return new LeaveRequestResource($leaveRequest->load(['employee', 'leaveType']));
    }

    /**
     * Approve a leave request (Manager or HR).
     */
    public function approve(Request $request, LeaveRequest $leaveRequest): LeaveRequestResource
    {
        if ($leaveRequest->status === LeaveStatus::APPROVED) {
            throw ValidationException::withMessages([
                'status' => ['Leave request is already approved.'],
            ]);
        }

        $leaveRequest->update([
            'status' => LeaveStatus::APPROVED,
            'approved_by' => $request->user()->employee?->id,
        ]);

        SendLeaveStatusNotificationJob::dispatch($leaveRequest);

        return new LeaveRequestResource($leaveRequest->load(['employee', 'leaveType']));
    }
}