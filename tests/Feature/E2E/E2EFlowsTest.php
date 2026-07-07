<?php

/**
 * E2E testleri Playwright veya Laravel Dusk kurulumu gerektirir.
 * CI'da etkinleştirmek için: composer require laravel/dusk --dev
 */
it('skips e2e login create event flow until browser runner is configured')->skip(
    'E2E altyapısı (Playwright/Dusk) henüz kurulmadı — Faz 9 opsiyonel adım.'
);

it('skips e2e timeline drag save flow until browser runner is configured')->skip(
    'E2E altyapısı (Playwright/Dusk) henüz kurulmadı — Faz 9 opsiyonel adım.'
);

it('skips e2e public program page flow until browser runner is configured')->skip(
    'E2E altyapısı (Playwright/Dusk) henüz kurulmadı — Faz 9 opsiyonel adım.'
);
