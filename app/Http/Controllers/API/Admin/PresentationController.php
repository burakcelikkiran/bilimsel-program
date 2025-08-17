<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Presentations",
 *     description="Presentation management endpoints"
 * )
 */
class PresentationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/program-sessions/{session}/presentations",
     *     summary="List presentations for a session",
     *     description="Get a paginated list of presentations for a specific program session",
     *     operationId="listPresentations",
     *     tags={"Presentations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="session",
     *         in="path",
     *         required=true,
     *         description="Program session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by presentation status",
     *         @OA\Schema(
     *             type="string",
     *             enum={"draft", "confirmed", "cancelled"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Filter by presentation language",
     *         @OA\Schema(
     *             type="string",
     *             enum={"tr", "en", "de", "fr", "es", "it"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="has_speakers",
     *         in="query",
     *         description="Filter by speaker availability",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in title, description, and abstract",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(
     *             type="string",
     *             enum={"sort_order", "title", "duration", "created_at", "speakers_count"},
     *             default="sort_order"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(
     *             type="string",
     *             enum={"asc", "desc"},
     *             default="asc"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (max 100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Presentation")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="session",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="start_time", type="string", format="datetime"),
     *                 @OA\Property(property="end_time", type="string", format="datetime"),
     *                 @OA\Property(
     *                     property="venue",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="display_name", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have access to this session's organization",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Session not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(ProgramSession $session, Request $request): JsonResponse
    {
        try {
            // Verify user has access to this session's organization
            $this->authorize('view', $session);
            
            $query = $session->presentations()
                ->with([
                    'speakers:id,first_name,last_name,email,organization_name,job_title',
                    'sponsor:id,name,logo_url,website_url'
                ])
                ->withCount('speakers');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('language')) {
                $query->where('language', $request->language);
            }

            if ($request->filled('has_speakers')) {
                if ($request->boolean('has_speakers')) {
                    $query->has('speakers');
                } else {
                    $query->doesntHave('speakers');
                }
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('abstract', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            $allowedSorts = ['sort_order', 'title', 'duration', 'created_at', 'speakers_count'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 100);
            $presentations = $query->paginate($perPage);

            // Transform data
            $presentations->getCollection()->transform(function ($presentation) {
                return $this->transformPresentation($presentation);
            });

            return response()->json([
                'success' => true,
                'data' => $presentations,
                'session' => [
                    'id' => $session->id,
                    'title' => $session->title,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'venue' => $session->venue ? [
                        'id' => $session->venue->id,
                        'display_name' => $session->venue->display_name
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Presentation listing failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sunumlar yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/program-sessions/{session}/presentations",
     *     summary="Create a new presentation",
     *     description="Create a new presentation for a specific program session",
     *     operationId="createPresentation",
     *     tags={"Presentations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="session",
     *         in="path",
     *         required=true,
     *         description="Program session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PresentationRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Presentation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sunum başarıyla oluşturuldu."),
     *             @OA\Property(property="data", ref="#/components/schemas/Presentation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Girilen bilgiler geçersiz."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="title",
     *                     type="array",
     *                     @OA\Items(type="string", example="Sunum başlığı zorunludur.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission to create presentations",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(ProgramSession $session, Request $request): JsonResponse
    {
        try {
            // Verify user has access to this session's organization
            $this->authorize('create', [Presentation::class, $session]);
            
            // Validate request data
            $validated = $this->validatePresentationData($request, $session);

            DB::beginTransaction();

            // Create presentation
            $presentation = new Presentation($validated);
            $presentation->program_session_id = $session->id;
            
            // Set sort order if not provided
            if (!isset($validated['sort_order'])) {
                $maxOrder = $session->presentations()->max('sort_order') ?? 0;
                $presentation->sort_order = $maxOrder + 1;
            }

            $presentation->save();

            // Attach speakers if provided
            if (!empty($validated['speakers'])) {
                $this->attachSpeakers($presentation, $validated['speakers']);
            }

            // Reload with relationships
            $presentation->load([
                'speakers:id,first_name,last_name,email,organization_name,job_title',
                'sponsor:id,name,logo_url,website_url'
            ]);

            DB::commit();

            Log::info('Presentation created successfully', [
                'presentation_id' => $presentation->id,
                'session_id' => $session->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sunum başarıyla oluşturuldu.',
                'data' => $this->transformPresentation($presentation)
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Girilen bilgiler geçersiz.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Presentation creation failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sunum oluşturulurken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/program-sessions/{session}/presentations/{presentation}",
     *     summary="Get a specific presentation",
     *     description="Retrieve detailed information about a specific presentation",
     *     operationId="getPresentation",
     *     tags={"Presentations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="session",
     *         in="path",
     *         required=true,
     *         description="Program session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="presentation",
     *         in="path",
     *         required=true,
     *         description="Presentation ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PresentationDetailed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Presentation not found or doesn't belong to this session",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have access to this presentation",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(ProgramSession $session, Presentation $presentation): JsonResponse
    {
        try {
            // Verify presentation belongs to session
            if ($presentation->program_session_id !== $session->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sunum bu oturuma ait değil.'
                ], 404);
            }

            // Verify user has access
            $this->authorize('view', $presentation);

            // Load with relationships
            $presentation->load([
                'speakers:id,first_name,last_name,email,organization_name,job_title,phone,bio',
                'sponsor:id,name,logo_url,website_url,description',
                'programSession.venue.eventDay.event:id,name,slug'
            ]);

            return response()->json([
                'success' => true,
                'data' => $this->transformPresentationDetailed($presentation)
            ]);

        } catch (\Exception $e) {
            Log::error('Presentation show failed', [
                'presentation_id' => $presentation->id,
                'session_id' => $session->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sunum yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/program-sessions/{session}/presentations/{presentation}",
     *     summary="Update a presentation",
     *     description="Update an existing presentation",
     *     operationId="updatePresentation",
     *     tags={"Presentations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="session",
     *         in="path",
     *         required=true,
     *         description="Program session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="presentation",
     *         in="path",
     *         required=true,
     *         description="Presentation ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PresentationRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Presentation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sunum başarıyla güncellendi."),
     *             @OA\Property(property="data", ref="#/components/schemas/Presentation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Girilen bilgiler geçersiz."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Presentation not found or doesn't belong to this session",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission to update this presentation",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(ProgramSession $session, Presentation $presentation, Request $request): JsonResponse
    {
        try {
            // Verify presentation belongs to session
            if ($presentation->program_session_id !== $session->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sunum bu oturuma ait değil.'
                ], 404);
            }

            // Verify user has access
            $this->authorize('update', $presentation);

            // Validate request data
            $validated = $this->validatePresentationData($request, $session, $presentation->id);

            DB::beginTransaction();

            // Update presentation
            $presentation->update($validated);

            // Update speakers if provided
            if (array_key_exists('speakers', $validated)) {
                $this->syncSpeakers($presentation, $validated['speakers'] ?? []);
            }

            // Reload with relationships
            $presentation->load([
                'speakers:id,first_name,last_name,email,organization_name,job_title',
                'sponsor:id,name,logo_url,website_url'
            ]);

            DB::commit();

            Log::info('Presentation updated successfully', [
                'presentation_id' => $presentation->id,
                'session_id' => $session->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sunum başarıyla güncellendi.',
                'data' => $this->transformPresentation($presentation)
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Girilen bilgiler geçersiz.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Presentation update failed', [
                'presentation_id' => $presentation->id,
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sunum güncellenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/program-sessions/{session}/presentations/{presentation}",
     *     summary="Delete a presentation",
     *     description="Delete an existing presentation",
     *     operationId="deletePresentation",
     *     tags={"Presentations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="session",
     *         in="path",
     *         required=true,
     *         description="Program session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="presentation",
     *         in="path",
     *         required=true,
     *         description="Presentation ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Presentation deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sunum 'Presentation Title' başarıyla silindi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Presentation not found or doesn't belong to this session",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission to delete this presentation",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(ProgramSession $session, Presentation $presentation): JsonResponse
    {
        try {
            // Verify presentation belongs to session
            if ($presentation->program_session_id !== $session->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sunum bu oturuma ait değil.'
                ], 404);
            }

            // Verify user has access
            $this->authorize('delete', $presentation);

            $presentationTitle = $presentation->title;

            DB::beginTransaction();

            // Detach all speakers
            $presentation->speakers()->detach();

            // Delete presentation
            $presentation->delete();

            DB::commit();

            Log::info('Presentation deleted successfully', [
                'presentation_id' => $presentation->id,
                'session_id' => $session->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Sunum '{$presentationTitle}' başarıyla silindi."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Presentation deletion failed', [
                'presentation_id' => $presentation->id,
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sunum silinirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Validate presentation data
     *
     * @param Request $request
     * @param ProgramSession $session
     * @param int|null $presentationId
     * @return array
     * @throws ValidationException
     */
    private function validatePresentationData(Request $request, ProgramSession $session, ?int $presentationId = null): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'abstract' => 'nullable|string|max:2000',
            'duration' => 'nullable|integer|min:1|max:480', // max 8 hours
            'language' => 'nullable|string|in:tr,en,de,fr,es,it',
            'status' => 'nullable|string|in:draft,confirmed,cancelled',
            'sort_order' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'sponsor_id' => 'nullable|exists:sponsors,id',
            'speakers' => 'nullable|array|max:10',
            'speakers.*.participant_id' => 'required|exists:participants,id',
            'speakers.*.role' => 'nullable|string|in:primary,secondary,moderator',
            'speakers.*.sort_order' => 'nullable|integer|min:0'
        ];

        $messages = [
            'title.required' => 'Sunum başlığı zorunludur.',
            'title.max' => 'Sunum başlığı en fazla 255 karakter olabilir.',
            'description.max' => 'Açıklama en fazla 1000 karakter olabilir.',
            'abstract.max' => 'Özet en fazla 2000 karakter olabilir.',
            'duration.integer' => 'Süre dakika cinsinden sayı olmalıdır.',
            'duration.min' => 'Süre en az 1 dakika olmalıdır.',
            'duration.max' => 'Süre en fazla 480 dakika (8 saat) olabilir.',
            'language.in' => 'Geçerli bir dil seçiniz.',
            'status.in' => 'Geçerli bir durum seçiniz.',
            'sponsor_id.exists' => 'Seçilen sponsor geçersiz.',
            'speakers.array' => 'Konuşmacılar bir dizi olmalıdır.',
            'speakers.max' => 'En fazla 10 konuşmacı ekleyebilirsiniz.',
            'speakers.*.participant_id.required' => 'Konuşmacı seçimi zorunludur.',
            'speakers.*.participant_id.exists' => 'Seçilen konuşmacı geçersiz.',
            'speakers.*.role.in' => 'Geçerli bir konuşmacı rolü seçiniz.',
            'speakers.*.sort_order.integer' => 'Konuşmacı sıralaması sayı olmalıdır.',
            'speakers.*.sort_order.min' => 'Konuşmacı sıralaması 0 veya daha büyük olmalıdır.'
        ];

        $validated = $request->validate($rules, $messages);

        // Additional validations
        if (isset($validated['speakers'])) {
            $participantIds = array_column($validated['speakers'], 'participant_id');
            if (count($participantIds) !== count(array_unique($participantIds))) {
                throw ValidationException::withMessages([
                    'speakers' => 'Aynı katılımcı birden fazla kez eklenemez.'
                ]);
            }

            // Verify speakers belong to the same organization as the session
            $sessionEvent = $session->venue->eventDay->event;
            $invalidSpeakers = Participant::whereIn('id', $participantIds)
                ->where('organization_id', '!=', $sessionEvent->organization_id)
                ->exists();

            if ($invalidSpeakers) {
                throw ValidationException::withMessages([
                    'speakers' => 'Bazı konuşmacılar bu etkinliğin organizasyonuna ait değil.'
                ]);
            }
        }

        // Verify sponsor belongs to the same organization if provided
        if (isset($validated['sponsor_id'])) {
            $sessionEvent = $session->venue->eventDay->event;
            $sponsor = \App\Models\Sponsor::find($validated['sponsor_id']);
            if ($sponsor && $sponsor->organization_id !== $sessionEvent->organization_id) {
                throw ValidationException::withMessages([
                    'sponsor_id' => 'Seçilen sponsor bu etkinliğin organizasyonuna ait değil.'
                ]);
            }
        }

        return $validated;
    }

    /**
     * Transform presentation for API response
     *
     * @param Presentation $presentation
     * @return array
     */
    private function transformPresentation(Presentation $presentation): array
    {
        return [
            'id' => $presentation->id,
            'title' => $presentation->title,
            'description' => $presentation->description,
            'abstract' => $presentation->abstract,
            'duration' => $presentation->duration,
            'language' => $presentation->language,
            'status' => $presentation->status ?? 'draft',
            'sort_order' => $presentation->sort_order ?? 0,
            'notes' => $presentation->notes,
            'sponsor' => $presentation->sponsor ? [
                'id' => $presentation->sponsor->id,
                'name' => $presentation->sponsor->name,
                'logo_url' => $presentation->sponsor->logo_url,
                'website_url' => $presentation->sponsor->website_url,
            ] : null,
            'speakers' => $presentation->speakers->map(function ($speaker) {
                return [
                    'id' => $speaker->id,
                    'first_name' => $speaker->first_name,
                    'last_name' => $speaker->last_name,
                    'full_name' => trim($speaker->first_name . ' ' . $speaker->last_name),
                    'title' => $speaker->title,
                    'organization_name' => $speaker->organization_name,
                    'job_title' => $speaker->job_title,
                    'email' => $speaker->email,
                    'role' => $speaker->pivot->speaker_role ?? 'primary',
                    'sort_order' => $speaker->pivot->sort_order ?? 0,
                ];
            }),
            'speakers_count' => $presentation->speakers_count ?? $presentation->speakers->count(),
            'permissions' => [
                'can_edit' => auth()->user()?->can('update', $presentation) ?? false,
                'can_delete' => auth()->user()?->can('delete', $presentation) ?? false,
            ],
            'created_at' => $presentation->created_at,
            'updated_at' => $presentation->updated_at,
        ];
    }

    /**
     * Transform presentation with detailed information
     *
     * @param Presentation $presentation
     * @return array
     */
    private function transformPresentationDetailed(Presentation $presentation): array
    {
        $basic = $this->transformPresentation($presentation);
        
        return array_merge($basic, [
            'program_session' => $presentation->programSession ? [
                'id' => $presentation->programSession->id,
                'title' => $presentation->programSession->title,
                'start_time' => $presentation->programSession->start_time,
                'end_time' => $presentation->programSession->end_time,
                'session_type' => $presentation->programSession->session_type,
                'venue' => $presentation->programSession->venue ? [
                    'id' => $presentation->programSession->venue->id,
                    'display_name' => $presentation->programSession->venue->display_name,
                    'event_day' => $presentation->programSession->venue->eventDay ? [
                        'id' => $presentation->programSession->venue->eventDay->id,
                        'display_name' => $presentation->programSession->venue->eventDay->display_name,
                        'date' => $presentation->programSession->venue->eventDay->date,
                        'event' => $presentation->programSession->venue->eventDay->event ? [
                            'id' => $presentation->programSession->venue->eventDay->event->id,
                            'name' => $presentation->programSession->venue->eventDay->event->name,
                            'slug' => $presentation->programSession->venue->eventDay->event->slug,
                        ] : null
                    ] : null
                ] : null
            ] : null,
            'speakers' => $presentation->speakers->map(function ($speaker) {
                return [
                    'id' => $speaker->id,
                    'first_name' => $speaker->first_name,
                    'last_name' => $speaker->last_name,
                    'full_name' => trim($speaker->first_name . ' ' . $speaker->last_name),
                    'title' => $speaker->title,
                    'organization_name' => $speaker->organization_name,
                    'job_title' => $speaker->job_title,
                    'email' => $speaker->email,
                    'phone' => $speaker->phone,
                    'bio' => $speaker->bio,
                    'role' => $speaker->pivot->speaker_role ?? 'primary',
                    'sort_order' => $speaker->pivot->sort_order ?? 0,
                ];
            }),
        ]);
    }

    /**
     * Attach speakers to presentation
     *
     * @param Presentation $presentation
     * @param array $speakers
     * @return void
     */
    private function attachSpeakers(Presentation $presentation, array $speakers): void
    {
        $speakerData = [];
        foreach ($speakers as $speaker) {
            $speakerData[$speaker['participant_id']] = [
                'speaker_role' => $speaker['role'] ?? 'primary',
                'sort_order' => $speaker['sort_order'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $presentation->speakers()->attach($speakerData);
    }

    /**
     * Sync speakers to presentation
     *
     * @param Presentation $presentation
     * @param array $speakers
     * @return void
     */
    private function syncSpeakers(Presentation $presentation, array $speakers): void
    {
        $speakerData = [];
        foreach ($speakers as $speaker) {
            $speakerData[$speaker['participant_id']] = [
                'speaker_role' => $speaker['role'] ?? 'primary',
                'sort_order' => $speaker['sort_order'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $presentation->speakers()->sync($speakerData);
    }
}

/**
 * @OA\Schema(
 *     schema="PresentationRequest",
 *     type="object",
 *     required={"title"},
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         maxLength=255,
 *         description="Presentation title",
 *         example="Introduction to Machine Learning"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         maxLength=1000,
 *         nullable=true,
 *         description="Presentation description",
 *         example="A comprehensive introduction to machine learning concepts and applications"
 *     ),
 *     @OA\Property(
 *         property="abstract",
 *         type="string",
 *         maxLength=2000,
 *         nullable=true,
 *         description="Presentation abstract",
 *         example="This presentation covers the fundamental concepts of machine learning..."
 *     ),
 *     @OA\Property(
 *         property="duration",
 *         type="integer",
 *         minimum=1,
 *         maximum=480,
 *         nullable=true,
 *         description="Duration in minutes",
 *         example=45
 *     ),
 *     @OA\Property(
 *         property="language",
 *         type="string",
 *         enum={"tr", "en", "de", "fr", "es", "it"},
 *         nullable=true,
 *         description="Presentation language",
 *         example="en"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"draft", "confirmed", "cancelled"},
 *         nullable=true,
 *         description="Presentation status",
 *         example="confirmed"
 *     ),
 *     @OA\Property(
 *         property="sort_order",
 *         type="integer",
 *         minimum=0,
 *         nullable=true,
 *         description="Sort order within session",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="notes",
 *         type="string",
 *         maxLength=1000,
 *         nullable=true,
 *         description="Internal notes",
 *         example="Special equipment needed"
 *     ),
 *     @OA\Property(
 *         property="sponsor_id",
 *         type="integer",
 *         nullable=true,
 *         description="Sponsor ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="speakers",
 *         type="array",
 *         maxItems=10,
 *         nullable=true,
 *         description="Presentation speakers",
 *         @OA\Items(
 *             type="object",
 *             required={"participant_id"},
 *             @OA\Property(
 *                 property="participant_id",
 *                 type="integer",
 *                 description="Participant ID",
 *                 example=1
 *             ),
 *             @OA\Property(
 *                 property="role",
 *                 type="string",
 *                 enum={"primary", "secondary", "moderator"},
 *                 nullable=true,
 *                 description="Speaker role",
 *                 example="primary"
 *             ),
 *             @OA\Property(
 *                 property="sort_order",
 *                 type="integer",
 *                 minimum=0,
 *                 nullable=true,
 *                 description="Speaker sort order",
 *                 example=0
 *             )
 *         )
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="Presentation",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Introduction to Machine Learning"),
 *     @OA\Property(property="description", type="string", nullable=true, example="A comprehensive introduction to machine learning concepts"),
 *     @OA\Property(property="abstract", type="string", nullable=true, example="This presentation covers..."),
 *     @OA\Property(property="duration", type="integer", nullable=true, example=45),
 *     @OA\Property(property="language", type="string", nullable=true, example="en"),
 *     @OA\Property(property="status", type="string", example="confirmed"),
 *     @OA\Property(property="sort_order", type="integer", example=1),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Special equipment needed"),
 *     @OA\Property(
 *         property="sponsor",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="TechCorp"),
 *         @OA\Property(property="logo_url", type="string", nullable=true, example="https://example.com/logo.png"),
 *         @OA\Property(property="website_url", type="string", nullable=true, example="https://techcorp.com")
 *     ),
 *     @OA\Property(
 *         property="speakers",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="first_name", type="string", example="John"),
 *             @OA\Property(property="last_name", type="string", example="Doe"),
 *             @OA\Property(property="full_name", type="string", example="John Doe"),
 *             @OA\Property(property="title", type="string", nullable=true, example="Dr."),
 *             @OA\Property(property="organization_name", type="string", nullable=true, example="TechCorp"),
 *             @OA\Property(property="job_title", type="string", nullable=true, example="Senior Engineer"),
 *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *             @OA\Property(property="role", type="string", example="primary"),
 *             @OA\Property(property="sort_order", type="integer", example=0)
 *         )
 *     ),
 *     @OA\Property(property="speakers_count", type="integer", example=1),
 *     @OA\Property(
 *         property="permissions",
 *         type="object",
 *         @OA\Property(property="can_edit", type="boolean", example=true),
 *         @OA\Property(property="can_delete", type="boolean", example=true)
 *     ),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2024-01-01T00:00:00Z")
 * )
 */

/**
 * @OA\Schema(
 *     schema="PresentationDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Presentation"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="program_session",
 *                 type="object",
 *                 nullable=true,
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="title", type="string", example="Opening Session"),
 *                 @OA\Property(property="start_time", type="string", format="datetime", example="2024-01-01T09:00:00Z"),
 *                 @OA\Property(property="end_time", type="string", format="datetime", example="2024-01-01T10:30:00Z"),
 *                 @OA\Property(property="session_type", type="string", example="keynote"),
 *                 @OA\Property(
 *                     property="venue",
 *                     type="object",
 *                     nullable=true,
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="display_name", type="string", example="Main Hall"),
 *                     @OA\Property(
 *                         property="event_day",
 *                         type="object",
 *                         nullable=true,
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="display_name", type="string", example="Day 1"),
 *                         @OA\Property(property="date", type="string", format="date", example="2024-01-01"),
 *                         @OA\Property(
 *                             property="event",
 *                             type="object",
 *                             nullable=true,
 *                             @OA\Property(property="id", type="integer", example=1),
 *                             @OA\Property(property="name", type="string", example="Tech Conference 2024"),
 *                             @OA\Property(property="slug", type="string", example="tech-conference-2024")
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Error message"),
 *     @OA\Property(property="error", type="string", nullable=true, example="Detailed error message")
 * )
 */