<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true);
$source = $input['source'] ?? '';
$artist = $input['artist'] ?? '';
$title = $input['title'] ?? '';
$songName = $input['songName'] ?? '';

function fetchWithCurl($url, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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

$response = ['success' => false, 'message' => 'No lyrics found'];

switch ($source) {
    case 'netease':
        $searchKeywords = [];
        if ($artist && $title) {
            $searchKeywords[] = $artist . ' ' . $title;
        }
        if ($title) {
            $searchKeywords[] = $title;
        }
        if ($songName && $songName !== $title) {
            $searchKeywords[] = $songName;
        }
        
        foreach ($searchKeywords as $keyword) {
            $searchUrl = "https://music.163.com/api/search/get?s=" . urlencode($keyword) . "&type=1&limit=3";
            list($result, $httpCode) = fetchWithCurl($searchUrl);
            
            if ($httpCode === 200 && $result) {
                $data = json_decode($result, true);
                if (isset($data['result']['songs']) && count($data['result']['songs']) > 0) {
                    $songId = $data['result']['songs'][0]['id'];
                    $lyricUrl = "https://music.163.com/api/song/lyric?lv=-1&kv=-1&tv=-1&id=" . $songId;
                    
                    list($lyricResult, $lyricCode) = fetchWithCurl($lyricUrl);
                    if ($lyricCode === 200 && $lyricResult) {
                        $lyricData = json_decode($lyricResult, true);
                        if (isset($lyricData['lrc']['lyric']) && !empty(trim($lyricData['lrc']['lyric']))) {
                            $response = [
                                'success' => true,
                                'lyrics' => $lyricData['lrc']['lyric'],
                                'hasTimestamps' => true,
                                'source' => 'netease',
                                'keyword' => $keyword
                            ];
                            break;
                        }
                    }
                }
            }
        }
        break;
        
    case 'kugou':
        $searchKeywords = [];
        if ($artist && $title) {
            $searchKeywords[] = $artist . ' ' . $title;
        }
        if ($title) {
            $searchKeywords[] = $title;
        }
        
        foreach ($searchKeywords as $keyword) {
            $searchUrl = "http://lyrics.kugou.com/search?ver=1&man=yes&client=pc&keyword=" . urlencode($keyword) . "&duration=&hash=";
            list($result, $httpCode) = fetchWithCurl($searchUrl);
            
            if ($httpCode === 200 && $result) {
                $data = json_decode($result, true);
                if (isset($data['candidates']) && count($data['candidates']) > 0) {
                    $candidate = $data['candidates'][0];
                    $lyricUrl = "http://lyrics.kugou.com/download?ver=1&client=pc&id=" . $candidate['id'] . "&accesskey=" . $candidate['accesskey'] . "&fmt=lrc&charset=utf8";
                    
                    list($lyricResult, $lyricCode) = fetchWithCurl($lyricUrl);
                    if ($lyricCode === 200 && $lyricResult) {
                        $lyricData = json_decode($lyricResult, true);
                        if (isset($lyricData['content']) && !empty(trim($lyricData['content']))) {
                            $lyrics = base64_decode($lyricData['content']);
                            $response = [
                                'success' => true,
                                'lyrics' => $lyrics,
                                'hasTimestamps' => true,
                                'source' => 'kugou',
                                'keyword' => $keyword
                            ];
                            break;
                        }
                    }
                }
            }
        }
        break;
        
    case 'lyricsovh':
        if ($artist && $title) {
            $apiUrl = "https://api.lyrics.ovh/v1/" . urlencode($artist) . "/" . urlencode($title);
            list($result, $httpCode) = fetchWithCurl($apiUrl);
            
            if ($httpCode === 200 && $result) {
                $data = json_decode($result, true);
                if (isset($data['lyrics']) && !empty(trim($data['lyrics']))) {
                    $response = [
                        'success' => true,
                        'lyrics' => $data['lyrics'],
                        'hasTimestamps' => false,
                        'source' => 'lyricsovh'
                    ];
                }
            }
        }
        break;
}

echo json_encode($response);
?>