<?php

// EventController.php'ye eklenecek metodlar
namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\JsonResponse; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ProgramSession;
use App\Models\Sponsor;
use App\Models\Venue;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Events",
 *     description="Event management operations"
 * )
 */
class EventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/ajax/events/for-select",
     *     tags={"Events", "AJAX"},
     *     summary="Get events for select dropdown",
     *     description="Returns a list of events formatted for use in select dropdowns",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for event name",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=100)
     *     ),
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filter by organization ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Events retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="value", type="integer", example=1),
     *                     @OA\Property(property="label", type="string", example="AI Conference 2025"),
     *                     @OA\Property(property="slug", type="string", example="ai-conference-2025"),
     *                     @OA\Property(property="date_range", type="string", example="15.06.2025 - 17.06.2025"),
     *                     @OA\Property(property="is_published", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Events could not be loaded."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getForSelect(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $query = Event::query();

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereIn('organization_id', $organizationIds);
            }

            // Search filter
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Organization filter
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            $events = $query->orderBy('start_date', 'desc')
                ->limit(50)
                ->get(['id', 'name', 'slug', 'start_date', 'end_date', 'is_published']);

            return response()->json([
                'success' => true,
                'data' => $events->map(function ($event) {
                    return [
                        'value' => $event->id,
                        'label' => $event->name,
                        'slug' => $event->slug,
                        'date_range' => $event->start_date->format('d.m.Y') . ' - ' . $event->end_date->format('d.m.Y'),
                        'is_published' => $event->is_published,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Events could not be loaded.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/events/{event}/analytics",
     *     tags={"Events", "Analytics"},
     *     summary="Get event statistics for dashboard",
     *     description="Returns comprehensive analytics data for a specific event",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Analytics data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="basic",
     *                     type="object",
     *                     @OA\Property(property="total_days", type="integer", example=3),
     *                     @OA\Property(property="total_venues", type="integer", example=5),
     *                     @OA\Property(property="total_sessions", type="integer", example=24),
     *                     @OA\Property(property="total_presentations", type="integer", example=48),
     *                     @OA\Property(property="total_speakers", type="integer", example=32),
     *                     @OA\Property(property="total_moderators", type="integer", example=12)
     *                 ),
     *                 @OA\Property(
     *                     property="by_day",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="date", type="string", format="date", example="2025-06-15"),
     *                         @OA\Property(property="title", type="string", example="Gün 1"),
     *                         @OA\Property(property="venues", type="integer", example=5),
     *                         @OA\Property(property="sessions", type="integer", example=8),
     *                         @OA\Property(property="presentations", type="integer", example=16)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="by_session_type",
     *                     type="object",
     *                     @OA\Property(property="main", type="integer", example=12),
     *                     @OA\Property(property="satellite", type="integer", example=6),
     *                     @OA\Property(property="oral_presentation", type="integer", example=24)
     *                 ),
     *                 @OA\Property(
     *                     property="sponsors",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=8),
     *                     @OA\Property(
     *                         property="by_level",
     *                         type="object",
     *                         @OA\Property(property="platinum", type="integer", example=2),
     *                         @OA\Property(property="gold", type="integer", example=3),
     *                         @OA\Property(property="silver", type="integer", example=3)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Event not found")
     *         )
     *     )
     * )
     */
    public function analytics(Event $event): JsonResponse
    {
        try {
            $this->authorize('view', $event);

            $statistics = [
                'basic' => [
                    'total_days' => $event->eventDays()->count(),
                    'total_venues' => $event->venues()->count(),
                    'total_sessions' => $event->programSessions()->count(),
                    'total_presentations' => $event->presentations()->count(),
                    'total_speakers' => $event->speakers()->count(),
                    'total_moderators' => $event->moderators()->count(),
                ],
                'by_day' => $event->eventDays()
                    ->withCount(['venues', 'programSessions', 'presentations'])
                    ->get()
                    ->map(function ($day) {
                        return [
                            'date' => $day->date,
                            'title' => $day->title,
                            'venues' => $day->venues_count,
                            'sessions' => $day->program_sessions_count,
                            'presentations' => $day->presentations_count,
                        ];
                    }),
                'by_session_type' => $event->programSessions()
                    ->selectRaw('session_type, COUNT(*) as count')
                    ->groupBy('session_type')
                    ->pluck('count', 'session_type'),
                'sponsors' => [
                    'total' => $event->organization->sponsors()->active()->count(),
                    'by_level' => $event->organization->sponsors()
                        ->active()
                        ->selectRaw('sponsor_level, COUNT(*) as count')
                        ->groupBy('sponsor_level')
                        ->pluck('count', 'sponsor_level'),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving event analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

/**
 * @OA\Tag(
 *     name="Participants",
 *     description="Participant management operations"
 * )
 */
class ParticipantController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/ajax/participants/for-select",
     *     tags={"Participants", "AJAX"},
     *     summary="Get participants for select dropdown with role filtering",
     *     description="Returns a list of participants formatted for use in select dropdowns, with optional role filtering",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by participant role",
     *         required=false,
     *         @OA\Schema(type="string", enum={"speaker", "moderator", "both"})
     *     ),
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filter by organization ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for participant name",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participants retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="value", type="integer", example=1),
     *                     @OA\Property(property="label", type="string", example="Dr. Ahmet Yılmaz"),
     *                     @OA\Property(property="title", type="string", example="Prof. Dr."),
     *                     @OA\Property(property="affiliation", type="string", example="İstanbul Üniversitesi"),
     *                     @OA\Property(
     *                         property="roles",
     *                         type="object",
     *                         @OA\Property(property="speaker", type="boolean", example=true),
     *                         @OA\Property(property="moderator", type="boolean", example=false)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="role",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected role is invalid.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getForSelect(Request $request): JsonResponse
    {
        $request->validate([
            'role' => 'nullable|in:speaker,moderator,both',
            'organization_id' => 'nullable|exists:organizations,id',
            'search' => 'nullable|string|max:100',
        ]);

        try {
            $user = auth()->user();
            $query = Participant::query();

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereIn('organization_id', $organizationIds);
            }

            // Organization filter
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Role filter
            if ($request->filled('role')) {
                switch ($request->role) {
                    case 'speaker':
                        $query->where('is_speaker', true);
                        break;
                    case 'moderator':
                        $query->where('is_moderator', true);
                        break;
                    case 'both':
                        $query->where('is_speaker', true)->where('is_moderator', true);
                        break;
                }
            }

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            }

            $participants = $query->orderBy('first_name')
                ->orderBy('last_name')
                ->limit(50)
                ->get(['id', 'first_name', 'last_name', 'title', 'affiliation', 'is_speaker', 'is_moderator']);

            return response()->json([
                'success' => true,
                'data' => $participants->map(function ($participant) {
                    return [
                        'value' => $participant->id,
                        'label' => trim($participant->first_name . ' ' . $participant->last_name),
                        'title' => $participant->title,
                        'affiliation' => $participant->affiliation,
                        'roles' => [
                            'speaker' => $participant->is_speaker,
                            'moderator' => $participant->is_moderator,
                        ],
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Participants could not be loaded.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

/**
 * @OA\Tag(
 *     name="Venues",
 *     description="Venue management operations"
 * )
 */
class VenueController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/ajax/venues/for-select",
     *     tags={"Venues", "AJAX"},
     *     summary="Get venues for select dropdown",
     *     description="Returns a list of venues formatted for use in select dropdowns",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event_id",
     *         in="query",
     *         description="Filter by event ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="event_day_id",
     *         in="query",
     *         description="Filter by event day ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for venue name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Venues retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="value", type="integer", example=1),
     *                     @OA\Property(property="label", type="string", example="Ana Salon"),
     *                     @OA\Property(property="capacity", type="integer", example=500),
     *                     @OA\Property(property="color", type="string", example="#FF5733"),
     *                     @OA\Property(
     *                         property="event_day",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Gün 1"),
     *                         @OA\Property(property="date", type="string", format="date", example="2025-06-15")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getForSelect(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $query = Venue::with('eventDay.event');

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereHas('eventDay.event', function ($q) use ($organizationIds) {
                    $q->whereIn('organization_id', $organizationIds);
                });
            }

            // Event filter
            if ($request->filled('event_id')) {
                $query->whereHas('eventDay', function ($q) use ($request) {
                    $q->where('event_id', $request->event_id);
                });
            }

            // Event day filter
            if ($request->filled('event_day_id')) {
                $query->where('event_day_id', $request->event_day_id);
            }

            // Search filter
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $venues = $query->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $venues->map(function ($venue) {
                    return [
                        'value' => $venue->id,
                        'label' => $venue->display_name ?? $venue->name,
                        'capacity' => $venue->capacity,
                        'color' => $venue->color,
                        'event_day' => [
                            'id' => $venue->eventDay->id,
                            'title' => $venue->eventDay->title,
                            'date' => $venue->eventDay->date,
                        ],
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Venues could not be loaded.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/venues/{venue}/check-availability",
     *     tags={"Venues"},
     *     summary="Check venue availability for specific time",
     *     description="Checks if a venue is available for a specific time range and returns any conflicts",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="venue",
     *         in="path",
     *         description="Venue ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"start_time", "end_time"},
     *             @OA\Property(property="start_time", type="string", format="time", example="09:00"),
     *             @OA\Property(property="end_time", type="string", format="time", example="10:30"),
     *             @OA\Property(property="exclude_session_id", type="integer", example=5, description="Session ID to exclude from conflict check")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Availability check completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="is_available", type="boolean", example=false),
     *                 @OA\Property(
     *                     property="conflicts",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="title", type="string", example="Açılış Oturumu"),
     *                         @OA\Property(property="start_time", type="string", format="time", example="09:30"),
     *                         @OA\Property(property="end_time", type="string", format="time", example="11:00"),
     *                         @OA\Property(property="session_type", type="string", example="main")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="end_time",
     *                     type="array",
     *                     @OA\Items(type="string", example="The end time must be after start time.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function checkAvailability(Request $request, Venue $venue): JsonResponse
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'exclude_session_id' => 'nullable|exists:program_sessions,id',
        ]);

        try {
            $conflicts = ProgramSession::where('venue_id', $venue->id)
                ->when($request->exclude_session_id, function ($q) use ($request) {
                    $q->where('id', '!=', $request->exclude_session_id);
                })
                ->where(function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('start_time', '<=', $request->start_time)
                          ->where('end_time', '>', $request->start_time);
                    })->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                          ->where('end_time', '>=', $request->end_time);
                    })->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '>=', $request->start_time)
                          ->where('end_time', '<=', $request->end_time);
                    });
                })
                ->with('categories')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'is_available' => $conflicts->isEmpty(),
                    'conflicts' => $conflicts->map(function ($session) {
                        return [
                            'id' => $session->id,
                            'title' => $session->title,
                            'start_time' => $session->start_time,
                            'end_time' => $session->end_time,
                            'session_type' => $session->session_type,
                        ];
                    }),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking venue availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

/**
 * @OA\Tag(
 *     name="Sponsors",
 *     description="Sponsor management operations"
 * )
 */
class SponsorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/ajax/sponsors/for-select",
     *     tags={"Sponsors", "AJAX"},
     *     summary="Get sponsors for select dropdown",
     *     description="Returns a list of active sponsors formatted for use in select dropdowns",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filter by organization ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sponsor_level",
     *         in="query",
     *         description="Filter by sponsor level",
     *         required=false,
     *         @OA\Schema(type="string", enum={"platinum", "gold", "silver", "bronze"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for sponsor name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sponsors retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="value", type="integer", example=1),
     *                     @OA\Property(property="label", type="string", example="TechCorp Inc."),
     *                     @OA\Property(property="level", type="string", example="platinum"),
     *                     @OA\Property(property="logo_url", type="string", example="https://example.com/storage/logos/techcorp.png"),
     *                     @OA\Property(property="website_url", type="string", example="https://techcorp.com")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getForSelect(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $query = Sponsor::where('is_active', true);

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereIn('organization_id', $organizationIds);
            }

            // Organization filter
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Level filter
            if ($request->filled('sponsor_level')) {
                $query->where('sponsor_level', $request->sponsor_level);
            }

            // Search filter
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $sponsors = $query->orderBy('sponsor_level')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(50)
                ->get(['id', 'name', 'sponsor_level', 'logo', 'website_url']);

            return response()->json([
                'success' => true,
                'data' => $sponsors->map(function ($sponsor) {
                    return [
                        'value' => $sponsor->id,
                        'label' => $sponsor->name,
                        'level' => $sponsor->sponsor_level,
                        'logo_url' => $sponsor->logo ? asset('storage/' . $sponsor->logo) : null,
                        'website_url' => $sponsor->website_url,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsors could not be loaded.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

/**
 * @OA\Tag(
 *     name="Program Sessions",
 *     description="Program session management operations"
 * )
 */
class ProgramSessionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/ajax/program-sessions/for-select",
     *     tags={"Program Sessions", "AJAX"},
     *     summary="Get program sessions for select dropdown",
     *     description="Returns a list of program sessions formatted for use in select dropdowns",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event_id",
     *         in="query",
     *         description="Filter by event ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="venue_id",
     *         in="query",
     *         description="Filter by venue ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for session title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Program sessions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="value", type="integer", example=1),
     *                     @OA\Property(property="label", type="string", example="Açılış Oturumu"),
     *                     @OA\Property(property="start_time", type="string", format="time", example="09:00:00"),
     *                     @OA\Property(property="end_time", type="string", format="time", example="10:30:00"),
     *                     @OA\Property(property="session_type", type="string", example="main"),
     *                     @OA\Property(
     *                         property="venue",
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Ana Salon"),
     *                         @OA\Property(property="event_day", type="string", example="Gün 1")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getForSelect(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $query = ProgramSession::with(['venue.eventDay.event']);

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereHas('venue.eventDay.event', function ($q) use ($organizationIds) {
                    $q->whereIn('organization_id', $organizationIds);
                });
            }

            // Event filter
            if ($request->filled('event_id')) {
                $query->whereHas('venue.eventDay', function ($q) use ($request) {
                    $q->where('event_id', $request->event_id);
                });
            }

            // Venue filter
            if ($request->filled('venue_id')) {
                $query->where('venue_id', $request->venue_id);
            }

            // Search filter
            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            $sessions = $query->orderBy('start_time')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $sessions->map(function ($session) {
                    return [
                        'value' => $session->id,
                        'label' => $session->title,
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                        'session_type' => $session->session_type,
                        'venue' => [
                            'name' => $session->venue->display_name ?? $session->venue->name,
                            'event_day' => $session->venue->eventDay->title,
                        ],
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program sessions could not be loaded.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/events/{event}/sessions/{session}/toggle-status",
     *     tags={"Program Sessions"},
     *     summary="Toggle session status",
     *     description="Toggles the active status of a program session",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="session",
     *         in="path",
     *         description="Program Session ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session status toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Session activated"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to update session",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Session does not belong to event",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Session does not belong to this event")
     *         )
     *     )
     * )
     */
    public function toggleStatus(Event $event, ProgramSession $session): JsonResponse
    {
        try {
            $this->authorize('update', $session);

            // Verify session belongs to event
            if ($session->venue->eventDay->event_id !== $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session does not belong to this event'
                ], 404);
            }

            $session->update([
                'is_active' => !($session->is_active ?? true)
            ]);

            return response()->json([
                'success' => true,
                'message' => $session->is_active ? 'Session activated' : 'Session deactivated',
                'data' => [
                    'id' => $session->id,
                    'is_active' => $session->is_active ?? true,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling session status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

/**
 * @OA\Tag(
 *     name="Event Days",
 *     description="Event day management operations"
 * )
 */
class EventDayController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/ajax/events/{event}/days",
     *     tags={"Event Days", "AJAX"},
     *     summary="Get event days for select dropdown",
     *     description="Returns a list of active event days for a specific event",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event days retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="value", type="integer", example=1),
     *                     @OA\Property(property="label", type="string", example="Gün 1"),
     *                     @OA\Property(property="date", type="string", format="date", example="2025-06-15"),
     *                     @OA\Property(property="formatted_date", type="string", example="15.06.2025"),
     *                     @OA\Property(property="sort_order", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to view event",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function getForSelect(Event $event): JsonResponse
    {
        try {
            $this->authorize('view', $event);

            $eventDays = $event->eventDays()
                ->where('is_active', true)
                ->orderBy('date')
                ->orderBy('sort_order')
                ->get(['id', 'title', 'date', 'sort_order']);

            return response()->json([
                'success' => true,
                'data' => $eventDays->map(function ($day) {
                    return [
                        'value' => $day->id,
                        'label' => $day->title,
                        'date' => $day->date,
                        'formatted_date' => $day->date ? $day->date->format('d.m.Y') : null,
                        'sort_order' => $day->sort_order,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Event days could not be loaded.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/events/{event}/generate-days",
     *     tags={"Event Days"},
     *     summary="Generate event days automatically",
     *     description="Automatically generates event days based on the specified criteria",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"generate_type"},
     *             @OA\Property(
     *                 property="generate_type",
     *                 type="string",
     *                 enum={"all_days", "business_days", "custom"},
     *                 example="business_days",
     *                 description="Type of day generation"
     *             ),
     *             @OA\Property(
     *                 property="custom_dates",
     *                 type="array",
     *                 @OA\Items(type="string", format="date"),
     *                 example={"2025-06-15", "2025-06-16", "2025-06-17"},
     *                 description="Required if generate_type is 'custom'"
     *             ),
     *             @OA\Property(
     *                 property="title_format",
     *                 type="string",
     *                 enum={"day_number", "date", "custom"},
     *                 example="day_number",
     *                 description="Format for day titles"
     *             ),
     *             @OA\Property(
     *                 property="custom_titles",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"Açılış Günü", "Ana Program", "Kapanış Günü"},
     *                 description="Custom titles for each day"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event days generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="3 event days created successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Gün 1"),
     *                     @OA\Property(property="date", type="string", format="date", example="2025-06-15"),
     *                     @OA\Property(property="sort_order", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="generate_type",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected generate type is invalid.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function generateDays(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'generate_type' => 'required|in:all_days,business_days,custom',
            'custom_dates' => 'required_if:generate_type,custom|array',
            'custom_dates.*' => 'required_if:generate_type,custom|date',
            'title_format' => 'nullable|string|in:day_number,date,custom',
            'custom_titles' => 'nullable|array',
        ]);

        try {
            $this->authorize('update', $event);

            DB::beginTransaction();

            $dates = [];
            $startDate = Carbon::parse($event->start_date);
            $endDate = Carbon::parse($event->end_date);

            switch ($request->generate_type) {
                case 'all_days':
                    while ($startDate->lte($endDate)) {
                        $dates[] = $startDate->copy();
                        $startDate->addDay();
                    }
                    break;

                case 'business_days':
                    while ($startDate->lte($endDate)) {
                        if ($startDate->isWeekday()) {
                            $dates[] = $startDate->copy();
                        }
                        $startDate->addDay();
                    }
                    break;

                case 'custom':
                    foreach ($request->custom_dates as $dateString) {
                        $date = Carbon::parse($dateString);
                        if ($date->gte($startDate) && $date->lte($endDate)) {
                            $dates[] = $date;
                        }
                    }
                    break;
            }

            $createdDays = [];
            foreach ($dates as $index => $date) {
                // Skip if day already exists
                if ($event->eventDays()->where('date', $date->format('Y-m-d'))->exists()) {
                    continue;
                }

                $title = match ($request->title_format) {
                    'day_number' => 'Gün ' . ($index + 1),
                    'date' => $date->locale('tr')->isoFormat('D MMMM YYYY'),
                    'custom' => $request->custom_titles[$index] ?? 'Gün ' . ($index + 1),
                    default => 'Gün ' . ($index + 1),
                };

                $eventDay = $event->eventDays()->create([
                    'title' => $title,
                    'date' => $date,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);

                $createdDays[] = $eventDay;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($createdDays) . ' event days created successfully.',
                'data' => $createdDays->map(function ($day) {
                    return [
                        'id' => $day->id,
                        'title' => $day->title,
                        'date' => $day->date,
                        'sort_order' => $day->sort_order,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating event days',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

/**
 * @OA\Info(
 *     title="Event Management System API",
 *     version="1.0.0",
 *     description="Comprehensive API for managing events, participants, venues, and program sessions",
 *     @OA\Contact(
 *         email="support@eventmanagement.com",
 *         name="API Support Team"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * @OA\Server(
 *     url="/api/v1",
 *     description="API Version 1"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum authentication"
 * )
 */

/**
 * Common response schemas
 */

/**
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation completed successfully"),
 *     @OA\Property(property="data", type="object")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="An error occurred"),
 *     @OA\Property(property="error", type="string", example="Detailed error message")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\AdditionalProperties(
 *             type="array",
 *             @OA\Items(type="string")
 *         )
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="from", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=5),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="to", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=75)
 * )
 */