<?php
$apiKeys = [];
$configFile = dirname(__FILE__) . '/api_keys.config.php';

if (file_exists($configFile)) {
    $apiKeys = include $configFile;
} else {
    $sampleConfig = "<?php\nreturn [\n    'spotify' => [\n        'client_id' => 'YOUR_SPOTIFY_CLIENT_ID',\n        'client_secret' => 'YOUR_SPOTIFY_CLIENT_SECRET'\n    ],\n    'youtube' => [\n        'api_key' => 'YOUR_YOUTUBE_API_KEY'\n    ],\n    'soundcloud' => [\n        'client_id' => 'YOUR_SOUNDCLOUD_CLIENT_ID'\n    ]\n];";
    file_put_contents($configFile, $sampleConfig);
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true);
$source = $input['source'] ?? '';
$query = $input['query'] ?? '';
$type = $input['type'] ?? 'song';
$limit = $input['limit'] ?? 50;
$offset = $input['offset'] ?? 0;
$pageToken = $input['pageToken'] ?? null;

function fetchWithCurl($url, $headers = []) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => '',
    ]);

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("CURL Error for $url: $error");
    }

    return [$result, $httpCode];
}

function searchSpotify($query, $type, $limit, $offset, $apiKeys) {
    global $apiKeys;

    if (empty($apiKeys['spotify']['client_id']) || empty($apiKeys['spotify']['client_secret'])) {
        return ['success' => false, 'message' => 'Spotify API keys not configured'];
    }

    $clientId = $apiKeys['spotify']['client_id'];
    $clientSecret = $apiKeys['spotify']['client_secret'];

    $tokenUrl = 'https://accounts.spotify.com/api/token';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $tokenUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
            'Content-Type: application/x-www-form-urlencoded'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);
    $tokenResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) {
        error_log('Spotify Token CURL Error: ' . curl_error($ch));
    }
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log('Spotify Token API Error: ' . $tokenResponse);
        return ['success' => false, 'message' => 'Failed to get access token. HTTP Code: ' . $httpCode];
    }

    $tokenData = json_decode($tokenResponse, true);
    if (empty($tokenData['access_token'])) {
        return ['success' => false, 'message' => 'Invalid token response'];
    }
    $accessToken = $tokenData['access_token'];

    $searchTypeMap = [
        'song' => 'track',
        'artist' => 'artist',
        'album' => 'album',
        'playlist' => 'playlist'
    ];
    $apiSearchType = $searchTypeMap[$type] ?? 'track';

    $searchUrl = 'https://api.spotify.com/v1/search?' . http_build_query([
        'q' => $query,
        'type' => $apiSearchType,
        'limit' => $limit,
        'offset' => $offset
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $searchUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: ' . 'application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);
    $searchResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) {
        error_log('Spotify Search CURL Error: ' . curl_error($ch));
    }
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log('Spotify Search API Error (' . $httpCode . '): ' . $searchResponse);
        return ['success' => false, 'message' => 'Spotify search failed. HTTP Code: ' . $httpCode];
    }

    $searchData = json_decode($searchResponse, true);

    $items = [];
    $keyForItems = $apiSearchType . 's';
    if (isset($searchData[$keyForItems]['items'])) {
        $items = $searchData[$keyForItems]['items'];
    }

    return [
        'success' => true,
        'results' => $items,
        'total' => $searchData[$keyForItems]['total'] ?? 0
    ];
}

function searchYouTube($query, $type, $apiKeys, $pageToken = null) {
    global $apiKeys;
    
    if (empty($apiKeys['youtube']['api_key'])) {
        return ['success' => false, 'message' => 'YouTube API key not configured'];
    }
    
    $apiKey = $apiKeys['youtube']['api_key'];
    
    $searchUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . 
                 urlencode($query) . "&maxResults=50&type=video&key=$apiKey";
    
    if ($pageToken) {
        $searchUrl .= "&pageToken=" . urlencode($pageToken);
    }
    
    list($result, $httpCode) = fetchWithCurl($searchUrl);
    
    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        $items = [];
        
        if (isset($data['items']) && count($data['items']) > 0) {
            $videoIds = array_map(function($item) {
                return $item['id']['videoId'] ?? '';
            }, $data['items']);
            
            $videoIds = array_filter($videoIds);
            
            $videoDetails = [];
            if (!empty($videoIds)) {
                $videoIds = array_slice($videoIds, 0, 50);
                $idsString = implode(',', $videoIds);
                
                $videosUrl = "https://www.googleapis.com/youtube/v3/videos?part=contentDetails,snippet&id=" . 
                            urlencode($idsString) . "&key=$apiKey";
                
                list($videosResult, $videosCode) = fetchWithCurl($videosUrl);
                
                if ($videosCode === 200 && $videosResult) {
                    $videosData = json_decode($videosResult, true);
                    
                    if (isset($videosData['items'])) {
                        foreach ($videosData['items'] as $videoItem) {
                            $videoId = $videoItem['id'] ?? '';
                            
                            $thumbnails = $videoItem['snippet']['thumbnails'] ?? 
                                         ($item['snippet']['thumbnails'] ?? []);
                            
                            $videoDetails[$videoId] = [
                                'duration' => $videoItem['contentDetails']['duration'] ?? null,
                                'thumbnails' => $thumbnails
                            ];
                        }
                    }
                }
            }
            
            foreach ($data['items'] as $item) {
                $videoId = $item['id']['videoId'] ?? '';
                $details = $videoDetails[$videoId] ?? [];
                
                $items[] = [
                    'id' => $videoId,
                    'title' => $item['snippet']['title'] ?? '',
                    'description' => $item['snippet']['description'] ?? '',
                    'channelTitle' => $item['snippet']['channelTitle'] ?? '',
                    'thumbnails' => $details['thumbnails'] ?? $item['snippet']['thumbnails'] ?? [],
                    'duration' => $details['duration'] ?? null,
                    'publishedAt' => $item['snippet']['publishedAt'] ?? '',
                    'channelId' => $item['snippet']['channelId'] ?? '',
                    'previewUrl' => $videoId ? "https://www.youtube.com/watch?v=" . $videoId : '',
                    'proxyUrl' => $videoId ? "/spectra/youtube_proxy.php?action=stream&videoId=" . $videoId : ''
                ];
            }
        }
        
        return [
            'success' => true,
            'results' => $items,
            'total' => $data['pageInfo']['totalResults'] ?? 0,
            'nextPageToken' => $data['nextPageToken'] ?? null,
            'prevPageToken' => $data['prevPageToken'] ?? null
        ];
    }
    
    return ['success' => false, 'message' => 'YouTube search failed. HTTP Code: ' . $httpCode];
}

function searchITunes($query, $type, $limit, $offset) {
    $entity = 'song';
    if ($type === 'artist') $entity = 'musicArtist';
    if ($type === 'album') $entity = 'album';
    if ($type === 'playlist') $entity = 'musicTrack';
    
    $totalNeeded = min($offset + $limit, 200);
    
    $url = "https://itunes.apple.com/search?term=" . urlencode($query) . 
           "&entity=$entity&limit=$totalNeeded";
    
    list($result, $httpCode) = fetchWithCurl($url);
    
    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        $allItems = $data['results'] ?? [];
        
        $items = array_slice($allItems, $offset, $limit);
        
        return [
            'success' => true,
            'results' => $items,
            'total' => count($allItems)
        ];
    }
    
    return ['success' => false, 'message' => 'iTunes search failed'];
}

$response = ['success' => false, 'message' => 'Search source not supported'];

switch ($source) {
    case 'spotify':
        $response = searchSpotify($query, $type, $limit, $offset, $apiKeys);
        break;
    case 'youtube':
        $response = searchYouTube($query, $type, $apiKeys, $pageToken);
        break;
    case 'itunes':
        $response = searchITunes($query, $type, $limit, $offset);
        break;
    case 'soundcloud':
        $response = [
            'success' => false,
            'message' => 'SoundCloud search requires OAuth authentication'
        ];
        break;
}

echo json_encode($response);
?>
