<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Jobs\SendAnnouncementPush;
use App\Models\Announcement;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    use AuthorizesRequests;

    public function index(Event $event): Response
    {
        $this->authorize('view', $event);

        $announcements = $event->announcements()
            ->with('programSession:id,title')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Announcements/Index', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
            ],
            'announcements' => $announcements,
            'can_create' => auth()->user()?->can('sendNotifications', $event) ?? false,
            'device_count' => $event->devices()->count(),
        ]);
    }

    public function create(Event $event): Response
    {
        $this->authorize('sendNotifications', $event);

        $sessions = $event->programSessions()
            ->orderBy('title')
            ->get(['program_sessions.id', 'program_sessions.title'])
            ->map(fn ($session) => [
                'id' => $session->id,
                'title' => $session->title,
            ]);

        return Inertia::render('Admin/Announcements/Create', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
            ],
            'sessions' => $sessions,
            'device_count' => $event->devices()->count(),
        ]);
    }

    public function store(StoreAnnouncementRequest $request, Event $event): RedirectResponse
    {
        $announcement = $event->announcements()->create([
            'title' => $request->validated('title'),
            'body' => $request->validated('body'),
            'program_session_id' => $request->validated('program_session_id'),
            'published_at' => now(),
        ]);

        if ($request->boolean('send_push')) {
            SendAnnouncementPush::dispatch($announcement->id);
        }

        return redirect()
            ->route('admin.events.announcements.index', $event)
            ->with('success', $request->boolean('send_push')
                ? 'Duyuru yayınlandı ve bildirim kuyruğa alındı.'
                : 'Duyuru yayınlandı.');
    }

    public function destroy(Event $event, Announcement $announcement): RedirectResponse
    {
        abort_unless($announcement->event_id === $event->id, 404);
        $this->authorize('delete', $announcement);

        $announcement->delete();

        return redirect()
            ->route('admin.events.announcements.index', $event)
            ->with('success', 'Duyuru silindi.');
    }
}
