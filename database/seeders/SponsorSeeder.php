<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Creating sponsors...');

        $sponsors = [
            // Platinum Level Sponsors
            [
                'name' => 'PFIZER',
                'website' => 'https://www.pfizer.com.tr',
                'contact_email' => 'info@pfizer.com.tr',
                'sponsor_level' => 'platinum',
            ],
            [
                'name' => 'ROCHE',
                'website' => 'https://www.roche.com.tr',
                'contact_email' => 'iletisim@roche.com.tr',
                'sponsor_level' => 'platinum',
            ],
            [
                'name' => 'NOVARTIS',
                'website' => 'https://www.novartis.com.tr',
                'contact_email' => 'info@novartis.com.tr',
                'sponsor_level' => 'platinum',
            ],

            // Gold Level Sponsors
            [
                'name' => 'SANOFI',
                'website' => 'https://www.sanofi.com.tr',
                'contact_email' => 'bilgi@sanofi.com.tr',
                'sponsor_level' => 'gold',
            ],
            [
                'name' => 'ABBOTT',
                'website' => 'https://www.abbott.com.tr',
                'contact_email' => 'info@abbott.com.tr',
                'sponsor_level' => 'gold',
            ],
            [
                'name' => 'JOHNSON & JOHNSON',
                'website' => 'https://www.jnj.com.tr',
                'contact_email' => 'iletisim@jnj.com.tr',
                'sponsor_level' => 'gold',
            ],
            [
                'name' => 'MERCK',
                'website' => 'https://www.merck.com.tr',
                'contact_email' => 'info@merck.com.tr',
                'sponsor_level' => 'gold',
            ],

            // Silver Level Sponsors
            [
                'name' => 'NESTLE',
                'website' => 'https://www.nestle.com.tr',
                'contact_email' => 'bilgi@nestle.com.tr',
                'sponsor_level' => 'silver',
            ],
            [
                'name' => 'FRESENIUS KABI',
                'website' => 'https://www.fresenius-kabi.com.tr',
                'contact_email' => 'info@fresenius-kabi.com.tr',
                'sponsor_level' => 'silver',
            ],
            [
                'name' => 'BAXTER',
                'website' => 'https://www.baxter.com.tr',
                'contact_email' => 'iletisim@baxter.com.tr',
                'sponsor_level' => 'silver',
            ],
            [
                'name' => 'BIOTEST',
                'website' => 'https://www.biotest.com.tr',
                'contact_email' => 'info@biotest.com.tr',
                'sponsor_level' => 'silver',
            ],
            [
                'name' => 'OCTAPHARMA',
                'website' => 'https://www.octapharma.com.tr',
                'contact_email' => 'bilgi@octapharma.com.tr',
                'sponsor_level' => 'silver',
            ],

            // Bronze Level Sponsors
            [
                'name' => 'ORZAX',
                'website' => 'https://www.orzax.com',
                'contact_email' => 'info@orzax.com',
                'sponsor_level' => 'bronze',
            ],
            [
                'name' => 'OPELLA',
                'website' => 'https://www.opella.com.tr',
                'contact_email' => 'iletisim@opella.com.tr',
                'sponsor_level' => 'bronze',
            ],
            [
                'name' => 'SANTA FARMA',
                'website' => 'https://www.santafarma.com.tr',
                'contact_email' => 'info@santafarma.com.tr',
                'sponsor_level' => 'bronze',
            ],
            [
                'name' => 'BILIM İLAÇ',
                'website' => 'https://www.bilimilac.com.tr',
                'contact_email' => 'bilgi@bilimilac.com.tr',
                'sponsor_level' => 'bronze',
            ],
            [
                'name' => 'ABDI İBRAHİM',
                'website' => 'https://www.abdiibrahim.com.tr',
                'contact_email' => 'info@abdiibrahim.com.tr',
                'sponsor_level' => 'bronze',
            ],
            [
                'name' => 'DEVA HOLDING',
                'website' => 'https://www.deva.com.tr',
                'contact_email' => 'iletisim@deva.com.tr',
                'sponsor_level' => 'bronze',
            ],
            [
                'name' => 'KOCAK FARMA',
                'website' => 'https://www.kocakfarma.com.tr',
                'contact_email' => 'info@kocakfarma.com.tr',
                'sponsor_level' => 'bronze',
            ],
            [
                'name' => 'NOBEL İLAÇ',
                'website' => 'https://www.nobelilac.com.tr',
                'contact_email' => 'bilgi@nobelilac.com.tr',
                'sponsor_level' => 'bronze',
            ],
        ];

        $organizations = Organization::where('is_active', true)->get();
        $totalSponsors = 0;

        foreach ($organizations as $organization) {
            // Her organizasyon için 8-12 sponsor oluştur
            $sponsorCount = rand(8, 12);
            $selectedSponsors = collect($sponsors)->random($sponsorCount);

            foreach ($selectedSponsors as $sponsorData) {
                Sponsor::create([
                    'organization_id' => $organization->id,
                    'name' => $sponsorData['name'],
                    'website' => $sponsorData['website'],
                    'contact_email' => $sponsorData['contact_email'],
                    'sponsor_level' => $sponsorData['sponsor_level'],
                    'is_active' => rand(1, 100) <= 90, // %90 aktif sponsor
                ]);

                $totalSponsors++;
            }
        }

        $this->command->info("✅ Created {$totalSponsors} sponsors across " . $organizations->count() . " organizations");
        
        // Level statistics
        $platinum = Sponsor::where('sponsor_level', 'platinum')->count();
        $gold = Sponsor::where('sponsor_level', 'gold')->count();
        $silver = Sponsor::where('sponsor_level', 'silver')->count();
        $bronze = Sponsor::where('sponsor_level', 'bronze')->count();
        
        $this->command->info("📊 Sponsor levels: Platinum: {$platinum}, Gold: {$gold}, Silver: {$silver}, Bronze: {$bronze}");
    }
}