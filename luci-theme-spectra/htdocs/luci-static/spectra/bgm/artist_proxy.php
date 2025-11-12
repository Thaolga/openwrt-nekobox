<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true);
$artist = $input['artist'] ?? '';
$title = $input['title'] ?? '';

function fetchWithCurl($url, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [$result, $httpCode];
}

$response = ['success' => false, 'message' => 'No artist image found'];

if (!empty($artist)) {
    $searchUrl = "https://music.163.com/api/search/get?s=" . urlencode($artist) . "&type=100&limit=1";
    list($result, $httpCode) = fetchWithCurl($searchUrl);
    
    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        if (isset($data['result']['artists'][0]['img1v1Url'])) {
            $imageUrl = $data['result']['artists'][0]['img1v1Url'];
            $response = [
                'success' => true,
                'imageUrl' => $imageUrl,
                'source' => 'netease'
            ];
        }
    }
}

echo json_encode($response);
?>