<?php
ini_set('memory_limit', '256M');
$base_dir = __DIR__;
$upload_dir = $base_dir;
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'mkv', 'mp3', 'wav', 'flac'];
$background_type = '';
$background_src = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    $files = $_FILES['upload_file'];
    $upload_errors = [];
    
    foreach ($files['name'] as $key => $filename) {
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $raw_filename = urldecode($filename);
            
            $ext = strtolower(pathinfo($raw_filename, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_types)) {
                $upload_errors[] = "不支持的文件类型：{$raw_filename}";
                continue;
            }
            
            $basename = pathinfo($raw_filename, PATHINFO_FILENAME);
            
            $safe_basename = preg_replace([
                '/[^\p{L}\p{N}_\- ]/u',
                '/\s+/',
                '/_+/',
                '/-+/'
            ], [
                '_',
                '_',
                '_',
                '-'
            ], $basename);
            
            $safe_basename = trim($safe_basename, '_.- ');
            
            if (empty($safe_basename)) {
                $safe_basename = uniqid();
            }
            
            $counter = 1;
            $final_name = "{$safe_basename}.{$ext}";
            $target_path = "{$upload_dir}/{$final_name}";
            while (file_exists($target_path)) {
                $final_name = "{$safe_basename}_{$counter}.{$ext}";
                $target_path = "{$upload_dir}/{$final_name}";
                $counter++;
            }
            
            if (!move_uploaded_file($files['tmp_name'][$key], $target_path)) {
                $upload_errors[] = "文件上传失败：{$final_name}";
            }
        }
    }
    
    if (!empty($upload_errors)) {
        echo "<script>alert('".implode("\\n", $upload_errors)."'); location.reload();</script>";
    } else {
        header("Location: ".$_SERVER['REQUEST_URI']);
    }
    exit;
}

if (isset($_GET['delete'])) {
    $file = $base_dir . '/' . basename($_GET['delete']);
    if (file_exists($file)) {
        unlink($file);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

if (isset($_POST['rename'])) {
    $oldName = $_POST['old_name'];
    $newName = trim($_POST['new_name']);

    $oldPath = $base_dir . DIRECTORY_SEPARATOR . basename($oldName);
    $newPath = $base_dir . DIRECTORY_SEPARATOR . basename($newName);

    $error = '';
    if (!file_exists($oldPath)) {
        $error = '原始文件不存在';
    } elseif ($newName === '') {
        $error = '文件名不能为空';
    } elseif (preg_match('/[\\\\\/:*?"<>|]/', $newName)) {
        $error = '包含非法字符：\/:*?"<>|';
    } elseif (file_exists($newPath)) {
        $error = '目标文件已存在';
    }

    if (!$error) {
        if (rename($oldPath, $newPath)) {
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            $error = '操作失败（权限/字符问题）';
        }
    }

    if ($error) {
        echo '<div class="alert alert-danger mb-3">错误：' 
             . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') 
             . '</div>';
    }
}

if (isset($_POST['batch_delete'])) {
    $deleted_files = [];
    foreach ($_POST['filenames'] as $filename) {
        $file = $base_dir . '/' . basename($filename);
        if (file_exists($file)) {
            unlink($file);
            $deleted_files[] = $filename;
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'deleted_files' => $deleted_files]);
    exit;
}

$files = array_diff(scandir($upload_dir), ['..', '.', '.htaccess', 'index.php']);
$files = array_filter($files, function ($file) {
    return pathinfo($file, PATHINFO_EXTENSION) !== 'php';
});

if (isset($_GET['background'])) {
    $background_src = htmlspecialchars($_GET['background']);
    $ext = strtolower(pathinfo($background_src, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        $background_type = 'image';
    } elseif (in_array($ext, ['mp4', 'mov'])) {
        $background_type = 'video';
    }
}
?>

<?php
$configFile = "/etc/config/spectra"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!file_exists($configFile)) {
        echo json_encode(["error" => "Config file not found!"]);
        exit;
    }

    $content = file_get_contents($configFile);
    preg_match("/option mode '(\w+)'/", $content, $matches);
    $currentMode = $matches[1] ?? "N/A";

    $newMode = ($currentMode === "dark") ? "light" : "dark";
    $updatedContent = preg_replace("/option mode '\w+'/", "option mode '$newMode'", $content);
    
    if (file_put_contents($configFile, $updatedContent) !== false) {
        echo json_encode(["success" => true, "mode" => $newMode]);
    } else {
        echo json_encode(["error" => "Failed to update config!"]);
    }
    exit;
}

if (!file_exists($configFile)) {
    $mode = "N/A";
} else {
    $content = file_get_contents($configFile);
    preg_match("/option mode '(\w+)'/", $content, $matches);
    $mode = $matches[1] ?? "N/A";
}
?>

<?php
$repoOwner = 'Thaolga';
$repoName = 'openwrt-nekobox';
$releaseTag = '1.8.8'; 
$packagePattern = '/^luci-theme-spectra_(.+)_all\.ipk$/';

$latestVersion = null;
$downloadUrl = null;

try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/{$repoOwner}/{$repoName}/releases/tags/{$releaseTag}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP');
    $response = curl_exec($ch);
        
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
        $releaseData = json_decode($response, true);
            
        foreach ($releaseData['assets'] as $asset) {
            if (preg_match($packagePattern, $asset['name'], $matches)) {
                $latestVersion = $matches[1];
                $downloadUrl = $asset['browser_download_url'];
                break;
            }
        }
    }
    curl_close($ch);
 } catch(Exception $e) {
}
?>

<head>
    <meta charset="utf-8">
    <title>媒体文件管理</title>
    <link href="/luci-static/spectra/css/bootstrap.min.css" rel="stylesheet">
    <script src="/luci-static/spectra/js/jquery.min.js"></script>
    <script src="/luci-static/spectra/js/bootstrap.bundle.min.js"></script>
    <script src="/luci-static/spectra/js/custom.js"></script>
    <script src="/luci-static/spectra/js/interact.min.js"></script>
    <link href="/luci-static/spectra/css//bootstrap-icons.css" rel="stylesheet">

    <script>
        const phpBackgroundType = '<?= $background_type ?>';
        const phpBackgroundSrc = '<?= $background_src ?>';
    </script>
<style>
body {
	text-align: center;
	font-family: Arial, sans-serif;
	padding: 20px;
        background: rgba(0, 0, 0, 0.5); 
        -webkit-backdrop-filter: blur(10px);
}

html, body {
    height: 100%;
    margin: 0;
    overflow-y: auto !important;
}

button {
	padding: 10px 20px;
	font-size: 16px;
	border: none;
	cursor: pointer;
	transition: 0.3s;
}

.light {
	background: white;
	color: black;
	border: 1px solid black;
}

.dark {
	background: black;
	color: white;
	border: 1px solid white;
}

.container-bg {
	background: #2a2a2a;
	padding: 20px;
	border-radius: 10px;
	box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
}

.card {
	background: #333;
	border: none;
	color: #fff;
}

.card-header {
	background: #444;
	color: #ffcc00;
	font-size: 1.5rem;
	font-weight: bold;
}

.table {
	color: #fff;
}

.table th {
	background: #444;
	color: #ffcc00;
	border-color: #555;
}

.table td {
	background: #3a3a3a;
	border-color: #555;
	color: #fff;
}

.table-striped tbody tr:nth-of-type(odd) {
	background: #2e2e2e;
}

.btn {
	border-radius: 8px;
	font-weight: bold;
	transition: transform 0.2s;
}

.btn:hover {
	transform: scale(1.1);
}

.btn-info {
	color: #fff !important;
}

.modal-content {
	background: #2a2a2a;
	color: #fff;
	border-radius: 10px;
}

.modal-header {
	background: #3a3a3a;
	border-bottom: 1px solid #444;
}

.modal-title {
	color: #ffcc00;
}

.modal-footer {
	border-top: 1px solid #444;
}

.btn-close {
	filter: invert(1);
}

.img-thumbnail {
	background: #555;
	border: 1px solid #777;
}

.table th, .table td {
	vertical-align: middle;
}

#status {
	font-size: 22px;
	color: #FFD700 !important;
}

h2 {
	color: oklch(77% .152 181.912) !important;
	margin-top: 20px;
}

.preview-container {
	position: relative;
	overflow: hidden;
	cursor: pointer;
	min-height: 200px;
	background: #2a2a2a;
	display: flex;
	align-items: center;
	justify-content: center;
}

.file-info-overlay {
	position: absolute;
	bottom: 0;
	left: 0;
	right: 0;
	background: rgba(0,0,0,0.7);
	color: white;
	padding: 0.5rem;
	transform: translateY(100%);
	transition: 0.2s;
	font-size: 0.8em;
}

.file-type-indicator {
	position: absolute;
	top: 8px;
	right: 8px;
	z-index: 2;
	background: rgba(0,0,0,0.6);
	padding: 4px 10px;
	border-radius: 15px;
	display: flex;
	align-items: center;
	gap: 6px;
	backdrop-filter: blur(2px);
}

.preview-container:hover .file-info-overlay {
	transform: translateY(0);
}

.preview-img {
	object-fit: contain !important;
	max-height: 300px;
	width: auto;
	height: auto;
	max-width: 100%;
	padding: 8px;
}

.video-wrapper {
	width: 100%;
	height: 0;
	padding-top: 56.25%;
	position: relative;
}

.preview-video {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	object-fit: contain;
}

.preview-video:hover::after {
	content: "";
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%,-50%);
	width: 40px;
	height: 40px;
	background: rgba(255,255,255,0.8) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='%23000000'%3E%3Cpath d='M8 5v14l11-7z'/%3E%3C/svg%3E") no-repeat center;
	border-radius: 50%;
	opacity: 0.8;
	pointer-events: none;
}

.card:hover .preview-img {
	transform: scale(1.03);
}

.fileCheckbox {
	margin-right: 8px;
	transform: scale(1.2);
}

#previewImage, #previewVideo {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;  
}

.card-body.pt-2.mt-2 .d-flex {
        justify-content: center;
}

.card-body.pt-2.mt-2 .d-flex .btn {
        margin: 0 5px; 
}

#playlistContainer .list-group-item {
	cursor: pointer;
	transition: background-color 0.3s, transform 0.2s;
	padding: 0.75rem 1rem;
	word-wrap: break-word;
	word-break: break-word;
	overflow-wrap: break-word;
	white-space: normal;
}

#playlistContainer .list-group-item:hover {
	background-color: #666 !important;
	transform: scale(1.02);
	box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

#playlistContainer .list-group-item.active {
	background-color: #0d6efd;
	border-color: #0d6efd;
	color: white;
}

#playlistContainer .badge {
	width: 24px;
	text-align: center;
	font-weight: normal;
}

#playlistContainer .delete-item {
	opacity: 0.6;
	transition: opacity 0.3s;
}

#playlistContainer .delete-item:hover {
	opacity: 1;
}

#playlistContainer {
	overflow-x: hidden;
	/
    overflow-y: auto;
}

#playlistContainer .text-truncate {
	display: inline-block;
	width: 100%;
	white-space: normal;
	word-wrap: break-word;
	word-break: break-word;
	font-size: 1.1em;
	line-height: 1.4;
}

#playlistContainer .list-group-item {
	padding: 1rem 1.5rem;
	margin-bottom: 3px;
	border-radius: 6px;
	transition: all 0.3s ease;
}

#playlistContainer .list-group-item:nth-child(odd) {
	background-color: #444;
	border-left: 3px solid #666;
	color: white;
}

#playlistContainer .list-group-item:nth-child(even) {
	background-color: #393939;
	border-left: 3px solid #555;
	color: white;
}

#playlistContainer .list-group-item.active {
	background: linear-gradient(145deg, #0a58ca, #0d6efd) !important;
	border-color: #004cff;
	transform: scale(1.02);
	box-shadow: 0 5px 15px rgba(0, 110, 253, 0.4);
	z-index: 1;
	color: white;
}

#playlistContainer .list-group-item:hover {
	background-color: #4a4a4a;
	transform: translateX(5px);
	cursor: pointer;
}

.text-muted {
	color: #FF99FF !important;
	font-size: 0.9em;
	letter-spacing: 0.5px;
}

::-webkit-scrollbar {
	width: 8px;
        opacity: 0 !important;
        transition: opacity 0.3s ease-in-out;
}

::-webkit-scrollbar-thumb {
	background: linear-gradient(to bottom, #007bff, #00a8ff);
	border-radius: 4px;
}

::-webkit-scrollbar-track {
	background-color: rgba(255, 255, 255, 0.1);
	margin: 120px 0;
}

body:hover, 
.container:hover, 
#playlistContainer:hover {
	overflow-x: hidden !important;
        overflow-y: auto !important;
}

#playlistContainer {
        cursor: default; 
}

#playlistContainer:hover {
        cursor: grab;    
}

#playlistContainer:active {
        cursor: grabbing;
}

::-webkit-scrollbar:horizontal {
	display: none !important;
	height: 0 !important;
}

@supports (scrollbar-width: none) {
	html {
		scrollbar-width: none !important;
	}
}

.drop-zone {
	border: 2px dashed rgba(0, 123, 255, 0.8);
	border-radius: 12px;
	padding: 30px;
	text-align: center;
	transition: all 0.3s ease;
	background: rgba(0, 0, 0, 0.3);
	backdrop-filter: blur(10px);
	position: relative;
	min-height: 200px;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
}

.drop-zone.dragover {
	border-color: rgba(13, 110, 253, 0.9);
	background: rgba(13, 110, 253, 0.15);
	box-shadow: 0 0 20px rgba(13, 110, 253, 0.3);
}

.upload-icon {
	font-size: 50px;
	color: rgba(0, 123, 255, 0.8);
	margin-bottom: 15px;
}

.upload-text {
	font-size: 18px;
	font-weight: 500;
	color: #e0e0e0;
	margin-bottom: 10px;
}

#customUploadButton {
	transition: all 0.2s ease;
	padding: 12px 24px;
}

#customUploadButton:hover {
	transform: translateY(-3px);
	box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
}

.file-list {
	max-height: 160px;
	overflow-y: auto;
	background: rgba(255, 255, 255, 0.1);
	border-radius: 8px;
	padding: 10px;
	width: 100%;
	text-align: left;
	box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
	color: #f8f9fa;
}

.file-list-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 8px 12px;
	border-bottom: 1px solid rgba(255, 255, 255, 0.2);
	font-size: 14px;
}

.file-list-item:last-child {
	border-bottom: none;
}

.file-list-item i {
	color: rgba(0, 123, 255, 0.8);
	margin-right: 8px;
}

.remove-file {
	cursor: pointer;
	color: #dc3545;
}

.remove-file:hover {
	color: #b02a37;
}

.upload-or {
        color: #ffffff !important;
}


@media (max-width: 768px) {
	.card-header .d-flex {
		gap: 0.5rem !important;
	}

	.btn-sm .btn-label {
		display: none;
	}

	.btn-sm {
		padding: 0.25rem 0.5rem;
	}

	.me-3 .badge {
		font-size: 0.75rem;
		padding: 0.35em 0.65em;
	}

	#openPlayerBtn span {
		display: none;
	}
}

@media (max-width: 576px) {
	.btn-sm i {
		font-size: 0.9rem;
	}

	.me-3 {
		flex-direction: column;
		gap: 0.2rem !important;
	}
}

@media (max-width: 576px) {
	.d-flex {
		flex-wrap: nowrap !important;
		justify-content: space-between;
	}

	.d-flex .btn {
		flex: 1 1 auto;
	}
}

</style>

<div class="container-sm container-bg text-center mt-4">
    <div class="alert alert-secondary d-none" id="toolbar">
        <div class="d-flex justify-content-between">
            <div>
                <button class="btn btn-outline-primary" id="selectAllBtn">全选</button>
                <span id="selectedInfo"></span>
            </div>
            <button class="btn btn-danger" id="batchDeleteBtn">批量删除选中文件</button>
        </div>
    </div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 style="position: relative; top: -8px;">Spectra 配置管理</h2>
        <p id="status" class="mb-0">当前模式: <?= ($mode == "dark") ? "暗色模式" : "亮色模式" ?></p>
        <button id="toggleButton" onclick="toggleConfig()" class="btn btn-primary">切换模式</button>
    </div>
        <h2 class="mb-0">文件列表</h2>
        <div class="d-flex align-items-center">
            <?php
            $mountPoint = '/overlay';
            $freeSpace = @disk_free_space($mountPoint);
            $totalSpace = @disk_total_space($mountPoint);
            $usedSpace = $totalSpace - $freeSpace;
            
            function formatSize($bytes) {
                $units = ['B', 'KB', 'MB', 'GB'];
                $index = 0;
                while ($bytes >= 1024 && $index < 3) {
                    $bytes /= 1024;
                    $index++;
                }
                return round($bytes, 2) . ' ' . $units[$index];
            }
            ?>
            
            <div class="me-3 d-flex gap-2 mt-4" 
                 data-bs-toggle="tooltip" 
                 title="挂载点：<?= $mountPoint ?>｜已用空间：<?= formatSize($usedSpace) ?>">
                <span class="btn btn-primary btn-sm"><i class="bi bi-hdd"></i> 总共：<?= $totalSpace ? formatSize($totalSpace) : 'N/A' ?></span>
                <span class="btn btn-success btn-sm"><i class="bi bi-hdd"></i> 剩余：<?= $freeSpace ? formatSize($freeSpace) : 'N/A' ?></span>
            </div>
            <?php if ($downloadUrl): ?><button class="btn btn-info btn-sm mt-4 update-theme-btn" data-url="<?= htmlspecialchars($downloadUrl) ?>" title="最新版本：<?= htmlspecialchars($latestVersion) ?>"><i class="bi bi-cloud-download"></i> <span class="btn-label">更新主题</span></button><?php endif; ?>
            <button class="btn btn-warning btn-sm ms-2 mt-4" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-upload"></i> <span class="btn-label">批量上传</span></button>
            <button class="btn btn-primary btn-sm ms-2 mt-4" id="openPlayerBtn" data-bs-toggle="modal" data-bs-target="#playerModal"><i class="bi bi-play-btn"></i> <span class="btn-label">播放器</span></button>
            <button class="btn btn-danger btn-sm ms-2 mt-4" id="clearBackgroundBtn"><i class="bi bi-trash"></i> <span class="btn-label">清除背景</span></button>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="d-flex align-items-center mt-4 mb-3">
            <input type="checkbox" id="selectAll" class="me-2">
            <label for="selectAll" style="color: white">全选</label>
        </div>

        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4">
            <?php foreach ($files as $file): 
                $path = $upload_dir . '/' . $file;
                $size = filesize($path);
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg','jpeg','png','gif']);
                $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mkv']);
                $isAudio = in_array($ext, ['mp3', 'wav', 'flac']);
                $isMedia = $isImage || $isVideo || $isAudio;
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="position-relative">
                        <?php if ($isMedia): ?>
                        <div class="preview-container">
                            <div class="file-type-indicator">
                                <?php if ($isImage): ?>
                                    <i class="bi bi-image-fill text-white"></i>
                                    <span class="text-white small">图片</span>
                                <?php elseif ($isVideo): ?>
                                    <i class="bi bi-play-circle-fill text-white"></i>
                                    <span class="text-white small">视频</span>
                                <?php elseif ($isAudio): ?>
                                    <i class="bi bi-music-note-beamed text-white"></i>
                                    <span class="text-white small">音频</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($isImage): ?>
                                <img src="<?= htmlspecialchars($file) ?>" 
                                     class="card-img-top preview-img img-fluid"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#previewModal"
                                     data-src="<?= htmlspecialchars($file) ?>">
                            <?php elseif ($isVideo): ?>
                                <video class="card-img-top preview-video"
                                       data-bs-toggle="modal"
                                       data-bs-target="#previewModal"
                                       data-src="<?= htmlspecialchars($file) ?>">
                                    <source src="<?= htmlspecialchars($file) ?>" type="video/mp4">
                                    <source src="<?= htmlspecialchars($file) ?>" type="video/webm">
                                    <source src="<?= htmlspecialchars($file) ?>" type="video/ogg">
                                </video>
                            <?php elseif ($isAudio): ?>
                                <div class="preview-audio-container" 
                                     data-bs-toggle="modal" 
                                     data-bs-target="#previewModal"
                                     data-src="<?= htmlspecialchars($file) ?>"
                                     data-type="audio">
                                    <div class="audio-placeholder">
                                        <i class="bi bi-file-music fs-1 text-muted"></i>
                                        <div class="hover-tips">点击激活悬停播放</div>
                                    </div>
                                        <audio class="hover-audio" preload="none"></audio>
                                </div>
                            <?php endif; ?>

                            <div class="file-info-overlay">
                                <p class="mb-1 small">名称：<?= htmlspecialchars($file) ?></p>
                                <p class="mb-1 small">大小：<?= round($size/(1024*1024),2) ?> MB</p>
                                <p class="mb-0 small text-uppercase">类型：<?= $ext ?></p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="card-body text-center">
                            <div class="file-type-indicator">
                                <i class="bi bi-file-earmark-text-fill text-white"></i>
                                <span class="text-white small">文档</span>
                            </div>
                            <i class="bi bi-file-earmark fs-1 text-muted"></i>
                            <p class="small mb-0"><?= htmlspecialchars($file) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body pt-2 mt-2">
                        <div class="d-flex flex-nowrap align-items-center justify-content-between gap-2">
                            <input type="checkbox" 
                                   class="fileCheckbox flex-shrink-0" 
                                   value="<?= htmlspecialchars($file) ?>"
                                   data-size="<?= $size ?>">
                            
                            <div class="d-flex flex-wrap gap-1 flex-grow-1" style="min-width: 0;">
                                <button class="btn btn-danger btn-sm" onclick="if(confirm('确定删除？')) window.location='?delete=<?= urlencode($file) ?>'"><i class="bi bi-trash"></i></button>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#renameModal-<?= md5($file) ?>"><i class="bi bi-pencil"></i></button>
                                
                                <?php if ($isMedia): ?>
                                <button class="btn btn-info btn-sm set-bg-btn" data-src="<?= htmlspecialchars($file) ?>" data-type="<?= $isVideo ? 'video' : ($isAudio ? 'audio' : 'image') ?>"><i class="bi bi-image"></i></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">预览</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="previewImage" src="" class="img-fluid d-none">
                    <audio id="previewAudio" controls class="d-none w-100"></audio>
                    <video id="previewVideo" controls class="d-none" style="width: 100%; height: auto;">
                        <source id="previewVideoSource" src="" type="video/mp4">
                    </video>
                </div>
            </div>
        </div>
    </div>

        <form id="batchDeleteForm" method="post" style="display: none;">
            <input type="hidden" name="batch_delete" value="1">
        </form>
    </div>

    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">选择文件进行批量上传</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" method="post" enctype="multipart/form-data">
                        <div class="drop-zone border rounded p-5 text-center mb-3">
                            <input type="file" name="upload_file[]" id="upload_file" multiple 
                                   style="opacity: 0; position: absolute; z-index: -1">
                            <div class="upload-area">
                                <i class="bi bi-cloud-upload-fill text-primary mb-3" style="font-size: 4rem;"></i>
                                <div class="fs-5 mb-2">拖放文件到这里</div>
                                <div class="text-muted upload-or mb-3">或</div>
                                <button type="button" class="btn btn-primary btn-lg" id="customUploadButton">
                                    <i class="bi bi-folder2-open me-2"></i>选择文件
                                </button>
                                <div class="file-list mt-3"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" id="updatePhpConfig">解锁 PHP 上传限制</button>
                    <button class="btn btn-primary" onclick="$('#uploadForm').submit()">上传</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($files as $file): ?>
    <div class="modal fade" id="renameModal-<?= md5($file) ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" action="">
                    <input type="hidden" name="old_name" value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">重命名 <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>新文件名</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="new_name"
                                   value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>"
                                   title="文件名不能包含以下字符：\/:*?&quot;<>|">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary" name="rename">确认</button>
                    </div>
                </form>
            </div>
       </div>
    </div>
    <?php endforeach; ?>

    <div class="modal fade" id="playerModal" tabindex="-1" aria-labelledby="playerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="playerModalLabel">媒体播放器</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column" style="height: 65vh;"> 
                    <div class="row g-4 flex-grow-1 h-100">
                        <div class="col-md-8 d-flex flex-column h-100">
                            <div class="ratio ratio-16x9 bg-dark rounded flex-grow-1 position-relative">
                                <video id="mainPlayer" controls class="w-100 h-100 d-none"></video>
                                <img id="imagePlayer" class="w-100 h-100 d-none object-fit-contain">
                            </div>
                        </div>
                    
                        <div class="col-md-4 d-flex flex-column h-100">
                            <h6 class="mb-3">播放列表</h6>
                            <div class="list-group flex-grow-1 overflow-auto" id="playlistContainer">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" id="clearPlaylist"><i class="bi bi-trash"></i> 清除列表</button>
                    <button class="btn btn-sm btn-primary" id="togglePlaylist"><i class="bi bi-list-ul"></i> 隐藏列表</button>
                    <button class="btn btn-sm btn-success" id="toggleFullscreen"><i class="bi bi-arrows-fullscreen"></i> 全屏</button>
                    <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> 关闭</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateConfirmModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">主题下载</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">注意：下载过程可能需要1-3分钟，请勿关闭电源！</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <a id="confirmUpdateLink" href="#" class="btn btn-danger">下载到本地</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.querySelector('.drop-zone');
        const fileInput = document.getElementById('upload_file');
        const customButton = document.getElementById('customUploadButton');
        const fileList = document.querySelector('.file-list');

        customButton.addEventListener('click', () => fileInput.click());

        function updateFileList() {
            fileList.innerHTML = "";
            Array.from(fileInput.files).forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-list-item';
                fileItem.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-earmark"></i> ${file.name}
                    </div>
                    <i class="bi bi-x remove-file"></i>
                `;

                fileItem.querySelector('.remove-file').addEventListener('click', () => {
                    fileItem.remove();
                    removeFileFromInput(file);
                });

                fileList.appendChild(fileItem);
            });
        }

        function removeFileFromInput(fileToRemove) {
            const dataTransfer = new DataTransfer();
            Array.from(fileInput.files).forEach(file => {
                if (file !== fileToRemove) {
                    dataTransfer.items.add(file);
                }
            });
            fileInput.files = dataTransfer.files;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        fileInput.addEventListener('change', updateFileList);

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            e.preventDefault();
            dropZone.classList.add('dragover');
        }

        function unhighlight(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        }

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length) {
                const dataTransfer = new DataTransfer();
            
                if (fileInput.files) {
                    Array.from(fileInput.files).forEach(file => 
                        dataTransfer.items.add(file)
                    );
                }
            
                Array.from(files).forEach(file => 
                    dataTransfer.items.add(file)
                );
            
                fileInput.files = dataTransfer.files;
                updateFileList();
            }
        });
    });
    </script>

    <script>
        $(document).ready(function() {
            $('#selectAll').change(function() {
                $('.fileCheckbox').prop('checked', this.checked);
                updateSelectionInfo();
            });

            $('.fileCheckbox').change(function() {
                $('#selectAll').prop('checked', $('.fileCheckbox:checked').length === $('.fileCheckbox').length);
                updateSelectionInfo();
            });

            $('#selectAllBtn').click(function() {
                const allChecked = $('.fileCheckbox:checked').length === $('.fileCheckbox').length;
                $('.fileCheckbox').prop('checked', !allChecked).trigger('change');
            });

            $('#batchDeleteBtn').click(function() {
                const files = $('.fileCheckbox:checked').map(function() { return $(this).val(); }).get();
                if (files.length === 0) { alert('请先选择要删除的文件！'); return; }
                if (confirm(`确定要删除选中的 ${files.length} 个文件吗？`)) {
                    const batchDeleteForm = $('#batchDeleteForm');
                    batchDeleteForm.empty();
                    batchDeleteForm.append('<input type="hidden" name="batch_delete" value="1">');
                    files.forEach(file => {
                        batchDeleteForm.append(`<input type="hidden" name="filenames[]" value="${file}">`);
                    });
                    batchDeleteForm.submit();
                }
            });

            function updateSelectionInfo() {
                const checked = $('.fileCheckbox:checked');
                const count = checked.length;
                const totalSize = checked.toArray().reduce((sum, el) => sum + parseInt($(el).data('size')), 0);
                if (count > 0) {
                    $('#toolbar').removeClass('d-none');
                    $('#selectedInfo').html(`已选择 ${count} 个文件，合计 ${(totalSize / (1024 * 1024)).toFixed(2)} MB`);
                } else {
                    $('#toolbar').addClass('d-none');
                }
            }

            $('.preview-img').click(function() {
                const src = $(this).data('src');
                $('#previewImage').attr('src', src).removeClass('d-none');
                $('#previewVideo').addClass('d-none');
            });

            $('.preview-video').click(function() {
                const src = $(this).data('src');
                $('#previewVideoSource').attr('src', src);
                $('#previewVideo')[0].load();
                $('#previewVideo').removeClass('d-none');
                $('#previewImage').addClass('d-none');
            });

            $('#previewModal').on('hidden.bs.modal', function() {
                $('#previewVideo')[0].pause();
            });

            $('.set-bg-btn').click(function() {
                const src = $(this).data('src');
                const type = $(this).data('type');
                setBackground(src, type);
            });

            $('#clearBackgroundBtn').click(function() {
                clearExistingBackground();
                localStorage.removeItem('phpBackgroundSrc');
                localStorage.removeItem('phpBackgroundType');
                localStorage.removeItem('backgroundSet');
                location.reload();
            });

            function setBackground(src, type) {
                if (type === 'image') {
                    setImageBackground(src);
                } else if (type === 'video') {
                    setVideoBackground(src);
                }
            }
        });

        $('#batchDeleteForm').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.post('', formData, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('批量删除操作失败');
                }
            }, 'json');
        });

        function setImageBackground(src) {
            clearExistingBackground();
            document.body.style.background = `url('/luci-static/spectra/bgm/${src}') no-repeat center center fixed`;
            document.body.style.backgroundSize = 'cover';
            localStorage.setItem('phpBackgroundSrc', src);
            localStorage.setItem('phpBackgroundType', 'image');
            checkAndReload();
        }

        function setVideoBackground(src, isPHP = false) {
            clearExistingBackground();
            let existingVideoTag = document.getElementById("background-video");
            if (existingVideoTag) {
                existingVideoTag.src = `/luci-static/spectra/bgm/${src}`;
            } else {
                videoTag = document.createElement("video");
                videoTag.className = "video-background";
                videoTag.id = "background-video";
                videoTag.autoplay = true;
                videoTag.loop = true;
                videoTag.muted = localStorage.getItem('videoMuted') === 'true';
                videoTag.playsInline = true;
                videoTag.innerHTML = `
                    <source src="/luci-static/spectra/bgm/${src}" type="video/mp4">
                    Your browser does not support the video tag.
                `;
                document.body.prepend(videoTag);

                let styleTag = document.querySelector("#video-style");
                if (!styleTag) {
                    styleTag = document.createElement("style");
                    styleTag.id = "video-style";
                    document.head.appendChild(styleTag);
                }
                styleTag.innerHTML = `
                    body {
                        background: transparent !important;
                        margin: 0;
                        padding: 0;
                        height: 100vh;
                        overflow: hidden;
                    }
                    .video-background {
                        position: fixed;
                        top: 50%;
                        left: 50%;
                        width: auto;
                        height: auto;
                        min-width: 100%;
                        min-height: 100%;
                        transform: translate(-50%, -50%);
                        object-fit: cover;
                        z-index: -1;
                    }
                    .video-background + .wrapper span {
                        display: none !important;
                    }
                `;
            }
            localStorage.setItem('phpBackgroundSrc', src);
            localStorage.setItem('phpBackgroundType', 'video');
            if (isPHP) {
                document.querySelector('.sound-toggle div').textContent = '🔊';
                videoTag.muted = false;
            }
            checkAndReload();
        }

        function clearExistingBackground() {
            document.body.style.background = ''; 
            let existingVideoTag = document.getElementById("background-video");
            if (existingVideoTag) {
                existingVideoTag.remove(); 
            }
            let styleTag = document.querySelector("#video-style");
            if (styleTag) {
                styleTag.remove(); 
            }
        }

        function checkAndReload() {
            if (!localStorage.getItem('backgroundSet')) {
                localStorage.setItem('backgroundSet', 'true');
                location.reload();
            }
        }

        if (phpBackgroundSrc && phpBackgroundType) {
            if (phpBackgroundType === 'image') {
                setImageBackground(phpBackgroundSrc);
            } else if (phpBackgroundType === 'video') {
                setVideoBackground(phpBackgroundSrc, true);
            }
        }

        document.querySelectorAll('.preview-video').forEach(video => {
            video.addEventListener('mouseenter', () => {
                video.play().catch(() => {})
            })
            video.addEventListener('mouseleave', () => {
                video.pause()
                video.currentTime = 0
            })
        });

        $(document).ready(function () {
            $(".set-bg-btn").click(function () {
                const bgSrc = $(this).data("src");
                const bgType = $(this).data("type");
                setTimeout(function () {
                    location.reload();
                }, 1000);
            });
        });
    </script>

    <script>
        function toggleConfig() {
            fetch("", { method: "POST" }) 
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateButton(data.mode);
                    } else {
                        document.getElementById("status").innerText = "更新失败: " + data.error;
                    }
                });
        }

        function updateButton(value) {
            let btn = document.getElementById("toggleButton");
            let status = document.getElementById("status");

            if (value == "dark") {
                btn.innerHTML = '<i class="bi bi-sun"></i> 切换到亮色模式'; 
                btn.className = "light";
                status.innerText = "当前模式: 暗色模式";
            } else {
                btn.innerHTML = '<i class="bi bi-moon"></i> 切换到暗色模式';
                btn.className = "dark";
                status.innerText = "当前模式: 亮色模式";
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            updateButton("<?= $mode ?>"); 
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Tooltip(document.querySelector('[data-bs-toggle="tooltip"]'))

            document.querySelectorAll('.update-theme-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const downloadUrl = this.dataset.url
                    const modal = new bootstrap.Modal('#updateConfirmModal')
                    document.getElementById('confirmUpdateLink').href = downloadUrl
                    modal.show()
                })
            })
        })
    </script> 

    <script>
        document.getElementById("updatePhpConfig").addEventListener("click", function() {
            if (confirm("您确定要更新 PHP 配置吗？")) {
                fetch("update_php_config.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" }
                })
                .then(response => response.json())
                .then(data => alert(data.message))
                .catch(error => alert("请求失败: " + error.message));
            }
        });
    </script>

    <script>
    let mediaInteraction = false;

    document.addEventListener('DOMContentLoaded', function() {
        const previewModal = document.getElementById('previewModal');
        const modalAudio = document.getElementById('previewAudio');

        previewModal.addEventListener('show.bs.modal', function(event) {
            const trigger = event.relatedTarget;
            const src = trigger.dataset.src;
            const mediaType = trigger.dataset.type || '';

            if (mediaType === 'audio') {
                modalAudio.innerHTML = ''; 
                const source = document.createElement('source');
                source.src = src;
                source.type = `audio/${src.split('.').pop()}`;
                modalAudio.appendChild(source);
                modalAudio.load();
                modalAudio.classList.remove('d-none'); 
            } else {
                modalAudio.classList.add('d-none'); 
            }
        });

        previewModal.addEventListener('hidden.bs.modal', () => {
            modalAudio.pause(); 
        });

        document.querySelectorAll('.preview-audio-container').forEach(container => {
            const audio = new Audio();
            audio.src = container.dataset.src;
            audio.volume = 0.5;
            const hoverTips = container.querySelector('.hover-tips'); 

            container.addEventListener('mouseenter', () => {
                if (!mediaInteraction) {
                    container.classList.add('needs-interact');
                    return;
                }
                audio.play().catch(() => {
                    container.classList.add('needs-interact');
                });
            });

            container.addEventListener('mouseleave', () => {
                audio.pause();
                audio.currentTime = 0;
            });

            container.addEventListener('click', (e) => {
                if (!mediaInteraction) {
                    e.preventDefault();
                    mediaInteraction = true;
                    document.body.classList.add('media-active');
                    container.classList.remove('needs-interact');
                    if (hoverTips) hoverTips.style.display = 'none';
                }
            });
        });

        document.addEventListener('click', () => {
            if (!mediaInteraction) {
                mediaInteraction = true;
                document.body.classList.add('media-active');
                document.querySelectorAll('.hover-tips').forEach(tip => tip.style.display = 'none');
            }
        });
    });
    </script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const player = document.getElementById('mainPlayer');
        const imagePlayer = document.getElementById('imagePlayer');
        const playlistContainer = document.getElementById('playlistContainer');
        let playlist = JSON.parse(localStorage.getItem('mediaPlaylist') || '[]');
        let currentIndex = 0;
        let imageTimer = null;

        function savePlaylist() {
            localStorage.setItem('mediaPlaylist', JSON.stringify(playlist));
        }

        function renderPlaylist() {
            playlistContainer.innerHTML = '';
            playlist.forEach((file, index) => {
                const item = document.createElement('a');
                item.className = `list-group-item list-group-item-action d-flex justify-content-between 
                                align-items-center ${index === currentIndex ? 'active' : ''}`;
                item.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary">${index + 1}</span>
                        <span class="text-truncate">${file.name}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted">${file.type.toUpperCase()}</small>
                        <button class="btn btn-sm btn-danger p-0 delete-item"><i class="bi bi-x"></i></button>
                    </div>
                `;
                item.onclick = () => playMedia(index);
                item.querySelector('.delete-item').onclick = (e) => {
                    e.stopPropagation();
                    playlist.splice(index, 1);
                    if(currentIndex >= index) currentIndex--;
                    savePlaylist();
                    renderPlaylist();
                    if(index === currentIndex) playNext();
                };
                playlistContainer.appendChild(item);
            });

            setTimeout(() => {
                const activeItem = playlistContainer.querySelector('.list-group-item.active');
                if (activeItem) {
                    activeItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }, 50);
        }

        function playMedia(index) {
            currentIndex = index;
            const media = playlist[index];
        
            player.classList.add('d-none');
            imagePlayer.classList.add('d-none');
            clearTimeout(imageTimer);

            if(media.type === 'image') {
                imagePlayer.src = media.url;
                imagePlayer.classList.remove('d-none');
                imageTimer = setTimeout(playNext, 5000);
            } else {
                player.src = media.url;
                player.classList.remove('d-none');
                player.load();
                player.play();
            }

            renderPlaylist();
        }

        function findNextValidIndex(startIndex) {
            let newIndex = startIndex;
             do {
                newIndex = (newIndex + 1) % playlist.length;
                if(newIndex === startIndex) return -1; 
            } while(playlist[newIndex].type === 'image')
            return newIndex;
        }

        function playNext() {
            if(playlist.length === 0) return;
        
            const nextIndex = findNextValidIndex(currentIndex);
            if(nextIndex === -1) return; 
        
            playMedia(nextIndex);
        }

        document.getElementById('openPlayerBtn').addEventListener('click', () => {
            const selectedFiles = Array.from(document.querySelectorAll('.fileCheckbox:checked'))
                .map(checkbox => {
                    const card = checkbox.closest('.card');
                    return {
                        name: checkbox.value,
                        url: checkbox.value,
                        type: card.querySelector('video') ? 'video' :
                              card.querySelector('audio') ? 'audio' :
                              card.querySelector('img') ? 'image' : 'file'
                    };
                });

            const validFiles = selectedFiles.filter(f => ['video','audio','image'].includes(f.type));
            validFiles.forEach(file => {
                if (!playlist.some(existing => existing.url === file.url)) {
                    playlist.push(file);
                }
            });

            const firstPlayable = playlist.findIndex(f => f.type !== 'image');
            if(firstPlayable !== -1) {
                currentIndex = firstPlayable - 1; 
                playNext();
            }

            savePlaylist();
            renderPlaylist();
        });

        document.getElementById('clearPlaylist').addEventListener('click', () => {
            playlist = [];
            currentIndex = 0;
            player.src = '';
            imagePlayer.src = '';
            savePlaylist();
            renderPlaylist();
        });

        player.addEventListener('ended', playNext);
        renderPlaylist();
    });
</script> 
<style>
.modal-content:fullscreen .row {
    --playlist-width: 350px; 
}

.modal-content:fullscreen .col-md-8 {
    flex: 1 1 calc(100% - var(--playlist-width)) !important;
    max-width: calc(100% - var(--playlist-width)) !important;
}

.modal-content:fullscreen .col-md-4 {
    flex: 0 0 var(--playlist-width) !important;
    max-width: var(--playlist-width) !important;
    transition: all 0.3s; 
}

.modal-content:fullscreen #playlistContainer {
    font-size: 0.9em; 
    padding: 0 0.5rem; 
}

.col-md-4 {
    transition: all 0.3s ease-in-out;
}

.modal-content:fullscreen .col-md-8.col-md-12 {
    flex: 0 0 100% !important;
    max-width: 100% !important;
}

.modal-content:fullscreen {
    width: 100vw !important;
    height: 100vh !important;
    border-radius: 0 !important;
    margin: 0 !important;
}

@media (max-width: 768px) {
    #togglePlaylist {
        display: none; 
    }
}

@media (max-width: 767px) {
    #playerModal .col-md-4 h6 {
        text-align: left; 
        margin-bottom: 10px; 
    }
}
</style>

<script>
const playlistToggleBtn = document.getElementById('togglePlaylist');
const playlistColumn = document.querySelector('.col-md-4');
let isPlaylistVisible = true;

playlistToggleBtn.addEventListener('click', () => {
    isPlaylistVisible = !isPlaylistVisible;
    playlistColumn.classList.toggle('d-none');
    
    const icon = playlistToggleBtn.querySelector('i');
    icon.className = isPlaylistVisible ? 'bi bi-list-ul' : 'bi bi-layout-sidebar';
    playlistToggleBtn.innerHTML = icon.outerHTML + ' ' + 
        (isPlaylistVisible ? '隐藏列表' : '显示列表');
    
    const mainColumn = document.querySelector('.col-md-8');
    mainColumn.classList.toggle('col-md-12');
});
</script>


<script>
const fullscreenBtn = document.getElementById('toggleFullscreen');
const modalContent = document.querySelector('#playerModal .modal-content');

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        modalContent.requestFullscreen().catch(console.error);
    } else {
        document.exitFullscreen();
    }
}

document.addEventListener('fullscreenchange', () => {
    const icon = fullscreenBtn.querySelector('i');
    icon.className = document.fullscreenElement ? 
        'bi bi-fullscreen-exit' : 
        'bi bi-arrows-fullscreen';
    fullscreenBtn.innerHTML = icon.outerHTML + ' ' + 
        (document.fullscreenElement ? '退出全屏' : '全屏');
});

fullscreenBtn.addEventListener('click', toggleFullscreen);

let startX = null;
const playlist = document.querySelector('#playlistContainer');

playlist.addEventListener('mousedown', (e) => {
    if (document.fullscreenElement) {
        startX = e.clientX;
        document.addEventListener('mousemove', handleDrag);
        document.addEventListener('mouseup', stopDrag);
    }
});

function handleDrag(e) {
    if (!startX) return;
    const newWidth = Math.min(Math.max(250, playlist.offsetWidth + (startX - e.clientX)), 400);
    document.documentElement.style.setProperty('--playlist-width', `${newWidth}px`);
    startX = e.clientX;
}

function stopDrag() {
    startX = null;
    document.removeEventListener('mousemove', handleDrag);
    document.removeEventListener('mouseup', stopDrag);
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  interact('.modal-dialog.draggable').draggable({
    allowFrom: '.modal-header',
    modifiers: [
      interact.modifiers.restrictRect({
        restriction: 'parent', 
        endOnly: true
      })
    ],
    listeners: {
      start(event) {
        event.target.style.transition = 'none';
        event.target.classList.add('dragging');
      },
      move(event) {
        const target = event.target;
        const x = (parseFloat(target.dataset.x) || 0) + event.dx;
        const y = (parseFloat(target.dataset.y) || 0) + event.dy;

        target.style.transform = `translate(${x}px, ${y}px)`;
        target.dataset.x = x;
        target.dataset.y = y;
      },
      end(event) {
        event.target.style.transition = '';
        event.target.classList.remove('dragging');
      }
    }
  });

  document.querySelectorAll('.modal').forEach(modal => {
    const dialog = modal.querySelector('.modal-dialog');
    dialog.classList.add('draggable');

    modal.addEventListener('show.bs.modal', () => {
      dialog.style.transform = ''; 
      dialog.dataset.x = 0;
      dialog.dataset.y = 0;
    });
  });
});

</script>
