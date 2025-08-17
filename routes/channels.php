<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User-specific private channels
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Organization channels (for organization members)
Broadcast::channel('organization.{organizationId}', function ($user, $organizationId) {
    return $user->organizations()->where('organizations.id', $organizationId)->exists();
});

// Event channels (for event participants and organizers)
Broadcast::channel('event.{eventId}', function ($user, $eventId) {
    $event = \App\Models\Event::find($eventId);
    if (!$event) {
        return false;
    }
    
    // Check if user has access to this event's organization
    return $user->organizations()->where('organizations.id', $event->organization_id)->exists();
});

// Event program real-time updates
Broadcast::channel('event.{eventId}.program', function ($user, $eventId) {
    $event = \App\Models\Event::find($eventId);
    if (!$event) {
        return false;
    }
    
    // Only organizers and editors can listen to program updates
    return $user->organizations()
               ->where('organizations.id', $event->organization_id)
               ->whereIn('role', ['organizer', 'editor'])
               ->exists();
});

// Session-specific channels (for session moderators and speakers)
Broadcast::channel('session.{sessionId}', function ($user, $sessionId) {
    $session = \App\Models\ProgramSession::with('venue.eventDay.event')->find($sessionId);
    if (!$session) {
        return false;
    }
    
    $event = $session->venue->eventDay->event;
    
    // Check if user is moderator of this session or has organization access
    $isModerator = $session->moderators()->where('participant_id', function ($query) use ($user) {
        $query->select('id')
              ->from('participants')
              ->where('email', $user->email);
    })->exists();
    
    $hasOrgAccess = $user->organizations()
                        ->where('organizations.id', $event->organization_id)
                        ->exists();
    
    return $isModerator || $hasOrgAccess;
});

// Presentation channels (for speakers)
Broadcast::channel('presentation.{presentationId}', function ($user, $presentationId) {
    $presentation = \App\Models\Presentation::with('programSession.venue.eventDay.event')->find($presentationId);
    if (!$presentation) {
        return false;
    }
    
    $event = $presentation->programSession->venue->eventDay->event;
    
    // Check if user is speaker of this presentation or has organization access
    $isSpeaker = $presentation->speakers()->where('participant_id', function ($query) use ($user) {
        $query->select('id')
              ->from('participants')
              ->where('email', $user->email);
    })->exists();
    
    $hasOrgAccess = $user->organizations()
                        ->where('organizations.id', $event->organization_id)
                        ->exists();
    
    return $isSpeaker || $hasOrgAccess;
});

// Admin notification channels
Broadcast::channel('admin.notifications', function ($user) {
    return $user->isAdmin();
});

// Organization admin channels
Broadcast::channel('organization.{organizationId}.admin', function ($user, $organizationId) {
    return $user->organizations()
               ->where('organizations.id', $organizationId)
               ->wherePivot('role', 'organizer')
               ->exists() || $user->isAdmin();
});

// Live event channels (for attendees during event)
Broadcast::channel('live-event.{eventId}', function ($user, $eventId) {
    $event = \App\Models\Event::find($eventId);
    if (!$event || !$event->is_published) {
        return false;
    }
    
    // During event, allow broader access for attendees
    return true; // You might want to implement attendee registration check here
});

// Live session channels (for real-time session updates during event)
Broadcast::channel('live-session.{sessionId}', function ($user, $sessionId) {
    $session = \App\Models\ProgramSession::with('venue.eventDay.event')->find($sessionId);
    if (!$session || !$session->venue->eventDay->event->is_published) {
        return false;
    }
    
    // During event, allow access for attendees
    return true; // You might want to implement attendee check here
});

// Chat channels for event communications
Broadcast::channel('event.{eventId}.chat', function ($user, $eventId) {
    $event = \App\Models\Event::find($eventId);
    if (!$event) {
        return false;
    }
    
    // Allow access for organization members and registered attendees
    $hasOrgAccess = $user->organizations()
                        ->where('organizations.id', $event->organization_id)
                        ->exists();
    
    // Add attendee check here if you implement attendee registration
    
    return $hasOrgAccess;
});

// File upload progress channels
Broadcast::channel('upload.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// System maintenance channels (for admins)
Broadcast::channel('system.maintenance', function ($user) {
    return $user->isAdmin();
});

// Bulk operation progress channels
Broadcast::channel('bulk-operation.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Event analytics real-time updates
Broadcast::channel('analytics.event.{eventId}', function ($user, $eventId) {
    $event = \App\Models\Event::find($eventId);
    if (!$event) {
        return false;
    }
    
    // Only organizers can see real-time analytics
    return $user->organizations()
               ->where('organizations.id', $event->organization_id)
               ->wherePivot('role', 'organizer')
               ->exists() || $user->isAdmin();
});