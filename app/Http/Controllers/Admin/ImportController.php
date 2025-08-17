<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Models\Venue;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

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
    public function participants(Request $request): RedirectResponse
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

            $headers = array_shift($data); // Remove header row
            $headers = array_map('trim', $headers);

            $requiredColumns = ['ad', 'soyad', 'email'];
            $missingColumns = array_diff($requiredColumns, array_map('strtolower', $headers));
            
            if (!empty($missingColumns)) {
                throw new \Exception('Gerekli kolonlar eksik: ' . implode(', ', $missingColumns));
            }

            $imported = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];

            foreach ($data as $rowIndex => $row) {
                $rowData = array_combine($headers, $row);
                $rowData = array_map('trim', $rowData);

                // Skip empty rows
                if (empty($rowData['ad']) && empty($rowData['soyad']) && empty($rowData['email'])) {
                    continue;
                }

                try {
                    $participantData = [
                        'organization_id' => $organizationId,
                        'first_name' => $rowData['ad'] ?? '',
                        'last_name' => $rowData['soyad'] ?? '',
                        'email' => $rowData['email'] ?? '',
                        'phone' => $rowData['telefon'] ?? null,
                        'title' => $rowData['ünvan'] ?? null,
                        'institution' => $rowData['kurum'] ?? null,
                        'city' => $rowData['şehir'] ?? null,
                        'country' => $rowData['ülke'] ?? 'Türkiye',
                        'biography' => $rowData['biyografi'] ?? null,
                        'website_url' => $rowData['website'] ?? null,
                        'linkedin_url' => $rowData['linkedin'] ?? null,
                        'twitter_handle' => $rowData['twitter'] ?? null,
                        'is_active' => true,
                    ];

                    $validator = Validator::make($participantData, [
                        'first_name' => 'required|string|max:255',
                        'last_name' => 'required|string|max:255',
                        'email' => 'required|email|max:255',
                        'phone' => 'nullable|string|max:50',
                        'title' => 'nullable|string|max:255',
                        'institution' => 'nullable|string|max:255',
                        'city' => 'nullable|string|max:100',
                        'country' => 'nullable|string|max:100',
                        'website_url' => 'nullable|url|max:500',
                        'linkedin_url' => 'nullable|url|max:500',
                        'twitter_handle' => 'nullable|string|max:100',
                    ]);

                    if ($validator->fails()) {
                        $errors[] = "Satır " . ($rowIndex + 2) . ": " . implode(', ', $validator->errors()->all());
                        $skipped++;
                        continue;
                    }

                    // Check if participant exists
                    $existingParticipant = Participant::where('organization_id', $organizationId)
                        ->where('email', $participantData['email'])
                        ->first();

                    if ($existingParticipant) {
                        if ($updateExisting) {
                            $existingParticipant->update($participantData);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        Participant::create($participantData);
                        $imported++;
                    }

                } catch (\Exception $e) {
                    $errors[] = "Satır " . ($rowIndex + 2) . ": " . $e->getMessage();
                    $skipped++;
                }
            }

            DB::commit();

            $message = "İçe aktarma tamamlandı. Yeni: {$imported}, Güncellenen: {$updated}, Atlanan: {$skipped}";
            
            if (!empty($errors)) {
                $message .= " Hatalar: " . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " (+" . (count($errors) - 5) . " daha...)";
                }
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'İçe aktarma hatası: ' . $e->getMessage()]);
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
            return back()->withErrors(['error' => 'İçe aktarma hatası: ' . $e->getMessage()]);
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
                    if (!in_array($sponsorLevel, ['platinum', 'gold', 'silver', 'bronze'])) {
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
            return back()->withErrors(['error' => 'İçe aktarma hatası: ' . $e->getMessage()]);
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
                    if (!empty($rowData['konuşmacılar'])) {
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
                                    'email' => strtolower(str_replace(' ', '.', $speakerName)) . '@example.com',
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
            return back()->withErrors(['error' => 'İçe aktarma hatası: ' . $e->getMessage()]);
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
                'headers' => ['ad', 'soyad', 'email', 'telefon', 'ünvan', 'kurum', 'şehir', 'ülke', 'biyografi', 'website', 'linkedin', 'twitter'],
                'sample_data' => [
                    ['Ahmet', 'Yılmaz', 'ahmet.yilmaz@example.com', '0532 123 4567', 'Dr.', 'ABC Üniversitesi', 'İstanbul', 'Türkiye', 'Pediatri uzmanı', 'https://example.com', 'https://linkedin.com/in/ahmet', '@ahmetyilmaz']
                ]
            ],
            'venues' => [
                'filename' => 'venues_template.xlsx',
                'headers' => ['ad', 'kapasite', 'konum', 'kat', 'açıklama', 'özellikler', 'sıra'],
                'sample_data' => [
                    ['Ana Salon', '500', 'Zemin Kat', 'Z1', 'Ana konferans salonu', 'Projektör,Ses sistemi,Klima', '1']
                ]
            ],
            'sponsors' => [
                'filename' => 'sponsors_template.xlsx',
                'headers' => ['ad', 'seviye', 'açıklama', 'website', 'email', 'telefon', 'iletişim_kişisi', 'sıra'],
                'sample_data' => [
                    ['ABC Şirketi', 'gold', 'Sağlık teknolojileri firması', 'https://abc.com', 'info@abc.com', '0212 123 4567', 'Mehmet Öz', '1']
                ]
            ],
            'presentations' => [
                'filename' => 'presentations_template.xlsx',
                'headers' => ['başlık', 'özet', 'süre', 'tür', 'dil', 'konuşmacılar', 'notlar'],
                'sample_data' => [
                    ['Pediatride Yeni Yaklaşımlar', 'Pediatri alanındaki son gelişmeler...', '30', 'oral', 'tr', 'Dr. Ahmet Yılmaz, Dr. Ayşe Demir', 'Özel not']
                ]
            ]
        ];

        if (!isset($templates[$type])) {
            abort(404);
        }

        $template = $templates[$type];
        $data = collect([$template['headers']])->concat($template['sample_data']);

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection {
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