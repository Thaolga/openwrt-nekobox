<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$cacheFile = __DIR__ . '/lib/weather_translation_cache.json';
// $debugFile = __DIR__ . '/weather_translation_debug.log';

function dbg($msg) {
    // global $debugFile;
    // @file_put_contents($debugFile, date('c') . " " . $msg . PHP_EOL, FILE_APPEND);
}

$raw = file_get_contents('php://input');
// dbg("RECV: " . $raw);

$cacheData = ["cities" => []];
if (file_exists($cacheFile)) {
    $content = @file_get_contents($cacheFile);
    $decoded = @json_decode($content, true);
    if (is_array($decoded)) {
        $cacheData = $decoded;
    } else {
        // dbg("WARN: invalid JSON in cache file, resetting");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'clear') {
        $cacheData = ["cities" => []];
        $json = json_encode($cacheData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $tmp = $cacheFile . '.tmp';
        @file_put_contents($tmp, $json);
        @rename($tmp, $cacheFile);
        @chmod($cacheFile, 0666);
        // dbg("ACTION: cleared all cache data");
        echo json_encode(['status' => 'cache cleared'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    echo json_encode($cacheData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = @json_decode($raw, true);
    if ($data === null) {
        $err = json_last_error_msg();
        // dbg("ERR: invalid JSON payload: " . $err);
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON payload: '.$err]);
        exit;
    }

    if (isset($data['cities']) && is_array($data['cities'])) {
        $cacheData['cities'] = $data['cities'];
        // dbg("ACTION: replaced entire cities");
    }

    elseif (isset($data['city']) && isset($data['translations']) && is_array($data['translations'])) {
        $city = trim($data['city']);
        $translations = $data['translations'];

        if (!isset($cacheData['cities'][$city])) {
            $cacheData['cities'][$city] = ['translations' => [], 'updated' => null];
        }

        foreach ($translations as $lang => $langVal) {
            $existingLang = $cacheData['cities'][$city]['translations'][$lang] ?? [];
            if (is_string($langVal)) {
                $newLang = array_replace_recursive($existingLang, ['city' => $langVal]);
            } elseif (is_array($langVal)) {
                $newLang = array_replace_recursive($existingLang, $langVal);
            } else {
                continue;
            }
            $cacheData['cities'][$city]['translations'][$lang] = $newLang;
        }

        if (isset($data['temp'])) {
            $cacheData['cities'][$city]['temp'] = $data['temp'];
        }
        if (isset($data['icon'])) {
            foreach ($translations as $lang => $_) {
                $cacheData['cities'][$city]['translations'][$lang]['lastIcon'] = $data['icon'];
            }
        }

        $cacheData['cities'][$city]['updated'] = date('c');
        // dbg("ACTION: merged translations for city={$city}");
    }

    elseif (isset($data['key']) && isset($data['lang']) && isset($data['text'])) {
        $key = trim($data['key']);
        $lang = trim($data['lang']);
        $text = $data['text'];
        if (!isset($cacheData['cities'][$key])) {
            $cacheData['cities'][$key] = ['translations' => [], 'updated' => null];
        }
        $cacheData['cities'][$key]['translations'][$lang] = array_replace_recursive(
            $cacheData['cities'][$key]['translations'][$lang] ?? [],
            ['city' => $text]
        );
        $cacheData['cities'][$key]['updated'] = date('c');
        // dbg("ACTION: set single translation for key={$key} lang={$lang}");
    } else {
        // dbg("ERR: unrecognized POST shape");
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input shape']);
        exit;
    }

    $json = json_encode($cacheData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $tmp = $cacheFile . '.tmp';
    @file_put_contents($tmp, $json);
    @rename($tmp, $cacheFile);
    @chmod($cacheFile, 0666);

    // dbg("OK: wrote cache {$cacheFile}");
    echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
?>