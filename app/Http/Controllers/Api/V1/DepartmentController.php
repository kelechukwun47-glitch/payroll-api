<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\EmployeeResource;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DepartmentController extends Controller
{
    /**
     * List all departments with eager-loaded department head and employee count.
     */
    public function index(): AnonymousResourceCollection
    {
        $departments = Department::with(['head.user'])
            ->withCount('employees')
            ->paginate(15);

        return DepartmentResource::collection($departments);
    }

    /**
     * Create a new department.
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = Department::create($request->validated());

        return (new DepartmentResource($department->load('head.user')))
            ->additional(['status' => 'success', 'message' => 'Department created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * View a single department with its head details.
     */
    public function show(Department $department): DepartmentResource
    {
        return new DepartmentResource(
            $department->load('head.user')->loadCount('employees')
        );
    }

    /**
     * Update department details.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): DepartmentResource
    {
        $department->update($request->validated());

        return new DepartmentResource($department->load('head.user'));
    }

    /**
     * Delete department.
     */
    public function destroy(Department $department): JsonResponse
    {
        $department->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Department deleted successfully.',
        ]);
    }

    /**
     * View all employees assigned to a specific department.
     */
    public function employees(Department $department): AnonymousResourceCollection
    {
        $employees = $department->employees()
            ->with(['user', 'manager.user'])
            ->paginate(15);

        return EmployeeResource::collection($employees);
    }
}