<?php

namespace App\Listeners;

use App\Events\LeaveApproved;
use App\Models\LeaveRequest;
use App\Services\AuditLogService;

class LogLeaveApprovalAudit
{
    /**
     * Handle the event.
     */
    public function handle(LeaveApproved $event): void
    {
        AuditLogService::log(
            action: 'leave_approved',
            recordType: LeaveRequest::class,
            recordId: $event->leaveRequest->id,
            oldValues: ['status' => 'pending'],
            newValues: [
                'status' => 'approved',
                'approved_by' => $event->approverUserId,
            ]
        );
    }
}