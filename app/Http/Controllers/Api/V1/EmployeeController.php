<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    /**
     * Display a paginated listing of employees with eager loading.
     */
    public function index(): JsonResponse
    {
        $employees = Employee::with(['user', 'department', 'manager.user'])
            ->latest()
            ->paginate(15);

        return response()->json(EmployeeResource::collection($employees)->response()->getData(true));
    }

    /**
     * Display the specified employee details.
     */
    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['user', 'department', 'manager.user']);

        return response()->json([
            'data' => new EmployeeResource($employee),
        ]);
    }

    /**
     * Update the specified employee record.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee->update($request->validated());

        return response()->json([
            'message' => 'Employee updated successfully',
            'data' => new EmployeeResource($employee->fresh(['user', 'department', 'manager'])),
        ]);
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json([
            'message' => 'Employee deleted successfully',
        ]);
    }
}