<?php
header('Content-Type: application/json');

$data   = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'create') {
    $filename     = $data['filename']      ?? '';
    $expire       = intval($data['expire'] ?? 86400);
    $maxDownloads = intval($data['max_downloads'] ?? 0);

    try {
        $token = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Token generation failed']);
        exit;
    }

    $expireTime = time() + $expire;

    $sharesDir = __DIR__ . '/shares/';
    if (!is_dir($sharesDir)) {
        mkdir($sharesDir, 0755, true);
    }

    $shareData = [
        'filename'       => $filename,
        'token'          => $token,
        'expire'         => $expireTime,
        'max_downloads'  => $maxDownloads,
        'download_count' => 0,
        'created_at'     => time(),
    ];

    $recordFile = $sharesDir . $token . '.json';
    if (false === file_put_contents($recordFile, json_encode($shareData, JSON_PRETTY_PRINT))) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save the sharing record']);
        exit;
    }

    echo json_encode(['success' => true, 'token' => $token]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
