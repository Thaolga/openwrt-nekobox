<?php
ob_start();
include './cfg.php';
$uploadDir = '/www/nekobox/proxy/';
$configDir = '/etc/neko/config/';

ini_set('memory_limit', '256M');

$enable_timezone = isset($_COOKIE['enable_timezone']) && $_COOKIE['enable_timezone'] == '1';

if ($enable_timezone) {
    date_default_timezone_set('Asia/Shanghai');
}

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
                echo '文件上传成功：' . htmlspecialchars(basename($file['name']));
            } else {
                echo '文件上传失败！';
            }
        } else {
            echo '上传错误：' . $file['error'];
        }
    }

    if (isset($_FILES['configFileInput'])) {
        $file = $_FILES['configFileInput'];
        $uploadFilePath = $configDir . basename($file['name']);

        if ($file['error'] === UPLOAD_ERR_OK) {
            if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
                echo '配置文件上传成功：' . htmlspecialchars(basename($file['name']));
            } else {
                echo '配置文件上传失败！';
            }
        } else {
            echo '上传错误：' . $file['error'];
        }
    }

    if (isset($_POST['deleteFile'])) {
        $fileToDelete = $uploadDir . basename($_POST['deleteFile']);
        if (file_exists($fileToDelete) && unlink($fileToDelete)) {
            echo '文件删除成功：' . htmlspecialchars(basename($_POST['deleteFile']));
        } else {
            echo '文件删除失败！';
        }
    }

    if (isset($_POST['deleteConfigFile'])) {
        $fileToDelete = $configDir . basename($_POST['deleteConfigFile']);
        if (file_exists($fileToDelete) && unlink($fileToDelete)) {
            echo '配置文件删除成功：' . htmlspecialchars(basename($_POST['deleteConfigFile']));
        } else {
            echo '配置文件删除失败！';
        }
    }

    if (isset($_POST['oldFileName'], $_POST['newFileName'], $_POST['fileType'])) {
        $oldFileName = basename($_POST['oldFileName']);
        $newFileName = basename($_POST['newFileName']);
    
        if ($_POST['fileType'] === 'proxy') {
            $oldFilePath = $uploadDir . $oldFileName;
            $newFilePath = $uploadDir . $newFileName;
        } elseif ($_POST['fileType'] === 'config') {
            $oldFilePath = $configDir . $oldFileName;
            $newFilePath = $configDir . $newFileName;
        } else {
            echo '无效的文件类型';
            exit;
        }

        if (file_exists($oldFilePath) && !file_exists($newFilePath)) {
            if (rename($oldFilePath, $newFilePath)) {
                echo '文件重命名成功：' . htmlspecialchars($oldFileName) . ' -> ' . htmlspecialchars($newFileName);
            } else {
                echo '文件重命名失败！';
            }
        } else {
            echo '文件重命名失败，文件不存在或新文件名已存在。';
        }
    }

    if (isset($_POST['editFile']) && isset($_POST['fileType'])) {
        $fileToEdit = ($_POST['fileType'] === 'proxy') ? $uploadDir . basename($_POST['editFile']) : $configDir . basename($_POST['editFile']);
        $fileContent = '';
        $editingFileName = htmlspecialchars($_POST['editFile']);

        if (file_exists($fileToEdit)) {
            $handle = fopen($fileToEdit, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $fileContent .= htmlspecialchars($line);
                }
                fclose($handle);
            } else {
                echo '无法打开文件';
            }
        }
    }

    if (isset($_POST['saveContent'], $_POST['fileName'], $_POST['fileType'])) {
        $fileToSave = ($_POST['fileType'] === 'proxy') ? $uploadDir . basename($_POST['fileName']) : $configDir . basename($_POST['fileName']);
        $contentToSave = $_POST['saveContent'];
        file_put_contents($fileToSave, $contentToSave);
        echo '<p>文件内容已更新：' . htmlspecialchars(basename($fileToSave)) . '</p>';
    }

    if (isset($_GET['customFile'])) {
        $customDir = rtrim($_GET['customDir'], '/') . '/';
        $customFilePath = $customDir . basename($_GET['customFile']);
        if (file_exists($customFilePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($customFilePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($customFilePath));
            readfile($customFilePath);
            exit;
        } else {
            echo '文件不存在！';
        }
    }
}

function formatFileModificationTime($filePath) {
    if (file_exists($filePath)) {
        $fileModTime = filemtime($filePath);
        return date('Y-m-d H:i:s', $fileModTime);
    } else {
        return '文件不存在';
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
?>

<?php
$subscriptionPath = '/www/nekobox/proxy/';
$subscriptionFile = $subscriptionPath . 'subscriptions.json';
$subscriptions = [];
while (ob_get_level() > 0) {
    ob_end_flush();
}
function outputMessage($message) {
    echo "<div class='alert alert-info text-start'>" . htmlspecialchars($message) . "</div>";
    ob_flush();
    flush();
}
if (!file_exists($subscriptionPath)) {
    mkdir($subscriptionPath, 0755, true);
}
if (!file_exists($subscriptionFile)) {
    file_put_contents($subscriptionFile, json_encode([]));
}
$subscriptions = json_decode(file_get_contents($subscriptionFile), true);
if (!$subscriptions) {
    for ($i = 0; $i < 3; $i++) {
        $subscriptions[$i] = [
            'url' => '',
            'file_name' => "subscription_{$i}.yaml",
        ];
    }
}
if (isset($_POST['saveSubscription'])) {
    $index = intval($_POST['index']);
    if ($index >= 0 && $index < 3) {
        $url = $_POST['subscription_url'] ?? '';
        $customFileName = $_POST['custom_file_name'] ?? "subscription_{$index}.yaml";
        $subscriptions[$index]['url'] = $url;
        $subscriptions[$index]['file_name'] = $customFileName;
        
        if (!empty($url)) {
            $finalPath = $subscriptionPath . $customFileName;
            $command = sprintf("curl -fsSL -o %s %s", 
                escapeshellarg($finalPath), 
                escapeshellarg($url)
            );
            
            exec($command . ' 2>&1', $output, $return_var);
            
            if ($return_var === 0) {
                outputMessage("订阅链接 {$url} 更新成功！文件已保存到: {$finalPath}");
            } else {
                outputMessage("配置更新失败！错误信息: " . implode("\n", $output));
            }
        } else {
            outputMessage("第" . ($index + 1) . "个订阅链接为空！");
        }
        
        file_put_contents($subscriptionFile, json_encode($subscriptions));
    }
}
$updateCompleted = isset($_POST['saveSubscription']); 
?>

<?php
$subscriptionPath = '/etc/neko/config/';
$dataFile = $subscriptionPath . 'subscription_data.json';

$message = "";
$defaultSubscriptions = [
    [
        'url' => '',
        'file_name' => 'config.json',
    ],
    [
        'url' => '',
        'file_name' => '',
    ],
    [
        'url' => '',
        'file_name' => '',
    ]
];

if (!file_exists($subscriptionPath)) {
    mkdir($subscriptionPath, 0755, true);
}

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode(['subscriptions' => $defaultSubscriptions], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$subscriptionData = json_decode(file_get_contents($dataFile), true);

if (!isset($subscriptionData['subscriptions']) || !is_array($subscriptionData['subscriptions'])) {
    $subscriptionData['subscriptions'] = $defaultSubscriptions;
}

if (isset($_POST['update_index'])) {
    $index = intval($_POST['update_index']);
    $subscriptionUrl = $_POST["subscription_url_$index"] ?? '';
    $customFileName = ($_POST["custom_file_name_$index"] ?? '') ?: 'config.json';

    if ($index < 0 || $index >= count($subscriptionData['subscriptions'])) {
        $message = "无效的订阅索引！";
    } elseif (empty($subscriptionUrl)) {
        $message = "订阅 $index 的链接为空！";
    } else {
        $subscriptionData['subscriptions'][$index]['url'] = $subscriptionUrl;
        $subscriptionData['subscriptions'][$index]['file_name'] = $customFileName;
        $finalPath = $subscriptionPath . $customFileName;

        $originalContent = file_exists($finalPath) ? file_get_contents($finalPath) : '';

        $ch = curl_init($subscriptionUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $fileContent = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($fileContent === false) {
            $message = "订阅 $index 无法下载文件。cURL 错误信息: " . $error;
        } else {
            $fileContent = str_replace("\xEF\xBB\xBF", '', $fileContent);

            $parsedData = json_decode($fileContent, true);
            if ($parsedData === null && json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents($finalPath, $originalContent);
                $message = "订阅 $index 解析 JSON 数据失败！错误信息: " . json_last_error_msg();
            } else {
                if (isset($parsedData['inbounds'])) {
                    $newInbounds = [];

                    foreach ($parsedData['inbounds'] as $inbound) {
                        if (isset($inbound['type']) && $inbound['type'] === 'mixed' && $inbound['tag'] === 'mixed-in') {
                            $newInbounds[] = $inbound;
                        } elseif (isset($inbound['type']) && $inbound['type'] === 'tun') {
                            continue;
                        }
                    }

                    $newInbounds[] = [
                      "tag" => "tun",
                      "type" => "tun",
                      "inet4_address" => "172.19.0.0/30",
                      "inet6_address" => "fdfe:dcba:9876::0/126",
                      "stack" => "system",
                      "auto_route" => true,
                      "strict_route" => true,
                      "sniff" => true,
                      "platform" => [
                        "http_proxy" => [
                          "enabled" => true,
                          "server" => "0.0.0.0",
                          "server_port" => 7890
                        ]
                      ]
                    ];

                    $newInbounds[] = [
                      "tag" => "mixed",
                      "type" => "mixed",
                      "listen" => "0.0.0.0",
                      "listen_port" => 7890,
                      "sniff" => true
                    ];

                    $parsedData['inbounds'] = $newInbounds;
                }

                if (isset($parsedData['experimental']['clash_api'])) {
                    $parsedData['experimental']['clash_api'] = [
                        "external_ui" => "/etc/neko/ui/",
                        "external_controller" => "0.0.0.0:9090",
                        "secret" => "Akun"
                    ];
                }

                $fileContent = json_encode($parsedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                if (file_put_contents($finalPath, $fileContent) === false) {
                    $message = "订阅 $index 无法保存文件到: $finalPath";
                } else {
                    $message = "订阅 $index 更新成功！文件已保存到: {$finalPath}，并成功解析和替换 JSON 数据。";
                }
            }
        }

        file_put_contents($dataFile, json_encode($subscriptionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
?>


<?php
$url = "https://github.com/Thaolga/neko/releases/download/1.2.0/nekoclash.zip";
$zipFile = "/tmp/nekoclash.zip";
$extractPath = "/www/nekobox";
$logFile = "/tmp/update_log.txt";

function logMessage($message) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function downloadFile($url, $path) {
    $fp = fopen($path, 'w+');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    logMessage("文件下载成功，保存到: $path");
}

function unzipFile($zipFile, $extractPath) {
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0755, true);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $filePath = $extractPath . '/' . preg_replace('/^nekoclash\//', '', $filename);

            if (substr($filename, -1) == '/') {
                if (!is_dir($filePath)) {
                    mkdir($filePath, 0755, true);
                }
            } else {
                copy("zip://".$zipFile."#".$filename, $filePath);
            }
        }

        $zip->close();
        logMessage("文件解压成功");
        return true;
    } else {
        return false;
    }
}

if (isset($_POST['update'])) {
    downloadFile($url, $zipFile);
    
    if (unzipFile($zipFile, $extractPath)) {
        echo "规则集更新成功！";
        logMessage("规则集更新成功");
    } else {
        echo "解压失败！";
        logMessage("规则集更新失败");
    }
}
?>

<!doctype html>
<html lang="en" data-bs-theme="<?php echo substr($neko_theme, 0, -4) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sing-box - Neko</title>
    <link rel="icon" href="./assets/img/nekobox.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <script src="./assets/js/feather.min.js"></script>
    <script src="./assets/js/jquery-2.1.3.min.js"></script>
    <script src="./assets/js/neko.js"></script>
</head>
<body>
<div class="container-sm container-bg callout border border-3 rounded-4 col-11">
    <div class="row">
        <a href="./index.php" class="col btn btn-lg">🏠 首页</a>
        <a href="./upload.php" class="col btn btn-lg">📂 Mihomo</a>
        <a href="./upload_sb.php" class="col btn btn-lg">🗂️ Sing-box</a>
        <a href="./box.php" class="col btn btn-lg">💹 转换</a>
        <a href="./nekobox.php" class="col btn btn-lg">📦 文件助手</a>
    <div class="text-center">
      <h1 style="margin-top: 40px; margin-bottom: 20px;">Sing-box 文件管理</h1>
        <h2>代理文件管理 ➤ p核专用</h2>
        <form action="upload_sb.php" method="get" onsubmit="saveSettings()">
        <label for="enable_timezone">启用时区设置:</label>
        <input type="checkbox" id="enable_timezone" name="enable_timezone" value="1">
        <button type="submit" style="background-color: #4CAF50; color: white; border: none; cursor: pointer;"> 提交 </button>
       </form>
    <script>
        function saveSettings() {
            const enableTimezone = document.getElementById('enable_timezone').checked;
            document.cookie = "enable_timezone=" + (enableTimezone ? '1' : '0') + "; path=/";
        }

        function loadSettings() {
            const cookies = document.cookie.split('; ');
            let enableTimezone = '0';
            cookies.forEach(cookie => {
                const [name, value] = cookie.split('=');
                if (name === 'enable_timezone') enableTimezone = value;
            });
            document.getElementById('enable_timezone').checked = (enableTimezone === '1');
        }

        window.onload = loadSettings;
    </script>

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
</style>
  <div class="text-center">
            <table class="table table-dark table-bordered">
                <thead>
                    <tr>
                <th style="width: 30%;">文件名</th>
                <th style="width: 10%;">大小</th>
                <th style="width: 20%;">修改时间</th>
                <th style="width: 40%;">执行操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proxyFiles as $file): ?>
                        <?php $filePath = $uploadDir . $file; ?>
                        <tr>
                            <td><a href="download.php?file=<?php echo urlencode($file); ?>"><?php echo htmlspecialchars($file); ?></a></td>
                            <td class="size-column"><?php echo file_exists($filePath) ? formatSize(filesize($filePath)) : '文件不存在'; ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', filemtime($filePath))); ?></td>
                            <td class="action-column">
                                <div class="btn-group">
                                    <form action="" method="post" class="d-inline">
                            <form action="" method="post" class="d-inline">
                                <input type="hidden" name="deleteFile" value="<?php echo htmlspecialchars($file); ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('确定要删除这个文件吗？');"><i>🗑️</i> 删除</button>
                            </form>
                                    
                            <form action="" method="post" class="d-inline">
                                <input type="hidden" name="editFile" value="<?php echo htmlspecialchars($file); ?>">
                                <input type="hidden" name="fileType" value="proxy">
                                <button type="button" class="btn btn-success btn-sm btn-rename" data-toggle="modal" data-target="#renameModal" data-filename="<?php echo htmlspecialchars($file); ?>" data-filetype="proxy"><i>✏️</i> 重命名</button>
                            </form>
                                    <form action="" method="post" class="d-inline">
                                        <input type="hidden" name="editFile" value="<?php echo htmlspecialchars($file); ?>">
                                        <input type="hidden" name="fileType" value="proxy"> 
                                        <button type="submit" class="btn btn-warning btn-sm"> <i>📝</i> 编辑 </button>
                                    </form>

                            <form action="" method="post" enctype="multipart/form-data" class="form-inline d-inline upload-btn">
                                <input type="file" name="fileInput" class="form-control-file" required id="fileInput-<?php echo htmlspecialchars($file); ?>" style="display: none;" onchange="this.form.submit()">
                                <button type="button" class="btn btn-info btn-sm" onclick="document.getElementById('fileInput-<?php echo htmlspecialchars($file); ?>').click();"><i>📤</i> 上传</button>
                            </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

<div class="table-wrapper">
    <h2>配置文件管理</h2>
    <table class="table table-dark table-bordered">
        <thead>
            <tr>
                <th style="width: 30%;">文件名</th>
                <th style="width: 10%;">大小</th>
                <th style="width: 20%;">修改时间</th>
                <th style="width: 40%;">执行操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($configFiles as $file): ?>
                <?php $filePath = $configDir . $file; ?>
                <tr>
                    <td><a href="download.php?file=<?php echo urlencode($file); ?>"><?php echo htmlspecialchars($file); ?></a></td>
                    <td><?php echo file_exists($filePath) ? formatSize(filesize($filePath)) : '文件不存在'; ?></td>
                    <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', filemtime($filePath))); ?></td>
                    <td>
                        <div class="btn-group">
                            <form action="" method="post" class="d-inline">
                                <input type="hidden" name="deleteConfigFile" value="<?php echo htmlspecialchars($file); ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('确定要删除这个文件吗？');"><i>🗑️</i> 删除</button>
                            </form>
                            <form action="" method="post" class="d-inline">
                                <input type="hidden" name="editFile" value="<?php echo htmlspecialchars($file); ?>">
                                <button type="button" class="btn btn-success btn-sm btn-rename" data-toggle="modal" data-target="#renameModal" data-filename="<?php echo htmlspecialchars($file); ?>" data-filetype="config"><i>✏️</i> 重命名</button>
                            </form>
                            <form action="" method="post" class="d-inline">
                                <input type="hidden" name="editFile" value="<?php echo htmlspecialchars($file); ?>">
                                <input type="hidden" name="fileType" value="<?php echo htmlspecialchars($file); ?>">
                                <button type="submit" class="btn btn-warning btn-sm"><i>📝</i> 编辑</button>
                            </form>
                            <form action="" method="post" enctype="multipart/form-data" class="form-inline d-inline upload-btn">
                                <input type="file" name="configFileInput" class="form-control-file" required id="fileInput-<?php echo htmlspecialchars($file); ?>" style="display: none;" onchange="this.form.submit()">
                                <button type="button" class="btn btn-info btn-sm" onclick="document.getElementById('fileInput-<?php echo htmlspecialchars($file); ?>').click();"><i>📤</i> 上传</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (isset($fileContent)): ?>
    <?php if (isset($_POST['editFile'])): ?>
        <?php $fileToEdit = ($_POST['fileType'] === 'proxy') ? $uploadDir . basename($_POST['editFile']) : $configDir . basename($_POST['editFile']); ?>
        <h2 class="mt-5">编辑文件: <?php echo $editingFileName; ?></h2>
        <p>最后更新日期: <?php echo date('Y-m-d H:i:s', filemtime($fileToEdit)); ?></p>

        <div class="btn-group mb-3">
            <button type="button" class="btn btn-primary" id="toggleBasicEditor">普通编辑器</button>
            <button type="button" class="btn btn-warning" id="toggleAceEditor">高级编辑器</button>
            <button type="button" class="btn btn-info" id="toggleFullScreenEditor">全屏编辑</button>
        </div>

        <div class="editor-container">
            <form action="" method="post">
                <textarea name="saveContent" id="basicEditor" class="editor"><?php echo $fileContent; ?></textarea><br>

                <div id="aceEditorContainer" class="d-none resizable" style="height: 400px; width: 100%;"></div>

                <div id="fontSizeContainer" class="d-none mb-3">
                    <label for="fontSizeSelector">字体大小:</label>
                    <select id="fontSizeSelector" class="form-control" style="width: auto; display: inline-block;">
                        <option value="18px">18px</option>
                        <option value="20px">20px</option>
                        <option value="24px">24px</option>
                        <option value="26px">26px</option>
                    </select>
                </div>

                <input type="hidden" name="fileName" value="<?php echo htmlspecialchars($_POST['editFile']); ?>">
                <input type="hidden" name="fileType" value="<?php echo htmlspecialchars($_POST['fileType']); ?>">
                <button type="submit" class="btn btn-primary mt-2" onclick="syncEditorContent()"><i>💾</i> 保存内容</button>
            </form>
            <button id="closeEditorButton" class="close-fullscreen" onclick="closeEditor()">X</button>
            <div id="aceEditorError" class="error-popup d-none">
                <span id="aceEditorErrorMessage"></span>
                <button id="closeErrorPopup">关闭</button>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
        <h1 style="margin-top: 20px; margin-bottom: 20px;">Sing-box 订阅</h1>
    <style>
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <form method="post">
        <button type="submit" name="update">🔄 更新规则集</button>
    </form>
</body>
     </br>
        <?php if ($message): ?>
            <p><?php echo nl2br(htmlspecialchars($message)); ?></p>
        <?php endif; ?>
<form method="post">
    <div class="row">
        <?php for ($i = 0; $i < 3; $i++): ?>
            <div class="col-md-4 mb-3">
                <div class="card subscription-card p-2">
                    <div class="card-body p-2">
                        <h6 class="card-title text-primary">订阅链接 <?php echo $i + 1; ?></h6>
                        <div class="form-group mb-2">
                            <input type="text" name="subscription_url_<?php echo $i; ?>" id="subscription_url_<?php echo $i; ?>" class="form-control form-control-sm white-text" placeholder="订阅链接" value="<?php echo htmlspecialchars($subscriptionData['subscriptions'][$i]['url'] ?? ''); ?>">
                        </div>
                        <div class="form-group mb-2">
                            <label for="custom_file_name_<?php echo $i; ?>" class="text-primary">自定义文件名 <?php echo ($i === 0) ? '(固定为 config.json)' : ''; ?></label>
                            <input type="text" name="custom_file_name_<?php echo $i; ?>" id="custom_file_name_<?php echo $i; ?>" class="form-control form-control-sm white-text" value="<?php echo htmlspecialchars($subscriptionData['subscriptions'][$i]['file_name'] ?? ($i === 0 ? 'config.json' : '')); ?>" <?php echo ($i === 0) ? 'readonly' : ''; ?>>
                        </div>
                        <button type="submit" name="update_index" value="<?php echo $i; ?>" class="btn btn-info btn-sm"><i>🔄</i> 更新订阅 <?php echo $i + 1; ?></button>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</form>

<h2 class="text-success text-center mt-4 mb-4">订阅管理 ➤ p核专用</h2>
<div class="help-text mb-3 text-start">
    <strong>1. 注意：</strong> 通用模板（<code>puernya.json</code>）最多支持<strong>3个</strong>订阅链接，请勿更改默认名称。
</div>
 <div class="help-text mb-3 text-start"> 
    <strong>2. 只支持Clash和Sing-box格式的订阅，不支持通用格式
    </div>
<div class="help-text mb-3 text-start"> 
    <strong>3. 保存与更新：</strong> 填写完毕后，请点击"更新配置"按钮进行保存。
</div>
        <div class="row">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">订阅链接 <?php echo ($i + 1); ?></h5>
                            <form method="post">
                                <div class="input-group mb-3">
                                    <input type="text" name="subscription_url" id="subscriptionurl<?php echo $i; ?>" 
                                           value="<?php echo htmlspecialchars($subscriptions[$i]['url']); ?>" required 
                                           class="form-control" placeholder="输入链接">
                                    <input type="text" name="custom_file_name" id="custom_filename<?php echo $i; ?>" 
                                           value="<?php echo htmlspecialchars($subscriptions[$i]['file_name']); ?>" 
                                           class="form-control" placeholder="自定义文件名">
                                    <input type="hidden" name="index" value="<?php echo $i; ?>">
                                    <button type="submit" name="saveSubscription" class="btn btn-success ml-2">
                                        <i>🔄</i> 更新
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>

    <script>
        function showCompletionMessage() {
            var messageDiv = document.createElement('div');
            messageDiv.textContent = '更新完成';
            messageDiv.style.position = 'fixed';
            messageDiv.style.top = '20px';
            messageDiv.style.left = '50%';
            messageDiv.style.transform = 'translateX(-50%)';
            messageDiv.style.padding = '10px 20px';
            messageDiv.style.backgroundColor = '#4CAF50';
            messageDiv.style.color = 'white';
            messageDiv.style.borderRadius = '5px';
            messageDiv.style.zIndex = '1000';
            document.body.appendChild(messageDiv);

            setTimeout(function() {
                messageDiv.style.transition = 'opacity 0.5s';
                messageDiv.style.opacity = '0';
                setTimeout(function() {
                    document.body.removeChild(messageDiv);
                }, 500);
            }, 3000);
        }

        <?php if ($updateCompleted): ?>
        window.onload = showCompletionMessage;
        <?php endif; ?>
    </script>

        <div class="modal fade" id="renameModal" tabindex="-1" role="dialog" aria-labelledby="renameModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="renameModalLabel">重命名文件</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="renameForm" action="" method="post">
                            <input type="hidden" name="oldFileName" id="oldFileName">
                            <input type="hidden" name="fileType" id="fileType">
                            <div class="form-group">
                                <label for="newFileName">新文件名</label>
                                <input type="text" class="form-control" id="newFileName" name="newFileName" required>
                            </div>
                            <p>是否确定要重命名这个文件?</p>
                            <div class="form-group text-right">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                                <button type="submit" class="btn btn-primary">确定</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

<script src="./assets/bootstrap/jquery-3.5.1.slim.min.js"></script>
<script src="./assets/bootstrap/popper.min.js"></script>
<script src="./assets/bootstrap/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>

<script>
    $('#renameModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); 
        var oldFileName = button.data('filename'); 
        var fileType = button.data('filetype');
        var modal = $(this);
        modal.find('#oldFileName').val(oldFileName); 
        modal.find('#fileType').val(fileType);
        modal.find('#newFileName').val(oldFileName); 
    });

    function closeEditor() {
        window.location.href = window.location.href; 
    }

    var aceEditor = ace.edit("aceEditorContainer");
    aceEditor.setTheme("ace/theme/monokai");
    aceEditor.session.setMode("ace/mode/json");
    aceEditor.session.setUseWorker(true);
    aceEditor.getSession().setUseWrapMode(true);

    function setDefaultFontSize() {
        var defaultFontSize = '20px';
        document.getElementById('basicEditor').style.fontSize = defaultFontSize;
        aceEditor.setFontSize(defaultFontSize);
    }

    document.addEventListener('DOMContentLoaded', setDefaultFontSize);

    aceEditor.setValue(document.getElementById('basicEditor').value);

    aceEditor.session.on('changeAnnotation', function() {
        var annotations = aceEditor.getSession().getAnnotations();
        if (annotations.length > 0) {
            var errorMessage = annotations[0].text;
            var errorLine = annotations[0].row + 1;
            showErrorPopup('JSON 语法错误: 行 ' + errorLine + ': ' + errorMessage);
        } else {
            hideErrorPopup();
        }
    });

    document.getElementById('toggleBasicEditor').addEventListener('click', function() {
        document.getElementById('basicEditor').classList.remove('d-none');
        document.getElementById('aceEditorContainer').classList.add('d-none');
        document.getElementById('fontSizeContainer').classList.remove('d-none');
    });

    document.getElementById('toggleAceEditor').addEventListener('click', function() {
        document.getElementById('basicEditor').classList.add('d-none');
        document.getElementById('aceEditorContainer').classList.remove('d-none');
        document.getElementById('fontSizeContainer').classList.remove('d-none');
        aceEditor.setValue(document.getElementById('basicEditor').value);
    });

    document.getElementById('toggleFullScreenEditor').addEventListener('click', function() {
        var editorContainer = document.getElementById('aceEditorContainer');
        if (!document.fullscreenElement) {
            editorContainer.requestFullscreen().then(function() {
                aceEditor.resize();
                enableFullScreenMode();
            });
        } else {
            document.exitFullscreen().then(function() {
                aceEditor.resize();
                disableFullScreenMode();
            });
        }
    });

    function syncEditorContent() {
        if (!document.getElementById('basicEditor').classList.contains('d-none')) {
            aceEditor.setValue(document.getElementById('basicEditor').value);
        } else {
            document.getElementById('basicEditor').value = aceEditor.getValue();
        }
    }

    document.getElementById('fontSizeSelector').addEventListener('change', function() {
        var newFontSize = this.value;
        aceEditor.setFontSize(newFontSize);
        document.getElementById('basicEditor').style.fontSize = newFontSize;
    });

    function enableFullScreenMode() {
        document.getElementById('aceEditorContainer').classList.add('fullscreen');
        document.getElementById('aceEditorError').classList.add('fullscreen-popup');
        document.getElementById('fullscreenCancelButton').classList.remove('d-none');
    }

    function disableFullScreenMode() {
        document.getElementById('aceEditorContainer').classList.remove('fullscreen');
        document.getElementById('aceEditorError').classList.remove('fullscreen-popup');
        document.getElementById('fullscreenCancelButton').classList.add('d-none');
    }

    function showErrorPopup(message) {
        var errorPopup = document.getElementById('aceEditorError');
        var errorMessage = document.getElementById('aceEditorErrorMessage');
        errorMessage.innerText = message;
        errorPopup.classList.remove('d-none');
    }

    function hideErrorPopup() {
        var errorPopup = document.getElementById('aceEditorError');
        errorPopup.classList.add('d-none');
    }

    document.getElementById('closeErrorPopup').addEventListener('click', function() {
        hideErrorPopup();
    });

    (function() {
        const resizable = document.querySelector('.resizable');
        if (!resizable) return;

        const handle = document.createElement('div');
        handle.className = 'resize-handle';
        resizable.appendChild(handle);

        handle.addEventListener('mousedown', function(e) {
            e.preventDefault();
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });

        function onMouseMove(e) {
            resizable.style.width = e.clientX - resizable.getBoundingClientRect().left + 'px';
            resizable.style.height = e.clientY - resizable.getBoundingClientRect().top + 'px';
            aceEditor.resize();
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }
    })();
</script>

<style>
    .btn--warning {
        background-color: #ff9800;
        color: white !important; 
        border: none; 
        padding: 10px 20px; 
        border-radius: 5px; 
        cursor: pointer; 
        font-family: Arial, sans-serif; 
        font-weight: bold; 
    }

    .resizable {
        position: relative;
        overflow: hidden;
    }

    .resizable .resize-handle {
        width: 10px;
        height: 10px;
        background: #ddd;
        position: absolute;
        bottom: 0;
        right: 0;
        cursor: nwse-resize;
        z-index: 10;
    }

    .fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999;
        background-color: #1a1a1a;
    }

    #aceEditorError {
        color: red;
        font-weight: bold;
        margin-top: 10px;
    }

    .fullscreen-popup {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        z-index: 9999;
    }

    .close-fullscreen {
        position: fixed;
        top: 10px;
        right: 10px;
        z-index: 10000;
        background-color: red;
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    #aceEditorError button {
        margin-top: 10px;
        padding: 5px 10px;
        background-color: #ff6666;
        border: none;
        cursor: pointer;
    }

    textarea.editor {
        font-size: 20px;
        width: 100%; 
        height: 400px; 
        resize: both; 
    }

    .ace_editor {
        font-size: 20px;
    }
</style>
</body>

