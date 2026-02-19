<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config/sources.php';

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function error_response(string $message, int $status = 400, array $context = []): void
{
    json_response([
        'success' => false,
        'message' => $message,
        'context' => $context
    ], $status);
}

function validate_origin(array $config): void
{
    $allowedSingle = $config['allowed_origin'] ?? '';
    $allowedList = $config['allowed_origins'] ?? ($allowedSingle ? [$allowedSingle] : []);
    $frontendSecret = $config['frontend_secret'] ?? '';

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $providedSecret = $_SERVER['HTTP_X_BD_SECRET'] ?? '';

    $isSameHost = $origin && parse_url($origin, PHP_URL_HOST) === $host;

    // If a valid frontend secret is provided, allow (this ensures only our frontend can call).
    if ($frontendSecret && $providedSecret && hash_equals($frontendSecret, $providedSecret)) {
        $allowedHeader = $origin ?: ('https://' . $host);
        header('Access-Control-Allow-Origin: ' . $allowedHeader);
        header('Vary: Origin');
    } elseif (!$origin || in_array($origin, $allowedList, true) || $isSameHost) {
        // Permite chamadas internas (sem Origin), origens whitelist ou mesma origem/host.
        $allowedHeader = $origin ?: ($allowedSingle ?: ('https://' . $host));
        header('Access-Control-Allow-Origin: ' . $allowedHeader);
        header('Vary: Origin');
    } else {
        header('Access-Control-Allow-Origin: null');
        error_response('Origem não autorizada.', 403, ['origin' => $origin]);
    }

    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With, X-BD-SECRET');
    header('Access-Control-Max-Age: 86400');

    if ($method === 'OPTIONS') {
        exit;
    }
}

function get_date_param(): DateTimeImmutable
{
    $dateParam = $_GET['date'] ?? $_GET['data'] ?? null;
    if ($dateParam) {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', substr($dateParam, 0, 10));
        if ($date instanceof DateTimeImmutable) {
            return $date;
        }
    }

    return new DateTimeImmutable('today');
}

function get_reading_plan(DateTimeImmutable $date, array $config): array
{
    $schedule = build_reading_plan_schedule();
    $dayOfYear = (int) $date->format('z') + 1;
    $dayIndex = (($dayOfYear - 1) % 365) + 1;
    $segments = $schedule[$dayIndex] ?? [];

    return [
        'success' => true,
        'day_of_year' => $dayOfYear,
        'day_index' => $dayIndex,
        'segments' => $segments,
        'note' => 'Plano integral em 365 dias (2 a 5 capítulos/dia, em ordem canônica).'
    ];
}

function build_reading_plan_schedule(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $flat = [];
    foreach (get_catholic_books() as $book) {
        for ($c = 1; $c <= $book['chapters']; $c++) {
            $flat[] = [$book['name'], $c];
        }
    }

    $total = count($flat); // ~1189 capítulos
    $ptr = 0;
    $schedule = [];

    for ($day = 1; $day <= 365; $day++) {
        if ($ptr >= $total) {
            $schedule[$day] = [];
            continue;
        }

        $remainingChapters = $total - $ptr;
        $remainingDays = 365 - $day + 1;
        $chunk = (int) ceil($remainingChapters / $remainingDays);
        $chunk = max(2, min(5, $chunk));

        $segments = [];
        $count = 0;
        while ($count < $chunk && $ptr < $total) {
            [$book, $chapter] = $flat[$ptr];
            $start = $chapter;
            $end = $chapter;
            $ptr++;
            $count++;

            // Tenta agrupar capítulos consecutivos do mesmo livro dentro do chunk.
            while ($count < $chunk && $ptr < $total) {
                [$nextBook, $nextChapter] = $flat[$ptr];
                if ($nextBook === $book && $nextChapter === $end + 1) {
                    $end = $nextChapter;
                    $ptr++;
                    $count++;
                } else {
                    break;
                }
            }

            $segments[] = [
                'book' => $book,
                'from' => $start,
                'to' => $end
            ];
        }

        $schedule[$day] = $segments;
    }

    return $cache = $schedule;
}

function get_catholic_books(): array
{
    return [
        ['name' => 'Gênesis', 'chapters' => 50],
        ['name' => 'Êxodo', 'chapters' => 40],
        ['name' => 'Levítico', 'chapters' => 27],
        ['name' => 'Números', 'chapters' => 36],
        ['name' => 'Deuteronômio', 'chapters' => 34],
        ['name' => 'Josué', 'chapters' => 24],
        ['name' => 'Juízes', 'chapters' => 21],
        ['name' => 'Rute', 'chapters' => 4],
        ['name' => '1 Samuel', 'chapters' => 31],
        ['name' => '2 Samuel', 'chapters' => 24],
        ['name' => '1 Reis', 'chapters' => 22],
        ['name' => '2 Reis', 'chapters' => 25],
        ['name' => '1 Crônicas', 'chapters' => 29],
        ['name' => '2 Crônicas', 'chapters' => 36],
        ['name' => 'Esdras', 'chapters' => 10],
        ['name' => 'Neemias', 'chapters' => 13],
        ['name' => 'Tobias', 'chapters' => 14],
        ['name' => 'Judite', 'chapters' => 16],
        ['name' => 'Ester', 'chapters' => 10],
        ['name' => '1 Macabeus', 'chapters' => 16],
        ['name' => '2 Macabeus', 'chapters' => 15],
        ['name' => 'Jó', 'chapters' => 42],
        ['name' => 'Salmos', 'chapters' => 150],
        ['name' => 'Provérbios', 'chapters' => 31],
        ['name' => 'Eclesiastes', 'chapters' => 12],
        ['name' => 'Cântico dos Cânticos', 'chapters' => 8],
        ['name' => 'Sabedoria', 'chapters' => 19],
        ['name' => 'Eclesiástico', 'chapters' => 51],
        ['name' => 'Isaías', 'chapters' => 66],
        ['name' => 'Jeremias', 'chapters' => 52],
        ['name' => 'Lamentações', 'chapters' => 5],
        ['name' => 'Baruc', 'chapters' => 6],
        ['name' => 'Ezequiel', 'chapters' => 48],
        ['name' => 'Daniel', 'chapters' => 14],
        ['name' => 'Oseias', 'chapters' => 14],
        ['name' => 'Joel', 'chapters' => 4],
        ['name' => 'Amós', 'chapters' => 9],
        ['name' => 'Abdias', 'chapters' => 1],
        ['name' => 'Jonas', 'chapters' => 4],
        ['name' => 'Miqueias', 'chapters' => 7],
        ['name' => 'Naum', 'chapters' => 3],
        ['name' => 'Habacuque', 'chapters' => 3],
        ['name' => 'Sofonias', 'chapters' => 3],
        ['name' => 'Ageu', 'chapters' => 2],
        ['name' => 'Zacarias', 'chapters' => 14],
        ['name' => 'Malaquias', 'chapters' => 4],
        ['name' => 'Mateus', 'chapters' => 28],
        ['name' => 'Marcos', 'chapters' => 16],
        ['name' => 'Lucas', 'chapters' => 24],
        ['name' => 'João', 'chapters' => 21],
        ['name' => 'Atos dos Apóstolos', 'chapters' => 28],
        ['name' => 'Romanos', 'chapters' => 16],
        ['name' => '1 Coríntios', 'chapters' => 16],
        ['name' => '2 Coríntios', 'chapters' => 13],
        ['name' => 'Gálatas', 'chapters' => 6],
        ['name' => 'Efésios', 'chapters' => 6],
        ['name' => 'Filipenses', 'chapters' => 4],
        ['name' => 'Colossenses', 'chapters' => 4],
        ['name' => '1 Tessalonicenses', 'chapters' => 5],
        ['name' => '2 Tessalonicenses', 'chapters' => 3],
        ['name' => '1 Timóteo', 'chapters' => 6],
        ['name' => '2 Timóteo', 'chapters' => 4],
        ['name' => 'Tito', 'chapters' => 3],
        ['name' => 'Filemon', 'chapters' => 1],
        ['name' => 'Hebreus', 'chapters' => 13],
        ['name' => 'Tiago', 'chapters' => 5],
        ['name' => '1 Pedro', 'chapters' => 5],
        ['name' => '2 Pedro', 'chapters' => 3],
        ['name' => '1 João', 'chapters' => 5],
        ['name' => '2 João', 'chapters' => 1],
        ['name' => '3 João', 'chapters' => 1],
        ['name' => 'Judas', 'chapters' => 1],
        ['name' => 'Apocalipse', 'chapters' => 22]
    ];
}
