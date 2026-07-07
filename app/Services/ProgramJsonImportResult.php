<?php

namespace App\Services;

class ProgramJsonImportResult
{
    public function __construct(
        public int $days = 0,
        public int $venues = 0,
        public int $sessions = 0,
        public int $presentations = 0,
        public int $participants = 0,
        public int $moderatorLinks = 0,
        public int $speakerLinks = 0,
        /** @var array<int, string> */
        public array $warnings = [],
    ) {}

    /**
     * @return array<string, int|array<int, string>>
     */
    public function toArray(): array
    {
        return [
            'days' => $this->days,
            'venues' => $this->venues,
            'sessions' => $this->sessions,
            'presentations' => $this->presentations,
            'participants' => $this->participants,
            'moderator_links' => $this->moderatorLinks,
            'speaker_links' => $this->speakerLinks,
            'warnings' => $this->warnings,
        ];
    }
}
