<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventDay;
use App\Models\Event;
use App\Models\Venue;
use App\Models\ProgramSession;
use App\Models\Presentation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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
     *     path="/api/v1/events/{event}/days",
     *     tags={"Event Days"},
     *     summary="Get all event days for a specific event",
     *     description="Returns a paginated list of event days with statistics for a specific event",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for event day title or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by active status",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
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
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Gün 1"),
     *                     @OA\Property(property="date", type="string", format="date", example="2025-06-15"),
     *                     @OA\Property(property="description", type="string", example="Conference opening day"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="sort_order", type="integer", example=1),
     *                     @OA\Property(property="venues_count", type="integer", example=5),
     *                     @OA\Property(property="program_sessions_count", type="integer", example=8),
     *                     @OA\Property(property="presentations_count", type="integer", example=16),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=3),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=3)
     *             ),
     *             @OA\Property(
     *                 property="event",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="AI Conference 2025"),
     *                 @OA\Property(property="slug", type="string", example="ai-conference-2025"),
     *                 @OA\Property(property="start_date", type="string", format="date"),
     *                 @OA\Property(property="end_date", type="string", format="date")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Event günleri yüklenirken hata oluştu."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request, Event $event): JsonResponse
    {
        try {
            // Check venues table structure
            $hasEventDayId = Schema::hasColumn('venues', 'event_day_id');
            
            $query = $event->eventDays()
                ->when($request->search, function ($q, $search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                })
                ->when($request->is_active !== null, function ($q) use ($request) {
                    $q->where('is_active', $request->boolean('is_active'));
                })
                ->orderBy('date')
                ->orderBy('sort_order');

            // Pagination parameters
            $perPage = min($request->get('per_page', 15), 100); // Max 100 items per page
            $eventDays = $query->paginate($perPage);

            // Add counts to each event day
            foreach ($eventDays as $eventDay) {
                try {
                    if ($hasEventDayId) {
                        $venueIds = Venue::where('event_day_id', $eventDay->id)
                            ->whereNull('deleted_at')
                            ->pluck('id');
                    } else {
                        $venueIds = Venue::where('organization_id', $event->organization_id)
                            ->whereNull('deleted_at')
                            ->pluck('id');
                    }
                    
                    $eventDay->venues_count = $venueIds->count();
                    $eventDay->program_sessions_count = ProgramSession::whereIn('venue_id', $venueIds)
                        ->whereNull('deleted_at')
                        ->count();
                    
                    $sessionIds = ProgramSession::whereIn('venue_id', $venueIds)
                        ->whereNull('deleted_at')
                        ->pluck('id');
                    
                    $eventDay->presentations_count = Presentation::whereIn('program_session_id', $sessionIds)
                        ->whereNull('deleted_at')
                        ->count();
                        
                } catch (\Exception $e) {
                    Log::error('Error calculating counts for event day API', [
                        'event_day_id' => $eventDay->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    $eventDay->venues_count = 0;
                    $eventDay->program_sessions_count = 0;
                    $eventDay->presentations_count = 0;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $eventDays->items(),
                'pagination' => [
                    'current_page' => $eventDays->currentPage(),
                    'per_page' => $eventDays->perPage(),
                    'total' => $eventDays->total(),
                    'last_page' => $eventDays->lastPage(),
                    'from' => $eventDays->firstItem(),
                    'to' => $eventDays->lastItem(),
                ],
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API EventDay index error', [
                'error' => $e->getMessage(),
                'event_id' => $event->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Event günleri yüklenirken hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/events/{event}/days/{eventDay}",
     *     tags={"Event Days"},
     *     summary="Get a specific event day",
     *     description="Returns detailed information about a specific event day including venue information if requested",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="eventDay",
     *         in="path",
     *         description="Event Day ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include_venues",
     *         in="query",
     *         description="Include venues information in response",
     *         required=false,
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event day retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Gün 1"),
     *                 @OA\Property(property="date", type="string", format="date", example="2025-06-15"),
     *                 @OA\Property(property="description", type="string", example="Conference opening day"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="sort_order", type="integer", example=1),
     *                 @OA\Property(property="venues_count", type="integer", example=5),
     *                 @OA\Property(property="program_sessions_count", type="integer", example=8),
     *                 @OA\Property(property="presentations_count", type="integer", example=16),
     *                 @OA\Property(property="formatted_date", type="string", example="15.06.2025"),
     *                 @OA\Property(property="day_of_week", type="string", example="Monday"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="venues",
     *                     type="array",
     *                     description="Only included if include_venues=true",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Ana Salon"),
     *                         @OA\Property(property="display_name", type="string", example="Ana Salon"),
     *                         @OA\Property(property="capacity", type="integer", example=500),
     *                         @OA\Property(property="color", type="string", example="#FF5733"),
     *                         @OA\Property(property="sort_order", type="integer", example=1)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="event",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="AI Conference 2025"),
     *                 @OA\Property(property="slug", type="string", example="ai-conference-2025")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event day not found for this event",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Event day not found for this event.")
     *         )
     *     )
     * )
     */
    public function show(Event $event, EventDay $eventDay): JsonResponse
    {
        try {
            // Check if event day belongs to the event
            if ($eventDay->event_id !== $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event day not found for this event.'
                ], 404);
            }

            // Load relationships safely
            $eventDay->load(['venues' => function ($query) {
                $query->orderBy('sort_order');
            }]);

            // Calculate counts
            $venueIds = $eventDay->venues()->pluck('id')->toArray();
            $programSessionsCount = ProgramSession::whereIn('venue_id', $venueIds)->count();
            $sessionIds = ProgramSession::whereIn('venue_id', $venueIds)->pluck('id')->toArray();
            $presentationsCount = Presentation::whereIn('program_session_id', $sessionIds)->count();

            // Format response
            $eventDayData = [
                'id' => $eventDay->id,
                'title' => $eventDay->title,
                'date' => $eventDay->date,
                'description' => $eventDay->description,
                'is_active' => $eventDay->is_active,
                'sort_order' => $eventDay->sort_order,
                'venues_count' => count($venueIds),
                'program_sessions_count' => $programSessionsCount,
                'presentations_count' => $presentationsCount,
                'created_at' => $eventDay->created_at,
                'updated_at' => $eventDay->updated_at,
                'formatted_date' => $eventDay->date ? $eventDay->date->format('d.m.Y') : null,
                'day_of_week' => $eventDay->date ? $eventDay->date->format('l') : null,
            ];

            // Include venues if requested
            if (request()->get('include_venues')) {
                $eventDayData['venues'] = $eventDay->venues->map(function ($venue) {
                    return [
                        'id' => $venue->id,
                        'name' => $venue->name,
                        'display_name' => $venue->display_name ?? $venue->name,
                        'capacity' => $venue->capacity,
                        'color' => $venue->color,
                        'sort_order' => $venue->sort_order,
                    ];
                });
            }

            return response()->json([
                'success' => true,
                'data' => $eventDayData,
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API EventDay show error', [
                'event_day_id' => $eventDay->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Event günü yüklenirken hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/events/{event}/days",
     *     tags={"Event Days"},
     *     summary="Create a new event day",
     *     description="Creates a new event day for a specific event with date validation",
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
     *             required={"title", "date"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Gün 1"),
     *             @OA\Property(property="date", type="string", format="date", example="2025-06-15", description="Must be between event start and end dates"),
     *             @OA\Property(property="description", type="string", maxLength=1000, example="Conference opening day"),
     *             @OA\Property(property="is_active", type="boolean", example=true, default=true),
     *             @OA\Property(property="sort_order", type="integer", minimum=0, example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Event day created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event günü başarıyla oluşturuldu."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Gün 1"),
     *                 @OA\Property(property="date", type="string", format="date", example="2025-06-15"),
     *                 @OA\Property(property="description", type="string", example="Conference opening day"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="sort_order", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="date",
     *                     type="array",
     *                     @OA\Items(type="string", example="The date has already been taken.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request, Event $event): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'date' => [
                    'required',
                    'date',
                    'after_or_equal:' . $event->start_date,
                    'before_or_equal:' . $event->end_date,
                    Rule::unique('event_days')->where('event_id', $event->id),
                ],
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            $validated['event_id'] = $event->id;
            $validated['is_active'] = $validated['is_active'] ?? true;

            $eventDay = EventDay::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Event günü başarıyla oluşturuldu.',
                'data' => [
                    'id' => $eventDay->id,
                    'title' => $eventDay->title,
                    'date' => $eventDay->date,
                    'description' => $eventDay->description,
                    'is_active' => $eventDay->is_active,
                    'sort_order' => $eventDay->sort_order,
                    'created_at' => $eventDay->created_at,
                    'updated_at' => $eventDay->updated_at,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API EventDay store error', [
                'error' => $e->getMessage(),
                'event_id' => $event->id,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Event günü oluşturulurken hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/events/{event}/days/{eventDay}",
     *     tags={"Event Days"},
     *     summary="Update an event day",
     *     description="Updates an existing event day with validation",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="eventDay",
     *         in="path",
     *         description="Event Day ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "date"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Gün 1 - Güncellendi"),
     *             @OA\Property(property="date", type="string", format="date", example="2025-06-15"),
     *             @OA\Property(property="description", type="string", maxLength=1000, example="Updated description"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="sort_order", type="integer", minimum=0, example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event day updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event günü başarıyla güncellendi."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Gün 1 - Güncellendi"),
     *                 @OA\Property(property="date", type="string", format="date", example="2025-06-15"),
     *                 @OA\Property(property="description", type="string", example="Updated description"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="sort_order", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event day not found for this event",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Event day not found for this event.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Event $event, EventDay $eventDay): JsonResponse
    {
        try {
            // Check if event day belongs to the event
            if ($eventDay->event_id !== $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event day not found for this event.'
                ], 404);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'date' => [
                    'required',
                    'date',
                    'after_or_equal:' . $event->start_date,
                    'before_or_equal:' . $event->end_date,
                    Rule::unique('event_days')->where('event_id', $event->id)->ignore($eventDay->id),
                ],
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            $eventDay->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Event günü başarıyla güncellendi.',
                'data' => [
                    'id' => $eventDay->id,
                    'title' => $eventDay->title,
                    'date' => $eventDay->date,
                    'description' => $eventDay->description,
                    'is_active' => $eventDay->is_active,
                    'sort_order' => $eventDay->sort_order,
                    'created_at' => $eventDay->created_at,
                    'updated_at' => $eventDay->updated_at,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API EventDay update error', [
                'error' => $e->getMessage(),
                'event_day_id' => $eventDay->id,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Event günü güncellenirken hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/events/{event}/days/{eventDay}",
     *     tags={"Event Days"},
     *     summary="Delete an event day",
     *     description="Deletes an event day if it has no associated program sessions",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="eventDay",
     *         in="path",
     *         description="Event Day ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event day deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event günü başarıyla silindi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event day not found for this event",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Event day not found for this event.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete event day with program sessions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bu günde program oturumları bulunduğu için silinemez.")
     *         )
     *     )
     * )
     */
    public function destroy(Event $event, EventDay $eventDay): JsonResponse
    {
        try {
            // Check if event day belongs to the event
            if ($eventDay->event_id !== $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event day not found for this event.'
                ], 404);
            }

            // Check if there are any program sessions
            $venueIds = $eventDay->venues()->pluck('id')->toArray();
            $hasPrograms = ProgramSession::whereIn('venue_id', $venueIds)->exists();
            
            if ($hasPrograms) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu günde program oturumları bulunduğu için silinemez.',
                ], 422);
            }

            $eventDay->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event günü başarıyla silindi.',
            ]);

        } catch (\Exception $e) {
            Log::error('API EventDay destroy error', [
                'error' => $e->getMessage(),
                'event_day_id' => $eventDay->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Event günü silinirken hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/events/{event}/days/{eventDay}/toggle-status",
     *     tags={"Event Days"},
     *     summary="Toggle event day status",
     *     description="Toggles the active status of an event day",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="eventDay",
     *         in="path",
     *         description="Event Day ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event day status toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event günü durumu başarıyla değiştirildi."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="is_active", type="boolean", example=false),
     *                 @OA\Property(property="status", type="string", example="inactive")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event day not found for this event",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Event day not found for this event.")
     *         )
     *     )
     * )
     */
    public function toggleStatus(Event $event, EventDay $eventDay): JsonResponse
    {
        try {
            // Check if event day belongs to the event
            if ($eventDay->event_id !== $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event day not found for this event.'
                ], 404);
            }

            $eventDay->update(['is_active' => !$eventDay->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Event günü durumu başarıyla değiştirildi.',
                'data' => [
                    'id' => $eventDay->id,
                    'is_active' => $eventDay->is_active,
                    'status' => $eventDay->is_active ? 'active' : 'inactive'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API EventDay toggle status error', [
                'error' => $e->getMessage(),
                'event_day_id' => $eventDay->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Durum değiştirilirken hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/events/{event}/days/sort-order",
     *     tags={"Event Days"},
     *     summary="Update sort order for multiple event days",
     *     description="Updates the sort order for multiple event days in batch",
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
     *             required={"event_days"},
     *             @OA\Property(
     *                 property="event_days",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="sort_order", type="integer", minimum=0, example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sort order updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event günleri sıralaması başarıyla güncellendi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="event_days.0.id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected event_days.0.id is invalid.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function updateSortOrder(Request $request, Event $event): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_days' => 'required|array',
                'event_days.*.id' => 'required|exists:event_days,id',
                'event_days.*.sort_order' => 'required|integer|min:0',
            ]);

            foreach ($validated['event_days'] as $dayData) {
                EventDay::where('id', $dayData['id'])
                    ->where('event_id', $event->id)
                    ->update(['sort_order' => $dayData['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Event günleri sıralaması başarıyla güncellendi.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API EventDay sort order error', [
                'error' => $e->getMessage(),
                'event_id' => $event->id,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Sıralama güncellenirken hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/events/{event}/days/{eventDay}/schedule",
     *     tags={"Event Days", "Schedule"},
     *     summary="Get event day schedule with program sessions",
     *     description="Returns detailed schedule information for a specific event day including all program sessions grouped by time slots",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="eventDay",
     *         in="path",
     *         description="Event Day ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Schedule retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="event_day",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Gün 1"),
     *                     @OA\Property(property="date", type="string", format="date", example="2025-06-15"),
     *                     @OA\Property(property="formatted_date", type="string", example="15.06.2025")
     *                 ),
     *                 @OA\Property(
     *                     property="time_slots",
     *                     type="object",
     *                     @OA\AdditionalProperties(
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="title", type="string", example="Açılış Oturumu"),
     *                             @OA\Property(property="description", type="string", example="Conference opening session"),
     *                             @OA\Property(property="start_time", type="string", format="time", example="09:00:00"),
     *                             @OA\Property(property="end_time", type="string", format="time", example="10:30:00"),
     *                             @OA\Property(property="session_type", type="string", example="main"),
     *                             @OA\Property(property="is_break", type="boolean", example=false),
     *                             @OA\Property(
     *                                 property="venue",
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Ana Salon"),
     *                                 @OA\Property(property="display_name", type="string", example="Ana Salon"),
     *                                 @OA\Property(property="capacity", type="integer", example=500),
     *                                 @OA\Property(property="color", type="string", example="#FF5733")
     *                             ),
     *                             @OA\Property(
     *                                 property="category",
     *                                 type="object",
     *                                 nullable=true,
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="AI & Machine Learning"),
     *                                 @OA\Property(property="color", type="string", example="#3366FF")
     *                             ),
     *                             @OA\Property(property="moderators_count", type="integer", example=2),
     *                             @OA\Property(property="presentations_count", type="integer", example=4)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="venues",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Ana Salon"),
     *                         @OA\Property(property="display_name", type="string", example="Ana Salon"),
     *                         @OA\Property(property="capacity", type="integer", example=500),
     *                         @OA\Property(property="color", type="string", example="#FF5733"),
     *                         @OA\Property(property="sort_order", type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event day not found for this event",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Event day not found for this event.")
     *         )
     *     )
     * )
     */
    public function schedule(Event $event, EventDay $eventDay): JsonResponse
    {
        try {
            // Check if event day belongs to the event
            if ($eventDay->event_id !== $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event day not found for this event.'
                ], 404);
            }

            $venueIds = $eventDay->venues()->pluck('id')->toArray();
            $sessions = ProgramSession::whereIn('venue_id', $venueIds)
                ->with(['venue', 'categories', 'presentations.speakers', 'moderators'])
                ->orderBy('start_time')
                ->orderBy('sort_order')
                ->get();

            // Group sessions by time slots
            $timeSlots = [];
            foreach ($sessions as $session) {
                $timeKey = $session->start_time ? Carbon::parse($session->start_time)->format('H:i') : '00:00';
                
                if (!isset($timeSlots[$timeKey])) {
                    $timeSlots[$timeKey] = [];
                }
                
                $timeSlots[$timeKey][] = [
                    'id' => $session->id,
                    'title' => $session->title,
                    'description' => $session->description,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'session_type' => $session->session_type,
                    'is_break' => $session->is_break,
                    'venue' => [
                        'id' => $session->venue->id,
                        'name' => $session->venue->name,
                        'display_name' => $session->venue->display_name ?? $session->venue->name,
                        'capacity' => $session->venue->capacity,
                        'color' => $session->venue->color,
                    ],
                    'category' => $session->categories->first() ? [
                        'id' => $session->categories->first()->id,
                        'name' => $session->categories->first()->name,
                        'color' => $session->categories->first()->color,
                    ] : null,
                    'moderators_count' => $session->moderators->count(),
                    'presentations_count' => $session->presentations->count(),
                ];
            }

            // Sort time slots
            ksort($timeSlots);

            return response()->json([
                'success' => true,
                'data' => [
                    'event_day' => [
                        'id' => $eventDay->id,
                        'title' => $eventDay->title,
                        'date' => $eventDay->date,
                        'formatted_date' => $eventDay->date ? $eventDay->date->format('d.m.Y') : null,
                    ],
                    'time_slots' => $timeSlots,
                    'venues' => $eventDay->venues->map(function ($venue) {
                        return [
                            'id' => $venue->id,
                            'name' => $venue->name,
                            'display_name' => $venue->display_name ?? $venue->name,
                            'capacity' => $venue->capacity,
                            'color' => $venue->color,
                            'sort_order' => $venue->sort_order,
                        ];
                    }),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API EventDay schedule error', [
                'error' => $e->getMessage(),
                'event_day_id' => $eventDay->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Program yüklenirken hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/events/{event}/statistics",
     *     tags={"Event Days", "Statistics"},
     *     summary="Get event statistics",
     *     description="Returns comprehensive statistics for an event including counts of days, venues, sessions, and presentations",
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
     *         description="Statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_days", type="integer", example=3),
     *                 @OA\Property(property="active_days", type="integer", example=3),
     *                 @OA\Property(property="total_venues", type="integer", example=5),
     *                 @OA\Property(property="total_sessions", type="integer", example=24),
     *                 @OA\Property(property="total_presentations", type="integer", example=48)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="İstatistikler yüklenirken hata oluştu."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function statistics(Event $event): JsonResponse
    {
        try {
            $hasEventDayId = Schema::hasColumn('venues', 'event_day_id');
            
            if ($hasEventDayId) {
                $allEventDayIds = $event->eventDays()->pluck('id');
                $allVenueIds = Venue::whereIn('event_day_id', $allEventDayIds)
                    ->whereNull('deleted_at')
                    ->pluck('id');
            } else {
                $allVenueIds = Venue::where('organization_id', $event->organization_id)
                    ->whereNull('deleted_at')
                    ->pluck('id');
            }
            
            $allSessionIds = ProgramSession::whereIn('venue_id', $allVenueIds)
                ->whereNull('deleted_at')
                ->pluck('id');

            $stats = [
                'total_days' => $event->eventDays()->count(),
                'active_days' => $event->eventDays()->where('is_active', true)->count(),
                'total_venues' => $allVenueIds->count(),
                'total_sessions' => $allSessionIds->count(),
                'total_presentations' => Presentation::whereIn('program_session_id', $allSessionIds)
                    ->whereNull('deleted_at')
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('API EventDay statistics error', [
                'error' => $e->getMessage(),
                'event_id' => $event->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'İstatistikler yüklenirken hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ajax/events/{event}/days",
     *     tags={"Event Days", "AJAX"},
     *     summary="Get event days for select dropdown",
     *     description="Returns a list of active event days for a specific event formatted for dropdowns",
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
     *     description="Automatically generates event days based on the specified criteria and date range",
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
     *                 description="Type of day generation: all_days (every day), business_days (weekdays only), custom (specific dates)"
     *             ),
     *             @OA\Property(
     *                 property="custom_dates",
     *                 type="array",
     *                 @OA\Items(type="string", format="date"),
     *                 example={"2025-06-15", "2025-06-16", "2025-06-17"},
     *                 description="Required if generate_type is 'custom'. Must be within event date range."
     *             ),
     *             @OA\Property(
     *                 property="title_format",
     *                 type="string",
     *                 enum={"day_number", "date", "custom"},
     *                 example="day_number",
     *                 description="Format for generating day titles"
     *             ),
     *             @OA\Property(
     *                 property="custom_titles",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"Açılış Günü", "Ana Program", "Kapanış Günü"},
     *                 description="Custom titles for each day (used with title_format=custom)"
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
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
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
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to update event",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
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

            \DB::beginTransaction();

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

            \DB::commit();

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
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating event days',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}