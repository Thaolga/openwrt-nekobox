<?php
ini_set('memory_limit', '256M');
$base_dir = __DIR__;
$upload_dir = $base_dir;
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
$files = array_filter($files, function ($file) use ($upload_dir) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    return !in_array(strtolower($ext), ['php', 'txt']) && basename($file) !== 'shares' && !is_dir($upload_dir . DIRECTORY_SEPARATOR . $file);
});

if (isset($_GET['background'])) {
    $background_src = htmlspecialchars($_GET['background']);
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

<?php
$default_url = 'https://raw.githubusercontent.com/Thaolga/Rules/main/music/songs.txt';
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
            $message = 'Update successful!';
        } else {
            $message = 'Update failed, please check permissions.';
        }
    }

    if (isset($_POST['reset_default'])) {
        if (file_put_contents($file_path, $default_url) !== false) {
            chmod($file_path, 0644);
            $message = 'Default URL restored!';
        } else {
            $message = 'Restore failed, please check permissions.';
        }
    }
} else {
    $new_url = file_exists($file_path) ? file_get_contents($file_path) : $default_url;
}
?>

<head>
    <meta charset="utf-8">
    <title>Media File Management</title>
    <link href="/luci-static/spectra/css/bootstrap-icons.css" rel="stylesheet">
    <link href="/luci-static/spectra/css/all.min.css" rel="stylesheet">
    <link href="/luci-static/spectra/css/bootstrap.min.css" rel="stylesheet">
    <link href="/luci-static/spectra/css/weather-icons.min.css" rel="stylesheet">
    <script src="/luci-static/spectra/js/jquery.min.js"></script>
    <script src="/luci-static/spectra/js/bootstrap.bundle.min.js"></script>
    <script src="/luci-static/spectra/js/custom.js"></script>
    <script src="/luci-static/spectra/js/interact.min.js"></script>
    <script src="/luci-static/spectra/js/Sortable.min.js"></script>
    <script>
        const phpBackgroundType = '<?= $background_type ?>';
        const phpBackgroundSrc = '<?= $background_src ?>';
    </script>
    <script>
      (function() {
        const root = document.documentElement;
        const theme = localStorage.getItem("theme") || "dark";
        root.setAttribute("data-theme", theme);

        const hueKey = `${theme}BaseHue`;
        const chromaKey = `${theme}BaseChroma`;

        const defaultHue = theme === "dark" ? 260 : 200;
        const defaultChroma = theme === "dark" ? 0.14 : 0.18;

        const storedHue    = parseFloat(localStorage.getItem(hueKey));
        const storedChroma = parseFloat(localStorage.getItem(chromaKey));

        const baseHue    = !isNaN(storedHue)   ? storedHue   : defaultHue;
        const baseChroma = !isNaN(storedChroma)? storedChroma: defaultChroma;

        root.style.setProperty("--base-hue", baseHue);
        root.style.setProperty("--base-chroma", baseChroma);
      })();
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
	
	--bg-body: oklch(40% var(--base-chroma) var(--base-hue) / 90%);
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
	--sunset-bg: oklch(40% var(--base-chroma) var(--base-hue) / 90%);
	--color-accent: oklch(55% 0.3 240);
        --ocean-bg:     oklch(45% 0.3 calc(var(--base-hue) + 220));
        --forest-bg:    oklch(40% 0.3 calc(var(--base-hue) + 140));
        --rose-bg:      oklch(45% 0.3 calc(var(--base-hue) + 350));
        --lavender-bg:  oklch(43% 0.3 calc(var(--base-hue) + 270));
        --sand-bg:      oklch(42% 0.3 calc(var(--base-hue) + 60));
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
	--btn-primary-bg: oklch(55% 0.3 var(--base-hue));
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
	--btn-info-bg: oklch(55% 0.3 220);
	--btn-info-hover: color-mix(in oklch, var(--btn-info-bg), black 10%);
	--btn-warning-bg: oklch(55% 0.22 80);
	--btn-warning-hover: color-mix(in oklch, var(--btn-warning-bg), black 15%);
	--sunset-bg: oklch(50% var(--base-chroma) var(--base-hue) / 90%);
	--color-accent: oklch(55% 0.3 220);
        --forest-bg:   oklch(50% 0.3 calc(var(--base-hue) + 140));
        --rose-bg:     oklch(50% 0.3 calc(var(--base-hue) + 350));
        --lavender-bg: oklch(50% 0.3 calc(var(--base-hue) + 270));
        --sand-bg:     oklch(50% 0.3 calc(var(--base-hue) + 60));
}

@font-face {
        font-display: swap; 
        font-family: 'Fredoka One';
        font-style: normal;
        font-weight: 400;
        src: url('/luci-static/spectra/fonts/fredoka-v16-latin-regular.woff2') format('woff2');
}

@font-face {
        font-display: swap; 
        font-family: 'Noto Serif SC';
        font-style: normal;
        font-weight: 400;
        src: url('/luci-static/spectra/fonts/noto-serif-sc-v31-latin-regular.woff2') format('woff2'); 
}

@font-face {
        font-display: swap; 
        font-family: 'Comic Neue';
        font-style: normal;
        font-weight: 400;
        src: url('/luci-static/spectra/fonts/comic-neue-v8-latin-regular.woff2') format('woff2'); 
}

@font-face {
        font-display: swap; 
        font-family: 'DM Serif Display';
        font-style: normal;
        font-weight: 400;
        src: url('/luci-static/spectra/fonts/dm-serif-display-v15-latin-regular.woff2') format('woff2');
}

body {
        background: var(--body-bg-color, #f0ffff);
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

body.system-nofo-font {
        font-family: 'Noto Serif SC';
        font-weight: 400;
}


body.system-mono-font {
        font-family: 'Comic Neue';
        font-weight: 400;
}

body.dm-serif-font {
  font-family: 'DM Serif Display';
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
      <span id="weatherText" style="color:var(--accent-color); font-weight: 700;"></span>
    </div>
</div>
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center text-center gap-2">
        <h5 class="mb-0" style="line-height: 40px; height: 40px;" data-translate="spectra_config"></h5>
        <p id="status" class="mb-0"><span data-translate="current_mode">当前模式:</span> 加载中...</p>
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
            <div class="me-3 d-flex gap-2 mt-2 ps-2 custom-tooltip-wrapper gap-2" 
                 data-tooltip="挂载点：<?= $mountPoint ?>｜已用空间：<?= formatSize($usedSpace) ?>">
                <span class="btn btn-primary btn-sm mb-2 d-none d-sm-inline"><i class="bi bi-hdd"></i> <span data-translate="total">Total：</span><?= $totalSpace ? formatSize($totalSpace) : 'N/A' ?></span>
                <span class="btn btn-success btn-sm mb-2  d-none d-sm-inline"><i class="bi bi-hdd"></i> <span data-translate="free">Free：</span><?= $freeSpace ? formatSize($freeSpace) : 'N/A' ?></span>
            </div>
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#updateConfirmModal" data-translate-title="check_update"><i class="fas fa-cloud-download-alt"></i> <span class="btn-label"></span></button>
            <button class="btn btn-warning ms-2" data-bs-toggle="modal" data-bs-target="#uploadModal" data-translate-title="batch_upload"><i class="bi bi-upload"></i> <span class="btn-label"></span></button>
            <button class="btn btn-primary ms-2" id="openPlayerBtn" data-bs-toggle="modal" data-bs-target="#playerModal" data-translate-title="add_to_playlist"><i class="bi bi-play-btn"></i> <span class="btn-label"></span></button>
            <button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#musicModal" data-translate-title="music_player"><i class="bi bi-music-note-beamed"></i></button>
            <button type="button" class="btn btn-primary ms-2" onclick="showIpDetailModal()" data-translate-title="ip_info"><i class="fa-solid fa-satellite-dish"></i></button>
            <button class="btn btn-danger ms-2" id="clear-cache-btn" data-translate-title="clear_config"><i class="bi bi-trash3-fill"></i></button>
            <button class="btn btn-danger ms-2" id="clearBackgroundBtn" data-translate-title="clear_background"><i class="bi bi-trash"></i> <span class="btn-label"></span></button> 
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
            <input type="color" id="colorPicker" style="margin-right: 10px;" value="#333333" data-translate-title="component_bg_color"/>
            <input type="color" id="bodyBgColorPicker" value="#f0ffff" style="margin-right: 10px;" data-translate-title="page_bg_color" />
            <button class="btn btn-info ms-2" id="fontToggleBtn" data-translate-title="toggle_font"><i id="fontToggleIcon" class="fa-solid fa-font" style="color: white;"></i></button>
            <button class="btn btn-success ms-2 d-none d-sm-inline" id="toggleScreenBtn" data-translate-title="toggle_fullscreen"><i class="bi bi-arrows-fullscreen"></i></button>
            <button class="btn btn-warning ms-2 d-none d-sm-inline" id="weatherBtn" data-bs-toggle="modal" data-bs-target="#cityModal" data-translate-title="set_city"><i class="bi bi-geo-alt"></i></button>
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
                               value="<?= htmlspecialchars($file) ?>"
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
                                <button class="btn btn-danger custom-btn" onclick="handleDeleteConfirmation('<?= urlencode($file) ?>')" data-translate-title="delete"><i class="bi bi-trash"></i></button>
                                <button class="btn btn-primary custom-btn" data-bs-toggle="modal" data-bs-target="#renameModal-<?= md5($file) ?>" data-translate-title="rename"><i class="bi bi-pencil"></i></button>
                                <a href="?download=<?= urlencode($file) ?>" class="btn btn-success custom-btn"><i class="bi bi-download" data-translate-title="download"></i></a>   
                                <button class="btn btn-warning share-btn custom-btn"data-filename="<?= htmlspecialchars($file) ?>"data-bs-toggle="modal"data-bs-target="#shareModal" data-translate-title="shareLinkLabel"><i class="bi bi-share"></i></button>
                                <?php if ($isMedia): ?>
                                <button class="btn btn-info set-bg-btn custom-btn" 
                                        data-src="<?= htmlspecialchars($file) ?>"
                                        data-type="<?= $isVideo ? 'video' : ($isAudio ? 'audio' : 'image') ?>"
                                        onclick="setBackground('<?= htmlspecialchars($file) ?>')"
                                        data-translate-title="set_background">
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

<div class="modal fade" id="cityModal" tabindex="-1" aria-labelledby="cityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cityModalLabel" data-translate="set_city">Set City</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="cityInput" class="form-label" data-translate="input_label">City Name</label>
          <input type="text" class="form-control" id="cityInput" data-translate-placeholder="input_placeholder">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveCityBtn" data-translate="save">Save</button>
      </div>
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
    <div class="modal fade" id="renameModal-<?= md5($file) ?>" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" action="">
                    <input type="hidden" name="old_name" value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" data-translate="rename_file">
                            <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label data-translate="new_filename"></label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="new_name"
                                value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>"
                                data-translate-title="invalid_filename_chars"
                            >
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

<div id="floatingLyrics">
    <div class="floating-controls">
        <button class="ctrl-btn" onclick="changeTrack(-1, true)" data-translate-title="previous_track">
            <i class="fas fa-backward"></i>
        </button>
        <button class="ctrl-btn" id="floatingPlayBtn" onclick="togglePlay()" data-translate-title="play_pause">
            <i class="bi bi-play-fill"></i>
        </button>
        <button class="ctrl-btn" onclick="changeTrack(1, true)" data-translate-title="next_track">
            <i class="fas fa-forward"></i>
        </button>
        <button class="ctrl-btn" id="floatingRepeatBtn" onclick="toggleRepeat()">
            <i class="bi bi-arrow-repeat"></i>
        </button>
        <button class="ctrl-btn" id="speedToggle" data-translate-title="playback_speed">
            <span id="speedLabel">1×</span>
        </button>
        <button class="ctrl-btn" id="muteToggle" data-translate-title="volume">
            <i class="bi bi-volume-up-fill"></i>
        </button>
        <button class="ctrl-btn toggleFloatingLyricsBtn" data-translate-title="toggle_floating_lyrics">
            <i class="bi bi-display floatingIcon"></i>
        </button>
    </div>
    <div id="floatingCurrentSong" class="vertical-title"></div>
    <div class="vertical-lyrics"></div>
</div>

<span id="clearConfirmText" data-translate="clear_confirm" class="d-none"></span>

<div class="modal fade" id="musicModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="langModalLabel" data-translate="music_player"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="floatingLyrics"></div>
                <div id="currentSong" class="mb-3 text-center font-weight-bold fs-4"></div>
                <div class="lyrics-container" id="lyricsContainer" style="height: 300px; overflow-y: auto;"></div>
            <div class="non-lyrics-content">
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
                    <button class="btn btn-outline-light control-btn toggleFloatingLyricsBtn" data-translate-title="toggle_floating_lyrics">
                        <i class="bi bi-display floatingIcon"></i>
                    </button>
                    <button class="btn btn-outline-light control-btn" id="repeatBtn" onclick="toggleRepeat()">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <button class="btn btn-outline-light control-btn" onclick="changeTrack(-1, true)" data-translate-title="previous_track">
                        <i class="bi bi-caret-left-fill"></i>
                    </button>
                    <button class="btn btn-success control-btn" id="playPauseBtn" onclick="togglePlay()" data-translate-title="play_pause">
                        <i class="bi bi-play-fill"></i>
                    </button>
                    <button class="btn btn-outline-light control-btn" onclick="changeTrack(1, true)" data-translate-title="next_track">
                        <i class="bi bi-caret-right-fill"></i>
                    </button>
                    <button class="btn btn-outline-light control-btn" type="button" data-bs-toggle="modal" data-bs-target="#urlModal" data-translate-title="custom_playlist">
                        <i class="bi bi-music-note-list"></i>
                    </button>
                    <button class="btn btn-volume position-relative" id="volumeToggle" data-translate-title="volume">
                        <i class="bi bi-volume-up-fill"></i>
                        <div class="volume-slider-container position-absolute bottom-100 start-50 translate-middle-x mb-1 p-2" id="volumePanel" style="display: none; width: 120px;">
                            <input type="range" class="form-range volume-slider" id="volumeSlider" min="0" max="1" step="0.01" value="1">
                        </div>
                    </button>
                </div>
                <div class="playlist mt-3" id="playlist"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info" id="lyricsToggle"><i class="bi bi-chevron-down" id="lyricsIcon"></i></button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-translate="cancel">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="urlModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" data-translate="update_playlist"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?= strpos($message, '成功') !== false ? 'success' : 'danger' ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label data-translate="playlist_url"></label>
                        <input 
                            type="text" 
                            name="new_url" 
                            id="new_url" 
                            class="form-control" 
                            value="<?= htmlspecialchars($new_url) ?>" 
                            required
                        >
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

<div class="modal fade" id="weatherModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCityName">—</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="list-unstyled mb-0">
          <li><strong data-translate="weather_label">Weather</strong>：<span id="modalDesc">—</span></li>
          <li><strong data-translate="temperature_label">Temperature</strong>：<span id="modalTemp">—</span>℃</li>
          <li><strong data-translate="feels_like_label">Feels like</strong>：<span id="modalFeels">—</span>℃</li>
          <li><strong data-translate="humidity_label">Humidity</strong>：<span id="modalHumidity">—</span>%</li>
          <li><strong data-translate="pressure_label">Pressure</strong>：<span id="modalPressure">—</span> hPa</li>
          <li><strong data-translate="wind_label">Wind speed</strong>：<span id="modalWind">—</span> m/s</li>
          <li><strong data-translate="sunrise_label">Sunrise</strong>：<span id="modalSunrise">—</span></li>
          <li><strong data-translate="sunset_label">Sunset</strong>：<span id="modalSunset">—</span></li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel">Cancel</button>
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

<div class="modal fade" id="confirmModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" data-translate="confirm_title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="confirmModalMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmModalYes" data-translate="confirm">Confirm</button>
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
        document.body.style.background = `url('/luci-static/spectra/bgm/${src}') no-repeat center center fixed`;
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

    window.addEventListener('load', function () {
        if (localStorage.getItem('redirectAfterImage') === 'true') {
            localStorage.removeItem('redirectAfterImage');
            setTimeout(() => {
                window.top.location.href = "/cgi-bin/luci/admin/services/spectra?bg=image";
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
  let userInteracted = false;

  function hexToRgb(hex) {
    const fullHex = hex.replace(/^#?([a-f\d])([a-f\d])([a-f\d])$/i, 
      (_, r, g, b) => `#${r}${r}${g}${g}${b}${b}`);
    
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(fullHex);
    return result ? {
      r: parseInt(result[1], 16),
      g: parseInt(result[2], 16),
      b: parseInt(result[3], 16)
    } : { r: 0, g: 0, b: 0 };
  }

  function rgbToLinear(c) {
    const normalized = c / 255;
    return normalized <= 0.04045 
      ? normalized / 12.92 
      : Math.pow((normalized + 0.055) / 1.055, 2.4);
  }

  function rgbToOklch(r, g, b) {
    const [lr, lg, lb] = [r, g, b].map(rgbToLinear);
    
    const l = 0.4122214708 * lr + 0.5363325363 * lg + 0.0514459929 * lb;
    const m = 0.2119034982 * lr + 0.6806995451 * lg + 0.1073969566 * lb;
    const s = 0.0883024619 * lr + 0.2817188376 * lg + 0.6299787005 * lb;

    const l_ = Math.cbrt(l);
    const m_ = Math.cbrt(m);
    const s_ = Math.cbrt(s);

    const L = 0.2104542553 * l_ + 0.7936177850 * m_ - 0.0040720468 * s_;
    const a = 1.9779984951 * l_ - 2.4285922050 * m_ + 0.4505937099 * s_;
    const b_ = 0.0259040371 * l_ + 0.7827717662 * m_ - 0.8086757660 * s_;

    const c = Math.sqrt(a ** 2 + b_ ** 2);
    let h = Math.atan2(b_, a) * 180 / Math.PI;
    h = h >= 0 ? h : h + 360;

    return { 
      l: L * 100,
      c: c,
      h: h
    };
  }

  function hexToOklch(hex) {
    const { r, g, b } = hexToRgb(hex);
    return rgbToOklch(r, g, b);
  }

  function oklchToHex(h, c, l = 50) {
    const L = l / 100;
    const a = c * Math.cos(h * Math.PI / 180);
    const b = c * Math.sin(h * Math.PI / 180);

    const l_ = L + 0.3963377774 * a + 0.2158037573 * b;
    const m_ = L - 0.1055613458 * a - 0.0638541728 * b;
    const s_ = L - 0.0894841775 * a - 1.2914855480 * b;

    const [lr, lg, lb] = [l_, m_, s_].map(v => v ** 3);
    
    const r = 4.0767416621 * lr - 3.3077115913 * lg + 0.2309699292 * lb;
    const g = -1.2684380046 * lr + 2.6097574011 * lg - 0.3413193965 * lb;
    const bLinear = -0.0041960863 * lr - 0.7034186147 * lg + 1.7076147010 * lb;

    const toSRGB = (v) => {
      v = Math.min(Math.max(v, 0), 1);
      return v > 0.0031308 
        ? 1.055 * (v ** (1/2.4)) - 0.055 
        : 12.92 * v;
    };

    const [R, G, B] = [r, g, bLinear].map(v => 
      Math.round(toSRGB(v) * 255)
    );

    return `#${[R, G, B]
      .map(x => x.toString(16).padStart(2, '0'))
      .join('')}`.toUpperCase();
  }

  function updateTextPrimary(currentL) {
    const textL = currentL > 60 ? 20 : 95;
    document.documentElement.style.setProperty('--text-primary', `oklch(${textL}% 0 0)`);
  }

  function updateBaseHueFromColorPicker(event) {
    const color = event.target.value;
    const { h, c } = hexToOklch(color);
    const theme = document.documentElement.getAttribute("data-theme") || "dark";
    const currentL = theme === "dark" ? 30 : 80;

    document.documentElement.style.setProperty('--base-hue', h);
    document.documentElement.style.setProperty('--base-chroma', c);
    localStorage.setItem(`${theme}BaseHue`, h);
    localStorage.setItem(`${theme}BaseChroma`, c);

    updateTextPrimary(currentL);
  }

  function toggleConfig() {
    fetch("/luci-static/spectra/bgm/theme-switcher.php", { method: "POST" })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateButton(data.mode);
        } else {
          document.getElementById("status").innerText = "Update failed: " + data.error;
        }
      })
      .catch(error => {
        document.getElementById("status").innerText = "Request error: " + error;
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
    const baseHue   = parseFloat(localStorage.getItem(hueKey))   ?? (theme==="dark"?260:200);
    const baseChroma= parseFloat(localStorage.getItem(chromaKey))?? (theme==="dark"?0.10:0.05);

    body.style.setProperty('--base-hue', baseHue);
    body.style.setProperty('--base-chroma', baseChroma);
    body.setAttribute("data-theme", theme);

    const l = theme === "dark" ? 30 : 80;
    document.getElementById("colorPicker").value = oklchToHex(baseHue, baseChroma, l);

    if (theme === "dark") {
        const message = translations['current_mode_dark'] || "Current Mode: Dark Mode";
        btn.innerHTML = `<i class="bi bi-sun"></i> ${translations['switch_to_light_mode'] || 'Switch to Light Mode'}`;
        btn.className = "btn btn-primary light";
        status.innerText = message;

        if (userInteracted && typeof speakMessage === 'function') {
            speakMessage(message);
        }
        if (userInteracted && typeof showLogMessage === 'function') {
            showLogMessage(message);
        }
    } else {
        const message = translations['current_mode_light'] || "Current Mode: Light Mode";
        btn.innerHTML = `<i class="bi bi-moon"></i> ${translations['switch_to_dark_mode'] || 'Switch to Dark Mode'}`;
        btn.className = "btn btn-primary dark";
        status.innerText = message;
        if (userInteracted && typeof speakMessage === 'function') {
            speakMessage(message);
        }
        if (userInteracted && typeof showLogMessage === 'function') {
            showLogMessage(message);
        }
    }

    const currentL = theme === "dark" ? 30 : 80;
    updateTextPrimary(currentL);

    localStorage.setItem("theme", theme);
    const bodyBgKey = `${theme}BodyBgColor`;
    const defaultBg = theme === "dark" ? "#333333" : "#f0ffff";
    document.body.style.background = localStorage.getItem(bodyBgKey) || defaultBg;
    
    document.getElementById("bodyBgColorPicker").value = document.body.style.background;
  }

  function getContrastColor(hex) {
    const rgb = hexToRgb(hex);
    const luminance = (0.2126 * rgb.r + 0.7152 * rgb.g + 0.0722 * rgb.b) / 255;
    return luminance > 0.45 ? '#000000' : '#FFFFFF';
  }

  document.addEventListener("DOMContentLoaded", () => {
    const saved = localStorage.getItem("theme") || "dark";
    document.documentElement.setAttribute("data-theme", saved);

    const hueKey    = `${saved}BaseHue`;
    const chromaKey = `${saved}BaseChroma`;
    const defaultHue    = saved==="dark"?260:0;
    const defaultChroma = saved==="dark"?0.10:0.05;

    const hVal = parseFloat(localStorage.getItem(hueKey))    || defaultHue;
    const cVal = parseFloat(localStorage.getItem(chromaKey)) || defaultChroma;
    document.documentElement.style.setProperty('--base-hue', hVal);
    document.documentElement.style.setProperty('--base-chroma', cVal);

    const picker = document.getElementById("colorPicker");
    const l = saved==="dark"?30:80;
    picker.value = oklchToHex(hVal, cVal, l);
    picker.addEventListener('input', updateBaseHueFromColorPicker);
    document.getElementById("penIcon")?.addEventListener("click", () => picker.click());

    const toggleBtn = document.getElementById("toggleButton");
    toggleBtn.addEventListener("click", () => {
      userInteracted = true;

      setTimeout(() => {
        window.top.location.href = "/cgi-bin/luci/admin/services/spectra";
      }, 3000);
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "ArrowDown" || e.keyCode === 40) {
        userInteracted = true;
      }
    });

    fetch("/luci-static/spectra/bgm/theme-switcher.php")
      .then(res => res.json())
      .then(data => {
        if(data.mode) {
          updateButton(data.mode);
        }
      })
      .catch(error => {
        document.getElementById("status").innerText = "Failed to read: " + error;
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
      .catch(error => console.error('Request failed:', error));
}
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const initColorStorage = () => {
        if (!localStorage.getItem("darkBodyBgColor")) localStorage.setItem("darkBodyBgColor", "#333333");
        if (!localStorage.getItem("lightBodyBgColor")) localStorage.setItem("lightBodyBgColor", "#f0ffff");
    };

    const applyThemeColor = () => {
        const currentTheme = document.documentElement.getAttribute("data-theme") || "dark";
        const colorKey = `${currentTheme}BodyBgColor`;
        document.body.style.background = localStorage.getItem(colorKey);
        document.getElementById("bodyBgColorPicker").value = localStorage.getItem(colorKey);
    };

    initColorStorage();

    applyThemeColor();

    const themeObserver = new MutationObserver((mutations) => {
        mutations.forEach(mutation => {
            if (mutation.attributeName === "data-theme") {
                applyThemeColor();
            }
        });
    });
    themeObserver.observe(document.documentElement, { attributes: true });

    document.getElementById("bodyBgColorPicker").addEventListener("input", (e) => {
        const currentTheme = document.documentElement.getAttribute("data-theme") || "dark";
        const colorKey = `${currentTheme}BodyBgColor`;
        const newColor = e.target.value;
        
        document.body.style.background = newColor;
        localStorage.setItem(colorKey, newColor);
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
    color: #fff;
    font-weight: bold;
}

.playlist-item.active {
    background: var(--rose-bg);
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
    display: none; 
    opacity: 0;
    pointer-events: none;
    cursor: default; 
    transition: opacity 0.3s ease;
    writing-mode: vertical-rl;
    text-orientation: mixed;
    line-height: 2;
    z-index: 2;
    flex-direction: column; 
    gap: 0.5em;
    width: 200px;
    resize: none;
    overflow: auto;
    user-select: none;
}

#floatingLyrics.visible {
    display: flex;
    opacity: 1;
    pointer-events: auto;
    cursor: move;  
}

#floatingLyrics #floatingCurrentSong.vertical-title {
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
    margin-bottom: 10px;
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
    font-size: 1.6rem;
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
    display: inline-block;
    margin-right: 2px;
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

.char.active {
    transform: scale(1.2);
}

.char[data-start] + .char[data-start] {
    margin-left: 12px;
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
    writing-mode: vertical-rl; 
    text-orientation: upright; 
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

.log-box {
    position: fixed;
    left: 20px;
    padding: 12px 16px;
    background: var(--sand-bg);
    color: white;
    border-radius: 8px;
    z-index: 9999;
    max-width: 320px;
    font-size: 15px;
    word-wrap: break-word;
    line-height: 1.5;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.15);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(2px);
    transform: translateY(0);
    opacity: 0;
    animation: scrollUp 12s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    display: inline-block;
    margin-bottom: 10px;
    transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

@keyframes scrollUp {
    0% {
        top: 90%;
        opacity: 0;
    }
    20% {
        opacity: 1;
    }
    80% {
        top: 50%;
        opacity: 1;
    }
    100% {
        top: 45%;
        opacity: 0;
    }
}

.log-box.exiting {
    animation: fadeOut 0.3s forwards;
}

.log-content {
    padding: 6px 20px 6px 8px;
    color: white;
}

.close-btn {
    position: absolute;
    top: 6px;
    right: 10px;
    background: transparent;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 20px;
    line-height: 1;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
}

.log-box:hover .close-btn {
    opacity: 0.7;
    pointer-events: auto;
}

.log-box:hover .close-btn:hover {
    opacity: 1;
}

@keyframes fadeOut {
    to { 
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
}

.log-icon {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin-right: 3px;
    vertical-align: middle;
}

.log-box.error { background: linear-gradient(145deg, #ff4444, #cc0000); }
.log-box.warning { background: linear-gradient(145deg, #ffc107, #ffab00); }
.log-box.info { background: linear-gradient(145deg, #2196F3, #1976D2); }

@media (max-width: 768px) {
    .log-box {
        left: 10px;
        right: 10px;
        max-width: none;
        font-size: 14px;
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
    color: white !important;
}

.list-group-item:hover .text-muted,
.list-group-item:hover .text-truncate {
    color: white !important;
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

const showLogMessage = (function() {
    const bgColors = [
        'var(--ocean-bg)',
        'var(--forest-bg)',
        'var(--lavender-bg)',
        'var(--sand-bg)'
    ];
    
    let currentIndex = 0;
    const activeLogs = new Set();
    const BASE_OFFSET = 20;
    const MARGIN = 10;

    function createIcon(type) {
        const icons = {
            error: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z',
            warning: 'M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z',
            info: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z'
        };
    
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#fff" d="${icons[type] || icons.info}"/></svg>`;
    
        return `data:image/svg+xml;base64,${btoa(svg)}`;
    }

    function updatePositions() {
        let verticalPos = BASE_OFFSET;
        activeLogs.forEach(log => {
            log.style.transform = `translateY(${verticalPos}px)`;
            verticalPos += log.offsetHeight + MARGIN;
        });
    }

    return function(message, type = '') {
        const logBox = document.createElement('div');
        logBox.className = `log-box ${type}`;
        
        if (!type) {
            logBox.style.background = bgColors[currentIndex];
            currentIndex = (currentIndex + 1) % bgColors.length;
        }

        logBox.innerHTML = `
            <div class="log-content">
                <span class="log-icon" style="background-image:url('${createIcon(type)}')"></span>
                ${decodeURIComponent(message)}
                <button class="close-btn">&times;</button>
            </div>
        `;

        logBox.querySelector('.close-btn').onclick = () => {
            logBox.classList.add('exiting');
            setTimeout(() => logBox.remove(), 300);
        };

        logBox.addEventListener('mouseenter', () => 
            logBox.style.animationPlayState = 'paused');
        logBox.addEventListener('mouseleave', () => 
            logBox.style.animationPlayState = 'running');

        document.body.appendChild(logBox);
        activeLogs.add(logBox);
        
        requestAnimationFrame(() => {
            logBox.classList.add('active');
            updatePositions();
        });

        setTimeout(() => {
            logBox.classList.add('exiting');
            setTimeout(() => {
                logBox.remove();
                activeLogs.delete(logBox);
                updatePositions();
            }, 300);
        }, 12000);
    };
})();

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

function changeTrack(direction, isManual = false) {
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
    showLogMessage(`Playlist loaded: ${songs.length} songs`);
    setTimeout(() => scrollToCurrentTrack(), 100);
}

function playTrack(index) {
    const songName = decodeURIComponent(songs[index].split('/').pop().replace(/\.\w+$/, ''));
    const message = `${translations['playlist_click'] || 'Playlist Click'}：${translations['index'] || 'Index'}：${index + 1}，${translations['song_name'] || 'Song Name'}：${songName}`;
    audioPlayer.pause();
    currentTrackIndex = index;
    loadTrack(songs[index]);
    
    isPlaying = true;
    audioPlayer.play().catch((error) => {
        isPlaying = false;
    });
    
    updatePlayButton();
    savePlayerState();
    showLogMessage(message);
    speakMessage(message);
    
    event.target.classList.add('clicked');
    setTimeout(() => event.target.classList.remove('clicked'), 200);
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
        const message = translations['loading_lyrics'] || 'Loading Lyrics...';
        const verticalText = message.split('').map(char => `<span class="char">${char}</span>`).join('');
    
        const statusMsg = container.id === 'lyricsContainer' 
            ? `<div id="no-lyrics">${message}</div>`
            : `<div id="noLyricsFloating" class="vertical-message">${verticalText}</div>`;

        container.innerHTML = statusMsg;
    });

    fetch(lyricsUrl)
        .then(response => {
            if (!response.ok) {
                if (response.status === 404) {
                    throw new Error('LYRICS_NOT_FOUND');
                } else {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
            }
            return response.arrayBuffer();
        })
        .then(buffer => {
            const decoder = new TextDecoder('utf-8');
            parseLyrics(decoder.decode(buffer));
            displayLyrics();
            document.dispatchEvent(new Event('lyricsLoaded'));
        })
        .catch(error => {
            if (error.message === 'LYRICS_NOT_FOUND') {
                containers.forEach(container => {
                    if (container.id === 'lyricsContainer') {
                        container.innerHTML = `<div id="no-lyrics">${translations['no_lyrics'] || 'No Lyrics Available'}</div>`;
                    } else {
                        const message = translations['no_lyrics'] || 'No Lyrics Available';
                        const verticalText = message.split('').map(char => `<span class="char">${char}</span>`).join('');
                        container.innerHTML = `<div id="noLyricsFloating" class="vertical-text">${verticalText}</div>`;
                    }
                });
            } else {
                console.error(`${translations['lyrics_load_failed'] || 'Lyrics Load Failed'}:`, error);
                containers.forEach(container => {
                    if (container.id === 'lyricsContainer') {
                        container.innerHTML = `<div id="no-lyrics">${translations['lyrics_load_failed'] || 'Failed to load lyrics'}</div>`;
                    } else {
                        const message = translations['lyrics_load_failed'] || 'Failed to load lyrics';
                        const verticalText = message.split('').map(char => `<span class="char">${char}</span>`).join('');
                        container.innerHTML = `<div id="noLyricsFloating" class="vertical-message">${verticalText}</div>`;
                    }
                });
            }
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

function isEnglishWord(text) {
    return /^[a-zA-Z']+$/.test(text);
}

function createCharSpans(text, startTime, endTime) {
    const elements = [];
    const words = text.split(/(\s+)/); 
    
    let currentTime = startTime;
    const totalDuration = endTime - startTime;
    const validWords = words.filter(word => word.trim().length > 0);
    const durationPerWord = totalDuration / validWords.length;

    words.forEach(word => {
        if (word.trim().length === 0) return;

        const isEnglish = isEnglishWord(word.replace(/[^a-zA-Z']/g, ''));
        const span = document.createElement('span');
        span.className = 'char';
        
        if (isEnglish) {
            span.textContent = word;
            span.dataset.start = currentTime;
            span.dataset.end = currentTime + durationPerWord;
            currentTime += durationPerWord;
        } else {
            const chars = word.split('');
            const charDuration = durationPerWord / chars.length;
            chars.forEach((char, index) => {
                const charSpan = document.createElement('span');
                charSpan.className = 'char';
                charSpan.textContent = char;
                charSpan.dataset.start = currentTime + index * charDuration;
                charSpan.dataset.end = currentTime + (index + 1) * charDuration;
                elements.push(charSpan);
            });
            currentTime += durationPerWord;
            return;
        }
        
        elements.push(span);
    });

    return elements;
}

function displayLyrics() {
    const lyricsContainer = document.getElementById('lyricsContainer');
    const floatingLyrics = document.querySelector('#floatingLyrics .vertical-lyrics');
    
    lyricsContainer.innerHTML = '';
    floatingLyrics.innerHTML = '';

    if (Object.keys(window.lyrics).length === 0) {
        const message = translations['no_lyrics'] || 'No Lyrics Available';
        const verticalText = message.split('').map(char => `<span class="char">${char}</span>`).join('');
    
        lyricsContainer.innerHTML = `<div id="no-lyrics">${message}</div>`;
        floatingLyrics.innerHTML = `<div id="noLyricsFloating" class="vertical-message">${verticalText}</div>`;
        return;
    }

    lyricTimes.forEach((time, index) => {
        const line = document.createElement('div');
        line.className = 'lyric-line';
        line.dataset.time = time;
        
        const endTime = index < lyricTimes.length - 1 
                      ? lyricTimes[index + 1] 
                      : time + 3; 
        
        const elements = createCharSpans(lyrics[time], time, endTime);
        elements.forEach(element => line.appendChild(element));
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

    audioPlayer.pause();
    audioPlayer.src = url;
    
    audioPlayer.load();
    audioPlayer.addEventListener('canplaythrough', () => {
        audioPlayer.play().catch((error) => {
            console.log(`${translations['autoplay_blocked'] || 'Autoplay Blocked'}:`, error);
            showLogMessage(translations['click_to_play'] || 'Click play button to start');
        });
        isPlaying = true;
        updatePlayButton();
    }, { once: true });

    updatePlayButton(); 
    updatePlaylistUI();
    loadLyrics(url);
    updateCurrentSong(url);
    updateTimeDisplay();
    
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
    
    const floatingTitle = document.querySelector('#floatingLyrics #floatingCurrentSong');
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
        .catch(error => console.error('Playlist loading failed:', error));
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
            const itemTop = activeItem.offsetTop;
            const itemHeight = activeItem.offsetHeight;
            const containerHeight = playlist.offsetHeight;

            if (itemTop < playlist.scrollTop || 
                itemTop + itemHeight > playlist.scrollTop + containerHeight) {
                activeItem.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                    inline: 'nearest'
                });
            }

            activeItem.style.animation = 'none';
        }
    }, 300);
}

loadDefaultPlaylist();
window.addEventListener('resize', () => {
    isSmallScreen = window.innerWidth < 768;
});
</script>

<style>
.lyrics-mode {
    .non-lyrics-content {
        display: none !important;
    }

    #lyricsContainer {
        height: calc(70vh - 150px) !important; 
        position: relative;
        z-index: 1000;
    }

    #currentSong {
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    #floatingLyrics {
        display: none;
    }
}

#lyricsToggle .bi {
    transition: transform 0.3s;
}
.lyrics-mode #lyricsToggle .bi {
    transform: rotate(180deg);
}
</style>

<script>
let isLyricsMode = localStorage.getItem('lyricsMode') === 'true';

function toggleLyricsMode() {
    const modal = document.getElementById('musicModal');
    const icon = document.getElementById('lyricsIcon');

    isLyricsMode = !isLyricsMode;
    modal.classList.toggle('lyrics-mode', isLyricsMode);
    localStorage.setItem('lyricsMode', isLyricsMode);

    icon.className = isLyricsMode ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
}

document.addEventListener('DOMContentLoaded', () => {
    const icon = document.getElementById('lyricsIcon');
    document.getElementById('lyricsToggle').addEventListener('click', toggleLyricsMode);

    if (isLyricsMode) {
        document.getElementById('musicModal').classList.add('lyrics-mode');
        icon.className = 'bi bi-chevron-up';
    }
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
  const muteToggle    = document.getElementById('muteToggle');
  const volumeToggle  = document.getElementById('volumeToggle');
  const volumePanel   = document.getElementById('volumePanel');
  const volumeSlider  = document.getElementById('volumeSlider');
  const muteIconEl    = muteToggle.querySelector('i');
  const volumeIconEl  = volumeToggle.querySelector('i');

  let lastVolume = 1;

  const savedVolume = localStorage.getItem('audioVolume');
  const savedMuted  = localStorage.getItem('audioMuted');
  if (savedVolume !== null) {
    lastVolume = parseFloat(savedVolume);
  }
  audioPlayer.volume = lastVolume;
  volumeSlider.value = lastVolume;
  audioPlayer.muted = (savedMuted === 'true');

  updateVolumeIcon();

  function togglePanel() {
    const isVisible = volumePanel.classList.contains('show');
    if (isVisible) {
      volumePanel.classList.remove('show');
      setTimeout(() => (volumePanel.style.display = 'none'), 200);
    } else {
      volumePanel.style.display = 'block';
      setTimeout(() => volumePanel.classList.add('show'), 10);
    }
  }

  function toggleMute() {
    audioPlayer.muted = !audioPlayer.muted;
    if (!audioPlayer.muted && audioPlayer.volume === 0) {
      audioPlayer.volume = lastVolume;
      volumeSlider.value = lastVolume;
    }
    localStorage.setItem('audioMuted', audioPlayer.muted);
    updateVolumeIcon();

    const muteMessage = audioPlayer.muted
      ? (translations['mute_on']  || 'Audio muted')
      : (translations['mute_off'] || 'Audio unmuted');
    showLogMessage(muteMessage);
    speakMessage(muteMessage);
  }

  function updateVolumeIcon() {
    let cls;
    if (audioPlayer.muted || audioPlayer.volume === 0) {
      cls = 'bi bi-volume-mute-fill';
    } else if (audioPlayer.volume < 0.5) {
      cls = 'bi bi-volume-down-fill';
    } else {
      cls = 'bi bi-volume-up-fill';
    }
    muteIconEl.className = cls;
    volumeIconEl.className = cls;

    if (!audioPlayer.muted) {
      lastVolume = audioPlayer.volume;
      localStorage.setItem('audioVolume', lastVolume);
    }
  }

  muteToggle.addEventListener('click', e => {
    e.stopPropagation();
    toggleMute();
  });

  volumeToggle.addEventListener('click', e => {
    e.stopPropagation();
    if (e.target === volumeIconEl) {
      toggleMute();
    } else {
      togglePanel();
    }
  });

  document.addEventListener('click', () => {
    if (volumePanel.classList.contains('show')) {
      volumePanel.classList.remove('show');
      setTimeout(() => (volumePanel.style.display = 'none'), 200);
    }
  });

  volumeSlider.addEventListener('input', e => {
    const vol = Math.round(parseFloat(e.target.value) * 100);
    audioPlayer.volume = e.target.value;
    if (audioPlayer.muted) {
      audioPlayer.muted = false;
      localStorage.setItem('audioMuted', 'false');
    }
    updateVolumeIcon();

    const volumeMessage = translations['volume_change']
      ? translations['volume_change'].replace('{vol}', vol)
      : `Volume adjusted to ${vol}%`;
    showLogMessage(volumeMessage);
    speakMessage(volumeMessage);
  });

  const speedToggle = document.getElementById('speedToggle');
  const speedLabel = document.getElementById('speedLabel');
  const speeds = [0.75, 1, 1.25, 1.5, 1.75, 2];
  let speedIndex = 1;

  const savedSpeed = localStorage.getItem('audioSpeed');
  if (savedSpeed !== null) {
    const idx = speeds.indexOf(parseFloat(savedSpeed));
    if (idx !== -1) {
      speedIndex = idx;
    }
  }

  audioPlayer.playbackRate = speeds[speedIndex];
  speedLabel.textContent = speeds[speedIndex] + '×';

  function toggleSpeed() {
    speedIndex = (speedIndex + 1) % speeds.length;
    const rate = speeds[speedIndex];
    audioPlayer.playbackRate = rate;
    speedLabel.textContent = rate + '×';
    localStorage.setItem('audioSpeed', rate);

    const speedMessage = translations['speed_change']
      ? translations['speed_change'].replace('{rate}', rate)
      : `Playback speed changed to ${rate}x`;
    showLogMessage(speedMessage);
    speakMessage(speedMessage);
  }

  speedToggle.addEventListener('click', e => {
    e.stopPropagation();
    toggleSpeed();
  });

  speedToggle.addEventListener('click', e => e.stopPropagation());
</script>

<script>
document.addEventListener('keydown', function(event) {
    if (event.ctrlKey && event.shiftKey && event.code === 'KeyC') {
        confirmAndClearCache();
        event.preventDefault();
    }
});

document.getElementById('clear-cache-btn').addEventListener('click', function() {
    confirmAndClearCache();
});

function confirmAndClearCache() {
    const confirmText = translations['clear_confirm'] || 'Are you sure you want to clear the configuration?';
    speakMessage(translations['clear_confirm'] || 'Are you sure you want to clear the configuration?');
    showConfirmation(confirmText, () => {
        clearCache();
    });
}

function clearCache() {
    localStorage.clear();
    sessionStorage.clear();
    sessionStorage.setItem('cacheCleared', 'true');
    location.reload(true);
}

window.addEventListener('load', function() {
    if (sessionStorage.getItem('cacheCleared') === 'true') {
        const message = translations['cache_cleared'] || 'Cache Cleared';
        showLogMessage(message);
        speakMessage(message);
        sessionStorage.removeItem('cacheCleared');
        setTimeout(() => {
            window.top.location.href = "/cgi-bin/luci/admin/services/spectra";
        }, 3000);
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
  const icon = document.getElementById("fontToggleIcon");
  const body = document.body;
  const storageKey = "fontToggle";

  const fonts = [
    { class: "default-font", key: "font_default", icon: "fa-font" },
    { class: "fredoka-font", key: "font_fredoka", icon: "fa-child-reaching" },
    { class: "system-nofo-font", key: "font_noto", icon: "fa-language" },
    { class: "system-mono-font", key: "font_mono", icon: "fa-code" },
    { class: "dm-serif-font", key: "font_dm_serif", icon: "fa-feather-pointed" }
  ];

  const savedFont = localStorage.getItem(storageKey);
  if (savedFont) {
    const fontObj = fonts.find(f => f.class === savedFont);
    body.classList.add(savedFont);
    if (fontObj) updateIcon(fontObj.icon);
  } else {
    updateIcon("fa-font");
  }

  btn.addEventListener("click", () => {
    const currentIndex = fonts.findIndex(f => body.classList.contains(f.class));
    const nextIndex = (currentIndex + 1) % fonts.length;
    const nextFont = fonts[nextIndex];

    fonts.forEach(f => body.classList.remove(f.class));
    body.classList.add(nextFont.class);
    localStorage.setItem(storageKey, nextFont.class);

    updateIcon(nextFont.icon);

    const message = translations[nextFont.key] || "Switched font";
    if (typeof speakMessage === "function") speakMessage(message);
    if (typeof showLogMessage === "function") showLogMessage(message);
  });

  function updateIcon(iconName) {
    icon.className = `fa-solid ${iconName}`;
    icon.style.color = "white";
  }
});
</script>

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
        'thailand'               => '泰语',
        'japanese'               => '日语',
        'russian'                => '俄语',
        'germany'                => '德语',
        'france'                 => '法语',
        'arabic'                 => '阿拉伯语',
        'spanish'                => '西班牙语',
        'bangladesh'             => '孟加拉语',
        'close'                  => '关闭',
        'save'                   => '保存',
        'theme_download'         => '主题下载',
        'select_all'             => '全选',
        'batch_delete'           => '批量删除选中文件',
        'batch_delete_success'   => '✅ 批量删除成功',
        'batch_delete_failed'    => '❌ 批量删除失败',
        'confirm_delete'         => '确定删除？',
        'total'                  => '总共：',
        'free'                   => '剩余：',
        'hover_to_preview'       => '点击激活悬停播放',
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
        'supported_formats'      => '支持格式：[ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
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
        'zodiacs' => ['猴','鸡','狗','猪','鼠','牛','虎','兔','龙','蛇','马','羊'],
        'heavenlyStems' => ['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'],
        'earthlyBranches' => ['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'],
        'months' => ['正','二','三','四','五','六','七','八','九','十','冬','腊'],
        'days' => ['初一','初二','初三','初四','初五','初六','初七','初八','初九','初十',
                   '十一','十二','十三','十四','十五','十六','十七','十八','十九','二十',
                   '廿一','廿二','廿三','廿四','廿五','廿六','廿七','廿八','廿九','三十'],
        'leap_prefix' => '闰',
        'year_suffix' => '年',
        'month_suffix' => '月',
        'day_suffix' => '',
        'periods' => ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'],
        'default_period' => '時',
        'initial' => '初',  
        'middle' => '正',   
        'final' =>'末',  
        'clear_confirm' =>'确定要清除目前配置恢复预设配置吗？', 
        'back_to_first' => '已返回播放列表第一首歌曲',
        'font_default' => '已切换为圆润字体',
        'font_fredoka' => '已切换为默认字体',
        'font_mono'   => '已切换为趣味手写字体',
        'font_noto'     => '已切换为中文衬线字体',
        'font_dm_serif'     => '已切换为 DM Serif Display 字体',
        'error_loading_time' => '时间显示异常',
        'switch_to_light_mode' => '切换到亮色模式',
        'switch_to_dark_mode' => '切换到暗色模式',
        'current_mode_dark' => '当前模式: 暗色模式',
        'current_mode_light' => '当前模式: 亮色模式',
        'fetching_version' => '正在获取版本信息...',
        'latest_version' => '最新版本',
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
        'unable_to_fetch_current_version' => '正在获取当前版本...',
        'current_version' => '当前版本',
        'copy_command'     => '复制命令',
        'command_copied'   => '命令已复制到剪贴板！',
        "updateModalLabel" => "更新状态",
        "updateDescription" => "更新过程即将开始。",
        "waitingMessage" => "等待操作开始...",
        "update_plugin" => "更新插件",
        "installation_complete" => "安装完成！",
        'confirm_title'             => '确认操作',
        'confirm_delete_file'   => '确定要删除文件 %s 吗？',
        'delete_success'      => '删除成功：%s',
        'delete_failure'      => '删除失败：%s',
        'upload_error_type_not_supported' => '不支持的文件类型：%s',
        'upload_error_move_failed'        => '文件上传失败：%s',
        'confirm_clear_background' => '确定要清除背景吗？',
        'background_cleared'      => '背景已清除！',
        'createShareLink' => '创建分享链接',
        'closeButton' => '关闭',
        'expireTimeLabel' => '过期时间',
        'expire1Hour' => '1 小时',
        'expire1Day' => '1 天',
        'expire7Days' => '7 天',
        'expire30Days' => '30 天',
        'maxDownloadsLabel' => '最大下载次数',
        'max1Download' => '1 次',
        'max5Downloads' => '5 次',
        'max10Downloads' => '10 次',
        'maxUnlimited' => '不限',
        'shareLinkLabel' => '分享链接',
        'copyLinkButton' => '复制链接',
        'closeButtonFooter' => '关闭',
        'generateLinkButton' => '生成链接',
        'fileNotSelected' => '未选择文件',
        'httpError' => 'HTTP 错误',
        'linkGenerated' => '✅ 分享链接已生成',
        'operationFailed' => '❌ 操作失败',
        'generateLinkFirst' => '请先生成分享链接',
        'linkCopied' => '📋 链接已复制',
        'copyFailed' => '❌ 复制失败',
        'cleanExpiredButton' => '清理过期',
        'deleteAllButton' => '删除全部',
        'cleanSuccess' => '✅ 清理完成，%s 项已删除',
        'deleteSuccess' => '✅ 所有分享记录已删除，%s 个文件已移除',
        'confirmDeleteAll' => '⚠️ 确定要删除所有分享记录吗？',
        'operationFailed' => '❌ 操作失败',
        'ip_info' => 'IP详细信息',
        'ip_support' => 'IP支持',
        'ip_address' => 'IP地址',
        'location' => '地区',
        'isp' => '运营商',
        'asn' => 'ASN',
        'timezone' => '时区',
        'latitude_longitude' => '经纬度',
        'latency_info' => '延迟信息',
        'mute_on' => '音频已静音',
        'mute_off' => '音频取消静音',
        'volume_change' => '音量调整为 {vol}%',
        'speed_change' => '播放速度切换为 {rate} 倍',
        'invalid_city_non_chinese' => '请输入非中文的城市名称。',
        'invalid_city_uppercase' => '城市名称必须以大写英文字母开头。',
        'city_saved' => '城市已保存为：{city}',
        'city_saved_speak' => '城市已保存为{city}，正在获取最新天气信息...',
        'invalid_city' => '请输入有效的城市名称。',
        'set_city' => '设置城市',
        'input_label' => '城市名称',
        'input_placeholder' => '例如：Beijing',
        'floating_lyrics_enabled' => '浮动歌词已开启',
        'floating_lyrics_disabled' => '浮动歌词已关闭',
        'weather_label'     => '天气',
        'temperature_label' => '温度',
        'feels_like_label'  => '体感',
        'humidity_label'    => '湿度',
        'pressure_label'    => '气压',
        'wind_label'        => '风速',
        'sunrise_label'     => '日出',
        'sunset_label'      => '日落',
        'fit_contain'    => '正常比例',
        'fit_fill'       => '拉伸填充',
        'fit_none'       => '原始尺寸',
        'fit_scale-down' => '智能适应',
        'fit_cover'      => '默认裁剪',
        'current_fit_mode'    => '当前显示模式',
        'selected_info' => '已选择 %d 个文件，合计 %s MB'
    ],

    'hk' => [
        'select_language'        => '選擇語言',
        'simplified_chinese'     => '簡體中文',
        'traditional_chinese'    => '繁體中文',
        'english'                => '英文',
        'korean'                 => '韓語',
        'vietnamese'             => '越南語',
        'thailand'               => '泰語',
        'japanese'               => '日語',
        'russian'                => '俄語',
        'germany'                => '德語',
        'france'                 => '法語',
        'arabic'                 => '阿拉伯語',
        'spanish'                => '西班牙語',
       'bangladesh'              => '孟加拉語',
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
        'supported_formats'      => '支持格式：[ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
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
        'zodiacs' => ['猴','雞','狗','豬','鼠','牛','虎','兔','龍','蛇','馬','羊'],
        'heavenlyStems' => ['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'],
        'earthlyBranches' => ['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'],
        'months' => ['正','二','三','四','五','六','七','八','九','十','冬','臘'],
        'days' => ['初一','初二','初三','初四','初五','初六','初七','初八','初九','初十',
                   '十一','十二','十三','十四','十五','十六','十七','十八','十九','二十',
                   '廿一','廿二','廿三','廿四','廿五','廿六','廿七','廿八','廿九','三十'],
        'leap_prefix' => '閏',
        'year_suffix' => '年',
        'month_suffix' => '月',
        'day_suffix' => '',
        'periods' => ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'],
        'default_period' => '時',
        'initial' => '初',  
        'middle' => '正',   
        'final' =>'末',   
        'clear_confirm' => '確定要清除目前配置恢復預設配置嗎？',
        'back_to_first' => '已返回播放列表第一首歌曲', 
        'error_loading_time' => '時間顯示異常',
        'switch_to_light_mode' => '切換到亮色模式',
        'switch_to_dark_mode' => '切換到暗色模式',
        'current_mode_dark' => '當前模式: 暗色模式',
        'current_mode_light' => '當前模式: 亮色模式',
        'fetching_version' => '正在獲取版本信息...',
        'latest_version' => '最新版本',
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
        'font_default' => '已切換為圓潤字體',
        'font_fredoka' => '已切換為預設字體',
        'font_mono'    => '已切換為趣味手寫字體',
        'font_noto'    => '已切換為中文襯線字體',
        'font_dm_serif'     => '已切換為 DM Serif Display 字體',
        'batch_delete_success' => '✅ 批量刪除成功',
        'batch_delete_failed' => '❌ 批量刪除失敗',
        'confirm_delete' => '確定刪除？',
        'unable_to_fetch_current_version' => '正在獲取當前版本...',
        'current_version' => '當前版本',
        'copy_command'     => '複製命令',
        'command_copied'   => '命令已複製到剪貼簿！',
        "updateModalLabel" => "更新狀態",
        "updateDescription" => "更新過程即將開始。",
        "waitingMessage" => "等待操作開始...",
        "update_plugin" => "更新插件",
        "installation_complete" => "安裝完成！",
        'confirm_title'         => '確認操作',
        'confirm_delete_file'   => '確定要刪除文件 %s 嗎？',
        'delete_success'      => '刪除成功：%s',
        'delete_failure'      => '刪除失敗：%s',
        'upload_error_type_not_supported' => '不支持的文件類型：%s',
        'upload_error_move_failed'        => '文件上傳失敗：%s',
        'confirm_clear_background' => '確定要清除背景嗎？',
        'background_cleared'      => '背景已清除！',
        'createShareLink' => '創建分享鏈接',
        'closeButton' => '關閉',
        'expireTimeLabel' => '過期時間',
        'expire1Hour' => '1 小時',
        'expire1Day' => '1 天',
        'expire7Days' => '7 天',
        'expire30Days' => '30 天',
        'maxDownloadsLabel' => '最大下載次數',
        'max1Download' => '1 次',
        'max5Downloads' => '5 次',
        'max10Downloads' => '10 次',
        'maxUnlimited' => '不限',
        'shareLinkLabel' => '分享鏈接',
        'copyLinkButton' => '複製鏈接',
        'closeButtonFooter' => '關閉',
        'generateLinkButton' => '生成鏈接',
        'fileNotSelected' => '未選擇文件',
        'httpError' => 'HTTP 錯誤',
        'linkGenerated' => '✅ 分享鏈接已生成',
        'operationFailed' => '❌ 操作失敗',
        'generateLinkFirst' => '請先生成分享鏈接',
        'linkCopied' => '📋 鏈接已複製',
        'copyFailed' => '❌ 複製失敗',
        'cleanExpiredButton' => '清理過期',
        'deleteAllButton' => '刪除全部',
        'cleanSuccess' => '✅ 清理完成，%s 項已刪除',
        'deleteSuccess' => '✅ 所有分享記錄已刪除，%s 個文件已移除',
        'confirmDeleteAll' => '⚠️ 確定要刪除所有分享記錄嗎？',
        'operationFailed' => '❌ 操作失敗',
        'ip_info' => 'IP詳細資料',
        'ip_support' => 'IP支援',
        'ip_address' => 'IP地址',
        'location' => '地區',
        'isp' => '運營商',
        'asn' => 'ASN',
        'timezone' => '時區',
        'latitude_longitude' => '經緯度',
        'latency_info' => '延遲資訊',
        'mute_on' => '音頻已靜音',
        'mute_off' => '音頻取消靜音',
        'volume_change' => '音量調整為 {vol}%',
        'speed_change' => '播放速度切換為 {rate} 倍',
        'invalid_city_non_chinese' => '請輸入非中文的城市名稱。',
        'invalid_city_uppercase' => '城市名稱必須以大寫英文字母開頭。',
        'city_saved' => '城市已保存為：{city}',
        'city_saved_speak' => '城市已保存為{city}，正在獲取最新天氣信息...',
        'invalid_city' => '請輸入有效的城市名稱。',
        'set_city' => '設置城市',
        'input_label' => '城市名稱',
        'input_placeholder' => '例如：Beijing',
        'floating_lyrics_enabled' => '浮動歌詞已開啟',
        'floating_lyrics_disabled' => '浮動歌詞已關閉',
        'weather_label'     => '天氣',
        'temperature_label' => '溫度',
        'feels_like_label'  => '體感',
        'humidity_label'    => '濕度',
        'pressure_label'    => '氣壓',
        'wind_label'        => '風速',
        'sunrise_label'     => '日出',
        'sunset_label'      => '日落',
        'fit_contain'    => '正常比例',
        'fit_fill'       => '拉伸填充',
        'fit_none'       => '原始尺寸',
        'fit_scale-down' => '智能適應',
        'fit_cover'      => '預設裁剪',
        'current_fit_mode'    => '當前顯示模式',
        'selected_info' => '已選擇 %d 個文件，合計 %s MB'
    ],

    'ko' => [
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
        'bangladesh'             => '벵골어',
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
        'supported_formats'      => '지원 포맷: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
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
        'weekDays' =>  ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'],
        'labels' => [
            'year' => '년',
            'month' => '월',
            'day' => '일',
            'week' => ''
        ],
        'zodiacs' => ['원숭이','닭','개','돼지','쥐','소','호랑이','토끼','용','뱀','말','양'],
        'heavenlyStems' => ['갑','을','병','정','무','기','경','신','임','계'],
        'earthlyBranches' => ['자','축','인','묘','진','사','오','미','신','유','술','해'],
        'months' => ['1','2','3','4','5','6','7','8','9','10','11','12'],
        'days' => ['1일','2일','3일','4일','5일','6일','7일','8일','9일','10일',
                   '11일','12일','13일','14일','15일','16일','17일','18일','19일','20일',
                   '21일','22일','23일','24일','25일','26일','27일','28일','29일','30일'],
        'leap_prefix' => '윤',
        'year_suffix' => '년',
        'month_suffix' => '월',
        'day_suffix' => '',
        'initial' => '초',  
        'middle' => '정',   
        'final' =>'말',  
        'clear_confirm' => '구성을 지우시겠습니까?',
        'back_to_first' => '플레이리스트 첫 번째 곡으로 돌아갔습니다',
        'periods' => ['자', '축', '인', '묘', '진', '사', '오', '미', '신', '유', '술', '해'],
        'default_period' => '시',
        'error_loading_time' => '시간 표시 오류',
        'switch_to_light_mode' => '밝은 모드로 전환',
        'switch_to_dark_mode' => '어두운 모드로 전환',
        'current_mode_dark' => '현재 모드: 어두운 모드',
        'current_mode_light' => '현재 모드: 밝은 모드',
        'fetching_version' => '버전 정보를 가져오는 중...',
        'latest_version' => '최신 버전',
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
        'font_default' => '둥근 글꼴로 전환되었습니다',
        'font_fredoka' => '기본 글꼴로 전환되었습니다',
        'font_mono'    => '재미있는 손글씨 글꼴로 전환되었습니다',
        'font_noto'    => '중국어 명조체 글꼴로 전환되었습니다',
        'font_dm_serif'     => 'DM Serif Display 글꼴로 변경됨',
        'batch_delete_success' => '✅ 배치 삭제 성공',
        'batch_delete_failed' => '❌ 배치 삭제 실패',
        'confirm_delete' => '삭제하시겠습니까?',
        'unable_to_fetch_current_version' => '현재 버전 정보를 가져오는 중...',
        'current_version' => '현재 버전',
        'copy_command'     => '명령 복사',
        'command_copied'   => '명령이 클립보드에 복사되었습니다!',
        "updateModalLabel" => "업데이트 상태",
        "updateDescription" => "업데이트 과정이 곧 시작됩니다.",
        "waitingMessage" => "작업이 시작될 때까지 기다리는 중...",
        "update_plugin" => "플러그인 업데이트",
        "installation_complete" => "설치 완료!",
        'confirm_title'         => '작업 확인',
        'confirm_delete_file'   => '파일 %s을(를) 삭제하시겠습니까?',
        'delete_success'      => '삭제 성공: %s',
        'delete_failure'      => '삭제 실패: %s',
        'upload_error_type_not_supported' => '지원되지 않는 파일 형식: %s',
        'upload_error_move_failed'        => '파일 업로드 실패: %s',
        'confirm_clear_background' => '배경을 지우시겠습니까?',
        'background_cleared'      => '배경이 지워졌습니다!',
        'createShareLink' => '공유 링크 생성',
        'closeButton' => '닫기',
        'expireTimeLabel' => '만료 시간',
        'expire1Hour' => '1 시간',
        'expire1Day' => '1 일',
        'expire7Days' => '7 일',
        'expire30Days' => '30 일',
        'maxDownloadsLabel' => '최대 다운로드 횟수',
        'max1Download' => '1 회',
        'max5Downloads' => '5 회',
        'max10Downloads' => '10 회',
        'maxUnlimited' => '무제한',
        'shareLinkLabel' => '공유 링크',
        'copyLinkButton' => '링크 복사',
        'closeButtonFooter' => '닫기',
        'generateLinkButton' => '링크 생성',
        'fileNotSelected' => '파일을 선택하지 않았습니다',
        'httpError' => 'HTTP 오류',
        'linkGenerated' => '✅ 공유 링크 생성됨',
        'operationFailed' => '❌ 작업 실패',
        'generateLinkFirst' => '먼저 공유 링크를 생성하십시오',
        'linkCopied' => '📋 링크 복사됨',
        'copyFailed' => '❌ 복사 실패',
        'cleanExpiredButton' => '만료 정리',
        'deleteAllButton' => '모두 삭제',
        'cleanSuccess' => '✅ 정리 완료, %s 항목이 삭제되었습니다',
        'deleteSuccess' => '✅ 모든 공유 기록이 삭제되었습니다, %s 개의 파일이 제거되었습니다',
        'confirmDeleteAll' => '⚠️ 모든 공유 기록을 삭제하시겠습니까?',
        'operationFailed' => '❌ 작업 실패',
        'ip_info' => 'IP 상세 정보',
        'ip_support' => 'IP 지원',
        'ip_address' => 'IP 주소',
        'location' => '지역',
        'isp' => '통신사',
        'asn' => 'ASN',
        'timezone' => '시간대',
        'latitude_longitude' => '좌표',
        'latency_info' => '지연 정보',
        'mute_on' => '오디오가 음소거되었습니다',
        'mute_off' => '오디오 음소거 해제',
        'volume_change' => '볼륨이 {vol}%로 조정되었습니다',
        'speed_change' => '재생 속도가 {rate}배로 변경되었습니다',
        'invalid_city_non_chinese' => '중국어 문자가 없는 도시 이름을 입력하세요.',
        'invalid_city_uppercase' => '도시 이름은 대문자로 시작해야 합니다.',
        'city_saved' => '도시가 저장되었습니다: {city}',
        'city_saved_speak' => '도시가 {city}로 저장되었습니다. 최신 날씨 정보를 가져오고 있습니다...',
        'invalid_city' => '유효한 도시 이름을 입력하세요.',
        'set_city' => '도시 설정',
        'input_label' => '도시 이름',
        'input_placeholder' => '예: Beijing',
        'floating_lyrics_enabled' => '플로팅 가사가 활성화되었습니다',
        'floating_lyrics_disabled' => '플로팅 가사가 비활성화되었습니다',
        'weather_label'     => '날씨',
        'temperature_label' => '기온',
        'feels_like_label'  => '체감 온도',
        'humidity_label'    => '습도',
        'pressure_label'    => '기압',
        'wind_label'        => '풍속',
        'sunrise_label'     => '일출',
        'sunset_label'      => '일몰',
        'current_fit_mode'    => '현재 모드',
        'fit_contain'    => '정상 비율',
        'fit_fill'       => '채우기',
        'fit_none'       => '원본 크기',
        'fit_scale-down' => '스케일 다운',
        'fit_cover'      => '자르기',
        'selected_info' => '선택된 파일: %d개, 총합: %s MB'
    ],

    'ja' => [
        'select_language'        => '言語を選択',
        'simplified_chinese'     => '簡体字中国語',
        'traditional_chinese'    => '繁体字中国語',
        'english'                => '英語',
        'korean'                 => '韓国語',
        'vietnamese'             => 'ベトナム語',
        'thailand'               => 'タイ語',
        'japanese'               => '日本語',
        'russian'                => 'ロシア語',
        'germany'                => 'ドイツ語',
        'france'                 => 'フランス語',
        'arabic'                 => 'アラビア語',
        'spanish'                => 'スペイン語',
        'bangladesh'             => 'ベンガル語',
        'close'                  => '閉じる',
        'save'                   => '保存',
        'theme_download'         => 'テーマのダウンロード',
        'select_all'             => 'すべて選択',
        'batch_delete'           => '一括削除',
        'batch_delete_success'   => '✅ 一括削除成功',
        'batch_delete_failed'    => '❌ 一括削除失敗',
        'confirm_delete'         => '削除しますか？',
        'total'                  => '合計：',
        'free'                   => '空き容量：',
        'hover_to_preview'       => 'ホバーでプレビュー（クリックで有効化）',
        'spectra_config'         => 'Spectra設定管理',
        'current_mode'           => '現在のモード：読み込み中...',
        'toggle_mode'            => 'モード切替',
        'check_update'           => 'アップデート確認',
        'batch_upload'           => '一括アップロード',
        'add_to_playlist'        => 'プレイリストに追加',
        'clear_background'       => '背景をクリア',
        'clear_background_label' => '背景クリア',
        'file_list'              => 'ファイル一覧',
        'component_bg_color'     => 'コンポーネント背景色',
        'page_bg_color'          => 'ページ背景色',
        'toggle_font'            => 'フォント切替',
        'filename'               => 'ファイル名：',
        'filesize'               => 'サイズ：',
        'duration'               => '再生時間：',
        'resolution'             => '解像度：',
        'bitrate'                => 'ビットレート：',
        'type'                   => 'タイプ：',
        'image'                  => '画像',
        'video'                  => '動画',
        'audio'                  => '音声',
        'document'               => 'ドキュメント',
        'delete'                 => '削除',
        'rename'                 => '名前変更',
        'download'               => 'ダウンロード',
        'set_background'         => '背景設定',
        'preview'                => 'プレビュー',
        'toggle_fullscreen'      => '全画面切替',
        'supported_formats'      => '対応フォーマット：[ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'ファイルをドラッグ＆ドロップ',
        'or'                     => 'または',
        'select_files'           => 'ファイルを選択',
        'unlock_php_upload_limit'=> 'PHPアップロード制限解除',
        'upload'                 => 'アップロード',
        'cancel'                 => 'キャンセル',
        'rename_file'            => 'ファイル名変更',
        'new_filename'           => '新しいファイル名',
        'invalid_filename_chars' => '使用不可文字：\\/:*?"<>|',
        'confirm'                => '確認',
        'media_player'           => 'メディアプレイヤー',
        'playlist'               => 'プレイリスト',
        'clear_list'             => 'リストクリア',
        'toggle_list'            => 'リスト非表示',
        'picture_in_picture'     => 'ピクチャーインピクチャー',
        'fullscreen'             => '全画面表示',
        'music_player'           => 'ミュージックプレイヤー',
        'play_pause'             => '再生/一時停止',
        'previous_track'         => '前の曲',
        'next_track'             => '次の曲',
        'repeat_mode'            => 'リピート再生',
        'toggle_floating_lyrics' => 'フローティング歌詞',
        'clear_config'           => '設定クリア',
        'custom_playlist'        => 'カスタムプレイリスト',
        'volume'                 => '音量',
        'update_playlist'        => 'プレイリスト更新',
        'playlist_url'           => 'プレイリストURL',
        'reset_default'          => 'デフォルトに戻す',
        'toggle_lyrics'          => '歌詞非表示',
        'fetching_version'       => 'バージョン確認中...',
        'download_local'         => 'ローカルに保存',
        'change_language'        => '言語変更',
        'pause_playing'          => '再生停止',
        'start_playing'          => '再生開始',
        'manual_switch'          => '手動切替',
        'auto_switch'            => '自動切替：',
        'switch_to'              => '切り替え先：',
        'auto_play'              => '自動再生',
        'lyrics_load_failed'     => '歌詞読み込み失敗',
        'order_play'             => '順次再生',
        'single_loop'            => '一曲リピート',
        'shuffle_play'           => 'シャッフル再生',
        'playlist_click'         => 'プレイリストクリック',
        'index'                  => '番号',
        'song_name'              => '曲名',
        'no_lyrics'              => '歌詞なし',
        'loading_lyrics'         => '歌詞読み込み中...',
        'autoplay_blocked'       => '自動再生がブロックされました',
        'cache_cleared'               => '設定をクリアしました',
        'open_custom_playlist'        => 'カスタムプレイリストを開く',
        'reset_default_playlist'      => 'デフォルトプレイリストを復元',
        'reset_default_error'         => 'リセットエラーが発生しました',
        'reset_default_failed'        => 'リセットに失敗しました',
        'playlist_load_failed'        => 'プレイリストの読み込み失敗',
        'playlist_load_failed_message'=> 'プレイリスト読み込みに失敗しました',
        'hour_announcement'      => '時報、現在の時間は',
        'hour_exact'             => '時ちょうど',
        'weekDays' =>  ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'],
        'labels' => [
            'year' => '年',
            'month' => '月',
            'day' => '日',
            'week' => ''
        ],
        'zodiacs' => ['申','酉','戌','亥','子','丑','寅','卯','辰','巳','午','未'],
        'heavenlyStems' => ['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'],
        'earthlyBranches' => ['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'],
        'months' => ['1','2','3','4','5','6','7','8','9','10','11','12'],
        'days' => ['1日','2日','3日','4日','5日','6日','7日','8日','9日','10日',
                   '11日','12日','13日','14日','15日','16日','17日','18日','19日','20日',
                   '21日','22日','23日','24日','25日','26日','27日','28日','29日','30日'],
        'leap_prefix' => '閏',
        'year_suffix' => '年',
        'month_suffix' => '月',
        'day_suffix' => '',
        'periods' => ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'],
        'default_period' => '時',
        'initial' => '初',  
        'middle' => '正',   
        'final' =>'末',  
        'clear_confirm' =>'設定をリセットしますか？', 
        'back_to_first' => 'プレイリストの先頭に戻りました',
        'font_default' => '丸ゴシック体に変更',
        'font_fredoka' => 'デフォルトフォントに戻す',
        'font_mono'   => '手書き風フォントに変更',
        'font_noto'     => '漢字書体に変更',
        'font_dm_serif'     => 'DM Serif Display フォントに切り替えました',
        'error_loading_time' => '時刻表示エラー',
        'switch_to_light_mode' => 'ライトモードへ',
        'switch_to_dark_mode' => 'ダークモードへ',
        'current_mode_dark' => '現在のモード：ダーク',
        'current_mode_light' => '現在のモード：ライト',
        'fetching_version' => 'バージョン確認中...',
        'latest_version' => '最新バージョン',
        'unable_to_fetch_version' => '最新バージョン取得失敗',
        'request_failed' => 'リクエスト失敗、後ほど再試行してください',
        'pip_not_supported' => 'ピクチャーインピクチャー非対応',
        'pip_operation_failed' => 'ピクチャーインピクチャー操作失敗',
        'exit_picture_in_picture' => 'ピクチャーインピクチャー終了',
        'picture_in_picture' => 'ピクチャーインピクチャー',
        'hide_playlist' => 'リスト非表示',
        'show_playlist' => 'リスト表示',
        'enter_fullscreen' => '全画面開始',
        'exit_fullscreen' => '全画面終了',
        'confirm_update_php' => 'PHP設定を更新しますか？',
        'select_files_to_delete' => '削除するファイルを選択してください！',
        'confirm_batch_delete' => '%d個のファイルを削除しますか？',
        'unable_to_fetch_current_version' => '現在のバージョン確認中...',
        'current_version' => '現在のバージョン',
        'copy_command'     => 'コマンドをコピー',
        'command_copied'   => 'コマンドをコピーしました！',
        "updateModalLabel" => "更新ステータス",
        "updateDescription" => "更新プロセスを開始します",
        "waitingMessage" => "処理開始待機中...",
        "update_plugin" => "プラグイン更新",
        "installation_complete" => "インストール完了！",
        'confirm_title'             => '操作確認',
        'confirm_delete_file'   => 'ファイル「%s」を削除しますか？',
        'delete_success'      => '削除成功：%s',
        'delete_failure'      => '削除失敗：%s',
        'upload_error_type_not_supported' => '非対応フォーマット：%s',
        'upload_error_move_failed'        => 'アップロード失敗：%s',
        'confirm_clear_background' => '背景をクリアしますか？',
        'background_cleared'      => '背景をクリアしました！',
        'createShareLink' => 'シェアリンクを作成',
        'closeButton' => '閉じる',
        'expireTimeLabel' => '有効期限',
        'expire1Hour' => '1 時間',
        'expire1Day' => '1 日',
        'expire7Days' => '7 日',
        'expire30Days' => '30 日',
        'maxDownloadsLabel' => '最大ダウンロード回数',
        'max1Download' => '1 回',
        'max5Downloads' => '5 回',
        'max10Downloads' => '10 回',
        'maxUnlimited' => '無制限',
        'shareLinkLabel' => 'シェアリンク',
        'copyLinkButton' => 'リンクをコピー',
        'closeButtonFooter' => '閉じる',
        'generateLinkButton' => 'リンクを生成',
        'fileNotSelected' => 'ファイルが選択されていません',
        'httpError' => 'HTTP エラー',
        'linkGenerated' => '✅ シェアリンクが生成されました',
        'operationFailed' => '❌ 操作失敗',
        'generateLinkFirst' => '先にシェアリンクを生成してください',
        'linkCopied' => '📋 リンクがコピーされました',
        'copyFailed' => '❌ コピー失敗',
        'cleanExpiredButton' => '期限切れを削除',
        'deleteAllButton' => 'すべて削除',
        'cleanSuccess' => '✅ クリーン完了, %s 件が削除されました',
        'deleteSuccess' => '✅ すべての共有記録を削除しました, %s 個のファイルが削除されました',
        'confirmDeleteAll' => '⚠️ すべての共有記録を削除してもよろしいですか？',
        'operationFailed' => '❌ 操作に失敗しました',
        'ip_info' => 'IP詳細情報',
        'ip_support' => 'IPサポート',
        'ip_address' => 'IPアドレス',
        'location' => '地域',
        'isp' => 'プロバイダ',
        'asn' => 'ASN',
        'timezone' => 'タイムゾーン',
        'latitude_longitude' => '座標',
        'latency_info' => 'レイテンシ情報',
        'mute_on' => 'オーディオがミュートされました',
        'mute_off' => 'オーディオのミュートが解除されました',
        'volume_change' => '音量が {vol}% に調整されました',
        'speed_change' => '再生速度が {rate} 倍に変更されました',
        'invalid_city_non_chinese' => '中国語の文字を含まない都市名を入力してください。',
        'invalid_city_uppercase' => '都市名は大文字の英字で始める必要があります。',
        'city_saved' => '保存された都市: {city}',
        'city_saved_speak' => '保存された都市: {city}、最新の天気情報を取得しています...',
        'invalid_city' => '有効な都市名を入力してください。',
        'set_city' => '都市を設定',
        'input_label' => '都市名',
        'input_placeholder' => '例: 北京',
        'floating_lyrics_enabled' => 'フローティング歌詞が有効になりました',
        'floating_lyrics_disabled' => 'フローティング歌詞が無効になりました',
        'weather_label'     => '天気',
        'temperature_label' => '気温',
        'feels_like_label'  => '体感',
        'humidity_label'    => '湿度',
        'pressure_label'    => '気圧',
        'wind_label'        => '風速',
        'sunrise_label'     => '日の出',
        'sunset_label'      => '日の入り',
        'fit_contain'    => '標準比率',
        'fit_fill'       => '引き伸ばし',
        'fit_none'       => '元のサイズ',
        'fit_scale-down' => '自動調整',
        'fit_cover'      => 'トリミング',
        'current_fit_mode'    => '現在のモード',
        'selected_info' => '%dファイル選択（%s MB）'
    ],

    'vi' => [
        'select_language'        => 'Chọn ngôn ngữ',
        'simplified_chinese'     => 'Tiếng Trung Giản thể',
        'traditional_chinese'    => 'Tiếng Trung Phồn thể',
        'english'                => 'Tiếng Anh',
        'korean'                 => 'Tiếng Hàn',
        'vietnamese'             => 'Tiếng Việt',
        'thailand'               => 'Tiếng Thái',
        'japanese'               => 'Tiếng Nhật',
        'russian'                => 'Tiếng Nga',
        'germany'                => 'Tiếng Đức',
        'france'                 => 'Tiếng Pháp',
        'arabic'                 => 'Tiếng Ả Rập',
        'spanish'                => 'Tiếng Tây Ban Nha',
        'bangladesh'             => 'Tiếng Bangladesh',
        'close'                  => 'Đóng',
        'save'                   => 'Lưu',
        'theme_download'         => 'Tải chủ đề',
        'select_all'             => 'Chọn tất cả',
        'batch_delete'           => 'Xóa hàng loạt',
        'batch_delete_success'   => '✅ Xóa hàng loạt thành công',
        'batch_delete_failed'    => '❌ Xóa hàng loạt thất bại',
        'confirm_delete'         => 'Xác nhận xóa?',
        'total'                  => 'Tổng:',
        'free'                   => 'Còn lại:',
        'hover_to_preview'       => 'Nhấp để kích hoạt xem trước khi di chuột',
        'spectra_config'         => 'Quản lý cấu hình Spectra',
        'current_mode'           => 'Chế độ hiện tại: Đang tải...',
        'toggle_mode'            => 'Chuyển chế độ',
        'check_update'           => 'Kiểm tra cập nhật',
        'batch_upload'           => 'Chọn tệp để tải lên hàng loạt',
        'add_to_playlist'        => 'Chọn để thêm vào danh sách phát',
        'clear_background'       => 'Xóa nền',
        'clear_background_label' => 'Xóa nền',
        'file_list'              => 'Danh sách tệp',
        'component_bg_color'     => 'Chọn màu nền thành phần',
        'page_bg_color'          => 'Chọn màu nền trang',
        'toggle_font'            => 'Thay đổi phông chữ',
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
        'toggle_fullscreen'      => 'Chuyển toàn màn hình',
        'supported_formats'      => 'Định dạng hỗ trợ: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'Kéo thả tệp vào đây',
        'or'                     => 'hoặc',
        'select_files'           => 'Chọn tệp',
        'unlock_php_upload_limit'=> 'Mở khóa giới hạn tải lên PHP',
        'upload'                 => 'Tải lên',
        'cancel'                 => 'Hủy',
        'rename_file'            => 'Đổi tên tệp',
        'new_filename'           => 'Tên mới',
        'invalid_filename_chars' => 'Tên tệp không được chứa: \\/:*?"<>|',
        'confirm'                => 'Xác nhận',
        'media_player'           => 'Trình phát đa phương tiện',
        'playlist'               => 'Danh sách phát',
        'clear_list'             => 'Xóa danh sách',
        'toggle_list'            => 'Ẩn danh sách',
        'picture_in_picture'     => 'Hình trong hình',
        'fullscreen'             => 'Toàn màn hình',
        'music_player'           => 'Trình phát nhạc',
        'play_pause'             => 'Phát/Tạm dừng',
        'previous_track'         => 'Bài trước',
        'next_track'             => 'Bài tiếp theo',
        'repeat_mode'            => 'Phát lặp lại',
        'toggle_floating_lyrics' => 'Lời bài hát nổi',
        'clear_config'           => 'Xóa cấu hình',
        'custom_playlist'        => 'Danh sách phát tùy chỉnh',
        'volume'                 => 'Âm lượng',
        'update_playlist'        => 'Cập nhật danh sách phát',
        'playlist_url'           => 'Đường dẫn danh sách phát',
        'reset_default'          => 'Khôi phục mặc định',
        'toggle_lyrics'          => 'Tắt lời bài hát',
        'fetching_version'       => 'Đang kiểm tra phiên bản...',
        'download_local'         => 'Tải về máy',
        'change_language'        => 'Thay đổi ngôn ngữ',
        'pause_playing'          => 'Tạm dừng phát',
        'start_playing'          => 'Bắt đầu phát',
        'manual_switch'          => 'Chuyển thủ công',
        'auto_switch'            => 'Tự động chuyển sang',
        'switch_to'              => 'Chuyển sang',
        'auto_play'              => 'Tự động phát',
        'lyrics_load_failed'     => 'Tải lời bài hát thất bại',
        'order_play'             => 'Phát tuần tự',
        'single_loop'            => 'Lặp lại bài hát',
        'shuffle_play'           => 'Phát ngẫu nhiên',
        'playlist_click'         => 'Nhấp vào danh sách phát',
        'index'                  => 'Thứ tự',
        'song_name'              => 'Tên bài hát',
        'no_lyrics'              => 'Không có lời bài hát',
        'loading_lyrics'         => 'Đang tải lời bài hát...',
        'autoplay_blocked'       => 'Tự động phát bị chặn',
        'cache_cleared'               => 'Đã xóa cấu hình',
        'open_custom_playlist'        => 'Mở danh sách phát tùy chỉnh',
        'reset_default_playlist'      => 'Đã khôi phục đường dẫn mặc định',
        'reset_default_error'         => 'Lỗi khi khôi phục mặc định',
        'reset_default_failed'        => 'Khôi phục mặc định thất bại',
        'playlist_load_failed'        => 'Tải danh sách phát thất bại',
        'playlist_load_failed_message'=> 'Tải danh sách phát thất bại',
        'hour_announcement'      => 'Báo giờ, hiện tại là',  
        'hour_exact'             => 'giờ đúng',
        'weekDays' => ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'],
        'labels' => [
            'year' => 'Năm',
            'month' => 'Tháng',
            'day' => 'Ngày',
            'week' => ''
        ],
        'zodiacs' => ['Khỉ','Gà','Chó','Lợn','Chuột','Trâu','Hổ','Thỏ','Rồng','Rắn','Ngựa','Dê'],
        'heavenlyStems' => ['Giáp','Ất','Bính','Đinh','Mậu','Kỷ','Canh','Tân','Nhâm','Quý'],
        'earthlyBranches' => ['Tí','Sửu','Dần','Mão','Thìn','Tỵ','Ngọ','Mùi','Thân','Dậu','Tuất','Hợi'],
        'months' => ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'],
        'days' => ['Ngày 1','Ngày 2','Ngày 3','Ngày 4','Ngày 5','Ngày 6','Ngày 7','Ngày 8','Ngày 9','Ngày 10',
                   'Ngày 11','Ngày 12','Ngày 13','Ngày 14','Ngày 15','Ngày 16','Ngày 17','Ngày 18','Ngày 19','Ngày 20',
                   'Ngày 21','Ngày 22','Ngày 23','Ngày 24','Ngày 25','Ngày 26','Ngày 27','Ngày 28','Ngày 29','Ngày 30'],
        'leap_prefix' => 'Nhuận',
        'year_suffix' => ' Năm',
        'month_suffix' => '',
        'day_suffix' => '',
        'periods' => ['Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi'],
        'default_period' => ' Giờ',
        'initial' => 'đầu',  
        'middle' => 'giữa',   
        'final' =>'cuối',  
        'clear_confirm' =>'Xác nhận xóa cấu hình hiện tại?', 
        'back_to_first' => 'Đã quay về bài đầu tiên',
        'font_default' => 'Đã chuyển sang font tròn',
        'font_fredoka' => 'Đã chuyển về font mặc định',
        'font_mono'   => 'Đã chuyển sang font viết tay',
        'font_noto'     => 'Đã chuyển sang font chữ Hán',
        'font_dm_serif'     => 'Đã chuyển sang font DM Serif Display',
        'error_loading_time' => 'Lỗi hiển thị thời gian',
        'switch_to_light_mode' => 'Chuyển sang chế độ sáng',
        'switch_to_dark_mode' => 'Chuyển sang chế độ tối',
        'current_mode_dark' => 'Chế độ hiện tại: Tối',
        'current_mode_light' => 'Chế độ hiện tại: Sáng',
        'fetching_version' => 'Đang kiểm tra phiên bản...',
        'latest_version' => 'Phiên bản mới nhất',
        'unable_to_fetch_version' => 'Không thể kiểm tra phiên bản mới',
        'request_failed' => 'Yêu cầu thất bại, vui lòng thử lại',
        'pip_not_supported' => 'Không hỗ trợ hình trong hình',
        'pip_operation_failed' => 'Thao tác hình trong hình thất bại',
        'exit_picture_in_picture' => 'Thoát hình trong hình',
        'picture_in_picture' => 'Hình trong hình',
        'hide_playlist' => 'Ẩn danh sách',
        'show_playlist' => 'Hiện danh sách',
        'enter_fullscreen' => 'Vào toàn màn hình',
        'exit_fullscreen' => 'Thoát toàn màn hình',
        'confirm_update_php' => 'Xác nhận cập nhật cấu hình PHP?',
        'select_files_to_delete' => 'Vui lòng chọn tệp cần xóa!',
        'confirm_batch_delete' => 'Xác nhận xóa %d tệp?',
        'unable_to_fetch_current_version' => 'Đang kiểm tra phiên bản hiện tại...',
        'current_version' => 'Phiên bản hiện tại',
        'copy_command'     => 'Sao chép lệnh',
        'command_copied'   => 'Đã sao chép lệnh!',
        "updateModalLabel" => "Trạng thái cập nhật",
        "updateDescription" => "Quá trình cập nhật đang bắt đầu.",
        "waitingMessage" => "Đang chờ bắt đầu...",
        "update_plugin" => "Cập nhật plugin",
        "installation_complete" => "Cài đặt hoàn tất!",
        'confirm_title'             => 'Xác nhận thao tác',
        'confirm_delete_file'   => 'Xác nhận xóa tệp %s?',
        'delete_success'      => 'Xóa thành công: %s',
        'delete_failure'      => 'Xóa thất bại: %s',
        'upload_error_type_not_supported' => 'Không hỗ trợ định dạng: %s',
        'upload_error_move_failed'        => 'Tải lên thất bại: %s',
        'confirm_clear_background' => 'Xác nhận xóa nền?',
        'background_cleared'      => 'Đã xóa nền!',
        'createShareLink' => 'Tạo liên kết chia sẻ',
        'closeButton' => 'Đóng',
        'expireTimeLabel' => 'Thời gian hết hạn',
        'expire1Hour' => '1 giờ',
        'expire1Day' => '1 ngày',
        'expire7Days' => '7 ngày',
        'expire30Days' => '30 ngày',
        'maxDownloadsLabel' => 'Số lượt tải tối đa',
        'max1Download' => '1 lần',
        'max5Downloads' => '5 lần',
        'max10Downloads' => '10 lần',
        'maxUnlimited' => 'Không giới hạn',
        'shareLinkLabel' => 'Liên kết chia sẻ',
        'copyLinkButton' => 'Sao chép liên kết',
        'closeButtonFooter' => 'Đóng',
        'generateLinkButton' => 'Tạo liên kết',
        'fileNotSelected' => 'Chưa chọn tệp',
        'httpError' => 'Lỗi HTTP',
        'linkGenerated' => '✅ Đã tạo liên kết chia sẻ',
        'operationFailed' => '❌ Thao tác thất bại',
        'generateLinkFirst' => 'Vui lòng tạo liên kết chia sẻ trước',
        'linkCopied' => '📋 Liên kết đã được sao chép',
        'copyFailed' => '❌ Sao chép thất bại',
        'cleanExpiredButton' => 'Dọn hết hạn',
        'deleteAllButton' => 'Xóa tất cả',
        'cleanSuccess' => '✅ Dọn dẹp hoàn tất, %s mục đã bị xóa',
        'deleteSuccess' => '✅ Tất cả liên kết đã bị xóa, %s tệp đã bị xóa',
        'confirmDeleteAll' => '⚠️ Bạn có chắc muốn xóa TẤT CẢ các liên kết chia sẻ không?',
        'operationFailed' => '❌ Thao tác thất bại',
        'ip_info' => 'Thông tin IP',
        'ip_support' => 'Hỗ trợ IP',
        'ip_address' => 'Địa chỉ IP',
        'location' => 'Khu vực',
        'isp' => 'Nhà cung cấp',
        'asn' => 'ASN',
        'timezone' => 'Múi giờ',
        'latitude_longitude' => 'Tọa độ',
        'latency_info' => 'Thông tin độ trễ',
        'mute_on' => 'Âm thanh đã được tắt',
        'mute_off' => 'Âm thanh đã được bật lại',
        'volume_change' => 'Âm lượng đã điều chỉnh thành {vol}%',
        'speed_change' => 'Tốc độ phát đã chuyển sang {rate} lần',
        'invalid_city_non_chinese' => 'Vui lòng nhập tên thành phố không chứa ký tự tiếng Trung.',
        'invalid_city_uppercase' => 'Tên thành phố phải bắt đầu bằng chữ cái in hoa.',
        'city_saved' => 'Đã lưu thành phố: {city}',
        'city_saved_speak' => 'Đã lưu thành phố {city}, đang lấy thông tin thời tiết mới nhất...',
        'invalid_city' => 'Vui lòng nhập tên thành phố hợp lệ.',
        'set_city' => 'Đặt Thành Phố',
        'input_label' => 'Tên Thành Phố',
        'input_placeholder' => 'ví dụ: Beijing',
        'floating_lyrics_enabled' => 'Đã bật lời bài hát nổi',
        'floating_lyrics_disabled' => 'Đã tắt lời bài hát nổi',
        'weather_label'     => 'Thời tiết',
        'temperature_label' => 'Nhiệt độ',
        'feels_like_label'  => 'Cảm giác như',
        'humidity_label'    => 'Độ ẩm',
        'pressure_label'    => 'Áp suất',
        'wind_label'        => 'Tốc độ gió',
        'sunrise_label'     => 'Bình minh',
        'sunset_label'      => 'Hoàng hôn',
        'current_fit_mode'    => 'Chế độ hiện tại',
        'fit_contain'    => 'Giữ tỷ lệ',
        'fit_fill'       => 'Kéo giãn',
        'fit_none'       => 'Kích thước gốc',
        'fit_scale-down' => 'Tự động thu nhỏ',
        'fit_cover'      => 'Cắt vừa',
        'selected_info' => 'Đã chọn %d tệp (%s MB)'
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
        'bangladesh'             => 'เบงกาลี',
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
        'supported_formats'      => 'รูปแบบที่รองรับ: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
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
        'error_loading_time' => 'แสดงเวลาไม่ถูกต้อง',
        'switch_to_light_mode' => 'เปลี่ยนเป็นโหมดสว่าง',
        'switch_to_dark_mode' => 'เปลี่ยนเป็นโหมดมืด',
        'current_mode_dark' => 'โหมดปัจจุบัน: โหมดมืด',
        'current_mode_light' => 'โหมดปัจจุบัน: โหมดสว่าง',
        'fetching_version' => 'กำลังดึงข้อมูลเวอร์ชัน...',
        'latest_version' => 'เวอร์ชันล่าสุด',
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
        'clear_confirm' => 'คุณแน่ใจหรือว่าต้องการล้างการตั้งค่า?',
        'back_to_first' => 'กลับไปที่เพลงแรกในรายการเพลง',
        'font_default' => 'เปลี่ยนเป็นแบบอักษรโค้งมนแล้ว',
        'font_fredoka' => 'เปลี่ยนเป็นแบบอักษรเริ่มต้นแล้ว',
        'font_mono'    => 'เปลี่ยนเป็นแบบอักษรลายมือสนุก ๆ แล้ว',
        'font_noto'    => 'เปลี่ยนเป็นแบบอักษรมีเชิงภาษาจีนแล้ว',
        'font_dm_serif'     => 'เปลี่ยนเป็นฟอนต์ DM Serif Display',
        'batch_delete_success' => '✅ การลบเป็นกลุ่มสำเร็จ',
        'batch_delete_failed' => '❌ การลบเป็นกลุ่มล้มเหลว',
        'confirm_delete' => 'คุณแน่ใจหรือไม่ว่าต้องการลบ?',
        'unable_to_fetch_current_version' => 'กำลังดึงข้อมูลเวอร์ชันปัจจุบัน...',
        'current_version' => 'เวอร์ชันปัจจุบัน',
        'copy_command'     => 'คัดลอกคำสั่ง',
        'command_copied'   => 'คัดลอกคำสั่งไปยังคลิปบอร์ดแล้ว!',
        "updateModalLabel" => "สถานะการอัปเดต",
        "updateDescription" => "กระบวนการอัปเดตกำลังจะเริ่มต้น...",
        "waitingMessage" => "รอให้การดำเนินการเริ่มต้น...",
        "update_plugin" => "อัปเดตปลั๊กอิน",
        "installation_complete" => "การติดตั้งเสร็จสิ้น!",
        'confirm_title'         => 'ยืนยันการดำเนินการ',
        'confirm_delete_file'   => 'คุณแน่ใจหรือไม่ที่จะลบไฟล์ %s?',
        'delete_success'      => 'ลบสำเร็จ: %s',
        'delete_failure'      => 'ลบไม่สำเร็จ: %s',
        'upload_error_type_not_supported' => 'ประเภทไฟล์ที่ไม่รองรับ: %s',
        'upload_error_move_failed'        => 'การอัปโหลดไฟล์ล้มเหลว: %s',
        'confirm_clear_background' => 'แน่ใจหรือไม่ว่าต้องการลบพื้นหลัง?',
        'background_cleared'      => 'ลบพื้นหลังแล้ว!',
        'createShareLink' => 'สร้างลิงค์การแชร์',
        'closeButton' => 'ปิด',
        'expireTimeLabel' => 'เวลาหมดอายุ',
        'expire1Hour' => '1 ชั่วโมง',
        'expire1Day' => '1 วัน',
        'expire7Days' => '7 วัน',
        'expire30Days' => '30 วัน',
        'maxDownloadsLabel' => 'จำนวนการดาวน์โหลดสูงสุด',
        'max1Download' => '1 ครั้ง',
        'max5Downloads' => '5 ครั้ง',
        'max10Downloads' => '10 ครั้ง',
        'maxUnlimited' => 'ไม่จำกัด',
        'shareLinkLabel' => 'ลิงค์การแชร์',
        'copyLinkButton' => 'คัดลอกลิงค์',
        'closeButtonFooter' => 'ปิด',
        'generateLinkButton' => 'สร้างลิงค์',
        'fileNotSelected' => 'ไม่เลือกไฟล์',
        'httpError' => 'ข้อผิดพลาด HTTP',
        'linkGenerated' => '✅ สร้างลิงค์การแชร์แล้ว',
        'operationFailed' => '❌ การดำเนินการล้มเหลว',
        'generateLinkFirst' => 'โปรดสร้างลิงค์การแชร์ก่อน',
        'linkCopied' => '📋 ลิงค์ถูกคัดลอก',
        'copyFailed' => '❌ การคัดลอกล้มเหลว',
        'cleanExpiredButton' => 'ล้างที่หมดอายุ',
        'deleteAllButton' => 'ลบทั้งหมด',
        'cleanSuccess' => '✅ ล้างสำเร็จ, %s รายการถูกลบ',
        'deleteSuccess' => '✅ ลบประวัติการแชร์ทั้งหมดแล้ว, %s ไฟล์ถูกลบ',
        'confirmDeleteAll' => '⚠️ คุณแน่ใจหรือไม่ว่าต้องการลบประวัติการแชร์ทั้งหมด?',
        'operationFailed' => '❌ ล้มเหลวในการดำเนินการ',
        'ip_info' => 'รายละเอียด IP',
        'ip_support' => 'การสนับสนุน IP',
        'ip_address' => 'ที่อยู่ IP',
        'location' => 'ที่ตั้ง',
        'isp' => 'ผู้ให้บริการ',
        'asn' => 'ASN',
        'timezone' => 'เขตเวลา',
        'latitude_longitude' => 'พิกัด',
        'latency_info' => 'ข้อมูลความหน่วง',
        'mute_on' => 'เสียงถูกปิด',
        'mute_off' => 'เสียงถูกเปิด',
        'volume_change' => 'ปรับระดับเสียงเป็น {vol}%',
        'speed_change' => 'เปลี่ยนความเร็วการเล่นเป็น {rate} เท่า',
        'invalid_city_non_chinese' => 'กรุณาใส่ชื่อเมืองที่ไม่มีอักษรจีน',
        'invalid_city_uppercase' => 'ชื่อเมืองต้องขึ้นต้นด้วยตัวอักษรพิมพ์ใหญ่',
        'city_saved' => 'เมืองถูกบันทึกแล้ว: {city}',
        'city_saved_speak' => 'เมืองถูกบันทึกเป็น {city} กำลังดึงข้อมูลสภาพอากาศล่าสุด...',
        'invalid_city' => 'กรุณาใส่ชื่อเมืองที่ถูกต้อง',
        'set_city' => 'ตั้งค่าชื่อเมือง',
        'input_label' => 'ชื่อเมือง',
        'input_placeholder' => 'ตัวอย่าง: Beijing',
        'floating_lyrics_enabled' => 'เปิดใช้งานเนื้อเพลงลอย',
        'floating_lyrics_disabled' => 'ปิดใช้งานเนื้อเพลงลอย',
        'weather_label'     => 'สภาพอากาศ',
        'temperature_label' => 'อุณหภูมิ',
        'feels_like_label'  => 'รู้สึกเหมือน',
        'humidity_label'    => 'ความชื้น',
        'pressure_label'    => 'ความกดอากาศ',
        'wind_label'        => 'ความเร็วลม',
        'sunrise_label'     => 'พระอาทิตย์ขึ้น',
        'sunset_label'      => 'พระอาทิตย์ตก',
        'current_fit_mode'    => 'โหมดปัจจุบัน',
        'fit_contain'    => 'อัตราส่วนปกติ',
        'fit_fill'       => 'เติมเต็ม',
        'fit_none'       => 'ขนาดดั้งเดิม',
        'fit_scale-down' => 'ปรับอัตโนมัติ',
        'fit_cover'      => 'ครอบตัด',
        'selected_info' => 'เลือกไฟล์แล้ว %d ไฟล์ รวมทั้งหมด %s MB'
    ],

    'ru' => [
        'select_language'        => 'Выберите язык',
        'simplified_chinese'     => 'Упрощенный китайский',
        'traditional_chinese'    => 'Традиционный китайский',
        'english'                => 'Английский',
        'korean'                 => 'Корейский',
        'vietnamese'             => 'Вьетнамский',
        'thailand'               => 'Тайский',
        'japanese'               => 'Японский',
        'russian'                => 'Русский',
        'germany'                => 'Немецкий',
        'france'                 => 'Французский',
        'arabic'                 => 'Арабский',
        'spanish'                => 'Испанский',
        'bangladesh'             => 'Бенгальский',
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
        'supported_formats'      => 'Поддерживаемые форматы: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
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
        'error_loading_time' => 'Ошибка отображения времени',
        'switch_to_light_mode' => 'Переключиться на светлый режим',
        'switch_to_dark_mode' => 'Переключиться на темный режим',
        'current_mode_dark' => 'Текущий режим: темный',
        'current_mode_light' => 'Текущий режим: светлый',
        'fetching_version' => 'Получение информации о версии...',
        'latest_version' => 'Последняя версия',
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
        'clear_confirm' => 'Вы уверены, что хотите очистить конфигурацию?',
        'back_to_first' => 'Вернулся к первой песне в плейлисте',
        'font_default' => 'Переключено на округлый шрифт',
        'font_fredoka' => 'Переключено на шрифт по умолчанию',
        'font_mono'    => 'Переключено на забавный рукописный шрифт',
        'font_noto'    => 'Переключено на китайский рубленый шрифт',
        'font_dm_serif'     => 'Переключено на шрифт DM Serif Display',
        'batch_delete_success' => '✅ Успешное массовое удаление',
        'batch_delete_failed' => '❌ Ошибка массового удаления',
        'confirm_delete' => 'Вы уверены, что хотите удалить?',
        'unable_to_fetch_current_version' => 'Получение информации о текущей версии...',
        'current_version' => 'Текущая версия',
        'copy_command'     => 'Скопировать команду',
        'command_copied'   => 'Команда скопирована в буфер обмена!',
        "updateModalLabel" => "Статус обновления",
        "updateDescription" => "Процесс обновления вот-вот начнется.",
        "waitingMessage" => "Ожидание начала операции...",
        "update_plugin" => "Обновить плагин",
        "installation_complete" => "Установка завершена!",
        'confirm_title'         => 'Подтвердите действие',
        'confirm_delete_file'   => 'Вы уверены, что хотите удалить файл %s?',
        'confirm_delete_file' => 'Вы уверены, что хотите удалить файл %s?',
        'delete_success'      => 'Успешно удалено: %s',
        'delete_failure'      => 'Не удалось удалить: %s',
        'upload_error_type_not_supported' => 'Неподдерживаемый тип файла: %s',
        'upload_error_move_failed'        => 'Ошибка загрузки файла: %s',
        'confirm_clear_background' => 'Вы уверены, что хотите очистить фон?',
        'background_cleared'      => 'Фон очищен!',
        'createShareLink' => 'Создать ссылку для обмена',
        'closeButton' => 'Закрыть',
        'expireTimeLabel' => 'Время истечения',
        'expire1Hour' => '1 час',
        'expire1Day' => '1 день',
        'expire7Days' => '7 дней',
        'expire30Days' => '30 дней',
        'maxDownloadsLabel' => 'Максимальное количество загрузок',
        'max1Download' => '1 раз',
        'max5Downloads' => '5 раз',
        'max10Downloads' => '10 раз',
        'maxUnlimited' => 'Неограничено',
        'shareLinkLabel' => 'Ссылка для обмена',
        'copyLinkButton' => 'Копировать ссылку',
        'closeButtonFooter' => 'Закрыть',
        'generateLinkButton' => 'Создать ссылку',
        'fileNotSelected' => 'Файл не выбран',
        'httpError' => 'Ошибка HTTP',
        'linkGenerated' => '✅ Ссылка для обмена создана',
        'operationFailed' => '❌ Операция не удалась',
        'generateLinkFirst' => 'Сначала создайте ссылку для обмена',
        'linkCopied' => '📋 Ссылка скопирована',
        'copyFailed' => '❌ Ошибка копирования',
        'cleanExpiredButton' => 'Очистить просроченное',
        'deleteAllButton' => 'Удалить всё',
        'cleanSuccess' => '✅ Очистка завершена, %s предмет(ов) удалено',
        'deleteSuccess' => '✅ Все записи о совместном доступе удалены, %s файл(ов) удалено',
        'confirmDeleteAll' => '⚠️ Вы уверены, что хотите удалить ВСЕ записи о совместном доступе?',
        'operationFailed' => '❌ Не удалось выполнить операцию',
        'ip_info' => 'IP информация',
        'ip_support' => 'IP поддержка',
        'ip_address' => 'IP адрес',
        'location' => 'Локация',
        'isp' => 'Провайдер',
        'asn' => 'ASN',
        'timezone' => 'Часовой пояс',
        'latitude_longitude' => 'Координаты',
        'latency_info' => 'Задержка',
        'mute_on' => 'Аудио отключено',
        'mute_off' => 'Аудио включено',
        'volume_change' => 'Громкость изменена на {vol}%',
        'speed_change' => 'Скорость воспроизведения изменена на {rate}x',
        'invalid_city_non_chinese' => 'Введите название города без китайских символов.',
        'invalid_city_uppercase' => 'Название города должно начинаться с заглавной буквы.',
        'city_saved' => 'Город сохранен: {city}',
        'city_saved_speak' => 'Город сохранен: {city}, получение последней информации о погоде...',
        'invalid_city' => 'Введите допустимое название города.',
        'set_city' => 'Установить город',
        'input_label' => 'Название города',
        'input_placeholder' => 'например: Пекин',
        'floating_lyrics_enabled' => 'Плавающие тексты включены',
        'floating_lyrics_disabled' => 'Плавающие тексты отключены',
        'weather_label'     => 'Погода',
        'temperature_label' => 'Температура',
        'feels_like_label'  => 'Ощущается как',
        'humidity_label'    => 'Влажность',
        'pressure_label'    => 'Давление',
        'wind_label'        => 'Скорость ветра',
        'sunrise_label'     => 'Восход',
        'sunset_label'      => 'Закат',
        'current_fit_mode'    => 'Текущий режим',
        'fit_contain'    => 'Обычное соотношение',
        'fit_fill'       => 'Растянуть',
        'fit_none'       => 'Оригинальный размер',
        'fit_scale-down' => 'Уменьшить при необходимости',
        'fit_cover'      => 'Обрезать по размеру',
        'selected_info' => 'Выбрано %d файлов, всего %s MB'
    ],

    'ar' => [
        'select_language'        => 'اختر اللغة',
        'simplified_chinese'     => 'الصينية المبسطة',
        'traditional_chinese'    => 'الصينية التقليدية',
        'english'                => 'الإنجليزية',
        'korean'                 => 'الكورية',
        'vietnamese'             => 'الفيتنامية',
        'thailand'               => 'التايلاندية',
        'japanese'               => 'اليابانية',
        'russian'                => 'الروسية',
        'germany'                => 'الألمانية',
        'france'                 => 'الفرنسية',
        'arabic'                 => 'العربية',
        'spanish'                => 'الإسبانية',
        'bangladesh'             => 'البنغالية',
        'close'                  => 'إغلاق',
        'save'                   => 'حفظ',
        'theme_download'         => 'تنزيل السمة',
        'select_all'             => 'تحديد الكل',
        'batch_delete'           => 'حذف جماعي للملفات المحددة',
        'batch_delete_success'   => '✅ الحذف الجماعي ناجح',
        'batch_delete_failed'    => '❌ فشل الحذف الجماعي',
        'confirm_delete'         => 'تأكيد الحذف؟',
        'total'                  => 'الإجمالي:',
        'free'                   => 'المتبقي:',
        'hover_to_preview'       => 'انقر لتفعيل معاينة التحويم',
        'spectra_config'         => 'إدارة إعدادات Spectra',
        'current_mode'           => 'الوضع الحالي: جاري التحميل...',
        'toggle_mode'            => 'تبديل الوضع',
        'check_update'           => 'التحقق من التحديثات',
        'batch_upload'           => 'اختر ملفات للرفع الجماعي',
        'add_to_playlist'        => 'حدد لإضافة إلى قائمة التشغيل',
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
        'set_background'         => 'تعيين خلفية',
        'preview'                => 'معاينة',
        'toggle_fullscreen'      => 'تبديل ملء الشاشة',
        'supported_formats'      => 'التنسيقات المدعومة: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'أسقط الملفات هنا',
        'or'                     => 'أو',
        'select_files'           => 'اختر ملفات',
        'unlock_php_upload_limit'=> 'رفع قيود الرفع في PHP',
        'upload'                 => 'رفع',
        'cancel'                 => 'إلغاء',
        'rename_file'            => 'إعادة تسمية الملف',
        'new_filename'           => 'اسم الملف الجديد',
        'invalid_filename_chars' => 'لا يمكن أن يحتوي اسم الملف على: \\/:*?"<>|',
        'confirm'                => 'تأكيد',
        'media_player'           => 'مشغل الوسائط',
        'playlist'               => 'قائمة التشغيل',
        'clear_list'             => 'مسح القائمة',
        'toggle_list'            => 'إخفاء القائمة',
        'picture_in_picture'     => 'صورة داخل صورة',
        'fullscreen'             => 'ملء الشاشة',
        'music_player'           => 'مشغل الموسيقى',
        'play_pause'             => 'تشغيل/إيقاف مؤقت',
        'previous_track'         => 'المقطع السابق',
        'next_track'             => 'المقطع التالي',
        'repeat_mode'            => 'تكرار التشغيل',
        'toggle_floating_lyrics' => 'كلمات عائمة',
        'clear_config'           => 'مسح الإعدادات',
        'custom_playlist'        => 'قائمة تشغيل مخصصة',
        'volume'                 => 'الصوت',
        'update_playlist'        => 'تحديث قائمة التشغيل',
        'playlist_url'           => 'رابط قائمة التشغيل',
        'reset_default'          => 'إعادة الضبط',
        'toggle_lyrics'          => 'إخفاء الكلمات',
        'fetching_version'       => 'جاري التحقق من الإصدار...',
        'download_local'         => 'تنزيل محلي',
        'change_language'        => 'تغيير اللغة',
        'pause_playing'          => 'إيقاف التشغيل',
        'start_playing'          => 'بدء التشغيل',
        'manual_switch'          => 'تبديل يدوي',
        'auto_switch'            => 'تبديل تلقائي إلى',
        'switch_to'              => 'التبديل إلى',
        'auto_play'              => 'تشغيل تلقائي',
        'lyrics_load_failed'     => 'فشل تحميل الكلمات',
        'order_play'             => 'تشغيل بالتسلسل',
        'single_loop'            => 'تكرار المقطع',
        'shuffle_play'           => 'تشغيل عشوائي',
        'playlist_click'         => 'نقر قائمة التشغيل',
        'index'                  => 'الفهرس',
        'song_name'              => 'اسم الأغنية',
        'no_lyrics'              => 'لا توجد كلمات',
        'loading_lyrics'         => 'جاري تحميل الكلمات...',
        'autoplay_blocked'       => 'تم حظر التشغيل التلقائي',
        'cache_cleared'               => 'تم مسح الإعدادات',
        'open_custom_playlist'        => 'فتح قائمة تشغيل مخصصة',
        'reset_default_playlist'      => 'تمت إعادة تعيين رابط قائمة التشغيل الافتراضي',
        'reset_default_error'         => 'خطأ أثناء إعادة التعيين',
        'reset_default_failed'        => 'فشل إعادة التعيين',
        'playlist_load_failed'        => 'فشل تحميل قائمة التشغيل',
        'playlist_load_failed_message'=> 'فشل تحميل قائمة التشغيل',
        'hour_announcement'      => 'النشرة الزمنية، التوقيت المحلي هو',  
        'hour_exact'             => 'الساعة بالضبط',
        'weekDays' => ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
        'labels' => [
            'year' => 'سنة',
            'month' => 'شهر',
            'day' => 'يوم',
            'week' => 'أسبوع'
        ],
        'zodiacs' => ['القرد','الديك','الكلب','الخنزير','الفأر','الثور','النمر','الأرنب','التنين','الأفعى','الحصان','الخروف'],
        'heavenlyStems' => ['جيا','يي','بينغ','دينغ','وو','جي','قينغ','شين','رين','غوي'],
        'earthlyBranches' => ['زي','تشو','يين','ماو','تشين','سي','وو','وي','شين','يو','شو','هاي'],
        'months' => ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'],
        'days' => ['الأول','الثاني','الثالث','الرابع','الخامس','السادس','السابع','الثامن','التاسع','العاشر',
                   'الحادي عشر','الثاني عشر','الثالث عشر','الرابع عشر','الخامس عشر','السادس عشر','السابع عشر','الثامن عشر','التاسع عشر','العشرون',
                   'الحادي والعشرون','الثاني والعشرون','الثالث والعشرون','الرابع والعشرون','الخامس والعشرون','السادس والعشرون','السابع والعشرون','الثامن والعشرون','التاسع والعشرون','الثلاثون'],
        'clear_confirm' =>'هل تريد مسح الإعدادات الحالية؟', 
        'back_to_first' => 'العودة إلى أول مقطع في القائمة',
        'font_default' => 'تم التبديل إلى الخط المدور',
        'font_fredoka' => 'تم التبديل إلى الخط الافتراضي',
        'font_mono'   => 'تم التبديل إلى الخط اليدوي',
        'font_noto'     => 'تم التبديل إلى الخط الصيني',
        'font_dm_serif'     => 'تم التبديل إلى خط DM Serif Display',
        'error_loading_time' => 'خطأ في عرض الوقت',
        'switch_to_light_mode' => 'الوضع الفاتح',
        'switch_to_dark_mode' => 'الوضع الداكن',
        'current_mode_dark' => 'الوضع الحالي: داكن',
        'current_mode_light' => 'الوضع الحالي: فاتح',
        'fetching_version' => 'جاري التحقق من الإصدار...',
        'latest_version' => 'آخر إصدار',
        'unable_to_fetch_version' => 'تعذر الحصول على آخر إصدار',
        'request_failed' => 'فشل الطلب، حاول لاحقًا',
        'pip_not_supported' => 'لا يدعم التشغيل بصورة داخل صورة',
        'pip_operation_failed' => 'فشل عملية الصورة داخل الصورة',
        'exit_picture_in_picture' => 'خروج من صورة داخل صورة',
        'picture_in_picture' => 'صورة داخل صورة',
        'hide_playlist' => 'إخفاء القائمة',
        'show_playlist' => 'إظهار القائمة',
        'enter_fullscreen' => 'ملء الشاشة',
        'exit_fullscreen' => 'خروج من ملء الشاشة',
        'confirm_update_php' => 'هل تريد تحديث إعدادات PHP؟',
        'select_files_to_delete' => 'الرجاء اختيار الملفات للحذف أولاً!',
        'confirm_batch_delete' => 'هل تريد حذف %d ملفات؟',
        'unable_to_fetch_current_version' => 'جاري التحقق من الإصدار الحالي...',
        'current_version' => 'الإصدار الحالي',
        'copy_command'     => 'نسخ الأمر',
        'command_copied'   => 'تم نسخ الأمر!',
        "updateModalLabel" => "حالة التحديث",
        "updateDescription" => "جاري بدء عملية التحديث.",
        "waitingMessage" => "بانتظار بدء العملية...",
        "update_plugin" => "تحديث الملحق",
        "installation_complete" => "اكتمل التثبيت!",
        'confirm_title'             => 'تأكيد العملية',
        'confirm_delete_file'   => 'هل تريد حذف الملف %s؟',
        'delete_success'      => 'تم الحذف: %s',
        'delete_failure'      => 'فشل الحذف: %s',
        'upload_error_type_not_supported' => 'نوع ملف غير مدعوم: %s',
        'upload_error_move_failed'        => 'فشل الرفع: %s',
        'confirm_clear_background' => 'هل تريد مسح الخلفية؟',
        'background_cleared'      => 'تم مسح الخلفية!',
        'createShareLink' => 'إنشاء رابط المشاركة',
        'closeButton' => 'إغلاق',
        'expireTimeLabel' => 'وقت الانتهاء',
        'expire1Hour' => '1 ساعة',
        'expire1Day' => '1 يوم',
        'expire7Days' => '7 أيام',
        'expire30Days' => '30 يوم',
        'maxDownloadsLabel' => 'الحد الأقصى للتنزيلات',
        'max1Download' => '1 مرة',
        'max5Downloads' => '5 مرات',
        'max10Downloads' => '10 مرات',
        'maxUnlimited' => 'غير محدود',
        'shareLinkLabel' => 'رابط المشاركة',
        'copyLinkButton' => 'نسخ الرابط',
        'closeButtonFooter' => 'إغلاق',
        'generateLinkButton' => 'إنشاء الرابط',
        'fileNotSelected' => 'لم يتم اختيار الملف',
        'httpError' => 'خطأ HTTP',
        'linkGenerated' => '✅ تم إنشاء رابط المشاركة',
        'operationFailed' => '❌ فشل العملية',
        'generateLinkFirst' => 'يرجى إنشاء رابط المشاركة أولاً',
        'linkCopied' => '📋 تم نسخ الرابط',
        'copyFailed' => '❌ فشل النسخ',
        'cleanExpiredButton' => 'تنظيف المنتهية',
        'deleteAllButton' => 'حذف الكل',
        'cleanSuccess' => '✅ تم التنظيف بنجاح، تم حذف %s عنصرًا منتهي الصلاحية',
        'deleteSuccess' => '✅ تم حذف جميع سجلات المشاركة، تم حذف %s ملفًا',
        'confirmDeleteAll' => '⚠️ هل أنت متأكد أنك تريد حذف جميع سجلات المشاركة؟',
        'operationFailed' => '❌ فشل في العملية',
        'ip_info' => 'تفاصيل IP',
        'ip_support' => 'دعم IP',
        'ip_address' => 'عنوان IP',
        'location' => 'الموقع',
        'isp' => 'مزود الخدمة',
        'asn' => 'ASN',
        'timezone' => 'المنطقة الزمنية',
        'latitude_longitude' => 'إحداثيات',
        'latency_info' => 'معلومات التأخر',
        'mute_on' => 'تم كتم الصوت',
        'mute_off' => 'تم إلغاء كتم الصوت',
        'volume_change' => 'تم تعديل مستوى الصوت إلى {vol}%',
        'speed_change' => 'تم تغيير سرعة التشغيل إلى {rate}x',
        'invalid_city_non_chinese' => 'يرجى إدخال اسم مدينة بدون أحرف صينية.',
        'invalid_city_uppercase' => 'يجب أن يبدأ اسم المدينة بحرف كبير باللغة الإنجليزية.',
        'city_saved' => 'تم حفظ المدينة: {city}',
        'city_saved_speak' => 'تم حفظ المدينة: {city}، جارٍ جلب أحدث معلومات الطقس...',
        'invalid_city' => 'يرجى إدخال اسم مدينة صالح.',
        'set_city' => 'تعيين المدينة',
        'input_label' => 'اسم المدينة',
        'floating_lyrics_enabled' => 'تم تفعيل كلمات الأغاني العائمة',
        'floating_lyrics_disabled' => 'تم تعطيل كلمات الأغاني العائمة',
        'input_placeholder' => 'على سبيل المثال: بكين',   
        'weather_label'     => 'الطقس',
        'temperature_label' => 'درجة الحرارة',
        'feels_like_label'  => 'يشعر كأنّه',
        'humidity_label'    => 'الرطوبة',
        'pressure_label'    => 'الضغط',
        'wind_label'        => 'سرعة الرياح',
        'sunrise_label'     => 'شروق الشمس',
        'sunset_label'      => 'غروب الشمس',    
        'current_fit_mode'    => 'الوضع الحالي',
        'fit_contain'    => 'نسبة عادية',
        'fit_fill'       => 'تمديد لملء',
        'fit_none'       => 'الحجم الأصلي',
        'fit_scale-down' => 'تكييف ذكي',
        'fit_cover'      => 'اقتصاص افتراضي',
        'selected_info' => 'تم اختيار %d ملفات (%s ميجابايت)'
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
        'bangladesh'             => 'Bengalí',
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
        'supported_formats'      => 'Formatos compatibles: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
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
        'error_loading_time' => 'Error al mostrar la hora',
        'switch_to_light_mode' => 'Cambiar al modo claro',
        'switch_to_dark_mode' => 'Cambiar al modo oscuro',
        'current_mode_dark' => 'Modo actual: Modo oscuro',
        'current_mode_light' => 'Modo actual: Modo claro',
        'fetching_version' => 'Obteniendo información de la versión...',
        'latest_version' => 'Última versión',
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
        'clear_confirm' => '¿Estás seguro de que deseas borrar la configuración?',
        'back_to_first' => 'Regresado a la primera canción en la lista de reproducción',
        'font_default' => 'Cambiado a fuente redondeada',
        'font_fredoka' => 'Cambiado a fuente predeterminada',
        'font_mono'    => 'Cambiado a fuente manuscrita divertida',
        'font_noto'    => 'Cambiado a fuente serif en chino',
        'font_dm_serif'     => 'Cambiado a la fuente DM Serif Display',
        'batch_delete_success' => '✅ Eliminación masiva exitosa',
        'batch_delete_failed' => '❌ Fallo en la eliminación masiva',
        'confirm_delete' => '¿Estás seguro de que deseas eliminar?',
        'unable_to_fetch_current_version' => 'Obteniendo la versión actual...',
        'current_version' => 'Versión actual',
        'copy_command'     => 'Copiar comando',
        'command_copied'   => '¡Comando copiado al portapapeles!',
        "updateModalLabel" => "Estado de actualización",
        "updateDescription" => "El proceso de actualización está a punto de comenzar.",
        "waitingMessage" => "Esperando que comience la operación...",
        "update_plugin" => "Actualizar complemento",
        "installation_complete" => "¡Instalación completa!",
        'confirm_title'         => 'Confirmar acción',
        'confirm_delete_file'   => '¿Estás seguro de que deseas eliminar el archivo %s?',
        'delete_success'      => 'Eliminado con éxito: %s',
        'delete_failure'      => 'Error al eliminar: %s',
        'upload_error_type_not_supported' => 'Tipo de archivo no soportado: %s',
        'upload_error_move_failed'        => 'Error de carga: %s',
        'confirm_clear_background' => '¿Estás seguro de que quieres borrar el fondo?',
        'background_cleared'      => '¡Fondo borrado!',
        'createShareLink' => 'Crear enlace de compartición',
        'closeButton' => 'Cerrar',
        'expireTimeLabel' => 'Tiempo de expiración',
        'expire1Hour' => '1 Hora',
        'expire1Day' => '1 Día',
        'expire7Days' => '7 Días',
        'expire30Days' => '30 Días',
        'maxDownloadsLabel' => 'Descargas máximas',
        'max1Download' => '1 vez',
        'max5Downloads' => '5 veces',
        'max10Downloads' => '10 veces',
        'maxUnlimited' => 'Ilimitado',
        'shareLinkLabel' => 'Enlace para compartir',
        'copyLinkButton' => 'Copiar enlace',
        'closeButtonFooter' => 'Cerrar',
        'generateLinkButton' => 'Generar enlace',
        'fileNotSelected' => 'Archivo no seleccionado',
        'httpError' => 'Error HTTP',
        'linkGenerated' => '✅ Enlace de compartición generado',
        'operationFailed' => '❌ Operación fallida',
        'generateLinkFirst' => 'Por favor, genera el enlace de compartición primero',
        'linkCopied' => '📋 Enlace copiado',
        'copyFailed' => '❌ Error al copiar',
        'cleanExpiredButton' => 'Limpiar caducados',
        'deleteAllButton' => 'Eliminar todo',
        'cleanSuccess' => '✅ Limpieza completada, %s elemento(s) caducado(s) eliminado(s)',
        'deleteSuccess' => '✅ Todos los registros compartidos han sido eliminados, %s archivo(s) eliminado(s)',
        'confirmDeleteAll' => '⚠️ ¿Está seguro de que desea eliminar TODOS los registros compartidos?',
        'operationFailed' => '❌ Operación fallida',
        'ip_info' => 'Detalles de IP',
        'ip_support' => 'Soporte IP',
        'ip_address' => 'Dirección IP',
        'location' => 'Ubicación',
        'isp' => 'Proveedor',
        'asn' => 'ASN',
        'timezone' => 'Zona horaria',
        'latitude_longitude' => 'Coordenadas',
        'latency_info' => 'Informe de latencia',
        'mute_on' => 'Audio silenciado',
        'mute_off' => 'Audio reactivado',
        'volume_change' => 'Volumen ajustado al {vol}%',
        'speed_change' => 'Velocidad de reproducción cambiada a {rate}x',
        'invalid_city_non_chinese' => 'Por favor, introduzca un nombre de ciudad sin caracteres chinos.',
        'invalid_city_uppercase' => 'El nombre de la ciudad debe comenzar con una letra mayúscula.',
        'city_saved' => 'Ciudad guardada como: {city}',
        'city_saved_speak' => 'Ciudad guardada como: {city}, obteniendo la información meteorológica más reciente...',
        'invalid_city' => 'Por favor, introduzca un nombre de ciudad válido.',
        'set_city' => 'Establecer Ciudad',
        'input_label' => 'Nombre de la ciudad',
        'input_placeholder' => 'por ejemplo: Beijing',
        'floating_lyrics_enabled' => 'Letras flotantes habilitadas',
        'floating_lyrics_disabled' => 'Letras flotantes deshabilitadas',
        'weather_label'     => 'Clima',
        'temperature_label' => 'Temperatura',
        'feels_like_label'  => 'Sensación térmica',
        'humidity_label'    => 'Humedad',
        'pressure_label'    => 'Presión',
        'wind_label'        => 'Velocidad del viento',
        'sunrise_label'     => 'Amanecer',
        'sunset_label'      => 'Atardecer',
        'current_fit_mode'    => 'Modo actual',
        'fit_contain'    => 'Proporción normal',
        'fit_fill'       => 'Estirar',
        'fit_none'       => 'Tamaño original',
        'fit_scale-down' => 'Escalado inteligente',
        'fit_cover'      => 'Recorte predeterminado',
        'selected_info' => 'Seleccionados %d archivos, en total %s MB'
    ],

    'de' => [
        'select_language'        => 'Sprache auswählen',
        'simplified_chinese'     => 'Vereinfachtes Chinesisch',
        'traditional_chinese'    => 'Traditionelles Chinesisch',
        'english'                => 'Englisch',
        'korean'                 => 'Koreanisch',
        'vietnamese'             => 'Vietnamesisch',
        'thailand'               => 'Thailändisch',
        'japanese'               => 'Japanisch',
        'russian'                => 'Russisch',
        'germany'                => 'Deutsch',
        'france'                 => 'Französisch',
        'arabic'                 => 'Arabisch',
        'spanish'                => 'Spanisch',
        'bangladesh'             => 'Bengalisch',
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
        'supported_formats'      => 'Unterstützte Formate: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
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
        'error_loading_time' => 'Fehler beim Anzeigen der Zeit',
        'switch_to_light_mode' => 'Zum hellen Modus wechseln',
        'switch_to_dark_mode' => 'Zum dunklen Modus wechseln',
        'current_mode_dark' => 'Aktueller Modus: Dunkelmodus',
        'current_mode_light' => 'Aktueller Modus: Hellmodus',
        'fetching_version' => 'Version wird abgerufen...',
        'latest_version' => 'Neueste Version',
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
        'clear_confirm' => 'Sind Sie sicher, dass Sie die Konfiguration löschen möchten?', 
        'back_to_first' => 'Zur ersten Wiedergabeliste zurückgekehrt',
        'font_default' => 'Auf runde Schriftart umgestellt',
        'font_fredoka' => 'Auf Standardschriftart umgestellt',
        'font_mono'    => 'Auf lustige Handschrift umgestellt',
        'font_noto'    => 'Auf chinesische Serifenschrift umgestellt',
        'font_dm_serif'     => 'Auf DM Serif Display-Schriftart umgeschaltet',
        'batch_delete_success' => '✅ Stapel-Löschung erfolgreich',
        'batch_delete_failed' => '❌ Stapel-Löschung fehlgeschlagen',
        'confirm_delete' => 'Bist du sicher, dass du löschen möchtest?',
        'unable_to_fetch_current_version' => 'Aktuelle Version wird abgerufen...',
        'current_version' => 'Aktuelle Version',
        'copy_command'     => 'Befehl kopieren',
        'command_copied'   => 'Befehl wurde in die Zwischenablage kopiert!',
        "updateModalLabel" => "Aktualisierungsstatus",
        "updateDescription" => "Der Aktualisierungsprozess wird gleich beginnen.",
        "waitingMessage" => "Warten auf den Beginn der Operation...",
        "update_plugin" => "Plugin aktualisieren",
        "installation_complete" => "Installation abgeschlossen!",
        'confirm_title'         => 'Bestätigen Sie die Aktion',
        'confirm_delete_file'   => 'Möchten Sie die Datei %s wirklich löschen?',
        'delete_success'      => 'Erfolgreich gelöscht: %s',
        'delete_failure'      => 'Löschen fehlgeschlagen: %s',
        'upload_error_type_not_supported' => 'Nicht unterstützter Dateityp: %s',
        'upload_error_move_failed'        => 'Upload fehlgeschlagen: %s',
        'confirm_clear_background' => 'Möchten Sie den Hintergrund wirklich löschen?',
        'background_cleared'      => 'Hintergrund wurde gelöscht!',
        'createShareLink' => 'Freigabelink erstellen',
        'closeButton' => 'Schließen',
        'expireTimeLabel' => 'Ablaufzeit',
        'expire1Hour' => '1 Stunde',
        'expire1Day' => '1 Tag',
        'expire7Days' => '7 Tage',
        'expire30Days' => '30 Tage',
        'maxDownloadsLabel' => 'Maximale Downloads',
        'max1Download' => '1 Mal',
        'max5Downloads' => '5 Mal',
        'max10Downloads' => '10 Mal',
        'maxUnlimited' => 'Unbegrenzt',
        'shareLinkLabel' => 'Freigabelink',
        'copyLinkButton' => 'Link kopieren',
        'closeButtonFooter' => 'Schließen',
        'generateLinkButton' => 'Link erstellen',
        'fileNotSelected' => 'Datei nicht ausgewählt',
        'httpError' => 'HTTP-Fehler',
        'linkGenerated' => '✅ Freigabelink generiert',
        'operationFailed' => '❌ Vorgang fehlgeschlagen',
        'generateLinkFirst' => 'Bitte generieren Sie zuerst den Freigabelink',
        'linkCopied' => '📋 Link kopiert',
        'copyFailed' => '❌ Kopieren fehlgeschlagen',
        'cleanExpiredButton' => 'Abgelaufene löschen',
        'deleteAllButton' => 'Alle löschen',
        'cleanSuccess' => '✅ Reinigung abgeschlossen, %s Elemente wurden entfernt',
        'deleteSuccess' => '✅ Alle Freigabelinks wurden gelöscht, %s Datei(en) wurden entfernt',
        'confirmDeleteAll' => '⚠️ Möchten Sie wirklich ALLE Freigabelinks löschen?',
        'operationFailed' => '❌ Vorgang fehlgeschlagen',
        'ip_info' => 'IP-Informationen',
        'ip_support' => 'IP-Support',
        'ip_address' => 'IP-Adresse',
        'location' => 'Standort',
        'isp' => 'Anbieter',
        'asn' => 'ASN',
        'timezone' => 'Zeitzone',
        'latitude_longitude' => 'Koordinaten',
        'latency_info' => 'Latenzinformationen',
        'mute_on' => 'Audio stummgeschaltet',
        'mute_off' => 'Audio-Stummschaltung aufgehoben',
        'volume_change' => 'Lautstärke auf {vol}% eingestellt',
        'speed_change' => 'Wiedergabegeschwindigkeit auf {rate}x geändert',
        'invalid_city_non_chinese' => 'Bitte geben Sie einen Städtenamen ohne chinesische Zeichen ein.',
        'invalid_city_uppercase' => 'Der Städtename muss mit einem Großbuchstaben beginnen.',
        'city_saved' => 'Stadt gespeichert als: {city}',
        'city_saved_speak' => 'Stadt gespeichert als: {city}, die neuesten Wetterinformationen werden abgerufen...',
        'invalid_city' => 'Bitte geben Sie einen gültigen Städtenamen ein.',
        'set_city' => 'Stadt festlegen',
        'input_label' => 'Stadtname',
        'input_placeholder' => 'z.B.: Beijing',
        'floating_lyrics_enabled' => 'Schwebende Liedtexte aktiviert',
        'floating_lyrics_disabled' => 'Schwebende Liedtexte deaktiviert',
        'weather_label'     => 'Wetter',
        'temperature_label' => 'Temperatur',
        'feels_like_label'  => 'Gefühlt',
        'humidity_label'    => 'Luftfeuchtigkeit',
        'pressure_label'    => 'Luftdruck',
        'wind_label'        => 'Windgeschwindigkeit',
        'sunrise_label'     => 'Sonnenaufgang',
        'sunset_label'      => 'Sonnenuntergang',
        'current_fit_mode'    => 'Aktueller Modus',
        'fit_contain'    => 'Seitenverhältnis beibehalten',
        'fit_fill'       => 'Ausfüllen',
        'fit_none'       => 'Originalgröße',
        'fit_scale-down' => 'Skalieren falls nötig',
        'fit_cover'      => 'Zuschneiden',
        'selected_info' => '%d Dateien ausgewählt, insgesamt %s MB'
    ],

    'fr' => [
        'select_language'        => 'Choisir la langue',
        'simplified_chinese'     => 'Chinois simplifié',
        'traditional_chinese'    => 'Chinois traditionnel',
        'english'                => 'Anglais',
        'korean'                 => 'Coréen',
        'vietnamese'             => 'Vietnamien',
        'thailand'               => 'Thaï',
        'japanese'               => 'Japonais',
        'russian'                => 'Russe',
        'germany'                => 'Allemand',
        'france'                 => 'Français',
        'arabic'                 => 'Arabe',
        'spanish'                => 'Espagnol',
        'bangladesh'             => 'Bengali',
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
        'supported_formats'      => 'Formats pris en charge : [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
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
        'error_loading_time' => 'Erreur lors de l\'affichage de l\'heure',
        'switch_to_light_mode' => 'Passer au mode clair',
        'switch_to_dark_mode' => 'Passer au mode sombre',
        'current_mode_dark' => 'Mode actuel : Mode sombre',
        'current_mode_light' => 'Mode actuel : Mode clair',
        'fetching_version' => 'Récupération des informations de version...',
        'latest_version' => 'Dernière version',
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
        'clear_confirm' => 'Êtes-vous sûr de vouloir effacer la configuration ?',
        'back_to_first' => 'Retour à la première chanson de la liste de lecture',
        'font_default' => 'Police arrondie activée',
        'font_fredoka' => 'Police par défaut activée',
        'font_mono'    => 'Police manuscrite activée',
        'font_noto'    => 'Police avec empattement chinoise activée',
        'font_dm_serif'     => 'Passé à la police DM Serif Display',
        'batch_delete_success' => '✅ Suppression par lot réussie',
        'batch_delete_failed' => '❌ Échec de la suppression par lot',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer?',
        'unable_to_fetch_current_version' => 'Récupération de la version actuelle...',
        'current_version' => 'Version actuelle',
        'copy_command'     => 'Copier la commande',
        'command_copied'   => 'Commande copiée dans le presse-papiers !',
        "updateModalLabel" => "Statut de la mise à jour",
        "updateDescription" => "Le processus de mise à jour va bientôt commencer.",
        "waitingMessage" => "En attente du début de l'opération...",
        "update_plugin" => "Mettre à jour le plugin",
        "installation_complete" => "Installation terminée !",
        'confirm_title'         => 'Confirmer l\'action',
       'confirm_delete_file'   => 'Êtes-vous sûr de vouloir supprimer le fichier %s ?',
        'delete_success'      => 'Suppression réussie : %s',
        'delete_failure'      => 'Échec de la suppression : %s',
        'upload_error_type_not_supported' => 'Type de fichier non pris en charge : %s',
        'upload_error_move_failed'        => 'Échec du téléchargement : %s',
        'confirm_clear_background' => 'Voulez-vous vraiment effacer l\'arrière-plan?',
        'background_cleared'      => 'Arrière-plan effacé!',
        'createShareLink' => 'Créer un lien de partage',
        'closeButton' => 'Fermer',
        'expireTimeLabel' => 'Temps d\'expiration',
        'expire1Hour' => '1 Heure',
        'expire1Day' => '1 Jour',
        'expire7Days' => '7 Jours',
        'expire30Days' => '30 Jours',
        'maxDownloadsLabel' => 'Téléchargements maximum',
        'max1Download' => '1 fois',
        'max5Downloads' => '5 fois',
        'max10Downloads' => '10 fois',
        'maxUnlimited' => 'Illimité',
        'shareLinkLabel' => 'Lien de partage',
        'copyLinkButton' => 'Copier le lien',
        'closeButtonFooter' => 'Fermer',
        'generateLinkButton' => 'Générer le lien',
        'fileNotSelected' => 'Fichier non sélectionné',
        'httpError' => 'Erreur HTTP',
        'linkGenerated' => '✅ Lien de partage généré',
        'operationFailed' => '❌ Échec de l\'opération',
        'generateLinkFirst' => 'Veuillez d\'abord générer le lien de partage',
        'linkCopied' => '📋 Lien copié',
        'copyFailed' => '❌ Échec de la copie',
        'cleanExpiredButton' => 'Nettoyer expirés',
        'deleteAllButton' => 'Supprimer tout',
        'cleanSuccess' => '✅ Nettoyage terminé, %s élément(s) expiré(s) supprimé(s)',
        'deleteSuccess' => '✅ Tous les liens partagés ont été supprimés, %s fichier(s) supprimé(s)',
        'confirmDeleteAll' => '⚠️ Voulez-vous vraiment supprimer TOUS les enregistrements de partage ?',
        'operationFailed' => '❌ Échec de l\'opération',
        'ip_info' => 'Informations IP',
        'ip_support' => 'Support IP',
        'ip_address' => 'Adresse IP',
        'location' => 'Localisation',
        'isp' => 'Fournisseur',
        'asn' => 'ASN',
        'timezone' => 'Fuseau horaire',
        'latitude_longitude' => 'Coordonnées',
        'latency_info' => 'Informations de latence',
        'mute_on' => 'Audio coupé',
        'mute_off' => 'Audio réactivé',
        'volume_change' => 'Volume ajusté à {vol}%',
        'speed_change' => 'Vitesse de lecture changée à {rate}x',
        'invalid_city_non_chinese' => 'Veuillez entrer un nom de ville sans caractères chinois.',
        'invalid_city_uppercase' => 'Le nom de la ville doit commencer par une lettre majuscule.',
        'city_saved' => 'Ville enregistrée : {city}',
        'city_saved_speak' => 'Ville enregistrée : {city}, récupération des dernières informations météorologiques...',
        'invalid_city' => 'Veuillez entrer un nom de ville valide.',
        'set_city' => 'Définir la ville',
        'input_label' => 'Nom de la ville',
        'input_placeholder' => 'par exemple : Beijing',
        'floating_lyrics_enabled' => 'Paroles flottantes activées',
        'floating_lyrics_disabled' => 'Paroles flottantes désactivées',
        'weather_label'     => 'Météo',
        'temperature_label' => 'Température',
        'feels_like_label'  => 'Ressenti',
        'humidity_label'    => 'Humidité',
        'pressure_label'    => 'Pression',
        'wind_label'        => 'Vitesse du vent',
        'sunrise_label'     => 'Lever du soleil',
        'sunset_label'      => 'Coucher du soleil',
        'current_fit_mode'    => 'Mode actuel',
        'fit_contain'    => 'Proportions normales',
        'fit_fill'       => 'Remplir',
        'fit_none'       => 'Taille d’origine',
        'fit_scale-down' => 'Réduction automatique',
        'fit_cover'      => 'Rogner',
        'selected_info' => '%d fichiers sélectionnés, total de %s Mo'
    ],

    'en' => [
        'select_language'        => 'Select Language',
        'simplified_chinese'     => 'Simplified Chinese',
        'traditional_chinese'    => 'Traditional Chinese',
        'english'                => 'English',
        'korean'                 => 'Korean',
        'vietnamese'             => 'Vietnamese',
        'thailand'               => 'Thai',
        'japanese'               => 'Japanese',
        'russian'                => 'Russian',
        'germany'                => 'German',
        'france'                 => 'French',
        'arabic'                 => 'Arabic',
        'spanish'                => 'Spanish',
        'bangladesh'             => 'Bengali',
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
        'supported_formats'      => 'Supported formats: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
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
        'weekDays' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        'labels' => [
            'year' => '',
            'month' => '',
            'day' => '',
            'week' => ''
        ],
        'zodiacs' => ['Monkey','Rooster','Dog','Pig','Rat','Ox','Tiger','Rabbit','Dragon','Snake','Horse','Goat'],
        'heavenlyStems' => ['Jia','Yi','Bing','Ding','Wu','Ji','Geng','Xin','Ren','Gui'],
        'earthlyBranches' => ['Zi','Chou','Yin','Mao','Chen','Si','Wu','Wei','Shen','You','Xu','Hai'],
        'months' => ['1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th','11th','12th'],
        'days' => ['1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th',
                   '11th','12th','13th','14th','15th','16th','17th','18th','19th','20th',
                   '21st','22nd','23rd','24th','25th','26th','27th','28th','29th','30th'],
        'leap_prefix' => 'Leap ',
        'year_suffix' => ' Year',
        'month_suffix' => ' Month',
        'day_suffix' => '',
        'periods' => ['Zi', 'Chou', 'Yin', 'Mao', 'Chen', 'Si', 'Wu', 'Wei', 'Shen', 'You', 'Xu', 'Hai'],
        'default_period' => ' Time',
        'error_loading_time' => 'Error loading time',
        'switch_to_light_mode' => 'Switch to Light Mode',
        'switch_to_dark_mode' => 'Switch to Dark Mode',
        'current_mode_dark' => 'Current Mode: Dark Mode',
        'current_mode_light' => 'Current Mode: Light Mode',
        'fetching_version' => 'Fetching version info...',
        'latest_version' => 'Latest Version',
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
        'clear_confirm' => 'Are you sure you want to clear the config?',
        'back_to_first' => 'Returned to the first song in the playlist',
        'font_default' => 'Switched to rounded font',
        'font_fredoka' => 'Switched to default font',
        'font_mono'    => 'Switched to fun handwriting font',
        'font_noto'    => 'Switched to Chinese serif font',
        'font_dm_serif'     => 'Switched to DM Serif Display font',
        'batch_delete_success' => '✅ Batch delete successful',
        'batch_delete_failed' => '❌ Batch delete failed',
        'confirm_delete' => 'Are you sure you want to delete?',
        'unable_to_fetch_current_version' => 'Fetching current version...',
        'current_version' => 'Current Version',
        'copy_command'     => 'Copy Command',
        'command_copied'   => 'Command copied to clipboard!',
        "updateModalLabel" => "Update Status",
        "updateDescription" => "The update process is about to begin.",
        "waitingMessage" => "Waiting for the operation to start...",
        "update_plugin" => "Update Plugin",
        "installation_complete" => "Installation complete!",
        'confirm_title'         => 'Confirm Action',
        'confirm_delete_file'   => 'Are you sure you want to delete file %s?',
        'delete_success'      => 'Deleted successfully: %s',
        'delete_failure'      => 'Failed to delete: %s',
        'upload_error_type_not_supported' => 'Unsupported file type: %s',
        'upload_error_move_failed'        => 'Upload failed: %s',
        'confirm_clear_background' => 'Are you sure you want to clear the background?',
        'background_cleared'      => 'Background cleared!',
        'createShareLink' => 'Create Share Link',
        'closeButton' => 'Close',
        'expireTimeLabel' => 'Expiration Time',
        'expire1Hour' => '1 Hour',
        'expire1Day' => '1 Day',
        'expire7Days' => '7 Days',
        'expire30Days' => '30 Days',
        'maxDownloadsLabel' => 'Max Downloads',
        'max1Download' => '1 Time',
        'max5Downloads' => '5 Times',
        'max10Downloads' => '10 Times',
        'maxUnlimited' => 'Unlimited',
        'shareLinkLabel' => 'Share Link',
        'copyLinkButton' => 'Copy Link',
        'closeButtonFooter' => 'Close',
        'generateLinkButton' => 'Generate Link',
        'fileNotSelected' => 'File not selected',
        'httpError' => 'HTTP Error',
        'linkGenerated' => '✅ Share link generated',
        'operationFailed' => '❌ Operation failed',
        'generateLinkFirst' => 'Please generate the share link first',
        'linkCopied' => '📋 Link copied',
        'copyFailed' => '❌ Copy failed',
        'cleanExpiredButton' => 'Clean Expired',
        'deleteAllButton' => 'Delete All',
        'cleanSuccess' => '✅ Clean completed, %s expired item(s) removed',
        'deleteSuccess' => '✅ All share records deleted, %s file(s) removed',
        'confirmDeleteAll' => '⚠️ Are you sure you want to delete ALL share records?',
        'operationFailed' => '❌ Operation failed',
        'ip_info' => 'IP Details',
        'ip_support' => 'IP Support',
        'ip_address' => 'IP Address',
        'location' => 'Location',
        'isp' => 'ISP',
        'asn' => 'ASN',
        'timezone' => 'Timezone',
        'latitude_longitude' => 'Coordinates',
        'latency_info' => 'Latency Info',
        'mute_on' => 'Audio muted',
        'mute_off' => 'Audio unmuted',
        'volume_change' => 'Volume adjusted to {vol}%',
        'speed_change' => 'Playback speed changed to {rate}x',
        'invalid_city_non_chinese' => 'Please enter a city name without Chinese characters.',
        'invalid_city_uppercase' => 'The city name must start with an uppercase English letter.',
        'city_saved' => 'City saved as: {city}',
        'city_saved_speak' => 'City saved as {city}, fetching the latest weather information...',
        'invalid_city' => 'Please enter a valid city name.',
        'set_city' => 'Set City',
        'input_label' => 'City Name',
        'input_placeholder' => 'e.g., Beijing',
        'floating_lyrics_enabled' => 'Floating lyrics enabled',
        'floating_lyrics_disabled' => 'Floating lyrics disabled',
        'weather_label'     => 'Weather',
        'temperature_label' => 'Temperature',
        'feels_like_label'  => 'Feels like',
        'humidity_label'    => 'Humidity',
        'pressure_label'    => 'Pressure',
        'wind_label'        => 'Wind speed',
        'sunrise_label'     => 'Sunrise',
        'sunset_label'      => 'Sunset',
        'current_fit_mode'    => 'Current mode',
        'fit_contain'    => 'Contain',
        'fit_fill'       => 'Fill',
        'fit_none'       => 'Original size',
        'fit_scale-down' => 'Scale down',
        'fit_cover'      => 'Cover',
        'selected_info' => 'Selected %d files, total %s MB'
    ],
    'bn' => [
        'select_language'        => 'ভাষা নির্বাচন করুন',
        'simplified_chinese'     => 'সরলীকৃত চীনা',
        'traditional_chinese'    => 'প্রথাগত চীনা',
        'english'                => 'ইংরেজি',
        'korean'                 => 'কোরিয়ান',
        'vietnamese'             => 'ভিয়েতনামী',
        'thailand'               => 'থাই',
        'japanese'               => 'জাপানি',
        'russian'                => 'রাশিয়ান',
        'germany'                => 'জার্মান',
        'france'                 => 'ফরাসি',
        'arabic'                 => 'আরবি',
        'spanish'                => 'স্প্যানিশ',
        'bangladesh'             => 'বাংলা',
        'close'                  => 'বন্ধ',
        'save'                   => 'সংরক্ষণ',
        'theme_download'         => 'থিম ডাউনলোড',
        'select_all'             => 'সব নির্বাচন',
        'batch_delete'           => 'নির্বাচিত ফাইল একসাথে মুছুন',
        'batch_delete_success'   => '✅ একসাথে মুছুন সফল',
        'batch_delete_failed'    => '❌ একসাথে মুছুন ব্যর্থ',
        'confirm_delete'         => 'মুছে ফেলতে চান?',
        'total'                  => 'মোট:',
        'free'                   => 'অবশিষ্ট:',
        'hover_to_preview'       => 'প্লে করতে ক্লিক করুন',
        'spectra_config'         => 'Spectra কনফিগারেশন',
        'current_mode'           => 'বর্তমান মোড: লোড হচ্ছে...',
        'toggle_mode'            => 'মোড পরিবর্তন',
        'check_update'           => 'আপডেট চেক করুন',
        'batch_upload'           => 'একসাথে আপলোডের জন্য ফাইল নির্বাচন',
        'add_to_playlist'        => 'প্লেলিস্টে যোগ করতে চেক করুন',
        'clear_background'       => 'পটভূমি সাফ',
        'clear_background_label' => 'পটভূমি সাফ',
        'file_list'              => 'ফাইল তালিকা',
        'component_bg_color'     => 'কম্পোনেন্টের পটভূমি রং নির্বাচন',
        'page_bg_color'          => 'পৃষ্ঠার পটভূমি রং নির্বাচন',
        'toggle_font'            => 'ফন্ট পরিবর্তন',
        'filename'               => 'নাম:',
        'filesize'               => 'আকার:',
        'duration'               => 'সময়:',
        'resolution'             => 'রেজোলিউশন:',
        'bitrate'                => 'বিটরেট:',
        'type'                   => 'ধরণ:',
        'image'                  => 'ছবি',
        'video'                  => 'ভিডিও',
        'audio'                  => 'অডিও',
        'document'               => 'ডকুমেন্ট',
        'delete'                 => 'মুছুন',
        'rename'                 => 'নাম পরিবর্তন',
        'download'               => 'ডাউনলোড',
        'set_background'         => 'পটভূমি সেট করুন',
        'preview'                => 'প্রিভিউ',
        'toggle_fullscreen'      => 'ফুলস্ক্রিন পরিবর্তন',
        'supported_formats'      => 'সমর্থিত ফরম্যাট: [ jpg, jpeg, png, gif, webp, mp4, webm, mkv, mp3, wav, flac ]',
        'drop_files_here'        => 'ফাইল এখানে ড্রপ করুন',
        'or'                     => 'অথবা',
        'select_files'           => 'ফাইল নির্বাচন',
        'unlock_php_upload_limit'=> 'PHP আপলোড লিমিট আনলক',
        'upload'                 => 'আপলোড',
        'cancel'                 => 'বাতিল',
        'rename_file'            => 'নাম পরিবর্তন',
        'new_filename'           => 'নতুন ফাইলনাম',
        'invalid_filename_chars' => 'ফাইলনামে এই অক্ষর থাকতে পারবে না: \\/：*?"<>|',
        'confirm'                => 'নিশ্চিত',
        'media_player'           => 'মিডিয়া প্লেয়ার',
        'playlist'               => 'প্লেলিস্ট',
        'clear_list'             => 'তালিকা সাফ',
        'toggle_list'            => 'তালিকা লুকান',
        'picture_in_picture'     => 'পিকচার ইন পিকচার',
        'fullscreen'             => 'ফুলস্ক্রিন',
        'music_player'           => 'মিউজিক প্লেয়ার',
        'play_pause'             => 'প্লে/পজ',
        'previous_track'         => 'আগের ট্র্যাক',
        'next_track'             => 'পরের ট্র্যাক',
        'repeat_mode'            => 'পুনরাবৃত্তি মোড',
        'toggle_floating_lyrics' => 'ভাসমান গানের কথা',
        'clear_config'           => 'কনফিগারেশন সাফ',
        'custom_playlist'        => 'কাস্টম প্লেলিস্ট',
        'volume'                 => 'ভলিউম',
        'update_playlist'        => 'প্লেলিস্ট আপডেট',
        'playlist_url'           => 'প্লেলিস্ট URL',
        'reset_default'          => 'ডিফল্টে ফিরুন',
        'toggle_lyrics'          => 'গানের কথা বন্ধ',
        'fetching_version'       => 'সংস্করণ তথ্য পাওয়া হচ্ছে...',
        'download_local'         => 'লোকালে ডাউনলোড',
        'change_language'        => 'ভাষা পরিবর্তন',
        'pause_playing'          => 'প্লে থামান',
        'start_playing'          => 'প্লে শুরু',
        'manual_switch'          => 'ম্যানুয়াল পরিবর্তন',
        'auto_switch'            => 'স্বয়ংক্রিয়ভাবে পরিবর্তন',
        'switch_to'              => 'পরিবর্তন করুন',
        'auto_play'              => 'স্বয়ংক্রিয় প্লে',
        'lyrics_load_failed'     => 'গানের কথা লোড ব্যর্থ',
        'order_play'             => 'অনুক্রমিক প্লে',
        'single_loop'            => 'একক লুপ',
        'shuffle_play'           => 'এলোমেলো প্লে',
        'playlist_click'         => 'প্লেলিস্ট ক্লিক',
        'index'                  => 'সূচী',
        'song_name'              => 'গানের নাম',
        'no_lyrics'              => 'কোনো গানের কথা নেই',
        'loading_lyrics'         => 'গানের কথা লোড হচ্ছে...',
        'autoplay_blocked'       => 'স্বয়ংক্রিয় প্লে ব্লক করা হয়েছে',
        'cache_cleared'          => 'কনফিগারেশন সাফ হয়েছে',
        'open_custom_playlist'   => 'কাস্টম প্লেলিস্ট খুলুন',
        'reset_default_playlist' => 'ডিফল্ট প্লেলিস্ট লিঙ্ক ফিরিয়ে দেওয়া হয়েছে',
        'reset_default_error'    => 'ডিফল্ট লিঙ্ক ফিরিয়ে দেওয়ার সময় ত্রুটি',
        'reset_default_failed'   => 'ডিফল্ট লিঙ্ক ফিরিয়ে দেওয়া ব্যর্থ',
        'playlist_load_failed'   => 'প্লেলিস্ট লোড ব্যর্থ',
        'playlist_load_failed_message' => 'প্লেলিস্ট লোড করতে ব্যর্থ',
        'hour_announcement'      => 'ঘন্টা ঘোষণা, এখন বেইজিং সময়',
        'hour_exact'             => 'টা বাজে',
        'weekDays' => ['রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহস্পতি', 'শুক্র', 'শনি'],
        'labels' => [
            'year' => 'বছর',
            'month' => 'মাস',
            'day' => 'তারিখ',
            'week' => 'সপ্তাহ'
        ],
        'zodiacs' => ['বানর','মোরগ','কুকুর','শূকর','ইঁদুর','গরু','বাঘ','খরগোশ','ড্রাগন','সাপ','ঘোড়া','ছাগল'],
        'clear_confirm' =>'কনফিগারেশন সাফ করতে চান?', 
        'back_to_first' => 'প্লেলিস্টের প্রথম গানে ফিরে গেছে',
        'font_default' => 'গোলাকার ফন্টে পরিবর্তন করা হয়েছে',
        'font_fredoka' => 'ডিফল্ট ফন্টে পরিবর্তন করা হয়েছে',
        'font_mono'    => 'হাতের লেখা ফন্টে পরিবর্তন করা হয়েছে',
        'font_noto'    => 'চীনা সেরিফ ফন্টে পরিবর্তন করা হয়েছে',
        'font_dm_serif'     => 'DM Serif Display ফন্টে পরিবর্তিত হয়েছে',
        'error_loading_time' => 'সময় প্রদর্শনে ত্রুটি',
        'switch_to_light_mode' => 'হালকা মোডে পরিবর্তন',
        'switch_to_dark_mode' => 'অন্ধকার মোডে পরিবর্তন',
        'current_mode_dark' => 'বর্তমান মোড: অন্ধকার মোড',
        'current_mode_light' => 'বর্তমান মোড: হালকা মোড',
        'fetching_version' => 'সংস্করণ তথ্য পাওয়া হচ্ছে...',
        'latest_version' => 'সর্বশেষ সংস্করণ',
        'unable_to_fetch_version' => 'সর্বশেষ সংস্করণ তথ্য পাওয়া যায়নি',
        'request_failed' => 'অনুরোধ ব্যর্থ, পরে আবার চেষ্টা করুন',
        'pip_not_supported' => 'বর্তমান মিডিয়া পিকচার ইন পিকচার সমর্থন করে না',
        'pip_operation_failed' => 'পিকচার ইন পিকচার অপারেশন ব্যর্থ',
        'exit_picture_in_picture' => 'পিকচার ইন পিকচার থেকে বের হন',
        'picture_in_picture' => 'পিকচার ইন পিকচার',
        'hide_playlist' => 'তালিকা লুকান',
        'show_playlist' => 'তালিকা দেখান',
        'enter_fullscreen' => 'ফুলস্ক্রিনে যান',
        'exit_fullscreen' => 'ফুলস্ক্রিন থেকে বের হন',
        'confirm_update_php' => 'আপনি PHP কনফিগারেশন আপডেট করতে চান?',
        'select_files_to_delete' => 'দয়া করে প্রথমে মুছতে চাওয়া ফাইল নির্বাচন করুন!',
        'confirm_batch_delete' => '%d টি নির্বাচিত ফাইল মুছতে চান?',
        'unable_to_fetch_current_version' => 'বর্তমান সংস্করণ পাওয়া হচ্ছে...',
        'current_version' => 'বর্তমান সংস্করণ',
        'copy_command'     => 'কমান্ড কপি',
        'command_copied'   => 'কমান্ড ক্লিপবোর্ডে কপি হয়েছে!',
        "updateModalLabel" => "আপডেট অবস্থা",
        "updateDescription" => "আপডেট প্রক্রিয়া শুরু হতে চলেছে।",
        "waitingMessage" => "অপারেশন শুরু হওয়ার জন্য অপেক্ষা...",
        "update_plugin" => "প্লাগইন আপডেট",
        "installation_complete" => "ইনস্টলেশন সম্পূর্ণ!",
        'confirm_title'             => 'অপারেশন নিশ্চিত',
        'confirm_delete_file'   => '%s ফাইলটি মুছতে চান?',
        'delete_success'      => 'সফলভাবে মুছে ফেলা হয়েছে: %s',
        'delete_failure'      => 'মুছতে ব্যর্থ: %s',
        'upload_error_type_not_supported' => 'অসমর্থিত ফাইল টাইপ: %s',
        'upload_error_move_failed'        => 'ফাইল আপলোড ব্যর্থ: %s',
        'confirm_clear_background' => 'পটভূমি সাফ করতে চান?',
        'background_cleared'      => 'পটভূমি সাফ করা হয়েছে!',
        'fileNotSelected' => 'ফাইল নির্বাচন করা হয়নি',
        'httpError' => 'HTTP ত্রুটি',
        'linkGenerated' => '✅ শেয়ার লিঙ্ক তৈরি হয়েছে',
        'operationFailed' => '❌ অপারেশন ব্যর্থ',
        'generateLinkFirst' => 'দয়া করে আগে শেয়ার লিঙ্ক তৈরি করুন',
        'linkCopied' => '📋 লিঙ্ক কপি করা হয়েছে',
        'copyFailed' => '❌ কপি ব্যর্থ',
        'createShareLink' => 'শেয়ার লিঙ্ক তৈরি করুন',
        'closeButton' => 'বন্ধ করুন',
        'expireTimeLabel' => 'মেয়াদ শেষ হওয়ার সময়',
        'expire1Hour' => '1 ঘণ্টা',
        'expire1Day' => '1 দিন',
        'expire7Days' => '7 দিন',
        'expire30Days' => '30 দিন',
        'maxDownloadsLabel' => 'সর্বাধিক ডাউনলোড সংখ্যা',
        'max1Download' => '1 বার',
        'max5Downloads' => '5 বার',
        'max10Downloads' => '10 বার',
        'maxUnlimited' => 'অসীম',
        'shareLinkLabel' => 'শেয়ার লিঙ্ক',
        'copyLinkButton' => 'লিঙ্ক কপি করুন',
        'closeButtonFooter' => 'বন্ধ করুন',
        'generateLinkButton' => 'লিঙ্ক তৈরি করুন',
        'cleanExpiredButton' => 'মেয়াদোত্তীর্ণ পরিষ্কার করুন',
        'deleteAllButton' => 'সব মুছে ফেলুন',
        'cleanSuccess' => '✅ পরিষ্কার সম্পন্ন হয়েছে, %s আইটেম মুছে ফেলা হয়েছে',
        'deleteSuccess' => '✅ সব শেয়ার রেকর্ড মুছে ফেলা হয়েছে, %s ফাইল মুছে ফেলা হয়েছে',
        'confirmDeleteAll' => '⚠️ আপনি কি নিশ্চিত আপনি সব শেয়ার রেকর্ড মুছে ফেলতে চান?',
        'operationFailed' => '❌ অপারেশন ব্যর্থ হয়েছে',
        'ip_info' => 'আইপি বিবরণ',
        'ip_support' => 'আইপি সমর্থন',
        'ip_address' => 'আইপি ঠিকানা',
        'location' => 'অবস্থান',
        'isp' => 'সেবা প্রদানকারী',
        'asn' => 'ASN',
        'timezone' => 'সময় অঞ্চল',
        'latitude_longitude' => 'স্থানাঙ্ক',
        'latency_info' => 'বিলম্ব তথ্য',
        'mute_on' => 'অডিও নিস্তব্ধ করা হয়েছে',
        'mute_off' => 'অডিও মিউট বন্ধ হয়েছে',
        'volume_change' => 'ভলিউম {vol}% এ সমন্বয় করা হয়েছে',
        'speed_change' => 'প্লেব্যাক গতি {rate}x এ পরিবর্তন করা হয়েছে',
        'invalid_city_non_chinese' => 'চীনা অক্ষর ছাড়া একটি শহরের নাম লিখুন।',
        'invalid_city_uppercase' => 'শহরের নাম বড় হাতের অক্ষর দিয়ে শুরু করতে হবে।',
        'city_saved' => 'শহর সংরক্ষণ করা হয়েছে: {city}',
        'city_saved_speak' => 'শহর সংরক্ষণ করা হয়েছে {city}, সর্বশেষ আবহাওয়া তথ্য আনছে...',
        'invalid_city' => 'বৈধ শহরের নাম লিখুন।',
        'set_city' => 'শহর সেট করুন',
        'input_label' => 'শহরের নাম',
        'input_placeholder' => 'যেমন: বেইজিং',
        'floating_lyrics_enabled' => 'ভাসমান গানের কথা সক্রিয় করা হয়েছে',
        'floating_lyrics_disabled' => 'ভাসমান গানের কথা অক্ষম করা হয়েছে',
        'weather_label'     => 'আবহাওয়া',
        'temperature_label' => 'তাপমাত্রা',
        'feels_like_label'  => 'অনুভূত তাপমাত্রা',
        'humidity_label'    => 'আর্দ্রতা',
        'pressure_label'    => 'চাপ',
        'wind_label'        => 'বায়ুর গতি',
        'sunrise_label'     => 'সূর্যোদয়',
        'sunset_label'      => 'সূর্যাস্ত',
        'current_fit_mode'    => 'বর্তমান মোড',
        'fit_contain'    => 'স্বাভাবিক অনুপাত',
        'fit_fill'       => 'সম্পূর্ণ ভরাট',
        'fit_none'       => 'মূল আকার',
        'fit_scale-down' => 'স্মার্ট মানানসই',
        'fit_cover'      => 'ক্রপ মোড',
        'selected_info' => '%d টি ফাইল নির্বাচিত, মোট %s MB'
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
        'ko': '/luci-static/ipip/flags/kr.png',
        'ja': '/luci-static/ipip/flags/jp.png',
        'ru': '/luci-static/ipip/flags/ru.png',
        'ar': '/luci-static/ipip/flags/sa.png',
        'es': '/luci-static/ipip/flags/es.png',
        'de': '/luci-static/ipip/flags/de.png',
        'fr': '/luci-static/ipip/flags/fr.png',
        'th': '/luci-static/ipip/flags/th.png',
        'bn': '/luci-static/ipip/flags/bd.png',
        'vi': '/luci-static/ipip/flags/vn.png'
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

          const langLabelMap = {
              'zh': '语言已切换为简体中文',
              'hk': '語言已切換為繁體中文',
              'en': 'Language switched to English',
              'ko': '언어가 한국어로 변경되었습니다',
              'ja': '言語が日本語に変更されました',
              'ru': 'Язык переключен на русский',
              'ar': 'تم تغيير اللغة إلى العربية',
              'es': 'El idioma ha cambiado a español',
              'de': 'Sprache auf Deutsch umgestellt',
              'fr': 'Langue changée en français',
              'th': 'เปลี่ยนภาษาเป็นภาษาไทยแล้ว',
              'bn': 'ভাষা বাংলাতে পরিবর্তন করা হয়েছে',
              'vi': 'Đã chuyển ngôn ngữ sang tiếng Việt'
          };

          const message = langLabelMap[lang] || 'Language switched';

          if (typeof speakMessage === 'function') {
              speakMessage(message);
          }
          if (typeof showLogMessage === 'function') {
              showLogMessage(message);
          }
      });
}
</script>

<script>
document.addEventListener('keydown', function (event) {
    const target = event.target;
    const isTyping = target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable;

    if (isTyping) return;

    switch (event.code) {
        case 'Space':
            event.preventDefault();
            togglePlay();
            break;
        case 'ArrowLeft':
            event.preventDefault();
            changeTrack(-1, true);
            break;
        case 'ArrowRight':
            event.preventDefault();
            changeTrack(1, true);
            break;
        case 'ArrowUp':
            event.preventDefault();
            document.querySelector('.toggleFloatingLyricsBtn')?.click();
            break;
        case 'ArrowDown': 
            event.preventDefault();
            document.getElementById('toggleButton')?.click();
            break;
        case 'Delete':
          if (currentTrackIndex !== 0) {
            currentTrackIndex = 0;
            loadTrack(songs[0]);
          }
            const message = translations['back_to_first'] || 'Returned to the first song in the playlist';
            showLogMessage(message);
            speakMessage(message);
            break;
        case 'Insert':
            document.getElementById('repeatBtn')?.click();
            break;
        case 'Home':
            event.preventDefault();
            document.querySelector('.btn.btn-success.ms-2[data-bs-target="#musicModal"]').click();
            break;
        case 'Escape':
            event.preventDefault();
            const confirmText = document.getElementById('clearConfirmText')?.textContent.trim() || 'Are you sure you want to clear the config?';
            showConfirmation(confirmText, () => {
                document.getElementById('clear-cache-btn')?.click();
            });
            speakMessage(translations['clear_confirm'] || 'Are you sure you want to clear the configuration?');
            break;
    }
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
        console.log('Drag and drop is disabled on small screens.');
    }
});
</script>

<script>
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
document.addEventListener("DOMContentLoaded", function () {
    window.showConfirmation = function(message, onConfirm) {
        const decodedMessage = decodeURIComponent(message);

        document.getElementById('confirmModalMessage').innerText = decodedMessage;

        const oldBtn = document.getElementById('confirmModalYes');
        const newBtn = oldBtn.cloneNode(true);
        oldBtn.parentNode.replaceChild(newBtn, oldBtn);

        newBtn.addEventListener('click', () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
            modal.hide();
            if (typeof onConfirm === 'function') onConfirm();
        });

        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        modal.show();
    };

    window.handleDeleteConfirmation = function(file) {
        const decodedFile = decodeURIComponent(file); 
        const confirmMessage = (translations['confirm_delete_file'] || 'Are you sure you want to delete file %s?').replace('%s', decodedFile);
        showConfirmation(confirmMessage, () => {
            fetch(`?delete=${file}`)
                .then(response => {
                    if (response.ok) {
                        const successMsg = (translations['delete_success'] || 'Successfully deleted: %s').replace('%s', decodedFile);
                        showLogMessage(successMsg);
                        speakMessage(successMsg);
                        setTimeout(() => window.location.reload(), 9000); 
                    } else {
                        const errorMsg = (translations['delete_failure'] || 'Failed to delete: %s').replace('%s', decodedFile);
                        showLogMessage(errorMsg);
                        speakMessage(errorMsg);
                    }
                })
                .catch(() => { /*  */ });
        });
    };
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
      
      const response = await fetch('/luci-static/spectra/bgm/share.php', {
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

      const link = `${window.location.origin}/luci-static/spectra/bgm/download.php?token=${data.token}`;
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
    const res = await fetch('/luci-static/spectra/bgm/manage_tokens.php?action=clean');
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
        const res = await fetch('/luci-static/spectra/bgm/manage_tokens.php?action=delete_all');
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

    let isp = geo.org||geo.isp||'';
    if (!isp && geo.as) isp = geo.as.split(' ').slice(1).join(' ');
    isp = await translateText(isp);
    const asn    = geo.asn||geo.as?.split(' ')[0]||'';
    const asnOrg = await translateText(geo.asn_org||isp);

    const vals = modalEl.querySelectorAll('.detail-row .detail-value');
    vals[0].textContent = ip;
    vals[1].textContent = locationText;
    vals[2].textContent = isp;
    vals[3].textContent = [asn,asnOrg].filter(Boolean).join(' ');
    vals[4].textContent = geo.timezone||'';

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
  let city = localStorage.getItem('city') || 'Beijing';
  const apiKey = 'fc8bd2637768c286c6f1ed5f1915eb22';
  let currentWeatherData = null;

  const countryToLang = {
    CN: 'zh_cn', ZH: 'zh_cn', HK: 'zh_tw',
    EN: 'en', KO: 'kr', VI: 'vi', TH: 'th',
    JA: 'ja', RU: 'ru', DE: 'de', FR: 'fr',
    AR: 'ar', ES: 'es', BN: 'en'
  };
  let rawLang = localStorage.getItem('language') || 'CN';
  const targetLang = countryToLang[rawLang.toUpperCase()] || rawLang;

  const localeMap = {
    zh_cn: 'zh-CN', zh_tw: 'zh-TW', en: 'en-US', kr: 'ko-KR',
    vi: 'vi-VN', th: 'th-TH', ja: 'ja-JP', ru: 'ru-RU',
    de: 'de-DE', fr: 'fr-FR', ar: 'ar-EG', es: 'es-ES'
  };
  const locale = localeMap[targetLang] || 'en-US';
  const timeFormatter = new Intl.DateTimeFormat(locale, { hour: 'numeric', minute: '2-digit', hour12: true });

  const weatherIcon    = document.getElementById('weatherIcon');
  const weatherText    = document.getElementById('weatherText');
  const cityInput      = document.getElementById('cityInput');
  const saveCityBtn    = document.getElementById('saveCityBtn');
  const weatherDisplay = document.querySelector('.weather-display');

  function owmCodeToWiClass(code) {
    const map = {
      '01d': 'wi-day-sunny',    '01n': 'wi-night-clear',
      '02d': 'wi-day-cloudy',   '02n': 'wi-night-cloudy',
      '03d': 'wi-cloud',        '03n': 'wi-cloud',
      '04d': 'wi-cloudy',       '04n': 'wi-cloudy',
      '09d': 'wi-showers',      '09n': 'wi-showers',
      '10d': 'wi-day-rain',     '10n': 'wi-night-alt-rain',
      '11d': 'wi-thunderstorm', '11n': 'wi-thunderstorm',
      '13d': 'wi-snow',         '13n': 'wi-snow',
      '50d': 'wi-fog',          '50n': 'wi-fog'
    };
    return map[code] || 'wi-na';
  }

  function updateWeatherUI(data) {
    const iconCode = data.weather[0].icon;
    const temp     = Math.round(data.main.temp);
    const desc     = data.weather[0].description;

    weatherIcon.className = `wi ${owmCodeToWiClass(iconCode)}`;
    const colorMap = { '01d':'#FFD700','02d':'#C0C0C0','09d':'#00BFFF','13d':'#ADD8E6' };
    weatherIcon.style.color = colorMap[iconCode] || '#FFF';
    weatherIcon.title       = desc;
    weatherText.textContent = `${desc} ${temp}℃`;
  }

  function fetchWeather() {
    const url = `https://api.openweathermap.org/data/2.5/weather`
              + `?q=${encodeURIComponent(city)}`
              + `&appid=${apiKey}`
              + `&units=metric`
              + `&lang=${targetLang}`;
    fetch(url)
      .then(res => res.ok ? res.json() : Promise.reject('Network not OK'))
      .then(data => {
        if (data.weather && data.main) {
          currentWeatherData = data;
          updateWeatherUI(data);
        }
      })
      .catch(err => console.error('Error fetching weather：', err));
  }

  function saveCity() {
    const value = cityInput.value.trim();
    const chineseCharPattern = /[\u4e00-\u9fff]/;
    const startsUpper = /^[A-Z]/;

    if (chineseCharPattern.test(value)) {
      const msg = translations['invalid_city_non_chinese'];
      speakMessage(msg); showLogMessage(msg);
    }
    else if (!startsUpper.test(value)) {
      const msg = translations['invalid_city_uppercase'];
      speakMessage(msg); showLogMessage(msg);
    }
    else if (value) {
      city = value;
      localStorage.setItem('city', city);
      const savedMsg = translations['city_saved'].replace('{city}', city);
      const speakMsg = translations['city_saved_speak'].replace('{city}', city);
      showLogMessage(savedMsg);
      speakMessage(speakMsg);
      fetchWeather();
      bootstrap.Modal.getInstance(document.getElementById('cityModal')).hide();
    }
    else {
      const msg = translations['invalid_city'];
      speakMessage(msg);
    }
  }

  async function openWeatherModal() {
    if (!currentWeatherData) return;
    const d = currentWeatherData;

    const translatedCityName = await translateText(d.name, rawLang);
    document.getElementById('modalCityName').textContent = translatedCityName;
    document.getElementById('modalDesc').textContent      = d.weather[0].description;
    document.getElementById('modalTemp').textContent      = Math.round(d.main.temp);
    document.getElementById('modalFeels').textContent     = Math.round(d.main.feels_like);
    document.getElementById('modalHumidity').textContent  = d.main.humidity;
    document.getElementById('modalPressure').textContent  = d.main.pressure;
    document.getElementById('modalWind').textContent      = d.wind.speed;

    const toTime = ts => timeFormatter.format(new Date(ts * 1000));
    document.getElementById('modalSunrise').textContent   = toTime(d.sys.sunrise);
    document.getElementById('modalSunset').textContent    = toTime(d.sys.sunset);

    bootstrap.Modal.getOrCreateInstance(
      document.getElementById('weatherModal')
    ).show();
  }

  saveCityBtn.addEventListener('click', saveCity);
  weatherDisplay.addEventListener('click', openWeatherModal);

  document.addEventListener('DOMContentLoaded', () => {
    cityInput.value = city;
    fetchWeather();
    setInterval(fetchWeather, 10 * 60 * 1000);
  });
</script>

<script>
(function() {
  const toggleBtns = document.querySelectorAll('.toggleFloatingLyricsBtn');
  const box = document.getElementById('floatingLyrics');

  const savedState = localStorage.getItem('floatingLyricsVisible') === 'true';
  box.classList.toggle('visible', savedState);

  box.style.resize   = 'none';
  box.style.overflow = 'auto';
  box.style.position = 'absolute';

  toggleBtns.forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const isNowVisible = box.classList.toggle('visible');
      localStorage.setItem('floatingLyricsVisible', isNowVisible);

      const msgKey = isNowVisible
        ? 'floating_lyrics_enabled'
        : 'floating_lyrics_disabled';
      const message = translations[msgKey] ||
        (isNowVisible
          ? "Floating lyrics enabled"
          : "Floating lyrics disabled");
      showLogMessage(message);
      speakMessage(message);
    });
  });

  let isDragging = false, offsetX = 0, offsetY = 0;

  box.addEventListener('mousedown', e => {
    if (e.target.closest('.ctrl-btn')) return;
    e.preventDefault();
    isDragging = true;
    offsetX = e.clientX - box.offsetLeft;
    offsetY = e.clientY - box.offsetTop;
  });

  document.addEventListener('mousemove', e => {
    if (!isDragging) return;
    box.style.left = (e.clientX - offsetX) + 'px';
    box.style.top  = (e.clientY - offsetY) + 'px';
  });

  document.addEventListener('mouseup', () => {
    isDragging = false;
  });
})();
</script>
