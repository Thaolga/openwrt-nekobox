<?php
$history_file = 'background_history.txt';

if (isset($_POST['file'])) {
    $newBackground = trim($_POST['file']);

    if (!file_exists($history_file)) {
        file_put_contents($history_file, "");
        chmod($history_file, 0666);
    }

    $background_history = file_exists($history_file) ? file($history_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

    $background_history = array_diff($background_history, [$newBackground]);
    array_unshift($background_history, $newBackground);
    $background_history = array_slice($background_history, 0, 50);

    file_put_contents($history_file, implode("\n", $background_history) . "\n");

    exit;
}
?>
