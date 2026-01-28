<?php
ini_set('memory_limit', '512M');
$RECENT_MAX = 15;
$ROOT_DIR = '/';
$EXCLUDE_DIRS = [
    '/proc', '/sys', '/dev', '/tmp', '/run', '/rom',
    '/var/lock', '/var/run', '/overlay/upper'
];

$TYPE_EXT = [
    'music'  => ['mp3', 'ogg', 'wav', 'flac', 'm4a', 'aac'],
    'video'  => ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm'],
    'image'  => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg']
];

if (isset($_GET['preview']) && $_GET['preview'] == '1' && isset($_GET['path'])) {
    $filePath = urldecode($_GET['path']);
    
    $filePath = preg_replace('#/+#', '/', $filePath);
    if (substr($filePath, 0, 1) !== '/') {
        $filePath = '/' . $filePath;
    }
    
    $realPath = realpath($filePath);
    if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
        http_response_code(403);
        header('Content-Type: text/plain');
        exit('Access Denied: Invalid path');
    }
    
    foreach ($EXCLUDE_DIRS as $exclude) {
        if (strpos($realPath, $exclude) === 0) {
            http_response_code(403);
            header('Content-Type: text/plain');
            exit('Access Denied: Path is excluded');
        }
    }
    
    if (file_exists($realPath) && is_readable($realPath)) {
        $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $mimeMap = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'svg' => 'image/svg+xml',
            'png' => 'image/png', 'gif' => 'image/gif', 'svgz' => 'image/svg+xml',
            'bmp' => 'image/bmp', 'webp' => 'image/webp',
            'mp3' => 'audio/mpeg', 'wav' => 'audio/wav',
            'ogg' => 'audio/ogg', 'flac' => 'audio/flac',
            'm4a' => 'audio/mp4', 'aac' => 'audio/aac',
            'mp4' => 'video/mp4', 'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska', 'mov' => 'video/quicktime',
            'wmv' => 'video/x-ms-wmv', 'flv' => 'video/x-flv',
            'webm' => 'video/webm'
        ];
        
        $mimeType = $mimeMap[$ext] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($realPath));
        header('Cache-Control: max-age=3600');
        readfile($realPath);
        exit;
    } else {
        http_response_code(404);
        header('Content-Type: text/plain');
        exit('File not found or not readable: ' . $filePath);
    }
}

function getCpuUsageSimple() {
    if (!is_readable('/proc/stat')) {
        return 0;
    }
    
    $content1 = @file_get_contents('/proc/stat');
    if ($content1 === false) {
        return 0;
    }
    
    $lines1 = explode("\n", $content1);
    if (empty($lines1[0])) {
        return 0;
    }
    
    if (function_exists('time_nanosleep')) {
        time_nanosleep(0, 100000000);
    } else {
        $start = microtime(true);
        while (microtime(true) - $start < 0.1) {
        }
    }
    
    $content2 = @file_get_contents('/proc/stat');
    if ($content2 === false) {
        return 0;
    }
    
    $lines2 = explode("\n", $content2);
    if (empty($lines2[0])) {
        return 0;
    }
    
    $cpu1 = preg_split('/\s+/', trim($lines1[0]));
    $cpu2 = preg_split('/\s+/', trim($lines2[0]));
    
    if (count($cpu1) < 5 || count($cpu2) < 5) {
        return 0;
    }
    
    $total1 = intval($cpu1[1]) + intval($cpu1[2]) + intval($cpu1[3]) + intval($cpu1[4]);
    $idle1 = intval($cpu1[4]);
    
    $total2 = intval($cpu2[1]) + intval($cpu2[2]) + intval($cpu2[3]) + intval($cpu2[4]);
    $idle2 = intval($cpu2[4]);
    
    $totalDiff = $total2 - $total1;
    $idleDiff = $idle2 - $idle1;
    
    if ($totalDiff > 0) {
        $usage = (($totalDiff - $idleDiff) / $totalDiff) * 100;
        return round($usage, 1);
    }
    
    return 0;
}

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');

    $dt = json_decode(shell_exec("ubus call system board"), true);
    $devices = $dt['model'] ?? 'Unknown';
    
    $cpuUsage = getCpuUsageSimple();

    $tmpramTotal = exec("cat /proc/meminfo | grep MemTotal | awk '{print \$2}'");
    $tmpramAvailable = exec("cat /proc/meminfo | grep MemAvailable | awk '{print \$2}'");
    
    $tmpramTotal = intval($tmpramTotal);
    $tmpramAvailable = intval($tmpramAvailable);
    
    $ramTotal = number_format(($tmpramTotal / 1024), 1);
    $ramAvailable = number_format(($tmpramAvailable / 1024), 1);
    $ramUsage = number_format((($tmpramTotal - $tmpramAvailable) / 1024), 1);
    $memUsage = $tmpramTotal > 0 ? round((($tmpramTotal - $tmpramAvailable) / $tmpramTotal) * 100, 1) : 0;
    
    $raw_uptime = exec("cat /proc/uptime | awk '{print \$1}'");
    $days = floor($raw_uptime / 86400);
    $hours = floor(($raw_uptime / 3600) % 24);
    $minutes = floor(($raw_uptime / 60) % 60);
    $seconds = floor($raw_uptime % 60);
    $uptimeText = "{$days} days {$hours} hours {$minutes} minutes {$seconds} seconds";
    
    $cpuLoad = shell_exec("cat /proc/loadavg");
    $cpuLoad = explode(' ', $cpuLoad);
    $cpuLoadAvg1Min = round($cpuLoad[0], 2);
    $cpuLoadAvg5Min = round($cpuLoad[1], 2);
    $cpuLoadAvg15Min = round($cpuLoad[2], 2);
    
    $timezone = trim(shell_exec("uci get system.@system[0].zonename 2>/dev/null"));
    if (!$timezone) {
        $timezone = trim(shell_exec("cat /etc/TZ 2>/dev/null"));
        if (!$timezone) {
            $timezone = 'UTC';
        }
    }
    date_default_timezone_set($timezone);
    $currentTime = date("Y-m-d H:i:s");
    
    $cpuTemp = '--';
    $tempFiles = [
        '/sys/class/thermal/thermal_zone0/temp',
        '/sys/devices/virtual/thermal/thermal_zone0/temp'
    ];
    foreach ($tempFiles as $tempFile) {
        if (file_exists($tempFile)) {
            $temp = intval(file_get_contents($tempFile));
            if ($temp > 0) {
                $cpuTemp = $temp > 1000 ? round($temp / 1000, 1) : round($temp, 1);
                break;
            }
        }
    }
    
    $cpuCores = exec("grep -c '^processor' /proc/cpuinfo");
    $cpuCores = intval($cpuCores) ?: 1;
    
    $cpuFreq = '--';
    $freqFiles = [
        '/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq',
        '/sys/devices/system/cpu/cpu0/cpufreq/cpuinfo_cur_freq',
        '/proc/cpuinfo'
    ];
    
    foreach ($freqFiles as $freqFile) {
        if (file_exists($freqFile)) {
            if ($freqFile === '/proc/cpuinfo') {
                $freqContent = file_get_contents($freqFile);
                if (preg_match('/cpu MHz\s*:\s*([\d.]+)/', $freqContent, $matches)) {
                    $freq = floatval($matches[1]);
                    $cpuFreq = round($freq, 0) . ' MHz';
                    break;
                }
            } else {
                $freqContent = file_get_contents($freqFile);
                if ($freqContent !== false) {
                    $freq = intval(trim($freqContent));
                    if ($freq > 0) {
                        if ($freq > 1000) {
                            $cpuFreq = round($freq / 1000, 1) . ' GHz';
                        } else {
                            $cpuFreq = $freq . ' MHz';
                        }
                        break;
                    }
                }
            }
        }
    }
    
    $processCount = intval(shell_exec("ps | wc -l")) - 1;
    
    $networkRx = 0;
    $networkTx = 0;
    
    $netStat = @file('/proc/net/dev');
    if ($netStat) {
        $interfaces = ['br-lan', 'eth0', 'eth1', 'wlan0', 'wlan1'];
        
        foreach ($netStat as $line) {
            if (strpos($line, ':') === false) continue;
            
            foreach ($interfaces as $interface) {
                $pattern = '/^\s*' . $interface . ':\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/';
                if (preg_match($pattern, $line, $matches)) {
                    $networkRx = intval($matches[1]);
                    $networkTx = intval($matches[2]);
                    break 2;
                }
            }
        }
    }
    
    if ($networkRx == 0 && $networkTx == 0 && $netStat) {
        foreach ($netStat as $line) {
            if (strpos($line, ':') !== false && !preg_match('/^\s*lo:/', $line)) {
                if (preg_match('/^\s*(\w+):\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', $line, $matches)) {
                    $networkRx = intval($matches[2]);
                    $networkTx = intval($matches[3]);
                    break;
                }
            }
        }
    }
    
    $diskInfo = disk_free_space('/') !== false ? [
        'total' => disk_total_space('/'),
        'free' => disk_free_space('/'),
        'used' => disk_total_space('/') - disk_free_space('/')
    ] : null;
    
    $diskUsage = $diskInfo ? round(($diskInfo['used'] / $diskInfo['total']) * 100, 1) : 0;
    $diskTotal = $diskInfo ? round($diskInfo['total'] / (1024*1024*1024), 2) : 0;
    $diskUsed = $diskInfo ? round($diskInfo['used'] / (1024*1024*1024), 2) : 0;

    $openwrtVersion = trim(shell_exec("cat /etc/openwrt_release | grep 'DISTRIB_DESCRIPTION' | cut -d'=' -f2 | sed \"s/['\\\"]//g\""));
    if (!$openwrtVersion) {
        $openwrtVersion = trim(shell_exec("cat /etc/openwrt_release | grep 'DISTRIB_DESCRIPTION' | awk -F\"='\" '{print \$2}' | sed \"s/'//g\""));
    }
    if (!$openwrtVersion) {
        $openwrtVersion = $dt['release']['distribution'] . ' ' . $dt['release']['version'] ?? 'Unknown';
    }
    
    $kernelVersion = trim(shell_exec("uname -r"));
    if (!$kernelVersion) {
        $kernelVersion = trim(shell_exec("cat /proc/version | awk '{print \$3}'"));
    }
    
    $boardInfo = json_decode(shell_exec("ubus call system board"), true);
    $boardModel = $boardInfo['model'] ?? 'Unknown';
    
    echo json_encode([
        'success' => true,
        'cpu_usage' => $cpuUsage,
        'mem_usage' => $memUsage,
        'mem_total' => $ramTotal,
        'mem_used' => $ramUsage,
        'mem_free' => $ramAvailable,
        'cpu_temp' => $cpuTemp,
        'process_count' => $processCount,
        'cpu_cores' => $cpuCores,
        'cpu_freq' => $cpuFreq,
        'network_rx' => $networkRx,
        'network_tx' => $networkTx,
        'load_avg' => "$cpuLoadAvg1Min, $cpuLoadAvg5Min, $cpuLoadAvg15Min",
        'uptime' => $uptimeText,
        'system_time' => $currentTime,
        'timezone' => $timezone,
        'disk_usage' => $diskUsage,
        'disk_total' => $diskTotal,
        'disk_used' => $diskUsed,
        'openwrt_version' => $openwrtVersion,
        'kernel_version' => $kernelVersion,
        'board_model' => $boardModel
    ]);
    exit;
}

function getDiskInfo($path = '/') {
    $freeSpace = @disk_free_space($path);
    $totalSpace = @disk_total_space($path);
    
    if ($freeSpace === false || $totalSpace === false) {
        return null;
    }
    
    $usedSpace = $totalSpace - $freeSpace;
    $usedPercent = $totalSpace > 0 ? round(($usedSpace / $totalSpace) * 100, 1) : 0;
    
    $free_mb = round($freeSpace / (1024*1024), 1);
    $total_mb = round($totalSpace / (1024*1024), 1);
    $used_mb = round($usedSpace / (1024*1024), 1);
    
    return [
        'free' => $freeSpace,
        'total' => $totalSpace,
        'used' => $usedSpace,
        'used_percent' => $usedPercent,
        'free_mb' => $free_mb,
        'total_mb' => $total_mb,
        'used_mb' => $used_mb
    ];
}

function scanDirectory($path, $maxDepth = 5) {
    global $EXCLUDE_DIRS;
    $files = [];
    $seenFiles = [];
    
    if (!is_dir($path) || !is_readable($path)) {
        return $files;
    }
    
    $path = preg_replace('#/+#', '/', $path);
    
    foreach ($EXCLUDE_DIRS as $exclude) {
        if (strpos($path, $exclude) === 0) {
            return $files;
        }
    }
    
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        
        foreach ($iterator as $file) {
            if ($iterator->getDepth() > $maxDepth) {
                $iterator->next();
                continue;
            }
            
            if ($file->isFile() && $file->isReadable()) {
                $filePath = $file->getPathname();
                $realPath = realpath($filePath);
                
                if (!$realPath) continue;
                
                $excluded = false;
                foreach ($EXCLUDE_DIRS as $exclude) {
                    if (strpos($realPath, $exclude) === 0) {
                        $excluded = true;
                        break;
                    }
                }
                
                if ($excluded) continue;

                $fileName = $file->getFilename();
                $fileSize = $file->getSize();
                $fileKey = $fileName . '_' . $fileSize;
                
                if (isset($seenFiles[$fileKey])) {
                    continue;
                }
                
                $seenFiles[$fileKey] = true;
                
                $files[] = [
                    'path' => $realPath,
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'mtime' => $file->getMTime(),
                    'ext' => strtolower($file->getExtension()),
                    'safe_path' => htmlspecialchars($realPath, ENT_QUOTES, 'UTF-8'),
                    'safe_name' => htmlspecialchars($file->getFilename(), ENT_QUOTES, 'UTF-8')
                ];
            }
        }
    } catch (Exception $e) {
    }
    
    return $files;
}

function formatFileSize($bytes) {
    if ($bytes == 0) return "0 B";
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

function getVideoThumbnail($videoPath) {
    return "?thumbnail=1&path=" . urlencode($videoPath);
}

$media = ['music' => [], 'video' => [], 'image' => []];
$files = scanDirectory($ROOT_DIR);

foreach ($files as $file) {
    $ext = $file['ext'];
    foreach ($TYPE_EXT as $type => $exts) {
        if (in_array($ext, $exts)) {
            $media[$type][] = $file;
            break;
        }
    }
}

foreach ($media as &$files) {
    usort($files, function($a, $b) {
        return $b['mtime'] - $a['mtime'];
    });
}

$diskInfo = getDiskInfo('/');
$recent = isset($_COOKIE['recent_media']) ? json_decode($_COOKIE['recent_media'], true) : [];
$systemInfo = [
    'cpu_usage' => 0,
    'mem_usage' => 0,
    'mem_total' => 0,
    'mem_used' => 0,
    'mem_free' => 0,
    'uptime' => '',
    'load_avg' => '',
    'system_time' => date('Y-m-d H:i:s'),
    'timezone' => 'UTC'
];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate="openwrt_media_center">OpenWrt Media Center</title>
    <?php include './spectra.php'; ?>
</head>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: var(--font-family, -apple-system, BlinkMacSystemFont, sans-serif);
    background: #1a1a1a;
    color: #fff;
    height: 100vh;
    overflow: hidden;
}

.main-container {
    display: flex;
    height: 100vh;
}

.content-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.3s ease;
}

.content-area.fullscreen {
    display: none;
}

.top-bar {
    padding: 20px 30px;
    background: #2c2c2c;
    border-bottom: 1px solid #444;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo h1 {
    font-size: 1.5rem;
    color: #4CAF50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stats {
    display: flex;
    gap: 25px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 1.3rem;
    font-weight: bold;
    color: #4CAF50;
    display: block;
}

.stat-label {
    font-size: 0.85rem;
    color: #aaa;
    margin-top: 3px;
}

.actions {
    display: flex;
    gap: 10px;
}

.action-btn {
    background: #333;
    color: white;
    border: 1px solid #555;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #444;
    border-color: #666;
}

.action-btn.primary {
    background: #4CAF50;
    border-color: #4CAF50;
}

.action-btn.primary:hover {
    background: #45a049;
}

.side-nav {
    width: 240px;
    background: #252525;
    border-right: 1px solid #444;
    padding: 20px 15px;
    overflow-y: auto;
}

.nav-section {
    margin-bottom: 25px;
}

.nav-section-title {
    color: #888;
    font-size: 0.9rem;
    text-transform: uppercase;
    padding: 0 0 10px;
    border-bottom: 1px solid #333;
    margin-bottom: 15px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    color: #ccc;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 3px solid transparent;
    border-radius: 8px;
    margin: 5px 0;
}

.nav-item:hover {
    background: rgba(51, 51, 51, 0.8);
    color: white;
    transform: translateX(3px);
}

.nav-item.active {
    background: #4CAF50;
    color: white;
}

.nav-icon {
    font-size: 1.2rem;
    width: 24px;
    margin-right: 12px;
}

.media-grid-container {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
    background: #1a1a1a;
}

.grid-title {
    font-size: 1.4rem;
    margin-bottom: 25px;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
}

.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.media-item {
    background: #2c2c2c;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s;
    border: 1px solid #444;
    position: relative;
}

.media-item:hover {
    transform: translateY(-5px);
    border-color: #4CAF50;
    box-shadow: 0 10px 20px rgba(0,0,0,0.3);
}

.media-thumb {
    width: 100%;
    height: 140px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.media-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-thumb i {
    font-size: 2.5rem;
    color: white;
}

.media-info {
    padding: 15px;
}

.media-name {
    font-weight: 600;
    margin-bottom: 8px;
    color: white;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.media-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #aaa;
}

.player-area {
    width: 50%;
    background: #000;
    display: none;
    flex-direction: column;
    border-left: 1px solid #333;
}

.player-area.active {
    display: flex;
}

.player-header {
    padding: 20px;
    background: rgba(0, 0, 0, 0.8);
    border-bottom: 1px solid #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.player-title {
    font-size: 1.2rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

.player-actions {
    display: flex;
    gap: 10px;
}

.player-btn {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.player-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.player-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

#audioPlayer, #videoPlayer {
    width: 100%;
    background: #000;
    border-radius: 8px;
}

#imageViewer {
    max-width: 100%;
    max-height: 80vh;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.fullscreen-player {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #000;
    z-index: 1000;
    display: none;
}

.fullscreen-player.active {
    display: flex;
    flex-direction: column;
}

.fullscreen-header {
    padding: 20px;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1001;
}

.fullscreen-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    padding: 0;
    margin: 0;
}

#fullscreenVideo {
    width: 100%;
    height: 100%;
    background: #000;
    object-fit: contain;
}

#fullscreenAudio {
    width: 80%;
    max-width: 800px;
    background: #000;
    position: relative;
    z-index: 1002;
}

#fullscreenImage {
    width: auto;
    height: auto;
    max-width: 95%;
    max-height: 95%;
    object-fit: contain;
    margin: auto;
}

#fullscreenPlayError {
    color: #fff;
    text-align: center;
    padding: 40px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.8);
    border-radius: 10px;
}

.player-area {
    width: 50%;
    background: #000;
    display: none;
    flex-direction: column;
    border-left: 1px solid #333;
}

.player-area.active {
    display: flex;
}

.player-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
}

#videoPlayer {
    width: 100%;
    max-height: calc(100vh - 120px);
    background: #000;
    border-radius: 8px;
    object-fit: contain;
}

#audioPlayer {
    width: 100%;
    max-width: 600px;
    background: #000;
    border-radius: 8px;
}

#imageViewer {
    max-width: 90%;
    max-height: 80vh;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    object-fit: contain;
}

.fullscreen-player {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.fullscreen-player:fullscreen #fullscreenVideo::-webkit-media-controls-panel,
.fullscreen-player:fullscreen #fullscreenAudio::-webkit-media-controls-panel {
    opacity: 0;
    transition: opacity 0.3s;
}

.fullscreen-player:fullscreen:hover #fullscreenVideo::-webkit-media-controls-panel,
.fullscreen-player:fullscreen:hover #fullscreenAudio::-webkit-media-controls-panel {
    opacity: 1;
}

@media (max-width: 768px) {
    .player-area {
        width: 100%;
    }
    
    #fullscreenAudio {
        width: 90%;
    }
    
    #fullscreenImage {
        max-width: 98%;
        max-height: 90%;
    }
}

@media (hover: none) and (pointer: coarse) {
    #fullscreenVideo::-webkit-media-controls-panel,
    #fullscreenAudio::-webkit-media-controls-panel {
        opacity: 1 !important;
    }
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
    background: #2c2c2c;
    border-radius: 12px;
    border: 1px solid #444;
}

.empty-icon {
    font-size: 3.5rem;
    margin-bottom: 20px;
    opacity: 0.5;
    color: #4CAF50;
}

.recent-list {
    padding: 20px;
}

.recent-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    background: #2c2c2c;
    border-radius: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid #444;
}

.recent-item:hover {
    background: #333;
    border-color: #4CAF50;
}

.recent-icon {
    width: 40px;
    height: 40px;
    border-radius: 6px;
    background: rgba(76, 175, 80, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: #4CAF50;
}

.recent-info {
    flex: 1;
    min-width: 0;
}

.recent-name {
    font-weight: 500;
    margin-bottom: 3px;
    color: white;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.recent-path {
    font-size: 0.85rem;
    color: #aaa;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #2c2c2c;
}

::-webkit-scrollbar-thumb {
    background: #555;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #666;
}

.loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 14px;
}

.warning-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #ff9800;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: bold;
}

.media-item {
    animation: fadeIn 0.3s ease forwards;
    opacity: 0;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.fullscreen-player video {
    width: 100vw;
    height: 100vh;
    object-fit: contain;
}

.fullscreen-player img {
    max-width: 95vw;
    max-height: 95vh;
    object-fit: contain;
}

.skeleton {
    background: linear-gradient(90deg, #2c2c2c 25%, #333 50%, #2c2c2c 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.context-menu {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 450px;
    background: #2c2c2c;
    border: 1px solid #444;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    z-index: 10000;
    display: none;
}

.context-menu-header {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #444;
    background: #333;
    border-radius: 10px 10px 0 0;
}

.context-menu-header i {
    color: #4CAF50;
    margin-right: 10px;
    font-size: 1.2rem;
}

.context-menu-header span {
    font-weight: bold;
    flex: 1;
}

.context-menu-close {
    background: none;
    border: none;
    color: #aaa;
    cursor: pointer;
    font-size: 1.2rem;
    padding: 5px;
    border-radius: 5px;
}

.context-menu-close:hover {
    background: #444;
    color: #fff;
}

.context-menu-content {
    padding: 20px;
    max-height: 400px;
    overflow-y: auto;
}

.info-item {
    display: flex;
    margin-bottom: 15px;
    align-items: flex-start;
}

.info-label {
    width: 100px;
    color: #aaa;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.info-value {
    flex: 1;
    color: #fff;
    word-break: break-all;
    line-height: 1.4;
}

.context-menu-actions {
    display: flex;
    padding: 15px 20px;
    border-top: 1px solid #444;
    gap: 10px;
}

.context-menu-btn {
    flex: 1;
    padding: 10px;
    background: #333;
    border: 1px solid #444;
    border-radius: 5px;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.context-menu-btn:hover {
    background: #444;
}

.context-menu-btn:first-child {
    background: #4CAF50;
    border-color: #4CAF50;
}

.context-menu-btn:first-child:hover {
    background: #45a049;
}

.context-menu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: none;
}

.chart-container {
    position: relative;
    width: 100%;
}

.chart-canvas {
    width: 100% !important;
    height: 100% !important;
}

@keyframes pulse {
    0% { opacity: 0.7; }
    50% { opacity: 1; }
    100% { opacity: 0.7; }
}

.critical {
    animation: pulse 1s infinite;
}

.status-card {
    background: #333;
    border-radius: 10px;
    padding: 15px;
    transition: all 0.3s;
}

.status-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.system-time {
    font-family: 'Courier New', monospace;
    letter-spacing: 1px;
}

.time-updating {
    animation: timePulse 1s infinite;
}

@keyframes timePulse {
    0% { opacity: 0.8; }
    50% { opacity: 1; }
    100% { opacity: 0.8; }
}

@media (max-width: 768px) {
    #homeSection > div:first-child {
        grid-template-columns: 1fr;
    }

    .system-status-grid {
        grid-template-columns: 1fr !important;
    }
    
    .system-charts {
        grid-template-columns: 1fr;
    }
    
    .real-time-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .real-time-stats {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 1200px) {
    .main-container {
        flex-direction: column;
    }
    
    .player-area {
        width: 100%;
        height: 50vh;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1000;
    }
    
    .content-area {
        height: 50vh;
    }
    
    .side-nav {
        width: 100%;
        height: auto;
        flex-direction: row;
        overflow-x: auto;
        padding: 10px;
    }
    
    .nav-section {
        padding: 15px;
        border-bottom: none;
        border-right: 1px solid #333;
    }
}

@media (max-width: 768px) {
    .media-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
    }
    
    .top-bar {
        padding: 15px;
        flex-wrap: wrap;
    }
    
    .stats {
        order: 3;
        width: 100%;
        justify-content: space-around;
        margin-top: 15px;
    }

    .side-nav {
        width: 180px;
        padding: 15px 0;
    }
    
    .nav-item {
        padding: 10px 12px;
    }

    .logo h1 {
        margin-bottom: 12px;
    }
}

@media (prefers-color-scheme: dark) {
    body {
        background: #0a0a0a;
    }
    
    .media-item {
        background: #222;
    }
}

.media-item:focus-visible,
.nav-item:focus-visible,
.action-btn:focus-visible {
    outline: 2px solid #4CAF50;
    outline-offset: 2px;
}

.hover-playable {
    position: relative;
}

.hover-video-container {
    z-index: 100 !important;
}

@media (hover: none) and (pointer: coarse) {
    .media-item:hover {
        transform: none;
    }
    
    .action-btn,
    .player-btn {
        min-height: 44px;
        min-width: 44px;
    }
}
</style>
<div class="main-container">
    <div class="content-area" id="contentArea">
        <div class="top-bar">
            <div class="logo">
                <h1><i class="fas fa-server"></i> <span data-translate="openwrt_media_center">OpenWrt Media Center</span></h1>
            </div>
            
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-value"><?= count($media['music']) ?></span>
                    <span class="stat-label" data-translate="audio">Music</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= count($media['video']) ?></span>
                    <span class="stat-label" data-translate="video">Video</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= count($media['image']) ?></span>
                    <span class="stat-label" data-translate="image">Image</span>
                </div>
                <?php if ($diskInfo): ?>
                <div class="stat-item">
                    <span class="stat-value"><?= $diskInfo['used_percent'] ?>%</span>
                    <span class="stat-label" data-translate="disk_usage">Disk Usage</span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="actions">
                <button class="action-btn" onclick="refreshMedia()">
                    <i class="fas fa-redo"></i>
                    <span data-translate="refresh">Refresh</span>
                </button>
                <button class="action-btn primary" onclick="toggleFullscreen()">
                    <i class="fas fa-expand"></i>
                    <span data-translate="fullscreen_play">Fullscreen Play</span>
                </button>
            </div>
        </div>
        
        <div style="display: flex; flex: 1; overflow: hidden;">
            <div class="side-nav">
                <div class="nav-section">
                    <div class="nav-section-title" data-translate="media_categories">Media Categories</div>
                    <a href="#" class="nav-item active" onclick="showSection('home')">
                        <span class="nav-icon"><i class="fas fa-home"></i></span>
                        <span data-translate="home">Home</span>
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('music')">
                        <span class="nav-icon"><i class="fas fa-music"></i></span>
                        <span data-translate="audio">Music</span>
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('video')">
                        <span class="nav-icon"><i class="fas fa-video"></i></span>
                        <span data-translate="video">Video</span>
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('image')">
                        <span class="nav-icon"><i class="fas fa-image"></i></span>
                        <span data-translate="image">Image</span>
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('recent')">
                        <span class="nav-icon"><i class="fas fa-history"></i></span>
                        <span data-translate="recent_play">Recent Play</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title" data-translate="system_status">System Status</div>
                    <?php if ($diskInfo): ?>
                    <div style="padding: 15px; color: #aaa; font-size: 0.9rem;">
                        <div><span data-translate="disk_usage_colon">Disk Usage:</span> <?= $diskInfo['used_mb'] ?>MB / <?= $diskInfo['total_mb'] ?>MB</div>
                        <div style="height: 6px; background: #333; border-radius: 3px; margin: 10px 0; overflow: hidden;">
                            <div style="width: <?= $diskInfo['used_percent'] ?>%; height: 100%; background: #4CAF50;"></div>
                        </div>
                        <div><span data-translate="free_space">Free Space:</span> <?= $diskInfo['free_mb'] ?>MB</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="media-grid-container" id="gridContainer">
                <div id="homeSection" class="grid-section">
                    <div class="grid-title">
                        <i class="fas fa-home"></i>
                        <span data-translate="welcome_to_media_center">Welcome to Media Center</span>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div style="background: #2c2c2c; border-radius: 10px; padding: 25px; border: 1px solid #444;">
                            <h3 style="margin-bottom: 20px; color: #4CAF50;" data-translate="media_statistics">Media Statistics</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 2rem; color: #4CAF50; margin-bottom: 5px;"><?= count($media['music']) ?></div>
                                    <div style="color: #aaa;" data-translate="music_files">Music Files</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 2rem; color: #4CAF50; margin-bottom: 5px;"><?= count($media['video']) ?></div>
                                    <div style="color: #aaa;" data-translate="video_files">Video Files</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 2rem; color: #4CAF50; margin-bottom: 5px;"><?= count($media['image']) ?></div>
                                    <div style="color: #aaa;" data-translate="image_files">Image Files</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 2rem; color: #4CAF50; margin-bottom: 5px;"><?= count($media['music']) + count($media['video']) + count($media['image']) ?></div>
                                    <div style="color: #aaa;" data-translate="total_files">Total Files</div>
                                </div>
                            </div>
                        </div>

                        <div style="background: #2c2c2c; border-radius: 10px; padding: 25px; border: 1px solid #444;">
                            <h3 style="margin-bottom: 20px; color: #4CAF50;" data-translate="quick_actions">Quick Actions</h3>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <button class="action-btn" onclick="showSection('music')" style="justify-content: flex-start; text-align: left;">
                                    <i class="fas fa-music"></i>
                                    <span data-translate="browse_music">Browse Music</span>
                                </button>
                                <button class="action-btn" onclick="showSection('video')" style="justify-content: flex-start; text-align: left;">
                                    <i class="fas fa-video"></i>
                                    <span data-translate="browse_video">Browse Video</span>
                                </button>
                                <button class="action-btn" onclick="showSection('image')" style="justify-content: flex-start; text-align: left;">
                                    <i class="fas fa-image"></i>
                                    <span data-translate="browse_images">Browse Images</span>
                                </button>
                            </div>
                        </div>

                        <div style="background: #2c2c2c; border-radius: 10px; padding: 25px; border: 1px solid #444;">
                            <h3 style="margin-bottom: 20px; color: #4CAF50;" data-translate="system_status">System Status</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; color: #4CAF50; margin-bottom: 5px;" id="cpuUsageDisplay">--%</div>
                                    <div style="color: #aaa;" data-translate="cpu_usage">CPU Usage</div>
                                    <div style="font-size: 0.8rem; color: #666; margin-top: 5px;" id="cpuCoresDisplay">
                                        <i class="fas fa-microchip"></i> <span id="cpuCoresValue">--</span> <span data-translate="cores">cores</span>
                                    </div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; color: #2196F3; margin-bottom: 5px;" id="memUsageDisplay">--%</div>
                                    <div style="color: #aaa;" data-translate="memory_usage">Memory Usage</div>
                                    <div style="font-size: 0.8rem; color: #666; margin-top: 5px;">
                                        <i class="fas fa-memory"></i> <span id="memUsedDisplay">--</span>/<span id="memTotalDisplay">--</span> MB
                                    </div>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.2rem; color: #3F51B5; margin-bottom: 5px;" id="openwrtVersionDisplay">--</div>
                                    <div style="color: #aaa; font-size: 0.9rem;" data-translate="openwrt_version">OpenWrt Version</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.2rem; color: #795548; margin-bottom: 5px;" id="kernelVersionDisplay">--</div>
                                    <div style="color: #aaa; font-size: 0.9rem;" data-translate="kernel_version">Kernel Version</div>
                                </div>
                            </div>
    
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.2rem; color: #009688; margin-bottom: 5px;" id="boardModelDisplay">--</div>
                                    <div style="color: #aaa; font-size: 0.9rem;" data-translate="board_model">Board Model</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.2rem; color: #FF9800; margin-bottom: 5px;" id="timeValue">--:--:--</div>
                                    <div style="color: #aaa; font-size: 0.9rem;" data-translate="system_time">System Time</div>
                                </div>
                            </div>
    
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.2rem; color: #00BCD4; margin-bottom: 5px;" id="timezoneDisplay">--</div>
                                    <div style="color: #aaa; font-size: 0.9rem;" data-translate="timezone">Timezone</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.2rem; color: #E91E63; margin-bottom: 5px;" id="loadAvgDisplay">--</div>
                                    <div style="color: #aaa; font-size: 0.9rem;" data-translate="load_average">Load Average</div>
                                </div>
                            </div>
    
                            <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px; margin-bottom: 0;">
                                <div style="font-size: 1.2rem; color: #9C27B0; margin-bottom: 5px;" id="uptimeDisplay">--:--:--</div>
                                <div style="color: #aaa; font-size: 0.9rem;" data-translate="uptime">Uptime</div>
                            </div>
                        </div>

                        <div style="background: #2c2c2c; border-radius: 10px; padding: 25px; border: 1px solid #444;">
                            <h3 style="margin-bottom: 20px; color: #4CAF50;" data-translate="system_monitoring">System Monitoring</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <span style="color: #fff; font-weight: 500;" data-translate="cpu_usage">CPU Usage</span>
                                        <span id="cpuUsageValue" style="color: #4CAF50; font-weight: bold;"><?= $systemInfo['cpu_usage'] ?>%</span>
                                    </div>
                                    <div style="height: 30px; background: #333; border-radius: 15px; overflow: hidden; position: relative;">
                                        <div id="cpuUsageBar" style="width: <?= min($systemInfo['cpu_usage'], 100) ?>%; height: 100%; background: linear-gradient(90deg, #4CAF50, #8BC34A); transition: width 1s ease; border-radius: 15px;"></div>
                                    </div>
                                    <div id="cpuChart" style="height: 200px; margin-top: 20px; background: #222; border-radius: 10px; padding: 15px;">
                                        <canvas id="cpuChartCanvas"></canvas>
                                    </div>
                                </div>
                                
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <span style="color: #fff; font-weight: 500;" data-translate="memory_usage">Memory Usage</span>
                                        <span id="memUsageValue" style="color: #2196F3; font-weight: bold;"><?= $systemInfo['mem_usage'] ?>%</span>
                                    </div>
                                    <div style="height: 30px; background: #333; border-radius: 15px; overflow: hidden; position: relative;">
                                        <div id="memUsageBar" style="width: <?= min($systemInfo['mem_usage'], 100) ?>%; height: 100%; background: linear-gradient(90deg, #2196F3, #03A9F4); transition: width 1s ease; border-radius: 15px;"></div>
                                    </div>
                                    <div id="memChart" style="height: 200px; margin-top: 20px; background: #222; border-radius: 10px; padding: 15px;">
                                        <canvas id="memChartCanvas"></canvas>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 30px;">
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.2rem; color: #FF9800; margin-bottom: 5px;" id="cpuTempDisplay">--°C</div>
                                    <div style="color: #aaa; font-size: 0.9rem;" data-translate="cpu_temperature">CPU Temperature</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.2rem; color: #9C27B0; margin-bottom: 5px;" id="processCountDisplay">--</div>
                                    <div style="color: #aaa; font-size: 0.9rem;" data-translate="running_processes">Running Processes</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.2rem; color: #00BCD4; margin-bottom: 5px;" id="cpuFreqDisplay">--</div>
                                    <div style="color: #aaa; font-size: 0.9rem;" data-translate="cpu_frequency">CPU Frequency</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: #333; border-radius: 8px;">
                                    <div style="font-size: 1.2rem; color: #E91E63; margin-bottom: 5px;" id="networkSpeedDisplay">0 KB/s</div>
                                    <div style="color: #aaa; font-size: 0.9rem;" data-translate="network_speed">Network Speed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="musicSection" class="grid-section" style="display: none;">
                    <div class="grid-title">
                        <i class="fas fa-music"></i>
                        <span data-translate="audio">Music</span> (<?= count($media['music']) ?> <span data-translate="items">items</span>)
                    </div>
                    
                    <?php if (empty($media['music'])): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-music"></i>
                        </div>
                        <p style="margin-top: 15px;" data-translate="no_music_files_found">No music files found</p>
                    </div>
                    <?php else: ?>
                    <div class="media-grid">
                        <?php foreach ($media['music'] as $item): 
                            $path = $item['path'];
                            $file = basename($path);
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $size = $item['size'];
                            
                            $duration = $bitrate = '';
                            $ffmpegPath = '/usr/bin/ffmpeg';
                            $cmd = "$ffmpegPath -i \"$path\" 2>&1";
                            $output = shell_exec($cmd);
                            
                            if ($output) {
                                preg_match('/Duration:\s*(\d+):(\d+):(\d+)/', $output, $matches) && $duration = sprintf("%02d:%02d:%02d", $matches[1], $matches[2], $matches[3]);
                                (preg_match('/bitrate:\s*(\d+)\s*kb\/s/', $output, $matches) || preg_match('/Stream.*Audio:.*?(\d+)\s*kb\/s/', $output, $matches)) && $bitrate = $matches[1] . ' kbps';
                            } else {
                                $duration = $bitrate = 'Unknown';
                            }
                        ?>
                        <div class="media-item hover-playable" 
                             data-type="audio"
                             data-src="?preview=1&path=<?= urlencode($item['path']) ?>"
                             data-filename="<?= htmlspecialchars($item['safe_name']) ?>"
                             data-filesize="<?= formatFileSize($item['size']) ?>"
                             data-duration="<?= $duration ?>"
                             data-bitrate="<?= $bitrate ?>"
                             data-resolution="N/A"
                             data-ext="<?= strtoupper($item['ext']) ?>"
                             onclick="playMedia('<?= $item['safe_path'] ?>')"
                             oncontextmenu="showMediaInfo(event, this)">
                            <div class="media-thumb"><i class="fas fa-music"></i></div>
                            <div class="media-info">
                                <div class="media-name"><?= $item['safe_name'] ?></div>
                                <div class="media-meta">
                                    <span><?= strtoupper($item['ext']) ?></span>
                                    <span><?= formatFileSize($item['size']) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div id="videoSection" class="grid-section" style="display: none;">
                    <div class="grid-title">
                        <i class="fas fa-video"></i>
                        <span data-translate="video">Video</span> (<?= count($media['video']) ?> <span data-translate="items">items</span>)
                    </div>
                    
                    <?php if (empty($media['video'])): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <p style="margin-top: 15px;" data-translate="no_video_files_found">No video files found</p>
                    </div>
                    <?php else: ?>
                    <div class="media-grid">
                        <?php foreach ($media['video'] as $item): 
                            $path = $item['path'];
                            $file = basename($path);
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $size = $item['size'];
                            
                            $duration = $bitrate = $resolution = '';
                            $ffmpegPath = '/usr/bin/ffmpeg';
                            $cmd = "$ffmpegPath -i \"$path\" 2>&1";
                            $output = shell_exec($cmd);
                            
                            if ($output) {
                                preg_match('/Duration:\s*(\d+):(\d+):(\d+)/', $output, $matches) && $duration = sprintf("%02d:%02d:%02d", $matches[1], $matches[2], $matches[3]);
                                (preg_match('/bitrate:\s*(\d+)\s*kb\/s/', $output, $matches) || preg_match('/Stream.*Video:.*?(\d+)\s*kb\/s/', $output, $matches)) && $bitrate = $matches[1] . ' kbps';
                                preg_match('/(\d{3,4})x(\d{3,4})/', $output, $matches) && $resolution = $matches[1] . 'x' . $matches[2];
                            } else {
                                $duration = $bitrate = $resolution = 'Unknown';
                            }
                        ?>
                        <div class="media-item hover-playable" 
                             data-type="video"
                             data-src="?preview=1&path=<?= urlencode($item['path']) ?>"
                             data-filename="<?= htmlspecialchars($item['safe_name']) ?>"
                             data-filesize="<?= formatFileSize($item['size']) ?>"
                             data-duration="<?= $duration ?>"
                             data-bitrate="<?= $bitrate ?>"
                             data-resolution="<?= $resolution ?>"
                             data-ext="<?= strtoupper($item['ext']) ?>"
                             onclick="playMedia('<?= $item['safe_path'] ?>')"
                             oncontextmenu="showMediaInfo(event, this)">
                            <div class="media-thumb"><i class="fas fa-video"></i></div>
                            <div class="media-info">
                                <div class="media-name"><?= $item['safe_name'] ?></div>
                                <div class="media-meta">
                                    <span><?= strtoupper($item['ext']) ?></span>
                                    <span><?= formatFileSize($item['size']) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div id="imageSection" class="grid-section" style="display: none;">
                    <div class="grid-title">
                        <i class="fas fa-image"></i>
                        <span data-translate="image">Image</span> (<?= count($media['image']) ?> <span data-translate="items">items</span>)
                    </div>
                    
                    <?php if (empty($media['image'])): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <p style="margin-top: 15px;" data-translate="no_image_files_found">No image files found</p>
                    </div>
                    <?php else: ?>
                    <div class="media-grid">
                        <?php foreach ($media['image'] as $item): 
                            $path = $item['path'];
                            $file = basename($path);
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $size = $item['size'];
                            
                            $resolution = 'Unknown';
                            $imageInfo = @getimagesize($path);
                            $imageInfo && $resolution = $imageInfo[0] . 'x' . $imageInfo[1];
                        ?>
                        <div class="media-item" 
                             data-type="image"
                             data-src="?preview=1&path=<?= urlencode($item['path']) ?>"
                             data-filename="<?= htmlspecialchars($item['safe_name']) ?>"
                             data-filesize="<?= formatFileSize($item['size']) ?>"
                             data-duration="N/A"
                             data-bitrate="N/A"
                             data-resolution="<?= $resolution ?>"
                             data-ext="<?= strtoupper($item['ext']) ?>"
                             onclick="playMedia('<?= $item['safe_path'] ?>')"
                             oncontextmenu="showMediaInfo(event, this)">
                            <div class="media-thumb">
                                <img src="?preview=1&path=<?= urlencode($item['path']) ?>" 
                                     alt="<?= $item['safe_name'] ?>"
                                     loading="lazy"
                                     onerror="handleThumbError(this)">
                            </div>
                            <div class="media-info">
                                <div class="media-name"><?= $item['safe_name'] ?></div>
                                <div class="media-meta">
                                    <span><?= strtoupper($item['ext']) ?></span>
                                    <span><?= formatFileSize($item['size']) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div id="recentSection" class="grid-section" style="display: none;">
                    <div class="grid-title">
                        <i class="fas fa-history"></i>
                        <span data-translate="recent_play">Recent Play</span>
                    </div>
                    
                    <div class="recent-list" id="recentList">
                        <?php if (empty($recent)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-play-circle"></i>
                            </div>
                            <p style="margin-top: 15px;" data-translate="no_playback_history">No playback history</p>
                        </div>
                        <?php else: ?>
                            <?php foreach (array_slice($recent, 0, 10) as $file): ?>
                            <?php
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $icon = 'fas fa-image';
                            if (in_array($ext, $TYPE_EXT['music'])) {
                                $icon = 'fas fa-music';
                            } elseif (in_array($ext, $TYPE_EXT['video'])) {
                                $icon = 'fas fa-video';
                            }
                            ?>
                            <div class="recent-item" onclick="playMedia('<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>')">
                                <div class="recent-icon">
                                    <i class="<?= $icon ?>"></i>
                                </div>
                                <div class="recent-info">
                                    <div class="recent-name"><?= htmlspecialchars(basename($file), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="recent-path"><?= htmlspecialchars(dirname($file), ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="player-area" id="playerArea">
        <div class="player-header">
            <div class="player-title" id="playerTitle">
                <i class="fas fa-play"></i>
                <span data-translate="media_player">Media Player</span>
            </div>
            <div class="player-actions">
                <button class="player-btn" onclick="toggleFullscreenPlayer()">
                    <i class="fas fa-expand"></i>
                </button>
                <button class="player-btn" onclick="closePlayer()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="player-content">
            <audio id="audioPlayer" controls style="display: none;"></audio>
            <video id="videoPlayer" controls style="display: none;"></video>
            <img id="imageViewer" style="display: none;" />
            <div id="playError" style="display: none; text-align: center; padding: 40px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ff9800; margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 10px;" data-translate="cannot_play_media">Cannot play media file</h3>
                <p style="color: #aaa;" data-translate="possible_reasons">Possible reasons:</p>
                <ul style="color: #aaa; text-align: left; margin-top: 10px; padding-left: 20px;">
                    <li data-translate="reason_unsupported_format">File format not supported by browser</li>
                    <li data-translate="reason_incorrect_path">File path is incorrect</li>
                    <li data-translate="reason_server_unreachable">Server cannot access the file</li>
                </ul>
            </div>
        </div>
    </div>
</div>
    
<div class="fullscreen-player" id="fullscreenPlayer">
    <div class="fullscreen-header">
        <div class="player-title" id="fullscreenTitle">
            <i class="fas fa-play"></i>
            <span data-translate="fullscreen_play">Fullscreen Play</span>
        </div>
        <div class="player-actions">
            <button class="player-btn" onclick="toggleFullscreenPlayer()">
                <i class="fas fa-compress"></i>
            </button>
            <button class="player-btn" onclick="closeFullscreenPlayer()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    
    <div class="fullscreen-content">
        <audio id="fullscreenAudio" controls style="display: none;"></audio>
        <video id="fullscreenVideo" controls style="display: none;"></video>
        <img id="fullscreenImage" style="display: none;" />
        <div id="fullscreenPlayError" style="display: none; text-align: center; padding: 40px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ff9800; margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 10px;" data-translate="cannot_play_media">Cannot play media file</h3>
        </div>
    </div>
</div>

<div id="mediaContextMenu" class="context-menu" style="display: none;">
    <div class="context-menu-header">
        <i class="fas fa-info-circle"></i>
        <span data-translate="media_info">Media Info</span>
        <button class="context-menu-close" onclick="hideContextMenu()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="context-menu-content">
        <div class="info-item">
            <span class="info-label" data-translate="filename">Name:</span>
            <span class="info-value" id="infoFilename"></span>
        </div>
        <div class="info-item">
            <span class="info-label" data-translate="filesize">Size:</span>
            <span class="info-value" id="infoFilesize"></span>
        </div>
        <div class="info-item">
            <span class="info-label" data-translate="type">Type:</span>
            <span class="info-value" id="infoType"></span>
        </div>
        <div class="info-item" id="durationItem">
            <span class="info-label" data-translate="duration">Duration:</span>
            <span class="info-value" id="infoDuration"></span>
        </div>
        <div class="info-item" id="resolutionItem">
            <span class="info-label" data-translate="resolution">Resolution:</span>
            <span class="info-value" id="infoResolution"></span>
        </div>
        <div class="info-item" id="bitrateItem">
            <span class="info-label" data-translate="bitrate">Bitrate:</span>
            <span class="info-value" id="infoBitrate"></span>
        </div>
        <div class="info-item">
            <span class="info-label" data-translate="file_path">Path:</span>
            <span class="info-value" id="infoPath"></span>
        </div>
    </div>
    <div class="context-menu-actions">
        <button class="context-menu-btn" onclick="playSelectedMedia()">
            <i class="fas fa-play"></i>
            <span data-translate="play">Play</span>
        </button>
        <button class="context-menu-btn" onclick="closeContextMenu()">
            <i class="fas fa-times"></i>
            <span data-translate="close">Close</span>
        </button>
    </div>
</div>

<div id="contextMenuOverlay" class="context-menu-overlay" style="display: none;" onclick="hideContextMenu()"></div>

<script>
let selectedMediaElement = null;
let selectedMediaPath = '';
let hoverAudio = null;
let hoverVideo = null;
let userInteracted = false;    
let imageSwitchTimer = null;  
let autoNextEnabled = true;   
let currentMediaList = [];    
let currentMediaIndex = -1;

function handleThumbError(img) {
    img.style.display = 'none';
    const thumb = img.parentElement;
    if (thumb) {
        thumb.innerHTML = '<i class="fas fa-image"></i>';
    }
}
    
let currentMedia = {
    type: null,
    src: null,
    path: null,
    ext: null,
    wasPlaying: false  
};

function showSection(sectionId) {
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    const navItem = document.querySelector(`.nav-item[onclick="showSection('${sectionId}')"]`);
    if (navItem) {
        navItem.classList.add('active');
    }
    
    document.querySelectorAll('.grid-section').forEach(section => {
        section.style.display = 'none';
    });
    const targetSection = document.getElementById(sectionId + 'Section');
    if (targetSection) {
        targetSection.style.display = 'block';
    }
    
    document.querySelector('.media-grid-container').scrollTop = 0;
}
    
function playMedia(filePath) {    
    filePath = filePath.trim();
    
    const fileName = filePath.split('/').pop();
    const fileExt = fileName.split('.').pop().toLowerCase();
    
    const previewUrl = `?preview=1&path=${encodeURIComponent(filePath)}`;
    
    const audioPlayer = document.getElementById('audioPlayer');
    const videoPlayer = document.getElementById('videoPlayer');
    const imageViewer = document.getElementById('imageViewer');
    const playError = document.getElementById('playError');
    const playerArea = document.getElementById('playerArea');
    const playerTitle = document.getElementById('playerTitle');
    
    if (imageSwitchTimer) {
        clearInterval(imageSwitchTimer);
        imageSwitchTimer = null;
    }
    
    audioPlayer.style.display = 'none';
    videoPlayer.style.display = 'none';
    imageViewer.style.display = 'none';
    playError.style.display = 'none';
    
    audioPlayer.pause();
    videoPlayer.pause();
    
    playerTitle.innerHTML = `<i class="fas fa-play"></i>${fileName}`;
    
    const musicExts = ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac'];
    const videoExts = ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm'];
    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
    
    audioPlayer.src = '';
    videoPlayer.src = '';
    imageViewer.src = '';
    
    audioPlayer.onerror = null;
    videoPlayer.onerror = null;
    imageViewer.onerror = null;
    audioPlayer.onended = null;  
    videoPlayer.onended = null;  
    
    function handleMediaError(element, type) {
        return function(e) {
            element.style.display = 'none';
            playError.style.display = 'block';
            playerArea.classList.add('active');
            currentMedia = { type: null, src: null, path: null, ext: null, wasPlaying: false };
        };
    }
    
    if (musicExts.includes(fileExt)) {
        audioPlayer.onerror = handleMediaError(audioPlayer, translations['audio'] || 'Audio');
        audioPlayer.onended = function() {
            if (autoNextEnabled) {
                playNextMedia();
            }
        };
        audioPlayer.src = previewUrl;
        audioPlayer.load();
        audioPlayer.style.display = 'block';
        audioPlayer.play().catch(e => {
            audioPlayer.style.display = 'none';
            playError.style.display = 'block';
        });
        currentMedia = { type: 'audio', src: previewUrl, path: filePath, ext: fileExt, wasPlaying: false };
        
        updateCurrentMediaList('music', filePath);
    } 
    else if (videoExts.includes(fileExt)) {
        videoPlayer.onerror = handleMediaError(videoPlayer, translations['video'] || 'Video');
        videoPlayer.onended = function() {
            if (autoNextEnabled) {
                playNextMedia();
            }
        };
        videoPlayer.src = previewUrl;
        videoPlayer.load();
        videoPlayer.style.display = 'block';
        videoPlayer.play().catch(e => {
            videoPlayer.style.display = 'none';
            playError.style.display = 'block';
        });
        currentMedia = { type: 'video', src: previewUrl, path: filePath, ext: fileExt, wasPlaying: false };
        
        updateCurrentMediaList('video', filePath);
    } 
    else if (imageExts.includes(fileExt)) {
        imageViewer.onerror = handleMediaError(imageViewer, translations['image'] || 'Image');
        imageViewer.src = previewUrl;
        imageViewer.style.display = 'block';
        currentMedia = { type: 'image', src: previewUrl, path: filePath, ext: fileExt, wasPlaying: false };
        
        updateCurrentMediaList('image', filePath);
        
        if (autoNextEnabled) {
            startImageAutoSwitch();
        }
    } else {
        playError.style.display = 'block';
        playerArea.classList.add('active');
    }
    
    playerArea.classList.add('active');
    
    saveToRecent(filePath);
}
    
function updateCurrentMediaList(category, filePath) {
    try {
        const mediaLists = {
            'music': <?php echo json_encode(array_column($media['music'], 'path')); ?>,
            'video': <?php echo json_encode(array_column($media['video'], 'path')); ?>,
            'image': <?php echo json_encode(array_column($media['image'], 'path')); ?>
        };
        
        currentMediaList = mediaLists[category] || [];
        currentMediaIndex = currentMediaList.indexOf(filePath);
        
    } catch (e) {
        currentMediaList = [];
        currentMediaIndex = -1;
    }
}
    
function startImageAutoSwitch() {
    if (imageSwitchTimer) {
        clearInterval(imageSwitchTimer);
    }
    
    if (!autoNextEnabled || currentMediaList.length < 2) {
        return;
    }
        
    imageSwitchTimer = setInterval(() => {
        playNextMedia();
    }, 5000);
}
    
function playNextMedia() {
    if (!autoNextEnabled) {
        return;
    }

    if (currentMediaList.length === 0 || currentMediaIndex === -1) {
        return;
    }
    
    const nextIndex = (currentMediaIndex + 1) % currentMediaList.length;
    const nextFilePath = currentMediaList[nextIndex];
    
    if (nextFilePath) {
        playMedia(nextFilePath);
    }
}
    
function playPreviousMedia() {
    if (!autoNextEnabled) {
        return;
    }

    if (currentMediaList.length === 0 || currentMediaIndex === -1) {
        return;
    }
    
    const prevIndex = (currentMediaIndex - 1 + currentMediaList.length) % currentMediaList.length;
    const prevFilePath = currentMediaList[prevIndex];
    
    if (prevFilePath) {
        playMedia(prevFilePath);
    }
}
    
function toggleAutoNext() {
    autoNextEnabled = !autoNextEnabled;
    const toggleBtn = document.getElementById('autoNextToggle');
    if (toggleBtn) {
        toggleBtn.innerHTML = autoNextEnabled ? 
            `<i class="fas fa-toggle-on"></i> ${translations['auto_play'] || 'Auto Play'}` : 
            `<i class="fas fa-toggle-off"></i> ${translations['auto_play'] || 'Auto Play'}`;
    }
    
    showLogMessage(autoNextEnabled ? 
        (translations['auto_play_enabled'] || 'Auto play enabled') : 
        (translations['auto_play_disabled'] || 'Auto play disabled'));
    
    if (currentMedia.type === 'image') {
        if (autoNextEnabled && currentMediaList.length > 1) {
            startImageAutoSwitch();
        } else {
            if (imageSwitchTimer) {
                clearInterval(imageSwitchTimer);
                imageSwitchTimer = null;
            }
        }
    }
}
    
function initAutoPlayToggle() {
    const actions = document.querySelector('.actions');
    if (actions) {
        const toggleBtn = document.createElement('button');
        toggleBtn.id = 'autoNextToggle';
        toggleBtn.className = 'action-btn';
        const icon = autoNextEnabled ? 'fa-toggle-on' : 'fa-toggle-off';
        toggleBtn.innerHTML = `<i class="fas ${icon}"></i> <span>${translations['auto_play'] || 'Auto Play'}</span>`;
        toggleBtn.onclick = toggleAutoNext;
        actions.insertBefore(toggleBtn, actions.firstChild);
    }
}
    
function saveToRecent(filePath) {
    try {
        let recent = JSON.parse(localStorage.getItem('recent_media') || '[]');
        
        recent = recent.filter(f => f !== filePath);
        
        recent.unshift(filePath);
        
        if (recent.length > <?= $RECENT_MAX ?>) {
            recent = recent.slice(0, <?= $RECENT_MAX ?>);
        }
        
        localStorage.setItem('recent_media', JSON.stringify(recent));
        
        updateRecentList();
    } catch (e) {
    }
}
    
function updateRecentList() {
    try {
        const recent = JSON.parse(localStorage.getItem('recent_media') || '[]');
        const recentList = document.getElementById('recentList');
        
        if (!recentList) return;
        
        if (recent.length === 0) {
            recentList.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <p style="margin-top: 15px;">${translations['no_playback_history'] || 'No playback history'}</p>
                </div>`;
            return;
        }
        
        const musicExts = ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac'];
        const videoExts = ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm'];
        
        recentList.innerHTML = recent.slice(0, 20).map(file => {
            const ext = file.split('.').pop().toLowerCase();
            let icon = 'fas fa-image';
            if (musicExts.includes(ext)) icon = 'fas fa-music';
            else if (videoExts.includes(ext)) icon = 'fas fa-video';
            
            const safePath = file.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const safeName = file.split('/').pop().replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const safeDir = file.split('/').slice(0, -1).join('/').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            
            return `
            <div class="recent-item" onclick="playMedia('${safePath}')">
                <div class="recent-icon">
                    <i class="${icon}"></i>
                </div>
                <div class="recent-info">
                    <div class="recent-name">${safeName}</div>
                    <div class="recent-path">${safeDir}</div>
                </div>
            </div>`;
        }).join('');
    } catch (e) {
    }
}
    
function closePlayer() {
    const playerArea = document.getElementById('playerArea');
    const audioPlayer = document.getElementById('audioPlayer');
    const videoPlayer = document.getElementById('videoPlayer');
    
    if (imageSwitchTimer) {
        clearInterval(imageSwitchTimer);
        imageSwitchTimer = null;
    }
    
    playerArea.classList.remove('active');
    audioPlayer.pause();
    videoPlayer.pause();
}
    
function toggleFullscreenPlayer() {
    const fullscreenPlayer = document.getElementById('fullscreenPlayer');
    const contentArea = document.getElementById('contentArea');
    const playerArea = document.getElementById('playerArea');
    
    if (fullscreenPlayer.classList.contains('active')) {
        closeFullscreenPlayer();
    } else {
        if (!currentMedia.src) return;
        
        const audioPlayer = document.getElementById('audioPlayer');
        const videoPlayer = document.getElementById('videoPlayer');
        const fullscreenAudio = document.getElementById('fullscreenAudio');
        const fullscreenVideo = document.getElementById('fullscreenVideo');
        const fullscreenImage = document.getElementById('fullscreenImage');
        const fullscreenPlayError = document.getElementById('fullscreenPlayError');
        const fullscreenTitle = document.getElementById('fullscreenTitle');
        
        if (currentMedia.type === 'audio') {
            currentMedia.wasPlaying = !audioPlayer.paused;
            audioPlayer.pause();
        } else if (currentMedia.type === 'video') {
            currentMedia.wasPlaying = !videoPlayer.paused;
            videoPlayer.pause();
        }
        
        fullscreenAudio.style.display = 'none';
        fullscreenVideo.style.display = 'none';
        fullscreenImage.style.display = 'none';
        fullscreenPlayError.style.display = 'none';
        
        fullscreenAudio.pause();
        fullscreenVideo.pause();
        
        fullscreenAudio.src = '';
        fullscreenVideo.src = '';
        fullscreenImage.src = '';
        
        if (currentMedia.type === 'audio') {
            fullscreenAudio.src = currentMedia.src;
            fullscreenAudio.load();
            fullscreenAudio.style.display = 'block';
            if (currentMedia.wasPlaying) {
                fullscreenAudio.play();
            }
        } else if (currentMedia.type === 'video') {
            fullscreenVideo.src = currentMedia.src;
            fullscreenVideo.load();
            fullscreenVideo.style.display = 'block';
            if (currentMedia.wasPlaying) {
                fullscreenVideo.play();
            }
        } else if (currentMedia.type === 'image') {
            fullscreenImage.src = currentMedia.src;
            fullscreenImage.style.display = 'block';
        }
        
        contentArea.classList.add('fullscreen');
        playerArea.classList.remove('active');
        fullscreenPlayer.classList.add('active');
        
        const fileName = currentMedia.path.split('/').pop();
        fullscreenTitle.innerHTML = `<i class="fas fa-play"></i>${fileName}`;
    }
}
    
function closeFullscreenPlayer() {
    const fullscreenPlayer = document.getElementById('fullscreenPlayer');
    const contentArea = document.getElementById('contentArea');
    const audioPlayer = document.getElementById('audioPlayer');
    const videoPlayer = document.getElementById('videoPlayer');
    const fullscreenAudio = document.getElementById('fullscreenAudio');
    const fullscreenVideo = document.getElementById('fullscreenVideo');
    
    fullscreenAudio.pause();
    fullscreenVideo.pause();
    
    fullscreenPlayer.classList.remove('active');
    contentArea.classList.remove('fullscreen');
    
    if (currentMedia.src) {
        const playerArea = document.getElementById('playerArea');
        playerArea.classList.add('active');
        
        if (currentMedia.type === 'audio') {
            if (currentMedia.wasPlaying) {
                audioPlayer.play();
            }
        } else if (currentMedia.type === 'video') {
            if (currentMedia.wasPlaying) {
                videoPlayer.play();
            }
        }
    }
}
    
function refreshMedia() {
    updateRecentList();
    window.location.reload();
}
    
function toggleFullscreen() {
    const fullscreenPlayer = document.getElementById('fullscreenPlayer');
    
    if (fullscreenPlayer.classList.contains('active')) {
        closeFullscreenPlayer();
        return;
    }
    
    if (currentMedia.src) {
        const audioPlayer = document.getElementById('audioPlayer');
        const videoPlayer = document.getElementById('videoPlayer');
        
        if (currentMedia.type === 'audio') {
            currentMedia.wasPlaying = !audioPlayer.paused;
            audioPlayer.pause();
        } else if (currentMedia.type === 'video') {
            currentMedia.wasPlaying = !videoPlayer.paused;
            videoPlayer.pause();
        }
        
        activateFullscreenPlayer();
    } else {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
            });
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }
}

function activateFullscreenPlayer() {
    const fullscreenPlayer = document.getElementById('fullscreenPlayer');
    const contentArea = document.getElementById('contentArea');
    const playerArea = document.getElementById('playerArea');
    const fullscreenAudio = document.getElementById('fullscreenAudio');
    const fullscreenVideo = document.getElementById('fullscreenVideo');
    const fullscreenImage = document.getElementById('fullscreenImage');
    const fullscreenTitle = document.getElementById('fullscreenTitle');
    
    contentArea.classList.add('fullscreen');
    playerArea.classList.remove('active');
    fullscreenPlayer.classList.add('active');
    
    fullscreenAudio.style.display = 'none';
    fullscreenVideo.style.display = 'none';
    fullscreenImage.style.display = 'none';
    
    fullscreenAudio.pause();
    fullscreenVideo.pause();
    
    if (currentMedia.type === 'audio') {
        fullscreenAudio.src = currentMedia.src;
        fullscreenAudio.load();
        fullscreenAudio.style.display = 'block';
        if (currentMedia.wasPlaying) {
            fullscreenAudio.play();
        }
    } else if (currentMedia.type === 'video') {
        fullscreenVideo.src = currentMedia.src;
        fullscreenVideo.load();
        fullscreenVideo.style.display = 'block';
        if (currentMedia.wasPlaying) {
            fullscreenVideo.play();
        }
    } else if (currentMedia.type === 'image') {
        fullscreenImage.src = currentMedia.src;
        fullscreenImage.style.display = 'block';
    }
    
    const fileName = currentMedia.path.split('/').pop();
    fullscreenTitle.innerHTML = `<i class="fas fa-play"></i>${fileName}`;
    
    fullscreenPlayer.style.zIndex = '9999';
}

function closeFullscreenPlayer() {
    const fullscreenPlayer = document.getElementById('fullscreenPlayer');
    const contentArea = document.getElementById('contentArea');
    const audioPlayer = document.getElementById('audioPlayer');
    const videoPlayer = document.getElementById('videoPlayer');
    const fullscreenAudio = document.getElementById('fullscreenAudio');
    const fullscreenVideo = document.getElementById('fullscreenVideo');
    
    fullscreenAudio.pause();
    fullscreenVideo.pause();
    
    fullscreenPlayer.classList.remove('active');
    contentArea.classList.remove('fullscreen');
    
    if (currentMedia.src) {
        const playerArea = document.getElementById('playerArea');
        playerArea.classList.add('active');
        
        if (currentMedia.type === 'audio' && currentMedia.wasPlaying) {
            audioPlayer.play();
        } else if (currentMedia.type === 'video' && currentMedia.wasPlaying) {
            videoPlayer.play();
        }
    }
}

function showMediaInfo(event, element) {
    event.preventDefault();
    event.stopPropagation();
    
    selectedMediaElement = element;
    selectedMediaPath = element.getAttribute('data-src') ? 
        element.getAttribute('data-src').split('path=')[1] : '';
    
    const filename = element.getAttribute('data-filename') || 'Unknown';
    const filesize = element.getAttribute('data-filesize') || 'Unknown';
    const type = element.getAttribute('data-type') || 'Unknown';
    const duration = element.getAttribute('data-duration') || 'N/A';
    const resolution = element.getAttribute('data-resolution') || 'N/A';
    const bitrate = element.getAttribute('data-bitrate') || 'N/A';
    const fileExt = element.getAttribute('data-ext') || 'Unknown';
    
    const fullPath = element.getAttribute('data-src') ? 
        decodeURIComponent(element.getAttribute('data-src').split('path=')[1]) : 'Unknown';
        document.getElementById('infoFilename').textContent = filename;
        document.getElementById('infoFilesize').textContent = filesize;
        document.getElementById('infoType').textContent = `${type} (${fileExt})`;
        document.getElementById('infoDuration').textContent = duration;
        document.getElementById('infoResolution').textContent = resolution;
        document.getElementById('infoBitrate').textContent = bitrate;
        document.getElementById('infoPath').textContent = fullPath;
   
    const durationItem = document.getElementById('durationItem');
    const resolutionItem = document.getElementById('resolutionItem');
    const bitrateItem = document.getElementById('bitrateItem');
    
    if (type === 'audio') {
        durationItem.style.display = 'flex';
        resolutionItem.style.display = 'none';
        bitrateItem.style.display = 'flex';
    } else if (type === 'video') {
        durationItem.style.display = 'flex';
        resolutionItem.style.display = 'flex';
        bitrateItem.style.display = 'flex';
    } else if (type === 'image') {
        durationItem.style.display = 'none';
        resolutionItem.style.display = 'flex';
        bitrateItem.style.display = 'none';
    }
    
    document.getElementById('mediaContextMenu').style.display = 'block';
    document.getElementById('contextMenuOverlay').style.display = 'block';
}

function hideContextMenu() {
    document.getElementById('mediaContextMenu').style.display = 'none';
    document.getElementById('contextMenuOverlay').style.display = 'none';
    selectedMediaElement = null;
    selectedMediaPath = '';
}

function playSelectedMedia() {
    if (selectedMediaPath) {
        const safePath = decodeURIComponent(selectedMediaPath);
        playMedia(safePath);
    } else if (selectedMediaElement) {
        const clickEvent = new MouseEvent('click', {
            bubbles: true,
            cancelable: true,
            view: window
        });
        selectedMediaElement.dispatchEvent(clickEvent);
    }
    hideContextMenu();
}

function closeContextMenu() {
    hideContextMenu();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideContextMenu();
    }
});

document.addEventListener('contextmenu', function(e) {
    const contextMenu = document.getElementById('mediaContextMenu');
    if (contextMenu.style.display === 'block') {
        e.preventDefault();
    }
}); 

document.addEventListener('click', function() {
    if (!userInteracted) {
        userInteracted = true;
    }
});

function initHoverPlay() {
    const items = document.querySelectorAll('.hover-playable');
    
    items.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const type = this.getAttribute('data-type');
            const src = this.getAttribute('data-src');
            
            stopHoverPlay();
            
            if (type === 'audio') {
                hoverAudio = new Audio(src);
                hoverAudio.volume = 0.5;
            } 
            else if (type === 'video' && userInteracted) {
                const thumb = this.querySelector('.media-thumb');
                if (!thumb) return;
                
                hoverVideoContainer = document.createElement('div');
                hoverVideoContainer.className = 'hover-video-container';
                hoverVideoContainer.style.cssText = `
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 10;
                    border-radius: 8px;
                    overflow: hidden;
                    background: #000;
                `;
                
                hoverVideo = document.createElement('video');
                hoverVideo.src = src;
                hoverVideo.controls = false;
                hoverVideo.autoplay = true;
                hoverVideo.muted = false;
                hoverVideo.playsInline = true;
                hoverVideo.style.cssText = `
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                `;
                
                hoverVideoContainer.appendChild(hoverVideo);
                thumb.appendChild(hoverVideoContainer);
                
                const icon = thumb.querySelector('i');
                if (icon) icon.style.opacity = '0.3';
                
                hoverVideo.play().catch(e => {
                    if (hoverVideoContainer && hoverVideoContainer.parentNode) {
                        hoverVideoContainer.parentNode.removeChild(hoverVideoContainer);
                    }
                });
            }
        });
        
        item.addEventListener('mouseleave', function() {
            stopHoverPlay();
        });
    });
}

function stopHoverPlay() {
    if (hoverAudio) {
        hoverAudio.pause();
        hoverAudio.currentTime = 0;
        hoverAudio = null;
    }
    
    if (hoverVideo) {
        hoverVideo.pause();
        hoverVideo.currentTime = 0;
        
        if (hoverVideoContainer && hoverVideoContainer.parentNode) {
            hoverVideoContainer.parentNode.removeChild(hoverVideoContainer);
            
            const icon = hoverVideoContainer.parentNode.querySelector('i');
            if (icon) icon.style.opacity = '1';
        }
        
        hoverVideo = null;
        hoverVideoContainer = null;
    }
}

let cpuChart = null;
let memChart = null;
let cpuData = [];
let memData = [];
let timeLabels = [];
let maxDataPoints = 30;
let networkHistory = [];
let systemMonitorInterval = null;
let lastNetworkRx = 0;
let lastNetworkTx = 0;

async function updateSystemInfo() {
    try {
        const response = await fetch('?ajax=1');
        const data = await response.json();
        
        if (data.success) {
            let cpuUsage = parseFloat(data.cpu_usage) || 0;
            cpuUsage = Math.max(0, Math.min(100, cpuUsage));
            
            updateElementText('cpuUsageDisplay', cpuUsage.toFixed(1) + '%');
            updateElementText('cpuUsageValue', cpuUsage.toFixed(1) + '%');
            
            const cpuBar = document.getElementById('cpuUsageBar');
            if (cpuBar) {
                cpuBar.style.width = Math.min(cpuUsage, 100) + '%';
            }
            
            updateElementText('cpuCoresValue', data.cpu_cores || '--');
            
            updateElementText('cpuFreqDisplay', data.cpu_freq || '--');

            if (data.openwrt_version) {
                updateElementText('openwrtVersionDisplay', data.openwrt_version || 'Unknown');
            }
            
            if (data.kernel_version) {
                updateElementText('kernelVersionDisplay', data.kernel_version || 'Unknown');
            }
            
            if (data.board_model) {
                updateElementText('boardModelDisplay', data.board_model || 'Unknown');
            }
            
            const memUsage = parseFloat(data.mem_usage) || 0;
            updateElementText('memUsageDisplay', memUsage.toFixed(1) + '%');
            updateElementText('memUsageValue', memUsage.toFixed(1) + '%');
            
            const memBar = document.getElementById('memUsageBar');
            if (memBar) memBar.style.width = Math.min(memUsage, 100) + '%';
            
            if (data.mem_total !== undefined && data.mem_used !== undefined) {
                const cleanNumber = (str) => {
                    if (typeof str === 'string') {
                        return parseFloat(str.replace(/,/g, ''));
                    }
                    return parseFloat(str || 0);
                };
    
                const memUsed = cleanNumber(data.mem_used);
                const memTotal = cleanNumber(data.mem_total);
    
                updateElementText('memUsedDisplay', memUsed.toFixed(1));
                updateElementText('memTotalDisplay', memTotal.toFixed(1));
            }    
     
            if (cpuChart && memChart) {
                updateChartData(cpuUsage, memUsage);
            }
            
            if (data.cpu_temp && data.cpu_temp !== '--') {
                updateElementText('cpuTempDisplay', data.cpu_temp + '°C');
                const tempElement = document.getElementById('cpuTempDisplay');
                const temp = parseFloat(data.cpu_temp);
                
                if (temp > 70) {
                    tempElement.style.color = '#F44336';
                } else if (temp > 60) {
                    tempElement.style.color = '#FF9800';
                } else {
                    tempElement.style.color = '#4CAF50';
                }
            }
            
            updateElementText('processCountDisplay', data.process_count || '--');
            
            const uptimeElement = document.getElementById('uptimeDisplay');
            if (uptimeElement && data.uptime) {
                let uptimeText = data.uptime;
    
                uptimeText = uptimeText.replace(/days/gi, translations['uptime_days'] || 'days')
                                                  .replace(/hours/gi, translations['uptime_hours'] || 'hours')
                                                  .replace(/minutes/gi, translations['minutes'] || 'minutes')
                                                  .replace(/seconds/gi, translations['seconds'] || 'seconds');
                uptimeElement.textContent = uptimeText;
            }
            
            updateElementText('loadAvgDisplay', data.load_avg || '--');
            
            updateElementText('timeValue', data.system_time || '--:--:--');
            updateElementText('timezoneDisplay', data.timezone || 'UTC');
            
            if (data.network_rx !== undefined && data.network_tx !== undefined) {
                updateNetworkSpeed(data.network_rx, data.network_tx);
            }
        }
    } catch (error) {
        console.error('Failed to update system info:', error);
        showErrorState();
    }
}

function updateElementText(elementId, text) {
    const element = document.getElementById(elementId);
    if (element) element.textContent = text;
}

function showErrorState() {
    updateElementText('cpuUsageDisplay', '--%');
    updateElementText('memUsageDisplay', '--%');
    updateElementText('cpuTempDisplay', '--°C');
    updateElementText('processCountDisplay', '--');
    updateElementText('uptimeDisplay', '--:--:--');
    updateElementText('loadAvgDisplay', '--');
    updateElementText('timeValue', '--:--:--');
    updateElementText('timezoneDisplay', '--');
}

function initCharts() {
    const cpuCtx = document.getElementById('cpuChartCanvas');
    const memCtx = document.getElementById('memChartCanvas');
    
    if (!cpuCtx || !memCtx) {
        console.log('Chart canvas not found');
        return;
    }
    
    cpuData = [];
    memData = [];
    timeLabels = [];
    
    for (let i = 0; i < maxDataPoints; i++) {
        cpuData.push(0);
        memData.push(0);
        timeLabels.push('');
    }
    
    try {
        cpuChart = new Chart(cpuCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'CPU Usage',
                    data: cpuData,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `CPU: ${ctx.parsed.y.toFixed(1)}%`
                        }
                    }
                },
                scales: {
                    x: { display: false },
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            color: '#888',
                            callback: (value) => `${value}%`
                        },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    }
                }
            }
        });
        
        memChart = new Chart(memCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'Memory Usage',
                    data: memData,
                    borderColor: '#2196F3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `Memory: ${ctx.parsed.y.toFixed(1)}%`
                        }
                    }
                },
                scales: {
                    x: { display: false },
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            color: '#888',
                            callback: (value) => `${value}%`
                        },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
}

function updateChartData(cpuValue, memValue) {
    if (!cpuChart || !memChart) return;
    
    cpuData.push(cpuValue);
    memData.push(memValue);
    
    if (cpuData.length > maxDataPoints) {
        cpuData.shift();
        memData.shift();
    }
    
    cpuChart.data.datasets[0].data = cpuData;
    memChart.data.datasets[0].data = memData;
    
    cpuChart.update('none');
    memChart.update('none');
}

function updateNetworkSpeed(rx, tx) {
    const networkElement = document.getElementById('networkSpeedDisplay');
    if (!networkElement) return;
    
    const currentTime = Date.now();
    
    if (typeof updateNetworkSpeed.lastTime === 'undefined') {
        updateNetworkSpeed.lastTime = currentTime;
        updateNetworkSpeed.lastRx = rx;
        updateNetworkSpeed.lastTx = tx;
        return;
    }
    
    const timeDiff = (currentTime - updateNetworkSpeed.lastTime) / 1000;
    
    if (timeDiff > 0) {
        const rxSpeed = (rx - updateNetworkSpeed.lastRx) / timeDiff;
        const txSpeed = (tx - updateNetworkSpeed.lastTx) / timeDiff;
        const totalSpeed = rxSpeed + txSpeed;
        
        let displayText, color;
        
        if (totalSpeed < 1024) {
            displayText = totalSpeed.toFixed(1) + ' B/s';
            color = '#4CAF50';
        } else if (totalSpeed < 1024 * 1024) {
            displayText = (totalSpeed / 1024).toFixed(1) + ' KB/s';
            color = '#2196F3';
        } else {
            displayText = (totalSpeed / (1024 * 1024)).toFixed(1) + ' MB/s';
            color = '#E91E63';
        }
        
        networkElement.innerHTML = `${displayText}<br>
                                   <span style="font-size: 0.8rem; color: #888;">
                                   ↓${(rxSpeed < 1024 ? rxSpeed.toFixed(1) + ' B' : 
                                       rxSpeed < 1024 * 1024 ? (rxSpeed / 1024).toFixed(1) + ' KB' : 
                                       (rxSpeed / (1024 * 1024)).toFixed(1) + ' MB')}/s ↑
                                   ${(txSpeed < 1024 ? txSpeed.toFixed(1) + ' B' : 
                                       txSpeed < 1024 * 1024 ? (txSpeed / 1024).toFixed(1) + ' KB' : 
                                       (txSpeed / (1024 * 1024)).toFixed(1) + ' MB')}/s</span>`;
        networkElement.style.color = color;
        
        updateNetworkSpeed.lastTime = currentTime;
        updateNetworkSpeed.lastRx = rx;
        updateNetworkSpeed.lastTx = tx;
    }
}
updateNetworkSpeed.lastTime = undefined;
updateNetworkSpeed.lastRx = 0;
updateNetworkSpeed.lastTx = 0;

function startSystemMonitoring() {
    stopSystemMonitoring();
    
    if (document.getElementById('cpuChartCanvas') && typeof Chart !== 'undefined') {
        initCharts();
    } else if (typeof Chart === 'undefined') {
        const script = document.createElement('script');
        script.src = '/luci-static/spectra/js/chart.js';
        script.onload = initCharts;
        document.head.appendChild(script);
    }
    
    updateSystemInfo();
    
    systemMonitorInterval = setInterval(updateSystemInfo, 1000);
}

function stopSystemMonitoring() {
    if (systemMonitorInterval) {
        clearInterval(systemMonitorInterval);
        systemMonitorInterval = null;
    }
}

function showSection(sectionId) {
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    const navItem = document.querySelector(`.nav-item[onclick="showSection('${sectionId}')"]`);
    if (navItem) {
        navItem.classList.add('active');
    }
    
    document.querySelectorAll('.grid-section').forEach(section => {
        section.style.display = 'none';
    });
    const targetSection = document.getElementById(sectionId + 'Section');
    if (targetSection) {
        targetSection.style.display = 'block';
    }
    
    document.querySelector('.media-grid-container').scrollTop = 0;
    
    if (sectionId === 'home') {
        startSystemMonitoring();
    } else {
        stopSystemMonitoring();
    }
}
    
document.addEventListener('DOMContentLoaded', function() {
    updateRecentList();
    initHoverPlay();
    startSystemMonitoring();
    initAutoPlayToggle();  

    if (typeof Chart !== 'undefined') {
        startSystemMonitoring();
    } else {
        const script = document.createElement('script');
        script.src = '/luci-static/spectra/js/chart.js';
        script.onload = function() {
            startSystemMonitoring();
        };
        document.head.appendChild(script);
    }


    document.addEventListener('click', function(e) {
        const contextMenu = document.getElementById('mediaContextMenu');
        const overlay = document.getElementById('contextMenuOverlay');
        if (contextMenu.style.display === 'block' && 
            !contextMenu.contains(e.target) && 
            !overlay.contains(e.target)) {
            hideContextMenu();
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (document.getElementById('fullscreenPlayer').classList.contains('active')) {
                closeFullscreenPlayer();
            } else {
                closePlayer();
            }
        }
        if (e.key === ' ' && !e.target.matches('input, textarea, select, button')) {
            e.preventDefault();
            const audioPlayer = document.getElementById('audioPlayer');
            const videoPlayer = document.getElementById('videoPlayer');
            const fullscreenAudio = document.getElementById('fullscreenAudio');
            const fullscreenVideo = document.getElementById('fullscreenVideo');
            
            if (fullscreenAudio.style.display === 'block') {
                if (fullscreenAudio.paused) fullscreenAudio.play();
                else fullscreenAudio.pause();
            } else if (fullscreenVideo.style.display === 'block') {
                if (fullscreenVideo.paused) fullscreenVideo.play();
                else fullscreenVideo.pause();
            } else if (audioPlayer.style.display === 'block') {
                if (audioPlayer.paused) audioPlayer.play();
                else audioPlayer.pause();
            } else if (videoPlayer.style.display === 'block') {
                if (videoPlayer.paused) videoPlayer.play();
                else videoPlayer.pause();
            }
        }
        if (e.key === 'f' || e.key === 'F') {
            e.preventDefault();
            toggleFullscreen();
        }
        if (e.key === 'ArrowRight') {
            e.preventDefault();
            playNextMedia();
        }
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            playPreviousMedia();
        }
        if (e.key === 'a' || e.key === 'A') {
            e.preventDefault();
            toggleAutoNext();
        }
    });
});
</script>

