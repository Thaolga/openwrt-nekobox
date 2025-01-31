<?php
ob_start();
include './cfg.php';
$uploadDir = '/etc/neko/proxy_provider/';
$configDir = '/etc/neko/config/';

ini_set('memory_limit', '256M');

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['fileInput'])) {
        $file = $_FILES['fileInput'];
        $uploadFilePath = $uploadDir . basename($file['name']);

        if ($file['error'] === UPLOAD_ERR_OK) {
            if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
               echo 'File upload successful: ' . htmlspecialchars(basename($file['name']));
            } else {
                echo 'File upload failed!';
            }
        } else {
            echo 'Upload error: ' . $file['error'];
        }
    }

    if (isset($_FILES['configFileInput'])) {
        $file = $_FILES['configFileInput'];
        $uploadFilePath = $configDir . basename($file['name']);

        if ($file['error'] === UPLOAD_ERR_OK) {
            if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
                echo 'Configuration file uploaded successfully.：' . htmlspecialchars(basename($file['name']));
            } else {
                echo 'Configuration file upload failed!';
            }
        } else {
            echo 'Upload error:' . $file['error'];
        }
    }

    if (isset($_POST['deleteFile'])) {
        $fileToDelete = $uploadDir . basename($_POST['deleteFile']);
        if (file_exists($fileToDelete) && unlink($fileToDelete)) {
            echo 'File deleted successfully.：' . htmlspecialchars(basename($_POST['deleteFile']));
        } else {
            echo 'File deletion failed!';
        }
    }

    if (isset($_POST['deleteConfigFile'])) {
        $fileToDelete = $configDir . basename($_POST['deleteConfigFile']);
        if (file_exists($fileToDelete) && unlink($fileToDelete)) {
            echo 'Configuration file deleted successfully:' . htmlspecialchars(basename($_POST['deleteConfigFile']));
        } else {
            echo 'Configuration file deletion failed!';
        }
    }

    if (isset($_POST['oldFileName'], $_POST['newFileName'], $_POST['fileType'])) {
        $oldFileName = basename($_POST['oldFileName']);
        $newFileName = basename($_POST['newFileName']);
        $fileType = $_POST['fileType'];

        if ($fileType === 'proxy') {
            $oldFilePath = $uploadDir. $oldFileName;
            $newFilePath = $uploadDir. $newFileName;
        } elseif ($fileType === 'config') {
            $oldFilePath = $configDir . $oldFileName;
            $newFilePath = $configDir . $newFileName;
        } else {
        echo 'Invalid file type';
            exit;
        }

    if (file_exists($oldFilePath) && !file_exists($newFilePath)) {
        if (rename($oldFilePath, $newFilePath)) {
            echo 'File renamed successfully：' . htmlspecialchars($oldFileName) . ' -> ' . htmlspecialchars($newFileName);
        } else {
            echo 'File renaming failed!';
        }
    } else {
        echo 'File renaming failed, the file does not exist or the new file name already exists.';
    }
}

    if (isset($_POST['saveContent'], $_POST['fileName'], $_POST['fileType'])) {
            $fileToSave = ($_POST['fileType'] === 'proxy') ? $uploadDir . basename($_POST['fileName']) : $configDir . basename($_POST['fileName']);
            $contentToSave = $_POST['saveContent'];
            file_put_contents($fileToSave, $contentToSave);
        echo '<p>File content has been updated：' . htmlspecialchars(basename($fileToSave)) . '</p>';
    }
}

function formatFileModificationTime($filePath) {
    if (file_exists($filePath)) {
        $fileModTime = filemtime($filePath);
        return date('Y-m-d H:i:s', $fileModTime);
    } else {
        return 'File does not exist.';
    }
}

$proxyFiles = scandir($uploadDir);
$configFiles = scandir($configDir);

if ($proxyFiles !== false) {
    $proxyFiles = array_diff($proxyFiles, array('.', '..'));
    $proxyFiles = array_filter($proxyFiles, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) !== 'txt';
    });
} else {
    $proxyFiles = []; 
}

if ($configFiles !== false) {
    $configFiles = array_diff($configFiles, array('.', '..'));
} else {
    $configFiles = []; 
}

function formatSize($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $unit = 0;
    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }
    return round($size, 2) . ' ' . $units[$unit];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editFile'], $_GET['fileType'])) {
    $filePath = ($_GET['fileType'] === 'proxy') ? $uploadDir. basename($_GET['editFile']) : $configDir . basename($_GET['editFile']);
    if (file_exists($filePath)) {
        header('Content-Type: text/plain');
        echo file_get_contents($filePath);
        exit;
    } else {
        echo 'File does not exist.';
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['downloadFile'], $_GET['fileType'])) {
    $fileType = $_GET['fileType'];
    $fileName = basename($_GET['downloadFile']);
    $filePath = ($fileType === 'proxy') ? $uploadDir . $fileName : $configDir . $fileName;

    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo 'File does not exist';
    }
}
?>

<?php
$subscriptionPath = '/etc/neko/proxy_provider/';
$subscriptionFile = $subscriptionPath . 'subscriptions.json';
$notificationMessage = "";
$subscriptions = [];
$updateCompleted = false;

function storeUpdateLog($message) {
    if (!isset($_SESSION['update_logs'])) {
        $_SESSION['update_logs'] = [];
    }
    $_SESSION['update_logs'][] = $message;
}

if (!file_exists($subscriptionPath)) {
    mkdir($subscriptionPath, 0755, true);
}

if (!file_exists($subscriptionFile)) {
    file_put_contents($subscriptionFile, json_encode([]));
}

$subscriptions = json_decode(file_get_contents($subscriptionFile), true);
if (!$subscriptions) {
    for ($i = 0; $i < 6; $i++) {
        $subscriptions[$i] = [
            'url' => '',
            'file_name' => "subscription_" . ($i + 1) . ".yaml",  
        ];
    }
}

if (isset($_POST['update'])) {
    $index = intval($_POST['index']);
    $url = trim($_POST['subscription_url'] ?? '');
    $customFileName = trim($_POST['custom_file_name'] ?? "subscription_" . ($index + 1) . ".yaml");  

    $subscriptions[$index]['url'] = $url;
    $subscriptions[$index]['file_name'] = $customFileName;

    if (!empty($url)) {
        $tempPath = $subscriptionPath . $customFileName . ".temp";
        $finalPath = $subscriptionPath . $customFileName;

        $command = "curl -s -L -o {$tempPath} {$url}";
        exec($command . ' 2>&1', $output, $return_var);

        if ($return_var !== 0) {
            $command = "wget -q --show-progress -O {$tempPath} {$url}";
            exec($command . ' 2>&1', $output, $return_var);
        }

        if ($return_var === 0) {
            $_SESSION['update_logs'] = [];
            storeUpdateLog("✅ Subscription " . htmlspecialchars($url) . " has been downloaded and saved to a temporary file: " . htmlspecialchars($tempPath));

            $fileContent = file_get_contents($tempPath);

            if (base64_encode(base64_decode($fileContent, true)) === $fileContent) {
                $decodedContent = base64_decode($fileContent);
                if ($decodedContent !== false && strlen($decodedContent) > 0) {
                    file_put_contents($finalPath, "# Clash Meta Config\n\n" . $decodedContent);
                    storeUpdateLog("📂 Base64 decoding successful, configuration saved to: " . htmlspecialchars($finalPath));
                    unlink($tempPath); 
                    $notificationMessage = 'Update successful';
                    $updateCompleted = true;
                } else {
                    storeUpdateLog("⚠️ Base64 decoding failed, please check the subscription link content!");
                    unlink($tempPath); 
                    $notificationMessage = 'Update failed';
                }
            } 
            elseif (substr($fileContent, 0, 2) === "\x1f\x8b") {
                $decompressedContent = gzdecode($fileContent);
                if ($decompressedContent !== false) {
                    file_put_contents($finalPath, "# Clash Meta Config\n\n" . $decompressedContent);
                    storeUpdateLog("📂 Gzip decompression successful, configuration saved to: " . htmlspecialchars($finalPath));
                    unlink($tempPath); 
                    $notificationMessage = 'Update successful';
                    $updateCompleted = true;
                } else {
                    storeUpdateLog("⚠️  Gzip decompression failed, please check the subscription link format!");
                    unlink($tempPath); 
                    $notificationMessage = 'Update failed';
                }
            } 
            else {
                rename($tempPath, $finalPath); 
                storeUpdateLog("✅ Subscription content successfully downloaded, no decoding required");
                $notificationMessage = 'Update successful';
                $updateCompleted = true;
            }
        } else {
            storeUpdateLog("❌ Subscription update failed! Error message: " . implode("\n", $output));
            unlink($tempPath); 
            $notificationMessage = 'Update failed';
        }
    } else {
        storeUpdateLog("⚠️ The " . ($index + 1) . "th subscription link is empty!");
        $notificationMessage = 'Update failed';
    }

    file_put_contents($subscriptionFile, json_encode($subscriptions));
}

?>
<?php
$shellScriptPath = '/etc/neko/core/update_mihomo.sh';
$LOG_FILE = '/etc/neko/tmp/log.txt'; 
$JSON_FILE = '/etc/neko/proxy_provider/subscriptions.json';
$SAVE_DIR = '/etc/neko/proxy_provider';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['createShellScript'])) {
        $shellScriptContent = <<<EOL
#!/bin/bash

LOG_FILE="/etc/neko/tmp/log.txt"
JSON_FILE="/etc/neko/proxy_provider/subscriptions.json"
SAVE_DIR="/etc/neko/proxy_provider"

log() {
    echo "$(date '+[ %H:%M:%S ]') \$1" >> "\$LOG_FILE"
}

log "Starting subscription update task..."

if [ ! -f "\$JSON_FILE" ]; then
    log "❌ Error: JSON file does not exist: \$JSON_FILE"
    exit 1
fi

jq -c '.[]' "\$JSON_FILE" | while read -r ITEM; do
    URL=\$(echo "\$ITEM" | jq -r '.url')         
    FILE_NAME=\$(echo "\$ITEM" | jq -r '.file_name')  

    if [ -z "\$URL" ] || [ "\$URL" == "null" ]; then
        log "⚠️ Skipping empty subscription URL, file name: \$FILE_NAME"
        continue
    fi

    if [ -z "\$FILE_NAME" ] || [ "\$FILE_NAME" == "null" ]; then
        log "❌ Error: File name is empty, skipping this URL: \$URL"
        continue
    fi

    SAVE_PATH="\$SAVE_DIR/\$FILE_NAME"
    TEMP_PATH="\$SAVE_PATH.temp"  

    log "🔄 Downloading: \$URL to temporary file: \$TEMP_PATH"

    curl -s -L -o "\$TEMP_PATH" "\$URL"

    if [ \$? -ne 0 ]; then
        wget -q -O "\$TEMP_PATH" "\$URL"
    fi

    if [ \$? -eq 0 ]; then
        log "✅ File downloaded successfully: \$TEMP_PATH"

        if base64 -d "\$TEMP_PATH" > /dev/null 2>&1; then
            base64 -d "\$TEMP_PATH" > "\$SAVE_PATH"
            if [ \$? -eq 0 ]; then
                log "📂 Base64 decoding successful, configuration saved: \$SAVE_PATH"
                rm -f "\$TEMP_PATH"
            else
                log "⚠️ Base64 decoding failed: \$SAVE_PATH"
                rm -f "\$TEMP_PATH"
            fi
        elif file "\$TEMP_PATH" | grep -q "gzip compressed"; then
            gunzip -c "\$TEMP_PATH" > "\$SAVE_PATH"
            if [ \$? -eq 0 ]; then
                log "📂 Gzip decompression successful, configuration saved: \$SAVE_PATH"
                rm -f "\$TEMP_PATH"
            else
                log "⚠️ Gzip decompression failed: \$SAVE_PATH"
                rm -f "\$TEMP_PATH"
            fi
        else
            mv "\$TEMP_PATH" "\$SAVE_PATH"
            log "✅ Subscription content successfully downloaded, no decoding required"
        fi
    else
        log "❌ Subscription update failed: \$URL"
        rm -f "\$TEMP_PATH"
    fi
done

log "🚀 All subscription links updated successfully！"
EOL;

        if (file_put_contents($shellScriptPath, $shellScriptContent) !== false) {
            chmod($shellScriptPath, 0755); 
            echo "<div class='alert alert-success'>Shell script successfully created! Path: $shellScriptPath</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to create Shell script, please check permissions.</div>";
        }
    }
}
?>

<?php
$CRON_LOG_FILE = '/etc/neko/tmp/log.txt'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['createCronJob'])) {
        $cronExpression = trim($_POST['cronExpression']);

        if (empty($cronExpression)) {
            file_put_contents($CRON_LOG_FILE, date('[ H:i:s ] ') . "Error: Cron expression cannot be empty.\n", FILE_APPEND);
            echo "<div class='alert alert-warning'>Cron expression cannot be empty.</div>";
            exit;
        }

        $cronJob = "$cronExpression /etc/neko/core/update_mihomo.sh";

        exec("crontab -l | grep -v '/etc/neko/core/update_mihomo.sh' | crontab -", $output, $returnVarRemove);
        if ($returnVarRemove === 0) {
            file_put_contents($CRON_LOG_FILE, date('[ H:i:s ] ') . "Successfully removed old Cron job.\n", FILE_APPEND);
        } else {
            file_put_contents($CRON_LOG_FILE, date('[ H:i:s ] ') . "Failed to remove old Cron job.\n", FILE_APPEND);
        }

        exec("(crontab -l; echo '$cronJob') | crontab -", $output, $returnVarAdd);
        if ($returnVarAdd === 0) {
            file_put_contents($CRON_LOG_FILE, date('[ H:i:s ] ') . "Successfully added new Cron job: $cronJob\n", FILE_APPEND);
            echo "<div class='alert alert-success'>Cron job successfully added or updated!</div>";
        } else {
            file_put_contents($CRON_LOG_FILE, date('[ H:i:s ] ') . "Failed to add new Cron job.\n", FILE_APPEND);
            echo "<div class='alert alert-danger'>Failed to add or update Cron job. Please check server permissions.</div>";
        }
    }
}
?>

<?php
$file_urls = [
    'geoip' => 'https://github.com/MetaCubeX/meta-rules-dat/releases/download/latest/geoip.metadb',
    'geosite' => 'https://github.com/MetaCubeX/meta-rules-dat/releases/download/latest/geosite.dat',
    'cache' => 'https://github.com/Thaolga/neko/raw/main/cache.db' 
];

$download_directories = [
    'geoip' => '/etc/neko/',
    'geosite' => '/etc/neko/',
    'cache' => '/www/nekobox/' 
];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['file'])) {
    $file = $_GET['file'];

    if (isset($file_urls[$file])) {
        $file_url = $file_urls[$file];
        $destination_directory = $download_directories[$file];
        $destination_path = $destination_directory . basename($file_url);

        if (download_file($file_url, $destination_path)) {
            echo "<div class='alert alert-success'>The file was successfully downloaded to $destination_path</div>";
        } else {
            echo "<div class='alert alert-danger'>File download failed</div>";
        }
    } else {
        echo "Invalid file request";
    }
}

function download_file($url, $destination) {
    $ch = curl_init($url);
    $fp = fopen($destination, 'wb');

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $result = curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    return $result !== false;
}
?>
<!doctype html>
<html lang="en" data-bs-theme="<?php echo substr($neko_theme, 0, -4) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mihomo - NekoBox</title>
    <link rel="icon" href="./assets/img/nekobox.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">
    <link href="./assets/bootstrap/bootstrap-icons.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <script type="text/javascript" src="./assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="./assets/js/feather.min.js"></script>
    <script type="text/javascript" src="./assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="./assets/js/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="./assets/js/neko.js"></script>
    <?php include './ping.php'; ?>
</head>
<?php if ($updateCompleted): ?>
    <script>
        if (!sessionStorage.getItem('refreshed')) {
            sessionStorage.setItem('refreshed', 'true');
            window.location.reload(); 
        } else {
            sessionStorage.removeItem('refreshed'); 
        }
    </script>
<?php endif; ?>
<body>
<div class="position-fixed w-100 d-flex flex-column align-items-center" style="top: 20px; z-index: 1050;">
    <div id="updateNotification" class="alert alert-info alert-dismissible fade show shadow-lg" role="alert" style="display: none; min-width: 320px; max-width: 600px; opacity: 0.95;">
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
            <strong>🔔 Update Notification</strong>
        </div>
        
        <div class="alert alert-info mt-2 p-2 small">
            <strong>⚠️ Instructions for Use：</strong>
            <ul class="mb-0 pl-3">
                <li>The general template (mihomo.yaml) supports up to <strong>6</strong> subscription links.</li>
                <li>Please do not change the default file name.</li>
                <li>This template supports all format subscription links without the need for additional conversion.</li>
            </ul>
        </div>

        <div id="updateLogContainer" class="small mt-2"></div>

        <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>

<style>
.alert-success {
    background-color: #4CAF50 !important; 
    border: 1px solid rgba(255, 255, 255, 0.3) !important; 
    border-radius: 8px !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important; 
    padding: 16px 20px !important;
    position: relative;
    color: #fff !important; 
    backdrop-filter: blur(8px); 
    margin-top: 15px !important;
}

.alert .close,
.alert .btn-close {
    position: absolute !important;
    right: 10px !important;
    top: 10px !important;
    background-color: #dc3545 !important; 
    opacity: 1 !important;
    width: 24px !important;
    height: 24px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 16px !important; 
    color: #fff !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
}

.alert .close:hover,
.alert .btn-close:hover {
    background-color: #bd2130 !important;
    transform: rotate(90deg); 
}

#updateMessages {
    margin-top: 12px;
    padding-right: 20px;
    font-size: 14px;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.9); 
}

#updateMessages .alert-warning {
    background-color: rgba(255, 193, 7, 0.1) !important; 
    border-radius: 6px;
    padding: 12px 15px;
    border: 1px solid rgba(255, 193, 7, 0.2);
}

#updateMessages ul {
    margin-bottom: 0;
    padding-left: 20px;
}

#updateMessages li {
    margin-bottom: 6px;
    color: rgba(255, 255, 255, 0.9);
}

html {
    font-size: 16px;  
}

.container-fluid {
    max-width: 2400px;
    width: 100%;
    margin: 0 auto;
}

#dropZone {
    height: 150px;
    cursor: pointer;
    transition: background-color 0.3s;
}

#dropZone.bg-light {
    background-color: #e9ecef;
}

#dropZone:hover {
    background-color: #f8f9fa;
    border-color: #0d6efd;
}

.upload-icon {
    font-size: 1.5rem; 
}

@media (max-width: 768px) {
    .table thead {
        display: none; 
    }

    .table tbody, 
    .table tr, 
    .table td {
        display: block;
        width: 100%;
    }

    .table td::before {
        content: attr(data-label); 
        font-weight: bold;
        display: block;
        text-transform: uppercase;
        color: #23407E; 
    }

    .table tr {
        margin-bottom: 10px;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
}

</style>

<script>
function displayUpdateNotification() {
    const notification = $('#updateNotification');
    const updateLogs = <?php echo json_encode($_SESSION['update_logs'] ?? []); ?>;
    
    if (updateLogs.length > 0) {
        const logsHtml = updateLogs.map(log => `<div>${log}</div>`).join('');
        $('#updateLogContainer').html(logsHtml);
    }
    
    notification.fadeIn().addClass('show');
    
    setTimeout(function() {
        notification.fadeOut(300, function() {
            notification.hide();
            $('#updateLogContainer').html('');
        });
    }, 10000);
}

<?php if (!empty($notificationMessage)): ?>
    $(document).ready(function() {
        displayUpdateNotification();
    });
<?php endif; ?>
</script>
<div class="container-sm container-bg callout border border-3 rounded-4 col-11">
    <div class="row">
        <a href="./index.php" class="col btn btn-lg"><i class="bi bi-house-door"></i> Home</a>
        <a href="./mihomo_manager.php" class="col btn btn-lg"><i class="bi bi-folder"></i> Manager</a>
        <a href="./singbox.php" class="col btn btn-lg"><i class="bi bi-shop"></i> Template I</a>
        <a href="./subscription.php" class="col btn btn-lg"><i class="bi bi-bank"></i> Template II</a>
        <a href="./mihomo.php" class="col btn btn-lg"><i class="bi bi-building"></i> Template III</a>
    </div>
    <div class="text-center">
        <h2 style="margin-top: 40px; margin-bottom: 20px;">File Management</h2>
<div class="container-fluid">
    <div class="table-responsive">
        <table class="table table-striped table-bordered text-center">
            <thead class="thead-dark">
                <tr>
                    <th style="width: 20%;">File Name</th>
                    <th style="width: 10%;">Size</th>
                    <th style="width: 20%;">Modification Time</th>
                    <th style="width: 10%;">File Type</th>
                    <th style="width: 30%;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $allFiles = array_merge($proxyFiles, $configFiles);
                $allFilePaths = array_merge(array_map(function($file) use ($uploadDir) {
                    return $uploadDir . $file;
                }, $proxyFiles), array_map(function($file) use ($configDir) {
                    return $configDir . $file;
                }, $configFiles));
                $fileTypes = array_merge(array_fill(0, count($proxyFiles), 'Proxy File'), array_fill(0, count($configFiles), 'Config File'));
                
                foreach ($allFiles as $index => $file) {
                    $filePath = $allFilePaths[$index];
                    $fileType = $fileTypes[$index];
                ?>
                    <tr>
                        <td class="align-middle" data-label="File Name">
                            <a href="download.php?file=<?php echo urlencode($file); ?>"><?php echo htmlspecialchars($file); ?></a>
                        </td>
                        <td class="align-middle" data-label="Size">
                            <?php echo file_exists($filePath) ? formatSize(filesize($filePath)) : 'The file does not exist'; ?>
                        </td>
                        <td class="align-middle" data-label="Modification Time">
                            <?php echo htmlspecialchars(date('Y-m-d H:i:s', filemtime($filePath))); ?>
                        </td>
                        <td class="align-middle" data-label="File Type">
                            <?php echo htmlspecialchars($fileType); ?>
                        </td>
                        <td class="align-middle">
                            <div class="action-buttons">
                                <?php if ($fileType == 'Proxy File'): ?>
                                    <form action="" method="post" class="d-inline mb-1">
                                        <input type="hidden" name="deleteFile" value="<?php echo htmlspecialchars($file); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="🗑️ Delete" onclick="return confirm('Are you sure you want to delete this file？');"><i class="bi bi-trash"></i></button>
                                    </form>
                                    <form action="" method="post" class="d-inline mb-1">
                                        <input type="hidden" name="oldFileName" value="<?php echo htmlspecialchars($file); ?>">
                                        <input type="hidden" name="fileType" value="proxy">
                                        <button type="button" class="btn btn-success btn-sm btn-rename" title="✏️ Rename" data-toggle="modal" data-target="#renameModal" data-filename="<?php echo htmlspecialchars($file); ?>" data-filetype="proxy"><i class="bi bi-pencil"></i></button>
                                    </form>
                                    <form action="" method="post" class="d-inline mb-1">
                                        <button type="button" class="btn btn-warning btn-sm" title="📝 Edit" onclick="openEditModal('<?php echo htmlspecialchars($file); ?>', 'proxy')"><i class="bi bi-pen"></i></button>
                                    </form>
                                    <form action="" method="post" enctype="multipart/form-data" class="d-inline upload-btn mb-1">
                                        <input type="file" name="fileInput" class="form-control-file" required id="fileInput-<?php echo htmlspecialchars($file); ?>" style="display: none;" onchange="this.form.submit()">
                                        <button type="button" class="btn btn-info btn-sm" title="📤 Upload" onclick="openUploadModal('proxy')"><i class="bi bi-upload"></i></button>
                                    </form>
                                    <form action="" method="get" class="d-inline mb-1">
                                        <input type="hidden" name="downloadFile" value="<?php echo htmlspecialchars($file); ?>">
                                        <input type="hidden" name="fileType" value="proxy">
                                        <button type="submit" class="btn btn-primary btn-sm" title="📥 Download" ><i class="bi bi-download"></i></button>
                                    </form>
                                <?php else: ?>
                                    <form action="" method="post" class="d-inline mb-1">
                                        <input type="hidden" name="deleteConfigFile" value="<?php echo htmlspecialchars($file); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="🗑️ Delete" onclick="return confirm('Are you sure you want to delete this file？');"><i class="bi bi-trash"></i></button>
                                    </form>
                                    <form action="" method="post" class="d-inline mb-1">
                                        <input type="hidden" name="oldFileName" value="<?php echo htmlspecialchars($file); ?>">
                                        <input type="hidden" name="fileType" value="config">
                                        <button type="button" class="btn btn-success btn-sm btn-rename" title="✏️ Rename" data-toggle="modal" data-target="#renameModal" data-filename="<?php echo htmlspecialchars($file); ?>" data-filetype="config"><i class="bi bi-pencil"></i></button>
                                    </form>
                                    <form action="" method="post" class="d-inline mb-1">
                                        <button type="button" class="btn btn-warning btn-sm" title="📝 Edit" onclick="openEditModal('<?php echo htmlspecialchars($file); ?>', 'config')"><i class="bi bi-pen"></i></button>
                                    </form>
                                    <form action="" method="post" enctype="multipart/form-data" class="d-inline upload-btn mb-1">
                                        <input type="file" name="configFileInput" class="form-control-file" required id="fileInput-<?php echo htmlspecialchars($file); ?>" style="display: none;" onchange="this.form.submit()">
                                        <button type="button" class="btn btn-info btn-sm" title="📤 Upload" onclick="openUploadModal('config')"><i class="bi bi-upload"></i></button>
                                    </form>
                                    <form action="" method="get" class="d-inline mb-1">
                                        <input type="hidden" name="downloadFile" value="<?php echo htmlspecialchars($file); ?>">
                                        <input type="hidden" name="fileType" value="config">
                                        <button type="submit" class="btn btn-primary btn-sm"  title="📥 Download" ><i class="bi bi-download"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload File</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="dropZone" class="border border-primary rounded text-center py-4 position-relative">
                    <i class="bi bi-cloud-upload-fill text-primary upload-icon"></i>
                    <p class="mb-0 mt-3">Drag files to this area to upload<br>or click the button below to select files</p>
                </div>
                <input type="file" id="fileInputModal" class="form-control mt-3" hidden>
                <button id="selectFileBtn" class="btn btn-primary btn-block mt-3 w-100">Select File</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renameModalLabel">Rename File</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="renameForm" action="" method="post">
                    <input type="hidden" name="oldFileName" id="oldFileName">
                    <input type="hidden" name="fileType" id="fileType">

                    <div class="mb-3">
                        <label for="newFileName" class="form-label">New File Name</label>
                        <input type="text" class="form-control" id="newFileName" name="newFileName" required>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.0/beautify.min.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/js-yaml@4.1.0/dist/js-yaml.min.js"></script>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit File: <span id="editingFileName"></span></h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="" method="post" onsubmit="syncEditorContent()">
                    <textarea name="saveContent" id="fileContent" class="form-control" style="height: 500px;"></textarea>
                    <input type="hidden" name="fileName" id="hiddenFileName">
                    <input type="hidden" name="fileType" id="hiddenFileType">
                    <div class="mt-3 d-flex justify-content-start gap-2">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-pink" onclick="openFullScreenEditor()">Advanced Edit</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fullScreenEditorModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content" style="border: none;">
            <div class="modal-header d-flex justify-content-between align-items-center" style="border-bottom: none;">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title mr-3">Advanced Edit - Fullscreen Mode</h5>
                    <select id="fontSize" onchange="changeFontSize()" class="form-select mx-1" style="width: auto; font-size: 0.8rem;">
                        <option value="18px">18px</option>
                        <option value="20px" selected>20px</option>
                        <option value="22px">22px</option>
                        <option value="24px">24px</option>
                        <option value="26px">26px</option>
                        <option value="28px">28px</option>
                        <option value="30px">30px</option>
                        <option value="32px">32px</option>
                        <option value="34px">34px</option>
                        <option value="36px">36px</option>
                        <option value="38px">38px</option>
                        <option value="40px">40px</option>
                    </select>

                    <select id="editorTheme" onchange="changeEditorTheme()" class="form-select mx-1" style="width: auto; font-size: 0.9rem;">
                        <option value="ace/theme/vibrant_ink">Vibrant Ink</option>
                        <option value="ace/theme/monokai">Monokai</option>
                        <option value="ace/theme/github">GitHub</option>
                        <option value="ace/theme/tomorrow">Tomorrow</option>
                        <option value="ace/theme/twilight">Twilight</option>
                        <option value="ace/theme/solarized_dark">Solarized Dark</option>
                        <option value="ace/theme/solarized_light">Solarized Light</option>
                        <option value="ace/theme/textmate">TextMate</option>
                        <option value="ace/theme/terminal">Terminal</option>
                        <option value="ace/theme/chrome">Chrome</option>
                        <option value="ace/theme/eclipse">Eclipse</option>
                        <option value="ace/theme/dreamweaver">Dreamweaver</option>
                        <option value="ace/theme/xcode">Xcode</option>
                        <option value="ace/theme/kuroir">Kuroir</option>
                        <option value="ace/theme/katzenmilch">KatzenMilch</option>
                        <option value="ace/theme/sqlserver">SQL Server</option>
                        <option value="ace/theme/ambiance">Ambiance</option>
                        <option value="ace/theme/chaos">Chaos</option>
                        <option value="ace/theme/clouds_midnight">Clouds Midnight</option>
                        <option value="ace/theme/cobalt">Cobalt</option>
                        <option value="ace/theme/gruvbox">Gruvbox</option>
                        <option value="ace/theme/idle_fingers">Idle Fingers</option>
                        <option value="ace/theme/kr_theme">krTheme</option>
                        <option value="ace/theme/merbivore">Merbivore</option>
                        <option value="ace/theme/mono_industrial">Mono Industrial</option>
                        <option value="ace/theme/pastel_on_dark">Pastel on Dark</option>
                    </select>

                    <button type="button" class="btn btn-success btn-sm mx-1" onclick="formatContent()">Format</button>
                    <button type="button" class="btn btn-info btn-sm mx-1" id="jsonValidationBtn" onclick="validateJsonSyntax()">Validate JSON Syntax</button>
                    <button type="button" class="btn btn-info btn-sm mx-1" id="yamlValidationBtn" onclick="validateYamlSyntax()" style="display: none;">Validate YAML Syntax</button>
                    <button type="button" class="btn btn-primary btn-sm mx-1" onclick="saveFullScreenContent()">Save and Close</button>
                    <button type="button" class="btn btn-primary btn-sm mx-1" onclick="openSearch()">Search</button>
                    <button type="button" class="btn btn-primary btn-sm mx-1" onclick="closeFullScreenEditor()">Cancel</button>
                    <button type="button" class="btn btn-warning btn-sm mx-1" id="toggleFullscreenBtn" onclick="toggleFullscreen()">Fullscreen</button>
                </div>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeFullScreenEditor()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="d-flex justify-content-center align-items-center my-1" id="editorStatus" style="font-weight: bold; font-size: 0.9rem;">
                    <span id="lineColumnDisplay" style="color: blue; font-size: 1.1rem;">Lines: 1, Columns: 1</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="charCountDisplay" style="color: blue; font-size: 1.1rem;">Character Count: 0</span>
                </div>
                    <div class="modal-body" style="padding: 0; height: 100%;">
                <div id="aceEditorContainer" style="height: 100%; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<script>
let isJsonDetected = false;

let aceEditorInstance;

function initializeAceEditor() {
    aceEditorInstance = ace.edit("aceEditorContainer");
    const savedTheme = localStorage.getItem("editorTheme") || "ace/theme/Vibrant Ink";
    aceEditorInstance.setTheme(savedTheme);
    aceEditorInstance.session.setMode("ace/mode/javascript"); 
    aceEditorInstance.setOptions({
        fontSize: "20px",
        wrap: true
    });

    document.getElementById("editorTheme").value = savedTheme;
    aceEditorInstance.getSession().on('change', () => {
        updateEditorStatus();
        detectContentFormat();
    });
    aceEditorInstance.selection.on('changeCursor', updateEditorStatus);
    detectContentFormat(); 
    }

    function openFullScreenEditor() {
        aceEditorInstance.setValue(document.getElementById('fileContent').value, -1); 
        $('#fullScreenEditorModal').modal('show'); 
        updateEditorStatus(); 
    }

    function saveFullScreenContent() {
        document.getElementById('fileContent').value = aceEditorInstance.getValue();
        $('#fullScreenEditorModal').modal('hide'); 
        $('#editModal').modal('hide'); 
        document.getElementById('editForm').submit(); 
    }

    function closeFullScreenEditor() {
        $('#fullScreenEditorModal').modal('hide');
    }

    function changeFontSize() {
        const fontSize = document.getElementById("fontSize").value;
        aceEditorInstance.setFontSize(fontSize);
    }

    function changeEditorTheme() {
        const theme = document.getElementById("editorTheme").value;
        aceEditorInstance.setTheme(theme);
        localStorage.setItem("editorTheme", theme); 
    }

    function openSearch() {
        aceEditorInstance.execCommand("find");
    }

    function detectContentFormat() {
        const content = aceEditorInstance.getValue().trim();

        if (isJsonDetected) {
            document.getElementById("jsonValidationBtn").style.display = "inline-block";
            document.getElementById("yamlValidationBtn").style.display = "none";
            return;
        }

        try {
            JSON.parse(content);
            document.getElementById("jsonValidationBtn").style.display = "inline-block";
            document.getElementById("yamlValidationBtn").style.display = "none";
            isJsonDetected = true; 
        } catch {
        if (isYamlFormat(content)) {
            document.getElementById("jsonValidationBtn").style.display = "none";
            document.getElementById("yamlValidationBtn").style.display = "inline-block";
        } else {
            document.getElementById("jsonValidationBtn").style.display = "none";
            document.getElementById("yamlValidationBtn").style.display = "none";
            }
        }
    }

    function isYamlFormat(content) {
            const yamlPattern = /^(---|\w+:\s)/m;
            return yamlPattern.test(content);
    }

    function validateJsonSyntax() {
            const content = aceEditorInstance.getValue();
            let annotations = [];
        try {
            JSON.parse(content);
            alert("JSON syntax is correct");
        } catch (e) {
            const line = e.lineNumber ? e.lineNumber - 1 : 0;
            annotations.push({
            row: line,
            column: 0,
            text: e.message,
            type: "error"
        });
        aceEditorInstance.session.setAnnotations(annotations);
        alert("JSON syntax error: " + e.message);
        }
    }

    function validateYamlSyntax() {
            const content = aceEditorInstance.getValue();
            let annotations = [];
        try {
            jsyaml.load(content); 
            alert("YAML syntax is correct");
        } catch (e) {
            const line = e.mark ? e.mark.line : 0;
            annotations.push({
            row: line,
            column: 0,
            text: e.message,
            type: "error"
        });
        aceEditorInstance.session.setAnnotations(annotations);
        alert("YAML syntax error: " + e.message);
        }
    }

    function formatContent() {
        const content = aceEditorInstance.getValue();
        const mode = aceEditorInstance.session.$modeId;
        let formattedContent;

        try {
            if (mode === "ace/mode/json") {
                formattedContent = JSON.stringify(JSON.parse(content), null, 4);
                aceEditorInstance.setValue(formattedContent, -1);
                alert("JSON formatted successfully");
            } else if (mode === "ace/mode/javascript") {
                formattedContent = js_beautify(content, { indent_size: 4 });
                aceEditorInstance.setValue(formattedContent, -1);
                alert("JavaScript formatted successfully");
            } else {
                alert("Current mode does not support formatting indentation");
            }
        } catch (e) {
            alert("Formatting error: " + e.message);
        }
    }

    function openEditModal(fileName, fileType) {
        document.getElementById('editingFileName').textContent = fileName;
        document.getElementById('hiddenFileName').value = fileName;
        document.getElementById('hiddenFileType').value = fileType;

        fetch(`?editFile=${encodeURIComponent(fileName)}&fileType=${fileType}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('fileContent').value = data; 
                $('#editModal').modal('show');
            })
            .catch(error => console.error('Failed to retrieve file content:', error));
    }

    function syncEditorContent() {
        document.getElementById('fileContent').value = document.getElementById('fileContent').value;
    }

    function updateEditorStatus() {
        const cursor = aceEditorInstance.getCursorPosition();
        const line = cursor.row + 1;
        const column = cursor.column + 1;
        const charCount = aceEditorInstance.getValue().length;

        document.getElementById('lineColumnDisplay').textContent = `Line: ${line}, Column: ${column}`;
        document.getElementById('charCountDisplay').textContent = `Character Count: ${charCount}`;
    }

    $(document).ready(function() {
        initializeAceEditor();
    });

    document.addEventListener("DOMContentLoaded", function() {
        const renameButtons = document.querySelectorAll(".btn-rename");
        renameButtons.forEach(button => {
            button.addEventListener("click", function() {
                const oldFileName = this.getAttribute("data-filename");
                const fileType = this.getAttribute("data-filetype");
                document.getElementById("oldFileName").value = oldFileName;
                document.getElementById("fileType").value = fileType;
                document.getElementById("newFileName").value = oldFileName;
                $('#renameModal').modal('show');
            });
        });
    });

    function toggleFullscreen() {
        const modal = document.getElementById('fullScreenEditorModal');
    
        if (!document.fullscreenElement) {
            modal.requestFullscreen()
                .then(() => {
                    document.getElementById('toggleFullscreenBtn').textContent = 'Exit Fullscreen';
                })
                .catch((err) => console.error(`Error attempting to enable full-screen mode: ${err.message}`));
        } else {
            document.exitFullscreen()
                .then(() => {
                    document.getElementById('toggleFullscreenBtn').textContent = 'Fullscreen';
                })
                .catch((err) => console.error(`Error attempting to exit full-screen mode: ${err.message}`));
            }
       }

    let fileType = ''; 
    function openUploadModal(type) {
        fileType = type;
        const modal = new bootstrap.Modal(document.getElementById('uploadModal'));
        modal.show();
    }

    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropZone.classList.add('bg-light');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('bg-light');
    });

    dropZone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropZone.classList.remove('bg-light');
        const files = event.dataTransfer.files;
        if (files.length > 0) {
            handleFileUpload(files[0]);
        }
    });

    document.getElementById('selectFileBtn').addEventListener('click', () => {
        document.getElementById('fileInputModal').click();
    });

    document.getElementById('fileInputModal').addEventListener('change', (event) => {
        const files = event.target.files;
        if (files.length > 0) {
            handleFileUpload(files[0]);
        }
    });

    function handleFileUpload(file) {
        const formData = new FormData();
        formData.append(fileType === 'proxy' ? 'fileInput' : 'configFileInput', file);

        fetch('', {
            method: 'POST',
            body: formData,
        })
            .then((response) => response.text())
            .then((result) => {
                alert(result);
                location.reload(); 
        })
            .catch((error) => {
                alert('Upload failed：' + error.message);
        });
    }
</script>
<h2 class="text-center mt-4 mb-4">Mihomo Subscription</h2>

<?php if (isset($message) && $message): ?>
    <div class="alert alert-info">
        <?php echo nl2br(htmlspecialchars($message)); ?>
    </div>
<?php endif; ?>

<?php if (isset($subscriptions) && is_array($subscriptions)): ?>
    <div class="container-fluid">
        <div class="row">
            <?php 
            $maxSubscriptions = 6; 
            for ($i = 0; $i < $maxSubscriptions; $i++): 
                $displayIndex = $i + 1; 
                $url = $subscriptions[$i]['url'] ?? '';
                $fileName = $subscriptions[$i]['file_name'] ?? "subscription_" . ($displayIndex) . ".yaml"; 
            ?>
                <div class="col-md-4 mb-3">
                    <form method="post" class="card shadow-sm">
                        <div class="card-body">
                            <div class="form-group">
                                <h5 for="subscription_url_<?php echo $displayIndex; ?>" class="mb-2">Subscription link  <?php echo $displayIndex; ?></h5>
                                <input type="text" name="subscription_url" id="subscription_url_<?php echo $displayIndex; ?>" value="<?php echo htmlspecialchars($url); ?>" class="form-control" placeholder="Please enter the subscription link">
                            </div>
                            <div class="form-group">
                                <label for="custom_file_name_<?php echo $displayIndex; ?>">Custom file name</label>
                                <input type="text" name="custom_file_name" id="custom_file_name_<?php echo $displayIndex; ?>" value="<?php echo htmlspecialchars($fileName); ?>" class="form-control">
                            </div>
                            <input type="hidden" name="index" value="<?php echo $i; ?>">
                            <div class="text-center mt-3"> 
                                <button type="submit" name="update" class="btn btn-info btn-block"><i class="bi bi-arrow-repeat"></i> Update subscription <?php echo $displayIndex; ?></button>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if (($displayIndex) % 3 == 0 && $displayIndex < $maxSubscriptions): ?>
                    </div><div class="row">
                <?php endif; ?>

            <?php endfor; ?>
        </div>
    </div>
<?php else: ?>
    <p>No subscription information found</p>
<?php endif; ?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <h2 class="mt-4 mb-4 text-center">Auto-update</h2>
        <form method="post" class="text-center">
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cronModal">
                <i class="bi bi-clock"></i> Set up a scheduled task
            </button>
            <button type="submit" name="createShellScript" value="true" class="btn btn-success">
                <i class="bi bi-terminal"></i> Generate an update script
            </button>
            <button type="button" class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#downloadModal">
                <i class="bi bi-download"></i> Update the database
            </button>
            <a class="btn btn-orange btn-sm text-white" target="_blank" href="./filekit.php" style="font-size: 14px; font-weight: bold;">
                <i class="bi bi-file-earmark-text"></i> Open File Assistant
            </a>
        </div>
        </form>
    </div>

    <div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="downloadModalLabel">Select Database to Download</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <form method="GET" action="">
                        <div class="mb-3">
                            <label for="fileSelect" class="form-label">Select File</label>
                            <select class="form-select" id="fileSelect" name="file">
                                <option value="geoip">geoip.metadb</option>
                                <option value="geosite">geosite.dat</option>
                                <option value="cache">cache.db</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary me-2">Download</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="POST">
    <div class="modal fade" id="cronModal" tabindex="-1" aria-labelledby="cronModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cronModalLabel">Set up a Cron scheduled task</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cronExpression" class="form-label">Cron expression</label>
                        <input type="text" class="form-control" id="cronExpression" name="cronExpression" value="0 2 * * *" required>
                    </div>
                    <div class="alert alert-info">
                        <strong>Tip:</strong> Cron expression format:
                        <ul>
                            <li><code>Minutes Hours Day Month Weekday</code></li>
                            <li>Example: Every day at 2 AM: <code>0 2 * * *</code></li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end gap-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="createCronJob" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>
<style>
    .btn-group {
        display: flex;
        gap: 10px; 
        justify-content: center; 
    }
    .btn {
        margin: 0; 
    }

    .table-dark {
        background-color: #6f42c1; 
        color: white; 
    }
    .table-dark th, .table-dark td {
        background-color: #5a32a3; 
    }
    #cronModal .alert {
        text-align: left; 
    }

    #cronModal code {
        white-space: pre-wrap; 
    }

</style>
      <footer class="text-center mt-3">
    <p><?php echo $footer ?></p>
</footer>
