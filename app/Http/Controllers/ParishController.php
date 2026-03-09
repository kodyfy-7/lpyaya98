<?php

namespace App\Http\Controllers;

use App\Http\Resources\ParishResource;
use App\Models\Area;
use App\Models\Parish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParishController extends Controller
{
    // ──────────────────────────────────────────
    // GET /api/admin/parishes
    // ──────────────────────────────────────────
    public function getAllParishes(Request $request)
    {
        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('perPage', 25);
            $search = $request->query('search');
            $sort = $request->query('sort', 'createdAt:desc');

            [$sortColumn, $sortDirection] = array_pad(explode(':', $sort), 2, 'desc');

            $query = Parish::query();

            if ($search) {
                $query->where('name', 'ilike', "%{$search}%");
            }

            $total = $query->count();
            $parishes = $query->orderBy($sortColumn, $sortDirection)
                ->paginate($perPage, ['*'], 'page', $page);

            if ($parishes->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            return response()->json([
                'success' => true,
                'data' => ParishResource::collection($parishes->items()),
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalPages' => $parishes->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // POST /api/admin/parishes
    // ──────────────────────────────────────────
    public function createAParish(Request $request)
    {
        $request->validate([
            'areaId' => 'required|uuid',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $area = Area::find($request->input('areaId'));

            if (! $area) {
                return response()->json([
                    'success' => false,
                    'message' => 'Area does not exist',
                ], 400);
            }

            $exists = Parish::where('name', $request->input('name'))
                ->where('areaId', $request->input('areaId'))
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parish with the same name already exists.',
                ], 400);
            }

            $parish = Parish::create([
                'areaId' => $request->input('areaId'),
                'name' => $request->input('name'),
                'status' => $request->input('status', 'active'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parish created successfully.',
                'data' => new ParishResource($parish),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // POST /api/admin/parishes/upload
    // ──────────────────────────────────────────
    public function uploadParishes(Request $request)
    {
        $request->validate([
            'areaId' => 'required|uuid',
            'parishes' => 'required|array|min:1',
            'parishes.*.name' => 'required|string|max:255',
        ]);

        try {
            $area = Area::find($request->input('areaId'));

            if (! $area) {
                return response()->json([
                    'success' => false,
                    'message' => 'Area does not exist',
                ], 400);
            }

            DB::transaction(function () use ($request) {
                foreach ($request->input('parishes') as $parishData) {
                    $exists = Parish::where('name', $parishData['name'])
                        ->where('areaId', $request->input('areaId'))
                        ->exists();

                    if (! $exists) {
                        Parish::create([
                            'areaId' => $request->input('areaId'),
                            'name' => $parishData['name'],
                            'status' => 'active',
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Parishes uploaded successfully.',
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // PATCH /api/admin/parishes/{parishId}
    // ──────────────────────────────────────────
    public function updateAParish(Request $request, string $parishId)
    {
        $request->validate([
            'areaId' => 'required|uuid',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $area = Area::find($request->input('areaId'));

            if (! $area) {
                return response()->json([
                    'success' => false,
                    'message' => 'Area not found.',
                ], 400);
            }

            $parish = Parish::find($parishId);

            if (! $parish) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parish does not exist.',
                ], 404);
            }

            $nameExists = Parish::where('name', $request->input('name'))
                ->where('areaId', $request->input('areaId'))
                ->where('id', '!=', $parishId)
                ->exists();

            if ($nameExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parish with the same name already exists in this area.',
                ], 409);
            }

            $parish->update([
                'areaId' => $request->input('areaId'),
                'name' => $request->input('name'),
                'status' => $request->input('status'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parish updated successfully.',
                'data' => new ParishResource($parish->fresh()),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
