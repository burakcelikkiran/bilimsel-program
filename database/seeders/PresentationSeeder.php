<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Models\Sponsor;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PresentationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📄 Creating presentations...');

        $presentationTitles = [
            // Neonatoloji
            'Prematüre Bebeklerde Nörogelişimsel Takip',
            'Neonatal Sepsis Tanı ve Tedavi Yaklaşımları',
            'ECMO ile Tedavi Edilen Yenidoğanların Uzun Dönem Sonuçları',
            'Konjenital Kalp Hastalığı Olan Yenidoğanlarda Cerrahi Öncesi Yönetim',
            'Bronkopulmoner Displazi Önleme Stratejileri',
            
            // Endokrinoloji
            'Çocukluk Çağı Tip 1 Diyabetinde Teknolojik Gelişmeler',
            'Adölesan Dönemde Tiroid Hastalıkları',
            'Büyüme Hormonu Eksikliği Tanı Kriterleri',
            'Puberte Prekoks: Güncel Tedavi Yaklaşımları',
            'Çocukluk Çağı Obesitesi ve Metabolik Sendrom',
            
            // Enfeksiyon
            'COVID-19\'un Çocuklar Üzerindeki Etkileri',
            'Antibiyotik Direnci ile Mücadele Stratejileri',
            'Çocukluk Çağı Tüberküloz Tanı ve Tedavisi',
            'İnvaziv Fungal Enfeksiyonlar',
            'Aşı Kararsızlığı ile Başa Çıkma Yöntemleri',
            
            // Kardiyoloji
            'Konjenital Kalp Hastalıklarında Erken Tanı',
            'Çocuklarda Hipertansiyon Yönetimi',
            'Kawasaki Hastalığı: Tanı ve Tedavi Güncellemeleri',
            'Pediatrik Kalp Nakli Sonuçları',
            'Spor Kardiyolojisi: Çocuk ve Adölesanlarda Değerlendirme',
            
            // Gastroenteroloji
            'İnflamatuar Barsak Hastalıklarında Biyolojik Tedaviler',
            'Çölyak Hastalığı: Glütensiz Diyet Ötesi',
            'Pediatrik Karaciğer Nakli İndikasyonları',
            'Fonksiyonel Karın Ağrısı Tedavi Yaklaşımları',
            'Helikobakter Pilori Eradikasyonu',
            
            // Nöroloji
            'Çocukluk Çağı Epilepsisinde İlaç Dirençli Vakalar',
            'Otizm Spektrum Bozukluğu: Erken Müdahale',
            'Pediatrik Felç: Rehabilitasyon Yaklaşımları',
            'Migren Proflaksisinde Yeni Yaklaşımlar',
            'Geç Gelişimsel Bozuklukların Değerlendirilmesi',
            
            // Hematoloji-Onkoloji
            'Çocukluk Çağı Lösemilerinde İmmünoterapi',
            'Solid Tümörlerde Minimal İnvaziv Cerrahi',
            'Hemofili Tedavisinde Profilaksi Yaklaşımları',
            'Talasemi Hastalarında Demir Şelasyon Tedavisi',
            'Çocukluk Çağı Kanserlerinde Palyatif Bakım',
        ];

        $abstracts = [
            'Bu çalışmada, hastanemizde son 5 yılda tedavi gördü hastaların retrospektif analizi yapılmıştır. Sonuçlar literatürle karşılaştırılarak değerlendirilmiştir.',
            'Prospektif kohort çalışmasında, 200 hasta 2 yıl boyunca takip edilmiştir. Tedavi etkinliği ve yan etkiler detaylı olarak analiz edilmiştir.',
            'Meta-analiz çalışmasında, 2010-2023 yılları arasındaki 50 çalışma incelenmiştir. Tedavi protokollerinin etkinliği karşılaştırmalı olarak değerlendirilmiştir.',
            'Randomize kontrollü çalışmada, yeni tedavi yaklaşımının etkinliği standart tedavi ile karşılaştırılmıştır. 12 aylık takip sonuçları sunulmuştur.',
            'Olgu serisi çalışmasında, nadir görülen hastalığa sahip 15 hastanın klinik özellikleri ve tedavi yanıtları analiz edilmiştir.',
            'Çok merkezli çalışmada, farklı tedavi protokollerinin karşılaştırılması yapılmıştır. 3 yıllık uzun dönem sonuçlar değerlendirilmiştir.',
            'Sistematik derleme çalışmasında, son 10 yılda yayınlanan tüm çalışmalar gözden geçirilmiştir. Kanıta dayalı öneriler geliştirilmiştir.',
            'Kesitsel çalışmada, hastalık prevalansı ve risk faktörleri araştırılmıştır. Demografik özellikler detaylı olarak analiz edilmiştir.',
        ];

        $totalPresentations = 0;

        foreach (ProgramSession::where('is_break', false)->get() as $session) {
            // Break olmayan oturumlar için sunum oluştur
            $presentationCount = $this->getPresentationCount($session);
            
            if ($presentationCount === 0) continue;
            
            // Oturum süresi içinde zaman dilimlerini oluştur
            $timeSlots = $this->generatePresentationTimeSlots($session, $presentationCount);
            
            for ($i = 0; $i < $presentationCount; $i++) {
                $title = $presentationTitles[array_rand($presentationTitles)];
                $abstract = $abstracts[array_rand($abstracts)];
                $presentationType = $this->selectPresentationType($session);
                
                $presentation = Presentation::create([
                    'program_session_id' => $session->id,
                    'title' => $title,
                    'abstract' => $abstract,
                    'start_time' => $timeSlots[$i]['start'],
                    'end_time' => $timeSlots[$i]['end'],
                    'presentation_type' => $presentationType,
                    'sponsor_id' => $this->getRandomSponsor($session),
                    'sort_order' => $i + 1,
                ]);
                
                // Konuşmacıları ata
                $this->attachSpeakers($presentation, $session);
                
                $totalPresentations++;
            }
        }

        $this->command->info("✅ Created {$totalPresentations} presentations");
        $this->command->info("📊 Presentations by type:");
        $types = ['keynote', 'oral', 'case_presentation', 'symposium'];
        foreach ($types as $type) {
            $count = Presentation::where('presentation_type', $type)->count();
            $this->command->info("   - {$type}: {$count}");
        }
    }

    private function getPresentationCount(ProgramSession $session): int
    {
        // Oturum tipine göre sunum sayısını belirle
        return match($session->session_type) {
            'main' => rand(1, 3), // Ana oturumlar: 1-3 sunum
            'satellite' => rand(3, 5), // Uydu sempozyumları: 3-5 sunum
            'oral_presentation' => rand(4, 6), // Sözlü bildiri: 4-6 sunum
            'special' => rand(2, 4), // Özel oturumlar: 2-4 sunum
            'break' => 0, // Ara oturumlarında sunum yok
            default => rand(1, 3),
        };
    }

    private function generatePresentationTimeSlots(ProgramSession $session, int $count): array
    {
        // Daha güvenli time parsing
        $sessionStartStr = $session->start_time instanceof \Carbon\Carbon 
            ? $session->start_time->format('H:i') 
            : (string) $session->start_time;
        
        $sessionEndStr = $session->end_time instanceof \Carbon\Carbon 
            ? $session->end_time->format('H:i') 
            : (string) $session->end_time;
        
        // H:i:s formatında gelebilir, sadece H:i kısmını al
        $sessionStartStr = substr($sessionStartStr, 0, 5); // "09:00:00" -> "09:00"
        $sessionEndStr = substr($sessionEndStr, 0, 5);     // "10:30:00" -> "10:30"
        
        try {
            $sessionStart = Carbon::createFromFormat('H:i', $sessionStartStr);
            $sessionEnd = Carbon::createFromFormat('H:i', $sessionEndStr);
        } catch (\Exception $e) {
            // Eğer hala hata varsa, farklı format dene
            $sessionStart = Carbon::parse($session->start_time);
            $sessionEnd = Carbon::parse($session->end_time);
        }
        
        $totalMinutes = $sessionStart->diffInMinutes($sessionEnd);
        
        // Her sunum için ortalama süre (tartışma dahil)
        $averageDuration = intval($totalMinutes / $count);
        
        $slots = [];
        $currentTime = $sessionStart->copy();
        
        for ($i = 0; $i < $count; $i++) {
            // Son sunum dışında belirlenen süreyi kullan
            if ($i === $count - 1) {
                $endTime = $sessionEnd->copy();
            } else {
                $duration = rand($averageDuration - 5, $averageDuration + 5);
                $endTime = $currentTime->copy()->addMinutes($duration);
            }
            
            $slots[] = [
                'start' => $currentTime->format('H:i:s'), // Database için H:i:s formatı
                'end' => $endTime->format('H:i:s'),       // Database için H:i:s formatı
            ];
            
            $currentTime = $endTime->copy();
        }
        
        return $slots;
    }

    private function selectPresentationType(ProgramSession $session): string
    {
        // Oturum tipine göre sunum tipini belirle
        return match($session->session_type) {
            'main' => rand(1, 100) <= 60 ? 'keynote' : 'oral',
            'satellite' => rand(1, 100) <= 70 ? 'symposium' : 'oral',
            'oral_presentation' => 'oral',
            'special' => rand(1, 100) <= 40 ? 'case_presentation' : 'oral',
            default => 'oral',
        };
    }

    private function getRandomSponsor(ProgramSession $session): ?int
    {
        // %30 oranında sunum sponsoru
        if (rand(1, 100) <= 30) {
            $venue = $session->venue;
            $eventDay = $venue->eventDay;
            $event = $eventDay->event;
            
            $sponsor = Sponsor::where('organization_id', $event->organization_id)
                             ->where('is_active', true)
                             ->inRandomOrder()
                             ->first();
            
            return $sponsor?->id; // Normal ID döndür (UUID değil)
        }
        
        return null;
    }

    private function attachSpeakers(Presentation $presentation, ProgramSession $session): void
    {
        $venue = $session->venue;
        $eventDay = $venue->eventDay;
        $event = $eventDay->event;
        
        // Konuşmacı havuzunu al
        $speakers = Participant::where('organization_id', $event->organization_id)
                              ->where('is_speaker', true)
                              ->inRandomOrder()
                              ->get();
        
        if ($speakers->isEmpty()) return;
        
        // Sunum tipine göre konuşmacı sayısını belirle
        $speakerCount = match($presentation->presentation_type) {
            'keynote' => 1,
            'oral' => rand(1, 2),
            'case_presentation' => rand(1, 3),
            'symposium' => rand(2, 4),
            default => rand(1, 2),
        };
        
        $selectedSpeakers = $speakers->take($speakerCount);
        
        $sortOrder = 1;
        foreach ($selectedSpeakers as $index => $speaker) {
            $role = match($index) {
                0 => 'primary',
                default => rand(1, 100) <= 70 ? 'co_speaker' : 'discussant',
            };
            
            $presentation->speakers()->attach($speaker->id, [
                'speaker_role' => $role,
                'sort_order' => $sortOrder++,
            ]);
        }
    }
}