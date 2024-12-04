<?php

ini_set('memory_limit', '128M'); 

function logMessage($message) {
    $logFile = '/var/log/sing-box_update.log'; 
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function writeVersionToFile($version) {
    $versionFile = '/etc/neko/core/version.txt';
    file_put_contents($versionFile, $version);
}

$repo_owner = "Thaolga";
$repo_name = "luci-app-nekoclash";
$latest_version = "1.10.0-alpha.29-067c81a7"; 
$current_version = ''; 
$install_path = '/usr/bin/sing-box'; 
$temp_file = '/tmp/sing-box.tar.gz'; 
$temp_dir = '/tmp/singbox_temp'; 

if (file_exists($install_path)) {
    $current_version = trim(shell_exec("{$install_path} --version"));
}

if (isset($_GET['check_version'])) {
    echo "当前版本: $current_version\n";
    echo "最新版本: $latest_version\n";
    exit;
}

$current_arch = trim(shell_exec("uname -m"));
$download_url = '';

switch ($current_arch) {
    case 'aarch64':
        $download_url = "https://github.com/Thaolga/luci-app-nekoclash/releases/download/sing-box/sing-box-puernya-linux-armv8.tar.gz";
        break;
    case 'x86_64':
        $download_url = "https://github.com/Thaolga/luci-app-nekoclash/releases/download/sing-box/sing-box-puernya-linux-amd64.tar.gz";
        break;
    default:
        die("未找到适合架构的下载链接: $current_arch");
}

if (trim($current_version) === trim($latest_version)) {
    die("当前版本已是最新版本。");
}

exec("wget -O '$temp_file' '$download_url'", $output, $return_var);
if ($return_var !== 0) {
    die("下载失败！");
}

if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0755, true);
}

exec("tar -xzf '$temp_file' -C '$temp_dir'", $output, $return_var);
if ($return_var !== 0) {
    die("解压失败！");
}

$extracted_file = glob("$temp_dir/CrashCore")[0] ?? '';
if ($extracted_file && file_exists($extracted_file)) {
    exec("cp -f '$extracted_file' '$install_path'");
    exec("chmod 0755 '$install_path'");
    writeVersionToFile($latest_version); 
    echo "更新完成！最新版本: $latest_version\n";
} else {
    die("解压后的文件 'CrashCore' 不存在。");
}

unlink($temp_file);
exec("rm -r '$temp_dir'");
?>
