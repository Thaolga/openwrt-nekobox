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
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
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

function fetchNeteaseLyrics($artist, $title, $songName) {
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
                        return [
                            'lyrics' => $lyricData['lrc']['lyric'],
                            'hasTimestamps' => true,
                            'source' => 'netease',
                            'keyword' => $keyword
                        ];
                    }
                }
            }
        }
    }
    
    return null;
}

function fetchKugouLyrics($artist, $title, $songName) {
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
                        return [
                            'lyrics' => $lyrics,
                            'hasTimestamps' => true,
                            'source' => 'kugou',
                            'keyword' => $keyword
                        ];
                    }
                }
            }
        }
    }
    
    return null;
}

function fetchLRCLibLyrics($artist, $title, $songName) {
    if (!$title) return null;
    
    $searchQuery = $title;
    if ($artist) {
        $searchQuery = $artist . ' ' . $title;
    }
    
    $searchUrl = "https://lrclib.net/api/search?q=" . urlencode($searchQuery);
    list($result, $httpCode) = fetchWithCurl($searchUrl);
    
    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        if (is_array($data) && count($data) > 0) {
            $songData = $data[0];
            if (isset($songData['syncedLyrics']) && !empty(trim($songData['syncedLyrics']))) {
                return [
                    'lyrics' => $songData['syncedLyrics'],
                    'hasTimestamps' => true,
                    'source' => 'lrclib'
                ];
            } elseif (isset($songData['plainLyrics']) && !empty(trim($songData['plainLyrics']))) {
                return [
                    'lyrics' => $songData['plainLyrics'],
                    'hasTimestamps' => false,
                    'source' => 'lrclib'
                ];
            }
        }
    }
    
    return null;
}

$response = ['success' => false, 'message' => 'No lyrics found'];

if (!empty($artist) || !empty($title) || !empty($songName)) {
    $lyricsData = null;
    $finalSource = '';

    $sources = [
        'lrclib' => fn() => fetchLRCLibLyrics($artist, $title, $songName),
        'netease' => fn() => fetchNeteaseLyrics($artist, $title, $songName),
        'kugou' => fn() => fetchKugouLyrics($artist, $title, $songName),
    ];

    if ($source === 'auto') {
        foreach ($sources as $name => $fetcher) {
            $lyricsData = $fetcher();
            if ($lyricsData) {
                $finalSource = $name;
                break;
            }
        }
    } else {
        if (isset($sources[$source])) {
            $lyricsData = $sources[$source]();
            $finalSource = $source;
        }
    }

    if ($lyricsData) {
        $response = [
            'success' => true,
            'lyrics' => $lyricsData['lyrics'],
            'hasTimestamps' => $lyricsData['hasTimestamps'],
            'source' => $finalSource,
            'artist' => $artist,
            'title' => $title,
            'songName' => $songName
        ];
    } else {
        $response = ['success' => false, 'message' => 'No lyrics found from selected source'];
    }
}

echo json_encode($response);
?>