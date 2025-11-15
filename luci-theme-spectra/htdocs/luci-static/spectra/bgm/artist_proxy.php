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
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$result, $httpCode];
}

function optimizeImageUrl($url, $source) {
    if (!$url) return $url;

    switch ($source) {
        case 'netease':
            if (strpos($url, '?param=') !== false) {
                $url = preg_replace('/\\?param=\\d+y\\d+/', '?param=1000y1000', $url);
            } else {
                $url .= '?param=1000y1000';
            }
            break;

        case 'itunes':
            $url = str_replace(['100x100bb', '60x60bb', '30x30bb'], '1200x1200bb', $url);
            break;

        case 'deezer':
            $url = str_replace(['cover_big', 'cover_medium', 'cover_small'], 'cover_xl', $url);
            break;
    }

    return $url;
}

function fetchNeteaseImage($artist, $title) {
    if (!empty($title)) {
        $searchUrl = "https://music.163.com/api/search/get?s=" . urlencode($artist . ' ' . $title) . "&type=1&limit=1";
        list($result, $httpCode) = fetchWithCurl($searchUrl);

        if ($httpCode === 200 && $result) {
            $data = json_decode($result, true);
            if (isset($data['result']['songs'][0]['album']['picUrl'])) {
                $imageUrl = $data['result']['songs'][0]['album']['picUrl'];
                return optimizeImageUrl($imageUrl, 'netease');
            }
        }
    }

    $searchUrl = "https://music.163.com/api/search/get?s=" . urlencode($artist) . "&type=100&limit=1";
    list($result, $httpCode) = fetchWithCurl($searchUrl);

    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        if (isset($data['result']['artists'][0]['img1v1Url'])) {
            $imageUrl = $data['result']['artists'][0]['img1v1Url'];
            return optimizeImageUrl($imageUrl, 'netease');
        }
    }

    return null;
}

function fetchItunesImage($artist, $title) {
    $searchUrl = "https://itunes.apple.com/search?term=" . urlencode($artist . ' ' . $title) . "&entity=song&limit=3";
    list($result, $httpCode) = fetchWithCurl($searchUrl);

    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        if (isset($data['results'][0]['artworkUrl100'])) {
            return optimizeImageUrl($data['results'][0]['artworkUrl100'], 'itunes');
        }
    }
    return null;
}

function fetchDeezerImage($artist, $title) {
    $searchUrl = "https://api.deezer.com/search?q=" . urlencode("artist:\"$artist\" track:\"$title\"") . "&limit=3";
    list($result, $httpCode) = fetchWithCurl($searchUrl);

    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        if (isset($data['data'][0]['album']['cover_xl'])) {
            return optimizeImageUrl($data['data'][0]['album']['cover_xl'], 'deezer');
        }
    }
    return null;
}

function fetchLastfmImage($artist, $title) {
    $apiKey = 'a496648b2d382af443aaaadf7f7f6895';
    $method = (!empty($title) ? 'track.getInfo' : 'artist.getInfo');
    $url = "http://ws.audioscrobbler.com/2.0/?method={$method}&artist=" . urlencode($artist);
    if (!empty($title)) $url .= "&track=" . urlencode($title);
    $url .= "&api_key={$apiKey}&format=json&autocorrect=1";

    list($result, $httpCode) = fetchWithCurl($url);

    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        $images = [];

        if (!empty($title) && isset($data['track']['album']['image'])) {
            $images = $data['track']['album']['image'];
        } elseif (isset($data['artist']['image'])) {
            $images = $data['artist']['image'];
        }

        if (!empty($images)) {
            $largest = end($images);
            if (!empty($largest['#text'])) return $largest['#text'];
        }
    }
    return null;
}

$response = ['success' => false, 'message' => 'No high-quality image found'];

if (!empty($artist)) {
    $imageUrl = null;
    $source = '';

    $sources = [
        'netease' => fn() => fetchNeteaseImage($artist, $title),
        'lastfm'  => fn() => fetchLastfmImage($artist, $title),
        'itunes'  => fn() => fetchItunesImage($artist, $title),
        'deezer'  => fn() => fetchDeezerImage($artist, $title),
    ];

    foreach ($sources as $name => $fetcher) {
        $imageUrl = $fetcher();
        if ($imageUrl) {
            $source = $name;
            break;
        }
    }

    if ($imageUrl) {
        $response = [
            'success' => true,
            'imageUrl' => $imageUrl,
            'source' => $source,
            'artist' => $artist,
            'title' => $title
        ];
    }
}

echo json_encode($response);
?>
