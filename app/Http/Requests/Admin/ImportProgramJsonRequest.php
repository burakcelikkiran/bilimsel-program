<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ImportProgramJsonRequest extends FormRequest
{
    /**
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $decodedProgramData = null;

    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event && ($this->user()?->can('import', $event) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['nullable', 'required_without:json', 'file', 'max:10240', 'extensions:json,txt'],
            'json' => ['nullable', 'required_without:file', 'string', 'max:10485760'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required_without' => 'JSON dosyası yükleyin veya JSON metni yapıştırın.',
            'file.file' => 'Geçerli bir dosya seçiniz.',
            'file.max' => 'Dosya boyutu en fazla 10MB olabilir.',
            'file.extensions' => 'Dosya JSON formatında olmalıdır.',
            'json.required_without' => 'JSON dosyası yükleyin veya JSON metni yapıştırın.',
            'json.string' => 'JSON metin formatında olmalıdır.',
            'json.max' => 'JSON metni en fazla 10MB olabilir.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $payload = $this->decodePayload($validator);

            if ($payload === null) {
                return;
            }

            $this->validateProgramStructure($validator, $payload);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function programData(): array
    {
        if ($this->decodedProgramData === null) {
            throw new \RuntimeException('Program JSON verisi doğrulanmadı.');
        }

        return $this->decodedProgramData;
    }

    protected function prepareForValidation(): void
    {
        $json = $this->input('json');

        if (is_string($json) && trim($json) === '') {
            $this->merge(['json' => null]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function decodePayload(Validator $validator): ?array
    {
        $raw = $this->rawJsonString();

        if ($raw === null) {
            $validator->errors()->add('json', 'JSON içeriği okunamadı.');

            return null;
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $validator->errors()->add('json', 'Geçersiz JSON formatı.');

            return null;
        }

        if (! is_array($decoded)) {
            $validator->errors()->add('json', 'JSON bir dizi olmalıdır.');

            return null;
        }

        if ($this->isTimelineFormat($decoded)) {
            $validator->errors()->add(
                'json',
                'Bu timeline JSON’u. Lütfen Program JSON formatını kullanın (Date, Venues, Sessions).'
            );

            return null;
        }

        if (! array_is_list($decoded)) {
            $validator->errors()->add('json', 'Program JSON kökü bir gün dizisi olmalıdır.');

            return null;
        }

        if ($decoded === []) {
            $validator->errors()->add('json', 'Program JSON boş olamaz.');

            return null;
        }

        /** @var array<int, array<string, mixed>> $decoded */
        $this->decodedProgramData = $decoded;

        return $decoded;
    }

    /**
     * @param  array<int, mixed>  $programData
     */
    private function validateProgramStructure(Validator $validator, array $programData): void
    {
        foreach ($programData as $index => $dayData) {
            $dayNumber = $index + 1;

            if (! is_array($dayData)) {
                $validator->errors()->add('json', "{$dayNumber}. gün geçersiz bir nesne.");

                continue;
            }

            if (! $this->hasResolvableDate($dayData)) {
                $validator->errors()->add(
                    'json',
                    "{$dayNumber}. günde IsoDate veya Date (gg.aa.yyyy) alanı zorunludur."
                );
            }

            if (! isset($dayData['Venues']) || ! is_array($dayData['Venues'])) {
                $validator->errors()->add('json', "{$dayNumber}. günde Venues dizisi zorunludur.");
            }
        }
    }

    /**
     * @param  array<string, mixed>  $dayData
     */
    private function hasResolvableDate(array $dayData): bool
    {
        $isoDate = trim((string) ($dayData['IsoDate'] ?? ''));

        if ($isoDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $isoDate) === 1) {
            return true;
        }

        $date = trim((string) ($dayData['Date'] ?? ''));

        if ($date === '') {
            return false;
        }

        try {
            $parsed = Carbon::createFromFormat('d.m.Y', $date);

            return $parsed instanceof Carbon && $parsed->format('d.m.Y') === $date;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @param  array<mixed>  $decoded
     */
    private function isTimelineFormat(array $decoded): bool
    {
        if (array_is_list($decoded)) {
            return false;
        }

        return array_key_exists('event', $decoded)
            || array_key_exists('data', $decoded)
            || array_key_exists('generated_at', $decoded);
    }

    private function rawJsonString(): ?string
    {
        if ($this->hasFile('file')) {
            $contents = file_get_contents($this->file('file')->getRealPath());

            return $contents === false ? null : $contents;
        }

        $json = $this->input('json');

        return is_string($json) ? $json : null;
    }
}
