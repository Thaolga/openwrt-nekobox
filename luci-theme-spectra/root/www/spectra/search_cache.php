<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$cacheDir = dirname(__FILE__) . '/cache/';

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$cacheFile = $cacheDir . 'search_cache.json';

$query = $_POST['query'] ?? ($_GET['query'] ?? '');
$type = $_POST['type'] ?? ($_GET['type'] ?? 'song');
$source = $_POST['source'] ?? ($_GET['source'] ?? 'itunes');

if (isset($_GET['get_playlist']) && $_GET['get_playlist'] === '1') {
    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        
        if (!$cacheData) {
            echo json_encode(['success' => false, 'message' => 'Cache file corrupted']);
            exit;
        }
        
        if ($cacheData['query'] === $query && 
            $cacheData['type'] === $type && 
            $cacheData['source'] === $source) {
            
            echo json_encode([
                'success' => true, 
                'data' => $cacheData,
                'playlist_ready' => true
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cache mismatch for playlist']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No cache available for playlist']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $results = $_POST['results'] ?? '[]';
    
    if (is_string($results)) {
        $results = json_decode($results, true);
    }
    
    $uniqueResults = removeDuplicates($results, $source);
    
    $cacheData = [
        'query' => $query,
        'type' => $type,
        'source' => $source,
        'results' => $uniqueResults,
        'timestamp' => time(),
        'date' => date('Y-m-d H:i:s'),
        'original_count' => count($results),
        'unique_count' => count($uniqueResults),
        'is_playlist' => false
    ];
    
    file_put_contents($cacheFile, json_encode($cacheData, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true, 
        'message' => 'Cache saved (duplicates removed)',
        'stats' => [
            'original' => count($results),
            'unique' => count($uniqueResults),
            'removed' => count($results) - count($uniqueResults)
        ]
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $query && $source) {
    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        
        if (!$cacheData) {
            echo json_encode(['success' => false, 'message' => 'Cache file corrupted']);
            exit;
        }
        
        if ($cacheData['query'] === $query && 
            $cacheData['type'] === $type && 
            $cacheData['source'] === $source) {
            
            if (time() - $cacheData['timestamp'] < 86400) {
                echo json_encode(['success' => true, 'data' => $cacheData]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Cache expired']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Cache mismatch']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Cache not found']);
    }
    exit;
}

if (isset($_GET['get_latest']) && $_GET['get_latest'] === '1') {
    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        
        if (!$cacheData) {
            echo json_encode(['success' => false, 'message' => 'Cache file corrupted']);
            exit;
        }
        
        if (time() - $cacheData['timestamp'] < 86400) {
            echo json_encode(['success' => true, 'data' => $cacheData]);
        } else {
            unlink($cacheFile);
            echo json_encode(['success' => false, 'message' => 'Cache expired and deleted']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No cache available']);
    }
    exit;
}

function removeDuplicates($results, $source) {
    if (!$results || !is_array($results)) {
        return [];
    }
    
    $seen = [];
    $uniqueResults = [];
    
    foreach ($results as $item) {
        $uniqueId = getUniqueIdentifier($item, $source);
        
        if (!in_array($uniqueId, $seen)) {
            $seen[] = $uniqueId;
            $uniqueResults[] = $item;
        }
    }
    
    return $uniqueResults;
}

function getUniqueIdentifier($item, $source) {
    switch($source) {
        case 'itunes':
            return isset($item['trackId']) ? 'itunes_' . $item['trackId'] : md5(json_encode($item));
            
        case 'spotify':
            return isset($item['id']) ? 'spotify_' . $item['id'] : md5(json_encode($item));
            
        case 'youtube':
            if (isset($item['id']['videoId'])) {
                return 'youtube_' . $item['id']['videoId'];
            } elseif (isset($item['id'])) {
                return 'youtube_' . $item['id'];
            }
            return md5(json_encode($item));
            
        case 'soundcloud':
            return isset($item['id']) ? 'soundcloud_' . $item['id'] : md5(json_encode($item));
            
        default:
            $title = isset($item['trackName']) ? $item['trackName'] : 
                    (isset($item['name']) ? $item['name'] : 
                    (isset($item['title']) ? $item['title'] : ''));
            
            $artist = isset($item['artistName']) ? $item['artistName'] : 
                     (isset($item['artists'][0]['name']) ? $item['artists'][0]['name'] : 
                     (isset($item['channelTitle']) ? $item['channelTitle'] : ''));
            
            return md5($title . '_' . $artist);
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>