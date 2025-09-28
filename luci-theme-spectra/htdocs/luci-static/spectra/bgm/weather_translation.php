<?php
header('Content-Type: application/json');

$cacheFile = __DIR__ . '/weather_translation_cache.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if (!$data) $data = ["cities" => []];
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode(["cities" => []], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['city']) || !isset($data['translations'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $cacheData = ["cities" => []];
    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true) ?: ["cities" => []];
    }

    $city = $data['city'];
    $translations = $data['translations'];

    $cacheData["cities"][$city] = [
        "translations" => $translations,
        "updated" => date('c')
    ];

    file_put_contents($cacheFile, json_encode($cacheData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
?>
