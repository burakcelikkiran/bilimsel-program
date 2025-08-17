<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\ProgramSession;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TimelineController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display timeline view for an event
     */
    public function show(Event $event): Response
    {
        $this->authorize('view', $event);

        // Load all event data with relationships for timeline
        $event->load([
            'eventDays' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('date')
                    ->orderBy('sort_order');
            },
            'eventDays.venues' => function ($query) {
                $query->orderBy('sort_order');
            },
            'eventDays.venues.programSessions' => function ($query) {
                $query->with([
                    'sponsor',
                    'moderators',
                    'categories', // Categories relationship'i eklendi
                    'presentations' => function ($q) {
                        $q->with(['speakers'])
                            ->orderBy('start_time')
                            ->orderBy('sort_order');
                    }
                ])
                    ->orderBy('start_time')
                    ->orderBy('sort_order');
            }
        ]);

        // Calculate timeline stats
        $stats = $this->calculateTimelineStats($event);

        // Format data for timeline component
        $timelineData = $this->formatTimelineData($event);

        return Inertia::render('Admin/Timeline/Index', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'timezone' => $event->timezone ?? 'Europe/Istanbul',
                'can_edit' => auth()->user()->can('update', $event),
            ],
            'timelineData' => $timelineData,
            'stats' => $stats,
            'filters' => $this->getTimelineFilters($event),
        ]);
    }

    /**
     * Show timeline editor view
     */
    public function edit(Event $event): Response
    {
        $this->authorize('update', $event);

        // Load relationships with categories
        $event->load([
            'eventDays.venues.programSessions.sponsor',
            'eventDays.venues.programSessions.moderators',
            'eventDays.venues.programSessions.categories',
            'eventDays.venues.programSessions.presentations.speakers'
        ]);

        $timelineData = $this->formatTimelineData($event);
        $editableData = $this->formatEditableData($event);

        return Inertia::render('Admin/Timeline/Edit', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'timezone' => $event->timezone ?? 'Europe/Istanbul',
            ],
            'timelineData' => $timelineData,
            'editableData' => $editableData,
            'availableVenues' => $this->getAvailableVenues($event),
            'availableCategories' => $this->getAvailableCategories($event),
        ]);
    }

    /**
     * Get timeline data via AJAX
     */
    public function getTimelineData(Request $request, Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        // Apply filters from request
        $filters = $request->only(['day_id', 'venue_id', 'category_id', 'session_type']);

        $timelineData = $this->formatTimelineData($event, $filters);

        return response()->json([
            'success' => true,
            'data' => $timelineData,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Export timeline data
     */
    public function export(Request $request, Event $event)
    {
        $this->authorize('view', $event);

        $format = $request->input('format', 'json');

        switch ($format) {
            case 'pdf':
                // PDF export için direkt download response döndür
                return redirect()->route('admin.export.events.program-pdf', $event);

            case 'excel':
                // Excel export için direkt download response döndür  
                return redirect()->route('admin.export.events.program-excel', $event);

            case 'json':
            default:
                $timelineData = $this->formatTimelineData($event);
                // JSON için dosyayı indirmek için response header'ları ayarla
                $fileName = Str::slug($event->name) . '_timeline_' . now()->format('Y-m-d') . '.json';

                return response()->json($timelineData, 200, [
                    'Content-Type' => 'application/json',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                ]);
        }
    }

    /**
     * Format timeline data for frontend
     */
    private function formatTimelineData(Event $event, array $filters = []): array
    {
        $timelineData = [];

        foreach ($event->eventDays as $eventDay) {
            // Apply day filter
            if (!empty($filters['day_id']) && $eventDay->id != $filters['day_id']) {
                continue;
            }

            $dayData = [
                'id' => $eventDay->id,
                'title' => $eventDay->title,
                'display_name' => $eventDay->title,
                'date' => $eventDay->date->toDateString(),
                'formatted_date' => $eventDay->date->locale('tr')->translatedFormat('d F Y l'),
                'is_active' => $eventDay->is_active,
                'venues' => [],
            ];

            foreach ($eventDay->venues as $venue) {
                // Apply venue filter
                if (!empty($filters['venue_id']) && $venue->id != $filters['venue_id']) {
                    continue;
                }

                $venueData = [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'display_name' => $venue->display_name ?? $venue->name,
                    'color' => $venue->color ?? '#3B82F6',
                    'capacity' => $venue->capacity,
                    'sessions' => [],
                ];

                foreach ($venue->programSessions as $session) {
                    // Apply session type filter
                    if (!empty($filters['session_type']) && $session->session_type != $filters['session_type']) {
                        continue;
                    }

                    // Apply category filter - categories relationship kullan
                    if (!empty($filters['category_id'])) {
                        $hasCategory = $session->categories->contains('id', $filters['category_id']);
                        if (!$hasCategory) {
                            continue;
                        }
                    }

                    $sessionData = [
                        'id' => $session->id,
                        'title' => $session->title,
                        'description' => $session->description,
                        'start_time' => $session->start_time?->format('H:i'),
                        'end_time' => $session->end_time?->format('H:i'),
                        'formatted_time_range' => $this->formatTimeRange($session),
                        'duration_in_minutes' => $this->calculateDuration($session),
                        'session_type' => $session->session_type,
                        'session_type_display' => $this->getSessionTypeDisplay($session->session_type),
                        'moderator_title' => $session->moderator_title,
                        'is_break' => $session->is_break,
                        'sort_order' => $session->sort_order,
                        
                        // Categories - multiple categories support
                        'categories' => $session->categories->map(function ($category) {
                            return [
                                'id' => $category->id,
                                'name' => $category->name,
                                'color' => $category->color ?? '#3B82F6',
                            ];
                        }),
                        
                        'sponsor' => $session->sponsor ? [
                            'id' => $session->sponsor->id,
                            'name' => $session->sponsor->name,
                            'logo_url' => $session->sponsor->logo_url,
                        ] : null,
                        
                        'moderators' => $session->moderators->map(function ($moderator) {
                            return [
                                'id' => $moderator->id,
                                'full_name' => $moderator->first_name . ' ' . $moderator->last_name,
                                'title' => $moderator->title,
                                'affiliation' => $moderator->affiliation,
                            ];
                        }),
                        
                        'presentations' => $session->presentations->map(function ($presentation) {
                            return [
                                'id' => $presentation->id,
                                'title' => $presentation->title,
                                'abstract' => $presentation->abstract,
                                'start_time' => $presentation->start_time,
                                'end_time' => $presentation->end_time,
                                'formatted_time_range' => $this->formatTimeRange($presentation),
                                'duration_minutes' => $this->calculateDuration($presentation),
                                'presentation_type' => $presentation->presentation_type,
                                'presentation_type_display' => $this->getPresentationTypeDisplay($presentation->presentation_type),
                                'sort_order' => $presentation->sort_order,
                                'speakers' => $presentation->speakers->map(function ($speaker) {
                                    return [
                                        'id' => $speaker->id,
                                        'full_name' => $speaker->first_name . ' ' . $speaker->last_name,
                                        'title' => $speaker->title,
                                        'affiliation' => $speaker->affiliation,
                                        'speaker_role' => $speaker->pivot->speaker_role ?? 'speaker',
                                    ];
                                }),
                            ];
                        }),
                        
                        'can_edit' => auth()->user()->can('update', $session),
                        'can_delete' => auth()->user()->can('delete', $session),
                    ];

                    $venueData['sessions'][] = $sessionData;
                }

                // Only add venue if it has sessions (after filtering)
                if (!empty($venueData['sessions']) || empty($filters)) {
                    $dayData['venues'][] = $venueData;
                }
            }

            // Only add day if it has venues with sessions (after filtering)
            if (!empty($dayData['venues']) || empty($filters)) {
                $timelineData[] = $dayData;
            }
        }

        return $timelineData;
    }

    /**
     * Calculate timeline statistics
     */
    private function calculateTimelineStats(Event $event): array
    {
        $totalSessions = 0;
        $totalPresentations = 0;
        $totalDuration = 0;
        $sessionTypes = [];
        $venueUtilization = [];

        foreach ($event->eventDays as $eventDay) {
            foreach ($eventDay->venues as $venue) {
                $venueSessions = $venue->programSessions->count();
                $venueUtilization[] = [
                    'venue_name' => $venue->display_name ?? $venue->name,
                    'sessions_count' => $venueSessions,
                ];

                foreach ($venue->programSessions as $session) {
                    $totalSessions++;
                    $totalPresentations += $session->presentations->count();
                    $totalDuration += $this->calculateDuration($session);

                    $type = $this->getSessionTypeDisplay($session->session_type);
                    $sessionTypes[$type] = ($sessionTypes[$type] ?? 0) + 1;
                }
            }
        }

        return [
            'total_days' => $event->eventDays->count(),
            'total_venues' => $event->eventDays->sum(fn($day) => $day->venues->count()),
            'total_sessions' => $totalSessions,
            'total_presentations' => $totalPresentations,
            'total_duration_minutes' => $totalDuration,
            'total_duration_hours' => round($totalDuration / 60, 1),
            'session_types' => $sessionTypes,
            'venue_utilization' => collect($venueUtilization)->sortByDesc('sessions_count')->values(),
            'average_sessions_per_day' => $event->eventDays->count() > 0 ? round($totalSessions / $event->eventDays->count(), 1) : 0,
        ];
    }

    /**
     * Get available filters for timeline
     */
    private function getTimelineFilters(Event $event): array
    {
        return [
            'days' => $event->eventDays->map(function ($day) {
                return [
                    'value' => $day->id,
                    'label' => $day->title,
                    'date' => $day->date->toDateString(),
                ];
            }),
            'venues' => $event->eventDays->flatMap->venues->unique('id')->map(function ($venue) {
                return [
                    'value' => $venue->id,
                    'label' => $venue->display_name ?? $venue->name,
                    'color' => $venue->color,
                ];
            }),
            'session_types' => [
                ['value' => 'plenary', 'label' => 'Genel Oturum'],
                ['value' => 'parallel', 'label' => 'Paralel Oturum'],
                ['value' => 'workshop', 'label' => 'Workshop'],
                ['value' => 'poster', 'label' => 'Poster'],
                ['value' => 'break', 'label' => 'Ara'],
                ['value' => 'lunch', 'label' => 'Öğle Arası'],
                ['value' => 'social', 'label' => 'Sosyal'],
                ['value' => 'main', 'label' => 'Ana Oturum'],
                ['value' => 'satellite', 'label' => 'Uydu Sempozyumu'],
                ['value' => 'oral_presentation', 'label' => 'Sözlü Bildiri'],
                ['value' => 'special', 'label' => 'Özel Oturum'],
            ],
            // Categories filter - tüm etkinlikteki kategorileri al
            'categories' => $this->getEventCategories($event),
        ];
    }

    /**
     * Get all categories used in the event
     */
    private function getEventCategories(Event $event): array
    {
        $categories = collect();
        
        foreach ($event->eventDays as $eventDay) {
            foreach ($eventDay->venues as $venue) {
                foreach ($venue->programSessions as $session) {
                    if ($session->relationLoaded('categories')) {
                        $categories = $categories->merge($session->categories);
                    }
                }
            }
        }
        
        return $categories->unique('id')->map(function ($category) {
            return [
                'value' => $category->id,
                'label' => $category->name,
                'color' => $category->color ?? '#3B82F6',
            ];
        })->values()->toArray();
    }

    /**
     * Format data for drag & drop editor
     */
    private function formatEditableData(Event $event): array
    {
        return [
            'conflicts' => $this->detectTimeConflicts($event),
            'available_time_slots' => $this->getAvailableTimeSlots($event),
            'venue_capacities' => $this->getVenueCapacities($event),
        ];
    }

    /**
     * Get available venues for event
     */
    private function getAvailableVenues(Event $event): array
    {
        return $event->eventDays
            ->flatMap->venues
            ->unique('id')
            ->map(function ($venue) {
                return [
                    'id' => $venue->id,
                    'name' => $venue->display_name ?? $venue->name,
                    'capacity' => $venue->capacity,
                    'color' => $venue->color,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get available categories for event sessions
     */
    private function getAvailableCategories(Event $event): array
    {
        return $this->getEventCategories($event);
    }

    /**
     * Detect time conflicts in schedule
     */
    private function detectTimeConflicts(Event $event): array
    {
        $conflicts = [];

        foreach ($event->eventDays as $eventDay) {
            foreach ($eventDay->venues as $venue) {
                $sessions = $venue->programSessions->sortBy('start_time');

                for ($i = 0; $i < $sessions->count() - 1; $i++) {
                    $current = $sessions->values()[$i];
                    $next = $sessions->values()[$i + 1];

                    if (
                        $current->end_time && $next->start_time &&
                        $current->end_time > $next->start_time
                    ) {
                        $conflicts[] = [
                            'type' => 'time_overlap',
                            'venue_id' => $venue->id,
                            'venue_name' => $venue->display_name ?? $venue->name,
                            'session1_id' => $current->id,
                            'session1_title' => $current->title,
                            'session2_id' => $next->id,
                            'session2_title' => $next->title,
                            'overlap_minutes' => $current->end_time->diffInMinutes($next->start_time),
                        ];
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * Get available time slots for scheduling
     */
    private function getAvailableTimeSlots(Event $event): array
    {
        $slots = [];
        $start = Carbon::createFromFormat('H:i', '08:00');
        $end = Carbon::createFromFormat('H:i', '18:00');

        while ($start < $end) {
            $slots[] = [
                'time' => $start->format('H:i'),
                'label' => $start->format('H:i'),
            ];
            $start->addMinutes(30);
        }

        return $slots;
    }

    /**
     * Get venue capacities and utilization
     */
    private function getVenueCapacities(Event $event): array
    {
        return $event->eventDays
            ->flatMap->venues
            ->unique('id')
            ->map(function ($venue) {
                return [
                    'venue_id' => $venue->id,
                    'name' => $venue->display_name ?? $venue->name,
                    'capacity' => $venue->capacity,
                    'sessions_count' => $venue->programSessions->count(),
                    'utilization_percentage' => $venue->capacity > 0
                        ? round(($venue->programSessions->count() / $venue->capacity) * 100, 1)
                        : 0,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Helper method to format time range
     */
    private function formatTimeRange($model): string
    {
        if (!$model->start_time || !$model->end_time) {
            return '';
        }

        $start = $model->start_time instanceof Carbon ? $model->start_time : Carbon::parse($model->start_time);
        $end = $model->end_time instanceof Carbon ? $model->end_time : Carbon::parse($model->end_time);

        return $start->format('H:i') . ' - ' . $end->format('H:i');
    }

    /**
     * Helper method to calculate duration
     */
    private function calculateDuration($model): int
    {
        if (!$model->start_time || !$model->end_time) {
            return 0;
        }

        $start = $model->start_time instanceof Carbon ? $model->start_time : Carbon::parse($model->start_time);
        $end = $model->end_time instanceof Carbon ? $model->end_time : Carbon::parse($model->end_time);

        return $start->diffInMinutes($end);
    }

    /**
     * Helper method to get session type display
     */
    private function getSessionTypeDisplay($type): string
    {
        $types = [
            'plenary' => 'Genel Oturum',
            'parallel' => 'Paralel Oturum',
            'workshop' => 'Workshop',
            'poster' => 'Poster',
            'break' => 'Ara',
            'lunch' => 'Öğle Arası',
            'social' => 'Sosyal',
            'main' => 'Ana Oturum',
            'satellite' => 'Uydu Sempozyumu',
            'oral_presentation' => 'Sözlü Bildiri',
            'special' => 'Özel Oturum',
        ];

        return $types[$type] ?? ucfirst($type);
    }

    /**
     * Helper method to get presentation type display
     */
    private function getPresentationTypeDisplay($type): string
    {
        $types = [
            'oral' => 'Sözlü Sunum',
            'poster' => 'Poster',
            'keynote' => 'Açılış Konuşması',
            'panel' => 'Panel',
        ];

        return $types[$type] ?? ucfirst($type);
    }

    /**
     * Update session order and positions via drag & drop
     */
    public function updateOrder(Request $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $validator = Validator::make($request->all(), [
            'changes' => 'required|array',
            'changes.*.type' => 'required|in:session_moved,session_reordered',
            'changes.*.session_id' => 'required|exists:program_sessions,id',
            'timeline_data' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->changes as $change) {
                $this->processChange($change, $event);
            }

            // Recalculate and return updated timeline data
            $updatedTimelineData = $this->formatTimelineData($event);
            $updatedStats = $this->calculateTimelineStats($event);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Timeline başarıyla güncellendi',
                'data' => [
                    'timeline' => $updatedTimelineData,
                    'stats' => $updatedStats
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Timeline güncellenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move session to different venue or time slot
     */
    public function moveSession(Request $request, Event $event, ProgramSession $session): JsonResponse
    {
        $this->authorize('update', $session);

        $validator = Validator::make($request->all(), [
            'target_venue_id' => 'required|exists:venues,id',
            'target_day_id' => 'required|exists:event_days,id',
            'new_position' => 'nullable|integer|min:0',
            'new_start_time' => 'nullable|date_format:H:i',
            'new_end_time' => 'nullable|date_format:H:i|after:new_start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $targetVenue = Venue::findOrFail($request->target_venue_id);
            $targetDay = EventDay::findOrFail($request->target_day_id);

            // Validate that venue belongs to the event
            if (!$targetVenue->eventDays()->where('event_days.id', $targetDay->id)->exists()) {
                throw new \Exception('Hedef salon bu etkinlik gününe ait değil');
            }

            // Check for time conflicts if new time is provided
            if ($request->new_start_time && $request->new_end_time) {
                $conflictExists = $this->checkTimeConflict(
                    $targetVenue->id,
                    $targetDay->id,
                    $request->new_start_time,
                    $request->new_end_time,
                    $session->id
                );

                if ($conflictExists) {
                    throw new \Exception('Hedef zaman diliminde çakışma var');
                }
            }

            // Update session
            $session->update([
                'venue_id' => $targetVenue->id,
                'event_day_id' => $targetDay->id,
                'start_time' => $request->new_start_time ?? $session->start_time,
                'end_time' => $request->new_end_time ?? $session->end_time,
            ]);

            // Reorder sessions in target venue if position is specified
            if ($request->has('new_position')) {
                $this->reorderSessionsInVenue($targetVenue->id, $targetDay->id, $session->id, $request->new_position);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Oturum başarıyla taşındı',
                'session' => $session->fresh()->load(['venue', 'eventDay', 'moderators'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder sessions within a venue
     */
    public function reorderVenueSessions(Request $request, Event $event, Venue $venue): JsonResponse
    {
        $this->authorize('update', $venue);

        $validator = Validator::make($request->all(), [
            'session_ids' => 'required|array',
            'session_ids.*' => 'exists:program_sessions,id',
            'day_id' => 'required|exists:event_days,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $dayId = $request->day_id;
            $sessionIds = $request->session_ids;

            // Validate that all sessions belong to this venue and day
            $venueSessionIds = ProgramSession::where('venue_id', $venue->id)
                ->where('event_day_id', $dayId)
                ->pluck('id')
                ->toArray();

            if (array_diff($sessionIds, $venueSessionIds)) {
                throw new \Exception('Bazı oturumlar bu salon ve güne ait değil');
            }

            // Update sort order
            foreach ($sessionIds as $index => $sessionId) {
                ProgramSession::where('id', $sessionId)->update([
                    'sort_order' => $index + 1
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Oturum sırası güncellendi'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate session move operation
     */
    public function validateMove(Request $request, Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:program_sessions,id',
            'target_venue_id' => 'required|exists:venues,id',
            'target_day_id' => 'required|exists:event_days,id',
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
            $targetVenue = Venue::findOrFail($request->target_venue_id);
            $targetDay = EventDay::findOrFail($request->target_day_id);

            $validation = $this->validateSessionMove($session, $targetVenue, $targetDay, $request->new_start_time, $request->new_end_time);

            return response()->json([
                'valid' => $validation['valid'],
                'message' => $validation['message'],
                'conflicts' => $validation['conflicts'] ?? []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a timeline change
     */
    private function processChange(array $change, Event $event): void
    {
        switch ($change['type']) {
            case 'session_moved':
                $this->processSessionMove($change, $event);
                break;
            case 'session_reordered':
                $this->processSessionReorder($change, $event);
                break;
            default:
                throw new \Exception('Bilinmeyen değişiklik tipi: ' . $change['type']);
        }
    }

    /**
     * Process session move change
     */
    private function processSessionMove(array $change, Event $event): void
    {
        $session = ProgramSession::findOrFail($change['session_id']);

        // Validate session belongs to event through venue->eventDay->event
        $sessionEvent = $session->venue?->eventDay?->event;
        if (!$sessionEvent || $sessionEvent->id !== $event->id) {
            throw new \Exception('Oturum bu etkinliğe ait değil');
        }

        $fromVenue = Venue::findOrFail($change['from_venue_id']);
        $toVenue = Venue::findOrFail($change['to_venue_id']);
        $fromDay = EventDay::findOrFail($change['from_day_id']);
        $toDay = EventDay::findOrFail($change['to_day_id']);

        // Update session venue and day
        $updateData = [
            'venue_id' => $toVenue->id,
        ];

        // Update time if provided
        if (isset($change['new_time'])) {
            $updateData['start_time'] = $change['new_time']['start_time'];
            $updateData['end_time'] = $change['new_time']['end_time'];
        }

        $session->update($updateData);

        // Update sort order in target venue
        if (isset($change['new_position'])) {
            $this->reorderSessionsInVenue($toVenue->id, $toDay->id, $session->id, $change['new_position']);
        }
    }

    /**
     * Process session reorder change
     */
    private function processSessionReorder(array $change, Event $event): void
    {
        if (!isset($change['venue_id']) || !isset($change['day_id']) || !isset($change['session_order'])) {
            throw new \Exception('Sıralama için gerekli veriler eksik');
        }

        $venue = Venue::findOrFail($change['venue_id']);
        $day = EventDay::findOrFail($change['day_id']);

        foreach ($change['session_order'] as $index => $sessionId) {
            ProgramSession::where('id', $sessionId)
                ->where('venue_id', $venue->id)
                ->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Check for time conflicts
     */
    private function checkTimeConflict(int $venueId, int $dayId, string $startTime, string $endTime, ?int $excludeSessionId = null): bool
    {
        $query = ProgramSession::where('venue_id', $venueId)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time');

        if ($excludeSessionId) {
            $query->where('id', '!=', $excludeSessionId);
        }

        $existingSessions = $query->get();

        foreach ($existingSessions as $session) {
            if ($this->timeRangesOverlap(
                $startTime,
                $endTime,
                $session->start_time->format('H:i'),
                $session->end_time->format('H:i')
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if two time ranges overlap
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
     * Validate session move operation
     */
    private function validateSessionMove(ProgramSession $session, Venue $targetVenue, EventDay $targetDay, ?string $newStartTime = null, ?string $newEndTime = null): array
    {
        $conflicts = [];
        $valid = true;
        $message = '';

        // Use provided times or session's current times
        $startTime = $newStartTime ?? $session->start_time->format('H:i');
        $endTime = $newEndTime ?? $session->end_time->format('H:i');

        // Check venue capacity (if applicable)
        if ($targetVenue->capacity > 0) {
            $currentSessionCount = ProgramSession::where('venue_id', $targetVenue->id)
                ->where('id', '!=', $session->id)
                ->count();

            if ($currentSessionCount >= $targetVenue->capacity) {
                $valid = false;
                $message = 'Hedef salon kapasitesi dolu';
            }
        }

        // Check time conflicts
        if ($valid && $startTime && $endTime) {
            $conflictingSessions = ProgramSession::where('venue_id', $targetVenue->id)
                ->where('id', '!=', $session->id)
                ->whereNotNull('start_time')
                ->whereNotNull('end_time')
                ->get()
                ->filter(function ($existingSession) use ($startTime, $endTime) {
                    return $this->timeRangesOverlap(
                        $startTime,
                        $endTime,
                        $existingSession->start_time->format('H:i'),
                        $existingSession->end_time->format('H:i')
                    );
                });

            if ($conflictingSessions->isNotEmpty()) {
                $valid = false;
                $message = 'Zaman çakışması var';
                $conflicts = $conflictingSessions->map(function ($conflictingSession) {
                    return [
                        'session_id' => $conflictingSession->id,
                        'session_title' => $conflictingSession->title,
                        'time_range' => $this->formatTimeRange($conflictingSession),
                    ];
                })->toArray();
            }
        }

        return [
            'valid' => $valid,
            'message' => $message,
            'conflicts' => $conflicts
        ];
    }

    /**
     * Reorder sessions within a venue and day
     */
    private function reorderSessionsInVenue(int $venueId, int $dayId, int $sessionId, int $newPosition): void
    {
        // Get all sessions in this venue except the moving session
        $sessions = ProgramSession::where('venue_id', $venueId)
            ->where('id', '!=', $sessionId)
            ->orderBy('sort_order')
            ->pluck('id')
            ->toArray();

        // Insert the moving session at the new position
        array_splice($sessions, $newPosition, 0, $sessionId);

        // Update sort orders
        foreach ($sessions as $index => $id) {
            ProgramSession::where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Show help page
     */
    public function help(): Response
    {
        return Inertia::render('Admin/Timeline/Help', [
            'helpSections' => [
                [
                    'title' => 'Timeline Nasıl Kullanılır',
                    'content' => 'Timeline sayfası etkinlik programınızı kronolojik olarak görüntülemenizi sağlar.',
                ],
                [
                    'title' => 'Temel Özellikler',
                    'content' => 'Oturumları sürükle-bırak ile düzenleyebilir, zaman çakışmalarını kontrol edebilirsiniz.',
                ],
                [
                    'title' => 'Kısayollar',
                    'content' => 'Ctrl+S: Kaydet, Ctrl+Z: Geri al, Ctrl+Y: İleri al',
                ],
                [
                    'title' => 'Filtreleme',
                    'content' => 'Sol menüden gün, salon veya oturum türüne göre filtreleme yapabilirsiniz.',
                ],
                [
                    'title' => 'Export İşlemleri',
                    'content' => 'Timeline verilerini PDF, Excel veya JSON formatında dışa aktarabilirsiniz.',
                ],
            ],
        ]);
    }
}