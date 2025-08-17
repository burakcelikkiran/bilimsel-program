<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProgramSessionRequest;
use App\Http\Requests\Admin\UpdateProgramSessionRequest;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ProgramSession;
use App\Models\ProgramSessionCategory;
use App\Models\Sponsor;
use App\Models\Venue;
use App\Models\EventDay;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ProgramSessionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of program sessions
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        $query = ProgramSession::with(['venue.eventDay.event.organization', 'sponsor', 'moderators', 'categories'])
            ->withCount(['presentations', 'moderators']);

        // Apply user access restrictions
        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $query->whereHas('venue.eventDay.event', function ($eventQuery) use ($organizationIds) {
                $eventQuery->whereIn('organization_id', $organizationIds);
            });
        }

        // Filter by event
        if ($request->filled('event_id')) {
            $query->whereHas('venue.eventDay', function ($dayQuery) use ($request) {
                $dayQuery->where('event_id', $request->event_id);
            });
        }

        // Filter by venue
        if ($request->filled('venue_id')) {
            $query->where('venue_id', $request->venue_id);
        }

        // Filter by session type
        if ($request->filled('session_type')) {
            $query->where('session_type', $request->session_type);
        }

        // Filter by sponsor
        if ($request->filled('sponsor_id')) {
            $query->where('sponsor_id', $request->sponsor_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($categoryQuery) use ($request) {
                $categoryQuery->where('program_session_category_id', $request->category_id);
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Exclude breaks if requested
        if ($request->boolean('exclude_breaks')) {
            $query->nonBreaks();
        }

        // Sort options
        $sortField = $request->get('sort', 'start_time');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSorts = ['title', 'start_time', 'end_time', 'session_type', 'created_at', 'presentations_count'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $sessions = $query->paginate(20)
            ->withQueryString()
            ->through(function ($session) {
                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'description' => $session->description,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'formatted_time_range' => $session->formatted_time_range,
                    'duration_in_minutes' => $session->duration_in_minutes,
                    'formatted_duration' => $session->formatted_duration,
                    'session_type' => $session->session_type,
                    'session_type_display' => $session->session_type_display,
                    'moderator_title' => $session->moderator_title,
                    'is_break' => $session->is_break,
                    'is_sponsored' => $session->isSponsored(),
                    'venue' => [
                        'id' => $session->venue->id,
                        'display_name' => $session->venue->display_name,
                        'color' => $session->venue->color,
                        'event_day' => [
                            'display_name' => $session->venue->eventDay->display_name,
                            'date' => $session->venue->eventDay->date,
                            'event' => [
                                'id' => $session->venue->eventDay->event->id,
                                'name' => $session->venue->eventDay->event->name,
                                'organization' => [
                                    'name' => $session->venue->eventDay->event->organization->name,
                                ],
                            ],
                        ],
                    ],
                    'sponsor' => $session->sponsor ? [
                        'id' => $session->sponsor->id,
                        'name' => $session->sponsor->name,
                        'logo_url' => $session->sponsor->logo_url,
                    ] : null,
                    'moderators' => $session->moderators->map(function ($moderator) {
                        return [
                            'id' => $moderator->id,
                            'full_name' => $moderator->full_name,
                            'affiliation' => $moderator->affiliation,
                        ];
                    }),
                    'categories' => $session->categories->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'color' => $category->color,
                        ];
                    }),
                    'presentations_count' => $session->presentations_count,
                    'moderators_count' => $session->moderators_count,
                    'can_edit' => auth()->user()?->can('update', $session) ?? false,
                    'can_delete' => auth()->user()?->can('delete', $session) ?? false,
                ];
            });

        // Get filter options
        $filterOptions = $this->getFilterOptions($user);

        return Inertia::render('Admin/ProgramSessions/Index', [
            'sessions' => $sessions,
            'filter_options' => $filterOptions,
            'filters' => [
                'search' => $request->search,
                'event_id' => $request->event_id,
                'venue_id' => $request->venue_id,
                'session_type' => $request->session_type,
                'sponsor_id' => $request->sponsor_id,
                'category_id' => $request->category_id,
                'exclude_breaks' => $request->boolean('exclude_breaks'),
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
            'can_create' => auth()->user()?->can('create', ProgramSession::class) ?? false,
        ]);
    }

    /**
     * Store a newly created program session
     */
    public function store(StoreProgramSessionRequest $request)
    {
        $this->authorize('create', ProgramSession::class);

        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Create session
            $session = ProgramSession::create($data);

            // Attach moderators if provided
            if (!empty($data['moderator_ids'])) {
                $moderators = collect($data['moderator_ids'])->mapWithKeys(function ($id, $index) {
                    return [$id => ['sort_order' => $index + 1]];
                });
                $session->moderators()->attach($moderators);
            }

            // Attach categories if provided
            if (!empty($data['category_ids'])) {
                $session->categories()->attach($data['category_ids']);
            }

            DB::commit();

            return redirect()
                ->route('admin.program-sessions.show', $session)
                ->with('success', 'Oturum başarıyla oluşturuldu.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Oturum oluşturulurken bir hata oluştu: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for creating a new program session
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', ProgramSession::class);

        $user = auth()->user();

        // Get event context from URL parameters
        $eventId = $request->get('event_id');
        $eventDayId = $request->get('event_day_id');
        $venueId = $request->get('venue_id');

        // Get available data for form with event context
        $formData = $this->getFormDataWithEventContext($user, $eventId, $eventDayId, $venueId);

        return Inertia::render('Admin/ProgramSessions/Create', $formData);
    }



    /**
     * Get form data for create/edit forms with event context
     */
    private function getFormDataWithEventContext($user, $eventId = null, $eventDayId = null, $venueId = null)
    {
        // Get events accessible to user
        $eventsQuery = Event::with(['eventDays.venues']);

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $eventsQuery->whereIn('organization_id', $organizationIds);
        }

        $events = $eventsQuery->orderBy('start_date', 'desc')->get();

        // Get selected event
        $selectedEvent = null;
        if ($eventId) {
            $selectedEvent = $events->firstWhere('id', $eventId);
        }

        // Get event days for selected event - FIX: use display_name instead of title
        $eventDays = collect(); // Initialize as empty collection
        if ($selectedEvent) {
            $eventDays = $selectedEvent->eventDays()
                ->ordered()
                ->select(['id', 'display_name', 'date', 'event_id'])
                ->get();
        }

        // Get selected event day
        $selectedEventDay = null;
        if ($eventDayId && $selectedEvent) {
            $selectedEventDay = $eventDays->firstWhere('id', $eventDayId);
        }

        // Get venues for selected event day
        $venues = collect(); // Initialize as empty collection
        if ($selectedEventDay) {
            $venues = $selectedEventDay->venues()->ordered()->get(['id', 'name', 'display_name', 'color', 'event_day_id']);
        }

        // Get participants for moderators
        $participantsQuery = Participant::moderators();

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $participantsQuery->whereIn('organization_id', $organizationIds);
        }

        $participants = $participantsQuery->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'title', 'affiliation']);

        // Get sponsors
        $sponsorsQuery = Sponsor::active();

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $sponsorsQuery->whereIn('organization_id', $organizationIds);
        }

        $sponsors = $sponsorsQuery->orderBy('name')->get(['id', 'name', 'logo'])->map(function ($sponsor) {
            return [
                'id' => $sponsor->id,
                'name' => $sponsor->name,
                'logo_url' => $sponsor->logo ? asset('storage/' . $sponsor->logo) : null,
            ];
        });

        // Get categories for selected event
        $categories = collect(); // Initialize as empty collection
        if ($selectedEvent) {
            $categories = $selectedEvent->programSessionCategories()->ordered()->get(['id', 'name', 'color']);
        }

        return [
            // Events for initial selection
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'formatted_date_range' => $event->formatted_date_range,
                ];
            }),

            // Event context
            'selectedEvent' => $selectedEvent ? [
                'id' => $selectedEvent->id,
                'name' => $selectedEvent->name,
                'slug' => $selectedEvent->slug,
            ] : null,

            // Ensure eventDays is always a collection before calling map()
            'eventDays' => $eventDays->map(function ($day) {
                return [
                    'id' => $day->id,
                    'title' => $day->display_name ?? "Gün " . ($day->sort_order + 1),
                    'date' => $day->date,
                    'display_name' => $day->display_name ?? "Gün " . ($day->sort_order + 1),
                ];
            }),

            'selectedEventDay' => $selectedEventDay ? [
                'id' => $selectedEventDay->id,
                'title' => $selectedEventDay->display_name ?? "Gün " . ($selectedEventDay->sort_order + 1),
                'date' => $selectedEventDay->date,
                'display_name' => $selectedEventDay->display_name ?? "Gün " . ($selectedEventDay->sort_order + 1),
            ] : null,

            // Ensure venues is always a collection before calling map()
            'venues' => $venues->map(function ($venue) {
                return [
                    'id' => $venue->id,
                    'name' => $venue->display_name ?? $venue->name,
                    'display_name' => $venue->display_name ?? $venue->name,
                    'color' => $venue->color,
                ];
            }),

            'participants' => $participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'full_name' => $participant->full_name,
                    'title' => $participant->title,
                    'affiliation' => $participant->affiliation,
                ];
            }),

            'sponsors' => $sponsors,

            // Ensure categories is always a collection
            'categories' => $categories,

            'sessionTypes' => [
                ['value' => 'main', 'label' => 'Ana Oturum'],
                ['value' => 'satellite', 'label' => 'Uydu Sempozyumu'],
                ['value' => 'oral_presentation', 'label' => 'Sözlü Bildiri'],
                ['value' => 'special', 'label' => 'Özel Oturum'],
                ['value' => 'break', 'label' => 'Ara'],
            ],

            'moderatorTitles' => [
                ['value' => 'Oturum Başkanı', 'label' => 'Oturum Başkanı'],
                ['value' => 'Oturum Başkanları', 'label' => 'Oturum Başkanları'],
                ['value' => 'Kolaylaştırıcı', 'label' => 'Kolaylaştırıcı'],
                ['value' => 'Moderatör', 'label' => 'Moderatör'],
                ['value' => 'Başkan', 'label' => 'Başkan'],
            ],

            // For pre-selection
            'selectedEventId' => $eventId,
            'selectedEventDayId' => $eventDayId,
            'selectedVenueId' => $venueId,
        ];
    }


    /**
     * AJAX endpoint to get event days for selected event
     */
    public function getEventDays(Request $request): JsonResponse
    {
        $eventId = $request->get('event_id');

        if (!$eventId) {
            return response()->json([
                'success' => false,
                'message' => 'Event ID gerekli'
            ], 400);
        }

        $user = auth()->user();
        $eventQuery = Event::where('id', $eventId);

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $eventQuery->whereIn('organization_id', $organizationIds);
        }

        $event = $eventQuery->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Etkinlik bulunamadı'
            ], 404);
        }

        // FIX: Use display_name instead of title
        $eventDays = $event->eventDays()
            ->ordered()
            ->select(['id', 'display_name', 'date', 'event_id'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $eventDays->map(function ($day) {
                return [
                    'id' => $day->id,
                    'title' => $day->display_name ?? "Gün " . ($day->sort_order + 1),
                    'date' => $day->date,
                    'display_name' => $day->display_name ?? "Gün " . ($day->sort_order + 1),
                ];
            })
        ]);
    }



    /**
     * AJAX endpoint to get venues for selected event day
     */
    public function getVenuesForEventDay(Request $request): JsonResponse
    {
        $eventDayId = $request->get('event_day_id');

        if (!$eventDayId) {
            return response()->json([
                'success' => false,
                'message' => 'Event Day ID gerekli'
            ], 400);
        }

        $user = auth()->user();
        $eventDayQuery = EventDay::where('id', $eventDayId);

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $eventDayQuery->whereHas('event', function ($query) use ($organizationIds) {
                $query->whereIn('organization_id', $organizationIds);
            });
        }

        $eventDay = $eventDayQuery->first();

        if (!$eventDay) {
            return response()->json([
                'success' => false,
                'message' => 'Etkinlik günü bulunamadı'
            ], 404);
        }

        $venues = $eventDay->venues()->ordered()->get(['id', 'name', 'display_name', 'color', 'event_day_id']);

        return response()->json([
            'success' => true,
            'data' => $venues->map(function ($venue) {
                return [
                    'id' => $venue->id,
                    'name' => $venue->display_name ?? $venue->name,
                    'display_name' => $venue->display_name ?? $venue->name,
                    'color' => $venue->color,
                ];
            })
        ]);
    }


    /**
     * AJAX endpoint to get categories for selected event
     */
    public function getCategoriesForEvent(Request $request): JsonResponse
    {
        $eventId = $request->get('event_id');

        if (!$eventId) {
            return response()->json([
                'success' => false,
                'message' => 'Event ID gerekli'
            ], 400);
        }

        $user = auth()->user();
        $eventQuery = Event::where('id', $eventId);

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $eventQuery->whereIn('organization_id', $organizationIds);
        }

        $event = $eventQuery->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Etkinlik bulunamadı'
            ], 404);
        }

        $categories = $event->programSessionCategories()->ordered()->get(['id', 'name', 'color']);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }



    /**
     * Display the specified program session
     */
    public function show(ProgramSession $programSession): Response
    {
        $this->authorize('view', $programSession);

        $programSession->load([
            'venue.eventDay.event.organization',
            'sponsor',
            'moderators' => function ($query) {
                $query->orderBy('sort_order'); // pivot_sort_order yerine sort_order
            },
            'categories',
            'presentations' => function ($query) {
                $query->ordered()->with(['speakers' => function ($speakerQuery) {
                    $speakerQuery->orderBy('sort_order'); // pivot_sort_order yerine sort_order
                }, 'sponsor']);
            }
        ]);

        return Inertia::render('Admin/ProgramSessions/Show', [
            'session' => [
                'id' => $programSession->id,
                'title' => $programSession->title,
                'description' => $programSession->description,
                'start_time' => $programSession->start_time,
                'end_time' => $programSession->end_time,
                'formatted_time_range' => $programSession->formatted_time_range,
                'duration_in_minutes' => $programSession->duration_in_minutes,
                'formatted_duration' => $programSession->formatted_duration,
                'session_type' => $programSession->session_type,
                'session_type_display' => $programSession->session_type_display,
                'moderator_title' => $programSession->moderator_title,
                'is_break' => $programSession->is_break,
                'sort_order' => $programSession->sort_order,
                'created_at' => $programSession->created_at,
                'updated_at' => $programSession->updated_at,
                'venue' => [
                    'id' => $programSession->venue->id,
                    'display_name' => $programSession->venue->display_name,
                    'color' => $programSession->venue->color,
                    'capacity' => $programSession->venue->capacity,
                    'event_day' => [
                        'id' => $programSession->venue->eventDay->id,
                        'display_name' => $programSession->venue->eventDay->display_name,
                        'date' => $programSession->venue->eventDay->date,
                        'event' => [
                            'id' => $programSession->venue->eventDay->event->id,
                            'name' => $programSession->venue->eventDay->event->name,
                            'slug' => $programSession->venue->eventDay->event->slug,
                            'organization' => [
                                'name' => $programSession->venue->eventDay->event->organization->name,
                            ],
                        ],
                    ],
                ],
                'sponsor' => $programSession->sponsor ? [
                    'id' => $programSession->sponsor->id,
                    'name' => $programSession->sponsor->name,
                    'logo_url' => $programSession->sponsor->logo_url,
                    'website' => $programSession->sponsor->website,
                ] : null,
                'moderators' => $programSession->moderators->map(function ($moderator) {
                    return [
                        'id' => $moderator->id,
                        'full_name' => $moderator->full_name,
                        'title' => $moderator->title,
                        'affiliation' => $moderator->affiliation,
                        'email' => $moderator->email,
                        'sort_order' => $moderator->pivot->sort_order ?? 0, // Null kontrolü eklendi
                    ];
                }),
                'categories' => $programSession->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'color' => $category->color,
                    ];
                }),
                'presentations' => $programSession->presentations->map(function ($presentation) {
                    return [
                        'id' => $presentation->id,
                        'title' => $presentation->title,
                        'abstract' => $presentation->abstract,
                        'start_time' => $presentation->start_time,
                        'end_time' => $presentation->end_time,
                        'formatted_time_range' => $presentation->formatted_time_range,
                        'presentation_type' => $presentation->presentation_type,
                        'presentation_type_display' => $presentation->presentation_type_display,
                        'sort_order' => $presentation->sort_order,
                        'speakers' => $presentation->speakers->map(function ($speaker) {
                            return [
                                'id' => $speaker->id,
                                'full_name' => $speaker->full_name,
                                'title' => $speaker->title,
                                'affiliation' => $speaker->affiliation,
                                'speaker_role' => $speaker->pivot->speaker_role ?? 'speaker',
                                'sort_order' => $speaker->pivot->sort_order ?? 0,
                            ];
                        }),
                        'sponsor' => $presentation->sponsor ? [
                            'name' => $presentation->sponsor->name,
                            'logo_url' => $presentation->sponsor->logo_url,
                        ] : null,
                    ];
                }),
            ],
            'can_edit' => auth()->user()?->can('update', $programSession) ?? false,
            'can_delete' => auth()->user()?->can('delete', $programSession) ?? false,
            'can_create_presentations' => auth()->user()?->can('createPresentations', $programSession) ?? false,
            'can_manage_moderators' => auth()->user()?->can('manageModerators', $programSession) ?? false,
        ]);
    }

    /**
     * Show the form for editing program session - Mevcut uzun versiyonu korundu
     */
    public function edit(ProgramSession $programSession): Response
    {
        $this->authorize('update', $programSession);

        $user = auth()->user();

        // Load necessary relationships
        $programSession->load([
            'venue.eventDay.event',
            'sponsor',
            'moderators',
            'categories'
        ]);

        // Format times for frontend (HH:MM format)
        $startTime = $programSession->start_time ? $programSession->start_time->format('H:i') : '';
        $endTime = $programSession->end_time ? $programSession->end_time->format('H:i') : '';

        // Get venues - same event's venues only
        $venues = [];
        if ($programSession->venue && $programSession->venue->eventDay && $programSession->venue->eventDay->event) {
            $event = $programSession->venue->eventDay->event;
            $venues = $event->eventDays()
                ->with(['venues' => function ($query) {
                    $query->ordered();
                }])
                ->get()
                ->flatMap->venues
                ->map(function ($venue) {
                    return [
                        'id' => $venue->id,
                        'name' => $venue->display_name ?? $venue->name,
                        'display_name' => $venue->display_name ?? $venue->name,
                    ];
                })
                ->unique('id')
                ->values();
        }

        // Get participants for moderators - unique participants only
        $participantsQuery = Participant::moderators();
        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $participantsQuery->whereIn('organization_id', $organizationIds);
        }
        $participants = $participantsQuery
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'title', 'affiliation'])
            ->unique(function ($participant) {
                // Aynı ad ve soyadı olan kişileri tekrarlatma
                return trim($participant->first_name . ' ' . $participant->last_name);
            })
            ->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'full_name' => trim($participant->first_name . ' ' . $participant->last_name),
                    'title' => $participant->title,
                    'affiliation' => $participant->affiliation,
                ];
            })
            ->values(); // Re-index

        // Get sponsors - unique sponsors only
        $sponsorsQuery = Sponsor::active();
        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $sponsorsQuery->whereIn('organization_id', $organizationIds);
        }
        $sponsors = $sponsorsQuery
            ->distinct('name') // Aynı isimli sponsor'ları tekrarlatma
            ->orderBy('name')
            ->get(['id', 'name'])
            ->unique('name') // Double check - name'e göre unique yap
            ->values(); // Re-index array

        // Get categories for this event - unique categories only
        $categories = [];
        if ($programSession->venue && $programSession->venue->eventDay && $programSession->venue->eventDay->event) {
            $categories = $programSession->venue->eventDay->event->programSessionCategories()
                ->orderBy('sort_order')
                ->get(['id', 'name', 'color'])
                ->unique('name') // Aynı isimli kategorileri tekrarlatma
                ->values(); // Re-index
        }

        // Session types
        $sessionTypes = [
            ['value' => 'presentation', 'label' => 'Sunum'],
            ['value' => 'panel', 'label' => 'Panel'],
            ['value' => 'workshop', 'label' => 'Atölye'],
            ['value' => 'break', 'label' => 'Ara'],
            ['value' => 'special', 'label' => 'Özel Oturum'],
        ];

        // Moderator titles
        $moderatorTitles = [
            ['value' => 'moderator', 'label' => 'Moderatör'],
            ['value' => 'chair', 'label' => 'Başkan'],
            ['value' => 'co-chair', 'label' => 'Eş Başkan'],
        ];

        // Add current session data with proper time formatting
        $formData = [
            'programSession' => [
                'id' => $programSession->id,
                'venue_id' => $programSession->venue_id,
                'title' => $programSession->title,
                'description' => $programSession->description,
                'start_time' => $startTime, // Düzgün formatlanmış saat
                'end_time' => $endTime, // Düzgün formatlanmış saat
                'formatted_time_range' => $startTime && $endTime ? "{$startTime} - {$endTime}" : '', // Güzel format
                'session_type' => $programSession->session_type,
                'session_type_display' => $programSession->session_type_display,
                'moderator_title' => $programSession->moderator_title,
                'sponsor_id' => $programSession->sponsor_id,
                'is_break' => (bool) $programSession->is_break, // Boolean'a çevir
                'moderator_ids' => $programSession->moderators()->pluck('participant_id')->toArray(),
                'category_ids' => $programSession->categories()->pluck('program_session_category_id')->toArray(),

                // Venue relationship with full data for breadcrumbs
                'venue' => [
                    'id' => $programSession->venue->id,
                    'name' => $programSession->venue->display_name ?? $programSession->venue->name,
                    'display_name' => $programSession->venue->display_name ?? $programSession->venue->name,
                    'event_day' => [
                        'id' => $programSession->venue->eventDay->id,
                        'title' => $programSession->venue->eventDay->title,
                        'display_name' => $programSession->venue->eventDay->display_name ?? $programSession->venue->eventDay->title,
                        'event' => [
                            'id' => $programSession->venue->eventDay->event->id,
                            'name' => $programSession->venue->eventDay->event->name,
                            'slug' => $programSession->venue->eventDay->event->slug,
                        ]
                    ]
                ],
                'can_edit' => auth()->user()?->can('update', $programSession) ?? false,
                'can_delete' => auth()->user()?->can('delete', $programSession) ?? false,
            ],

            // Form options - düzgün formatlanmış
            'venues' => $venues,
            'participants' => $participants,
            'sponsors' => $sponsors,
            'categories' => $categories,
            'sessionTypes' => $sessionTypes,
            'moderatorTitles' => $moderatorTitles,

            // Cascade seçimi için eklenen alanlar
            'events' => collect([$programSession->venue->eventDay->event])->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'formatted_date_range' => $event->formatted_date_range,
                ];
            }),
            'selectedEvent' => [
                'id' => $programSession->venue->eventDay->event->id,
                'name' => $programSession->venue->eventDay->event->name,
                'slug' => $programSession->venue->eventDay->event->slug,
            ],
            'eventDays' => collect([$programSession->venue->eventDay])->map(function ($day) {
                return [
                    'id' => $day->id,
                    'title' => $day->title,
                    'date' => $day->date,
                    'display_name' => $day->display_name ?? $day->title,
                ];
            }),
            'selectedEventDay' => [
                'id' => $programSession->venue->eventDay->id,
                'title' => $programSession->venue->eventDay->title,
                'date' => $programSession->venue->eventDay->date,
                'display_name' => $programSession->venue->eventDay->display_name ?? $programSession->venue->eventDay->title,
            ],
            'selectedEventId' => $programSession->venue->eventDay->event->id,
            'selectedEventDayId' => $programSession->venue->eventDay->id,
            'selectedVenueId' => $programSession->venue_id,
        ];

        // Debug backend data with counts
        \Log::info('🔍 ProgramSession Edit - Backend Data:', [
            'session_id' => $programSession->id,
            'title' => $programSession->title,
            'venues_count' => $venues->count(),
            'participants_count' => $participants->count(),
            'sponsors_count' => $sponsors->count(),
            'categories_count' => $categories->count(),
            'sponsors_list' => $sponsors->pluck('name')->toArray(), // Sponsor isimlerini logla
            'start_time_formatted' => $startTime,
            'end_time_formatted' => $endTime,
            'venue_id' => $programSession->venue_id,
            'moderator_ids' => $formData['programSession']['moderator_ids'],
            'category_ids' => $formData['programSession']['category_ids'],
        ]);

        return Inertia::render('Admin/ProgramSessions/Edit', $formData);
    }

    /**
     * Update the specified program session
     */
    public function update(UpdateProgramSessionRequest $request, ProgramSession $programSession)
    {
        $this->authorize('update', $programSession);

        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Update session
            $programSession->update($data);

            // Update moderators
            if (isset($data['moderator_ids'])) {
                $moderators = collect($data['moderator_ids'])->mapWithKeys(function ($id, $index) {
                    return [$id => ['sort_order' => $index + 1]];
                });
                $programSession->moderators()->sync($moderators);
            }

            // Update categories
            if (isset($data['category_ids'])) {
                $programSession->categories()->sync($data['category_ids']);
            }

            DB::commit();

            return redirect()
                ->route('admin.program-sessions.show', $programSession)
                ->with('success', 'Oturum başarıyla güncellendi.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Oturum güncellenirken bir hata oluştu: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified program session
     */
    public function destroy(ProgramSession $programSession)
    {
        $this->authorize('delete', $programSession);

        try {
            if (!$programSession->canBeDeleted()) {
                return back()->withErrors([
                    'error' => 'Sunumları olan oturum silinemez.'
                ]);
            }

            DB::beginTransaction();

            $sessionTitle = $programSession->title;
            $programSession->delete();

            DB::commit();

            return redirect()
                ->route('admin.program-sessions.index')
                ->with('success', "'{$sessionTitle}' oturumu başarıyla silindi.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Oturum silinirken bir hata oluştu.'
            ]);
        }
    }

    /**
     * Reorder sessions within a venue
     */
    public function reorder(Request $request, Venue $venue)
    {
        $this->authorize('update', $venue);

        $request->validate([
            'session_ids' => 'required|array',
            'session_ids.*' => 'exists:program_sessions,id',
        ]);

        try {
            ProgramSession::reorderByVenue($venue->id, $request->session_ids);

            return response()->json(['message' => 'Oturum sırası güncellendi.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Sıralama güncellenirken hata oluştu.'], 500);
        }
    }

    /**
     * Move session to different time slot
     */
    public function moveTimeSlot(Request $request, ProgramSession $programSession)
    {
        $this->authorize('update', $programSession);

        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        try {
            $moved = $programSession->moveToTimeSlot(
                $request->start_time,
                $request->end_time
            );

            if (!$moved) {
                return response()->json(['error' => 'Seçilen zaman diliminde çakışma var.'], 422);
            }

            return response()->json(['message' => 'Oturum zamanı güncellendi.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Zaman güncellenirken hata oluştu.'], 500);
        }
    }

    /**
     * Duplicate session
     */
    public function duplicate(ProgramSession $programSession)
    {
        $this->authorize('create', ProgramSession::class);
        $this->authorize('view', $programSession);

        try {
            $newSession = $programSession->duplicate([
                'title' => $programSession->title . ' (Kopya)',
                'start_time' => $programSession->end_time,
                'end_time' => $programSession->end_time->addMinutes($programSession->duration_in_minutes),
            ]);

            return redirect()
                ->route('admin.program-sessions.show', $newSession)
                ->with('success', 'Oturum başarıyla kopyalandı.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Oturum kopyalanırken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get filter options for index page
     */
    private function getFilterOptions($user)
    {
        $eventsQuery = Event::orderBy('start_date', 'desc');
        $sponsorsQuery = Sponsor::active()->orderBy('name');
        $categoriesQuery = ProgramSessionCategory::orderBy('name');

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $eventsQuery->whereIn('organization_id', $organizationIds);
            $sponsorsQuery->whereIn('organization_id', $organizationIds);
            $categoriesQuery->whereHas('event', function ($query) use ($organizationIds) {
                $query->whereIn('organization_id', $organizationIds);
            });
        }

        return [
            'events' => $eventsQuery->get(['id', 'name'])->map(function ($event) {
                return [
                    'value' => $event->id,
                    'label' => $event->name,
                ];
            }),
            'session_types' => collect(ProgramSession::getSessionTypes())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                ];
            })->values(),
            'sponsors' => $sponsorsQuery->get(['id', 'name'])->map(function ($sponsor) {
                return [
                    'value' => $sponsor->id,
                    'label' => $sponsor->name,
                ];
            }),
            'categories' => $categoriesQuery->get(['id', 'name'])->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->name,
                ];
            }),
        ];
    }

    /**
     * Display timeline view for program sessions
     */
    public function timeline(Request $request, $eventSlug = null): Response
    {
        $user = auth()->user();

        // Get specific event if slug provided
        $selectedEvent = null;
        if ($eventSlug) {
            $selectedEvent = Event::where('slug', $eventSlug)->firstOrFail();

            // Check user access to this event
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                if (!$organizationIds->contains($selectedEvent->organization_id)) {
                    abort(403, 'Bu etkinliğe erişim yetkiniz yok.');
                }
            }
        }

        // Get events accessible to user
        $eventsQuery = Event::with(['eventDays.venues']);

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $eventsQuery->whereIn('organization_id', $organizationIds);
        }

        $events = $eventsQuery->orderBy('start_date', 'desc')->get();

        // Get event days with venues
        $eventDaysQuery = \App\Models\EventDay::with(['venues', 'event']);

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $eventDaysQuery->whereHas('event', function ($q) use ($organizationIds) {
                $q->whereIn('organization_id', $organizationIds);
            });
        }

        // Filter by selected event
        if ($selectedEvent) {
            $eventDaysQuery->where('event_id', $selectedEvent->id);
        } elseif ($request->filled('event_id')) {
            $eventDaysQuery->where('event_id', $request->event_id);
        }

        $eventDays = $eventDaysQuery->orderBy('date')->get();

        // Get venues
        $venuesQuery = Venue::with(['eventDay.event']);

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $venuesQuery->whereHas('eventDay.event', function ($q) use ($organizationIds) {
                $q->whereIn('organization_id', $organizationIds);
            });
        }

        if ($selectedEvent) {
            $venuesQuery->whereHas('eventDay', function ($q) use ($selectedEvent) {
                $q->where('event_id', $selectedEvent->id);
            });
        } elseif ($request->filled('event_day_id')) {
            $venuesQuery->where('event_day_id', $request->event_day_id);
        }

        $venues = $venuesQuery->orderBy('sort_order')->get();

        // Get program sessions
        $sessionsQuery = ProgramSession::with([
            'venue.eventDay.event',
            'moderators',
            'presentations',
            'categories',
            'sponsor'
        ])->withCount(['presentations', 'moderators']);

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $sessionsQuery->whereHas('venue.eventDay.event', function ($q) use ($organizationIds) {
                $q->whereIn('organization_id', $organizationIds);
            });
        }

        // Filter by selected event
        if ($selectedEvent) {
            $sessionsQuery->whereHas('venue.eventDay', function ($q) use ($selectedEvent) {
                $q->where('event_id', $selectedEvent->id);
            });
        } elseif ($request->filled('event_day_id')) {
            $sessionsQuery->whereHas('venue', function ($q) use ($request) {
                $q->where('event_day_id', $request->event_day_id);
            });
        }

        // Filter by specific venues if requested
        if ($request->filled('venue_ids')) {
            $venueIds = is_array($request->venue_ids) ? $request->venue_ids : explode(',', $request->venue_ids);
            $sessionsQuery->whereIn('venue_id', $venueIds);
        }

        $sessions = $sessionsQuery->orderBy('start_time')->orderBy('sort_order')->get();

        // Get categories for filtering
        $categoriesQuery = ProgramSessionCategory::query();

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $categoriesQuery->whereIn('organization_id', $organizationIds);
        }

        $categories = $categoriesQuery->orderBy('name')->get();

        // Get participants for moderator selection
        $participantsQuery = Participant::query();

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $participantsQuery->whereIn('organization_id', $organizationIds);
        }

        $participants = $participantsQuery->orderBy('first_name')->orderBy('last_name')->get();

        // Breadcrumbs
        $breadcrumbs = [
            ['label' => 'Ana Sayfa', 'href' => route('admin.dashboard')],
            ['label' => 'Program Oturumları', 'href' => route('admin.program-sessions.index')],
        ];

        if ($selectedEvent) {
            $breadcrumbs[] = ['label' => $selectedEvent->name, 'href' => route('admin.events.show', $selectedEvent->slug)];
            $breadcrumbs[] = ['label' => 'Timeline', 'href' => null];
        } else {
            $breadcrumbs[] = ['label' => 'Timeline', 'href' => null];
        }

        return Inertia::render('Admin/ProgramSessions/Timeline', [
            'selectedEvent' => $selectedEvent,
            'events' => $events,
            'eventDays' => $eventDays,
            'venues' => $venues,
            'sessions' => $sessions,
            'categories' => $categories,
            'participants' => $participants,
            'breadcrumbs' => $breadcrumbs,
            'filters' => [
                'event_id' => $selectedEvent?->id ?? $request->event_id,
                'event_day_id' => $request->event_day_id,
                'venue_ids' => $request->venue_ids,
            ],
            'initialSelectedDay' => $request->event_day_id ?? $eventDays->first()?->id,
            'sessionTypes' => [
                'main' => 'Ana Oturum',
                'satellite' => 'Satellit Oturum',
                'oral_presentation' => 'Sözlü Sunum',
                'poster_presentation' => 'Poster Sunum',
                'special' => 'Özel Oturum',
                'workshop' => 'Çalıştay',
                'panel' => 'Panel',
                'break' => 'Ara',
                'lunch' => 'Öğle Arası',
                'social' => 'Sosyal Etkinlik'
            ]
        ]);
    }

    /**
     * Get for select dropdown - AJAX helper
     */
    public function getForSelect(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = ProgramSession::select(['id', 'title', 'venue_id', 'start_time', 'end_time'])
            ->with(['venue:id,display_name,event_day_id', 'venue.eventDay:id,title,event_id', 'venue.eventDay.event:id,name']);

        // Apply user access restrictions
        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $query->whereHas('venue.eventDay.event', function ($eventQuery) use ($organizationIds) {
                $eventQuery->whereIn('organization_id', $organizationIds);
            });
        }

        // Filter by event if provided
        if ($request->filled('event_id')) {
            $query->whereHas('venue.eventDay', function ($dayQuery) use ($request) {
                $dayQuery->where('event_id', $request->event_id);
            });
        }

        // Filter by venue if provided
        if ($request->filled('venue_id')) {
            $query->where('venue_id', $request->venue_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        $sessions = $query->orderBy('start_time')
            ->orderBy('title')
            ->limit(50)
            ->get()
            ->map(function ($session) {
                return [
                    'value' => $session->id,
                    'label' => $session->title,
                    'time_range' => $session->start_time && $session->end_time ?
                        $session->start_time->format('H:i') . ' - ' . $session->end_time->format('H:i') : '',
                    'venue_name' => $session->venue->display_name ?? $session->venue->name ?? '',
                    'event_name' => $session->venue->eventDay->event->name ?? ''
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * Update sort order for sessions
     */
    public function updateSortOrder(Request $request): JsonResponse
    {
        $request->validate([
            'session_ids' => 'required|array',
            'session_ids.*' => 'exists:program_sessions,id',
            'venue_id' => 'nullable|exists:venues,id'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->session_ids as $index => $sessionId) {
                $session = ProgramSession::findOrFail($sessionId);

                // Check authorization
                $this->authorize('update', $session);

                $session->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sıralama başarıyla güncellendi'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Sıralama güncellenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle session status (active/inactive)
     */
    public function toggleStatus(ProgramSession $programSession): JsonResponse
    {
        $this->authorize('update', $programSession);

        try {
            $newStatus = !$programSession->is_active;
            $programSession->update(['is_active' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => $newStatus ? 'Oturum aktifleştirildi' : 'Oturum pasifleştirildi',
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Durum değiştirilemedi: ' . $e->getMessage()
            ], 500);
        }
    }

    // *** YENİ EKLENEN SÜRÜKLE-BIRAK METODLARI ***

    /**
     * Move session to different venue
     */
    public function moveToVenue(Request $request, ProgramSession $programSession): JsonResponse
    {
        $this->authorize('update', $programSession);

        $validator = Validator::make($request->all(), [
            'venue_id' => 'required|exists:venues,id',
            'event_day_id' => 'nullable|exists:event_days,id',
            'maintain_time' => 'boolean',
            'new_position' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $venue = Venue::findOrFail($request->venue_id);
            $eventDayId = $request->event_day_id ?? $programSession->venue->event_day_id;

            // Check for conflicts
            if ($request->maintain_time && $programSession->start_time && $programSession->end_time) {
                $conflicts = $this->checkVenueTimeConflicts(
                    $venue->id,
                    $eventDayId,
                    $programSession->start_time->format('H:i'),
                    $programSession->end_time->format('H:i'),
                    $programSession->id
                );

                if (!empty($conflicts)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hedef salonda zaman çakışması var',
                        'conflicts' => $conflicts
                    ], 422);
                }
            }

            // Update session
            $programSession->update([
                'venue_id' => $venue->id,
            ]);

            // Update position if specified
            if ($request->has('new_position')) {
                $this->reorderSessionInVenue($venue->id, $eventDayId, $programSession->id, $request->new_position);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Oturum başarıyla taşındı',
                'session' => $programSession->fresh()->load(['venue', 'venue.eventDay'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Taşıma işlemi başarısız: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move session to different time
     */
    public function moveToTime(Request $request, ProgramSession $programSession): JsonResponse
    {
        $this->authorize('update', $programSession);

        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'event_day_id' => 'nullable|exists:event_days,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $eventDayId = $request->event_day_id ?? $programSession->venue->event_day_id;

            // Check for conflicts
            $conflicts = $this->checkVenueTimeConflicts(
                $programSession->venue_id,
                $eventDayId,
                $request->start_time,
                $request->end_time,
                $programSession->id
            );

            if (!empty($conflicts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yeni zaman diliminde çakışma var',
                    'conflicts' => $conflicts
                ], 422);
            }

            // Update session times
            $programSession->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Oturum zamanı başarıyla güncellendi',
                'session' => $programSession->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Zaman güncelleme başarısız: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reorder sessions
     */
    public function bulkReorder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'updates' => 'required|array',
            'updates.*.session_id' => 'required|exists:program_sessions,id',
            'updates.*.venue_id' => 'required|exists:venues,id',
            'updates.*.sort_order' => 'required|integer|min:1',
            'updates.*.start_time' => 'nullable|date_format:H:i',
            'updates.*.end_time' => 'nullable|date_format:H:i|after:updates.*.start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $conflicts = [];
            $updated = 0;

            foreach ($request->updates as $update) {
                $session = ProgramSession::findOrFail($update['session_id']);

                // Check authorization
                if (!auth()->user()->can('update', $session)) {
                    continue;
                }

                // Check for time conflicts if times are provided
                if (isset($update['start_time']) && isset($update['end_time'])) {
                    $sessionConflicts = $this->checkVenueTimeConflicts(
                        $update['venue_id'],
                        $session->venue->event_day_id,
                        $update['start_time'],
                        $update['end_time'],
                        $session->id
                    );

                    if (!empty($sessionConflicts)) {
                        $conflicts[] = [
                            'session_id' => $session->id,
                            'session_title' => $session->title,
                            'conflicts' => $sessionConflicts
                        ];
                        continue;
                    }
                }

                // Update session
                $updateData = [
                    'venue_id' => $update['venue_id'],
                    'sort_order' => $update['sort_order'],
                ];

                if (isset($update['start_time'])) $updateData['start_time'] = $update['start_time'];
                if (isset($update['end_time'])) $updateData['end_time'] = $update['end_time'];

                $session->update($updateData);
                $updated++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updated} oturum başarıyla güncellendi",
                'updated_count' => $updated,
                'conflicts' => $conflicts
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Toplu güncelleme başarısız: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check time conflicts for sessions
     */
    public function checkTimeConflicts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'venue_id' => 'required|exists:venues,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'exclude_session_id' => 'nullable|exists:program_sessions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $venue = Venue::findOrFail($request->venue_id);

            $conflicts = $this->checkVenueTimeConflicts(
                $request->venue_id,
                $venue->event_day_id,
                $request->start_time,
                $request->end_time,
                $request->exclude_session_id
            );

            return response()->json([
                'success' => true,
                'has_conflicts' => !empty($conflicts),
                'conflicts' => $conflicts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Çakışma kontrolü başarısız: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto schedule sessions
     */
    public function autoSchedule(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'strategy' => 'in:time_optimized,venue_optimized,speaker_optimized',
            'session_ids' => 'nullable|array',
            'session_ids.*' => 'exists:program_sessions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $event = Event::findOrFail($request->event_id);
            $strategy = $request->strategy ?? 'time_optimized';
            $sessionIds = $request->session_ids;

            // Get sessions to schedule
            $query = ProgramSession::whereHas('venue.eventDay', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            });

            if ($sessionIds) {
                $query->whereIn('id', $sessionIds);
            } else {
                // Only schedule sessions without proper time/venue assignment
                $query->where(function ($q) {
                    $q->whereNull('start_time')
                        ->orWhereNull('end_time');
                });
            }

            $sessionsToSchedule = $query->get();

            if ($sessionsToSchedule->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Planlanacak oturum bulunamadı'
                ]);
            }

            $scheduled = $this->performAutoScheduling($event, $sessionsToSchedule, $strategy);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$scheduled} oturum otomatik planlandı",
                'scheduled_count' => $scheduled
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Otomatik planlama başarısız: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get move options for a session
     */
    public function getMoveOptions(ProgramSession $programSession): JsonResponse
    {
        $this->authorize('view', $programSession);

        try {
            $event = $programSession->venue->eventDay->event;

            $options = [
                'available_venues' => $this->getAvailableVenues($event),
                'available_days' => $this->getAvailableDays($event),
                'available_time_slots' => $this->getAvailableTimeSlots(),
                'current_conflicts' => $this->getCurrentConflicts($programSession),
                'suggested_times' => $this->getSuggestedTimes($programSession)
            ];

            return response()->json([
                'success' => true,
                'options' => $options
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Seçenekler alınamadı: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate session move
     */
    public function validateMove(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:program_sessions,id',
            'target_venue_id' => 'required|exists:venues,id',
            'new_start_time' => 'nullable|date_format:H:i',
            'new_end_time' => 'nullable|date_format:H:i|after:new_start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $session = ProgramSession::findOrFail($request->session_id);

            // Check authorization
            if (!auth()->user()->can('update', $session)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Bu işlem için yetkiniz yok'
                ], 403);
            }

            $venue = Venue::findOrFail($request->target_venue_id);
            $startTime = $request->new_start_time ?? $session->start_time?->format('H:i');
            $endTime = $request->new_end_time ?? $session->end_time?->format('H:i');

            $isValid = true;
            $message = '';
            $conflicts = [];

            // Check time conflicts if times are provided
            if ($startTime && $endTime) {
                $timeConflicts = $this->checkVenueTimeConflicts(
                    $venue->id,
                    $venue->event_day_id,
                    $startTime,
                    $endTime,
                    $session->id
                );

                if (!empty($timeConflicts)) {
                    $isValid = false;
                    $message = 'Zaman çakışması var';
                    $conflicts = $timeConflicts;
                }
            }

            // Check venue capacity
            if ($venue->capacity > 0) {
                $currentSessionCount = ProgramSession::where('venue_id', $venue->id)
                    ->where('id', '!=', $session->id)
                    ->count();

                if ($currentSessionCount >= $venue->capacity) {
                    $isValid = false;
                    $message = 'Salon kapasitesi dolu';
                }
            }

            return response()->json([
                'valid' => $isValid,
                'message' => $message,
                'conflicts' => $conflicts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Doğrulama hatası: ' . $e->getMessage()
            ], 500);
        }
    }

    // *** HELPER METODLARI ***

    /**
     * Helper: Check venue time conflicts
     */
    private function checkVenueTimeConflicts(int $venueId, int $dayId, string $startTime, string $endTime, ?int $excludeSessionId = null): array
    {
        $query = ProgramSession::where('venue_id', $venueId)
            ->whereHas('venue', function ($q) use ($dayId) {
                $q->where('event_day_id', $dayId);
            })
            ->whereNotNull('start_time')
            ->whereNotNull('end_time');

        if ($excludeSessionId) {
            $query->where('id', '!=', $excludeSessionId);
        }

        $existingSessions = $query->get();
        $conflicts = [];

        foreach ($existingSessions as $session) {
            if ($this->timeRangesOverlap(
                $startTime,
                $endTime,
                $session->start_time->format('H:i'),
                $session->end_time->format('H:i')
            )) {
                $conflicts[] = [
                    'session_id' => $session->id,
                    'session_title' => $session->title,
                    'time_range' => $session->start_time->format('H:i') . ' - ' . $session->end_time->format('H:i'),
                    'overlap_minutes' => $this->calculateOverlapMinutes(
                        $startTime,
                        $endTime,
                        $session->start_time->format('H:i'),
                        $session->end_time->format('H:i')
                    )
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Helper: Check if time ranges overlap
     */
    private function timeRangesOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        $start1 = Carbon::createFromFormat('H:i', $start1);
        $end1 = Carbon::createFromFormat('H:i', $end1);
        $start2 = Carbon::createFromFormat('H:i', $start2);
        $end2 = Carbon::createFromFormat('H:i', $end2);

        return $start1 < $end2 && $end1 > $start2;
    }

    /**
     * Helper: Calculate overlap minutes
     */
    private function calculateOverlapMinutes(string $start1, string $end1, string $start2, string $end2): int
    {
        $start1 = Carbon::createFromFormat('H:i', $start1);
        $end1 = Carbon::createFromFormat('H:i', $end1);
        $start2 = Carbon::createFromFormat('H:i', $start2);
        $end2 = Carbon::createFromFormat('H:i', $end2);

        $overlapStart = $start1 > $start2 ? $start1 : $start2;
        $overlapEnd = $end1 < $end2 ? $end1 : $end2;

        return $overlapStart < $overlapEnd ? $overlapStart->diffInMinutes($overlapEnd) : 0;
    }

    /**
     * Helper: Reorder session in venue
     */
    private function reorderSessionInVenue(int $venueId, int $dayId, int $sessionId, int $newPosition): void
    {
        $sessions = ProgramSession::where('venue_id', $venueId)
            ->whereHas('venue', function ($q) use ($dayId) {
                $q->where('event_day_id', $dayId);
            })
            ->where('id', '!=', $sessionId)
            ->orderBy('sort_order')
            ->pluck('id')
            ->toArray();

        array_splice($sessions, $newPosition, 0, $sessionId);

        foreach ($sessions as $index => $id) {
            ProgramSession::where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Helper: Perform auto scheduling
     */
    private function performAutoScheduling(Event $event, $sessions, string $strategy): int
    {
        $scheduled = 0;
        $timeSlots = $this->generateTimeSlots();
        $venues = $event->eventDays->flatMap->venues;

        foreach ($sessions as $session) {
            foreach ($venues as $venue) {
                foreach ($timeSlots as $slot) {
                    $conflicts = $this->checkVenueTimeConflicts(
                        $venue->id,
                        $venue->event_day_id,
                        $slot['start'],
                        $slot['end']
                    );

                    if (empty($conflicts)) {
                        $session->update([
                            'venue_id' => $venue->id,
                            'start_time' => $slot['start'],
                            'end_time' => $slot['end'],
                        ]);
                        $scheduled++;
                        break 2;
                    }
                }
            }
        }

        return $scheduled;
    }

    /**
     * Helper: Generate time slots
     */
    private function generateTimeSlots(): array
    {
        $slots = [];
        $start = Carbon::createFromFormat('H:i', '09:00');
        $end = Carbon::createFromFormat('H:i', '17:00');

        while ($start < $end) {
            $slotEnd = $start->copy()->addHour();
            $slots[] = [
                'start' => $start->format('H:i'),
                'end' => $slotEnd->format('H:i')
            ];
            $start->addHour();
        }

        return $slots;
    }

    /**
     * Helper: Get available venues
     */
    private function getAvailableVenues(Event $event): array
    {
        return $event->eventDays->flatMap->venues->unique('id')->map(function ($venue) {
            return [
                'id' => $venue->id,
                'name' => $venue->display_name ?? $venue->name,
                'capacity' => $venue->capacity,
                'color' => $venue->color
            ];
        })->values()->toArray();
    }

    /**
     * Helper: Get available days
     */
    private function getAvailableDays(Event $event): array
    {
        return $event->eventDays->map(function ($day) {
            return [
                'id' => $day->id,
                'title' => $day->title,
                'date' => $day->date->toDateString()
            ];
        })->toArray();
    }

    /**
     * Helper: Get available time slots
     */
    private function getAvailableTimeSlots(): array
    {
        return $this->generateTimeSlots();
    }

    /**
     * Helper: Get current conflicts
     */
    private function getCurrentConflicts(ProgramSession $session): array
    {
        if (!$session->start_time || !$session->end_time || !$session->venue_id) {
            return [];
        }

        return $this->checkVenueTimeConflicts(
            $session->venue_id,
            $session->venue->event_day_id,
            $session->start_time->format('H:i'),
            $session->end_time->format('H:i'),
            $session->id
        );
    }

    /**
     * Helper: Get suggested times
     */
    private function getSuggestedTimes(ProgramSession $session): array
    {
        return $this->generateTimeSlots();
    }

    /**
     * Get form data for create/edit forms - LEGACY METHOD (Backward compatibility)
     */
    private function getFormData($user, $venueId = null)
    {
        // Get events accessible to user
        $eventsQuery = Event::with(['eventDays.venues']);

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $eventsQuery->whereIn('organization_id', $organizationIds);
        }

        $events = $eventsQuery->orderBy('start_date', 'desc')->get();

        // Get venues for selected venue's event if provided
        $venues = [];
        $selectedEvent = null;
        if ($venueId) {
            $venue = Venue::with('eventDay.event')->find($venueId);
            if ($venue) {
                $selectedEvent = $venue->eventDay->event;
                $venues = $selectedEvent->eventDays()
                    ->with(['venues' => function ($query) {
                        $query->ordered();
                    }])
                    ->ordered()
                    ->get();
            }
        }

        // Get participants for moderators
        $participantsQuery = Participant::moderators();

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $participantsQuery->whereIn('organization_id', $organizationIds);
        }

        $participants = $participantsQuery->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'title', 'affiliation']);

        // Get sponsors - Use actual database column 'logo'
        $sponsorsQuery = Sponsor::active();

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $sponsorsQuery->whereIn('organization_id', $organizationIds);
        }

        $sponsors = $sponsorsQuery->orderBy('name')->get(['id', 'name', 'logo'])->map(function ($sponsor) {
            return [
                'id' => $sponsor->id,
                'name' => $sponsor->name,
                'logo_url' => $sponsor->logo ? asset('storage/' . $sponsor->logo) : null,
            ];
        });

        // Get session categories for selected event
        $categories = [];
        if ($selectedEvent) {
            $categories = $selectedEvent->programSessionCategories()->ordered()->get(['id', 'name', 'color']);
        }

        return [
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'formatted_date_range' => $event->formatted_date_range,
                    'event_days' => $event->eventDays->map(function ($day) {
                        return [
                            'id' => $day->id,
                            'display_name' => $day->display_name,
                            'venues' => $day->venues->map(function ($venue) {
                                return [
                                    'id' => $venue->id,
                                    'display_name' => $venue->display_name,
                                    'color' => $venue->color,
                                ];
                            }),
                        ];
                    }),
                ];
            }),
            'venues' => $venues,
            'participants' => $participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'full_name' => $participant->full_name,
                    'affiliation' => $participant->affiliation,
                ];
            }),
            'sponsors' => $sponsors,
            'categories' => $categories,
            'session_types' => ProgramSession::getSessionTypes(),
            'moderator_titles' => ProgramSession::getDefaultModeratorTitles(),
            'selected_venue_id' => $venueId,
        ];
    }
}
