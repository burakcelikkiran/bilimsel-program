<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventDay;
use App\Models\Event;
use App\Models\Venue;
use App\Models\ProgramSession;
use App\Models\Presentation;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class EventDayController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display event days for specific event
     */
    public function index(Request $request, Event $event): Response
    {
        $this->authorize('view', $event);

        try {
            // Venues tablosunun yapısını kontrol et
            $hasEventDayId = Schema::hasColumn('venues', 'event_day_id');
            $hasOrganizationId = Schema::hasColumn('venues', 'organization_id');

            Log::info('Venues table structure:', [
                'has_event_day_id' => $hasEventDayId,
                'has_organization_id' => $hasOrganizationId
            ]);

            $eventDays = $event->eventDays()
                ->when($request->search, function ($query, $search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                ->orderBy('date')
                ->orderBy('sort_order')
                ->paginate($request->per_page ?? 15)
                ->withQueryString();

            // Manuel count hesaplamaları - VENUE YAPISI KONTROL EDİLECEK
            foreach ($eventDays as $eventDay) {
                try {
                    if ($hasEventDayId) {
                        // event_day_id kolonu varsa normal şekilde say
                        $eventDay->venues_count = Venue::where('event_day_id', $eventDay->id)
                            ->whereNull('deleted_at')
                            ->count();

                        $venueIds = Venue::where('event_day_id', $eventDay->id)
                            ->whereNull('deleted_at')
                            ->pluck('id');
                    } else {
                        // event_day_id kolonu yoksa, organization_id ile hesapla
                        Log::warning('event_day_id column not found, using organization structure');

                        $eventDay->venues_count = Venue::where('organization_id', $event->organization_id)
                            ->whereNull('deleted_at')
                            ->count();

                        $venueIds = Venue::where('organization_id', $event->organization_id)
                            ->whereNull('deleted_at')
                            ->pluck('id');
                    }

                    $eventDay->program_sessions_count = ProgramSession::whereIn('venue_id', $venueIds)
                        ->whereNull('deleted_at')
                        ->count();

                    // Presentations count - program sessions üzerinden
                    $sessionIds = ProgramSession::whereIn('venue_id', $venueIds)
                        ->whereNull('deleted_at')
                        ->pluck('id');

                    $eventDay->presentations_count = Presentation::whereIn('program_session_id', $sessionIds)
                        ->whereNull('deleted_at')
                        ->count();
                } catch (\Exception $e) {
                    Log::error('Error calculating counts for event day', [
                        'event_day_id' => $eventDay->id,
                        'error' => $e->getMessage()
                    ]);

                    // Hata durumunda default değerler
                    $eventDay->venues_count = 0;
                    $eventDay->program_sessions_count = 0;
                    $eventDay->presentations_count = 0;
                }
            }

            // Event için genel stats da güvenli şekilde hesaplayalım
            try {
                if ($hasEventDayId) {
                    $allEventDayIds = $event->eventDays()->pluck('id');
                    $allVenueIds = Venue::whereIn('event_day_id', $allEventDayIds)
                        ->whereNull('deleted_at')
                        ->pluck('id');
                } else {
                    // organization yapısını kullan
                    $allVenueIds = Venue::where('organization_id', $event->organization_id)
                        ->whereNull('deleted_at')
                        ->pluck('id');
                }

                $allSessionIds = ProgramSession::whereIn('venue_id', $allVenueIds)
                    ->whereNull('deleted_at')
                    ->pluck('id');

                $stats = [
                    'total_days' => $event->eventDays()->count(),
                    'total_venues' => $allVenueIds->count(),
                    'total_sessions' => $allSessionIds->count(),
                    'total_presentations' => Presentation::whereIn('program_session_id', $allSessionIds)
                        ->whereNull('deleted_at')
                        ->count(),
                ];
            } catch (\Exception $e) {
                Log::error('Error calculating event stats', ['error' => $e->getMessage()]);
                $stats = [
                    'total_days' => $event->eventDays()->count(),
                    'total_venues' => 0,
                    'total_sessions' => 0,
                    'total_presentations' => 0,
                ];
            }

            return Inertia::render('Admin/EventDays/Index', [
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                    'organization' => [
                        'id' => $event->organization->id,
                        'name' => $event->organization->name,
                    ]
                ],
                'eventDays' => $eventDays,
                'stats' => $stats,
                'filters' => $request->only(['search', 'per_page']),
            ]);
        } catch (\Exception $e) {
            Log::error('EventDayController index error', [
                'error' => $e->getMessage(),
                'event_id' => $event->id
            ]);

            // Hata durumunda boş data ile Inertia response dön
            return Inertia::render('Admin/EventDays/Index', [
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                    'organization' => [
                        'id' => $event->organization->id ?? null,
                        'name' => $event->organization->name ?? '',
                    ],
                ],
                'eventDays' => collect(),
                'stats' => [
                    'total_days' => 0,
                    'total_venues' => 0,
                    'total_sessions' => 0,
                    'total_presentations' => 0,
                ],
                'filters' => $request->only(['search', 'per_page']),
                'error' => 'Event günleri yüklenirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new event day
     */
    public function create(Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('Admin/EventDays/Create', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'organization' => [
                    'id' => $event->organization->id,
                    'name' => $event->organization->name,
                ]
            ],
        ]);
    }

    /**
     * Store a newly created event day
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'date' => [
                'required',
                'date',
                'after_or_equal:' . $event->start_date,
                'before_or_equal:' . $event->end_date,
                Rule::unique('event_days')->where('event_id', $event->id),
            ],
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['event_id'] = $event->id;
        $validated['is_active'] = $validated['is_active'] ?? true;

        try {
            DB::beginTransaction();

            $eventDay = EventDay::create($validated);

            DB::commit();

            return redirect()
                ->route('admin.events.days.index', $event)
                ->with('success', "Etkinlik günü '{$eventDay->display_name}' başarıyla oluşturuldu.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Etkinlik günü oluşturulurken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified event day
     */
    public function show(Event $event, EventDay $eventDay): Response
    {
        $this->authorize('view', $event);

        // EventDay'in bu etkinliğe ait olduğunu kontrol et
        if ($eventDay->event_id !== $event->id) {
            abort(404, 'Bu etkinlik günü bulunamadı.');
        }

        try {
            // Event organizasyon bilgisiyle birlikte yükle
            $event->load('organization');

            // EventDay ve ilişkili verileri yükle - güvenli şekilde
            try {
                $eventDay->load([
                    'venues' => function ($query) {
                        $query->orderBy('sort_order');
                    }
                ]);
            } catch (\Exception $e) {
                Log::error('Error loading eventDay relations: ' . $e->getMessage());
            }

            // Manuel count hesaplamaları
            try {
                $venuesCount = $eventDay->venues()->count();
                $venueIds = $eventDay->venues()->pluck('id')->toArray();
                $programSessionsCount = ProgramSession::whereIn('venue_id', $venueIds)->count();
            } catch (\Exception $e) {
                $venuesCount = 0;
                $programSessionsCount = 0;
            }

            // Program oturumlarını güvenli şekilde yükle
            $timeSlots = collect();
            try {
                $sessions = ProgramSession::whereIn('venue_id', $venueIds)
                    ->with(['venue', 'categories', 'presentations.speakers', 'moderators'])
                    ->orderBy('start_time')
                    ->orderBy('sort_order')
                    ->get();

                foreach ($sessions as $session) {
                    $timeKey = $session->start_time ? Carbon::parse($session->start_time)->format('H:i') : '00:00';

                    if (!$timeSlots->has($timeKey)) {
                        $timeSlots[$timeKey] = collect();
                    }

                    $timeSlots[$timeKey]->push([
                        'id' => $session->id,
                        'title' => $session->title,
                        'description' => $session->description,
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                        'session_type' => $session->session_type,
                        'moderator_title' => $session->moderator_title,
                        'is_break' => $session->is_break,
                        'venue' => [
                            'id' => $session->venue->id,
                            'name' => $session->venue->name,
                            'display_name' => $session->venue->display_name ?? $session->venue->name,
                            'capacity' => $session->venue->capacity,
                            'color' => $session->venue->color ?? '#3B82F6',
                        ],
                        'category' => $session->categories->first() ? [
                            'id' => $session->categories->first()->id,
                            'name' => $session->categories->first()->name,
                            'color' => $session->categories->first()->color,
                        ] : null,
                        'moderators' => $session->moderators->map(function ($moderator) {
                            return [
                                'id' => $moderator->id,
                                'name' => $moderator->first_name . ' ' . $moderator->last_name,
                                'title' => $moderator->title,
                                'affiliation' => $moderator->affiliation,
                            ];
                        }),
                        'presentations' => $session->presentations->map(function ($presentation) {
                            return [
                                'id' => $presentation->id,
                                'title' => $presentation->title,
                                'start_time' => $presentation->start_time,
                                'duration' => $presentation->duration,
                                'speakers' => $presentation->speakers->map(function ($speaker) {
                                    return [
                                        'id' => $speaker->id,
                                        'name' => $speaker->first_name . ' ' . $speaker->last_name,
                                        'title' => $speaker->title,
                                        'affiliation' => $speaker->affiliation,
                                    ];
                                })
                            ];
                        })
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error loading program sessions: ' . $e->getMessage());
            }

            // Zaman dilimlerini sırala ve obje formatına çevir
            $timeSlots = $timeSlots->sortKeys()->toArray();

            return Inertia::render('Admin/EventDays/Show', [
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                    'organization' => [
                        'id' => $event->organization->id,
                        'name' => $event->organization->name,
                    ]
                ],
                'eventDay' => [
                    'id' => $eventDay->id,
                    'display_name' => $eventDay->display_name,
                    'date' => $eventDay->date,
                    'is_active' => $eventDay->is_active,
                    'sort_order' => $eventDay->sort_order,
                    'venues_count' => $venuesCount,
                    'program_sessions_count' => $programSessionsCount,
                    'presentations_count' => 0, // Geçici olarak 0 set ediyoruz
                    'created_at' => $eventDay->created_at,
                    'updated_at' => $eventDay->updated_at,
                ],
                'timeSlots' => $timeSlots,
                'venues' => $eventDay->venues->map(function ($venue) {
                    return [
                        'id' => $venue->id,
                        'name' => $venue->name,
                        'display_name' => $venue->display_name ?? $venue->name,
                        'capacity' => $venue->capacity,
                        'color' => $venue->color ?? '#3B82F6',
                        'sort_order' => $venue->sort_order,
                        'program_sessions_count' => 0, // Geçici olarak 0
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('EventDay show error', [
                'event_day_id' => $eventDay->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'error' => 'Event günü detayları yüklenirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified event day
     */
    public function edit(Event $event, EventDay $eventDay): Response
    {
        $this->authorize('update', $event);

        // EventDay'in bu etkinliğe ait olduğunu kontrol et
        if ($eventDay->event_id !== $event->id) {
            abort(404, 'Bu etkinlik günü bulunamadı.');
        }

        try {
            // Event organizasyon bilgisiyle birlikte yükle
            $event->load('organization');

            // Manuel count hesaplamaları - güvenli şekilde
            try {
                $venuesCount = $eventDay->venues()->count();
            } catch (\Exception $e) {
                $venuesCount = 0;
            }

            try {
                $venueIds = $eventDay->venues()->pluck('id')->toArray();
                $programSessionsCount = ProgramSession::whereIn('venue_id', $venueIds)->count();
            } catch (\Exception $e) {
                $programSessionsCount = 0;
            }

            try {
                $sessionIds = ProgramSession::whereIn('venue_id', $venueIds)->pluck('id')->toArray();
                $presentationsCount = Presentation::whereIn('program_session_id', $sessionIds)->count();
            } catch (\Exception $e) {
                $presentationsCount = 0;
            }

            return Inertia::render('Admin/EventDays/Edit', [
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                    'organization' => [
                        'id' => $event->organization->id,
                        'name' => $event->organization->name,
                    ]
                ],
                'eventDay' => [
                    'id' => $eventDay->id,
                    'display_name' => $eventDay->display_name,
                    'date' => $eventDay->date,
                    'is_active' => $eventDay->is_active,
                    'sort_order' => $eventDay->sort_order,
                    'venues_count' => $venuesCount,
                    'program_sessions_count' => $programSessionsCount,
                    'presentations_count' => $presentationsCount,
                    'created_at' => $eventDay->created_at,
                    'updated_at' => $eventDay->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('EventDay edit form error', [
                'event_day_id' => $eventDay->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'error' => 'Düzenleme formu yüklenirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update the specified event day
     */
    public function update(Request $request, Event $event, EventDay $eventDay): RedirectResponse
    {
        $this->authorize('update', $event);

        // EventDay'in bu etkinliğe ait olduğunu kontrol et
        if ($eventDay->event_id !== $event->id) {
            abort(404, 'Bu etkinlik günü bulunamadı.');
        }

        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'date' => [
                'required',
                'date',
                'after_or_equal:' . $event->start_date,
                'before_or_equal:' . $event->end_date,
                Rule::unique('event_days')->where('event_id', $event->id)->ignore($eventDay->id),
            ],
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $eventDay->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.events.days.show', [$event->slug, $eventDay->id])
                ->with('success', "Etkinlik günü '{$eventDay->display_name}' başarıyla güncellendi.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Etkinlik günü güncellenirken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified event day
     */
    public function destroy(Event $event, EventDay $eventDay): RedirectResponse
    {
        $this->authorize('update', $event);

        // EventDay'in bu etkinliğe ait olduğunu kontrol et
        if ($eventDay->event_id !== $event->id) {
            abort(404, 'Bu etkinlik günü bulunamadı.');
        }

        // Check if there are any program sessions - güvenli şekilde
        try {
            $venueIds = $eventDay->venues()->pluck('id')->toArray();
            $hasPrograms = ProgramSession::whereIn('venue_id', $venueIds)->exists();

            if ($hasPrograms) {
                return back()->withErrors([
                    'error' => "'{$eventDay->title}' günü silinemez çünkü bu güne ait program oturumları bulunmaktadır."
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error checking program sessions: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'Program oturumları kontrol edilirken hata oluştu.'
            ]);
        }

        try {
            DB::beginTransaction();

            $eventDay->delete();

            DB::commit();

            return redirect()
                ->route('admin.events.days.index', $event->slug)
                ->with('success', "Etkinlik günü '{$eventDay->title}' başarıyla silindi.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Silme işlemi sırasında bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Duplicate the specified event day
     */
    public function duplicate(Event $event, EventDay $eventDay): RedirectResponse
    {
        $this->authorize('update', $event);

        // EventDay'in bu etkinliğe ait olduğunu kontrol et
        if ($eventDay->event_id !== $event->id) {
            abort(404, 'Bu etkinlik günü bulunamadı.');
        }

        try {
            DB::beginTransaction();

            $newEventDay = $eventDay->replicate();
            $newEventDay->display_name = $eventDay->display_name . ' (Kopya)';
            $newEventDay->date = $eventDay->date->addDay();
            $newEventDay->save();

            DB::commit();

            return redirect()
                ->route('admin.events.days.index', $event->slug)
                ->with('success', "Etkinlik günü '{$newEventDay->title}' başarıyla kopyalandı.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Etkinlik günü kopyalanırken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle event day status
     */
    public function toggleStatus(Event $event, EventDay $eventDay): RedirectResponse
    {
        $this->authorize('update', $event);

        // EventDay'in bu etkinliğe ait olduğunu kontrol et
        if ($eventDay->event_id !== $event->id) {
            abort(404, 'Bu etkinlik günü bulunamadı.');
        }

        try {
            $eventDay->update(['is_active' => !$eventDay->is_active]);

            $status = $eventDay->is_active ? 'aktif' : 'pasif';

            return back()->with('success', "Etkinlik günü başarıyla {$status} yapıldı.");
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Durum değiştirme sırasında bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update sort order for event days
     */
    public function updateSortOrder(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'event_days' => 'required|array',
            'event_days.*.id' => 'required|exists:event_days,id',
            'event_days.*.sort_order' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['event_days'] as $dayData) {
                EventDay::where('id', $dayData['id'])
                    ->where('event_id', $event->id)
                    ->update(['sort_order' => $dayData['sort_order']]);
            }

            DB::commit();

            return back()->with('success', 'Etkinlik günleri sıralaması güncellendi.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Sıralama güncellenirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate automatic event days from event date range
     */
    public function generateDays(Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        try {
            DB::beginTransaction();

            $startDate = $event->start_date;
            $endDate = $event->end_date;
            $totalDays = $startDate->diffInDays($endDate) + 1;

            // Get existing dates to avoid duplicates
            $existingDates = EventDay::where('event_id', $event->id)
                ->pluck('date')
                ->map(fn($date) => $date instanceof Carbon ? $date->toDateString() : $date)
                ->toArray();

            $eventDaysToCreate = [];
            $currentDate = $startDate->copy();

            for ($dayNumber = 1; $dayNumber <= $totalDays; $dayNumber++) {
                $dateString = $currentDate->toDateString();
                
                if (!in_array($dateString, $existingDates)) {
                    $eventDaysToCreate[] = [
                        'event_id' => $event->id,
                        'display_name' => $totalDays > 1 ? "{$dayNumber}. Gün" : $event->name,
                        'date' => $dateString,
                        'sort_order' => $dayNumber,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                $currentDate->addDay();
            }

            $created = count($eventDaysToCreate);
            
            if ($created > 0) {
                EventDay::insert($eventDaysToCreate);
            }

            DB::commit();

            if ($created > 0) {
                return back()->with('success', "{$created} adet etkinlik günü otomatik olarak oluşturuldu.");
            } else {
                return back()->with('info', 'Tüm günler zaten mevcut.');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Günler oluşturulurken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get event days for select options (AJAX)
     */
    public function getForSelect(Event $event)
    {
        $this->authorize('view', $event);

        $eventDays = $event->eventDays()
            ->where('is_active', true)
            ->orderBy('date')
            ->get(['id', 'title', 'date'])
            ->map(function ($day) {
                return [
                    'value' => $day->id,
                    'label' => $day->title . ' (' . $day->date->format('d.m.Y') . ')',
                    'date' => $day->date,
                ];
            });

        return response()->json($eventDays);
    }

    /**
     * Get day schedule overview
     */
    public function getSchedule(Event $event, EventDay $eventDay)
    {
        $this->authorize('view', $event);

        // EventDay'in bu etkinliğe ait olduğunu kontrol et
        if ($eventDay->event_id !== $event->id) {
            abort(404, 'Bu etkinlik günü bulunamadı.');
        }

        try {
            $venueIds = $eventDay->venues()->pluck('id')->toArray();
            $sessions = ProgramSession::whereIn('venue_id', $venueIds)
                ->with(['venue', 'categories', 'presentations.speakers'])
                ->orderBy('start_time')
                ->get()
                ->groupBy('venue_id');

            $venues = $eventDay->venues()
                ->whereIn('id', $sessions->keys())
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'sessions' => $sessions,
                'venues' => $venues,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting schedule: ' . $e->getMessage());
            return response()->json(['error' => 'Schedule yüklenirken hata oluştu.'], 500);
        }
    }

    /**
     * Export event day data
     */
    public function export(Event $event, EventDay $eventDay)
    {
        $this->authorize('view', $event);

        // EventDay'in bu etkinliğe ait olduğunu kontrol et
        if ($eventDay->event_id !== $event->id) {
            abort(404, 'Bu etkinlik günü bulunamadı.');
        }

        try {
            $venueIds = $eventDay->venues()->pluck('id')->toArray();
            $sessions = ProgramSession::whereIn('venue_id', $venueIds)
                ->with(['venue', 'categories', 'presentations.speakers', 'moderators'])
                ->orderBy('start_time')
                ->get();

            $data = $sessions->map(function ($session) {
                return [
                    'Salon' => $session->venue->display_name ?? $session->venue->name,
                    'Oturum' => $session->title,
                    'Başlangıç Saati' => $session->start_time,
                    'Bitiş Saati' => $session->end_time,
                    'Tür' => $session->session_type_display ?? $session->session_type,
                    'Moderatörler' => $session->moderators->pluck('full_name')->join(', '),
                    'Sunum Sayısı' => $session->presentations->count(),
                    'Konuşmacılar' => $session->presentations->flatMap->speakers->pluck('full_name')->unique()->join(', '),
                ];
            });

            $filename = "event-day-{$eventDay->id}-" . now()->format('Y-m-d-H-i') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');

                // Add BOM for UTF-8
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Add headers
                if ($data->isNotEmpty()) {
                    fputcsv($file, array_keys($data->first()));
                }

                // Add data
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error exporting event day: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Export işlemi sırasında hata oluştu.']);
        }
    }
}
