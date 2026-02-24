<?php
ini_set('memory_limit', '1024M');
$timezone = trim(shell_exec("uci get system.@system[0].zonename 2>/dev/null"));
date_default_timezone_set($timezone ?: 'UTC');
$RECENT_MAX = 15;
$ROOT_DIR = '/';
$MEDIA_CACHE_DIR = './lib/';
$MUSIC_CACHE_FILE = $MEDIA_CACHE_DIR . 'music_cache.json';
$VIDEO_CACHE_FILE = $MEDIA_CACHE_DIR . 'video_cache.json';
$IMAGE_CACHE_FILE = $MEDIA_CACHE_DIR . 'image_cache.json';
$RECYCLE_BIN_DIR = './recycle_bin/';
$RECYCLE_BIN_ENABLED = true;
$RECYCLE_BIN_DAYS = 30;

$settingsFile = './config/recycle_bin.json';
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
    if ($settings) {
        $RECYCLE_BIN_ENABLED = $settings['enabled'] ?? true;
        $RECYCLE_BIN_DAYS = $settings['days'] ?? 30;
    }
}

$EXCLUDE_DIRS = [
    '/dev', '/run', '/var/lock', '/var/run', '/overlay/upper'
];

$ARCHIVE_EXT = [
    'zip', 'tar', 'gz', 'bz2', '7z', 'rar', 'tgz', 'tbz2'
];

$ARCHIVE_TOOLS = [
    'zip' => 'zip',
    'unzip' => 'unzip',
    'tar' => 'tar',
    'gzip' => 'gzip',
    'gunzip' => 'gunzip',
    'bzip2' => 'bzip2',
    'bunzip2' => 'bunzip2',
    '7z' => '7z',
    'rar' => 'unrar'
];

$TYPE_EXT = [
    'music'  => ['mp3', 'ogg', 'wav', 'flac', 'm4a', 'aac'],
    'video'  => ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm'],
    'image'  => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'],
    'text'   => ['txt', 'log', 'conf', 'ini', 'json', 'xml', 'html', 'css', 'js', 'php', 'py', 'sh', 'md'],
    'archive'=> ['zip', 'tar', 'gz', 'bz2', '7z', 'rar']
];

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'list_files') {
        header('Content-Type: application/json');
        
        $path = isset($_GET['path']) ? urldecode($_GET['path']) : $ROOT_DIR;
        $path = preg_replace('#/+#', '/', $path);
        
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        foreach ($EXCLUDE_DIRS as $exclude) {
            if (strpos($realPath, $exclude) === 0) {
                echo json_encode(['success' => false, 'error' => 'Path excluded']);
                exit;
            }
        }
        
        $items = [];
        if (is_dir($realPath) && is_readable($realPath)) {
            try {
                $iterator = new DirectoryIterator($realPath);
                
                foreach ($iterator as $item) {
                    if ($item->isDot()) continue;
                    
                    $itemPath = $item->getPathname();
                    $itemRealPath = realpath($itemPath);
                    
                    if (!$itemRealPath) continue;
                    
                    $excluded = false;
                    foreach ($EXCLUDE_DIRS as $exclude) {
                        if (strpos($itemRealPath, $exclude) === 0) {
                            $excluded = true;
                            break;
                        }
                    }
                    
                    if ($excluded) continue;
                    
                    $isDir = $item->isDir();
                    $size = $isDir ? 0 : $item->getSize();
                    $perms = substr(sprintf('%o', $item->getPerms()), -4);

                    $owner = 'unknown';
                    try {
                        if (function_exists('posix_getpwuid')) {
                            $ownerInfo = posix_getpwuid($item->getOwner());
                            $owner = $ownerInfo['name'] ?? 'unknown';
                        } else {
                            $owner = $item->getOwner();
                        }
                    } catch (Exception $e) {
                        $owner = 'unknown';
                    }
                
                    $group = 'unknown';
                    try {
                        if (function_exists('posix_getgrgid')) {
                            $groupInfo = posix_getgrgid($item->getGroup());
                            $group = $groupInfo['name'] ?? 'unknown';
                        } else {
                            $group = $item->getGroup();
                        }
                    } catch (Exception $e) {
                        $group = 'unknown';
                    }  
                    
                    $type = 'file';
                    $ext = strtolower($item->getExtension());
                    $icon = 'fa-file';
                    
                    if ($isDir) {
                        $type = 'directory';
                        $icon = 'fa-folder';
                    } elseif (in_array($ext, $TYPE_EXT['music'])) {
                        $type = 'music';
                        $icon = 'fa-music';
                    } elseif (in_array($ext, $TYPE_EXT['video'])) {
                        $type = 'video';
                        $icon = 'fa-video';
                    } elseif (in_array($ext, $TYPE_EXT['image'])) {
                        $type = 'image';
                        $icon = 'fa-image';
                    } elseif (in_array($ext, $TYPE_EXT['text'])) {
                        $type = 'text';
                        $icon = 'fa-file-alt';
                    } elseif (in_array($ext, $TYPE_EXT['archive'])) {
                        $type = 'archive';
                        $icon = 'fa-file-archive';
                    } else {
                        $icon = 'fa-file';
                    }
                    
                    $items[] = [
                        'name' => $item->getFilename(),
                        'path' => $itemPath,
                        'type' => $type,
                        'is_dir' => $isDir,
                        'size' => $size,
                        'size_formatted' => formatFileSize($size),
                        'modified' => $item->getMTime(),
                        'modified_formatted' => date('Y-m-d H:i:s', $item->getMTime()),
                        'permissions' => $perms,
                        'owner' => $owner,
                        'group' => $group,
                        'icon' => $icon,
                        'ext' => $ext,
                        'safe_name' => htmlspecialchars($item->getFilename(), ENT_QUOTES, 'UTF-8'),
                        'safe_path' => htmlspecialchars($itemPath, ENT_QUOTES, 'UTF-8')
                    ];
                }
                
                usort($items, function($a, $b) {
                    if ($a['is_dir'] && !$b['is_dir']) return -1;
                    if (!$a['is_dir'] && $b['is_dir']) return 1;
                    preg_match('/(\d+)/', $a['name'], $matchesA);
                    preg_match('/(\d+)/', $b['name'], $matchesB);
    
                    if (isset($matchesA[1]) && isset($matchesB[1])) {
                        $numA = intval($matchesA[1]);
                        $numB = intval($matchesB[1]);
        
                        if ($numA !== $numB) {
                            return $numA - $numB;
                        }
                    }
                    return strcasecmp($a['name'], $b['name']);
                });
                
                echo json_encode([
                    'success' => true,
                    'path' => $realPath,
                    'parent' => dirname($realPath),
                    'items' => $items,
                    'disk_info' => getDiskInfo($realPath)
                ]);
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Directory not readable']);
        }
        exit;
    }

    if ($action === 'install_package') {
        header('Content-Type: application/json');
    
        $path = isset($_POST['path']) ? urldecode($_POST['path']) : '';
        $force = isset($_POST['force']) && $_POST['force'] == '1';
        $update = isset($_POST['update']) && $_POST['update'] == '1';
    
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
    
        $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['ipk', 'apk', 'run'])) {
            echo json_encode(['success' => false, 'error' => 'Not a valid package file']);
            exit;
        }
    
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
    
        ob_implicit_flush(true);
        ob_end_flush();
    
        $output = [];
        $returnVar = 0;
    
        echo "event: start\n";
        echo "data: " . json_encode(['message' => 'Starting installation...']) . "\n\n";
 
        if ($ext === 'ipk') {
            if ($update) {
                echo "event: output\n";
                echo "data: " . json_encode(['message' => 'Updating package lists...']) . "\n\n";
            
                $updateCmd = "opkg update 2>&1";
                exec($updateCmd, $updateOutput, $updateReturn);
                foreach ($updateOutput as $line) {
                    echo "event: output\n";
                    echo "data: " . json_encode(['message' => $line]) . "\n\n";
                }
            }
        
            $installCmd = "opkg install " . ($force ? "--force-depends --force-overwrite " : "") . escapeshellarg($realPath) . " 2>&1";
        
        } elseif ($ext === 'apk') {
            $installCmd = "adb install " . ($force ? "-r " : "") . escapeshellarg($realPath) . " 2>&1";
        
        } elseif ($ext === 'run') {
            echo "event: output\n";
            echo "data: " . json_encode(['message' => 'Setting execute permission...']) . "\n\n";
        
            $chmodCmd = "chmod +x " . escapeshellarg($realPath) . " 2>&1";
            exec($chmodCmd, $chmodOutput, $chmodReturn);
        
            if ($chmodReturn !== 0) {
                echo "event: error\n";
                echo "data: " . json_encode(['message' => 'Failed to set execute permission']) . "\n\n";
                exit;
            }
        
            echo "event: output\n";
            echo "data: " . json_encode(['message' => 'Execute permission set successfully']) . "\n\n";
        
            $installCmd = escapeshellarg($realPath) . " 2>&1";
        }

        if (isset($installCmd)) {
            $descriptors = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ];
        
            $process = proc_open($installCmd, $descriptors, $pipes);
        
            if (is_resource($process)) {
                while ($line = fgets($pipes[1])) {
                    echo "event: output\n";
                    echo "data: " . json_encode(['message' => trim($line)]) . "\n\n";
                }
            
                while ($line = fgets($pipes[2])) {
                    echo "event: output\n";
                    echo "data: " . json_encode(['message' => trim($line)]) . "\n\n";
                }
            
                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);
            
                $returnVar = proc_close($process);
            
                if ($returnVar === 0) {
                    echo "event: complete\n";
                    echo "data: " . json_encode(['success' => true, 'message' => 'Installation completed successfully']) . "\n\n";
                } else {
                    echo "event: complete\n";
                    echo "data: " . json_encode(['success' => false, 'message' => 'Installation failed']) . "\n\n";
                }
            } else {
                echo "event: error\n";
                echo "data: " . json_encode(['message' => 'Failed to start installation process']) . "\n\n";
            }
        }
    
        exit;
    }

    elseif ($action === 'download_folder') {
        header('Content-Type: application/octet-stream');
        if (ob_get_level()) ob_end_clean();
    
        $path = isset($_GET['path']) ? urldecode($_GET['path']) : '';
        $realPath = realpath($path);
    
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            http_response_code(403);
            exit('Invalid path');
        }
        if (!is_dir($realPath)) {
            http_response_code(400);
            exit('Not a directory');
        }
    
        $folderName = basename($realPath);
        $tarFilename = $folderName . '.tar';
        header('Content-Disposition: attachment; filename="' . rawurlencode($tarFilename) . '"');
    
        $cmd = "tar -cf - -C " . escapeshellarg(dirname($realPath)) . " " . escapeshellarg($folderName);
    
        passthru($cmd);
        exit;
    }
   
    if ($action === 'get_file_info') {
        header('Content-Type: application/json');
        
        $path = isset($_GET['path']) ? urldecode($_GET['path']) : '';
        $path = preg_replace('#/+#', '/', $path);
        
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        foreach ($EXCLUDE_DIRS as $exclude) {
            if (strpos($realPath, $exclude) === 0) {
                echo json_encode(['success' => false, 'error' => 'Path excluded']);
                exit;
            }
        }
        
        if (file_exists($realPath) && is_readable($realPath)) {
            $isDir = is_dir($realPath);
            $size = $isDir ? 0 : filesize($realPath);
            $perms = substr(sprintf('%o', fileperms($realPath)), -4);
            $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($realPath))['name'] : fileowner($realPath);
            $group = function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($realPath))['name'] : filegroup($realPath);
            
            $info = [
                'name' => basename($realPath),
                'path' => $realPath,
                'is_dir' => $isDir,
                'size' => $size,
                'size_formatted' => formatFileSize($size),
                'modified' => filemtime($realPath),
                'modified_formatted' => date('Y-m-d H:i:s', filemtime($realPath)),
                'accessed' => fileatime($realPath),
                'accessed_formatted' => date('Y-m-d H:i:s', fileatime($realPath)),
                'created' => filectime($realPath),
                'created_formatted' => date('Y-m-d H:i:s', filectime($realPath)),
                'permissions' => $perms,
                'owner' => $owner,
                'group' => $group,
                'inode' => fileinode($realPath),
                'real_path' => $realPath
            ];
            
            if (!$isDir) {
                $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
                $info['extension'] = $ext;
                $info['mime_type'] = getMimeType($ext);
                
                if (in_array($ext, $TYPE_EXT['music']) || in_array($ext, $TYPE_EXT['video']) || in_array($ext, $TYPE_EXT['image'])) {
                    $mediaInfo = getDetailedMediaInfo($realPath, $ext);
                    if ($mediaInfo) {
                        $info['media_info'] = $mediaInfo;
                    }
                }
            }
            
            echo json_encode(['success' => true, 'info' => $info]);
        } else {
            echo json_encode(['success' => false, 'error' => 'File not found']);
        }
        exit;
    }
    
    if ($action === 'create_folder') {
        header('Content-Type: application/json');
        
        $path = isset($_GET['path']) ? urldecode($_GET['path']) : '';
        $name = isset($_GET['name']) ? urldecode($_GET['name']) : '';
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Folder name is required']);
            exit;
        }

        $name = preg_replace('/[\/:*?"<>|]/', '', $name);
        
        $path = rtrim($path, '/') . '/';
        $fullPath = $path . $name;
        
        $fullPath = realpath(dirname($fullPath)) . '/' . $name;
        
        if (strpos($fullPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        if (file_exists($fullPath)) {
            echo json_encode(['success' => false, 'error' => 'Folder already exists']);
            exit;
        }
        
        if (@mkdir($fullPath, 0755, true)) {
            echo json_encode(['success' => true, 'message' => 'Folder created successfully']);
        } else {
            $error = error_get_last();
            echo json_encode(['success' => false, 'error' => 'Failed to create folder: ' . ($error['message'] ?? 'Unknown error')]);
        }
        exit;
    }

    if ($action === 'create_file') {
        header('Content-Type: application/json');
        
        $path = isset($_POST['path']) ? urldecode($_POST['path']) : '';
        $name = isset($_POST['name']) ? urldecode($_POST['name']) : '';
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'File name is required']);
            exit;
        }
        
        $name = preg_replace('/[\/:*?"<>|]/', '', $name);
        
        $fullPath = $path . '/' . $name;
        $fullPath = preg_replace('#/+#', '/', $fullPath);
        
        if (substr($fullPath, 0, 1) !== '/') {
            $fullPath = '/' . $fullPath;
        }
        
        $realPath = realpath(dirname($fullPath));
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        if (file_exists($fullPath)) {
            echo json_encode(['success' => false, 'error' => 'File already exists']);
            exit;
        }
        
        if (@file_put_contents($fullPath, '') !== false) {
            echo json_encode(['success' => true, 'message' => 'File created successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create file']);
        }
        exit;
    }
    
    if ($action === 'delete_item') {
        header('Content-Type: application/json');
        
        $path = isset($_GET['path']) ? urldecode($_GET['path']) : '';
        $path = preg_replace('#/+#', '/', $path);
        
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        $success = false;
        $message = '';
        
        if (is_dir($realPath)) {
            if (deleteDirectory($realPath)) {
                $success = true;
                $message = 'Directory deleted successfully';
            } else {
                $message = 'Failed to delete directory';
            }
        } else {
            if (@unlink($realPath)) {
                $success = true;
                $message = 'File deleted successfully';
            } else {
                $message = 'Failed to delete file';
            }
        }
        
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
    
    if ($action === 'save_file') {
        header('Content-Type: application/json; charset=utf-8');
        
        $path = isset($_POST['path']) ? urldecode($_POST['path']) : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $isBase64 = isset($_POST['is_base64']) && $_POST['is_base64'] == '1';
        
        $path = preg_replace('#/+#', '/', $path);
        
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        foreach ($EXCLUDE_DIRS as $exclude) {
            if (strpos($realPath, $exclude) === 0) {
                echo json_encode(['success' => false, 'error' => 'Path excluded']);
                exit;
            }
        }
        
        if (file_exists($realPath) && is_writable($realPath)) {
            try {
                if ($isBase64) {
                    $content = base64_decode($content);
                    if ($content === false) {
                        echo json_encode(['success' => false, 'error' => 'Failed to decode base64 content']);
                        exit;
                    }
                    if (!mb_check_encoding($content, 'UTF-8')) {
                        $content = mb_convert_encoding($content, 'UTF-8', 'auto');
                    }
                } else {
                    if (!mb_check_encoding($content, 'UTF-8')) {
                        $content = mb_convert_encoding($content, 'UTF-8', 'auto');
                    }
                }
                
                $result = file_put_contents($realPath, $content);
                
                if ($result !== false) {
                    @chmod($realPath, 0644);
                    echo json_encode(['success' => true, 'message' => 'File saved successfully']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'File not writable']);
        }
        exit;
    }
    
    if ($action === 'read_file') {
        header('Content-Type: application/json; charset=utf-8');
        
        $path = isset($_GET['path']) ? urldecode($_GET['path']) : '';
        $path = preg_replace('#/+#', '/', $path);
        
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        foreach ($EXCLUDE_DIRS as $exclude) {
            if (strpos($realPath, $exclude) === 0) {
                echo json_encode(['success' => false, 'error' => 'Path excluded']);
                exit;
            }
        }
        
        if (file_exists($realPath) && is_readable($realPath) && is_file($realPath)) {
            $content = @file_get_contents($realPath);
            if ($content !== false) {
                $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
                $textExts = ['txt', 'log', 'conf', 'ini', 'json', 'xml', 'html', 'htm',
                           'css', 'js', 'php', 'py', 'sh', 'md', 'yaml', 'yml',
                           'csv', 'sql', 'bat', 'cmd', 'jsx', 'ts', 'tsx', 'vue',
                           'c', 'cpp', 'h', 'hpp', 'java', 'go', 'rs', 'rb', 'perl',
                           'lua', 'swift', 'kt', 'scala', 'groovy', 'dart'];
                
                $encoding = mb_detect_encoding($content, ['UTF-8', 'GBK', 'GB2312', 'BIG5', 'ASCII', 'ISO-8859-1'], true);
                
                if (!$encoding) {
                    $encoding = 'UTF-8';
                }
                
                if ($encoding !== 'UTF-8') {
                    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                }
                
                if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
                    $content = substr($content, 3);
                }
                
                if (in_array($ext, $textExts) || filesize($realPath) < 1024 * 1024 * 5) {

                    function safeBase64Encode($string) {
                        $string = mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));
                        if (substr($string, 0, 3) === "\xEF\xBB\xBF") {
                            $string = substr($string, 3);
                        }
                        return base64_encode($string);
                    }
                    
                    $base64Content = safeBase64Encode($content);
                    
                    echo json_encode([
                        'success' => true,
                        'content' => $content,  
                        'is_base64' => false,
                        'size' => filesize($realPath),
                        'mime' => getMimeType($ext),
                        'encoding' => 'UTF-8',
                        'extension' => $ext
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    
                } else {
                    echo json_encode([
                        'success' => true,
                        'content' => '⚠️ The file is too large or contains binary data. Please edit it directly on the server.',
                        'is_base64' => false,
                        'size' => filesize($realPath),
                        'mime' => getMimeType($ext),
                        'encoding' => 'binary'
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to read file']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'File not readable']);
        }
        exit;
    }
    
    if ($action === 'upload_file') {
        header('Content-Type: application/json');
        
        $path = isset($_POST['path']) ? urldecode($_POST['path']) : '';
        $path = preg_replace('#/+#', '/', $path);
        
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0 || !is_dir($realPath)) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        $uploadedFiles = [];
        $errors = [];

        function generateUniqueFilename($directory, $filename) {
            $pathinfo = pathinfo($filename);
            $name = $pathinfo['filename'];
            $extension = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';
            $counter = 1;
            $newFilename = $filename;
        
            while (file_exists($directory . '/' . $newFilename)) {
                $newFilename = $name . '_' . $counter . $extension;
                $counter++;
            }
        
            return $newFilename;
        }
        
        if (isset($_FILES['file'])) {
            if (!is_array($_FILES['file']['name'])) {
                $file = $_FILES['file'];
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $fileName = preg_replace('/[\/:*?"<>|]/', '_', basename($file['name']));

                    if (file_exists($realPath . '/' . $fileName)) {
                        $fileName = generateUniqueFilename($realPath, $fileName);
                    }

                    $targetFile = $realPath . '/' . $fileName;
                    
                    if (!file_exists($targetFile) && move_uploaded_file($file['tmp_name'], $targetFile)) {
                        $uploadedFiles[] = $fileName;
                    } else {
                        $errors[] = $fileName;
                    }
                }
            } else {
                $fileCount = count($_FILES['file']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
                        $fileName = preg_replace('/[\/:*?"<>|]/', '_', basename($_FILES['file']['name'][$i]));

                        if (file_exists($realPath . '/' . $fileName)) {
                            $fileName = generateUniqueFilename($realPath, $fileName);
                        }
                    
                        $targetFile = $realPath . '/' . $fileName;
                        
                        if (!file_exists($targetFile) && move_uploaded_file($_FILES['file']['tmp_name'][$i], $targetFile)) {
                            $uploadedFiles[] = $fileName;
                        } else {
                            $errors[] = $fileName;
                        }
                    }
                }
            }
        }
        
        if (!empty($uploadedFiles)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Files uploaded successfully',
                'files' => $uploadedFiles,
                'error_files' => $errors
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No files were uploaded']);
        }
        exit;
    }

    if ($action === 'upload_folder') {
        header('Content-Type: application/json');
    
        $path = isset($_POST['path']) ? urldecode($_POST['path']) : '';
        $realPath = realpath($path);
    
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0 || !is_dir($realPath)) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
    
        $uploaded = 0;
        $errors = [];
    
        if (isset($_FILES['files']) && isset($_POST['paths'])) {
            $files = $_FILES['files'];
            $paths = $_POST['paths'];
        
            for ($i = 0; $i < count($paths); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $relativePath = $paths[$i];
                    $targetPath = $realPath . '/' . $relativePath;
                
                    if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                        $uploaded++;
                    } else {
                        $errors[] = "Failed to upload: $relativePath";
                    }
                }
            }
        }
    
        echo json_encode([
            'success' => $uploaded > 0,
            'files_uploaded' => $uploaded,
            'errors' => $errors
        ]);
        exit;
    }

    if ($action === 'upload_chunk') {
        header('Content-Type: application/json');
        
        $path = isset($_POST['path']) ? urldecode($_POST['path']) : '';
        $filePath = isset($_POST['filePath']) ? $_POST['filePath'] : '';
        $chunkIndex = isset($_POST['chunkIndex']) ? intval($_POST['chunkIndex']) : 0;
        $totalChunks = isset($_POST['totalChunks']) ? intval($_POST['totalChunks']) : 0;
        $fileName = isset($_POST['fileName']) ? $_POST['fileName'] : '';
        $totalSize = isset($_POST['totalSize']) ? intval($_POST['totalSize']) : 0;
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0 || !is_dir($realPath)) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        $tempDir = $realPath . '/.uploads_temp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $tempFile = $tempDir . '/' . md5($filePath) . '_' . $chunkIndex . '.tmp';
        
        if (isset($_FILES['chunk']) && $_FILES['chunk']['error'] === UPLOAD_ERR_OK) {
            if (move_uploaded_file($_FILES['chunk']['tmp_name'], $tempFile)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to save chunk']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'No chunk uploaded']);
        }
        exit;
    }

    if ($action === 'upload_complete') {
        header('Content-Type: application/json');
        
        $path = isset($_POST['path']) ? urldecode($_POST['path']) : '';
        $filePath = isset($_POST['filePath']) ? $_POST['filePath'] : '';
        $totalChunks = isset($_POST['totalChunks']) ? intval($_POST['totalChunks']) : 0;
        $fileName = isset($_POST['fileName']) ? $_POST['fileName'] : '';
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0 || !is_dir($realPath)) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        $targetPath = $realPath . '/' . $filePath;
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        $tempDir = $realPath . '/.uploads_temp';
        $baseHash = md5($filePath);
        
        $outFile = fopen($targetPath, 'wb');
        if ($outFile) {
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkFile = $tempDir . '/' . $baseHash . '_' . $i . '.tmp';
                if (file_exists($chunkFile)) {
                    $inFile = fopen($chunkFile, 'rb');
                    stream_copy_to_stream($inFile, $outFile);
                    fclose($inFile);
                    unlink($chunkFile);
                }
            }
            fclose($outFile);
            
            if (count(glob($tempDir . '/*')) === 0) {
                rmdir($tempDir);
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create output file']);
        }
        exit;
    }

    if ($action === 'convert_media') {
        header('Content-Type: application/json');
    
        $input = json_decode(file_get_contents('php://input'), true);
        $inputFile = $input['input'];
        $outputFile = $input['output'];
        $format = $input['format'];
        $quality = $input['quality'];
    
        if (strpos(realpath($inputFile), $ROOT_DIR) !== 0 || 
            strpos(realpath(dirname($outputFile)), $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
    
        $ffmpegPath = '/usr/bin/ffmpeg';
    
        $quality_param = '';
        switch($quality) {
            case 'high':
                $quality_param = $format === 'mp3' ? '-b:a 320k' : '-crf 18';
                break;
            case 'medium':
                $quality_param = $format === 'mp3' ? '-b:a 192k' : '-crf 23';
                break;
            case 'low':
                $quality_param = $format === 'mp3' ? '-b:a 128k' : '-crf 28';
                break;
        }
    
        $cmd = $ffmpegPath . " -i " . escapeshellarg($inputFile) . " -y";
    
        switch($format) {
            case 'mp3':
                $cmd .= " -acodec libmp3lame $quality_param";
                break;
            case 'wav':
                $cmd .= " -acodec pcm_s16le";
                break;
            case 'ogg':
                $cmd .= " -acodec libvorbis -q:a " . ($quality === 'high' ? '8' : ($quality === 'medium' ? '5' : '3'));
                break;
            case 'flac':
                $cmd .= " -acodec flac";
                break;
            case 'aac':
            case 'm4a':
                $cmd .= " -acodec aac $quality_param";
                break;
            case 'mp4':
                $cmd .= " -c:v libx264 -preset medium $quality_param -c:a aac -b:a 128k";
                break;
            case 'avi':
                $cmd .= " -c:v mpeg4 -q:v " . ($quality === 'high' ? '2' : ($quality === 'medium' ? '5' : '8')) . " -c:a mp3 -b:a 128k";
                break;
            case 'mkv':
                $cmd .= " -c:v libx264 -preset medium $quality_param -c:a aac -b:a 128k";
                break;
            case 'mov':
                $cmd .= " -c:v libx264 -preset medium $quality_param -c:a aac -b:a 128k";
                break;
            case 'webm':
                $cmd .= " -c:v libvpx-vp9 -crf " . ($quality === 'high' ? '18' : ($quality === 'medium' ? '23' : '28')) . " -b:v 0 -c:a libopus -b:a 128k";
                break;
            case 'gif':
                $cmd .= " -vf fps=10,scale=480:-1:flags=lanczos -c:v gif";
                break;
            case 'mp2':
                $cmd .= " -acodec mp2 -b:a " . ($quality === 'high' ? '384k' : ($quality === 'medium' ? '256k' : '192k'));
                break;
            case 'ac3':
                $cmd .= " -acodec ac3 -b:a " . ($quality === 'high' ? '640k' : ($quality === 'medium' ? '448k' : '384k'));
                break;
            case 'dts':
                $cmd .= " -acodec dts -strict -2 -b:a " . ($quality === 'high' ? '1536k' : ($quality === 'medium' ? '768k' : '512k'));
                break;
            case 'wmv':
                $cmd .= " -c:v wmv2 -b:v " . ($quality === 'high' ? '2000k' : ($quality === 'medium' ? '1000k' : '500k')) . " -c:a wmav2 -b:a 128k";
                break;
            case 'flv':
                $cmd .= " -c:v flv -b:v " . ($quality === 'high' ? '1500k' : ($quality === 'medium' ? '800k' : '400k')) . " -c:a mp3 -b:a 128k -f flv";
                break;
            case '3gp':
                $cmd .= " -c:v h263 -vf 'scale=176:144' -b:v " . ($quality === 'high' ? '500k' : ($quality === 'medium' ? '300k' : '150k')) . " -c:a aac -b:a 64k -ac 1 -ar 8000 -f 3gp";
                break;
            case 'hevc':
            case 'h265':
                $cmd .= " -c:v libx264 -preset medium -crf " . ($quality === 'high' ? '18' : ($quality === 'medium' ? '23' : '28')) . " -c:a aac -b:a 128k";
                break;
            case 'jpg':
            case 'jpeg':
                $cmd .= " -c:v mjpeg -q:v " . ($quality === 'high' ? '2' : ($quality === 'medium' ? '5' : '10'));
                break;
            case 'png':
                $cmd .= " -c:v png -compression_level " . ($quality === 'high' ? '1' : ($quality === 'medium' ? '6' : '9'));
                break;
            case 'webp':
                $cmd .= " -c:v libwebp -quality " . ($quality === 'high' ? '100' : ($quality === 'medium' ? '85' : '60'));
                break;
            case 'bmp':
                $cmd .= " -c:v bmp";
                break;
            case 'tiff':
                $cmd .= " -c:v tiff -compression_algo " . ($quality === 'high' ? 'lzw' : 'raw');
                break;
            case 'ico':
                $cmd .= " -c:v bmp -vf scale=256:256";
                break;
        }
    
        $cmd .= " " . escapeshellarg($outputFile) . " 2>&1";
    
        exec($cmd, $output, $returnCode);
    
        if ($returnCode === 0 && file_exists($outputFile)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => implode("\n", $output)]);
        }
        exit;
    }

    if ($action === 'rename_item') {
        header('Content-Type: application/json');
        
        $oldPath = isset($_GET['old']) ? urldecode($_GET['old']) : '';
        $newName = isset($_GET['new']) ? urldecode($_GET['new']) : '';
        
        if (empty($oldPath) || empty($newName)) {
            echo json_encode(['success' => false, 'error' => 'Old path and new name are required']);
            exit;
        }
        
        $newName = preg_replace('/[\/:*?"<>|]/', '', $newName);
        
        $oldPath = preg_replace('#/+#', '/', $oldPath);
        if (substr($oldPath, 0, 1) !== '/') {
            $oldPath = '/' . $oldPath;
        }
        
        $oldRealPath = realpath($oldPath);
        if (!$oldRealPath || strpos($oldRealPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid old path']);
            exit;
        }
        
        foreach ($EXCLUDE_DIRS as $exclude) {
            if (strpos($oldRealPath, $exclude) === 0) {
                echo json_encode(['success' => false, 'error' => 'Old path is excluded']);
                exit;
            }
        }
        
        $newPath = dirname($oldRealPath) . '/' . $newName;
        
        if (file_exists($newPath)) {
            echo json_encode(['success' => false, 'error' => 'New name already exists']);
            exit;
        }
        
        if (@rename($oldRealPath, $newPath)) {
            echo json_encode(['success' => true, 'message' => 'Renamed successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to rename']);
        }
        exit;
    }

    if ($action === 'move_item') {
        header('Content-Type: application/json');
        
        $sourcePath = isset($_GET['source']) ? urldecode($_GET['source']) : '';
        $destDir = isset($_GET['dest']) ? urldecode($_GET['dest']) : '';
        
        if (empty($sourcePath) || empty($destDir)) {
            echo json_encode(['success' => false, 'error' => 'Source and destination are required']);
            exit;
        }
        
        $sourcePath = preg_replace('#/+#', '/', $sourcePath);
        if (substr($sourcePath, 0, 1) !== '/') {
            $sourcePath = '/' . $sourcePath;
        }
        
        $destDir = preg_replace('#/+#', '/', $destDir);
        if (substr($destDir, 0, 1) !== '/') {
            $destDir = '/' . $destDir;
        }
        
        $sourceRealPath = realpath($sourcePath);
        $destRealPath = realpath($destDir);
        
        if (!$sourceRealPath || strpos($sourceRealPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid source path']);
            exit;
        }
        
        if (!$destRealPath || strpos($destRealPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid destination path']);
            exit;
        }
        
        foreach ($EXCLUDE_DIRS as $exclude) {
            if (strpos($sourceRealPath, $exclude) === 0) {
                echo json_encode(['success' => false, 'error' => 'Source path is excluded']);
                exit;
            }
            if (strpos($destRealPath, $exclude) === 0) {
                echo json_encode(['success' => false, 'error' => 'Destination path is excluded']);
                exit;
            }
        }
        
        if (!is_dir($destRealPath)) {
            echo json_encode(['success' => false, 'error' => 'Destination is not a directory']);
            exit;
        }
        
        $destPath = $destRealPath . '/' . basename($sourceRealPath);
        
        if (file_exists($destPath)) {
            echo json_encode(['success' => false, 'error' => 'A file with the same name already exists in the destination']);
            exit;
        }
        
        if (@rename($sourceRealPath, $destPath)) {
            echo json_encode(['success' => true, 'message' => 'Moved successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to move']);
        }
        exit;
    }

    if ($action === 'search') {
        header('Content-Type: application/json');
        
        $term = isset($_GET['term']) ? urldecode($_GET['term']) : '';
        
        if (empty($term)) {
            echo json_encode(['success' => false, 'error' => 'Search term is required']);
            exit;
        }
        
        try {
            $searchResults = searchFilesByName($ROOT_DIR, $term);
            echo json_encode(['success' => true, 'results' => $searchResults]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'change_permissions') {
        header('Content-Type: application/json');
        
        $path = isset($_POST['path']) ? urldecode($_POST['path']) : '';
        $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : '';
        
        if (empty($path) || empty($permissions)) {
            echo json_encode(['success' => false, 'error' => 'Path and permission value are required']);
            exit;
        }
        
        if (!preg_match('/^[0-7]{3,4}$/', $permissions)) {
            echo json_encode(['success' => false, 'error' => 'Invalid permission format']);
            exit;
        }
        
        $path = preg_replace('#/+#', '/', $path);
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        global $EXCLUDE_DIRS;
        foreach ($EXCLUDE_DIRS as $exclude) {
            if (strpos($realPath, $exclude) === 0) {
                echo json_encode(['success' => false, 'error' => 'Path is excluded']);
                exit;
            }
        }
        
        $mode = octdec($permissions);
        
        if (@chmod($realPath, $mode)) {
            echo json_encode(['success' => true, 'message' => 'Permissions updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update permissions, please check file permissions']);
        }
        exit;
    }

    if ($action === 'copy_item') {
        header('Content-Type: application/json');
        
        $sourcePath = isset($_GET['source']) ? urldecode($_GET['source']) : '';
        $destDir = isset($_GET['dest']) ? urldecode($_GET['dest']) : '';
        
        if (empty($sourcePath) || empty($destDir)) {
            echo json_encode(['success' => false, 'error' => 'Source and destination are required']);
            exit;
        }
        
        $sourcePath = preg_replace('#/+#', '/', $sourcePath);
        if (substr($sourcePath, 0, 1) !== '/') {
            $sourcePath = '/' . $sourcePath;
        }
        
        $destDir = preg_replace('#/+#', '/', $destDir);
        if (substr($destDir, 0, 1) !== '/') {
            $destDir = '/' . $destDir;
        }
        
        $sourceRealPath = realpath($sourcePath);
        $destRealPath = realpath($destDir);
        
        if (!$sourceRealPath || strpos($sourceRealPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid source path']);
            exit;
        }
        
        if (!$destRealPath || strpos($destRealPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid destination path']);
            exit;
        }
        
        foreach ($EXCLUDE_DIRS as $exclude) {
            if (strpos($sourceRealPath, $exclude) === 0) {
                echo json_encode(['success' => false, 'error' => 'Source path is excluded']);
                exit;
            }
            if (strpos($destRealPath, $exclude) === 0) {
                echo json_encode(['success' => false, 'error' => 'Destination path is excluded']);
                exit;
            }
        }
        
        if (!is_dir($destRealPath)) {
            echo json_encode(['success' => false, 'error' => 'Destination is not a directory']);
            exit;
        }
        
        $destPath = $destRealPath . '/' . basename($sourceRealPath);
        
        if (file_exists($destPath)) {
            echo json_encode(['success' => false, 'error' => 'A file with the same name already exists in the destination']);
            exit;
        }
        
        if (is_dir($sourceRealPath)) {
            if (copyDirectory($sourceRealPath, $destPath)) {
                echo json_encode(['success' => true, 'message' => 'Directory copied successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to copy directory']);
            }
        } else {
            if (@copy($sourceRealPath, $destPath)) {
                echo json_encode(['success' => true, 'message' => 'File copied successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to copy file']);
            }
        }
        exit;
    }

    if ($action === 'file_hash') {
        header('Content-Type: application/json');
    
        $path = isset($_GET['path']) ? urldecode($_GET['path']) : '';
    
        $path = preg_replace('#/+#', '/', $path);
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
    
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
    
        foreach ($EXCLUDE_DIRS as $exclude) {
            if (strpos($realPath, $exclude) === 0) {
                echo json_encode(['success' => false, 'error' => 'Path excluded']);
                exit;
            }
        }
    
        if (!is_file($realPath) || !is_readable($realPath)) {
            echo json_encode(['success' => false, 'error' => 'File not readable']);
            exit;
        }
    
        try {
            if (!file_exists($realPath)) {
                throw new Exception('File does not exist');
            }
        
            $md5 = md5_file($realPath);
            $sha1 = sha1_file($realPath);
            $sha256 = hash_file('sha256', $realPath);
        
            if ($md5 === false || $sha1 === false || $sha256 === false) {
                throw new Exception('Failed to calculate hash');
            }
        
            $result = [
                'success' => true,
                'filename' => basename($realPath),
                'path' => $realPath,
                'size' => filesize($realPath),
                'size_formatted' => formatFileSize(filesize($realPath)),
                'md5' => $md5,
                'sha1' => $sha1,
                'sha256' => $sha256,
                'modified' => date('Y-m-d H:i:s', filemtime($realPath))
            ];
        
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false, 
                'error' => 'Hash calculation failed: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    if ($action === 'transcode') {
        $path = isset($_GET['path']) ? urldecode($_GET['path']) : '';
        $format = isset($_GET['format']) ? $_GET['format'] : 'mp4';
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            http_response_code(403);
            exit;
        }
        
        $ffmpeg = '/usr/bin/ffmpeg';
        if (!file_exists($ffmpeg)) {
            http_response_code(500);
            exit('FFmpeg not found');
        }
        
        header('Content-Type: video/mp4');
        header('Content-Disposition: inline');
        
        $cmd = "$ffmpeg -i " . escapeshellarg($realPath) . 
               " -c:v libx264 -preset ultrafast -tune zerolatency" .
               " -c:a aac -f mp4 -movflags frag_keyframe+empty_moov pipe:1 2>/dev/null";
        
        passthru($cmd);
        exit;
    }

    if ($action === 'full_scan') {
        header('Content-Type: application/json');
        
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $validTypes = ['music', 'video', 'image'];
        
        if (!in_array($type, $validTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid scan type']);
            exit;
        }
        
        if (!is_dir($MEDIA_CACHE_DIR)) {
            mkdir($MEDIA_CACHE_DIR, 0755, true);
        }
        
        $media = [];
        $files = scanDirectory($ROOT_DIR, 20);
        
        foreach ($files as $file) {
            $ext = $file['ext'];
            
            if ($type === 'video' && in_array($ext, ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', '3gp', 'ogv', 'mpg', 'mpeg'])) {
                $handle = fopen($file['path'], 'rb');
                $firstChunk = fread($handle, 65536);
                fclose($handle);
                $contentHash = md5($firstChunk . $file['size']);
                $cacheInfo = './video_thumbs/' . $contentHash . '.json';
                
                if (file_exists($cacheInfo)) {
                    $info = json_decode(file_get_contents($cacheInfo), true);
                    $durationSeconds = $info['duration'] ?? 0;
                    $hours = floor($durationSeconds / 3600);
                    $minutes = floor(($durationSeconds % 3600) / 60);
                    $seconds = $durationSeconds % 60;
                    
                    if ($hours > 0) {
                        $file['duration'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                    } else {
                        $file['duration'] = sprintf("%02d:%02d", $minutes, $seconds);
                    }
                } else {
                    $ffmpegPath = '/usr/bin/ffmpeg';
                    $duration = '--:--';
                    $cmd = $ffmpegPath . " -i " . escapeshellarg($file['path']) . " 2>&1";
                    $output = shell_exec($cmd);
                    if ($output && preg_match('/Duration: (\d{2}):(\d{2}):(\d{2})\.\d+/', $output, $matches)) {
                        $duration = $matches[1] . ':' . $matches[2] . ':' . $matches[3];
                    }
                    $file['duration'] = $duration;
                }
                
                $media[] = $file;
            } elseif ($type === 'music' && in_array($ext, ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac'])) {
                $ffmpegPath = '/usr/bin/ffmpeg';
                $duration = '--:--';
                $cmd = $ffmpegPath . " -i " . escapeshellarg($file['path']) . " 2>&1";
                $output = shell_exec($cmd);
                if ($output && preg_match('/Duration: (\d{2}):(\d{2}):(\d{2})\.\d+/', $output, $matches)) {
                    $duration = $matches[1] . ':' . $matches[2] . ':' . $matches[3];
                }
                $file['duration'] = $duration;
                $media[] = $file;
            } elseif ($type === 'image' && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'])) {
                $media[] = $file;
            }
        }

        usort($media, function($a, $b) {
            return $b['mtime'] - $a['mtime'];
        });

        $cacheFile = $type === 'music' ? $MUSIC_CACHE_FILE :
                    ($type === 'video' ? $VIDEO_CACHE_FILE : $IMAGE_CACHE_FILE);
        
        $result = file_put_contents($cacheFile, json_encode($media));
        
        echo json_encode([
            'success' => $result !== false,
            'type' => $type,
            'count' => count($media),
            'message' => $result !== false ? 'Scan completed' : 'Failed to save cache'
        ]);
        exit;
    }

    if ($action === 'clear_cache_type') {
        header('Content-Type: application/json');
        
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $validTypes = ['music', 'video', 'image'];
        
        if (!in_array($type, $validTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid cache type']);
            exit;
        }
        
        $cacheFile = $type === 'music' ? $MUSIC_CACHE_FILE :
                    ($type === 'video' ? $VIDEO_CACHE_FILE : $IMAGE_CACHE_FILE);
        
        $success = false;
        if (file_exists($cacheFile)) {
            $success = unlink($cacheFile);
        } else {
            $success = true;
        }
        
        echo json_encode([
            'success' => $success,
            'type' => $type,
            'message' => $success ? ucfirst($type) . ' cache cleared' : 'Failed to clear cache'
        ]);
        exit;
    }

    if ($action === 'get_media') {
        header('Content-Type: application/json');
        
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $validTypes = ['music', 'video', 'image'];
        
        if (!in_array($type, $validTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid media type']);
            exit;
        }
        
        $cacheFile = $type === 'music' ? $MUSIC_CACHE_FILE :
                    ($type === 'video' ? $VIDEO_CACHE_FILE : $IMAGE_CACHE_FILE);
        
        $media = [];
        if (file_exists($cacheFile)) {
            $media = json_decode(file_get_contents($cacheFile), true) ?: [];
        }
        
        echo json_encode([
            'success' => true,
            'type' => $type,
            'media' => $media
        ]);
        exit;
    }

    if ($action === 'clear_cache') {
        header('Content-Type: application/json');
        
        $success = true;
        $messages = [];
        
        $cacheFiles = [
            'music' => $MUSIC_CACHE_FILE,
            'video' => $VIDEO_CACHE_FILE,
            'image' => $IMAGE_CACHE_FILE
        ];
        
        foreach ($cacheFiles as $type => $file) {
            if (file_exists($file)) {
                if (unlink($file)) {
                    $messages[] = ucfirst($type) . ' cache cleared';
                } else {
                    $success = false;
                    $messages[] = 'Failed to clear ' . $type . ' cache';
                }
            } else {
                $messages[] = ucfirst($type) . ' cache not found';
            }
        }
        
        echo json_encode([
            'success' => $success,
            'message' => implode(', ', $messages)
        ]);
        exit;
    }

    if ($action === 'get_playlist') {
       header('Content-Type: application/json');
    
        $dir = isset($_GET['dir']) ? urldecode($_GET['dir']) : '';
        $dir = preg_replace('#/+#', '/', $dir);
    
        if (substr($dir, 0, 1) !== '/') {
            $dir = '/' . $dir;
        }
    
        $realDir = realpath($dir);
        if (!$realDir || strpos($realDir, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid directory']);
            exit;
        }
    
        $cacheFile = './lib/playlist_cache.json';
        $playlists = [];
    
        if (file_exists($cacheFile)) {
            $content = file_get_contents($cacheFile);
            $playlists = json_decode($content, true) ?: [];
        }
    
        echo json_encode([
            'success' => true,
            'dir' => $realDir,
            'playlist' => isset($playlists[$realDir]) ? $playlists[$realDir] : []
        ]);
        exit;
    }

    if ($action === 'save_playlist') {
        header('Content-Type: application/json');
    
        $input = json_decode(file_get_contents('php://input'), true);
        $dir = isset($input['dir']) ? $input['dir'] : '';
        $playlist = isset($input['playlist']) ? $input['playlist'] : [];
    
        if (empty($dir) || empty($playlist)) {
            echo json_encode(['success' => false, 'error' => 'Directory and playlist are required']);
            exit;
        }
    
        $dir = preg_replace('#/+#', '/', $dir);
        if (substr($dir, 0, 1) !== '/') {
            $dir = '/' . $dir;
        }
    
        $realDir = realpath($dir);
        if (!$realDir || strpos($realDir, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid directory']);
            exit;
        }
    
        if (!is_dir('./lib')) {
            mkdir('./lib', 0755, true);
        }
    
        $cacheFile = './lib/playlist_cache.json';
    
        $playlists = [
            $realDir => $playlist
        ];
    
        if (file_put_contents(
            $cacheFile,
            json_encode($playlists, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        )) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save playlist']);
        }
    
        exit;
    }

    if ($action === 'video_thumbnail') {
        $path = isset($_GET['path']) ? urldecode($_GET['path']) : '';
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            http_response_code(404);
            exit;
        }
        
        $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $videoExts = ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', '3gp', 'ogv', 'mpg', 'mpeg'];
        
        if (!in_array($ext, $videoExts)) {
            http_response_code(400);
            exit;
        }
        
        $ffmpegPath = '/usr/bin/ffmpeg';
        if (!file_exists($ffmpegPath)) {
            header('Content-Type: image/svg+xml');
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#2196F3"><path d="M18 9v10H6V9h12zM16 7H8v2h8V7zm2-2H6v2h12V5z"/></svg>';
            exit;
        }
        
        $cacheDir = './video_thumbs/';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $handle = fopen($realPath, 'rb');
        $firstChunk = fread($handle, 65536);
        fclose($handle);
        
        $fileSize = filesize($realPath);
        $contentHash = md5($firstChunk . $fileSize);
        
        $cacheFile = $cacheDir . $contentHash . '.jpg';
        $cacheInfo = $cacheDir . $contentHash . '.json';
        
        if (file_exists($cacheFile) && file_exists($cacheInfo)) {
            $info = json_decode(file_get_contents($cacheInfo), true);
            if ($info && $info['size'] == $fileSize) {
                header('Content-Type: image/jpeg');
                header('Content-Length: ' . filesize($cacheFile));
                header('Cache-Control: max-age=31536000');
                header('X-Cache-Hit: true');
                readfile($cacheFile);
                exit;
            }
        }

        $duration = '0';
        $durationCmd = $ffmpegPath . " -i " . escapeshellarg($realPath) . " 2>&1";
        $output = shell_exec($durationCmd);
        if ($output && preg_match('/Duration: (\d{2}):(\d{2}):(\d{2})\.\d+/', $output, $matches)) {
            $hours = intval($matches[1]);
            $minutes = intval($matches[2]);
            $seconds = intval($matches[3]);
            $durationSeconds = $hours * 3600 + $minutes * 60 + $seconds;
            $duration = round($durationSeconds);
        }
        
        $cmd = $ffmpegPath . " -ss 00:00:01 -i " . escapeshellarg($realPath) .
               " -vframes 1 -vf scale=320:-1 -q:v 2 -y " .
               escapeshellarg($cacheFile) . " 2>&1";
        
        exec($cmd, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($cacheFile)) {
            $cacheInfoData = [
                'path' => $realPath,
                'size' => $fileSize,
                'hash' => $contentHash,
                'duration' => $duration,
                'created' => time()
            ];
            file_put_contents($cacheInfo, json_encode($cacheInfoData));
            
            header('Content-Type: image/jpeg');
            header('Content-Length: ' . filesize($cacheFile));
            header('Cache-Control: max-age=31536000');
            header('X-Cache-Hit: false');
            readfile($cacheFile);
        } else {
            header('Content-Type: image/svg+xml');
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#2196F3"><path d="M18 9v10H6V9h12zM16 7H8v2h8V7zm2-2H6v2h12V5z"/></svg>';
        }
        exit;
    }

    if ($action === 'clean_thumbnail_cache') {
        $cacheDir = './video_thumbs/';
        
        if (is_dir($cacheDir)) {
            if (function_exists('exec')) {
                exec('rm -rf ' . escapeshellarg($cacheDir));
            } else {
                deleteDirectory($cacheDir);
            }
        }
        
        echo json_encode([
            'success' => true,
            'cleaned' => 'all',
            'message' => 'Thumbnail cache cleared successfully'
        ]);
        exit;
    }

    if ($action === 'get_recycle_bin') {
        header('Content-Type: application/json');
        
        $recycleDir = $RECYCLE_BIN_DIR;
        $items = [];
        
        if (is_dir($recycleDir)) {
            try {
                $iterator = new DirectoryIterator($recycleDir);
                
                foreach ($iterator as $item) {
                    if ($item->isDot()) continue;
                    
                    $itemPath = $item->getPathname();
                    $metaFile = $itemPath . '.meta.json';
                    
                    $originalPath = '';
                    $deletedTime = $item->getMTime();
                    
                    if (file_exists($metaFile)) {
                        $meta = json_decode(file_get_contents($metaFile), true);
                        $originalPath = $meta['original_path'] ?? '';
                        $deletedTime = $meta['deleted_time'] ?? $item->getMTime();
                    }
                    
                    $items[] = [
                        'name' => $item->getFilename(),
                        'path' => $itemPath,
                        'original_path' => $originalPath,
                        'size' => $item->isDir() ? 0 : $item->getSize(),
                        'size_formatted' => formatFileSize($item->isDir() ? 0 : $item->getSize()),
                        'deleted_time' => $deletedTime,
                        'deleted_formatted' => date('Y-m-d H:i:s', $deletedTime),
                        'is_dir' => $item->isDir(),
                        'ext' => $item->isDir() ? '' : strtolower($item->getExtension())
                    ];
                }
                
                usort($items, function($a, $b) {
                    return $b['deleted_time'] - $a['deleted_time'];
                });
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
        }
        
        echo json_encode(['success' => true, 'items' => $items]);
        exit;
    }

    if ($action === 'move_to_recycle_bin') {
        header('Content-Type: application/json');
        
        $path = isset($_GET['path']) ? urldecode($_GET['path']) : '';
        $forceDelete = isset($_GET['force']) && $_GET['force'] === 'true';
        
        $realPath = realpath($path);
        if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }
        
        if ($forceDelete) {
            if (is_dir($realPath)) {
                $success = deleteDirectory($realPath);
            } else {
                $success = @unlink($realPath);
            }
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'File permanently deleted' : 'Delete failed'
            ]);
            exit;
        }
        
        if (!is_dir($RECYCLE_BIN_DIR)) {
            @mkdir($RECYCLE_BIN_DIR, 0755, true);
        }
        
        $uniqueName = uniqid() . '_' . basename($realPath);
        $recyclePath = $RECYCLE_BIN_DIR . $uniqueName;
        
        $metaData = [
            'original_path' => $realPath,
            'deleted_time' => time(),
            'is_dir' => is_dir($realPath)
        ];
        file_put_contents($recyclePath . '.meta.json', json_encode($metaData));
        
        if (@rename($realPath, $recyclePath)) {
            echo json_encode(['success' => true, 'message' => 'Moved to recycle bin']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to move to recycle bin']);
        }
        exit;
    }

    if ($action === 'restore_from_recycle_bin') {
        header('Content-Type: application/json');
        
        $recyclePath = isset($_GET['path']) ? urldecode($_GET['path']) : '';
        
        $realPath = realpath($recyclePath);
        if (!$realPath || strpos($realPath, realpath($RECYCLE_BIN_DIR)) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid recycle bin path']);
            exit;
        }
        
        $metaFile = $realPath . '.meta.json';
        if (!file_exists($metaFile)) {
            echo json_encode(['success' => false, 'error' => 'Metadata not found']);
            exit;
        }
        
        $meta = json_decode(file_get_contents($metaFile), true);
        $originalPath = $meta['original_path'];
        
        if (file_exists($originalPath)) {
            $dir = dirname($originalPath);
            $name = basename($originalPath);
            $pathinfo = pathinfo($name);
            $counter = 1;
            
            while (file_exists($originalPath)) {
                $newName = $pathinfo['filename'] . '_recover_' . $counter . (isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '');
                $originalPath = $dir . '/' . $newName;
                $counter++;
            }
        }
        
        $targetDir = dirname($originalPath);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }
        
        if (@rename($realPath, $originalPath)) {
            @unlink($metaFile);
            echo json_encode(['success' => true, 'message' => 'File restored', 'restored_path' => $originalPath]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to restore file']);
        }
        exit;
    }

    if ($action === 'empty_recycle_bin') {
        header('Content-Type: application/json');
        
        if (is_dir($RECYCLE_BIN_DIR)) {
            deleteDirectory($RECYCLE_BIN_DIR);
            @mkdir($RECYCLE_BIN_DIR, 0755, true);
            echo json_encode(['success' => true, 'message' => 'Recycle bin emptied']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Recycle bin is already empty']);
        }
        exit;
    }

    if ($action === 'recycle_bin_settings') {
        header('Content-Type: application/json');
        
        $enabled = isset($_POST['enabled']) ? $_POST['enabled'] === 'true' : $RECYCLE_BIN_ENABLED;
        $days = isset($_POST['days']) ? intval($_POST['days']) : $RECYCLE_BIN_DAYS;
        
        $settingsFile = './config/recycle_bin.json';
        if (!is_dir('./config')) {
            @mkdir('./config', 0755, true);
        }
        
        $settings = [
            'enabled' => $enabled,
            'days' => $days,
            'updated' => time()
        ];
        
        file_put_contents($settingsFile, json_encode($settings));
        
        echo json_encode(['success' => true, 'settings' => $settings]);
        exit;
    }

    if ($action === 'get_recycle_bin_settings') {
        header('Content-Type: application/json');
        
        $settingsFile = './config/recycle_bin.json';
        $defaultSettings = [
            'enabled' => $RECYCLE_BIN_ENABLED,
            'days' => $RECYCLE_BIN_DAYS
        ];
        
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true);
            if ($settings) {
                $settings = array_merge($defaultSettings, $settings);
            } else {
                $settings = $defaultSettings;
            }
        } else {
            $settings = $defaultSettings;
        }
        
        echo json_encode(['success' => true, 'settings' => $settings]);
        exit;
    }

    if ($action === 'archive_action') {
        header('Content-Type: application/json');
    
        $actionType = isset($_POST['action_type']) ? $_POST['action_type'] : '';
        $archiveType = isset($_POST['archive_type']) ? $_POST['archive_type'] : 'zip';
        $destination = isset($_POST['destination']) ? urldecode($_POST['destination']) : '';
    
        $result = null;
        $message = '';
    
        if ($actionType === 'extract') {
            $path = isset($_POST['path']) ? urldecode($_POST['path']) : '';
        
            if (empty($path)) {
                echo json_encode(['success' => false, 'error' => 'Path is required']);
                exit;
            }
        
            $realPath = realpath($path);
            if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0 || !is_file($realPath)) {
                echo json_encode(['success' => false, 'error' => 'Not a valid archive file']);
                exit;
            }
        
            $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        
            if (empty($destination)) {
                $destination = dirname($realPath);
            }
        
            $destRealPath = $destination;
            if (!file_exists($destRealPath)) {
                @mkdir($destRealPath, 0755, true);
            }
        
            if (!is_dir($destRealPath) || !is_writable($destRealPath)) {
                echo json_encode(['success' => false, 'error' => 'Destination directory is not writable']);
                exit;
            }
        
            $result = extractArchive($realPath, $destRealPath, $ext);
            $message = $result ? 'Archive extracted successfully' : 'Failed to extract archive';
        
        } elseif ($actionType === 'compress') {
            if (empty($destination)) {
                echo json_encode(['success' => false, 'error' => 'Destination is required']);
                exit;
            }
        
            $destDir = dirname($destination);
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0755, true);
            }
        
            if (isset($_POST['paths']) && is_array($_POST['paths'])) {
                $paths = array_map('urldecode', $_POST['paths']);
                $validPaths = [];
            
                foreach ($paths as $path) {
                    $realPath = realpath($path);
                    if ($realPath && strpos($realPath, $ROOT_DIR) === 0) {
                        $validPaths[] = $realPath;
                    }
                }
            
                if (empty($validPaths)) {
                    echo json_encode(['success' => false, 'error' => 'No valid files to compress']);
                    exit;
                }
            
                $result = createArchive($validPaths, $destination, $archiveType);
                $message = $result ? 'Archive created successfully' : 'Failed to create archive';
            
            } elseif (isset($_POST['path'])) {
                $path = urldecode($_POST['path']);
                $realPath = realpath($path);
            
                if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
                    echo json_encode(['success' => false, 'error' => 'Invalid path']);
                    exit;
                }
            
                $result = createArchive($realPath, $destination, $archiveType);
                $message = $result ? 'Archive created successfully' : 'Failed to create archive';
            
            } else {
                echo json_encode(['success' => false, 'error' => 'No files to compress']);
                exit;
            }
        
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action type']);
            exit;
        }
    
        echo json_encode([
            'success' => $result,
            'message' => $message,
            'action' => $actionType,
            'destination' => $destination
        ]);
        exit;
    }
}

function getDetailedMediaInfo($filePath, $extension) {
    $info = [];
    
    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico'])) {
        $imageInfo = @getimagesize($filePath);
        if ($imageInfo) {
            $info['type'] = 'image';
            $info['width'] = $imageInfo[0];
            $info['height'] = $imageInfo[1];
            $info['resolution'] = $imageInfo[0] . 'x' . $imageInfo[1];
            $info['mime'] = $imageInfo['mime'];
            $info['bits'] = $imageInfo['bits'] ?? null;
            $info['channels'] = $imageInfo['channels'] ?? null;
        }
        return $info;
    }
    
    $ffprobePath = '/usr/bin/ffprobe';
    $ffmpegPath = '/usr/bin/ffmpeg';
    
    if (file_exists($ffprobePath)) {
        $cmd = $ffprobePath . " -v quiet -print_format json -show_format -show_streams " . escapeshellarg($filePath);
        $output = shell_exec($cmd);
        
        if ($output) {
            $data = json_decode($output, true);
            if ($data) {
                $info['type'] = in_array($extension, ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac']) ? 'audio' : 'video';
                $info['format'] = $data['format']['format_name'] ?? $extension;
                $info['size'] = $data['format']['size'] ?? filesize($filePath);
                $info['bitrate'] = isset($data['format']['bit_rate']) ? round($data['format']['bit_rate'] / 1000) . ' kbps' : null;
                
                if (isset($data['format']['duration'])) {
                    $duration = floatval($data['format']['duration']);
                    $hours = floor($duration / 3600);
                    $minutes = floor(($duration % 3600) / 60);
                    $seconds = floor($duration % 60);
                    $info['duration'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                    $info['duration_seconds'] = $duration;
                }
                
                foreach ($data['streams'] as $stream) {
                    if ($stream['codec_type'] === 'video') {
                        $info['video_codec'] = $stream['codec_name'] ?? null;
                        $info['width'] = $stream['width'] ?? null;
                        $info['height'] = $stream['height'] ?? null;
                        if ($info['width'] && $info['height']) {
                            $info['resolution'] = $info['width'] . 'x' . $info['height'];
                        }
                        $info['frame_rate'] = $stream['r_frame_rate'] ?? null;
                        if ($info['frame_rate']) {
                            $parts = explode('/', $info['frame_rate']);
                            if (count($parts) == 2 && $parts[1] != 0) {
                                $info['frame_rate'] = round($parts[0] / $parts[1], 2) . ' fps';
                            }
                        }
                        break;
                    }
                }
                
                foreach ($data['streams'] as $stream) {
                    if ($stream['codec_type'] === 'audio') {
                        $info['audio_codec'] = $stream['codec_name'] ?? null;
                        $info['sample_rate'] = isset($stream['sample_rate']) ? $stream['sample_rate'] . ' Hz' : null;
                        $info['channels'] = $stream['channels'] ?? null;
                        if ($info['channels']) {
                            $channelNames = ['', 'Mono', 'Stereo', '2.1', '5.1', '7.1'];
                            $info['channel_layout'] = $channelNames[$info['channels']] ?? $info['channels'] . ' channels';
                        }
                        break;
                    }
                }
                
                return $info;
            }
        }
    }
    
    if (file_exists($ffmpegPath)) {
        $cmd = $ffmpegPath . " -i " . escapeshellarg($filePath) . " 2>&1";
        $output = shell_exec($cmd);
        
        if ($output) {
            $info['type'] = in_array($extension, ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac']) ? 'audio' : 'video';
            
            if (preg_match('/Duration:\s*(\d+):(\d+):(\d+\.\d+)/', $output, $matches)) {
                $hours = intval($matches[1]);
                $minutes = intval($matches[2]);
                $seconds = intval($matches[3]);
                $info['duration'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
            }
            
            if (preg_match('/bitrate:\s*(\d+)\s*kb\/s/', $output, $matches)) {
                $info['bitrate'] = $matches[1] . ' kbps';
            }
            
            if (preg_match('/(\d{3,4})x(\d{3,4})/', $output, $matches)) {
                $info['width'] = intval($matches[1]);
                $info['height'] = intval($matches[2]);
                $info['resolution'] = $matches[1] . 'x' . $matches[2];
            }
            
            if (preg_match('/Stream.*Video:\s*([a-zA-Z0-9]+)/', $output, $matches)) {
                $info['video_codec'] = $matches[1];
            }
            
            if (preg_match('/Stream.*Audio:\s*([a-zA-Z0-9]+)/', $output, $matches)) {
                $info['audio_codec'] = $matches[1];
            }
            
            if (preg_match('/Stream.*Audio:.*?(\d+)\s*Hz/', $output, $matches)) {
                $info['sample_rate'] = $matches[1] . ' Hz';
            }
        }
    }
    
    return !empty($info) ? $info : null;
}

function extractArchive($archivePath, $destination, $type) {
    global $ARCHIVE_TOOLS, $ROOT_DIR;
    
    $type = strtolower($type);
    
    $absArchivePath = realpath($archivePath) ?: $archivePath;
    $absDestination = $destination;
    
    $absArchivePath = str_replace('//', '/', $absArchivePath);
    $absDestination = str_replace('//', '/', $absDestination);
    
    if (strpos($absDestination, $ROOT_DIR) !== 0) {
        return false;
    }
    
    if (!file_exists($absArchivePath)) {
        return false;
    }
    
    if (!file_exists($absDestination)) {
        @mkdir($absDestination, 0755, true);
    }
    
    if (!is_writable($absDestination)) {
        return false;
    }
    
    if ($type === 'zip' && class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($absArchivePath) === TRUE) {
            $result = $zip->extractTo($absDestination);
            $zip->close();
            return $result;
        }
        return false;
    }
    
    $absArchivePath = escapeshellarg($absArchivePath);
    $absDestination = escapeshellarg($absDestination);
    
    switch($type) {
        case 'zip':
            if (!commandExists($ARCHIVE_TOOLS['unzip'])) return false;
            $cmd = "cd $absDestination && {$ARCHIVE_TOOLS['unzip']} -q $absArchivePath 2>&1";
            break;
            
        case 'tar':
            if (!commandExists($ARCHIVE_TOOLS['tar'])) return false;
            $cmd = "cd $absDestination && {$ARCHIVE_TOOLS['tar']} -xf $absArchivePath 2>&1";
            break;
            
        case 'gz':
            if (!commandExists($ARCHIVE_TOOLS['gunzip'])) return false;
            $outputFile = pathinfo(trim($absArchivePath, "'"), PATHINFO_FILENAME);
            $cmd = "cd $absDestination && {$ARCHIVE_TOOLS['gunzip']} -c $absArchivePath > " . escapeshellarg($outputFile) . " 2>&1";
            break;
            
        case 'tar.gz':
        case 'tgz':
            if (!commandExists($ARCHIVE_TOOLS['tar'])) return false;
            $cmd = "cd $absDestination && {$ARCHIVE_TOOLS['tar']} -xzf $absArchivePath 2>&1";
            break;
            
        case 'bz2':
            if (!commandExists($ARCHIVE_TOOLS['bunzip2'])) return false;
            $outputFile = pathinfo(trim($absArchivePath, "'"), PATHINFO_FILENAME);
            $cmd = "cd $absDestination && {$ARCHIVE_TOOLS['bunzip2']} -c $absArchivePath > " . escapeshellarg($outputFile) . " 2>&1";
            break;
            
        case 'tar.bz2':
        case 'tbz2':
            if (!commandExists($ARCHIVE_TOOLS['tar'])) return false;
            $cmd = "cd $absDestination && {$ARCHIVE_TOOLS['tar']} -xjf $absArchivePath 2>&1";
            break;
            
        case '7z':
            if (!commandExists($ARCHIVE_TOOLS['7z'])) return false;
            $cmd = "{$ARCHIVE_TOOLS['7z']} x $absArchivePath -o$absDestination -y 2>&1";
            break;
            
        case 'rar':
            if (!commandExists($ARCHIVE_TOOLS['rar'])) return false;
            $cmd = "cd $absDestination && {$ARCHIVE_TOOLS['rar']} x $absArchivePath 2>&1";
            break;
            
        default:
            return false;
    }
    
    $output = shell_exec($cmd);
    
    if ($output && (strpos($output, 'error') !== false || strpos($output, 'Error') !== false)) {
        return false;
    }
    
    return true;
}

function createArchive($source, $destination, $type) {
    global $ARCHIVE_TOOLS;
    
    $type = strtolower($type);
    
    if ($type === 'zip' && class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            
            if (is_array($source)) {
                foreach ($source as $file) {
                    if (file_exists($file)) {
                        if (is_dir($file)) {
                            $iterator = new RecursiveIteratorIterator(
                                new RecursiveDirectoryIterator($file, RecursiveDirectoryIterator::SKIP_DOTS),
                                RecursiveIteratorIterator::SELF_FIRST
                            );
                            
                            $basePath = rtrim($file, '/') . '/';
                            
                            foreach ($iterator as $item) {
                                $filePath = $item->getRealPath();
                                
                                $relativePath = substr($filePath, strlen($basePath));
                                
                                if ($item->isDir()) {
                                    $zip->addEmptyDir(basename($file) . '/' . $relativePath);
                                } else {
                                    $zip->addFile($filePath, basename($file) . '/' . $relativePath);
                                }
                            }
                        } else {
                            $zip->addFile($file, basename($file));
                        }
                    }
                }
            } 
            else {
                if (is_dir($source)) {
                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::SELF_FIRST
                    );
                    
                    $basePath = rtrim($source, '/') . '/';
                    
                    foreach ($iterator as $item) {
                        $filePath = $item->getRealPath();
                        $relativePath = substr($filePath, strlen($basePath));
                        
                        if ($item->isDir()) {
                            $zip->addEmptyDir($relativePath);
                        } else {
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                } else {
                    $zip->addFile($source, basename($source));
                }
            }
            
            $result = $zip->close();
            return $result;
        }
        return false;
    }
    
    $destination = escapeshellarg($destination);
    
    switch($type) {
        case 'zip':
            if (!commandExists($ARCHIVE_TOOLS['zip'])) return false;
            
            if (is_array($source)) {
                $dirs = [];
                $files = [];
                
                foreach ($source as $file) {
                    if (is_dir($file)) {
                        $parentDir = dirname($file);
                        $dirName = basename($file);
                        $cmd = "cd " . escapeshellarg($parentDir) . " && {$ARCHIVE_TOOLS['zip']} -r $destination $dirName 2>&1";
                        $output = shell_exec($cmd);
                        
                        if ($output && (stripos($output, 'error') !== false || stripos($output, 'failed') !== false)) {
                            return false;
                        }
                    } else {
                        $files[] = escapeshellarg($file);
                    }
                }
                
                if (!empty($files)) {
                    $filesList = implode(' ', $files);
                    $cmd = "{$ARCHIVE_TOOLS['zip']} $destination $filesList 2>&1";
                    $output = shell_exec($cmd);
                    
                    if ($output && (stripos($output, 'error') !== false || stripos($output, 'failed') !== false)) {
                        return false;
                    }
                }
                
                return file_exists(trim($destination, "'"));
                
            } else {
                $source = escapeshellarg($source);
                if (is_dir($source)) {
                    $parentDir = dirname(trim($source, "'"));
                    $dirName = basename(trim($source, "'"));
                    $cmd = "cd " . escapeshellarg($parentDir) . " && {$ARCHIVE_TOOLS['zip']} -r " . basename($destination) . " $dirName 2>&1";
                } else {
                    $cmd = "{$ARCHIVE_TOOLS['zip']} $destination $source 2>&1";
                }
            }
            break;

        case '7z':
            if (!commandExists($ARCHIVE_TOOLS['7z'])) {
                error_log("7z command not found");
                return false;
            }
            
            if (is_array($source)) {
                $files = array();
                foreach ($source as $file) {
                    $files[] = escapeshellarg($file);
                }
                $filesList = implode(' ', $files);
                $cmd = "{$ARCHIVE_TOOLS['7z']} a $destination $filesList 2>&1";
            } else {
                $source = escapeshellarg($source);
                $cmd = "{$ARCHIVE_TOOLS['7z']} a $destination $source 2>&1";
            }
            break;
            
        case 'tar':
            if (!commandExists($ARCHIVE_TOOLS['tar'])) return false;
            
            if (is_array($source)) {
                $files = implode(' ', array_map('escapeshellarg', $source));
                $cmd = "{$ARCHIVE_TOOLS['tar']} -cf $destination $files 2>&1";
            } else {
                $source = escapeshellarg($source);
                $cmd = "{$ARCHIVE_TOOLS['tar']} -cf $destination $source 2>&1";
            }
            break;
            
        case 'gz':
            if (!commandExists($ARCHIVE_TOOLS['gzip'])) return false;
            if (is_array($source)) {
                return false;
            }
            $source = escapeshellarg($source);
            $cmd = "{$ARCHIVE_TOOLS['gzip']} -c $source > $destination 2>&1";
            break;
            
        case 'bz2':
            if (!commandExists($ARCHIVE_TOOLS['bzip2'])) return false;
            if (is_array($source)) {
                return false;
            }
            $source = escapeshellarg($source);
            $cmd = "{$ARCHIVE_TOOLS['bzip2']} -c $source > $destination 2>&1";
            break;
            
        default:
            return false;
    }
    
    $output = shell_exec($cmd);
    
    if ($output && (stripos($output, 'error') !== false || stripos($output, 'failed') !== false)) {
        error_log("Compress error: $output - Command: $cmd");
        return false;
    }
    
    $destFile = trim($destination, "'");
    if (!file_exists($destFile)) {
        $destFile = trim($destination, '"');
    }
    
    return file_exists($destFile);
}

function commandExists($command) {
    $output = shell_exec("which $command 2>/dev/null");
    return !empty($output);
}

function searchFilesByName($rootDir, $searchTerm) {
    global $EXCLUDE_DIRS;
    
    $results = [];
    
    if (!is_dir($rootDir) || !is_readable($rootDir)) {
        return $results;
    }
    
    $searchTermLower = strtolower($searchTerm);
    
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $filePath = $file->getPathname();
            $realPath = realpath($filePath);
            
            if (!$realPath) continue;
            
            foreach ($EXCLUDE_DIRS as $exclude) {
                if (strpos($realPath, $exclude) === 0) {
                    continue 2;
                }
            }
            
            $fileName = $file->getFilename();
            $fileNameLower = strtolower($fileName);
            $isDir = $file->isDir();
            
            if (strpos($fileNameLower, $searchTermLower) !== false) {
                $results[] = [
                    'name' => $fileName,
                    'path' => $filePath,
                    'dir' => dirname($filePath),
                    'is_dir' => $isDir,
                    'size' => $isDir ? 0 : $file->getSize(),
                    'size_formatted' => formatFileSize($isDir ? 0 : $file->getSize()),
                    'modified' => $file->getMTime(),
                    'modified_formatted' => date('Y-m-d H:i:s', $file->getMTime()),
                    'extension' => $isDir ? '' : strtolower($file->getExtension()),
                    'matched_part' => highlightMatch($fileName, $searchTerm)
                ];
                
                if (count($results) >= 100) break;
            }
        }
    } catch (Exception $e) {
    }
    
    return $results;
}

function highlightMatch($fileName, $searchTerm) {
    $position = stripos($fileName, $searchTerm);
    if ($position !== false) {
        $before = substr($fileName, 0, $position);
        $match = substr($fileName, $position, strlen($searchTerm));
        $after = substr($fileName, $position + strlen($searchTerm));
        return [
            'before' => $before,
            'match' => $match,
            'after' => $after
        ];
    }
    return null;
}

function copyDirectory($source, $dest) {
    if (!is_dir($dest)) {
        if (!mkdir($dest, 0755, true)) {
            return false;
        }
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $target = $dest . '/' . $iterator->getSubPathName();
        
        if ($item->isDir()) {
            if (!is_dir($target)) {
                if (!mkdir($target)) {
                    return false;
                }
            }
        } else {
            if (!copy($item, $target)) {
                return false;
            }
        }
    }
    
    return true;
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    
    if (!is_dir($dir)) return @unlink($dir);
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            @unlink($path);
        }
    }
    
    return @rmdir($dir);
}

function getMimeType($ext) {
    $mimeMap = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'gif' => 'image/gif', 'bmp' => 'image/bmp', 'webp' => 'image/webp',
        'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
        'mp3' => 'audio/mpeg', 'wav' => 'audio/wav', 'ogg' => 'audio/ogg',
        'flac' => 'audio/flac', 'm4a' => 'audio/mp4', 'aac' => 'audio/aac',
        'mp4' => 'video/mp4', 'avi' => 'video/x-msvideo', 'mkv' => 'video/x-matroska',
        'mov' => 'video/quicktime', 'wmv' => 'video/x-ms-wmv', 'flv' => 'video/x-flv',
        'webm' => 'video/webm',
        'pdf' => 'application/pdf', 'zip' => 'application/zip',
        'tar' => 'application/x-tar', 'gz' => 'application/gzip', 'bz2' => 'application/x-bzip2',
        '7z' => 'application/x-7z-compressed', 'rar' => 'application/x-rar-compressed',
        'txt' => 'text/plain', 'log' => 'text/plain', 'conf' => 'text/plain',
        'ini' => 'text/plain', 'json' => 'application/json', 'xml' => 'application/xml',
        'html' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript',
        'php' => 'application/x-httpd-php', 'py' => 'text/x-python', 'sh' => 'application/x-sh',
        'md' => 'text/markdown'
    ];
    
    return $mimeMap[strtolower($ext)] ?? 'application/octet-stream';
}

function getMediaInfo($path) {
    $ffmpegPath = '/usr/bin/ffmpeg';
    if (!file_exists($ffmpegPath)) {
        $ffmpegPath = 'ffmpeg';
    }
    
    $cmd = "$ffmpegPath -i \"" . escapeshellarg($path) . "\" 2>&1";
    $output = shell_exec($cmd);
    
    if (!$output) return null;
    
    $info = [];
    
    if (preg_match('/Duration:\s*(\d+):(\d+):(\d+\.\d+)/', $output, $matches)) {
        $hours = intval($matches[1]);
        $minutes = intval($matches[2]);
        $seconds = floatval($matches[3]);
        $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
        $info['duration'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        $info['duration_seconds'] = $totalSeconds;
    }
    
    if (preg_match('/bitrate:\s*(\d+)\s*kb\/s/', $output, $matches)) {
        $info['bitrate'] = $matches[1] . ' kbps';
    }
    
    if (preg_match('/(\d{3,4})x(\d{3,4})/', $output, $matches)) {
        $info['resolution'] = $matches[1] . 'x' . $matches[2];
        $info['width'] = intval($matches[1]);
        $info['height'] = intval($matches[2]);
    }
    
    if (preg_match('/Stream.*Audio:.*?(\d+)\s*Hz/', $output, $matches)) {
        $info['sample_rate'] = $matches[1] . ' Hz';
    }
    
    if (preg_match('/Stream.*Video:.*?([a-zA-Z0-9]+)/', $output, $matches)) {
        $info['video_codec'] = $matches[1];
    }
    
    if (preg_match('/Stream.*Audio:.*?([a-zA-Z0-9]+)/', $output, $matches)) {
        $info['audio_codec'] = $matches[1];
    }
    
    return empty($info) ? null : $info;
}

if (isset($_GET['preview']) && $_GET['preview'] == '1' && isset($_GET['path'])) {
    $filePath = urldecode($_GET['path']);
    $filePath = preg_replace('/#.*$/', '', $filePath);   
    $filePath = preg_replace('#/+#', '/', $filePath);
    if (substr($filePath, 0, 1) !== '/') {
        $filePath = '/' . $filePath;
    }
    
    $realPath = realpath($filePath);
    if (!$realPath || strpos($realPath, $ROOT_DIR) !== 0) {
        http_response_code(403);
        header('Content-Type: text/plain');
        exit('Access Denied: Invalid path');
    }
    
    foreach ($EXCLUDE_DIRS as $exclude) {
        if (strpos($realPath, $exclude) === 0) {
            http_response_code(403);
            header('Content-Type: text/plain');
            exit('Access Denied: Path is excluded');
        }
    }
    
    if (file_exists($realPath) && is_readable($realPath)) {
        $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $mimeMap = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpe' => 'image/jpeg', 'jfif' => 'image/jpeg',
            'png' => 'image/png', 'gif' => 'image/gif', 'bmp' => 'image/bmp', 'webp' => 'image/webp',
            'svg' => 'image/svg+xml', 'svgz' => 'image/svg+xml', 'ico' => 'image/x-icon', 'cur' => 'image/x-icon',
            'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'psd' => 'image/vnd.adobe.photoshop',
            'heic' => 'image/heic', 'heif' => 'image/heif', 'avif' => 'image/avif',
            'mp3' => 'audio/mpeg', 'wav' => 'audio/wav', 'ogg' => 'audio/ogg', 'flac' => 'audio/flac',
            'm4a' => 'audio/mp4', 'aac' => 'audio/aac', 'wma' => 'audio/x-ms-wma', 'opus' => 'audio/opus',
            'mp2' => 'audio/mpeg', 'ac3' => 'audio/ac3', 'dts' => 'audio/vnd.dts', 'eac3' => 'audio/eac3',
            'ape' => 'audio/ape', 'wv' => 'audio/wavpack', 'tta' => 'audio/tta', 'tak' => 'audio/tak',
            'dsf' => 'audio/dsd', 'dff' => 'audio/dsd', 'sacd' => 'audio/sacd',
            'mid' => 'audio/midi', 'midi' => 'audio/midi', 'rmi' => 'audio/midi',
            'amr' => 'audio/amr', 'awb' => 'audio/amr-wb', 'sln' => 'audio/speex',
            'ra' => 'audio/vnd.rn-realaudio', 'ram' => 'audio/vnd.rn-realaudio',
            'aiff' => 'audio/aiff', 'aif' => 'audio/aiff', 'aifc' => 'audio/aiff',
            'caf' => 'audio/x-caf', 'm4r' => 'audio/mp4', 'xmf' => 'audio/mobile-xmf',
            'mp4' => 'video/mp4', 'm4v' => 'video/x-m4v', 'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska', 'mov' => 'video/quicktime', 'wmv' => 'video/x-ms-wmv',
            'flv' => 'video/x-flv', 'webm' => 'video/webm', '3gp' => 'video/3gpp', '3g2' => 'video/3gpp2',
            'ogv' => 'video/ogg', 'mpg' => 'video/mpeg', 'mpeg' => 'video/mpeg', 'mpe' => 'video/mpeg',
            'ts' => 'video/mp2t', 'm2ts' => 'video/mp2t', 'mts' => 'video/mp2t',
            'vob' => 'video/dvd', 'ifo' => 'video/dvd', 'bup' => 'video/dvd',
            'rm' => 'video/vnd.rn-realvideo', 'rmvb' => 'video/vnd.rn-realvideo',
            'qt' => 'video/quicktime', 'hdmov' => 'video/quicktime', 'dv' => 'video/dv',
            'asf' => 'video/x-ms-asf', 'asx' => 'video/x-ms-asf',
            'divx' => 'video/divx', 'xvid' => 'video/xvid',
            'f4v' => 'video/mp4', 'f4p' => 'video/mp4', 'f4a' => 'video/mp4', 'f4b' => 'video/mp4',
            'avchd' => 'video/avchd', 'mxf' => 'video/mxf', 'gxf' => 'video/gxf',
            'mj2' => 'video/mj2', 'drc' => 'video/vnd.dlna.mpeg-tts',
            'dnxhd' => 'video/dnxhd', 'prores' => 'video/prores',
            'vp8' => 'video/vp8', 'vp9' => 'video/vp9', 'av1' => 'video/av1',
            'hevc' => 'video/hevc', 'h264' => 'video/h264', 'h265' => 'video/h265'
        ];
        
        $mimeType = $mimeMap[$ext] ?? 'application/octet-stream';
        $size = filesize($realPath);
        
        $range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null;
        
        if ($range && preg_match('/^bytes=(\d+)-(\d*)$/', $range, $matches)) {
            $start = intval($matches[1]);
            $end = isset($matches[2]) && $matches[2] !== '' ? intval($matches[2]) : $size - 1;
            
            if ($start >= $size || $end >= $size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header('Content-Range: bytes */' . $size);
                exit;
            }
            
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
            header('Content-Length: ' . ($end - $start + 1));
            header('Content-Type: ' . $mimeType);
            header('Accept-Ranges: bytes');
            header('Cache-Control: max-age=3600');
            
            $fp = fopen($realPath, 'rb');
            fseek($fp, $start);
            $bytesToSend = $end - $start + 1;
            $buffer = 8192;
            
            while ($bytesToSend > 0 && !feof($fp)) {
                $readSize = min($buffer, $bytesToSend);
                echo fread($fp, $readSize);
                $bytesToSend -= $readSize;
                flush();
            }
            fclose($fp);
            
        } else {
            header('HTTP/1.1 200 OK');
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . $size);
            header('Accept-Ranges: bytes');
            header('Cache-Control: max-age=3600');
            
            readfile($realPath);
        }
        exit;
        
    } else {
        http_response_code(404);
        header('Content-Type: text/plain');
        exit('File not found or not readable: ' . $filePath);
    }
}

function getCpuUsageSimple() {
    if (!is_readable('/proc/stat')) {
        return 0;
    }
    
    $content1 = @file_get_contents('/proc/stat');
    if ($content1 === false) {
        return 0;
    }
    
    $lines1 = explode("\n", $content1);
    if (empty($lines1[0])) {
        return 0;
    }
    
    if (function_exists('time_nanosleep')) {
        time_nanosleep(0, 100000000);
    } else {
        $start = microtime(true);
        while (microtime(true) - $start < 0.1) {
        }
    }
    
    $content2 = @file_get_contents('/proc/stat');
    if ($content2 === false) {
        return 0;
    }
    
    $lines2 = explode("\n", $content2);
    if (empty($lines2[0])) {
        return 0;
    }
    
    $cpu1 = preg_split('/\s+/', trim($lines1[0]));
    $cpu2 = preg_split('/\s+/', trim($lines2[0]));
    
    if (count($cpu1) < 5 || count($cpu2) < 5) {
        return 0;
    }
    
    $total1 = intval($cpu1[1]) + intval($cpu1[2]) + intval($cpu1[3]) + intval($cpu1[4]);
    $idle1 = intval($cpu1[4]);
    
    $total2 = intval($cpu2[1]) + intval($cpu2[2]) + intval($cpu2[3]) + intval($cpu2[4]);
    $idle2 = intval($cpu2[4]);
    
    $totalDiff = $total2 - $total1;
    $idleDiff = $idle2 - $idle1;
    
    if ($totalDiff > 0) {
        $usage = (($totalDiff - $idleDiff) / $totalDiff) * 100;
        return round($usage, 1);
    }
    
    return 0;
}

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');

    $dt = json_decode(shell_exec("ubus call system board"), true);
    $devices = $dt['model'] ?? 'Unknown';
    
    $cpuUsage = getCpuUsageSimple();

    $tmpramTotal = exec("cat /proc/meminfo | grep MemTotal | awk '{print \$2}'");
    $tmpramAvailable = exec("cat /proc/meminfo | grep MemAvailable | awk '{print \$2}'");
    
    $tmpramTotal = intval($tmpramTotal);
    $tmpramAvailable = intval($tmpramAvailable);
    
    $ramTotal = number_format(($tmpramTotal / 1024), 1);
    $ramAvailable = number_format(($tmpramAvailable / 1024), 1);
    $ramUsage = number_format((($tmpramTotal - $tmpramAvailable) / 1024), 1);
    $memUsage = $tmpramTotal > 0 ? round((($tmpramTotal - $tmpramAvailable) / $tmpramTotal) * 100, 1) : 0;
    
    $raw_uptime = exec("cat /proc/uptime | awk '{print \$1}'");
    $days = floor($raw_uptime / 86400);
    $hours = floor(($raw_uptime / 3600) % 24);
    $minutes = floor(($raw_uptime / 60) % 60);
    $seconds = floor($raw_uptime % 60);
    $uptimeText = "{$days} days {$hours} hours {$minutes} minutes {$seconds} seconds";
    
    $cpuLoad = shell_exec("cat /proc/loadavg");
    $cpuLoad = explode(' ', $cpuLoad);
    $cpuLoadAvg1Min = round($cpuLoad[0], 2);
    $cpuLoadAvg5Min = round($cpuLoad[1], 2);
    $cpuLoadAvg15Min = round($cpuLoad[2], 2);
    
    $timezone = trim(shell_exec("uci get system.@system[0].zonename 2>/dev/null"));
    if (!$timezone) {
        $timezone = trim(shell_exec("cat /etc/TZ 2>/dev/null"));
        if (!$timezone) {
            $timezone = 'UTC';
        }
    }
    date_default_timezone_set($timezone);
    $currentTime = date("Y-m-d H:i:s");

    $cpuModel = 'Unknown';
    if (file_exists('/proc/cpuinfo')) {
        $cpuInfoContent = file_get_contents('/proc/cpuinfo');
        if (preg_match('/model name\s*:\s*(.+)/', $cpuInfoContent, $matches)) {
            $cpuModel = trim($matches[1]);
            $cpuModel = preg_replace('/\bProcessor\b/i', '', $cpuModel);
            $cpuModel = preg_replace('/\s+/', ' ', $cpuModel);
            $cpuModel = trim($cpuModel);
        } elseif (preg_match('/Hardware\s*:\s*(.+)/', $cpuInfoContent, $matches)) {
            $cpuModel = trim($matches[1]);
        }
    }
    
    $cpuTemp = '--';
    $tempFiles = [
        '/sys/class/thermal/thermal_zone0/temp',
        '/sys/devices/virtual/thermal/thermal_zone0/temp'
    ];
    foreach ($tempFiles as $tempFile) {
        if (file_exists($tempFile)) {
            $temp = intval(file_get_contents($tempFile));
            if ($temp > 0) {
                $cpuTemp = $temp > 1000 ? round($temp / 1000, 1) : round($temp, 1);
                break;
            }
        }
    }
    
    $cpuCores = exec("grep -c '^processor' /proc/cpuinfo");
    $cpuCores = intval($cpuCores) ?: 1;
    
    $cpuFreq = '--';
    $freqFiles = [
        '/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq',
        '/sys/devices/system/cpu/cpu0/cpufreq/cpuinfo_cur_freq',
        '/proc/cpuinfo'
    ];
    
    foreach ($freqFiles as $freqFile) {
        if (file_exists($freqFile)) {
            if ($freqFile === '/proc/cpuinfo') {
                $freqContent = file_get_contents($freqFile);
                if (preg_match('/cpu MHz\s*:\s*([\d.]+)/', $freqContent, $matches)) {
                    $freq = floatval($matches[1]);
                    $cpuFreq = round($freq, 0) . ' MHz';
                    break;
                }
            } else {
                $freqContent = file_get_contents($freqFile);
                if ($freqContent !== false) {
                    $freq = intval(trim($freqContent));
                    if ($freq > 0) {
                        if ($freq > 1000) {
                            $cpuFreq = round($freq / 1000, 1) . ' GHz';
                        } else {
                            $cpuFreq = $freq . ' MHz';
                        }
                        break;
                    }
                }
            }
        }
    }
    
    $processCount = intval(shell_exec("ps | wc -l")) - 1;
    
    $networkRx = 0;
    $networkTx = 0;
    
    $netStat = @file('/proc/net/dev');
    if ($netStat) {
        $interfaces = ['br-lan', 'eth0', 'eth1', 'wlan0', 'wlan1'];
        
        foreach ($netStat as $line) {
            if (strpos($line, ':') === false) continue;
            
            foreach ($interfaces as $interface) {
                $pattern = '/^\s*' . $interface . ':\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/';
                if (preg_match($pattern, $line, $matches)) {
                    $networkRx = intval($matches[1]);
                    $networkTx = intval($matches[2]);
                    break 2;
                }
            }
        }
    }
    
    if ($networkRx == 0 && $networkTx == 0 && $netStat) {
        foreach ($netStat as $line) {
            if (strpos($line, ':') !== false && !preg_match('/^\s*lo:/', $line)) {
                if (preg_match('/^\s*(\w+):\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', $line, $matches)) {
                    $networkRx = intval($matches[2]);
                    $networkTx = intval($matches[3]);
                    break;
                }
            }
        }
    }
    
    $diskInfo = disk_free_space('/') !== false ? [
        'total' => disk_total_space('/'),
        'free' => disk_free_space('/'),
        'used' => disk_total_space('/') - disk_free_space('/')
    ] : null;
    
    $diskUsage = $diskInfo ? round(($diskInfo['used'] / $diskInfo['total']) * 100, 1) : 0;
    $diskTotal = $diskInfo ? round($diskInfo['total'] / (1024*1024*1024), 2) : 0;
    $diskUsed = $diskInfo ? round($diskInfo['used'] / (1024*1024*1024), 2) : 0;

    $openwrtVersion = trim(shell_exec("cat /etc/openwrt_release | grep 'DISTRIB_DESCRIPTION' | cut -d'=' -f2 | sed \"s/['\\\"]//g\""));
    if (!$openwrtVersion) {
        $openwrtVersion = trim(shell_exec("cat /etc/openwrt_release | grep 'DISTRIB_DESCRIPTION' | awk -F\"='\" '{print \$2}' | sed \"s/'//g\""));
    }
    if (!$openwrtVersion) {
        $openwrtVersion = $dt['release']['distribution'] . ' ' . $dt['release']['version'] ?? 'Unknown';
    }
    
    $kernelVersion = trim(shell_exec("uname -r"));
    if (!$kernelVersion) {
        $kernelVersion = trim(shell_exec("cat /proc/version | awk '{print \$3}'"));
    }
    
    $boardInfo = json_decode(shell_exec("ubus call system board"), true);
    $boardModel = $boardInfo['model'] ?? 'Unknown';
    
    echo json_encode([
        'success' => true,
        'cpu_usage' => $cpuUsage,
        'cpu_model' => $cpuModel,
        'mem_usage' => $memUsage,
        'mem_total' => $ramTotal,
        'mem_used' => $ramUsage,
        'mem_free' => $ramAvailable,
        'cpu_temp' => $cpuTemp,
        'process_count' => $processCount,
        'cpu_cores' => $cpuCores,
        'cpu_freq' => $cpuFreq,
        'network_rx' => $networkRx,
        'network_tx' => $networkTx,
        'load_avg' => "$cpuLoadAvg1Min, $cpuLoadAvg5Min, $cpuLoadAvg15Min",
        'uptime' => $uptimeText,
        'system_time' => $currentTime,
        'timezone' => $timezone,
        'disk_usage' => $diskUsage,
        'disk_total' => $diskTotal,
        'disk_used' => $diskUsed,
        'openwrt_version' => $openwrtVersion,
        'kernel_version' => $kernelVersion,
        'board_model' => $boardModel
    ]);
    exit;
}

function getDiskInfo($path = '/') {
    $freeSpace = @disk_free_space($path);
    $totalSpace = @disk_total_space($path);
    
    if ($freeSpace === false || $totalSpace === false) {
        return null;
    }
    
    $usedSpace = $totalSpace - $freeSpace;
    $usedPercent = $totalSpace > 0 ? round(($usedSpace / $totalSpace) * 100, 1) : 0;
    
    $free_mb = round($freeSpace / (1024*1024), 1);
    $total_mb = round($totalSpace / (1024*1024), 1);
    $used_mb = round($usedSpace / (1024*1024), 1);
    
    return [
        'free' => $freeSpace,
        'total' => $totalSpace,
        'used' => $usedSpace,
        'used_percent' => $usedPercent,
        'free_mb' => $free_mb,
        'total_mb' => $total_mb,
        'used_mb' => $used_mb
    ];
}

function scanDirectory($path, $maxDepth = 5, $fast = false) {
    global $EXCLUDE_DIRS;
    $files = [];
    $seenFiles = [];
    
    if (!is_dir($path) || !is_readable($path)) {
        return $files;
    }
    
    $path = preg_replace('#/+#', '/', $path);
    
    foreach ($EXCLUDE_DIRS as $exclude) {
        if (strpos($path, $exclude) === 0) {
            return $files;
        }
    }
    
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        
        $iterator->setMaxDepth($maxDepth);
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->isReadable()) {
                $filePath = $file->getPathname();
                $realPath = realpath($filePath);
                
                if (!$realPath) continue;
                
                $excluded = false;
                foreach ($EXCLUDE_DIRS as $exclude) {
                    if (strpos($realPath, $exclude) === 0) {
                        $excluded = true;
                        break;
                    }
                }
                
                if ($excluded) continue;

                $fileName = $file->getFilename();
                $fileSize = $file->getSize();
                $fileKey = $fileName . '_' . $fileSize;
                
                if (isset($seenFiles[$fileKey])) {
                    continue;
                }
                
                $seenFiles[$fileKey] = true;
                
                if ($fast) {
                    $ext = strtolower($file->getExtension());
                    $files[] = [
                        'path' => $realPath,
                        'name' => $fileName,
                        'size' => $fileSize,
                        'mtime' => $file->getMTime(),
                        'ext' => $ext,
                        'safe_path' => htmlspecialchars($realPath, ENT_QUOTES, 'UTF-8'),
                        'safe_name' => htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8')
                    ];
                } else {
                    $files[] = [
                        'path' => $realPath,
                        'name' => $fileName,
                        'size' => $fileSize,
                        'mtime' => $file->getMTime(),
                        'ext' => strtolower($file->getExtension()),
                        'safe_path' => htmlspecialchars($realPath, ENT_QUOTES, 'UTF-8'),
                        'safe_name' => htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8')
                    ];
                }
            }
            
            if (count($files) % 100 == 0) {
                gc_collect_cycles();
            }
        }
    } catch (Exception $e) {
        error_log("Scan error: " . $e->getMessage());
    }
    
    return $files;
}

function formatFileSize($bytes) {
    if ($bytes == 0) return "0 B";
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

function getVideoThumbnail($videoPath) {
    return "?thumbnail=1&path=" . urlencode($videoPath);
}

$media = ['music' => [], 'video' => [], 'image' => []];

$musicFile = './lib/music_cache.json';
$videoFile = './lib/video_cache.json';
$imageFile = './lib/image_cache.json';

if (file_exists($musicFile)) {
    $cached = json_decode(file_get_contents($musicFile), true);
    if ($cached) {
        $media['music'] = $cached;
    }
}

if (file_exists($videoFile)) {
    $cached = json_decode(file_get_contents($videoFile), true);
    if ($cached) {
        $media['video'] = $cached;
    }
}

if (file_exists($imageFile)) {
    $cached = json_decode(file_get_contents($imageFile), true);
    if ($cached) {
        $media['image'] = $cached;
    }
}

$diskInfo = getDiskInfo('/');
$recent = isset($_COOKIE['recent_media']) ? json_decode($_COOKIE['recent_media'], true) : [];
$systemInfo = [
    'cpu_usage' => 0,
    'mem_usage' => 0,
    'mem_total' => 0,
    'mem_used' => 0,
    'mem_free' => 0,
    'uptime' => '',
    'load_avg' => '',
    'system_time' => date('Y-m-d H:i:s'),
    'timezone' => 'UTC'
];
?>