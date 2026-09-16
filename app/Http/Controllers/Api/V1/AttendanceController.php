<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ClockInRequest;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {
    }

    /**
     * Clock in the authenticated employee.
     */
    public function clockIn(ClockInRequest $request): AttendanceResource
    {
        $attendance = $this->attendanceService->clockIn($request->user()->employee);

        return new AttendanceResource($attendance->load('employee'));
    }

    /**
     * Clock out the authenticated employee.
     */
    public function clockOut(Request $request): AttendanceResource
    {
        $attendance = $this->attendanceService->clockOut($request->user()->employee);

        return new AttendanceResource($attendance->load('employee'));
    }
}