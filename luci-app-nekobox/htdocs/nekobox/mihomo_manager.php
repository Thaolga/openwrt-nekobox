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
?>

<?php
$subscriptionPath = '/etc/neko/proxy_provider/';
$subscriptionFile = $subscriptionPath . 'subscriptions.json';
$message = "";
$subscriptions = [];
$updateCompleted = false;

function outputMessage($message) {
    if (!isset($_SESSION['update_messages'])) {
        $_SESSION['update_messages'] = [];
    }
    $_SESSION['update_messages'][] = $message;
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
    $url = $_POST['subscription_url'] ?? '';
    $customFileName = $_POST['custom_file_name'] ?? "subscription_" . ($index + 1) . ".yaml";  

    $subscriptions[$index]['url'] = $url;
    $subscriptions[$index]['file_name'] = $customFileName;

    if (!empty($url)) {
        $finalPath = $subscriptionPath . $customFileName;

        $command = "wget -q --show-progress -O {$finalPath} {$url}";
        exec($command . ' 2>&1', $output, $return_var);

        if ($return_var !== 0) {
            $command = "curl -s -o {$finalPath} {$url}";
            exec($command . ' 2>&1', $output, $return_var);
        }

        if ($return_var === 0) {
            $_SESSION['update_messages'] = array();
            $_SESSION['update_messages'][] = '<div class="alert alert-warning" style="margin-bottom: 8px;">
                <strong>⚠️ Instructions:</strong>
                <ul class="mb-0 pl-3">
                    <li>The general template (mihomo.yaml) supports up to <strong>6</strong> subscription links</li>
                    <li>Please do not change the default file name</li>
                    <li>This template supports all format subscription links, no additional conversion is required</li>
                </ul>
            </div>';

            $fileContent = file_get_contents($finalPath);
            $decodedContent = base64_decode($fileContent);

            if ($decodedContent === false) {
                $_SESSION['update_messages'][] = "Base64 decoding failed, please check if the downloaded file content is valid!";            
                $message = "Base64 decoding failed";
            } else {
                $clashFile = $subscriptionPath . $customFileName;
                file_put_contents($clashFile, "# Clash Meta Config\n\n" . $decodedContent);
                $_SESSION['update_messages'][] = "Subscription link {$url} updated successfully, and the decoded content has been saved to: {$clashFile}";
                $message = 'Update successful';
                $updateCompleted = true;
            }
        } else {
            $_SESSION['update_messages'][] = "Configuration update failed! Error message: " . implode("\n", $output);
            $message = 'Update failed';
        }
    } else {
        $_SESSION['update_messages'][] = "The subscription link at position " . ($index + 1) . " is empty!";
        $message = 'Update failed';
    }

    file_put_contents($subscriptionFile, json_encode($subscriptions));
    }
?>
<?php
$shellScriptPath = '/etc/neko/core/update_mihomo.sh';
$LOG_FILE = '/tmp/update_subscription.log';
$JSON_FILE = '/etc/neko/proxy_provider/subscriptions.json';
$SAVE_DIR = '/etc/neko/proxy_provider';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['createShellScript'])) {
        $shellScriptContent = <<<EOL
#!/bin/bash

LOG_FILE="$LOG_FILE"
JSON_FILE="$JSON_FILE"
SAVE_DIR="$SAVE_DIR"

if [ ! -f "\$JSON_FILE" ]; then
    echo "\$(date): Error: JSON file does not exist: \$JSON_FILE" >> "\$LOG_FILE"
    exit 1
fi

echo "\$(date): Start processing subscription links..." >> "\$LOG_FILE"

jq -c '.[]' "\$JSON_FILE" | while read -r ITEM; do
    URL=\$(echo "\$ITEM" | jq -r '.url')         
    FILE_NAME=\$(echo "\$ITEM" | jq -r '.file_name')  

    if [ -z "\$URL" ] || [ "\$URL" == "null" ]; then
        echo "\$(date): Skip empty URLs and filenames: \$FILE_NAME" >> "\$LOG_FILE"
        continue
    fi

    if [ -z "\$FILE_NAME" ] || [ "\$FILE_NAME" == "null" ]; then
        echo "\$(date): Error: Filename is empty, skip this link: \$URL" >> "\$LOG_FILE"
        continue
    fi

    SAVE_PATH="\$SAVE_DIR/\$FILE_NAME"
    TEMP_PATH="\$SAVE_PATH.temp"  
    echo "\$(date): Downloading link: \$URL Saved to temporary file: \$TEMP_PATH" >> "\$LOG_FILE"

    wget -q -O "\$TEMP_PATH" "\$URL"

    if [ \$? -eq 0 ]; then
        echo "\$(date): File download successful: \$TEMP_PATH" >> "\$LOG_FILE"
        
        base64 -d "\$TEMP_PATH" > "\$SAVE_PATH"

        if [ \$? -eq 0 ]; then
            echo "\$(date): File decoded successfully: \$SAVE_PATH" >> "\$LOG_FILE"
        else
            echo "\$(date): Error: File decoding failed: \$SAVE_PATH" >> "\$LOG_FILE"
        fi

        rm -f "\$TEMP_PATH"
    else
        echo "\$(date): Error: File download failed: \$URL" >> "\$LOG_FILE"
    fi
done

echo "\$(date): All subscription links processed" >> "\$LOG_FILE"
EOL;

        if (file_put_contents($shellScriptPath, $shellScriptContent) !== false) {
            chmod($shellScriptPath, 0755);
            echo "<div class='alert alert-success'>Shell script has been created successfully! Path: $shellScriptPath</div>";
        } else {
            echo "<div class='alert alert-danger'>Unable to create Shell script, please check permissions</div>";
        }
    }
}
?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['createCronJob'])) {
        $cronExpression = trim($_POST['cronExpression']);

        if (empty($cronExpression)) {
            echo "<div class='alert alert-warning'>Cron expression cannot be empty</div>";
            exit;
        }

        $cronJob = "$cronExpression /etc/neko/core/update_mihomo.sh > /dev/null 2>&1";
        exec("crontab -l | grep -v '/etc/neko/core/update_mihomo.sh' | crontab -");
        exec("(crontab -l; echo '$cronJob') | crontab -");
        echo "<div class='alert alert-success'>Cron job has been successfully added or updated!</div>";
    }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
<div class="position-fixed w-100 d-flex justify-content-center" style="top: 20px; z-index: 1050;">
    <div id="updateAlert" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none; min-width: 300px; max-width: 600px;">
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
            <strong>Update Complete</strong>
        </div>
        <div id="updateMessages" class="small mt-2"></div>
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

@media (max-width: 767px) {
    .table th,
    .table td {
        padding: 6px 8px; 
        font-size: 14px;
    }

    .table th:nth-child(1), .table td:nth-child(1) {
        width: 25%; 
    }
    .table th:nth-child(2), .table td:nth-child(2) {
        width: 20%; 
    }
    .table th:nth-child(3), .table td:nth-child(3) {
        width: 25%; 
    }
    .table th:nth-child(4), .table td:nth-child(4) {
        width: 100%; 
    }

.btn-group, .d-flex {
    display: flex;
    flex-wrap: wrap; 
    justify-content: center;
    gap: 5px;
}

.btn-group .btn {
    flex: 1 1 auto; 
    font-size: 12px;
    padding: 6px 8px;
}

.btn-group .btn:last-child {
    margin-right: 0;
  }
}

@media (max-width: 767px) {
    .btn-rename {
    width: 70px !important; 
    font-size: 0.6rem; 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
    display: inline-block;
    text-align: center; 
}

.btn-group {
    display: flex;
    gap: 10px; 
    justify-content: center; 
}

.btn {
    margin: 0; 
}

td {
    vertical-align: middle;
}

.action-btn {
    padding: 6px 12px; 
    font-size: 0.85rem; 
    display: inline-block;
}

.btn-group.d-flex {
    flex-wrap: wrap;
}
</style>

<script>
function showUpdateAlert() {
    const alert = $('#updateAlert');
    const messages = <?php echo json_encode($_SESSION['update_messages'] ?? []); ?>;
    
    if (messages.length > 0) {
        const messagesHtml = messages.map(msg => `<div>${msg}</div>`).join('');
        $('#updateMessages').html(messagesHtml);
    }
    
    alert.show().addClass('show');
    
    setTimeout(function() {
        alert.removeClass('show');
        setTimeout(function() {
            alert.hide();
            $('#updateMessages').html('');
        }, 150);
    }, 12000);
}

<?php if (!empty($message)): ?>
    $(document).ready(function() {
        showUpdateAlert();
    });
<?php endif; ?>
</script>
<div class="container-sm container-bg callout border border-3 rounded-4 col-11">
    <div class="row">
        <a href="./index.php" class="col btn btn-lg">🏠 Home</a>
        <a href="./mihomo_manager.php" class="col btn btn-lg">🗃️ Manager</a>
        <a href="./singbox.php" class="col btn btn-lg">🏦 Sing-box</a>
        <a href="./subscription.php" class="col btn btn-lg">🏣 Singbox</a>
        <a href="./mihomo.php" class="col btn btn-lg">🏪 Mihomo</a>
    </div>
    <div class="text-center">
        <h2 style="margin-top: 40px; margin-bottom: 20px;">File Management</h2>
       <div class="card mb-4">
    <div class="card-body">
<div class="container">
    <div class="table-responsive">
        <table class="table table-striped table-bordered text-center">
            <thead class="thead-dark">
                <tr>
                    <th style="width: 18%;">File Name</th>
                    <th style="width: 9%;">Size</th>
                    <th style="width: 13%;">Modification Time</th>
                    <th style="width: 13%;">File Type</th>
                    <th style="width: 37%;">Action</th>
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
                $fileTypes = array_merge(array_fill(0, count($proxyFiles), 'Proxy File'), array_fill(0, count($configFiles), 'Configuration File'));
                
                foreach ($allFiles as $index => $file) {
                    $filePath = $allFilePaths[$index];
                    $fileType = $fileTypes[$index];
                ?>
                    <tr>
                        <td class="align-middle">
                            <a href="download.php?file=<?php echo urlencode($file); ?>"><?php echo htmlspecialchars($file); ?></a>
                        </td>
                        <td class="align-middle">
                            <?php echo file_exists($filePath) ? formatSize(filesize($filePath)) : 'File Not Found'; ?>
                        </td>
                        <td class="align-middle">
                            <?php echo htmlspecialchars(date('Y-m-d H:i:s', filemtime($filePath))); ?>
                        </td>
                        <td class="align-middle">
                            <?php echo htmlspecialchars($fileType); ?>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <?php if ($fileType == 'Proxy File'): ?>
                                    <form action="" method="post" class="d-inline">
                                        <input type="hidden" name="deleteFile" value="<?php echo htmlspecialchars($file); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm mx-1" onclick="return confirm('Are you sure you want to delete this file？');"><i>🗑️</i> Delete</button>
                                    </form>
                                    <form action="" method="post" class="d-inline">
                                        <input type="hidden" name="oldFileName" value="<?php echo htmlspecialchars($file); ?>">
                                        <input type="hidden" name="fileType" value="proxy">
                                        <button type="button" class="btn btn-success btn-sm mx-1 btn-rename" data-toggle="modal" data-target="#renameModal" data-filename="<?php echo htmlspecialchars($file); ?>" data-filetype="proxy"><i>✏️</i> Rename</button>
                                    </form>
                                    <form action="" method="post" class="d-inline">
                                        <button type="button" class="btn btn-warning btn-sm mx-1" onclick="openEditModal('<?php echo htmlspecialchars($file); ?>', 'proxy')"><i>📝</i> Rename</button>
                                    </form>
                                    <form action="" method="post" enctype="multipart/form-data" class="d-inline upload-btn">
                                        <input type="file" name="fileInput" class="form-control-file" required id="fileInput-<?php echo htmlspecialchars($file); ?>" style="display: none;" onchange="this.form.submit()">
                                        <button type="button" class="btn btn-info btn-sm mx-1" onclick="document.getElementById('fileInput-<?php echo htmlspecialchars($file); ?>').click();"><i>📤</i> Upload</button>
                                    </form>
                                <?php else: ?>
                                    <form action="" method="post" class="d-inline">
                                        <input type="hidden" name="deleteConfigFile" value="<?php echo htmlspecialchars($file); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm mx-1" onclick="return confirm('Are you sure you want to delete this file？');"><i>🗑️</i> Delete</button>
                                    </form>
                                    <form action="" method="post" class="d-inline">
                                        <input type="hidden" name="oldFileName" value="<?php echo htmlspecialchars($file); ?>">
                                        <input type="hidden" name="fileType" value="config">
                                        <button type="button" class="btn btn-success btn-sm mx-1 btn-rename" data-toggle="modal" data-target="#renameModal" data-filename="<?php echo htmlspecialchars($file); ?>" data-filetype="config"><i>✏️</i> Rename</button>
                                    </form>
                                    <form action="" method="post" class="d-inline">
                                        <button type="button" class="btn btn-warning btn-sm mx-1" onclick="openEditModal('<?php echo htmlspecialchars($file); ?>', 'config')"><i>📝</i> Rename</button>
                                    </form>
                                    <form action="" method="post" enctype="multipart/form-data" class="d-inline upload-btn">
                                        <input type="file" name="configFileInput" class="form-control-file" required id="fileInput-<?php echo htmlspecialchars($file); ?>" style="display: none;" onchange="this.form.submit()">
                                        <button type="button" class="btn btn-info btn-sm mx-1" onclick="document.getElementById('fileInput-<?php echo htmlspecialchars($file); ?>').click();"><i>📤</i>  Upload</button>
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

<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renameModalLabel">Rename File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
</script>
<h2 class="text-center mt-4 mb-4">Mihomo Subscription Management</h2>

<?php if (isset($message) && $message): ?>
    <div class="alert alert-info">
        <?php echo nl2br(htmlspecialchars($message)); ?>
    </div>
<?php endif; ?>

<?php if (isset($subscriptions) && is_array($subscriptions)): ?>
    <div class="row">
        <?php 
        $maxSubscriptions = 6; 
        for ($i = 0; $i < $maxSubscriptions; $i++): 
            $displayIndex = $i + 1; 
            $url = $subscriptions[$i]['url'] ?? '';
            $fileName = $subscriptions[$i]['file_name'] ?? "subscription_" . ($displayIndex) . ".yaml"; 
        ?>
            <div class="col-md-4 mb-3 px-4">
                <form method="post" class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <h5 for="subscription_url_<?php echo $displayIndex; ?>" class="mb-2">Subscription link <?php echo $displayIndex; ?></h5>
                            <input type="text" name="subscription_url" id="subscription_url_<?php echo $displayIndex; ?>" value="<?php echo htmlspecialchars($url); ?>" class="form-control" placeholder="Please enter the subscription link">
                        </div>
                        <div class="form-group">
                            <label for="custom_file_name_<?php echo $displayIndex; ?>">Custom file name</label>
                            <input type="text" name="custom_file_name" id="custom_file_name_<?php echo $displayIndex; ?>" value="<?php echo htmlspecialchars($fileName); ?>" class="form-control">
                        </div>
                        <input type="hidden" name="index" value="<?php echo $i; ?>">
                        <div class="text-center mt-3"> 
                            <button type="submit" name="update" class="btn btn-info">🔄 Update subscription <?php echo $displayIndex; ?></button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (($displayIndex) % 3 == 0 && $displayIndex < $maxSubscriptions): ?>
                </div><div class="row">
            <?php endif; ?>

        <?php endfor; ?>
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
             <button type="button" class="btn btn-primary mx-2" data-bs-toggle="modal" data-bs-target="#cronModal">
                Set up a scheduled task
            </button>
            <button type="submit" name="createShellScript" value="true" class="btn btn-success mx-2">
                Generate an update script
            </button>
             <td>
            <a class="btn btn-info btn-sm text-white" target="_blank" href="./filekit.php" style="font-size: 14px; font-weight: bold;">
                Open File Assistant
            </a>
        </td>
        </form>
    </div>
<form method="POST">
    <div class="modal fade" id="cronModal" tabindex="-1" aria-labelledby="cronModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cronModalLabel">Set up a Cron scheduled task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cronExpression" class="form-label">Cron expression</label>
                        <input type="text" class="form-control" id="cronExpression" name="cronExpression" placeholder="e.g., 0 2 * * *" required>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">modal</button>
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
