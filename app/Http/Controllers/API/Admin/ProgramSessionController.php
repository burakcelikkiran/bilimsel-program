<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\ProgramSession;
use App\Models\Venue;
use App\Models\ProgramSessionCategory;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Program Sessions",
 *     description="API Endpoints for managing program sessions"
 * )
 */
class ProgramSessionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/events/{event}/sessions",
     *     tags={"Program Sessions"},
     *     summary="Get list of program sessions for an event",
     *     description="Retrieve paginated list of program sessions with filtering and sorting options",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="venue_id",
     *         in="query",
     *         description="Filter by venue ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="session_type",
     *         in="query",
     *         description="Filter by session type",
     *         @OA\Schema(
     *             type="string",
     *             enum={"plenary", "parallel", "workshop", "poster", "break", "lunch", "social"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter by date (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in title and description",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(
     *             type="string",
     *             enum={"title", "start_time", "end_time", "session_type", "created_at"},
     *             default="start_time"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="direction",
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
     *         description="Number of items per page (max 100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Program sessions retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ProgramSessionResource")
     *                 ),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
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
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request, Event $event): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Post(
     *     path="/api/v1/events/{event}/sessions",
     *     tags={"Program Sessions"},
     *     summary="Create a new program session",
     *     description="Create a new program session for the specified event",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProgramSessionCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Program session created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Program session created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ProgramSessionResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or time conflict",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="venue_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The venue id field is required.")
     *                 )
     *             )
     *         )
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
     *     )
     * )
     */
    public function store(Request $request, Event $event): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Get(
     *     path="/api/v1/events/{event}/sessions/{session}",
     *     tags={"Program Sessions"},
     *     summary="Get a specific program session",
     *     description="Retrieve a specific program session with all related data including presentations and speakers",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="session",
     *         in="path",
     *         required=true,
     *         description="Program Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Program session retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ProgramSessionDetailResource"
     *             )
     *         )
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
     *         response=404,
     *         description="Program session not found or doesn't belong to this event",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(Event $event, ProgramSession $session): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Put(
     *     path="/api/v1/events/{event}/sessions/{session}",
     *     tags={"Program Sessions"},
     *     summary="Update a program session",
     *     description="Update an existing program session with new data",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="session",
     *         in="path",
     *         required=true,
     *         description="Program Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProgramSessionUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Program session updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Program session updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ProgramSessionResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or time conflict",
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
     *         response=404,
     *         description="Program session not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(Request $request, Event $event, ProgramSession $session): JsonResponse
    {
        // Method implementation...
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/events/{event}/sessions/{session}",
     *     tags={"Program Sessions"},
     *     summary="Delete a program session",
     *     description="Delete a program session. Session cannot be deleted if it has presentations.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="session",
     *         in="path",
     *         required=true,
     *         description="Program Session ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Program session deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Program session 'Session Title' deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete session with presentations",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot delete session with 3 presentations. Please remove presentations first.")
     *         )
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
     *         response=404,
     *         description="Program session not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(Event $event, ProgramSession $session): JsonResponse
    {
        // Method implementation...
    }
}

/**
 * @OA\Schema(
 *     schema="ProgramSessionCreateRequest",
 *     type="object",
 *     required={"venue_id", "title", "start_time", "end_time", "session_type"},
 *     @OA\Property(property="venue_id", type="integer", description="ID of the venue", example=1),
 *     @OA\Property(property="title", type="string", maxLength=500, description="Session title", example="Opening Ceremony"),
 *     @OA\Property(property="description", type="string", maxLength=2000, description="Session description", example="Welcome to our annual conference"),
 *     @OA\Property(property="start_time", type="string", format="time", description="Start time (H:i format)", example="09:00"),
 *     @OA\Property(property="end_time", type="string", format="time", description="End time (H:i format)", example="10:30"),
 *     @OA\Property(
 *         property="session_type",
 *         type="string",
 *         enum={"plenary", "parallel", "workshop", "poster", "break", "lunch", "social"},
 *         description="Type of session",
 *         example="plenary"
 *     ),
 *     @OA\Property(property="max_participants", type="integer", minimum=1, description="Maximum number of participants", example=100),
 *     @OA\Property(property="is_break", type="boolean", description="Whether this is a break session", example=false),
 *     @OA\Property(property="is_featured", type="boolean", description="Whether this session is featured", example=true),
 *     @OA\Property(property="sponsor_id", type="integer", description="ID of the sponsor", example=1),
 *     @OA\Property(
 *         property="category_ids",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         description="Array of category IDs",
 *         example={1, 2, 3}
 *     ),
 *     @OA\Property(
 *         property="moderator_ids",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         description="Array of moderator (participant) IDs",
 *         example={1, 2}
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProgramSessionUpdateRequest",
 *     type="object",
 *     required={"venue_id", "title", "start_time", "end_time", "session_type"},
 *     @OA\Property(property="venue_id", type="integer", description="ID of the venue", example=1),
 *     @OA\Property(property="title", type="string", maxLength=500, description="Session title", example="Opening Ceremony"),
 *     @OA\Property(property="description", type="string", maxLength=2000, description="Session description", example="Welcome to our annual conference"),
 *     @OA\Property(property="start_time", type="string", format="time", description="Start time (H:i format)", example="09:00"),
 *     @OA\Property(property="end_time", type="string", format="time", description="End time (H:i format)", example="10:30"),
 *     @OA\Property(
 *         property="session_type",
 *         type="string",
 *         enum={"plenary", "parallel", "workshop", "poster", "break", "lunch", "social"},
 *         description="Type of session",
 *         example="plenary"
 *     ),
 *     @OA\Property(property="max_participants", type="integer", minimum=1, description="Maximum number of participants", example=100),
 *     @OA\Property(property="is_break", type="boolean", description="Whether this is a break session", example=false),
 *     @OA\Property(property="is_featured", type="boolean", description="Whether this session is featured", example=true),
 *     @OA\Property(property="sponsor_id", type="integer", description="ID of the sponsor", example=1),
 *     @OA\Property(
 *         property="category_ids",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         description="Array of category IDs",
 *         example={1, 2, 3}
 *     ),
 *     @OA\Property(
 *         property="moderator_ids",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         description="Array of moderator (participant) IDs",
 *         example={1, 2}
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProgramSessionResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Opening Ceremony"),
 *     @OA\Property(property="description", type="string", example="Welcome to our annual conference"),
 *     @OA\Property(property="start_time", type="string", format="time", example="09:00:00"),
 *     @OA\Property(property="end_time", type="string", format="time", example="10:30:00"),
 *     @OA\Property(property="formatted_time_range", type="string", example="09:00 - 10:30"),
 *     @OA\Property(property="duration_in_minutes", type="integer", example=90),
 *     @OA\Property(property="session_type", type="string", example="plenary"),
 *     @OA\Property(property="session_type_display", type="string", example="Plenary Session"),
 *     @OA\Property(property="is_break", type="boolean", example=false),
 *     @OA\Property(property="is_featured", type="boolean", example=true),
 *     @OA\Property(property="max_participants", type="integer", example=100),
 *     @OA\Property(
 *         property="venue",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Main Auditorium"),
 *         @OA\Property(property="display_name", type="string", example="Main Auditorium"),
 *         @OA\Property(property="color", type="string", example="#007bff"),
 *         @OA\Property(
 *             property="event_day",
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="title", type="string", example="Day 1"),
 *             @OA\Property(property="date", type="string", format="date", example="2024-06-23")
 *         )
 *     ),
 *     @OA\Property(
 *         property="sponsor",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="TechCorp"),
 *         @OA\Property(property="logo_url", type="string", example="https://example.com/logo.png")
 *     ),
 *     @OA\Property(
 *         property="moderators",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="full_name", type="string", example="Dr. John Doe"),
 *             @OA\Property(property="title", type="string", example="Professor"),
 *             @OA\Property(property="affiliation", type="string", example="University of Technology")
 *         )
 *     ),
 *     @OA\Property(
 *         property="categories",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Technology"),
 *             @OA\Property(property="color", type="string", example="#28a745")
 *         )
 *     ),
 *     @OA\Property(property="presentations_count", type="integer", example=5),
 *     @OA\Property(property="moderators_count", type="integer", example=2),
 *     @OA\Property(
 *         property="permissions",
 *         type="object",
 *         @OA\Property(property="can_edit", type="boolean", example=true),
 *         @OA\Property(property="can_delete", type="boolean", example=true)
 *     ),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-06-23T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2024-06-23T10:00:00.000000Z")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProgramSessionDetailResource",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ProgramSessionResource"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="presentations",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="AI in Healthcare"),
 *                     @OA\Property(property="abstract", type="string", example="This presentation discusses..."),
 *                     @OA\Property(property="start_time", type="string", format="time", example="09:15:00"),
 *                     @OA\Property(property="end_time", type="string", format="time", example="09:30:00"),
 *                     @OA\Property(property="presentation_type", type="string", example="oral"),
 *                     @OA\Property(property="sort_order", type="integer", example=1),
 *                     @OA\Property(
 *                         property="speakers",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="id", type="integer", example=1),
 *                             @OA\Property(property="full_name", type="string", example="Dr. Jane Smith"),
 *                             @OA\Property(property="title", type="string", example="Researcher"),
 *                             @OA\Property(property="affiliation", type="string", example="Medical University")
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
 *     @OA\Property(property="error", type="string", example="Detailed error information")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation error"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *             property="field_name",
 *             type="array",
 *             @OA\Items(type="string")
 *         )
 *     )
 * )
 */