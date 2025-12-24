<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    // Index – list all
    public function index()
    {
        $departments = Department::all();
        return $this->success($departments, 'Departments retrieved successfully');
    }

    // Store – create new
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        $department = Department::create($data);

        return $this->success($department, 'Department created successfully', 201);
    }

    // Show – get one
    public function show(Department $department)
    {
        return $this->success($department, 'Department retrieved successfully');
    }

    // Update – edit
    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $department->update($data);

        return $this->success($department, 'Department updated successfully');
    }

    // Delete
    public function destroy(Department $department)
    {
        $department->delete();
        return $this->success([], 'Department deleted successfully');
    }
}
