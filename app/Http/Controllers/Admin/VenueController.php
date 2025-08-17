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

            // Debug log
            Log::info('Venue create form accessed', [
                'user_id' => auth()->id(),
                'event_days_count' => $eventDays->count(),
                'event_days' => $eventDays->toArray()
            ]);

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

            Log::info('Cleaned event days', [
                'cleaned_count' => count($cleanedEventDays),
                'selected_day' => $cleanedSelectedEventDay
            ]);

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
            // Debug log
            Log::info('Venue store request received', [
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            // Validation rules
            $validatedData = $request->validate([
                'event_day_id' => 'required|exists:event_days,id',
                'name' => 'required|string|max:255',
                'display_name' => 'nullable|string|max:255',
                'capacity' => 'nullable|integer|min:1|max:50000',
                'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            Log::info('Venue validation passed', ['validated_data' => $validatedData]);

            // Clean input data for UTF-8 safety
            foreach (['name', 'display_name'] as $field) {
                if (isset($validatedData[$field])) {
                    $validatedData[$field] = $this->aggressiveUtf8Clean($validatedData[$field]);
                }
            }

            // Check if user has access to this event day
            $eventDay = EventDay::with('event.organization')->findOrFail($validatedData['event_day_id']);
            
            Log::info('Event day found', [
                'event_day' => $eventDay->toArray()
            ]);

            $this->authorize('create', [Venue::class, $eventDay]);

            DB::beginTransaction();

            // Set display_name to name if not provided
            if (empty($validatedData['display_name'])) {
                $validatedData['display_name'] = $validatedData['name'];
            }

            Log::info('About to create venue', ['final_data' => $validatedData]);

            // Create venue
            $venue = Venue::create($validatedData);

            Log::info('Venue created', ['venue_id' => $venue->id]);

            // Load relationships for response
            $venue->load(['eventDay.event.organization']);

            DB::commit();

            Log::info('Venue created successfully', [
                'venue_id' => $venue->id,
                'venue_name' => $venue->name,
                'event_day_id' => $venue->event_day_id,
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

            // Update venue
            $venue->update($validatedData);

            DB::commit();

            Log::info('Venue updated successfully', [
                'venue_id' => $venue->id,
                'venue_name' => $venue->name,
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
     * Remove the specified venue from storage
     */
    public function destroy(Request $request, Venue $venue)
    {
        try {
            $this->authorize('delete', $venue);

            DB::beginTransaction();

            // Check if venue has any program sessions
            $sessionCount = $venue->programSessions()->count();
            if ($sessionCount > 0) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Bu salon silinemez çünkü {$sessionCount} adet oturum içeriyor. Önce oturumları silin."
                    ], 422);
                }

                return back()->withErrors("Bu salon silinemez çünkü {$sessionCount} adet oturum içeriyor. Önce oturumları silin.");
            }

            $venueName = $venue->name;
            $venueId = $venue->id;

            // Soft delete the venue
            $venue->delete();

            DB::commit();

            Log::info('Venue deleted successfully', [
                'venue_id' => $venueId,
                'venue_name' => $venueName,
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
        Log::info('Getting accessible event days for user', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'is_admin' => $user->isAdmin()
        ]);

        if ($user->isAdmin()) {
            $eventDays = EventDay::with('event.organization')->get();
            Log::info('Admin user - returning all event days', [
                'count' => $eventDays->count(),
                'event_days' => $eventDays->toArray()
            ]);
            return $eventDays;
        }

        $organizationIds = $user->organizations()->pluck('organizations.id');
        Log::info('Non-admin user organizations', [
            'organization_ids' => $organizationIds->toArray()
        ]);

        $eventDays = EventDay::with('event.organization')
            ->whereHas('event', function ($q) use ($organizationIds) {
                $q->whereIn('organization_id', $organizationIds);
            })
            ->get();

        Log::info('Filtered event days for user', [
            'count' => $eventDays->count(),
            'event_days' => $eventDays->toArray()
        ]);

        return $eventDays;
    }

    /**
     * AGGRESSIVE UTF-8 cleaning function - Ana sorun çözücü
     */
    private function aggressiveUtf8Clean(?string $string): ?string
    {
        if (empty($string)) {
            return $string;
        }

        // Step 1: Remove null bytes and control characters
        $cleaned = str_replace(["\0", "\x00"], '', $string);
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);
        
        // Step 2: Force UTF-8 encoding multiple times
        $cleaned = mb_convert_encoding($cleaned, 'UTF-8', 'UTF-8');
        
        // Step 3: Use iconv for additional cleaning
        $cleaned = iconv('UTF-8', 'UTF-8//IGNORE', $cleaned);
        
        // Step 4: Remove problematic Unicode characters
        $problematicChars = [
            "\u{FEFF}" => '', // BOM
            "\u{200B}" => '', // Zero width space
            "\u{200C}" => '', // Zero width non-joiner
            "\u{200D}" => '', // Zero width joiner
            "\u{2060}" => '', // Word joiner
            "\u{FFFD}" => '', // Replacement character
            "\u{FFF9}" => '', // Interlinear annotation anchor
            "\u{FFFA}" => '', // Interlinear annotation separator
            "\u{FFFB}" => '', // Interlinear annotation terminator
        ];

        foreach ($problematicChars as $char => $replacement) {
            $cleaned = str_replace($char, $replacement, $cleaned);
        }

        // Step 5: Use filter_var for additional cleaning
        $cleaned = filter_var($cleaned, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
        
        // Step 6: Final UTF-8 validation and conversion
        if (!mb_check_encoding($cleaned, 'UTF-8')) {
            $cleaned = mb_convert_encoding($cleaned, 'UTF-8', 'auto');
        }
        
        // Step 7: Use preg_replace to remove any remaining non-printable chars
        $cleaned = preg_replace('/[^\P{C}\s]/u', '', $cleaned);
        
        // Step 8: Final safety check
        if (!is_string($cleaned) || !mb_check_encoding($cleaned, 'UTF-8')) {
            $cleaned = 'Temizlenmemiş Metin';
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