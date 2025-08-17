<?php

use App\Http\Controllers\API\PublicEventController;
use App\Http\Controllers\API\EventProgramController;
use App\Http\Controllers\API\ProgramSessionApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - CATCH-ALL ROUTE KALDIRILDI
|--------------------------------------------------------------------------
*/

// KRİTİK: Bu rotalar otomatik olarak /api prefix'i alır
// Yani /api/test, /api/health, /api/v1/events olarak erişilir

// TEST ENDPOINT'LERİ - ÖNCE BU ÇALIŞSIN
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API çalışıyor!',
        'timestamp' => now(),
        'url' => request()->fullUrl(),
        'method' => request()->method(),
        'headers' => [
            'host' => request()->header('host'),
            'user-agent' => request()->header('user-agent'),
        ]
    ]);
});

Route::get('/ping', function () {
    return response()->json(['pong' => true, 'time' => now()]);
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'environment' => app()->environment(),
        'laravel_version' => app()->version(),
    ]);
});

// API Documentation endpoint
Route::get('/docs', function () {
    return response()->json([
        'name' => 'Event Management System API',
        'version' => '1.0.0',
        'documentation' => url('/docs/api'),
        'endpoints' => [
            'public' => [
                'events' => url('/api/v1/events'),
                'search' => url('/api/v1/search'),
                'health' => url('/api/health'),
                'test' => url('/api/test'),
            ],
            'admin' => [
                'events' => url('/api/v1/events'),
                'participants' => url('/api/v1/participants'),
                'venues' => url('/api/v1/venues'),
                'sponsors' => url('/api/v1/sponsors'),
            ],
            'system' => [
                'health' => url('/api/health'),
                'user' => url('/api/v1/user'),
            ],
        ],
    ]);
})->name('api-info');

// Route debugging endpoint
Route::get('/debug/routes', function () {
    if (!app()->environment(['local', 'development'])) {
        abort(404);
    }

    $routes = [];
    foreach (Route::getRoutes() as $route) {
        if (str_starts_with($route->uri(), 'api/')) {
            $routes[] = [
                'method' => implode('|', $route->methods()),
                'uri' => '/' . $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        }
    }

    return response()->json([
        'total_api_routes' => count($routes),
        'routes' => $routes,
        'current_request' => [
            'url' => request()->fullUrl(),
            'path' => request()->path(),
            'method' => request()->method(),
        ]
    ]);
});

// V1 API - TÜM ENDPOINT'LER
Route::prefix('v1')->name('api.v1.')->group(function () {

    // Test endpoint
    Route::get('/test', [PublicEventController::class, 'test'])->name('test');

    // Events API - KAPSAMLI VERSİYON
    Route::prefix('events')->name('events.')->group(function () {

        // Get all published events
        Route::get('/', [PublicEventController::class, 'index'])->name('index');

        // Get specific event by slug
        Route::get('/{event:slug}', [PublicEventController::class, 'show'])->name('show');

        // Event speakers
        Route::prefix('{event:slug}/speakers')->name('speakers.')->group(function () {
            Route::get('/', [PublicEventController::class, 'speakers'])->name('index');
            Route::get('/{participant}', [PublicEventController::class, 'speaker'])
                ->name('show')->where('participant', '[0-9]+');
        });

        // Event program - KAPSAMLI VERSİYON
        Route::prefix('{event:slug}/program')->name('program.')->group(function () {
            // Get full program as JSON
            Route::get('/', [EventProgramController::class, 'getProgram'])->name('index');

            // Get program for specific day
            Route::get('/day/{date}', [EventProgramController::class, 'getDayProgram'])
                ->name('day')->where('date', '\d{4}-\d{2}-\d{2}');

            // Get sessions by venue
            Route::get('/venue/{venue}', [EventProgramController::class, 'getVenueSessions'])
                ->name('venue')->where('venue', '[0-9]+');

            // Download program as PDF
            Route::get('/download/pdf', [EventProgramController::class, 'downloadPdf'])->name('pdf');

            // Download program as CSV
            Route::get('/download/csv', [EventProgramController::class, 'downloadCsv'])->name('csv');

            // Search in program
            Route::get('/search', [EventProgramController::class, 'search'])->name('search');
        });

        // Event venues
        Route::prefix('{event:slug}/venues')->name('venues.')->group(function () {
            Route::get('/', [PublicEventController::class, 'venues'])->name('index');
            Route::get('/{venue}', [PublicEventController::class, 'venue'])
                ->name('show')->where('venue', '[0-9]+');
        });

        // Event sponsors
        Route::prefix('{event:slug}/sponsors')->name('sponsors.')->group(function () {
            Route::get('/', [PublicEventController::class, 'sponsors'])->name('index');
            Route::get('/{sponsor}', [PublicEventController::class, 'sponsor'])
                ->name('show')->where('sponsor', '[0-9]+');
        });

        // Event statistics - KRİTİK: Bu route'u açık şekilde tanımla
        Route::get('{event:slug}/stats', [PublicEventController::class, 'statistics'])->name('statistics');
    });

    // Organizations API
    Route::prefix('organizations')->name('organizations.')->group(function () {
        Route::get('/{organization}', [PublicEventController::class, 'organization'])->name('show');
        Route::get('/{organization}/events', [PublicEventController::class, 'organizationEvents'])->name('events');
    });

    // Search API
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('/events', [PublicEventController::class, 'searchEvents'])->name('events');
        Route::get('/speakers', [PublicEventController::class, 'searchSpeakers'])->name('speakers');
        Route::get('/presentations', [PublicEventController::class, 'searchPresentations'])->name('presentations');
    });

    Route::post('/program-sessions/bulk-update', [ProgramSessionApiController::class, 'bulkUpdate']);
    Route::post('/program-sessions/{session}/update-venue', [ProgramSessionApiController::class, 'updateVenue']);
    Route::post('/program-sessions/{session}/update-time', [ProgramSessionApiController::class, 'updateTime']);
    Route::get('/program-sessions/timeline-data', [ProgramSessionApiController::class, 'getTimelineData']);
    Route::post('/program-sessions/quick-create', [ProgramSessionApiController::class, 'quickCreate']);

    // Import test endpoints
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/test', function () {
            return response()->json([
                'success' => true,
                'message' => 'Import API çalışıyor',
                'available_endpoints' => [
                    'participants' => '/api/v1/import/participants',
                    'presentations' => '/api/v1/import/presentations',
                    'sessions' => '/api/v1/import/sessions',
                ]
            ]);
        })->name('test');

        Route::post('/participants', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'Participants import endpoint çalışıyor',
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'note' => 'Bu bir test endpoint\'i - gerçek import henüz implemente edilmedi'
            ]);
        })->name('participants');

        Route::post('/presentations', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'Presentations import endpoint çalışıyor',
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);
        })->name('presentations');
    });
});

// Debug endpoint (only for development)
Route::get('/debug/event/{slug}', function ($slug) {
    if (!app()->environment(['local', 'development'])) {
        abort(404);
    }

    try {
        $event = \App\Models\Event::where('slug', $slug)->first();

        if (!$event) {
            return response()->json(['error' => 'Event not found']);
        }

        $debug = [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'is_published' => $event->is_published,
            ],
            'relationships' => [],
            'table_structures' => [],
        ];

        // Test each relationship
        try {
            $debug['relationships']['organization'] = $event->organization ?
                ['id' => $event->organization->id, 'name' => $event->organization->name] : null;
        } catch (\Exception $e) {
            $debug['relationships']['organization'] = 'ERROR: ' . $e->getMessage();
        }

        try {
            $debug['relationships']['eventDays'] = $event->eventDays->count();
        } catch (\Exception $e) {
            $debug['relationships']['eventDays'] = 'ERROR: ' . $e->getMessage();
        }

        try {
            $debug['relationships']['venues'] = $event->venues->count();
        } catch (\Exception $e) {
            $debug['relationships']['venues'] = 'ERROR: ' . $e->getMessage();
        }

        // Check table structures
        try {
            $debug['table_structures']['organizations'] = \Schema::getColumnListing('organizations');
        } catch (\Exception $e) {
            $debug['table_structures']['organizations'] = 'ERROR: ' . $e->getMessage();
        }

        try {
            $debug['table_structures']['events'] = \Schema::getColumnListing('events');
        } catch (\Exception $e) {
            $debug['table_structures']['events'] = 'ERROR: ' . $e->getMessage();
        }

        return response()->json($debug);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ]);
    }
})->name('debug-event');
