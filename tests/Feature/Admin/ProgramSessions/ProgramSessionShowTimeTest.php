<?php

use App\Models\ProgramSession;

beforeEach(fn () => test()->withoutVite());

it('returns program session show times as H:i strings without timezone shift', function () {
    $hierarchy = programHierarchy();

    $session = ProgramSession::factory()
        ->for($hierarchy['venue'])
        ->atTime('08:30', '09:30')
        ->create();

    $this->actingAs($hierarchy['user'])
        ->get(route('admin.program-sessions.show', $session))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/ProgramSessions/Show')
            ->where('session.start_time', '08:30')
            ->where('session.end_time', '09:30')
            ->where('session.formatted_time_range', '08:30 - 09:30'));
});
