<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEmployeeRequest;
use App\Http\Requests\Api\V1\UpdateEmployeeRequest;
use App\Http\Resources\Api\V1\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(): AnonymousResourceCollection
    {
        $employees = Employee::with(['user', 'department', 'manager.user'])
            ->paginate(15);

        return EmployeeResource::collection($employees);
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
     * Remove the specified employee.
     */
    public function destroy(Employee $employee): array
    {
        $employee->delete();

        return [
            'message' => 'Employee record deleted successfully.',
        ];
    }
}