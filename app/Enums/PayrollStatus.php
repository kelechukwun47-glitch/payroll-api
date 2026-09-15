<?php

namespace App\Enums;

enum PayrollStatus: string
{
    case DRAFT = 'draft';
    case PROCESSING = 'processing';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case PAID = 'paid';
    case FAILED = 'failed';
}