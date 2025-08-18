<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PresentationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of presentations
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        $organizationId = $this->getCurrentOrganizationId($user);

        if (!$organizationId) {
            return $this->renderEmptyIndex($request);
        }

        $query = Presentation::query()
            ->whereHas('programSession.venue.eventDay.event', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->with([
                'programSession.venue.eventDay.event',
                'programSession.venue',
                'speakers'
            ])
            ->withCount('speakers');

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

        $presentations = $query->paginate($request->get('per_page', 15))
            ->withQueryString();

        // Transform data
        $presentations->getCollection()->transform(function ($presentation) {
            return $this->transformPresentationForIndex($presentation);
        });

        return Inertia::render('Admin/Presentations/Index', [
            'presentations' => $presentations,
            'events' => $this->getEventsForFilter($organizationId),
            'filters' => $request->all(),
            'stats' => $this->calculateStats($organizationId)
        ]);
    }

    /**
     * Show the form for creating a new presentation
     */
    public function create(Request $request): Response
    {
        $user = auth()->user();
        $organizationId = $this->getCurrentOrganizationId($user);

        if (!$organizationId) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Organizasyon bulunamadı.');
        }

        $programSessions = $this->getProgramSessionsForForm($organizationId);
        $participants = $this->getParticipantsForForm($organizationId);
        $preselectedSession = $this->getPreselectedSession($request);

        return Inertia::render('Admin/Presentations/Create', [
            'programSessions' => $programSessions,
            'participants' => $participants,
            'preselectedSession' => $preselectedSession,
        ]);
    }

    /**
     * Store a newly created presentation
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePresentationData($request);

        // Check session belongs to user's organization
        if (!$this->validateSessionAccess($validated['program_session_id'])) {
            return back()->withErrors(['error' => 'Bu oturuma sunum ekleyemezsiniz.']);
        }

        try {
            DB::beginTransaction();

            $presentation = Presentation::create($validated);
            $this->attachSpeakers($presentation, $validated['speakers'] ?? []);

            DB::commit();

            return redirect()
                ->route('admin.presentations.index')
                ->with('success', "Sunum '{$presentation->title}' başarıyla oluşturuldu.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Sunum oluşturulurken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified presentation
     */
    public function show(Presentation $presentation): Response
    {
        $this->authorize('view', $presentation);

        $presentation->load([
            'programSession.venue.eventDay.event',
            'programSession.categories',
            'speakers'
        ]);

        return Inertia::render('Admin/Presentations/Show', [
            'presentation' => $this->transformPresentationForShow($presentation),
        ]);
    }

    /**
     * Show the form for editing the specified presentation
     */
    public function edit(Presentation $presentation): Response
    {
        $this->authorize('update', $presentation);

        $user = auth()->user();
        $organizationId = $this->getCurrentOrganizationId($user);

        $presentation->load(['speakers']);

        return Inertia::render('Admin/Presentations/Edit', [
            'presentation' => $this->transformPresentationForEdit($presentation),
            'programSessions' => $this->getProgramSessionsForForm($organizationId),
            'participants' => $this->getParticipantsForForm($organizationId),
        ]);
    }

    /**
     * Update the specified presentation
     */
    public function update(Request $request, Presentation $presentation): RedirectResponse
    {
        $this->authorize('update', $presentation);

        $validated = $this->validatePresentationData($request);

        try {
            DB::beginTransaction();

            $presentation->update($validated);
            $this->syncSpeakers($presentation, $validated['speakers'] ?? []);

            DB::commit();

            return redirect()
                ->route('admin.presentations.index')
                ->with('success', "Sunum '{$presentation->title}' başarıyla güncellendi.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Sunum güncellenirken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified presentation
     */
    public function destroy(Presentation $presentation): RedirectResponse
    {
        $this->authorize('delete', $presentation);

        try {
            DB::beginTransaction();

            $presentationTitle = $presentation->title;
            $presentation->speakers()->detach();
            $presentation->delete();

            DB::commit();

            return redirect()
                ->route('admin.presentations.index')
                ->with('success', "Sunum '{$presentationTitle}' başarıyla silindi.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Sunum silinirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Duplicate the specified presentation
     */
    public function duplicate(Presentation $presentation): RedirectResponse
    {
        $this->authorize('create', Presentation::class);

        try {
            DB::beginTransaction();

            $newPresentation = $presentation->replicate();
            $newPresentation->title = $presentation->title . ' (Kopya)';
            $newPresentation->save();

            // Copy speakers
            foreach ($presentation->speakers as $speaker) {
                $newPresentation->speakers()->attach($speaker->id, [
                    'speaker_role' => $speaker->pivot->speaker_role,
                    'sort_order' => $speaker->pivot->sort_order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.presentations.edit', $newPresentation)
                ->with('success', "Sunum '{$presentation->title}' kopyalandı.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Sunum kopyalanırken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    // =================================================================
    // PRIVATE HELPER METHODS
    // =================================================================

    /**
     * Get current organization ID
     */
    private function getCurrentOrganizationId($user): ?int
    {
        if ($user->currentOrganization) {
            return $user->currentOrganization->id;
        }

        $firstOrganization = $user->organizations()->first();
        if ($firstOrganization) {
            return $firstOrganization->id;
        }

        if ($user->isAdmin()) {
            $firstOrg = \App\Models\Organization::first();
            return $firstOrg ? $firstOrg->id : null;
        }

        return null;
    }

    /**
     * Render empty index page
     */
    private function renderEmptyIndex($request): Response
    {
        return Inertia::render('Admin/Presentations/Index', [
            'presentations' => [
                'data' => [],
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1,
                'from' => null,
                'to' => null
            ],
            'events' => [],
            'filters' => $request->all(),
            'stats' => [
                'total' => 0,
                'with_speakers' => 0,
                'without_speakers' => 0,
                'keynote' => 0
            ],
            'error' => 'Organizasyon bulunamadı. Lütfen bir organizasyona bağlı olduğunuzdan emin olun.'
        ]);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, $request): void
    {
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('abstract', 'like', "%{$searchTerm}%")
                    ->orWhereHas('speakers', function ($sq) use ($searchTerm) {
                        $sq->where('first_name', 'like', "%{$searchTerm}%")
                            ->orWhere('last_name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        if ($request->filled('event_id')) {
            $query->whereHas('programSession.venue.eventDay.event', function ($q) use ($request) {
                $q->where('id', $request->event_id);
            });
        }

        if ($request->filled('session_id')) {
            $query->where('program_session_id', $request->session_id);
        }

        if ($request->filled('presentation_type')) {
            $query->where('presentation_type', $request->presentation_type);
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, $request): void
    {
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['title', 'created_at', 'start_time', 'presentation_type'];

        if (in_array($sortField, $allowedSorts)) {
            if ($sortField === 'start_time') {
                $query->join('program_sessions', 'presentations.program_session_id', '=', 'program_sessions.id')
                    ->orderBy('program_sessions.start_time', $sortDirection)
                    ->select('presentations.*');
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Transform presentation for index
     */
    private function transformPresentationForIndex($presentation): array
    {
        $programSession = $presentation->programSession;
        
        return [
            'id' => $presentation->id,
            'title' => $this->cleanString($presentation->title),
            'abstract' => $this->cleanString($presentation->abstract),
            'duration_minutes' => $presentation->duration_minutes,
            'presentation_type' => $presentation->presentation_type,
            'language' => $presentation->language,
            'start_time' => $programSession?->start_time?->format('H:i'),
            'end_time' => $programSession?->end_time?->format('H:i'),
            'formatted_time_range' => $programSession?->formatted_time_range,
            'program_session' => $this->transformProgramSession($programSession),
            'speakers' => $this->transformSpeakers($presentation->speakers),
            'speakers_count' => $presentation->speakers_count,
            'can_edit' => $this->canAuthorize('update', $presentation),
            'can_delete' => $this->canAuthorize('delete', $presentation),
        ];
    }

    /**
     * Transform presentation for show
     */
    private function transformPresentationForShow($presentation): array
    {
        return [
            'id' => $presentation->id,
            'title' => $this->cleanString($presentation->title),
            'abstract' => $this->cleanString($presentation->abstract),
            'duration_minutes' => $presentation->duration_minutes,
            'presentation_type' => $presentation->presentation_type,
            'language' => $presentation->language,
            'notes' => $this->cleanString($presentation->notes),
            'sort_order' => $presentation->sort_order,
            'created_at' => $presentation->created_at?->toISOString(),
            'updated_at' => $presentation->updated_at?->toISOString(),
            'programSession' => $this->transformProgramSessionDetailed($presentation->programSession),
            'speakers' => $this->transformSpeakersDetailed($presentation->speakers),
        ];
    }

    /**
     * Transform presentation for edit
     */
    private function transformPresentationForEdit($presentation): array
    {
        return [
            'id' => $presentation->id,
            'program_session_id' => $presentation->program_session_id,
            'title' => $this->cleanString($presentation->title),
            'abstract' => $this->cleanString($presentation->abstract),
            'duration_minutes' => $presentation->duration_minutes,
            'presentation_type' => $presentation->presentation_type ?? '',
            'language' => $presentation->language ?? '',
            'notes' => $this->cleanString($presentation->notes),
            'sort_order' => $presentation->sort_order ?? 0,
            'speakers' => $presentation->speakers->map(function ($speaker) {
                return [
                    'participant_id' => $speaker->id,
                    'role' => $speaker->pivot->speaker_role ?? 'primary',
                    'sort_order' => $speaker->pivot->sort_order ?? 0,
                ];
            })->toArray()
        ];
    }

    /**
     * Transform program session
     */
    private function transformProgramSession($session): ?array
    {
        if (!$session) return null;

        return [
            'id' => $session->id,
            'title' => $this->cleanString($session->title),
            'venue' => $session->venue ? [
                'id' => $session->venue->id,
                'display_name' => $this->cleanString($session->venue->display_name),
                'event_day' => $session->venue->eventDay ? [
                    'id' => $session->venue->eventDay->id,
                    'display_name' => $this->cleanString($session->venue->eventDay->display_name),
                    'event' => $session->venue->eventDay->event ? [
                        'id' => $session->venue->eventDay->event->id,
                        'name' => $this->cleanString($session->venue->eventDay->event->name),
                    ] : null
                ] : null
            ] : null
        ];
    }

    /**
     * Transform program session detailed
     */
    private function transformProgramSessionDetailed($session): ?array
    {
        if (!$session) return null;

        return [
            'id' => $session->id,
            'title' => $this->cleanString($session->title),
            'start_time' => $session->start_time,
            'end_time' => $session->end_time,
            'venue' => $session->venue ? [
                'id' => $session->venue->id,
                'display_name' => $this->cleanString($session->venue->display_name),
                'eventDay' => $session->venue->eventDay ? [
                    'id' => $session->venue->eventDay->id,
                    'display_name' => $this->cleanString($session->venue->eventDay->display_name),
                    'event' => $session->venue->eventDay->event ? [
                        'id' => $session->venue->eventDay->event->id,
                        'name' => $this->cleanString($session->venue->eventDay->event->name),
                    ] : null
                ] : null
            ] : null,
            'categories' => $session->categories ? $session->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $this->cleanString($category->name),
                ];
            })->toArray() : []
        ];
    }

    /**
     * Transform speakers
     */
    private function transformSpeakers($speakers): array
    {
        if (!$speakers) return [];

        return $speakers->map(function ($speaker) {
            return [
                'id' => $speaker->id,
                'first_name' => $this->cleanString($speaker->first_name),
                'last_name' => $this->cleanString($speaker->last_name),
                'participant' => [
                    'id' => $speaker->id,
                    'first_name' => $this->cleanString($speaker->first_name),
                    'last_name' => $this->cleanString($speaker->last_name),
                ],
                'pivot' => [
                    'speaker_role' => $speaker->pivot->speaker_role ?? 'primary'
                ]
            ];
        })->toArray();
    }

    /**
     * Transform speakers detailed
     */
    private function transformSpeakersDetailed($speakers): array
    {
        if (!$speakers) return [];

        return $speakers->map(function ($speaker) {
            return [
                'id' => $speaker->id,
                'first_name' => $this->cleanString($speaker->first_name),
                'last_name' => $this->cleanString($speaker->last_name),
                'email' => $this->cleanString($speaker->email),
                'affiliation' => $this->cleanString($speaker->affiliation),
                'pivot' => [
                    'speaker_role' => $speaker->pivot->speaker_role ?? 'primary',
                    'sort_order' => $speaker->pivot->sort_order ?? 0,
                ]
            ];
        })->toArray();
    }

    /**
     * Get events for filter
     */
    private function getEventsForFilter($organizationId): array
    {
        return \App\Models\Event::where('organization_id', $organizationId)
            ->select('id', 'name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $this->cleanString($event->name),
                    'name' => $this->cleanString($event->name),
                ];
            })
            ->toArray();
    }

    /**
     * Get program sessions for form
     */
    private function getProgramSessionsForForm($organizationId): array
    {
        return ProgramSession::whereHas('venue.eventDay.event', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->with(['venue.eventDay.event', 'venue'])
            ->orderBy('start_time')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'title' => $this->cleanString($session->title),
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'venue' => $session->venue ? [
                        'id' => $session->venue->id,
                        'display_name' => $this->cleanString($session->venue->display_name),
                        'eventDay' => $session->venue->eventDay ? [
                            'id' => $session->venue->eventDay->id,
                            'display_name' => $this->cleanString($session->venue->eventDay->display_name),
                            'event' => $session->venue->eventDay->event ? [
                                'id' => $session->venue->eventDay->event->id,
                                'name' => $this->cleanString($session->venue->eventDay->event->name),
                            ] : null
                        ] : null
                    ] : null
                ];
            })
            ->toArray();
    }

    /**
     * Get participants for form
     */
    private function getParticipantsForForm($organizationId): array
    {
        return Participant::where('organization_id', $organizationId)
            ->orderBy('last_name')
            ->get()
            ->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'first_name' => $this->cleanString($participant->first_name),
                    'last_name' => $this->cleanString($participant->last_name),
                    'email' => $this->cleanString($participant->email),
                ];
            })
            ->toArray();
    }

    /**
     * Get preselected session
     */
    private function getPreselectedSession($request): ?array
    {
        if (!$request->session_id) return null;

        $session = ProgramSession::find($request->session_id);
        if (!$session) return null;

        return [
            'id' => $session->id,
            'title' => $this->cleanString($session->title),
        ];
    }

    /**
     * Calculate stats
     */
    private function calculateStats($organizationId): array
    {
        $baseQuery = Presentation::whereHas('programSession.venue.eventDay.event', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        });

        return [
            'total' => $baseQuery->count(),
            'with_speakers' => $baseQuery->has('speakers')->count(),
            'without_speakers' => $baseQuery->doesntHave('speakers')->count(),
            'keynote' => $baseQuery->where('presentation_type', 'keynote')->count()
        ];
    }

    /**
     * Validate presentation data
     */
    private function validatePresentationData($request): array
    {
        return $request->validate([
            'program_session_id' => 'required|exists:program_sessions,id',
            'title' => 'required|string|max:500',
            'abstract' => 'nullable|string|max:5000',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'presentation_type' => 'nullable|in:keynote,oral,poster,panel,workshop',
            'language' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'speakers' => 'nullable|array',
            'speakers.*.participant_id' => 'required|exists:participants,id',
            'speakers.*.role' => 'required|in:primary,secondary,moderator',
            'speakers.*.sort_order' => 'nullable|integer|min:0',
        ]);
    }

    /**
     * Validate session access
     */
    private function validateSessionAccess($sessionId): bool
    {
        $user = auth()->user();
        $organizationId = $this->getCurrentOrganizationId($user);

        $session = ProgramSession::with('venue.eventDay.event')->find($sessionId);

        return $session && $session->venue->eventDay->event->organization_id === $organizationId;
    }

    /**
     * Attach speakers
     */
    private function attachSpeakers($presentation, $speakers): void
    {
        foreach ($speakers as $speakerData) {
            $presentation->speakers()->attach($speakerData['participant_id'], [
                'speaker_role' => $speakerData['role'],
                'sort_order' => $speakerData['sort_order'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Sync speakers
     */
    private function syncSpeakers($presentation, $speakers): void
    {
        $speakersData = [];
        foreach ($speakers as $speakerData) {
            $speakersData[$speakerData['participant_id']] = [
                'speaker_role' => $speakerData['role'],
                'sort_order' => $speakerData['sort_order'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $presentation->speakers()->sync($speakersData);
    }

    /**
     * Clean string for UTF-8
     */
    private function cleanString($string): ?string
    {
        if ($string === null) return null;

        $cleaned = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);
        $cleaned = str_replace("\0", '', $cleaned);

        return $cleaned;
    }

    /**
     * Check authorization safely
     */
    private function canAuthorize($action, $resource): bool
    {
        try {
            $this->authorize($action, $resource);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
