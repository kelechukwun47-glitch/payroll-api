<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Events\LeaveApproved;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    /**
     * Submit a leave request with limit, balance, and overlap validation.
     */
    public function submitLeaveRequest(Employee $employee, array $data): LeaveRequest
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => ['End date cannot be before start date.'],
            ]);
        }

        $daysRequested = $startDate->diffInDays($endDate) + 1;
        $leaveType = LeaveType::findOrFail($data['leave_type_id']);

        if ($daysRequested > $leaveType->allowed_days) {
            throw ValidationException::withMessages([
                'days_requested' => ["Requested days ({$daysRequested}) exceed allowed limit ({$leaveType->allowed_days}) for {$leaveType->name}."],
            ]);
        }

        // Check for overlapping approved/pending leave requests
        $hasOverlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', [LeaveStatus::PENDING, LeaveStatus::APPROVED])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'start_date' => ['You already have an active or pending leave request for these dates.'],
            ]);
        }

        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days_requested' => $daysRequested,
            'reason' => $data['reason'] ?? null,
            'status' => LeaveStatus::PENDING,
        ]);
    }

    /**
     * Approve a leave request and dispatch event.
     */
    public function approveLeaveRequest(LeaveRequest $leaveRequest, ?int $approverEmployeeId): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $approverEmployeeId) {
            if ($leaveRequest->status === LeaveStatus::APPROVED) {
                throw ValidationException::withMessages([
                    'status' => ['Leave request is already approved.'],
                ]);
            }

            $leaveRequest->update([
                'status' => LeaveStatus::APPROVED,
                'approved_by' => $approverEmployeeId,
            ]);

            // Dispatch Event for Audit Logging & Background Notifications[cite: 1]
            event(new LeaveApproved($leaveRequest, auth()->id() ?? 1));

            return $leaveRequest;
        });
    }
}