<?php

namespace App\Http\Controllers;

use App\Http\Resources\AreaResource;
use App\Models\Area;
use App\Models\Zone;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function getAllAreas(Request $request)
    {
        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('perPage', 25);
            $search = $request->query('search');
            $sort = $request->query('sort', 'createdAt:desc');

            [$sortColumn, $sortDirection] = array_pad(explode(':', $sort), 2, 'desc');

            $query = Area::with('parishes');

            if ($search) {
                $query->where('name', 'ilike', "%{$search}%");
            }

            $total = $query->count();
            $areas = $query->orderBy($sortColumn, $sortDirection)
                ->paginate($perPage, ['*'], 'page', $page);

            if ($areas->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            return response()->json([
                'success' => true,
                'data' => AreaResource::collection($areas->items()),
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalPages' => $areas->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // POST /api/admin/areas
    // ──────────────────────────────────────────
    public function createAArea(Request $request)
    {
        $request->validate([
            'zoneId' => 'required|uuid',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $zone = Zone::find($request->zoneId);

            if (! $zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zone does not exist',
                ], 400);
            }

            $exists = Area::where('name', $request->name)->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Area with the same name already exists.',
                ], 400);
            }

            $area = Area::create([
                'zoneId' => $request->zoneId,
                'name' => $request->name,
                'status' => $request->status ?? 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Area created successfully.',
                'data' => new AreaResource($area),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // PATCH /api/admin/areas/{areaId}
    // ──────────────────────────────────────────
    public function updateAArea(Request $request, string $areaId)
    {
        $request->validate([
            'zoneId' => 'required|uuid',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $area = Area::find($areaId);

            if (! $area) {
                return response()->json([
                    'success' => false,
                    'message' => 'Area not found.',
                ], 404);
            }

            $zone = Zone::find($request->zoneId);

            if (! $zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zone does not exist',
                ], 400);
            }

            $nameExists = Area::where('name', $request->name)
                ->where('id', '!=', $areaId)
                ->exists();

            if ($nameExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Area with the same name already exists.',
                ], 400);
            }

            $area->update([
                'zoneId' => $request->zoneId,
                'name' => $request->name,
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Area updated successfully.',
                'data' => new AreaResource($area->fresh()),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
