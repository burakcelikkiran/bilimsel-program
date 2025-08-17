<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreParticipantRequest;
use App\Http\Requests\Admin\UpdateParticipantRequest;
use App\Models\Participant;
use App\Models\Organization;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Participants",
 *     description="API endpoints for managing participants (speakers, moderators)"
 * )
 */
class ParticipantController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of participants
     *
     * @OA\Get(
     *     path="/api/v1/admin/participants",
     *     operationId="getParticipants",
     *     tags={"Participants"},
     *     summary="Get list of participants",
     *     description="Returns a paginated list of participants with filtering, search and sorting capabilities",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for participant name, email, or affiliation",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filter by organization ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by participant role",
     *         required=false,
     *         @OA\Schema(type="string", enum={"speaker", "moderator", "both", "none"})
     *     ),
     *     @OA\Parameter(
     *         name="affiliation",
     *         in="query",
     *         description="Filter by affiliation",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"first_name", "last_name", "email", "affiliation", "created_at", "is_speaker", "is_moderator"}, default="first_name")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ParticipantWithStats")),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - No accessible participants",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $query = Participant::with(['organization']);

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                if ($organizationIds->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erişilebilir katılımcı bulunamadı.',
                        'data' => []
                    ], 403);
                }
                $query->whereIn('organization_id', $organizationIds);
            }

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('affiliation', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            }

            // Filter by organization
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Filter by role
            if ($request->filled('role')) {
                switch ($request->role) {
                    case 'speaker':
                        $query->where('is_speaker', true);
                        break;
                    case 'moderator':
                        $query->where('is_moderator', true);
                        break;
                    case 'both':
                        $query->where('is_speaker', true)->where('is_moderator', true);
                        break;
                    case 'none':
                        $query->where('is_speaker', false)->where('is_moderator', false);
                        break;
                }
            }

            // Filter by affiliation
            if ($request->filled('affiliation')) {
                $query->where('affiliation', 'like', "%{$request->affiliation}%");
            }

            // Sort options
            $sortField = $request->get('sort', 'first_name');
            $sortDirection = $request->get('direction', 'asc');
            
            $allowedSorts = ['first_name', 'last_name', 'email', 'affiliation', 'created_at', 'is_speaker', 'is_moderator'];
            if (in_array($sortField, $allowedSorts)) {
                if ($sortField === 'first_name') {
                    $query->orderBy('first_name', $sortDirection)
                          ->orderBy('last_name', $sortDirection);
                } else {
                    $query->orderBy($sortField, $sortDirection);
                }
            }

            // Add statistics
            $query->withCount([
                'presentations',
                'moderatedSessions'
            ]);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $perPage = min($perPage, 100);

            $participants = $query->paginate($perPage);

            // Add calculated fields
            $participants->getCollection()->transform(function ($participant) {
                $participant->full_name = trim($participant->first_name . ' ' . $participant->last_name);
                $participant->total_participations = $participant->presentations_count + $participant->moderated_sessions_count;
                $participant->role_badge = [];
                
                if ($participant->is_speaker) {
                    $participant->role_badge[] = 'Konuşmacı';
                }
                if ($participant->is_moderator) {
                    $participant->role_badge[] = 'Moderatör';
                }
                
                return $participant;
            });

            return response()->json([
                'success' => true,
                'data' => $participants->items(),
                'meta' => [
                    'current_page' => $participants->currentPage(),
                    'per_page' => $participants->perPage(),
                    'total' => $participants->total(),
                    'last_page' => $participants->lastPage(),
                    'from' => $participants->firstItem(),
                    'to' => $participants->lastItem(),
                ],
                'links' => [
                    'first' => $participants->url(1),
                    'last' => $participants->url($participants->lastPage()),
                    'prev' => $participants->previousPageUrl(),
                    'next' => $participants->nextPageUrl(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Admin ParticipantController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Katılımcılar listelenirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created participant
     *
     * @OA\Post(
     *     path="/api/v1/admin/participants",
     *     operationId="storeParticipant",
     *     tags={"Participants"},
     *     summary="Create a new participant",
     *     description="Creates a new participant with the provided data",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="organization_id", type="integer", description="Organization ID"),
     *                 @OA\Property(property="first_name", type="string", maxLength=255, description="First name"),
     *                 @OA\Property(property="last_name", type="string", maxLength=255, description="Last name"),
     *                 @OA\Property(property="title", type="string", maxLength=255, nullable=true, description="Professional title"),
     *                 @OA\Property(property="email", type="string", format="email", maxLength=255, description="Email address"),
     *                 @OA\Property(property="affiliation", type="string", maxLength=500, nullable=true, description="Institutional affiliation"),
     *                 @OA\Property(property="bio", type="string", nullable=true, description="Biography"),
     *                 @OA\Property(property="photo", type="string", format="binary", description="Profile photo"),
     *                 @OA\Property(property="is_speaker", type="boolean", description="Can be a speaker"),
     *                 @OA\Property(property="is_moderator", type="boolean", description="Can be a moderator"),
     *                 @OA\Property(property="linkedin_url", type="string", format="url", nullable=true, description="LinkedIn profile URL"),
     *                 @OA\Property(property="twitter_url", type="string", format="url", nullable=true, description="Twitter profile URL"),
     *                 @OA\Property(property="website_url", type="string", format="url", nullable=true, description="Personal website URL")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Participant created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Katılımcı başarıyla oluşturuldu."),
     *             @OA\Property(property="data", ref="#/components/schemas/Participant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(StoreParticipantRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Handle photo upload if provided
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('participants', 'public');
            }

            $participant = Participant::create($data);

            DB::commit();

            // Load relationships for response
            $participant->load(['organization']);

            return response()->json([
                'success' => true,
                'message' => 'Katılımcı başarıyla oluşturuldu.',
                'data' => $participant
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded file if exists
            if (isset($data['photo'])) {
                Storage::disk('public')->delete($data['photo']);
            }

            Log::error('Admin ParticipantController@store error: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Katılımcı oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified participant
     *
     * @OA\Get(
     *     path="/api/v1/admin/participants/{participant}",
     *     operationId="showParticipant",
     *     tags={"Participants"},
     *     summary="Get participant details",
     *     description="Returns detailed information about a specific participant including statistics and participations",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="participant",
     *         in="path",
     *         description="Participant ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="participant", ref="#/components/schemas/ParticipantDetail"),
     *                 @OA\Property(property="statistics", ref="#/components/schemas/ParticipantStatistics"),
     *                 @OA\Property(property="participations_by_event", type="array", @OA\Items(ref="#/components/schemas/ParticipationsByEvent")),
     *                 @OA\Property(property="recent_activities", type="array", @OA\Items(ref="#/components/schemas/ParticipantActivity")),
     *                 @OA\Property(property="permissions", ref="#/components/schemas/ParticipantPermissions")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Participant not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(Participant $participant): JsonResponse
    {
        try {
            $participant->load([
                'organization',
                'moderatedSessions' => function ($query) {
                    $query->with(['venue.eventDay.event'])
                          ->orderBy('start_time', 'desc');
                },
                'presentations' => function ($query) {
                    $query->with(['programSession.venue.eventDay.event'])
                          ->orderBy('start_time', 'desc');
                }
            ]);

            // Calculate participation statistics
            $statistics = [
                'total_sessions_moderated' => $participant->moderatedSessions->count(),
                'total_presentations' => $participant->presentations->count(),
                'primary_presentations' => $participant->presentations->where('pivot.speaker_role', 'primary')->count(),
                'co_speaker_presentations' => $participant->presentations->where('pivot.speaker_role', 'co_speaker')->count(),
                'discussant_presentations' => $participant->presentations->where('pivot.speaker_role', 'discussant')->count(),
                'total_participations' => $participant->moderatedSessions->count() + $participant->presentations->count(),
            ];

            // Get participation by events
            $participationsByEvent = collect();
            
            // Add moderated sessions
            foreach ($participant->moderatedSessions as $session) {
                $event = $session->venue->eventDay->event;
                $participationsByEvent->push([
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'type' => 'moderator',
                    'title' => $session->title,
                    'date' => $session->venue->eventDay->date,
                    'venue' => $session->venue->display_name ?? $session->venue->name,
                    'time' => $session->start_time && $session->end_time 
                        ? $session->start_time . ' - ' . $session->end_time 
                        : null,
                ]);
            }

            // Add presentations
            foreach ($participant->presentations as $presentation) {
                $event = $presentation->programSession->venue->eventDay->event;
                $participationsByEvent->push([
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'type' => 'speaker',
                    'title' => $presentation->title,
                    'date' => $presentation->programSession->venue->eventDay->date,
                    'venue' => $presentation->programSession->venue->display_name ?? $presentation->programSession->venue->name,
                    'speaker_role' => $presentation->pivot->speaker_role ?? 'primary',
                    'time' => $presentation->start_time && $presentation->end_time 
                        ? $presentation->start_time . ' - ' . $presentation->end_time 
                        : null,
                ]);
            }

            // Group by event and sort by date
            $groupedParticipations = $participationsByEvent
                ->groupBy('event_id')
                ->map(function ($items, $eventId) {
                    return [
                        'event_id' => $eventId,
                        'event_name' => $items->first()['event_name'],
                        'participations' => $items->sortByDesc('date')->values(),
                        'total_count' => $items->count(),
                        'moderator_count' => $items->where('type', 'moderator')->count(),
                        'speaker_count' => $items->where('type', 'speaker')->count(),
                    ];
                })
                ->sortByDesc(function ($item) {
                    return $item['participations']->max('date');
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'participant' => $participant,
                    'statistics' => $statistics,
                    'participations_by_event' => $groupedParticipations,
                    'recent_activities' => $participationsByEvent->sortByDesc('date')->take(10)->values(),
                    'permissions' => [
                        'can_edit' => auth()->user()->can('update', $participant),
                        'can_delete' => auth()->user()->can('delete', $participant),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Admin ParticipantController@show error: ' . $e->getMessage(), [
                'participant_id' => $participant->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Katılımcı detayları getirilirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified participant
     *
     * @OA\Put(
     *     path="/api/v1/admin/participants/{participant}",
     *     operationId="updateParticipant",
     *     tags={"Participants"},
     *     summary="Update participant",
     *     description="Updates the specified participant with provided data",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="participant",
     *         in="path",
     *         description="Participant ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="organization_id", type="integer", description="Organization ID"),
     *                 @OA\Property(property="first_name", type="string", maxLength=255, description="First name"),
     *                 @OA\Property(property="last_name", type="string", maxLength=255, description="Last name"),
     *                 @OA\Property(property="title", type="string", maxLength=255, nullable=true, description="Professional title"),
     *                 @OA\Property(property="email", type="string", format="email", maxLength=255, description="Email address"),
     *                 @OA\Property(property="affiliation", type="string", maxLength=500, nullable=true, description="Institutional affiliation"),
     *                 @OA\Property(property="bio", type="string", nullable=true, description="Biography"),
     *                 @OA\Property(property="photo", type="string", format="binary", description="Profile photo"),
     *                 @OA\Property(property="is_speaker", type="boolean", description="Can be a speaker"),
     *                 @OA\Property(property="is_moderator", type="boolean", description="Can be a moderator"),
     *                 @OA\Property(property="linkedin_url", type="string", format="url", nullable=true, description="LinkedIn profile URL"),
     *                 @OA\Property(property="twitter_url", type="string", format="url", nullable=true, description="Twitter profile URL"),
     *                 @OA\Property(property="website_url", type="string", format="url", nullable=true, description="Personal website URL")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participant updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Katılımcı başarıyla güncellendi."),
     *             @OA\Property(property="data", ref="#/components/schemas/Participant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Participant not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(UpdateParticipantRequest $request, Participant $participant): JsonResponse
    {
        $this->authorize('update', $participant);

        DB::beginTransaction();

        try {
            $data = $request->validated();
            $oldPhoto = $participant->photo;

            // Handle photo upload if provided
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('participants', 'public');
                
                // Delete old photo if it exists
                if ($oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }

            $participant->update($data);

            DB::commit();

            // Load relationships for response
            $participant->load(['organization']);

            return response()->json([
                'success' => true,
                'message' => 'Katılımcı başarıyla güncellendi.',
                'data' => $participant
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up newly uploaded file if exists
            if (isset($data['photo']) && $data['photo'] !== $oldPhoto) {
                Storage::disk('public')->delete($data['photo']);
            }

            Log::error('Admin ParticipantController@update error: ' . $e->getMessage(), [
                'participant_id' => $participant->id,
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Katılımcı güncellenirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified participant
     *
     * @OA\Delete(
     *     path="/api/v1/admin/participants/{participant}",
     *     operationId="deleteParticipant",
     *     tags={"Participants"},
     *     summary="Delete participant",
     *     description="Deletes the specified participant if they have no associated presentations or sessions",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="participant",
     *         in="path",
     *         description="Participant ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participant deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="'John Doe' katılımcısı başarıyla silindi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete participant with associated data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Sunum veya oturum moderatörlüğü olan katılımcı silinemez.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Participant not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(Participant $participant): JsonResponse
    {
        $this->authorize('delete', $participant);

        try {
            // Check if participant has presentations or moderated sessions
            if ($participant->presentations()->exists() || $participant->moderatedSessions()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sunum veya oturum moderatörlüğü olan katılımcı silinemez.'
                ], 422);
            }

            DB::beginTransaction();

            $participantName = trim($participant->first_name . ' ' . $participant->last_name);
            $oldPhoto = $participant->photo;

            $participant->delete();

            // Delete photo if it exists
            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "'{$participantName}' katılımcısı başarıyla silindi."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Admin ParticipantController@destroy error: ' . $e->getMessage(), [
                'participant_id' => $participant->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Katılımcı silinirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search participants for selection (autocomplete)
     *
     * @OA\Get(
     *     path="/api/v1/admin/participants/search",
     *     operationId="searchParticipants",
     *     tags={"Participants"},
     *     summary="Search participants",
     *     description="Search participants for autocomplete/selection purposes",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query (minimum 2 characters)",
     *         required=true,
     *         @OA\Schema(type="string", minLength=2, maxLength=100)
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by role",
     *         required=false,
     *         @OA\Schema(type="string", enum={"speaker", "moderator"})
     *     ),
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filter by organization ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ParticipantSearchResult")),
     *             @OA\Property(property="total", type="integer", description="Number of results returned")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'role' => 'nullable|in:speaker,moderator',
            'organization_id' => 'nullable|exists:organizations,id',
        ]);

        try {
            $user = auth()->user();
            $query = Participant::query();

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereIn('organization_id', $organizationIds);
            }

            // Filter by organization if specified
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Filter by role if specified
            if ($request->filled('role')) {
                if ($request->role === 'speaker') {
                    $query->where('is_speaker', true);
                } elseif ($request->role === 'moderator') {
                    $query->where('is_moderator', true);
                }
            }

            // Search by name, email, or affiliation
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('affiliation', 'like', "%{$searchTerm}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchTerm}%"]);
            });

            $participants = $query
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->limit(20)
                ->get(['id', 'first_name', 'last_name', 'title', 'affiliation', 'email', 'is_speaker', 'is_moderator'])
                ->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'name' => trim($participant->first_name . ' ' . $participant->last_name),
                        'title' => $participant->title,
                        'affiliation' => $participant->affiliation,
                        'email' => $participant->email,
                        'roles' => [
                            'speaker' => $participant->is_speaker,
                            'moderator' => $participant->is_moderator,
                        ],
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $participants,
                'total' => $participants->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Admin ParticipantController@search error: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Katılımcı arama sırasında bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import participants from CSV/Excel
     *
     * @OA\Post(
     *     path="/api/v1/admin/participants/import",
     *     operationId="importParticipants",
     *     tags={"Participants"},
     *     summary="Import participants from file",
     *     description="Import participants from CSV or Excel file with field mapping",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary", description="CSV or Excel file (max 10MB)"),
     *                 @OA\Property(property="organization_id", type="integer", description="Target organization ID"),
     *                 @OA\Property(property="mapping", type="object", description="Field mapping configuration",
     *                     @OA\Property(property="first_name", type="string", description="Column name for first name"),
     *                     @OA\Property(property="last_name", type="string", description="Column name for last name"),
     *                     @OA\Property(property="email", type="string", description="Column name for email")
     *                 ),
     *                 @OA\Property(property="update_existing", type="boolean", description="Update existing participants if found")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=501,
     *         description="Not implemented - redirect to ImportController",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Import functionality will be implemented in a dedicated ImportController.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
            'organization_id' => 'required|exists:organizations,id',
            'mapping' => 'required|array',
            'mapping.first_name' => 'required|string',
            'mapping.last_name' => 'required|string',
            'mapping.email' => 'required|string',
            'update_existing' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            // This would implement CSV/Excel import functionality
            // For now, return a placeholder response
            
            return response()->json([
                'success' => false,
                'message' => 'Import functionality will be implemented in a dedicated ImportController.'
            ], 501);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Admin ParticipantController@import error: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Katılımcı içe aktarma sırasında bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export participants to CSV/Excel
     *
     * @OA\Post(
     *     path="/api/v1/admin/participants/export",
     *     operationId="exportParticipants",
     *     tags={"Participants"},
     *     summary="Export participants to file",
     *     description="Export participants data to CSV or Excel format with customizable fields",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="format", type="string", enum={"csv", "xlsx"}, description="Export format"),
     *             @OA\Property(property="organization_id", type="integer", nullable=true, description="Filter by organization"),
     *             @OA\Property(property="role", type="string", enum={"speaker", "moderator", "both", "none"}, nullable=true, description="Filter by role"),
     *             @OA\Property(property="fields", type="array", description="Fields to include in export",
     *                 @OA\Items(type="string", enum={"name", "email", "title", "affiliation", "organization", "roles", "statistics"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=501,
     *         description="Not implemented - redirect to ExportController",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Export functionality will be implemented in a dedicated ExportController.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx',
            'organization_id' => 'nullable|exists:organizations,id',
            'role' => 'nullable|in:speaker,moderator,both,none',
            'fields' => 'array',
            'fields.*' => 'in:name,email,title,affiliation,organization,roles,statistics',
        ]);

        try {
            // This would implement export functionality
            // For now, return a placeholder response
            
            return response()->json([
                'success' => false,
                'message' => 'Export functionality will be implemented in a dedicated ExportController.'
            ], 501);

        } catch (\Exception $e) {
            Log::error('Admin ParticipantController@export error: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Katılımcı dışa aktarma sırasında bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get participant statistics
     *
     * @OA\Get(
     *     path="/api/v1/admin/participants/statistics",
     *     operationId="getParticipantStatistics",
     *     tags={"Participants"},
     *     summary="Get participant statistics",
     *     description="Returns comprehensive statistics about participants",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="Filter by organization ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ParticipantGlobalStatistics")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $query = Participant::query();

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereIn('organization_id', $organizationIds);
            }

            // Filter by organization if specified
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            $statistics = [
                'total_participants' => $query->count(),
                'speakers' => $query->where('is_speaker', true)->count(),
                'moderators' => $query->where('is_moderator', true)->count(),
                'both_roles' => $query->where('is_speaker', true)->where('is_moderator', true)->count(),
                'no_role' => $query->where('is_speaker', false)->where('is_moderator', false)->count(),
                'by_organization' => $query->with('organization')
                    ->get()
                    ->groupBy('organization.name')
                    ->map(function ($participants, $orgName) {
                        return [
                            'organization' => $orgName ?? 'Bilinmeyen',
                            'total' => $participants->count(),
                            'speakers' => $participants->where('is_speaker', true)->count(),
                            'moderators' => $participants->where('is_moderator', true)->count(),
                        ];
                    })
                    ->values(),
                'top_affiliations' => $query->selectRaw('affiliation, COUNT(*) as count')
                    ->whereNotNull('affiliation')
                    ->groupBy('affiliation')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->pluck('count', 'affiliation'),
                'recent_additions' => $query->latest()
                    ->limit(5)
                    ->get(['id', 'first_name', 'last_name', 'created_at'])
                    ->map(function ($participant) {
                        return [
                            'id' => $participant->id,
                            'name' => trim($participant->first_name . ' ' . $participant->last_name),
                            'created_at' => $participant->created_at,
                        ];
                    }),
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Admin ParticipantController@statistics error: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'İstatistikler getirilirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk operations on participants
     *
     * @OA\Post(
     *     path="/api/v1/admin/participants/bulk",
     *     operationId="bulkParticipantOperations",
     *     tags={"Participants"},
     *     summary="Perform bulk operations on participants",
     *     description="Perform various bulk operations like delete, update roles, or change organization on multiple participants",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="action", type="string", enum={"delete", "update_roles", "change_organization", "export"}, description="Bulk action to perform"),
     *             @OA\Property(property="participant_ids", type="array", description="Array of participant IDs", @OA\Items(type="integer"), minItems=1),
     *             @OA\Property(property="is_speaker", type="boolean", nullable=true, description="Speaker role (for update_roles action)"),
     *             @OA\Property(property="is_moderator", type="boolean", nullable=true, description="Moderator role (for update_roles action)"),
     *             @OA\Property(property="organization_id", type="integer", nullable=true, description="Target organization ID (for change_organization action)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bulk operation completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="5 katılımcının rolleri güncellendi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or operation not allowed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Sunum veya oturum moderatörlüğü olan katılımcılar silinemez.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied to some participants",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bazı katılımcılara erişim yetkiniz yok.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:delete,update_roles,change_organization,export',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:participants,id',
            'is_speaker' => 'boolean',
            'is_moderator' => 'boolean',
            'organization_id' => 'nullable|exists:organizations,id',
        ]);

        DB::beginTransaction();

        try {
            $user = auth()->user();
            
            $query = Participant::whereIn('id', $request->participant_ids);

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereIn('organization_id', $organizationIds);
            }

            $participants = $query->get();

            if ($participants->count() !== count($request->participant_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bazı katılımcılara erişim yetkiniz yok.'
                ], 403);
            }

            $result = [];

            switch ($request->action) {
                case 'delete':
                    // Check if any participant has presentations or sessions
                    $hasParticipations = $participants->filter(function ($participant) {
                        return $participant->presentations()->exists() || $participant->moderatedSessions()->exists();
                    });

                    if ($hasParticipations->count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sunum veya oturum moderatörlüğü olan katılımcılar silinemez.'
                        ], 422);
                    }

                    $participants->each->delete();
                    $result['message'] = count($request->participant_ids) . ' katılımcı silindi.';
                    break;

                case 'update_roles':
                    $updateData = [];
                    if ($request->has('is_speaker')) {
                        $updateData['is_speaker'] = $request->boolean('is_speaker');
                    }
                    if ($request->has('is_moderator')) {
                        $updateData['is_moderator'] = $request->boolean('is_moderator');
                    }
                    
                    if (!empty($updateData)) {
                        Participant::whereIn('id', $request->participant_ids)->update($updateData);
                        $result['message'] = count($request->participant_ids) . ' katılımcının rolleri güncellendi.';
                    }
                    break;

                case 'change_organization':
                    if (!$request->organization_id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Organizasyon seçimi zorunludur.'
                        ], 422);
                    }

                    Participant::whereIn('id', $request->participant_ids)
                        ->update(['organization_id' => $request->organization_id]);
                    $result['message'] = count($request->participant_ids) . ' katılımcının organizasyonu değiştirildi.';
                    break;

                case 'export':
                    // Export functionality would be implemented here
                    $result['message'] = 'Export functionality will be implemented.';
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Admin ParticipantController@bulk error: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Toplu işlem gerçekleştirilirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available options for creating/editing participants
     *
     * @OA\Get(
     *     path="/api/v1/admin/participants/options",
     *     operationId="getParticipantOptions",
     *     tags={"Participants"},
     *     summary="Get participant form options",
     *     description="Returns available options for participant forms (organizations, titles, etc.)",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="organizations", type="array", @OA\Items(ref="#/components/schemas/Organization")),
     *                 @OA\Property(property="titles", type="array", @OA\Items(type="string"), description="Common professional titles")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function options(): JsonResponse
    {
        try {
            $user = auth()->user();

            // Get available organizations
            $organizations = $user->isAdmin()
                ? Organization::active()->orderBy('name')->get(['id', 'name'])
                : $user->organizations()->orderBy('name')->get(['id', 'name']);

            // Common titles
            $titles = [
                'Prof. Dr.',
                'Doç. Dr.',
                'Dr.',
                'Dr. Öğr. Üyesi',
                'Öğr. Gör.',
                'Arş. Gör.',
                'Uzm. Dr.',
                'Hemşire',
                'Ebe',
                'Fizyoterapist',
                'Diyetisyen',
                'Psikolog',
                'Öğrenci',
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'organizations' => $organizations,
                    'titles' => $titles,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Admin ParticipantController@options error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Seçenekler getirilirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

/**
 * @OA\Schema(
 *     schema="Participant",
 *     type="object",
 *     title="Participant",
 *     description="Participant model",
 *     @OA\Property(property="id", type="integer", description="Participant ID"),
 *     @OA\Property(property="organization_id", type="integer", description="Organization ID"),
 *     @OA\Property(property="first_name", type="string", description="First name"),
 *     @OA\Property(property="last_name", type="string", description="Last name"),
 *     @OA\Property(property="title", type="string", nullable=true, description="Professional title"),
 *     @OA\Property(property="email", type="string", format="email", description="Email address"),
 *     @OA\Property(property="affiliation", type="string", nullable=true, description="Institutional affiliation"),
 *     @OA\Property(property="bio", type="string", nullable=true, description="Biography"),
 *     @OA\Property(property="photo", type="string", nullable=true, description="Profile photo path"),
 *     @OA\Property(property="is_speaker", type="boolean", description="Can be a speaker"),
 *     @OA\Property(property="is_moderator", type="boolean", description="Can be a moderator"),
 *     @OA\Property(property="linkedin_url", type="string", nullable=true, description="LinkedIn profile URL"),
 *     @OA\Property(property="twitter_url", type="string", nullable=true, description="Twitter profile URL"),
 *     @OA\Property(property="website_url", type="string", nullable=true, description="Personal website URL"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Update date"),
 *     @OA\Property(property="organization", ref="#/components/schemas/Organization", description="Related organization")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantWithStats",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Participant"),
 *         @OA\Schema(
 *             @OA\Property(property="presentations_count", type="integer", description="Number of presentations"),
 *             @OA\Property(property="moderated_sessions_count", type="integer", description="Number of moderated sessions"),
 *             @OA\Property(property="full_name", type="string", description="Full name (first + last)"),
 *             @OA\Property(property="total_participations", type="integer", description="Total participations (presentations + sessions)"),
 *             @OA\Property(property="role_badge", type="array", @OA\Items(type="string"), description="Role badges array")
 *         )
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantDetail",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Participant"),
 *         @OA\Schema(
 *             @OA\Property(property="moderated_sessions", type="array", @OA\Items(ref="#/components/schemas/ProgramSession")),
 *             @OA\Property(property="presentations", type="array", @OA\Items(ref="#/components/schemas/Presentation"))
 *         )
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantStatistics",
 *     type="object",
 *     title="Participant Statistics",
 *     description="Detailed statistics for a specific participant",
 *     @OA\Property(property="total_sessions_moderated", type="integer", description="Total sessions moderated"),
 *     @OA\Property(property="total_presentations", type="integer", description="Total presentations"),
 *     @OA\Property(property="primary_presentations", type="integer", description="Primary speaker presentations"),
 *     @OA\Property(property="co_speaker_presentations", type="integer", description="Co-speaker presentations"),
 *     @OA\Property(property="discussant_presentations", type="integer", description="Discussant presentations"),
 *     @OA\Property(property="total_participations", type="integer", description="Total participations")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipationsByEvent",
 *     type="object",
 *     title="Participations by Event",
 *     description="Participant's activities grouped by event",
 *     @OA\Property(property="event_id", type="integer", description="Event ID"),
 *     @OA\Property(property="event_name", type="string", description="Event name"),
 *     @OA\Property(property="participations", type="array", @OA\Items(ref="#/components/schemas/ParticipantActivity")),
 *     @OA\Property(property="total_count", type="integer", description="Total participations in event"),
 *     @OA\Property(property="moderator_count", type="integer", description="Moderator activities count"),
 *     @OA\Property(property="speaker_count", type="integer", description="Speaker activities count")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantActivity",
 *     type="object",
 *     title="Participant Activity",
 *     description="Single participant activity (presentation or session)",
 *     @OA\Property(property="event_id", type="integer", description="Event ID"),
 *     @OA\Property(property="event_name", type="string", description="Event name"),
 *     @OA\Property(property="type", type="string", enum={"speaker", "moderator"}, description="Activity type"),
 *     @OA\Property(property="title", type="string", description="Session or presentation title"),
 *     @OA\Property(property="date", type="string", format="date", description="Activity date"),
 *     @OA\Property(property="venue", type="string", description="Venue name"),
 *     @OA\Property(property="time", type="string", nullable=true, description="Time range"),
 *     @OA\Property(property="speaker_role", type="string", nullable=true, enum={"primary", "co_speaker", "discussant"}, description="Speaker role (for speaker activities)")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantPermissions",
 *     type="object",
 *     title="Participant Permissions",
 *     description="User permissions for participant",
 *     @OA\Property(property="can_edit", type="boolean", description="User can edit this participant"),
 *     @OA\Property(property="can_delete", type="boolean", description="User can delete this participant")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantSearchResult",
 *     type="object",
 *     title="Participant Search Result",
 *     description="Participant data for search/autocomplete results",
 *     @OA\Property(property="id", type="integer", description="Participant ID"),
 *     @OA\Property(property="name", type="string", description="Full name"),
 *     @OA\Property(property="title", type="string", nullable=true, description="Professional title"),
 *     @OA\Property(property="affiliation", type="string", nullable=true, description="Institutional affiliation"),
 *     @OA\Property(property="email", type="string", description="Email address"),
 *     @OA\Property(property="roles", type="object",
 *         @OA\Property(property="speaker", type="boolean", description="Can be a speaker"),
 *         @OA\Property(property="moderator", type="boolean", description="Can be a moderator")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantGlobalStatistics",
 *     type="object",
 *     title="Global Participant Statistics",
 *     description="Comprehensive participant statistics across the system",
 *     @OA\Property(property="total_participants", type="integer", description="Total number of participants"),
 *     @OA\Property(property="speakers", type="integer", description="Number of speakers"),
 *     @OA\Property(property="moderators", type="integer", description="Number of moderators"),
 *     @OA\Property(property="both_roles", type="integer", description="Participants with both roles"),
 *     @OA\Property(property="no_role", type="integer", description="Participants with no assigned role"),
 *     @OA\Property(property="by_organization", type="array", @OA\Items(ref="#/components/schemas/ParticipantOrganizationStats")),
 *     @OA\Property(property="top_affiliations", type="object", description="Top affiliations with participant counts"),
 *     @OA\Property(property="recent_additions", type="array", @OA\Items(ref="#/components/schemas/RecentParticipant"))
 * )
 */

/**
 * @OA\Schema(
 *     schema="ParticipantOrganizationStats",
 *     type="object",
 *     title="Participant Organization Statistics",
 *     description="Participant statistics by organization",
 *     @OA\Property(property="organization", type="string", description="Organization name"),
 *     @OA\Property(property="total", type="integer", description="Total participants"),
 *     @OA\Property(property="speakers", type="integer", description="Number of speakers"),
 *     @OA\Property(property="moderators", type="integer", description="Number of moderators")
 * )
 */

/**
 * @OA\Schema(
 *     schema="RecentParticipant",
 *     type="object",
 *     title="Recent Participant",
 *     description="Recently added participant summary",
 *     @OA\Property(property="id", type="integer", description="Participant ID"),
 *     @OA\Property(property="name", type="string", description="Full name"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date")
 * )
 */

/**
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     title="Pagination Meta",
 *     description="Pagination metadata",
 *     @OA\Property(property="current_page", type="integer", description="Current page number"),
 *     @OA\Property(property="per_page", type="integer", description="Items per page"),
 *     @OA\Property(property="total", type="integer", description="Total items"),
 *     @OA\Property(property="last_page", type="integer", description="Last page number"),
 *     @OA\Property(property="from", type="integer", nullable=true, description="First item number on current page"),
 *     @OA\Property(property="to", type="integer", nullable=true, description="Last item number on current page")
 * )
 */

/**
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     title="Pagination Links",
 *     description="Pagination navigation links",
 *     @OA\Property(property="first", type="string", description="First page URL"),
 *     @OA\Property(property="last", type="string", description="Last page URL"),
 *     @OA\Property(property="prev", type="string", nullable=true, description="Previous page URL"),
 *     @OA\Property(property="next", type="string", nullable=true, description="Next page URL")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="Error Response",
 *     description="Standard error response format",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", description="Error message"),
 *     @OA\Property(property="error", type="string", nullable=true, description="Detailed error information")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     title="Validation Error Response",
 *     description="Validation error response format",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", description="Main error message"),
 *     @OA\Property(property="errors", type="object", description="Field-specific validation errors")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Organization",
 *     type="object",
 *     title="Organization",
 *     description="Organization model",
 *     @OA\Property(property="id", type="integer", description="Organization ID"),
 *     @OA\Property(property="name", type="string", description="Organization name")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProgramSession",
 *     type="object",
 *     title="Program Session",
 *     description="Program session model",
 *     @OA\Property(property="id", type="integer", description="Session ID"),
 *     @OA\Property(property="title", type="string", description="Session title"),
 *     @OA\Property(property="start_time", type="string", format="time", description="Start time"),
 *     @OA\Property(property="end_time", type="string", format="time", description="End time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Presentation",
 *     type="object",
 *     title="Presentation",
 *     description="Presentation model",
 *     @OA\Property(property="id", type="integer", description="Presentation ID"),
 *     @OA\Property(property="title", type="string", description="Presentation title"),
 *     @OA\Property(property="start_time", type="string", format="time", description="Start time"),
 *     @OA\Property(property="end_time", type="string", format="time", description="End time")
 * )