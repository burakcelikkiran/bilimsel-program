<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\Venue;
use App\Models\ProgramSession;
use App\Models\Presentation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class EventProgramController extends Controller
{
    /**
     * Get full event program as JSON
     * GET /api/v1/events/{slug}/program
     */
    public function getProgram(Request $request, string $slug): JsonResponse
    {
        try {
            $event = Event::with([
                'organization',
                'eventDays' => function ($query) {
                    $query->where('is_active', true)->orderBy('date');
                },
                'eventDays.venues' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'eventDays.venues.programSessions' => function ($query) {
                    $query->with([
                        'sponsor',
                        'categories',
                        'presentations.speakers',
                        'moderators'
                    ])->orderBy('start_time');
                },
            ])
                ->where('slug', $slug)
                ->where('is_published', true)
                ->firstOrFail();

            // Statistics hesaplama - manuel ve güvenli
            $totalVenues = 0;
            $totalSessions = 0;
            $totalPresentations = 0;

            foreach ($event->eventDays as $day) {
                $totalVenues += $day->venues->count();
                foreach ($day->venues as $venue) {
                    if ($venue->programSessions) {
                        $totalSessions += $venue->programSessions->count();
                        foreach ($venue->programSessions as $session) {
                            if ($session->presentations) {
                                $totalPresentations += $session->presentations->count();
                            }
                        }
                    }
                }
            }

            // Build program structure
            $program = [
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'description' => $event->description,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                    'timezone' => $event->timezone,
                    'venue_address' => $event->venue_address,
                    'contact_email' => $event->contact_email,
                    'contact_phone' => $event->contact_phone,
                    'website_url' => $event->website_url,
                    'organization' => $event->organization ? [
                        'id' => $event->organization->id,
                        'name' => $event->organization->name,
                        'website_url' => $event->organization->website_url ?? null,
                    ] : null,
                ],
                'statistics' => [
                    'total_days' => $event->eventDays->count(),
                    'total_venues' => $totalVenues,
                    'total_sessions' => $totalSessions,
                    'total_presentations' => $totalPresentations,
                ],
                'days' => $event->eventDays->map(function ($day) {
                    return [
                        'id' => $day->id,
                        'title' => $day->title,
                        'date' => $day->date,
                        'formatted_date' => $day->date->format('d M Y'),
                        'day_name' => $day->date->format('l'),
                        'venues' => $day->venues->map(function ($venue) {
                            return [
                                'id' => $venue->id,
                                'name' => $venue->name,
                                'display_name' => $venue->display_name,
                                'color' => $venue->color,
                                'capacity' => $venue->capacity,
                                'sessions' => $venue->programSessions ? $venue->programSessions->map(function ($session) {
                                    return [
                                        'id' => $session->id,
                                        'title' => $session->title,
                                        'description' => $session->description,
                                        'start_time' => $session->start_time,
                                        'end_time' => $session->end_time,
                                        'session_type' => $session->session_type,
                                        'is_break' => $session->is_break ?? false,
                                        'moderator_title' => $session->moderator_title,
                                        'sponsor' => $session->sponsor ? [
                                            'id' => $session->sponsor->id,
                                            'name' => $session->sponsor->name,
                                            'logo' => $session->sponsor->logo ? 
                                                asset('storage/' . $session->sponsor->logo) : null,
                                        ] : null,
                                        'categories' => $session->categories ? $session->categories->map(function ($category) {
                                            return [
                                                'id' => $category->id,
                                                'name' => $category->name,
                                                'color' => $category->color,
                                            ];
                                        }) : [],
                                        'moderators' => $session->moderators ? $session->moderators->map(function ($moderator) {
                                            return [
                                                'id' => $moderator->id,
                                                'full_name' => $moderator->full_name ?? ($moderator->first_name . ' ' . $moderator->last_name),
                                                'title' => $moderator->title,
                                                'institution' => $moderator->institution,
                                            ];
                                        }) : [],
                                        'presentations' => $session->presentations ? $session->presentations->map(function ($presentation) {
                                            return [
                                                'id' => $presentation->id,
                                                'title' => $presentation->title,
                                                'abstract' => $presentation->abstract,
                                                'sort_order' => $presentation->sort_order,
                                                'speakers' => $presentation->speakers ? $presentation->speakers->map(function ($speaker) {
                                                    return [
                                                        'id' => $speaker->id,
                                                        'full_name' => $speaker->full_name ?? ($speaker->first_name . ' ' . $speaker->last_name),
                                                        'title' => $speaker->title,
                                                        'institution' => $speaker->institution,
                                                        'bio' => $speaker->bio,
                                                    ];
                                                }) : [],
                                            ];
                                        }) : [],
                                    ];
                                }) : [],
                            ];
                        }),
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $program,
                'generated_at' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Get program for a specific day
     * GET /api/v1/events/{slug}/program/day/{date}
     */
    public function getDayProgram(Request $request, string $slug, string $date): JsonResponse
    {
        try {
            $event = Event::where('slug', $slug)
                ->where('is_published', true)
                ->firstOrFail();

            $eventDay = EventDay::with([
                'venues.programSessions' => function ($query) {
                    $query->with([
                        'categories',
                        'presentations.speakers',
                        'moderators',
                        'sponsor'
                    ])->orderBy('start_time');
                }
            ])
                ->where('event_id', $event->id)
                ->where('date', $date)
                ->where('is_active', true)
                ->firstOrFail();

            $dayProgram = [
                'day' => [
                    'id' => $eventDay->id,
                    'title' => $eventDay->title,
                    'date' => $eventDay->date,
                    'formatted_date' => $eventDay->date->format('d M Y'),
                    'day_name' => $eventDay->date->format('l'),
                ],
                'venues' => $eventDay->venues->map(function ($venue) {
                    return [
                        'id' => $venue->id,
                        'name' => $venue->name,
                        'display_name' => $venue->display_name,
                        'capacity' => $venue->capacity,
                        'color' => $venue->color,
                        'sessions' => $venue->programSessions ? $venue->programSessions->map(function ($session) {
                            return [
                                'id' => $session->id,
                                'title' => $session->title,
                                'description' => $session->description,
                                'start_time' => $session->start_time,
                                'end_time' => $session->end_time,
                                'session_type' => $session->session_type,
                                'is_break' => $session->is_break ?? false,
                                'presentations' => $session->presentations ? $session->presentations->map(function ($presentation) {
                                    return [
                                        'id' => $presentation->id,
                                        'title' => $presentation->title,
                                        'speakers' => $presentation->speakers ? $presentation->speakers->pluck('full_name')->filter() : [],
                                    ];
                                }) : [],
                            ];
                        }) : [],
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $dayProgram,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Günlük program yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get sessions by venue - DÜZELTME
     * GET /api/v1/events/{slug}/program/venue/{venue}
     */
    public function getVenueSessions(Request $request, string $slug, int $venueId): JsonResponse
    {
        try {
            $event = Event::where('slug', $slug)
                ->where('is_published', true)
                ->firstOrFail();

            $venue = Venue::with([
                'eventDay',
                'programSessions' => function ($query) {
                    $query->with([
                        'categories',
                        'presentations.speakers',
                        'moderators',
                        'sponsor'
                    ])->orderBy('start_time');
                }
            ])->findOrFail($venueId);

            // Verify venue belongs to this event - DÜZELTME
            if (!$venue->eventDay || $venue->eventDay->event_id !== $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mekan bu etkinlikte bulunamadı.',
                ], 404);
            }

            $venueData = [
                'venue' => [
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'display_name' => $venue->display_name,
                    'description' => $venue->description ?? null,
                    'capacity' => $venue->capacity,
                    'color' => $venue->color,
                ],
                'day' => [
                    'id' => $venue->eventDay->id,
                    'title' => $venue->eventDay->title,
                    'date' => $venue->eventDay->date,
                    'formatted_date' => $venue->eventDay->date->format('d M Y'),
                ],
                'sessions' => $venue->programSessions ? $venue->programSessions->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'title' => $session->title,
                        'description' => $session->description,
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                        'session_type' => $session->session_type,
                        'sponsor' => $session->sponsor ? [
                            'id' => $session->sponsor->id,
                            'name' => $session->sponsor->name,
                        ] : null,
                        'categories' => $session->categories ? $session->categories->pluck('name') : [],
                        'moderators' => $session->moderators ? $session->moderators->map(function ($moderator) {
                            return $moderator->full_name ?? ($moderator->first_name . ' ' . $moderator->last_name);
                        })->filter() : [],
                        'presentations_count' => $session->presentations ? $session->presentations->count() : 0,
                        'presentations' => $session->presentations ? $session->presentations->map(function ($presentation) {
                            return [
                                'id' => $presentation->id,
                                'title' => $presentation->title,
                                'speakers' => $presentation->speakers ? $presentation->speakers->map(function ($speaker) {
                                    return $speaker->full_name ?? ($speaker->first_name . ' ' . $speaker->last_name);
                                })->filter() : [],
                            ];
                        }) : [],
                    ];
                }) : [],
            ];

            return response()->json([
                'success' => true,
                'data' => $venueData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mekan oturumları yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Search in program
     * GET /api/v1/events/{slug}/program/search
     */
    public function search(Request $request, string $slug): JsonResponse
    {
        try {
            $query = $request->input('q');
            
            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arama terimi gerekli.',
                ], 400);
            }

            $event = Event::where('slug', $slug)
                ->where('is_published', true)
                ->firstOrFail();

            // Basit arama - sadece mevcut data ile
            $results = [
                'sessions' => [],
                'presentations' => [],
                'speakers' => [],
            ];

            // Session arama
            try {
                if (class_exists('App\Models\ProgramSession')) {
                    $sessions = ProgramSession::whereHas('venue.eventDay', function ($q) use ($event) {
                        $q->where('event_id', $event->id);
                    })
                    ->where(function ($q) use ($query) {
                        $q->where('title', 'like', "%{$query}%")
                          ->orWhere('description', 'like', "%{$query}%");
                    })
                    ->with(['venue.eventDay'])
                    ->limit(10)
                    ->get();

                    $results['sessions'] = $sessions->map(function ($session) {
                        return [
                            'type' => 'session',
                            'id' => $session->id,
                            'title' => $session->title,
                            'venue_name' => $session->venue->name,
                            'day_date' => $session->venue->eventDay->date,
                        ];
                    });
                }
            } catch (\Exception $e) {
                // ProgramSession model/relationship doesn't exist
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'total_results' => count($results['sessions']) + count($results['presentations']) + count($results['speakers']),
                'query' => $query,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arama yapılırken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Download program as PDF
     * GET /api/v1/events/{slug}/program/download/pdf
     */
    public function downloadPdf(Request $request, string $slug)
    {
        try {
            return response()->json([
                'success' => false,
                'message' => 'PDF export henüz implemente edilmedi.',
                'note' => 'Blade view template gerekli: resources/views/exports/program-pdf.blade.php'
            ], 501);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'PDF oluşturulurken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Download program as CSV
     * GET /api/v1/events/{slug}/program/download/csv
     */
    public function downloadCsv(Request $request, string $slug)
    {
        try {
            $event = Event::where('slug', $slug)
                ->where('is_published', true)
                ->firstOrFail();

            $filename = $event->slug . '-program.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($event) {
                $file = fopen('php://output', 'w');
                
                // UTF-8 BOM for Excel compatibility
                fprintf($file, "\xEF\xBB\xBF");
                
                // CSV headers
                fputcsv($file, [
                    'Event ID',
                    'Event Name',
                    'Venue ID',
                    'Venue Name',
                    'Day',
                    'Date',
                    'Status'
                ]);

                // Basic event data
                fputcsv($file, [
                    $event->id,
                    $event->name,
                    'N/A',
                    'N/A',
                    'N/A',
                    $event->start_date->format('Y-m-d'),
                    'Published'
                ]);

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'CSV oluşturulurken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}