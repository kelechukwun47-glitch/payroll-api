<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEmployeeRequest;
use App\Http\Requests\Api\V1\UpdateEmployeeRequest;
use App\Http\Resources\Api\V1\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees with search and filtering.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Employee::with(['user', 'department', 'manager.user']);

        // Filter by Department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->query('department_id'));
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('employment_status', $request->query('status'));
        }

        // Filter by Manager
        if ($request->filled('manager_id')) {
            $query->where('manager_id', $request->query('manager_id'));
        }

        // Search by Name or Employee Number
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        return EmployeeResource::collection($query->paginate(15));
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request): EmployeeResource
    {
        $employee = Employee::create($request->validated());

        return new EmployeeResource($employee->load(['user', 'department', 'manager.user']));
    }

    /**
     * Display the specified employee details.
     */
    public function show(Employee $employee): EmployeeResource
    {
        return new EmployeeResource(
            $employee->load(['user', 'department', 'manager.user'])
        );
    }

    /**
     * Update the specified employee record.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): EmployeeResource
    {
        $employee->update($request->validated());

        return new EmployeeResource(
            $employee->fresh(['user', 'department', 'manager.user'])
        );
    }

    /**
     * Remove or deactivate the specified employee.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        // Guard against deleting employees with historical data dependency
        $hasDependencies = $employee->payslips()->exists() 
            || $employee->leaveRequests()->exists() 
            || $employee->bonuses()->exists() 
            || $employee->attendances()->exists();

        if ($hasDependencies) {
            throw ValidationException::withMessages([
                'employee' => ['Cannot delete employee with historical payroll, leave, bonus, or attendance records. Update their status to inactive instead.'],
            ]);
        }

        $employee->delete();

        return response()->json([
            'message' => 'Employee record deleted successfully.',
        ], 200);
    }
}