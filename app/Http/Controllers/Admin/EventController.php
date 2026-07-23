<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventPageKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\EventPage;
use App\Models\Organization;
use App\Models\ProgramSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EventController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of events
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        // ENHANCED DEBUG LOGGING
        Log::info('🚀 EventController@index - Starting with enhanced debug', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_is_admin' => $user->isAdmin(),
            'request_params' => $request->all(),
            'request_url' => $request->fullUrl(),
        ]);

        // Check database and Event model
        try {
            $totalEventsInDb = Event::count();
            $totalOrganizationsInDb = Organization::count();

            Log::info('📊 Database Status Check', [
                'total_events_in_database' => $totalEventsInDb,
                'total_organizations_in_database' => $totalOrganizationsInDb,
                'event_model_accessible' => true,
                'organization_model_accessible' => true,
            ]);

            // If no events exist, create a test event for debugging
            if ($totalEventsInDb === 0) {
                Log::warning('⚠️ No events found in database, creating test event');
                try {
                    $testEvent = Event::create([
                        'name' => 'Test Event for Debug',
                        'slug' => 'test-event-debug',
                        'description' => 'Bu test amaçlı oluşturulan bir etkinliktir.',
                        'start_date' => now()->addDays(7),
                        'end_date' => now()->addDays(9),
                        'location' => 'Test Location',
                        'organization_id' => 1, // Assuming organization ID 1 exists
                        'is_published' => true,
                        'created_by' => $user->id,
                    ]);
                    Log::info('✅ Test event created successfully', ['event_id' => $testEvent->id]);
                } catch (\Exception $e) {
                    Log::error('❌ Failed to create test event', ['error' => $e->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            Log::error('❌ Database access error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Check user organizations
        try {
            if (! $user->isAdmin()) {
                $userOrganizations = $user->organizations();
                $organizationIds = $userOrganizations->pluck('organizations.id');

                Log::info('👥 User Organization Check', [
                    'is_admin' => false,
                    'user_organizations_count' => $organizationIds->count(),
                    'organization_ids' => $organizationIds->toArray(),
                ]);

                if ($organizationIds->count() === 0) {
                    Log::warning('⚠️ User has no organizations - this will result in empty events list');
                }
            } else {
                Log::info('👑 User is admin - will see all events');
            }
        } catch (\Exception $e) {
            Log::error('❌ Error checking user organizations', ['error' => $e->getMessage()]);
        }

        // Build query with step-by-step logging
        Log::info('🔍 Building event query');

        $query = Event::with(['organization', 'creator'])
            ->withCount(['eventDays']);

        // Apply user access restrictions
        if (! $user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            Log::info('🔒 Applying organization restriction', [
                'organization_ids' => $organizationIds->toArray(),
            ]);
            $query->whereIn('organization_id', $organizationIds);
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            Log::info('🔍 Applying search filter', ['search_term' => $searchTerm]);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('location', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by organization
        if ($request->filled('organization_id')) {
            Log::info('🏢 Applying organization filter', ['org_id' => $request->organization_id]);
            $query->where('organization_id', $request->organization_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            Log::info('📊 Applying status filter', ['status' => $request->status]);
            match ($request->status) {
                'published' => $query->where('is_published', true),
                'draft' => $query->where('is_published', false),
                'upcoming' => $query->where('start_date', '>', now()),
                'ongoing' => $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now()),
                'past' => $query->where('end_date', '<', now()),
                default => null
            };
        }

        // Date range filter
        if ($request->filled('date_from')) {
            Log::info('📅 Applying date_from filter', ['date_from' => $request->date_from]);
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            Log::info('📅 Applying date_to filter', ['date_to' => $request->date_to]);
            $query->where('end_date', '<=', $request->date_to);
        }

        // Sort options
        $sortField = $request->get('sort', 'start_date');
        $sortDirection = $request->get('direction', 'desc');
        Log::info('📊 Applying sort', ['field' => $sortField, 'direction' => $sortDirection]);

        $allowedSorts = ['name', 'start_date', 'end_date', 'created_at', 'event_days_count'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Check query before execution
        try {
            $queryCount = $query->count();
            Log::info('📊 Query count before pagination', [
                'matching_events' => $queryCount,
                'query_sql' => $query->toSql(),
                'query_bindings' => $query->getBindings(),
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error getting query count', ['error' => $e->getMessage()]);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        Log::info('📄 Executing pagination', ['per_page' => $perPage]);

        try {
            $events = $query->paginate($perPage)->withQueryString();
            Log::info('✅ Pagination successful', [
                'events_count' => $events->count(),
                'total_events' => $events->total(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Pagination failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty result on pagination error
            return Inertia::render('Admin/Events/Index', [
                'events' => [
                    'data' => [],
                    'links' => [],
                    'meta' => [
                        'current_page' => 1,
                        'from' => 0,
                        'last_page' => 1,
                        'per_page' => 15,
                        'to' => 0,
                        'total' => 0,
                    ],
                ],
                'organizations' => [],
                'filters' => [],
                'stats' => ['total' => 0, 'published' => 0, 'upcoming' => 0, 'ongoing' => 0],
                'can_create' => false,
                'debug_error' => 'Pagination error: '.$e->getMessage(),
            ]);
        }

        // Transform events data with safe calculations
        Log::info('🔄 Starting event transformation');

        $transformedEvents = $events->through(function ($event) {
            Log::info('🔄 Transforming event', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'event_slug' => $event->slug,
            ]);

            // Calculate additional stats safely
            try {
                $totalSessions = $this->calculateEventTotalSessions($event);
                $totalPresentations = $this->calculateEventTotalPresentations($event);
            } catch (\Exception $e) {
                Log::warning('Error calculating event stats', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
                $totalSessions = 0;
                $totalPresentations = 0;
            }

            return [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'description' => $event->description ? Str::limit($event->description, 100) : null,
                'status' => $this->determineEventStatus($event),
                'start_date' => $event->start_date?->format('Y-m-d'),
                'end_date' => $event->end_date?->format('Y-m-d'),
                'formatted_date_range' => $this->formatDateRange($event->start_date, $event->end_date),
                'location' => $event->location,
                'is_published' => (bool) $event->is_published,
                'duration' => $event->start_date && $event->end_date
                    ? $event->start_date->diffInDays($event->end_date) + 1
                    : 1,
                'organization' => $event->organization ? [
                    'id' => $event->organization->id,
                    'name' => $event->organization->name,
                    'logo_url' => $event->organization->logo_url ?? null,
                ] : [
                    'id' => null,
                    'name' => 'Organizasyon Belirtilmemiş',
                    'logo_url' => null,
                ],
                'creator' => $event->creator ? [
                    'id' => $event->creator->id,
                    'name' => $event->creator->name,
                ] : [
                    'id' => null,
                    'name' => 'Bilinmeyen Kullanıcı',
                ],
                'event_days_count' => $event->event_days_count ?? 0,
                'total_sessions' => $totalSessions,
                'total_presentations' => $totalPresentations,
                'banner_url' => $event->banner_url ?? null,
                'can_edit' => auth()->user()?->can('update', $event) ?? false,
                'can_delete' => auth()->user()?->can('delete', $event) ?? false,
                'can_publish' => auth()->user()?->can('publish', $event) ?? false,
                'created_at' => $event->created_at,
                'updated_at' => $event->updated_at,
            ];
        });

        Log::info('✅ Event transformation completed', [
            'transformed_events_count' => $transformedEvents->count(),
        ]);

        // Get organizations for filter dropdown
        Log::info('🏢 Getting organizations for dropdown');

        try {
            $organizations = $user->isAdmin()
                ? Organization::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                : $user->organizations()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

            Log::info('✅ Organizations retrieved', ['count' => $organizations->count()]);
        } catch (\Exception $e) {
            Log::error('❌ Error getting organizations', ['error' => $e->getMessage()]);
            $organizations = collect([]);
        }

        // Calculate stats for dashboard
        Log::info('📊 Calculating dashboard stats');

        try {
            $stats = $this->calculateDashboardStats($user);
            Log::info('✅ Stats calculated', $stats);
        } catch (\Exception $e) {
            Log::error('❌ Error calculating stats', ['error' => $e->getMessage()]);
            $stats = ['total' => 0, 'published' => 0, 'upcoming' => 0, 'ongoing' => 0];
        }

        // Prepare response data
        $responseData = [
            'events' => [
                'data' => $transformedEvents->all(),
                'links' => $events->linkCollection(),
                'meta' => [
                    'current_page' => $events->currentPage(),
                    'from' => $events->firstItem(),
                    'last_page' => $events->lastPage(),
                    'per_page' => $events->perPage(),
                    'to' => $events->lastItem(),
                    'total' => $events->total(),
                ],
            ],
            'organizations' => $organizations,
            'filters' => [
                'search' => $request->search,
                'organization_id' => $request->organization_id,
                'status' => $request->status,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'sort' => $sortField,
                'direction' => $sortDirection,
                'per_page' => $perPage,
            ],
            'stats' => $stats,
            'can_create' => auth()->user()?->can('create', Event::class) ?? false,
        ];

        // Final debug log
        Log::info('📤 EventController@index - Final response prepared', [
            'events_data_count' => count($responseData['events']['data']),
            'organizations_count' => $organizations->count(),
            'stats' => $stats,
            'total_events_returned' => $events->total(),
            'pagination_meta' => $responseData['events']['meta'],
        ]);

        return Inertia::render('Admin/Events/Index', $responseData);
    }

    /**
     * Show the form for creating a new event
     */
    public function create(): Response
    {
        $this->authorize('create', Event::class);

        $user = auth()->user();

        // Get available organizations
        $organizations = $user->isAdmin()
            ? Organization::where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : $user->organizations()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Events/Create', [
            'organizations' => $organizations,
            'pageSections' => EventPageKey::sections(),
        ]);
    }

    /**
     * Store a newly created event
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $this->authorize('create', Event::class);

        DB::beginTransaction();

        try {
            $data = $request->validated();
            $pages = $data['pages'] ?? [];
            unset($data['pages']);

            // title'ı name'e dönüştür
            if (isset($data['title'])) {
                $data['name'] = $data['title'];
                unset($data['title']);
            }

            // Generate unique slug
            if (empty($data['slug'])) {
                $data['slug'] = Event::generateUniqueSlug($data['name']);
            }

            // created_by'ı set et
            $data['created_by'] = auth()->id();

            $event = Event::create($data);

            $this->upsertEventPages($event, $pages);

            // Auto create days if requested
            if (! empty($data['auto_create_days']) && $event->start_date && $event->end_date) {
                $this->autoCreateEventDays($event);
            }

            DB::commit();

            return redirect()
                ->route('admin.events.show', $event)
                ->with('success', 'Etkinlik başarıyla oluşturuldu.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('EventController@store failed', [
                'error' => $e->getMessage(),
                'data' => $data ?? null,
            ]);

            return back()
                ->withErrors(['error' => 'Etkinlik oluşturulurken bir hata oluştu: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Auto create event days based on start and end dates
     */
    private function autoCreateEventDays(Event $event): void
    {
        try {
            $eventDaysData = $event->generateEventDaysData();

            // Bulk insert for better performance
            EventDay::insert($eventDaysData);

            Log::info('Auto-created event days', [
                'event_id' => $event->id,
                'days_created' => count($eventDaysData),
            ]);
        } catch (\Exception $e) {
            Log::error('Auto create event days failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Re-throw to be caught by transaction
        }
    }

    /**
     * Display the specified event
     */
    public function show(Event $event): Response
    {
        $this->authorize('view', $event);

        $event->load([
            'organization',
            'creator',
            'eventPages',
            'eventDays' => function ($query) {
                $query->orderBy('date')->with(['venues' => function ($venueQuery) {
                    $venueQuery->orderBy('sort_order')->withCount('programSessions');
                }]);
            },
            'programSessionCategories' => function ($query) {
                $query->orderBy('sort_order')->withCount('programSessions');
            },
        ]);

        // Get event statistics with error handling
        $statistics = $this->calculateEventStatistics($event);

        // Get recent activity
        $recentActivities = $this->getRecentActivities($event);

        // Timeline verilerini hazırla
        $timelineData = $this->prepareTimelineData($event);

        return Inertia::render('Admin/Events/Show', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'description' => $event->description,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'formatted_date_range' => $this->formatDateRange($event->start_date, $event->end_date),
                'location' => $event->location,
                'is_published' => $event->is_published,
                'status' => $this->determineEventStatus($event),
                'duration' => $event->start_date && $event->end_date
                    ? $event->start_date->diffInDays($event->end_date) + 1
                    : 1,
                'created_at' => $event->created_at,
                'updated_at' => $event->updated_at,
                'organization' => $event->organization ? [
                    'id' => $event->organization->id,
                    'name' => $event->organization->name,
                ] : [
                    'id' => null,
                    'name' => 'Organizasyon Belirtilmemiş',
                ],
                'creator' => $event->creator ? [
                    'id' => $event->creator->id,
                    'name' => $event->creator->name,
                ] : [
                    'id' => null,
                    'name' => 'Bilinmeyen Kullanıcı',
                ],
                'event_days' => $event->eventDays->map(function ($day) {
                    return [
                        'id' => $day->id,
                        'date' => $day->date,
                        'title' => $day->title,
                        'display_name' => $day->title,
                        'sort_order' => $day->sort_order,
                        'venues_count' => $day->venues_count ?? 0,
                        'venues' => $day->venues->map(function ($venue) {
                            return [
                                'id' => $venue->id,
                                'name' => $venue->name,
                                'display_name' => $venue->display_name ?? $venue->name,
                                'color' => $venue->color ?? '#6B7280',
                                'sessions_count' => $venue->program_sessions_count ?? 0,
                            ];
                        }),
                    ];
                }),
                'categories' => $event->programSessionCategories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'color' => $category->color,
                        'sessions_count' => $category->program_sessions_count ?? 0,
                    ];
                }),
                'event_days_count' => $event->eventDays->count(),
                'total_sessions' => $statistics['total_sessions'] ?? 0,
                'total_presentations' => $statistics['total_presentations'] ?? 0,
                'total_venues' => $statistics['total_venues'] ?? 0,
                'total_participants' => $statistics['total_participants'] ?? 0,
                'pages' => $event->pagesMap(),
            ],
            'pageSections' => EventPageKey::sections(),
            'statistics' => $statistics,
            'recent_activities' => $recentActivities,
            'timelineData' => $timelineData,
            'can_edit' => auth()->user()?->can('update', $event) ?? false,
            'can_delete' => auth()->user()?->can('delete', $event) ?? false,
            'can_publish' => auth()->user()?->can('publish', $event) ?? false,
            'can_manage_days' => auth()->user()?->can('manageDays', $event) ?? false,
        ]);
    }

    /**
     * Timeline verilerini hazırlayan yardımcı fonksiyon
     */
    private function prepareTimelineData(Event $event): array
    {
        try {
            // Program oturumlarını yükle
            $sessions = ProgramSession::whereHas('venue.eventDay', function ($query) use ($event) {
                $query->where('event_id', $event->id);
            })
                ->with([
                    'venue.eventDay',
                    'presentations.participants',
                    'category',
                    'moderators',
                    'sponsor',
                ])
                ->orderBy('start_time')
                ->get();

            // Etkinlik günlerini yükle
            $eventDays = $event->eventDays()
                ->with(['venues' => function ($query) {
                    $query->orderBy('sort_order');
                }])
                ->orderBy('date')
                ->get();

            // Oturumları map'le
            $mappedSessions = $sessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'description' => $session->description,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'duration_in_minutes' => $session->duration_in_minutes,
                    'is_break' => $session->is_break ?? false,
                    'is_sponsored' => $session->isSponsored() ?? false,
                    'session_type' => $session->session_type,
                    'session_type_display' => $this->getSessionTypeDisplay($session->session_type),
                    'venue' => $session->venue ? [
                        'id' => $session->venue->id,
                        'name' => $session->venue->name,
                        'display_name' => $session->venue->display_name ?? $session->venue->name,
                        'color' => $session->venue->color ?? '#6B7280',
                        'event_day' => $session->venue->eventDay ? [
                            'id' => $session->venue->eventDay->id,
                            'date' => $session->venue->eventDay->date,
                            'display_name' => $session->venue->eventDay->title,
                        ] : null,
                    ] : null,
                    'category' => $session->category ? [
                        'id' => $session->category->id,
                        'name' => $session->category->name,
                        'color' => $session->category->color,
                    ] : null,
                    'presentations_count' => $session->presentations->count(),
                    'moderators_count' => $session->moderators->count(),
                ];
            });

            // Çakışmaları tespit et
            $conflicts = $this->detectTimeConflicts($mappedSessions);

            return [
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                    'location' => $event->location,
                    'organization' => $event->organization ? [
                        'id' => $event->organization->id,
                        'name' => $event->organization->name,
                    ] : null,
                ],
                'eventDays' => $eventDays->map(function ($day) {
                    return [
                        'id' => $day->id,
                        'date' => $day->date,
                        'display_name' => $day->title,
                        'venues' => $day->venues->map(function ($venue) {
                            return [
                                'id' => $venue->id,
                                'name' => $venue->name,
                                'display_name' => $venue->display_name ?? $venue->name,
                                'color' => $venue->color ?? '#6B7280',
                                'capacity' => $venue->capacity,
                                'sort_order' => $venue->sort_order,
                            ];
                        }),
                    ];
                }),
                'sessions' => $mappedSessions,
                'conflicts' => $conflicts,
            ];
        } catch (\Exception $e) {
            Log::warning('Error preparing timeline data', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                ],
                'eventDays' => [],
                'sessions' => [],
                'conflicts' => [],
            ];
        }
    }

    /**
     * Oturum tip görüntüleme helper'ı
     */
    private function getSessionTypeDisplay(?string $sessionType): string
    {
        $types = [
            'keynote' => 'Açılış Konuşması',
            'oral' => 'Sözlü Bildiri',
            'poster' => 'Poster Sunumu',
            'panel' => 'Panel Oturumu',
            'workshop' => 'Çalıştay',
            'break' => 'Ara',
            'lunch' => 'Öğle Arası',
            'coffee' => 'Kahve Arası',
            'networking' => 'Networking',
            'ceremony' => 'Tören',
        ];

        return $types[$sessionType] ?? ($sessionType ?? 'Genel Oturum');
    }

    /**
     * Show the form for editing event
     */
    public function edit(Event $event): Response
    {
        $this->authorize('update', $event);

        $user = auth()->user();

        // Get available organizations
        $organizations = $user->isAdmin()
            ? Organization::where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : $user->organizations()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Event data array
        $event->load('eventPages');

        $eventData = [
            'id' => $event->id,
            'name' => $event->name ?? '',
            'slug' => $event->slug ?? '',
            'description' => $event->description ?? '',
            'start_date' => $event->start_date ? $event->start_date->format('Y-m-d') : '',
            'end_date' => $event->end_date ? $event->end_date->format('Y-m-d') : '',
            'location' => $event->location ?? '',
            'organization_id' => $event->organization_id ?? null,
            'is_published' => (bool) $event->is_published,
            'pages' => $event->pagesMap(),
        ];

        return Inertia::render('Admin/Events/Edit', [
            'event' => $eventData,
            'organizations' => $organizations,
            'pageSections' => EventPageKey::sections(),
        ]);
    }

    /**
     * Update the specified event
     */
    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        DB::beginTransaction();

        try {
            $data = $request->validated();
            $pages = $data['pages'] ?? [];
            unset($data['pages']);

            if (isset($data['title'])) {
                $data['name'] = $data['title'];
                unset($data['title']);
            }

            // Generate unique slug if name changed
            if (isset($data['name']) && $data['name'] !== $event->name && empty($data['slug'])) {
                $data['slug'] = Event::generateUniqueSlug($data['name'], $event->slug);
            }

            $data = array_intersect_key($data, array_flip($event->getFillable()));

            $event->update($data);

            $this->upsertEventPages($event, $pages);

            DB::commit();

            return redirect()
                ->route('admin.events.show', $event)
                ->with('success', 'Etkinlik başarıyla güncellendi.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('EventController@update failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'data' => $data ?? null,
            ]);

            return back()
                ->withErrors(['error' => 'Etkinlik güncellenirken bir hata oluştu: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Publish/unpublish event
     */
    public function togglePublish(Event $event): RedirectResponse
    {
        $this->authorize('publish', $event);

        try {
            if (! $event->is_published && ! $this->canEventBePublished($event)) {
                return back()->withErrors([
                    'error' => 'Etkinlik yayınlanabilmesi için en az bir gün ve salona sahip olmalıdır.',
                ]);
            }

            $event->update(['is_published' => ! $event->is_published]);

            $message = $event->is_published
                ? 'Etkinlik başarıyla yayınlandı.'
                : 'Etkinlik yayından kaldırıldı.';

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('EventController@togglePublish failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Etkinlik durumu değiştirilirken bir hata oluştu.',
            ]);
        }
    }

    /**
     * Duplicate event
     */
    public function duplicate(Event $event): RedirectResponse
    {
        $this->authorize('create', Event::class);
        $this->authorize('view', $event);

        DB::beginTransaction();

        try {
            $newEvent = $event->replicate();
            $newEvent->name = $event->name.' (Kopya)';
            $newEvent->slug = Event::generateUniqueSlug($newEvent->name);
            $newEvent->is_published = false;
            $newEvent->created_by = auth()->id();
            $newEvent->save();

            // Copy event days and venues
            foreach ($event->eventDays as $day) {
                $newDay = $day->replicate();
                $newDay->event_id = $newEvent->id;
                $newDay->save();

                foreach ($day->venues as $venue) {
                    $newVenue = $venue->replicate();
                    $newVenue->event_day_id = $newDay->id;
                    $newVenue->save();
                }
            }

            // Copy session categories
            foreach ($event->programSessionCategories as $category) {
                $newCategory = $category->replicate();
                $newCategory->event_id = $newEvent->id;
                $newCategory->save();
            }

            DB::commit();

            return redirect()
                ->route('admin.events.show', $newEvent)
                ->with('success', 'Etkinlik başarıyla kopyalandı.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('EventController@duplicate failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Etkinlik kopyalanırken bir hata oluştu: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified event
     */
    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        try {
            // Check if event is published
            if ($event->is_published) {
                return back()->withErrors([
                    'error' => 'Yayınlanmış etkinlik silinemez. Önce yayından kaldırın.',
                ]);
            }

            DB::beginTransaction();

            $eventName = $event->name;
            $event->delete();

            DB::commit();

            return redirect()
                ->route('admin.events.index')
                ->with('success', "'{$eventName}' etkinliği başarıyla silindi.");
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('EventController@destroy failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Etkinlik silinirken bir hata oluştu.',
            ]);
        }
    }

    /**
     * Export event program
     */
    public function export(Event $event, Request $request): RedirectResponse
    {
        $this->authorize('view', $event);

        $format = $request->get('format', 'pdf');

        // This will be implemented in ExportController
        return redirect()->route('admin.export.event-program', [
            'event' => $event,
            'format' => $format,
        ]);
    }

    /**
     * Get event public program
     */
    public function publicProgram(Event $event): RedirectResponse
    {
        if (! $event->is_published) {
            abort(404);
        }

        // This will redirect to public API
        return redirect()->route('api.events.program', $event->slug);
    }

    /**
     * PRIVATE HELPER METHODS
     */

    /**
     * Determine event status based on dates and publication status
     */
    private function determineEventStatus($event): string
    {
        if (! $event->is_published) {
            return 'draft';
        }

        $now = now();
        $startDate = $event->start_date;
        $endDate = $event->end_date;

        if (! $startDate) {
            return 'draft';
        }

        if ($startDate > $now) {
            return 'upcoming';
        }

        if ($endDate && $endDate < $now) {
            return 'completed';
        }

        if ($startDate <= $now && (! $endDate || $endDate >= $now)) {
            return 'ongoing';
        }

        return 'published';
    }

    /**
     * Format date range for display
     */
    private function formatDateRange($startDate, $endDate): string
    {
        if (! $startDate) {
            return '-';
        }

        $start = $startDate->format('d.m.Y');

        if (! $endDate || $startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
            return $start;
        }

        $end = $endDate->format('d.m.Y');

        return "{$start} - {$end}";
    }

    /**
     * Calculate total sessions for an event safely
     */
    private function calculateEventTotalSessions($event): int
    {
        try {
            return $event->eventDays()
                ->with(['venues.programSessions'])
                ->get()
                ->flatMap->venues
                ->sum(function ($venue) {
                    return $venue->programSessions->count();
                });
        } catch (\Exception $e) {
            Log::warning('Error calculating total sessions', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Calculate total presentations for an event safely
     */
    private function calculateEventTotalPresentations($event): int
    {
        try {
            return $event->eventDays()
                ->with(['venues.programSessions.presentations'])
                ->get()
                ->flatMap->venues
                ->flatMap->programSessions
                ->sum(function ($session) {
                    return $session->presentations->count();
                });
        } catch (\Exception $e) {
            Log::warning('Error calculating total presentations', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Calculate dashboard stats
     */
    private function calculateDashboardStats($user): array
    {
        try {
            $baseQuery = Event::query();

            if (! $user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $baseQuery->whereIn('organization_id', $organizationIds);
            }

            return [
                'total' => $baseQuery->count(),
                'published' => $baseQuery->where('is_published', true)->count(),
                'upcoming' => $baseQuery->where('start_date', '>', now())->count(),
                'ongoing' => $baseQuery->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::warning('Error calculating dashboard stats', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'total' => 0,
                'published' => 0,
                'upcoming' => 0,
                'ongoing' => 0,
            ];
        }
    }

    /**
     * Calculate event statistics
     */
    private function calculateEventStatistics($event): array
    {
        try {
            $totalDays = $event->eventDays()->count();
            $totalVenues = $event->eventDays()->withCount('venues')->get()->sum('venues_count');
            $totalSessions = $this->calculateEventTotalSessions($event);
            $totalPresentations = $this->calculateEventTotalPresentations($event);

            // Calculate unique participants
            $totalParticipants = $event->eventDays()
                ->with(['venues.programSessions.moderators', 'venues.programSessions.presentations.speakers'])
                ->get()
                ->flatMap(function ($day) {
                    return $day->venues->flatMap(function ($venue) {
                        $moderators = $venue->programSessions->flatMap->moderators;
                        $speakers = $venue->programSessions->flatMap->presentations->flatMap->speakers;

                        return $moderators->merge($speakers);
                    });
                })
                ->unique('id')
                ->count();

            return [
                'total_days' => $totalDays,
                'total_venues' => $totalVenues,
                'total_sessions' => $totalSessions,
                'total_presentations' => $totalPresentations,
                'total_participants' => $totalParticipants,
            ];
        } catch (\Exception $e) {
            Log::warning('Error calculating event statistics', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'total_days' => 0,
                'total_venues' => 0,
                'total_sessions' => 0,
                'total_presentations' => 0,
                'total_participants' => 0,
            ];
        }
    }

    /**
     * Get recent activities for an event
     */
    private function getRecentActivities($event): array
    {
        try {
            $recentActivities = collect();

            // Recent event days
            $recentDays = $event->eventDays()->latest()->limit(3)->get();
            foreach ($recentDays as $day) {
                $recentActivities->push([
                    'type' => 'day_created',
                    'title' => 'Yeni Gün',
                    'message' => "'{$day->title}' günü oluşturuldu",
                    'date' => $day->created_at,
                    'link' => route('admin.events.days.show', [$event, $day]),
                ]);
            }

            return $recentActivities->sortByDesc('date')->take(10)->values()->toArray();
        } catch (\Exception $e) {
            Log::warning('Error getting recent activities', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Check if event can be published
     */
    private function canEventBePublished($event): bool
    {
        try {
            return $event->eventDays()->count() > 0 &&
                $event->eventDays()->whereHas('venues')->count() > 0;
        } catch (\Exception $e) {
            Log::warning('Error checking if event can be published', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Show event timeline view
     */
    public function timeline(Event $event)
    {
        $this->authorize('view', $event);

        try {
            // Load event with necessary relationships
            $event->load(['organization', 'eventDays.venues']);

            // Get all event days with their venues
            $eventDays = $event->eventDays()
                ->with(['venues' => function ($query) {
                    $query->ordered();
                }])
                ->orderBy('date')
                ->get()
                ->map(function ($eventDay) {
                    return [
                        'id' => $eventDay->id,
                        'display_name' => $eventDay->display_name,
                        'date' => $eventDay->date,
                        'venues' => $eventDay->venues->map(function ($venue) {
                            return [
                                'id' => $venue->id,
                                'display_name' => $venue->display_name,
                                'color' => $venue->color,
                            ];
                        }),
                    ];
                });

            // Get all program sessions for this event
            $sessions = ProgramSession::whereHas('venue.eventDay', function ($query) use ($event) {
                $query->where('event_id', $event->id);
            })
                ->with([
                    'venue.eventDay',
                    'sponsor',
                    'moderators',
                    'categories',
                    'presentations',
                ])
                ->orderBy('start_time')
                ->get()
                ->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'title' => $session->title,
                        'description' => $session->description,
                        'start_time' => $session->start_time ? $session->start_time->format('H:i') : null,
                        'end_time' => $session->end_time ? $session->end_time->format('H:i') : null,
                        'duration_in_minutes' => $session->duration_in_minutes,
                        'session_type' => $session->session_type,
                        'is_break' => $session->is_break,
                        'is_sponsored' => $session->isSponsored(),
                        'moderators_count' => $session->moderators->count(),
                        'presentations_count' => $session->presentations->count(),
                        'can_edit' => auth()->user()->can('update', $session),
                        'can_delete' => auth()->user()->can('delete', $session),
                        'venue' => [
                            'id' => $session->venue->id,
                            'display_name' => $session->venue->display_name,
                            'color' => $session->venue->color,
                            'event_day' => [
                                'id' => $session->venue->eventDay->id,
                                'display_name' => $session->venue->eventDay->display_name,
                                'date' => $session->venue->eventDay->date,
                            ],
                        ],
                        'sponsor' => $session->sponsor ? [
                            'id' => $session->sponsor->id,
                            'name' => $session->sponsor->name,
                            'logo_url' => $session->sponsor->logo_url,
                        ] : null,
                    ];
                });

            // Detect time conflicts
            $conflicts = $this->detectTimeConflicts($sessions);

            // Transform event data
            $eventData = [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'location' => $event->location,
                'is_published' => $event->is_published,
                'organization' => [
                    'id' => $event->organization->id,
                    'name' => $event->organization->name,
                ],
            ];

            return Inertia::render('Admin/Events/Timeline', [
                'event' => $eventData,
                'eventDays' => $eventDays,
                'sessions' => $sessions,
                'conflicts' => $conflicts,
            ]);
        } catch (\Exception $e) {
            Log::error('Event timeline load failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // ✅ FIX: Use redirect with proper route instead of back()
            return redirect()->route('admin.events.show', $event)
                ->withErrors(['error' => 'Timeline yüklenirken bir hata oluştu.']);
        }
    }

    /**
     * Export event timeline in various formats
     *
     * @return BinaryFileResponse|JsonResponse|RedirectResponse
     */
    public function exportTimeline(Request $request, Event $event)
    {
        $this->authorize('view', $event);

        $format = $request->input('format', 'pdf');

        try {
            // Load event data for export
            $event->load(['organization', 'eventDays.venues']);

            // Get sessions data
            $sessions = ProgramSession::whereHas('venue.eventDay', function ($query) use ($event) {
                $query->where('event_id', $event->id);
            })
                ->with([
                    'venue.eventDay',
                    'sponsor',
                    'moderators',
                    'categories',
                    'presentations',
                ])
                ->orderBy('start_time')
                ->get();

            switch ($format) {
                case 'pdf':
                    return $this->exportTimelinePdf($event, $sessions);

                case 'excel':
                    return $this->exportTimelineExcel($event, $sessions);

                case 'image':
                    return $this->exportTimelineImage($event, $sessions);

                default:
                    abort(400, 'Geçersiz dışa aktarım formatı');
            }
        } catch (\Exception $e) {
            Log::error('Timeline export failed', [
                'event_id' => $event->id,
                'format' => $format,
                'error' => $e->getMessage(),
            ]);

            // ✅ FIX: Use redirect with proper route instead of back()
            return redirect()->route('admin.events.show', $event)
                ->withErrors(['error' => 'Dışa aktarım sırasında bir hata oluştu.']);
        }
    }

    /**
     * Detect time conflicts between sessions
     *
     * @param  Collection|array  $sessions
     */
    private function detectTimeConflicts($sessions): array
    {
        $conflicts = [];

        // ✅ FIX: Handle both Collection and array input
        $sessionsCollection = collect($sessions);
        $sessionsByVenue = $sessionsCollection->groupBy('venue.id');

        foreach ($sessionsByVenue as $venueId => $venueSessions) {
            $venueSessions = collect($venueSessions)->sortBy('start_time');

            for ($i = 0; $i < $venueSessions->count() - 1; $i++) {
                $currentSession = $venueSessions->values()->get($i); // ✅ FIX: Use values() to reset keys
                $nextSession = $venueSessions->values()->get($i + 1);

                // ✅ FIX: Proper array access for mapped data
                if (
                    ! isset($currentSession['start_time']) || ! isset($currentSession['end_time']) ||
                    ! isset($nextSession['start_time']) || ! isset($nextSession['end_time']) ||
                    ! $currentSession['start_time'] || ! $currentSession['end_time'] ||
                    ! $nextSession['start_time'] || ! $nextSession['end_time']
                ) {
                    continue;
                }

                // Check if current session ends after next session starts
                if ($currentSession['end_time'] > $nextSession['start_time']) {
                    $conflicts[] = [
                        'venue_id' => $venueId,
                        'session1' => [
                            'id' => $currentSession['id'],
                            'title' => $currentSession['title'],
                            'start_time' => $currentSession['start_time'],
                            'end_time' => $currentSession['end_time'],
                        ],
                        'session2' => [
                            'id' => $nextSession['id'],
                            'title' => $nextSession['title'],
                            'start_time' => $nextSession['start_time'],
                            'end_time' => $nextSession['end_time'],
                        ],
                        'overlap_minutes' => $this->calculateOverlapMinutes(
                            $currentSession['end_time'],
                            $nextSession['start_time']
                        ),
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Calculate overlap in minutes between two times
     */
    private function calculateOverlapMinutes(string $endTime, string $startTime): int
    {
        try {
            $end = Carbon::createFromFormat('H:i', $endTime);
            $start = Carbon::createFromFormat('H:i', $startTime);

            if ($end > $start) {
                return $end->diffInMinutes($start);
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Export timeline as PDF
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $sessions
     * @return BinaryFileResponse
     */
    private function exportTimelinePdf(Event $event, $sessions)
    {
        // ✅ FIX: Check if PDF package is available
        if (! class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            return response()->json([
                'error' => 'PDF export paketi yüklü değil. Lütfen barryvdh/laravel-dompdf paketini yükleyin.',
                'format' => 'pdf',
            ], 500);
        }

        // Group sessions by day and venue for PDF layout
        $groupedSessions = $this->groupSessionsForExport($sessions);

        try {
            $pdf = Pdf::loadView('exports.timeline-pdf', [
                'event' => $event,
                'groupedSessions' => $groupedSessions,
                'exportDate' => now(),
            ]);

            $filename = Event::createSlugFromTurkish($event->name).'-program-'.now()->format('Y-m-d').'.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('PDF export failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'PDF oluşturulurken hata oluştu: '.$e->getMessage(),
                'format' => 'pdf',
            ], 500);
        }
    }

    /**
     * Export timeline as Excel
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $sessions
     * @return BinaryFileResponse
     */
    private function exportTimelineExcel(Event $event, $sessions)
    {
        // ✅ FIX: Check if Excel package is available
        if (! class_exists('Rap2hpoutre\FastExcel\FastExcel')) {
            return response()->json([
                'error' => 'Excel export paketi yüklü değil. Lütfen rap2hpoutre/fast-excel paketini yükleyin.',
                'format' => 'excel',
            ], 500);
        }

        $data = [];

        foreach ($sessions as $session) {
            $data[] = [
                'Tarih' => $session->venue->eventDay->date,
                'Gün' => $session->venue->eventDay->display_name,
                'Salon' => $session->venue->display_name,
                'Başlangıç' => $session->start_time ? $session->start_time->format('H:i') : '',
                'Bitiş' => $session->end_time ? $session->end_time->format('H:i') : '',
                'Süre (dk)' => $session->duration_in_minutes,
                'Oturum Başlığı' => $session->title,
                'Açıklama' => $session->description,
                'Tür' => $session->session_type_display ?? $session->session_type,
                'Ara mı?' => $session->is_break ? 'Evet' : 'Hayır',
                'Sponsorlu mu?' => $session->isSponsored() ? 'Evet' : 'Hayır',
                'Moderatör Sayısı' => $session->moderators->count(),
                'Sunum Sayısı' => $session->presentations->count(),
            ];
        }

        $filename = Event::createSlugFromTurkish($event->name).'-program-'.now()->format('Y-m-d').'.xlsx';

        try {
            return (new FastExcel($data))->download($filename);
        } catch (\Exception $e) {
            Log::error('Excel export failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Excel dosyası oluşturulurken hata oluştu: '.$e->getMessage(),
                'format' => 'excel',
            ], 500);
        }
    }

    /**
     * @param  array<string, string|null>  $pages
     */
    private function upsertEventPages(Event $event, array $pages): void
    {
        foreach (EventPageKey::cases() as $pageKey) {
            $content = $pages[$pageKey->value] ?? null;

            if ($content === null) {
                continue;
            }

            EventPage::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'key' => $pageKey->value,
                ],
                [
                    'content' => $content,
                    'sort_order' => $pageKey->sortOrder(),
                    'is_published' => true,
                ]
            );
        }
    }
}
