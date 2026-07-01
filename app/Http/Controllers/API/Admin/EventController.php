<?php

// EventController.php'ye eklenecek metodlar
namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\JsonResponse; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ProgramSession;
use App\Models\Sponsor;
use App\Models\Venue;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Events",
 *     description="Event management operations"
 * )
 */
class EventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/ajax/events/for-select",
     *     tags={"Events", "AJAX"},
     *     summary="Get events for select dropdown",
     *     description="Returns a list of events formatted for use in select dropdowns",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for event name",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=100)
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
     *         description="Events retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="value", type="integer", example=1),
     *                     @OA\Property(property="label", type="string", example="AI Conference 2025"),
     *                     @OA\Property(property="slug", type="string", example="ai-conference-2025"),
     *                     @OA\Property(property="date_range", type="string", example="15.06.2025 - 17.06.2025"),
     *                     @OA\Property(property="is_published", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Events could not be loaded."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getForSelect(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $query = Event::query();

            // Apply user access restrictions
            if (!$user->isAdmin()) {
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereIn('organization_id', $organizationIds);
            }

            // Search filter
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Organization filter
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            $events = $query->orderBy('start_date', 'desc')
                ->limit(50)
                ->get(['id', 'name', 'slug', 'start_date', 'end_date', 'is_published']);

            return response()->json([
                'success' => true,
                'data' => $events->map(function ($event) {
                    return [
                        'value' => $event->id,
                        'label' => $event->name,
                        'slug' => $event->slug,
                        'date_range' => $event->start_date->format('d.m.Y') . ' - ' . $event->end_date->format('d.m.Y'),
                        'is_published' => $event->is_published,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Events could not be loaded.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/events/{event}/analytics",
     *     tags={"Events", "Analytics"},
     *     summary="Get event statistics for dashboard",
     *     description="Returns comprehensive analytics data for a specific event",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Analytics data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="basic",
     *                     type="object",
     *                     @OA\Property(property="total_days", type="integer", example=3),
     *                     @OA\Property(property="total_venues", type="integer", example=5),
     *                     @OA\Property(property="total_sessions", type="integer", example=24),
     *                     @OA\Property(property="total_presentations", type="integer", example=48),
     *                     @OA\Property(property="total_speakers", type="integer", example=32),
     *                     @OA\Property(property="total_moderators", type="integer", example=12)
     *                 ),
     *                 @OA\Property(
     *                     property="by_day",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="date", type="string", format="date", example="2025-06-15"),
     *                         @OA\Property(property="title", type="string", example="Gün 1"),
     *                         @OA\Property(property="venues", type="integer", example=5),
     *                         @OA\Property(property="sessions", type="integer", example=8),
     *                         @OA\Property(property="presentations", type="integer", example=16)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="by_session_type",
     *                     type="object",
     *                     @OA\Property(property="main", type="integer", example=12),
     *                     @OA\Property(property="satellite", type="integer", example=6),
     *                     @OA\Property(property="oral_presentation", type="integer", example=24)
     *                 ),
     *                 @OA\Property(
     *                     property="sponsors",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=8),
     *                     @OA\Property(
     *                         property="by_level",
     *                         type="object",
     *                         @OA\Property(property="platinum", type="integer", example=2),
     *                         @OA\Property(property="gold", type="integer", example=3),
     *                         @OA\Property(property="silver", type="integer", example=3)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Event not found")
     *         )
     *     )
     * )
     */
    public function analytics(Event $event): JsonResponse
    {
        try {
            $this->authorize('view', $event);

            $statistics = [
                'basic' => [
                    'total_days' => $event->eventDays()->count(),
                    'total_venues' => $event->venues()->count(),
                    'total_sessions' => $event->programSessions()->count(),
                    'total_presentations' => $event->presentations()->count(),
                    'total_speakers' => $event->speakers()->count(),
                    'total_moderators' => $event->moderators()->count(),
                ],
                'by_day' => $event->eventDays()
                    ->withCount(['venues', 'programSessions', 'presentations'])
                    ->get()
                    ->map(function ($day) {
                        return [
                            'date' => $day->date,
                            'title' => $day->title,
                            'venues' => $day->venues_count,
                            'sessions' => $day->program_sessions_count,
                            'presentations' => $day->presentations_count,
                        ];
                    }),
                'by_session_type' => $event->programSessions()
                    ->selectRaw('session_type, COUNT(*) as count')
                    ->groupBy('session_type')
                    ->pluck('count', 'session_type'),
                'sponsors' => [
                    'total' => $event->organization->sponsors()->active()->count(),
                    'by_level' => $event->organization->sponsors()
                        ->active()
                        ->selectRaw('sponsor_level, COUNT(*) as count')
                        ->groupBy('sponsor_level')
                        ->pluck('count', 'sponsor_level'),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving event analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
