<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case PENDING = 'pending';
    case MANAGER_APPROVED = 'manager_approved';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}