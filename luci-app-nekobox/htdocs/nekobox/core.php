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
        logMessage("Failed to write version file: $versionFile");
    } else {
        logMessage("Successfully wrote version file: $versionFile");
    }
}

$repo_owner = "MetaCubeX";
$repo_name = "mihomo";
$api_url = "https://api.github.com/repos/$repo_owner/$repo_name/releases/latest";

$curl_command = "curl -s -H 'User-Agent: PHP' " . escapeshellarg($api_url);
$response = shell_exec($curl_command);

if ($response === false || empty($response)) {
    logMessage("GitHub API request failed.");
    die("GitHub API request failed. Please check your network connection or try again later.");
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    logMessage("Error parsing GitHub API response: " . json_last_error_msg());
    die("Error parsing GitHub API response: " . json_last_error_msg());
}

$latest_version = $data['tag_name'] ?? '';

if (empty($latest_version)) {
    logMessage("No latest version information found.");
    die("No latest version information found.");
}

$current_version = ''; 
$install_path = '/etc/neko/core/mihomo'; 
$temp_file = '/tmp/mihomo.gz'; 

if (file_exists($install_path)) {
    $current_version = trim(shell_exec("{$install_path} --version"));
    logMessage("Current version: $current_version");
} else {
    logMessage("Current version file does not exist, treating as not installed.");
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
        logMessage("No suitable download link found for architecture: $current_arch");
        echo "No suitable download link found for architecture: $current_arch";
        exit;
}

logMessage("Latest version: $latest_version");
logMessage("Current architecture: $current_arch");
logMessage("Download URL: $download_url");

if (trim($current_version) === trim($latest_version)) {
    logMessage("Current version is already up to date, no update needed.");
    echo "Current version is already up to date.";
    exit;
}

logMessage("Starting core update download...");
exec("wget -O '$temp_file' '$download_url'", $output, $return_var);
logMessage("wget return code: $return_var");

if ($return_var === 0) {
    logMessage("Starting file extraction and renaming...");
    exec("gzip -d -c '$temp_file' > '/tmp/mihomo-linux-arm64'", $output, $return_var);

    if ($return_var === 0) {
        exec("mv '/tmp/mihomo-linux-arm64' '$install_path'", $output, $return_var);
        logMessage("File move return code: $return_var");

        if ($return_var === 0) {
            exec("chmod 0755 '$install_path'", $output, $return_var);
            logMessage("Permission set return code: $return_var");

            if ($return_var === 0) {
                logMessage("Core update completed! Current version: $latest_version");
                writeVersionToFile($latest_version); 
                echo "Update completed! Current version: $latest_version";
            } else {
                logMessage("Permission setting failed!");
                echo "Permission setting failed!";
            }
        } else {
            logMessage("File move failed, return code: $return_var");
            echo "File move failed!";
        }
    } else {
        logMessage("Extraction failed, return code: $return_var");
        echo "Extraction failed!";
    }
} else {
    logMessage("Download failed, return code: $return_var");
    echo "Download failed!";
}

if (file_exists($temp_file)) {
    unlink($temp_file);
    logMessage("Temporary file cleanup: $temp_file");
}
?>
