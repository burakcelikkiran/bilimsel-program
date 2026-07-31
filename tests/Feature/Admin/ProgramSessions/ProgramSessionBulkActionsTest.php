<?php

use App\Models\Participant;
use App\Models\Presentation;
use App\Models\ProgramSession;
use Illuminate\Support\Facades\DB;

it('bulk destroys deletable program sessions', function () {
    $data = programHierarchy();

    $secondSession = ProgramSession::factory()->create([
        'venue_id' => $data['venue']->id,
        'start_time' => '11:00',
        'end_time' => '12:00',
        'session_type' => 'main',
        'is_break' => false,
    ]);

    test()->actingAs($data['user'])
        ->delete(route('admin.program-sessions.bulk-destroy'), [
            'session_ids' => [$data['programSession']->id, $secondSession->id],
        ])
        ->assertRedirect(route('admin.program-sessions.index'))
        ->assertSessionHas('success');

    expect(ProgramSession::find($data['programSession']->id))->toBeNull()
        ->and(ProgramSession::find($secondSession->id))->toBeNull();
});

it('bulk destroys sessions with presentations and detaches participant relations', function () {
    $data = fullEventProgram();

    $data['presentation']->speakers()->attach($data['participant']->id, [
        'speaker_role' => 'primary',
        'sort_order' => 1,
    ]);

    $data['programSession']->moderators()->attach($data['participant']->id, [
        'sort_order' => 1,
    ]);

    $deletableSession = ProgramSession::factory()->create([
        'venue_id' => $data['venue']->id,
        'start_time' => '11:00',
        'end_time' => '12:00',
        'session_type' => 'main',
        'is_break' => false,
    ]);

    test()->actingAs($data['user'])
        ->delete(route('admin.program-sessions.bulk-destroy'), [
            'session_ids' => [$data['programSession']->id, $deletableSession->id],
        ])
        ->assertRedirect(route('admin.program-sessions.index'))
        ->assertSessionHas('success');

    expect(ProgramSession::find($data['programSession']->id))->toBeNull()
        ->and(ProgramSession::find($deletableSession->id))->toBeNull()
        ->and(Presentation::find($data['presentation']->id))->toBeNull()
        ->and(Participant::find($data['participant']->id))->not->toBeNull()
        ->and(DB::table('presentation_speakers')->where('presentation_id', $data['presentation']->id)->count())->toBe(0)
        ->and(DB::table('program_session_moderators')->where('program_session_id', $data['programSession']->id)->count())->toBe(0);
});

it('bulk duplicates program sessions', function () {
    $data = programHierarchy();

    test()->actingAs($data['user'])
        ->post(route('admin.program-sessions.bulk-duplicate'), [
            'session_ids' => [$data['programSession']->id],
        ])
        ->assertRedirect(route('admin.program-sessions.index'))
        ->assertSessionHas('success');

    expect(ProgramSession::query()->where('venue_id', $data['venue']->id)->count())->toBe(2);
});
