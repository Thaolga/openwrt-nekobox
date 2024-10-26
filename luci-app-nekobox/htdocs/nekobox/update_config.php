<?php
if (isset($_POST['action']) && $_POST['action'] === 'update_config') {
    $configFilePath = '/etc/neko/config/mihomo.yaml'; 
    $url = 'https://raw.githubusercontent.com/Thaolga/openwrt-nekobox/refs/heads/main/luci-app-nekobox/root/etc/neko/config/mihomo.yaml';

    $ch = curl_init($url);
    $fp = fopen($configFilePath, 'w');

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

    $success = curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    if ($success) {
        echo "<script>alert('Mihomo configuration file has been successfully updated!');</script>";
        error_log("Mihomo configuration file has been successfully updated!");
    } else {
        echo "<script>alert('Configuration file update failed!');</script>";
        error_log("Configuration file update failed!");
    }
}
?>
