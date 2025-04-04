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

if (isset($_GET['download'])) {
    $file = $_GET['download'];
    $filePath = $upload_dir . '/' . $file;

    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        $error = "文件未找到：" . htmlspecialchars($file);
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
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    return !in_array(strtolower($ext), ['php', 'txt']); 
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
:root {
	--base-hue: 260;
	--base-chroma: 0.03;
	--danger-base: 15;
	
	--bg-body: oklch(20% var(--base-chroma) var(--base-hue) / 50%);
	--bg-container: oklch(30% var(--base-chroma) var(--base-hue));
	--text-primary: oklch(95% 0 0); 
	--accent-color: oklch(70% 0.2 calc(var(--base-hue) + 0));
	--card-bg: oklch(25% var(--base-chroma) var(--base-hue));
	--header-bg: oklch(35% var(--base-chroma) var(--base-hue));
	--border-color: oklch(40% var(--base-chroma) var(--base-hue));
	--btn-primary-bg: oklch(50% 0.15 var(--base-hue));
        --btn-success-bg: oklch(50% 0.2 240); 
	--nav-btn-color: oklch(95% 0 0 / 80%);
	--hover-tips-color: oklch(95% 0 0 / 80%);
	--playlist-text: oklch(95% 0 0);
	--text-secondary: oklch(75% 0 0);
	--item-border: 3px solid oklch(40% var(--base-chroma) var(--base-hue));
	--item-hover-bg: color-mix(in oklch, var(--btn-primary-bg), white 10%);
	--item-hover-shadow: 0 2px 8px oklch(var(--base-hue) 0.2 0.2 / 0.3);
	--drag-over-bg: oklch(30% var(--base-chroma) var(--base-hue) / 0.2);
	--drag-over-shadow: 0 0 20px oklch(var(--base-hue) 0.15 0 / 0.25);
	--file-list-bg: oklch(25% var(--base-chroma) var(--base-hue) / 0.3);
	--file-list-border: oklch(35% var(--base-chroma) var(--base-hue) / 0.4);
	--danger-color: oklch(65% 0.25 var(--danger-base));
	--danger-hover: oklch(75% 0.3 var(--danger-base));
	--btn-info-bg: oklch(50% 0.2 220);  
	--btn-info-hover: color-mix(in oklch, var(--btn-info-bg), white 10%);
	--btn-warning-bg: oklch(50% 0.25 50);  
	--btn-warning-hover: color-mix(in oklch, var(--btn-warning-bg), white 10%);
}

[data-theme="light"] {
	--base-hue: 200;
	--base-chroma: 0.01;
	
	--bg-body: oklch(95% var(--base-chroma) var(--base-hue) / 90%);
	--bg-container: oklch(99% var(--base-chroma) var(--base-hue));
	--text-primary: oklch(25% var(--base-chroma) var(--base-hue));
	--accent-color: oklch(60% 0.2 calc(var(--base-hue) + 60));
	--card-bg: oklch(96% var(--base-chroma) var(--base-hue));
	--header-bg: oklch(88% var(--base-chroma) var(--base-hue));
	--border-color: oklch(85% var(--base-chroma) var(--base-hue));
	--btn-primary-bg: oklch(45% 0.15 var(--base-hue));
        --btn-success-bg: oklch(70% 0.2 240); 
	--nav-btn-color: oklch(70% 0.2 calc(var(--base-hue) + 60));
	--playlist-text: oklch(25% 0 0);
	--text-secondary: oklch(40% 0 0);
	--item-border: 3px solid oklch(85% var(--base-chroma) var(--base-hue));
	--item-hover-bg: color-mix(in oklch, var(--accent-color), white 20%);
	--item-hover-shadow: 0 2px 12px oklch(var(--base-hue) 0.15 0.5 / 0.2);
	--drag-over-bg: oklch(90% var(--base-chroma) var(--base-hue) / 0.3);
	--drag-over-shadow: 0 0 25px oklch(var(--base-hue) 0.1 0 / 0.15);
	--file-list-bg: oklch(95% var(--base-chroma) var(--base-hue) / 0.4);
	--file-list-border: oklch(85% var(--base-chroma) var(--base-hue) / 0.6);
	--danger-color: oklch(50% 0.3 var(--danger-base));
	--danger-hover: oklch(40% 0.35 var(--danger-base));
	--btn-info-bg: oklch(65% 0.18 220);
	--btn-info-hover: color-mix(in oklch, var(--btn-info-bg), black 10%);
	--btn-warning-bg: oklch(60% 0.3 50);
	--btn-warning-hover: color-mix(in oklch, var(--btn-warning-bg), black 15%);

}

body {
	background: var(--bg-body);
	color: var(--text-primary);
	-webkit-backdrop-filter: blur(10px);
	transition: all 0.3s ease;
}

.container-bg,
.card,
.modal-content,
.table {
	--bg-l: oklch(30% 0 0); 
	color: oklch(calc(100% - var(--bg-l)) 0 0);
}

.container-bg {
	padding: 20px;
	border-radius: 10px;
	background: var(--bg-container);
	box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.time-display {
	font-family: system-ui, -apple-system, "Segoe UI", Roboto !important;
	font-size: 1.2rem !important;
	color: var(--text-primary);
	padding: 6px 12px !important;
	display: flex !important;
	align-items: center !important;
	flex-wrap: wrap !important;
	gap: 8px !important;
}

.week-display {
	color: var(--text-secondary);
	font-size: 0.95em !important;
	margin-left: 6px !important;
}

.lunar-text {
	color: var(--text-secondary);
	font-size: 0.9em !important;
}

#timeDisplay {
	font-weight: 500 !important;
	color: var(--accent-color);
	margin-left: auto !important;
}

.modern-time {
	font-weight: 500 !important;
}

.ancient-time {
	font-size: 0.9em !important;
	margin-left: 4px !important;
	letter-spacing: 1px !important;
	font-family: "Noto Serif SC", serif !important;
}

.card {
	background: var(--card-bg);
	border: 1px solid var(--border-color);
}

.card-header {
	background: var(--header-bg) !important;
	border-bottom: 1px solid var(--border-color);
}

.table {
	--bs-table-bg: var(--card-bg);
	--bs-table-color: var(--text-primary);
	--bs-table-border-color: var(--border-color);
	--bs-table-striped-bg: rgba(0, 0, 0, 0.05);
}

.btn {
	border-radius: 8px;
	font-weight: bold;
	transition: transform 0.2s;
}

.btn:hover {
	transform: scale(1.1);
}

.btn-primary {
	background: var(--btn-primary-bg);
	border: 1px solid var(--border-color);
}

.btn-info {
	background-color: var(--btn-info-bg) !important;
	color: white !important;
	border: none !important; 

	&:hover {
		background-color: var(--btn-info-hover) !important;
		color: white !important;
	}
}

.btn-warning {
	background-color: var(--btn-warning-bg) !important;
	color: white !important;
	border: none !important; 

	&:hover {
		background-color: var(--btn-warning-hover) !important;
		color: white !important;
	}
}

#status {
	font-size: 22px;
	color: var(--accent-color) !important;
}

h5,
h2 {
	color: var(--accent-color) !important;
}

.img-thumbnail {
	background: var(--bg-container);
	border: 1px solid var(--border-color);
}

#toggleButton {
        background-color: var(--btn-success-bg);
        color: var(--text-primary);
}

.modal-content {
	background: var(--bg-container);
	border: 1px solid var(--border-color);
}

.modal-header {
	background: var(--header-bg);
	border-bottom: 1px solid var(--border-color);
}

.modal-title {
	color: var(--accent-color) !important;
}

.modal-body {
	color: var(--text-primary);
}

label {
	color: var(--text-primary) !important;
}

label[for="selectAll"] {
	margin-left: 8px;
	vertical-align: middle;
}

.preview-container {
	position: relative;
	overflow: hidden;
	cursor: pointer;
	min-height: 300px;
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
        object-fit: cover; 
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
        max-height: 100vh;
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
	background-color: var(--card-bg);
	color: var(--playlist-text);
	border-left: var(--item-border);
}

#playlistContainer .list-group-item:hover {
	background-color: var(--item-hover-bg) !important;
	box-shadow: var(--item-hover-shadow);
	transform: scale(1.02);
	--playlist-text: oklch(100% 0 0); 
	color: var(--playlist-text);
}

[data-theme="light"] #playlistContainer .list-group-item:hover {
	--playlist-text: oklch(20% 0 0); 
}

#playlistContainer .badge {
	width: 24px;
	text-align: center;
	font-weight: normal;
	background-color: var(--btn-primary-bg);
	color: var(--text-primary);
}

#playlistContainer .delete-item {
	opacity: 0.6;
	transition: opacity 0.3s;
	color: var(--text-primary);
}

#playlistContainer .delete-item:hover {
	opacity: 1;
}

#playlistContainer {
	overflow-x: hidden;
	overflow-y: auto;
	background-color: var(--bg-container);
}

#playlistContainer .text-truncate {
	display: inline-block;
	width: 100%;
	white-space: normal;
	word-wrap: break-word;
	word-break: break-word;
	font-size: 1.1em;
	line-height: 1.4;
	color: var(--text-primary);
}

#playlistContainer .list-group-item {
	padding: 1rem 1.5rem;
	margin-bottom: 3px;
	border-radius: 6px;
	transition: all 0.3s ease;
	background-color: var(--card-bg);
	color: var(--text-primary);
}

#playlistContainer .list-group-item:nth-child(odd) {
	background-color: var(--card-bg);
	border-left: 3px solid var(--border-color);
	color: var(--text-primary);
}

#playlistContainer .list-group-item:nth-child(even) {
	background-color: var(--card-bg);
	border-left: 3px solid var(--border-color);
	color: var(--text-primary);
}

#playlistContainer .list-group-item.active {
	background: linear-gradient(145deg, var(--accent-color), var(--accent-color)) !important;
	border-color: var(--accent-color);
	transform: scale(1.02);
	box-shadow: 0 4px 16px color-mix(in oklch, var(--accent-color), black 15%);
	z-index: 2;
	color: var(--text-primary);
}

#playlistContainer .list-group-item:hover {
	background-color: var(--btn-primary-bg);
	transform: translateX(5px);
	cursor: pointer;
}

.text-muted {
	color: var(--text-secondary) !important;
	font-size: 0.9em;
	letter-spacing: 0.5px;
	opacity: 0.7;
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
	border: 2px dashed var(--accent-color);
	background: color-mix(in oklch, var(--bg-container), transparent 30%);
	min-height: 200px;
	transition: border-color 0.3s ease,
		background 0.4s cubic-bezier(0.25, 0.8, 0.25, 1),
		box-shadow 0.3s ease;
}

.drop-zone.dragover {
	border-color: color-mix(in oklch, var(--accent-color), white 20%);
	background: var(--drag-over-bg);
	box-shadow: var(--drag-over-shadow);
	.upload-icon {
		animation: pulse-glow 1.5s ease infinite;
}
}

.upload-icon {
	font-size: 50px;
	color: var(--accent-color);
	margin-bottom: 15px;
}

.upload-text {
	font-size: 18px;
	font-weight: 500;
	color: var(--text-primary);
	margin-bottom: 10px;
}

#customUploadButton {
	--btn-hover-bg: color-mix(in oklch, var(--btn-primary-bg), white 8%);
	background: var(--btn-primary-bg);
	position: relative;
	overflow: hidden;
	&: :after {
		content: "";
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: radial-gradient(circle at 50% 50%, 
			oklch(100% 0 0 / 0.15) 0%, 
			transparent 70%);
	opacity: 0;
	transition: opacity 0.3s ease;
}
	
	&:hover {
	background: var(--btn-hover-bg);
	transform: translateY(-2px) scale(1.05);
	&: :after {
			opacity: 1;
}
	}
}

.file-list {
	max-height: 200px;
	overflow-y: auto;
	background: var(--file-list-bg);
	border: 1px solid var(--file-list-border);
	scrollbar-width: thin;
	scrollbar-color: var(--accent-color) transparent;
	&: :-webkit-scrollbar-thumb {
		background: var(--accent-color);
	border-radius: 4px;
}
}

.file-list-item {
	border-bottom-color: color-mix(in oklch, var(--file-list-border), transparent 50%);
	transition: background 0.2s ease;
	&: hover {
		background: color-mix(in oklch, var(--btn-primary-bg), transparent 80%);
}
}

.remove-file {
	color: var(--accent-color) !important;
	transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.remove-file:hover {
	color: oklch(65% 0.25 15) !important;
	filter: drop-shadow(0 0 4px oklch(65% 0.3 15 / 0.3));
	transform: scale(1.15);
}

[data-theme="light"] .remove-file:hover {
	color: oklch(50% 0.3 15) !important;
	filter: drop-shadow(0 0 6px oklch(50% 0.3 15 / 0.2));
}

@keyframes danger-pulse {
	0% {
		opacity: 0.8;
	}

	50% {
		opacity: 1;
	}

	100% {
		opacity: 0.8;
	}
}

.remove-file:hover::after {
	position: absolute;
	right: -1.2em;
	top: 50%;
	transform: translateY(-50%);
	animation: danger-pulse 1.5s ease infinite;
	filter: hue-rotate(-20deg);
}

.file-list-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 8px 12px;
	min-height: 42px;
}

.file-list-item:hover {
        cursor: grab; 
}

.remove-file {
	display: inline-flex !important;
	align-items: center;
	justify-content: center;
	width: 24px;
	height: 24px;
	margin-left: 12px;
	font-size: 1.25rem;
	vertical-align: middle;
	position: relative;
	top: 1px;
}

.bi-file-earmark {
	font-size: 1.1rem;
	margin-right: 10px;
	position: relative;
	top: -1px;
}

.file-list-item > div:first-child {
	display: inline-flex;
	align-items: center;
	max-width: calc(100% - 40px);
	line-height: 1.4;
}

.file-list-item:last-child {
	border-bottom: none;
}

.file-list-item i {
	color: var(--accent-color);
	margin-right: 8px;
}

.btn-close {
	width: 15px !important;
	height: 15px !important;
	background-color: #30e8dc !important;
	border-radius: 6px !important;
	border: none !important;
	position: relative !important;
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	cursor: pointer !important;
	transition: background-color 0.2s ease, transform 0.2s ease !important;
}

.btn-close::before, 
.btn-close::after {
	content: '' !important;
	position: absolute !important;
	width: 12px !important;
	height: 2px !important;
	background-color: #ff4d4f !important;
	border-radius: 2px !important;
	transition: background-color 0.2s ease !important;
}

.btn-close::before {
	transform: rotate(45deg) !important;
}

.btn-close::after {
	transform: rotate(-45deg) !important;
}

.btn-close:hover {
	background-color: #30e8dc !important;
}

.btn-close:hover::before, 
.btn-close:hover::after {
	background-color: #d9363e !important;
}

.btn-close:active {
	transform: scale(0.9) !important;
}

.card:hover .fileCheckbox {
    filter: drop-shadow(0 0 3px rgba(13, 110, 253, 0.5));
}

@media (max-width: 576px) {
    .fileCheckbox {
        transform: scale(1.1) !important;
    }
}
</style>

<style>
#previewModal .modal-body {
    height: 65vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative; 
}

#previewImage,
#previewVideo {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

#previewAudio {
    width: 100%;
    max-height: 100%;
    position: absolute; 
    bottom: 20px; 
    left: 0;
}

.hover-tips {
    font-size: 0.8rem;
    color: var(--hover-tips-color);
    margin-top: 10px; 

}

.file-info-overlay p {
    margin-bottom: 0.5rem; 
    text-align: left; 
}

.preview-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    font-size: 3rem;
    color: var(--nav-btn-color); 
    cursor: pointer;
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s;
}

.modal-content:hover .preview-nav-btn {
    opacity: 1;
}

#prevBtn {
    left: 20px;
}

#nextBtn {
    right: 20px;
}

.loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 40px;
    height: 40px;
    border: 3px solid var(--border-color);
    border-top: 3px solid var(--accent-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    display: none;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

@media (max-width: 576px) {
    .card-body .btn {
        font-size: 0.75rem !important;  
        padding: 0.25rem 0.5rem !important; 
        white-space: nowrap;  
    }
 }

@media (max-width: 768px) {
    #previewAudio {
        width: 95% !important;
        max-width: none;
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
<div class="card-header d-flex justify-content-between align-items-center py-2">
    <div class="time-display">
        <span id="dateDisplay"></span>
        <span id="weekDisplay"></span>
        <span id="lunarDisplay" class="lunar-text"></span>
        <span id="timeDisplay"></span>
    </div>
</div>
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center text-center gap-2">
        <h5 class="mb-0" style="line-height: 40px; height: 40px;">Spectra 配置管理</h5>
        <p id="status" class="mb-0">当前模式: <?= ($mode == "dark") ? "暗色模式" : "亮色模式" ?></p>
        <button id="toggleButton" onclick="toggleConfig()" class="btn btn-primary">切换模式</button>
    </div>
        <div class="d-flex align-items-center">
            <?php
            $mountPoint = '/'; 
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
            <div class="me-3 d-flex gap-2 mt-4 ps-2" 
                 data-bs-toggle="tooltip" 
                 title="挂载点：<?= $mountPoint ?>｜已用空间：<?= formatSize($usedSpace) ?>">
                <span class="btn btn-primary btn-sm"><i class="bi bi-hdd"></i> 总共：<?= $totalSpace ? formatSize($totalSpace) : 'N/A' ?></span>
                <span class="btn btn-success btn-sm"><i class="bi bi-hdd"></i> 剩余：<?= $freeSpace ? formatSize($freeSpace) : 'N/A' ?></span>
            </div>
            <?php if ($downloadUrl): ?><button class="btn btn-info mt-4 update-theme-btn" data-url="<?= htmlspecialchars($downloadUrl) ?>" title="更新主题"><i class="bi bi-cloud-download"></i> <span class="btn-label"></span></button><?php endif; ?>
            <button class="btn btn-warning ms-2 mt-4" data-bs-toggle="modal" data-bs-target="#uploadModal" title="批量上传"><i class="bi bi-upload"></i> <span class="btn-label"></span></button>
            <button class="btn btn-primary ms-2 mt-4" id="openPlayerBtn" data-bs-toggle="modal" data-bs-target="#playerModal" title="勾选添加到播放列表"><i class="bi bi-play-btn"></i> <span class="btn-label"></span></button>
            <button class="btn btn-primary ms-2 mt-4" data-bs-toggle="modal" data-bs-target="#musicModal"><i class="bi bi-music-note"></i></button>
            <button class="btn btn-danger ms-2 mt-4" id="clearBackgroundBtn" title="清除背景"><i class="bi bi-trash"></i> <span class="btn-label"></span></button>
        </div>
    </div>
        <h2 class="mt-3 mb-0">文件列表</h2>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="d-flex align-items-center mb-3 ps-2">
            <input type="checkbox" id="selectAll" class="form-check-input me-2 shadow-sm" style="width: 1.05em; height: 1.05em; border-radius: 0.35em; margin-left: 1px; transform: scale(1.2)">
            <label for="selectAll" class="form-check-label fs-5 ms-1" style="margin-right: 10px;">全选</label>
            <input type="color" id="colorPicker"  value="#ff6600" />
        </div>

        <?php
            $history_file = 'background_history.txt';
            $background_history = file_exists($history_file) ? file($history_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

            usort($files, function ($a, $b) use ($background_history) {
                $posA = array_search($a, $background_history);
                $posB = array_search($b, $background_history);
                return ($posA === false ? PHP_INT_MAX : $posA) - ($posB === false ? PHP_INT_MAX : $posB);
            });
        ?>

        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4">
            <?php foreach ($files as $file): 
                $path = $upload_dir . '/' . $file;
                $size = filesize($path);
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg','jpeg','png','gif']);
                $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mkv']);
                $isAudio = in_array($ext, ['mp3', 'wav', 'flac']);
                $isMedia = $isImage || $isVideo || $isAudio;
                $resolution = '';
                $duration = '';
                $bitrate = '';
                if ($isImage) {
                    $imageInfo = @getimagesize($path);
                    if ($imageInfo) {
                        $resolution = $imageInfo[0] . 'x' . $imageInfo[1];
                    }
                } elseif ($isVideo) {
                    $ffmpegPath = '/usr/bin/ffmpeg'; 
                    $cmd = "$ffmpegPath -i \"$path\" 2>&1";
                    $output = shell_exec($cmd);

                    if ($output) {
                        if (preg_match('/(\d{3,4})x(\d{3,4})/', $output, $matches)) {
                            $resolution = $matches[1] . 'x' . $matches[2];
                        }

                        if (preg_match('/Duration: (\d+):(\d+):(\d+)\.(\d+)/', $output, $matches)) {
                            $hours = intval($matches[1]);
                            $minutes = intval($matches[2]);
                            $seconds = intval($matches[3]);
                            $duration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                        }

                        if (preg_match('/bitrate: (\d+) kb\/s/', $output, $matches)) {
                            $bitrate = $matches[1] . ' kbps';
                        }
                
                    } else {
                        $resolution = '无法获取分辨率';
                        $duration = '无法获取时长';
                        $bitrate = '无法获取比特率';
                    }
                }
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm position-relative"> 
                    <div class="position-absolute start-0 top-0 m-2 z-2">
                        <input type="checkbox" 
                               class="fileCheckbox form-check-input shadow" 
                               value="<?= htmlspecialchars($file) ?>"
                               data-size="<?= $size ?>"
                               style="width: 1.05em; height: 1.05em; border-radius: 0.35em; transform: scale(1.2);">
                    </div>
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
                                     data-type="image"
                                     data-src="<?= htmlspecialchars($file) ?>">
                            <?php elseif ($isVideo): ?>
                                <video class="card-img-top preview-video"
                                       data-bs-toggle="modal"
                                       data-bs-target="#previewModal"
                                       data-type="video"
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
                                <?php if ($duration): ?><p class="mb-1 small">时长：<?= $duration ?></p><?php endif; ?>
                                <?php if ($resolution): ?><p class="mb-1 small">分辨率：<?= $resolution ?></p><?php endif; ?>
                                <?php if ($bitrate): ?><p class="mb-1 small">比特率：<?= $bitrate ?></p><?php endif; ?>
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
                            <div class="d-flex flex-nowrap gap-1 flex-grow-1" style="min-width: 0;">
                                <button class="btn btn-danger" onclick="if(confirm('确定删除？')) window.location='?delete=<?= urlencode($file) ?>'"  title="删除"><i class="bi bi-trash"></i></button>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#renameModal-<?= md5($file) ?>" title="重命名"><i class="bi bi-pencil"></i></button>
                                <a href="?download=<?= urlencode($file) ?>" class="btn btn-success" title="下载"><i class="bi bi-download"></i></a>                     
                                <?php if ($isMedia): ?>
                                <button class="btn btn-info set-bg-btn" data-src="<?= htmlspecialchars($file) ?>" data-type="<?= $isVideo ? 'video' : ($isAudio ? 'audio' : 'image') ?>" title="设置背景" onclick="setBackground('<?= htmlspecialchars($file) ?>')"><i class="bi bi-image"></i></button>
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
                <div class="modal-body text-center position-relative">
                    <div class="loading-spinner"></div>
                    <div id="prevBtn" class="preview-nav-btn"><i class="bi bi-chevron-left"></i></div>
                    <div id="nextBtn" class="preview-nav-btn"><i class="bi bi-chevron-right"></i></div>
                    <img id="previewImage" src="" class="img-fluid d-none">
                    <audio id="previewAudio" controls class="d-none w-100"></audio>
                    <video id="previewVideo" controls class="d-none">
                        <source id="previewVideoSource" src="" type="video/mp4">
                    </video>
              </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="fullscreenToggle">切换全屏</button>
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
                  <div class="alert alert-warning">支持格式：[ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]</div>
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
                    <button class="btn btn-sm btn-info" id="togglePip" style="display: none;"><i class="bi bi-pip"></i> 画中画</button>
                    <button class="btn btn-sm btn-success" id="toggleFullscreen"><i class="bi bi-arrows-fullscreen"></i> 全屏</button>
                    <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> 关闭</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="musicModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="langModalLabel">音乐播放器</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="floatingLyrics"></div>                   
                    <div id="currentSong" class="mb-3 text-center font-weight-bold fs-4"></div>                    
                    <div class="lyrics-container" id="lyricsContainer" style="height: 300px; overflow-y: auto;">
                    </div>                    
                    <div class="progress-container mt-3">
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" id="progressBar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>           
                    <div class="d-flex justify-content-between mt-2 small">
                        <span id="currentTime">0:00</span>
                        <span id="duration">0:00</span>
                    </div>                  
                    <div class="controls d-flex justify-content-center gap-3 mt-4">
                        <button class="btn btn-outline-light control-btn" onclick="changeTrack(-1)">
                            <i class="bi bi-skip-backward-fill"></i>
                        </button>
                        <button class="btn btn-success control-btn" id="playPauseBtn" onclick="togglePlay()">
                            <i class="bi bi-play-fill"></i>
                        </button>
                        <button class="btn btn-outline-light control-btn" onclick="changeTrack(1)">
                            <i class="bi bi-skip-forward-fill"></i>
                        </button>
                        <button class="btn btn-outline-light control-btn" id="repeatBtn" onclick="toggleRepeat()">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                    </div>
                    <div class="playlist mt-3" id="playlist"></div>
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
                    <div class="alert alert-warning">最新版本：<?= htmlspecialchars($latestVersion) ?></div>
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
.col-md-8, .col-md-12 {
	transition: all 0.3s ease;
}

.fullscreen-modal {
	width: 100vw !important;
	height: 100vh !important;
	margin: 0 !important;
	padding: 0 !important;
	max-width: none !important;
}

.fullscreen-modal .modal-content {
	height: 100%;
	display: flex;
	flex-direction: column;
}

.fullscreen-modal .modal-header,
.fullscreen-modal .modal-footer {
	flex-shrink: 0;
	min-height: 60px;
}

.fullscreen-modal .modal-body {
	flex: 1;
	min-height: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 10px;
}

#previewVideo {
	max-width: 100%;
	max-height: 100%;
	width: auto;
        height: 100% !important; 
	object-fit: contain;
}

.modal-content:fullscreen .row {
	--playlist-width: 450px;
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
function calculateAvailableHeight() {
  const header = document.querySelector('header');
  const footer = document.querySelector('footer');
  const headerHeight = header ? header.offsetHeight : 0;
  const footerHeight = footer ? footer.offsetHeight : 0;
  return window.innerHeight - headerHeight - footerHeight;
}

document.getElementById("fullscreenToggle").addEventListener("click", function () {
    const modalDialog = document.querySelector("#previewModal .modal-xl"); 
    const btn = document.getElementById("fullscreenToggle");

    if (!document.fullscreenElement) {
        modalDialog.dataset.originalWidth = modalDialog.style.width;
        modalDialog.dataset.originalHeight = modalDialog.style.height;
        
        if (modalDialog.requestFullscreen) modalDialog.requestFullscreen();
        
        modalDialog.classList.add("fullscreen-modal");
        btn.innerText = "退出全屏";
    } else {
        if (document.exitFullscreen) document.exitFullscreen();
        
        modalDialog.classList.remove("fullscreen-modal");
        if (window.innerWidth <= 576) {
            modalDialog.style.height = `${calculateAvailableHeight()}px`;
        } else {
            modalDialog.style.width = modalDialog.dataset.originalWidth;
            modalDialog.style.height = modalDialog.dataset.originalHeight;
        }
        btn.innerText = "进入全屏";
    }
});

function handleFullscreenChange() {
    const isFullscreen = !!document.fullscreenElement;
    const modalDialog = document.querySelector("#previewModal .modal-xl");
    const btn = document.getElementById("fullscreenToggle");

    if (!isFullscreen && modalDialog) {
        modalDialog.classList.remove("fullscreen-modal");
        if (window.innerWidth <= 576) {
            modalDialog.style.height = `${calculateAvailableHeight()}px`;
            window.addEventListener('resize', handleVerticalResize);
        } else {
            modalDialog.style.width = modalDialog.dataset.originalWidth;
            modalDialog.style.height = modalDialog.dataset.originalHeight;
        }
        btn.innerText = "进入全屏";
    }
}

function handleVerticalResize() {
    if (window.innerWidth > 576) return;
    const modalDialog = document.querySelector("#previewModal .modal-xl");
    modalDialog.style.height = `${calculateAvailableHeight()}px`;
}

document.addEventListener('fullscreenchange', handleFullscreenChange);
window.addEventListener('resize', handleVerticalResize);
</script>

<style>
.modal-xl {
    max-width: 1140px;
    width: 80%;
    margin: 1rem auto; 
    transition: height 0.3s ease; 
}

.fullscreen-modal {
    max-width: none !important;
    width: 100vw !important;
    height: 100vh !important;
    margin: 0;
}

.modal-footer {
    position: relative; 
    height: 60px;
    flex-shrink: 0; 
}

@media (max-width: 576px) {
    .modal-xl:not(.fullscreen-modal) {
        max-height: calc(100vh - var(--header-height) - var(--footer-height));
        overflow-y: auto;
    }
}
</style>

<script>
const playlistToggleBtn = document.getElementById('togglePlaylist');
const playlistColumn = document.querySelector('.col-md-4');
const fullscreenBtn = document.getElementById('toggleFullscreen');
const modalContent = document.querySelector('#playerModal .modal-content');
const videoElement = document.querySelector('#videoElement'); 
const pipButton = document.getElementById('togglePip');
const mainPlayer = document.getElementById('mainPlayer');
let isPlaylistVisible = true;

if ('pictureInPictureEnabled' in document) {
    pipButton.style.display = 'inline-block'; 
    
    pipButton.addEventListener('click', async () => {
        try {
            if (document.pictureInPictureElement) {
                await document.exitPictureInPicture();
            } else {
                if (mainPlayer.classList.contains('d-none')) {
                    return alert('当前媒体不支持画中画');
                }
                await mainPlayer.requestPictureInPicture();
            }
        } catch (error) {
            console.error('画中画操作失败:', error);
        }
    });

    mainPlayer.addEventListener('enterpictureinpicture', () => {
        pipButton.querySelector('i').className = 'bi bi-pip-fill';
        pipButton.innerHTML = pipButton.querySelector('i').outerHTML + ' 退出画中画';
    });

    mainPlayer.addEventListener('leavepictureinpicture', () => {
        pipButton.querySelector('i').className = 'bi bi-pip';
        pipButton.innerHTML = pipButton.querySelector('i').outerHTML + ' 画中画';
    });
}

playlistToggleBtn.addEventListener('click', () => {
    isPlaylistVisible = !isPlaylistVisible;
    playlistColumn.classList.toggle('d-none');
    
    const icon = playlistToggleBtn.querySelector('i');
    icon.className = isPlaylistVisible ? 'bi bi-list-ul' : 'bi bi-layout-sidebar';
    playlistToggleBtn.innerHTML = icon.outerHTML + ' ' + 
        (isPlaylistVisible ? '隐藏列表' : '显示列表');
    
    const mainColumn = document.querySelector('.col-md-8');
    mainColumn.classList.toggle('col-md-12');
    
    checkFullscreenState(); 
});

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        modalContent.requestFullscreen().then(() => {
            updateFullscreenButton(true); 
            checkFullscreenState();
        }).catch(console.error);
    } else {
        document.exitFullscreen();
    }
}

function updateFullscreenButton(isFullscreen) {
    const icon = fullscreenBtn.querySelector('i');
    if (isFullscreen) {
        icon.className = 'bi bi-fullscreen-exit';
        fullscreenBtn.innerHTML = icon.outerHTML + ' 退出全屏';
    } else {
        icon.className = 'bi bi-arrows-fullscreen';
        fullscreenBtn.innerHTML = icon.outerHTML + ' 全屏';
    }
}

document.addEventListener('fullscreenchange', () => {
    const isFullscreen = !!document.fullscreenElement;
    updateFullscreenButton(isFullscreen); 
    checkFullscreenState();
});

function checkFullscreenState() {
    const modalContent = document.querySelector('#playerModal .modal-content');
    const videoContainer = modalContent.querySelector('.video-container');
    
    if (document.fullscreenElement) {
        const footerHeight = document.querySelector('.modal-footer').offsetHeight;
        videoContainer.style.height = `calc(100vh - ${footerHeight}px)`;
        videoElement.style.height = '100%';
    } else {
        videoContainer.style.height = isPlaylistVisible ? 'calc(100vh - 180px)' : 'calc(100vh - 120px)';
    }
}

document.addEventListener('fullscreenchange', checkFullscreenState);

fullscreenBtn.addEventListener('click', toggleFullscreen);

let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(checkFullscreenState, 100);
});

updateFullscreenButton(false); 
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

<script>
let mediaFiles = [];
let currentPreviewIndex = -1;

function initMediaFiles() {
    mediaFiles = [];
    document.querySelectorAll('[data-bs-target="#previewModal"]').forEach((el, index) => {
        if (el.dataset.src) {
            mediaFiles.push({
                src: el.dataset.src,
                type: el.dataset.type || 'image',
                element: el
            });
            el.dataset.fileIndex = index;
        }
    });
}

function cleanMediaElements() {
    const elements = {
        image: document.getElementById('previewImage'),
        video: document.getElementById('previewVideo'),
        audio: document.getElementById('previewAudio')
    };
    
    elements.image.classList.add('d-none');
    elements.image.src = '';
    
    elements.audio.classList.add('d-none');
    elements.audio.src = '';
    if(elements.audio.pause) elements.audio.pause();
    
    elements.video.classList.add('d-none');
    const source = elements.video.querySelector('source');
    if(source) source.src = '';
    if(elements.video.pause) elements.video.pause();
}

document.getElementById('previewModal').addEventListener('show.bs.modal', function(e) {
    initMediaFiles();
    const trigger = e.relatedTarget;
    currentPreviewIndex = parseInt(trigger.dataset.fileIndex);
    loadAndPlayMedia();
});

function loadAndPlayMedia() {
    cleanMediaElements(); 
    
    const currentFile = mediaFiles[currentPreviewIndex];
    if (!currentFile) return;

    switch(currentFile.type) {
        case 'image':
            const img = document.getElementById('previewImage');
            img.src = currentFile.src;
            img.classList.remove('d-none');
            break;
            
        case 'video':
            const video = document.getElementById('previewVideo');
            const source = video.querySelector('source');
            source.src = currentFile.src;
            video.load();
            video.classList.remove('d-none');
            video.play().catch(e => console.log('Video play failed:', e));
            break;
            
        case 'audio':
            const audio = document.getElementById('previewAudio');
            audio.src = currentFile.src;
            audio.classList.remove('d-none');
            audio.play().catch(e => console.log('Audio play failed:', e));
            break;
    }
}

document.getElementById('prevBtn').addEventListener('click', () => {
    currentPreviewIndex = (currentPreviewIndex - 1 + mediaFiles.length) % mediaFiles.length;
    loadAndPlayMedia();
});

document.getElementById('nextBtn').addEventListener('click', () => {
    currentPreviewIndex = (currentPreviewIndex + 1) % mediaFiles.length;
    loadAndPlayMedia();
});
</script>

<script>
    function updateBaseHueFromColorPicker(event) {
        const color = event.target.value; 
        const rgb = hexToRgb(color); 
        const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b); 
        const hue = hsl.h; 

        document.documentElement.style.setProperty('--base-hue', hue);
        
        localStorage.setItem("baseHue", hue);
        
        document.getElementById("colorPicker").value = color;
    }

    function hexToRgb(hex) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return { r, g, b };
    }

    function rgbToHsl(r, g, b) {
        r /= 255;
        g /= 255;
        b /= 255;
        
        let max = Math.max(r, g, b);
        let min = Math.min(r, g, b);
        let h = (max + min) / 2;
        let s = (max + min) / 2;
        let l = (max + min) / 2;
        
        if (max === min) {
            h = s = 0;
        } else {
            let d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }
        
        return { h: h * 360, s, l };
    }

    function hslToHex(h, s, l) {
        h = h % 360;
        s = Math.max(0, Math.min(100, s)) / 100;
        l = Math.max(0, Math.min(100, l)) / 100;

        let c = (1 - Math.abs(2 * l - 1)) * s;
        let x = c * (1 - Math.abs(((h / 60) % 2) - 1));
        let m = l - c / 2;

        let [r, g, b] = [0, 0, 0];

        if (0 <= h && h < 60) [r, g, b] = [c, x, 0];
        else if (60 <= h && h < 120) [r, g, b] = [x, c, 0];
        else if (120 <= h && h < 180) [r, g, b] = [0, c, x];
        else if (180 <= h && h < 240) [r, g, b] = [0, x, c];
        else if (240 <= h && h < 300) [r, g, b] = [x, 0, c];
        else [r, g, b] = [c, 0, x];

        return "#" + [r, g, b]
            .map(channel => Math.round((channel + m) * 255)
            .toString(16)
            .padStart(2, "0"))
            .join("")
            .toUpperCase();
    }

    document.addEventListener("DOMContentLoaded", () => {
        const savedHue = localStorage.getItem("baseHue") || 200;
        
        document.documentElement.style.setProperty('--base-hue', savedHue);
        
        const colorPicker = document.getElementById("colorPicker");
        const savedColor = hslToHex(savedHue, 50, 50); 
        colorPicker.value = savedColor; 

        colorPicker.addEventListener('input', updateBaseHueFromColorPicker);

        const penIcon = document.getElementById("penIcon");
        penIcon.addEventListener("click", () => {
            colorPicker.click(); 
        });
    });

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
        const body = document.documentElement;
        const btn = document.getElementById("toggleButton");
        const status = document.getElementById("status");

        const baseHue = value === 'dark' ? 260 : 200;
        const chroma = value === 'dark' ? 0.03 : 0.01;

        body.style.setProperty('--base-hue', baseHue);
        body.style.setProperty('--base-chroma', chroma);

        body.setAttribute("data-theme", value);

        if (value === "dark") {
            btn.innerHTML = '<i class="bi bi-sun"></i> 切换到亮色模式';
            btn.className = "btn btn-primary light";
            status.innerText = "当前模式: 暗色模式";
        } else {
            btn.innerHTML = '<i class="bi bi-moon"></i> 切换到暗色模式';
            btn.className = "btn btn-primary dark";
            status.innerText = "当前模式: 亮色模式";
        }

        localStorage.setItem("theme", value);
    }
</script>

<script>
function setBackground(filename) {
    fetch('set_background.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'file=' + encodeURIComponent(filename)
    }).then(() => location.reload()) 
      .catch(error => console.error('请求失败:', error)); 
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    updateDateTime();
    setInterval(updateDateTime, 1000);

    const requiredElements = ['dateDisplay', 'timeDisplay', 'lunarDisplay'];
    requiredElements.forEach(id => {
        if (!document.getElementById(id)) {
            console.error(`元素 #${id} 未找到`);
        }
    });
});

const localeConfig = {
    'zh-CN': {
        weekDays: ['日', '一', '二', '三', '四', '五', '六'],
        labels: {
            year: '年',
            month: '月',
            day: '号',
            week: '星期'
        }
    },
    'en-US': {
        weekDays: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
        labels: {
            year: ' Year ',
            month: ' Month ',
            day: ' Day ',
            week: ' '
        }
    }
};

function getLunar(date) {
    const lunarInfo = [
        0x04bd8,0x04ae0,0x0a570,0x054d5,0x0d260,0x0d950,0x16554,0x056a0,0x09ad0,0x055d2,
        0x04ae0,0x0a5b6,0x0a4d0,0x0d250,0x1d255,0x0b540,0x0d6a0,0x0ada2,0x095b0,0x14977,
        0x04970,0x0a4b0,0x0b4b5,0x06a50,0x06d40,0x1ab54,0x02b60,0x09570,0x052f2,0x04970,
        0x06566,0x0d4a0,0x0ea50,0x16a95,0x05ad0,0x02b60,0x186e3,0x092e0,0x1c8d7,0x0c950,
        0x0d4a0,0x1d8a6,0x0b550,0x056a0,0x1a5b4,0x025d0,0x092d0,0x0d2b2,0x0a950,0x0b557,
        0x06ca0,0x0b550,0x15355,0x04da0,0x0a5d0,0x14573,0x052d0,0x0a9a8,0x0e950,0x06aa0,
        0x0aea6,0x0ab50,0x04b60,0x0aae4,0x0a570,0x05260,0x0f263,0x0d950,0x05b57,0x056a0,
        0x096d0,0x04dd5,0x04ad0,0x0a4d0,0x0d4d4,0x0d250,0x0d558,0x0b540,0x0b5a0,0x195a6,
        0x095b0,0x049b0,0x0a974,0x0a4b0,0x0b27a,0x06a50,0x06d40,0x0af46,0x0ab60,0x09570,
        0x04af5,0x04970,0x064b0,0x074a3,0x0ea50,0x06b58,0x055c0,0x0ab60,0x096d5,0x092e0,
        0x0c960,0x0d954,0x0d4a0,0x0da50,0x07552,0x056a0,0x0abb7,0x025d0,0x092d0,0x0cab5,
        0x0a950,0x0b4a0,0x0baa4,0x0ad50,0x055d9,0x04ba0,0x0a5b0,0x15176,0x052b0,0x0a930,
        0x07954,0x06aa0,0x0ad50,0x05b52,0x04b60,0x0a6e6,0x0a4e0,0x0d260,0x0ea65,0x0d530,
        0x05aa0,0x076a3,0x096d0,0x04bd7,0x04ad0,0x0a4d0,0x1d0b6,0x0d250,0x0d520,0x0dd45,
        0x0b5a0,0x056d0,0x055b2,0x049b0,0x0a577,0x0a4b0,0x0aa50,0x1b255,0x06d20,0x0ada0
    ];

    const zodiacs = ['猴','鸡','狗','猪','鼠','牛','虎','兔','龙','蛇','马','羊'];
    const Gan = ['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'];
    const Zhi = ['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'];
    const lunarMonths = ['正','二','三','四','五','六','七','八','九','十','冬','腊'];
    const lunarDays = ['初一','初二','初三','初四','初五','初六','初七','初八','初九','初十',
                      '十一','十二','十三','十四','十五','十六','十七','十八','十九','二十',
                      '廿一','廿二','廿三','廿四','廿五','廿六','廿七','廿八','廿九','三十'];

    let year = date.getFullYear();
    let month = date.getMonth();
    let day = date.getDate();
    
    let i, leap=0, temp=0;
    let baseDate = new Date(1900,0,31);
    let offset = Math.floor((date - baseDate)/86400000);

    for(i=1900; i<2100 && offset>0; i++) {
        temp = getLunarYearDays(i);
        offset -= temp;
    }

    if(offset<0) { 
        offset += temp; 
        i--; 
    }

    let lunarYear = i;
    let leapMonth = getLeapMonth(lunarYear);
    let isLeap = false;

    for(i=1; i<13 && offset>0; i++) {
        if(leap>0 && i==(leap+1) && !isLeap){
            --i; 
            isLeap = true; 
            temp = getLeapMonthDays(lunarYear); 
        } else {
            temp = getMonthDays(lunarYear, i); 
        }
        
        if(isLeap && i==(leap+1)) isLeap = false;
        offset -= temp;
    }

    if(offset==0 && leap>0 && i==leap+1) {
        if(isLeap) { 
            isLeap = false; 
        } else { 
            isLeap = true; 
            --i; 
        }
    }

    if(offset<0){
        offset += temp;
        i--;
    }

    let lunarMonth = i;
    let lunarDay = offset + 1;

    const zodiac = zodiacs[lunarYear % 12];
    const monthName = (isLeap ? '闰' : '') + lunarMonths[lunarMonth-1] + '月';
    const dayName = lunarDays[lunarDay-1];
    const ganZhiYear = Gan[(lunarYear - 4) % 10] + Zhi[(lunarYear - 4) % 12];

    return {
        zodiac: zodiac,
        year: ganZhiYear + '年',
        month: monthName,
        day: dayName
    };

    function getLunarYearDays(year) {
        let sum = 348;
        for(let i=0x8000; i>0x8; i>>=1) {
            sum += (lunarInfo[year-1900] & i) ? 1 : 0;
        }
        return sum + getLeapMonthDays(year);
    }

    function getLeapMonth(year) {
        return lunarInfo[year-1900] & 0xf;
    }

    function getLeapMonthDays(year) {
        return getLeapMonth(year) ? ((lunarInfo[year-1900] & 0x10000) ? 30 : 29) : 0;
    }

    function getMonthDays(year, month) {
        return (lunarInfo[year-1900] & (0x10000 >> month)) ? 30 : 29;
    }
}

function updateDateTime() {
    try {
        const now = new Date();
        const lang = navigator.language;
        const config = localeConfig[lang] || localeConfig['zh-CN'];

        const hours = now.getHours();
        const ancientTime = getAncientTime(hours); 
        const weekDayIndex = now.getDay();
        const weekDay = config.weekDays[weekDayIndex];

        const timeStr = [
            now.getHours().toString().padStart(2, '0'),
            now.getMinutes().toString().padStart(2, '0'),
            now.getSeconds().toString().padStart(2, '0')
        ].join(':');

        const timeElement = document.getElementById('timeDisplay');
        if (timeElement) {
            if (lang.startsWith('zh')) {
                timeElement.innerHTML = `
                    <span class="modern-time">${timeStr}</span>
                    <span class="ancient-time">【${ancientTime}】</span>
                `;
            } else {
                timeElement.textContent = timeStr;
            }
        }

        const dateElement = document.getElementById('dateDisplay');
        if (dateElement) {
            dateElement.textContent = 
                `${now.getFullYear()}${config.labels.year}${now.getMonth() + 1}${config.labels.month}${now.getDate()}${config.labels.day}`;
        }

        const weekElement = document.getElementById('weekDisplay');
        if (weekElement) {
            weekElement.className = 'week-display';
            weekElement.textContent = `${config.labels.week}${weekDay}`;

            if (lang.startsWith('en')) {
                weekElement.textContent = weekDay;
                weekElement.style.fontSize = '0.95em';
            }
        }

        const lunarElement = document.getElementById('lunarDisplay');
        if (lang.startsWith('zh') && lunarElement) {
            const lunar = getLunar(now);
            lunarElement.textContent = `${lunar.year} ${lunar.month}${lunar.day} ${lunar.zodiac}年`;
        }

        if (now.getHours() === 0 && 
            now.getMinutes() === 0 && 
            now.getSeconds() === 0) {
            setTimeout(() => location.reload(), 1000);
        }

    } catch (error) {
        console.error('时间更新失败:', error);

        const dateElement = document.getElementById('dateDisplay');
        if (dateElement) {
            dateElement.textContent = '时间显示异常';
        }
    }
}

function getAncientTime(hours) {
    const periods = [
        { start: 23, end: 1, name: '子', overnight: true },  
        { start: 1, end: 3, name: '丑' },
        { start: 3, end: 5, name: '寅' },
        { start: 5, end: 7, name: '卯' },  
        { start: 7, end: 9, name: '辰' },
        { start: 9, end: 11, name: '巳'},
        { start: 11, end: 13, name: '午'},
        { start: 13, end: 15, name: '未'},
        { start: 15, end: 17, name: '申'},
        { start: 17, end: 19, name: '酉'},
        { start: 19, end: 21, name: '戌'},
        { start: 21, end: 23, name: '亥'}
    ];

    const match = periods.find(p => {
        if (p.overnight) { 
            return hours >= p.start || hours < p.end;
        }
        return hours >= p.start && hours < p.end;
    });

    return match ? `${match.name}時` : '亥時';
}

const colorList = [
    '#ff6b6b',
    '#4dabf7', 
    '#51cf66', 
    '#f59f00', 
    '#845ef7', 
    '#20c997', 
    '#f783ac', 
];

let usedColors = [];
const elements = document.querySelectorAll('.time-display span');

function getNextColor() {
    if (usedColors.length === colorList.length) {
        usedColors = []; 
    }

    const remainingColors = colorList.filter(c => !usedColors.includes(c));
    const nextColor = remainingColors[Math.floor(Math.random() * remainingColors.length)];
    usedColors.push(nextColor);
    return nextColor;
}

function rotateColors() {
    elements.forEach(el => {
        const color = getNextColor();
        el.style.color = color;
    });
}

setInterval(rotateColors, 4000); 
</script>

<style>
:root {
    --primary-color: #00c896;
    --secondary-color: #0e574e;
    --background: rgba(30, 30, 30, 0.6);
    --text-color: #ffffff;
    --glass-blur: blur(20px);
    --radius: 20px;
}

body {
    margin: 0;
    font-family: 'Zen Old Mincho', 'Noto Serif SC', 'Segoe UI', serif;
    background: linear-gradient(145deg, #101010, #1a1a1a);
    color: var(--text-color);
    background-attachment: fixed;
}

#playerModal.active {
    display: flex;
    animation: slideIn 0.3s ease forwards;
}

@keyframes slideIn {
    from {
        transform: translateY(40px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.lyrics-container {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: var(--radius);
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255,255,255,0.05);
}

.lyric-line {
    font-size: 1.2em;
    margin: 18px 0;
    opacity: 0.5;
    text-align: center;
    transition: all 0.3s ease;
    line-height: 1.6;
}

.lyric-line .char {
    display: inline-block;
    white-space: nowrap;
    margin-right: 0.1rem;  
}

.lyric-line .char.played {
    background: linear-gradient(...);
}

.lyric-line.highlight {
    opacity: 1;
    transform: scale(1.05);
    color: var(--primary-color);
    font-weight: 600;
}

.lyric-line.highlight .char {
    transition: all 0.1s ease;  
}

.lyric-line.highlight .char.active {
    transform: scale(1.2);
    background: linear-gradient(
        90deg,
        #ff3366 0%, 
        #ff9933 25%,
        #ffcc00 50%, 
        #66ff33 75%,
        #33ccff 100%
    );
    background-size: 200% auto;
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent !important;
    animation: color-flow 1s linear infinite;
    text-shadow: 
        0 0 10px rgba(255,51,102,0.5),
        0 0 15px rgba(102,255,51,0.5),
        0 0 20px rgba(51,204,255,0.5);
}

#lyricsContainer::-webkit-scrollbar {
    width: 13.5px; 
}

#lyricsContainer::-webkit-scrollbar-track {
    background: rgba(0, 31, 63, 0.9);
    margin: 80px 0;
}

#lyricsContainer::-webkit-scrollbar-thumb {
    background-color: #007bff; 
    border-radius: 10px; 
    border: 3px solid #000; 
}

.lyric-line.enter-active {
    animation: textPop 0.5s ease;
}

@keyframes textPop {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes color-flow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.progress-container {
    width: 100%;
    height: 6px;
    background: rgba(255,255,255,0.08);
    border-radius: 4px;
    margin: 16px 0;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: var(--primary-color);
    transition: width 0.2s ease;
}

.controls {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 15px;
}

.control-btn {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    color: var(--text-color);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.control-btn:hover {
    background: rgba(255,255,255,0.15);
    transform: scale(1.1);
}

#playPauseBtn {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
    background: var(--primary-color);
    color: white;
    box-shadow: 0 4px 20px rgba(0, 200, 150, 0.3);
}

.playlist {
    margin-top: 20px;
    max-height: 380px;
    overflow-y: auto;
    padding: 10px;
    border-radius: var(--radius);
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255,255,255,0.05);
}

.playlist-item {
    padding: 10px 14px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.95rem;
}

.playlist-item:hover {
    background: rgba(255,255,255,0.05);
}

.playlist-item.active {
    background: var(--primary-color);
    color: white;
}

#floatingLyrics {
    position: fixed;
    top: 30px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.6);
    padding: 12px 28px;
    border-radius: 30px;
    font-size: 1.1rem;
    backdrop-filter: blur(5px);
    opacity: 0;
    transition: opacity 0.3s ease;
}

#floatingLyrics.visible {
    opacity: 1;
}

.char.active {
    color: var(--primary-color);
    font-weight: bold;
    transition: transform 0.1s ease;
}

.char.played {
    opacity: 0.4;
}

.playlist {
    counter-reset: list-item;
}

.playlist-item::before {
    content: counter(list-item) ".";
    counter-increment: list-item;
    margin-right: 8px;
    opacity: 0.6;
}

.time-display {
    display: flex;
    justify-content: space-between;
    margin-top: 8px;
    font-size: 0.9em;
    color: rgba(255,255,255,0.7);
}

.progress-container {
    cursor: pointer; 
}


.lyrics-loading {
    position: relative;
    min-height: 100px;
}

.lyrics-loading::after {
    content: "歌词加载中...";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #666;

} 

.no-lyrics {
    text-align: center;
    color: #999;
    padding: 2rem;
    font-size: 1.2em;
} 

.progress-bar {
    height: 100%;
    background: #28a745;
    border-radius: 4px;
    transition: width 0.1s linear;
}

.progress-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 2;
}

#currentSong {
    font-weight: bold; 
    color: rgb(40, 237, 240);
    text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.5); 
}

</style>

<script>
const audioPlayer = new Audio();
let songs = JSON.parse(localStorage.getItem('cachedPlaylist') || '[]');
let currentTrackIndex = JSON.parse(localStorage.getItem('currentTrackIndex') || '0');
let isPlaying = JSON.parse(localStorage.getItem('isPlaying') || 'false');
let repeatMode = JSON.parse(localStorage.getItem('repeatMode') || '0');
let isHovering = false;
let isManualScroll = false;
let isSmallScreen = window.innerWidth < 768;

function togglePlay() {
    if (isPlaying) {
        audioPlayer.pause();
    } else {
        audioPlayer.play();
    }
    isPlaying = !isPlaying;
    updatePlayButton();
    savePlayerState();
}

function updatePlayButton() {
    const btn = document.getElementById('playPauseBtn');
    btn.innerHTML = isPlaying ? '<i class="bi bi-pause-fill"></i>' : '<i class="bi bi-play-fill"></i>';
}

function changeTrack(direction) {
    if (repeatMode === 2) { 
        currentTrackIndex = Math.floor(Math.random() * songs.length);
    } else {
        currentTrackIndex = (currentTrackIndex + direction + songs.length) % songs.length;
    }
    loadTrack(songs[currentTrackIndex]);
}

function toggleRepeat() {
    repeatMode = (repeatMode + 1) % 3;
    const btn = document.getElementById('repeatBtn');
    switch (repeatMode) {
        case 0:
            btn.title = '顺序播放';
            btn.classList.remove('btn-success', 'btn-warning');
            btn.innerHTML = '<i class="bi bi-repeat"></i>';
            break;
        case 1:
            btn.title = '单曲循环';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-warning');
            btn.innerHTML = '<i class="bi bi-repeat-1"></i>';
            break;
        case 2:
            btn.title = '随机播放';
            btn.classList.add('btn-warning');
            btn.classList.remove('btn-success');
            btn.innerHTML = '<i class="bi bi-shuffle"></i>';
            break;
    }
    savePlayerState();
}

function updatePlaylistUI() {
    const playlist = document.getElementById('playlist');
    playlist.innerHTML = songs.map((url, index) => `
        <div class="playlist-item ${index === currentTrackIndex ? 'active' : ''}" 
             onclick="playTrack(${index})">
            ${decodeURIComponent(url.split('/').pop().replace(/\.\w+$/, ''))}
        </div>
    `).join('');
    setTimeout(() => scrollToCurrentTrack(), 100);
}

function playTrack(index) {
    currentTrackIndex = index;
    loadTrack(songs[index]);
}

function scrollToCurrentTrack() {
    const playlist = document.getElementById('playlist');
    const activeItem = playlist.querySelector('.playlist-item.active');
    if (activeItem) {
        activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function loadLyrics(songUrl) {
    const lyricsUrl = songUrl.replace(/\.\w+$/, '.lrc');
    fetch(lyricsUrl)
        .then(response => response.arrayBuffer())
        .then(buffer => {
            const decoder = new TextDecoder('utf-8');
            parseLyrics(decoder.decode(buffer));
            displayLyrics();
        })
        .catch(error => console.error('歌词加载失败:', error));
}

function parseLyrics(text) {
    window.lyrics = {};
    window.lyricTimes = [];
    const regex = /\[(\d+):(\d+)\.(\d+)\](.+)/g;
    let match;
    while ((match = regex.exec(text)) !== null) {
        const time = parseInt(match[1]) * 60 + parseInt(match[2]) + parseInt(match[3])/1000;
        const content = match[4].replace(/\[\d+:\d+\.\d+\]/g, '').trim();
        lyrics[time] = content;
        lyricTimes.push(time);
    }
    lyricTimes.sort((a, b) => a - b);
}

function tokenize(text) {
    const tokens = [];
    let currentWord = '';
    
    for (const char of text) {
        if (/\s/.test(char)) {
            if (currentWord) {
                tokens.push(currentWord);
                currentWord = '';
            }
            tokens.push({ type: 'space', value: char });
            continue;
        }

        if (/[-–—]/.test(char)) {
            if (currentWord) {
                tokens.push(currentWord);
                currentWord = '';
            }
            tokens.push({ type: 'punctuation', value: char });
            continue;
        }

        if (/[a-zA-Z0-9]/.test(char)) {
            currentWord += char;
        } else {
            if (currentWord) {
                tokens.push(currentWord);
                currentWord = '';
            }
            tokens.push({ type: 'char', value: char });
        }
    }

    if (currentWord) tokens.push(currentWord);
    return tokens;
}

function createCharSpans(text, startTime, endTime) {
    const tokens = tokenize(text);
    const totalDuration = endTime - startTime;
    const charCount = text.replace(/\s/g, '').length; 
    const durationPerChar = totalDuration / charCount;

    let charIndex = 0;
    const spans = [];

    tokens.forEach(token => {
        if (typeof token === 'string') { 
            const wordSpan = document.createElement('span');
            wordSpan.className = 'word';
            const letters = token.split('');
            
            letters.forEach(letter => {
                const span = document.createElement('span');
                span.className = 'char';
                span.textContent = letter;
                span.dataset.start = startTime + charIndex * durationPerChar;
                span.dataset.end = startTime + (charIndex + 1) * durationPerChar;
                wordSpan.appendChild(span);
                charIndex++;
            });
            
            spans.push(wordSpan);
        } else if (token.type === 'space') { 
            const spaceSpan = document.createElement('span');
            spaceSpan.className = 'char space';
            spaceSpan.innerHTML = '&nbsp;';
            spans.push(spaceSpan);
        } else if (token.type === 'punctuation') { 
            const punctSpan = document.createElement('span');
            punctSpan.className = 'char punctuation';
            punctSpan.textContent = token.value;
            punctSpan.dataset.start = startTime + charIndex * durationPerChar;
            punctSpan.dataset.end = startTime + (charIndex + 1) * durationPerChar;
            spans.push(punctSpan);
            charIndex++;
        } else { 
            const span = document.createElement('span');
            span.className = 'char';
            span.textContent = token.value;
            span.dataset.start = startTime + charIndex * durationPerChar;
            span.dataset.end = startTime + (charIndex + 1) * durationPerChar;
            spans.push(span);
            charIndex++;
        }
    });

    return spans;
}

function displayLyrics() {
    const lyricsContainer = document.getElementById('lyricsContainer');
    lyricsContainer.innerHTML = '';

    lyricTimes.forEach((time, index) => {
        const line = document.createElement('div');
        line.className = 'lyric-line';
        line.dataset.time = time;
        
        const endTime = index < lyricTimes.length - 1 
                      ? lyricTimes[index + 1] 
                      : time + 3; 
        
        const chars = createCharSpans(lyrics[time], time, endTime);
        chars.forEach(span => line.appendChild(span)); 
        lyricsContainer.appendChild(line);
    });

    audioPlayer.addEventListener('timeupdate', syncLyrics);
}

document.addEventListener('DOMContentLoaded', () => {
    const lyricsContainer = document.getElementById('lyricsContainer');
    
    lyricsContainer.addEventListener('mouseenter', () => {
        isHovering = true;
    });

    lyricsContainer.addEventListener('mouseleave', () => {
        isHovering = false;
        isManualScroll = false;
    });

    lyricsContainer.addEventListener('scroll', () => {
        if (isHovering) {
            isManualScroll = true;
            setTimeout(() => {
                isManualScroll = false;
            }, 3000); 
        }
    });

    loadPlayerState();
    updatePlaylistUI();
});

function syncLyrics() {
    const currentTime = audioPlayer.currentTime;
    const lyricsContainer = document.getElementById('lyricsContainer');
    const lines = lyricsContainer.querySelectorAll('.lyric-line');
    let currentLine = null;
    let hasActiveLine = false;

    lines.forEach(line => line.classList.remove('highlight', 'played'));

    for (let i = lines.length - 1; i >= 0; i--) {
        const line = lines[i];
        const lineTime = parseFloat(line.dataset.time);
        if (currentTime >= lineTime) {
            line.classList.add('highlight');
            currentLine = line;
            hasActiveLine = true;
            break;
        }
    }

    if (currentLine) {
        const chars = currentLine.querySelectorAll('.char');
        chars.forEach(char => {
            const start = parseFloat(char.dataset.start);
            const end = parseFloat(char.dataset.end);
            if (currentTime >= start && currentTime <= end) {
                char.classList.add('active');
            } else if (currentTime > end) {
                char.classList.add('played');
            }
        });

        const floatingLyrics = document.getElementById('floatingLyrics');
        if (!floatingLyrics.innerHTML || currentLine.dataset.time !== floatingLyrics.dataset.time) {
            floatingLyrics.innerHTML = currentLine.innerHTML;
            floatingLyrics.dataset.time = currentLine.dataset.time;
            floatingLyrics.classList.add('enter-active');
            setTimeout(() => floatingLyrics.classList.remove('enter-active'), 500);
        }

        const floatingChars = floatingLyrics.querySelectorAll('.char');
        chars.forEach((char, index) => {
            const floatingChar = floatingChars[index];
            if (!floatingChar) return;

            const start = parseFloat(char.dataset.start);
            const end = parseFloat(char.dataset.end);
            
            if (currentTime >= start && currentTime <= end) {
                floatingChar.classList.add('active');
                const progress = (currentTime - start) / (end - start);
                floatingChar.style.transform = `scale(${1 + progress * 0.2})`;
            } else {
                floatingChar.classList.remove('active');
                floatingChar.style.transform = '';
            }
        });

        if (!isSmallScreen && !isHovering && !isManualScroll) {
            const lineRect = currentLine.getBoundingClientRect();
            const containerRect = lyricsContainer.getBoundingClientRect();
            const targetPosition = lineRect.top - containerRect.top + lyricsContainer.scrollTop - (lyricsContainer.clientHeight / 2) + (lineRect.height / 2);
            
            const buffer = 50;
            if (lineRect.top < containerRect.top + buffer || 
                lineRect.bottom > containerRect.bottom - buffer) {
                lyricsContainer.scrollTo({ top: targetPosition, behavior: 'smooth' });
            }
        }

        if (!hasActiveLine && lyricsContainer.scrollTop !== 0) {
            lyricsContainer.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
}

function loadTrack(url) {
    audioPlayer.src = url;
    updatePlayButton(); 
    updatePlaylistUI();
    loadLyrics(url);
    updateCurrentSong(url);
    updateTimeDisplay();
    
    if (isPlaying) {
        audioPlayer.play().catch((error) => {
            console.log('自动播放被阻止:', error);
            isPlaying = false;
            updatePlayButton();
            savePlayerState();
        });
    }
    savePlayerState();
}

function initializePlayer() {
    audioPlayer.src = songs[currentTrackIndex] || '';
    audioPlayer.currentTime = JSON.parse(localStorage.getItem('currentTime') || '0');
    
    audioPlayer.addEventListener('loadedmetadata', () => {
        loadLyrics(songs[currentTrackIndex]); 
        updateCurrentSong(songs[currentTrackIndex]);
    });

    updatePlayButton();
    setRepeatButtonState();
    updateTimeDisplay(true); 
    
    if (isPlaying) {
        audioPlayer.play().catch((error) => {
            console.log('自动播放被阻止:', error);
            isPlaying = false;
            saveCoreState();
            updatePlayButton();
        });
    }
}

function saveCoreState() {
    localStorage.setItem('cachedPlaylist', JSON.stringify(songs));
    localStorage.setItem('currentTrackIndex', currentTrackIndex);
    localStorage.setItem('isPlaying', isPlaying);
    localStorage.setItem('repeatMode', repeatMode);
    localStorage.setItem('currentTime', audioPlayer.currentTime);
}

function updateCurrentSong(url) {
    const songName = decodeURIComponent(url.split('/').pop().replace(/\.\w+$/, ''));
    document.getElementById('currentSong').textContent = songName;
}

function updateTimeDisplay() {
    const currentTimeElement = document.getElementById('currentTime');
    const durationElement = document.getElementById('duration');
    const progressBar = document.getElementById('progressBar');

    audioPlayer.addEventListener('timeupdate', () => {
        const currentTime = audioPlayer.currentTime;
        const duration = audioPlayer.duration || 0;
        const progress = (currentTime / duration) * 100 || 0;

        currentTimeElement.textContent = formatTime(currentTime);
        durationElement.textContent = formatTime(duration);
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute('aria-valuenow', progress);
    });

    progressBar.parentElement.addEventListener('click', (e) => {
        const rect = progressBar.parentElement.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const clickedPercent = (x / rect.width) * 100;
        const newTime = (clickedPercent / 100) * audioPlayer.duration;
        audioPlayer.currentTime = newTime;
    });
}

function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);
    return `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
}

audioPlayer.addEventListener('ended', () => {
    if (repeatMode === 1) {
        audioPlayer.play();
    } else {
        changeTrack(1);
    }
});

function savePlayerState() {
    localStorage.setItem('playerState', JSON.stringify({
        isPlaying: isPlaying,
        repeatMode: repeatMode,
        currentTrackIndex: currentTrackIndex,
        currentTime: audioPlayer.currentTime,
        currentTrack: songs[currentTrackIndex]
    }));
}

function loadPlayerState() {
    const savedState = localStorage.getItem('playerState');
    if (savedState) {
        const state = JSON.parse(savedState);
        isPlaying = state.isPlaying; 
        repeatMode = state.repeatMode;
        currentTrackIndex = state.currentTrackIndex || 0;
        
        if (state.currentTrack) {
            audioPlayer.currentTime = state.currentTime;
            setRepeatButtonState();
        }
    }
}

function setRepeatButtonState() {
    const btn = document.getElementById('repeatBtn');
    switch (repeatMode) {
        case 0:
            btn.title = '顺序播放';
            btn.classList.remove('btn-success', 'btn-warning');
            break;
        case 1:
            btn.title = '单曲循环';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-warning');
            break;
        case 2:
            btn.title = '随机播放';
            btn.classList.add('btn-warning');
            btn.classList.remove('btn-success');
            break;
    }
}

function loadDefaultPlaylist() {
    fetch('https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/songs.txt')
        .then(response => response.text())
        .then(data => {
            const newSongs = data.split('\n').filter(url => url.trim());
            
            if (JSON.stringify(songs) !== JSON.stringify(newSongs)) {
                songs = [...new Set([...songs, ...newSongs])];
                currentTrackIndex = songs.findIndex(url => url === localStorage.getItem('currentTrack'));
                if (currentTrackIndex === -1) currentTrackIndex = 0;
                localStorage.setItem('cachedPlaylist', JSON.stringify(songs));
            }
            
            updatePlaylistUI();
            initializePlayer();
            
            if (songs[currentTrackIndex]) {
                loadLyrics(songs[currentTrackIndex]);
                updateCurrentSong(songs[currentTrackIndex]);
            }
        })
        .catch(error => console.error('播放列表加载失败:', error));
}

function loadLyrics(songUrl) {
    const lyricsUrl = songUrl.replace(/\.\w+$/, '.lrc');
    fetch(lyricsUrl)
        .then(response => {
            if (!response.ok) throw new Error('歌词不存在');
            return response.arrayBuffer();
        })
        .then(buffer => {
            const decoder = new TextDecoder('utf-8');
            parseLyrics(decoder.decode(buffer));
            displayLyrics();
            document.dispatchEvent(new Event('lyricsLoaded'));
        })
        .catch(error => {
            console.error('歌词加载失败:', error);
            document.getElementById('lyricsContainer').innerHTML = 
                `<div class="no-lyrics">暂无歌词</div>`;
        });
}

function updatePlaylistUI() {
    const playlist = document.getElementById('playlist');
    playlist.innerHTML = songs.map((url, index) => `
        <div class="playlist-item 
            ${index === currentTrackIndex ? 'active' : ''}
            ${!isPlaying && index === currentTrackIndex ? 'paused' : ''}" 
            onclick="playTrack(${index})">
            ${decodeURIComponent(url.split('/').pop().replace(/\.\w+$/, ''))}
        </div>
    `).join('');
    
    setTimeout(() => {
        const activeItem = playlist.querySelector('.active');
        if (activeItem) {
            activeItem.scrollIntoView({ behavior: 'auto', block: 'nearest' });
            activeItem.classList.toggle('blink', !isPlaying);
        }
    }, 100);
}

loadDefaultPlaylist();
window.addEventListener('resize', () => {
    isSmallScreen = window.innerWidth < 768;
});
</script>