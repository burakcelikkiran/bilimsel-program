<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\EventDay;
use App\Models\Event;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class VenueController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of venues with filtering and pagination
     * 
     * @param Request $request
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            // Base query with relationships
            $query = Venue::with(['eventDay.event.organization'])
                ->withCount('programSessions');

            // User access control - non-admin users can only see venues from their organizations
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                if ($organizationIds->isEmpty()) {
                    return $this->renderEmptyVenuesResponse($request);
                } else {
                    $query->whereHas('eventDay.event', function ($q) use ($organizationIds) {
                        $q->whereIn('organization_id', $organizationIds);
                    });
                }
            }

            // Search filter with UTF-8 cleaning
            if ($request->filled('search')) {
                $searchTerm = $this->aggressiveUtf8Clean($request->get('search'));
                $query->search($searchTerm);
            }

            // Event filter
            if ($request->filled('event_id')) {
                $query->whereHas('eventDay', function ($q) use ($request) {
                    $q->where('event_id', $request->get('event_id'));
                });
            }

            // Event Day filter
            if ($request->filled('event_day_id')) {
                $query->byEventDay($request->get('event_day_id'));
            }

            // Capacity filters
            if ($request->filled('min_capacity')) {
                $query->where('capacity', '>=', $request->get('min_capacity'));
            }
            if ($request->filled('max_capacity')) {
                $query->where('capacity', '<=', $request->get('max_capacity'));
            }

            // Capacity range filter
            if ($request->filled('capacity_range')) {
                $range = $request->get('capacity_range');
                switch ($range) {
                    case 'small':
                        $query->whereBetween('capacity', [1, 50]);
                        break;
                    case 'medium':
                        $query->whereBetween('capacity', [51, 200]);
                        break;
                    case 'large':
                        $query->where('capacity', '>', 200);
                        break;
                }
            }

            // Sorting
            $sortField = $request->get('sort', 'sort_order');
            $sortDirection = $request->get('direction', 'asc');

            $allowedSortFields = ['name', 'display_name', 'capacity', 'sort_order', 'created_at'];
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->ordered();
            }

            // For AJAX requests, return JSON
            if ($request->wantsJson()) {
                $venues = $query->paginate($request->get('per_page', 15));

                // Sanitize venues for JSON
                $sanitizedVenues = $venues->getCollection()->map(function ($venue) {
                    return $this->createSafeVenueArray($venue);
                });
                $venues->setCollection($sanitizedVenues);

                return response()->json([
                    'success' => true,
                    'data' => $venues,
                    'message' => 'Salonlar başarıyla listelendi.'
                ]);
            }

            // For regular requests, return Inertia response
            $venues = $query->paginate($request->get('per_page', 15));
            $events = $this->getUserAccessibleEvents($user);
            $eventDays = $this->getUserAccessibleEventDays($user);

            // Sanitize all data for Inertia
            $sanitizedVenues = $venues->getCollection()->map(function ($venue) {
                return $this->createSafeVenueArray($venue);
            });
            $venues->setCollection($sanitizedVenues);

            $sanitizedEvents = $this->sanitizeEventsCollection($events);
            $sanitizedEventDays = $this->sanitizeEventDaysCollection($eventDays);

            return Inertia::render('Admin/Venues/Index', [
                'venues' => $venues,
                'events' => $sanitizedEvents,
                'eventDays' => $sanitizedEventDays,
                'filters' => $request->only(['search', 'event_id', 'event_day_id', 'capacity_range', 'sort', 'direction', 'per_page']),
                'stats' => [
                    'total' => $venues->total(),
                    'with_sessions' => $venues->getCollection()->where('program_sessions_count', '>', 0)->count(),
                    'without_sessions' => $venues->getCollection()->where('program_sessions_count', 0)->count(),
                ],
                'canCreate' => auth()->user()->isEditor()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to list venues', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return $this->handleVenuesError($request, $e);
        }
    }

    /**
     * Show the form for creating a new venue
     */
    public function create(Request $request)
    {
        try {
            $user = auth()->user();
            $eventDays = $this->getUserAccessibleEventDays($user);



            $selectedEventDay = null;
            if ($request->filled('event_day_id')) {
                $selectedEventDay = $eventDays->where('id', $request->get('event_day_id'))->first();
                if (!$selectedEventDay) {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Belirtilen etkinlik günü bulunamadı veya erişim yetkiniz yok.'
                        ], 403);
                    }

                    return redirect()->route('admin.venues.index')
                        ->withErrors('Belirtilen etkinlik günü bulunamadı veya erişim yetkiniz yok.');
                }
            }

            // Clean event days data
            $cleanedEventDays = $this->sanitizeEventDaysCollection($eventDays);
            $cleanedSelectedEventDay = $selectedEventDay ? $this->createSafeEventDayArray($selectedEventDay) : null;

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'event_days' => $cleanedEventDays,
                        'selected_event_day' => $cleanedSelectedEventDay,
                        'color_options' => $this->getColorOptions()
                    ]
                ]);
            }

            return Inertia::render('Admin/Venues/Create', [
                'eventDays' => $cleanedEventDays,
                'selectedEventDay' => $cleanedSelectedEventDay,
                'colorOptions' => $this->getColorOptions()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load venue creation form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salon oluşturma formu yüklenirken bir hata oluştu.'
                ], 500);
            }

            return redirect()->route('admin.venues.index')
                ->withErrors('Salon oluşturma formu yüklenirken bir hata oluştu.');
        }
    }

    /**
     * Store a newly created venue in storage
     */
    public function store(Request $request)
    {
        try {
            // Validation rules
            $validatedData = $request->validate([
                'event_day_id' => 'required|exists:event_days,id',
                'name' => 'required|string|max:255',
                'display_name' => 'nullable|string|max:255',
                'capacity' => 'nullable|integer|min:1|max:50000',
                'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            // Clean input data for UTF-8 safety
            foreach (['name', 'display_name'] as $field) {
                if (isset($validatedData[$field])) {
                    $validatedData[$field] = $this->aggressiveUtf8Clean($validatedData[$field]);
                }
            }

            // Check if user has access to this event day
            $eventDay = EventDay::with('event.organization')->findOrFail($validatedData['event_day_id']);
            
            $this->authorize('create', [Venue::class, $eventDay]);

            DB::beginTransaction();

            // Set display_name to name if not provided
            if (empty($validatedData['display_name'])) {
                $validatedData['display_name'] = $validatedData['name'];
            }

            // Set sort_order to 0 if not provided or null (database constraint)
            if (!isset($validatedData['sort_order']) || $validatedData['sort_order'] === null || $validatedData['sort_order'] === '') {
                $validatedData['sort_order'] = 0;
            }

            // Create venue
            $venue = Venue::create($validatedData);

            // Load relationships for response
            $venue->load(['eventDay.event.organization']);

            DB::commit();

            Log::info('Venue created successfully', [
                'venue_id' => $venue->id,
                'created_by' => auth()->id()
            ]);

            if ($request->wantsJson()) {
                $cleanVenue = $this->createSafeVenueArray($venue);
                return response()->json([
                    'success' => true,
                    'data' => $cleanVenue,
                    'message' => 'Salon başarıyla oluşturuldu.'
                ], 201);
            }

            return redirect()
                ->route('admin.venues.show', $venue)
                ->with('success', 'Salon başarıyla oluşturuldu.');
        } catch (ValidationException $e) {
            DB::rollBack();

            Log::error('Venue validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doğrulama hatası oluştu.',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Venue creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salon oluşturulurken bir hata oluştu.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            return back()
                ->withErrors('Salon oluşturulurken bir hata oluştu.')
                ->withInput();
        }
    }

    /**
     * Display the specified venue
     */
    public function show(Venue $venue)
    {
        try {
            $this->authorize('view', $venue);

            // Load relationships and statistics
            $venue->load([
                'eventDay.event.organization',
                'programSessions' => function ($query) {
                    $query->with(['moderators', 'presentations.speakers', 'sponsor'])
                        ->orderBy('start_time')
                        ->orderBy('sort_order');
                }
            ]);

            // Calculate venue statistics
            $statistics = $this->calculateVenueStatistics($venue);

            if (request()->wantsJson()) {
                $cleanVenue = $this->createSafeVenueArray($venue);
                return response()->json([
                    'success' => true,
                    'data' => [
                        'venue' => $cleanVenue,
                        'statistics' => $statistics
                    ]
                ]);
            }

            $cleanVenue = $this->createSafeVenueArray($venue);
            return Inertia::render('Admin/Venues/Show', [
                'venue' => $cleanVenue,
                'statistics' => $statistics
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to show venue', [
                'venue_id' => $venue->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salon detayları yüklenirken bir hata oluştu.'
                ], 500);
            }

            return redirect()->route('admin.venues.index')
                ->withErrors('Salon detayları yüklenirken bir hata oluştu.');
        }
    }

    /**
     * Show the form for editing the specified venue
     */
    public function edit(Venue $venue)
    {
        try {
            $this->authorize('update', $venue);

            $user = auth()->user();
            $eventDays = $this->getUserAccessibleEventDays($user);

            // Load current venue data with event day relationship
            $venue->load(['eventDay.event.organization']);

            // Ensure the venue's current event day is included in the eventDays list
            if ($venue->eventDay && !$eventDays->contains('id', $venue->event_day_id)) {
                // If user doesn't have access to venue's current event day, add it for editing
                $eventDays->push($venue->eventDay);
            }

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'venue' => $this->createSafeVenueArray($venue),
                        'event_days' => $this->sanitizeEventDaysCollection($eventDays),
                        'color_options' => $this->getColorOptions()
                    ]
                ]);
            }

            // Sanitize all data for Inertia
            $sanitizedVenue = $this->createSafeVenueArray($venue);
            $sanitizedEventDays = $this->sanitizeEventDaysCollection($eventDays);

            return Inertia::render('Admin/Venues/Edit', [
                'venue' => $sanitizedVenue,
                'eventDays' => $sanitizedEventDays,
                'colorOptions' => $this->getColorOptions()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load venue edit form', [
                'venue_id' => $venue->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salon düzenleme formu yüklenirken bir hata oluştu.'
                ], 500);
            }

            return redirect()->route('admin.venues.index')
                ->withErrors('Salon düzenleme formu yüklenirken bir hata oluştu.');
        }
    }

    /**
     * Update the specified venue in storage
     */
    public function update(Request $request, Venue $venue)
    {
        try {
            $this->authorize('update', $venue);

            // Validation rules
            $validatedData = $request->validate([
                'event_day_id' => 'required|exists:event_days,id',
                'name' => 'required|string|max:255',
                'display_name' => 'nullable|string|max:255',
                'capacity' => 'nullable|integer|min:1|max:50000',
                'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            // AGGRESSIVE UTF-8 cleaning before any processing
            foreach (['name', 'display_name'] as $field) {
                if (isset($validatedData[$field])) {
                    $validatedData[$field] = $this->aggressiveUtf8Clean($validatedData[$field]);
                }
            }

            // Check if user has access to the target event day
            $eventDay = EventDay::with('event.organization')->findOrFail($validatedData['event_day_id']);

            // Check user access to the event day's organization
            $user = auth()->user();
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                if (!$organizationIds->contains($eventDay->event->organization_id)) {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Hedef etkinlik gününe erişim yetkiniz yok.'
                        ], 403);
                    }

                    return back()
                        ->withErrors('Hedef etkinlik gününe erişim yetkiniz yok.')
                        ->withInput();
                }
            }

            DB::beginTransaction();

            // Set display_name to name if not provided
            if (empty($validatedData['display_name'])) {
                $validatedData['display_name'] = $validatedData['name'];
            }

            // Set sort_order to 0 if not provided or null (database constraint)
            if (!isset($validatedData['sort_order']) || $validatedData['sort_order'] === null || $validatedData['sort_order'] === '') {
                $validatedData['sort_order'] = 0;
            }

            // Update venue
            $venue->update($validatedData);

            DB::commit();

            Log::info('Venue updated successfully', [
                'venue_id' => $venue->id,
                'updated_by' => auth()->id()
            ]);

            if ($request->wantsJson()) {
                // Reload and aggressively clean venue for JSON response
                $venue->load(['eventDay.event.organization']);
                $cleanVenue = $this->createSafeVenueArray($venue);

                return response()->json([
                    'success' => true,
                    'data' => $cleanVenue,
                    'message' => 'Salon başarıyla güncellendi.'
                ]);
            }

            // For Inertia response, redirect to avoid potential serialization issues
            return redirect()
                ->route('admin.venues.show', $venue->id)
                ->with('success', 'Salon başarıyla güncellendi.');
        } catch (ValidationException $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doğrulama hatası oluştu.',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Venue update failed', [
                'venue_id' => $venue->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salon güncellenirken bir hata oluştu.'
                ], 500);
            }

            return back()
                ->withErrors('Salon güncellenirken bir hata oluştu.')
                ->withInput();
        }
    }

    /**
     * Get venue delete preview data
     */
    public function deletePreview(Request $request, Venue $venue)
    {
        try {
            $this->authorize('delete', $venue);

            // Get related sessions with event information
            $sessions = $venue->programSessions()
                ->with(['venue.eventDay.event:id,name', 'venue.eventDay:id,event_id,display_name,date'])
                ->select('id', 'title', 'venue_id', 'start_time', 'end_time')
                ->get();

            $sessionCount = $sessions->count();

            // Check if user can manage sessions (for cascade delete)
            $canCascadeDelete = $sessionCount > 0 && 
                ($request->user()->isAdmin() || 
                 $request->user()->can('manageSessions', $venue));

            return response()->json([
                'success' => true,
                'venue' => [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'display_name' => $venue->display_name,
                    'capacity' => $venue->capacity,
                    'color' => $venue->color,
                ],
                'sessions' => $sessions->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'name' => $session->title,
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                        'event_day' => $session->venue && $session->venue->eventDay ? [
                            'name' => $session->venue->eventDay->display_name,
                            'date' => $session->venue->eventDay->date,
                            'event' => $session->venue->eventDay->event ? [
                                'name' => $session->venue->eventDay->event->name
                            ] : null
                        ] : null
                    ];
                }),
                'session_count' => $sessionCount,
                'can_cascade_delete' => $canCascadeDelete,
            ]);
        } catch (\Exception $e) {
            Log::error('Venue delete preview failed', [
                'venue_id' => $venue->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Salon bilgileri yüklenirken bir hata oluştu.'
            ], 500);
        }
    }

    /**
     * Remove the specified venue from storage
     */
    public function destroy(Request $request, Venue $venue)
    {
        try {
            $this->authorize('delete', $venue);

            DB::beginTransaction();

            // Check if venue has any program sessions
            $sessionCount = $venue->programSessions()->count();
            $cascadeDelete = $request->boolean('cascade_delete', false);
            $confirmCascade = $request->boolean('confirm_cascade', false);

            if ($sessionCount > 0) {
                // If cascade delete is requested and confirmed
                if ($cascadeDelete && $confirmCascade) {
                    // Check if user can manage sessions
                    if (!$request->user()->isAdmin() && !$request->user()->can('manageSessions', $venue)) {
                        if ($request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Bu salon\'un oturumlarını silmek için yetkiniz yok.'
                            ], 403);
                        }
                        return back()->withErrors('Bu salon\'un oturumlarını silmek için yetkiniz yok.');
                    }

                    // Delete all program sessions first
                    $deletedSessions = $venue->programSessions()->get();
                    $venue->programSessions()->delete();

                    Log::info('Venue sessions deleted via cascade', [
                        'venue_id' => $venue->id,
                        'session_count' => $sessionCount,
                        'deleted_sessions' => $deletedSessions->pluck('id')->toArray(),
                        'user_id' => auth()->id()
                    ]);
                } else {
                    // Return error if sessions exist but cascade delete not requested
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Bu salon silinemez çünkü {$sessionCount} adet oturum içeriyor. Önce oturumları silin."
                        ], 422);
                    }

                    return back()->withErrors("Bu salon silinemez çünkü {$sessionCount} adet oturum içeriyor. Önce oturumları silin.");
                }
            }

            $venueName = $venue->name;
            $venueId = $venue->id;

            // Soft delete the venue
            $venue->delete();

            DB::commit();

            Log::info('Venue deleted successfully', [
                'venue_id' => $venueId,
                'deleted_by' => auth()->id()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Salon başarıyla silindi.'
                ]);
            }

            return redirect()
                ->route('admin.venues.index')
                ->with('success', 'Salon başarıyla silindi.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Venue deletion failed', [
                'venue_id' => $venue->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salon silinirken bir hata oluştu.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            return redirect()
                ->route('admin.venues.index')
                ->withErrors('Salon silinirken bir hata oluştu.');
        }
    }

    /**
     * Get user's accessible events
     */
    private function getUserAccessibleEvents($user)
    {
        if ($user->isAdmin()) {
            return Event::with('organization')->get();
        }

        $organizationIds = $user->organizations()->pluck('organizations.id');
        return Event::with('organization')
            ->whereIn('organization_id', $organizationIds)
            ->get();
    }

    /**
     * Get user's accessible event days
     */
    private function getUserAccessibleEventDays($user)
    {
        if ($user->isAdmin()) {
            return EventDay::with('event.organization')->get();
        }

        $organizationIds = $user->organizations()->pluck('organizations.id');
        
        return EventDay::with('event.organization')
            ->whereHas('event', function ($q) use ($organizationIds) {
                $q->whereIn('organization_id', $organizationIds);
            })
            ->get();
    }

    /**
     * Clean UTF-8 string for safe database storage
     */
    private function aggressiveUtf8Clean(?string $string): ?string
    {
        if (empty($string)) {
            return $string;
        }

        // Basic cleaning: remove null bytes and control characters
        $cleaned = str_replace(["\0", "\x00"], '', $string);
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);
        
        // Ensure UTF-8 encoding
        $cleaned = mb_convert_encoding($cleaned, 'UTF-8', 'UTF-8');
        
        // Remove common problematic characters
        $cleaned = str_replace([
            "\u{FEFF}", // BOM
            "\u{200B}", // Zero width space
            "\u{FFFD}", // Replacement character
        ], '', $cleaned);
        
        // Final validation
        if (!mb_check_encoding($cleaned, 'UTF-8')) {
            $cleaned = mb_convert_encoding($cleaned, 'UTF-8', 'auto');
        }
        
        return trim($cleaned);
    }

    /**
     * Create a completely safe array for JSON encoding
     */
    private function createSafeVenueArray(Venue $venue): array
    {
        try {
            // Clean main venue data
            $data = [
                'id' => $venue->id,
                'event_day_id' => $venue->event_day_id, // Bu field'ın dahil edildiğinden emin olalım
                'name' => $this->aggressiveUtf8Clean($venue->name),
                'display_name' => $this->aggressiveUtf8Clean($venue->display_name),
                'capacity' => $venue->capacity,
                'color' => $venue->color,
                'sort_order' => $venue->sort_order,
                'program_sessions_count' => $venue->program_sessions_count ?? 0,
                'created_at' => $venue->created_at?->toISOString(),
                'updated_at' => $venue->updated_at?->toISOString(),
                // Permission fields
                'can_delete' => auth()->user()->can('delete', $venue),
                'can_edit' => auth()->user()->can('update', $venue),
            ];

            // Clean related data
            if ($venue->relationLoaded('eventDay') && $venue->eventDay) {
                $data['event_day'] = $this->createSafeEventDayArray($venue->eventDay);
            }

            // Test JSON encoding to ensure it's safe
            $testJson = json_encode($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('JSON encoding test failed for venue', [
                    'venue_id' => $venue->id,
                    'json_error' => json_last_error_msg()
                ]);
                
                // Return minimal safe data
                return [
                    'id' => $venue->id,
                    'event_day_id' => $venue->event_day_id,
                    'name' => 'Salon ' . $venue->id,
                    'display_name' => 'Salon ' . $venue->id,
                    'capacity' => $venue->capacity,
                    'color' => $venue->color ?? '#3B82F6',
                    'sort_order' => $venue->sort_order ?? 0,
                    'program_sessions_count' => 0,
                    'created_at' => null,
                    'updated_at' => null,
                    // Permission fields
                    'can_delete' => auth()->user()->can('delete', $venue),
                    'can_edit' => auth()->user()->can('update', $venue),
                ];
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Error creating safe venue array', [
                'venue_id' => $venue->id,
                'error' => $e->getMessage()
            ]);
            
            // Return minimal safe data
            return [
                'id' => $venue->id,
                'event_day_id' => $venue->event_day_id,
                'name' => 'Salon ' . $venue->id,
                'display_name' => 'Salon ' . $venue->id,
                'capacity' => $venue->capacity,
                'color' => '#3B82F6',
                'sort_order' => 0,
                'program_sessions_count' => 0,
                'created_at' => null,
                'updated_at' => null,
            ];
        }
    }

    /**
     * Create safe event day array
     */
    private function createSafeEventDayArray(EventDay $eventDay): array
    {
        try {
            $data = [
                'id' => $eventDay->id,
                'title' => $this->aggressiveUtf8Clean($eventDay->display_name), // EventDay model'inde title accessor display_name'i kullanıyor
                'display_name' => $this->aggressiveUtf8Clean($eventDay->display_name),
                'date' => $eventDay->date?->toDateString(),
            ];

            if ($eventDay->relationLoaded('event') && $eventDay->event) {
                $data['event'] = [
                    'id' => $eventDay->event->id,
                    'name' => $this->aggressiveUtf8Clean($eventDay->event->name),
                    'slug' => $this->aggressiveUtf8Clean($eventDay->event->slug),
                ];

                if ($eventDay->event->relationLoaded('organization') && $eventDay->event->organization) {
                    $data['event']['organization'] = [
                        'id' => $eventDay->event->organization->id,
                        'name' => $this->aggressiveUtf8Clean($eventDay->event->organization->name),
                    ];
                }
            }

            return $data;
        } catch (\Exception $e) {
            return [
                'id' => $eventDay->id,
                'title' => 'Etkinlik Günü ' . $eventDay->id,
                'display_name' => 'Etkinlik Günü ' . $eventDay->id,
                'date' => null,
            ];
        }
    }

    /**
     * Sanitize events collection
     */
    private function sanitizeEventsCollection($events): array
    {
        return $events->map(function ($event) {
            try {
                $data = [
                    'id' => $event->id,
                    'name' => $this->aggressiveUtf8Clean($event->name),
                    'slug' => $this->aggressiveUtf8Clean($event->slug),
                ];

                if ($event->relationLoaded('organization') && $event->organization) {
                    $data['organization'] = [
                        'id' => $event->organization->id,
                        'name' => $this->aggressiveUtf8Clean($event->organization->name),
                    ];
                }

                return $data;
            } catch (\Exception $e) {
                return [
                    'id' => $event->id,
                    'name' => 'Etkinlik ' . $event->id,
                    'slug' => 'event-' . $event->id,
                ];
            }
        })->toArray();
    }

    /**
     * Sanitize event days collection
     */
    private function sanitizeEventDaysCollection($eventDays): array
    {
        return $eventDays->map(function ($eventDay) {
            try {
                $data = [
                    'id' => $eventDay->id,
                    'title' => $this->aggressiveUtf8Clean($eventDay->display_name), // EventDay model'inde title accessor display_name'i kullanıyor
                    'display_name' => $this->aggressiveUtf8Clean($eventDay->display_name),
                    'date' => $eventDay->date?->toDateString(),
                ];

                if ($eventDay->relationLoaded('event') && $eventDay->event) {
                    $data['event'] = [
                        'id' => $eventDay->event->id,
                        'name' => $this->aggressiveUtf8Clean($eventDay->event->name),
                        'slug' => $this->aggressiveUtf8Clean($eventDay->event->slug),
                    ];

                    if ($eventDay->event->relationLoaded('organization') && $eventDay->event->organization) {
                        $data['event']['organization'] = [
                            'id' => $eventDay->event->organization->id,
                            'name' => $this->aggressiveUtf8Clean($eventDay->event->organization->name),
                        ];
                    }
                }

                return $data;
            } catch (\Exception $e) {
                return [
                    'id' => $eventDay->id,
                    'title' => 'Etkinlik Günü ' . $eventDay->id,
                    'display_name' => 'Etkinlik Günü ' . $eventDay->id,
                    'date' => null,
                ];
            }
        })->toArray();
    }

    /**
     * Render empty venues response
     */
    private function renderEmptyVenuesResponse(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => ['data' => [], 'total' => 0],
                'message' => 'Erişilebilir salon bulunamadı.'
            ]);
        }

        return Inertia::render('Admin/Venues/Index', [
            'venues' => ['data' => [], 'total' => 0, 'current_page' => 1, 'last_page' => 1, 'from' => 0, 'to' => 0],
            'events' => [],
            'eventDays' => [],
            'filters' => $request->only(['search', 'event_id', 'event_day_id', 'sort', 'direction']),
            'stats' => ['total' => 0, 'with_sessions' => 0, 'without_sessions' => 0],
            'canCreate' => auth()->user()->isEditor()
        ])->with('error', 'Erişilebilir salon bulunamadı.');
    }

    /**
     * Handle venues error response
     */
    private function handleVenuesError(Request $request, \Exception $e)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Salonlar listesi yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }

        return Inertia::render('Admin/Venues/Index', [
            'venues' => ['data' => [], 'total' => 0, 'current_page' => 1, 'last_page' => 1, 'from' => 0, 'to' => 0],
            'events' => [],
            'eventDays' => [],
            'filters' => $request->only(['search', 'event_id', 'event_day_id', 'sort', 'direction']),
            'stats' => ['total' => 0, 'with_sessions' => 0, 'without_sessions' => 0],
            'canCreate' => auth()->user()->isEditor()
        ])->with('error', 'Salonlar listesi yüklenirken bir hata oluştu.');
    }

    /**
     * Calculate venue statistics
     */
    private function calculateVenueStatistics(Venue $venue): array
    {
        $totalPresentations = 0;
        $totalSpeakers = 0;
        $totalDuration = 0;

        foreach ($venue->programSessions as $session) {
            $totalPresentations += $session->presentations->count();

            foreach ($session->presentations as $presentation) {
                $totalSpeakers += $presentation->speakers->count();
            }

            // Calculate session duration
            if ($session->start_time && $session->end_time) {
                $start = \Carbon\Carbon::parse($session->start_time);
                $end = \Carbon\Carbon::parse($session->end_time);
                $totalDuration += $start->diffInMinutes($end);
            }
        }

        return [
            'total_sessions' => $venue->programSessions->count(),
            'total_presentations' => $totalPresentations,
            'total_speakers' => $totalSpeakers,
            'total_duration_minutes' => $totalDuration,
            'total_duration_hours' => round($totalDuration / 60, 1),
            'capacity_utilization' => $venue->capacity ?
                min(100, round(($totalSpeakers / $venue->capacity) * 100, 1)) : null
        ];
    }

    /**
     * Get available color options for venues
     */
    private function getColorOptions(): array
    {
        return [
            '#3B82F6' => 'Mavi',
            '#EF4444' => 'Kırmızı',
            '#10B981' => 'Yeşil',
            '#F59E0B' => 'Sarı',
            '#8B5CF6' => 'Mor',
            '#F97316' => 'Turuncu',
            '#06B6D4' => 'Cyan',
            '#84CC16' => 'Lime',
            '#EC4899' => 'Pembe',
            '#6B7280' => 'Gri'
        ];
    }
}