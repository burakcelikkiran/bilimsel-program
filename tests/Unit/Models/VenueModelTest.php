<?php

use App\Models\Venue;

it('slugifies venue name with turkish characters', function () {
    $venue = new Venue;
    $venue->name = 'Ana Salon İstanbul';

    expect($venue->name)->toBe('ana-salon-istanbul');
});

it('capitalizes display name', function () {
    $venue = new Venue;
    $venue->display_name = 'kongre salonu';

    expect($venue->display_name)->toBe('Kongre salonu');
});
