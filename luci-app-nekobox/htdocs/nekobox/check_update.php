<?php
function getCurrentVersion() {
    $packageName = 'luci-app-nekobox';
    $command = "opkg list-installed | grep $packageName";
    $output = shell_exec($command . ' 2>&1');

    if ($output === null || empty($output)) {
        return "Error";
    }

    if (preg_match('/(\d+\.\d+\.\d+-(?:r|rc)\d+)/', $output, $matches)) {
        return $matches[1];
    }

    return "Error";
}

function getLatestVersion() {
    $url = "https://github.com/Thaolga/openwrt-nekobox/releases";
    $html = shell_exec("curl -m 10 -s $url");

    if ($html === null || empty($html)) {
        return "Error";
    }

    if (preg_match('/luci-app-nekobox_(\d+\.\d+\.\d+-(?:r|rc)\d+)/', $html, $matches)) {
        return $matches[1];
    }

    return "Error";
}

function compareVersions($ver1, $ver2) {
    if ($ver1 === $ver2) {
        return 0;
    }
    
    list($main1, $rel1) = explode('-', $ver1 . '-');
    list($main2, $rel2) = explode('-', $ver2 . '-');
    
    $rel1 = rtrim($rel1, '-');
    $rel2 = rtrim($rel2, '-');
    
    $mainCompare = version_compare($main1, $main2);
    if ($mainCompare !== 0) {
        return $mainCompare;
    }
    
    return compareReleases($rel1, $rel2);
}

function compareReleases($rel1, $rel2) {
    if (empty($rel1) && empty($rel2)) return 0;
    if (empty($rel1)) return -1;
    if (empty($rel2)) return 1;
    
    preg_match('/(r|rc)(\d+)/', $rel1, $m1);
    preg_match('/(r|rc)(\d+)/', $rel2, $m2);
    
    $type1 = $m1[1] ?? '';
    $type2 = $m2[1] ?? '';
    $num1 = intval($m1[2] ?? 0);
    $num2 = intval($m2[2] ?? 0);
    
    $priority = ['r' => 1, 'rc' => 2];
    $pri1 = $priority[$type1] ?? 0;
    $pri2 = $priority[$type2] ?? 0;
    
    if ($pri1 !== $pri2) {
        return $pri1 - $pri2;
    }
    
    return $num1 - $num2;
}

$currentVersion = getCurrentVersion();
$latestVersion = getLatestVersion();

$response = [
    'currentVersion' => $currentVersion,
    'latestVersion' => $latestVersion,
    'hasUpdate' => false
];

if ($currentVersion !== "Error" && $latestVersion !== "Error") {
    $compare = compareVersions($currentVersion, $latestVersion);
    $response['hasUpdate'] = ($compare < 0);
    $response['compareResult'] = $compare;
} else {
    $response['error'] = 'Version fetch failed';
}

header('Content-Type: application/json');
echo json_encode($response);
?>