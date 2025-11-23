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
$preferredSource = $input['source'] ?? 'auto';

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

function optimizeImageUrl($url, $source) {
    if (!$url) return $url;

    switch ($source) {
        case 'netease':
            if (strpos($url, '?param=') !== false) {
                $url = preg_replace('/\?param=\d+y\d+/', '?param=2000y2000', $url);
            } else {
                $url .= '?param=2000y2000';
            }
            break;

        case 'itunes':
            $url = str_replace(['100x100bb', '60x60bb', '30x30bb'], '2000x2000bb', $url);
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
    $searchUrl = "https://itunes.apple.com/search?term=" . urlencode($artist . ' ' . $title) . "&entity=song&limit=5";
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
    if (!empty($title)) {
        $searchUrl = "https://api.deezer.com/search?q=" . urlencode("artist:\"$artist\" track:\"$title\"") . "&limit=3";
        list($result, $httpCode) = fetchWithCurl($searchUrl);

        if ($httpCode === 200 && $result) {
            $data = json_decode($result, true);
            if (isset($data['data'][0]['album']['cover_xl'])) {
                return optimizeImageUrl($data['data'][0]['album']['cover_xl'], 'deezer');
            }
        }
    }

    $searchUrl = "https://api.deezer.com/search?q=" . urlencode("artist:\"$artist\"") . "&limit=1";
    list($result, $httpCode) = fetchWithCurl($searchUrl);

    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        if (isset($data['data'][0]['artist']['picture_xl'])) {
            return $data['data'][0]['artist']['picture_xl'];
        }
    }

    return null;
}

function fetchLastfmImage($artist, $title) {
    $apiKey = 'a496648b2d382af443aaaadf7f7f6895';
    
    if (!empty($title)) {
        $url = "http://ws.audioscrobbler.com/2.0/?method=track.getInfo&artist=" . urlencode($artist) . 
               "&track=" . urlencode($title) . "&api_key={$apiKey}&format=json&autocorrect=1";
        list($result, $httpCode) = fetchWithCurl($url);

        if ($httpCode === 200 && $result) {
            $data = json_decode($result, true);
            if (isset($data['track']['album']['image'])) {
                $images = $data['track']['album']['image'];
                if (!empty($images)) {
                    $largest = end($images);
                    if (!empty($largest['#text'])) {
                        return $largest['#text'];
                    }
                }
            }
        }
    }

    $url = "http://ws.audioscrobbler.com/2.0/?method=artist.getInfo&artist=" . urlencode($artist) . 
           "&api_key={$apiKey}&format=json&autocorrect=1";
    list($result, $httpCode) = fetchWithCurl($url);

    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        if (isset($data['artist']['image'])) {
            $images = $data['artist']['image'];
            if (!empty($images)) {
                $largest = end($images);
                if (!empty($largest['#text'])) {
                    return $largest['#text'];
                }
            }
        }
    }

    return null;
}

$response = ['success' => false, 'message' => 'No high-quality image found'];

if (!empty($artist)) {
    $imageUrl = null;
    $source = '';

    $sources = [
        'itunes'  => fn() => fetchItunesImage($artist, $title),
        'netease' => fn() => fetchNeteaseImage($artist, $title),
        'deezer'  => fn() => fetchDeezerImage($artist, $title),
        'lastfm'  => fn() => fetchLastfmImage($artist, $title),
    ];

    if ($preferredSource === 'auto') {
        foreach ($sources as $name => $fetcher) {
            $imageUrl = $fetcher();
            if ($imageUrl) {
                $source = $name;
                break;
            }
        }
    } else {
        if (isset($sources[$preferredSource])) {
            $imageUrl = $sources[$preferredSource]();
            $source = $preferredSource;
        }
        
        if (!$imageUrl) {
            foreach ($sources as $name => $fetcher) {
                if ($name === $preferredSource) continue;
                $imageUrl = $fetcher();
                if ($imageUrl) {
                    $source = $name;
                    break;
                }
            }
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