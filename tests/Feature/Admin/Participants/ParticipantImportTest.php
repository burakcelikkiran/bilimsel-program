<?php

use App\Models\Organization;
use App\Models\Participant;
use Illuminate\Http\UploadedFile;

function participantImportCsv(array $rows = []): UploadedFile
{
    $headers = 'ad,soyad,email,telefon,ünvan,kurum,biyografi,konuşmacı,moderatör';
    $defaultRows = [
        'Ahmet,Yilmaz,ahmet@example.com,05321234567,Dr.,ABC Universitesi,Pediatri uzmani,evet,hayir',
        'Ayse,Demir,ayse@example.com,,,,,,',
    ];

    $content = $headers."\n".implode("\n", $rows !== [] ? $rows : $defaultRows);

    return UploadedFile::fake()->createWithContent('participants.csv', $content);
}

it('imports participants from csv for selected organization', function () {
    ['organization' => $org, 'user' => $user] = adminContext();

    test()->actingAs($user)
        ->post(route('admin.participants.bulk-import'), [
            'file' => participantImportCsv(),
            'organization_id' => $org->id,
        ])
        ->assertRedirect(route('admin.participants.index'))
        ->assertSessionHas('success');

    expect(Participant::query()->where('organization_id', $org->id)->count())->toBe(2)
        ->and(Participant::query()->where('email', 'ahmet@example.com')->first())
        ->organization_id->toBe($org->id)
        ->first_name->toBe('Ahmet')
        ->affiliation->toBe('ABC Universitesi')
        ->is_speaker->toBeTrue()
        ->is_moderator->toBeFalse();
});

it('updates existing participants when update_existing is enabled', function () {
    ['organization' => $org, 'user' => $user] = adminContext();

    Participant::factory()->create([
        'organization_id' => $org->id,
        'first_name' => 'Ahmet',
        'last_name' => 'Eski',
        'email' => 'ahmet@example.com',
        'affiliation' => 'Eski Kurum',
    ]);

    test()->actingAs($user)
        ->post(route('admin.participants.bulk-import'), [
            'file' => participantImportCsv([
                'Ahmet,Yilmaz,ahmet@example.com,,,Yeni Kurum,,,',
            ]),
            'organization_id' => $org->id,
            'update_existing' => true,
        ])
        ->assertRedirect(route('admin.participants.index'))
        ->assertSessionHas('success');

    expect(Participant::query()->where('organization_id', $org->id)->count())->toBe(1)
        ->and(Participant::query()->where('email', 'ahmet@example.com')->first())
        ->last_name->toBe('Yilmaz')
        ->affiliation->toBe('Yeni Kurum');
});

it('skips existing participants when update_existing is disabled', function () {
    ['organization' => $org, 'user' => $user] = adminContext();

    Participant::factory()->create([
        'organization_id' => $org->id,
        'first_name' => 'Ahmet',
        'last_name' => 'Eski',
        'email' => 'ahmet@example.com',
    ]);

    test()->actingAs($user)
        ->post(route('admin.participants.bulk-import'), [
            'file' => participantImportCsv([
                'Ahmet,Yilmaz,ahmet@example.com,,,,,,,',
            ]),
            'organization_id' => $org->id,
            'update_existing' => false,
        ])
        ->assertRedirect(route('admin.participants.index'))
        ->assertSessionHas('success');

    expect(Participant::query()->where('email', 'ahmet@example.com')->first())
        ->last_name->toBe('Eski');
});

it('forbids importing into unauthorized organization', function () {
    ['organization' => $org, 'user' => $user] = editorContext();
    $otherOrganization = Organization::factory()->create();

    test()->actingAs($user)
        ->post(route('admin.participants.bulk-import'), [
            'file' => participantImportCsv(),
            'organization_id' => $otherOrganization->id,
        ])
        ->assertForbidden();

    expect(Participant::query()->where('organization_id', $otherOrganization->id)->count())->toBe(0)
        ->and(Participant::query()->where('organization_id', $org->id)->count())->toBe(0);
});

it('rejects participant bulk import without file', function () {
    ['organization' => $org, 'user' => $user] = adminContext();

    test()->actingAs($user)
        ->from(route('admin.participants.index'))
        ->post(route('admin.participants.bulk-import'), [
            'organization_id' => $org->id,
        ])
        ->assertSessionHasErrors(['file']);
});

it('allows organizer to import participants for own organization', function () {
    ['organization' => $org, 'user' => $user] = organizerContext();

    test()->actingAs($user)
        ->post(route('admin.participants.bulk-import'), [
            'file' => participantImportCsv([
                'Mehmet,Oz,mehmet@example.com,,,,,,,',
            ]),
            'organization_id' => $org->id,
        ])
        ->assertRedirect(route('admin.participants.index'))
        ->assertSessionHas('success');

    expect(Participant::query()->where('email', 'mehmet@example.com')->exists())->toBeTrue();
});

it('imports participants with only first and last name when email is empty', function () {
    ['organization' => $org, 'user' => $user] = adminContext();

    test()->actingAs($user)
        ->post(route('admin.participants.bulk-import'), [
            'file' => participantImportCsv([
                'Abdurrahman,Akgun,,,,,,,,',
                'Adem,Bozaykut,,,,,,,,',
            ]),
            'organization_id' => $org->id,
        ])
        ->assertRedirect(route('admin.participants.index'))
        ->assertSessionHas('success');

    expect(Participant::query()->where('organization_id', $org->id)->count())->toBe(2)
        ->and(Participant::query()->where('first_name', 'Abdurrahman')->first())
        ->last_name->toBe('Akgun')
        ->email->toBeNull();
});
