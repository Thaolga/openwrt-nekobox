<?php

ini_set('memory_limit', '128M'); 

$logMessages = [];

function logMessage($message) {
    global $logMessages;
    $timestamp = date('H:i:s');
    $logMessages[] = "[$timestamp] $message";
}

$urls = [
    "https://mirror.ghproxy.com/https://raw.githubusercontent.com/Thaolga/openwrt-nekobox/nekobox/luci-app-nekobox/root/etc/neko/config/mihomo.yaml" => "/etc/neko/config/mihomo.yaml",
    "https://mirror.ghproxy.com/https://raw.githubusercontent.com/Thaolga/openwrt-nekobox/nekobox/luci-app-nekobox/root/etc/neko/config/Puernya.json" => "/etc/neko/config/Puernya.json"
];

foreach ($urls as $download_url => $destination_path) {
    if (!is_dir(dirname($destination_path))) {
        mkdir(dirname($destination_path), 0755, true);
    }

    exec("wget -O '$destination_path' '$download_url'", $output, $return_var);
    if ($return_var !== 0) {
        logMessage("Download failed: $destination_path");
        die("Download failed: $destination_path");
    }

    logMessage(basename($destination_path) . " file has been successfully updated!");
}

echo implode("\n", $logMessages);

?>
