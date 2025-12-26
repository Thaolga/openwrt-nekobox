<?php
$repoOwner = 'Thaolga';
$repoName = 'openwrt-nekobox';
$releaseTag = '1.8.8'; 
$packagePattern = '/^luci-theme-spectra_(.+)_all\.ipk$/';

$latestVersion = null;
$downloadUrl = null;
$currentInstalledVersion = null;
$needsUpdate = false;

function extractMainVersion($version) {
    $cleanVersion = explode('~', $version)[0];
    
    $parts = explode('.', $cleanVersion);
    
    if (isset($parts[0], $parts[1])) {
        return $parts[0] . '.' . $parts[1];
    }
    
    return $cleanVersion;
}

try {
    $installedPackages = shell_exec("opkg list-installed luci-theme-spectra 2>/dev/null");
    if ($installedPackages) {
        preg_match('/luci-theme-spectra\s+-\s+([0-9\.~a-zA-Z0-9]+)/', $installedPackages, $matches);
        if (isset($matches[1])) {
            $currentInstalledVersion = $matches[1];
        }
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/{$repoOwner}/{$repoName}/releases/tags/{$releaseTag}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode === 200) {
        $releaseData = json_decode($response, true);

        if (isset($releaseData['assets']) && is_array($releaseData['assets'])) {
            foreach ($releaseData['assets'] as $asset) {
                if (preg_match($packagePattern, $asset['name'], $matches)) {
                    $latestVersion = preg_replace('/\.([^.]+)$/', '~$1', $matches[1]);
                    
                    $downloadUrl = isset($asset['browser_download_url']) ? $asset['browser_download_url'] : null;

                    if ($currentInstalledVersion && $latestVersion) {
                        $currentMain = extractMainVersion($currentInstalledVersion);
                        $latestMain = extractMainVersion($latestVersion);

                        if (version_compare($latestMain, $currentMain, '>')) {
                            $needsUpdate = true;
                        }
                    }

                    break;
                }
            }
        }
    }
    
    curl_close($ch);

} catch (Exception $e) {
    //error_log("Theme update check error: " . $e->getMessage());
}

echo json_encode([
    'currentVersion' => $currentInstalledVersion,
    'version' => $latestVersion,
    'url' => $downloadUrl,
    'needsUpdate' => $needsUpdate
]);
?>