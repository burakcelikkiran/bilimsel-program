<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventDayController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\FileUploadController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\PresentationController;
use App\Http\Controllers\Admin\ProgramSessionController;
use App\Http\Controllers\Admin\ProgramSessionCategoryController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\VenueController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\API\PublicEventController;
use App\Http\Controllers\Admin\TimelineController;
use App\Http\Controllers\Admin\DragDropController;
use App\Models\Event;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Main welcome page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('welcome');

// Public event pages for attendees (using controllers from API namespace for data)
Route::prefix('events')->name('events.')->group(function () {
    Route::get('/', [PublicEventController::class, 'indexPage'])->name('index');
    Route::get('/{event:slug}', [PublicEventController::class, 'showPage'])->name('show');
    Route::get('/{event:slug}/program', [PublicEventController::class, 'programPage'])->name('program');
    Route::get('/{event:slug}/speakers', [PublicEventController::class, 'speakersPage'])->name('speakers');
    Route::get('/{event:slug}/venues', [PublicEventController::class, 'venuesPage'])->name('venues');
    Route::get('/{event:slug}/sponsors', [PublicEventController::class, 'sponsorsPage'])->name('sponsors');
});

// Development/Debug routes
Route::middleware(['web'])->group(function () {
    Route::get('/debug', function () {
        if (!app()->environment(['local', 'development'])) {
            abort(404);
        }

        return response()->json([
            'message' => 'Web routes çalışıyor',
            'timestamp' => now(),
            'auth_check' => auth()->check(),
            'user' => auth()->user()?->only(['id', 'name', 'email']),
            'environment' => app()->environment(),
        ]);
    })->name('debug');
});

// API Documentation routes
Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('api-docs.json', function () {
        $path = storage_path('api-docs' . DIRECTORY_SEPARATOR . 'api-docs.json');

        if (!file_exists($path)) {
            return response()->json([
                'error' => 'API documentation not found',
                'message' => 'Swagger dokümantasyonu bulunamadı. Lütfen php artisan l5-swagger:generate komutunu çalıştırın.',
                'path' => $path,
                'exists' => file_exists($path)
            ], 404);
        }

        $content = file_get_contents($path);

        return response($content)
            ->header('Content-Type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    })->name('json');

    Route::get('/', function () {
        $urlToDocs = route('docs.json');

        return view('swagger-ui', [
            'urlToDocs' => $urlToDocs,
            'title' => 'Event Management System API'
        ]);
    })->name('index');
});

// Swagger JSON dosyasını serve etmek için (L5-Swagger'ın kendi route'undan önce)
Route::get('docs/api-docs.json', function () {
    $path = storage_path('api-docs' . DIRECTORY_SEPARATOR . 'api-docs.json');

    if (!file_exists($path)) {
        return response()->json([
            'error' => 'API documentation not found',
            'message' => 'Swagger dokümantasyonu bulunamadı. Lütfen php artisan l5-swagger:generate komutunu çalıştırın.',
            'path' => $path,
            'exists' => file_exists($path)
        ], 404);
    }

    $content = file_get_contents($path);

    return response($content)
        ->header('Content-Type', 'application/json')
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
})->name('l5-swagger.docs');

// L5-Swagger UI route'larını manuel ekleyelim
Route::get('api/documentation', function () {
    $urlToDocs = url('docs/api-docs.json');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Management System API</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@3.52.5/swagger-ui.css" />
    <style>
        html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin:0; background: #fafafa; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@3.52.5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@3.52.5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: '{$urlToDocs}',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                docExpansion: "list",
                filter: true,
                persistAuthorization: true
            });
        };
    </script>
</body>
</html>
HTML;
})->name('l5-swagger.api');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Profile management routes (Jetstream)
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile-information', [ProfileController::class, 'updateProfileInformation'])->name('profile-information.update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::delete('/profile-photo', [ProfileController::class, 'destroyProfilePhoto'])->name('profile-photo.destroy');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Dashboard redirect for backward compatibility
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | ADMIN PANEL ROUTES - SINGLE CONSOLIDATED GROUP
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')->name('admin.')->group(function () {


        // AJAX endpoints
        Route::get('ajax/program-sessions/event-days', [ProgramSessionController::class, 'getEventDays']);
        Route::get('ajax/program-sessions/venues-for-event-day', [ProgramSessionController::class, 'getVenuesForEventDay']);
        Route::get('ajax/program-sessions/categories-for-event', [ProgramSessionController::class, 'getCategoriesForEvent']);

        // Dashboard & Quick Stats
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications');
        Route::get('/quick-stats', [DashboardController::class, 'quickStats'])->name('quick-stats');

        /*
        |--------------------------------------------------------------------------
        | ORGANIZATION MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::resource('organizations', OrganizationController::class);
        Route::prefix('organizations/{organization}')->name('organizations.')->group(function () {
            // User management within organization
            Route::post('/users', [OrganizationController::class, 'attachUser'])->name('users.attach');
            Route::delete('/users/{user}', [OrganizationController::class, 'detachUser'])->name('users.detach');
            Route::patch('/users/{user}/role', [OrganizationController::class, 'updateUserRole'])->name('users.update-role');

            // Organization specific actions
            Route::get('/export', [OrganizationController::class, 'export'])->name('export');
            Route::post('/duplicate', [OrganizationController::class, 'duplicate'])->name('duplicate');
            Route::patch('/toggle-status', [OrganizationController::class, 'toggleStatus'])->name('toggle-status');
        });

        /*
        |--------------------------------------------------------------------------
        | EVENT MANAGEMENT
        |--------------------------------------------------------------------------
        */
        Route::resource('events', EventController::class);
        Route::prefix('events/{event}')->name('events.')->group(function () {
            // Event specific actions
            Route::patch('/toggle-publish', [EventController::class, 'togglePublish'])->name('toggle-publish');
            Route::post('/duplicate', [EventController::class, 'duplicate'])->name('duplicate');
            Route::get('/export', [EventController::class, 'export'])->name('export');
            Route::get('/public-program', [EventController::class, 'publicProgram'])->name('public-program');

            // *** YENİ EKLENEN TIMELINE SHORTCUTS ***
            // Event içinden timeline'a direkt erişim
            Route::get('/timeline', function (Event $event) {
                return redirect()->route('admin.timeline.show', $event->slug);
            })->name('timeline');

            Route::get('/timeline-editor', function (Event $event) {
                return redirect()->route('admin.timeline.edit', $event->slug);
            })->name('timeline-editor');

            // Event days management (nested resource) - PARAMETRE ADINI DÜZELTİYORUZ
            Route::resource('days', EventDayController::class, [
                'parameters' => ['days' => 'eventDay']
            ]);
            Route::prefix('days')->name('days.')->group(function () {
                Route::patch('/sort-order', [EventDayController::class, 'updateSortOrder'])->name('update-sort-order');
                Route::patch('/{eventDay}/toggle-status', [EventDayController::class, 'toggleStatus'])->name('toggle-status');
            });

            // Venues management within event days
            Route::resource('venues', VenueController::class);
            Route::prefix('venues')->name('venues.')->group(function () {
                Route::patch('/sort-order', [VenueController::class, 'updateSortOrder'])->name('update-sort-order');
                Route::patch('/{venue}/toggle-status', [VenueController::class, 'toggleStatus'])->name('toggle-status');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | TIMELINE MANAGEMENT - GÜNCELLENMİŞ
        |--------------------------------------------------------------------------
        */
        Route::prefix('timeline')->name('timeline.')->group(function () {
            // Timeline görünümü
            Route::get('/events/{event:slug}', [TimelineController::class, 'show'])
                ->name('show');

            // Timeline editör
            Route::get('/events/{event:slug}/edit', [TimelineController::class, 'edit'])
                ->name('edit');

            // AJAX timeline data
            Route::get('/events/{event:slug}/data', [TimelineController::class, 'getTimelineData'])
                ->name('data');

            // Timeline export
            Route::post('/events/{event:slug}/export', [TimelineController::class, 'export'])
                ->name('export');

            // *** YENİ EKLENEN SÜRÜKLE-BIRAK ROUTES ***
            Route::post('/events/{event:slug}/update-order', [TimelineController::class, 'updateOrder'])
                ->name('update-order');

            Route::post('/events/{event:slug}/move-session/{session}', [TimelineController::class, 'moveSession'])
                ->name('move-session');

            Route::post('/events/{event:slug}/venues/{venue}/reorder', [TimelineController::class, 'reorderVenueSessions'])
                ->name('venue.reorder');

            Route::post('/events/{event:slug}/validate-move', [TimelineController::class, 'validateMove'])
                ->name('validate-move');

            // Timeline settings (isteğe bağlı)
            Route::get('/settings', [TimelineController::class, 'settings'])
                ->name('settings');

            // Timeline help (isteğe bağlı)
            Route::get('/help', [TimelineController::class, 'help'])
                ->name('help');
        });

        /*
        |--------------------------------------------------------------------------
        | DRAG & DROP OPERATIONS (YENİ)
        |--------------------------------------------------------------------------
        */
        Route::prefix('drag-drop')->name('drag-drop.')->group(function () {
            // Ana editör sayfası
            Route::get('/editor', [DragDropController::class, 'editor'])
                ->name('editor');

            // Event-specific editör
            Route::get('/events/{event:slug}/editor', [DragDropController::class, 'eventEditor'])
                ->name('event-editor');

            // Session taşıma işlemleri
            Route::patch('/sessions/move', [DragDropController::class, 'moveSession'])
                ->name('move-session');

            // Presentation taşıma işlemleri
            Route::patch('/presentations/move', [DragDropController::class, 'movePresentation'])
                ->name('move-presentation');

            // Bulk update işlemleri
            Route::patch('/bulk-update', [DragDropController::class, 'bulkUpdate'])
                ->name('bulk-update');

            // Zaman çakışma kontrolü
            Route::post('/check-conflicts', [DragDropController::class, 'checkConflicts'])
                ->name('check-conflicts');

            // Auto-arrange (otomatik düzenleme)
            Route::post('/auto-arrange', [DragDropController::class, 'autoArrange'])
                ->name('auto-arrange');

            // Save layout (düzen kaydetme)
            Route::post('/save-layout', [DragDropController::class, 'saveLayout'])
                ->name('save-layout');

            // Load layout (düzen yükleme)
            Route::get('/load-layout/{event:slug}', [DragDropController::class, 'loadLayout'])
                ->name('load-layout');
        });

        /*
        |--------------------------------------------------------------------------
        | PROGRAM SESSION MANAGEMENT - GÜNCELLENMİŞ
        |--------------------------------------------------------------------------
        */
        Route::prefix('program-sessions')->name('program-sessions.')->group(function () {
            // Timeline route (mevcut) - DÜZELTME: Sadece timeline görünümü
            Route::get('/timeline', [ProgramSessionController::class, 'timeline'])->name('timeline');
            Route::get('/timeline/{event?}', [ProgramSessionController::class, 'timeline'])->name('timeline.event');

            // Session specific actions (mevcut)
            Route::post('/{programSession}/duplicate', [ProgramSessionController::class, 'duplicate'])->name('duplicate');
            Route::patch('/{programSession}/toggle-status', [ProgramSessionController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('/sort-order', [ProgramSessionController::class, 'updateSortOrder'])->name('update-sort-order');
            Route::get('/for-select', [ProgramSessionController::class, 'getForSelect'])->name('for-select');

            // *** YENİ EKLENEN SÜRÜKLE-BIRAK ROUTES ***
            Route::post('/{programSession}/move-to-venue', [ProgramSessionController::class, 'moveToVenue'])
                ->name('move-to-venue');

            Route::post('/{programSession}/move-to-time', [ProgramSessionController::class, 'moveToTime'])
                ->name('move-to-time');

            Route::post('/bulk-reorder', [ProgramSessionController::class, 'bulkReorder'])
                ->name('bulk-reorder');

            Route::post('/check-time-conflicts', [ProgramSessionController::class, 'checkTimeConflicts'])
                ->name('check-time-conflicts');

            Route::post('/auto-schedule', [ProgramSessionController::class, 'autoSchedule'])
                ->name('auto-schedule');

            Route::get('/{programSession}/move-options', [ProgramSessionController::class, 'getMoveOptions'])
                ->name('move-options');
        });

        Route::resource('program-sessions', ProgramSessionController::class);

        // Program session categories
        Route::resource('categories', ProgramSessionCategoryController::class);
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::post('/{category}/duplicate', [ProgramSessionCategoryController::class, 'duplicate'])->name('duplicate');
            Route::patch('/sort-order', [ProgramSessionCategoryController::class, 'updateSortOrder'])->name('update-sort-order');
            Route::get('/for-select', [ProgramSessionCategoryController::class, 'getForSelect'])->name('for-select');
        });

        /*
        |--------------------------------------------------------------------------
        | PRESENTATION MANAGEMENT - GÜNCELLENMİŞ
        |--------------------------------------------------------------------------
        */

        Route::resource('presentations', PresentationController::class);
        Route::prefix('presentations')->name('presentations.')->group(function () {
            // Mevcut route'lar
            Route::post('/{presentation}/duplicate', [PresentationController::class, 'duplicate'])->name('duplicate');
            Route::patch('/{presentation}/toggle-status', [PresentationController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('/sort-order', [PresentationController::class, 'updateSortOrder'])->name('update-sort-order');

            // *** YENİ EKLENEN ROUTES ***
            Route::post('/{presentation}/move-to-session', [PresentationController::class, 'moveToSession'])
                ->name('move-to-session');

            Route::post('/{presentation}/change-time', [PresentationController::class, 'changeTime'])
                ->name('change-time');

            Route::post('/reorder-in-session', [PresentationController::class, 'reorderInSession'])
                ->name('reorder-in-session');
        });

        /*
        |--------------------------------------------------------------------------
        | PARTICIPANT MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::resource('participants', ParticipantController::class);
        Route::prefix('participants')->name('participants.')->group(function () {
            Route::post('/{participant}/duplicate', [ParticipantController::class, 'duplicate'])->name('duplicate');
            Route::patch('/{participant}/toggle-status', [ParticipantController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/search', [ParticipantController::class, 'search'])->name('search');
            Route::post('/bulk-import', [ParticipantController::class, 'bulkImport'])->name('bulk-import');
        });

        /*
        |--------------------------------------------------------------------------
        | VENUE MANAGEMENT - GÜNCELLENMİŞ
        |--------------------------------------------------------------------------
        */

        Route::resource('venues', VenueController::class);
        Route::prefix('venues')->name('venues.')->group(function () {
            // Mevcut venue actions
            Route::post('/{venue}/duplicate', [VenueController::class, 'duplicate'])->name('duplicate');
            Route::patch('/{venue}/toggle-status', [VenueController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('/sort-order', [VenueController::class, 'updateSortOrder'])->name('update-sort-order');
            Route::post('/{venue}/check-availability', [VenueController::class, 'checkAvailability'])->name('check-availability');

            // AJAX helpers (mevcut)
            Route::get('/for-select', [VenueController::class, 'getForSelect'])->name('for-select');

            // *** YENİ EKLENEN ROUTES ***
            Route::get('/{venue}/capacity-status', [VenueController::class, 'getCapacityStatus'])
                ->name('capacity-status');

            Route::get('/{venue}/sessions-by-day/{eventDay}', [VenueController::class, 'getSessionsByDay'])
                ->name('sessions-by-day');

            Route::post('/{venue}/optimize-schedule', [VenueController::class, 'optimizeSchedule'])
                ->name('optimize-schedule');
        });

        /*
        |--------------------------------------------------------------------------
        | SPONSOR MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::resource('sponsors', SponsorController::class);
        Route::prefix('sponsors')->name('sponsors.')->group(function () {
            Route::post('/{sponsor}/duplicate', [SponsorController::class, 'duplicate'])->name('duplicate');
            Route::patch('/{sponsor}/toggle-status', [SponsorController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('/sort-order', [SponsorController::class, 'updateSortOrder'])->name('update-sort-order');
            Route::get('/for-select', [SponsorController::class, 'getForSelect'])->name('for-select');
        });

        /*
        |--------------------------------------------------------------------------
        | FILE UPLOAD MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::prefix('upload')->name('upload.')->group(function () {
            Route::post('/image', [FileUploadController::class, 'uploadImage'])->name('image');
            Route::post('/document', [FileUploadController::class, 'uploadDocument'])->name('document');
            Route::delete('/{file}', [FileUploadController::class, 'deleteFile'])->name('delete');
        });

        /*
        |--------------------------------------------------------------------------
        | IMPORT MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::prefix('import')->name('import.')->group(function () {
            Route::get('/', [ImportController::class, 'index'])->name('index');
            Route::post('/participants', [ImportController::class, 'participants'])->name('participants');
            Route::post('/venues', [ImportController::class, 'venues'])->name('venues');
            Route::post('/sponsors', [ImportController::class, 'sponsors'])->name('sponsors');
            Route::post('/presentations', [ImportController::class, 'presentations'])->name('presentations');
            Route::get('/templates/{type}', [ImportController::class, 'downloadTemplate'])
                ->name('templates')
                ->where('type', 'participants|venues|sponsors|presentations');
        });

        /*
        |--------------------------------------------------------------------------
        | EXPORT MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::prefix('export')->name('export.')->group(function () {
            // Event specific exports
            Route::get('/events/{event}/program-pdf', [ExportController::class, 'programPdf'])->name('events.program-pdf');
            Route::get('/events/{event}/speakers-pdf', [ExportController::class, 'speakersPdf'])->name('events.speakers-pdf');
            Route::get('/events/{event}/sessions-pdf', [ExportController::class, 'sessionsPdf'])->name('events.sessions-pdf');
            Route::get('/events/{event}/program-excel', [ExportController::class, 'programExcel'])->name('events.program-excel');
            Route::get('/events/{event}/presentations-excel', [ExportController::class, 'presentationsExcel'])->name('events.presentations-excel');
            Route::get('/events/{event}/statistics-excel', [ExportController::class, 'statisticsExcel'])->name('events.statistics-excel');
            Route::get('/events/{event}/program-hierarchical-excel', [ExportController::class, 'programHierarchicalExcel'])->name('events.program-hierarchical-excel');

            // General exports
            Route::get('/participants-excel', [ExportController::class, 'participantsExcel'])->name('participants-excel');
            Route::get('/venues-excel', [ExportController::class, 'venuesExcel'])->name('venues-excel');
            Route::get('/sponsors-excel', [ExportController::class, 'sponsorsExcel'])->name('sponsors-excel');
        });

        /*
        |--------------------------------------------------------------------------
        | AJAX HELPER ROUTES - GÜNCELLENMİŞ
        |--------------------------------------------------------------------------
        */

        Route::prefix('ajax')->name('ajax.')->group(function () {
            // Mevcut route'lar
            Route::get('/events/{event}/days', [EventDayController::class, 'getForSelect'])->name('event-days');
            Route::get('/venues/for-select', [VenueController::class, 'getForSelect'])->name('venues');
            Route::get('/participants/search', [ParticipantController::class, 'search'])->name('participants');
            Route::get('/sponsors/for-select', [SponsorController::class, 'getForSelect'])->name('sponsors');
            Route::get('/categories/for-select', [ProgramSessionCategoryController::class, 'getForSelect'])->name('categories');
            Route::get('/program-sessions/for-select', [ProgramSessionController::class, 'getForSelect'])->name('program-sessions');

            // *** YENİ EKLENEN AJAX ROUTES ***
            Route::get('/events/{event}/timeline-data', [TimelineController::class, 'getTimelineData'])
                ->name('timeline-data');

            Route::get('/events/{event}/available-time-slots', [TimelineController::class, 'getAvailableTimeSlots'])
                ->name('available-time-slots');

            Route::get('/events/{event}/venue-conflicts', [TimelineController::class, 'getVenueConflicts'])
                ->name('venue-conflicts');

            Route::post('/validate-session-move', [ProgramSessionController::class, 'validateMove'])
                ->name('validate-session-move');

            Route::post('/validate-presentation-move', [PresentationController::class, 'validateMove'])
                ->name('validate-presentation-move');
        });

        /*
        |--------------------------------------------------------------------------
        | QUICK ACCESS ROUTES (YENİ)
        |--------------------------------------------------------------------------
        */
        Route::prefix('quick')->name('quick.')->group(function () {
            // Dashboard'dan hızlı erişim
            Route::get('/timeline', [TimelineController::class, 'quickAccess'])->name('timeline');
            Route::get('/drag-drop', [DragDropController::class, 'quickAccess'])->name('drag-drop');
        });
    });
});

/*
|--------------------------------------------------------------------------
| FALLBACK ROUTES
|--------------------------------------------------------------------------
*/

// 404 fallback route for web requests
Route::fallback(function () {
    if (request()->expectsJson() || request()->is('api/*')) {
        return response()->json([
            'error' => '404 - API endpoint bulunamadı',
            'message' => 'İstenen API endpoint mevcut değil.',
            'url' => request()->fullUrl(),
            'suggestion' => 'API dokümantasyonu için /docs adresini ziyaret edin.'
        ], 404);
    }

    return Inertia::render('Errors/404', [
        'status' => 404,
        'message' => 'Sayfa bulunamadı',
        'url' => request()->fullUrl()
    ]);
});
