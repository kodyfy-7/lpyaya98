<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddNewMembersRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Models\Parish;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function getAllMembers(Request $request)
    {
        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('perPage', 25);
            $search = $request->query('search');
            $sort = $request->query('sort', 'createdAt:desc');
            $isAdmin = $request->query('isAdmin', 'false');
            $zoneId = $request->query('zoneId');
            $areaId = $request->query('areaId');
            $parishId = $request->query('parishId');
            $activity = $request->query('activity');

            [$sortColumn, $sortDirection] = array_pad(explode(':', $sort), 2, 'desc');

            $query = Member::with([
                'user',
                'area:id,name',
                'zone:id,name',
                'parish:id,name,areaId',
                'parish.area:id,name,zoneId',
                'parish.area.zone:id,name',
                'province:id,name',
                'department:id,name',
                'areaPosition:id,name',
                'zonePosition:id,name',
                'parishPosition:id,name',
            ])
                ->whereHas('user', function ($q) use ($search, $isAdmin, $activity) {
                    if ($search) {
                        $q->where('name', 'ilike', "%{$search}%");
                    }

                    if ($isAdmin === 'true') {
                        $q->where('isAdmin', true);
                    }

                    if ($activity === 'active') {
                        $q->whereNotNull('emailVerifiedAt')->whereNull('deactivatedAt');
                    }

                    if ($activity === 'pending') {
                        $q->whereNull('emailVerifiedAt');
                    }

                    if ($activity === 'deactivated') {
                        $q->whereNotNull('deactivatedAt');
                    }
                });

            if ($zoneId) {
                $query->where('zoneId', $zoneId);
            }
            if ($areaId) {
                $query->where('areaId', $areaId);
            }
            if ($parishId) {
                $query->where('parishId', $parishId);
            }

            $total = $query->count();
            $members = $query->orderBy($sortColumn, $sortDirection)
                ->paginate($perPage, ['*'], 'page', $page);

            if ($members->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            return response()->json([
                'success' => true,
                'data' => MemberResource::collection($members->items()),
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalPages' => $members->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAllMemberSummary(Request $request)
    {
        try {
            $isAdmin = $request->query('isAdmin');

            if ($isAdmin === 'true') {
                $base = User::whereHas('members')->where('isAdmin', true);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'active' => (clone $base)->whereNotNull('emailVerifiedAt')->count(),
                        'pending' => (clone $base)->whereNull('emailVerifiedAt')->count(),
                        'deactivated' => (clone $base)->whereNotNull('deactivatedAt')->count(),
                    ],
                ]);
            }

            // Scoped to requesting user's member context
            $member = $request->user()->members()->with(['parish', 'area', 'zone'])->first();

            $parishId = $member?->parish?->id;
            $areaId = $member?->area?->id;
            $zoneId = $member?->zone?->id;

            return response()->json([
                'success' => true,
                'data' => [
                    'totalMembers' => Member::count(),
                    'totalMembersParish' => Member::where('parishId', $parishId)->count(),
                    'totalMemberByZone' => Member::where('zoneId', $zoneId)->count(),
                    'totalMemberByArea' => Member::where('areaId', $areaId)->count(),
                    'totalMemberByDepartment' => Member::whereNotNull('departmentId')->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function addNewMembers(AddNewMembersRequest $request)
    {
        try {
            $parish = Parish::with('area.zone')->find($request->parishId);

            if (! $parish) {
                return response()->json(['success' => false, 'message' => 'Parish not found.'], 404);
            }

            $alreadyExists = [];

            DB::transaction(function () use ($request, &$alreadyExists) {
                foreach ($request->members as $memberData) {
                    $email = strtolower($memberData['email']);

                    $userExists = User::whereRaw('LOWER("email") = ?', [$email])->exists();

                    if ($userExists) {
                        $alreadyExists[] = $email;

                        continue;
                    }

                    $user = User::create([
                        'name' => $memberData['name'],
                        'dateOfBirth' => $memberData['dob'] ?? null,
                        'email' => $email,
                        'gender' => $memberData['gender'] ?? null,
                        'phoneNumber' => $memberData['phoneNumber'] ?? null,
                        'education' => $memberData['education'] ?? null,
                        'occupation' => $memberData['occupation'] ?? null,
                        'address' => $memberData['address'] ?? null,
                    ]);

                    Member::create([
                        'userId' => $user->id,
                        'zoneId' => $memberData['zoneId'] ?? null,
                        'zonePositionId' => $memberData['zonePositionId'] ?? null,
                        'areaId' => $memberData['areaId'] ?? null,
                        'areaPositionId' => $memberData['areaPositionId'] ?? null,
                        'provinceId' => $memberData['provinceId'] ?? null,
                        'provincePositionId' => $memberData['provincePositionId'] ?? null,
                        'parishId' => $request->parishId,
                        'parishPositionId' => $memberData['parishPositionId'] ?? null,
                        'departmentId' => $memberData['departmentId'] ?? null,
                    ]);
                }
            });

            $message = 'Membership data saved successfully.';
            if (count($alreadyExists) > 0) {
                $message .= ' However, some members already exist.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $alreadyExists,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateMember(UpdateMemberRequest $request, string $memberId)
    {
        try {
            $member = Member::find($memberId);

            if (! $member) {
                return response()->json(['success' => false, 'message' => 'Member not found'], 404);
            }

            // Update the linked user profile
            User::where('id', $member->userId)->update([
                'name' => $request->name,
                'dateOfBirth' => $request->dob,
                'gender' => $request->gender,
                'phoneNumber' => $request->phoneNumber,
                'education' => $request->education,
                'occupation' => $request->occupation,
                'address' => $request->address,
            ]);

            // Update member assignments
            $member->update([
                'zoneId' => $request->zoneId,
                'areaId' => $request->areaId,
                'parishId' => $request->parishId,
                'departmentId' => $request->departmentId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Member updated successfully',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateMemberStatus(Request $request, string $memberId, string $status)
    {
        try {
            $member = Member::find($memberId);

            if (! $member) {
                return response()->json(['success' => false, 'message' => 'Member not found'], 404);
            }

            $user = User::find($member->userId);

            if ($status === 'activate') {
                $user->update([
                    'emailVerifiedAt' => now(),
                    'deactivatedAt' => null,
                ]);

                return response()->json(['success' => true, 'message' => 'Member activated successfully']);
            }

            if ($status === 'deactivate') {
                $user->update(['deactivatedAt' => now()]);

                return response()->json(['success' => true, 'message' => 'Member deactivated successfully']);
            }

            return response()->json(['success' => true, 'message' => 'Successfully']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateMembership(Request $request)
    {
        $request->validate([
            'userId' => 'required|uuid|exists:users,id',
            'zoneId' => 'nullable|uuid',
            'zonePositionId' => 'nullable|uuid',
            'areaId' => 'nullable|uuid',
            'areaPositionId' => 'nullable|uuid',
            'provinceId' => 'nullable|uuid',
            'provincePositionId' => 'nullable|uuid',
            'parishId' => 'nullable|uuid',
            'parishPositionId' => 'nullable|uuid',
            'departmentId' => 'nullable|uuid',
        ]);

        try {
            $user = User::find($request->userId);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $memberData = $request->only([
                'userId', 'zoneId', 'zonePositionId',
                'areaId', 'areaPositionId',
                'provinceId', 'provincePositionId',
                'parishId', 'parishPositionId',
                'departmentId',
            ]);

            DB::transaction(function () use ($memberData, $request) {
                $existingMember = Member::where('userId', $request->userId)->first();

                if (! $existingMember) {
                    Member::create($memberData);
                } else {
                    $existingMember->update($memberData);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Membership data saved successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'System optimization in progress, please wait',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
