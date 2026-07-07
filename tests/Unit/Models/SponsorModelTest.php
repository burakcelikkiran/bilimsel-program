<?php

use App\Models\Sponsor;

it('uppercases sponsor name on save', function () {
    $sponsor = Sponsor::factory()->make(['name' => 'Acme Corp']);
    $sponsor->save();

    expect($sponsor->fresh()->name)->toBe('ACME CORP');
});

it('prefixes website with https when missing', function () {
    $sponsor = Sponsor::factory()->make(['website' => 'example.com']);
    $sponsor->save();

    expect($sponsor->fresh()->website)->toBe('https://example.com');
});
