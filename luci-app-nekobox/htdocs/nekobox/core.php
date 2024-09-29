<?php
function logMessage($message) {
    $logFile = '/var/log/mihomo_update.log'; 
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function writeVersionToFile($version) {
    $versionFile = '/etc/neko/core/mihomo_version.txt';
    $result = file_put_contents($versionFile, $version);
    if ($result === false) {
        logMessage("无法写入版本文件: $versionFile");
    } else {
        logMessage("成功写入版本文件: $versionFile");
    }
}

$repo_owner = "MetaCubeX";
$repo_name = "mihomo";
$api_url = "https://api.github.com/repos/$repo_owner/$repo_name/releases/latest";

$curl_command = "curl -s -H 'User-Agent: PHP' " . escapeshellarg($api_url);
$response = shell_exec($curl_command);

if ($response === false || empty($response)) {
    logMessage("GitHub API 请求失败。");
    die("GitHub API 请求失败。请检查网络连接或稍后重试。");
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    logMessage("解析 GitHub API 响应时出错: " . json_last_error_msg());
    die("解析 GitHub API 响应时出错: " . json_last_error_msg());
}

$latest_version = $data['tag_name'] ?? '';

if (empty($latest_version)) {
    logMessage("未找到最新版本信息。");
    die("未找到最新版本信息。");
}

$current_version = ''; 
$install_path = '/etc/neko/core/mihomo'; 
$temp_file = '/tmp/mihomo.gz'; 

if (file_exists($install_path)) {
    $current_version = trim(shell_exec("{$install_path} --version"));
    logMessage("当前版本: $current_version");
} else {
    logMessage("当前版本文件不存在，将视为未安装。");
}

$current_arch = trim(shell_exec("uname -m"));

$download_url = '';
$base_version = ltrim($latest_version, 'v'); 
switch ($current_arch) {
    case 'aarch64':
        $download_url = "https://github.com/MetaCubeX/mihomo/releases/download/$latest_version/mihomo-linux-arm64-v$base_version.gz";
        break;
    case 'armv7l':
        $download_url = "https://github.com/MetaCubeX/mihomo/releases/download/$latest_version/mihomo-linux-armv7l-v$base_version.gz";
        break;
    case 'x86_64':
        $download_url = "https://github.com/MetaCubeX/mihomo/releases/download/$latest_version/mihomo-linux-amd64-v$base_version.gz";
        break;
    default:
        logMessage("未找到适合架构的下载链接: $current_arch");
        echo "未找到适合架构的下载链接: $current_arch";
        exit;
}

logMessage("最新版本: $latest_version");
logMessage("当前架构: $current_arch");
logMessage("下载链接: $download_url");

if (trim($current_version) === trim($latest_version)) {
    logMessage("当前版本已是最新版本，无需更新。");
    echo "当前版本已是最新版本。";
    exit;
}

logMessage("开始下载核心更新...");
exec("wget -O '$temp_file' '$download_url'", $output, $return_var);
logMessage("wget 返回值: $return_var");

if ($return_var === 0) {
    logMessage("开始解压并重命名文件...");
    exec("gzip -d -c '$temp_file' > '/tmp/mihomo-linux-arm64'", $output, $return_var);

    if ($return_var === 0) {
        exec("mv '/tmp/mihomo-linux-arm64' '$install_path'", $output, $return_var);
        logMessage("文件移动返回值: $return_var");

        if ($return_var === 0) {
            exec("chmod 0755 '$install_path'", $output, $return_var);
            logMessage("权限设置返回值: $return_var");

            if ($return_var === 0) {
                logMessage("核心更新完成！当前版本: $latest_version");
                writeVersionToFile($latest_version); 
                echo "更新完成！当前版本: $latest_version";
            } else {
                logMessage("设置权限失败！");
                echo "设置权限失败！";
            }
        } else {
            logMessage("移动文件失败，返回值: $return_var");
            echo "移动文件失败！";
        }
    } else {
        logMessage("解压失败，返回值: $return_var");
        echo "解压失败！";
    }
} else {
    logMessage("下载失败，返回值: $return_var");
    echo "下载失败！";
}

if (file_exists($temp_file)) {
    unlink($temp_file);
    logMessage("清理临时文件: $temp_file");
}
?>
