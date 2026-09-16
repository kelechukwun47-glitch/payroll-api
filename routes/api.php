<?php

use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BonusController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\LeaveRequestController;
use App\Http\Controllers\Api\V1\PayrollController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth (Public)
    Route::post('login', [AuthController::class, 'login']);

    // Protected V1 Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth Profile & Logout
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        // Dashboard Aggregates
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Employees & Reporting Structure
        Route::apiResource('employees', EmployeeController::class);

        // Departments
        Route::apiResource('departments', DepartmentController::class);
        Route::get('departments/{department}/employees', [DepartmentController::class, 'employees']);

        // Attendance
        Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut']);

        // Leave Requests
        Route::post('leave-requests', [LeaveRequestController::class, 'store']);
        Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve']);

        // Payroll
        Route::post('payrolls/generate', [PayrollController::class, 'generate']);
    });
});