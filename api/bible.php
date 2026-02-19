<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$config = $GLOBALS['config'] ?? require __DIR__ . '/../config/sources.php';
validate_origin($config);

// Params
$book = $_GET['book'] ?? '';
$chapter = $_GET['chapter'] ?? '';

if (!$book || !$chapter) {
    error_response('Parâmetros inválidos. Use book e chapter.', 400);
}

// sanitize
function slugify_for_fs(string $s): string
{
    $t = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
    $t = preg_replace('/[^a-zA-Z0-9\s-]/', '', $t);
    $t = strtolower(trim($t));
    $t = preg_replace('/[\s_]+/', '-', $t);
    return $t ?: 'book';
}

$safeBook = slugify_for_fs($book);
$safeChapter = preg_replace('/[^0-9]/', '', (string)$chapter);

$base = __DIR__ . '/../storage/cache/chapters';
$dir = $base . '/' . $safeBook;
if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
}

$path = $dir . '/' . $safeChapter . '.json';
$tmp = $path . '.tmp';
$lockFile = $path . '.lock';
$ttl = 60 * 60 * 24 * 7; // 7 days

// If cache valid, return it
if (is_file($path) && (time() - filemtime($path) < $ttl)) {
    $content = file_get_contents($path);
    if ($content !== false) {
        json_response(['success' => true, 'source' => 'cache', 'text' => json_decode($content, true)['text'] ?? null]);
    }
}

// Use lock to prevent stampede
$lock = fopen($lockFile, 'c');
if ($lock) {
    if (flock($lock, LOCK_EX)) {
        // Re-check cache after acquiring lock
        if (is_file($path) && (time() - filemtime($path) < $ttl)) {
            $content = file_get_contents($path);
            flock($lock, LOCK_UN);
            fclose($lock);
            json_response(['success' => true, 'source' => 'cache', 'text' => json_decode($content, true)['text'] ?? null]);
        }

        // Fetch from external API
        $url = 'https://bible-api.com/' . rawurlencode($book) . '+' . rawurlencode($chapter) . '?translation=almeida';

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 8,
                'header' => "User-Agent: BibliaDiaria/1.0\r\n"
            ]
        ]);

        $resp = @file_get_contents($url, false, $ctx);
        if ($resp !== false) {
            // Save atomically
            $data = ['text' => json_decode($resp, true)['text'] ?? null, 'fetched_at' => time(), 'source_url' => $url];
            $w = @file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            if ($w !== false) {
                @rename($tmp, $path);
            }
            flock($lock, LOCK_UN);
            fclose($lock);
            json_response(['success' => true, 'source' => 'remote', 'text' => $data['text']]);
        }

        // remote failed: if stale exists return it
        if (is_file($path)) {
            $content = file_get_contents($path);
            flock($lock, LOCK_UN);
            fclose($lock);
            json_response(['success' => true, 'source' => 'stale', 'text' => json_decode($content, true)['text'] ?? null]);
        }

        flock($lock, LOCK_UN);
        fclose($lock);
    } else {
        fclose($lock);
    }
}

error_response('Não foi possível obter o capítulo.', 503);
