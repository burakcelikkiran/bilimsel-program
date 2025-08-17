<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Creating organizations...');

        $organizations = [
            [
                'name' => 'Türk Pediatri Derneği',
                'description' => 'Türkiye\'nin en köklü ve prestijli pediatri derneği. 1962 yılından beri çocuk sağlığı alanında bilimsel çalışmalar ve eğitim faaliyetleri yürütmektedir.',
                'contact_email' => 'info@turkpediatri.org.tr',
                'contact_phone' => '+90 312 466 23 71',
                'is_active' => true,
                'organizer_email' => 'mehmet.ozkan@pediatri.org.tr',
                'editors' => ['zeynep.sahin@hastane.com', 'emre.celik@universite.edu.tr'],
            ],
            [
                'name' => 'Türk Neonatoloji Derneği',
                'description' => 'Yenidoğan tıbbı alanında Türkiye\'nin öncü kuruluşu. Prematüre bebek bakımı ve neonatal yoğun bakım konularında araştırma ve eğitim faaliyetleri düzenlemektedir.',
                'contact_email' => 'iletisim@neonatoloji.org.tr',
                'contact_phone' => '+90 216 578 45 32',
                'is_active' => true,
                'organizer_email' => 'ayse.demir@neonatoloji.org.tr',
                'editors' => ['seda.aydin@klinik.com'],
            ],
            [
                'name' => 'Pediatrik Endokrinoloji ve Diyabet Derneği',
                'description' => 'Çocuklarda endokrin sistemi hastalıkları, büyüme-gelişme bozuklukları ve diyabet konularında uzmanlaşmış bilimsel dernek.',
                'contact_email' => 'bilgi@pedendo.org.tr',
                'contact_phone' => '+90 212 234 67 89',
                'is_active' => true,
                'organizer_email' => 'fatma.kaya@endokrin.org.tr',
                'editors' => ['burak.ozdemir@platform.org'],
            ],
            [
                'name' => 'Sağlık Bakanlığı Çocuk Sağlığı Daire Başkanlığı',
                'description' => 'T.C. Sağlık Bakanlığı bünyesinde çocuk sağlığı politikalarının belirlenmesi ve uygulanması konularında çalışan resmi kurum.',
                'contact_email' => 'cocuksagligi@saglik.gov.tr', 
                'contact_phone' => '+90 312 585 12 34',
                'is_active' => true,
                'organizer_email' => 'ali.yilmaz@saglik.gov.tr',
                'editors' => ['elif.koc@merkez.gov.tr'],
            ],
            [
                'name' => 'Pediatrik Kardiyoloji Derneği',
                'description' => 'Çocuklarda kalp hastalıkları tanı ve tedavisi konusunda çalışan uzmanların oluşturduğu bilimsel dernek.',
                'contact_email' => 'info@pedkardiyoloji.org.tr',
                'contact_phone' => '+90 216 345 78 90',
                'is_active' => false, // Pasif örneği için
                'organizer_email' => null,
                'editors' => [],
            ],
        ];

        foreach ($organizations as $orgData) {
            try {
                // Create organization - UUID kaldırıldı
                $organization = Organization::create([
                    'name' => $orgData['name'],
                    'description' => $orgData['description'],
                    'contact_email' => $orgData['contact_email'],
                    'contact_phone' => $orgData['contact_phone'],
                    'is_active' => $orgData['is_active'],
                ]);

                $this->command->info("Created organization: {$organization->name} (ID: {$organization->id})");

                // Attach organizer
                if ($orgData['organizer_email']) {
                    $organizer = User::where('email', $orgData['organizer_email'])->first();
                    if ($organizer) {
                        $organization->users()->attach($organizer->id, ['role' => 'organizer']);
                        $this->command->info("  └── Attached organizer: {$organizer->email}");
                    } else {
                        $this->command->warn("  └── Organizer not found: {$orgData['organizer_email']}");
                    }
                }

                // Attach editors
                foreach ($orgData['editors'] as $editorEmail) {
                    $editor = User::where('email', $editorEmail)->first();
                    if ($editor) {
                        $organization->users()->attach($editor->id, ['role' => 'editor']);
                        $this->command->info("  └── Attached editor: {$editor->email}");
                    } else {
                        $this->command->warn("  └── Editor not found: {$editorEmail}");
                    }
                }

            } catch (\Exception $e) {
                $this->command->error("Error creating organization {$orgData['name']}: " . $e->getMessage());
            }
        }

        $this->command->info("✅ Created " . Organization::count() . " organizations");
        $this->command->info("📎 Attached users to organizations with roles");
    }
}