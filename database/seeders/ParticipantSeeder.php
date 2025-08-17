<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Participant;
use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👨‍⚕️ Creating participants...');

        $titles = ['Prof. Dr.', 'Doç. Dr.', 'Dr.', 'Uzm. Dr.', 'Araş. Gör. Dr.', 'Öğr. Gör. Dr.'];
        
        $firstNames = [
            'Mehmet', 'Ayşe', 'Fatma', 'Ali', 'Zeynep', 'Ahmet', 'Elif', 'Emre', 'Seda', 'Burak',
            'Özge', 'Can', 'Derya', 'Oğuz', 'Pınar', 'Serkan', 'Gizem', 'Murat', 'Esra', 'Cem',
            'Deniz', 'Tuğba', 'Onur', 'Sibel', 'Erhan', 'Neslihan', 'Tolga', 'Burcu', 'Koray', 'Aslı'
        ];

        $lastNames = [
            'Yılmaz', 'Kaya', 'Demir', 'Çelik', 'Şahin', 'Öztürk', 'Aydın', 'Özdemir', 'Arslan', 'Doğan',
            'Kılıç', 'Aslan', 'Çetin', 'Kara', 'Koç', 'Kurt', 'Özkan', 'Şimşek', 'Türk', 'Uçar',
            'Yıldız', 'Güler', 'Topal', 'Sarı', 'Polat', 'Korkmaz', 'Uysal', 'Aksoy', 'Taş', 'Bilgin'
        ];

        $affiliations = [
            'Hacettepe Üniversitesi Tıp Fakültesi',
            'İstanbul Üniversitesi-Cerrahpaşa Tıp Fakültesi',
            'Ankara Üniversitesi Tıp Fakültesi',
            'Ege Üniversitesi Tıp Fakültesi',
            'Gazi Üniversitesi Tıp Fakültesi',
            'Dokuz Eylül Üniversitesi Tıp Fakültesi',
            'Marmara Üniversitesi Tıp Fakültesi',
            'Acıbadem Üniversitesi Tıp Fakültesi',
            'Koç Üniversitesi Tıp Fakültesi',
            'Başkent Üniversitesi Tıp Fakültesi',
            'Ankara Şehir Hastanesi',
            'İstanbul Şişli Hamidiye Etfal Hastanesi',
            'İzmir Dr. Behçet Uz Çocuk Hastanesi',
            'Antalya Eğitim ve Araştırma Hastanesi',
            'Bursa Uludağ Üniversitesi Tıp Fakültesi',
            'Erciyes Üniversitesi Tıp Fakültesi',
            'Çukurova Üniversitesi Tıp Fakültesi',
            'Pamukkale Üniversitesi Tıp Fakültesi',
            'Karadeniz Teknik Üniversitesi Tıp Fakültesi',
            'Süleyman Demirel Üniversitesi Tıp Fakültesi',
        ];

        $bios = [
            'Pediatri alanında 15 yıllık deneyime sahip. Çocuk enfeksiyon hastalıkları konusunda uzmanlaşmış.',
            'Neonatoloji uzmanı. Prematüre bebek bakımı ve yenidoğan yoğun bakım konularında çalışmaktadır.',
            'Pediatrik endokrinoloji alanında araştırmalar yürütmekte. Çocukluk çağı diyabeti ana ilgi alanı.',
            'Çocuk kardiyolojisi uzmanı. Konjenital kalp hastalıkları tanı ve tedavisinde deneyimli.',
            'Pediatrik hematoloji-onkoloji alanında çalışan deneyimli hekim. Çocukluk çağı kanserlerinde uzman.',
            'Çocuk nörolojisi uzmanı. Epilepsi ve gelişimsel bozukluklar konularında araştırmalar yapıyor.',
            'Pediatrik gastroenteroloji alanında çalışmakta. İnflamatuar barsak hastalıkları konusunda uzman.',
            'Çocuk cerrahisi uzmanı. Minimal invaziv cerrahi tekniklerde deneyimli.',
            'Pediatrik üroloji alanında çalışan hekim. Çocukluk çağı ürolojik sorunlarında uzman.',
            'Çocuk psikiyatrisi uzmanı. Gelişimsel bozukluklar ve davranış sorunları konularında çalışıyor.',
        ];

        $organizations = Organization::where('is_active', true)->get();
        $totalParticipants = 0;

        foreach ($organizations as $organization) {
            // Her organizasyon için 15-25 katılımcı oluştur
            $participantCount = rand(15, 25);

            for ($i = 0; $i < $participantCount; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $title = $titles[array_rand($titles)];
                $affiliation = $affiliations[array_rand($affiliations)];
                $bio = $bios[array_rand($bios)];

                // Email oluştur
                $email = strtolower(str_replace([' ', '.'], ['', ''], $firstName . '.' . $lastName)) . '@' . 
                         strtolower(str_replace(' ', '', $affiliation)) . '.com';

                // Telefon oluştur
                $phone = '+90 5' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99);

                // Roller belirle
                $isSpeaker = rand(1, 100) <= 80; // %80 konuşmacı
                $isModerator = rand(1, 100) <= 60; // %60 moderatör

                Participant::create([
                    'organization_id' => $organization->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'title' => $title,
                    'affiliation' => $affiliation,
                    'email' => $email,
                    'phone' => $phone,
                    'bio' => $bio,
                    'is_speaker' => $isSpeaker,
                    'is_moderator' => $isModerator,
                ]);

                $totalParticipants++;
            }
        }

        $this->command->info("✅ Created {$totalParticipants} participants across " . $organizations->count() . " organizations");
        $this->command->info("📊 Speakers: " . Participant::where('is_speaker', true)->count() . ", Moderators: " . Participant::where('is_moderator', true)->count());
    }
}