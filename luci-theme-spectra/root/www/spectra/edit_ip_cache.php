<?php
header('Content-Type: application/json');

$cacheFile = __DIR__ . '/lib/ip_cache.json';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = file_get_contents('php://input');
if ($input === false) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$jsonData = json_decode($input, true);
if ($jsonData === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

if (empty($jsonData)) {
    $jsonData = [];
}

$result = file_put_contents($cacheFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result !== false) {
    echo json_encode(['success' => true, 'message' => 'Saved successfully', 'bytes' => $result]);
} else {
    $error = error_get_last();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save',
        'error' => $error ? $error['message'] : 'Unknown error',
        'file' => $cacheFile,
        'writable' => is_writable(dirname($cacheFile))
    ]);
}
?>

