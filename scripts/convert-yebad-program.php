#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Converts yebad2026 program-tr.vue HTML tables to ProgramJsonImporter JSON.
 *
 * Usage: php scripts/convert-yebad-program.php
 */
const SOURCE_VUE = __DIR__.'/../yebad2026/pages/program-tr.vue';
const OUTPUT_JSON = __DIR__.'/../yebadprogram.json';

/** @var array<string, string> */
const SESSION_TYPE_IDS = [
    'kurs' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
    'Oturum' => 'cf38da30-ea9f-408f-92ff-6ff376edbdab',
    'AÇILIŞ' => '37561e12-5896-4d33-8901-d5bbcd762500',
    'Uydu Sempozyumu' => 'b2c3d4e5-f6a7-8901-bcde-f23456789012',
    'Ara' => 'c3d4e5f6-a7b8-9012-cdef-345678901234',
    'Öğle Yemeği' => 'd4e5f6a7-b8c9-0123-def0-456789012345',
    'Sözlü Bildiri Oturumları' => 'e5f6a7b8-c9d0-1234-ef01-567890123456',
];

/** @var array<string, array{iso: string, display: string}> */
const DAYS = [
    'gun1' => ['iso' => '2026-05-07', 'display' => '07.05.2026'],
    'gun2' => ['iso' => '2026-05-08', 'display' => '08.05.2026'],
    'gun3' => ['iso' => '2026-05-09', 'display' => '09.05.2026'],
    'gun4' => ['iso' => '2026-05-10', 'display' => '10.05.2026'],
];

function main(): void
{
    $vuePath = resolveSourcePath();
    $vueContent = file_get_contents($vuePath);
    if ($vueContent === false) {
        fwrite(STDERR, "Kaynak dosya okunamadı: {$vuePath}\n");
        exit(1);
    }

    $template = extractTemplate($vueContent);

    $program = [];

    // Day 1 – Kurslar (Salon A & B)
    $gun1Html = extractBetween($template, '<div id="gun1"', '<div id="gun2"');
    $salonATbody = extractTbodyByMarker($gun1Html, 'key="salon1"');
    $salonBTbody = extractTbodyByMarker($gun1Html, 'key="salon2"');
    $program[] = buildDay(
        DAYS['gun1']['iso'],
        DAYS['gun1']['display'],
        [
            ['name' => 'Salon A', 'sessions' => parseSingleVenueTable($salonATbody, DAYS['gun1'], 'kurs')],
            ['name' => 'Salon B', 'sessions' => parseSingleVenueTable($salonBTbody, DAYS['gun1'], 'kurs')],
        ]
    );

    // Days 2–4 – parallel Salon A/B
    foreach (['gun2', 'gun3', 'gun4'] as $gunKey) {
        $nextKey = match ($gunKey) {
            'gun2' => 'gun3',
            'gun3' => 'gun4',
            'gun4' => 'sozlu',
        };
        $sectionHtml = extractBetween($template, "<div id=\"{$gunKey}\"", "<div id=\"{$nextKey}\"");
        $tbody = extractFirstTbody($sectionHtml);
        ['Salon A' => $venueA, 'Salon B' => $venueB] = parseParallelTable($tbody, DAYS[$gunKey]);
        $program[] = buildDay(
            DAYS[$gunKey]['iso'],
            DAYS[$gunKey]['display'],
            [
                ['name' => 'Salon A', 'sessions' => $venueA],
                ['name' => 'Salon B', 'sessions' => $venueB],
            ]
        );
    }

    // Salon C – Sözlü sunumlar
    $sozluHtml = extractBetween($template, '<div id="sozlu"', '</template>');
    attachSozluVenue($program, extractTbodyByMarker($sozluHtml, 'key="sozlu-cuma"'), '2026-05-08');
    attachSozluVenue($program, extractTbodyByMarker($sozluHtml, 'key="sozlu-cumartesi"'), '2026-05-09');

    $json = json_encode($program, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        fwrite(STDERR, "JSON encode hatası\n");
        exit(1);
    }

    file_put_contents(OUTPUT_JSON, $json.PHP_EOL);

    echo 'Yazıldı: '.OUTPUT_JSON.PHP_EOL.PHP_EOL;
    echo 'Oturum sayıları (gün bazında):'.PHP_EOL;
    foreach ($program as $day) {
        $total = 0;
        $byVenue = [];
        foreach ($day['Venues'] as $venue) {
            $count = count($venue['Sessions']);
            $byVenue[$venue['Venue']] = $count;
            $total += $count;
        }
        $venueSummary = implode(', ', array_map(
            fn (string $v, int $c) => "{$v}: {$c}",
            array_keys($byVenue),
            array_values($byVenue)
        ));
        echo "  {$day['IsoDate']} ({$day['Date']}): {$total} oturum [{$venueSummary}]".PHP_EOL;
    }
}

function resolveSourcePath(): string
{
    $local = __DIR__.'/../yebad2026/pages/program-tr.vue';
    if (is_file($local)) {
        return $local;
    }

    $external = '/Users/burakcelikkiran/Projects/yebad2026/pages/program-tr.vue';
    if (is_file($external)) {
        return $external;
    }

    return $local;
}

function extractTemplate(string $vueContent): string
{
    if (! preg_match('/<template>(.*)<\/template>/s', $vueContent, $matches)) {
        fwrite(STDERR, "Vue template bulunamadı\n");
        exit(1);
    }

    return $matches[1];
}

function extractBetween(string $html, string $startMarker, string $endMarker): string
{
    $start = strpos($html, $startMarker);
    if ($start === false) {
        return '';
    }
    $end = strpos($html, $endMarker, $start + strlen($startMarker));
    if ($end === false) {
        return substr($html, $start);
    }

    return substr($html, $start, $end - $start);
}

function extractTbodyByMarker(string $html, string $marker): string
{
    $pos = strpos($html, $marker);
    if ($pos === false) {
        return '';
    }
    $fragment = substr($html, $pos);

    return extractFirstTbody($fragment);
}

function extractFirstTbody(string $html): string
{
    if (! preg_match('/<tbody[^>]*>(.*?)<\/tbody>/s', $html, $matches)) {
        return '';
    }

    return $matches[1];
}

/**
 * @return array<int, array<string, mixed>>
 */
function parseSingleVenueTable(string $tbodyHtml, array $day, string $defaultSessionType): array
{
    $sessions = [];
    $currentSession = null;
    $lastSessionIndex = null;
    $rows = parseTableRows($tbodyHtml);

    foreach ($rows as $row) {
        $cells = $row['cells'];
        if (count($cells) < 2) {
            continue;
        }

        [$startTime, $endTime] = parseTimeRange($cells[0]['text']);
        $contentHtml = $cells[1]['html'];
        $contentText = $cells[1]['text'];
        $isHeader = str_contains($cells[1]['class'], 'tg-0pky2') || isSessionHeaderText($contentText);

        if (isBreakText($contentText) || isLunchText($contentText)) {
            $currentSession = null;
            $sessions[] = buildBreakSession($day, $startTime, $endTime, $contentText);

            continue;
        }

        if ($isHeader) {
            $currentSession = buildSessionFromHeader($day, $startTime, $endTime, $contentHtml, $defaultSessionType);
            $sessions[] = $currentSession;
            $lastSessionIndex = count($sessions) - 1;

            continue;
        }

        if ($currentSession === null && isset($lastSessionIndex)) {
            $currentSession = $sessions[$lastSessionIndex];
        }

        if ($currentSession === null) {
            continue;
        }

        $presentation = buildPresentation($day, $startTime, $endTime, $contentHtml);
        if ($presentation !== null) {
            $sessions[$lastSessionIndex]['SessionContents'][] = $presentation;
            $currentSession = $sessions[$lastSessionIndex];
        }
    }

    return $sessions;
}

/**
 * @return array{Salon A: array<int, array<string, mixed>>, Salon B: array<int, array<string, mixed>>}
 */
function parseParallelTable(string $tbodyHtml, array $day): array
{
    $venues = [
        'Salon A' => [],
        'Salon B' => [],
    ];
    $current = [
        'Salon A' => null,
        'Salon B' => null,
    ];

    $rows = parseTableRows($tbodyHtml);

    foreach ($rows as $row) {
        $cells = $row['cells'];
        $cellCount = count($cells);

        if ($cellCount === 0) {
            continue;
        }

        // Full-width rows (opening, lunch, break with colspan)
        if ($cellCount <= 2 && ($cellCount === 1 || (int) ($cells[0]['colspan'] ?? 1) >= 4 || (int) ($cells[1]['colspan'] ?? 1) >= 3)) {
            $timeCell = $cells[0];
            $contentCell = $cells[$cellCount - 1];
            [$startTime, $endTime] = parseTimeRange($timeCell['text']);
            $contentText = $contentCell['text'];
            $contentHtml = $contentCell['html'];

            if (isBreakText($contentText) || isLunchText($contentText)) {
                $current['Salon A'] = null;
                $current['Salon B'] = null;
                $breakSession = buildBreakSession($day, $startTime, $endTime, $contentText);
                $venues['Salon A'][] = $breakSession;
                $venues['Salon B'][] = array_merge($breakSession, [
                    'SessionID' => sessionUuid("{$day['iso']}|Salon B|{$startTime}|{$contentText}"),
                ]);

                continue;
            }

            if (isOpeningText($contentText)) {
                $current['Salon A'] = null;
                $current['Salon B'] = null;
                $openingA = buildOpeningSession($day, $startTime, $endTime, $contentHtml);
                $openingB = array_merge($openingA, [
                    'SessionID' => sessionUuid("{$day['iso']}|Salon B|opening|{$startTime}"),
                ]);
                $venues['Salon A'][] = $openingA;
                $venues['Salon B'][] = $openingB;

                continue;
            }

            if (isKapanisText($contentText)) {
                $current['Salon A'] = null;
                $current['Salon B'] = null;
                $kapanis = buildSessionShell($day, 'Oturum', 'KAPANIŞ', $startTime, $endTime, '', [], [], false);
                $venues['Salon A'][] = $kapanis;
                $venues['Salon B'][] = array_merge($kapanis, [
                    'SessionID' => sessionUuid("{$day['iso']}|Salon B|kapanis|{$startTime}"),
                ]);

                continue;
            }
        }

        // Standard 4-column row: process both venues
        if ($cellCount === 4) {
            processParallelVenueCell($venues, $current, 'Salon A', $cells[0], $cells[1], $day);
            processParallelVenueCell($venues, $current, 'Salon B', $cells[2], $cells[3], $day);

            continue;
        }

        // Rowspan continuation (2 cells): Salon B only
        if ($cellCount === 2) {
            processParallelVenueCell($venues, $current, 'Salon B', $cells[0], $cells[1], $day);
        }
    }

    return $venues;
}

/**
 * @param  array<string, array<int, array<string, mixed>>>  $venues
 * @param  array<string, array<string, mixed>|null>  $current
 * @param  array{text: string, html: string, class: string, colspan?: int}  $timeCell
 * @param  array{text: string, html: string, class: string, colspan?: int}  $contentCell
 */
function processParallelVenueCell(array &$venues, array &$current, string $venueName, array $timeCell, array $contentCell, array $day): void
{
    [$startTime, $endTime] = parseTimeRange($timeCell['text']);
    $contentHtml = $contentCell['html'];
    $contentText = $contentCell['text'];

    if ($contentText === '') {
        return;
    }

    if (isBreakText($contentText) || isLunchText($contentText)) {
        $current[$venueName] = null;
        $venues[$venueName][] = buildBreakSession($day, $startTime, $endTime, $contentText, $venueName);

        return;
    }

    $isHeader = str_contains($contentCell['class'], 'tg-0pky2') || isSessionHeaderText($contentText);

    if ($isHeader) {
        $sessionType = detectSessionType($contentText, $contentHtml);
        $current[$venueName] = buildSessionFromHeader($day, $startTime, $endTime, $contentHtml, $sessionType, $venueName);
        $venues[$venueName][] = $current[$venueName];

        return;
    }

    if ($current[$venueName] === null) {
        return;
    }

    $presentation = buildPresentation($day, $startTime, $endTime, $contentHtml);
    if ($presentation !== null) {
        $idx = count($venues[$venueName]) - 1;
        $venues[$venueName][$idx]['SessionContents'][] = $presentation;
    }
}

/**
 * @param  array<int, array<string, mixed>>  $program
 */
function attachSozluVenue(array &$program, string $tbodyHtml, string $isoDate): void
{
    $display = formatTurkishDate($isoDate);

    foreach ($program as &$dayEntry) {
        if ($dayEntry['IsoDate'] !== $isoDate) {
            continue;
        }

        $dayEntry['Venues'][] = [
            'Venue' => 'Salon C',
            'Sessions' => parseSingleVenueTable($tbodyHtml, ['iso' => $isoDate, 'display' => $display], 'Sözlü Bildiri Oturumları'),
        ];

        return;
    }
}

/**
 * @param  array<int, array{name: string, sessions: array<int, array<string, mixed>>}>  $venues
 * @return array<string, mixed>
 */
function buildDay(string $isoDate, string $displayDate, array $venues): array
{
    return [
        'Date' => $displayDate,
        'IsoDate' => $isoDate,
        'Venues' => array_map(fn (array $v) => [
            'Venue' => $v['name'],
            'Sessions' => $v['sessions'],
        ], $venues),
    ];
}

/**
 * @return array<int, array{cells: array<int, array{text: string, html: string, class: string, colspan: int, rowspan: int}>}>
 */
function parseTableRows(string $tbodyHtml): array
{
    $tbodyHtml = preg_replace('/<!--.*?-->/s', '', $tbodyHtml) ?? $tbodyHtml;
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML(
        '<?xml encoding="UTF-8"><table><tbody>'.$tbodyHtml.'</tbody></table>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    $rows = [];
    foreach ($dom->getElementsByTagName('tr') as $tr) {
        $cells = [];
        foreach ($tr->getElementsByTagName('td') as $td) {
            $cells[] = [
                'text' => normalizeWhitespace(stripTagsKeepBreaks(innerHtml($td))),
                'html' => innerHtml($td),
                'class' => $td->getAttribute('class'),
                'colspan' => (int) $td->getAttribute('colspan') ?: 1,
                'rowspan' => (int) $td->getAttribute('rowspan') ?: 1,
            ];
        }
        if ($cells !== []) {
            $rows[] = ['cells' => $cells];
        }
    }

    return $rows;
}

/**
 * @return array<string, mixed>
 */
function buildSessionFromHeader(array $day, string $startTime, string $endTime, string $contentHtml, string $sessionType, string $venue = ''): array
{
    $title = extractPrimaryTitle($contentHtml);
    $topic = extractTopic($contentHtml);
    $staffList = extractModeratorStaffList($contentHtml);
    $showTime = ! in_array($sessionType, ['Ara', 'Öğle Yemeği', 'AÇILIŞ'], true);

    return buildSessionShell($day, $sessionType, $title, $startTime, $endTime, $topic, $staffList, [], $showTime, $venue);
}

/**
 * @param  array<int, array<string, mixed>>  $staffList
 * @param  array<int, array<string, mixed>>  $contents
 * @return array<string, mixed>
 */
function buildSessionShell(
    array $day,
    string $sessionType,
    string $title,
    string $startTime,
    string $endTime,
    string $topic,
    array $staffList,
    array $contents,
    bool $showTime,
    string $venue = ''
): array {
    $key = "{$day['iso']}|{$venue}|{$sessionType}|{$title}|{$startTime}";

    return [
        'SessionTypeID' => SESSION_TYPE_IDS[$sessionType] ?? sessionUuid("type:{$sessionType}"),
        'SessionType' => $sessionType,
        'StartDate' => $day['display'],
        'StartTime' => $startTime,
        'StartDateJSON' => dateTimeJson($day['iso'], $startTime),
        'EndDate' => $day['display'],
        'EndTime' => $endTime,
        'EndDateJSON' => dateTimeJson($day['iso'], $endTime),
        'SessionID' => sessionUuid($key),
        'LogoStatus' => false,
        'ShowTime' => $showTime,
        'Session' => $title,
        'Topic' => $topic,
        'ExtraInfo' => '',
        'StaffList' => $staffList,
        'SessionContents' => $contents,
    ];
}

/**
 * @return array<string, mixed>
 */
function buildBreakSession(array $day, string $startTime, string $endTime, string $contentText, string $venue = ''): array
{
    $sessionType = isLunchText($contentText) ? 'Öğle Yemeği' : 'Ara';
    $title = isLunchText($contentText) ? 'ÖĞLE YEMEĞİ' : 'KAHVE ARASI';

    return buildSessionShell($day, $sessionType, $title, $startTime, $endTime, '', [], [], false, $venue);
}

/**
 * @return array<string, mixed>
 */
function buildOpeningSession(array $day, string $startTime, string $endTime, string $contentHtml): array
{
    $staffList = [];
    $names = extractOpeningSpeakers($contentHtml);
    if ($names !== []) {
        $staffList[] = [
            'StaffType' => 'Konuşmacılar',
            'Staff' => array_map(fn (string $name) => staffMember('', $name), $names),
        ];
    }

    return buildSessionShell($day, 'AÇILIŞ', 'AÇILIŞ KONUŞMALARI', $startTime, $endTime, '', $staffList, [], false);
}

/**
 * @return array<string, mixed>|null
 */
function buildPresentation(array $day, string $startTime, string $endTime, string $contentHtml): ?array
{
    $title = extractPresentationTitle($contentHtml);
    if ($title === '') {
        return null;
    }

    $speakers = extractSpeakerNames($contentHtml);

    return [
        'StartDate' => $day['display'],
        'StartTime' => $startTime,
        'StartDateJSON' => dateTimeJson($day['iso'], $startTime),
        'EndDate' => $day['display'],
        'EndTime' => $endTime,
        'EndDateJSON' => dateTimeJson($day['iso'], $endTime),
        'SessionContent' => $title,
        'ExtraInfo' => '',
        'StaffList' => $speakers !== []
            ? [[
                'StaffType' => count($speakers) === 1 ? 'Konuşmacı' : 'Konuşmacılar',
                'Staff' => array_map(fn (string $n) => staffMember('', $n), $speakers),
            ]]
            : [],
    ];
}

function extractPrimaryTitle(string $html): string
{
    $text = htmlToPlainWithBreaks($html);

    // Kurs N - Title
    if (preg_match('/\b(Kurs\s+\d+\s*-\s*.+?)(?:\s*(?:\||$)|\s*Oturum Başkan)/iu', $text, $m)) {
        return trim($m[1]);
    }

    // OTURUM N - Panel: ...
    if (preg_match('/\b(OTURUM\s+\d+)\s*(?:<br>|\s)*Panel:\s*(.+?)(?:\s*Oturum Başkan|$)/iu', $text, $m)) {
        return trim($m[1].' - Panel: '.trim($m[2]));
    }

    // OTURUM N without Panel (e.g. OTURUM 19<br> 2026'da ...)
    if (preg_match('/\b(OTURUM\s+\d+)\s*(?:<br>|\s)+(.+?)(?:\s*Oturum Başkan|$)/iu', $text, $m)) {
        $rest = trim(preg_replace('/\s+/', ' ', $m[2]) ?? $m[2]);

        return trim($m[1].' - '.$rest);
    }

    // UYDU SEMPOZYUMU
    if (preg_match('/UYDU\s+SEMPOZYUMU\s*(.+?)(?:\s*(?:Oturum Başkan|Moderatör|Konuşmacı|Prof\.?\s*Dr).*)?$/iu', $text, $m)) {
        $subtitle = trim(preg_replace('/\s+/', ' ', $m[1]) ?? $m[1]);
        $subtitle = trim(preg_replace('/\s*(Prof\.?\s*Dr.*)$/iu', '', $subtitle) ?? $subtitle);

        return $subtitle !== '' ? 'UYDU SEMPOZYUMU - '.$subtitle : 'UYDU SEMPOZYUMU';
    }

    // Sözlü Sunum
    if (stripos($text, 'Sözlü Sunum') !== false) {
        return 'Sözlü Sunum';
    }

    // AÇILIŞ / KAPANIŞ
    if (stripos($text, 'AÇILIŞ') !== false) {
        return 'AÇILIŞ KONUŞMALARI';
    }
    if (stripos($text, 'KAPANIŞ') !== false) {
        return 'KAPANIŞ';
    }

    // Breaks
    if (isBreakText($text)) {
        return isLunchText($text) ? 'ÖĞLE YEMEĞİ' : 'KAHVE ARASI';
    }

    // Fallback: first bold segment
    if (preg_match('/<b[^>]*>(.*?)<\/b>/is', $html, $m)) {
        return trim(stripTagsKeepBreaks($m[1]));
    }

    return trim($text);
}

function extractTopic(string $html): string
{
    $text = htmlToPlainWithBreaks($html);
    if (preg_match('/Panel:\s*(.+?)(?:\s*Oturum Başkan|$)/iu', $text, $m)) {
        return trim($m[1]);
    }

    return '';
}

/**
 * @return array<int, array<string, mixed>>
 */
function extractModeratorStaffList(string $html): array
{
    $text = htmlToPlainWithBreaks($html);
    $staffType = 'Oturum Başkanı';
    $names = [];

    if (preg_match('/Oturum Başkan(?:ı|ları)\s*:?\s*<b>(.*?)<\/b>/iu', $html, $m)) {
        $names = parseNameList(stripTags($m[1]));
        $staffType = str_contains($text, 'Oturum Başkanları') ? 'Oturum Başkanları' : 'Oturum Başkanı';
    } elseif (preg_match('/Oturum Başkan(?:ı|ları)\s*:?\s*(.+?)(?:\s*Panelistler|\s*Konuşmacı|$)/iu', $text, $m)) {
        $names = parseNameList($m[1]);
        $staffType = str_contains($text, 'Oturum Başkanları') ? 'Oturum Başkanları' : 'Oturum Başkanı';
    } elseif (preg_match('/Moderatör\s*:?\s*(.+?)(?:\s*Konuşmacı|$)/iu', $text, $m)) {
        $names = parseNameList($m[1]);
        $staffType = 'Moderatör';
    }

    if ($names === [] && preg_match('/Konuşmacı\s*:?\s*(.+?)$/iu', $text, $m)) {
        $names = parseNameList($m[1]);
        $staffType = 'Konuşmacı';
    }

    if ($names === []) {
        return [];
    }

    return [[
        'StaffType' => $staffType,
        'Staff' => array_map(fn (string $n) => staffMember('', $n), $names),
    ]];
}

function extractPresentationTitle(string $html): string
{
    $plain = htmlToPlainWithBreaks($html);
    // Remove SS-XX prefix for title but keep in content
    $plain = trim(preg_replace('/\s+/', ' ', $plain) ?? $plain);

    // Split on speaker line (last bold block)
    if (preg_match('/<b[^>]*>([^<]+)<\/b>\s*$/iu', $html, $speakerMatch)) {
        $speakerPos = strrpos($plain, trim(stripTags($speakerMatch[1])));
        if ($speakerPos !== false && $speakerPos > 0) {
            $plain = trim(substr($plain, 0, $speakerPos));
        }
    }

    // Remove leading SS-XX code from display
    $plain = trim(preg_replace('/^\s*SS-\d+\s*/iu', '', $plain) ?? $plain);

    return $plain;
}

/**
 * @return array<int, string>
 */
function extractSpeakerNames(string $html): array
{
    if (preg_match_all('/<b[^>]*>([^<]+)<\/b>/iu', $html, $matches)) {
        $bolds = array_map(fn ($b) => trim(stripTags($b)), $matches[1]);
        if (count($bolds) <= 1) {
            return $bolds;
        }

        // Last bold block is usually speaker(s)
        $last = array_pop($bolds);
        if ($last !== null && ! preg_match('/^SS-\d+$/i', $last)) {
            return parseNameList($last);
        }
    }

    return [];
}

/**
 * @return array<int, string>
 */
function extractOpeningSpeakers(string $html): array
{
    $text = htmlToPlainWithBreaks($html);
    $text = preg_replace('/^AÇILIŞ\s+KONUŞMALARI\s*/iu', '', $text) ?? $text;

    return parseNameList($text);
}

/**
 * @return array<int, string>
 */
function parseNameList(string $text): array
{
    $text = trim(stripTags($text));
    $text = preg_replace('/\s*(Panelistler|Konuşmacı|Moderatör).*/iu', '', $text) ?? $text;
    $parts = preg_split('/\s*[,–\-]\s*|\s+-\s+/u', $text) ?: [];

    return array_values(array_filter(array_map(
        fn (string $p) => trim(preg_replace('/\s+/', ' ', $p) ?? $p),
        $parts
    ), fn (string $p) => $p !== '' && ! preg_match('/^(Prof\.?\s*Dr\.?|Dr\.?)$/iu', $p)));
}

/**
 * @return array{0: string, 1: string}
 */
function parseTimeRange(string $raw): array
{
    $raw = trim(str_replace('.', ':', $raw));
    if ($raw === '') {
        return ['', ''];
    }

    if (preg_match('/(\d{1,2}:\d{2})\s*[-–]\s*(\d{1,2}:\d{2})/u', $raw, $m)) {
        return [normalizeTime($m[1]), normalizeTime($m[2])];
    }

    return [normalizeTime($raw), ''];
}

function normalizeTime(string $time): string
{
    if (preg_match('/^(\d{1,2}):(\d{2})$/', trim($time), $m)) {
        return sprintf('%02d:%s', (int) $m[1], $m[2]);
    }

    return trim($time);
}

function detectSessionType(string $text, string $html): string
{
    if (isUyduText($text)) {
        return 'Uydu Sempozyumu';
    }
    if (isOpeningText($text)) {
        return 'AÇILIŞ';
    }
    if (stripos($text, 'Sözlü Sunum') !== false) {
        return 'Sözlü Bildiri Oturumları';
    }
    if (preg_match('/\bKurs\s+\d+/iu', $text)) {
        return 'kurs';
    }

    return 'Oturum';
}

function isSessionHeaderText(string $text): bool
{
    return (bool) preg_match('/\b(OTURUM\s+\d+|Kurs\s+\d+|UYDU\s+SEMPOZYUMU|Sözlü\s+Sunum|AÇILIŞ|KAPANIŞ)/iu', $text);
}

function isBreakText(string $text): bool
{
    return (bool) preg_match('/KAHVE\s+ARASI/iu', $text) || isLunchText($text);
}

function isLunchText(string $text): bool
{
    return (bool) preg_match('/ÖĞLE\s+YEMEĞİ/iu', $text);
}

function isOpeningText(string $text): bool
{
    return (bool) preg_match('/AÇILIŞ\s+KONUŞMALARI/iu', $text);
}

function isKapanisText(string $text): bool
{
    return (bool) preg_match('/\bKAPANIŞ\b/iu', $text);
}

function isUyduText(string $text): bool
{
    return (bool) preg_match('/UYDU\s+SEMPOZYUMU/iu', $text);
}

/**
 * @return array{Title: string, FullName: string, Institution: string}
 */
function staffMember(string $title, string $fullName): array
{
    return [
        'Title' => $title,
        'FullName' => trim($fullName),
        'Institution' => '',
    ];
}

function dateTimeJson(string $isoDate, string $time): string
{
    if ($time === '') {
        return '"'.$isoDate.'T00:00:00"';
    }

    return '"'.$isoDate.'T'.$time.':00"';
}

function formatTurkishDate(string $isoDate): string
{
    $dt = DateTime::createFromFormat('Y-m-d', $isoDate);

    return $dt ? $dt->format('d.m.Y') : $isoDate;
}

function sessionUuid(string $input): string
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

function htmlToPlainWithBreaks(string $html): string
{
    $html = preg_replace('/<br\s*\/?>/i', ' ', $html) ?? $html;

    return normalizeWhitespace(stripTags($html));
}

function stripTags(string $html): string
{
    return html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function stripTagsKeepBreaks(string $html): string
{
    $html = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;

    return html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function innerHtml(DOMNode $node): string
{
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $node->ownerDocument?->saveHTML($child) ?? '';
    }

    return $html;
}

function normalizeWhitespace(string $text): string
{
    $text = preg_replace('/[\x{00A0}\s]+/u', ' ', $text) ?? $text;

    return trim($text);
}

main();
