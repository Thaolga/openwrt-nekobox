<?php
$base_dir = __DIR__;
$upload_dir = $base_dir;
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'mp3', 'wav'];
$background_type = '';
$background_src = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    $files = $_FILES['upload_file'];
    foreach ($files['name'] as $key => $filename) {
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed_types)) {
                $target_path = $upload_dir . '/' . basename($filename);
                if (!file_exists($target_path)) {
                    move_uploaded_file($files['tmp_name'][$key], $target_path);
                } else {
                    $error = "文件 {$filename} 已存在";
                }
            } else {
                $error = "不支持的文件类型：{$filename}";
            }
        }
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
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
    $old_name = $base_dir . '/' . basename($_POST['old_name']);
    $new_name = $base_dir . '/' . basename($_POST['new_name']);
    
    if (!preg_match('/^[\w\-\.]+$/', $_POST['new_name'])) {
        echo json_encode(['success' => false, 'error' => '文件名包含非法字符']);
        exit;
    }

    if (rename($old_name, $new_name)) {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
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
    <title>媒体文件管理</title>
    <link href="/luci-static/spectra/css/bootstrap.min.css" rel="stylesheet">
    <script src="/luci-static/spectra/js/jquery.min.js"></script>
    <script src="/luci-static/spectra/js/bootstrap.bundle.min.js"></script>
    <script src="/luci-static/spectra/js/custom.js"></script>
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
	font-size: 18px;
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

</style>
<body>
    <h2>Spectra 配置管理</h2>
    <p id="status">当前模式: <?= ($mode == "dark") ? "暗色模式" : "亮色模式" ?></p>
    <button id="toggleButton" onclick="toggleConfig()">切换模式</button>
</body>
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
        
                    <div class="me-3 d-flex gap-2" 
                        data-bs-toggle="tooltip" 
                        title="挂载点：<?= $mountPoint ?>｜已用空间：<?= formatSize($usedSpace) ?>">
                        <span class="badge bg-primary"><i class="bi bi-hdd"></i> 总共：<?= $totalSpace ? formatSize($totalSpace) : 'N/A' ?></span>
                        <span class="badge bg-success"><i class="bi bi-hdd"></i> 剩余：<?= $freeSpace ? formatSize($freeSpace) : 'N/A' ?></span>
                  </div>
                    <?php if ($downloadUrl): ?><button class="btn btn-success btn-sm update-theme-btn" data-url="<?= htmlspecialchars($downloadUrl) ?>" title="最新版本：<?= htmlspecialchars($latestVersion) ?>"><i class="bi bi-cloud-download"></i> 更新主题</button><?php endif; ?>
                    <button class="btn btn-success btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-upload"></i> 批量上传</button>
                    <button class="btn btn-danger btn-sm ms-2" id="clearBackgroundBtn"><i class="bi bi-trash"></i> 清除背景</button>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
        
                <div class="d-flex align-items-center mb-3">
                    <input type="checkbox" id="selectAll" class="me-2">
                    <label for="selectAll">全选</label>
                </div>

                <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4">
                    <?php foreach ($files as $file): 
                        $path = $upload_dir . '/' . $file;
                        $size = filesize($path);
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg','jpeg','png','gif']);
                        $isVideo = ($ext === 'mp4');
                        $isMedia = $isImage || $isVideo;
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
                                            <i class="bi bi-play-circle-fill  text-white"></i>
                                            <span class="text-white small">视频</span>
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
                                        </video>
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
                                                data-size="<?= $size ?>"

                                    <div class="d-flex flex-wrap gap-1 flex-grow-1" style="min-width: 0;">
                                        <button class="btn btn-danger btn-sm" onclick="if(confirm('确定删除？')) window.location='?delete=<?= urlencode($file) ?>'"><i class="bi bi-trash"></i></button>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#renameModal-<?= md5($file) ?>"><i class="bi bi-pencil"></i></button>
                            
                                    <?php if ($isMedia): ?>
                                        <button class="btn btn-info btn-sm set-bg-btn" data-src="<?= htmlspecialchars($file) ?>" data-type="<?= $isVideo ? 'video' : 'image' ?>"><i class="bi bi-image"></i></button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
                        <input type="file" name="upload_file[]" id="upload_file" multiple required>
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="post" action="">
                    <input type="hidden" name="old_name" value="<?= htmlspecialchars($file) ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">重命名 <?= htmlspecialchars($file) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>新文件名</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="new_name"
                                   value="<?= htmlspecialchars($file) ?>"
                                   pattern="[\w\-\.]+"
                                   required
                                   title="允许字母、数字、下划线、连字符和点号">
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

    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">预览</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="previewImage" src="" class="img-fluid d-none">
                    <video id="previewVideo" controls class="d-none" style="width: 100%; height: auto;">
                        <source id="previewVideoSource" src="" type="video/mp4">
                    </video>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="updateConfirmModal">
    <div class="modal-dialog modal-xl modal-dialog-centered">
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
                btn.innerText = "切换到亮色模式";
                btn.className = "light";
                status.innerText = "当前模式: 暗色模式";
            } else {
                btn.innerText = "切换到暗色模式";
                btn.className = "dark";
                status.innerText = "当前模式: 亮色模式";
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            updateButton("<?= $mode ?>"); 
        });
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

