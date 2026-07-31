<?php

namespace App\Services;

use App\Models\Participant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantImporter
{
    /**
     * @return array{imported: int, updated: int, skipped: int, errors: array<int, string>}
     */
    public function importFromFile(UploadedFile $file, int $organizationId, bool $updateExisting = false): array
    {
        $rows = Excel::toArray([], $file)[0] ?? [];

        if ($rows === []) {
            throw new \RuntimeException('Dosya boş veya okunamıyor.');
        }

        $headers = $this->normalizeHeaders(array_shift($rows));
        $this->assertRequiredColumns($headers);

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $rowIndex => $row) {
            $rowData = $this->mapRow($headers, $row);

            if ($this->isEmptyRow($rowData)) {
                continue;
            }

            try {
                $participantData = $this->mapRowToParticipantData($rowData, $organizationId);

                $validator = Validator::make($participantData, [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'email' => 'nullable|email|max:255',
                    'phone' => 'nullable|string|max:50',
                    'title' => 'nullable|string|max:255',
                    'affiliation' => 'nullable|string|max:255',
                    'bio' => 'nullable|string',
                    'is_speaker' => 'boolean',
                    'is_moderator' => 'boolean',
                ]);

                if ($validator->fails()) {
                    $errors[] = 'Satır '.($rowIndex + 2).': '.implode(', ', $validator->errors()->all());
                    $skipped++;

                    continue;
                }

                $existingParticipant = $this->findExistingParticipant($organizationId, $participantData);

                if ($existingParticipant) {
                    if ($updateExisting) {
                        $existingParticipant->update($participantData);
                        $updated++;
                    } else {
                        $skipped++;
                    }

                    continue;
                }

                Participant::create($participantData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = 'Satır '.($rowIndex + 2).': '.$e->getMessage();
                $skipped++;
            }
        }

        return compact('imported', 'updated', 'skipped', 'errors');
    }

    public function buildSummaryMessage(array $result): string
    {
        $message = "İçe aktarma tamamlandı. Yeni: {$result['imported']}, Güncellenen: {$result['updated']}, Atlanan: {$result['skipped']}";

        if ($result['errors'] !== []) {
            $message .= ' Hatalar: '.implode('; ', array_slice($result['errors'], 0, 5));

            if (count($result['errors']) > 5) {
                $message .= ' (+'.(count($result['errors']) - 5).' daha...)';
            }
        }

        return $message;
    }

    /**
     * @param  array<int, string|null>  $headers
     */
    private function assertRequiredColumns(array $headers): void
    {
        $requiredColumns = ['ad', 'soyad'];
        $missingColumns = array_diff($requiredColumns, $headers);

        if ($missingColumns !== []) {
            throw new \RuntimeException('Gerekli kolonlar eksik: '.implode(', ', $missingColumns));
        }
    }

    /**
     * @param  array<string, mixed>  $participantData
     */
    private function findExistingParticipant(int $organizationId, array $participantData): ?Participant
    {
        if (($participantData['email'] ?? null) !== null) {
            return Participant::query()
                ->where('organization_id', $organizationId)
                ->where('email', $participantData['email'])
                ->first();
        }

        return Participant::query()
            ->where('organization_id', $organizationId)
            ->where('first_name', $participantData['first_name'])
            ->where('last_name', $participantData['last_name'])
            ->first();
    }

    /**
     * @param  array<int, mixed>  $headers
     * @return array<int, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        $aliases = [
            'e-posta' => 'email',
            'e posta' => 'email',
            'eposta' => 'email',
            'isim' => 'ad',
            'soyisim' => 'soyad',
            'konuşmacı moderatör' => 'konuşmacı',
        ];

        return array_map(function ($header) use ($aliases) {
            $normalized = mb_strtolower(trim((string) $header));

            return $aliases[$normalized] ?? $normalized;
        }, $headers);
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, mixed>  $row
     * @return array<string, string>
     */
    private function mapRow(array $headers, array $row): array
    {
        $rowData = [];

        foreach ($headers as $index => $header) {
            $rowData[$header] = trim((string) ($row[$index] ?? ''));
        }

        return $rowData;
    }

    /**
     * @param  array<string, string>  $rowData
     */
    private function isEmptyRow(array $rowData): bool
    {
        return ($rowData['ad'] ?? '') === ''
            && ($rowData['soyad'] ?? '') === '';
    }

    /**
     * @param  array<string, string>  $rowData
     * @return array<string, mixed>
     */
    private function mapRowToParticipantData(array $rowData, int $organizationId): array
    {
        return [
            'organization_id' => $organizationId,
            'first_name' => $rowData['ad'] ?? '',
            'last_name' => $rowData['soyad'] ?? '',
            'email' => ($rowData['email'] ?? '') !== '' ? $rowData['email'] : null,
            'phone' => ($rowData['telefon'] ?? '') !== '' ? $rowData['telefon'] : null,
            'title' => ($rowData['ünvan'] ?? '') !== '' ? $rowData['ünvan'] : null,
            'affiliation' => ($rowData['kurum'] ?? '') !== '' ? $rowData['kurum'] : null,
            'bio' => ($rowData['biyografi'] ?? '') !== '' ? $rowData['biyografi'] : null,
            'is_speaker' => $this->parseBoolean($rowData['konuşmacı'] ?? null),
            'is_moderator' => $this->parseBoolean($rowData['moderatör'] ?? null),
        ];
    }

    private function parseBoolean(?string $value): bool
    {
        if ($value === null || trim($value) === '') {
            return false;
        }

        $normalized = mb_strtolower(trim($value));

        return in_array($normalized, ['1', 'true', 'evet', 'yes', 'e'], true);
    }
}
