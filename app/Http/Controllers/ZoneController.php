<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Http\Resources\ZoneResource;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    // ──────────────────────────────────────────
    // GET /api/admin/zones
    // ──────────────────────────────────────────
    public function getAllZones(Request $request)
    {
        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('perPage', 25);
            $search = $request->query('search');
            $sort = $request->query('sort', 'createdAt:desc');

            [$sortColumn, $sortDirection] = array_pad(explode(':', $sort), 2, 'desc');

            $query = Zone::with([
                'areas.parishes',
            ]);

            if ($search) {
                $query->where('name', 'ilike', "%{$search}%");
            }

            $total = $query->count();
            $zones = $query->orderBy($sortColumn, $sortDirection)
                ->paginate($perPage, ['*'], 'page', $page);

            if ($zones->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            return response()->json([
                'success' => true,
                'data' => ZoneResource::collection($zones->items()),
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalPages' => $zones->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function createAZone(CreateZoneRequest $request)
    {
        try {
            $exists = Zone::where('name', $request->name)->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zone with the same name already exists.',
                ], 400);
            }

            $zone = Zone::create([
                'name' => $request->name,
                'status' => $request->status ?? 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Zone created successfully.',
                'data' => new ZoneResource($zone),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateAZone(UpdateZoneRequest $request, string $zoneId)
    {
        try {
            $zone = Zone::find($zoneId);

            if (! $zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zone not found.',
                ], 404);
            }

            $nameExists = Zone::where('name', $request->name)
                ->where('id', '!=', $zoneId)
                ->exists();

            if ($nameExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zone with the same name already exists.',
                ], 400);
            }

            $zone->update([
                'name' => $request->name,
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Zone updated successfully.',
                'data' => new ZoneResource($zone->fresh()),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
