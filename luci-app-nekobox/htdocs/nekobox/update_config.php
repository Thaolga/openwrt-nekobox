<?php
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 300);

$logMessages = [];

function logMessage($filename, $message) {
    global $logMessages;
    $timestamp = date('H:i:s', strtotime('+8 hours'));
    $logMessages[] = "[$timestamp] $filename: $message";
}

function downloadFile($url, $destination, $retries = 3, $timeout = 30) {
    $attempt = 1;
    
    while ($attempt <= $retries) {
        try {
            $dir = dirname($destination);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);

            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($content === false) {
                throw new Exception("Download failed: " . curl_error($ch));
            }
            
            if ($httpCode !== 200) {
                throw new Exception("HTTP response code error: $httpCode");
            }
            
            if (file_put_contents($destination, $content) === false) {
                throw new Exception("Unable to save file to $destination");
            }
            
            curl_close($ch);
            logMessage(basename($destination), "Download and save successful");
            return true;
            
        } catch (Exception $e) {
            logMessage(basename($destination), "Attempt $attempt failed: " . $e->getMessage());
            curl_close($ch);
            
            if ($attempt === $retries) {
                logMessage(basename($destination), "All download attempts failed");
                return false;
            }
            
            $attempt++;
            sleep(2);
        }
    }
    
    return false;
}

echo "Start updating configuration file...\n";

$urls = [
    "https://raw.githubusercontent.com/Thaolga/openwrt-nekobox/nekobox/luci-app-nekobox/root/etc/neko/config/mihomo.yaml" => "/etc/neko/config/mihomo.yaml",
    "https://raw.githubusercontent.com/Thaolga/openwrt-nekobox/nekobox/luci-app-nekobox/root/etc/neko/config/Puernya.json" => "/etc/neko/config/Puernya.json"
];

foreach ($urls as $url => $destination) {
    logMessage(basename($destination), "Start downloading from $url");
    
    if (downloadFile($url, $destination)) {
        logMessage(basename($destination), "File update successful");
    } else {
        logMessage(basename($destination), "File update failed");
    }
}

echo "\nConfiguration file update completed！\n\n";

foreach ($logMessages as $message) {
    echo $message . "\n";
}
?>