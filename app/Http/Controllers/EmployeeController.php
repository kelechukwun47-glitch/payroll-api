<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function index(): JsonResponse
    {
        $employees = Employee::with(['user', 'department', 'manager.user'])->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $employees,
        ]);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = Employee::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Employee profile created successfully.',
            'data' => $employee->load(['user', 'department', 'manager.user']),
        ], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $employee->load(['user', 'department', 'manager.user', 'directReports.user']),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Employee profile updated successfully.',
            'data' => $employee->fresh(['user', 'department', 'manager.user']),
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Employee profile deleted successfully.',
        ]);
    }
}