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
        $error_message = urlencode(implode("\n", $upload_errors));
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . $error_message);
        exit;
    }
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
if (!empty($_GET['error'])) {
    echo '<div class="alert alert-danger mt-3 mx-3" role="alert" id="log-message">';
    echo nl2br(htmlspecialchars(urldecode($_GET['error'])));
    echo '</div>';
}
?>

<?php
$default_url = 'https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/songs.txt';
$file_path = __DIR__ . '/url_config.txt'; 
$message = '';

if (!file_exists($file_path)) {
    if (file_put_contents($file_path, $default_url) !== false) {
        chmod($file_path, 0644); 
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_url'])) {
        $new_url = $_POST['new_url'];
        if (file_put_contents($file_path, $new_url) !== false) {
            chmod($file_path, 0644);  
            $message = '更新成功！';
        } else {
            $message = '更新失败，请检查权限。';
        }
    }

    if (isset($_POST['reset_default'])) {
        if (file_put_contents($file_path, $default_url) !== false) {
            chmod($file_path, 0644);
            $message = '已恢复默认地址！';
        } else {
            $message = '恢复失败，请检查权限。';
        }
    }
} else {
    $new_url = file_exists($file_path) ? file_get_contents($file_path) : $default_url;
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
        --base-hue-1: 20;
        --base-hue-2: 200;
        --base-hue-3: 135;
        --base-hue-4: 80;
        --base-hue-5: 270;
        --base-hue-6: 170;
        --base-hue-7: 340;
        --l: 85%;
        --c: 0.18;
	
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
	--btn-warning-bg: oklch(70% 0.18 80); 
	--btn-warning-hover: color-mix(in oklch, var(--btn-warning-bg), white 10%);
	--color-accent: oklch(55% 0.18 240);

}

[data-theme="light"] {
	--base-hue: 200;
	--base-chroma: 0.01;
        --l: 60%;
        --c: 0.25;
	
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
	--btn-warning-bg: oklch(85% 0.22 80);
	--btn-warning-hover: color-mix(in oklch, var(--btn-warning-bg), black 15%);
	--color-accent: oklch(75% 0.14 220);
}

@font-face {
	font-display: swap; 
  font-family: 'Fredoka One';
  font-style: normal;
  font-weight: 400;
  src: url('/luci-static/spectra/fonts/fredoka-v16-latin-regular.woff2') format('woff2');
}

body {
        background: var(--body-bg-color, #1a1a2e);
        color: var(--text-primary);
        -webkit-backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        font-family: 'Fredoka One', cursive;
        font-weight: 400; 
        background: oklch(var(--bg-l) var(--base-chroma) var(--base-hue));
}

body.default-font {
        font-family: system-ui, sans-serif;
        font-weight: 400;
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
	font-size: 1.4rem !important;
	color: var(--text-primary);
	padding: 6px 12px !important;
	display: flex !important;
	align-items: center !important;
	flex-wrap: wrap !important;
	gap: 8px !important;
}

.week-display {
	color: var(--text-secondary);
	margin-left: 6px !important;
}

.lunar-text {
	color: var(--text-secondary);
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
	margin-left: 4px !important;
	letter-spacing: 1px !important;
}

.custom-tooltip-wrapper {
        position: relative;
        display: inline-block;
        cursor: help; 
}

.custom-tooltip-wrapper::after {
        content: attr(data-tooltip);
        position: absolute;
        top: -100%; 
        left: 0;
        transform: translateY(-8px); 
        background-color: rgba(0, 0, 0, 0.8);
        color: #fff;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 0.875rem;
        white-space: pre-wrap;
        line-height: 1.4;
        z-index: 999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
        max-width: 300px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.custom-tooltip-wrapper:hover::after {
        opacity: 1;
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
        font-weight: bold;
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
	background: var(--card-bg);
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
	width: 100%;
	height: 300px; 
	overflow: hidden;
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
        position: absolute;
        min-width: 100%;
        min-height: 100%;
        object-fit: cover; 
}

.preview-container:hover .preview-img {
	transform: scale(1.05);
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
	background: var(--color-accent) !important;  
	border-color: var(--color-accent);      
	box-shadow: none;
	z-index: 2;
	color: var(--text-primary);
}

#playlistContainer .list-group-item:hover {
	background-color: var(--color-accent);
	transform: translateX(5px);
	cursor: pointer;
}

.text-muted {
	color: var(--accent-color) !important;
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
	background: var(--accent-color);
	border-radius: 4px;
}

::-webkit-scrollbar-track {
	margin: 50px 0;
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
    font-size: 1.3rem;
    color: var(--accent-color);
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

@media (max-width: 768px) {
    .me-3.d-flex.gap-2.mt-4.ps-2 {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.3rem;
        padding-left: 15px; 
        margin-bottom: 0.5rem !important; 
  }

@media (max-width: 768px) {
    .controls {
        gap: 0.1rem;  
    }

    .controls .control-btn {
        font-size: 0.7rem;  
        padding: 0.1rem 0.2rem;  
        border-radius: 50%;  
    }

    .controls .btn {
        padding: 0.1rem 0.2rem; 
    }
}

@media (max-width: 575.98px) {
  .time-display {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 0.25rem 0.5rem !important;
    font-size: 1.05rem !important;
  }

  .time-display > span {
    flex: 1 1 45% !important; 
    box-sizing: border-box;
    white-space: nowrap !important;
  }

  .time-display > span:nth-child(1),
  .time-display > span:nth-child(2) {
    order: 1; 
  }

  .time-display > span:nth-child(3),
  .time-display > span:nth-child(4) {
    order: 2; 
    margin-top: 0.25rem !important;
  }

  .time-display > span { 
    min-width: 45% !important;
    overflow: visible !important; 
  }

  .lunar-text { 
    font-size: 1.05rem !important;
    letter-spacing: -0.3px !important; 
  }
}
</style>

<div class="container-sm container-bg text-center mt-4">
    <div class="alert alert-secondary d-none" id="toolbar">
        <div class="d-flex justify-content-between">
            <div>
                <button class="btn btn-outline-primary" id="selectAllBtn" data-translate="select_all">全选</button>
                <span id="selectedInfo"></span>
            </div>
            <button class="btn btn-danger" id="batchDeleteBtn" data-translate="batch_delete">批量删除选中文件</button>
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
        <h5 class="mb-0" style="line-height: 40px; height: 40px;" data-translate="spectra_config">Spectra 配置管理</h5>
        <p id="status" class="mb-0"><span data-translate="current_mode">当前模式:</span> 加载中...</p>
        <button id="toggleButton" onclick="toggleConfig()" class="btn btn-primary" data-translate="toggle_mode">切换模式</button>
    </div>
        <div class="d-flex align-items-center">
            <?php
            $mountPoint = '/'; 
            $freeSpace = @disk_free_space($mountPoint);
            $totalSpace = @disk_total_space($mountPoint);
            $usedSpace = $totalSpace - $freeSpace;
            
            function formatSize($bytes) {
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                $index = 0;
                while ($bytes >= 1024 && $index < 3) {
                    $bytes /= 1024;
                    $index++;
                }
                return round($bytes, 2) . ' ' . $units[$index];
            }
            ?>  
            <div class="me-3 d-flex gap-2 mt-4 ps-2 custom-tooltip-wrapper" 
                 data-tooltip="挂载点：<?= $mountPoint ?>｜已用空间：<?= formatSize($usedSpace) ?>">
                <span class="btn btn-primary btn-sm"><i class="bi bi-hdd"></i> <span data-translate="total">Total：</span>：<?= $totalSpace ? formatSize($totalSpace) : 'N/A' ?></span>
                <span class="btn btn-success btn-sm"><i class="bi bi-hdd"></i> <span data-translate="free">Free：</span>：<?= $freeSpace ? formatSize($freeSpace) : 'N/A' ?></span>
            </div>
            <button class="btn btn-info mt-4" data-bs-toggle="modal" data-bs-target="#updateConfirmModal" data-translate-title="check_update"><i class="bi bi-cloud-download"></i> <span class="btn-label"></span></button>
            <button class="btn btn-warning ms-2 mt-4" data-bs-toggle="modal" data-bs-target="#uploadModal" data-translate-title="batch_upload"><i class="bi bi-upload"></i> <span class="btn-label"></span></button>
            <button class="btn btn-primary ms-2 mt-4" id="openPlayerBtn" data-bs-toggle="modal" data-bs-target="#playerModal" data-translate-title="add_to_playlist"><i class="bi bi-play-btn"></i> <span class="btn-label"></span></button>
            <button class="btn btn-success ms-2 mt-4" data-bs-toggle="modal" data-bs-target="#musicModal"><i class="bi bi-music-note"></i></button>
            <button class="btn btn-danger ms-2 mt-4" id="clearBackgroundBtn" data-translate-title="clear_background"><i class="bi bi-trash"></i> <span class="btn-label"></span></button>
        </div>
    </div>
        <h2 class="mt-3 mb-0" data-translate="file_list">File List</h2>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="d-flex align-items-center mb-3 ps-2">
            <input type="checkbox" id="selectAll" class="form-check-input me-2 shadow-sm" style="width: 1.05em; height: 1.05em; border-radius: 0.35em; margin-left: 1px; transform: scale(1.2)">
            <label for="selectAll" class="form-check-label fs-5 ms-1" style="margin-right: 10px;" data-translate="select_all">Select All'</label>
            <input type="color" id="colorPicker" style="margin-right: 10px;" value="#ff6600" data-translate-title="component_bg_color"/>
            <input type="color" id="bodyBgColorPicker"  style="margin-right: 10px; value="#1a1a2e" data-translate-title="page_bg_color" />
            <button id="fontToggleBtn" style="border: 1px solid white; border-radius: 4px; width: 50px; display: flex; align-items: center; justify-content: center;" data-translate-title="toggle_font">🅰️</button>
        <div class="ms-auto" style="margin-right: 20px;">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#langModal">
                <img id="flagIcon" src="/luci-static/ipip/flags/<?php echo $currentLang; ?>.png" style="width:24px; height:16px">
                <span data-translate="change_language">Change Language</span>
            </button>
        </div>
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
                               style="width: 1.05em !important; height: 1.05em !important; border-radius: 0.35em; transform: scale(1.2);">
                    </div>
                    <div class="position-relative">
                        <?php if ($isMedia): ?>
                        <div class="preview-container">
                            <div class="file-type-indicator">
                                <?php if ($isImage): ?>
                                    <i class="bi bi-image-fill text-white"></i>
                                    <span class="text-white small" data-translate="image">Image</span>
                                <?php elseif ($isVideo): ?>
                                    <i class="bi bi-play-circle-fill text-white"></i>
                                    <span class="text-white small" data-translate="video">Video</span>
                                <?php elseif ($isAudio): ?>
                                    <i class="bi bi-music-note-beamed text-white"></i>
                                    <span class="text-white small" data-translate="audio">Audio</span>
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
                                        <div class="hover-tips" data-translate="hover_to_preview">Click to activate hover preview</div>
                                    </div>
                                        <audio class="hover-audio" preload="none"></audio>
                                </div>
                            <?php endif; ?>

                            <div class="file-info-overlay">
                                <p class="mb-1 small"><span data-translate="filename">Name：</span> <?= htmlspecialchars($file) ?></p>
                                <p class="mb-1 small"><span data-translate="filesize">Size：</span> <?= round($size/(1024*1024),2) ?> MB</p>
                                <?php if ($duration): ?><p class="mb-1 small"><span data-translate="duration">Duration：</span><?= $duration ?></p><?php endif; ?>
                                <?php if ($resolution): ?><p class="mb-1 small"><span data-translate="resolution">Resolution：</span> <?= $resolution ?></p><?php endif; ?>
                                <?php if ($bitrate): ?><p class="mb-1 small"><span data-translate="bitrate">Bitrate：</span> <?= $bitrate ?></p><?php endif; ?>
                                <p class="mb-0 small text-uppercase"><span data-translate="type">Type：</span> <?= $ext ?></p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="card-body text-center">
                            <div class="file-type-indicator">
                                <i class="bi bi-file-earmark-text-fill text-white"></i>
                                <span class="text-white small" data-translate="document">Document</span>
                            </div>
                            <i class="bi bi-file-earmark fs-1 text-muted"></i>
                            <p class="small mb-0"><?= htmlspecialchars($file) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body pt-2 mt-2">
                        <div class="d-flex flex-nowrap align-items-center justify-content-between gap-2">                         
                            <div class="d-flex flex-nowrap gap-1 flex-grow-1" style="min-width: 0;">
                                <button class="btn btn-danger" onclick="if(confirm('确定删除？')) window.location='?delete=<?= urlencode($file) ?>'" data-translate-title="delete"><i class="bi bi-trash"></i></button>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#renameModal-<?= md5($file) ?>" data-translate-title="rename"><i class="bi bi-pencil"></i></button>
                                <a href="?download=<?= urlencode($file) ?>" class="btn btn-success"><i class="bi bi-download" data-translate-title="download"></i></a>                     
                                <?php if ($isMedia): ?>
                                <button class="btn btn-info set-bg-btn" data-src="<?= htmlspecialchars($file) ?>" data-type="<?= $isVideo ? 'video' : ($isAudio ? 'audio' : 'image') ?>" onclick="setBackground('<?= htmlspecialchars($file) ?>')" data-translate-title="set_background"><i class="bi bi-image"></i></button>
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
                    <h5 class="modal-title" data-translate="preview">Preview</h5>
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
                    <button class="btn btn-primary" id="fullscreenToggle" data-translate="toggle_fullscreen">Toggle Fullscreen</button>
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
                    <h5 class="modal-title" data-translate="batch_upload"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="alert alert-warning" data-translate="supported_formats"></div>
                    <form id="uploadForm" method="post" enctype="multipart/form-data">
                        <div class="drop-zone border rounded p-5 text-center mb-3">
                            <input type="file" name="upload_file[]" id="upload_file" multiple 
                                   style="opacity: 0; position: absolute; z-index: -1">
                            <div class="upload-area">
                                <i class="bi bi-cloud-upload-fill text-primary mb-3" style="font-size: 4rem;"></i>
                                <div class="fs-5 mb-2" data-translate="drop_files_here"></div>
                                <div class="text-muted upload-or mb-3" data-translate="or"></div>
                                <button type="button" class="btn btn-primary btn-lg" id="customUploadButton">
                                    <i class="bi bi-folder2-open me-2"></i><span data-translate="select_files"></span>
                                </button>
                                <div class="file-list mt-3"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" id="updatePhpConfig" data-translate="unlock_php_upload_limit"></button>
                    <button class="btn btn-primary" onclick="$('#uploadForm').submit()" data-translate="upload"></button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel"></button>
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
                        <h5 class="modal-title" data-translate="rename_file"><?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label data-translate="new_filename"></label>
                            <input type="text" 
                                   class="form-control" 
                                   name="new_name"
                                   value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>"
                                   data-translate-title="invalid_filename_chars">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel"></button>
                        <button type="submit" class="btn btn-primary" name="rename" data-translate="confirm"></button>
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
                    <h5 class="modal-title" id="playerModalLabel" data-translate="media_player"></h5>
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
                            <h6 class="mb-3" data-translate="playlist"></h6>
                            <div class="list-group flex-grow-1 overflow-auto" id="playlistContainer">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" id="clearPlaylist"><i class="bi bi-trash"></i> <span data-translate="clear_list"></span></button>
                    <button class="btn btn-sm btn-primary" id="togglePlaylist"><i class="bi bi-list-ul"></i> <span data-translate="toggle_list"></span></button>
                    <button class="btn btn-sm btn-info" id="togglePip" style="display: none;"><i class="bi bi-pip"></i> <span data-translate="picture_in_picture"></span></button>
                    <button class="btn btn-sm btn-success" id="toggleFullscreen"><i class="bi bi-arrows-fullscreen"></i> <span data-translate="fullscreen"></span></button>
                    <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> <span data-translate="close"></span></button>
                </div>
            </div>
        </div>
    </div>
<div id="floatingLyrics">
    <div class="floating-controls">
        <button class="ctrl-btn" onclick="changeTrack(-1)" data-translate-title="previous_track">
            <i class="bi bi-skip-backward-fill"></i>
        </button>
        <button class="ctrl-btn" id="floatingPlayBtn" onclick="togglePlay()"  data-translate-title="play_pause">
            <i class="bi bi-play-fill"></i>
        </button>
        <button class="ctrl-btn" onclick="changeTrack(1)" data-translate-title="next_track">
            <i class="bi bi-skip-forward-fill"></i>
        </button>
        <button class="ctrl-btn" id="floatingRepeatBtn" onclick="toggleRepeat()">
            <i class="bi bi-arrow-repeat"></i>
        </button>
        <button class="ctrl-btn" id="toggleFloatingLyrics" onclick="toggleFloating()" data-translate-title="toggle_floating_lyrics"><i id="floatingIcon" class="bi bi-display"></i></button>
    </div>
    <div id="currentSong" class="vertical-title"></div>
    <div class="vertical-lyrics"></div>
</div>
    <div class="modal fade" id="musicModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="langModalLabel" data-translate="music_player"></h5>
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
                        <button class="btn btn-outline-light control-btn" id="toggleFloatingLyrics" onclick="toggleFloating()" data-translate-title="toggle_floating_lyrics"><i id="floatingIcon" class="bi bi-display"></i></button>
                        <button class="btn btn-outline-light control-btn" id="repeatBtn" onclick="toggleRepeat()">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        <button class="btn btn-outline-light control-btn" onclick="changeTrack(-1)" data-translate-title="previous_track">
                            <i class="bi bi-caret-left-fill"></i>
                        </button>
                        <button class="btn btn-success control-btn" id="playPauseBtn" onclick="togglePlay()" data-translate-title="play_pause">
                            <i class="bi bi-play-fill"></i>
                        </button>
                        <button class="btn btn-outline-light control-btn" onclick="changeTrack(1)" data-translate-title="next_track">
                            <i class="bi bi-caret-right-fill"></i>
                        </button>
                        <button class="btn btn-outline-light control-btn" id="clear-cache-btn" data-translate-title="clear_config"><i class="bi bi-trash3-fill"></i></button>
                       <button class="btn btn-outline-light control-btn" type="button" data-bs-toggle="modal" data-bs-target="#urlModal" data-translate-title="custom_playlist"><i class="bi bi-music-note-list"></i></button>
                        <button class="btn btn-volume position-relative" id="volumeToggle" data-translate-title="volume">
                            <i class="bi bi-volume-up-fill"></i>
                            <div class="volume-slider-container position-absolute bottom-100 start-50 translate-middle-x mb-1 p-2"
                                 id="volumePanel"
                                 style="display: none; width: 120px;">
                                <input type="range" 
                                       class="form-range volume-slider" 
                                       id="volumeSlider"
                                       min="0" 
                                       max="1" 
                                       step="0.01"
                                       value="1">
                                </div>
                            </button> 
                        </div>
                    <div class="playlist mt-3" id="playlist"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="urlModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" data-translate="update_playlist"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if($message): ?>
                    <div class="alert alert-<?= strpos($message, '成功') !== false ? 'success' : 'danger' ?>">
                        <?= $message ?>
                    </div>
                    <?php endif; ?>               
                    <form method="POST">
                        <div class="mb-3">
                            <label data-translate="playlist_url"></label>
                            <input type="text" name="new_url" id="new_url" class="form-control" 
                                   value="<?= htmlspecialchars($new_url) ?>" required>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" data-translate="save"></button>
                            <button type="submit" name="reset_default" class="btn btn-secondary" data-translate="reset_default"></button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" data-translate="theme_download"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="themeVersionInfo" class="alert alert-warning" data-translate="fetching_version"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel"></button>
                    <a id="confirmUpdateLink" href="#" class="btn btn-danger" target="_blank" data-translate="download_local"></a>
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
                if (files.length === 0) { alert(translations['select_files_to_delete'] || 'Please select files to delete first');  return; }
                if (confirm((translations['confirm_batch_delete'] || 'Are you sure you want to delete the selected %d files?').replace('%d', files.length))) {
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
                    $('#selectedInfo').html((translations['selected_info'] || 'Selected %d files，total %s MB').replace('%d', count).replace('%s', (totalSize / (1024 * 1024)).toFixed(2)));
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
        document.getElementById("updatePhpConfig").addEventListener("click", function() {
            if (confirm(translations['confirm_update_php'] || "Are you sure you want to update PHP configuration?")) {
                fetch("update_php_config.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" }
                })
                .then(response => response.json())
                .then(data => alert(data.message))
                .catch(error => alert(translations['request_failed'] || "Request failed: " + error.message));
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
        btn.innerText = translations['exit_fullscreen'] || 'Exit Fullscreen';
    } else {
        if (document.exitFullscreen) document.exitFullscreen();
        
        modalDialog.classList.remove("fullscreen-modal");
        if (window.innerWidth <= 576) {
            modalDialog.style.height = `${calculateAvailableHeight()}px`;
        } else {
            modalDialog.style.width = modalDialog.dataset.originalWidth;
            modalDialog.style.height = modalDialog.dataset.originalHeight;
        }
        btn.innerText = translations['enter_fullscreen'] || 'Enter Fullscreen';
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
        btn.innerText = translations['enter_fullscreen'] || 'Enter Fullscreen';
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
    border: none !important; 
    box-shadow: none !important;
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
                    return alert(translations['pip_not_supported'] || 'Current media does not support Picture-in-Picture');
                }
                await mainPlayer.requestPictureInPicture();
            }
        } catch (error) {
            console.error(translations['pip_operation_failed'] || 'Picture-in-Picture operation failed:', error);
        }
    });

    mainPlayer.addEventListener('enterpictureinpicture', () => {
        pipButton.querySelector('i').className = 'bi bi-pip-fill';
        pipButton.innerHTML = pipButton.querySelector('i').outerHTML + 
            ` ${translations['exit_picture_in_picture'] || 'Exit Picture-in-Picture'}`;
    });

    mainPlayer.addEventListener('leavepictureinpicture', () => {
        pipButton.querySelector('i').className = 'bi bi-pip';
        pipButton.innerHTML = pipButton.querySelector('i').outerHTML + 
            ` ${translations['picture_in_picture'] || 'Picture-in-Picture'}`;
    });
}

playlistToggleBtn.addEventListener('click', () => {
    isPlaylistVisible = !isPlaylistVisible;
    playlistColumn.classList.toggle('d-none');
    
    const icon = playlistToggleBtn.querySelector('i');
    icon.className = isPlaylistVisible ? 'bi bi-list-ul' : 'bi bi-layout-sidebar';
    playlistToggleBtn.innerHTML = icon.outerHTML + ' ' + 
        (isPlaylistVisible ? translations['hide_playlist'] || 'Hide Playlist' : translations['show_playlist'] || 'Show Playlist');
    
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
        fullscreenBtn.innerHTML = icon.outerHTML + ` ${translations['exit_fullscreen'] || 'Exit Fullscreen'}`;
    } else {
        icon.className = 'bi bi-arrows-fullscreen';
        fullscreenBtn.innerHTML = icon.outerHTML + ` ${translations['enter_fullscreen'] || 'Enter Fullscreen'}`;
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
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('updateConfirmModal');
    const versionInfo = document.getElementById('themeVersionInfo');
    const downloadLink = document.getElementById('confirmUpdateLink');

    modalElement.addEventListener('shown.bs.modal', function () {
        versionInfo.textContent = translations['fetching_version'] || 'Fetching version info...';
        downloadLink.href = '#';

        fetch('check_theme_update.php')
            .then(response => response.json())
            .then(data => {
                if (data.version && data.url) {
                    versionInfo.textContent = `${translations['latest_version'] || 'Latest Version：'}${data.version}`;
                    downloadLink.href = data.url;
                } else {
                    versionInfo.textContent = translations['unable_to_fetch_version'] || 'Unable to fetch the latest version info';
                }
            })
            .catch(() => {
                versionInfo.textContent = translations['request_failed'] || 'Request failed, please try again later';
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
  function hexToRgb(hex) {
    return {
      r: parseInt(hex.slice(1, 3), 16),
      g: parseInt(hex.slice(3, 5), 16),
      b: parseInt(hex.slice(5, 7), 16)
    };
  }

  function rgbToOklch(r, g, b) {
    const [lr, lg, lb] = [r, g, b].map(c => {
      c /= 255;
      return c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    });

    const x = 0.4122214708 * lr + 0.5363325363 * lg + 0.0514459929 * lb;
    const y = 0.2119034982 * lr + 0.6806995451 * lg + 0.1073969566 * lb;
    const z = 0.0883024619 * lr + 0.2817188376 * lg + 0.6299787005 * lb;

    const l = Math.cbrt(0.8189330101 * x + 0.3618667424 * y - 0.1288997136 * z);
    const m = Math.cbrt(-0.0321965433 * x + 0.9295746987 * y + 0.0361446476 * z);
    const s = Math.cbrt(0.0481421477 * x - 0.0192659616 * y + 0.9902282127 * z);

    const l_ = 0.2104542553 * l + 0.7936177850 * m - 0.0040720468 * s;
    const a  = 1.9779984951 * l - 2.4285922050 * m + 0.4505937099 * s;
    const b_ = 0.0259040371 * l + 0.7827717662 * m - 0.8086757660 * s;

    const c = Math.sqrt(a * a + b_ * b_);
    let h = Math.atan2(b_, a) * 180 / Math.PI;
    if (h < 0) { 
      h += 360; 
    }

    return { l: l_ * 100, c: c, h: h };
  }

  function hexToOklch(hex) {
    const rgb = hexToRgb(hex);
    return rgbToOklch(rgb.r, rgb.g, rgb.b);
  }

  function oklchToHex(h, c, l = 50) {
    const hslSat = c * 100;
    return hslToHex(h, hslSat, l);
  }

  function hslToHex(h, s, l) {
    h = h % 360;
    s = Math.max(0, Math.min(100, s)) / 100;
    l = Math.max(0, Math.min(100, l)) / 100;

    const c = (1 - Math.abs(2 * l - 1)) * s;
    const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
    const m = l - c / 2;
    let r, g, b;

    if (h < 60) {
      [r, g, b] = [c, x, 0];
    } else if (h < 120) {
      [r, g, b] = [x, c, 0];
    } else if (h < 180) {
      [r, g, b] = [0, c, x];
    } else if (h < 240) {
      [r, g, b] = [0, x, c];
    } else if (h < 300) {
      [r, g, b] = [x, 0, c];
    } else {
      [r, g, b] = [c, 0, x];
    }

    const toHex = channel => Math.round((channel + m) * 255)
                                  .toString(16)
                                  .padStart(2, "0");
    return "#" + [r, g, b].map(toHex).join("").toUpperCase();
  }

  function updateTextPrimary(currentL) {
    const textL = currentL > 60 ? 20 : 95;
    document.documentElement.style.setProperty('--text-primary', `oklch(${textL}% 0 0)`);
  }

  function updateBaseHueFromColorPicker(event) {
    const color = event.target.value;
    const oklch = hexToOklch(color);
    const currentTheme = document.documentElement.getAttribute("data-theme") || "dark";
    const hueKey = `${currentTheme}BaseHue`;
    const chromaKey = `${currentTheme}BaseChroma`;
    const currentL = document.documentElement.getAttribute('data-theme') === 'dark' ? 30 : 80;

    document.documentElement.style.setProperty('--base-hue', oklch.h);
    document.documentElement.style.setProperty('--base-chroma', oklch.c);

    localStorage.setItem(hueKey, oklch.h);
    localStorage.setItem(chromaKey, oklch.c);

    updateTextPrimary(currentL);
  }

  function toggleConfig() {
    fetch("/luci-static/spectra/bgm/theme-switcher.php", { method: "POST" })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateButton(data.mode);
        } else {
          document.getElementById("status").innerText = "更新失败: " + data.error;
        }
      })
      .catch(error => {
        document.getElementById("status").innerText = "请求出错: " + error;
      });
  }

  function updateButton(theme) {
    const body = document.documentElement;
    const btn = document.getElementById("toggleButton");
    const status = document.getElementById("status");
    const oldTheme = body.getAttribute("data-theme") || "dark";

    localStorage.setItem(`${oldTheme}BaseHue`, parseFloat(getComputedStyle(body).getPropertyValue('--base-hue')));
    localStorage.setItem(`${oldTheme}BaseChroma`, parseFloat(getComputedStyle(body).getPropertyValue('--base-chroma')));

    const hueKey = `${theme}BaseHue`;
    const chromaKey = `${theme}BaseChroma`;
    const baseHue = parseFloat(localStorage.getItem(hueKey)) || (theme === "dark" ? 260 : 200);
    const baseChroma = parseFloat(localStorage.getItem(chromaKey)) || (theme === "dark" ? 0.03 : 0.01);

    body.style.setProperty('--base-hue', baseHue);
    body.style.setProperty('--base-chroma', baseChroma);
    body.setAttribute("data-theme", theme);

    const colorPicker = document.getElementById("colorPicker");
    colorPicker.value = oklchToHex(baseHue, baseChroma, 50);

    if (theme === "dark") {
        btn.innerHTML = `<i class="bi bi-sun"></i> ${translations['switch_to_light_mode'] || 'Switch to Light Mode'}`;
        btn.className = "btn btn-primary light";
        status.innerText = translations['current_mode_dark'] || "Current Mode: Dark Mode";
    } else {
        btn.innerHTML = `<i class="bi bi-moon"></i> ${translations['switch_to_dark_mode'] || 'Switch to Dark Mode'}`;
        btn.className = "btn btn-primary dark";
        status.innerText = translations['current_mode_light'] || "Current Mode: Light Mode";
    }

    const currentL = theme === "dark" ? 30 : 85;
    updateTextPrimary(currentL);

    localStorage.setItem("theme", theme);
  }

  function getContrastColor(hex) {
    const rgb = hexToRgb(hex);
    const luminance = (0.2126 * rgb.r + 0.7152 * rgb.g + 0.0722 * rgb.b) / 255;
    return luminance > 0.45 ? '#000000' : '#FFFFFF';
  }

  document.addEventListener("DOMContentLoaded", () => {
    const savedTheme = localStorage.getItem("theme") || "dark";
    document.documentElement.setAttribute("data-theme", savedTheme);
    const hueKey = `${savedTheme}BaseHue`;
    const chromaKey = `${savedTheme}BaseChroma`;
    const defaultHue = savedTheme === "dark" ? 260 : 200;
    const defaultChroma = savedTheme === "dark" ? 0.03 : 0.01;

    const savedHueValue = localStorage.getItem(hueKey) || defaultHue;
    const savedChromaValue = localStorage.getItem(chromaKey) || defaultChroma;

    document.documentElement.style.setProperty('--base-hue', savedHueValue);
    document.documentElement.style.setProperty('--base-chroma', savedChromaValue);

    const colorPicker = document.getElementById("colorPicker");
    colorPicker.value = oklchToHex(savedHueValue, savedChromaValue, 50);
    colorPicker.addEventListener('input', updateBaseHueFromColorPicker);

    const penIcon = document.getElementById("penIcon");
    if (penIcon) {
      penIcon.addEventListener("click", () => colorPicker.click());
    }

    fetch("/luci-static/spectra/bgm/theme-switcher.php")
      .then(res => res.json())
      .then(data => {
        if(data.mode) {
          updateButton(data.mode);
        }
      })
      .catch(error => {
        document.getElementById("status").innerText = "读取失败: " + error;
      });
  });
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
document.addEventListener("DOMContentLoaded", () => {
    const savedBodyBg = localStorage.getItem("bodyBgColor") || "#1a1a2e";
    document.body.style.background = savedBodyBg;

    const bodyBgPicker = document.getElementById("bodyBgColorPicker");
    bodyBgPicker.value = savedBodyBg;
    
    bodyBgPicker.addEventListener("input", (e) => {
        const selectedColor = e.target.value;
        document.body.style.background = selectedColor;
        localStorage.setItem("bodyBgColor", selectedColor); 
    });
});
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
        const lang = localStorage.getItem('language') || 'zh'; 
        const translations = langData[lang] || langData['en']; 

        const hours = now.getHours();
        const minutes = now.getMinutes();
        const ancientTime = getAncientTime(hours); 
        const weekDayIndex = now.getDay();
        const weekDay = translations.weekDays ? translations.weekDays[weekDayIndex] : weekDayIndex;

        const timeStr = [
            now.getHours().toString().padStart(2, '0'),
            now.getMinutes().toString().padStart(2, '0'),
            now.getSeconds().toString().padStart(2, '0')
        ].join(':');

        const timeElement = document.getElementById('timeDisplay');
        if (timeElement) {
            if (lang === 'zh' || lang === 'hk') {
                timeElement.innerHTML = `
                    <span class="ancient-time">${ancientTime}</span>
                    <span class="modern-time">${timeStr}</span>
                `;
            } else {
                timeElement.textContent = timeStr;
            }
        }

        if (minutes === 0 && now.getSeconds() === 0) {
            if (lastAnnouncedHour !== hours) {
                let announcement;
                if (lang === 'zh' || lang === 'hk') {
                    announcement = `${translations['hour_announcement'] || '整点报时，现在是北京时间'}${hours}${translations['hour_exact'] || '点整'}`;
                } else {
                    announcement = `${translations['hour_announcement_en'] || "It's"} ${hours} ${translations['hour_exact_en'] || "o'clock"}`;
                }
                speakMessage(announcement);
                lastAnnouncedHour = hours;
            }
        } else if (minutes !== 0) {
            lastAnnouncedHour = -1;  
        }

        const dateElement = document.getElementById('dateDisplay');
        if (dateElement) {
            const dateStr = `${now.getFullYear()}${translations.labels ? translations.labels.year : ' Year '}${now.getMonth() + 1}${translations.labels ? translations.labels.month : ' Month '}${now.getDate()}${translations.labels ? translations.labels.day : ' Day '}`;
            dateElement.textContent = dateStr;
        }

        const weekElement = document.getElementById('weekDisplay');
        if (weekElement) {
            if (lang === 'zh' || lang === 'hk') {
                weekElement.textContent = `${translations.labels ? translations.labels.week : '星期'}${weekDay}`;
            } else {
                weekElement.textContent = weekDay;
            }
        }

        const lunarElement = document.getElementById('lunarDisplay');
        if ((lang === 'zh' || lang === 'hk') && lunarElement) {
            const lunar = getLunar(now);
            lunarElement.textContent = `${lunar.year} ${lunar.month}${lunar.day} ${lunar.zodiac}年`;
        } else if (lunarElement) {
            lunarElement.textContent = ''; 
        }

        if (now.getHours() === 0 && now.getMinutes() === 0 && now.getSeconds() === 0) {
            setTimeout(() => location.reload(), 1000);
        }

    } catch (error) {
        console.error('时间更新失败:', error);

        const dateElement = document.getElementById('dateDisplay');
        if (dateElement) {
            dateElement.textContent = translations['error_loading_time'] || 'Error loading time';
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

const elements = document.querySelectorAll('.time-display span');
const currentSong = document.querySelector('#currentSong');

let usedColors = [];

function getColorListFromTheme() {
    const styles = getComputedStyle(document.documentElement);
    const lightness = styles.getPropertyValue('--l').trim();
    const chroma = styles.getPropertyValue('--c').trim();

    const colors = [];
    for (let i = 1; i <= 7; i++) {
        const hue = styles.getPropertyValue(`--base-hue-${i}`).trim();
        const color = `oklch(${lightness} ${chroma} ${hue})`;
        colors.push(color);
    }
    return colors;
}

function getNextColor(colorList) {
    if (usedColors.length === colorList.length) {
        usedColors = [];
    }

    const remaining = colorList.filter(c => !usedColors.includes(c));
    const next = remaining[Math.floor(Math.random() * remaining.length)];
    usedColors.push(next);
    return next;
}

function rotateColors() {
    const colorList = getColorListFromTheme();

    elements.forEach(el => {
        el.style.color = getNextColor(colorList);
    });

    if (currentSong) {
        currentSong.style.color = getNextColor(colorList);
    }
}

setInterval(rotateColors, 4000);
</script>

<style>
:root {
    --primary-color: var(--accent-color);
    --secondary-color: var(--btn-primary-bg);
    --background: var(--bg-body);
    --text-color: var(--text-primary);
    --glass-blur: blur(20px);
    --radius: 20px;
}

body {
    margin: 0;
    background: linear-gradient(145deg, var(--bg-body), var(--bg-container));
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
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.lyric-line {
    opacity: 1 !important;
    color: var(--text-primary) !important; 
    font-size: 1.1rem;
    transition: all 0.3s ease;
    transition: color 0.3s; 
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
    color: var(--text-primary) !important; 
    font-size: 1.3rem;
}

.lyric-line.highlight .char {
    transition: all 0.1s ease;  
}

.lyric-line.highlight .char.active {
    opacity: 1;
    transform: scale(1.3);
    background: linear-gradient(
        90deg,
        oklch(65% 0.25 15) 0%, 
        oklch(70% 0.25 50) 25%,
        oklch(75% 0.25 85) 50%, 
        oklch(70% 0.25 135) 75%,
        oklch(65% 0.25 240) 100%
    );
    background-size: 200% auto;
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent !important;
    animation: color-flow 1s linear infinite;
        0 0 10px rgba(255,51,102,0.5),
        0 0 15px rgba(102,255,51,0.5),
        0 0 20px rgba(51,204,255,0.5);
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

.char.space {
    display: inline;
    min-width: 0.5em; 
}

.progress-container {
    width: 100%;
    height: 6px;
    background: var(--border-color);
    border-radius: 4px;
    margin: 16px 0;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: var(--accent-color);
    transition: width 0.2s ease;
}

.controls {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 15px;
}

.control-btn, #volumeToggle {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    color: var(--text-color);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.control-btn:hover, #volumeToggle:hover {
    background: var(--item-hover-bg);
    transform: scale(1.1);
}

#playPauseBtn {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
    background: var(--accent-color);
    color: var(--text-primary);
    box-shadow: 0 4px 20px rgba(var(--accent-color), 0.3);
}

.playlist {
    margin-top: 20px;
    max-height: 380px;
    overflow-y: auto;
    padding: 10px;
    border-radius: var(--radius);
    background: var(--card-bg);
    border: 1px solid var(--border-color);
}

.playlist-item {
    padding: 10px 14px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.95rem;
}

.playlist-item:hover {
    background: var(--item-hover-bg);
    font-weight: bold;
}

.playlist-item.active {
    background: var(--color-accent);
    color: white;
    font-weight: bold;
}

#floatingLyrics {
    position: fixed;
    top: 2%;
    right: 4.5%;
    background: var(--bg-body);
    padding: 15px 10px;
    border-radius: 20px;
    backdrop-filter: var(--glass-blur);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: auto;
    writing-mode: vertical-rl;
    text-orientation: mixed;
    line-height: 2;
    display: flex;
    flex-direction: column; 
    gap: 0.5em;
}

#floatingLyrics.visible {
    opacity: 1;
}

#floatingLyrics #currentSong.vertical-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--accent-color);
    writing-mode: vertical-rl;
    padding-right: 0.5em;
    margin-right: 0.5em;
    text-shadow: 0px 1px 2px rgba(0, 0, 0, 0.3), 
                 0px -1px 2px rgba(255, 255, 255, 0.4);
}

#floatingLyrics .vertical-lyrics {
    writing-mode: vertical-rl;
    text-combine-upright: all;
}

#floatingLyrics .char {
    font-size: 1.6rem; 
    transition: transform 0.3s ease;
    display: inline-block; 
    position: relative;
}

.floating-controls {
    display: flex;
    flex-direction: row; 
    gap: 0.8em;
    margin-bottom: 1em;
    order: -1; 
}

.ctrl-btn {
    background: var(--bg-body);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
}

.ctrl-btn:hover {
    background: var(--item-hover-bg);
    transform: scale(1.1);
}

.ctrl-btn i {
    font-size: 1.2rem;
}

.ctrl-btn.clicked {
    transform: scale(0.9);
    background: rgba(50, 205, 50, 0.5);
}

#currentSong.vertical-title {
    margin-top: 0.5em;
    border-right: none;
    padding-right: 0;
    padding-bottom: 0.8em;
    margin-right: 0;
    writing-mode: horizontal-tb; 
}

.vertical-lyrics {
    margin-top: 0.5em;
}

.char {
    transition: all 0.3s ease;
}

#floatingLyrics .char.active {
    color: var(--accent-color);
    animation: bounce-scale 0.6s ease-out;
    transform: scale(1.3);
    position: relative;
    text-shadow: none !important; 
}

@keyframes bounce-scale {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.3); 
    }
    70% {
        transform: scale(1.1); 
    }
    100% {
        transform: scale(1); 
    }
}

.char.played {

    transform: scale(1) !important;
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
    color: var(--text-secondary);
}

.progress-container {
    cursor: pointer; 
}

.lyrics-loading {
    position: relative;
    min-height: 100px;
}

#no-lyrics {
    text-align: center;
    color: var(--text-secondary);
    padding: 2rem;
    font-size: 1.8em;
}

#noLyricsFloating {
    width: min-content; 
    max-width: 4em;
    text-align: center;
    color: var(--text-secondary);
    line-height: 1.2;
    font-size: 1.5rem;
    padding: 10px 2px;
    letter-spacing: 0.2em;
}

@keyframes glow {
    0% { opacity: 0.8; }
    50% { opacity: 1; }
    100% { opacity: 0.8; }
}

.progress-bar {
    height: 100%;
    background: var(--btn-success-bg);
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
    font-weight: bold !important;
    color: var(--accent-color);
    text-shadow: 0px 1px 2px rgba(0, 0, 0, 0.3), 
                 0px -1px 2px rgba(255, 255, 255, 0.4);
}

#volumePanel {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    margin-bottom: 10px;
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 10px;
    width: 160px;
    box-shadow: 0 4px 10px var(--border-color);
    z-index: 1000;
    display: none;
}

#volumeSlider {
    width: 100%;
    accent-color: var(--text-color);
}

#volumeLabel {
    color: var(--text-color);
    font-size: 0.9rem;
    text-align: right;
    margin-top: 5px;
}

.heart {
    position: absolute;
    font-size: 2rem; 
    color: #ff69b4;
    pointer-events: none;
    opacity: 0;
    z-index: 9999;
    animation: heartAnimation 1s ease-in-out forwards;
}

@keyframes heartAnimation {
    0% {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
    50% {
        transform: scale(1.5) translateY(-50px); 
        opacity: 1;
    }
    100% {
        transform: scale(0) translateY(-100px); 
        opacity: 0;
    }
}

.list-group-item {
    cursor: pointer;
    color: var(--text-primary);
    background: var(--bg-container);
    border: 1px solid var(--border-color);
    transition: background 0.3s ease;
}

.list-group-item:hover {
    background: var(--item-hover-bg);
}

.list-group-item.active {
    background: var(--accent-color);
    color: white;
    border: 1px solid var(--accent-color);
}

.list-group-item.active .badge,
.list-group-item.active .text-truncate,
.list-group-item.active small,
.list-group-item.active i {
    color: white !important;
}

.list-group-item .delete-item {
    cursor: pointer;
}

.modal-xl {
    max-width: 60% !important;  
    width: 90% !important;
}

@media (max-width: 768px) {
    .modal-xl {
        max-width: 95% !important;
        width: 95% !important;
        margin: 1rem auto !important; 
    }
}

@media (max-width: 576px) {
    .modal-xl {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0.5rem auto !important;
    }
}
</style>

<script>
function toggleFloating() {
    const floating = document.getElementById('floatingLyrics');
    const icon = document.getElementById('floatingIcon');
    const isVisible = floating.classList.toggle('visible');
    icon.className = isVisible ? 'bi bi-display-fill' : 'bi bi-display';
    localStorage.setItem('floatingLyricsVisible', isVisible);
}

window.addEventListener('DOMContentLoaded', () => {
    const floating = document.getElementById('floatingLyrics');
    const icon = document.getElementById('floatingIcon');
    const saved = localStorage.getItem('floatingLyricsVisible') === 'true';

    if (saved) {
        floating.classList.add('visible');
        icon.className = 'bi bi-display-fill';
    } else {
        icon.className = 'bi bi-display';
    }
});
</script>

<script>
const audioPlayer = new Audio();
let songs = JSON.parse(localStorage.getItem('cachedPlaylist') || '[]');
let currentTrackIndex = JSON.parse(localStorage.getItem('currentTrackIndex') || '0');
let isPlaying = JSON.parse(localStorage.getItem('isPlaying') || 'false');
let repeatMode = JSON.parse(localStorage.getItem('repeatMode') || '0');
let isHovering = false;
let isManualScroll = false;
let isSmallScreen = window.innerWidth < 768;

const logBox = document.createElement('div');
logBox.style.position = 'fixed';
logBox.style.top = '90%';
logBox.style.left = '20px';
logBox.style.padding = '10px';
logBox.style.backgroundColor = 'green';
logBox.style.color = 'white';
logBox.style.borderRadius = '5px';
logBox.style.zIndex = '9999';
logBox.style.maxWidth = '250px';
logBox.style.fontSize = '14px';
logBox.style.display = 'none';
logBox.style.maxWidth = '300px';
logBox.style.wordWrap = 'break-word';
document.body.appendChild(logBox);

function showLogMessage(message) {
    const decodedMessage = decodeURIComponent(message);
    logBox.textContent = decodedMessage;
    logBox.style.display = 'block';
    logBox.style.animation = 'scrollUp 8s ease-out forwards';
    logBox.style.width = 'auto';
    logBox.style.maxWidth = '300px';

    setTimeout(() => {
        logBox.style.display = 'none';
    }, 8000);
}

const styleSheet = document.createElement('style');
styleSheet.innerHTML = `
    @keyframes scrollUp {
        0% {
            top: 90%;
        }
        100% {
            top: 50%;
        }
    }
`;
document.head.appendChild(styleSheet);

function speakMessage(message) {
    const utterance = new SpeechSynthesisUtterance(message);
    utterance.lang = currentLang;  
    speechSynthesis.speak(utterance);
}

function togglePlay() {
    if (isPlaying) {
        audioPlayer.pause();
        const pauseMessage = translations['pause_playing'] || 'Pause_Playing';
        showLogMessage(pauseMessage);
        speakMessage(pauseMessage);
    } else {
        audioPlayer.play();
        const playMessage = translations['start_playing'] || 'Start_Playing';
        showLogMessage(playMessage);
        speakMessage(playMessage);
    }
    isPlaying = !isPlaying;
    updatePlayButton();
    savePlayerState();

    const btn = event.target.closest('button');
    if(btn) {
        btn.classList.add('clicked');
        setTimeout(() => btn.classList.remove('clicked'), 200);
    }
}

function updatePlayButton() {
    const btn = document.getElementById('playPauseBtn');
    const floatingBtn = document.getElementById('floatingPlayBtn');
    const icon = isPlaying ? 'bi-pause-fill' : 'bi-play-fill';
    
    btn.innerHTML = `<i class="bi ${icon}"></i>`;
    floatingBtn.innerHTML = `<i class="bi ${icon}"></i>`;
}

function changeTrack(direction) {
    const isManual = event && event.type === 'click'; 
    const oldSong = songs[currentTrackIndex];
    
    if (repeatMode === 2 && !isManual) { 
        currentTrackIndex = Math.floor(Math.random() * songs.length);
    } else {
        currentTrackIndex = (currentTrackIndex + direction + songs.length) % songs.length;
    }

    const songName = decodeURIComponent(
        songs[currentTrackIndex].split('/').pop().replace(/\.\w+$/, '')
    );

    if (isManual) {
        const action = direction === -1
            ? translations['previous_track'] || 'Previous Track'
            : translations['next_track'] || 'Next Track';
        showLogMessage(`${translations['manual_switch'] || 'Manual Switch'}${action}：${songName}`);
        speakMessage(`${translations['switch_to'] || 'Switch to'}${action}：${songName}`);
    } else {
        showLogMessage(`${translations['auto_switch'] || 'Auto Switch to'}：${songName}`);
        speakMessage(`${translations['auto_play'] || 'Auto Play'}：${songName}`);
    }

    loadTrack(songs[currentTrackIndex]);
}

function toggleRepeat() {
    repeatMode = (repeatMode + 1) % 3;
    const mainBtn = document.getElementById('repeatBtn');
    const floatingBtn = document.getElementById('floatingRepeatBtn');

    [mainBtn, floatingBtn].forEach(btn => {
        btn.classList.remove('btn-success', 'btn-warning');
        btn.title = [
            translations['order_play'] || 'Order_Play',
            translations['single_loop'] || 'Single_Loop',
            translations['shuffle_play'] || 'Shuffle_Play'
        ][repeatMode];

        switch (repeatMode) {
            case 0:
                btn.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
                break;
            case 1:
                btn.innerHTML = '<i class="bi bi-repeat-1"></i>';
                btn.classList.add('btn-success');
                break;
            case 2:
                btn.innerHTML = '<i class="bi bi-shuffle"></i>';
                btn.classList.add('btn-warning');
                break;
        }
    });

    showLogMessage([
        translations['order_play'] || 'Order_Play',
        translations['single_loop'] || 'Single_Loop',
        translations['shuffle_play'] || 'Shuffle_Play'
    ][repeatMode]);
    speakMessage([
        translations['order_play'] || 'Order_Play',
        translations['single_loop'] || 'Single_Loop',
        translations['shuffle_play'] || 'Shuffle_Play'
    ][repeatMode]);
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
    showLogMessage(`播放列表已加载：${songs.length} 首歌曲`);
    setTimeout(() => scrollToCurrentTrack(), 100);
}

function playTrack(index) {
    const songName = decodeURIComponent(songs[index].split('/').pop().replace(/\.\w+$/, ''));
    showLogMessage(
        `${translations['playlist_click'] || 'Playlist Click'}：${translations['index'] || 'Index'}：${index + 1}，${translations['song_name'] || 'Song Name'}：${songName}`
    );
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
    
    window.lyrics = {};
    window.lyricTimes = [];
    
    const containers = [
        document.getElementById('lyricsContainer'),
        document.querySelector('#floatingLyrics .vertical-lyrics')
    ];
    
    containers.forEach(container => {
        const statusMsg = container.id === 'lyricsContainer' 
            ? `<div id="no-lyrics">${translations['loading_lyrics'] || 'Loading Lyrics...'}</div>`
            : `<div id="noLyricsFloating">${translations['loading_lyrics'] || 'Loading Lyrics...'}</div>`;
        container.innerHTML = statusMsg;
    });

    fetch(lyricsUrl)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.arrayBuffer();
        })
        .then(buffer => {
            const decoder = new TextDecoder('utf-8');
            parseLyrics(decoder.decode(buffer));
            displayLyrics();
            document.dispatchEvent(new Event('lyricsLoaded'));
        })
        .catch(error => {
            console.error(`${translations['lyrics_load_failed'] || 'Lyrics Load Failed'}:`, error);
            containers.forEach(container => {
                const errorMsg = container.id === 'lyricsContainer'
                    ? `<div id="no-lyrics">${translations['no_lyrics'] || 'No Lyrics Available'}</div>`
                    : `<div id="noLyricsFloating">${translations['no_lyrics'] || 'No Lyrics Available'}</div>`;
                container.innerHTML = errorMsg;
            });
        });
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
    const floatingLyrics = document.querySelector('#floatingLyrics .vertical-lyrics');
    
    lyricsContainer.innerHTML = '';
    floatingLyrics.innerHTML = '';

    if (Object.keys(window.lyrics).length === 0) {
        lyricsContainer.innerHTML = `<div id="no-lyrics">${translations['no_lyrics'] || 'No Lyrics Available'}</div>`;
        floatingLyrics.innerHTML = `<div id="noLyricsFloating">${translations['no_lyrics'] || 'No Lyrics Available'}</div>`;
        return;
    }

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

    lines.forEach(line => {
    line.classList.remove('highlight', 'played');
    line.style.color = 'white'; 
});

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
            } else if (currentTime > end && !char.classList.contains('played')) {
                char.classList.add('played');
                spawnHeartAbove(char); 
            }
        });

        const floatingContainer = document.getElementById('floatingLyrics');
        const floatingLyrics = floatingContainer.querySelector('.vertical-lyrics');
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

function spawnHeartAbove(char) {
    const heart = document.createElement('span');
    heart.className = 'heart';
    heart.textContent = '💖';

    const rect = char.getBoundingClientRect();
    const offsetTop = rect.top + window.scrollY;
    const offsetLeft = rect.left + window.scrollX;

    heart.style.left = `${offsetLeft + char.offsetWidth / 2}px`;
    heart.style.top = `${offsetTop - 30}px`; 

    document.body.appendChild(heart);

    requestAnimationFrame(() => {
        heart.classList.add('pop');
    });

    setTimeout(() => {
        heart.remove(); 
    }, 1000);
}

function loadTrack(url) {
    window.lyrics = {};
    window.lyricTimes = [];
    
    const lyricsContainers = [
        document.getElementById('lyricsContainer'),
        document.querySelector('#floatingLyrics .vertical-lyrics')
    ];
    
    lyricsContainers.forEach(container => {
        container.innerHTML = `<div class="no-lyrics">${translations['loading_lyrics'] || 'Loading Lyrics...'}</div>`;
    });

    audioPlayer.src = url;
    updatePlayButton(); 
    updatePlaylistUI();
    loadLyrics(url);
    updateCurrentSong(url);
    updateTimeDisplay();
    
    if (isPlaying) {
        audioPlayer.play().catch((error) => {
            console.log(`${translations['autoplay_blocked'] || 'Autoplay Blocked'}:`, error);
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
            console.log(`${translations['autoplay_blocked'] || 'Autoplay Blocked'}:`, error);
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
    
    const floatingTitle = document.querySelector('#floatingLyrics #currentSong');
    if (floatingTitle) floatingTitle.textContent = songName;

    const modalTitle = document.querySelector('#musicModal #currentSong');
    if (modalTitle) modalTitle.textContent = songName;
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
    const mainBtn = document.getElementById('repeatBtn');
    const floatingBtn = document.getElementById('floatingRepeatBtn');
    
    [mainBtn, floatingBtn].forEach(btn => {
        btn.classList.remove('btn-success', 'btn-warning');
        btn.title = [
            translations['order_play'] || 'Order_Play',
            translations['single_loop'] || 'Single_Loop',
            translations['shuffle_play'] || 'Shuffle_Play'
        ][repeatMode];
        
        switch(repeatMode) {
            case 1:
                btn.classList.add('btn-success'); 
                btn.innerHTML = '<i class="bi bi-repeat-1"></i>';
                break;
            case 2:
                btn.classList.add('btn-warning'); 
                btn.innerHTML = '<i class="bi bi-shuffle"></i>';
                break;
            default:
                btn.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
        }
    });
}

function loadDefaultPlaylist() {
    fetch('<?php echo $new_url; ?>')
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
            activeItem.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });
            activeItem.classList.toggle('blink', !isPlaying);
        }
    }, 300);
}

loadDefaultPlaylist();
window.addEventListener('resize', () => {
    isSmallScreen = window.innerWidth < 768;
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const logMessages = document.querySelectorAll('#log-message');

    logMessages.forEach(message => {
        setTimeout(() => {
            message.style.transition = "opacity 0.5s ease-out";  
            message.style.opacity = '0';  
            setTimeout(() => {
                message.remove();  
            }, 500); 
        }, 4000); 
    });
});
</script>

<script>
const volumeSlider = document.getElementById('volumeSlider');
const volumeToggle = document.getElementById('volumeToggle');
const volumePanel = document.getElementById('volumePanel');
let lastVolume = 1;

const savedVolume = localStorage.getItem('audioVolume');
if (savedVolume !== null) {
    lastVolume = parseFloat(savedVolume);
    audioPlayer.volume = lastVolume;
    volumeSlider.value = lastVolume;
}

volumeToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    const isVisible = volumePanel.classList.contains('show');
    if (isVisible) {
        volumePanel.classList.remove('show');
        setTimeout(() => volumePanel.style.display = 'none', 200);
    } else {
        volumePanel.style.display = 'block';
        setTimeout(() => volumePanel.classList.add('show'), 10); 
    }
});

document.addEventListener('click', () => {
    if (volumePanel.classList.contains('show')) {
        volumePanel.classList.remove('show');
        setTimeout(() => volumePanel.style.display = 'none', 200);
    }
});

volumeSlider.addEventListener('input', (e) => {
    const value = parseFloat(e.target.value);
    audioPlayer.volume = value;

    if (audioPlayer.muted) {
        audioPlayer.muted = false;
    }

    localStorage.setItem('audioVolume', value);

    updateVolumeIcon();
});

function updateVolumeIcon() {
    const icon = volumeToggle.querySelector('i');
    if (audioPlayer.muted || audioPlayer.volume === 0) {
        icon.className = 'bi bi-volume-mute-fill';
    } else if (audioPlayer.volume < 0.5) {
        icon.className = 'bi bi-volume-down-fill';
    } else {
        icon.className = 'bi bi-volume-up-fill';
    }

    if (!audioPlayer.muted) {
        lastVolume = audioPlayer.volume;
    }
}

audioPlayer.volume = lastVolume;
updateVolumeIcon();
</script>

<script>
const notificationMessage = 'Cache Cleared';
const speechMessage = 'Cache Cleared';

document.addEventListener('keydown', function(event) {
    if (event.ctrlKey && event.shiftKey && event.code === 'KeyC') {
        clearCache();
        event.preventDefault();  
    }
});

document.getElementById('clear-cache-btn').addEventListener('click', function() {
    clearCache();
});

function clearCache() {
    location.reload(true); 
    localStorage.clear();
    sessionStorage.clear();
    sessionStorage.setItem('cacheCleared', 'true');
    showLogMessage(notificationMessage);
    speakMessage(speechMessage);
}

window.addEventListener('load', function() {
    if (sessionStorage.getItem('cacheCleared') === 'true') {
        showLogMessage(notificationMessage);
        speakMessage(speechMessage);
        sessionStorage.removeItem('cacheCleared'); 
    }
});

function speakMessage(text) {
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(text);
        speechSynthesis.speak(utterance);
    }
}
</script>

<script>
document.addEventListener('keydown', function(event) {
    if (event.ctrlKey && event.shiftKey && event.key === 'V') {
        var urlModal = new bootstrap.Modal(document.getElementById('urlModal'));
        urlModal.show();
        speakMessage(translations['open_custom_playlist'] || 'Open Custom Playlist');
    }
});

document.getElementById('resetButton').addEventListener('click', function() {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'reset_default=true'
    })
    .then(response => response.text())  
    .then(data => {
        var urlModal = bootstrap.Modal.getInstance(document.getElementById('urlModal'));
        urlModal.hide();

        document.getElementById('new_url').value = '<?php echo $default_url; ?>';

        showNotification(translations['reset_default_playlist'] || 'Default Playlist Link Restored');
    })
    .catch(error => {
        console.error(translations['reset_default_error'] || 'Error Restoring Default Link:', error);
        showNotification(translations['reset_default_failed'] || 'Failed to Restore Default Link');
    });
});

function showNotification(message) {
    var notification = document.createElement('div');
    notification.style.position = 'fixed';
    notification.style.top = '10px';
    notification.style.right = '30px';
    notification.style.backgroundColor = '#4CAF50';
    notification.style.color = '#fff';
    notification.style.padding = '10px';
    notification.style.borderRadius = '5px';
    notification.style.zIndex = '9999';
    notification.innerText = message;

    document.body.appendChild(notification);

    setTimeout(function() {
        notification.style.display = 'none';
    }, 5000); 
}

function loadNewPlaylist(url) {
    const playlistContainer = document.getElementById('playlist');
    playlistContainer.innerHTML = '';  

    fetch(url)
        .then(response => response.json())  
        .then(data => {
            data.forEach(item => {
                const songElement = document.createElement('div');
                songElement.textContent = item.name; 
                playlistContainer.appendChild(songElement);
            });
        })
        .catch(error => {
            console.error(translations['playlist_load_failed'] || 'Failed to Load Playlist:', error);
            showNotification(translations['playlist_load_failed_message'] || 'Failed to Load Playlist');
        });
}

document.getElementById('urlModal').addEventListener('hidden.bs.modal', function() {
    const newUrl = document.getElementById('new_url').value;
    loadNewPlaylist(newUrl);
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("fontToggleBtn");
  const body = document.body;
  const storageKey = "fontToggle";

  if (localStorage.getItem(storageKey) === "default") {
    body.classList.add("default-font");
  }

  btn.addEventListener("click", () => {
    const isDefault = body.classList.toggle("default-font");
    localStorage.setItem(storageKey, isDefault ? "default" : "fredoka");
  });
});
</script>

<html lang="<?php echo $currentLang; ?>">
<div class="modal fade" id="langModal" tabindex="-1" aria-labelledby="langModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="langModalLabel" data-translate="select_language">Select Language</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <select id="langSelect" class="form-select" onchange="changeLanguage(this.value)">
                    <option value="zh" data-translate="simplified_chinese">Simplified Chinese</option>
                    <option value="hk" data-translate="traditional_chinese">Traditional Chinese</option>
                    <option value="en" data-translate="english">English</option>
                    <option value="kr" data-translate="korean">Korean</option>
                    <option value="vn" data-translate="vietnamese">Vietnamese</option>
                    <option value="th" data-translate="thailand">Thailand</option>
                    <option value="jp" data-translate="japanese"></option>
                    <option value="ru" data-translate="russian"></option>
                    <option value="de" data-translate="germany">Germany</option>
                    <option value="fr" data-translate="france">France</option>
                    <option value="ar" data-translate="arabic"></option>
                    <option value="es" data-translate="spanish">spanish</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="close">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
$langFilePath = __DIR__ . '/language.txt';
$defaultLang = 'en';

$langData = [
    'zh' => [
        'select_language'        => '选择语言',
        'simplified_chinese'     => '简体中文',
        'traditional_chinese'    => '繁體中文',
        'english'                => '英文',
        'korean'                 => '韩语',
        'vietnamese'             => '越南语',
        'thailand'             => '泰语',
        'japanese'               => '日语',
        'russian'                => '俄语',
        'germany'                => '德语',
        'france'                 => '法语',
        'arabic'                 => '阿拉伯语',
        'spanish'                => '西班牙语',
        'close'                  => '关闭',
        'save'                   => '保存',
        'theme_download'         => '主题下载',
        'select_all'             => '全选',
        'batch_delete'           => '批量删除选中文件',
        'total'                  => '总共：',
        'free'                   => '剩余：',
        'hover_to_preview'       => '点击激活悬停播放',
        'mount_info'             => '挂载点：{{mount}}｜已用空间：{{used}}',
        'spectra_config'         => 'Spectra 配置管理',
        'current_mode'           => '当前模式: 加载中...',
        'toggle_mode'            => '切换模式',
        'check_update'           => '检查更新',
        'batch_upload'           => '选择文件进行批量上传',
        'add_to_playlist'        => '勾选添加到播放列表',
        'clear_background'       => '清除背景',
        'clear_background_label' => '清除背景',
        'file_list'              => '文件列表',
        'component_bg_color'     => '选择组件背景色',
        'page_bg_color'          => '选择页面背景色',
        'toggle_font'            => '切换字体',
        'filename'               => '名称：',
        'filesize'               => '大小：',
        'duration'               => '时长：',
        'resolution'             => '分辨率：',
        'bitrate'                => '比特率：',
        'type'                   => '类型：',
        'image'                  => '图片',
        'video'                  => '视频',
        'audio'                  => '音频',
        'document'               => '文档',
        'delete'                 => '删除',
        'rename'                 => '重命名',
        'download'               => '下载',
        'set_background'         => '设置背景',
        'preview'                => '预览',
        'toggle_fullscreen'      => '切换全屏',
        'supported_formats'      => '支持格式：[ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => '拖放文件到这里',
        'or'                     => '或',
        'select_files'           => '选择文件',
        'unlock_php_upload_limit'=> '解锁 PHP 上传限制',
        'upload'                 => '上传',
        'cancel'                 => '取消',
        'rename_file'            => '重命名',
        'new_filename'           => '新文件名',
        'invalid_filename_chars' => '文件名不能包含以下字符：\\/：*?"<>|',
        'confirm'                => '确认',
        'media_player'           => '媒体播放器',
        'playlist'               => '播放列表',
        'clear_list'             => '清除列表',
        'toggle_list'            => '隐藏列表',
        'picture_in_picture'     => '画中画',
        'fullscreen'             => '全屏',
        'music_player'           => '音乐播放器',
        'play_pause'             => '播放/暂停',
        'previous_track'         => '上一首',
        'next_track'             => '下一首',
        'repeat_mode'            => '顺序播放',
        'toggle_floating_lyrics' => '桌面歌词',
        'clear_config'           => '清除配置',
        'custom_playlist'        => '自定义播放列表',
        'volume'                 => '音量',
        'update_playlist'        => '更新播放列表',
        'playlist_url'           => '播放列表地址',
        'reset_default'          => '恢复默认',
        'toggle_lyrics'          => '关闭歌词',
        'fetching_version'       => '正在获取版本信息...',
        'download_local'         => '下载到本地',
        'change_language'        => '更改语言',
        'pause_playing'          => '暂停播放',
        'start_playing'          => '开始播放',
        'manual_switch'          => '手动切换',
        'auto_switch'            => '自动切换到',
        'switch_to'              => '切换到',
        'auto_play'              => '自动播放',
        'lyrics_load_failed'     => '歌词加载失败',
        'order_play'             => '顺序播放',
        'single_loop'            => '单曲循环',
        'shuffle_play'           => '随机播放',
        'playlist_click'         => '播放列表点击',
        'index'                  => '索引',
        'song_name'              => '歌曲名称',
        'no_lyrics'              => '暂无歌词',
        'loading_lyrics'         => '歌词加载中...',
        'autoplay_blocked'       => '自动播放被阻止',
        'cache_cleared'               => '配置已清除',
        'open_custom_playlist'        => '打开自定义播放列表',
        'reset_default_playlist'      => '已恢复默认播放列表链接',
        'reset_default_error'         => '恢复默认链接时出错',
        'reset_default_failed'        => '恢复默认链接失败',
        'playlist_load_failed'        => '加载歌单失败',
        'playlist_load_failed_message'=> '加载歌单失败',
        'hour_announcement'      => '整点报时，现在是北京时间',  
        'hour_exact'             => '点整',
        'weekDays' => ['日', '一', '二', '三', '四', '五', '六'],
        'labels' => [
            'year' => '年',
            'month' => '月',
            'day' => '号',
            'week' => '星期'
        ],
        'hour_announcement' => '整点报时，现在是北京时间',
        'hour_exact' => '点整',
        'error_loading_time' => '时间显示异常',
        'switch_to_light_mode' => '切换到亮色模式',
        'switch_to_dark_mode' => '切换到暗色模式',
        'current_mode_dark' => '当前模式: 暗色模式',
        'current_mode_light' => '当前模式: 亮色模式',
        'fetching_version' => '正在获取版本信息...',
        'latest_version' => '最新版本：',
        'unable_to_fetch_version' => '无法获取最新版本信息',
        'request_failed' => '请求失败，请稍后再试',
        'pip_not_supported' => '当前媒体不支持画中画',
        'pip_operation_failed' => '画中画操作失败',
        'exit_picture_in_picture' => '退出画中画',
        'picture_in_picture' => '画中画',
        'hide_playlist' => '隐藏列表',
        'show_playlist' => '显示列表',
        'enter_fullscreen' => '进入全屏',
        'exit_fullscreen' => '退出全屏',
        'confirm_update_php' => '您确定要更新 PHP 配置吗？',
        'select_files_to_delete' => '请先选择要删除的文件！',
        'confirm_batch_delete' => '确定要删除选中的 %d 个文件吗？',
        'selected_info' => '已选择 %d 个文件，合计 %s MB'
    ],

    'hk' => [
        'select_language'        => '選擇語言',
        'simplified_chinese'     => '簡體中文',
        'traditional_chinese'    => '繁體中文',
        'english'                => '英文',
        'korean'                 => '韓語',
        'vietnamese'             => '越南語',
        'thailand'            => '泰語',
        'japanese'               => '日語',
        'russian'                => '俄語',
        'germany'                => '德語',
        'france'                 => '法語',
        'arabic'                 => '阿拉伯語',
        'spanish'                => '西班牙語',
        'close'                  => '關閉',
        'save'                   => '保存',
        'theme_download'         => '主題下載',
        'select_all'             => '全選',
        'batch_delete'           => '批量刪除選中文件',
        'total'                  => '總共：',
        'free'                   => '剩餘：',
        'hover_to_preview'       => '點擊激活懸停播放',
        'mount_info'             => '掛載點：{{mount}}｜已用空間：{{used}}',
        'spectra_config'         => 'Spectra 配置管理',
        'current_mode'           => '當前模式: 加載中...',
        'toggle_mode'            => '切換模式',
        'check_update'           => '檢查更新',
        'batch_upload'           => '選擇文件進行批量上傳',
        'add_to_playlist'        => '勾選添加到播放列表',
        'clear_background'       => '清除背景',
        'clear_background_label' => '清除背景',
        'file_list'              => '文件列表',
        'component_bg_color'     => '選擇組件背景色',
        'page_bg_color'          => '選擇頁面背景色',
        'toggle_font'            => '切換字體',
        'filename'               => '名稱：',
        'filesize'               => '大小：',
        'duration'               => '時長：',
        'resolution'             => '分辨率：',
        'bitrate'                => '比特率：',
        'type'                   => '類型：',
        'image'                  => '圖片',
        'video'                  => '視頻',
        'audio'                  => '音頻',
        'document'               => '文檔',
        'delete'                 => '刪除',
        'rename'                 => '重命名',
        'download'               => '下載',
        'set_background'         => '設置背景',
        'preview'                => '預覽',
        'toggle_fullscreen'      => '切換全屏',
        'supported_formats'      => '支持格式：[ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => '拖放文件到這裡',
        'or'                     => '或',
        'select_files'           => '選擇文件',
        'unlock_php_upload_limit'=> '解鎖 PHP 上傳限制',
        'upload'                 => '上傳',
        'cancel'                 => '取消',
        'rename_file'            => '重命名',
        'new_filename'           => '新文件名',
        'invalid_filename_chars' => '文件名不能包含以下字符：\\/：*?"<>|',
        'confirm'                => '確認',
        'media_player'           => '媒體播放器',
        'playlist'               => '播放列表',
        'clear_list'             => '清除列表',
        'toggle_list'            => '隱藏列表',
        'picture_in_picture'     => '畫中畫',
        'fullscreen'             => '全屏',
        'music_player'           => '音樂播放器',
        'play_pause'             => '播放/暫停',
        'previous_track'         => '上一首',
        'next_track'             => '下一首',
        'repeat_mode'            => '順序播放',
        'toggle_floating_lyrics' => '桌面歌詞',
        'clear_config'           => '清除配置',
        'custom_playlist'        => '自定義播放列表',
        'volume'                 => '音量',
        'update_playlist'        => '更新播放列表',
        'playlist_url'           => '播放列表地址',
        'reset_default'          => '恢復默認',
        'toggle_lyrics'          => '關閉歌詞',
        'fetching_version'       => '正在獲取版本信息...',
        'download_local'         => '下載到本地',
        'change_language'        => '更改語言',
        'pause_playing'          => '暫停播放',
        'start_playing'          => '開始播放',
        'manual_switch'          => '手動切換',
        'auto_switch'            => '自動切換到',
        'switch_to'              => '切換到',
        'auto_play'              => '自動播放',
        'lyrics_load_failed'     => '歌詞加載失敗',
        'order_play'             => '順序播放',
        'single_loop'            => '單曲循環',
        'shuffle_play'           => '隨機播放',
        'playlist_click'         => '播放列表點擊',
        'index'                  => '索引',
        'song_name'              => '歌曲名稱',
        'no_lyrics'              => '暫無歌詞',
        'loading_lyrics'         => '歌詞加載中...',
        'autoplay_blocked'       => '自動播放被阻止',
        'cache_cleared'               => '配置已清除',
        'open_custom_playlist'        => '打開自定義播放列表',
        'reset_default_playlist'      => '已恢復默認播放列表鏈接',
        'reset_default_error'         => '恢復默認鏈接時出錯',
        'reset_default_failed'        => '恢復默認鏈接失敗',
        'playlist_load_failed'        => '加載歌單失敗',
        'playlist_load_failed_message'=> '加載歌單失敗',
        'hour_announcement'      => '整點報時，現在是北京時間',  
        'hour_exact'             => '點整',
        'weekDays' => ['日', '一', '二', '三', '四', '五', '六'],
        'labels' => [
            'year' => '年',
            'month' => '月',
            'day' => '號',
            'week' => '星期'
        ],
        'hour_announcement' => '整點報時，現在是北京時間',
        'hour_exact' => '點整',
        'error_loading_time' => '時間顯示異常',
        'switch_to_light_mode' => '切換到亮色模式',
        'switch_to_dark_mode' => '切換到暗色模式',
        'current_mode_dark' => '當前模式: 暗色模式',
        'current_mode_light' => '當前模式: 亮色模式',
        'fetching_version' => '正在獲取版本信息...',
        'latest_version' => '最新版本：',
        'unable_to_fetch_version' => '無法獲取最新版本信息',
        'request_failed' => '請求失敗，請稍後再試',
        'pip_not_supported' => '當前媒體不支持畫中畫',
        'pip_operation_failed' => '畫中畫操作失敗',
        'exit_picture_in_picture' => '退出畫中畫',
        'picture_in_picture' => '畫中畫',
        'hide_playlist' => '隱藏列表',
        'show_playlist' => '顯示列表',
        'enter_fullscreen' => '進入全屏',
        'exit_fullscreen' => '退出全屏',
        'confirm_update_php' => '您確定要更新 PHP 配置嗎？',
        'select_files_to_delete' => '請先選擇要刪除的文件！',
        'confirm_batch_delete' => '確定要刪除選中的 %d 個文件嗎？',
        'selected_info' => '已選擇 %d 個文件，合計 %s MB'
    ],

    'kr' => [
        'select_language'        => '언어 선택',
        'simplified_chinese'     => '중국어 간체',
        'traditional_chinese'    => '중국어 번체',
        'english'                => '영어',
        'korean'                 => '한국어',
        'vietnamese'             => '베트남어',
        'thailand'               => '태국어',
        'japanese'               => '일본어',
        'russian'                => '러시아어',
        'germany'                => '독일어',
        'france'                 => '프랑스어',
        'arabic'                 => '아랍어',
        'spanish'                => '스페인어',
        'close'                  => '닫기',
        'save'                   => '저장',
        'theme_download'         => '테마 다운로드',
        'select_all'             => '전체 선택',
        'batch_delete'           => '선택한 파일 일괄 삭제',
        'total'                  => '총합:',
        'free'                   => '남은 공간:',
        'hover_to_preview'       => '클릭하여 미리보기 활성화',
        'mount_info'             => '마운트 포인트: {{mount}}｜사용 공간: {{used}}',
        'spectra_config'         => 'Spectra 설정 관리',
        'current_mode'           => '현재 모드: 로드 중...',
        'toggle_mode'            => '모드 전환',
        'check_update'           => '업데이트 확인',
        'batch_upload'           => '파일 선택하여 일괄 업로드',
        'add_to_playlist'        => '체크한 항목을 재생 목록에 추가',
        'clear_background'       => '배경 지우기',
        'clear_background_label' => '배경 지우기',
        'file_list'              => '파일 목록',
        'component_bg_color'     => '구성 요소 배경색 선택',
        'page_bg_color'          => '페이지 배경색 선택',
        'toggle_font'            => '글꼴 전환',
        'filename'               => '이름:',
        'filesize'               => '크기:',
        'duration'               => '재생 시간:',
        'resolution'             => '해상도:',
        'bitrate'                => '비트레이트:',
        'type'                   => '유형:',
        'image'                  => '이미지',
        'video'                  => '비디오',
        'audio'                  => '오디오',
        'document'               => '문서',
        'delete'                 => '삭제',
        'rename'                 => '이름 변경',
        'download'               => '다운로드',
        'set_background'         => '배경 설정',
        'preview'                => '미리보기',
        'toggle_fullscreen'      => '전체 화면 전환',
        'supported_formats'      => '지원 포맷: [ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => '파일을 여기에 드롭하세요',
        'or'                     => '또는',
        'select_files'           => '파일 선택',
        'unlock_php_upload_limit'=> 'PHP 업로드 제한 해제',
        'upload'                 => '업로드',
        'cancel'                 => '취소',
        'rename_file'            => '파일 이름 변경',
        'new_filename'           => '새 파일 이름',
        'invalid_filename_chars' => '파일 이름에 다음 문자를 포함할 수 없습니다: \\/:*?"<>|',
        'confirm'                => '확인',
        'media_player'           => '미디어 플레이어',
        'playlist'               => '재생 목록',
        'clear_list'             => '목록 지우기',
        'toggle_list'            => '목록 숨기기',
        'picture_in_picture'     => '화면 속 화면',
        'fullscreen'             => '전체 화면',
        'music_player'           => '음악 플레이어',
        'play_pause'             => '재생/일시정지',
        'previous_track'         => '이전 곡',
        'next_track'             => '다음 곡',
        'repeat_mode'            => '반복 재생',
        'toggle_floating_lyrics' => '플로팅 가사',
        'clear_config'           => '설정 지우기',
        'custom_playlist'        => '사용자 정의 재생 목록',
        'volume'                 => '볼륨',
        'update_playlist'        => '재생 목록 업데이트',
        'playlist_url'           => '재생 목록 URL',
        'reset_default'          => '기본값으로 재설정',
        'toggle_lyrics'          => '가사 끄기',
        'fetching_version'       => '버전 정보를 가져오는 중...',
        'download_local'         => '로컬에 다운로드',
        'change_language'        => '언어 변경',
        'pause_playing'          => '재생 일시정지',
        'start_playing'          => '재생 시작',
        'manual_switch'          => '수동 전환',
        'auto_switch'            => '자동 전환',
        'switch_to'              => '전환:',
        'auto_play'              => '자동 재생',
        'lyrics_load_failed'     => '가사 로드 실패',
        'order_play'             => '순차 재생',
        'single_loop'            => '단일 반복',
        'shuffle_play'           => '랜덤 재생',
        'playlist_click'         => '재생 목록 클릭',
        'index'                  => '인덱스',
        'song_name'              => '곡 이름',
        'no_lyrics'              => '가사 없음',
        'loading_lyrics'         => '가사 로드 중...',
        'autoplay_blocked'       => '자동 재생이 차단되었습니다',
        'cache_cleared'               => '설정이 지워졌습니다',
        'open_custom_playlist'        => '사용자 정의 재생 목록 열기',
        'reset_default_playlist'      => '기본 재생 목록 링크로 복원되었습니다',
        'reset_default_error'         => '기본 링크 복원 중 오류 발생',
        'reset_default_failed'        => '기본 링크 복원 실패',
        'playlist_load_failed'        => '재생 목록 로드 실패',
        'playlist_load_failed_message'=> '재생 목록 로드 실패',
        'hour_announcement'      => '정각 알림, 현재 시간은',
        'hour_exact'             => '시 정각',
        'weekDays' => ['일', '월', '화', '수', '목', '금', '토'],
        'labels' => [
            'year' => '년',
            'month' => '월',
            'day' => '일',
            'week' => '요일'
        ],
        'hour_announcement' => '정각 알림, 현재 시간은',
        'hour_exact' => '시 정각',
        'error_loading_time' => '시간 표시 오류',
        'switch_to_light_mode' => '밝은 모드로 전환',
        'switch_to_dark_mode' => '어두운 모드로 전환',
        'current_mode_dark' => '현재 모드: 어두운 모드',
        'current_mode_light' => '현재 모드: 밝은 모드',
        'fetching_version' => '버전 정보를 가져오는 중...',
        'latest_version' => '최신 버전:',
        'unable_to_fetch_version' => '최신 버전 정보를 가져올 수 없습니다',
        'request_failed' => '요청 실패, 나중에 다시 시도하세요',
        'pip_not_supported' => '현재 미디어는 화면 속 화면을 지원하지 않습니다',
        'pip_operation_failed' => '화면 속 화면 작업 실패',
        'exit_picture_in_picture' => '화면 속 화면 종료',
        'picture_in_picture' => '화면 속 화면',
        'hide_playlist' => '목록 숨기기',
        'show_playlist' => '목록 표시',
        'enter_fullscreen' => '전체 화면으로 전환',
        'exit_fullscreen' => '전체 화면 종료',
        'confirm_update_php' => 'PHP 설정을 업데이트하시겠습니까?',
        'select_files_to_delete' => '삭제할 파일을 선택하세요!',
        'confirm_batch_delete' => '선택된 %d개의 파일을 삭제하시겠습니까?',
        'selected_info' => '선택된 파일: %d개, 총합: %s MB'
    ],

    'jp' => [
        'select_language'        => '言語を選択',
        'simplified_chinese'     => '簡体字中国語',
        'traditional_chinese'    => '繁体字中国語',
        'english'                => '英語',
        'korean'                 => '韓国語',
        'vietnamese'             => 'ベトナム語',
        'thailand'              => 'タイ語',
        'japanese'               => '日本語',
        'russian'                => 'ロシア語',
        'germany'                => 'ドイツ語',
        'france'                 => 'フランス語',
        'arabic'                 => 'アラビア語',
        'spanish'                => 'スペイン語',
        'close'                  => '閉じる',
        'save'                   => '保存',
        'theme_download'         => 'テーマをダウンロード',
        'select_all'             => 'すべて選択',
        'batch_delete'           => '選択したファイルを一括削除',
        'total'                  => '合計：',
        'free'                   => '残り：',
        'hover_to_preview'       => 'クリックしてプレビューを有効化',
        'mount_info'             => 'マウントポイント：{{mount}}｜使用済み容量：{{used}}',
        'spectra_config'         => 'Spectra 設定管理',
        'current_mode'           => '現在のモード：読み込み中...',
        'toggle_mode'            => 'モード切り替え',
        'check_update'           => '更新を確認',
        'batch_upload'           => 'ファイルを選択して一括アップロード',
        'add_to_playlist'        => 'チェックを入れてプレイリストに追加',
        'clear_background'       => '背景をクリア',
        'clear_background_label' => '背景をクリア',
        'file_list'              => 'ファイルリスト',
        'component_bg_color'     => 'コンポーネント背景色を選択',
        'page_bg_color'          => 'ページ背景色を選択',
        'toggle_font'            => 'フォント切り替え',
        'filename'               => '名前：',
        'filesize'               => 'サイズ：',
        'duration'               => '再生時間：',
        'resolution'             => '解像度：',
        'bitrate'                => 'ビットレート：',
        'type'                   => 'タイプ：',
        'image'                  => '画像',
        'video'                  => 'ビデオ',
        'audio'                  => 'オーディオ',
        'document'               => 'ドキュメント',
        'delete'                 => '削除',
        'rename'                 => '名前を変更',
        'download'               => 'ダウンロード',
        'set_background'         => '背景を設定',
        'preview'                => 'プレビュー',
        'toggle_fullscreen'      => '全画面切り替え',
        'supported_formats'      => '対応形式：[ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'ここにファイルをドロップ',
        'or'                     => 'または',
        'select_files'           => 'ファイルを選択',
        'unlock_php_upload_limit'=> 'PHPのアップロード制限を解除',
        'upload'                 => 'アップロード',
        'cancel'                 => 'キャンセル',
        'rename_file'            => 'ファイル名を変更',
        'new_filename'           => '新しいファイル名',
        'invalid_filename_chars' => 'ファイル名に次の文字を含めることはできません：\\/：*?"<>|',
        'confirm'                => '確認',
        'media_player'           => 'メディアプレイヤー',
        'playlist'               => 'プレイリスト',
        'clear_list'             => 'リストをクリア',
        'toggle_list'            => 'リストを非表示',
        'picture_in_picture'     => 'ピクチャ・イン・ピクチャ',
        'fullscreen'             => '全画面',
        'music_player'           => '音楽プレイヤー',
        'play_pause'             => '再生/一時停止',
        'previous_track'         => '前のトラック',
        'next_track'             => '次のトラック',
        'repeat_mode'            => 'リピート再生',
        'toggle_floating_lyrics' => 'フローティング歌詞',
        'clear_config'           => '設定をクリア',
        'custom_playlist'        => 'カスタムプレイリスト',
        'volume'                 => '音量',
        'update_playlist'        => 'プレイリストを更新',
        'playlist_url'           => 'プレイリストURL',
        'reset_default'          => 'デフォルトにリセット',
        'toggle_lyrics'          => '歌詞を非表示',
        'fetching_version'       => 'バージョン情報を取得中...',
        'download_local'         => 'ローカルにダウンロード',
        'change_language'        => '言語を変更',
        'pause_playing'          => '再生を一時停止',
        'start_playing'          => '再生を開始',
        'manual_switch'          => '手動切り替え',
        'auto_switch'            => '自動切り替え',
        'switch_to'              => '切り替え：',
        'auto_play'              => '自動再生',
        'lyrics_load_failed'     => '歌詞の読み込みに失敗しました',
        'order_play'             => '順番再生',
        'single_loop'            => '単一ループ',
        'shuffle_play'           => 'シャッフル再生',
        'playlist_click'         => 'プレイリストクリック',
        'index'                  => 'インデックス',
        'song_name'              => '曲名',
        'no_lyrics'              => '歌詞がありません',
        'loading_lyrics'         => '歌詞を読み込み中...',
        'autoplay_blocked'       => '自動再生がブロックされました',
        'cache_cleared'               => '設定がクリアされました',
        'open_custom_playlist'        => 'カスタムプレイリストを開く',
        'reset_default_playlist'      => 'デフォルトのプレイリストリンクに戻りました',
        'reset_default_error'         => 'デフォルトリンク復元中にエラーが発生しました',
        'reset_default_failed'        => 'デフォルトリンクの復元に失敗しました',
        'playlist_load_failed'        => 'プレイリストの読み込みに失敗しました',
        'playlist_load_failed_message'=> 'プレイリストの読み込みに失敗しました',
        'hour_announcement'      => '時報、現在の時間は',
        'hour_exact'             => '時ちょうど',
        'weekDays' => ['日', '月', '火', '水', '木', '金', '土'],
        'labels' => [
            'year' => '年',
            'month' => '月',
            'day' => '日',
            'week' => '曜日'
        ],
        'hour_announcement' => '時報、現在の時間は',
        'hour_exact' => '時ちょうど',
        'error_loading_time' => '時間表示エラー',
        'switch_to_light_mode' => 'ライトモードに切り替え',
        'switch_to_dark_mode' => 'ダークモードに切り替え',
        'current_mode_dark' => '現在のモード：ダークモード',
        'current_mode_light' => '現在のモード：ライトモード',
        'fetching_version' => 'バージョン情報を取得中...',
        'latest_version' => '最新バージョン：',
        'unable_to_fetch_version' => '最新バージョン情報を取得できません',
        'request_failed' => 'リクエストに失敗しました。後でもう一度試してください',
        'pip_not_supported' => '現在のメディアはピクチャ・イン・ピクチャをサポートしていません',
        'pip_operation_failed' => 'ピクチャ・イン・ピクチャ操作に失敗しました',
        'exit_picture_in_picture' => 'ピクチャ・イン・ピクチャを終了',
        'picture_in_picture' => 'ピクチャ・イン・ピクチャ',
        'hide_playlist' => 'リストを非表示',
        'show_playlist' => 'リストを表示',
        'enter_fullscreen' => '全画面に切り替え',
        'exit_fullscreen' => '全画面を終了',
        'confirm_update_php' => 'PHP設定を更新しますか？',
        'select_files_to_delete' => '削除するファイルを選択してください！',
        'confirm_batch_delete' => '選択された%d個のファイルを削除しますか？',
        'selected_info' => '選択されたファイル：%d個、合計：%s MB'
    ],

    'vn' => [
        'select_language'        => 'Chọn ngôn ngữ',
        'simplified_chinese'     => 'Tiếng Trung giản thể',
        'traditional_chinese'    => 'Tiếng Trung phồn thể',
        'english'                => 'Tiếng Anh',
        'korean'                 => 'Tiếng Hàn',
        'thailand'               => 'Thái',
        'vietnamese'             => 'Tiếng Việt',
        'japanese'               => 'Tiếng Nhật',
        'russian'                => 'Tiếng Nga',
        'germany'                => 'Tiếng Đức',
        'france'                 => 'Tiếng Pháp',
        'arabic'                 => 'Tiếng Ả Rập',
        'spanish'                => 'Tiếng Tây Ban Nha',
        'close'                  => 'Đóng',
        'save'                   => 'Lưu',
        'theme_download'         => 'Tải xuống chủ đề',
        'select_all'             => 'Chọn tất cả',
        'batch_delete'           => 'Xóa nhiều tệp đã chọn',
        'total'                  => 'Tổng cộng:',
        'free'                   => 'Còn lại:',
        'hover_to_preview'       => 'Nhấn để kích hoạt xem trước',
        'mount_info'             => 'Điểm gắn kết: {{mount}}｜Dung lượng đã sử dụng: {{used}}',
        'spectra_config'         => 'Quản lý cấu hình Spectra',
        'current_mode'           => 'Chế độ hiện tại: Đang tải...',
        'toggle_mode'            => 'Chuyển đổi chế độ',
        'check_update'           => 'Kiểm tra cập nhật',
        'batch_upload'           => 'Chọn tệp để tải lên hàng loạt',
        'add_to_playlist'        => 'Chọn để thêm vào danh sách phát',
        'clear_background'       => 'Xóa nền',
        'clear_background_label' => 'Xóa nền',
        'file_list'              => 'Danh sách tệp',
        'component_bg_color'     => 'Chọn màu nền của thành phần',
        'page_bg_color'          => 'Chọn màu nền trang',
        'toggle_font'            => 'Chuyển đổi phông chữ',
        'filename'               => 'Tên:',
        'filesize'               => 'Kích thước:',
        'duration'               => 'Thời lượng:',
        'resolution'             => 'Độ phân giải:',
        'bitrate'                => 'Tốc độ bit:',
        'type'                   => 'Loại:',
        'image'                  => 'Hình ảnh',
        'video'                  => 'Video',
        'audio'                  => 'Âm thanh',
        'document'               => 'Tài liệu',
        'delete'                 => 'Xóa',
        'rename'                 => 'Đổi tên',
        'download'               => 'Tải xuống',
        'set_background'         => 'Đặt nền',
        'preview'                => 'Xem trước',
        'toggle_fullscreen'      => 'Chuyển đổi chế độ toàn màn hình',
        'supported_formats'      => 'Định dạng được hỗ trợ: [ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Kéo thả tệp vào đây',
        'or'                     => 'hoặc',
        'select_files'           => 'Chọn tệp',
        'unlock_php_upload_limit'=> 'Mở khóa giới hạn tải lên của PHP',
        'upload'                 => 'Tải lên',
        'cancel'                 => 'Hủy',
        'rename_file'            => 'Đổi tên tệp',
        'new_filename'           => 'Tên tệp mới',
        'invalid_filename_chars' => 'Tên tệp không được chứa các ký tự sau: \\/:*?"<>|',
        'confirm'                => 'Xác nhận',
        'media_player'           => 'Trình phát đa phương tiện',
        'playlist'               => 'Danh sách phát',
        'clear_list'             => 'Xóa danh sách',
        'toggle_list'            => 'Ẩn danh sách',
        'picture_in_picture'     => 'Hình trong hình',
        'fullscreen'             => 'Toàn màn hình',
        'music_player'           => 'Trình phát nhạc',
        'play_pause'             => 'Phát / Dừng',
        'previous_track'         => 'Bài trước',
        'next_track'             => 'Bài tiếp theo',
        'repeat_mode'            => 'Phát lặp lại',
        'toggle_floating_lyrics' => 'Lời bài hát nổi',
        'clear_config'           => 'Xóa cấu hình',
        'custom_playlist'        => 'Danh sách phát tùy chỉnh',
        'volume'                 => 'Âm lượng',
        'update_playlist'        => 'Cập nhật danh sách phát',
        'playlist_url'           => 'URL danh sách phát',
        'reset_default'          => 'Đặt lại mặc định',
        'toggle_lyrics'          => 'Ẩn lời bài hát',
        'fetching_version'       => 'Đang lấy thông tin phiên bản...',
        'download_local'         => 'Tải về máy',
        'change_language'        => 'Thay đổi ngôn ngữ',
        'pause_playing'          => 'Tạm dừng phát',
        'start_playing'          => 'Bắt đầu phát',
        'manual_switch'          => 'Chuyển đổi thủ công',
        'auto_switch'            => 'Chuyển đổi tự động',
        'switch_to'              => 'Chuyển sang:',
        'auto_play'              => 'Tự động phát',
        'lyrics_load_failed'     => 'Không tải được lời bài hát',
        'order_play'             => 'Phát theo thứ tự',
        'single_loop'            => 'Lặp lại một bài',
        'shuffle_play'           => 'Phát ngẫu nhiên',
        'playlist_click'         => 'Nhấn vào danh sách phát',
        'index'                  => 'Mục lục',
        'song_name'              => 'Tên bài hát',
        'no_lyrics'              => 'Không có lời bài hát',
        'loading_lyrics'         => 'Đang tải lời bài hát...',
        'autoplay_blocked'       => 'Tự động phát bị chặn',
        'cache_cleared'               => 'Cấu hình đã được xóa',
        'open_custom_playlist'        => 'Mở danh sách phát tùy chỉnh',
        'reset_default_playlist'      => 'Đã khôi phục liên kết danh sách phát mặc định',
        'reset_default_error'         => 'Lỗi khi khôi phục liên kết mặc định',
        'reset_default_failed'        => 'Không thể khôi phục liên kết mặc định',
        'playlist_load_failed'        => 'Không thể tải danh sách phát',
        'playlist_load_failed_message'=> 'Không thể tải danh sách phát',
        'hour_announcement'      => 'Thông báo giờ, hiện tại là',
        'hour_exact'             => 'giờ đúng',
        'weekDays' => ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'],
        'labels' => [
            'year' => 'Năm',
            'month' => 'Tháng',
            'day' => 'Ngày',
            'week' => 'Thứ'
        ],
        'hour_announcement' => 'Thông báo giờ, hiện tại là',
        'hour_exact' => 'giờ đúng',
        'error_loading_time' => 'Lỗi hiển thị thời gian',
        'switch_to_light_mode' => 'Chuyển sang chế độ sáng',
        'switch_to_dark_mode' => 'Chuyển sang chế độ tối',
        'current_mode_dark' => 'Chế độ hiện tại: Chế độ tối',
        'current_mode_light' => 'Chế độ hiện tại: Chế độ sáng',
        'fetching_version' => 'Đang lấy thông tin phiên bản...',
        'latest_version' => 'Phiên bản mới nhất:',
        'unable_to_fetch_version' => 'Không thể lấy thông tin phiên bản mới nhất',
        'request_failed' => 'Yêu cầu thất bại, vui lòng thử lại sau',
        'pip_not_supported' => 'Phương tiện hiện tại không hỗ trợ Hình trong hình',
        'pip_operation_failed' => 'Thao tác Hình trong hình thất bại',
        'exit_picture_in_picture' => 'Thoát Hình trong hình',
        'picture_in_picture' => 'Hình trong hình',
        'hide_playlist' => 'Ẩn danh sách phát',
        'show_playlist' => 'Hiện danh sách phát',
        'enter_fullscreen' => 'Chuyển sang toàn màn hình',
        'exit_fullscreen' => 'Thoát toàn màn hình',
        'confirm_update_php' => 'Bạn có chắc muốn cập nhật cấu hình PHP không?',
        'select_files_to_delete' => 'Vui lòng chọn tệp để xóa!',
        'confirm_batch_delete' => 'Bạn có chắc muốn xóa %d tệp đã chọn không?',
        'selected_info' => 'Đã chọn %d tệp, tổng cộng %s MB'
    ],

    'th' => [
        'select_language'        => 'เลือกภาษา',
        'simplified_chinese'     => 'ภาษาจีนตัวย่อ',
        'traditional_chinese'    => 'ภาษาจีนตัวเต็ม',
        'english'                => 'ภาษาอังกฤษ',
        'korean'                 => 'ภาษาเกาหลี',
        'vietnamese'             => 'ภาษาเวียดนาม',
        'japanese'               => 'ภาษาญี่ปุ่น',
        'russian'                => 'ภาษารัสเซีย',
        'germany'                => 'ภาษาเยอรมัน',
        'france'                 => 'ภาษาฝรั่งเศส',
        'arabic'                 => 'ภาษาอาหรับ',
        'spanish'                => 'ภาษาสเปน',
        'close'                  => 'ปิด',
        'save'                   => 'บันทึก',
        'theme_download'         => 'ดาวน์โหลดธีม',
        'select_all'             => 'เลือกทั้งหมด',
        'batch_delete'           => 'ลบไฟล์ที่เลือกทั้งหมด',
        'total'                  => 'รวมทั้งหมด:',
        'free'                   => 'ที่เหลือ:',
        'hover_to_preview'       => 'คลิกเพื่อเปิดการแสดงตัวอย่าง',
        'mount_info'             => 'จุดเชื่อมต่อ: {{mount}}｜พื้นที่ที่ใช้ไป: {{used}}',
        'spectra_config'         => 'การจัดการการตั้งค่า Spectra',
        'current_mode'           => 'โหมดปัจจุบัน: กำลังโหลด...',
        'toggle_mode'            => 'สลับโหมด',
        'check_update'           => 'ตรวจสอบการอัปเดต',
        'batch_upload'           => 'เลือกไฟล์เพื่ออัปโหลดครั้งละหลายไฟล์',
        'add_to_playlist'        => 'เลือกเพื่อเพิ่มลงในเพลย์ลิสต์',
        'clear_background'       => 'ล้างพื้นหลัง',
        'clear_background_label' => 'ล้างพื้นหลัง',
        'file_list'              => 'รายการไฟล์',
        'component_bg_color'     => 'เลือกสีพื้นหลังของคอมโพเนนต์',
        'page_bg_color'          => 'เลือกสีพื้นหลังของหน้า',
        'toggle_font'            => 'สลับแบบอักษร',
        'filename'               => 'ชื่อไฟล์:',
        'filesize'               => 'ขนาดไฟล์:',
        'duration'               => 'ระยะเวลา:',
        'resolution'             => 'ความละเอียด:',
        'bitrate'                => 'บิตเรต:',
        'type'                   => 'ประเภท:',
        'image'                  => 'ภาพ',
        'video'                  => 'วิดีโอ',
        'audio'                  => 'เสียง',
        'document'               => 'เอกสาร',
        'delete'                 => 'ลบ',
        'rename'                 => 'เปลี่ยนชื่อ',
        'download'               => 'ดาวน์โหลด',
        'set_background'         => 'ตั้งค่าพื้นหลัง',
        'preview'                => 'ดูตัวอย่าง',
        'toggle_fullscreen'      => 'สลับเป็นเต็มจอ',
        'supported_formats'      => 'รูปแบบที่รองรับ: [ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'ลากไฟล์มาที่นี่',
        'or'                     => 'หรือ',
        'select_files'           => 'เลือกไฟล์',
        'unlock_php_upload_limit'=> 'ปลดล็อกข้อจำกัดการอัปโหลดของ PHP',
        'upload'                 => 'อัปโหลด',
        'cancel'                 => 'ยกเลิก',
        'rename_file'            => 'เปลี่ยนชื่อไฟล์',
        'new_filename'           => 'ชื่อไฟล์ใหม่',
        'invalid_filename_chars' => 'ชื่อไฟล์ต้องไม่มีอักขระต่อไปนี้: \\/:*?"<>|',
        'confirm'                => 'ยืนยัน',
        'media_player'           => 'เครื่องเล่นสื่อ',
        'playlist'               => 'เพลย์ลิสต์',
        'clear_list'             => 'ล้างรายการ',
        'toggle_list'            => 'ซ่อนรายการ',
        'picture_in_picture'     => 'ภาพในภาพ',
        'fullscreen'             => 'เต็มจอ',
        'music_player'           => 'เครื่องเล่นเพลง',
        'play_pause'             => 'เล่น/หยุดชั่วคราว',
        'previous_track'         => 'เพลงก่อนหน้า',
        'next_track'             => 'เพลงถัดไป',
        'repeat_mode'            => 'โหมดเล่นซ้ำ',
        'toggle_floating_lyrics' => 'เนื้อเพลงลอย',
        'clear_config'           => 'ล้างการตั้งค่า',
        'custom_playlist'        => 'เพลย์ลิสต์ที่กำหนดเอง',
        'volume'                 => 'ระดับเสียง',
        'update_playlist'        => 'อัปเดตเพลย์ลิสต์',
        'playlist_url'           => 'URL เพลย์ลิสต์',
        'reset_default'          => 'รีเซ็ตเป็นค่าเริ่มต้น',
        'toggle_lyrics'          => 'ซ่อนเนื้อเพลง',
        'fetching_version'       => 'กำลังดึงข้อมูลเวอร์ชัน...',
        'download_local'         => 'ดาวน์โหลดไปยังเครื่อง',
        'change_language'        => 'เปลี่ยนภาษา',
        'pause_playing'          => 'หยุดเล่นชั่วคราว',
        'start_playing'          => 'เริ่มเล่น',
        'manual_switch'          => 'สลับด้วยตนเอง',
        'auto_switch'            => 'สลับอัตโนมัติ',
        'switch_to'              => 'สลับไปยัง:',
        'auto_play'              => 'เล่นอัตโนมัติ',
        'lyrics_load_failed'     => 'การโหลดเนื้อเพลงล้มเหลว',
        'order_play'             => 'เล่นตามลำดับ',
        'single_loop'            => 'เล่นซ้ำเพลงเดียว',
        'shuffle_play'           => 'เล่นแบบสุ่ม',
        'playlist_click'         => 'คลิกเพลย์ลิสต์',
        'index'                  => 'ดัชนี',
        'song_name'              => 'ชื่อเพลง',
        'no_lyrics'              => 'ไม่มีเนื้อเพลง',
        'loading_lyrics'         => 'กำลังโหลดเนื้อเพลง...',
        'autoplay_blocked'       => 'การเล่นอัตโนมัติถูกบล็อก',
        'cache_cleared'               => 'การตั้งค่าถูกล้าง',
        'open_custom_playlist'        => 'เปิดเพลย์ลิสต์ที่กำหนดเอง',
        'reset_default_playlist'      => 'รีเซ็ตลิงก์เพลย์ลิสต์เป็นค่าเริ่มต้น',
        'reset_default_error'         => 'เกิดข้อผิดพลาดขณะรีเซ็ตลิงก์ค่าเริ่มต้น',
        'reset_default_failed'        => 'การรีเซ็ตลิงก์ค่าเริ่มต้นล้มเหลว',
        'playlist_load_failed'        => 'ไม่สามารถโหลดเพลย์ลิสต์',
        'playlist_load_failed_message'=> 'ไม่สามารถโหลดเพลย์ลิสต์',
        'hour_announcement'      => 'การประกาศเวลา, เวลาขณะนี้คือ',
        'hour_exact'             => 'โมงตรง',
        'weekDays' => ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'],
        'labels' => [
            'year' => 'ปี',
            'month' => 'เดือน',
            'day' => 'วัน',
            'week' => 'สัปดาห์'
        ],
        'hour_announcement' => 'การประกาศเวลา, เวลาขณะนี้คือ',
        'hour_exact' => 'โมงตรง',
        'error_loading_time' => 'แสดงเวลาไม่ถูกต้อง',
        'switch_to_light_mode' => 'เปลี่ยนเป็นโหมดสว่าง',
        'switch_to_dark_mode' => 'เปลี่ยนเป็นโหมดมืด',
        'current_mode_dark' => 'โหมดปัจจุบัน: โหมดมืด',
        'current_mode_light' => 'โหมดปัจจุบัน: โหมดสว่าง',
        'fetching_version' => 'กำลังดึงข้อมูลเวอร์ชัน...',
        'latest_version' => 'เวอร์ชันล่าสุด:',
        'unable_to_fetch_version' => 'ไม่สามารถดึงข้อมูลเวอร์ชันล่าสุด',
        'request_failed' => 'การร้องขอล้มเหลว กรุณาลองใหม่ภายหลัง',
        'pip_not_supported' => 'สื่อปัจจุบันไม่รองรับภาพในภาพ',
        'pip_operation_failed' => 'การดำเนินการภาพในภาพล้มเหลว',
        'exit_picture_in_picture' => 'ออกจากภาพในภาพ',
        'picture_in_picture' => 'ภาพในภาพ',
        'hide_playlist' => 'ซ่อนรายการ',
        'show_playlist' => 'แสดงรายการ',
        'enter_fullscreen' => 'เปลี่ยนเป็นเต็มจอ',
        'exit_fullscreen' => 'ออกจากเต็มจอ',
        'confirm_update_php' => 'คุณแน่ใจหรือไม่ว่าต้องการอัปเดตการตั้งค่า PHP?',
        'select_files_to_delete' => 'กรุณาเลือกไฟล์ที่จะลบ!',
        'confirm_batch_delete' => 'คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์ที่เลือก %d ไฟล์?',
        'selected_info' => 'เลือกไฟล์แล้ว %d ไฟล์ รวมทั้งหมด %s MB'
    ],

    'ru' => [
        'select_language'        => 'Выберите язык',
        'simplified_chinese'     => 'Упрощенный китайский',
        'traditional_chinese'    => 'Традиционный китайский',
        'english'                => 'Английский',
        'korean'                 => 'Корейский',
        'vietnamese'             => 'Вьетнамский',
        'thailand'              => 'Тайский',
        'japanese'               => 'Японский',
        'russian'                => 'Русский',
        'germany'                => 'Немецкий',
        'france'                 => 'Французский',
        'arabic'                 => 'Арабский',
        'spanish'                => 'Испанский',
        'close'                  => 'Закрыть',
        'save'                   => 'Сохранить',
        'theme_download'         => 'Скачать тему',
        'select_all'             => 'Выбрать все',
        'batch_delete'           => 'Удалить выбранные файлы',
        'total'                  => 'Всего:',
        'free'                   => 'Свободно:',
        'hover_to_preview'       => 'Нажмите, чтобы включить предварительный просмотр',
        'mount_info'             => 'Точка монтирования: {{mount}}｜Используемое место: {{used}}',
        'spectra_config'         => 'Управление конфигурацией Spectra',
        'current_mode'           => 'Текущий режим: загрузка...',
        'toggle_mode'            => 'Переключить режим',
        'check_update'           => 'Проверить обновление',
        'batch_upload'           => 'Выберите файлы для массовой загрузки',
        'add_to_playlist'        => 'Добавить в плейлист',
        'clear_background'       => 'Очистить фон',
        'clear_background_label' => 'Очистить фон',
        'file_list'              => 'Список файлов',
        'component_bg_color'     => 'Выберите цвет фона компонента',
        'page_bg_color'          => 'Выберите цвет фона страницы',
        'toggle_font'            => 'Переключить шрифт',
        'filename'               => 'Имя файла:',
        'filesize'               => 'Размер:',
        'duration'               => 'Длительность:',
        'resolution'             => 'Разрешение:',
        'bitrate'                => 'Битрейт:',
        'type'                   => 'Тип:',
        'image'                  => 'Изображение',
        'video'                  => 'Видео',
        'audio'                  => 'Аудио',
        'document'               => 'Документ',
        'delete'                 => 'Удалить',
        'rename'                 => 'Переименовать',
        'download'               => 'Скачать',
        'set_background'         => 'Установить фон',
        'preview'                => 'Предварительный просмотр',
        'toggle_fullscreen'      => 'Переключить полноэкранный режим',
        'supported_formats'      => 'Поддерживаемые форматы: [ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Перетащите файлы сюда',
        'or'                     => 'или',
        'select_files'           => 'Выбрать файлы',
        'unlock_php_upload_limit'=> 'Снять ограничение PHP загрузки',
        'upload'                 => 'Загрузить',
        'cancel'                 => 'Отменить',
        'rename_file'            => 'Переименовать файл',
        'new_filename'           => 'Новое имя файла',
        'invalid_filename_chars' => 'Имя файла не может содержать следующие символы: \\/:*?"<>|',
        'confirm'                => 'Подтвердить',
        'media_player'           => 'Медиа-плеер',
        'playlist'               => 'Плейлист',
        'clear_list'             => 'Очистить список',
        'toggle_list'            => 'Скрыть список',
        'picture_in_picture'     => 'Картинка в картинке',
        'fullscreen'             => 'Полноэкранный режим',
        'music_player'           => 'Музыкальный плеер',
        'play_pause'             => 'Воспроизведение/Пауза',
        'previous_track'         => 'Предыдущий трек',
        'next_track'             => 'Следующий трек',
        'repeat_mode'            => 'Режим повтора',
        'toggle_floating_lyrics' => 'Плавающие тексты',
        'clear_config'           => 'Очистить конфигурацию',
        'custom_playlist'        => 'Пользовательский плейлист',
        'volume'                 => 'Громкость',
        'update_playlist'        => 'Обновить плейлист',
        'playlist_url'           => 'URL плейлиста',
        'reset_default'          => 'Сбросить настройки по умолчанию',
        'toggle_lyrics'          => 'Скрыть тексты',
        'fetching_version'       => 'Получение информации о версии...',
        'download_local'         => 'Скачать локально',
        'change_language'        => 'Изменить язык',
        'pause_playing'          => 'Пауза воспроизведения',
        'start_playing'          => 'Начать воспроизведение',
        'manual_switch'          => 'Ручное переключение',
        'auto_switch'            => 'Автоматическое переключение',
        'switch_to'              => 'Переключить на:',
        'auto_play'              => 'Автовоспроизведение',
        'lyrics_load_failed'     => 'Не удалось загрузить тексты',
        'order_play'             => 'Последовательное воспроизведение',
        'single_loop'            => 'Одиночный повтор',
        'shuffle_play'           => 'Случайное воспроизведение',
        'playlist_click'         => 'Клик по плейлисту',
        'index'                  => 'Индекс',
        'song_name'              => 'Название песни',
        'no_lyrics'              => 'Нет текстов',
        'loading_lyrics'         => 'Загрузка текстов...',
        'autoplay_blocked'       => 'Автовоспроизведение заблокировано',
        'cache_cleared'               => 'Конфигурация очищена',
        'open_custom_playlist'        => 'Открыть пользовательский плейлист',
        'reset_default_playlist'      => 'Сбросить ссылку плейлиста по умолчанию',
        'reset_default_error'         => 'Ошибка при сбросе ссылки по умолчанию',
        'reset_default_failed'        => 'Не удалось сбросить ссылку по умолчанию',
        'playlist_load_failed'        => 'Не удалось загрузить плейлист',
        'playlist_load_failed_message'=> 'Ошибка загрузки плейлиста',
        'hour_announcement'      => 'Объявление времени, сейчас',
        'hour_exact'             => 'час ровно',
        'weekDays' => ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        'labels' => [
            'year' => 'Год',
            'month' => 'Месяц',
            'day' => 'День',
            'week' => 'Неделя'
        ],
        'hour_announcement' => 'Объявление времени, сейчас',
        'hour_exact' => 'час ровно',
        'error_loading_time' => 'Ошибка отображения времени',
        'switch_to_light_mode' => 'Переключиться на светлый режим',
        'switch_to_dark_mode' => 'Переключиться на темный режим',
        'current_mode_dark' => 'Текущий режим: темный',
        'current_mode_light' => 'Текущий режим: светлый',
        'fetching_version' => 'Получение информации о версии...',
        'latest_version' => 'Последняя версия:',
        'unable_to_fetch_version' => 'Не удалось получить последнюю версию',
        'request_failed' => 'Запрос не удался, попробуйте позже',
        'pip_not_supported' => 'Текущее медиа не поддерживает картинку в картинке',
        'pip_operation_failed' => 'Не удалось выполнить операцию картинка в картинке',
        'exit_picture_in_picture' => 'Выйти из картинки в картинке',
        'picture_in_picture' => 'Картинка в картинке',
        'hide_playlist' => 'Скрыть список',
        'show_playlist' => 'Показать список',
        'enter_fullscreen' => 'Включить полноэкранный режим',
        'exit_fullscreen' => 'Выйти из полноэкранного режима',
        'confirm_update_php' => 'Вы уверены, что хотите обновить конфигурацию PHP?',
        'select_files_to_delete' => 'Выберите файлы для удаления!',
        'confirm_batch_delete' => 'Вы уверены, что хотите удалить выбранные %d файлов?',
        'selected_info' => 'Выбрано %d файлов, всего %s MB'
    ],

    'ar' => [
        'select_language'        => 'اختر اللغة',
        'simplified_chinese'     => 'الصينية المبسطة',
        'traditional_chinese'    => 'الصينية التقليدية',
        'english'                => 'الإنجليزية',
        'korean'                 => 'الكورية',
        'vietnamese'             => 'الفيتنامية',
        'thailand'              => 'التايلاندية',
        'japanese'               => 'اليابانية',
        'russian'                => 'الروسية',
        'germany'                => 'الألمانية',
        'france'                 => 'الفرنسية',
        'arabic'                 => 'العربية',
        'spanish'                => 'الإسبانية',
        'close'                  => 'إغلاق',
        'save'                   => 'حفظ',
        'theme_download'         => 'تنزيل الثيم',
        'select_all'             => 'تحديد الكل',
        'batch_delete'           => 'حذف الملفات المحددة دفعة واحدة',
        'total'                  => 'الإجمالي:',
        'free'                   => 'المتبقي:',
        'hover_to_preview'       => 'انقر لتفعيل المعاينة',
        'mount_info'             => 'نقطة التركيب: {{mount}}｜المساحة المستخدمة: {{used}}',
        'spectra_config'         => 'إدارة إعدادات Spectra',
        'current_mode'           => 'الوضع الحالي: جارٍ التحميل...',
        'toggle_mode'            => 'تبديل الوضع',
        'check_update'           => 'تحقق من التحديث',
        'batch_upload'           => 'حدد الملفات للتحميل دفعة واحدة',
        'add_to_playlist'        => 'إضافة الملفات المحددة إلى قائمة التشغيل',
        'clear_background'       => 'مسح الخلفية',
        'clear_background_label' => 'مسح الخلفية',
        'file_list'              => 'قائمة الملفات',
        'component_bg_color'     => 'اختر لون خلفية المكون',
        'page_bg_color'          => 'اختر لون خلفية الصفحة',
        'toggle_font'            => 'تبديل الخط',
        'filename'               => 'الاسم:',
        'filesize'               => 'الحجم:',
        'duration'               => 'المدة:',
        'resolution'             => 'الدقة:',
        'bitrate'                => 'معدل البت:',
        'type'                   => 'النوع:',
        'image'                  => 'صورة',
        'video'                  => 'فيديو',
        'audio'                  => 'صوت',
        'document'               => 'مستند',
        'delete'                 => 'حذف',
        'rename'                 => 'إعادة تسمية',
        'download'               => 'تنزيل',
        'set_background'         => 'تعيين الخلفية',
        'preview'                => 'معاينة',
        'toggle_fullscreen'      => 'تبديل وضع الشاشة الكاملة',
        'supported_formats'      => 'الصيغ المدعومة: [ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'اسحب الملفات هنا',
        'or'                     => 'أو',
        'select_files'           => 'حدد الملفات',
        'unlock_php_upload_limit'=> 'إزالة حد التحميل الخاص بـ PHP',
        'upload'                 => 'رفع',
        'cancel'                 => 'إلغاء',
        'rename_file'            => 'إعادة تسمية الملف',
        'new_filename'           => 'الاسم الجديد للملف',
        'invalid_filename_chars' => 'اسم الملف لا يمكن أن يحتوي على الأحرف التالية: \\/:*?"<>|',
        'confirm'                => 'تأكيد',
        'media_player'           => 'مشغل الوسائط',
        'playlist'               => 'قائمة التشغيل',
        'clear_list'             => 'مسح القائمة',
        'toggle_list'            => 'إخفاء القائمة',
        'picture_in_picture'     => 'صورة داخل صورة',
        'fullscreen'             => 'ملء الشاشة',
        'music_player'           => 'مشغل الموسيقى',
        'play_pause'             => 'تشغيل/إيقاف مؤقت',
        'previous_track'         => 'المسار السابق',
        'next_track'             => 'المسار التالي',
        'repeat_mode'            => 'وضع التكرار',
        'toggle_floating_lyrics' => 'كلمات الأغاني العائمة',
        'clear_config'           => 'مسح الإعدادات',
        'custom_playlist'        => 'قائمة تشغيل مخصصة',
        'volume'                 => 'مستوى الصوت',
        'update_playlist'        => 'تحديث قائمة التشغيل',
        'playlist_url'           => 'رابط قائمة التشغيل',
        'reset_default'          => 'إعادة التعيين إلى الافتراضي',
        'toggle_lyrics'          => 'إخفاء كلمات الأغاني',
        'fetching_version'       => 'جاري جلب معلومات الإصدار...',
        'download_local'         => 'تنزيل محلي',
        'change_language'        => 'تغيير اللغة',
        'pause_playing'          => 'إيقاف التشغيل مؤقتًا',
        'start_playing'          => 'بدء التشغيل',
        'manual_switch'          => 'التبديل اليدوي',
        'auto_switch'            => 'التبديل التلقائي',
        'switch_to'              => 'التبديل إلى:',
        'auto_play'              => 'تشغيل تلقائي',
        'lyrics_load_failed'     => 'فشل تحميل كلمات الأغاني',
        'order_play'             => 'تشغيل بالترتيب',
        'single_loop'            => 'تكرار الملف الواحد',
        'shuffle_play'           => 'تشغيل عشوائي',
        'playlist_click'         => 'النقر على قائمة التشغيل',
        'index'                  => 'الفهرس',
        'song_name'              => 'اسم الأغنية',
        'no_lyrics'              => 'لا توجد كلمات',
        'loading_lyrics'         => 'جارٍ تحميل كلمات الأغاني...',
        'autoplay_blocked'       => 'تم حظر التشغيل التلقائي',
        'cache_cleared'               => 'تم مسح الإعدادات',
        'open_custom_playlist'        => 'فتح قائمة التشغيل المخصصة',
        'reset_default_playlist'      => 'تمت إعادة تعيين رابط قائمة التشغيل الافتراضي',
        'reset_default_error'         => 'حدث خطأ أثناء إعادة تعيين الرابط الافتراضي',
        'reset_default_failed'        => 'فشل في إعادة تعيين الرابط الافتراضي',
        'playlist_load_failed'        => 'فشل تحميل قائمة التشغيل',
        'playlist_load_failed_message'=> 'فشل تحميل قائمة التشغيل',
        'hour_announcement'      => 'إعلان الساعة، الآن الساعة',
        'hour_exact'             => 'بالضبط',
        'weekDays' => ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
        'labels' => [
            'year' => 'سنة',
            'month' => 'شهر',
            'day' => 'يوم',
            'week' => 'أسبوع'
        ],
        'hour_announcement' => 'إعلان الساعة، الآن الساعة',
        'hour_exact' => 'بالضبط',
        'error_loading_time' => 'خطأ في عرض الوقت',
        'switch_to_light_mode' => 'التبديل إلى الوضع الفاتح',
        'switch_to_dark_mode' => 'التبديل إلى الوضع الداكن',
        'current_mode_dark' => 'الوضع الحالي: الوضع الداكن',
        'current_mode_light' => 'الوضع الحالي: الوضع الفاتح',
        'fetching_version' => 'جاري جلب معلومات الإصدار...',
        'latest_version' => 'أحدث إصدار:',
        'unable_to_fetch_version' => 'تعذر الحصول على أحدث إصدار',
        'request_failed' => 'فشل الطلب، يرجى المحاولة لاحقًا',
        'pip_not_supported' => 'الوسائط الحالية لا تدعم صورة داخل صورة',
        'pip_operation_failed' => 'فشل تشغيل صورة داخل صورة',
        'exit_picture_in_picture' => 'الخروج من صورة داخل صورة',
        'picture_in_picture' => 'صورة داخل صورة',
        'hide_playlist' => 'إخفاء القائمة',
        'show_playlist' => 'إظهار القائمة',
        'enter_fullscreen' => 'تبديل إلى وضع ملء الشاشة',
        'exit_fullscreen' => 'الخروج من وضع ملء الشاشة',
        'confirm_update_php' => 'هل أنت متأكد أنك تريد تحديث إعدادات PHP؟',
        'select_files_to_delete' => 'يرجى اختيار الملفات المراد حذفها!',
        'confirm_batch_delete' => 'هل تريد بالتأكيد حذف الملفات المحددة وعددها %d؟',
        'selected_info' => 'تم اختيار %d ملف، الحجم الإجمالي %s ميغابايت'
    ],

    'es' => [
        'select_language'        => 'Seleccionar idioma',
        'simplified_chinese'     => 'Chino simplificado',
        'traditional_chinese'    => 'Chino tradicional',
        'english'                => 'Inglés',
        'korean'                 => 'Coreano',
        'vietnamese'             => 'Vietnamita',
        'thailand'               => 'Tailandés',
        'japanese'               => 'Japonés',
        'russian'                => 'Ruso',
        'germany'                => 'Alemán',
        'france'                 => 'Francés',
        'arabic'                 => 'Árabe',
        'spanish'                => 'Español',
        'close'                  => 'Cerrar',
        'save'                   => 'Guardar',
        'theme_download'         => 'Descargar tema',
        'select_all'             => 'Seleccionar todo',
        'batch_delete'           => 'Eliminar archivos seleccionados en lote',
        'total'                  => 'Total:',
        'free'                   => 'Libre:',
        'hover_to_preview'       => 'Haga clic para activar la vista previa',
        'mount_info'             => 'Punto de montaje: {{mount}}｜Espacio utilizado: {{used}}',
        'spectra_config'         => 'Gestión de configuración de Spectra',
        'current_mode'           => 'Modo actual: cargando...',
        'toggle_mode'            => 'Cambiar modo',
        'check_update'           => 'Buscar actualizaciones',
        'batch_upload'           => 'Seleccionar archivos para carga masiva',
        'add_to_playlist'        => 'Seleccionar para añadir a la lista de reproducción',
        'clear_background'       => 'Borrar fondo',
        'clear_background_label' => 'Borrar fondo',
        'file_list'              => 'Lista de archivos',
        'component_bg_color'     => 'Seleccionar color de fondo del componente',
        'page_bg_color'          => 'Seleccionar color de fondo de la página',
        'toggle_font'            => 'Cambiar fuente',
        'filename'               => 'Nombre:',
        'filesize'               => 'Tamaño:',
        'duration'               => 'Duración:',
        'resolution'             => 'Resolución:',
        'bitrate'                => 'Tasa de bits:',
        'type'                   => 'Tipo:',
        'image'                  => 'Imagen',
        'video'                  => 'Vídeo',
        'audio'                  => 'Audio',
        'document'               => 'Documento',
        'delete'                 => 'Eliminar',
        'rename'                 => 'Renombrar',
        'download'               => 'Descargar',
        'set_background'         => 'Establecer fondo',
        'preview'                => 'Vista previa',
        'toggle_fullscreen'      => 'Cambiar a pantalla completa',
        'supported_formats'      => 'Formatos compatibles: [ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Arrastra los archivos aquí',
        'or'                     => 'o',
        'select_files'           => 'Seleccionar archivos',
        'unlock_php_upload_limit'=> 'Desbloquear límite de carga PHP',
        'upload'                 => 'Subir',
        'cancel'                 => 'Cancelar',
        'rename_file'            => 'Renombrar archivo',
        'new_filename'           => 'Nuevo nombre de archivo',
        'invalid_filename_chars' => 'El nombre del archivo no puede contener los siguientes caracteres: \\/:*?"<>|',
        'confirm'                => 'Confirmar',
        'media_player'           => 'Reproductor multimedia',
        'playlist'               => 'Lista de reproducción',
        'clear_list'             => 'Borrar lista',
        'toggle_list'            => 'Ocultar lista',
        'picture_in_picture'     => 'Imagen en imagen',
        'fullscreen'             => 'Pantalla completa',
        'music_player'           => 'Reproductor de música',
        'play_pause'             => 'Reproducir/Pausar',
        'previous_track'         => 'Pista anterior',
        'next_track'             => 'Siguiente pista',
        'repeat_mode'            => 'Modo de repetición',
        'toggle_floating_lyrics' => 'Letras flotantes',
        'clear_config'           => 'Borrar configuración',
        'custom_playlist'        => 'Lista de reproducción personalizada',
        'volume'                 => 'Volumen',
        'update_playlist'        => 'Actualizar lista de reproducción',
        'playlist_url'           => 'URL de la lista de reproducción',
        'reset_default'          => 'Restablecer a valores predeterminados',
        'toggle_lyrics'          => 'Ocultar letras',
        'fetching_version'       => 'Obteniendo información de la versión...',
        'download_local'         => 'Descargar localmente',
        'change_language'        => 'Cambiar idioma',
        'pause_playing'          => 'Pausar reproducción',
        'start_playing'          => 'Iniciar reproducción',
        'manual_switch'          => 'Cambio manual',
        'auto_switch'            => 'Cambio automático',
        'switch_to'              => 'Cambiar a:',
        'auto_play'              => 'Reproducción automática',
        'lyrics_load_failed'     => 'Error al cargar las letras',
        'order_play'             => 'Reproducción en orden',
        'single_loop'            => 'Repetición de una sola pista',
        'shuffle_play'           => 'Reproducción aleatoria',
        'playlist_click'         => 'Clic en la lista de reproducción',
        'index'                  => 'Índice',
        'song_name'              => 'Nombre de la canción',
        'no_lyrics'              => 'No hay letras disponibles',
        'loading_lyrics'         => 'Cargando letras...',
        'autoplay_blocked'       => 'Reproducción automática bloqueada',
        'cache_cleared'               => 'Configuración borrada',
        'open_custom_playlist'        => 'Abrir lista de reproducción personalizada',
        'reset_default_playlist'      => 'Restaurada la lista de reproducción predeterminada',
        'reset_default_error'         => 'Error al restaurar el enlace de la lista predeterminada',
        'reset_default_failed'        => 'Fallo al restaurar el enlace predeterminado',
        'playlist_load_failed'        => 'Error al cargar la lista de reproducción',
        'playlist_load_failed_message'=> 'Error al cargar la lista de reproducción',
        'hour_announcement'      => 'Anuncio de hora, ahora son las',
        'hour_exact'             => 'en punto',
        'weekDays' => ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        'labels' => [
            'year' => 'Año',
            'month' => 'Mes',
            'day' => 'Día',
            'week' => 'Semana'
        ],
        'hour_announcement' => 'Anuncio de hora, ahora son las',
        'hour_exact' => 'en punto',
        'error_loading_time' => 'Error al mostrar la hora',
        'switch_to_light_mode' => 'Cambiar al modo claro',
        'switch_to_dark_mode' => 'Cambiar al modo oscuro',
        'current_mode_dark' => 'Modo actual: Modo oscuro',
        'current_mode_light' => 'Modo actual: Modo claro',
        'fetching_version' => 'Obteniendo información de la versión...',
        'latest_version' => 'Última versión:',
        'unable_to_fetch_version' => 'No se pudo obtener la última versión',
        'request_failed' => 'Solicitud fallida, inténtelo de nuevo más tarde',
        'pip_not_supported' => 'El medio actual no admite Imagen en Imagen',
        'pip_operation_failed' => 'Error en la operación Imagen en Imagen',
        'exit_picture_in_picture' => 'Salir de Imagen en Imagen',
        'picture_in_picture' => 'Imagen en Imagen',
        'hide_playlist' => 'Ocultar lista de reproducción',
        'show_playlist' => 'Mostrar lista de reproducción',
        'enter_fullscreen' => 'Cambiar a pantalla completa',
        'exit_fullscreen' => 'Salir de pantalla completa',
        'confirm_update_php' => '¿Está seguro de que desea actualizar la configuración de PHP?',
        'select_files_to_delete' => '¡Seleccione primero los archivos a eliminar!',
        'confirm_batch_delete' => '¿Está seguro de que desea eliminar los %d archivos seleccionados?',
        'selected_info' => 'Seleccionados %d archivos, en total %s MB'
    ],

    'de' => [
        'select_language'        => 'Sprache auswählen',
        'simplified_chinese'     => 'Vereinfachtes Chinesisch',
        'traditional_chinese'    => 'Traditionelles Chinesisch',
        'english'                => 'Englisch',
        'korean'                 => 'Koreanisch',
        'vietnamese'             => 'Vietnamesisch',
        'thailand'             => 'Thailändisch',
        'japanese'               => 'Japanisch',
        'russian'                => 'Russisch',
        'germany'                => 'Deutsch',
        'france'                 => 'Französisch',
        'arabic'                 => 'Arabisch',
        'spanish'                => 'Spanisch',
        'close'                  => 'Schließen',
        'save'                   => 'Speichern',
        'theme_download'         => 'Theme herunterladen',
        'select_all'             => 'Alle auswählen',
        'batch_delete'           => 'Ausgewählte Dateien stapelweise löschen',
        'total'                  => 'Gesamt:',
        'free'                   => 'Frei:',
        'hover_to_preview'       => 'Klicken Sie, um die Vorschau zu aktivieren',
        'mount_info'             => 'Einhängepunkt: {{mount}}｜Verwendeter Speicherplatz: {{used}}',
        'spectra_config'         => 'Spectra-Konfigurationsverwaltung',
        'current_mode'           => 'Aktueller Modus: Laden...',
        'toggle_mode'            => 'Modus wechseln',
        'check_update'           => 'Nach Updates suchen',
        'batch_upload'           => 'Wählen Sie Dateien zum Stapel-Upload aus',
        'add_to_playlist'        => 'Zur Wiedergabeliste hinzufügen',
        'clear_background'       => 'Hintergrund löschen',
        'clear_background_label' => 'Hintergrund löschen',
        'file_list'              => 'Dateiliste',
        'component_bg_color'     => 'Hintergrundfarbe der Komponente auswählen',
        'page_bg_color'          => 'Hintergrundfarbe der Seite auswählen',
        'toggle_font'            => 'Schriftart wechseln',
        'filename'               => 'Dateiname:',
        'filesize'               => 'Dateigröße:',
        'duration'               => 'Dauer:',
        'resolution'             => 'Auflösung:',
        'bitrate'                => 'Bitrate:',
        'type'                   => 'Typ:',
        'image'                  => 'Bild',
        'video'                  => 'Video',
        'audio'                  => 'Audio',
        'document'               => 'Dokument',
        'delete'                 => 'Löschen',
        'rename'                 => 'Umbenennen',
        'download'               => 'Herunterladen',
        'set_background'         => 'Hintergrund festlegen',
        'preview'                => 'Vorschau',
        'toggle_fullscreen'      => 'Vollbildmodus umschalten',
        'supported_formats'      => 'Unterstützte Formate: [ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Dateien hier ablegen',
        'or'                     => 'oder',
        'select_files'           => 'Dateien auswählen',
        'unlock_php_upload_limit'=> 'PHP-Upload-Limit aufheben',
        'upload'                 => 'Hochladen',
        'cancel'                 => 'Abbrechen',
        'rename_file'            => 'Datei umbenennen',
        'new_filename'           => 'Neuer Dateiname',
        'invalid_filename_chars' => 'Dateiname darf folgende Zeichen nicht enthalten: \\/:*?"<>|',
        'confirm'                => 'Bestätigen',
        'media_player'           => 'Mediaplayer',
        'playlist'               => 'Wiedergabeliste',
        'clear_list'             => 'Liste löschen',
        'toggle_list'            => 'Liste ausblenden',
        'picture_in_picture'     => 'Bild-in-Bild',
        'fullscreen'             => 'Vollbild',
        'music_player'           => 'Musikplayer',
        'play_pause'             => 'Wiedergabe/Pause',
        'previous_track'         => 'Vorheriger Track',
        'next_track'             => 'Nächster Track',
        'repeat_mode'            => 'Wiederholungsmodus',
        'toggle_floating_lyrics' => 'Schwebende Liedtexte',
        'clear_config'           => 'Konfiguration löschen',
        'custom_playlist'        => 'Benutzerdefinierte Wiedergabeliste',
        'volume'                 => 'Lautstärke',
        'update_playlist'        => 'Wiedergabeliste aktualisieren',
        'playlist_url'           => 'URL der Wiedergabeliste',
        'reset_default'          => 'Auf Standard zurücksetzen',
        'toggle_lyrics'          => 'Liedtexte ausblenden',
        'fetching_version'       => 'Version wird abgerufen...',
        'download_local'         => 'Lokal herunterladen',
        'change_language'        => 'Sprache ändern',
        'pause_playing'          => 'Wiedergabe pausieren',
        'start_playing'          => 'Wiedergabe starten',
        'manual_switch'          => 'Manuelles Umschalten',
        'auto_switch'            => 'Automatisches Umschalten',
        'switch_to'              => 'Wechseln zu:',
        'auto_play'              => 'Automatische Wiedergabe',
        'lyrics_load_failed'     => 'Liedtexte konnten nicht geladen werden',
        'order_play'             => 'Reihenfolge abspielen',
        'single_loop'            => 'Einzelschleife',
        'shuffle_play'           => 'Zufallswiedergabe',
        'playlist_click'         => 'Klicken in der Wiedergabeliste',
        'index'                  => 'Index',
        'song_name'              => 'Liedname',
        'no_lyrics'              => 'Keine Liedtexte verfügbar',
        'loading_lyrics'         => 'Liedtexte werden geladen...',
        'autoplay_blocked'       => 'Automatische Wiedergabe blockiert',
        'cache_cleared'               => 'Konfiguration gelöscht',
        'open_custom_playlist'        => 'Benutzerdefinierte Wiedergabeliste öffnen',
        'reset_default_playlist'      => 'Standard-Wiedergabeliste wiederhergestellt',
        'reset_default_error'         => 'Fehler beim Wiederherstellen der Standard-Wiedergabeliste',
        'reset_default_failed'        => 'Standard-Wiedergabeliste konnte nicht wiederhergestellt werden',
        'playlist_load_failed'        => 'Wiedergabeliste konnte nicht geladen werden',
        'playlist_load_failed_message'=> 'Fehler beim Laden der Wiedergabeliste',
        'hour_announcement'      => 'Stundenansage, es ist jetzt',
        'hour_exact'             => 'Uhr',
        'weekDays' => ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
        'labels' => [
            'year' => 'Jahr',
            'month' => 'Monat',
            'day' => 'Tag',
            'week' => 'Woche'
        ],
        'hour_announcement' => 'Stundenansage, es ist jetzt',
        'hour_exact' => 'Uhr',
        'error_loading_time' => 'Fehler beim Anzeigen der Zeit',
        'switch_to_light_mode' => 'Zum hellen Modus wechseln',
        'switch_to_dark_mode' => 'Zum dunklen Modus wechseln',
        'current_mode_dark' => 'Aktueller Modus: Dunkelmodus',
        'current_mode_light' => 'Aktueller Modus: Hellmodus',
        'fetching_version' => 'Version wird abgerufen...',
        'latest_version' => 'Neueste Version:',
        'unable_to_fetch_version' => 'Neueste Version konnte nicht abgerufen werden',
        'request_failed' => 'Anfrage fehlgeschlagen, bitte später erneut versuchen',
        'pip_not_supported' => 'Das aktuelle Medium unterstützt Bild-in-Bild nicht',
        'pip_operation_failed' => 'Bild-in-Bild-Operation fehlgeschlagen',
        'exit_picture_in_picture' => 'Bild-in-Bild beenden',
        'picture_in_picture' => 'Bild-in-Bild',
        'hide_playlist' => 'Wiedergabeliste ausblenden',
        'show_playlist' => 'Wiedergabeliste anzeigen',
        'enter_fullscreen' => 'Vollbildmodus aktivieren',
        'exit_fullscreen' => 'Vollbildmodus beenden',
        'confirm_update_php' => 'Möchten Sie die PHP-Konfiguration wirklich aktualisieren?',
        'select_files_to_delete' => 'Bitte wählen Sie Dateien zum Löschen aus!',
        'confirm_batch_delete' => 'Möchten Sie die ausgewählten %d Dateien wirklich löschen?',
        'selected_info' => '%d Dateien ausgewählt, insgesamt %s MB'
    ],

    'fr' => [
        'select_language'        => 'Choisir la langue',
        'simplified_chinese'     => 'Chinois simplifié',
        'traditional_chinese'    => 'Chinois traditionnel',
        'english'                => 'Anglais',
        'korean'                 => 'Coréen',
        'vietnamese'             => 'Vietnamien',
        'thailand'                    => 'Thaï',
        'japanese'               => 'Japonais',
        'russian'                => 'Russe',
        'germany'                => 'Allemand',
        'france'                 => 'Français',
        'arabic'                 => 'Arabe',
        'spanish'                => 'Espagnol',
        'close'                  => 'Fermer',
        'save'                   => 'Enregistrer',
        'theme_download'         => 'Télécharger le thème',
        'select_all'             => 'Tout sélectionner',
        'batch_delete'           => 'Supprimer les fichiers sélectionnés par lot',
        'total'                  => 'Total :',
        'free'                   => 'Libre :',
        'hover_to_preview'       => 'Cliquez pour activer l\'aperçu',
        'mount_info'             => 'Point de montage : {{mount}}｜Espace utilisé : {{used}}',
        'spectra_config'         => 'Gestion des configurations Spectra',
        'current_mode'           => 'Mode actuel : Chargement...',
        'toggle_mode'            => 'Changer de mode',
        'check_update'           => 'Vérifier les mises à jour',
        'batch_upload'           => 'Sélectionner des fichiers pour un téléversement par lot',
        'add_to_playlist'        => 'Ajouter à la liste de lecture',
        'clear_background'       => 'Effacer l\'arrière-plan',
        'clear_background_label' => 'Effacer l\'arrière-plan',
        'file_list'              => 'Liste des fichiers',
        'component_bg_color'     => 'Choisir la couleur d\'arrière-plan du composant',
        'page_bg_color'          => 'Choisir la couleur d\'arrière-plan de la page',
        'toggle_font'            => 'Changer de police',
        'filename'               => 'Nom :',
        'filesize'               => 'Taille :',
        'duration'               => 'Durée :',
        'resolution'             => 'Résolution :',
        'bitrate'                => 'Débit :',
        'type'                   => 'Type :',
        'image'                  => 'Image',
        'video'                  => 'Vidéo',
        'audio'                  => 'Audio',
        'document'               => 'Document',
        'delete'                 => 'Supprimer',
        'rename'                 => 'Renommer',
        'download'               => 'Télécharger',
        'set_background'         => 'Définir comme arrière-plan',
        'preview'                => 'Aperçu',
        'toggle_fullscreen'      => 'Activer/désactiver le mode plein écran',
        'supported_formats'      => 'Formats pris en charge : [ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Déposez les fichiers ici',
        'or'                     => 'ou',
        'select_files'           => 'Sélectionner les fichiers',
        'unlock_php_upload_limit'=> 'Déverrouiller la limite de téléversement PHP',
        'upload'                 => 'Téléverser',
        'cancel'                 => 'Annuler',
        'rename_file'            => 'Renommer le fichier',
        'new_filename'           => 'Nouveau nom du fichier',
        'invalid_filename_chars' => 'Le nom du fichier ne peut pas contenir les caractères suivants : \\/:*?"<>|',
        'confirm'                => 'Confirmer',
        'media_player'           => 'Lecteur multimédia',
        'playlist'               => 'Liste de lecture',
        'clear_list'             => 'Effacer la liste',
        'toggle_list'            => 'Masquer la liste',
        'picture_in_picture'     => 'Image dans l\'image',
        'fullscreen'             => 'Plein écran',
        'music_player'           => 'Lecteur de musique',
        'play_pause'             => 'Lecture/Pause',
        'previous_track'         => 'Piste précédente',
        'next_track'             => 'Piste suivante',
        'repeat_mode'            => 'Mode répétition',
        'toggle_floating_lyrics' => 'Paroles flottantes',
        'clear_config'           => 'Effacer la configuration',
        'custom_playlist'        => 'Liste de lecture personnalisée',
        'volume'                 => 'Volume',
        'update_playlist'        => 'Mettre à jour la liste de lecture',
        'playlist_url'           => 'URL de la liste de lecture',
        'reset_default'          => 'Réinitialiser par défaut',
        'toggle_lyrics'          => 'Masquer les paroles',
        'fetching_version'       => 'Récupération des informations de version...',
        'download_local'         => 'Télécharger localement',
        'change_language'        => 'Changer de langue',
        'pause_playing'          => 'Mettre en pause',
        'start_playing'          => 'Commencer la lecture',
        'manual_switch'          => 'Changement manuel',
        'auto_switch'            => 'Changement automatique',
        'switch_to'              => 'Changer pour :',
        'auto_play'              => 'Lecture automatique',
        'lyrics_load_failed'     => 'Échec du chargement des paroles',
        'order_play'             => 'Lecture en ordre',
        'single_loop'            => 'Lecture en boucle',
        'shuffle_play'           => 'Lecture aléatoire',
        'playlist_click'         => 'Cliquer sur la liste de lecture',
        'index'                  => 'Index',
        'song_name'              => 'Nom de la chanson',
        'no_lyrics'              => 'Pas de paroles disponibles',
        'loading_lyrics'         => 'Chargement des paroles...',
        'autoplay_blocked'       => 'Lecture automatique bloquée',
        'cache_cleared'               => 'Configuration effacée',
        'open_custom_playlist'        => 'Ouvrir une liste de lecture personnalisée',
        'reset_default_playlist'      => 'Réinitialisation de la liste de lecture par défaut',
        'reset_default_error'         => 'Erreur lors de la réinitialisation de la liste de lecture par défaut',
        'reset_default_failed'        => 'Échec de la réinitialisation de la liste de lecture par défaut',
        'playlist_load_failed'        => 'Échec du chargement de la liste de lecture',
        'playlist_load_failed_message'=> 'Erreur lors du chargement de la liste de lecture',
        'hour_announcement'      => 'Annonce de l\'heure, il est actuellement',
        'hour_exact'             => 'heure(s) pile',
        'weekDays' => ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        'labels' => [
            'year' => 'Année',
            'month' => 'Mois',
            'day' => 'Jour',
            'week' => 'Semaine'
        ],
        'hour_announcement' => 'Annonce de l\'heure, il est actuellement',
        'hour_exact' => 'heure(s) pile',
        'error_loading_time' => 'Erreur lors de l\'affichage de l\'heure',
        'switch_to_light_mode' => 'Passer au mode clair',
        'switch_to_dark_mode' => 'Passer au mode sombre',
        'current_mode_dark' => 'Mode actuel : Mode sombre',
        'current_mode_light' => 'Mode actuel : Mode clair',
        'fetching_version' => 'Récupération des informations de version...',
        'latest_version' => 'Dernière version :',
        'unable_to_fetch_version' => 'Impossible de récupérer la dernière version',
        'request_failed' => 'La requête a échoué, veuillez réessayer plus tard',
        'pip_not_supported' => 'Le média actuel ne prend pas en charge l\'image dans l\'image',
        'pip_operation_failed' => 'Échec de l\'opération image dans l\'image',
        'exit_picture_in_picture' => 'Quitter le mode image dans l\'image',
        'picture_in_picture' => 'Image dans l\'image',
        'hide_playlist' => 'Masquer la liste de lecture',
        'show_playlist' => 'Afficher la liste de lecture',
        'enter_fullscreen' => 'Activer le mode plein écran',
        'exit_fullscreen' => 'Quitter le mode plein écran',
        'confirm_update_php' => 'Êtes-vous sûr de vouloir mettre à jour la configuration PHP ?',
        'select_files_to_delete' => 'Veuillez d\'abord sélectionner les fichiers à supprimer !',
        'confirm_batch_delete' => 'Êtes-vous sûr de vouloir supprimer les %d fichiers sélectionnés ?',
        'selected_info' => '%d fichiers sélectionnés, total de %s Mo'
    ],

    'en' => [
        'select_language'        => 'Select Language',
        'simplified_chinese'     => 'Simplified Chinese',
        'traditional_chinese'    => 'Traditional Chinese',
        'english'                => 'English',
        'korean'                 => 'Korean',
        'vietnamese'             => 'Vietnamese',
        'thailand'                  => 'Thai',
        'japanese'               => 'Japanese',
        'russian'                => 'Russian',
        'germany'                => 'German',
        'france'                 => 'French',
        'arabic'                 => 'Arabic',
        'spanish'                => 'Spanish',
        'close'                  => 'Close',
        'save'                   => 'Save',
        'theme_download'         => 'Theme Download',
        'select_all'             => 'Select All',
        'batch_delete'           => 'Delete Selected Files',
        'spectra_config'         => 'Spectra Configuration',
        'total'                  => 'Total:',
        'free'                   => 'Free:',
        'hover_to_preview'       => 'Click to activate hover preview',
        'mount_info'             => 'Mount point: {{mount}}｜Used: {{used}}',
        'current_mode'           => 'Current Mode: Loading...',
        'toggle_mode'            => 'Toggle Mode',
        'check_update'           => 'Check for Updates',
        'batch_upload'           => 'Select Files for Batch Upload',
        'add_to_playlist'        => 'Add Selected to Playlist',
        'clear_background'       => 'Clear Background',
        'clear_background_label' => 'Clear Background',
        'file_list'              => 'File List',
        'component_bg_color'     => 'Select Component Background Color',
        'page_bg_color'          => 'Select Page Background Color',
        'toggle_font'            => 'Toggle Font',
        'filename'               => 'Name:',
        'filesize'               => 'Size:',
        'duration'               => 'Duration:',
        'resolution'             => 'Resolution:',
        'bitrate'                => 'Bitrate:',
        'type'                   => 'Type:',
        'image'                  => 'Image',
        'video'                  => 'Video',
        'audio'                  => 'Audio',
        'document'               => 'Document',
        'delete'                 => 'Delete',
        'rename'                 => 'Rename',
        'download'               => 'Download',
        'set_background'         => 'Set Background',
        'preview'                => 'Preview',
        'toggle_fullscreen'      => 'Toggle Fullscreen',
        'supported_formats'      => 'Supported formats: [ jpg, jpeg, png, gif, mp4, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Drop files here',
        'or'                     => 'or',
        'select_files'           => 'Select Files',
        'unlock_php_upload_limit'=> 'Unlock PHP Upload Limit',
        'upload'                 => 'Upload',
        'cancel'                 => 'Cancel',
        'rename_file'            => 'Rename',
        'new_filename'           => 'New Filename',
        'invalid_filename_chars' => 'Filename cannot contain the following characters: \\/:*?"<>|',
        'confirm'                => 'Confirm',
        'media_player'           => 'Media Player',
        'playlist'               => 'Playlist',
        'clear_list'             => 'Clear List',
        'toggle_list'            => 'Toggle List',
        'picture_in_picture'     => 'Picture-in-Picture',
        'fullscreen'             => 'Fullscreen',
        'music_player'           => 'Music Player',
        'play_pause'             => 'Play/Pause',
        'previous_track'         => 'Previous Track',
        'next_track'             => 'Next Track',
        'repeat_mode'            => 'Repeat Mode',
        'toggle_floating_lyrics' => 'Floating Lyrics',
        'clear_config'           => 'Clear Config',
        'custom_playlist'        => 'Custom Playlist',
        'volume'                 => 'Volume',
        'update_playlist'        => 'Update Playlist',
        'playlist_url'           => 'Playlist URL',
        'reset_default'          => 'Reset to Default',
        'toggle_lyrics'          => 'Toggle Lyrics',
        'fetching_version'       => 'Fetching version info...',
        'download_local'         => 'Download Locally',
        'change_language'        => 'Change Language',
        'pause_playing'          => 'Pause Playing',
        'start_playing'          => 'Start Playing',
        'manual_switch'          => 'Manual Switch',
        'auto_switch'            => 'Auto Switch to',
        'switch_to'              => 'Switch to',
        'auto_play'              => 'Auto Play',
        'lyrics_load_failed'     => 'Lyrics Load Failed',
        'order_play'             => 'Order Play',
        'single_loop'            => 'Single Loop',
        'shuffle_play'           => 'Shuffle Play',
        'playlist_click'         => 'Playlist Click',
        'index'                  => 'Index',
        'song_name'              => 'Song Name',
        'no_lyrics'              => 'No Lyrics Available',
        'loading_lyrics'         => 'Loading Lyrics...',
        'autoplay_blocked'       => 'Autoplay Blocked',
        'cache_cleared'               => 'Cache Cleared',
        'open_custom_playlist'        => 'Open Custom Playlist',
        'reset_default_playlist'      => 'Default Playlist Link Restored',
        'reset_default_error'         => 'Error Restoring Default Link',
        'reset_default_failed'        => 'Failed to Restore Default Link',
        'playlist_load_failed'        => 'Failed to Load Playlist',
        'playlist_load_failed_message'=> 'Failed to Load Playlist',
        'hour_announcement_en'   => "It's",  
        'hour_exact_en'          => "o'clock",
        'weekDays' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        'labels' => [
            'year' => ' Year ',
            'month' => ' Month ',
            'day' => ' Day ',
            'week' => ''
        ],
        'hour_announcement_en' => "It's",
        'hour_exact_en' => "o'clock",
        'error_loading_time' => 'Error loading time',
        'switch_to_light_mode' => 'Switch to Light Mode',
        'switch_to_dark_mode' => 'Switch to Dark Mode',
        'current_mode_dark' => 'Current Mode: Dark Mode',
        'current_mode_light' => 'Current Mode: Light Mode',
        'fetching_version' => 'Fetching version info...',
        'latest_version' => 'Latest Version:',
        'unable_to_fetch_version' => 'Unable to fetch the latest version info',
        'request_failed' => 'Request failed, please try again later',
        'pip_not_supported' => 'Current media does not support Picture-in-Picture',
        'pip_operation_failed' => 'Picture-in-Picture operation failed',
        'exit_picture_in_picture' => 'Exit Picture-in-Picture',
        'picture_in_picture' => 'Picture-in-Picture',
        'hide_playlist' => 'Hide Playlist',
        'show_playlist' => 'Show Playlist',
        'enter_fullscreen' => 'Enter Fullscreen',
        'exit_fullscreen' => 'Exit Fullscreen',
        'confirm_update_php' => 'Are you sure you want to update PHP configuration?',
        'select_files_to_delete' => 'Please select files to delete first!',
        'confirm_batch_delete' => 'Are you sure you want to delete the selected %d files?',
        'selected_info' => 'Selected %d files, total %s MB'
    ]
];

if (!file_exists($langFilePath)) {
    file_put_contents($langFilePath, $defaultLang);
    chmod($langFilePath, 0644);
}

function getSavedLanguage() {
    global $langFilePath, $langData, $defaultLang;
    $savedLang = @trim(file_get_contents($langFilePath));
    return isset($langData[$savedLang]) ? $savedLang : $defaultLang;
}

function saveLanguage($lang) {
    global $langFilePath, $langData;
    if (isset($langData[$lang])) {
        file_put_contents($langFilePath, $lang);
    }
}

function __($key) {
    global $translations;
    return $translations[$key] ?? $key;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lang'])) {
    saveLanguage($_POST['lang']);
    echo 'Language updated to ' . $_POST['lang'];
    exit;
}

$currentLang = getSavedLanguage();
$translations = $langData[$currentLang];
?>

<script>
const langData = <?php echo json_encode($langData); ?>;
const currentLang = "<?php echo $currentLang; ?>";
let translations = langData[currentLang] || langData['en'];

document.addEventListener("DOMContentLoaded", () => {
    const userLang = localStorage.getItem('language') || currentLang;

    updateLanguage(userLang); 
    updateFlagIcon(userLang);  
    document.getElementById("langSelect").value = userLang; 
});

function updateLanguage(lang) {
    localStorage.setItem('language', lang); 
    translations = langData[lang] || langData['en'];  

    const translateElement = (el, attribute, property) => {
        const translationKey = el.getAttribute(attribute);
        if (translations[translationKey]) {
            el[property] = translations[translationKey];
        }
    };

    document.querySelectorAll('[data-translate]').forEach(el => {
        const translationKey = el.getAttribute('data-translate');
        const dynamicContent = el.getAttribute('data-dynamic-content') || '';

        if (translations[translationKey]) {
            if (el.tagName === 'OPTGROUP') {
                el.setAttribute('label', translations[translationKey]);
            } else {
                el.innerText = translations[translationKey] + dynamicContent; 
            }
        }
    });

    document.querySelectorAll('[data-translate-title]').forEach(el => {
        translateElement(el, 'data-translate-title', 'title');
    });

    document.querySelectorAll('[data-translate-placeholder]').forEach(el => {
        const translationKey = el.getAttribute('data-translate-placeholder');
        if (translations[translationKey]) {
            el.setAttribute('placeholder', translations[translationKey]);
            el.setAttribute('aria-label', translations[translationKey]);  
            el.setAttribute('title', translations[translationKey]); 
        }
    });

    document.querySelectorAll('[data-translate]').forEach(el => {
        const translationKey = el.getAttribute('data-translate');
        if (translationKey && translations[translationKey]) {
            el.setAttribute('label', translations[translationKey]);  
        }
    });
}


function updateFlagIcon(lang) {
    const flagImg = document.getElementById('flagIcon');
    if (!flagImg) return;
    
    const flagMap = {
        'zh': '/luci-static/ipip/flags/cn.png',
        'hk': '/luci-static/ipip/flags/hk.png',
        'en': '/luci-static/ipip/flags/us.png',
        'kr': '/luci-static/ipip/flags/kr.png',
        'jp': '/luci-static/ipip/flags/jp.png',
        'ru': '/luci-static/ipip/flags/ru.png',
        'ar': '/luci-static/ipip/flags/sa.png',
        'es': '/luci-static/ipip/flags/es.png',
        'de': '/luci-static/ipip/flags/de.png',
        'fr': '/luci-static/ipip/flags/fr.png',
        'th': '/luci-static/ipip/flags/th.png',
        'vn': '/luci-static/ipip/flags/vn.png'
    };
    
    flagImg.src = flagMap[lang] || flagMap['en'];
}

function changeLanguage(lang) {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'lang=' + lang
    }).then(response => response.text())
      .then(data => {
          console.log(data); 
          updateLanguage(lang);  
          updateFlagIcon(lang);  
      });
}
</script>

