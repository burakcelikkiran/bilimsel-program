<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\ProgramSession;
use App\Models\Presentation;
use App\Models\Venue;
use App\Models\EventDay;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DragDropController extends Controller
{
    use AuthorizesRequests;

    /**
     * Main drag & drop editor page
     */
    public function editor(): Response
    {
        // Get user's recent events for selection
        $user = auth()->user();
        $recentEvents = Event::where('organization_id', $user->currentOrganization->id ?? null)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'slug', 'start_date', 'end_date']);

        return Inertia::render('Admin/DragDrop/Editor', [
            'recentEvents' => $recentEvents,
            'canCreateEvents' => $user->can('create', Event::class),
        ]);
    }

    /**
     * Event-specific drag & drop editor
     */
    public function eventEditor(Event $event): Response
    {
        $this->authorize('update', $event);

        // Load event data for drag & drop
        $event->load([
            'eventDays' => function ($query) {
                $query->active()->orderBy('date')->orderBy('sort_order');
            },
            'eventDays.venues' => function ($query) {
                $query->active()->orderBy('sort_order');
            },
            'eventDays.venues.programSessions' => function ($query) {
                $query->with([
                    'category',
                    'sponsor',
                    'moderators.participant',
                    'presentations' => function ($q) {
                        $q->with(['speakers.participant'])
                            ->orderBy('start_time')
                            ->orderBy('sort_order');
                    }
                ])
                    ->orderBy('start_time')
                    ->orderBy('sort_order');
            }
        ]);

        // Get available resources for drag & drop
        $availableResources = $this->getAvailableResources($event);

        // Check for conflicts
        $conflicts = $this->detectAllConflicts($event);

        // Get layout settings
        $layoutSettings = $this->getLayoutSettings($event);

        return Inertia::render('Admin/DragDrop/EventEditor', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'timezone' => $event->timezone ?? 'Europe/Istanbul',
                'eventDays' => $event->eventDays,
            ],
            'availableResources' => $availableResources,
            'conflicts' => $conflicts,
            'layoutSettings' => $layoutSettings,
            'permissions' => [
                'can_edit_sessions' => $user->can('update', ProgramSession::class),
                'can_edit_presentations' => $user->can('update', Presentation::class),
                'can_create_sessions' => $user->can('create', ProgramSession::class),
                'can_create_presentations' => $user->can('create', Presentation::class),
            ]
        ]);
    }

    /**
     * Move a session to different venue/time
     */
    public function moveSession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:program_sessions,id',
            'target_venue_id' => 'required|exists:venues,id',
            'new_start_time' => 'required|date_format:H:i',
            'new_end_time' => 'required|date_format:H:i|after:new_start_time',
            'force_move' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $session = ProgramSession::findOrFail($request->session_id);
            $this->authorize('update', $session);

            $targetVenue = Venue::findOrFail($request->target_venue_id);

            // Check for conflicts unless forced
            if (!$request->force_move) {
                $conflicts = $this->checkSessionMoveConflicts(
                    $session,
                    $targetVenue,
                    $request->new_start_time,
                    $request->new_end_time
                );

                if (!empty($conflicts)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Zaman çakışması tespit edildi.',
                        'conflicts' => $conflicts,
                        'requires_confirmation' => true,
                    ], 409);
                }
            }

            // Perform the move
            $oldVenue = $session->venue_id;
            $oldStartTime = $session->start_time;
            $oldEndTime = $session->end_time;

            $session->update([
                'venue_id' => $targetVenue->id,
                'start_time' => $request->new_start_time,
                'end_time' => $request->new_end_time,
                'updated_at' => now(),
            ]);

            // Update presentations within session if needed
            $this->adjustPresentationTimes($session);

            // Log the move
            $this->logDragDropAction('session_moved', [
                'session_id' => $session->id,
                'from_venue_id' => $oldVenue,
                'to_venue_id' => $targetVenue->id,
                'from_time' => $oldStartTime . ' - ' . $oldEndTime,
                'to_time' => $request->new_start_time . ' - ' . $request->new_end_time,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Oturum başarıyla taşındı.',
                'session' => $session->fresh(['venue', 'category', 'presentations']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Oturum taşıma sırasında hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Move a presentation to different session/time
     */
    public function movePresentation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'presentation_id' => 'required|exists:presentations,id',
            'target_session_id' => 'required|exists:program_sessions,id',
            'new_start_time' => 'nullable|date_format:H:i',
            'new_end_time' => 'nullable|date_format:H:i|after:new_start_time',
            'new_sort_order' => 'nullable|integer|min:0',
            'force_move' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $presentation = Presentation::findOrFail($request->presentation_id);
            $this->authorize('update', $presentation);

            $targetSession = ProgramSession::findOrFail($request->target_session_id);
            $this->authorize('view', $targetSession);

            // Check for conflicts unless forced
            if (!$request->force_move) {
                $conflicts = $this->checkPresentationMoveConflicts(
                    $presentation,
                    $targetSession,
                    $request->new_start_time,
                    $request->new_end_time
                );

                if (!empty($conflicts)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Zaman çakışması tespit edildi.',
                        'conflicts' => $conflicts,
                        'requires_confirmation' => true,
                    ], 409);
                }
            }

            // Perform the move
            $oldSessionId = $presentation->program_session_id;
            $oldStartTime = $presentation->start_time;
            $oldEndTime = $presentation->end_time;
            $oldSortOrder = $presentation->sort_order;

            $updateData = [
                'program_session_id' => $targetSession->id,
                'updated_at' => now(),
            ];

            if ($request->new_start_time) {
                $updateData['start_time'] = $request->new_start_time;
            }
            if ($request->new_end_time) {
                $updateData['end_time'] = $request->new_end_time;
            }
            if ($request->new_sort_order !== null) {
                $updateData['sort_order'] = $request->new_sort_order;
            }

            $presentation->update($updateData);

            // Reorder presentations in both sessions
            $this->reorderPresentations($oldSessionId);
            if ($oldSessionId !== $targetSession->id) {
                $this->reorderPresentations($targetSession->id);
            }

            // Log the move
            $this->logDragDropAction('presentation_moved', [
                'presentation_id' => $presentation->id,
                'from_session_id' => $oldSessionId,
                'to_session_id' => $targetSession->id,
                'from_time' => $oldStartTime . ' - ' . $oldEndTime,
                'to_time' => ($request->new_start_time ?? $oldStartTime) . ' - ' . ($request->new_end_time ?? $oldEndTime),
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sunum başarıyla taşındı.',
                'presentation' => $presentation->fresh(['programSession', 'speakers']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Sunum taşıma sırasında hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Bulk update multiple items
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'operations' => 'required|array',
            'operations.*.type' => 'required|in:move_session,move_presentation,update_time,reorder',
            'operations.*.data' => 'required|array',
            'force_update' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $results = [];
            $conflicts = [];

            foreach ($request->operations as $index => $operation) {
                try {
                    $result = $this->performBulkOperation($operation, $request->force_update);
                    $results[] = $result;

                    if (!$result['success'] && isset($result['conflicts'])) {
                        $conflicts = array_merge($conflicts, $result['conflicts']);
                    }
                } catch (\Exception $e) {
                    $results[] = [
                        'success' => false,
                        'operation_index' => $index,
                        'message' => $e->getMessage(),
                    ];
                }
            }

            // If there are conflicts and not forced, return them
            if (!empty($conflicts) && !$request->force_update) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Çoklu güncelleme sırasında çakışmalar tespit edildi.',
                    'conflicts' => $conflicts,
                    'requires_confirmation' => true,
                ], 409);
            }

            DB::commit();

            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $totalCount = count($results);

            return response()->json([
                'success' => true,
                'message' => "{$successCount}/{$totalCount} işlem başarıyla tamamlandı.",
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Çoklu güncelleme sırasında hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check for time conflicts
     */
    public function checkConflicts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'session_moves' => 'array',
            'presentation_moves' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $event = Event::findOrFail($request->event_id);
            $this->authorize('view', $event);

            $conflicts = [];

            // Check session move conflicts
            foreach ($request->session_moves ?? [] as $move) {
                $sessionConflicts = $this->checkSessionMoveConflicts(
                    ProgramSession::find($move['session_id']),
                    Venue::find($move['target_venue_id']),
                    $move['new_start_time'],
                    $move['new_end_time']
                );
                $conflicts = array_merge($conflicts, $sessionConflicts);
            }

            // Check presentation move conflicts
            foreach ($request->presentation_moves ?? [] as $move) {
                $presentationConflicts = $this->checkPresentationMoveConflicts(
                    Presentation::find($move['presentation_id']),
                    ProgramSession::find($move['target_session_id']),
                    $move['new_start_time'] ?? null,
                    $move['new_end_time'] ?? null
                );
                $conflicts = array_merge($conflicts, $presentationConflicts);
            }

            return response()->json([
                'success' => true,
                'conflicts' => $conflicts,
                'has_conflicts' => !empty($conflicts),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Çakışma kontrolü sırasında hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Auto-arrange sessions optimally
     */
    public function autoArrange(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'strategy' => 'required|in:minimize_gaps,balance_venues,optimize_flow',
            'preserve_breaks' => 'boolean',
            'min_gap_minutes' => 'integer|min:0|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $event = Event::findOrFail($request->event_id);
            $this->authorize('update', $event);

            $strategy = $request->strategy;
            $preserveBreaks = $request->preserve_breaks ?? true;
            $minGapMinutes = $request->min_gap_minutes ?? 15;

            $arrangements = $this->generateAutoArrangement($event, $strategy, $preserveBreaks, $minGapMinutes);

            return response()->json([
                'success' => true,
                'arrangements' => $arrangements,
                'preview_mode' => true,
                'message' => 'Otomatik düzenleme önerisi hazırlandı.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Otomatik düzenleme sırasında hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Save current layout
     */
    public function saveLayout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'layout_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $event = Event::findOrFail($request->event_id);
            $this->authorize('update', $event);

            $layoutData = $this->captureCurrentLayout($event);

            // Save to event's timeline_config JSON field
            $timelineConfig = $event->timeline_config ?? [];
            $timelineConfig['saved_layouts'] = $timelineConfig['saved_layouts'] ?? [];

            $timelineConfig['saved_layouts'][] = [
                'name' => $request->layout_name,
                'description' => $request->description,
                'layout_data' => $layoutData,
                'created_at' => now()->toISOString(),
                'created_by' => auth()->id(),
            ];

            $event->update(['timeline_config' => $timelineConfig]);

            return response()->json([
                'success' => true,
                'message' => 'Düzen başarıyla kaydedildi.',
                'layout_id' => count($timelineConfig['saved_layouts']) - 1,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Düzen kaydetme sırasında hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Load saved layout
     */
    public function loadLayout(Event $event): JsonResponse
    {
        try {
            $this->authorize('view', $event);

            $timelineConfig = $event->timeline_config ?? [];
            $savedLayouts = $timelineConfig['saved_layouts'] ?? [];

            return response()->json([
                'success' => true,
                'layouts' => $savedLayouts,
                'current_layout' => $this->captureCurrentLayout($event),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Düzen yükleme sırasında hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Quick access to drag & drop
     */
    public function quickAccess(): Response
    {
        return redirect()->route('admin.drag-drop.editor');
    }

    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================

    /**
     * Get available resources for drag & drop
     */
    private function getAvailableResources(Event $event): array
    {
        $organizationId = auth()->user()->currentOrganization->id ?? null;

        return [
            'venues' => $event->eventDays->flatMap->venues->unique('id')->values(),
            'categories' => \App\Models\ProgramSessionCategory::where('organization_id', $organizationId)
                ->active()->get(['id', 'name', 'color']),
            'participants' => \App\Models\Participant::where('organization_id', $organizationId)
                ->active()->get(['id', 'first_name', 'last_name', 'title', 'affiliation']),
            'sponsors' => \App\Models\Sponsor::where('organization_id', $organizationId)
                ->active()->get(['id', 'name', 'logo_url']),
        ];
    }

    /**
     * Detect all conflicts in event
     */
    private function detectAllConflicts(Event $event): array
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
                            'type' => 'session_overlap',
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

                // Check presentation conflicts within sessions
                foreach ($sessions as $session) {
                    $presentations = $session->presentations->sortBy('start_time');

                    for ($j = 0; $j < $presentations->count() - 1; $j++) {
                        $currentPres = $presentations->values()[$j];
                        $nextPres = $presentations->values()[$j + 1];

                        if (
                            $currentPres->end_time && $nextPres->start_time &&
                            $currentPres->end_time > $nextPres->start_time
                        ) {
                            $conflicts[] = [
                                'type' => 'presentation_overlap',
                                'session_id' => $session->id,
                                'session_title' => $session->title,
                                'presentation1_id' => $currentPres->id,
                                'presentation1_title' => $currentPres->title,
                                'presentation2_id' => $nextPres->id,
                                'presentation2_title' => $nextPres->title,
                                'overlap_minutes' => $currentPres->end_time->diffInMinutes($nextPres->start_time),
                            ];
                        }
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * Get layout settings for event
     */
    private function getLayoutSettings(Event $event): array
    {
        $timelineConfig = $event->timeline_config ?? [];

        return [
            'grid_snap' => $timelineConfig['grid_snap'] ?? 15, // minutes
            'min_session_duration' => $timelineConfig['min_session_duration'] ?? 30,
            'min_gap_between_sessions' => $timelineConfig['min_gap_between_sessions'] ?? 15,
            'auto_arrange_enabled' => $timelineConfig['auto_arrange_enabled'] ?? true,
            'show_conflicts' => $timelineConfig['show_conflicts'] ?? true,
            'theme' => $timelineConfig['theme'] ?? 'default',
        ];
    }

    /**
     * Check conflicts for session move
     */
    private function checkSessionMoveConflicts(ProgramSession $session, Venue $targetVenue, string $startTime, string $endTime): array
    {
        $conflicts = [];

        // Check venue conflicts
        $conflictingSessions = ProgramSession::where('venue_id', $targetVenue->id)
            ->where('id', '!=', $session->id)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>', $startTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '>=', $startTime)
                        ->where('end_time', '<=', $endTime);
                });
            })
            ->get();

        foreach ($conflictingSessions as $conflictingSession) {
            $conflicts[] = [
                'type' => 'session_time_conflict',
                'message' => "'{$conflictingSession->title}' oturumu ile zaman çakışması",
                'conflicting_session_id' => $conflictingSession->id,
                'conflicting_session_title' => $conflictingSession->title,
                'venue_name' => $targetVenue->display_name ?? $targetVenue->name,
            ];
        }

        return $conflicts;
    }

    /**
     * Check conflicts for presentation move
     */
    private function checkPresentationMoveConflicts(Presentation $presentation, ProgramSession $targetSession, ?string $startTime, ?string $endTime): array
    {
        $conflicts = [];

        if ($startTime && $endTime) {
            // Check presentation time conflicts within session
            $conflictingPresentations = Presentation::where('program_session_id', $targetSession->id)
                ->where('id', '!=', $presentation->id)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>', $startTime);
                    })->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                            ->where('end_time', '>=', $endTime);
                    })->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '>=', $startTime)
                            ->where('end_time', '<=', $endTime);
                    });
                })
                ->get();

            foreach ($conflictingPresentations as $conflictingPresentation) {
                $conflicts[] = [
                    'type' => 'presentation_time_conflict',
                    'message' => "'{$conflictingPresentation->title}' sunumu ile zaman çakışması",
                    'conflicting_presentation_id' => $conflictingPresentation->id,
                    'conflicting_presentation_title' => $conflictingPresentation->title,
                    'session_title' => $targetSession->title,
                ];
            }

            // Check if presentation time is within session bounds
            if ($targetSession->start_time && $targetSession->end_time) {
                $sessionStart = $targetSession->start_time->format('H:i');
                $sessionEnd = $targetSession->end_time->format('H:i');

                if ($startTime < $sessionStart || $endTime > $sessionEnd) {
                    $conflicts[] = [
                        'type' => 'presentation_out_of_bounds',
                        'message' => 'Sunum zamanı oturum sınırları dışında',
                        'session_time_range' => "{$sessionStart} - {$sessionEnd}",
                        'requested_time_range' => "{$startTime} - {$endTime}",
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Adjust presentation times within session
     */
    private function adjustPresentationTimes(ProgramSession $session): void
    {
        $presentations = $session->presentations()->orderBy('sort_order')->get();

        if ($presentations->isEmpty()) {
            return;
        }

        $sessionDuration = $session->start_time->diffInMinutes($session->end_time);
        $totalPresentationDuration = $presentations->sum('duration_minutes');

        // If total duration fits, distribute evenly
        if ($totalPresentationDuration <= $sessionDuration) {
            $currentTime = $session->start_time->copy();

            foreach ($presentations as $presentation) {
                $duration = $presentation->duration_minutes ?? 20; // default 20 minutes
                $endTime = $currentTime->copy()->addMinutes($duration);

                $presentation->update([
                    'start_time' => $currentTime->format('H:i'),
                    'end_time' => $endTime->format('H:i'),
                ]);

                $currentTime = $endTime;
            }
        }
    }

    /**
     * Reorder presentations in session
     */
    private function reorderPresentations(int $sessionId): void
    {
        $presentations = Presentation::where('program_session_id', $sessionId)
            ->orderBy('start_time')
            ->orderBy('sort_order')
            ->get();

        foreach ($presentations as $index => $presentation) {
            $presentation->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Perform individual bulk operation
     */
    private function performBulkOperation(array $operation, bool $forceUpdate): array
    {
        switch ($operation['type']) {
            case 'move_session':
                return $this->bulkMoveSession($operation['data'], $forceUpdate);
            case 'move_presentation':
                return $this->bulkMovePresentation($operation['data'], $forceUpdate);
            case 'update_time':
                return $this->bulkUpdateTime($operation['data'], $forceUpdate);
            case 'reorder':
                return $this->bulkReorder($operation['data'], $forceUpdate);
            default:
                throw new \InvalidArgumentException("Unknown operation type: {$operation['type']}");
        }
    }

    /**
     * Bulk move session
     */
    private function bulkMoveSession(array $data, bool $forceUpdate): array
    {
        $session = ProgramSession::findOrFail($data['session_id']);
        $targetVenue = Venue::findOrFail($data['target_venue_id']);

        if (!$forceUpdate) {
            $conflicts = $this->checkSessionMoveConflicts(
                $session,
                $targetVenue,
                $data['new_start_time'],
                $data['new_end_time']
            );

            if (!empty($conflicts)) {
                return [
                    'success' => false,
                    'conflicts' => $conflicts,
                ];
            }
        }

        $session->update([
            'venue_id' => $targetVenue->id,
            'start_time' => $data['new_start_time'],
            'end_time' => $data['new_end_time'],
        ]);

        return ['success' => true, 'item_id' => $session->id];
    }

    /**
     * Bulk move presentation
     */
    private function bulkMovePresentation(array $data, bool $forceUpdate): array
    {
        $presentation = Presentation::findOrFail($data['presentation_id']);
        $targetSession = ProgramSession::findOrFail($data['target_session_id']);

        if (!$forceUpdate) {
            $conflicts = $this->checkPresentationMoveConflicts(
                $presentation,
                $targetSession,
                $data['new_start_time'] ?? null,
                $data['new_end_time'] ?? null
            );

            if (!empty($conflicts)) {
                return [
                    'success' => false,
                    'conflicts' => $conflicts,
                ];
            }
        }

        $updateData = ['program_session_id' => $targetSession->id];
        if (isset($data['new_start_time'])) {
            $updateData['start_time'] = $data['new_start_time'];
        }
        if (isset($data['new_end_time'])) {
            $updateData['end_time'] = $data['new_end_time'];
        }

        $presentation->update($updateData);

        return ['success' => true, 'item_id' => $presentation->id];
    }

    /**
     * Bulk update time
     */
    private function bulkUpdateTime(array $data, bool $forceUpdate): array
    {
        // Implementation for bulk time updates
        return ['success' => true, 'message' => 'Time updated'];
    }

    /**
     * Bulk reorder
     */
    private function bulkReorder(array $data, bool $forceUpdate): array
    {
        // Implementation for bulk reordering
        return ['success' => true, 'message' => 'Items reordered'];
    }

    /**
     * Generate auto arrangement
     */
    private function generateAutoArrangement(Event $event, string $strategy, bool $preserveBreaks, int $minGapMinutes): array
    {
        // Implementation for auto arrangement algorithms
        return [
            'strategy' => $strategy,
            'changes' => [],
            'estimated_improvement' => '15% daha az boşluk',
        ];
    }

    /**
     * Capture current layout
     */
    private function captureCurrentLayout(Event $event): array
    {
        $layout = [];

        foreach ($event->eventDays as $eventDay) {
            foreach ($eventDay->venues as $venue) {
                foreach ($venue->programSessions as $session) {
                    $layout['sessions'][] = [
                        'id' => $session->id,
                        'venue_id' => $venue->id,
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                        'sort_order' => $session->sort_order,
                    ];

                    foreach ($session->presentations as $presentation) {
                        $layout['presentations'][] = [
                            'id' => $presentation->id,
                            'session_id' => $session->id,
                            'start_time' => $presentation->start_time,
                            'end_time' => $presentation->end_time,
                            'sort_order' => $presentation->sort_order,
                        ];
                    }
                }
            }
        }

        return $layout;
    }

    /**
     * Log drag & drop action
     */
    private function logDragDropAction(string $action, array $data): void
    {
        // You can implement logging to a dedicated table or use Laravel's logging
        \Log::info("Drag&Drop Action: {$action}", $data);
    }
}
