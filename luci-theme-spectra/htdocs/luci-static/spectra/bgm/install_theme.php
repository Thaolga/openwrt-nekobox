<?php
$repoOwner = 'Thaolga';
$repoName = 'openwrt-nekobox';
$releaseTag = '1.8.8';

$downloadUrlBase = "https://github.com/$repoOwner/$repoName/releases/download/$releaseTag/";

function fetchAssetsFromRelease($repoOwner, $repoName, $releaseTag) {
    $url = "https://api.github.com/repos/$repoOwner/$repoName/releases/tags/$releaseTag";
    $tempFile = '/tmp/github_release.json';

    $command = "wget -q --header='User-Agent: PHP' -O $tempFile $url";
    $result = executeCommand($command);

    if ($result['status'] !== 0) {
        return ['error' => "Failed to fetch release data: " . $result['output']];
    }

    $response = file_get_contents($tempFile);
    if ($response === false) {
        return ['error' => 'Failed to read temporary release data file'];
    }

    unlink($tempFile);

    $releaseData = json_decode($response, true);

    if (isset($releaseData['assets']) && count($releaseData['assets']) > 0) {
        foreach ($releaseData['assets'] as $asset) {
            if (preg_match('/luci-theme-spectra_([0-9A-Za-z.\-_]+)_all.ipk/', $asset['name'], $matches)) {
                return [
                    'url' => $asset['browser_download_url'],
                    'filename' => $asset['name']
                ];
            }
        }
    }

    return ['error' => 'No matching asset found'];
}

function executeCommand($command) {
    $output = [];
    $status = 0;
    exec($command, $output, $status);
    return ['status' => $status, 'output' => implode("\n", $output)];
}

$assetData = fetchAssetsFromRelease($repoOwner, $repoName, $releaseTag);
if (isset($assetData['error'])) {
    echo $assetData['error'];
    exit;
}

$latestFile = $assetData['filename'];
$downloadUrl = $assetData['url'];

$updateResult = executeCommand('opkg update');
if ($updateResult['status'] !== 0) {
    echo "Error during opkg update: " . $updateResult['output'];
    exit;
}

$installResult = executeCommand('opkg install wget grep sed');
if ($installResult['status'] !== 0) {
    echo "Error installing dependencies: " . $installResult['output'];
    exit;
}

$downloadResult = executeCommand("wget -O /tmp/$latestFile $downloadUrl");
if ($downloadResult['status'] !== 0) {
    echo "Error downloading the package: " . $downloadResult['output'];
    exit;
}

$installThemeResult = executeCommand("opkg install --force-reinstall /tmp/$latestFile");
if ($installThemeResult['status'] !== 0) {
    echo "Error installing the package: " . $installThemeResult['output'];
    exit;
}

$cleanupResult = executeCommand("rm -f /tmp/$latestFile");
if ($cleanupResult['status'] !== 0) {
    echo "Error cleaning up the temporary file: " . $cleanupResult['output'];
    exit;
}

echo "Installation complete!";
?>