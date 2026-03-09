<?php

namespace App\Http\Controllers;

use App\Http\Resources\PositionResource;
use App\Models\Position;
use App\Models\PositionPrivilege;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    // ──────────────────────────────────────────
    // GET /api/admin/positions
    // ──────────────────────────────────────────
    public function getAllPositions(Request $request)
    {
        try {
            $page    = (int) $request->query('page', 1);
            $perPage = (int) $request->query('perPage', 25);
            $search  = $request->query('search');
            $level   = $request->query('level');
            $sort    = $request->query('sort', 'createdAt:desc');

            [$sortColumn, $sortDirection] = array_pad(explode(':', $sort), 2, 'desc');

            $query = Position::with([
                'positionPrivileges:id,positionId,privilegeId',
                'positionPrivileges.privilege:id,name,slug,moduleId',
                'positionPrivileges.privilege.module:id,name,slug',
            ]);

            if ($search) {
                $query->where('name', 'ilike', "%{$search}%");
            }

            if ($level) {
                $query->where('level', $level);
            }

            $total     = $query->count();
            $positions = $query->orderBy($sortColumn, $sortDirection)
                               ->paginate($perPage, ['*'], 'page', $page);

            if ($positions->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            return response()->json([
                'success' => true,
                'data'    => PositionResource::collection($positions->items()),
                'meta'    => [
                    'total'      => $total,
                    'page'       => $page,
                    'perPage'    => $perPage,
                    'totalPages' => $positions->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // POST /api/admin/positions
    // ──────────────────────────────────────────
    public function createAPosition(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'status'         => 'required|in:active,inactive',
            'level'          => 'required|string',
            'privilegeIds'   => 'nullable|array',
            'privilegeIds.*' => 'uuid',
        ]);

        try {
            $exists = Position::where('name', $request->input('name'))->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Position with the same name already exists.',
                ], 400);
            }

            $position = DB::transaction(function () use ($request) {
                $position = Position::create([
                    'name'   => $request->input('name'),
                    'status' => $request->input('status'),
                    'level'  => $request->input('level'),
                ]);

                if ($request->filled('privilegeIds')) {
                    $privileges = collect($request->input('privilegeIds'))->map(fn($privilegeId) => [
                        'positionId'  => $position->id,
                        'privilegeId' => $privilegeId,
                    ])->toArray();

                    PositionPrivilege::insert($privileges);
                }

                return $position;
            });

            return response()->json([
                'success' => true,
                'message' => 'Position created successfully.',
                'data'    => new PositionResource($position->load([
                    'positionPrivileges.privilege.module',
                ])),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create position',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────
    // PATCH /api/admin/positions/{positionId}
    // ──────────────────────────────────────────
    public function updateAPosition(Request $request, string $positionId)
    {
        $request->validate([
            'name'           => 'sometimes|string|max:255',
            'status'         => 'sometimes|in:active,inactive',
            'level'          => 'sometimes|string',
            'privilegeIds'   => 'nullable|array',
            'privilegeIds.*' => 'uuid',
        ]);

        try {
            $position = Position::find($positionId);

            if (!$position) {
                return response()->json([
                    'success' => false,
                    'message' => 'Position does not exist',
                ], 404);
            }

            // Check name uniqueness only if name is being changed
            if ($request->filled('name') && $request->input('name') !== $position->name) {
                $nameExists = Position::where('name', $request->input('name'))
                    ->where('id', '!=', $positionId)
                    ->exists();

                if ($nameExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Position with the same name already exists.',
                    ], 400);
                }
            }

            DB::transaction(function () use ($request, $position, $positionId) {
                $updatedData = array_filter([
                    'name'   => $request->input('name'),
                    'status' => $request->input('status'),
                    'level'  => $request->input('level'),
                ]);

                $position->update($updatedData);

                // Replace privileges if provided
                if ($request->filled('privilegeIds')) {
                    PositionPrivilege::where('positionId', $positionId)->delete();

                    $privileges = collect($request->input('privilegeIds'))->map(fn($privilegeId) => [
                        'positionId'  => $positionId,
                        'privilegeId' => $privilegeId,
                    ])->toArray();

                    PositionPrivilege::insert($privileges);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Position updated successfully.',
                'data'    => new PositionResource($position->fresh()->load([
                    'positionPrivileges.privilege.module',
                ])),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update position',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}