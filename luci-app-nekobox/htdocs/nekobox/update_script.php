<?php
$repo_owner = "Thaolga";
$repo_name = "openwrt-nekobox";
$package_name = "luci-app-nekobox";

$api_url = "https://api.github.com/repos/$repo_owner/$repo_name/releases/latest";
$local_api_response = "/tmp/api_response.json";

$curl_command = "curl -H 'User-Agent: PHP' -s " . escapeshellarg($api_url) . " -o " . escapeshellarg($local_api_response);
exec($curl_command . " 2>&1", $output, $return_var);

if (!file_exists($local_api_response)) {
    die("Unable to access GitHub API. Please check the URL or network connection. Output: " . implode("\n", $output));
}

$response = file_get_contents($local_api_response);
$data = json_decode($response, true);
unlink($local_api_response);

$new_version = $data['tag_name'] ?? '';

if (empty($new_version)) {
    die("No latest version found or version information is empty.");
}

if (isset($_GET['check_version'])) {
    echo "Latest version: V" . $new_version;
    exit;
}

$installed_lang = isset($_GET['lang']) ? $_GET['lang'] : 'en'; 

if ($installed_lang !== 'cn' && $installed_lang !== 'en') {
    die("Invalid language selection. Please choose 'cn' or 'en'.");
}

$download_url = "https://github.com/$repo_owner/$repo_name/releases/download/$new_version/{$package_name}_{$new_version}-{$installed_lang}_all.ipk";

echo "<pre>Latest version: $new_version</pre>";
echo "<pre>Download URL: $download_url</pre>";
echo "<pre id='logOutput'></pre>";

echo "<script>
        function appendLog(message) {
            document.getElementById('logOutput').innerHTML += message + '\\n';
        }
      </script>";

echo "<script>appendLog('Starting download...');</script>";

$local_file = "/tmp/{$package_name}_{$new_version}-{$installed_lang}_all.ipk";
$curl_command = "curl -sL " . escapeshellarg($download_url) . " -o " . escapeshellarg($local_file);
exec($curl_command . " 2>&1", $output, $return_var);

if ($return_var !== 0 || !file_exists($local_file)) {
    echo "<pre>Download failed. Command output: " . implode("\n", $output) . "</pre>";
    die("Download failed. The downloaded file was not found.");
}

echo "<script>appendLog('Download complete.');</script>";

echo "<script>appendLog('Updating package list...');</script>";
$output = shell_exec("opkg update");
echo "<pre>$output</pre>";

echo "<script>appendLog('Starting installation...');</script>";

$output = shell_exec("opkg install --force-reinstall " . escapeshellarg($local_file));
echo "<pre>$output</pre>";
echo "<script>appendLog('Installation complete.');</script>";

unlink($local_file);
?>