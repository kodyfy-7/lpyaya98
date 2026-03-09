<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    // ──────────────────────────────────────────
    // GET /api/admin/provinces
    // ──────────────────────────────────────────
    public function getAllProvinces(Request $request)
    {
        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('perPage', 25);
            $search = $request->query('search');
            $sort = $request->query('sort', 'createdAt:desc');

            [$sortColumn, $sortDirection] = array_pad(explode(':', $sort), 2, 'desc');

            $query = Province::query();

            if ($search) {
                $query->where('name', 'ilike', "%{$search}%");
            }

            $total = $query->count();
            $provinces = $query->orderBy($sortColumn, $sortDirection)
                ->paginate($perPage, ['*'], 'page', $page);

            if ($provinces->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            return response()->json([
                'success' => true,
                'data' => ProvinceResource::collection($provinces->items()),
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalPages' => $provinces->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // POST /api/admin/provinces
    // ──────────────────────────────────────────
    public function createAProvince(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $exists = Province::where('name', $request->input('name'))->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Province with the same name already exists.',
                ], 400);
            }

            $province = Province::create([
                'name' => $request->input('name'),
                'status' => $request->input('status', 'active'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Province created successfully.',
                'data' => new ProvinceResource($province),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // PATCH /api/admin/provinces/{provinceId}
    // ──────────────────────────────────────────
    public function updateAProvince(Request $request, string $provinceId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $province = Province::find($provinceId);

            if (! $province) {
                return response()->json([
                    'success' => false,
                    'message' => 'Province not found.',
                ], 404);
            }

            $nameExists = Province::where('name', $request->input('name'))
                ->where('id', '!=', $provinceId)
                ->exists();

            if ($nameExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Province with the same name already exists.',
                ], 400);
            }

            $province->update([
                'name' => $request->input('name'),
                'status' => $request->input('status'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Province updated successfully.',
                'data' => new ProvinceResource($province->fresh()),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
