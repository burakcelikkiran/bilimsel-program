<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Participant;
use App\Models\ProgramSession;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's accessible organizations
        $organizations = $user->isAdmin()
            ? Organization::active()->get()
            : $user->organizations()->wherePivot('organization_users.deleted_at', null)->get();

        // Get recent events based on user role
        $recentEvents = $this->getRecentEvents($user);

        // Get dashboard statistics
        $statistics = $this->getDashboardStatistics($user);

        // Get upcoming events
        $upcomingEvents = $this->getUpcomingEvents($user);

        // Get user's recent activities
        $recentActivities = $this->getRecentActivities($user);

        return Inertia::render('Admin/Dashboard', [
            'statistics' => [
                'total_events' => $statistics['total_events'] ?? 0,
                'total_organizations' => $statistics['total_organizations'] ?? 0,
                'total_participants' => $statistics['total_participants'] ?? 0,
                'pending_sessions' => $statistics['total_sessions'] ?? 0,
            ],
            'recentEvents' => $recentEvents,
            'upcomingEvents' => $upcomingEvents,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Get recent events based on user role
     */
    private function getRecentEvents($user)
    {
        $query = Event::with(['organization', 'creator'])
            ->latest()
            ->limit(5);

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $query->whereIn('organization_id', $organizationIds);
        }

        return $query->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'status' => $event->status,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'formatted_date_range' => $event->formatted_date_range,
                'organization' => [
                    'id' => $event->organization->id,
                    'name' => $event->organization->name,
                ],
                'is_published' => $event->is_published,
                'total_sessions' => $event->getTotalSessions(),
                'total_presentations' => $event->getTotalPresentations(),
            ];
        });
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStatistics($user)
    {
        if ($user->isAdmin()) {
            return [
                'total_events' => Event::count(),
                'published_events' => Event::published()->count(),
                'total_organizations' => Organization::active()->count(),
                'total_participants' => Participant::count(),
                'total_sessions' => ProgramSession::nonBreaks()->count(),
                'total_sponsors' => Sponsor::active()->count(),
                'upcoming_events' => Event::upcoming()->count(),
                'ongoing_events' => Event::ongoing()->count(),
            ];
        }

        $organizationIds = $user->organizations()->pluck('organizations.id');

        return [
            'my_events' => Event::whereIn('organization_id', $organizationIds)->count(),
            'published_events' => Event::whereIn('organization_id', $organizationIds)->published()->count(),
            'my_organizations' => $user->organizations()->count(),
            'total_participants' => Participant::whereIn('organization_id', $organizationIds)->count(),
            'total_sessions' => ProgramSession::whereHas('venue.eventDay.event', function ($query) use ($organizationIds) {
                $query->whereIn('organization_id', $organizationIds);
            })->nonBreaks()->count(),
            'total_sponsors' => Sponsor::whereIn('organization_id', $organizationIds)->active()->count(),
            'upcoming_events' => Event::whereIn('organization_id', $organizationIds)->upcoming()->count(),
            'ongoing_events' => Event::whereIn('organization_id', $organizationIds)->ongoing()->count(),
        ];
    }

    /**
     * Get upcoming events
     */
    private function getUpcomingEvents($user)
    {
        $query = Event::with(['organization'])
            ->upcoming()
            ->published()
            ->orderBy('start_date')
            ->limit(3);

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $query->whereIn('organization_id', $organizationIds);
        }

        return $query->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'formatted_date_range' => $event->formatted_date_range,
                'location' => $event->location,
                'organization' => [
                    'name' => $event->organization->name,
                ],
                'days_until_start' => now()->diffInDays($event->start_date),
            ];
        });
    }

    /**
     * Get user's recent activities
     */
    private function getRecentActivities($user)
    {
        $activities = collect();

        // Recent events created by user
        $recentEvents = Event::where('created_by', $user->id)
            ->latest()
            ->limit(3)
            ->get();

        foreach ($recentEvents as $event) {
            $activities->push([
                'type' => 'event_created',
                'message' => "'{$event->name}' etkinliğini oluşturdunuz",
                'date' => $event->created_at,
                'link' => route('admin.events.show', $event),
            ]);
        }

        // If user is moderating sessions (for participants who are also users)
        if (!$user->isAdmin()) {
            $participant = Participant::where('email', $user->email)->first();
            if ($participant) {
                $recentSessions = $participant->moderatedSessions()
                    ->with(['venue.eventDay.event'])
                    ->latest()
                    ->limit(2)
                    ->get();

                foreach ($recentSessions as $session) {
                    $activities->push([
                        'type' => 'session_moderated',
                        'message' => "'{$session->title}' oturumunda moderatör olarak atandınız",
                        'date' => $session->created_at,
                        'link' => route('admin.program-sessions.show', $session),
                    ]);
                }
            }
        }

        return $activities->sortByDesc('date')->take(5)->values();
    }

    /**
     * Get user permissions for frontend
     */
    private function getUserPermissions($user)
    {
        return [
            'can_create_organizations' => $user->isAdmin(),
            'can_manage_all_events' => $user->isAdmin(),
            'can_create_events' => $user->isOrganizer(),
            'can_edit_events' => $user->isEditor(),
            'can_manage_participants' => $user->isEditor(),
            'can_manage_sponsors' => $user->isEditor(),
            'can_view_analytics' => $user->isOrganizer(),
            'can_export_data' => $user->isEditor(),
            'can_import_data' => $user->isOrganizer(),
        ];
    }

    /**
     * Get user notifications
     */
    public function notifications(Request $request)
    {
        $user = $request->user();

        $notifications = collect();

        // Check for events starting soon
        $soonEvents = Event::upcoming()
            ->where('start_date', '<=', now()->addDays(7))
            ->where('start_date', '>', now())
            ->published();

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $soonEvents->whereIn('organization_id', $organizationIds);
        }

        foreach ($soonEvents->get() as $event) {
            $daysUntil = now()->diffInDays($event->start_date);
            $notifications->push([
                'type' => 'event_starting_soon',
                'title' => 'Etkinlik Yaklaşıyor',
                'message' => "'{$event->name}' etkinliği {$daysUntil} gün içinde başlayacak",
                'date' => $event->start_date,
                'link' => route('admin.events.show', $event),
                'priority' => 'medium',
            ]);
        }

        // Check for events without sessions
        $eventsWithoutSessions = Event::whereDoesntHave('eventDays.venues.programSessions')
            ->where('start_date', '>', now())
            ->published();

        if (!$user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $eventsWithoutSessions->whereIn('organization_id', $organizationIds);
        }

        foreach ($eventsWithoutSessions->get() as $event) {
            $notifications->push([
                'type' => 'event_incomplete',
                'title' => 'Eksik Program',
                'message' => "'{$event->name}' etkinliğinde henüz oturum bulunmuyor",
                'date' => $event->created_at,
                'link' => route('admin.events.show', $event),
                'priority' => 'high',
            ]);
        }

        return response()->json([
            'notifications' => $notifications->sortByDesc('date')->take(10)->values(),
            'unread_count' => $notifications->count(),
        ]);
    }

    /**
     * Quick statistics for widgets
     */
    public function quickStats(Request $request)
    {
        $user = $request->user();
        $timeframe = $request->get('timeframe', '30'); // days

        $fromDate = now()->subDays((int)$timeframe);

        if ($user->isAdmin()) {
            $stats = [
                'events_created' => Event::where('created_at', '>=', $fromDate)->count(),
                'sessions_created' => ProgramSession::where('created_at', '>=', $fromDate)->count(),
                'participants_added' => Participant::where('created_at', '>=', $fromDate)->count(),
                'organizations_added' => Organization::where('created_at', '>=', $fromDate)->count(),
            ];
        } else {
            $organizationIds = $user->organizations()->pluck('organizations.id');

            $stats = [
                'events_created' => Event::whereIn('organization_id', $organizationIds)
                    ->where('created_at', '>=', $fromDate)
                    ->count(),
                'sessions_created' => ProgramSession::whereHas('venue.eventDay.event', function ($query) use ($organizationIds) {
                    $query->whereIn('organization_id', $organizationIds);
                })
                    ->where('created_at', '>=', $fromDate)
                    ->count(),
                'participants_added' => Participant::whereIn('organization_id', $organizationIds)
                    ->where('created_at', '>=', $fromDate)
                    ->count(),
                'my_organizations' => $user->organizations()->count(),
            ];
        }

        return response()->json($stats);
    }
}
