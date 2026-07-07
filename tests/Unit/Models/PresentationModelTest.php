<?php

use App\Models\Presentation;
use App\Models\ProgramSession;

it('capitalizes presentation title', function () {
    $session = ProgramSession::factory()->create();
    $presentation = Presentation::factory()->create([
        'program_session_id' => $session->id,
        'title' => 'yeni sunum başlığı',
    ]);

    expect($presentation->fresh()->title)->toBe('Yeni sunum başlığı');
});

it('syncs duration from start and end times on create', function () {
    $session = ProgramSession::factory()->create();
    $presentation = Presentation::factory()->create([
        'program_session_id' => $session->id,
        'start_time' => '10:00',
        'end_time' => '10:45',
        'duration_minutes' => null,
    ]);

    expect($presentation->fresh()->duration_minutes)->toBe(45);
});
