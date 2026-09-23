<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreLeaveRequest;
use App\Http\Resources\Api\V1\LeaveRequestResource;
use App\Jobs\SendLeaveStatusNotificationJob;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveService $leaveService
    ) {
    }

    /**
     * Submit a new leave request.
     */
    public function store(StoreLeaveRequest $request): LeaveRequestResource
    {
        $employee = $request->user()->employee;
        $leaveRequest = $this->leaveService->submitLeaveRequest($employee, $request->validated());

        return new LeaveRequestResource($leaveRequest);
    }

    /**
     * Approve a leave request (Manager or HR).
     */
    public function approve(Request $request, LeaveRequest $leaveRequest): LeaveRequestResource
    {
        $approverId = $request->user()->employee?->id;
        $updatedRequest = $this->leaveService->approveLeaveRequest($leaveRequest, $approverId);

        SendLeaveStatusNotificationJob::dispatch($updatedRequest);

        return new LeaveRequestResource($updatedRequest->load(['employee', 'leaveType']));
    }
}