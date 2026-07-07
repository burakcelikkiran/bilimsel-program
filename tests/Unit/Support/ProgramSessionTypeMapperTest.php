<?php

use App\Support\ProgramSessionTypeMapper;

it('maps session types to program json labels', function () {
    expect(ProgramSessionTypeMapper::programJsonType('break'))->toBe('Ara');
    expect(ProgramSessionTypeMapper::programJsonType('main'))->toBe('Oturum');
    expect(ProgramSessionTypeMapper::programJsonType('unknown_type'))->toBe('Unknown_type');
});

it('maps session types to display labels', function () {
    expect(ProgramSessionTypeMapper::displayLabel('plenary'))->toBe('Genel Oturum');
    expect(ProgramSessionTypeMapper::displayLabel('oral_presentation'))->toBe('Sözlü Bildiri');
});

it('generates deterministic uuids for session types and ids', function () {
    $typeUuid = ProgramSessionTypeMapper::typeUuid('main');
    $sessionUuid = ProgramSessionTypeMapper::sessionUuid(42);

    expect($typeUuid)->toBe(ProgramSessionTypeMapper::typeUuid('main'));
    expect($sessionUuid)->toBe(ProgramSessionTypeMapper::sessionUuid(42));
    expect($typeUuid)->not->toBe($sessionUuid);
});
