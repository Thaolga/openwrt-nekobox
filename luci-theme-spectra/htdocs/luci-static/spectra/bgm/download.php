<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$token = $_GET['token'] ?? '';
if (empty($token)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid sharing link']));
}

$sharesDir = __DIR__ . '/shares/';
$shareFile = $sharesDir . $token . '.json';
if (!file_exists($shareFile)) {
    http_response_code(404);
    die(json_encode(['success' => false, 'message' => 'Failed to parse sharing record']));
}

$shareData = json_decode(file_get_contents($shareFile), true);
if (!$shareData) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Failed to parse sharing record']));
}

if (time() > ($shareData['expire'] ?? 0)) {
    unlink($shareFile);
    http_response_code(410);
    die(json_encode(['success' => false, 'message' => 'The link has expired']));
}

$maxDownloads  = intval($shareData['max_downloads'] ?? 0);
$downloadCount = intval($shareData['download_count'] ?? 0);
if ($maxDownloads > 0 && $downloadCount >= $maxDownloads) {
    unlink($shareFile);
    http_response_code(410);
    die(json_encode(['success' => false, 'message' => 'The maximum number of downloads has been reached']));
}

$shareData['download_count'] = $downloadCount + 1;
file_put_contents($shareFile, json_encode($shareData, JSON_PRETTY_PRINT));

$filePath = __DIR__ . '/' . $shareData['filename'];

if (!file_exists($filePath)) {
    http_response_code(404);
    die(json_encode(['success' => false, 'message' => 'The file does not exist']));
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
?>
