<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$cacheDir = dirname(__FILE__) . '/cache/';
$cacheFile = $cacheDir . 'search_cache.json';

$result = [
    'success' => false,
    'message' => '',
    'timestamp' => time()
];

if (file_exists($cacheFile)) {
    if (unlink($cacheFile)) {
        $result['success'] = true;
        $result['message'] = 'Cache cleared successfully';
        $result['deleted'] = true;
    } else {
        $result['message'] = 'Failed to delete cache file';
        $result['error'] = error_get_last()['message'] ?? 'Unknown error';
    }
} else {
    $result['success'] = true;
    $result['message'] = 'No cache file to clear';
    $result['deleted'] = false;
}

echo json_encode($result);
exit;
?>