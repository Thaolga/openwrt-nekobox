<?php
$repoOwner = 'Thaolga';
$repoName = 'openwrt-nekobox';
$releaseTag = '1.8.8'; 
$packagePattern = '/^luci-theme-spectra_(.+)_all\.ipk$/';

$latestVersion = null;
$downloadUrl = null;

try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/{$repoOwner}/{$repoName}/releases/tags/{$releaseTag}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP');
    $response = curl_exec($ch);
        
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
        $releaseData = json_decode($response, true);
        foreach ($releaseData['assets'] as $asset) {
            if (preg_match($packagePattern, $asset['name'], $matches)) {
                $latestVersion = $matches[1];
                $downloadUrl = $asset['browser_download_url'];
                break;
            }
        }
    }
    curl_close($ch);
} catch(Exception $e) {}

echo json_encode([
    'version' => $latestVersion,
    'url' => $downloadUrl,
]);
?>