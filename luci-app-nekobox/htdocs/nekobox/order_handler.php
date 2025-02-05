<?php
$backgroundHistoryFile = $_SERVER['DOCUMENT_ROOT'] . '/nekobox/background_history.txt';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order'])) {
    $order = $_POST['order'];

    if (file_exists($backgroundHistoryFile)) {
        $backgroundFiles = array_filter(array_map('trim', file($backgroundHistoryFile)));

        $newOrder = [];
        foreach ($order as $file) {
            $newOrder[] = basename($file); 
        }

        file_put_contents($backgroundHistoryFile, implode(PHP_EOL, $newOrder));
        echo 'Order saved';
    } else {
        echo 'Background history file does not exist';
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (file_exists($backgroundHistoryFile)) {
        $backgroundFiles = array_filter(array_map('trim', file($backgroundHistoryFile)));
        echo json_encode(array_values($backgroundFiles));
    } else {
        echo json_encode([]);
    }
} else {
    echo 'Invalid request';
}
?>