<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Services\FcmSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAnnouncementPush implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $announcementId) {}

    public function handle(FcmSender $fcm): void
    {
        $announcement = Announcement::query()->with('event.devices')->find($this->announcementId);
        if (! $announcement || $announcement->published_at === null) {
            return;
        }

        $tokens = $announcement->event->devices->pluck('token')->all();

        if (! $fcm->isConfigured()) {
            Log::warning('Announcement push skipped: Firebase is not configured.', [
                'announcement_id' => $announcement->id,
            ]);

            return;
        }

        $result = $fcm->sendToTokens(
            $tokens,
            $announcement->title,
            $announcement->body,
            [
                'type' => 'announcement',
                'id' => (string) $announcement->id,
            ],
        );

        $announcement->forceFill(['push_sent_at' => now()])->save();

        Log::info('Announcement push finished', [
            'announcement_id' => $announcement->id,
            'sent' => $result['sent'],
            'failed' => $result['failed'],
        ]);
    }
}
