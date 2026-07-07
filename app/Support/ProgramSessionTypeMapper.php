<?php

namespace App\Support;

class ProgramSessionTypeMapper
{
    /**
     * @var array<string, string>
     */
    private const PROGRAM_JSON_TYPES = [
        'break' => 'Ara',
        'lunch' => 'Öğle Yemeği',
        'satellite' => 'Uydu Sempozyumu',
        'workshop' => 'kurs',
        'main' => 'Oturum',
        'parallel' => 'Oturum',
        'plenary' => 'Oturum',
        'oral_presentation' => 'Sözlü Bildiri Oturumları',
        'special' => 'Özel Oturum',
        'poster' => 'Poster',
        'social' => 'Sosyal',
    ];

    /**
     * @var array<string, string>
     */
    private const PROGRAM_JSON_TO_SESSION_TYPE = [
        'kurs' => 'workshop',
        'Ara' => 'break',
        'Öğle Yemeği' => 'lunch',
        'Uydu Sempozyumu' => 'satellite',
        'Oturum' => 'main',
        'Sözlü Bildiri Oturumları' => 'oral_presentation',
        'AÇILIŞ' => 'special',
        'Genç TPK Oturumları' => 'special',
        'TAP Oturumları' => 'special',
        'Özel Oturum' => 'special',
        'Poster' => 'poster',
        'Sosyal' => 'social',
    ];

    /**
     * @var array<string, string>
     */
    private const DISPLAY_LABELS = [
        'plenary' => 'Genel Oturum',
        'parallel' => 'Paralel Oturum',
        'workshop' => 'Workshop',
        'poster' => 'Poster',
        'break' => 'Ara',
        'lunch' => 'Öğle Arası',
        'social' => 'Sosyal',
        'main' => 'Ana Oturum',
        'satellite' => 'Uydu Sempozyumu',
        'oral_presentation' => 'Sözlü Bildiri',
        'special' => 'Özel Oturum',
    ];

    public static function programJsonType(string $sessionType): string
    {
        return self::PROGRAM_JSON_TYPES[$sessionType] ?? ucfirst($sessionType);
    }

    public static function fromProgramJsonType(string $programJsonType): string
    {
        return self::PROGRAM_JSON_TO_SESSION_TYPE[$programJsonType] ?? 'special';
    }

    public static function hasProgramJsonTypeMapping(string $programJsonType): bool
    {
        return array_key_exists($programJsonType, self::PROGRAM_JSON_TO_SESSION_TYPE);
    }

    public static function displayLabel(string $sessionType): string
    {
        return self::DISPLAY_LABELS[$sessionType] ?? ucfirst($sessionType);
    }

    public static function typeUuid(string $sessionType): string
    {
        return self::deterministicUuid("session_type:{$sessionType}");
    }

    public static function sessionUuid(int $sessionId): string
    {
        return self::deterministicUuid("session:{$sessionId}");
    }

    private static function deterministicUuid(string $input): string
    {
        $hash = substr(hash('sha256', $input), 0, 32);

        return sprintf(
            '%08s-%04s-%04s-%04s-%12s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12)
        );
    }
}
