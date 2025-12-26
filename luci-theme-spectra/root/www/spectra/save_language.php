<?php
header('Content-Type: application/json');

$language_file = __DIR__ . '/lib/language.txt';

if (!file_exists($language_file)) {
    file_put_contents($language_file, '');
}

if ($_POST['action'] == 'save_language') {
    $language = $_POST['language'];
    if (!empty($language)) {
        file_put_contents($language_file, $language);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} elseif ($_POST['action'] == 'get_language') {
    if (file_exists($language_file)) {
        $current_language = trim(file_get_contents($language_file));
        echo json_encode(['success' => true, 'language' => $current_language]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>