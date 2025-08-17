<?php

namespace Database\Seeders;

use App\Models\EventDay;
use App\Models\Participant;
use App\Models\ProgramSession;
use App\Models\ProgramSessionCategory;
use App\Models\Sponsor;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProgramSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📋 Creating program sessions...');

        $sessionTypes = ['main', 'satellite', 'oral_presentation', 'break', 'special'];
        $moderatorTitles = ['Oturum Başkanı', 'Oturum Başkanları', 'Moderatör', 'Kolaylaştırıcı'];

        $sessionTemplates = [
            // Ana oturumlar
            'main' => [
                'Açılış Töreni',
                'Çocuk Sağlığında Güncel Gelişmeler',
                'Pediatride Yenilikçi Tedavi Yaklaşımları',
                'Çocukluk Çağı Beslenme Sorunları',
                'Gelişimsel Pediatri Güncellemeleri',
                'Aşı Bilimi ve Güncel Yaklaşımlar',
                'Kapanış Oturumu',
            ],
            'satellite' => [
                'Prematüre Bebek Bakımında İleri Teknikler',
                'Çocukluk Çağı Kanserlerinde Yeni Ufuklar',
                'Pediatrik Kardiyolojide Güncel Tedaviler',
                'Çocuk Endokrinolojisinde Son Gelişmeler',
                'Neonatal Yoğun Bakım Stratejileri',
                'Çocuk Nörolojisinde Yeni Yaklaşımlar',
            ],
            'oral_presentation' => [
                'En İyi Bildiriler - Neonatoloji',
                'En İyi Bildiriler - Enfeksiyon',
                'En İyi Bildiriler - Endokrinoloji',
                'Sözlü Bildiri - Kardiyoloji',
                'Sözlü Bildiri - Gastroenteroloji',
                'Genç Pediatrist Sunumları',
            ],
            'special' => [
                'Konsültasyon Saati',
                'Olgu Sunumları',
                'Workshop - Acil Müdahale',
                'Panel Tartışması',
                'Poster Sunumları',
            ],
        ];

        $totalSessions = 0;

        foreach (Venue::with('eventDay.event')->get() as $venue) {
            $event = $venue->eventDay->event;
            $eventDay = $venue->eventDay;

            // Venue tipine göre oturum sayısını belirle
            $sessionCount = $this->getSessionCountByVenue($venue);
            
            // Zaman dilimlerini oluştur
            $timeSlots = $this->generateTimeSlots($sessionCount);

            for ($i = 0; $i < $sessionCount; $i++) {
                $sessionType = $this->selectSessionType($venue, $i, $sessionCount);
                $title = $this->selectSessionTitle($sessionType, $sessionTemplates);
                
                $session = ProgramSession::create([
                    'venue_id' => $venue->id,
                    'title' => $title,
                    'description' => $this->generateDescription($title, $sessionType),
                    'start_time' => $timeSlots[$i]['start'],
                    'end_time' => $timeSlots[$i]['end'],
                    'session_type' => $sessionType,
                    'moderator_title' => $moderatorTitles[array_rand($moderatorTitles)],
                    'sponsor_id' => $this->getRandomSponsor($event->organization_id),
                    'is_break' => $sessionType === 'break',
                    'sort_order' => $i + 1,
                ]);

                // Moderatör ata
                $this->attachModerators($session, $event->organization_id);

                // Kategori ata
                $this->attachCategories($session, $event->id);

                $totalSessions++;
            }
        }

        $this->command->info("✅ Created {$totalSessions} program sessions");
        $this->command->info("📊 Sessions by type:");
        foreach ($sessionTypes as $type) {
            $count = ProgramSession::where('session_type', $type)->count();
            $this->command->info("   - {$type}: {$count}");
        }
    }

    private function getSessionCountByVenue(Venue $venue): int
    {
        // Venue kapasitesine göre oturum sayısını belirle
        if ($venue->capacity >= 600) {
            return rand(6, 8); // Ana salon
        } elseif ($venue->capacity >= 200) {
            return rand(4, 6); // Orta salon
        } else {
            return rand(2, 4); // Küçük salon
        }
    }

    private function generateTimeSlots(int $sessionCount): array
    {
        $slots = [];
        $startTime = Carbon::createFromTime(9, 0); // 09:00'da başla
        
        for ($i = 0; $i < $sessionCount; $i++) {
            $duration = rand(45, 90); // 45-90 dakika arası
            $endTime = $startTime->copy()->addMinutes($duration);
            
            $slots[] = [
                'start' => $startTime->format('H:i'),
                'end' => $endTime->format('H:i'),
            ];
            
            // Bir sonraki oturum için 15 dakika ara
            $startTime = $endTime->addMinutes(15);
            
            // Öğle arası kontrolü
            if ($startTime->hour >= 12 && $startTime->hour < 14 && $i < $sessionCount - 1) {
                $startTime = Carbon::createFromTime(14, 0);
            }
        }
        
        return $slots;
    }

    private function selectSessionType(Venue $venue, int $index, int $totalSessions): string
    {
        // İlk oturum genellikle açılış
        if ($index === 0 && $venue->capacity >= 400) {
            return rand(1, 100) <= 70 ? 'main' : 'special';
        }
        
        // Son oturum genellikle kapanış
        if ($index === $totalSessions - 1 && $venue->capacity >= 400) {
            return rand(1, 100) <= 60 ? 'main' : 'special';
        }
        
        // Ara oturumları
        if (rand(1, 100) <= 20) {
            return 'break';
        }
        
        // Venue kapasitesine göre tip dağılımı
        if ($venue->capacity >= 400) {
            $weights = ['main' => 40, 'satellite' => 30, 'oral_presentation' => 20, 'special' => 10];
        } else {
            $weights = ['main' => 20, 'satellite' => 25, 'oral_presentation' => 35, 'special' => 20];
        }
        
        return $this->weightedRandom($weights);
    }

    private function selectSessionTitle(string $sessionType, array $templates): string
    {
        if (!isset($templates[$sessionType])) {
            return "Oturum - " . ucfirst($sessionType);
        }
        
        $titles = $templates[$sessionType];
        return $titles[array_rand($titles)];
    }

    private function generateDescription(string $title, string $sessionType): string
    {
        $descriptions = [
            'main' => 'Bu oturumda pediatri alanındaki en güncel gelişmeler ve yenilikçi yaklaşımlar ele alınacaktır.',
            'satellite' => 'Uzmanlık alanına özgü derinlemesine bilgi paylaşımının yapılacağı uydu sempozyumu.',
            'oral_presentation' => 'Seçilmiş araştırma bulgularının sunulacağı sözlü bildiri oturumu.',
            'special' => 'Interaktif tartışma ve deneyim paylaşımının yapılacağı özel oturum.',
            'break' => 'Katılımcıların dinlenmesi ve networking yapabilmesi için ara.',
        ];
        
        return $descriptions[$sessionType] ?? 'Program oturumu açıklaması.';
    }

    private function getRandomSponsor($organizationId): ?string
    {
        // %60 oranında sponsor atanması
        if (rand(1, 100) <= 60) {
            $sponsor = Sponsor::where('organization_id', $organizationId)
                             ->where('is_active', true)
                             ->inRandomOrder()
                             ->first();
            return $sponsor?->id;
        }
        
        return null;
    }

    private function attachModerators(ProgramSession $session, int $organizationId): void
    {
        $moderators = Participant::where('organization_id', $organizationId)
                                ->where('is_moderator', true)
                                ->inRandomOrder()
                                ->limit(rand(1, 2))
                                ->get();
        
        $sortOrder = 1;
        foreach ($moderators as $moderator) {
            $session->moderators()->attach($moderator->id, [
                'sort_order' => $sortOrder++
            ]);
        }
    }

    private function attachCategories(ProgramSession $session, string $eventId): void
    {
        $categories = ProgramSessionCategory::where('event_id', $eventId)
                                           ->inRandomOrder()
                                           ->limit(rand(1, 2))
                                           ->get();
        
        $session->categories()->attach($categories->pluck('id'));
    }

    private function weightedRandom(array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($weights as $item => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $item;
            }
        }
        
        return array_key_first($weights);
    }
}