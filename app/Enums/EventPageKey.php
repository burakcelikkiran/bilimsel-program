<?php

namespace App\Enums;

enum EventPageKey: string
{
    case GeneralInfo = 'general_info';
    case Invitation = 'invitation';
    case AbstractSubmission = 'abstract_submission';
    case Committees = 'committees';
    case RegistrationAccommodation = 'registration_accommodation';
    case Contact = 'contact';

    public function label(): string
    {
        return match ($this) {
            self::GeneralInfo => 'Genel Bilgiler',
            self::Invitation => 'Davet',
            self::AbstractSubmission => 'Bildiri Gönderimi',
            self::Committees => 'Kurullar',
            self::RegistrationAccommodation => 'Kayıt & Konaklama',
            self::Contact => 'İletişim',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::GeneralInfo => 1,
            self::Invitation => 2,
            self::AbstractSubmission => 3,
            self::Committees => 4,
            self::RegistrationAccommodation => 5,
            self::Contact => 6,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<int, array{key: string, label: string, sort_order: int}>
     */
    public static function sections(): array
    {
        return array_map(
            fn (self $key) => [
                'key' => $key->value,
                'label' => $key->label(),
                'sort_order' => $key->sortOrder(),
            ],
            self::cases()
        );
    }
}
