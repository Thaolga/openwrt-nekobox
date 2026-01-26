<?php
$history_file = __DIR__ . '/lib/background_history.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['order']) && is_array($data['order'])) {
        $new_order = array_unique($data['order']);
        $dir = dirname($history_file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($history_file, implode("\n", $new_order));
        echo "OK";
    } else {
        http_response_code(400);
        echo "Invalid data";
    }
}