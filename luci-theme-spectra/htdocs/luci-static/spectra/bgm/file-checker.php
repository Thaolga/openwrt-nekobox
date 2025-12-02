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

$filePath = __DIR__ . '/' . $file;
$exists = file_exists($filePath);

echo json_encode(['exists' => $exists]);
?>