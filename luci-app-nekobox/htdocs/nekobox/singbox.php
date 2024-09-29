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

$repo_owner = "SagerNet";
$repo_name = "sing-box";
$api_url = "https://api.github.com/repos/$repo_owner/$repo_name/releases";

$curl_command = "curl -s -H 'User-Agent: PHP' --connect-timeout 10 " . escapeshellarg($api_url);
$response = shell_exec($curl_command);

if ($response === false || empty($response)) {
    logMessage("GitHub API 请求失败，可能是网络问题或 GitHub API 限制。");
    die("GitHub API 请求失败。请检查网络连接或稍后重试。");
}

logMessage("GitHub API 响应: " . substr($response, 0, 200) . "...");  

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    logMessage("解析 GitHub API 响应时出错: " . json_last_error_msg());
    die("解析 GitHub API 响应时出错: " . json_last_error_msg());
}

$latest_beta_version = '';
if (is_array($data)) {
    foreach ($data as $release) {
        if (isset($release['tag_name']) && strpos($release['tag_name'], 'beta') !== false) {
            $latest_beta_version = $release['tag_name'];
            break;
        }
    }
}

if (empty($latest_beta_version)) {
    logMessage("未找到 beta 版本信息。");
    die("未找到 beta 版本信息。");
}

$current_version = ''; 
$install_path = '/usr/bin/sing-box'; 
$temp_file = '/tmp/sing-box.tar.gz'; 
$temp_dir = '/tmp/singbox_temp'; 

if (file_exists($install_path)) {
    $current_version = trim(shell_exec("{$install_path} --version"));
}

$current_arch = trim(shell_exec("uname -m"));
$base_version = ltrim($latest_beta_version, 'v');
$download_url = '';

switch ($current_arch) {
    case 'aarch64':
        $download_url = "https://github.com/SagerNet/sing-box/releases/download/$latest_beta_version/sing-box-$base_version-linux-arm64.tar.gz";
        break;
    case 'x86_64':
        $download_url = "https://github.com/SagerNet/sing-box/releases/download/$latest_beta_version/sing-box-$base_version-linux-amd64.tar.gz";
        break;
    default:
        die("未找到适合架构的下载链接: $current_arch");
}

if (trim($current_version) === trim($latest_beta_version)) {
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

$extracted_file = glob("$temp_dir/sing-box-*/*sing-box")[0] ?? '';
if ($extracted_file && file_exists($extracted_file)) {
    exec("cp -f '$extracted_file' '$install_path'");
    exec("chmod 0755 '$install_path'");
    writeVersionToFile($latest_beta_version); 
    echo "更新完成！当前版本: $latest_beta_version";
} else {
    die("解压后的文件 'sing-box' 不存在。");
}

unlink($temp_file);
exec("rm -r '$temp_dir'");
?>
