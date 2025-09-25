<?php
function getCurrentVersion() {
    $packageName = 'luci-app-nekobox';
    $command = "opkg list-installed | grep $packageName";
    $output = shell_exec($command . ' 2>&1');

    if ($output === null || empty($output)) {
        return "Error";
    }

    $parts = explode(' - ', $output);
    if (count($parts) >= 2) {
        return cleanVersion($parts[1]);
    }

    return "Error";
}

function getLatestVersion() {
    $cacheFile = __DIR__ . '/lib/version_cache.json';
    $cacheTime = 300;

    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        if ($cacheData && isset($cacheData['version'], $cacheData['timestamp'])) {
            if (time() - $cacheData['timestamp'] < $cacheTime) {
                return $cacheData['version'];
            }
        }
    }

    $urls = [
        "https://api.github.com/repos/Thaolga/openwrt-nekobox/releases/latest",

        "https://ghproxy.com/https://api.github.com/repos/Thaolga/openwrt-nekobox/releases/latest",

        "https://data.jsdelivr.com/v1/package/gh/Thaolga/openwrt-nekobox"
    ];

    $latestVersion = "Error";

    foreach ($urls as $url) {
        $cmd = "curl -m 10 -s -H 'User-Agent: PHP' '$url'";
        $json = shell_exec($cmd);

        if ($json === null || empty($json)) {
            continue;
        }

        $data = json_decode($json, true);
        if (!$data) {
            continue;
        }

        if (isset($data['tag_name'])) {
            $latestVersion = cleanVersion($data['tag_name']);
            break;
        }

        if (isset($data['tags']['latest'])) {
            $latestVersion = cleanVersion($data['tags']['latest']);
            break;
        }
    }

    if ($latestVersion !== "Error") {
        file_put_contents($cacheFile, json_encode([
            'version' => $latestVersion,
            'timestamp' => time()
        ]));
    }

    return $latestVersion;
}

function cleanVersion($version) {
    $version = explode('-', $version)[0];
    return preg_replace('/[^0-9\.]/', '', $version);
}

$currentVersion = getCurrentVersion();
$latestVersion = getLatestVersion();

if ($currentVersion === "Error" || $latestVersion === "Error") {
    $response = [
        'currentVersion' => $currentVersion,
        'latestVersion' => $latestVersion,
        'hasUpdate' => false,
        'error' => 'Failed to fetch version information'
    ];
} else {
    $hasUpdate = (version_compare($currentVersion, $latestVersion, '<')) ? true : false;

    $response = [
        'currentVersion' => $currentVersion,
        'latestVersion' => $latestVersion,
        'hasUpdate' => $hasUpdate
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
