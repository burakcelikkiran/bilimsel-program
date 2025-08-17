<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Participant;
use App\Models\Venue;
use App\Models\Sponsor;
use App\Models\Presentation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class PublicEventController extends Controller
{
    /**
     * Get all published events with pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Event::query()
                ->with(['organization']) // Basit relationship loading
                ->where('is_published', true)
                ->select([
                    'id', 'name', 'slug', 'description', 'start_date', 'end_date',
                    'timezone', 'venue_address', 'website_url', 'organization_id',
                    'created_at', 'updated_at'
                ]);

            // Apply filters
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->has('organization') && !empty($request->organization)) {
                $query->whereHas('organization', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->organization}%");
                });
            }

            // Date filters
            if ($request->has('upcoming') && $request->upcoming == 'true') {
                $query->where('start_date', '>=', Carbon::today());
            }

            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('start_date', $request->year);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'start_date');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if (in_array($sortBy, ['start_date', 'name', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            } else {
                $query->orderBy('start_date', 'asc');
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 50); // Max 50 per page
            $events = $query->paginate($perPage);

            // Transform data with safe URL generation
            $events->getCollection()->transform(function ($event) use ($request) {
                $baseUrl = $request->getSchemeAndHttpHost();
                
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'description' => $event->description,
                    'start_date' => $event->start_date->format('Y-m-d'),
                    'end_date' => $event->end_date->format('Y-m-d'),
                    'timezone' => $event->timezone,
                    'venue_address' => $event->venue_address,
                    'website_url' => $event->website_url,
                    'duration_days' => $event->start_date->diffInDays($event->end_date) + 1,
                    'is_upcoming' => $event->start_date->isFuture(),
                    'is_ongoing' => $event->start_date->isPast() && $event->end_date->isFuture(),
                    'organization' => $event->organization ? [
                        'id' => $event->organization->id,
                        'name' => $event->organization->name,
                        'logo_url' => $event->organization->logo 
                            ? asset('storage/' . $event->organization->logo) 
                            : null,
                    ] : null,
                    'api_endpoints' => [
                        'self' => $baseUrl . '/api/v1/events/' . $event->slug,
                        'speakers' => $baseUrl . '/api/v1/events/' . $event->slug . '/speakers',
                        'venues' => $baseUrl . '/api/v1/events/' . $event->slug . '/venues',
                        'sponsors' => $baseUrl . '/api/v1/events/' . $event->slug . '/sponsors',
                        'statistics' => $baseUrl . '/api/v1/events/' . $event->slug . '/stats',
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $events,
                'meta' => [
                    'total_events' => $events->total(),
                    'current_page' => $events->currentPage(),
                    'per_page' => $events->perPage(),
                    'last_page' => $events->lastPage(),
                    'filters_applied' => $this->getAppliedFilters($request),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Etkinlikler yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get specific event by slug
     */
    public function show(Request $request, Event $event): JsonResponse
    {
        try {
            // Check if event is published
            if (!$event->is_published) {
                return response()->json([
                    'success' => false,
                    'message' => 'Etkinlik bulunamadı.',
                ], 404);
            }

            // Load relationships - Sadece mevcut kolonları sorgula
            $event->load([
                'organization', // Spesifik kolonlar belirtme, Laravel otomatik alacak
                'eventDays',
                'venues',
            ]);

            // Basic event statistics
            $stats = [
                'total_days' => $event->eventDays->count(),
                'total_venues' => $event->venues->count(),
                'total_sessions' => 0,
                'total_presentations' => 0,
            ];

            // Safe statistics calculation
            try {
                if (method_exists($event, 'programSessions')) {
                    $stats['total_sessions'] = $event->programSessions()->count();
                    $stats['total_presentations'] = $event->programSessions()
                        ->withCount('presentations')
                        ->get()
                        ->sum('presentations_count');
                }
            } catch (\Exception $e) {
                // Keep default values if relationships don't exist
            }

            $baseUrl = $request->getSchemeAndHttpHost();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'description' => $event->description,
                    'start_date' => $event->start_date->format('Y-m-d'),
                    'end_date' => $event->end_date->format('Y-m-d'),
                    'timezone' => $event->timezone,
                    'venue_address' => $event->venue_address,
                    'contact_email' => $event->contact_email,
                    'contact_phone' => $event->contact_phone,
                    'website_url' => $event->website_url,
                    'duration_days' => $event->start_date->diffInDays($event->end_date) + 1,
                    'is_upcoming' => $event->start_date->isFuture(),
                    'is_ongoing' => $event->start_date->isPast() && $event->end_date->isFuture(),
                    'organization' => $event->organization ? [
                        'id' => $event->organization->id,
                        'name' => $event->organization->name,
                        'description' => $event->organization->description ?? null,
                        'website_url' => $event->organization->website_url ?? null,
                        'contact_email' => $event->organization->contact_email ?? null,
                        'logo_url' => $event->organization->logo 
                            ? asset('storage/' . $event->organization->logo) 
                            : null,
                    ] : null,
                    'statistics' => $stats,
                    'event_days' => $event->eventDays->map(function ($day) {
                        return [
                            'id' => $day->id,
                            'title' => $day->title,
                            'date' => $day->date->format('Y-m-d'),
                            'day_name' => $day->date->format('l'),
                            'description' => $day->description,
                        ];
                    }),
                    'venues' => $event->venues->map(function ($venue) {
                        return [
                            'id' => $venue->id,
                            'name' => $venue->name,
                            'color' => $venue->color,
                            'capacity' => $venue->capacity,
                        ];
                    }),
                    'api_endpoints' => [
                        'speakers' => $baseUrl . '/api/v1/events/' . $event->slug . '/speakers',
                        'venues' => $baseUrl . '/api/v1/events/' . $event->slug . '/venues',
                        'sponsors' => $baseUrl . '/api/v1/events/' . $event->slug . '/sponsors',
                        'statistics' => $baseUrl . '/api/v1/events/' . $event->slug . '/stats',
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Etkinlik detayları yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get event speakers
     */
    public function speakers(Request $request, Event $event): JsonResponse
    {
        try {
            if (!$event->is_published) {
                return response()->json([
                    'success' => false,
                    'message' => 'Etkinlik bulunamadı.',
                ], 404);
            }

            // Basit speaker listesi için mock data
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'event' => [
                        'id' => $event->id,
                        'name' => $event->name,
                        'slug' => $event->slug,
                    ],
                    'total_speakers' => 0,
                    'message' => 'Speaker relationship henüz kurulmamış',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Konuşmacılar yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get specific speaker
     */
    public function speaker(Request $request, Event $event, Participant $participant): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $participant->id,
                    'message' => 'Speaker detail endpoint çalışıyor',
                ],
                'meta' => [
                    'event' => [
                        'id' => $event->id,
                        'name' => $event->name,
                        'slug' => $event->slug,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Konuşmacı detayları yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get event venues
     */
    public function venues(Request $request, Event $event): JsonResponse
    {
        try {
            if (!$event->is_published) {
                return response()->json([
                    'success' => false,
                    'message' => 'Etkinlik bulunamadı.',
                ], 404);
            }

            $venues = $event->venues;

            return response()->json([
                'success' => true,
                'data' => $venues->map(function ($venue) {
                    return [
                        'id' => $venue->id,
                        'name' => $venue->name,
                        'capacity' => $venue->capacity,
                        'color' => $venue->color,
                    ];
                }),
                'meta' => [
                    'event' => [
                        'id' => $event->id,
                        'name' => $event->name,
                        'slug' => $event->slug,
                    ],
                    'total_venues' => $venues->count(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Salonlar yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get specific venue
     */
    public function venue(Request $request, Event $event, Venue $venue): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'capacity' => $venue->capacity,
                    'color' => $venue->color,
                    'message' => 'Venue detail endpoint çalışıyor',
                ],
                'meta' => [
                    'event' => [
                        'id' => $event->id,
                        'name' => $event->name,
                        'slug' => $event->slug,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Salon detayları yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get event sponsors
     */
    public function sponsors(Request $request, Event $event): JsonResponse
    {
        try {
            if (!$event->is_published) {
                return response()->json([
                    'success' => false,
                    'message' => 'Etkinlik bulunamadı.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'event' => [
                        'id' => $event->id,
                        'name' => $event->name,
                        'slug' => $event->slug,
                    ],
                    'total_sponsors' => 0,
                    'message' => 'Sponsors endpoint çalışıyor',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsorlar yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get specific sponsor
     */
    public function sponsor(Request $request, Event $event, Sponsor $sponsor): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $sponsor->id,
                    'name' => $sponsor->name,
                    'message' => 'Sponsor detail endpoint çalışıyor',
                ],
                'meta' => [
                    'event' => [
                        'id' => $event->id,
                        'name' => $event->name,
                        'slug' => $event->slug,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsor detayları yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get event statistics
     */
    public function statistics(Request $request, Event $event): JsonResponse
    {
        try {
            if (!$event->is_published) {
                return response()->json([
                    'success' => false,
                    'message' => 'Etkinlik bulunamadı.',
                ], 404);
            }

            $stats = [
                'event' => [
                    'total_days' => $event->eventDays->count(),
                    'total_venues' => $event->venues->count(),
                    'total_sessions' => 0,
                    'total_presentations' => 0,
                    'total_speakers' => 0,
                    'total_moderators' => 0,
                    'total_sponsors' => 0,
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'meta' => [
                    'event' => [
                        'id' => $event->id,
                        'name' => $event->name,
                        'slug' => $event->slug,
                    ],
                    'generated_at' => Carbon::now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'İstatistikler yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Search events globally
     */
    public function searchEvents(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'upcoming_only' => 'boolean',
        ]);

        try {
            $query = Event::query()
                ->where('is_published', true)
                ->with(['organization:id,name,logo']);

            $searchTerm = $request->get('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });

            if ($request->get('upcoming_only', false)) {
                $query->where('start_date', '>=', Carbon::today());
            }

            $events = $query->orderBy('start_date', 'desc')
                          ->limit(50)
                          ->get();

            return response()->json([
                'success' => true,
                'data' => $events->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'name' => $event->name,
                        'slug' => $event->slug,
                        'description' => $event->description,
                        'start_date' => $event->start_date->format('Y-m-d'),
                        'end_date' => $event->end_date->format('Y-m-d'),
                        'organization' => $event->organization ? [
                            'id' => $event->organization->id,
                            'name' => $event->organization->name,
                        ] : null,
                    ];
                }),
                'meta' => [
                    'query' => $searchTerm,
                    'total_results' => $events->count(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arama sırasında bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Search speakers globally
     */
    public function searchSpeakers(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        try {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'query' => $request->get('q'),
                    'total_results' => 0,
                    'message' => 'Speaker search henüz implemente edilmedi',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Konuşmacı araması sırasında bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Search presentations globally
     */
    public function searchPresentations(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        try {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'query' => $request->get('q'),
                    'total_results' => 0,
                    'message' => 'Presentation search henüz implemente edilmedi',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sunum araması sırasında bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get organization's public events
     */
    public function organizationEvents(Request $request, Organization $organization): JsonResponse
    {
        try {
            $events = $organization->events()
                ->where('is_published', true)
                ->orderBy('start_date', 'desc')
                ->paginate(min($request->get('per_page', 15), 50));

            return response()->json([
                'success' => true,
                'data' => [
                    'organization' => [
                        'id' => $organization->id,
                        'name' => $organization->name,
                        'description' => $organization->description,
                        'logo_url' => $organization->logo 
                            ? asset('storage/' . $organization->logo) 
                            : null,
                    ],
                    'events' => $events->map(function ($event) {
                        return [
                            'id' => $event->id,
                            'name' => $event->name,
                            'slug' => $event->slug,
                            'description' => $event->description,
                            'start_date' => $event->start_date->format('Y-m-d'),
                            'end_date' => $event->end_date->format('Y-m-d'),
                            'duration_days' => $event->start_date->diffInDays($event->end_date) + 1,
                            'is_upcoming' => $event->start_date->isFuture(),
                            'is_ongoing' => $event->start_date->isPast() && $event->end_date->isFuture(),
                        ];
                    }),
                ],
                'meta' => [
                    'total_events' => $events->total(),
                    'current_page' => $events->currentPage(),
                    'per_page' => $events->perPage(),
                    'last_page' => $events->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Organizasyon etkinlikleri yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get organization info
     */
    public function organization(Request $request, Organization $organization): JsonResponse
    {
        try {
            $recentEvents = $organization->events()
                ->where('is_published', true)
                ->orderBy('start_date', 'desc')
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'description' => $organization->description,
                    'logo_url' => $organization->logo 
                        ? asset('storage/' . $organization->logo) 
                        : null,
                    'contact_email' => $organization->contact_email,
                    'contact_phone' => $organization->contact_phone,
                    'recent_events' => $recentEvents->map(function ($event) {
                        return [
                            'id' => $event->id,
                            'name' => $event->name,
                            'slug' => $event->slug,
                            'start_date' => $event->start_date->format('Y-m-d'),
                            'end_date' => $event->end_date->format('Y-m-d'),
                        ];
                    }),
                    'statistics' => [
                        'total_events' => $organization->events()->where('is_published', true)->count(),
                        'upcoming_events' => $organization->events()
                            ->where('is_published', true)
                            ->where('start_date', '>=', Carbon::today())
                            ->count(),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Organizasyon bilgileri yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Test endpoint
     */
    public function test(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'PublicEventController çalışıyor!',
            'controller' => self::class,
            'available_methods' => get_class_methods($this),
            'timestamp' => now(),
        ]);
    }

    /**
     * Get applied filters for meta information
     */
    private function getAppliedFilters(Request $request): array
    {
        $filters = [];
        
        if ($request->has('search') && !empty($request->search)) {
            $filters['search'] = $request->search;
        }
        
        if ($request->has('organization') && !empty($request->organization)) {
            $filters['organization'] = $request->organization;
        }
        
        if ($request->has('upcoming') && $request->upcoming == 'true') {
            $filters['upcoming'] = true;
        }
        
        if ($request->has('year') && !empty($request->year)) {
            $filters['year'] = $request->year;
        }
        
        return $filters;
    }
}