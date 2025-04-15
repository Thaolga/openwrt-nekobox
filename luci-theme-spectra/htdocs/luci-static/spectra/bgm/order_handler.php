<?php
$history_file = 'background_history.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['order']) && is_array($data['order'])) {
        $new_order = array_unique($data['order']);
        file_put_contents($history_file, implode("\n", $new_order));
        echo "OK";
    } else {
        http_response_code(400);
        echo "Invalid data";
    }
}
