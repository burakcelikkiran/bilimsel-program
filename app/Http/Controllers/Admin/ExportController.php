<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\ProgramSession;
use App\Models\Participant;
use App\Models\Presentation;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Browsershot\Browsershot;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Export event program as PDF
     */
    /**
     * Export event program as PDF using Vue SSR approach
     */
    public function programPdf(Event $event)
    {
        $this->authorize('view', $event);

        $event->load([
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
                    'presentations' => function ($q) {
                        $q->with(['speakers'])->orderBy('start_time')->orderBy('sort_order');
                    },
                    'moderators'
                ])->orderBy('start_time')->orderBy('sort_order');
            }
        ]);

        // Vue component data hazırla
        $data = [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'start_date' => $event->start_date?->format('d.m.Y'),
                'end_date' => $event->end_date?->format('d.m.Y'),
                'eventDays' => $event->eventDays->map(function ($eventDay) {
                    return [
                        'id' => $eventDay->id,
                        'title' => $eventDay->title,
                        'date' => $eventDay->date->format('d.m.Y l'),
                        'venues' => $eventDay->venues->map(function ($venue) {
                            return [
                                'id' => $venue->id,
                                'name' => $venue->display_name ?? $venue->name,
                                'capacity' => $venue->capacity,
                                'programSessions' => $venue->programSessions->map(function ($session) {
                                    return [
                                        'id' => $session->id,
                                        'title' => $session->title,
                                        'description' => $session->description,
                                        'formatted_time_range' => $session->formatted_time_range,
                                        'formatted_duration' => $session->formatted_duration,
                                        'session_type_display' => $session->session_type_display,
                                        'moderator_title' => $session->moderator_title,
                                        'is_break' => $session->is_break,
                                        'sponsor' => $session->sponsor ? [
                                            'name' => $session->sponsor->name
                                        ] : null,
                                        'categories' => $session->categories->map(function ($category) {
                                            return [
                                                'name' => $category->name,
                                                'color' => $category->color
                                            ];
                                        }),
                                        'moderators' => $session->moderators->map(function ($moderator) {
                                            return [
                                                'full_name' => $moderator->first_name . ' ' . $moderator->last_name,
                                                'title' => $moderator->title,
                                                'affiliation' => $moderator->affiliation
                                            ];
                                        }),
                                        'presentations' => $session->presentations->map(function ($presentation) {
                                            return [
                                                'title' => $presentation->title,
                                                'speakers' => $presentation->speakers->map(function ($speaker) {
                                                    return [
                                                        'full_name' => $speaker->first_name . ' ' . $speaker->last_name,
                                                        'title' => $speaker->title,
                                                        'affiliation' => $speaker->affiliation
                                                    ];
                                                })
                                            ];
                                        })
                                    ];
                                })
                            ];
                        })
                    ];
                })
            ],
            'meta' => [
                'generated_at' => Carbon::now()->format('d.m.Y H:i'),
                'generated_by' => auth()->user()->name,
            ]
        ];

        // HTML template oluştur - Vue component'ını simüle et
        $html = $this->generatePdfHtml($data);

        $filename = "program-{$event->slug}-" . now()->format('Y-m-d') . ".pdf";
        $tempPath = storage_path('app/temp/' . $filename);

        // Temp klasörü oluştur
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        try {
            Browsershot::html($html)
                ->setChromePath($this->getChromePath())
                ->paperSize(210, 297) // A4
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->timeout(60)
                ->save($tempPath);

            return response()->download($tempPath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            // Fallback: HTML response
            return response($html, 200, [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'inline; filename="' . $filename . '.html"'
            ]);
        }
    }


    /**
     * Export event program with hierarchical structure (Session Title + Presentations as sub-items)
     */
    public function programHierarchicalExcel(Event $event)
    {
        $this->authorize('view', $event);

        // Event'in tüm session'larını eventDays üzerinden al
        $event->load([
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
                    'presentations' => function ($q) {
                        $q->with(['speakers'])->orderBy('start_time')->orderBy('sort_order');
                    },
                    'moderators'
                ])->orderBy('start_time')->orderBy('sort_order');
            }
        ]);

        // Sessions'ları flatMap ile topla
        $sessions = $event->eventDays->flatMap(function ($eventDay) {
            return $eventDay->venues->flatMap(function ($venue) use ($eventDay) {
                return $venue->programSessions->map(function ($session) use ($eventDay, $venue) {
                    $session->setRelation('eventDay', $eventDay);
                    $session->setRelation('venue', $venue);
                    return $session;
                });
            });
        });

        $data = collect();

        foreach ($sessions as $session) {
            // Ana oturum başlığı (BÜYÜK BAŞLIK)
            $moderators = $session->moderators->map(function ($moderator) {
                return $moderator->first_name . ' ' . $moderator->last_name;
            })->join(', ');

            $data->push([
                'Saat' => $session->formatted_time_range ?? '',
                'İçerik' => strtoupper($session->title), // BÜYÜK HARFLERLE
                'Moderatör/Konuşmacı' => 'Oturum Başkanları: ' . $moderators,
                'Tip' => 'SESSION_HEADER',
                'Salon' => $session->venue->display_name ?? $session->venue->name,
                'Gün' => $session->eventDay->title,
            ]);

            // Alt sunumlar (Bold alt konular)
            foreach ($session->presentations as $presentation) {
                $speakers = $presentation->speakers->map(function ($speaker) {
                    return $speaker->first_name . ' ' . $speaker->last_name;
                })->join(', ');

                $data->push([
                    'Saat' => '', // Alt konularda saat boş
                    'İçerik' => $presentation->title, // Bu bold olarak formatlanacak
                    'Moderatör/Konuşmacı' => $speakers,
                    'Tip' => 'PRESENTATION',
                    'Salon' => '',
                    'Gün' => '',
                ]);
            }

            // Oturumlar arasına boş satır ekle
            $data->push([
                'Saat' => '',
                'İçerik' => '',
                'Moderatör/Konuşmacı' => '',
                'Tip' => 'SPACER',
                'Salon' => '',
                'Gün' => '',
            ]);
        }

        $filename = "program-hierarchical-{$event->slug}-" . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(new class($data) implements
            \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithStyles,
            \Maatwebsite\Excel\Concerns\WithColumnWidths
        {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function ($item) {
                    // Tip sütununu çıktıda göstermemek için çıkar
                    return [
                        'Saat' => $item['Saat'],
                        'İçerik' => $item['İçerik'],
                        'Moderatör/Konuşmacı' => $item['Moderatör/Konuşmacı'],
                        'Salon' => $item['Salon'],
                        'Gün' => $item['Gün'],
                    ];
                });
            }

            public function headings(): array
            {
                return ['Saat', 'İçerik', 'Moderatör/Konuşmacı', 'Salon', 'Gün'];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                $styles = [];
                $rowIndex = 2; // Header'dan sonra başla

                foreach ($this->data as $item) {
                    if ($item['Tip'] === 'SESSION_HEADER') {
                        // Oturum başlıkları için stil (büyük, kalın, arka plan rengi)
                        $styles["A{$rowIndex}:E{$rowIndex}"] = [
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                                'color' => ['rgb' => 'FFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '4472C4'], // Mavi arka plan
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                        ];
                    } elseif ($item['Tip'] === 'PRESENTATION') {
                        // Alt sunumlar için stil (kalın)
                        $styles["B{$rowIndex}"] = [
                            'font' => [
                                'bold' => true,
                                'size' => 10,
                            ],
                            'alignment' => [
                                'indent' => 1, // Girintili
                            ],
                        ];
                        $styles["C{$rowIndex}"] = [
                            'font' => [
                                'size' => 10,
                            ],
                        ];
                    }
                    $rowIndex++;
                }

                // Header satırı için stil
                $styles['A1:E1'] = [
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2'],
                    ],
                ];

                return $styles;
            }

            public function columnWidths(): array
            {
                return [
                    'A' => 15, // Saat
                    'B' => 50, // İçerik
                    'C' => 30, // Moderatör/Konuşmacı
                    'D' => 15, // Salon
                    'E' => 15, // Gün
                ];
            }
        }, $filename);
    }


    /**
     * Export speakers list as PDF
     */
    public function speakersPdf(Event $event)
    {
        $this->authorize('view', $event);

        // Event'in konuşmacılarını al
        $event->load([
            'eventDays.venues.programSessions.presentations.speakers'
        ]);

        // Tüm konuşmacıları topla
        $speakers = collect();
        foreach ($event->eventDays as $eventDay) {
            foreach ($eventDay->venues as $venue) {
                foreach ($venue->programSessions as $session) {
                    foreach ($session->presentations as $presentation) {
                        foreach ($presentation->speakers as $speaker) {
                            $speakers->push($speaker);
                        }
                    }
                }
            }
        }

        // Unique yap ve sırala
        $speakers = $speakers->unique('id')->sortBy(function ($speaker) {
            return $speaker->last_name . ' ' . $speaker->first_name;
        });

        $data = [
            'event' => $event,
            'speakers' => $speakers,
            'generated_at' => Carbon::now(),
            'generated_by' => auth()->user()->name,
        ];

        // HTML content oluştur
        $html = view('exports.speakers-pdf', $data)->render();

        $filename = "speakers-{$event->slug}-" . now()->format('Y-m-d') . ".pdf";
        $tempPath = storage_path('app/temp/' . $filename);

        // Temp klasörü oluştur
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        try {
            Browsershot::html($html)
                ->setChromePath($this->getChromePath())
                ->paperSize(210, 297) // A4 portrait
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->timeout(60)
                ->save($tempPath);

            return response()->download($tempPath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response($html, 200, [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'inline; filename="' . $filename . '.html"'
            ]);
        }
    }


    /**
     * Export sessions list as PDF
     */
    public function sessionsPdf(Event $event)
    {
        $this->authorize('view', $event);

        $event->load([
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
                    'presentations' => function ($q) {
                        $q->with(['speakers'])->orderBy('start_time')->orderBy('sort_order');
                    },
                    'moderators'
                ])->orderBy('start_time')->orderBy('sort_order');
            }
        ]);

        // Sessions'ları flatMap ile topla
        $sessions = $event->eventDays->flatMap(function ($eventDay) {
            return $eventDay->venues->flatMap(function ($venue) use ($eventDay) {
                return $venue->programSessions->map(function ($session) use ($eventDay, $venue) {
                    $session->setRelation('eventDay', $eventDay);
                    $session->setRelation('venue', $venue);
                    return $session;
                });
            });
        });

        $data = [
            'event' => $event,
            'sessions' => $sessions,
            'generated_at' => Carbon::now(),
            'generated_by' => auth()->user()->name,
        ];

        $html = view('exports.sessions-pdf', $data)->render();

        $filename = "sessions-{$event->slug}-" . now()->format('Y-m-d') . ".pdf";
        $tempPath = storage_path('app/temp/' . $filename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        try {
            Browsershot::html($html)
                ->setChromePath($this->getChromePath())
                ->paperSize(297, 210) // A4 landscape
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->timeout(60)
                ->save($tempPath);

            return response()->download($tempPath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response($html, 200, [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'inline; filename="' . $filename . '.html"'
            ]);
        }
    }

    /**
     * Generate HTML for PDF (Vue component style)
     */
    private function generatePdfHtml($data)
    {
        $eventData = $data['event'];
        $meta = $data['meta'];

        $html = '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($eventData['name']) . ' - Program</title>
    <style>
        @page {
            margin: 15mm 10mm;
            size: A4;
        }
        
        body {
            font-family: "Segoe UI", "DejaVu Sans", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Cover Page Styles */
        .cover-page {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            page-break-after: always;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            box-sizing: border-box;
        }

        .cover-title {
            font-size: 32px;
            font-weight: bold;
            margin: 0 0 20px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            line-height: 1.2;
        }

        .cover-subtitle {
            font-size: 18px;
            margin: 0 0 30px 0;
            opacity: 0.9;
            font-weight: 300;
        }

        .cover-dates {
            font-size: 24px;
            font-weight: 600;
            margin: 30px 0;
            padding: 15px 30px;
            background: rgba(255,255,255,0.2);
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }

        .cover-description {
            font-size: 14px;
            max-width: 600px;
            margin: 30px auto 0;
            line-height: 1.6;
            opacity: 0.9;
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(5px);
        }

        .cover-footer {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            opacity: 0.7;
        }

        /* Content Pages */
        .content-page {
            page-break-before: always;
        }

        /* Page Header for content pages */
        .page-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
            color: white;
            padding: 8px 15px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-event {
            font-weight: bold;
        }

        .header-day {
            opacity: 0.9;
        }

        .header-venue {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
        }

        /* Content area with top margin for fixed header */
        .content-area {
            margin-top: 45px;
            padding: 10px 0;
        }

        /* Venue Change Indicator */
        .venue-change {
            background: linear-gradient(135deg, #059669 0%, #10B981 100%);
            color: white;
            padding: 6px 15px;
            margin: 15px 0 10px 0;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .venue-name {
            display: flex;
            align-items: center;
        }

        .venue-name::before {
            content: "🏛️";
            margin-right: 8px;
        }

        .venue-capacity {
            font-size: 10px;
            background: rgba(255,255,255,0.2);
            padding: 2px 6px;
            border-radius: 10px;
        }

        /* Session Styles */
        .session {
            border: 1px solid #E5E7EB;
            margin-bottom: 12px;
            border-radius: 6px;
            overflow: hidden;
            page-break-inside: avoid;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            background: white;
        }

        .session-header {
            background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
            padding: 10px 15px;
            border-bottom: 1px solid #E5E7EB;
        }

        .session-title {
            font-weight: bold;
            color: #1F2937;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .session-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 10px;
            color: #6B7280;
        }

        .session-meta-item {
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .session-meta-item::before {
            margin-right: 4px;
            font-size: 11px;
        }

        .time::before { content: "🕐"; }
        .duration::before { content: "⏱️"; }
        .type::before { content: "📋"; }
        .break::before { content: "☕"; }

        /* Session Content */
        .session-content {
            padding: 12px 15px;
        }

        .session-description {
            margin-bottom: 12px;
            color: #4B5563;
            line-height: 1.5;
            font-size: 11px;
        }

        /* Moderators */
        .moderators {
            background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 12px;
            border-left: 3px solid #F59E0B;
        }

        .moderators-title {
            font-weight: bold;
            color: #92400E;
            margin-bottom: 4px;
            font-size: 11px;
        }

        .moderator-item {
            color: #78350F;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .moderator-item::before {
            content: "👤";
            margin-right: 5px;
        }

        /* Presentations */
        .presentations {
            margin-top: 12px;
        }

        .presentations-title {
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .presentation {
            background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 100%);
            padding: 10px 12px;
            margin-bottom: 8px;
            border-radius: 4px;
            border-left: 3px solid #0EA5E9;
        }

        .presentation-title {
            font-weight: bold;
            color: #0C4A6E;
            margin-bottom: 4px;
            font-size: 11px;
        }

        .presentation-title::before {
            content: "📊";
            margin-right: 6px;
        }

        .speakers {
            color: #0369A1;
            font-size: 10px;
            line-height: 1.3;
        }

        .speakers::before {
            content: "Konuşmacılar: ";
            font-weight: 600;
        }

        /* Categories */
        .categories {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .category-tag {
            background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 100%);
            color: #7C3AED;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 500;
            border: 1px solid #C4B5FD;
        }

        /* Sponsor */
        .sponsor {
            background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 100%);
            padding: 6px 12px;
            border-radius: 4px;
            margin-top: 10px;
            border-left: 3px solid #22C55E;
            text-align: center;
            font-weight: bold;
            color: #15803D;
            font-size: 11px;
        }

        .sponsor::before {
            content: "🤝 ";
        }

        /* Break Sessions */
        .session.break-session {
            background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%);
            border-color: #FECACA;
        }

        .session.break-session .session-header {
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
        }

        .session.break-session .session-title {
            color: #DC2626;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6B7280;
            background: #F9FAFB;
            border-radius: 6px;
            margin: 20px 0;
        }

        /* Page breaks and print optimizations */
        @media print {
            .page-header {
                position: running(header);
            }
            
            .session {
                page-break-inside: avoid;
            }

            .venue-change {
                page-break-after: avoid;
            }
        }

        @page content {
            @top-center {
                content: element(header);
            }
        }

        .content-page {
            page: content;
        }
    </style>
</head>
<body>';

        // Cover Page
        $html .= '<div class="cover-page">
        <div class="cover-title">' . htmlspecialchars($eventData['name']) . '</div>
        <div class="cover-subtitle">Program Kitapçığı</div>';

        if ($eventData['start_date'] && $eventData['end_date']) {
            $html .= '<div class="cover-dates">' . htmlspecialchars($eventData['start_date']) . ' - ' . htmlspecialchars($eventData['end_date']) . '</div>';
        }

        if ($eventData['description']) {
            $html .= '<div class="cover-description">' . nl2br(htmlspecialchars($eventData['description'])) . '</div>';
        }

        $html .= '<div class="cover-footer">
            <p>Oluşturulma: ' . htmlspecialchars($meta['generated_at']) . ' - ' . htmlspecialchars($meta['generated_by']) . '</p>
        </div>
    </div>';

        // Content Pages
        $html .= '<div class="content-page">';

        $currentDay = '';
        $currentVenue = '';

        // Check if we have content
        if (empty($eventData['eventDays'])) {
            $html .= '<div class="content-area">
            <div class="empty-state">
                <h3>Program Henüz Hazırlanmamış</h3>
                <p>Bu etkinlik için henüz program günü ve oturum bilgileri tanımlanmamış.</p>
            </div>
        </div>';
        } else {
            // Process all sessions in chronological order
            $allSessions = [];

            foreach ($eventData['eventDays'] as $eventDay) {
                foreach ($eventDay['venues'] as $venue) {
                    foreach ($venue['programSessions'] as $session) {
                        $allSessions[] = [
                            'session' => $session,
                            'venue' => $venue,
                            'day' => $eventDay
                        ];
                    }
                }
            }

            if (empty($allSessions)) {
                $html .= '<div class="content-area">
                <div class="empty-state">
                    <h3>Program Oturumu Bulunmuyor</h3>
                    <p>Tanımlanmış günler mevcut ancak henüz program oturumu eklenmemiş.</p>
                </div>
            </div>';
            } else {
                $html .= '<div class="content-area">';

                foreach ($allSessions as $index => $item) {
                    $session = $item['session'];
                    $venue = $item['venue'];
                    $day = $item['day'];

                    // Check if we need to show day or venue change
                    $dayChanged = $currentDay !== $day['title'];
                    $venueChanged = $currentVenue !== $venue['name'];

                    if ($dayChanged || $venueChanged) {
                        // Page header with current day and venue info
                        $html .= '<div class="page-header">
                        <div class="header-left">
                            <span class="header-event">' . htmlspecialchars($eventData['name']) . '</span>
                            <span class="header-day">' . htmlspecialchars($day['title']) . ' - ' . htmlspecialchars($day['date']) . '</span>
                        </div>
                        <div class="header-venue">' . htmlspecialchars($venue['name']);

                        if ($venue['capacity']) {
                            $html .= ' (Kapasite: ' . $venue['capacity'] . ')';
                        }

                        $html .= '</div>
                    </div>';

                        $currentDay = $day['title'];
                        $currentVenue = $venue['name'];
                    }

                    // Show venue change indicator if venue changed but not day
                    if (!$dayChanged && $venueChanged && $index > 0) {
                        $html .= '<div class="venue-change">
                        <div class="venue-name">' . htmlspecialchars($venue['name']) . '</div>';

                        if ($venue['capacity']) {
                            $html .= '<div class="venue-capacity">Kapasite: ' . $venue['capacity'] . '</div>';
                        }

                        $html .= '</div>';
                    }

                    // Session content
                    $sessionClasses = 'session';
                    if ($session['is_break']) {
                        $sessionClasses .= ' break-session';
                    }

                    $html .= '<div class="' . $sessionClasses . '">
                    <div class="session-header">
                        <div class="session-title">' . htmlspecialchars($session['title']) . '</div>
                        <div class="session-meta">
                            <span class="session-meta-item time">' . htmlspecialchars($session['formatted_time_range']) . '</span>
                            <span class="session-meta-item duration">' . htmlspecialchars($session['formatted_duration']) . '</span>
                            <span class="session-meta-item type">' . htmlspecialchars($session['session_type_display']) . '</span>';

                    if ($session['is_break']) {
                        $html .= '<span class="session-meta-item break">Ara</span>';
                    }

                    $html .= '</div>
                    </div>
                    <div class="session-content">';

                    // Description
                    if ($session['description']) {
                        $html .= '<div class="session-description">' . nl2br(htmlspecialchars($session['description'])) . '</div>';
                    }

                    // Moderators
                    if (!empty($session['moderators'])) {
                        $html .= '<div class="moderators">
                        <div class="moderators-title">' . htmlspecialchars($session['moderator_title'] ?? 'Moderatörler') . '</div>';

                        foreach ($session['moderators'] as $moderator) {
                            $html .= '<div class="moderator-item">' . htmlspecialchars($moderator['full_name']);
                            if ($moderator['title']) {
                                $html .= ' - ' . htmlspecialchars($moderator['title']);
                            }
                            if ($moderator['affiliation']) {
                                $html .= ' (' . htmlspecialchars($moderator['affiliation']) . ')';
                            }
                            $html .= '</div>';
                        }

                        $html .= '</div>';
                    }

                    // Presentations
                    if (!empty($session['presentations'])) {
                        $html .= '<div class="presentations">
                        <div class="presentations-title">Sunumlar</div>';

                        foreach ($session['presentations'] as $presentation) {
                            $html .= '<div class="presentation">
                            <div class="presentation-title">' . htmlspecialchars($presentation['title']) . '</div>';

                            if (!empty($presentation['speakers'])) {
                                $speakerNames = [];
                                foreach ($presentation['speakers'] as $speaker) {
                                    $speakerName = htmlspecialchars($speaker['full_name']);
                                    if ($speaker['title']) {
                                        $speakerName .= ' (' . htmlspecialchars($speaker['title']) . ')';
                                    }
                                    $speakerNames[] = $speakerName;
                                }
                                $html .= '<div class="speakers">' . implode(', ', $speakerNames) . '</div>';
                            }

                            $html .= '</div>';
                        }

                        $html .= '</div>';
                    }

                    // Categories
                    if (!empty($session['categories'])) {
                        $html .= '<div class="categories">';
                        foreach ($session['categories'] as $category) {
                            $html .= '<span class="category-tag">' . htmlspecialchars($category['name']) . '</span>';
                        }
                        $html .= '</div>';
                    }

                    // Sponsor
                    if ($session['sponsor']) {
                        $html .= '<div class="sponsor">Sponsor: ' . htmlspecialchars($session['sponsor']['name']) . '</div>';
                    }

                    $html .= '</div></div>'; // session-content, session
                }

                $html .= '</div>'; // content-area
            }
        }

        $html .= '</div>'; // content-page
        $html .= '</body></html>';

        return $html;
    }


    /**
     * Get Chrome executable path based on OS
     */
    private function getChromePath(): ?string
    {
        // Windows
        if (PHP_OS_FAMILY === 'Windows') {
            $paths = [
                'C:\Program Files\Google\Chrome\Application\chrome.exe',
                'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
                'C:\Users\\' . get_current_user() . '\AppData\Local\Google\Chrome\Application\chrome.exe',
            ];
        }
        // macOS
        elseif (PHP_OS_FAMILY === 'Darwin') {
            $paths = [
                '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
                '/usr/bin/google-chrome',
            ];
        }
        // Linux
        else {
            $paths = [
                '/usr/bin/google-chrome',
                '/usr/bin/google-chrome-stable',
                '/usr/bin/chromium-browser',
                '/usr/bin/chromium',
                '/snap/bin/chromium',
            ];
        }

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Node modules'den puppeteer chrome'u dene
        $puppeteerChrome = base_path('node_modules/puppeteer/.local-chromium');
        if (is_dir($puppeteerChrome)) {
            $chromeVersions = glob($puppeteerChrome . '/*/');
            if (!empty($chromeVersions)) {
                $latestVersion = end($chromeVersions);
                if (PHP_OS_FAMILY === 'Windows') {
                    $chromePath = $latestVersion . 'chrome-win/chrome.exe';
                } elseif (PHP_OS_FAMILY === 'Darwin') {
                    $chromePath = $latestVersion . 'chrome-mac/Chromium.app/Contents/MacOS/Chromium';
                } else {
                    $chromePath = $latestVersion . 'chrome-linux/chrome';
                }

                if (file_exists($chromePath)) {
                    return $chromePath;
                }
            }
        }

        return null;
    }

    /**
     * Export participants as Excel
     */
    public function participantsExcel(Request $request)
    {
        $organizationId = auth()->user()->currentOrganization->id;

        $participants = Participant::where('organization_id', $organizationId)
            ->when($request->event_id, function ($query, $eventId) {
                // Event'e bağlı konuşmacıları filtrele
                $query->whereHas('presentations.programSession', function ($q) use ($eventId) {
                    $q->whereHas('venue.eventDay.event', function ($subQ) use ($eventId) {
                        $subQ->where('id', $eventId);
                    });
                });
            })
            ->with(['organization'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $data = $participants->map(function ($participant) {
            return [
                'ID' => $participant->id,
                'Ad' => $participant->first_name,
                'Soyad' => $participant->last_name,
                'E-posta' => $participant->email,
                'Telefon' => $participant->phone,
                'Ünvan' => $participant->title,
                'Kurum' => $participant->affiliation,
                'Biyografi' => $participant->bio,
                'Konuşmacı' => $participant->is_speaker ? 'Evet' : 'Hayır',
                'Moderatör' => $participant->is_moderator ? 'Evet' : 'Hayır',
                'Oluşturma Tarihi' => $participant->created_at->format('d.m.Y H:i'),
            ];
        });

        $filename = 'participants-' . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return array_keys($this->data->first() ?? []);
            }
        }, $filename);
    }


    /**
     * Export event program as Excel
     */
    public function programExcel(Event $event)
    {
        $this->authorize('view', $event);

        // Event'in tüm session'larını eventDays üzerinden al
        $event->load([
            'eventDays' => function ($query) {
                $query->where('is_active', true)->orderBy('date');
            },
            'eventDays.venues' => function ($query) {
                $query->orderBy('sort_order');
            },
            'eventDays.venues.programSessions' => function ($query) {
                $query->with([
                    'sponsor', // tekil sponsor
                    'categories', // categories relationship
                    'presentations' => function ($q) {
                        $q->with(['speakers'])->orderBy('start_time')->orderBy('sort_order');
                    },
                    'moderators'
                ])->orderBy('start_time')->orderBy('sort_order');
            }
        ]);

        // Sessions'ları flatMap ile topla
        $sessions = $event->eventDays->flatMap(function ($eventDay) {
            return $eventDay->venues->flatMap(function ($venue) use ($eventDay) {
                return $venue->programSessions->map(function ($session) use ($eventDay, $venue) {
                    $session->setRelation('eventDay', $eventDay);
                    $session->setRelation('venue', $venue);
                    return $session;
                });
            });
        });

        $data = $sessions->map(function ($session) {
            $presentations = $session->presentations->map(function ($presentation) {
                $speakers = $presentation->speakers->map(function ($speaker) {
                    return $speaker->first_name . ' ' . $speaker->last_name;
                })->join(', ');
                return $presentation->title . ($speakers ? " - {$speakers}" : '');
            })->join(' | ');

            $moderators = $session->moderators->map(function ($moderator) {
                return $moderator->first_name . ' ' . $moderator->last_name;
            })->join(', ');

            $categories = $session->categories->pluck('name')->join(', ');

            return [
                'Gün' => $session->eventDay->title ?? '',
                'Tarih' => $session->eventDay->date ? $session->eventDay->date->format('d.m.Y') : '',
                'Başlangıç' => $session->start_time ? $session->start_time->format('H:i') : '',
                'Bitiş' => $session->end_time ? $session->end_time->format('H:i') : '',
                'Salon' => $session->venue->display_name ?? $session->venue->name ?? '',
                'Kategoriler' => $categories,
                'Oturum Başlığı' => $session->title ?? '',
                'Oturum Açıklaması' => $session->description ?? '',
                'Oturum Türü' => $session->session_type_display ?? $session->session_type ?? '',
                'Moderatörler' => $moderators,
                'Moderatör Başlığı' => $session->moderator_title ?? '',
                'Sunumlar' => $presentations,
                'Sponsor' => $session->sponsor->name ?? '', // tekil sponsor
                'Ara Oturum' => $session->is_break ? 'Evet' : 'Hayır',
                'Süre (dk)' => $session->duration_in_minutes ?? 0,
                'Sıra' => $session->sort_order ?? 0,
            ];
        });

        $filename = "program-{$event->slug}-" . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return array_keys($this->data->first() ?? []);
            }
        }, $filename);
    }


    /**
     * Export presentations as Excel
     */
    public function presentationsExcel(Event $event)
    {
        $this->authorize('view', $event);

        // Event'in tüm presentations'larını al
        $event->load([
            'eventDays.venues.programSessions.presentations.speakers'
        ]);

        // Presentations'ları topla
        $presentations = collect();
        foreach ($event->eventDays as $eventDay) {
            foreach ($eventDay->venues as $venue) {
                foreach ($venue->programSessions as $session) {
                    foreach ($session->presentations as $presentation) {
                        $presentation->setRelation('programSession', $session);
                        $presentation->programSession->setRelation('eventDay', $eventDay);
                        $presentation->programSession->setRelation('venue', $venue);
                        $presentations->push($presentation);
                    }
                }
            }
        }

        $data = $presentations->map(function ($presentation) {
            $speakers = $presentation->speakers->map(function ($speaker) {
                return $speaker->first_name . ' ' . $speaker->last_name . ' (' . ($speaker->pivot->speaker_role ?? 'speaker') . ')';
            })->join(', ');

            return [
                'ID' => $presentation->id,
                'Gün' => $presentation->programSession->eventDay->title ?? '',
                'Tarih' => $presentation->programSession->eventDay->date ? $presentation->programSession->eventDay->date->format('d.m.Y') : '',
                'Başlangıç' => $presentation->start_time ? $presentation->start_time->format('H:i') : ($presentation->programSession->start_time ? $presentation->programSession->start_time->format('H:i') : ''),
                'Salon' => $presentation->programSession->venue->display_name ?? $presentation->programSession->venue->name ?? '',
                'Oturum' => $presentation->programSession->title ?? '',
                'Sunum Başlığı' => $presentation->title ?? '',
                'Sunum Türü' => $presentation->presentation_type ?? '',
                'Süre (dk)' => $presentation->duration_minutes ?? '',
                'Dil' => $presentation->language ?? '',
                'Özet' => $presentation->abstract ?? '',
                'Konuşmacılar' => $speakers,
                'Sıra' => $presentation->sort_order ?? 0,
            ];
        });

        $filename = "presentations-{$event->slug}-" . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return array_keys($this->data->first() ?? []);
            }
        }, $filename);
    }

    /**
     * Export venues as Excel
     */
    public function venuesExcel()
    {
        $organizationId = auth()->user()->currentOrganization->id;

        // Venues'ları organization üzerinden al
        $venues = \App\Models\Venue::whereHas('eventDay.event', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })
            ->withCount('programSessions')
            ->with(['eventDay.event'])
            ->orderBy('sort_order')
            ->get();

        $data = $venues->map(function ($venue) {
            return [
                'ID' => $venue->id,
                'Ad' => $venue->name,
                'Görünen Ad' => $venue->display_name ?? $venue->name,
                'Kapasite' => $venue->capacity,
                'Renk' => $venue->color,
                'Etkinlik' => $venue->eventDay->event->name ?? '',
                'Gün' => $venue->eventDay->title ?? '',
                'Oturum Sayısı' => $venue->program_sessions_count ?? 0,
                'Sıra' => $venue->sort_order,
                'Oluşturma Tarihi' => $venue->created_at->format('d.m.Y H:i'),
            ];
        });

        $filename = 'venues-' . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return array_keys($this->data->first() ?? []);
            }
        }, $filename);
    }

    /**
     * Export sponsors as Excel
     */
    public function sponsorsExcel()
    {
        $organizationId = auth()->user()->currentOrganization->id;

        $sponsors = \App\Models\Sponsor::where('organization_id', $organizationId)
            ->withCount(['programSessions', 'presentations'])
            ->orderBy('name')
            ->get();

        $data = $sponsors->map(function ($sponsor) {
            return [
                'ID' => $sponsor->id,
                'Ad' => $sponsor->name,
                'Açıklama' => $sponsor->description,
                'Website' => $sponsor->website_url,
                'Logo' => $sponsor->logo_url ? 'Var' : 'Yok',
                'Oturum Sayısı' => $sponsor->program_sessions_count ?? 0,
                'Sunum Sayısı' => $sponsor->presentations_count ?? 0,
                'Oluşturma Tarihi' => $sponsor->created_at->format('d.m.Y H:i'),
            ];
        });

        $filename = 'sponsors-' . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return array_keys($this->data->first() ?? []);
            }
        }, $filename);
    }

    /**
     * Export event statistics as Excel
     */
    public function statisticsExcel(Event $event)
    {
        $this->authorize('view', $event);

        // Event'i yükle
        $event->load([
            'eventDays.venues.programSessions.presentations.speakers'
        ]);

        // Prepare different sheets of data
        $sheets = [];

        // Event Overview
        $totalSessions = $event->eventDays->sum(function ($day) {
            return $day->venues->sum(function ($venue) {
                return $venue->programSessions->count();
            });
        });

        $totalPresentations = $event->eventDays->sum(function ($day) {
            return $day->venues->sum(function ($venue) {
                return $venue->programSessions->sum(function ($session) {
                    return $session->presentations->count();
                });
            });
        });

        $uniqueSpeakers = collect();
        foreach ($event->eventDays as $day) {
            foreach ($day->venues as $venue) {
                foreach ($venue->programSessions as $session) {
                    foreach ($session->presentations as $presentation) {
                        foreach ($presentation->speakers as $speaker) {
                            $uniqueSpeakers->push($speaker->id);
                        }
                    }
                }
            }
        }
        $totalSpeakers = $uniqueSpeakers->unique()->count();

        $uniqueSponsors = collect();
        foreach ($event->eventDays as $day) {
            foreach ($day->venues as $venue) {
                foreach ($venue->programSessions as $session) {
                    if ($session->sponsor) {
                        $uniqueSponsors->push($session->sponsor->id);
                    }
                }
            }
        }
        $totalSponsors = $uniqueSponsors->unique()->count();

        $sheets['Etkinlik Özeti'] = collect([[
            'Etkinlik' => $event->name,
            'Başlangıç Tarihi' => $event->start_date?->format('d.m.Y') ?? '',
            'Bitiş Tarihi' => $event->end_date?->format('d.m.Y') ?? '',
            'Gün Sayısı' => $event->eventDays->count(),
            'Salon Sayısı' => $event->eventDays->sum(fn($day) => $day->venues->count()),
            'Toplam Oturum' => $totalSessions,
            'Toplam Sunum' => $totalPresentations,
            'Toplam Konuşmacı' => $totalSpeakers,
            'Sponsor Sayısı' => $totalSponsors,
        ]]);

        // Daily Statistics
        $dailyStats = $event->eventDays->map(function ($day) {
            $sessionsCount = $day->venues->sum(fn($venue) => $venue->programSessions->count());
            $presentationsCount = $day->venues->sum(function ($venue) {
                return $venue->programSessions->sum(fn($session) => $session->presentations->count());
            });

            return [
                'Gün' => $day->title,
                'Tarih' => $day->date?->format('d.m.Y') ?? '',
                'Oturum Sayısı' => $sessionsCount,
                'Sunum Sayısı' => $presentationsCount,
            ];
        });
        $sheets['Günlük İstatistikler'] = $dailyStats;

        // Venue Statistics
        $venueStats = collect();
        foreach ($event->eventDays as $day) {
            foreach ($day->venues as $venue) {
                $sessionsCount = $venue->programSessions->count();
                $totalDuration = $venue->programSessions->sum(function ($session) {
                    if ($session->start_time && $session->end_time) {
                        return $session->start_time->diffInMinutes($session->end_time);
                    }
                    return 0;
                });

                $venueStats->push([
                    'Salon' => $venue->display_name ?? $venue->name,
                    'Kapasite' => $venue->capacity ?? 0,
                    'Oturum Sayısı' => $sessionsCount,
                    'Toplam Süre (dk)' => $totalDuration,
                ]);
            }
        }
        $sheets['Salon İstatistikleri'] = $venueStats;

        $filename = "statistics-{$event->slug}-" . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(new class($sheets) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
            private $sheets;

            public function __construct($sheets)
            {
                $this->sheets = $sheets;
            }

            public function sheets(): array
            {
                $excelSheets = [];

                foreach ($this->sheets as $title => $data) {
                    $excelSheets[] = new class($title, $data) implements FromCollection, WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle {
                        private $title;
                        private $data;

                        public function __construct($title, $data)
                        {
                            $this->title = $title;
                            $this->data = $data;
                        }

                        public function collection()
                        {
                            return $this->data;
                        }

                        public function headings(): array
                        {
                            return array_keys($this->data->first() ?? []);
                        }

                        public function title(): string
                        {
                            return $this->title;
                        }
                    };
                }

                return $excelSheets;
            }
        }, $filename);
    }
}
