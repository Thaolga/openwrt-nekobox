<?php
header('Content-Type: application/json');

$playlistFile = __DIR__ . '/lib/playlist.txt';

$defaultPlaylistUrl = 'https://raw.githubusercontent.com/Thaolga/Rules/main/music/songs.txt';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (file_exists($playlistFile)) {
        $url = file_get_contents($playlistFile);
        if (empty(trim($url))) {
            $url = $defaultPlaylistUrl;
            file_put_contents($playlistFile, $url);
        }
    } else {
        $url = $defaultPlaylistUrl;
        file_put_contents($playlistFile, $url);
    }
    echo json_encode(['url' => trim($url)]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $newUrl = isset($input['url']) ? trim($input['url']) : '';

    if (!empty($newUrl)) {
        if (file_put_contents($playlistFile, $newUrl) !== false) {
            echo json_encode(['success' => true, 'message' => 'Playlist URL updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update playlist URL']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid URL provided']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unsupported request method']);
}
?>
