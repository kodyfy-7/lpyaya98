<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    // ──────────────────────────────────────────
    // GET /api/admin/departments
    // ──────────────────────────────────────────
    public function getAllDepartments(Request $request)
    {
        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('perPage', 25);
            $search = $request->query('search');
            $sort = $request->query('sort', 'createdAt:desc');

            [$sortColumn, $sortDirection] = array_pad(explode(':', $sort), 2, 'desc');

            $query = Department::query();

            if ($search) {
                $query->where('name', 'ilike', "%{$search}%");
            }

            $total = $query->count();
            $departments = $query->orderBy($sortColumn, $sortDirection)
                ->paginate($perPage, ['*'], 'page', $page);

            if ($departments->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            return response()->json([
                'success' => true,
                'data' => DepartmentResource::collection($departments->items()),
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalPages' => $departments->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // POST /api/admin/departments
    // ──────────────────────────────────────────
    public function createADepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $exists = Department::where('name', $request->input('name'))->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department with the same name already exists.',
                ], 400);
            }

            $department = Department::create([
                'name' => $request->input('name'),
                'status' => $request->input('status', 'active'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department created successfully.',
                'data' => new DepartmentResource($department),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // PATCH /api/admin/departments/{departmentId}
    // ──────────────────────────────────────────
    public function updateADepartment(Request $request, string $departmentId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $department = Department::find($departmentId);

            if (! $department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department does not exist',
                ], 404);
            }

            $nameExists = Department::where('name', $request->input('name'))
                ->where('id', '!=', $departmentId)
                ->exists();

            if ($nameExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department with the same name already exists.',
                ], 400);
            }

            $department->update([
                'name' => $request->input('name'),
                'status' => $request->input('status'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully.',
                'data' => new DepartmentResource($department->fresh()),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
