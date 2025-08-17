<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Participant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of participants
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        $query = Participant::with(['organization'])
                           ->withCount(['moderatedSessions', 'presentations']);

        // Apply user access restrictions
        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $query->whereIn('organization_id', $organizationIds);
        }

        // Filter by organization
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Filter by participant type
        if ($request->filled('type')) {
            match($request->type) {
                'speakers' => $query->speakers(),
                'moderators' => $query->moderators(),
                'both' => $query->where('is_speaker', true)->where('is_moderator', true),
                default => null
            };
        }

        // Filter by affiliation
        if ($request->filled('affiliation')) {
            $query->fromAffiliation($request->affiliation);
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sort options
        $sortField = $request->get('sort', 'first_name');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSorts = ['first_name', 'last_name', 'email', 'affiliation', 'created_at', 'moderated_sessions_count', 'presentations_count'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $participants = $query->paginate(20)
                            ->withQueryString()
                            ->through(function ($participant) {
                                return [
                                    'id' => $participant->id,
                                    'full_name' => $participant->full_name,
                                    'name_with_title' => $participant->name_with_title,
                                    'first_name' => $participant->first_name,
                                    'last_name' => $participant->last_name,
                                    'title' => $participant->title,
                                    'email' => $participant->email,
                                    'phone' => $participant->phone,
                                    'affiliation' => $participant->affiliation,
                                    'photo_url' => $participant->photo_url,
                                    'has_photo' => $participant->has_photo,
                                    'is_speaker' => $participant->is_speaker,
                                    'is_moderator' => $participant->is_moderator,
                                    'short_bio' => $participant->short_bio,
                                    'organization' => [
                                        'id' => $participant->organization->id,
                                        'name' => $participant->organization->name,
                                    ],
                                    'moderated_sessions_count' => $participant->moderated_sessions_count,
                                    'presentations_count' => $participant->presentations_count,
                                    // FIX: Use withCount results instead of method calls
                                    'total_participations' => ($participant->moderated_sessions_count ?? 0) + ($participant->presentations_count ?? 0),
                                    'created_at' => $participant->created_at,
                                    'can_edit' => auth()->user()?->can('update', $participant) ?? false,
                                    'can_delete' => auth()->user()?->can('delete', $participant) ?? false,
                                ];
                            });

        // Get organizations for filter dropdown
        $organizations = $user->isAdmin() 
            ? Organization::active()->orderBy('name')->get(['id', 'name'])
            : $user->organizations()->orderBy('name')->get(['id', 'name']);

        // Get unique affiliations for filter
        $affiliationsQuery = Participant::select('affiliation')
                                      ->whereNotNull('affiliation')
                                      ->distinct();
        
        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $affiliationsQuery->whereIn('organization_id', $organizationIds);
        }

        $affiliations = $affiliationsQuery->orderBy('affiliation')->pluck('affiliation');

        return Inertia::render('Admin/Participants/Index', [
            'participants' => $participants,
            'organizations' => $organizations,
            'affiliations' => $affiliations,
            'filters' => [
                'search' => $request->search,
                'organization_id' => $request->organization_id,
                'type' => $request->type,
                'affiliation' => $request->affiliation,
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
            'can_create' => auth()->user()?->can('create', Participant::class) ?? false,
        ]);
    }

    /**
     * Show the form for creating a new participant
     */
    public function create(): Response
    {
        $this->authorize('create', Participant::class);

        $user = auth()->user();
        
        // Get available organizations
        $organizations = $user->isAdmin() 
            ? Organization::active()->orderBy('name')->get(['id', 'name'])
            : $user->organizations()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Participants/Create', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Store a newly created participant
     */
    public function store(Request $request)
    {
        $this->authorize('create', Participant::class);

        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'affiliation' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('participants')->where(function ($query) use ($request) {
                    return $query->where('organization_id', $request->organization_id);
                }),
            ],
            'phone' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_speaker' => 'boolean',
            'is_moderator' => 'boolean',
        ]);

        // Check user access to organization
        if (!auth()->user()->isAdmin()) {
            $hasAccess = auth()->user()->organizations()->where('organizations.id', $request->organization_id)->exists();
            if (!$hasAccess) {
                abort(403, 'Bu organizasyona katılımcı ekleyemezsiniz.');
            }
        }

        DB::beginTransaction();
        
        try {
            $data = $request->all();

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('participants', 'public');
            }

            $participant = Participant::create($data);

            DB::commit();

            return redirect()
                ->route('admin.participants.show', $participant)
                ->with('success', 'Katılımcı başarıyla oluşturuldu.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if exists
            if (isset($data['photo'])) {
                Storage::disk('public')->delete($data['photo']);
            }

            return back()
                ->withErrors(['error' => 'Katılımcı oluşturulurken bir hata oluştu: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified participant
     */
    public function show(Participant $participant): Response
    {
        $this->authorize('view', $participant);

        $participant->load([
            'organization',
            'moderatedSessions' => function ($query) {
                $query->with(['venue.eventDay.event'])->latest();
            },
            'presentations' => function ($query) {
                $query->with(['programSession.venue.eventDay.event'])->latest();
            }
        ]);

        // FIX: Calculate statistics using database queries instead of method calls
        $moderatedSessionsCount = $participant->moderatedSessions()->count();
        $presentationsCount = $participant->presentations()->count();
        $primaryPresentationsCount = $participant->presentations()->wherePivot('speaker_role', 'primary')->count();
        $coSpeakerPresentationsCount = $participant->presentations()->wherePivot('speaker_role', 'co_speaker')->count();
        $discussantPresentationsCount = $participant->presentations()->wherePivot('speaker_role', 'discussant')->count();

        // Get participation statistics
        $statistics = [
            'total_sessions_moderated' => $moderatedSessionsCount,
            'total_presentations' => $presentationsCount,
            'primary_presentations' => $primaryPresentationsCount,
            'co_speaker_presentations' => $coSpeakerPresentationsCount,
            'discussant_presentations' => $discussantPresentationsCount,
            'total_participations' => $moderatedSessionsCount + $presentationsCount,
        ];

        // Get participation by events
        $participationsByEvent = collect();
        
        foreach ($participant->moderatedSessions as $session) {
            $event = $session->venue->eventDay->event;
            $participationsByEvent->push([
                'event_id' => $event->id,
                'event_name' => $event->name,
                'type' => 'moderator',
                'title' => $session->title,
                'date' => $session->venue->eventDay->date,
                'venue' => $session->venue->display_name,
            ]);
        }

        foreach ($participant->presentations as $presentation) {
            $event = $presentation->programSession->venue->eventDay->event;
            $participationsByEvent->push([
                'event_id' => $event->id,
                'event_name' => $event->name,
                'type' => 'speaker',
                'title' => $presentation->title,
                'date' => $presentation->programSession->venue->eventDay->date,
                'venue' => $presentation->programSession->venue->display_name,
                'speaker_role' => $presentation->pivot->speaker_role ?? 'primary',
            ]);
        }

        $groupedParticipations = $participationsByEvent->groupBy('event_name');

        return Inertia::render('Admin/Participants/Show', [
            'participant' => [
                'id' => $participant->id,
                'full_name' => $participant->full_name,
                'name_with_title' => $participant->name_with_title,
                'first_name' => $participant->first_name,
                'last_name' => $participant->last_name,
                'title' => $participant->title,
                'email' => $participant->email,
                'phone' => $participant->phone,
                'affiliation' => $participant->affiliation,
                'bio' => $participant->bio,
                'photo_url' => $participant->photo_url,
                'has_photo' => $participant->has_photo,
                'is_speaker' => $participant->is_speaker,
                'is_moderator' => $participant->is_moderator,
                'created_at' => $participant->created_at,
                'updated_at' => $participant->updated_at,
                'organization' => [
                    'id' => $participant->organization->id,
                    'name' => $participant->organization->name,
                ],
            ],
            'statistics' => $statistics,
            'participations_by_event' => $groupedParticipations,
            'can_edit' => auth()->user()?->can('update', $participant) ?? false,
            'can_delete' => auth()->user()?->can('delete', $participant) ?? false,
        ]);
    }

    /**
     * Show the form for editing participant - FIXED
     */
    public function edit(Participant $participant): Response
    {
        $this->authorize('update', $participant);

        $user = auth()->user();
        
        // Load organization relationship
        $participant->load(['organization']);
        
        // Get available organizations
        $organizations = $user->isAdmin() 
            ? Organization::active()->orderBy('name')->get(['id', 'name'])
            : $user->organizations()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Participants/Edit', [
            'participant' => [
                'id' => $participant->id,
                'organization_id' => $participant->organization_id,
                'first_name' => $participant->first_name,
                'last_name' => $participant->last_name,
                'full_name' => $participant->full_name, // FIX: Added full_name accessor
                'name_with_title' => $participant->name_with_title, // FIX: Added name_with_title accessor
                'title' => $participant->title,
                'email' => $participant->email,
                'phone' => $participant->phone,
                'affiliation' => $participant->affiliation,
                'bio' => $participant->bio,
                'photo' => $participant->photo, // FIX: Added photo field (file path)
                'photo_url' => $participant->photo_url, // FIX: Added photo_url accessor
                'has_photo' => $participant->has_photo, // FIX: Added has_photo accessor
                'is_speaker' => (bool) $participant->is_speaker, // FIX: Explicit boolean conversion
                'is_moderator' => (bool) $participant->is_moderator, // FIX: Explicit boolean conversion
                'created_at' => $participant->created_at,
                'updated_at' => $participant->updated_at,
                
                // FIX: Added organization relationship data
                'organization' => $participant->organization ? [
                    'id' => $participant->organization->id,
                    'name' => $participant->organization->name,
                ] : null,
                
                // FIX: Added presentation and session counts for display
                'presentations' => $participant->presentations()->with(['programSession.venue.eventDay.event'])->get()->map(function ($presentation) {
                    return [
                        'id' => $presentation->id,
                        'title' => $presentation->title,
                        'speaker_role' => $presentation->pivot->speaker_role ?? 'primary',
                        'session' => [
                            'title' => $presentation->programSession->title,
                            'event_name' => $presentation->programSession->venue->eventDay->event->name,
                        ],
                    ];
                }),
                
                'moderated_sessions' => $participant->moderatedSessions()->with(['venue.eventDay.event'])->get()->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'title' => $session->title,
                        'event_name' => $session->venue->eventDay->event->name,
                    ];
                }),
            ],
            'organizations' => $organizations,
        ]);
    }

    /**
     * Update the specified participant
     */
    public function update(Request $request, Participant $participant)
    {
        $this->authorize('update', $participant);

        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'affiliation' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('participants')->where(function ($query) use ($request) {
                    return $query->where('organization_id', $request->organization_id);
                })->ignore($participant->id),
            ],
            'phone' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable', // Can be file, string 'remove', or null
            'is_speaker' => 'boolean',
            'is_moderator' => 'boolean',
        ]);

        // Check user access to organization
        if (!auth()->user()->isAdmin()) {
            $hasAccess = auth()->user()->organizations()->where('organizations.id', $request->organization_id)->exists();
            if (!$hasAccess) {
                abort(403, 'Bu organizasyona katılımcı güncelleyemezsiniz.');
            }
        }

        DB::beginTransaction();
        
        try {
            $data = $request->except(['photo']); // Handle photo separately

            // Handle photo upload/removal
            if ($request->has('photo')) {
                if ($request->photo === 'remove') {
                    // Remove existing photo
                    if ($participant->photo) {
                        Storage::disk('public')->delete($participant->photo);
                        $data['photo'] = null;
                    }
                } elseif ($request->hasFile('photo')) {
                    // Upload new photo
                    // Delete old photo first
                    if ($participant->photo) {
                        Storage::disk('public')->delete($participant->photo);
                    }
                    $data['photo'] = $request->file('photo')->store('participants', 'public');
                }
                // If photo is null, don't update the photo field (keep existing)
            }

            $participant->update($data);

            DB::commit();

            return redirect()
                ->route('admin.participants.show', $participant)
                ->with('success', 'Katılımcı başarıyla güncellendi.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if exists
            if (isset($data['photo']) && $data['photo'] !== $participant->photo && $data['photo'] !== null) {
                Storage::disk('public')->delete($data['photo']);
            }

            return back()
                ->withErrors(['error' => 'Katılımcı güncellenirken bir hata oluştu: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified participant
     */
    public function destroy(Participant $participant)
    {
        $this->authorize('delete', $participant);

        try {
            // FIX: Use direct database query to check if can be deleted
            $hasModeratedSessions = $participant->moderatedSessions()->exists();
            $hasPresentations = $participant->presentations()->exists();
            
            if ($hasModeratedSessions || $hasPresentations) {
                return back()->withErrors([
                    'error' => 'Oturum veya sunumu olan katılımcı silinemez.'
                ]);
            }

            DB::beginTransaction();

            // Delete photo
            if ($participant->photo) {
                Storage::disk('public')->delete($participant->photo);
            }

            $participantName = $participant->full_name;
            $participant->delete();

            DB::commit();

            return redirect()
                ->route('admin.participants.index')
                ->with('success', "'{$participantName}' katılımcısı başarıyla silindi.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Katılımcı silinirken bir hata oluştu.'
            ]);
        }
    }

    /**
     * Bulk import participants
     */
    public function import(Request $request)
    {
        $this->authorize('create', Participant::class);

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx|max:2048',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        // This will be implemented in ImportController
        return redirect()->route('admin.import.participants', [
            'file' => $request->file('file'),
            'organization_id' => $request->organization_id,
        ]);
    }

    /**
     * Export participants
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Participant::class);

        // This will be implemented in ExportController
        return redirect()->route('admin.export.participants', $request->all());
    }

    /**
     * Search participants for autocomplete
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'organization_id' => 'nullable|exists:organizations,id',
            'type' => 'nullable|in:speakers,moderators,all',
        ]);

        $user = auth()->user();
        $query = Participant::query();

        // Apply user access restrictions
        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $query->whereIn('organization_id', $organizationIds);
        }

        // Filter by organization
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            match($request->type) {
                'speakers' => $query->speakers(),
                'moderators' => $query->moderators(),
                default => null
            };
        }

        // Search
        $participants = $query->search($request->q)
                            ->limit(20)
                            ->get(['id', 'first_name', 'last_name', 'title', 'affiliation', 'email']);

        return response()->json([
            'participants' => $participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'value' => $participant->id,
                    'label' => $participant->full_name,
                    'full_name' => $participant->full_name,
                    'name_with_title' => $participant->name_with_title,
                    'affiliation' => $participant->affiliation,
                    'email' => $participant->email,
                ];
            }),
        ]);
    }
}