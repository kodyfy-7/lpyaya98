<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterForEventRequest;
use App\Http\Resources\EventParticipantResource;
use App\Http\Resources\EventResource;
use App\Models\Area;
// use App\Services\MailService;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Parish;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    // public function __construct(protected MailService $mailService) {}

    // ──────────────────────────────────────────
    // GET /api/events/{eventId}
    // ──────────────────────────────────────────
    public function listAnEvent(string $eventId)
    {
        $event = Event::find($eventId);

        if (! $event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new EventResource($event),
            // 'data'    => $event,
        ]);
    }

    public function registerForAnEvent(RegisterForEventRequest $request, string $eventId)
    {
        DB::beginTransaction();
        try {
            $event = Event::select('id', 'title', 'startDate', 'startTime', 'location', 'registrationFee')
                ->find($eventId);

            if (! $event) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Event not found',
                ], 404);
            }

            $alreadyRegistered = EventParticipant::where('eventId', $eventId)
                ->where(function ($q) use ($request) {
                    $q->where('phoneNumber', $request->phoneNumber)
                        ->orWhere('email', $request->email);
                })
                ->exists();

            if ($alreadyRegistered) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => "It appears you have already registered for this event using this phone number ({$request->phoneNumber}) or email ({$request->email})",
                ], 400);
            }

            $lastRegNumber = EventParticipant::where('eventId', $eventId)
                ->latest('createdAt')
                ->value('registrationNumber') ?? '00000';

            $newRegistrationNumber = str_pad((int) $lastRegNumber + 1, 5, '0', STR_PAD_LEFT);

            $participant = EventParticipant::create([
                'eventId' => $eventId,
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'phoneNumber' => $request->phoneNumber,
                'zoneId' => $request->zoneId,
                'areaId' => $request->areaId,
                'parishId' => $request->parishId,
                'location' => $request->location,
                'registrationApproved' => $event->registrationFee == 0,
                'registrationNumber' => $newRegistrationNumber,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Event registered successfully',
                'data' => [
                    'registrationNumber' => $participant->registrationNumber,
                ],
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function eventParticipants(Request $request, string $eventId)
    {
        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('perPage', 25);
            $search = $request->query('search');
            $zoneId = $request->query('zoneId');
            $areaId = $request->query('areaId');
            $parishId = $request->query('parishId');
            $attended = $request->query('attended');
            $regApproved = $request->query('registrationApproved');
            $sort = $request->query('sort', 'createdAt:desc');

            [$sortColumn, $sortDirection] = array_pad(explode(':', $sort), 2, 'desc');

            $query = EventParticipant::with([
                'parish:id,name,areaId',
                'parish.area:id,name,zoneId',
                'parish.area.zone:id,name',
            ])
                ->whereHas('event', fn ($q) => $q->where('id', $eventId));

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('registrationNumber', 'ilike', "%{$search}%");
                });
            }

            if ($zoneId) {
                $query->where('zoneId', $zoneId);
            }
            if ($areaId) {
                $query->where('areaId', $areaId);
            }
            if ($parishId) {
                $query->where('parishId', $parishId);
            }

            if ($attended !== null) {
                $query->where('attended', filter_var($attended, FILTER_VALIDATE_BOOLEAN));
            }

            if ($regApproved !== null) {
                $query->where('registrationApproved', filter_var($regApproved, FILTER_VALIDATE_BOOLEAN));
            }

            $total = $query->count();
            $participants = $query->orderBy($sortColumn, $sortDirection)
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => EventParticipantResource::collection($participants->items()),
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalPages' => $participants->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // PATCH /api/events/{eventId}/participants/{eventParticipantId}/attendance
    // ──────────────────────────────────────────
    public function updateEventParticipantAttendance(Request $request, string $eventId, string $eventParticipantId)
    {
        try {
            $request->validate(['isAttended' => 'required|boolean']);

            $event = Event::select('id', 'title', 'registrationFee')->find($eventId);

            if (! $event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found.',
                ], 404);
            }

            $participant = EventParticipant::where('eventId', $eventId)
                ->where('id', $eventParticipantId)
                ->first();

            if (! $participant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participant not found.',
                ], 400);
            }

            $participant->update(['attended' => $request->isAttended]);

            return response()->json([
                'success' => true,
                'message' => 'Event participant updated successfully',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // GET /api/events/{eventId}/participants/counts?by=zone|area
    // ──────────────────────────────────────────
    public function eventParticipantCounts(Request $request, string $eventId)
    {
        try {
            $by = $request->query('by');

            if (! in_array($by, ['zone', 'area'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid 'by' parameter. Use 'zone' or 'area'.",
                ], 400);
            }

            $aggregationField = $by === 'zone' ? 'zoneId' : 'areaId';
            $relatedTable = $by === 'zone' ? 'zones' : 'areas';

            $counts = EventParticipant::select([
                DB::raw("event_participants.\"{$aggregationField}\""),
                DB::raw('COUNT(event_participants.id) as "totalRegistered"'),
                DB::raw('SUM(CASE WHEN event_participants."attended" = true THEN 1 ELSE 0 END) as "totalAttended"'),
                DB::raw("\"{$relatedTable}\".\"name\" as \"{$by}Name\""),
            ])
                ->join($relatedTable, "event_participants.{$aggregationField}", '=', "{$relatedTable}.id")
                ->where('event_participants.eventId', $eventId)
                ->groupBy("event_participants.{$aggregationField}", "{$relatedTable}.name")
                ->get();

            $formatted = $counts->map(fn ($row) => [
                $by => $row->{"{$by}Name"},
                'totalRegistered' => (int) $row->totalRegistered,
                'totalAttended' => (int) $row->totalAttended,
                'totalOnlyRegistered' => (int) $row->totalRegistered - (int) $row->totalAttended,
            ]);

            return response()->json([
                'success' => true,
                'data' => $formatted,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // GET /api/admin/events
    // ──────────────────────────────────────────
    public function listEvents(Request $request)
    {
        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('perPage', 25);
            $search = $request->query('search');
            $sort = $request->query('sort', 'createdAt:desc');

            [$sortColumn, $sortDirection] = array_pad(explode(':', $sort), 2, 'desc');

            $query = Event::select('events.*')
                ->selectRaw('COUNT(DISTINCT participants.id) as "registeredCount"')
                ->selectRaw('SUM(CASE WHEN participants."attended" = true THEN 1 ELSE 0 END) as "attendedCount"')
                ->leftJoin('event_participants as participants', function ($join) {
                    $join->on('participants."eventId"', '=', 'events.id')
                        ->whereNull('participants."deletedAt"');
                })
                ->groupBy('events.id');

            if ($search) {
                $query->where('events.title', 'ilike', "%{$search}%");
            }

            $events = $query->orderBy("events.{$sortColumn}", $sortDirection)
                ->paginate($perPage, ['*'], 'page', $page);

            if ($events->isEmpty()) {
                return response()->json(['success' => true, 'data' => [], 'meta' => []]);
            }

            $data = $events->map(function ($event) {
                $host = null;

                if ($event->parentId) {
                    $host = match ($event->type) {
                        'zone' => Zone::select('name')->find($event->parentId)?->name,
                        'area' => Area::select('name')->find($event->parentId)?->name,
                        'parish' => Parish::select('name')->find($event->parentId)?->name,
                        default => null,
                    };
                }

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'type' => $event->type,
                    'startDate' => $event->startDate,
                    'endDate' => $event->endDate,
                    'startTime' => $event->startTime,
                    'registrationFee' => $event->registrationFee,
                    'location' => $event->location,
                    'createdAt' => $event->createdAt,
                    'registeredCount' => (int) $event->registeredCount,
                    'attendedCount' => (int) $event->attendedCount,
                    'host' => $host,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => $events->total(),
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalPages' => $events->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // GET /api/admin/events/summary
    // ──────────────────────────────────────────
    public function eventsSummary()
    {
        try {
            // single query instead of 4 separate counts
            $counts = Event::select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type');

            return response()->json([
                'success' => true,
                'data' => [
                    'provinceEvents' => (int) ($counts['province'] ?? 0),
                    'zoneEvents' => (int) ($counts['zone'] ?? 0),
                    'areaEvents' => (int) ($counts['area'] ?? 0),
                    'parishEvents' => (int) ($counts['parish'] ?? 0),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // POST /api/admin/events
    // ──────────────────────────────────────────
    public function createEvent(CreateEventRequest $request)
    {
        try {
            $event = Event::create([
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'parentId' => $request->parentId,
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
                'startTime' => $request->startTime,
                'registrationFee' => $request->registrationFee ?? 0,
                'location' => $request->location,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => new EventResource($event),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────
    // PATCH /api/admin/events/{eventId}
    // ──────────────────────────────────────────
    public function updateEvent(UpdateEventRequest $request, string $eventId)
    {
        try {
            $event = Event::find($eventId);

            if (! $event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found.',
                ], 404);
            }

            $event->update($request->only([
                'title', 'description', 'type', 'parentId',
                'startDate', 'endDate', 'startTime',
                'registrationFee', 'location',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'data' => new EventResource($event->fresh()),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
