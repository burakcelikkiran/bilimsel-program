<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📅 Creating events...');

        $events = [
            [
                'organization_name' => 'Türk Pediatri Derneği',
                'name' => '25. Ulusal Pediatri Kongresi',
                'description' => 'Türkiye\'nin en kapsamlı pediatri kongresi. Çocuk sağlığı alanındaki son gelişmeler, yeni tedavi yöntemleri ve araştırma bulgularının paylaşıldığı prestijli bilimsel etkinlik.',
                'start_date' => '2025-09-15',
                'end_date' => '2025-09-18',
                'location' => 'Antalya Convention Center, Antalya',
                'is_published' => true,
                'created_by_email' => 'mehmet.ozkan@pediatri.org.tr',
            ],
            [
                'organization_name' => 'Türk Neonatoloji Derneği',
                'name' => '12. Neonatoloji Günleri',
                'description' => 'Yenidoğan tıbbı alanındaki en güncel gelişmelerin ele alındığı uzmanlık kongresi. Neonatal yoğun bakım, prematüre bebek yönetimi ve perinatal tıp konularına odaklanır.',
                'start_date' => '2025-07-10',
                'end_date' => '2025-07-12',
                'location' => 'İstanbul Lütfi Kırdar Kongre Merkezi, İstanbul',
                'is_published' => true,
                'created_by_email' => 'ayse.demir@neonatoloji.org.tr',
            ],
            [
                'organization_name' => 'Pediatrik Endokrinoloji ve Diyabet Derneği',
                'name' => 'Çocukluk Çağı Diyabet ve Obezite Sempozyumu',
                'description' => 'Çocuklarda artan diyabet ve obezite sorunlarına yönelik güncel yaklaşımların tartışıldığı özel sempozyum.',
                'start_date' => '2025-11-20',
                'end_date' => '2025-11-21',
                'location' => 'Ankara Hilton Hotel, Ankara',
                'is_published' => true,
                'created_by_email' => 'fatma.kaya@endokrin.org.tr',
            ],
            [
                'organization_name' => 'Sağlık Bakanlığı Çocuk Sağlığı Daire Başkanlığı',
                'name' => 'Ulusal Çocuk Sağlığı Politikaları Çalıştayı',
                'description' => 'Türkiye\'de çocuk sağlığı politikalarının geliştirilmesi ve uygulanması konularının ele alındığı resmi çalıştay.',
                'start_date' => '2025-05-25',
                'end_date' => '2025-05-26',
                'location' => 'Sağlık Bakanlığı Konferans Salonu, Ankara',
                'is_published' => false, // Taslak örneği
                'created_by_email' => 'ali.yilmaz@saglik.gov.tr',
            ],
            [
                'organization_name' => 'Türk Pediatri Derneği',
                'name' => 'Pediatride Aşı Güncellemeleri Eğitimi',
                'description' => 'Çocukluk çağı aşılamasındaki son gelişmeler ve güncellenen aşı takvimine yönelik eğitim programı.',
                'start_date' => '2025-03-15',
                'end_date' => '2025-03-15',
                'location' => 'İzmir Büyükşehir Belediyesi Kültür Merkezi, İzmir',
                'is_published' => true,
                'created_by_email' => 'mehmet.ozkan@pediatri.org.tr',
            ],
            [
                'organization_name' => 'Türk Neonatoloji Derneği',
                'name' => 'Prematüre Bebek Bakımı İleri Eğitim Kursu',
                'description' => 'Prematüre bebek bakımında uzmanlaşmak isteyen sağlık personeli için düzenlenen kapsamlı eğitim kursu.',
                'start_date' => '2025-12-05',
                'end_date' => '2025-12-07',
                'location' => 'Acıbadem Üniversitesi Tıp Fakültesi, İstanbul',
                'is_published' => false,
                'created_by_email' => 'ayse.demir@neonatoloji.org.tr',
            ],
            [
                'organization_name' => 'Pediatrik Endokrinoloji ve Diyabet Derneği',
                'name' => 'Büyüme Hormonu Eksikliği Tanı ve Tedavi Konsensusu',
                'description' => 'Büyüme hormonu eksikliği tanı ve tedavisinde güncel yaklaşımların belirlenmesi için düzenlenen konsensus toplantısı.',
                'start_date' => '2025-01-28',
                'end_date' => '2025-01-29',
                'location' => 'Conrad İstanbul Bosphorus, İstanbul',
                'is_published' => true,
                'created_by_email' => 'fatma.kaya@endokrin.org.tr',
            ],
            [
                'organization_name' => 'Türk Pediatri Derneği',
                'name' => 'Çocuk Enfeksiyon Hastalıkları Güncelleme Kursu',
                'description' => 'Çocuklarda enfeksiyon hastalıkları tanı, tedavi ve korunma yöntemlerinin güncel bilgiler ışığında ele alındığı eğitim kursu.',
                'start_date' => '2024-12-20',
                'end_date' => '2024-12-21',
                'location' => 'Ege Üniversitesi Tıp Fakültesi, İzmir',
                'is_published' => true,
                'created_by_email' => 'mehmet.ozkan@pediatri.org.tr',
            ],
        ];

        foreach ($events as $eventData) {
            $organization = Organization::where('name', $eventData['organization_name'])->first();
            $creator = User::where('email', $eventData['created_by_email'])->first();

            if ($organization && $creator) {
                Event::create([
                    'organization_id' => $organization->id,
                    'name' => $eventData['name'],
                    'slug' => Str::slug($eventData['name']),
                    'description' => $eventData['description'],
                    'start_date' => $eventData['start_date'],
                    'end_date' => $eventData['end_date'],
                    'location' => $eventData['location'],
                    'is_published' => $eventData['is_published'],
                    'created_by' => $creator->id,
                ]);
            }
        }

        $this->command->info("✅ Created " . Event::count() . " events");
        $this->command->info("📊 Events status: " . Event::where('is_published', true)->count() . " published, " . Event::where('is_published', false)->count() . " draft");
    }
}