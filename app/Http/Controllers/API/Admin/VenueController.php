<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\EventDay;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Venues",
 *     description="API Endpoints for managing event venues"
 * )
 */
class VenueController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/venues",
     *     tags={"Venues"},
     *     summary="Get list of venues",
     *     description="Retrieve paginated list of venues with filtering and sorting options. Users can only see venues from their accessible organizations.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in venue name, display name, and description",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="event_id",
     *         in="query",
     *         description="Filter by event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="event_day_id",
     *         in="query",
     *         description="Filter by event day ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="min_capacity",
     *         in="query",
     *         description="Minimum capacity filter",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="max_capacity",
     *         in="query",
     *         description="Maximum capacity filter",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(
     *             type="string",
     *             enum={"name", "display_name", "capacity", "sort_order", "created_at"},
     *             default="name"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(
     *             type="string",
     *             enum={"asc", "desc"},
     *             default="asc"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (max 100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/VenueResource")
     *                 ),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No accessible venues found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erişilebilir mekan bulunamadı."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validation
            $validated = $request->validate([
                'search' => 'sometimes|string|max:255',
                'event_id' => 'sometimes|integer|exists:events,id',
                'event_day_id' => 'sometimes|integer|exists:event_days,id',
                'min_capacity' => 'sometimes|integer|min:1',
                'max_capacity' => 'sometimes|integer|min:1',
                'sort' => 'sometimes|string|in:name,display_name,capacity,sort_order,created_at',
                'direction' => 'sometimes|string|in:asc,desc',
                'per_page' => 'sometimes|integer|min:1|max:100'
            ]);

            // Build query with relationships
            $query = Venue::with([
                'eventDay:id,title,date,event_id',
                'eventDay.event:id,name,slug,organization_id',
                'eventDay.event.organization:id,name'
            ])->withCount('programSessions');

            // User access control
            $user = $request->user();
            if (!$this->isAdmin($user)) {
                $userOrganizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereHas('eventDay.event', function ($q) use ($userOrganizationIds) {
                    $q->whereIn('organization_id', $userOrganizationIds);
                });
            }

            // Apply filters
            if (!empty($validated['search'])) {
                $query->where(function ($q) use ($validated) {
                    $q->where('name', 'like', '%' . $validated['search'] . '%')
                      ->orWhere('display_name', 'like', '%' . $validated['search'] . '%');
                });
            }

            if (!empty($validated['event_id'])) {
                $query->whereHas('eventDay', function ($q) use ($validated) {
                    $q->where('event_id', $validated['event_id']);
                });
            }

            if (!empty($validated['event_day_id'])) {
                $query->where('event_day_id', $validated['event_day_id']);
            }

            if (!empty($validated['min_capacity'])) {
                $query->where('capacity', '>=', $validated['min_capacity']);
            }

            if (!empty($validated['max_capacity'])) {
                $query->where('capacity', '<=', $validated['max_capacity']);
            }

            // Apply sorting
            $sortField = $validated['sort'] ?? 'name';
            $sortDirection = $validated['direction'] ?? 'asc';
            $query->orderBy($sortField, $sortDirection);

            // Paginate
            $perPage = $validated['per_page'] ?? 15;
            $venues = $query->paginate($perPage);

            // Check if no accessible venues found
            if ($venues->isEmpty() && !$this->isAdmin($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erişilebilir mekan bulunamadı.',
                    'data' => []
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $venues->items(),
                    'current_page' => $venues->currentPage(),
                    'last_page' => $venues->lastPage(),
                    'per_page' => $venues->perPage(),
                    'total' => $venues->total()
                ],
                'meta' => [
                    'total' => $venues->total(),
                    'per_page' => $venues->perPage(),
                    'current_page' => $venues->currentPage(),
                    'last_page' => $venues->lastPage()
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Girilen bilgiler geçersiz.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('VenueController@index error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Mekanlar listelenirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/venues",
     *     tags={"Venues"},
     *     summary="Create a new venue",
     *     description="Create a new venue for the specified event day. User must have create permissions and access to the event's organization.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/VenueCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Venue created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mekan başarıyla oluşturuldu."),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/VenueDetailedResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Girilen bilgiler geçersiz."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="Bu etkinlik gününde aynı isimde bir mekan zaten var.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied to event day",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bu etkinlik gününe mekan ekleyemezsiniz.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validation
            $validated = $request->validate([
                'event_day_id' => [
                    'required',
                    'exists:event_days,id',
                    function ($attribute, $value, $fail) use ($request) {
                        $eventDay = EventDay::with('event')->find($value);
                        if (!$eventDay) {
                            $fail('Etkinlik günü bulunamadı.');
                            return;
                        }
                        
                        $user = $request->user();
                        if (!$this->isAdmin($user)) {
                            $hasAccess = $user->organizations()
                                             ->where('organizations.id', $eventDay->event->organization_id)
                                             ->exists();
                            if (!$hasAccess) {
                                $fail('Bu etkinlik gününe mekan ekleyemezsiniz.');
                            }
                        }
                    }
                ],
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:255',
                    function ($attribute, $value, $fail) use ($request) {
                        $eventDayId = $request->input('event_day_id');
                        if (Venue::where('event_day_id', $eventDayId)->where('name', $value)->exists()) {
                            $fail('Bu etkinlik gününde aynı isimde bir mekan zaten var.');
                        }
                    }
                ],
                'display_name' => 'nullable|string|max:255',
                'capacity' => 'nullable|integer|min:1|max:100000',
                'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            DB::beginTransaction();

            // Create venue
            $venue = Venue::create($validated);

            // Load relationships for response
            $venue->load([
                'eventDay:id,title,date,event_id',
                'eventDay.event:id,name,slug,organization_id',
                'eventDay.event.organization:id,name'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mekan başarıyla oluşturuldu.',
                'data' => $venue
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Girilen bilgiler geçersiz.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VenueController@store error: ' . $e->getMessage(), [
                'data' => $request->all(),
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Mekan oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/venues/{venue}",
     *     tags={"Venues"},
     *     summary="Get a specific venue",
     *     description="Retrieve detailed information about a venue including program sessions, statistics, and time conflicts",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="venue",
     *         in="path",
     *         required=true,
     *         description="Venue ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="venue",
     *                     ref="#/components/schemas/VenueDetailedResource"
     *                 ),
     *                 @OA\Property(
     *                     property="statistics",
     *                     ref="#/components/schemas/VenueStatistics"
     *                 ),
     *                 @OA\Property(
     *                     property="time_conflicts",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/TimeConflict")
     *                 ),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="object",
     *                     @OA\Property(property="can_edit", type="boolean"),
     *                     @OA\Property(property="can_delete", type="boolean"),
     *                     @OA\Property(property="can_manage_sessions", type="boolean")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Venue not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(Venue $venue): JsonResponse
    {
        try {
            // User access control
            $user = request()->user();
            $userOrganizationIds = collect();
            
            if (!$this->isAdmin($user)) {
                $userOrganizationIds = $user->organizations()->pluck('organizations.id');
                $venue->load('eventDay.event');
                
                if (!$userOrganizationIds->contains($venue->eventDay->event->organization_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu mekana erişim yetkiniz bulunmuyor.'
                    ], 403);
                }
            }

            // Load detailed relationships
            $venue->load([
                'eventDay:id,title,date,event_id',
                'eventDay.event:id,name,slug,organization_id',
                'eventDay.event.organization:id,name',
                'programSessions:id,venue_id,title,start_time,end_time,session_type',
                'programSessions.presentations:id,program_session_id,title'
            ]);

            // Calculate statistics
            $statistics = [
                'total_sessions' => $venue->programSessions->count(),
                'total_presentations' => $venue->programSessions->sum(function ($session) {
                    return $session->presentations->count();
                }),
                'session_types' => $venue->programSessions->groupBy('session_type')->map->count(),
                'capacity_utilization' => $venue->capacity ? 
                    ($venue->programSessions->count() > 0 ? 
                        round(($venue->programSessions->count() / $venue->capacity) * 100, 2) : 0) : null
            ];

            // Check for time conflicts
            $timeConflicts = [];
            $sessions = $venue->programSessions->sortBy('start_time');
            
            foreach ($sessions as $session) {
                $conflicts = $sessions->filter(function ($otherSession) use ($session) {
                    return $otherSession->id !== $session->id &&
                           $session->start_time < $otherSession->end_time &&
                           $session->end_time > $otherSession->start_time;
                });
                
                if ($conflicts->isNotEmpty()) {
                    $timeConflicts[] = [
                        'session' => [
                            'id' => $session->id,
                            'title' => $session->title,
                            'start_time' => $session->start_time->format('H:i'),
                            'end_time' => $session->end_time->format('H:i')
                        ],
                        'conflicts_with' => $conflicts->map(function ($conflict) {
                            return [
                                'id' => $conflict->id,
                                'title' => $conflict->title,
                                'start_time' => $conflict->start_time->format('H:i'),
                                'end_time' => $conflict->end_time->format('H:i')
                            ];
                        })->values()
                    ];
                }
            }

            // User permissions
            $isAdmin = $this->isAdmin($user);
            $hasOrgAccess = $isAdmin || $userOrganizationIds->contains($venue->eventDay->event->organization_id);
            
            $permissions = [
                'can_edit' => $hasOrgAccess,
                'can_delete' => $hasOrgAccess && $venue->programSessions->isEmpty(),
                'can_manage_sessions' => $hasOrgAccess
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'venue' => $venue,
                    'statistics' => $statistics,
                    'time_conflicts' => $timeConflicts,
                    'permissions' => $permissions
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('VenueController@show error: ' . $e->getMessage(), [
                'venue_id' => $venue->id,
                'user_id' => request()->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Mekan bilgileri alınırken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/venues/{venue}",
     *     tags={"Venues"},
     *     summary="Update a venue",
     *     description="Update an existing venue with new data. If event_day_id is changed, user must have access to the target event day's organization.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="venue",
     *         in="path",
     *         required=true,
     *         description="Venue ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/VenueUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Venue updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mekan başarıyla güncellendi."),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/VenueDetailedResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied to target event day",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Hedef etkinlik gününe erişim yetkiniz yok.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Venue not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(Request $request, Venue $venue): JsonResponse
    {
        try {
            // User access control
            $user = $request->user();
            if (!$this->isAdmin($user)) {
                $venue->load('eventDay.event');
                $userOrganizationIds = $user->organizations()->pluck('organizations.id');
                
                if (!$userOrganizationIds->contains($venue->eventDay->event->organization_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu mekanı düzenleme yetkiniz bulunmuyor.'
                    ], 403);
                }
            }

            // Validation
            $validated = $request->validate([
                'event_day_id' => [
                    'sometimes',
                    'exists:event_days,id',
                    function ($attribute, $value, $fail) use ($request, $venue) {
                        if ($value && $value != $venue->event_day_id) {
                            $eventDay = EventDay::with('event')->find($value);
                            if (!$eventDay) {
                                $fail('Etkinlik günü bulunamadı.');
                                return;
                            }
                            
                            $user = $request->user();
                            if (!$this->isAdmin($user)) {
                                $hasAccess = $user->organizations()
                                                 ->where('organizations.id', $eventDay->event->organization_id)
                                                 ->exists();
                                if (!$hasAccess) {
                                    $fail('Bu etkinlik gününe mekan taşıyamazsınız.');
                                }
                            }
                        }
                    }
                ],
                'name' => [
                    'sometimes',
                    'string',
                    'min:2',
                    'max:255',
                    function ($attribute, $value, $fail) use ($request, $venue) {
                        $eventDayId = $request->input('event_day_id', $venue->event_day_id);
                        $existingVenue = Venue::where('event_day_id', $eventDayId)
                                             ->where('name', $value)
                                             ->where('id', '!=', $venue->id)
                                             ->first();
                        if ($existingVenue) {
                            $fail('Bu etkinlik gününde aynı isimde başka bir mekan zaten var.');
                        }
                    }
                ],
                'display_name' => 'sometimes|nullable|string|max:255',
                'capacity' => 'sometimes|nullable|integer|min:1|max:100000',
                'color' => 'sometimes|nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'sort_order' => 'sometimes|nullable|integer|min:0',
            ]);

            DB::beginTransaction();

            // Update venue
            $venue->update($validated);

            // Reload relationships
            $venue->load([
                'eventDay:id,title,date,event_id',
                'eventDay.event:id,name,slug,organization_id',
                'eventDay.event.organization:id,name'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mekan başarıyla güncellendi.',
                'data' => $venue
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Girilen bilgiler geçersiz.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VenueController@update error: ' . $e->getMessage(), [
                'venue_id' => $venue->id,
                'data' => $request->all(),
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Mekan güncellenirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/venues/{venue}",
     *     tags={"Venues"},
     *     summary="Delete a venue",
     *     description="Delete a venue. Venue cannot be deleted if it has program sessions.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="venue",
     *         in="path",
     *         required=true,
     *         description="Venue ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Venue deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="'Main Hall' mekanı başarıyla silindi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete venue with sessions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Oturum içeren mekan silinemez. Önce oturumları silin.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Venue not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(Venue $venue): JsonResponse
    {
        try {
            // User access control
            $user = request()->user();
            if (!$this->isAdmin($user)) {
                $venue->load('eventDay.event');
                $userOrganizationIds = $user->organizations()->pluck('organizations.id');
                
                if (!$userOrganizationIds->contains($venue->eventDay->event->organization_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu mekanı silme yetkiniz bulunmuyor.'
                    ], 403);
                }
            }

            // Check if venue has program sessions
            $sessionCount = $venue->programSessions()->count();
            if ($sessionCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Bu mekan silinemez. {$sessionCount} adet program oturumu bağlı.",
                    'details' => [
                        'reason' => 'has_sessions',
                        'session_count' => $sessionCount
                    ]
                ], 422);
            }

            DB::beginTransaction();

            // Store venue info for response
            $venueInfo = [
                'id' => $venue->id,
                'name' => $venue->name,
                'event_day_title' => $venue->eventDay->title ?? null
            ];

            // Soft delete the venue
            $venue->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mekan başarıyla silindi.',
                'data' => $venueInfo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VenueController@destroy error: ' . $e->getMessage(), [
                'venue_id' => $venue->id,
                'user_id' => request()->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Mekan silinirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to check if user is admin
     */
    private function isAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        // Check if user has isAdmin method
        if (method_exists($user, 'isAdmin')) {
            return $user->isAdmin();
        }

        // Fallback: check role attribute
        if (isset($user->role)) {
            return $user->role === 'admin';
        }

        // If no admin check available, assume not admin
        return false;
    }
}