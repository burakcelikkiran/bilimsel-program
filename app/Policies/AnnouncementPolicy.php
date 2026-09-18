<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user, Event $event): bool
    {
        return $user->can('view', $event);
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->can('view', $announcement->event);
    }

    public function create(User $user, Event $event): bool
    {
        return $user->can('sendNotifications', $event);
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->can('sendNotifications', $announcement->event);
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->can('sendNotifications', $announcement->event);
    }

    public function sendPush(User $user, Announcement $announcement): bool
    {
        return $user->can('sendNotifications', $announcement->event);
    }
}
