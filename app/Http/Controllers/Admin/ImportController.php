<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Models\Sponsor;
use App\Models\Venue;
use App\Services\ParticipantImporter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show import page
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Import/Index');
    }

    /**
     * Import participants from Excel/CSV
     */
    public function participants(Request $request, ParticipantImporter $participantImporter): RedirectResponse
    {
        $this->authorize('import', Participant::class);

        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls|max:10240',
            'organization_id' => 'required|exists:organizations,id',
            'update_existing' => 'boolean',
        ]);

        $user = auth()->user();

        if (! $user->isAdmin()) {
            $hasAccess = $user->organizations()
                ->where('organizations.id', $validated['organization_id'])
                ->exists();

            if (! $hasAccess) {
                abort(403);
            }
        }

        try {
            DB::beginTransaction();

            $result = $participantImporter->importFromFile(
                $request->file('file'),
                (int) $validated['organization_id'],
                $request->boolean('update_existing')
            );

            DB::commit();

            return back()->with('success', $participantImporter->buildSummaryMessage($result));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'İçe aktarma hatası: '.$e->getMessage()]);
        }
    }

    /**
     * Import venues from Excel/CSV
     */
    public function venues(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:5120',
            'update_existing' => 'boolean',
        ]);

        $organizationId = auth()->user()->currentOrganization->id;
        $updateExisting = $request->boolean('update_existing');

        try {
            DB::beginTransaction();

            $data = Excel::toArray([], $request->file('file'))[0];

            if (empty($data)) {
                throw new \Exception('Dosya boş veya okunamıyor.');
            }

            $headers = array_shift($data);
            $headers = array_map('trim', $headers);

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($data as $rowIndex => $row) {
                $rowData = array_combine($headers, $row);
                $rowData = array_map('trim', $rowData);

                if (empty($rowData['ad'])) {
                    continue;
                }

                try {
                    $venueData = [
                        'organization_id' => $organizationId,
                        'name' => $rowData['ad'],
                        'capacity' => is_numeric($rowData['kapasite'] ?? '') ? (int) $rowData['kapasite'] : null,
                        'location' => $rowData['konum'] ?? null,
                        'floor' => $rowData['kat'] ?? null,
                        'description' => $rowData['açıklama'] ?? null,
                        'facilities' => isset($rowData['özellikler']) ? explode(',', $rowData['özellikler']) : null,
                        'is_active' => true,
                        'sort_order' => is_numeric($rowData['sıra'] ?? '') ? (int) $rowData['sıra'] : 0,
                    ];

                    $venueData['slug'] = Venue::generateUniqueSlug($venueData['name'], $organizationId);

                    $existingVenue = Venue::where('organization_id', $organizationId)
                        ->where('name', $venueData['name'])
                        ->first();

                    if ($existingVenue) {
                        if ($updateExisting) {
                            $venueData['slug'] = Venue::generateUniqueSlug($venueData['name'], $organizationId, $existingVenue->id);
                            $existingVenue->update($venueData);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        Venue::create($venueData);
                        $imported++;
                    }

                } catch (\Exception $e) {
                    $skipped++;
                }
            }

            DB::commit();

            return back()->with('success', "Salon içe aktarma tamamlandı. Yeni: {$imported}, Güncellenen: {$updated}, Atlanan: {$skipped}");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'İçe aktarma hatası: '.$e->getMessage()]);
        }
    }

    /**
     * Import sponsors from Excel/CSV
     */
    public function sponsors(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:5120',
            'update_existing' => 'boolean',
        ]);

        $organizationId = auth()->user()->currentOrganization->id;
        $updateExisting = $request->boolean('update_existing');

        try {
            DB::beginTransaction();

            $data = Excel::toArray([], $request->file('file'))[0];

            if (empty($data)) {
                throw new \Exception('Dosya boş veya okunamıyor.');
            }

            $headers = array_shift($data);
            $headers = array_map('trim', $headers);

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($data as $rowIndex => $row) {
                $rowData = array_combine($headers, $row);
                $rowData = array_map('trim', $rowData);

                if (empty($rowData['ad'])) {
                    continue;
                }

                try {
                    $sponsorLevel = strtolower($rowData['seviye'] ?? 'bronze');
                    if (! in_array($sponsorLevel, ['platinum', 'gold', 'silver', 'bronze'])) {
                        $sponsorLevel = 'bronze';
                    }

                    $sponsorData = [
                        'organization_id' => $organizationId,
                        'name' => $rowData['ad'],
                        'sponsor_level' => $sponsorLevel,
                        'description' => $rowData['açıklama'] ?? null,
                        'website_url' => $rowData['website'] ?? null,
                        'contact_email' => $rowData['email'] ?? null,
                        'contact_phone' => $rowData['telefon'] ?? null,
                        'contact_person' => $rowData['iletişim_kişisi'] ?? null,
                        'is_active' => true,
                        'sort_order' => is_numeric($rowData['sıra'] ?? '') ? (int) $rowData['sıra'] : 0,
                    ];

                    $sponsorData['slug'] = Sponsor::generateUniqueSlug($sponsorData['name'], $organizationId);

                    $existingSponsor = Sponsor::where('organization_id', $organizationId)
                        ->where('name', $sponsorData['name'])
                        ->first();

                    if ($existingSponsor) {
                        if ($updateExisting) {
                            $sponsorData['slug'] = Sponsor::generateUniqueSlug($sponsorData['name'], $organizationId, $existingSponsor->id);
                            $existingSponsor->update($sponsorData);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        Sponsor::create($sponsorData);
                        $imported++;
                    }

                } catch (\Exception $e) {
                    $skipped++;
                }
            }

            DB::commit();

            return back()->with('success', "Sponsor içe aktarma tamamlandı. Yeni: {$imported}, Güncellenen: {$updated}, Atlanan: {$skipped}");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'İçe aktarma hatası: '.$e->getMessage()]);
        }
    }

    /**
     * Import presentations from Excel/CSV
     */
    public function presentations(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:5120',
            'program_session_id' => 'required|exists:program_sessions,id',
        ]);

        $session = ProgramSession::with('event')->find($request->program_session_id);

        if ($session->event->organization_id !== auth()->user()->currentOrganization->id) {
            return back()->withErrors(['error' => 'Bu oturuma sunum ekleyemezsiniz.']);
        }

        try {
            DB::beginTransaction();

            $data = Excel::toArray([], $request->file('file'))[0];

            if (empty($data)) {
                throw new \Exception('Dosya boş veya okunamıyor.');
            }

            $headers = array_shift($data);
            $headers = array_map('trim', $headers);

            $imported = 0;
            $skipped = 0;
            $sortOrder = 1;

            foreach ($data as $rowIndex => $row) {
                $rowData = array_combine($headers, $row);
                $rowData = array_map('trim', $rowData);

                if (empty($rowData['başlık'])) {
                    continue;
                }

                try {
                    $presentationData = [
                        'program_session_id' => $request->program_session_id,
                        'title' => $rowData['başlık'],
                        'abstract' => $rowData['özet'] ?? null,
                        'duration_minutes' => is_numeric($rowData['süre'] ?? '') ? (int) $rowData['süre'] : null,
                        'presentation_type' => $rowData['tür'] ?? null,
                        'language' => $rowData['dil'] ?? 'tr',
                        'notes' => $rowData['notlar'] ?? null,
                        'sort_order' => $sortOrder++,
                    ];

                    $presentation = Presentation::create($presentationData);

                    // Add speakers if provided
                    if (! empty($rowData['konuşmacılar'])) {
                        $speakerNames = explode(',', $rowData['konuşmacılar']);
                        foreach ($speakerNames as $speakerName) {
                            $speakerName = trim($speakerName);
                            if ($speakerName) {
                                $nameParts = explode(' ', $speakerName);
                                $firstName = array_shift($nameParts);
                                $lastName = implode(' ', $nameParts);

                                $participant = Participant::firstOrCreate([
                                    'organization_id' => $session->event->organization_id,
                                    'first_name' => $firstName,
                                    'last_name' => $lastName,
                                ], [
                                    'email' => strtolower(str_replace(' ', '.', $speakerName)).'@example.com',
                                    'is_active' => true,
                                ]);

                                $presentation->speakers()->create([
                                    'participant_id' => $participant->id,
                                    'role' => 'primary',
                                    'sort_order' => 0,
                                ]);
                            }
                        }
                    }

                    $imported++;

                } catch (\Exception $e) {
                    $skipped++;
                }
            }

            DB::commit();

            return back()->with('success', "Sunum içe aktarma tamamlandı. Yeni: {$imported}, Atlanan: {$skipped}");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'İçe aktarma hatası: '.$e->getMessage()]);
        }
    }

    /**
     * Download sample import template
     */
    public function downloadTemplate(string $type)
    {
        $templates = [
            'participants' => [
                'filename' => 'participants_template.xlsx',
                'headers' => ['ad', 'soyad', 'email', 'telefon', 'ünvan', 'kurum', 'biyografi', 'konuşmacı', 'moderatör'],
                'sample_data' => [
                    ['Ahmet', 'Yılmaz', 'ahmet.yilmaz@example.com', '0532 123 4567', 'Dr.', 'ABC Üniversitesi', 'Pediatri uzmanı', 'evet', 'hayır'],
                ],
            ],
            'venues' => [
                'filename' => 'venues_template.xlsx',
                'headers' => ['ad', 'kapasite', 'konum', 'kat', 'açıklama', 'özellikler', 'sıra'],
                'sample_data' => [
                    ['Ana Salon', '500', 'Zemin Kat', 'Z1', 'Ana konferans salonu', 'Projektör,Ses sistemi,Klima', '1'],
                ],
            ],
            'sponsors' => [
                'filename' => 'sponsors_template.xlsx',
                'headers' => ['ad', 'seviye', 'açıklama', 'website', 'email', 'telefon', 'iletişim_kişisi', 'sıra'],
                'sample_data' => [
                    ['ABC Şirketi', 'gold', 'Sağlık teknolojileri firması', 'https://abc.com', 'info@abc.com', '0212 123 4567', 'Mehmet Öz', '1'],
                ],
            ],
            'presentations' => [
                'filename' => 'presentations_template.xlsx',
                'headers' => ['başlık', 'özet', 'süre', 'tür', 'dil', 'konuşmacılar', 'notlar'],
                'sample_data' => [
                    ['Pediatride Yeni Yaklaşımlar', 'Pediatri alanındaki son gelişmeler...', '30', 'oral', 'tr', 'Dr. Ahmet Yılmaz, Dr. Ayşe Demir', 'Özel not'],
                ],
            ],
        ];

        if (! isset($templates[$type])) {
            abort(404);
        }

        $template = $templates[$type];
        $data = collect([$template['headers']])->concat($template['sample_data']);

        return Excel::download(new class($data) implements FromCollection
        {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data;
            }
        }, $template['filename']);
    }
}
