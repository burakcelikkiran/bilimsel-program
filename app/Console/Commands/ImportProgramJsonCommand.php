<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Organization;
use App\Services\ProgramJsonImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportProgramJsonCommand extends Command
{
    protected $signature = 'program:import
                            {slug : Etkinlik slug değeri}
                            {file : program.json dosya yolu}
                            {--dry-run : Veritabanına yazmadan sayım yap}
                            {--fresh : Mevcut program verisini silip yeniden yükle}
                            {--force : Onay sormadan fresh import uygula}
                            {--create-event : Etkinlik yoksa oluştur}
                            {--organization= : Organizasyon adı (create-event ile)}';

    protected $description = 'program.json dosyasını etkinliğe içe aktarır';

    /**
     * @var array<string, array<string, mixed>>
     */
    private const EVENT_DEFAULTS = [
        'turkpediatri-kongresi-2026' => [
            'name' => 'Türkpediatri Kongresi 2026',
            'start_date' => '2026-04-15',
            'end_date' => '2026-04-19',
            'organization' => 'Türk Pediatri Derneği',
        ],
    ];

    public function handle(ProgramJsonImporter $importer): int
    {
        $slug = $this->argument('slug');
        $filePath = $this->resolveFilePath($this->argument('file'));

        if (! File::exists($filePath)) {
            $this->error("Dosya bulunamadı: {$filePath}");

            return self::FAILURE;
        }

        $programData = json_decode(File::get($filePath), true);

        if (! is_array($programData)) {
            $this->error('Geçersiz JSON formatı.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $fresh = (bool) $this->option('fresh');

        $event = Event::query()->where('slug', $slug)->first();

        if (! $event) {
            if (! $this->option('create-event')) {
                $this->error("Etkinlik bulunamadı: {$slug}. --create-event ile oluşturabilirsiniz.");

                return self::FAILURE;
            }

            if ($dryRun) {
                $this->warn('Dry-run: etkinlik oluşturulmayacak, yalnızca JSON analiz edilecek.');
                $result = $importer->import(new Event([
                    'organization_id' => 0,
                    'name' => $slug,
                    'slug' => $slug,
                ]), $programData, dryRun: true);

                $this->table(
                    ['Metrik', 'Değer'],
                    collect($result->toArray())
                        ->except('warnings')
                        ->map(fn ($value, $key) => [$key, $value])
                        ->values()
                        ->all()
                );

                $this->info('Dry-run tamamlandı.');

                return self::SUCCESS;
            }

            $event = $this->createEvent($slug);
            $this->info("Etkinlik oluşturuldu: {$event->name} (#{$event->id})");
        }

        if ($fresh && $dryRun) {
            $this->warn('--fresh dry-run modunda yok sayılır.');
        }

        if ($fresh && ! $dryRun) {
            $shouldProceed = $this->option('force') || $this->confirm(
                "{$event->name} etkinliğinin mevcut program verisi silinecek. Devam edilsin mi?",
                true
            );

            if (! $shouldProceed) {
                $this->info('İşlem iptal edildi.');

                return self::SUCCESS;
            }
        }

        $this->info($dryRun ? 'Dry-run: program verisi analiz ediliyor...' : 'Program içe aktarılıyor...');

        $result = $importer->import($event, $programData, $dryRun, $fresh && ! $dryRun);

        $this->table(
            ['Metrik', 'Değer'],
            collect($result->toArray())
                ->except('warnings')
                ->map(fn ($value, $key) => [$key, $value])
                ->values()
                ->all()
        );

        if (! empty($result->warnings)) {
            $this->warn('Uyarılar:');
            foreach (array_slice($result->warnings, 0, 10) as $warning) {
                $this->line("  - {$warning}");
            }

            if (count($result->warnings) > 10) {
                $this->line('  ... +'.(count($result->warnings) - 10).' uyarı daha');
            }
        }

        $this->info($dryRun ? 'Dry-run tamamlandı.' : 'İçe aktarma tamamlandı.');

        return self::SUCCESS;
    }

    private function resolveFilePath(string $file): string
    {
        if (File::exists($file)) {
            return $file;
        }

        $basePath = base_path($file);

        if (File::exists($basePath)) {
            return $basePath;
        }

        return $file;
    }

    private function createEvent(string $slug): Event
    {
        $defaults = self::EVENT_DEFAULTS[$slug] ?? [
            'name' => str($slug)->replace('-', ' ')->title()->toString(),
            'start_date' => now()->addMonth()->toDateString(),
            'end_date' => now()->addMonth()->addDays(4)->toDateString(),
            'organization' => $this->option('organization') ?: 'Varsayılan Organizasyon',
        ];

        $organizationName = $this->option('organization') ?: $defaults['organization'];

        $organization = Organization::query()->firstOrCreate(
            ['name' => $organizationName],
            ['slug' => str($organizationName)->slug(), 'is_active' => true]
        );

        return Event::create([
            'organization_id' => $organization->id,
            'name' => $defaults['name'],
            'slug' => $slug,
            'start_date' => $defaults['start_date'],
            'end_date' => $defaults['end_date'],
            'is_published' => false,
        ]);
    }
}
