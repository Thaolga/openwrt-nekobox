<?php

ini_set('memory_limit', '128M'); 

function logMessage($message) {
    $logFile = '/var/log/config_update.log'; 
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

$download_url = "https://raw.githubusercontent.com/Thaolga/openwrt-nekobox/refs/heads/main/luci-app-nekobox/root/etc/neko/config/mihomo.yaml";
$destination_path = "/etc/neko/config/mihomo.yaml";

if (!is_dir(dirname($destination_path))) {
    mkdir(dirname($destination_path), 0755, true);
}

exec("wget -O '$destination_path' '$download_url'", $output, $return_var);
if ($return_var !== 0) {
    logMessage("Download failed!");
    die("Download failed!");
}

logMessage("mihomo.yaml file has been successfully updated!");
echo "The file has been successfully updated!";


?>
