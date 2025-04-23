<?php
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$sharesDir = __DIR__ . '/shares/';
$now = time();
$deleted = 0;

if (!is_dir($sharesDir)) {
    echo json_encode(['success' => false, 'message' => 'Shares directory does not exist']);
    exit;
}

foreach (glob($sharesDir . '*.json') as $file) {
    $remove = false;

    if ($action === 'clean') {
        $data = json_decode(file_get_contents($file), true);
        if (!$data) continue;

        $expired = $now > ($data['expire'] ?? 0);
        $exceeded = ($data['max_downloads'] ?? 0) > 0 && ($data['download_count'] ?? 0) >= $data['max_downloads'];
        $remove = $expired || $exceeded;
    } elseif ($action === 'delete_all') {
        $remove = true;
    }

    if ($remove && unlink($file)) {
        $deleted++;
    }
}

echo json_encode(['success' => true, 'deleted' => $deleted]);
?>
