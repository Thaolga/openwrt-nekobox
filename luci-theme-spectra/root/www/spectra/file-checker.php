<?php
header('Content-Type: application/json');

$file = $_GET['file'] ?? '';
if (empty($file)) {
    echo json_encode(['exists' => false]);
    exit;
}

if (strpos($file, '..') !== false || strpos($file, '/') !== false) {
    echo json_encode(['exists' => false]);
    exit;
}

$baseDir = __DIR__ . '/stream';
$filePath = $baseDir . '/' . $file;

if (!file_exists($baseDir)) {
    mkdir($baseDir, 0755, true);
}

$exists = file_exists($filePath);

echo json_encode(['exists' => $exists]);
?>