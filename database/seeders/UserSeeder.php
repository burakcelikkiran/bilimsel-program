<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👤 Creating users...');

        // Admin User
        User::create([
            'name' => 'Sistem Yöneticisi',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Organizer Users
        $organizers = [
            [
                'name' => 'Dr. Mehmet Özkan',
                'email' => 'mehmet.ozkan@pediatri.org.tr',
                'role' => 'organizer',
            ],
            [
                'name' => 'Prof. Dr. Ayşe Demir',
                'email' => 'ayse.demir@neonatoloji.org.tr', 
                'role' => 'organizer',
            ],
            [
                'name' => 'Doç. Dr. Fatma Kaya',
                'email' => 'fatma.kaya@endokrin.org.tr',
                'role' => 'organizer',
            ],
            [
                'name' => 'Dr. Ali Yılmaz',
                'email' => 'ali.yilmaz@saglik.gov.tr',
                'role' => 'organizer',
            ],
        ];

        foreach ($organizers as $organizer) {
            User::create([
                'name' => $organizer['name'],
                'email' => $organizer['email'],
                'password' => Hash::make('password'),
                'role' => $organizer['role'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        // Editor Users
        $editors = [
            [
                'name' => 'Uzm. Dr. Zeynep Şahin',
                'email' => 'zeynep.sahin@hastane.com',
                'role' => 'editor',
            ],
            [
                'name' => 'Dr. Emre Çelik',
                'email' => 'emre.celik@universite.edu.tr',
                'role' => 'editor',
            ],
            [
                'name' => 'Uzm. Dr. Seda Aydın',
                'email' => 'seda.aydin@klinik.com',
                'role' => 'editor',
            ],
            [
                'name' => 'Dr. Burak Özdemir',
                'email' => 'burak.ozdemir@platform.org',
                'role' => 'editor',
            ],
            [
                'name' => 'Uzm. Dr. Elif Koç',
                'email' => 'elif.koc@merkez.gov.tr',
                'role' => 'editor',
            ],
        ];

        foreach ($editors as $editor) {
            User::create([
                'name' => $editor['name'],
                'email' => $editor['email'],
                'password' => Hash::make('password'),
                'role' => $editor['role'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info("✅ Created " . User::count() . " users");
    }
}