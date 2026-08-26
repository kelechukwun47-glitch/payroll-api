<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case HR = 'hr';
    case MANAGER = 'manager';
    case EMPLOYEE = 'employee';
}