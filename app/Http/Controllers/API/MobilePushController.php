<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MobilePushController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'string', Rule::in(['android', 'ios'])],
            'app' => ['nullable', 'string', 'max:64'],
            'event' => ['nullable', 'string', 'max:255'],
        ]);

        $app = $validated['app'] ?? 'tpk2026';
        $event = $this->resolveEvent($validated['event'] ?? null, $app);

        if (! $event) {
            return response()->json([
                'success' => false,
                'message' => 'Etkinlik bulunamadı.',
            ], 422);
        }

        $device = EventDevice::query()->updateOrCreate(
            ['token' => $validated['token']],
            [
                'event_id' => $event->id,
                'platform' => $validated['platform'],
                'app' => $app,
                'last_seen_at' => now(),
            ],
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $device->id,
                'event' => $event->slug,
                'platform' => $device->platform,
            ],
        ]);
    }

    public function announcements(Request $request, Event $event): JsonResponse
    {
        if (! $event->is_published && $request->user()?->can('view', $event) !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Etkinlik bulunamadı.',
            ], 404);
        }

        $payload = Announcement::query()
            ->where('event_id', $event->id)
            ->published()
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (Announcement $announcement) => $announcement->toMobilePayload())
            ->values();

        return response()->json($payload);
    }

    private function resolveEvent(?string $slug, string $app): ?Event
    {
        $resolvedSlug = $slug
            ?: (config('firebase.app_event_map')[$app] ?? null)
            ?: config('firebase.default_event_slug');

        if (! is_string($resolvedSlug) || $resolvedSlug === '') {
            return null;
        }

        return Event::query()->where('slug', $resolvedSlug)->first();
    }
}
