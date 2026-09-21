<?php

it('forbids guests from accessing the horizon dashboard', function () {
    $this->get('/horizon')
        ->assertForbidden();
});

it('forbids organizers from accessing the horizon dashboard', function () {
    ['user' => $user] = organizerContext();

    $this->actingAs($user)
        ->get('/horizon')
        ->assertForbidden();
});

it('allows admins to access the horizon dashboard', function () {
    ['user' => $user] = adminContext();

    $this->actingAs($user)
        ->get('/horizon')
        ->assertOk();
});
