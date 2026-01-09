<?php
ini_set('memory_limit', '256M');
$base_dir = __DIR__;
$upload_dir = $base_dir . '/stream';
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mkv', 'mp3', 'wav', 'flac', 'ogg'];
$background_type = '';
$background_src = '';
$lang = $_POST['lang'] ?? $_GET['lang'] ?? 'en';
$lang = isset($langData[$lang]) ? $lang : 'en';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    $files = $_FILES['upload_file'];
    $upload_errors = [];
    
    foreach ($files['name'] as $key => $filename) {
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $raw_filename = urldecode($filename);
            
            $ext = strtolower(pathinfo($raw_filename, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_types)) {
                $upload_errors[] = sprintf($langData[$lang]['upload_error_type_not_supported'] ?? 'Unsupported file type: %s', $raw_filename);
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
                $upload_errors[] = sprintf($langData[$lang]['upload_error_move_failed'] ?? 'Upload failed: %s', $final_name);
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
    $file = $upload_dir . '/' . basename($_GET['delete']);
    if (file_exists($file)) {
        unlink($file);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

if (isset($_POST['rename'])) {
    $oldName = $_POST['old_name'];
    $newName = trim($_POST['new_name']);

    $oldPath = $upload_dir . DIRECTORY_SEPARATOR . basename($oldName);
    $newPath = $upload_dir . DIRECTORY_SEPARATOR . basename($newName);

    $error = '';
    if (!file_exists($oldPath)) {
        $error = 'Original file does not exist';
    } elseif ($newName === '') {
        $error = 'File name cannot be empty';
    } elseif (preg_match('/[\\\\\/:*?"<>|]/', $newName)) {
        $error = 'Contains invalid characters: \/:*?"<>|';
    } elseif (file_exists($newPath)) {
        $error = 'Target file already exists';
    }

    if (!$error) {
        if (rename($oldPath, $newPath)) {
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            $error = 'Operation failed (permissions/character issues)';
        }
    }

    if ($error) {
        echo '<div class="alert alert-danger mb-3">Error: ' 
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
        $error = "File not found: " . htmlspecialchars($file);
    }
}

if (isset($_POST['batch_delete'])) {
    $deleted_files = [];
    foreach ($_POST['filenames'] as $filename) {
        $file = $upload_dir . '/' . basename($filename);
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
$files = array_filter($files, function ($file) use ($upload_dir) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    return !in_array(strtolower($ext), ['php', 'txt', 'json']) && basename($file) !== 'shares' && !is_dir($upload_dir . DIRECTORY_SEPARATOR . $file);
});

if (isset($_GET['background'])) {
    $background_src = 'stream/' . htmlspecialchars($_GET['background']);
    $ext = strtolower(pathinfo($background_src, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        $background_type = 'image';
    } elseif (in_array($ext, ['mp4', 'webm', 'mkv'])) {
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

<head>
    <meta charset="utf-8">
    <title>Media File Management</title>
    <?php include './spectra.php'; ?>
    <script>
        const phpBackgroundType = '<?= $background_type ?>';
        const phpBackgroundSrc = '<?= $background_src ?>';
    </script>

    <style>
      #mainContainer { display: none; }
    </style>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        try {
            const container = document.getElementById('mainContainer');
              if (!container) return;

              const isFullscreen = localStorage.getItem('fullscreenState') === 'true';
        
              container.classList.toggle('container-fluid', isFullscreen);
              container.classList.toggle('container-sm', !isFullscreen);

              container.style.display = 'block';
        
              const toggleBtn = document.getElementById('toggleScreenBtn');
              if (toggleBtn) {
                  const icon = toggleBtn.querySelector('i');
                  icon.className = isFullscreen ? 'bi-fullscreen-exit' : 'bi-arrows-fullscreen';
              }

              toggleBtn.addEventListener('click', function() {
                  const isNowFullscreen = container.classList.contains('container-fluid');
                  const icon = this.querySelector('i');
            
                  container.classList.toggle('container-fluid', !isNowFullscreen);
                  container.classList.toggle('container-sm', isNowFullscreen);
            
                  icon.className = isNowFullscreen ? 'bi-arrows-fullscreen' : 'bi-fullscreen-exit';
                  localStorage.setItem('fullscreenState', !isNowFullscreen);
              });

          } catch (error) {
              const container = document.getElementById('mainContainer');
              if (container) container.style.display = 'block';
          }
      });
    </script>

<style>
html,
body {
        color: var(--text-primary);
        -webkit-backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        font-family: 'Fredoka One', cursive;
        font-weight: 400; 
        background: inherit !important;
        font-family: var(--font-family, -apple-system, BlinkMacSystemFont, sans-serif);
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
        margin-top: 15px !important;
        box-shadow: 1px 0 3px -2px color-mix(in oklch, var(--bg-container), black 30%) !important;
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

.card {
	background: var(--card-bg);
	border: 1px solid var(--border-color);
        border-radius: 1rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        overflow: hidden;

}

.card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.card img,
.card video {
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
}

.card-header {
	background: var(--card-bg) !important;
	border-bottom: 1px solid var(--card-bg);
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
        background-color: var(--sand-bg);

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

.d-flex {
        white-space: nowrap;
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
	font-size: 1.2em;
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
	position: relative;
	border: 2px dashed transparent;
	border-radius: 10px;
	padding: 2rem;
	z-index: 1;
}

.drop-zone::before {
	content: "";
	position: absolute;
	top: -2px;
	left: -2px;
	right: -2px;
	bottom: -2px;
	z-index: -1;
	border: 2px dashed var(--bs-primary);
	border-radius: 10px;
	animation: border-wave 2s linear infinite;
	pointer-events: none;
	mask-image: linear-gradient(90deg, #000 50%, transparent 0%);
	mask-size: 10px 100%;
	mask-repeat: repeat;
	-webkit-mask-image: linear-gradient(90deg, #000 50%, transparent 0%);
	-webkit-mask-size: 10px 100%;
	-webkit-mask-repeat: repeat;
}

@keyframes border-wave {
	0% {
		mask-position: 0 0;
	}

	100% {
		mask-position: 100% 0;
	}
}

.drop-zone:hover::before {
	border-color: var(--accent-color);
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
	transform: scale(1.1) !important;
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
.custom-btn {
    padding: 4px 8px;
    font-size: 14px;
    gap: 4px;
}

.custom-btn i {
    font-size: 16px;
}

.d-flex .custom-btn {
    margin: 0 4px;
}

.share-btn.custom-btn {
    background-color: #ffc107;
    color: #fff;
    padding: 6px 8px;
    font-size: 14px;
}

.share-btn.custom-btn i {
    font-size: 16px;
}

.set-bg-btn.custom-btn {
    background-color: #17a2b8;
    color: #fff;
    padding: 6px 8px;
    font-size: 14px;
}

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
    width: 100%;
    object-fit: contain;
}

#previewAudio {
    width: auto;
    max-width: 80%;
    margin: 20px auto 0;
    display: block;
    border-radius: 10px;
    padding: 5px 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

#previewAudio.d-none {
    display: none;
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

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.drag-handle {
    cursor: grab;
    z-index: 10;
    user-select: none;
}
.sortable-chosen .drag-handle {
    cursor: grabbing;
}

[data-filename] {
    cursor: grab;
}
.sortable-chosen {
    cursor: grabbing !important;
}

.upload-area i {
    animation: pulse 1s infinite;
}

.file-checkbox-wrapper {
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
}

.fileCheckbox:checked + .file-checkbox-wrapper,
.file-checkbox-wrapper.force-visible {
    opacity: 1 !important;
}

@media (min-width: 768px) {
    .card:hover .file-checkbox-wrapper:not(.force-visible) {
        opacity: 1;
    }
}

@media (max-width: 767.98px) {
    .file-checkbox-wrapper {
        opacity: 1 !important;
    }
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
    box-sizing: border-box;
    white-space: nowrap !important;
    overflow: visible !important;
  }

  .time-display > span:nth-child(1),
  .time-display > span:nth-child(2) {
    flex: 0 0 33.33% !important;
    order: 1;
  }

  .time-display > span:nth-child(3),
  .time-display > span:nth-child(4) {
    flex: 0 0 100% !important;
    order: 2;
    margin-top: 0.25rem !important;
    text-align: center !important;
  }

  .lunar-text {
    font-size: 1.05rem !important;
    letter-spacing: -0.3px !important;
  }
}

@media (max-width: 576px) {
  #fontToggleBtn {
    margin-right: 8px;
  }
  #langBtnWrapper {
    margin-left: 6px;
  }
}

@media (max-width: 576px) {
  .share-btn.custom-btn {
    display: none !important;
  }
}

@media (max-width: 576px) {
  .custom-btn {
    margin-right: 0px !important;
  }
}

@media (max-width: 575.98px) {
  #fontToggleBtn {
    min-height: 26px;
    padding: 8px 14px;
  }

  #fontToggleBtn i {
    font-size: 1.1rem;
  }
}

@media (max-width: 576px) {
    .card-body .custom-btn {
        padding: 0.2rem 0.3rem !important;
        font-size: 0.8rem !important;
    }
}

@media (max-width: 576px) {
    #fileGrid .card {
        padding: 0.25rem;
    }

    #fileGrid .card .card-body {
        padding: 0.25rem;
    }

    #fileGrid .card h5,
    #fileGrid .card p,
    #fileGrid .card .file-info-overlay p {
        font-size: 0.75rem;
        margin: 0.125rem 0;
    }

    #fileGrid .card .preview-container {
        max-height: 180px;
        overflow: hidden;
    }

    #fileGrid .card .custom-btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 768px) {
    .btn i {
        font-size: 0.8rem !important;
        margin-left: 3px;
    }
}

@media (max-width: 575.98px) {
  #selectAll-container input[type="checkbox"] {
    transform: scale(1) !important;
    width: 1em !important;
    height: 1em !important;
    margin-left: 0 !important;
    margin-right: 0.3rem !important;
    flex-shrink: 0;
  }

  #selectAll-container {
    flex-wrap: nowrap !important;
    gap: 0.2rem !important;
    overflow-x: auto;
  }

  #selectAll-container input[type="checkbox"] {
    margin-right: 0.15rem !important;
  }

  #selectAll-container label[for="selectAll"] {
    margin-right: 0.2rem !important;
  }

  #selectAll-container input[type="color"],
  #selectAll-container button {
    margin-right: 0.2rem !important;
  }
}

</style>

<div class="container-sm container-bg text-center mt-4" id="mainContainer">
   <div class="alert alert-secondary d-none" id="toolbar">
        <div class="d-flex justify-content-between flex-column flex-sm-row">
            <div>
                <button class="btn btn-outline-primary" id="selectAllBtn" data-translate="select_all"></button>
                <span id="selectedInfo"></span>
            </div>
            <button class="btn btn-danger" id="batchDeleteBtn" data-translate="batch_delete"></button>
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
    <div class="weather-display d-flex align-items-center d-none d-sm-inline">
      <i id="weatherIcon" class="wi wi-na" style="font-size:28px; margin-right:4px;"></i>
      <span id="cityNameDisplay" style="color:var(--accent-color); font-weight:700;"></span>
      <span id="weatherText" style="color:var(--accent-color); font-weight: 700;"></span>
    </div>
</div>
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center text-center gap-2">
        <h5 class="mb-0" style="line-height: 40px; height: 40px;" data-translate="spectra_config"></h5>
        <p id="status" class="mb-0"><span data-translate="current_mode"></span></p>
        <button id="toggleButton" onclick="toggleConfig()" class="btn btn-primary" data-translate="toggle_mode"></button>
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
                while ($bytes >= 1024 && $index < count($units) - 1) {
                    $bytes /= 1024;
                    $index++;
                }
                return round($bytes, 2) . ' ' . $units[$index];
            }
            ?>  
            <div class="me-3 d-flex gap-2 mt-2 ps-2" 
                 data-translate-tooltip="mount_info" data-mount-point="<?= $mountPoint ?>" data-used-space="<?= $usedSpace ? formatSize($usedSpace) : 'N/A' ?>" data-tooltip="">
                <span class="btn btn-primary btn-sm mb-2 d-none d-sm-inline"><i class="bi bi-hdd"></i> <span data-translate="total">Total：</span><?= $totalSpace ? formatSize($totalSpace) : 'N/A' ?></span>
                <span class="btn btn-success btn-sm mb-2  d-none d-sm-inline"><i class="bi bi-hdd"></i> <span data-translate="free">Free：</span><?= $freeSpace ? formatSize($freeSpace) : 'N/A' ?></span>
            </div>
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#updateConfirmModal" data-translate-tooltip="check_update"><i class="fas fa-cloud-download-alt"></i> <span class="btn-label"></span></button>
            <button class="btn btn-warning ms-2" data-bs-toggle="modal" data-bs-target="#uploadModal" data-translate-tooltip="batch_upload"><i class="bi bi-upload"></i> <span class="btn-label"></span></button>
            <button class="btn btn-primary ms-2" id="openPlayerBtn" data-bs-toggle="modal" data-bs-target="#playerModal" data-translate-tooltip="add_to_playlist"><i class="bi bi-play-btn"></i> <span class="btn-label"></span></button>
            <button class="btn btn-success ms-2 d-none d-sm-inline" id="toggleScreenBtn" data-translate-tooltip="toggle_fullscreen"><i class="bi bi-arrows-fullscreen"></i></button>
            <button type="button" class="btn btn-primary ms-2 d-none d-sm-inline" onclick="showIpDetailModal()" data-translate-tooltip="ip_info"><i class="fa-solid fa-satellite-dish"></i></button>
            <button class="btn btn-danger ms-2" id="clearBackgroundBtn" data-translate-tooltip="clear_background"><i class="bi bi-trash"></i> <span class="btn-label"></span></button> 
        </div>
    </div>
        <h2 class="mt-3 mb-0" data-translate="file_list">File List</h2>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="d-flex align-items-center mb-3 ps-2" id="selectAll-container">
            <input type="checkbox" id="selectAll" class="form-check-input me-2 shadow-sm" style="width: 1.05em; height: 1.05em; border-radius: 0.35em; margin-left: 1px; transform: scale(1.2)">
            <label for="selectAll" class="form-check-label fs-5 ms-1" style="margin-right: 10px;" data-translate="select_all">Select All</label>

        <div class="ms-auto" style="margin-right: 20px;">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#langModal">
                <img id="flagIcon" src="/luci-static/ipip/flags/<?php echo $flagFile; ?>" style="width:30px; height:22px; object-fit: contain;">
                <span data-translate="change_language">Change Language</span>
            </button>
        </div>
    </div>
        <?php
            $history_file = './lib/background_history.txt';
            $background_history = file_exists($history_file) ? file($history_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

            usort($files, function ($a, $b) use ($background_history) {
                $posA = array_search($a, $background_history);
                $posB = array_search($b, $background_history);
                return ($posA === false ? PHP_INT_MAX : $posA) - ($posB === false ? PHP_INT_MAX : $posB);
            });
        ?>
        <div  id="fileGrid" class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4">
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
                        $resolution = 'Resolution cannot be obtained';
                        $duration = 'Duration cannot be obtained';
                        $bitrate = 'Bitrate cannot be obtained';
                    }
                } elseif ($isAudio) { 
                    $ffmpegPath = '/usr/bin/ffmpeg';
                    $cmd = "$ffmpegPath -i \"$path\" 2>&1";
                    $output = shell_exec($cmd);

                    if ($output) {
                        if (preg_match('/Duration:\s*(\d+):(\d+):(\d+)/', $output, $matches)) {
                            $duration = sprintf("%02d:%02d:%02d", $matches[1], $matches[2], $matches[3]);
                        }

                        if (preg_match('/bitrate:\s*(\d+)\s*kb\/s/', $output, $matches) || 
                           preg_match('/Stream.*Audio:.*?(\d+)\s*kb\/s/', $output, $matches)) {
                            $bitrate = $matches[1] . ' kbps';
                        }
                    } else {
                        $duration = 'Duration cannot be obtained';
                        $bitrate = 'Bitrate cannot be obtained';
                    }
                }
            ?>
            <div class="col" data-filename="<?= htmlspecialchars($file) ?>">
                <div class="card h-100 shadow-sm position-relative"> 
                    <div class="file-checkbox-wrapper position-absolute start-0 top-0 m-2 z-2">
                        <input type="checkbox" 
                               class="fileCheckbox form-check-input shadow" 
                               value="stream/<?= htmlspecialchars($file) ?>"
                               data-size="<?= $size ?>"
                               style="width: 1.05em !important; height: 1.05em !important; border-radius: 0.35em; transform: scale(1.2);">
                    </div>
                    <div class="position-relative">
                        <?php if ($isMedia): ?>
                        <div class="preview-container">
                            <div class="file-type-indicator">
                                <?php if ($isImage): ?>
                                    <i class="fas fa-image text-white"></i>
                                    <span class="text-white small" data-translate="image">Image</span>
                                <?php elseif ($isVideo): ?>
                                    <i class="fas fa-play-circle text-white"></i>
                                    <span class="text-white small" data-translate="video">Video</span>
                                <?php elseif ($isAudio): ?>
                                    <i class="fas fa-music text-white"></i>
                                    <span class="text-white small" data-translate="audio">Audio</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($isImage): ?>
                                <img src="stream/<?= htmlspecialchars($file) ?>" 
                                     class="card-img-top preview-img img-fluid"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#previewModal"
                                     data-type="image"
                                      data-src="stream/<?= htmlspecialchars($file) ?>">
                            <?php elseif ($isVideo): ?>
                                <video class="card-img-top preview-video"
                                       data-bs-toggle="modal"
                                       data-bs-target="#previewModal"
                                       data-type="video"
                                       data-src="stream/<?= htmlspecialchars($file) ?>">
                                    <source src="stream/<?= htmlspecialchars($file) ?>" type="video/mp4">
                                    <source src="stream/<?= htmlspecialchars($file) ?>" type="video/webm">
                                    <source src="stream/<?= htmlspecialchars($file) ?>" type="video/ogg">
                                </video>
                            <?php elseif ($isAudio): ?>
                                <div class="preview-audio-container" 
                                     data-bs-toggle="modal" 
                                     data-bs-target="#previewModal"
                                     data-src="stream/<?= htmlspecialchars($file) ?>"
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
                                <button class="btn btn-danger custom-btn" onclick="handleDeleteConfirmation('<?= urlencode($file) ?>')" data-translate-tooltip="delete"><i class="bi bi-trash"></i></button>
                                <button class="btn btn-primary custom-btn" data-bs-toggle="modal" data-bs-target="#renameModal-<?= md5($file) ?>" data-translate-tooltip="rename"><i class="bi bi-pencil"></i></button>
                                <a href="?download=<?= urlencode($file) ?>" class="btn btn-success custom-btn"><i class="bi bi-download" data-translate-tooltip="download"></i></a>   
                                <button class="btn btn-warning share-btn custom-btn"data-filename="<?= htmlspecialchars($file) ?>"data-bs-toggle="modal"data-bs-target="#shareModal" data-translate-tooltip="shareLinkLabel"><i class="bi bi-share"></i></button>
                                <?php if ($isMedia): ?>
                                <button class="btn btn-info set-bg-btn custom-btn" 
                                        data-src="<?= htmlspecialchars($file) ?>"
                                        data-type="<?= $isVideo ? 'video' : ($isAudio ? 'audio' : 'image') ?>"
                                        onclick="setBackground('<?= htmlspecialchars($file) ?>')"
                                        data-translate-tooltip="set_background">
                                    <i class="bi <?= $isVideo ? 'bi-play-btn' : ($isAudio ? 'bi-music-note-beamed' : 'bi-image') ?>"></i>
                                </button>
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

<div class="modal fade" id="previewModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
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
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" data-translate="cancel">Cancel</button>
                <button class="btn btn-info me-2" id="fitTogglePreview" data-translate="fit_cover">Cover</button>
                <button class="btn btn-primary" id="fullscreenToggle" data-translate="toggle_fullscreen">Toggle Fullscreen</button>
            </div>
        </div>
    </div>
</div>

<form id="batchDeleteForm" method="post" style="display: none;">
    <input type="hidden" name="batch_delete" value="1">
</form>
    </div>

<div class="modal fade" id="uploadModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
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
                            <i class="fas fa-cloud-upload-alt text-primary mb-3" style="font-size: 4rem;"></i>
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
<div class="modal fade" id="renameModal-<?= md5($file) ?>" tabindex="-1" aria-labelledby="renameModalLabel-<?= md5($file) ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" action="">
            <input type="hidden" name="old_name" value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>">

            <div class="modal-header">
                <h5 class="modal-title" id="renameModalLabel-<?= md5($file) ?>" data-translate="rename_file">
                    <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="newName-<?= md5($file) ?>" data-translate="new_filename">New Filename</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="newName-<?= md5($file) ?>" 
                        name="new_name"
                        value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>"
                        data-translate-title="invalid_filename_chars"
                    >
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" name="rename" data-translate="confirm">Rename</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

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
                    <option value="ko" data-translate="korean">Korean</option>
                    <option value="vi" data-translate="vietnamese">Vietnamese</option>
                    <option value="th" data-translate="thailand">Thailand</option>
                    <option value="ja" data-translate="japanese"></option>
                    <option value="ru" data-translate="russian"></option>
                    <option value="de" data-translate="germany">Germany</option>
                    <option value="fr" data-translate="france">France</option>
                    <option value="ar" data-translate="arabic"></option>
                    <option value="es" data-translate="spanish">spanish</option>
                    <option value="bn" data-translate="bangladesh">Bangladesh</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="close">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="playerModal" tabindex="-1" aria-labelledby="playerModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="playerModalLabel" data-translate="media_player"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body  p-0 m-0 d-flex flex-column" style="height: 65vh;">
                <div class="row g-3 flex-grow-1 h-100">
                    <div class="col-md-8 d-flex flex-column h-100">
                        <div class="ratio ratio-16x9 bg-dark rounded flex-grow-1 position-relative">
                            <video id="mainPlayer" controls class="w-100 h-100 d-none"></video>
                            <img id="imagePlayer" class="w-100 h-100 d-none object-fit-contain">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex flex-column h-100">
                        <h6 class="pt-3 mb-3" data-translate="playlist"></h6>
                        <div class="list-group flex-grow-1 overflow-auto" id="playlistContainer"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-danger" id="clearPlaylist">
                    <i class="bi bi-trash"></i>
                    <span data-translate="clear_list"></span>
                </button>
                <button class="btn btn-sm btn-info" id="fitTogglePlayer">
                    <i class="bi bi-aspect-ratio"></i>
                    <span data-translate="fit_cover">Cover</span>
                </button>
                <button class="btn btn-sm btn-primary" id="togglePlaylist">
                    <i class="bi bi-list-ul"></i>
                    <span data-translate="toggle_list"></span>
                </button>
                <button class="btn btn-sm btn-info" id="togglePip" style="display: none;">
                    <i class="bi bi-pip"></i>
                    <span data-translate="picture_in_picture"></span>
                </button>
                <button class="btn btn-sm btn-success" id="toggleFullscreen">
                    <i class="bi bi-arrows-fullscreen"></i>
                    <span data-translate="fullscreen"></span>
                </button>
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                    <span data-translate="close"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateConfirmModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" data-translate="theme_download"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="currentVersionInfo" class="alert alert-info" data-translate="current_version"></div>
                <div id="themeVersionInfo" class="alert alert-warning" data-translate="fetching_version"></div>
                <textarea id="copyCommand" class="form-control" rows="3" readonly>
opkg update && opkg install wget grep sed && LATEST_FILE=$(wget -qO- https://github.com/Thaolga/openwrt-nekobox/releases/expanded_assets/1.8.8 | grep -o 'luci-theme-spectra_[0-9A-Za-z.\-_]*_all.ipk' | head -n1) && wget -O /tmp/"$LATEST_FILE" "https://github.com/Thaolga/openwrt-nekobox/releases/download/1.8.8/$LATEST_FILE" && opkg install --force-reinstall /tmp/"$LATEST_FILE" && rm -f /tmp/"$LATEST_FILE"
</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel"></button>
                <a id="confirmUpdateLink" href="#" class="btn btn-danger" target="_blank" data-translate="download_local"></a>
                <button id="copyCommandBtn" class="btn btn-info" data-translate="copy_command"></button>
                <button id="updatePluginBtn" class="btn btn-primary" data-translate="update_plugin">update_plugin</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade custom-modal" id="ipDetailModal" tabindex="-1" role="dialog" aria-labelledby="ipDetailModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl draggable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ipDetailModalLabel" data-translate="ip_info">IP Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="detail-row">
                    <span class="detail-label" data-translate="ip_address">IP Address</span>
                    <span class="detail-value"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label" data-translate="location">Location</span>
                    <span class="detail-value"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label" data-translate="isp">ISP</span>
                    <span class="detail-value"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">ASN</span>
                    <span class="detail-value"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label" data-translate="timezone">Timezone</span>
                    <span class="detail-value"></span>
                </div>
                <div class="detail-row map-coord-row" style="display: none;">
                    <span class="detail-label" data-translate="latitude_longitude">Coordinates</span>
                    <span class="detail-value"></span>
                </div>
                <div class="detail-row map-container" style="height: 400px; margin-top: 20px; display: none;">
                    <div id="leafletMap" style="width: 100%; height: 100%;"></div>
                </div>
                <h5 style="margin-top: 15px;" data-translate="latency_info">Latency Info</h5>
                <div class="detail-row" id="delayInfo" style="display: flex; flex-wrap: wrap;"></div>
                </div>
              <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel"></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="shareModalLabel" data-translate="createShareLink">Create Share Link</h5>
        <button type="button" class="btn-close" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="shareForm">
          <div class="mb-3">
            <label for="expireTime" class="form-label" data-translate="expireTimeLabel">Expiration Time</label>
            <select class="form-select" id="expireTime" name="expire">
              <option value="3600" data-translate="expire1Hour">1 Hour</option>
              <option value="86400" selected data-translate="expire1Day">1 Day</option>
              <option value="604800" data-translate="expire7Days">7 Days</option>
              <option value="2592000" data-translate="expire30Days">30 Days</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="maxDownloads" class="form-label" data-translate="maxDownloadsLabel">Max Downloads</label>
            <select class="form-select" id="maxDownloads" name="max_downloads">
              <option value="1" data-translate="max1Download">1 Time</option>
              <option value="5" data-translate="max5Downloads">5 Time</option>
              <option value="10" data-translate="max10Downloads">10 Time</option>
              <option value="0" selected data-translate="maxUnlimited">Unlimited</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="shareLink" class="form-label" data-translate="shareLinkLabel">Share Link</label>
            <div class="input-group">
              <input type="text" class="form-control" id="shareLink" readonly>
              <button class="btn btn-outline-secondary" type="button" id="copyLinkBtn" data-translate-title="copyLinkButton">
                <i class="bi bi-clipboard"></i>
              </button>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa fa-times" aria-hidden="true"></i> <span data-translate="closeButtonFooter">Close</span></button>
        <button type="button" class="btn btn-warning" id="cleanExpiredBtn"><i class="fa fa-broom" aria-hidden="true"></i> <span data-translate="cleanExpiredButton">Clean Expired</span></button>
        <button type="button" class="btn btn-danger" id="deleteAllBtn"><i class="fa fa-trash" aria-hidden="true"></i> <span data-translate="deleteAllButton">Delete All</span></button>
        <button type="button" class="btn btn-primary" id="generateShareBtn"><i class="fa fa-link" aria-hidden="true"></i> <span data-translate="generateLinkButton">Generate Link</span></button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel" data-translate="updateModalLabel">Update status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="updateDescription" class="alert alert-info mb-3" data-translate="updateDescription"></div>
                <pre id="logOutput" style="white-space: pre-wrap; word-wrap: break-word; text-align: left; display: inline-block;" data-translate="waitingMessage">Waiting for the operation to begin...</pre>
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
            if (files.length === 0) { 
                alert(translations['select_files_to_delete'] || 'Please select files to delete first');  
                return; 
            }
            const confirmText = (translations['confirm_batch_delete'] || 'Are you sure you want to delete the selected %d files?').replace('%d', files.length);
            showConfirmation(confirmText, () => {
                const batchDeleteForm = $('#batchDeleteForm');
                batchDeleteForm.empty();
                batchDeleteForm.append('<input type="hidden" name="batch_delete" value="1">');
                files.forEach(file => {
                    batchDeleteForm.append(`<input type="hidden" name="filenames[]" value="${file}">`);
                });
                batchDeleteForm.submit();
            });
        });

        function updateSelectionInfo() {
            const checked = $('.fileCheckbox:checked');
            const count = checked.length;
            const totalSize = checked.toArray().reduce((sum, el) => sum + parseInt($(el).data('size')), 0);
            if (count > 0) {
                $('#toolbar').removeClass('d-none');
                $('#selectedInfo').html((translations['file_summary'] || 'Selected %d files，total %s MB').replace('%d', count).replace('%s', (totalSize / (1024 * 1024)).toFixed(2)));
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
            const confirmMessage = translations['confirm_clear_background'] || 'Are you sure you want to clear the background?';
            speakMessage(translations['confirm_clear_background'] || 'Are you sure you want to clear the background?');
            showConfirmation(confirmMessage, () => {
                setTimeout(() => {
                    clearExistingBackground();
                    localStorage.removeItem('phpBackgroundSrc');
                    localStorage.removeItem('phpBackgroundType');
                    localStorage.removeItem('backgroundSet');

                    const clearedMsg = translations['background_cleared'] || 'Background cleared!';
                    showLogMessage(clearedMsg);
                    speakMessage(clearedMsg);

                    setTimeout(() => {
                          window.top.location.href = "/cgi-bin/luci/admin/services/spectra";
                    }, 3000);

                }, 0);
            });
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
            let message = '';
            if (response.success) {
                message = translations['batch_delete_success'] || '✅ Batch delete successful';
                showLogMessage(message);
                speakMessage(message);
                setTimeout(() => {
                      location.reload();
                }, 2500);
            } else {
                message = translations['batch_delete_failed'] || '❌ Batch delete failed';
                showLogMessage(message);
                speakMessage(message);
            }
        }, 'json');
    });

    function setImageBackground(src) {
        clearExistingBackground();
        document.body.style.background = `url('/spectra/stream/${src}') no-repeat center center fixed`;
        document.body.style.backgroundSize = 'cover';
        localStorage.setItem('phpBackgroundSrc', src);
        localStorage.setItem('phpBackgroundType', 'image');
        localStorage.setItem('redirectAfterImage', 'true');
        checkAndReload();
    }

    function setVideoBackground(src, isPHP = false) {
        clearExistingBackground();
        let existingVideoTag = document.getElementById("background-video");
        if (existingVideoTag) {
            existingVideoTag.src = `/spectra/stream/${src}`;
        } else {
            let videoTag = document.createElement("video");
            videoTag.className = "video-background";
            videoTag.id = "background-video";
            videoTag.autoplay = true;
            videoTag.loop = true;
            videoTag.muted = localStorage.getItem('videoMuted') === 'true';
            videoTag.playsInline = true;
            videoTag.innerHTML = `
                <source src="/spectra/stream/${src}" type="video/mp4">
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
        localStorage.setItem('redirectAfterVideo', 'true');
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

    window.addEventListener('load', function () {
        if (localStorage.getItem('redirectAfterImage') === 'true') {
            localStorage.removeItem('redirectAfterImage');
            setTimeout(() => {
                window.top.location.href = "/cgi-bin/luci/admin/services/spectra?bg=image";
            }, 3000);
        } else if (localStorage.getItem('redirectAfterVideo') === 'true') {
            localStorage.removeItem('redirectAfterVideo');
            setTimeout(() => {
                window.top.location.href = "/cgi-bin/luci/admin/services/spectra?bg=video";
            }, 3000);
        }
    });
</script>

<script>
    document.getElementById("updatePhpConfig").addEventListener("click", function() {
        const confirmText = translations['confirm_update_php'] || "Are you sure you want to update PHP configuration?";
        speakMessage(confirmText);
        showConfirmation(confirmText, () => {
            fetch("update_php_config.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json())
            .then(data => {
                const msg = data.message || "Configuration updated successfully.";
                showLogMessage(msg);
                speakMessage(msg);
            })
            .catch(error => {
                const errMsg = translations['request_failed'] || ("Request failed: " + error.message);
                showLogMessage(errMsg);
                speakMessage(errMsg);
            });
        });
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
    background-color: var(--header-bg) !important;
    border-top: 1px solid var(--border-color) !important;
}

#previewVideo {
    width: 100% !important;
    height: 100% !important;
    object-fit: fill;
}

@media (max-width: 576px) {
    .modal-xl:not(.fullscreen-modal) {
        max-height: calc(100vh - var(--header-height) - var(--footer-height));
        overflow-y: auto;
    }
}

#previewModal .modal-body {
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
}

#previewModal .modal-content {
    border-radius: 0.3rem;
    box-shadow: var(--bs-box-shadow);
}

#previewModal .modal-footer {
    background-color: var(--header-bg) !important;
    border-top: 1px solid var(--border-color) !important;
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
    const modalElement = document.getElementById('updateConfirmModal');
    const versionInfo = document.getElementById('themeVersionInfo');
    const currentVersionInfo = document.getElementById('currentVersionInfo');
    const downloadLink = document.getElementById('confirmUpdateLink');

    modalElement.addEventListener('shown.bs.modal', function () {
        versionInfo.textContent = translations['fetching_version'] || 'Fetching version info...';
        currentVersionInfo.textContent = translations['unable_to_fetch_current_version'] || 'Fetching current version...';
        downloadLink.href = '#';

        fetch('check_theme_update.php')
            .then(response => response.json())
            .then(data => {
                if (data.currentVersion) {
                    currentVersionInfo.textContent = `${translations['current_version'] || 'Current Version'}: ${data.currentVersion}`;
                } else {
                    currentVersionInfo.textContent = translations['unable_to_fetch_current_version'] || 'Unable to fetch the current version info';
                }

                if (data.version && data.url) {
                    versionInfo.textContent = `${translations['latest_version'] || 'Latest Version'}: ${data.version}`;
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
let mediaTimer = null;

const fitModes = [
  { mode: 'cover',      labelKey: 'fit_cover' },
  { mode: 'contain',    labelKey: 'fit_contain' },
  { mode: 'fill',       labelKey: 'fit_fill' },
  { mode: 'none',       labelKey: 'fit_none' },
  { mode: 'scale-down', labelKey: 'fit_scale-down' },
];
let currentFitIndex = 0;

const fitButtons = ['fitTogglePreview', 'fitTogglePlayer'];

function applyFitMode(announce = true) {
  const currentMode = fitModes[currentFitIndex];
  const label = translations?.[currentMode.labelKey] || currentMode.mode;

  ['previewImage', 'previewVideo', 'mainPlayer'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.objectFit = currentMode.mode;
  });

  fitButtons.forEach(id => {
    const btn = document.getElementById(id);
    if (btn) {
      const span = btn.querySelector('span[data-translate]');
      if (span) {
        span.textContent = label;
      } else {
        btn.textContent = label;
      }
    }
  });

  if (!announce) return;

  const prefix = translations?.['current_fit_mode'] || 'Current mode';
  const messageText = `${prefix}: ${label}`;
  if (typeof showLogMessage === 'function') showLogMessage(messageText);
  if (typeof speakMessage === 'function') speakMessage(messageText);
}

fitButtons.forEach(btnId => {
  const btn = document.getElementById(btnId);
  if (btn) {
    btn.addEventListener('click', () => {
      currentFitIndex = (currentFitIndex + 1) % fitModes.length;
      applyFitMode(true);
    });
  }
});

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
  if (mediaTimer) {
    clearTimeout(mediaTimer);
    mediaTimer = null;
  }
  ['Image','Audio','Video'].forEach(kind => {
    const el = document.getElementById('preview' + kind);
    if (el) {
      el.classList.add('d-none');
      if (kind !== 'Video' && kind !== 'Image') {
        el.src = '';
      }
      if (kind === 'Video' || kind === 'Audio') {
        el.pause();
        el.onended = null;
        if (kind === 'Video') {
          el.querySelector('source').src = '';
        }
      }
    }
  });
}

function loadAndPlayMedia() {
  cleanMediaElements();
  applyFitMode(false); 

  const currentFile = mediaFiles[currentPreviewIndex];
  if (!currentFile) return;

  switch (currentFile.type) {
    case 'image': {
      const img = document.getElementById('previewImage');
      img.src = currentFile.src;
      img.classList.remove('d-none');
      mediaTimer = setTimeout(() => {
        document.getElementById('nextBtn').click();
      }, 5000);
      break;
    }
    case 'video': {
      const video = document.getElementById('previewVideo');
      const source = video.querySelector('source');
      source.src = currentFile.src;
      video.load();
      video.classList.remove('d-none');
      video.onended = () => {
        document.getElementById('nextBtn').click();
      };
      video.play().catch(e => console.log('Video play failed:', e));
      break;
    }
    case 'audio': {
      const audio = document.getElementById('previewAudio');
      audio.src = currentFile.src;
      audio.classList.remove('d-none');
      audio.onended = () => {
        document.getElementById('nextBtn').click();
      };
      audio.play().catch(e => console.log('Audio play failed:', e));
      break;
    }
  }
}

document.getElementById('previewModal').addEventListener('show.bs.modal', function(e) {
  initMediaFiles();
  currentPreviewIndex = parseInt(e.relatedTarget.dataset.fileIndex);
  currentFitIndex = 0;
  loadAndPlayMedia();
});

document.getElementById('playerModal').addEventListener('show.bs.modal', function () {
  currentFitIndex = 0;
  applyFitMode(false);
});

document.getElementById('prevBtn').addEventListener('click', () => {
  currentPreviewIndex = (currentPreviewIndex - 1 + mediaFiles.length) % mediaFiles.length;
  loadAndPlayMedia();
});

document.getElementById('nextBtn').addEventListener('click', () => {
  currentPreviewIndex = (currentPreviewIndex + 1) % mediaFiles.length;
  loadAndPlayMedia();
});

document.addEventListener('keydown', (e) => {
  const modal = document.getElementById('previewModal');
  if (!modal.classList.contains('show')) return;

  const key = e.key.toLowerCase();
  if (key === 'a') {
    document.getElementById('prevBtn').click();
  } else if (key === 'd') {
    document.getElementById('nextBtn').click();
  }
});
</script>

<script>
function setBackground(filename) {
    fetch('set_background.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'file=' + encodeURIComponent(filename)
    }).then(() => location.reload()) 
      .catch(error => console.error('Request failed:', error));
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    updateDateTime();
    setInterval(updateDateTime, 1000);

    const requiredElements = ['dateDisplay', 'timeDisplay', 'lunarDisplay'];
    requiredElements.forEach(id => {
        if (!document.getElementById(id)) {
            console.error(`Element #${id} not found`);
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

    const defaultZodiacs = ['Monkey','Rooster','Dog','Pig','Rat','Ox','Tiger','Rabbit','Dragon','Snake','Horse','Goat'];
    const defaultGan = ['Jia','Yi','Bing','Ding','Wu','Ji','Geng','Xin','Ren','Gui'];
    const defaultZhi = ['Zi','Chou','Yin','Mao','Chen','Si','Wu','Wei','Shen','You','Xu','Hai'];
    const defaultMonths = ['1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th','11th','12th'];
    const defaultDays = ['1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th',
        '11th','12th','13th','14th','15th','16th','17th','18th','19th','20th',
        '21st','22nd','23rd','24th','25th','26th','27th','28th','29th','30th'];

    const zodiacs = translations.zodiacs || defaultZodiacs;
    const Gan = translations.heavenlyStems || defaultGan;
    const Zhi = translations.earthlyBranches || defaultZhi;
    const lunarMonths = translations.months || defaultMonths;
    const lunarDays = translations.days || defaultDays;
    const leapPrefix = translations.leap_prefix || 'Leap ';
    const yearSuffix = translations.year_suffix || ' Year';
    const monthSuffix = translations.month_suffix || ' Month';
    const daySuffix = translations.day_suffix || '';

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
    const monthName = (isLeap ? leapPrefix : '') + lunarMonths[lunarMonth-1] + monthSuffix;
    const dayName = lunarDays[lunarDay-1];
    const ganZhiYear = Gan[(lunarYear - 4) % 10] + Zhi[(lunarYear - 4) % 12];

    return {
        zodiac: zodiac,
        year: ganZhiYear + yearSuffix,
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
        const ancientTime = getAncientTime(now, translations);
        const weekDayIndex = now.getDay();
        const weekDay = translations.weekDays ? translations.weekDays[weekDayIndex] : weekDayIndex;

        const timeStr = [
            now.getHours().toString().padStart(2, '0'),
            now.getMinutes().toString().padStart(2, '0'),
            now.getSeconds().toString().padStart(2, '0')
        ].join(':');

        const timeElement = document.getElementById('timeDisplay');
        if (timeElement) {
            if (['zh', 'hk', 'ja', 'ko'].includes(lang)) {
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
                    announcement = `${translations['hour_announcement'] || '??'}${hours}${translations['hour_exact'] || '??'}`;
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
            let dateStr;
            switch(lang) {
                case 'zh':
                case 'hk':
                    dateStr = `${now.getFullYear()}${translations.labels.year}${now.getMonth()+1}${translations.labels.month}${now.getDate()}${translations.labels.day}`;
                    break;
                case 'vi':
                    dateStr = `${translations.labels.day} ${now.getDate()} ${translations.labels.month} ${now.getMonth()+1} ${translations.labels.year} ${now.getFullYear()}`;
                    break;
                case 'ko':
                    dateStr = `${now.getFullYear()}${translations.labels.year} ${now.getMonth()+1}${translations.labels.month} ${now.getDate()}${translations.labels.day}`;
                    break;
                case 'ja':
                    dateStr = `${now.getFullYear()}${translations.labels.year}${now.getMonth()+1}${translations.labels.month}${now.getDate()}${translations.labels.day}`;
                    break;
                default:
                    dateStr = `${now.getFullYear()}-${now.getMonth()+1}-${now.getDate()}`;
            }
            dateElement.textContent = dateStr;
        }

        const weekElement = document.getElementById('weekDisplay');
        if (weekElement) {
            if (['zh', 'hk', 'ko', 'ja'].includes(lang)) {
                weekElement.textContent = `${translations.labels.week}${weekDay}`;
            } else if (lang === 'vn') {
                weekElement.textContent = '';
            } else {
                weekElement.textContent = weekDay;
            }
        }

        const lunarElement = document.getElementById('lunarDisplay');
        if (['zh', 'hk', 'ja', 'ko'].includes(lang) && lunarElement) {
            const lunar = getLunar(now); 
            lunarElement.textContent = (() => {
                switch(lang) {
                    case 'zh':
                    case 'hk':
                        return `${lunar.year} ${lunar.month}${lunar.day} ${lunar.zodiac}年`;
                    case 'ja':
                        return `${lunar.year} ${lunar.month}${lunar.day} ${lunar.zodiac}年`;
                    case 'ko':
                        return `${lunar.year} ${lunar.month}${lunar.day} ${lunar.zodiac}띠`;
                    default: 
                        return '';
                }
            })();
        } else if (lunarElement) {
            lunarElement.textContent = '';
        }

        if (now.getHours() === 0 && now.getMinutes() === 0 && now.getSeconds() === 0) {
            setTimeout(() => location.reload(), 1000);
        }

    } catch (error) {
        showLogMessage(translations['error_loading_time'] || 'Error loading time');

        const dateElement = document.getElementById('dateDisplay');
        if (dateElement) {
            dateElement.textContent = translations['error_loading_time'] || 'Error loading time';
        }
    }
}

function getAncientTime(date, translations) {
    let hours = date.getHours();
    let minutes = date.getMinutes();

    hours += Math.floor(minutes / 60);
    minutes = minutes % 60;
    hours = hours % 24;
    if (hours < 0) hours += 24;

    const defaultPeriods = ['Zi', 'Chou', 'Yin', 'Mao', 'Chen', 'Si', 'Wu', 'Wei', 'Shen', 'You', 'Xu', 'Hai'];
    const periodLabels = translations?.periods || defaultPeriods;

    const periods = [
        { start: 23, end: 1, name: periodLabels[0], overnight: true },
        { start: 1, end: 3, name: periodLabels[1] },
        { start: 3, end: 5, name: periodLabels[2] },
        { start: 5, end: 7, name: periodLabels[3] },
        { start: 7, end: 9, name: periodLabels[4] },
        { start: 9, end: 11, name: periodLabels[5] },
        { start: 11, end: 13, name: periodLabels[6] },
        { start: 13, end: 15, name: periodLabels[7] },
        { start: 15, end: 17, name: periodLabels[8] },
        { start: 17, end: 19, name: periodLabels[9] },
        { start: 19, end: 21, name: periodLabels[10] },
        { start: 21, end: 23, name: periodLabels[11] }
    ];

    const match = periods.find(p => {
        if (p.overnight) return hours >= p.start || hours < p.end;
        return hours >= p.start && hours < p.end;
    });

    if (!match) return periodLabels[11];

    let totalMinutes = date.getHours() * 60 + date.getMinutes();
    let periodStartMinutes = match.start * 60;
    let periodEndMinutes = match.end * 60;

    if (match.overnight) {
        if (hours < match.start) totalMinutes += 24 * 60; 
        periodEndMinutes += 24 * 60;
    }

    const relativeMinutes = totalMinutes - periodStartMinutes;
    const periodLength = periodEndMinutes - periodStartMinutes;
    const stageDuration = periodLength / 3;

    let sub;
    if (relativeMinutes < stageDuration) {
        sub = translations?.initial || 'Initial';
    } else if (relativeMinutes < stageDuration * 2) {
        sub = translations?.middle || 'Middle';
    } else {
        sub = translations?.final || 'Final';
    }

    return `${match.name}${sub}`; 
}

const elements = document.querySelectorAll('.time-display span');
const currentSong = document.querySelector('#currentSong');
const floatingCurrentSong = document.getElementById('floatingCurrentSong');

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

    if (floatingCurrentSong) {
        floatingCurrentSong.style.color = getNextColor(colorList);
    }
}
setInterval(rotateColors, 4000);
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
document.addEventListener('change', function(e) {
    if(e.target.classList.contains('fileCheckbox')) {
        const wrapper = e.target.closest('.file-checkbox-wrapper');
        wrapper.classList.toggle('force-visible', e.target.checked);
        
        const allCheckboxes = [...document.querySelectorAll('.fileCheckbox:not(#selectAll)')];
        const allChecked = allCheckboxes.every(c => c.checked);
        document.getElementById('selectAll').checked = allChecked;
    }
});

document.getElementById('selectAll').addEventListener('change', function(e) {
    const checkboxes = document.querySelectorAll('.fileCheckbox:not(#selectAll)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = e.target.checked;
        const wrapper = checkbox.closest('.file-checkbox-wrapper');
        wrapper.classList.toggle('force-visible', e.target.checked);
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const grid = document.getElementById("fileGrid");

    const isSmallScreen = window.innerWidth < 768;

    if (!isSmallScreen) {
        new Sortable(grid, {
            animation: 150,
            onEnd: function () {
                const filenames = Array.from(grid.querySelectorAll('[data-filename]'))
                                      .map(el => el.getAttribute('data-filename'));
                
                fetch('order_handler.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ order: filenames })
                })
                .then(response => response.ok ? console.log('Order saved.') : console.error('Failed to save.'))
                .catch(console.error);
            }
        });
    } else {
        //console.log('Drag and drop is disabled on small screens.');
    }
});
</script>

<script>
function clearBackgroundDirectly() {
    clearExistingBackground();
    localStorage.removeItem('phpBackgroundSrc');
    localStorage.removeItem('phpBackgroundType');
    localStorage.removeItem('backgroundSet');
}

document.addEventListener('DOMContentLoaded', function () {
    const copyButton = document.getElementById('copyCommandBtn');
    const copyCommandTextarea = document.getElementById('copyCommand');

    copyButton.addEventListener('click', function () {
        copyCommandTextarea.select();
        document.execCommand('copy'); 
        const message = translations['command_copied'] || 'Command copied to clipboard!';
        showLogMessage(message);
        speakMessage(message);
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const updatePluginBtn = document.getElementById('updatePluginBtn');
    const updateConfirmModal = new bootstrap.Modal(document.getElementById('updateConfirmModal'));
    const updateModal = new bootstrap.Modal(document.getElementById('updateModal'));
    const logOutput = document.getElementById('logOutput');

    updatePluginBtn.addEventListener('click', function () {
        updateConfirmModal.hide();
        updateModal.show();

        fetch('install_theme.php', {
            method: 'POST',
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("Installation complete!")) {
                logOutput.textContent = "Installation complete!";
                setTimeout(() => {
                    updateModal.hide();
                    window.top.location.href = "/cgi-bin/luci/admin/services/spectra";
                }, 3000);
            } else {
                logOutput.textContent = data;
                setTimeout(() => {
                    updateModal.hide();
                }, 5000);
            }
        })
        .catch(error => {
            const message = translations['installation_complete'] || 'Installation complete!';
            logOutput.textContent = '';
            logOutput.textContent = message;
            showLogMessage(message);
            speakMessage(message);

            setTimeout(() => {
                updateModal.hide();
            }, 5000);
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  let currentFilename = '';
  const shareModal = document.getElementById('shareModal');
  const shareLinkInput = document.getElementById('shareLink');
  const copyLinkBtn = document.getElementById('copyLinkBtn');
  const generateShareBtn = document.getElementById('generateShareBtn');

  shareModal.addEventListener('show.bs.modal', (event) => {
    currentFilename = event.relatedTarget.dataset.filename;
  });

  generateShareBtn.addEventListener('click', async () => {
    const expire = parseInt(document.getElementById('expireTime').value, 10) || 0;
    const maxDownloads = parseInt(document.getElementById('maxDownloads').value, 10) || 0;

    try {
       if (!currentFilename) throw new Error(translations['fileNotSelected'] || 'No file selected');
      
      const response = await fetch('./share.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'create',
          filename: currentFilename,
          expire: expire,
          max_downloads: maxDownloads,
        }),
      });

      const data = await response.json();
      if (!response.ok || !data.success) {
        throw new Error(data.message || `${translations['httpError']} ${response.status}`);
      }

      const link = `${window.location.origin}/spectra/download.php?token=${data.token}`;
      shareLinkInput.value = link;
      const message = translations['linkGenerated'] || '✅ Share link generated';
      showLogMessage(message);
      speakMessage(message);
    } catch (error) {
      console.error('Error:', error);
      showLogMessage(`${translations['operationFailed'] || '❌ Operation failed'}: ${error.message}`);
    }
  });

  copyLinkBtn.addEventListener('click', async () => {
    try {
      if (!shareLinkInput.value) throw new Error(translations['generateLinkFirst'] || 'Please generate the share link first');
      
      await navigator.clipboard.writeText(shareLinkInput.value);
      showLogMessage(translations['linkCopied'] || '📋 Link copied to clipboard');
    } catch (error) {
      console.error('Copy failed:', error);
      showLogMessage(`${translations['copyFailed'] || '❌ Copy failed'}: ${error.message}`);
      shareLinkInput.select();
      shareLinkInput.setSelectionRange(0, 99999);
    }
  });
});

const cleanExpiredBtn = document.getElementById('cleanExpiredBtn');
cleanExpiredBtn.addEventListener('click', async () => {
  try {
    const res = await fetch('./manage_tokens.php?action=clean');
    const result = await res.json();

    if (result.success) {
      const msg = (translations['cleanSuccess'] || '✅ Clean completed').replace('%s', result.deleted);
      showLogMessage(msg);
      speakMessage(msg);
    } else {
      throw new Error(result.message || 'Operation failed');
    }
  } catch (err) {
    showLogMessage(`${translations['operationFailed'] || '❌ Operation failed'}: ${err.message}`);
  }
});

const deleteAllBtn = document.getElementById('deleteAllBtn');
if (deleteAllBtn) {
  deleteAllBtn.addEventListener('click', () => {
    const confirmMessage = translations['confirmDeleteAll'] || '⚠️ Are you sure you want to delete ALL share records?';
    showConfirmation(confirmMessage, async () => {
      try {
        const res = await fetch('./manage_tokens.php?action=delete_all');
        const result = await res.json();

        if (result.success) {
          const msg = (translations['deleteSuccess'] || '✅ All share records deleted').replace('%s', result.deleted);
          showLogMessage(msg);
          speakMessage(msg);
        } else {
          throw new Error(result.message || 'Operation failed');
        }
      } catch (err) {
        showLogMessage(`${translations['operationFailed'] || '❌ Operation failed'}: ${err.message}`);
      }
    });
  });
}
</script>

<script>
async function translateText(text, targetLang = null) {
  if (!text?.trim()) return text;
  const countryToLang = {
    'CN':'zh-CN','HK':'zh-HK','TW':'zh-TW','JA':'ja',
    'KO':'ko','VI':'vi','TH':'th','GB':'en','FR':'fr',
    'DE':'de','RU':'ru','US':'en','MX':'es'
  };
  if (!targetLang) targetLang = localStorage.getItem('language') || 'CN';
  targetLang = countryToLang[targetLang.toUpperCase()] || targetLang;
  const apiLangMap = {
    'zh-CN':'zh-CN','zh-HK':'zh-HK','zh-TW':'zh-TW',
    'ja':'ja','ko':'ko','vi':'vi','en':'en-GB','ru':'ru'
  };
  const apiTargetLang = apiLangMap[targetLang] || targetLang;
  const detectJP = t => /[\u3040-\u309F\u30A0-\u30FF\u4E00-\u9FAF]/.test(t);
  const sourceLang = detectJP(text) ? 'ja' : 'en';
  if (sourceLang.split('-')[0] === apiTargetLang.split('-')[0]) return text;
  const cacheKey = `trans_${sourceLang}_${apiTargetLang}_${text}`;
  const cached = localStorage.getItem(cacheKey);
  if (cached) return cached;
  const url = `https://api.mymemory.translated.net/get?q=${encodeURIComponent(text)}&langpair=${sourceLang}|${apiTargetLang}`;
  try {
    const res  = await fetch(url);
    const data = await res.json();
    const translated = data.responseData?.translatedText || text;
    localStorage.setItem(cacheKey, translated);
    return translated;
  } catch {
    return text;
  }
}

let IP = {
  ipApis: [
    {url:'https://api.ipify.org?format=json', type:'json', key:'ip'},
    {url:'https://ipapi.co/json/',           type:'json', key:'ip'}
  ],
  get(url,type) { return fetch(url,{cache:'no-store'}).then(r=> type==='text'?r.text():r.json()); },
  async fetchIP() {
    for (let api of this.ipApis) {
      try {
        const data = await this.get(api.url, api.type);
        const ip = api.type==='json'
          ? (api.key? data[api.key]: data.ip)
          : (data.match(/\d+\.\d+\.\d+\.\d+/)||[])[0];
        if (ip) return ip;
      } catch {}
    }
    throw new Error('Unable to retrieve IP');
  }
};

async function fetchGeo(ip) {
  for (let url of [
    `https://ipapi.co/${ip}/json/`,
    `https://api.ip.sb/geoip/${ip}`
  ]) {
    try { return await IP.get(url,'json'); }
    catch {}
  }
  throw new Error('Unable to retrieve geographic information');
}

const pingSites = { Baidu:'https://www.baidu.com', Taobao:'https://www.taobao.com', YouTube:'https://www.youtube.com', Google:'https://www.google.com', GitHub:'https://www.github.com', OpenAI:'https://www.openai.com' };
async function checkAllPings() {
  const res = {};
  for (let [name,url] of Object.entries(pingSites)) {
    try {
      const t0 = performance.now();
      await fetch(url,{mode:'no-cors',cache:'no-cache'});
      res[name] = Math.round(performance.now()-t0);
    } catch {
      res[name] = 'Timeout';
    }
  }
  return res;
}

async function showIpDetailModal() {
  const modalEl = document.getElementById('ipDetailModal');
  const modal   = new bootstrap.Modal(modalEl,{backdrop:'static',keyboard:false});
  modal.show();

  modalEl.querySelectorAll('.detail-value').forEach(el=>el.innerHTML=`<span class="spinner-border spinner-border-sm"></span>`);
  document.getElementById('delayInfo').innerHTML=`<span class="spinner-border spinner-border-sm"></span>`;
  document.querySelector('.map-coord-row').style.display='none';
  document.querySelector('.map-container').style.display='none';

  try {
    const ip  = await IP.fetchIP();
    const geo = await fetchGeo(ip);

    const parts = [geo.city,geo.region,geo.country_name].filter(Boolean);
    const unique = parts.filter((v,i,a)=>a.indexOf(v)===i).join(' ');
    const locationText = await translateText(unique);

    let isp = geo.org || geo.isp || '';
    isp = await translateText(isp);

    const asn = geo.asn || geo.as?.split(' ')[0] || '   ';
    const asnName = geo.asn_org || geo.as_name || geo.as?.split(' ').slice(1).join(' ') || '';
    const asnNameTranslated = await translateText(asnName);

    const vals = modalEl.querySelectorAll('.detail-row .detail-value');
    vals[0].textContent = ip;
    vals[1].textContent = locationText;
    vals[2].textContent = isp;
    vals[3].textContent = [asn, asnNameTranslated].filter(Boolean).join(' ');
    vals[4].textContent = geo.timezone || '';

    if (geo.latitude && geo.longitude) {
      document.querySelector('.map-coord-row').style.display='flex';
      document.querySelector('.map-coord-row .detail-value').textContent = `${geo.latitude}, ${geo.longitude}`;
      document.querySelector('.map-container').style.display='block';

      setTimeout(()=>{
        if (window._leafletMap) window._leafletMap.remove();
        window._leafletMap = L.map('leafletMap').setView([geo.latitude,geo.longitude],10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window._leafletMap);
        L.marker([geo.latitude,geo.longitude])
         .addTo(window._leafletMap)
         .bindPopup(locationText).openPopup();
         window._leafletMap.addControl(new L.Control.FullScreen({
             position: 'topright',
             title: ' ',
             titleCancel: ' ',
             content: '<i class="fas fa-expand"></i>',
             contentCancel: '<i class="fas fa-compress"></i>'
         }));
      },200);
    }

    const p = await checkAllPings();
    document.getElementById('delayInfo').innerHTML = Object.entries(p).map(([n,t])=>{
      const color = typeof t==='number'
        ? (t<300?'#09B63F':t<700?'#FFA500':'#ff6b6b')
        : '#ff6b6b';
      return `<span style="margin-right:20px;color:${color}">${n}: ${t==='Timeout'?'Timeout':t+'ms'}</span>`;
    }).join('');

  } catch (err) {
    console.error(err);
    modalEl.querySelector('.modal-body').innerHTML = `
      <div style="padding:20px;text-align:center;color:#c00;">
        <p>Failed to retrieve the information, please try again later.</p>
      </div>`;
  }
}
</script>

<link rel="stylesheet" href="/luci-static/spectra/css/leaflet.css" />
<script src="/luci-static/spectra/js/leaflet.js"></script>
<link rel="stylesheet" href="/luci-static/spectra/css/Control.FullScreen.min.css">
<script src="/luci-static/spectra/js/Control.FullScreen.min.js"></script>

<style>

#ipDetailModal .modal-body h5 {
    margin: 20px 0 15px !important;
}

.detail-row {
    display: flex;
    margin-bottom: 10px;
    line-height: 1.6;
}

.detail-label {
    flex: 0 0 200px;
    text-align: left;
    font-weight: 500;
    padding-right: 18px;
}

.detail-value {
    flex: 1;
    text-align: left;
    word-break: break-all;
    margin-left: 0;
}

.leaflet-popup-content-wrapper {
    background-color: var(--header-bg) !important;
    border-top: 1px solid var(--border-color) !important;
    color: var(--accent-color) !important;
}

.leaflet-popup-tip {
    background-color: var(--header-bg) !important;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", async () => {
  const weatherIcon = document.getElementById("weatherIcon");
  const cityNameDisplay = document.getElementById("cityNameDisplay");
  const weatherText = document.getElementById("weatherText");

  function owmCodeToWiClass(code) {
    const map = {
      '01d': 'day-sunny',    '01n': 'night-clear',
      '02d': 'day-cloudy',   '02n': 'night-cloudy',
      '03d': 'cloud',        '03n': 'cloud',
      '04d': 'cloudy',       '04n': 'cloudy',
      '09d': 'showers',      '09n': 'showers',
      '10d': 'day-rain',     '10n': 'night-alt-rain',
      '11d': 'thunderstorm', '11n': 'thunderstorm',
      '13d': 'snow',         '13n': 'snow',
      '50d': 'fog',          '50n': 'fog'
    };
    return map[code] || 'na';
  }

  async function getCurrentLanguage() {
    try {
      const res = await fetch('./save_language.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_language'
      });
      const data = await res.json();
      if (data.success && data.language) return data.language;
    } catch (_) {}
    return 'zh';
  }

  async function loadWeather() {
    try {
      const lang = await getCurrentLanguage();
      const localCity = localStorage.getItem('city');
      const res = await fetch("./weather_translation.php");
      if (!res.ok) return;

      const data = await res.json();
      const cities = data.cities || {};

      let cityKey = localCity && cities[localCity] ? localCity : Object.keys(cities)[0];
      if (!cityKey) return;

      const cityData = cities[cityKey];
      const translations = cityData.translations || {};
      const langData = translations[lang] || translations["zh"] || {};

      const cityName = langData.city || cityKey;
      const weatherDict = langData.weather || {};
      const lastIcon = langData.lastIcon || "na";

      const temp = cityData.temp != null ? Number(cityData.temp).toFixed(1) : '';

      const weatherKey = Object.keys(weatherDict)[0];
      const weatherValue = weatherDict[weatherKey] || "";

      cityNameDisplay.textContent = cityName + " ";
      weatherText.innerHTML = weatherValue + (temp !== '' ? `&nbsp;${temp}℃` : '');

      const wiClass = owmCodeToWiClass(lastIcon);
      weatherIcon.className = `wi wi-${wiClass}`;

      const colorMap = {
        '01d':'#FFD700','01n':'#FFE4B5',
        '02d':'#C0C0C0','02n':'#A9A9A9',
        '09d':'#00BFFF','09n':'#1E90FF',
        '10d':'#1E90FF','10n':'#104E8B',
        '11d':'#FFA500','11n':'#FF8C00',
        '13d':'#ADD8E6','13n':'#B0E0E6',
        '50d':'#C0C0C0','50n':'#808080'
      };
      weatherIcon.style.color = colorMap[lastIcon] || '#FFF';

    } catch (_) {
    }
  }

  await loadWeather();
});
</script>

<script>
let userInteracted = false;

function toggleConfig() {
    fetch("./theme-switcher.php", { 
        method: "POST",
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            userInteracted = true;
            updateStatus(data.mode);
            
            setTimeout(() => {
                window.top.location.href = "/cgi-bin/luci/admin/services/spectra";
            }, 3000);
        } else {
            document.getElementById("status").innerText = "Switch failed: " + data.error;
        }
    })
    .catch(error => {
        document.getElementById("status").innerText = "Request error: " + error;
    });
}

function updateStatus(theme) {
    const btn = document.getElementById("toggleButton");
    const status = document.getElementById("status");
    
    if (theme === "dark") {
        const message = translations['current_mode_dark'] || "Current Mode: Dark Mode";
        btn.innerHTML = `<i class="bi bi-sun"></i> ${translations['switch_to_light_mode'] || 'Switch to Light Mode'}`;
        status.innerText = message;

        if (userInteracted && typeof showLogMessage === 'function') {
            showLogMessage(message);
        }
        if (userInteracted && typeof speakMessage === 'function') {
            speakMessage(message);
        }
    } else {
        const message = translations['current_mode_light'] || "Current Mode: Light Mode";
        btn.innerHTML = `<i class="bi bi-moon"></i> ${translations['switch_to_dark_mode'] || 'Switch to Dark Mode'}`;
        status.innerText = message;
        if (userInteracted && typeof showLogMessage === 'function') {
            showLogMessage(message);
        }
        if (userInteracted && typeof speakMessage === 'function') {
            speakMessage(message);
        }
    }
}

fetch("./theme-switcher.php")
    .then(res => res.json())
    .then(data => {
        if(data.mode) {
            updateStatus(data.mode);
        }
    })
    .catch(error => {
        document.getElementById("status").innerText = "Error retrieving mode: " + error;
    });
</script>
