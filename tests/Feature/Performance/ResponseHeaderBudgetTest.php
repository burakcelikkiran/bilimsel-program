<?php

use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Testing\TestResponse;
use Tighten\Ziggy\Ziggy;

/**
 * @return int Approximate HTTP response header block size in bytes.
 */
function responseHeaderBlockSize(TestResponse $response): int
{
    $size = strlen('HTTP/1.1 '.$response->getStatusCode().' '.$response->statusText()).strlen("\r\n");

    foreach ($response->headers->all() as $name => $values) {
        foreach ((array) $values as $value) {
            $size += strlen($name.': '.$value."\r\n");
        }
    }

    return $size + strlen("\r\n");
}

it('does not register AddLinkHeadersForPreloadedAssets in the web middleware group', function () {
    $webMiddleware = app('router')->getMiddlewareGroups()['web'];

    expect($webMiddleware)->not->toContain(AddLinkHeadersForPreloadedAssets::class);
});

it('keeps full page response headers within nginx default buffer budget', function () {
    if (! file_exists(public_path('build/manifest.json'))) {
        $this->markTestSkipped('Vite manifest is required for full page header budget checks.');
    }

    ['user' => $user] = adminContext();

    $response = $this->actingAs($user)->get(route('admin.venues.index'));

    $response->assertOk();
    expect($response->headers->has('Link'))->toBeFalse();
    expect(responseHeaderBlockSize($response))->toBeLessThan(4096);
});

it('keeps full page html payload within budget', function () {
    if (! file_exists(public_path('build/manifest.json'))) {
        $this->markTestSkipped('Vite manifest is required for full page HTML budget checks.');
    }

    ['user' => $user] = adminContext();

    $response = $this->actingAs($user)->get(route('admin.venues.index'));

    $response->assertOk();
    expect(strlen($response->getContent()))->toBeLessThan(80 * 1024);
});

it('excludes api routes from the inline ziggy payload', function () {
    Ziggy::clearRoutes();

    $routes = json_decode((new Ziggy)->toJson(), true)['routes'] ?? [];

    $apiRoutes = array_filter(
        array_keys($routes),
        fn (string $name): bool => str_starts_with($name, 'api.')
    );

    expect($apiRoutes)->toBeEmpty();
});
