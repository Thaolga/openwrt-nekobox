<?php
set_time_limit(300);
ini_set('max_execution_time', 300);
header('Content-Type: text/plain');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

if (ob_get_level()) ob_end_clean();
ob_implicit_flush(true);

function executeCommand($command) {
    $output = [];
    $status = 0;
    exec($command . ' 2>&1', $output, $status);
    return ['status' => $status, 'output' => implode("\n", $output)];
}

function fetchAssetsFromRelease($repoOwner, $repoName, $releaseTag) {
    $url = "https://api.github.com/repos/$repoOwner/$repoName/releases/tags/$releaseTag";
    $tempFile = '/tmp/github_release.json';

    $command = "wget -q --header='User-Agent: PHP' -O $tempFile '$url' 2>/dev/null || curl -s -H 'User-Agent: PHP' -o $tempFile '$url' 2>/dev/null";
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

function logStep($message) {
    echo $message . "\n";
    flush();
    ob_flush();
    usleep(100000);
}

$repoOwner = 'Thaolga';
$repoName = 'openwrt-nekobox';
$releaseTag = '1.8.8';

logStep("🚀 Starting theme installation...");

logStep("📡 Fetching release information...");
$assetData = fetchAssetsFromRelease($repoOwner, $repoName, $releaseTag);
if (isset($assetData['error'])) {
    logStep("❌ Error: " . $assetData['error']);
    exit;
}

logStep("✅ Release info fetched successfully");
logStep("📦 Package: " . $assetData['filename']);

$latestFile = $assetData['filename'];
$downloadUrl = $assetData['url'];

logStep("🔄 Updating package list...");
$updateResult = executeCommand('opkg update');
if ($updateResult['status'] !== 0) {
    logStep("❌ Error during opkg update: " . $updateResult['output']);
    exit;
}
logStep("✅ Package list updated");

logStep("📥 Installing dependencies...");
$installResult = executeCommand('opkg install wget grep sed');
if ($installResult['status'] !== 0) {
    logStep("❌ Error installing dependencies: " . $installResult['output']);
    exit;
}
logStep("✅ Dependencies installed");

logStep("⬇️ Downloading theme package...");
$downloadResult = executeCommand("wget -O /tmp/$latestFile '$downloadUrl' 2>/dev/null || curl -s -L -o /tmp/$latestFile '$downloadUrl' 2>/dev/null");
if ($downloadResult['status'] !== 0 || !file_exists("/tmp/$latestFile")) {
    logStep("❌ Error downloading the package: " . $downloadResult['output']);
    exit;
}
logStep("✅ Package downloaded");

logStep("🔧 Installing theme...");
logStep("⏳ Installing theme package, please wait...");

while (ob_get_level() > 0) {
    ob_end_flush();
}
flush();

$installThemeResult = executeCommand("opkg install --force-reinstall /tmp/$latestFile");

echo "Package installation completed.\n";
flush();

if ($installThemeResult['status'] !== 0) {
    logStep("❌ Error installing the package: " . $installThemeResult['output']);
    exit;
}
logStep("✅ Theme installed successfully");

logStep("🧹 Cleaning up...");
$cleanupResult = executeCommand("rm -f /tmp/$latestFile");
if ($cleanupResult['status'] !== 0) {
    logStep("⚠️ Warning cleaning up: " . $cleanupResult['output']);
} else {
    logStep("✅ Cleanup completed");
}

logStep("🎉 INSTALLATION_SUCCESS");
?>