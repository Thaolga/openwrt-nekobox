<?php
ob_start();
include './cfg.php';
$root_dir = "/";
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$current_path = $root_dir . $current_dir;

if (strpos(realpath($current_path), realpath($root_dir)) !== 0) {
    $current_dir = '';
    $current_path = $root_dir;
}

if (isset($_GET['preview']) && isset($_GET['path'])) {
    $preview_path = realpath($root_dir . '/' . $_GET['path']);
    if ($preview_path && strpos($preview_path, realpath($root_dir)) === 0) {
        $mime_type = mime_content_type($preview_path);
        header('Content-Type: ' . $mime_type);
        readfile($preview_path);
        exit;
    }
    header('HTTP/1.0 404 Not Found');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'refresh') {
    $contents = getDirectoryContents($current_path);
    echo json_encode($contents);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'get_content' && isset($_GET['path'])) {
    $file_path = $current_path . $_GET['path'];
    if (file_exists($file_path) && is_readable($file_path)) {
        $content = file_get_contents($file_path);
        header('Content-Type: text/plain; charset=utf-8');
        echo $content;
        exit;
    } else {
        http_response_code(404);
        echo '文件不存在或不可读。';
        exit;
    }
}

if (isset($_GET['download'])) {
    downloadFile($current_path . $_GET['download']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'rename':
                $new_name = basename($_POST['new_path']);
                $old_path = $current_path . $_POST['old_path'];
                $new_path = dirname($old_path) . '/' . $new_name;
                renameItem($old_path, $new_path);
                break;
            case 'edit':
                $content = $_POST['content'];
                $encoding = $_POST['encoding'];
                $result = editFile($current_path . $_POST['path'], $content, $encoding);
                if (!$result) {
                    echo "<script>alert('错误: 无法保存文件。');</script>";
                }
                break;
            case 'delete':
                deleteItem($current_path . $_POST['path']);
                break;
            case 'chmod':
                chmodItem($current_path . $_POST['path'], $_POST['permissions']);
                break;
            case 'create_folder':
                $new_folder_name = $_POST['new_folder_name'];
                $new_folder_path = $current_path . '/' . $new_folder_name;
                if (!file_exists($new_folder_path)) {
                    mkdir($new_folder_path);
                }
                break;
            case 'create_file':
                $new_file_name = $_POST['new_file_name'];
                $new_file_path = $current_path . '/' . $new_file_name;
                if (!file_exists($new_file_path)) {
                    file_put_contents($new_file_path, '');
                }
                break;
            case 'delete_selected':
                if (isset($_POST['selected_paths']) && is_array($_POST['selected_paths'])) {
                    foreach ($_POST['selected_paths'] as $path) {
                        deleteItem($current_path . $path);
                    }
                }
                break;
        }
    } elseif (isset($_FILES['upload'])) {
        uploadFile($current_path);
    }
}

function deleteItem($path) {
    if (is_dir($path)) {
        deleteDirectory($path);
    } else {
        unlink($path);
    }
}

function readFileWithEncoding($path) {
    $content = file_get_contents($path);
    $encoding = mb_detect_encoding($content, ['UTF-8', 'ASCII', 'ISO-8859-1', 'Windows-1252', 'GBK', 'Big5', 'Shift_JIS', 'EUC-KR'], true);
    return json_encode([
        'content' => mb_convert_encoding($content, 'UTF-8', $encoding),
        'encoding' => $encoding
    ]);
}

function renameItem($old_path, $new_path) {
    $new_name = basename($new_path);
    
    $dir = dirname($old_path);
    
    $new_full_path = $dir . '/' . $new_name;
    
    return rename($old_path, $new_full_path);
}

function editFile($path, $content, $encoding) {
    if (file_exists($path) && is_writable($path)) {
        return file_put_contents($path, $content) !== false;
    }
    return false;
}

function chmodItem($path, $permissions) {
    chmod($path, octdec($permissions));
}

function uploadFile($destination) {
    $filename = basename($_FILES["upload"]["name"]);
    
    $target_file = rtrim($destination, '/') . '/' . $filename;
    
    if (move_uploaded_file($_FILES["upload"]["tmp_name"], $target_file)) {
        return true;
    } else {
        die('上传失败');
    }
}


function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

function downloadFile($file) {
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

function getDirectoryContents($dir) {
    $contents = array();
    foreach (scandir($dir) as $item) {
        if ($item != "." && $item != "..") {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            $perms = '----';
            $size = '-';
            $mtime = '-';
            $owner = '-';
            if (file_exists($path) && is_readable($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -4);
                if (!is_dir($path)) {
                    $size = formatSize(filesize($path));
                }
                $mtime = date("Y-m-d H:i:s", filemtime($path));
                $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($path))['name'] : fileowner($path);
            }
            $contents[] = array(
                'name' => $item,
                'path' => str_replace($dir, '', $path),
                'is_dir' => is_dir($path),
                'permissions' => $perms,
                'size' => $size,
                'mtime' => $mtime,
                'owner' => $owner,
                'extension' => pathinfo($path, PATHINFO_EXTENSION)
            );
        }
    }
    return $contents;
}

function formatSize($bytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

$contents = getDirectoryContents($current_path);

$breadcrumbs = array();
$path_parts = explode('/', trim($current_dir, '/'));
$cumulative_path = '';
foreach ($path_parts as $part) {
    $cumulative_path .= $part . '/';
    $breadcrumbs[] = array('name' => $part, 'path' => $cumulative_path);
}

if (isset($_GET['action']) && $_GET['action'] === 'search' && isset($_GET['term'])) {
    $searchTerm = $_GET['term'];
    $searchResults = searchFiles($current_path, $searchTerm);
    echo json_encode($searchResults);
    exit;
}

function searchFiles($dir, $term) {
    $results = array();
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $file) {
        if ($file->isDir()) continue;
        if (stripos($file->getFilename(), $term) !== false) {
            $relativePath = str_replace($dir, '', $file->getPathname());
            $results[] = array(
                'path' => $relativePath,
                'dir' => dirname($relativePath),
                'name' => $file->getFilename()
            );
        }
    }

    return $results;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo substr($neko_theme, 0, -4) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeKobox文件助手</title>
    <link rel="icon" href="./assets/img/nekobox.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <script src="./assets/js/feather.min.js"></script>
    <script src="./assets/js/jquery-2.1.3.min.js"></script>
    <script src="./assets/js/neko.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/mode-json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/mode-yaml.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.0/beautify.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.0/beautify-css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.0/beautify-html.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.0/beautify.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-yaml/4.1.0/js-yaml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>

    <style>
        .folder-icon::before{content:"📁";}.file-icon::before{content:"📄";}.file-icon.file-pdf::before{content:"📕";}.file-icon.file-doc::before,.file-icon.file-docx::before{content:"📘";}.file-icon.file-xls::before,.file-icon.file-xlsx::before{content:"📗";}.file-icon.file-ppt::before,.file-icon.file-pptx::before{content:"📙";}.file-icon.file-zip::before,.file-icon.file-rar::before,.file-icon.file-7z::before{content:"🗜️";}.file-icon.file-mp3::before,.file-icon.file-wav::before,.file-icon.file-ogg::before,.file-icon.file-flac::before{content:"🎵";}.file-icon.file-mp4::before,.file-icon.file-avi::before,.file-icon.file-mov::before,.file-icon.file-wmv::before,.file-icon.file-flv::before{content:"🎞️";}.file-icon.file-jpg::before,.file-icon.file-jpeg::before,.file-icon.file-png::before,.file-icon.file-gif::before,.file-icon.file-bmp::before,.file-icon.file-tiff::before{content:"🖼️";}.file-icon.file-txt::before{content:"📝";}.file-icon.file-rtf::before{content:"📄";}.file-icon.file-md::before,.file-icon.file-markdown::before{content:"📑";}.file-icon.file-exe::before,.file-icon.file-msi::before{content:"⚙️";}.file-icon.file-bat::before,.file-icon.file-sh::before,.file-icon.file-command::before{content:"📜";}.file-icon.file-iso::before,.file-icon.file-img::before{content:"💿";}.file-icon.file-sql::before,.file-icon.file-db::before,.file-icon.file-dbf::before{content:"🗃️";}.file-icon.file-font::before,.file-icon.file-ttf::before,.file-icon.file-otf::before,.file-icon.file-woff::before,.file-icon.file-woff2::before{content:"🔤";}.file-icon.file-cfg::before,.file-icon.file-conf::before,.file-icon.file-ini::before{content:"🔧";}.file-icon.file-psd::before,.file-icon.file-ai::before,.file-icon.file-eps::before,.file-icon.file-svg::before{content:"🎨";}.file-icon.file-dll::before,.file-icon.file-so::before{content:"🧩";}.file-icon.file-css::before{content:"🎨";}.file-icon.file-js::before{content:"🟨";}.file-icon.file-php::before{content:"🐘";}.file-icon.file-json::before{content:"📊";}.file-icon.file-html::before,.file-icon.file-htm::before{content:"🌐";}.file-icon.file-bin::before{content:"👾";}
        .breadcrumb { margin-bottom: 20px; }
        .breadcrumb a { text-decoration: none; color: #0066cc; }
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 1200px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        #editContent { width: 100%; min-height: 400px; margin-bottom: 10px; }
        #aceEditor { position: fixed; top: 0; right: 0; bottom: 0; left: 0; display: none; }
        #aceEditorContainer { width: 100%; height: 100%; }
        .theme-toggle { position: absolute; top: 20px; right: 20px; }
        #themeToggle { background: none; border: none; font-size: 24px; cursor: pointer; transition: color 0.3s ease; }
        #themeToggle:hover { color: #007bff; }
        body.dark-mode { background-color: #333; color: #fff; }
        body.dark-mode table { color: #fff; }
        body.dark-mode th { background-color: #444; }
        body.dark-mode td { background-color: #555; }
        body.dark-mode .modal-content { background-color: #444; color: #fff; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header img { height: 100px; }
        .theme-toggle { position: absolute; top: 20px; right: 20px; }
        #themeToggle { color: #333; background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 5px 10px; border-radius: 5px; }
        #themeToggle:hover { background-color: #e9ecef; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .header img { height: 100px; margin-left: -20px; margin-top: 20px; }
        .btn { padding: 5px 10px; border-radius: 4px; border: none; cursor: pointer; font-size: 14px; min-width: 70px; margin: 2px; }
        .btn-rename { background-color: #0d6efd; color: white; }
        .btn-rename:hover { background-color: #0a58ca; }
        .btn-edit { background-color: #198754; color: white; }
        .btn-edit:hover { background-color: #157347; }
        .btn-download { background-color: #0dcaf0; color: white; }
        .btn-download:hover { background-color: #0abaf0; }
        .btn-chmod { background-color: #6c757d; color: white; }
        .btn-chmod:hover { background-color: #5c636a; }
        .delete-btn { background-color: #dc3545; color: white; }
        .delete-btn:hover { background-color: #c82333; }
         table { width: 100%; border-collapse: collapse; }
         th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
         th { background-color: #f2f2f2; }
         td:first-child { text-align: left; }
        .top-left-controls { position: absolute; top: 20px; left: 20px; display: flex; gap: 10px; }
        .language-switcher { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
        .language-switcher:hover { background-color: #e9ecef; }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 1200px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input[type="text"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .form-group input[type="radio"] { margin-right: 5px; }
        .btn-primary { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-group { display: flex; gap: 10px; }
        .btn-group > * { flex: 1; height: 38px; padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; text-align: center; }
        .btn-group button { display: flex; justify-content: center; align-items: center; }
        #languageSwitcher { padding-right: 2rem; }
        @media (max-width: 768px) { .btn-group > * { min-width: 50px; } }
        .btn-chmod { background-color: #FFA500; color: #333; } 
        .btn-chmod:hover { background-color: #FF8C00; color: #fff; }

        .nav-row { display: flex; justify-content: space-around; flex-wrap: wrap; }
        .nav-btn { display: flex; align-items: center; justify-content: center; padding: 10px 15px; margin: 5px; border-radius: 8px; text-decoration: none; color: #333; background-color: #fff; transition: all 0.3s ease; font-weight: 500; min-width: 100px; }
        .nav-btn:hover { background-color: #007bff; color: #fff; transform: translateY(-2px); }
        .nav-btn span { margin-right: 8px; font-size: 1.2em; }
        @media (max-width: 768px) { .nav-btn { font-size: 0.9rem; padding: 8px 12px; min-width: 80px; } }
        #renameModal .modal-content { background-color: var(--bs-body-bg); border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); max-width: 500px; margin: 10% auto; }
        #renameModal h2 { color: var(--bs-body-color); font-size: 24px; margin-bottom: 20px; text-align: center; }
        #renameModal form { display: flex; flex-direction: column; gap: 15px; }
        #renameModal label { color: var(--bs-body-color); font-size: 16px; font-weight: 500; }
        #renameModal input[type="text"] { width: 100%; padding: 10px 15px; border: 1px solid var(--bs-border-color); border-radius: 8px; font-size: 16px; background: var(--bs-body-bg); color: var(--bs-body-color); transition: border-color 0.3s ease; }
        #renameModal input[type="text"]:focus { outline: none; border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25); }
        #renameModal .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        #renameModal .btn { flex: 1; padding: 10px; border-radius: 8px; font-size: 16px; font-weight: 500; transition: all 0.3s ease; }
        #renameModal .btn-primary { background-color: #0d6efd; border: none; color: white; }
        #renameModal .btn-secondary { background-color: #6c757d; border: none; color: white; }
        #renameModal .btn:hover { transform: translateY(-2px); box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        body.dark-mode #renameModal .modal-content { background-color: #2d3238; border-color: #495057; }
        body.dark-mode #renameModal input[type="text"] { background-color: #343a40; border-color: #495057; color: #fff; }
        #chmodModal .modal-content { background-color: var(--bs-body-bg); border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); max-width: 500px; margin: 10% auto; }
        #chmodModal h2 { color: var(--bs-body-color); font-size: 24px; margin-bottom: 20px; text-align: center; }
        #chmodModal form { display: flex; flex-direction: column; gap: 15px; }
        #chmodModal .permission-group { display: flex; gap: 20px; margin-bottom: 15px; }
        #chmodModal .permission-section { flex: 1; }
        #chmodModal .permission-section h3 { font-size: 16px; margin-bottom: 10px; color: var(--bs-body-color); }
        #chmodModal .checkbox-group { display: flex; flex-direction: column; gap: 8px; }
        #chmodModal label { color: var(--bs-body-color); font-size: 16px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        #chmodModal input[type="text"] { width: 100%; padding: 10px 15px; border: 1px solid var(--bs-border-color); border-radius: 8px; font-size: 16px; background: var(--bs-body-bg); color: var(--bs-body-color); transition: border-color 0.3s ease; text-align: center; letter-spacing: 1px; }
        #chmodModal input[type="text"]:focus { outline: none; border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25); }
        #chmodModal .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        #chmodModal .btn { flex: 1; padding: 10px; border-radius: 8px; font-size: 16px; font-weight: 500; transition: all 0.3s ease; }
        #chmodModal .btn-primary { background-color: #0d6efd; border: none; color: white; }
        #chmodModal .btn-secondary { background-color: #6c757d; border: none; color: white; }
        #chmodModal .btn:hover { transform: translateY(-2px); box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        body.dark-mode #chmodModal .modal-content { background-color: #2d3238; border-color: #495057; }
        body.dark-mode #chmodModal input[type="text"] { background-color: #343a40; border-color: #495057; color: #fff; }
        #previewModal .modal-content { width: 90%; max-width: 1200px; height: 90vh; overflow: auto; }
        #previewContainer { text-align: center; padding: 20px; }
        #previewContainer img { max-width: 100%; max-height: 70vh; object-fit: contain; }
        #previewContainer audio, #previewContainer video { max-width: 100%; }
        #previewContainer svg { max-width: 100%; max-height: 70vh; }
        .modal-content { background-color: var(--bs-body-bg); border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); position: relative; }
        .modal .close { position: absolute; right: 20px; top: 20px; font-size: 28px; font-weight: bold; cursor: pointer; z-index: 1; }
        body.dark-mode #previewModal .modal-content { background-color: #2d3238; color: #fff; }
        .button-group { display: flex; justify-content: center; flex-wrap: wrap; gap: 5px; }
        .btn-outline-secondary, #languageSwitcher { width: 38px; height: 38px; padding: 0; font-size: 1rem; line-height: 1; border-radius: 0.25rem; display: flex; align-items: center; justify-content: center; }
        #languageSwitcher { width: auto; padding: 0 0.5rem; }
        @media (max-width: 768px) { .button-group { justify-content: center; } }
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;color:var(--text-color);line-height:1.6;}
        :root {--primary-color: #3498db;--secondary-color: #2ecc71;--text-color: #34495e;--border-color: #e0e6ed;}
        table {width: 100%;border-collapse: separate;border-spacing: 0;background: #fff;border-radius: 10px;overflow: hidden;box-shadow: 0 5px 15px rgba(0,0,0,0.1);}
        th {background-color: var(--primary-color);color: white;font-weight: 600;text-transform: uppercase;letter-spacing: 0.5px;}
        tr:last-child td {border-bottom: none;}
        tr:hover {background-color: #f8f9fa;}
        .modal-content {background-color: #fff;border-radius: 10px;padding: 30px;box-shadow: 0 10px 30px rgba(0,0,0,0.2);}
        .modal h2 {margin-bottom: 20px;color: var(--primary-color);}
        .form-group {margin-bottom: 20px;}
        .form-group label {display: block;margin-bottom: 5px;font-weight: 600;}
        .form-group input[type="text"] {width: 100%;padding: 10px;border: 1px solid var(--border-color);border-radius: 5px;font-size: 16px;}
        @media (max-width: 768px) {table {font-size: 14px;} .btn {padding: 6px 12px;font-size: 12px;} .nav-btn {padding: 8px 16px;font-size: 14px;}}
        @keyframes fadeIn {from { opacity: 0; }to { opacity: 1; }}
        .modal {animation: fadeIn 0.3s ease;}
        .btn, .nav-btn {transition: all 0.3s ease;}
        .btn:hover, .nav-btn:hover {transform: translateY(-2px);box-shadow: 0 5px 15px rgba(0,0,0,0.1);}
        .modal {display: none;position: fixed;z-index: 1000;left: 0;top: 0;width: 100%;height: 100%;overflow: auto;background-color: rgba(0,0,0,0.4);backdrop-filter: blur(5px);}
        .modal.show .modal-content {transform: translateY(0);opacity: 1;}
        .close {color: #aaa;float: right;font-size: 28px;font-weight: bold;transition: color 0.3s ease;}
        .close:hover,.close:focus {color: #000;text-decoration: none;cursor: pointer;}
        .modal h2 {margin-top: 0;color: #333;font-size: 24px;font-weight: 600;margin-bottom: 20px;padding-bottom: 10px;border-bottom: 2px solid #f0f0f0;}
        .modal form {margin-top: 20px;}
        .modal .form-group {margin-bottom: 20px;}
        .modal label {display: block;margin-bottom: 5px;font-weight: 500;color: #555;}
        .modal input[type="text"],.modal input[type="password"],.modal textarea {width: 100%;padding: 10px;border: 1px solid #ddd;border-radius: 5px;font-size: 16px;transition: border-color 0.3s ease;}
        .modal input[type="text"]:focus,.modal input[type="password"]:focus,.modal textarea:focus {border-color: #3498db;outline: none;box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);}
        .modal .btn {padding: 10px 20px;font-size: 16px;border: none;border-radius: 5px;cursor: pointer;transition: all 0.3s ease;}
        .modal .btn-primary {background-color: #3498db;color: white;}
        .modal .btn-primary:hover {background-color: #2980b9;}
        .modal .btn-secondary {background-color: #95a5a6;color: white;}
        .modal .btn-secondary:hover {background-color: #7f8c8d;}
        body.dark-mode td.folder-icon a {color: #2ecc71;transition: color 0.3s ease;}
        body.dark-mode td.folder-icon a:hover {color: #27ae60;text-decoration: underline;}
        body.dark-mode .modal-content {background-color: #2c3e50;color: #ecf0f1;}
        body.dark-mode .modal h2 {color: #3498db;border-bottom-color: #34495e;}
        body.dark-mode .close {color: #bdc3c7;}
        body.dark-mode .close:hover,body.dark-mode .close:focus {color: #ecf0f1;}
        body.dark-mode .modal label {color: #ecf0f1;}
        body.dark-mode .modal input[type="text"],body.dark-mode .modal input[type="password"],body.dark-mode .modal textarea {background-color: #34495e;border-color: #2c3e50;color: #ecf0f1;}
        body.dark-mode .modal input[type="text"]:focus,body.dark-mode .modal input[type="password"]:focus,body.dark-mode .modal textarea:focus {border-color: #3498db;box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);}
        body.dark-mode #renameModal .modal-content,body.dark-mode #chmodModal .modal-content {color: #ecf0f1;}
        body.dark-mode #renameModal label,body.dark-mode #chmodModal label {color: #ecf0f1;}
        body.dark-mode #renameModal input[type="text"],body.dark-mode #chmodModal input[type="text"] {color: #ecf0f1;background-color: #34495e;border-color: #3498db;}
        body.dark-mode #renameModal .btn,body.dark-mode #chmodModal .btn {color: #ecf0f1;}
        body.dark-mode #renameModal .btn-primary,body.dark-mode #chmodModal .btn-primary {background-color: #3498db;}
        body.dark-mode #renameModal .btn-secondary,body.dark-mode #chmodModal .btn-secondary {background-color: #95a5a6;}
        body.dark-mode .modal-content {background-color: #2c3e50;color: #ecf0f1;}
        body.dark-mode .modal h2 {color: #ecf0f1;border-bottom-color: #34495e;}
        body.dark-mode .close {color: #bdc3c7;}
        body.dark-mode .close:hover,body.dark-mode .close:focus {color: #ecf0f1;}
        body.dark-mode .modal label {color: #ecf0f1;}
        body.dark-mode .modal input[type="text"],body.dark-mode .modal input[type="password"],body.dark-mode .modal textarea {background-color: #34495e;border-color: #2c3e50;color: #ecf0f1;}
        body.dark-mode .modal input[type="text"]:focus,body.dark-mode .modal input[type="password"]:focus,body.dark-mode .modal textarea:focus {border-color: #3498db;box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);}
        body.dark-mode #renameModal .modal-content,body.dark-mode #chmodModal .modal-content,body.dark-mode #editModal .modal-content,body.dark-mode #newFolderModal .modal-content,body.dark-mode #newFileModal .modal-content,body.dark-mode               #searchModal .modal-content,body.dark-mode #previewModal .modal-content {color: #ecf0f1;}
        body.dark-mode #renameModal h2,body.dark-mode #chmodModal h2,body.dark-mode #editModal h2,body.dark-mode #newFolderModal h2,body.dark-mode #newFileModal h2,body.dark-mode #searchModal h2,body.dark-mode #previewModal h2 {color: #ecf0f1;}
        #editModal .modal-content,#previewModal .modal-content {width: 80%;max-width: 1000px;}
        #searchModal .modal-dialog,#searchModal .modal-content {max-width: 90% !important;width: 800px !important;}
        body.dark-mode td.folder-icon a,body.dark-mode td.file-icon a {text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);}
        body.dark-mode .nav-container {background-color: transparent;box-shadow: none;}
        body.dark-mode .nav-btn {color: #ecf0f1;background-color: rgba(52, 73, 94, 0.6);transition: all 0.3s ease;}
        body.dark-mode .nav-btn:hover {background-color: rgba(52, 73, 94, 0.8);color: #3498db;transform: translateY(-2px);}
        body.dark-mode .nav-btn span {color: #3498db;}
        body.dark-mode .breadcrumb {background-color: transparent;color: #ecf0f1;}
        body.dark-mode .breadcrumb a {color: #3498db;transition: color 0.3s ease;}
        body.dark-mode .breadcrumb a:hover {color: #2980b9;text-decoration: underline;}
        .modal-content {background-color: #fff;margin: 5% auto;padding: 30px;border: none;width: 50%;max-width: 600px;box-shadow: 0 10px 30px rgba(0,0,0,0.2);border-radius: 15px;}
        #languageSwitcher {width: 120px; padding: 5px 25px 5px 10px;}
        body {overflow-x: hidden;}
  </style>
</head>
<body>
<div class="container-sm callout border border-3 rounded-4 col-11">
    <div class="row">
        <a href="./index.php" class="col btn btn-sm custom-btn-color" data-translate="home">🏠 首页</a>
        <a href="./upload.php" class="col btn btn-sm custom-btn-color">📂 Mihomo</a>
        <a href="./upload_sb.php" class="col btn btn-sm custom-btn-color">🗂️ Sing-box</a>
        <a href="./box.php" class="col btn btn-sm custom-btn-color" data-translate="convert">💹 转换</a>
        <a href="./nekobox.php" class="col btn btn-sm custom-btn-color" data-translate="fileAssistant">📦 文件助手</a>
    </div>
</div>

<div class="row">
    <div class="col-12">  
        <div class="container container-bg border border-3 rounded-4 p-3">
            <div class="row align-items-center mb-3">
                <div class="col-md-3 text-center text-md-start">
                    <img src="./assets/img/nekobox.png" alt="Neko Box" class="img-fluid" style="max-height: 100px;">
                </div>
                <div class="col-md-6 text-center"> 
                    <h1 class="mb-0" id="pageTitle">NeKoBox文件助手</h1>
                </div>
                <div class="col-md-3">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-toolbar justify-content-between">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="goBack()" title="返回上一级" data-translate-title="goBackTitle">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="location.href='?dir=/'" title="返回根目录"  data-translate-title="rootDirectoryTitle">
                                <i class="fas fa-home"></i> 
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="location.href='?dir=/root'" title="返回主目录"  data-translate-title="homeDirectoryTitle">
                                <i class="fas fa-user"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()" title="刷新目录内容"  data-translate-title="refreshDirectoryTitle">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="selectAll()" id="selectAllBtn" title="全选"  data-translate-title="selectAll">
                                <i class="fas fa-check-square"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="reverseSelection()" id="reverseSelectionBtn" title="反选"  data-translate-title="invertSelection">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="deleteSelected()" id="deleteSelectedBtn" title="删除所选"  data-translate-title="deleteSelected">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="showSearchModal()" id="searchBtn" title="搜索" data-translate-title="searchTitle">
                                <i class="fas fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="showCreateModal()" id="createBtn" title="新建"  data-translate-title="createTitle">    
                                <i class="fas fa-plus"></i> 
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="showUploadArea()" id="uploadBtn" title="上传"  data-translate-title="uploadTitle">
                                <i class="fas fa-upload"></i>
                            </button>
                        </div>
                        <div class="btn-group">
                            <select id="languageSwitcher" class="form-select">
                                <option value="en">English</option>
                                <option value="zh">中文</option>                  
                            </select>
                            <button id="themeToggle" class="btn btn-outline-secondary" title="切换主题"  data-translate-title="themeToggleTitle">
                                <i class="fas fa-moon"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="breadcrumb">
                <a href="?dir=">根目录</a> /
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <a href="?dir=<?php echo urlencode($crumb['path']); ?>"><?php echo htmlspecialchars($crumb['name']); ?></a>
                    <?php if ($index < count($breadcrumbs) - 1) echo " / "; ?>
                <?php endforeach; ?>
            </div>
            <div class="upload-container">
                <div class="upload-area" id="uploadArea" style="display: none;">
                    <form action="" method="post" enctype="multipart/form-data" id="uploadForm">
                        <input type="file" name="upload" id="fileInput" style="display: none;" required>
                        <div class="upload-drop-zone" id="dropZone">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        </div>
                    </form>
                    <button type="button" class="btn btn-secondary mt-2" onclick="hideUploadArea()">取消</button>
                </div>
            </div>
            <table>
                <tr>
                    <th><input type="checkbox" id="selectAllCheckbox"></th>
                    <th data-translate="name">名称</th>
                    <th data-translate="type">类型</th>
                    <th data-translate="size">大小</th>
                    <th data-translate="modifiedTime">修改时间</th>
                    <th data-translate="permissions">权限</th>
                    <th data-translate="owner">拥有者</th>
                    <th data-translate="actions">操作</th>
                </tr>
                <?php if ($current_dir != ''): ?>
                    <tr>
                        <td></td>
                        <td class="folder-icon"><a href="?dir=<?php echo urlencode(dirname($current_dir)); ?>">..</a></td>
                        <td>目录</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td></td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($contents as $item): ?>
                    <tr>
                        <td><input type="checkbox" class="file-checkbox" data-path="<?php echo htmlspecialchars($item['path']); ?>"></td>
                        <?php
                        $icon_class = $item['is_dir'] ? 'folder-icon' : 'file-icon';
                        if (!$item['is_dir']) {
                            $ext = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
                            $icon_class .= ' file-' . $ext;
                        }
                        ?>
                        <td class="<?php echo $icon_class; ?>">
                            <?php if ($item['is_dir']): ?>
                                <a href="?dir=<?php echo urlencode($current_dir . $item['path']); ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                            <?php else: ?>
                                <?php 
                                $ext = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'mp3', 'mp4'])): 
                                    $clean_path = ltrim(str_replace('//', '/', $item['path']), '/');
                                ?>
                                    <a href="#" onclick="previewFile('<?php echo htmlspecialchars($clean_path); ?>', '<?php echo $ext; ?>')"><?php echo htmlspecialchars($item['name']); ?></a>
                                <?php else: ?>
                                    <a href="#" onclick="showEditModal('<?php echo htmlspecialchars(addslashes($item['path'])); ?>')"><?php echo htmlspecialchars($item['name']); ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td data-translate="<?php echo $item['is_dir'] ? 'directory' : 'file'; ?>"><?php echo $item['is_dir'] ? '目录' : '文件'; ?></td>
                        <td><?php echo $item['size']; ?></td>
                        <td><?php echo $item['mtime']; ?></td>
                        <td><?php echo $item['permissions']; ?></td>
                        <td><?php echo htmlspecialchars($item['owner']); ?></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <button onclick="showRenameModal('<?php echo htmlspecialchars($item['name']); ?>', '<?php echo htmlspecialchars($item['path']); ?>')" class="btn btn-rename" data-translate="rename">✏️ 重命名</button>
                                <?php if (!$item['is_dir']): ?>
                                    <a href="?dir=<?php echo urlencode($current_dir); ?>&download=<?php echo urlencode($item['path']); ?>" class="btn btn-download" data-translate="download">⬇️ 下载</a>
                                <?php endif; ?>
                                <button onclick="showChmodModal('<?php echo htmlspecialchars($item['path']); ?>', '<?php echo $item['permissions']; ?>')" class="btn btn-chmod" data-translate="setPermissions">🔒 权限</button>
                                <form method="post" style="display:inline;" onsubmit="return confirmDelete('<?php echo htmlspecialchars($item['name']); ?>');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="path" value="<?php echo htmlspecialchars($item['path']); ?>">
                                    <button type="submit" class="btn delete-btn" data-translate="delete">🗑️ 删除</button> 
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div id="renameModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('renameModal')">&times;</span>
                    <h2 data-translate="rename">重命名</h2>
                    <form method="post" onsubmit="return validateRename()">
                        <input type="hidden" name="action" value="rename">
                        <input type="hidden" name="old_path" id="oldPath">
                        <div class="form-group">
                            <label for="newPath" data-translate="newName">新名称</label>
                            <input type="text" name="new_path" id="newPath" class="form-control" autocomplete="off" data-translate-placeholder="enterNewName">
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('renameModal')" data-translate="cancel">取消</button>
                            <button type="submit" class="btn btn-primary" data-translate="confirmRename">确认重命名</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="createModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('createModal')">&times;</span>
                    <h2 data-translate="create">新建</h2>
                    <button onclick="showNewFolderModal()" class="btn btn-primary mb-2" data-translate="newFolder">新建文件夹</button>
                    <button onclick="showNewFileModal()" class="btn btn-primary" data-translate="newFile">新建文件</button>
                </div>
            </div>

            <div id="newFolderModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('newFolderModal')">&times;</span>
                    <h2 data-translate="newFolder">新建文件夹</h2>
                    <form method="post" onsubmit="return createNewFolder()">
                        <input type="hidden" name="action" value="create_folder">
                        <label for="newFolderName" data-translate="folderName">文件夹名称:</label>
                        <input type="text" name="new_folder_name" id="newFolderName" required data-translate-placeholder="enterFolderName">
                        <input type="submit" value="创建" class="btn" data-translate="create">
                    </form>
                </div>
            </div>

            <div id="newFileModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('newFileModal')">&times;</span>
                    <h2 data-translate="newFile">新建文件</h2>
                    <form method="post" onsubmit="return createNewFile()">
                        <input type="hidden" name="action" value="create_file">
                        <label for="newFileName" data-translate="fileName">文件名称:</label>
                        <input type="text" name="new_file_name" id="newFileName" required data-translate-placeholder="enterFileName">
                        <input type="submit" value="创建" class="btn" data-translate="create">
                    </form>
                </div>
            </div>
        <div id="searchModal" class="modal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" data-translate="searchFiles">搜索文件</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="searchForm">
                                <div class="input-group mb-3">
                                    <input type="text" id="searchInput" class="form-control" data-translate="searchInputPlaceholder" data-translate-placeholder="searchInputPlaceholder" placeholder="输入文件名" required>
                                    <button type="submit" class="btn btn-primary" data-translate="search">搜索</button>
                                </div>
                            </form>
                            <div id="searchResults"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('editModal')">&times;</span>
                    <h2 data-translate="editFile">编辑文件</h2>
                    <form method="post" id="editForm" onsubmit="return saveEdit()">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="path" id="editPath">
                        <input type="hidden" name="encoding" id="editEncoding">
                        <textarea name="content" id="editContent" rows="10" cols="50"></textarea>
                        <input type="submit" value="保存" class="btn" data-translate="save">
                        <button type="button" onclick="openAceEditor()" class="btn" data-translate="advancedEdit">高级编辑</button>
                    </form>
                </div>
            </div>

            <div id="aceEditor">
                <div id="aceEditorContainer"></div>
                <div id="editorStatusBar">
                    <span id="cursorPosition"><span data-translate="line">行</span>: <span id="currentLine">1</span>, <span data-translate="column">列</span>: <span id="currentColumn">1</span></span>
                    <span id="characterCount"><span data-translate="characterCount">字符数</span>: <span id="charCount">0</span></span>
                </div>
                <div id="editorControls">
                    <select id="fontSize" onchange="changeFontSize()">
                        <option value="18px">18px</option>
                        <option value="20px" selected>20px</option>
                        <option value="22px">22px</option>
                        <option value="24px">24px</option>
                        <option value="26px">26px</option>
                    </select>
                    <select id="editorTheme" onchange="changeEditorTheme()">
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
                    <select id="encoding" onchange="changeEncoding()">
                        <option value="UTF-8">UTF-8</option>
                        <option value="ASCII">ASCII</option>
                        <option value="ISO-8859-1">ISO-8859-1 (Latin-1)</option>
                        <option value="Windows-1252">Windows-1252</option>
                        <option value="GBK">GBK (简体中文)</option>
                        <option value="Big5">Big5 (繁体中文)</option>
                        <option value="Shift_JIS">Shift_JIS (日文)</option>
                        <option value="EUC-KR">EUC-KR (韩文)</option>
                    </select>
                    <button onclick="toggleSearch()" class="btn" title="搜索文件内容" data-translate="search"><i class="fas fa-search"></i></button>
                    <button onclick="formatCode()" class="btn" data-translate="format">格式化</button>
                    <button onclick="validateJSON()" class="btn" id="validateJSONBtn" style="display: none;" data-translate="validateJSON">验证 JSON</button>
                    <button onclick="validateYAML()" class="btn" id="validateYAMLBtn" style="display: none;" data-translate="validateYAML">验证 YAML</button>
                    <button onclick="saveAceContent()" class="btn" data-translate="save">保存</button>
                    <button onclick="closeAceEditor()" class="btn" data-translate="close">关闭</button>
                </div>
            </div>

            <div id="aceEditor">
                <div id="aceEditorContainer"></div>
                <div style="position: absolute; top: 10px; right: 10px;">
                    <button onclick="saveAceContent()" class="btn" data-translate="save">保存</button>
                    <button onclick="closeAceEditor()" class="btn" style="margin-left: 10px;" data-translate="close">关闭</button>
                </div>
            </div>

            <div id="chmodModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('chmodModal')">&times;</span>
                    <h2 data-translate="setPermissions">设置权限</h2>
                    <form method="post" onsubmit="return validateChmod()">
                        <input type="hidden" name="action" value="chmod">
                        <input type="hidden" name="path" id="chmodPath">
                        <div class="form-group">
                            <label for="permissions" data-translate="permissionValue">权限值（例如：0644）</label>
                            <input type="text" name="permissions" id="permissions" class="form-control" maxlength="4" data-translate-placeholder="permissionPlaceholder" placeholder="0644" autocomplete="off">
                            <small class="form-text text-muted" data-translate="permissionHelp">输入三位或四位数字，例如：0644 或 0755</small>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('chmodModal')" data-translate="cancel">取消</button>
                            <button type="submit" class="btn btn-primary" data-translate="confirmChange">确认修改</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="previewModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('previewModal')">&times;</span>
                    <h2 data-translate="filePreview">文件预览</h2>
                    <div id="previewContainer">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const DEFAULT_FONT_SIZE = '20px';

    let aceEditor;

    function showModal(modalId) {
        document.getElementById(modalId).style.display = "block";
    }

function goBack() {
    window.history.back();
}

function refreshDirectory() {
    location.reload();
}

function showCreateModal() {
    showModal('createModal');
}

function showNewFolderModal() {
    closeModal('createModal');
    showModal('newFolderModal');
}

function showNewFileModal() {
    closeModal('createModal');
    showModal('newFileModal');
}

window.addEventListener("load", function() {
    aceEditor = ace.edit("aceEditorContainer");
    aceEditor.setTheme("ace/theme/monokai");
    aceEditor.setFontSize(20);

    aceEditor.getSession().selection.on('changeCursor', updateCursorPosition);
    aceEditor.getSession().on('change', updateCharacterCount);
});

function updateCursorPosition() {
    var cursorPosition = aceEditor.getCursorPosition();
    document.getElementById('currentLine').textContent = cursorPosition.row + 1;
    document.getElementById('currentColumn').textContent = cursorPosition.column + 1;
}

function updateCharacterCount() {
    var characterCount = aceEditor.getValue().length;
    document.getElementById('charCount').textContent = characterCount;
}

function refreshDirectory() {
    fetch('?action=refresh&dir=' + encodeURIComponent(currentDir))
        .then(response => response.json())
        .then(data => {
            updateDirectoryView(data);
        })
        .catch(error => console.error('Error:', error));
}

function updateDirectoryView(contents) {

}

function createNewFolder() {
    let folderName = document.getElementById('newFolderName').value.trim();
    if (folderName === '') {
        alert('文件夹名称不能为空');
        return false;
    }
    return true;
}

function createNewFile() {
    let fileName = document.getElementById('newFileName').value.trim();
    if (fileName === '') {
        alert('文件名称不能为空');
        return false;
    }
    return true;
}

function showSearchModal() {
    const searchModal = new bootstrap.Modal(document.getElementById('searchModal'), {
        backdrop: 'static',
        keyboard: false
    });
    searchModal.show();
}

function searchFiles(event) {
    event.preventDefault();
    const searchTerm = document.getElementById('searchInput').value;
    const currentDir = '<?php echo $current_dir; ?>';

    fetch(`?action=search&dir=${encodeURIComponent(currentDir)}&term=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('searchResults');
            resultsDiv.innerHTML = '';

            if (data.length === 0) {
                resultsDiv.innerHTML = '<p>没有找到匹配的文件。</p>';
            } else {
                const ul = document.createElement('ul');
                ul.className = 'list-group';
                data.forEach(file => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center';
                    const fileSpan = document.createElement('span');
                    fileSpan.textContent = `${file.name} (${file.path})`;
                    li.appendChild(fileSpan);

                    const moveButton = document.createElement('button');
                    moveButton.className = 'btn btn-sm btn-primary';
                    moveButton.textContent = '移至';
                    moveButton.onclick = function() {
                        const targetDir = file.dir === '' ? '/' : file.dir;
                        window.location.href = `?dir=${encodeURIComponent(targetDir)}`;
                        bootstrap.Modal.getInstance(document.getElementById('searchModal')).hide();
                    };
                    li.appendChild(moveButton);

                    ul.appendChild(li);
                });
                resultsDiv.appendChild(ul);
            }
        })
        .catch(error => {
            console.error('搜索出错:', error);
            alert('搜索时出错: ' + error.message);
        });
}

fileSpan.textContent = `${file.name} (${file.path})`;

function closeModal(modalId) {
    if (modalId === 'editModal' && document.getElementById('aceEditor').style.display === 'block') {
        return;
    }
    document.getElementById(modalId).style.display = "none";
}

    function changeEncoding() {
        let encoding = document.getElementById('encoding').value;
        let content = aceEditor.getValue();

        if (encoding === 'ASCII') {
            content = content.replace(/[^\x00-\x7F]/g, "");
        } else if (encoding !== 'UTF-8') {
            alert('编码已更改为 ' + encoding + '。实际转换将在保存时在服务器端进行。');
        }

        aceEditor.setValue(content, -1);
    }

function showEditModal(path) {
    document.getElementById('editPath').value = path;

    fetch('?action=get_content&dir=' + encodeURIComponent('<?php echo $current_dir; ?>') + '&path=' + encodeURIComponent(path))
        .then(response => {
            if (!response.ok) {
                throw new Error('无法获取文件内容: ' + response.statusText);
            }
            return response.text();
        })
        .then(data => {
            let content, encoding;
            try {
                const parsedData = JSON.parse(data);
                content = parsedData.content;
                encoding = parsedData.encoding;
            } catch (e) {
                content = data;
                encoding = 'Unknown';
            }

            document.getElementById('editContent').value = content;
            document.getElementById('editEncoding').value = encoding;

            if (!aceEditor) {
                aceEditor = ace.edit("aceEditorContainer");
                aceEditor.setTheme("ace/theme/monokai");
                aceEditor.setFontSize(DEFAULT_FONT_SIZE);
            } else {
                aceEditor.setFontSize(DEFAULT_FONT_SIZE);
            }

            aceEditor.setValue(content, -1);

            let fileExtension = path.split('.').pop().toLowerCase();
            let mode = getAceMode(fileExtension);
            aceEditor.session.setMode("ace/mode/" + mode);

            document.getElementById('encoding').value = encoding;
            document.getElementById('fontSize').value = DEFAULT_FONT_SIZE;

            showModal('editModal');
        })
        .catch(error => {
            console.error('编辑文件时出错:', error);
            alert('加载文件内容时出错: ' + error.message);
        });
}

    function setAceEditorTheme() {
        if (document.body.classList.contains('dark-mode')) {
            aceEditor.setTheme("ace/theme/monokai");
            document.getElementById('editorTheme').value = "ace/theme/monokai";
        } else {
            aceEditor.setTheme("ace/theme/github");
            document.getElementById('editorTheme').value = "ace/theme/github";
        }
    }

    function changeFontSize() {
        let fontSize = document.getElementById('fontSize').value;
        aceEditor.setFontSize(fontSize);
    }

    function changeEditorTheme() {
        let theme = document.getElementById('editorTheme').value;
        aceEditor.setTheme(theme);
        localStorage.setItem('preferredAceTheme', theme); 
    }

function formatCode() {
    let session = aceEditor.getSession();
    let beautify = ace.require("ace/ext/beautify");
    beautify.beautify(session);
}


function showChmodModal(path, currentPermissions) {
    document.getElementById('chmodPath').value = path;
    const permInput = document.getElementById('permissions');
    permInput.value = currentPermissions;
    
    setTimeout(() => {
        permInput.select();
        permInput.focus();
    }, 100);
    
    showModal('chmodModal');
}

function validateChmod() {
    const permissions = document.getElementById('permissions').value.trim();
    if (!/^[0-7]{3,4}$/.test(permissions)) {
        alert('请输入有效的权限值（三位或四位八进制数字，例如：644 或 0755）');
        return false;
    }
    
    const permNum = parseInt(permissions, 8);
    if (permNum > 0777) {
        alert('权限值不能超过 0777');
        return false;
    }
    
    return true;
}

document.getElementById('permissions').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-7]/g, '');
    if (this.value.length > 4) {
        this.value = this.value.slice(0, 4);
    }
});

function getAceMode(extension) {
    const modeMap = {
        'js': 'javascript',
        'json': 'json',
        'py': 'python',
        'php': 'php',
        'html': 'html',
        'css': 'css',
        'json': 'json',
        'xml': 'xml',
        'md': 'markdown',
        'txt': 'text',
        'yaml': 'yaml',
        'yml': 'yaml'
    };
    return modeMap[extension] || 'text';
}

function saveEdit() {
    if (document.getElementById('aceEditor').style.display === 'block') {
        saveAceContent();
    }
    else {
        let content = document.getElementById('editContent').value;
        let encoding = document.getElementById('editEncoding').value;
        document.getElementById('editForm').submit();
    }
    return false;
}

function showEditModal(path) {
    document.getElementById('editPath').value = path;

    fetch('?action=get_content&dir=' + encodeURIComponent('<?php echo $current_dir; ?>') + '&path=' + encodeURIComponent(path))
        .then(response => {
            if (!response.ok) {
                throw new Error('无法获取文件内容: ' + response.statusText);
            }
            return response.text();
        })
        .then(content => {
            document.getElementById('editContent').value = content;

            if (!aceEditor) {
                aceEditor = ace.edit("aceEditorContainer");
                aceEditor.setTheme("ace/theme/monokai");
                aceEditor.setFontSize(DEFAULT_FONT_SIZE);
            } else {
                aceEditor.setFontSize(DEFAULT_FONT_SIZE);
            }

            aceEditor.setValue(content, -1);

            let fileExtension = path.split('.').pop().toLowerCase();
            let mode = getAceMode(fileExtension);
            aceEditor.session.setMode("ace/mode/" + mode);

            document.getElementById('fontSize').value = DEFAULT_FONT_SIZE;

            showModal('editModal');
        })
        .catch(error => {
            console.error('编辑文件时出错:', error);
            alert('加载文件内容时出错: ' + error.message);
        });
}

function saveAceContent() {
    let content = aceEditor.getValue();
    let encoding = document.getElementById('encoding').value;
    document.getElementById('editContent').value = content;
    document.getElementById('editEncoding').value = encoding;
    document.getElementById('editContent').value = content;
}

function toggleSearch() {
    aceEditor.execCommand("find");
}

function setupSearchBox() {
    var searchBox = document.querySelector('.ace_search');
    if (!searchBox) return;

    searchBox.style.fontFamily = 'Arial, sans-serif';
    searchBox.style.fontSize = '14px';

    var buttons = searchBox.querySelectorAll('.ace_button');
    buttons.forEach(function(button) {
        button.style.padding = '4px 8px';
        button.style.marginLeft = '5px';
    });

    var inputs = searchBox.querySelectorAll('input');
    inputs.forEach(function(input) {
        input.style.padding = '4px';
        input.style.marginRight = '5px';
    });
}

function saveAceContent() {
    let content = aceEditor.getValue();
    let encoding = document.getElementById('encoding').value;
    document.getElementById('editContent').value = content;

    let encodingField = document.createElement('input');
    encodingField.type = 'hidden';
    encodingField.name = 'encoding';
    encodingField.value = encoding;
    document.getElementById('editModal').querySelector('form').appendChild(encodingField);
    document.getElementById('editModal').querySelector('form').submit();

}

function openAceEditor() {
    closeModal('editModal');
    document.getElementById('aceEditor').style.display = 'block';
    let content = document.getElementById('editContent').value;

    let fileExtension = document.getElementById('editPath').value.split('.').pop().toLowerCase();
    let mode = getAceMode(fileExtension);
    let session = aceEditor.getSession();
    session.setMode("ace/mode/" + mode);

    aceEditor.setOptions({
        enableBasicAutocompletion: true,
        enableLiveAutocompletion: true,
        enableSnippets: true
    });

    document.getElementById('validateJSONBtn').style.display = (mode === 'json') ? 'inline-block' : 'none';
    document.getElementById('validateYAMLBtn').style.display = (mode === 'yaml') ? 'inline-block' : 'none';

    if (mode === 'yaml') {
        session.setTabSize(2);
        session.setUseSoftTabs(true);
    }

    if (mode === 'json' || mode === 'yaml') {
        session.setOption("useWorker", false);
        if (session.$customWorker) {
            session.$customWorker.terminate();
        }
        session.$customWorker = createCustomWorker(session, mode);
        session.on("change", function() {
            session.$customWorker.postMessage({
                content: session.getValue(),
                mode: mode
            });
        });
        
        setupCustomIndent(session, mode);
    }
    setupCustomCompletion(session, mode);

    let savedTheme = localStorage.getItem('preferredAceTheme');
    if (savedTheme) {
        aceEditor.setTheme(savedTheme);
        document.getElementById('editorTheme').value = savedTheme;
    }

    aceEditor.setOptions({
        enableBasicAutocompletion: true,
        enableLiveAutocompletion: true,
        enableSnippets: true,
        showFoldWidgets: true,
        foldStyle: 'markbegin'
    });

    aceEditor.on("changeSelection", function() {
        setupSearchBox();
    });
    
    if (!aceEditor) {
        aceEditor = ace.edit("aceEditorContainer");
        aceEditor.setTheme("ace/theme/monokai");
        
        aceEditor.getSession().selection.on('changeCursor', updateCursorPosition);
        aceEditor.getSession().on('change', updateCharacterCount);
    }
    
    aceEditor.setValue(content, -1);
    aceEditor.resize();
    aceEditor.setFontSize(DEFAULT_FONT_SIZE);
    document.getElementById('fontSize').value = DEFAULT_FONT_SIZE;
    aceEditor.focus();
    
    updateCursorPosition();
    updateCharacterCount();
    
    if (!document.getElementById('editorStatusBar')) {
        const statusBar = document.createElement('div');
        statusBar.id = 'editorStatusBar';
        statusBar.innerHTML = `
            <span id="cursorPosition">行: 1, 列: 1</span>
            <span id="characterCount">字符数: 0</span>
        `;
        document.getElementById('aceEditor').appendChild(statusBar);
    }
}

function updateCharacterCount() {
    var characterCount = aceEditor.getValue().length;
    document.getElementById('characterCount').textContent = '字符数: ' + characterCount;
}

editor.on("change", function() {
    updateCursorPosition();
});

function updateCursorPosition() {
    var cursorPosition = aceEditor.getCursorPosition();
    document.getElementById('cursorPosition').textContent = '行: ' + (cursorPosition.row + 1) + ', 列: ' + (cursorPosition.column + 1);
}


aceEditor.getSession().on('change', updateCharacterCount);


aceEditor.getSession().selection.on('changeCursor', updateCursorPosition);

function validateJSON() {
    const editor = aceEditor;
    const content = editor.getValue();
    try {
        JSON.parse(content);
        alert('JSON 格式有效');
    } catch (e) {
        alert('无效的 JSON 格式: ' + e.message);
    }
}

function addErrorMarker(session, line, message) {
    var Range = ace.require("ace/range").Range;
    var marker = session.addMarker(new Range(line, 0, line, 1), "ace_error-marker", "fullLine");
    session.setAnnotations([{
        row: line,
        type: "error",
        text: message
    }]);
    return marker;
}

function closeAceEditor() {
    if (confirm('确定要关闭编辑器吗？请确保已保存更改。')) {
        document.getElementById('editContent').value = aceEditor.getValue();
        document.getElementById('aceEditor').style.display = 'none';
        showModal('editModal');
    }
}

function showRenameModal(oldName, oldPath) {
    document.getElementById('oldPath').value = oldPath;
    document.getElementById('newPath').value = oldName;
    
    const input = document.getElementById('newPath');
    const lastDotIndex = oldName.lastIndexOf('.');
    if(lastDotIndex > 0) {
        setTimeout(() => {
            input.setSelectionRange(0, lastDotIndex);
            input.focus();
        }, 100);
    } else {
        setTimeout(() => {
            input.select();
            input.focus();
        }, 100);
    }
    
    showModal('renameModal');
}

function validateRename() {
    const newPath = document.getElementById('newPath').value.trim();
    if (newPath === '') {
        alert('新名称不能为空');
        return false;
    }
    
    const invalidChars = /[<>:"/\\|?*]/g;
    if (invalidChars.test(newPath)) {
        alert('文件名不能包含以下字符: < > : " / \\ | ? *');
        return false;
    }
    
    return true;
}

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-beautify.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-spellcheck.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<style>
.upload-container { margin-bottom: 20px; }
.upload-area { margin-top: 10px; }
.upload-drop-zone { border: 2px dashed #ccc; border-radius: 8px; padding: 25px; text-align: center; background: #f8f9fa; transition: all 0.3s ease; cursor: pointer; min-height: 150px; display: flex; align-items: center; justify-content: center; }
.upload-drop-zone.drag-over { background: #e9ecef; border-color: #0d6efd; }
.upload-icon { font-size: 50px; color: #6c757d; transition: all 0.3s ease; }
.upload-drop-zone:hover .upload-icon { color: #0d6efd; transform: scale(1.1); }
body.dark-mode .upload-drop-zone { background: #2d3238; border-color: #495057; }
body.dark-mode .upload-drop-zone.drag-over { background: #343a40; border-color: #0d6efd; }
body.dark-mode .upload-icon { color: #adb5bd; }
body.dark-mode .upload-drop-zone:hover .upload-icon { color: #0d6efd; }
#searchModal{z-index:1060 !important;}.modal-backdrop{z-index:1050 !important;}.modal-content{background-color:var(--bs-body-bg);color:var(--bs-body-color);}#searchModal .modal-dialog{max-width:90% !important;width:800px !important;}#searchResults{max-height:400px;overflow-y:auto;}#searchResults .list-group-item{display:flex;justify-content:space-between;align-items:center;}#searchResults .list-group-item span{word-break:break-all;margin-right:10px;}#aceEditor{position:fixed;top:0;right:0;bottom:0;left:0;z-index:1000;}#aceEditorContainer{position:absolute;top:0;right:0;bottom:30px;left:0;}#editorStatusBar{position:absolute;bottom:0;left:0;right:0;height:30px;background-color:#f0f0f0;padding:5px 10px;font-size:12px;display:flex;justify-content:space-between;align-items:center;}body.dark-mode #editorStatusBar{background-color:#2d3238;color:#e0e0e0;}.ace_search{background-color:#f8f9fa;border:1px solid #ced4da;border-radius:4px;padding:10px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}.ace_search_form,.ace_replace_form{display:flex;align-items:center;margin-bottom:5px;}.ace_search_field{flex-grow:1;border:1px solid #ced4da;border-radius:4px;padding:4px;}.ace_searchbtn,.ace_replacebtn{background-color:#007bff;color:white;border:none;border-radius:4px;padding:4px 8px;margin-left:5px;cursor:pointer;}.ace_searchbtn:hover,.ace_replacebtn:hover{background-color:#0056b3;}.ace_search_options{margin-top:5px;}.ace_button{background-color:#6c757d;color:white;border:none;border-radius:4px;padding:4px 8px;margin-right:5px;cursor:pointer;}.ace_button:hover{background-color:#5a6268;}body.dark-mode .ace_search{background-color:#2d3238;border-color:#495057;}body.dark-mode .ace_search_field{background-color:#343a40;color:#f8f9fa;border-color:#495057;}body.dark-mode .ace_searchbtn,body.dark-mode .ace_replacebtn{background-color:#0056b3;}body.dark-mode .ace_searchbtn:hover,body.dark-mode .ace_replacebtn:hover{background-color:#004494;}body.dark-mode .ace_button{background-color:#495057;}body.dark-mode .ace_button:hover{background-color:#3d4349;}#aceEditor .btn:hover{background-color:#4682b4;transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.15);}#aceEditor .btn:focus{outline:none;}#aceEditor{color:#333;}#aceEditor{position:fixed;top:0;right:0;bottom:0;left:0;z-index:1000;display:none}#aceEditorContainer{position:absolute;top:40px;right:0;bottom:40px;left:0;overflow-x:auto}#editorStatusBar{position:absolute;left:50%;transform:translateX(-50%);bottom:0;height:30px;background-color:#000;color:#fff;display:flex;align-items:center;padding:0 10px;font-size:14px;z-index:1001;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}#editorControls{position:absolute;left:0;right:0;top:0;height:40px;background-color:#000;color:#fff;display:flex;justify-content:center;align-items:center;padding:0 10px;overflow-x:auto}#editorControls select,#editorControls button{margin:0 10px;height:30px;padding:5px 10px;font-size:12px;background-color:#000;color:#fff;border:none;display:flex;justify-content:center;align-items:center}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const saveLanguageBtn = document.getElementById('saveLanguage');
    const pageTitle = document.getElementById('pageTitle');
    const uploadBtn = document.getElementById('uploadBtn');

const translations = {
    zh: {
        pageTitle: "NeKoBox文件助手",
        uploadBtn: "上传文件",
        rootDirectory: "根目录",
        name: "名称",
        type: "类型",
        size: "大小",
        permissions: "权限",
        actions: "操作",
        directory: "目录",
        file: "文件",
        rename: "✏️ 重命名",
        edit: "📝 编辑",
        download: "📥 下载",
        delete: "🗑️ 删除",
        confirmDelete: "确定要删除 {0} 吗？这个操作不可撤销。",
        newName: "新名称:",
        close: "关闭",
        setPermissions: "🔒 设置权限",
        saveLanguage: "保存语言设置",
        languageSaved: "语言设置已保存",
        modifiedTime: "修改时间",
        owner: "拥有者",
        create: "新建",
        newFolder: "新建文件夹",
        newFile: "新建文件",
        folderName: "文件夹名称:",
        fileName: "文件名称:",
        search: "搜索",
        searchFiles: "搜索文件",
        noMatchingFiles: "没有找到匹配的文件。",
        moveTo: "移至",
        cancel: "取消",
        confirm: "确认",
        goBack: "返回上一级",
        refreshDirectory: "刷新目录内容",
        switchTheme: "切换主题",
        lightMode: "浅色模式",
        darkMode: "深色模式",
        filePreview: "文件预览",
        unableToLoadImage: "无法加载图片:",
        unableToLoadSVG: "无法加载SVG文件:",
        unableToLoadAudio: "无法加载音频:",
        unableToLoadVideo: "无法加载视频:",
        home: "首页",
        mihomo: "Mihomo",
        singBox: "Sing-box",
        convert: "订阅转换",
        fileAssistant: "文件助手",
        errorSavingFile: "错误: 无法保存文件。",
        uploadFailed: "上传失败",
        fileNotExistOrNotReadable: "文件不存在或不可读。",
        inputFileName: "输入文件名",
        search: "搜索",
        permissionValue: "权限值（例如：0644）",
        inputThreeOrFourDigits: "输入三位或四位数字，例如：0644 或 0755",
        fontSizeL: "字体大小",
        encodingL: "编码",
        confirmCloseEditor: "确定要关闭编辑器吗？请确保已保存更改。",
        newNameCannotBeEmpty: "新名称不能为空",
        fileNameCannotContainChars: "文件名不能包含以下字符: < > : \" / \\ | ? *",
        folderNameCannotBeEmpty: "文件夹名称不能为空",
        fileNameCannotBeEmpty: "文件名称不能为空",
        searchError: "搜索时出错: ",
        encodingChanged: "编码已更改为 {0}。实际转换将在保存时在服务器端进行。",
        errorLoadingFileContent: "加载文件内容时出错: ",
        inputValidPermissionValue: "请输入有效的权限值（三位或四位八进制数字，例如：644 或 0755）",
        permissionValueCannotExceed: "权限值不能超过 0777",
        goBackTitle: "返回上一级",
        rootDirectoryTitle: "返回根目录",
        homeDirectoryTitle: "返回主目录",
        refreshDirectoryTitle: "刷新目录内容",
        selectAll: "全选",
        invertSelection: "反选",
        deleteSelected: "删除所选",
        searchTitle: "搜索",
        createTitle: "新建",
        uploadTitle: "上传",
        searchInputPlaceholder: "输入文件名",
        moveTo: "移至",
        confirmRename: "确认重命名",
        create: "创建",
        confirmChange: "确认修改",
        themeToggleTitle: "切换主题",
        editFile: "编辑文件",
        save: "保存",
        advancedEdit: "高级编辑",
        line: "行",
        column: "列",
        characterCount: "字符数",
        fontSizeL: "字体大小",
        encodingL: "编码",
        gbk: "GBK (简体中文)",
        big5: "Big5 (繁体中文)",
        shiftJIS: "Shift_JIS (日文)",
        eucKR: "EUC-KR (韩文)",
        search: "搜索",
        format: "格式化",
        validateJSON: "验证 JSON",
        validateYAML: "验证 YAML",
        close: "关闭"
    },
    en: {
        pageTitle: "NeKoBox File Assistant",
        uploadBtn: "Upload File",
        rootDirectory: "root",
        name: "Name",
        type: "Type",
        size: "Size",
        permissions: "Permissions",
        actions: "Actions",
        directory: "Directory",
        file: "File",
        rename: "✏️ Rename",
        edit: "📝 Edit",
        download: "📥 Download",
        delete: "🗑️ Delete",
        confirmDelete: "Are you sure you want to delete {0}? This action cannot be undone.",
        newName: "New name:",
        close: "Close",
        setPermissions: "🔒 Set Permissions",
        saveLanguage: "Save Language Setting",
        languageSaved: "Language setting has been saved",
        modifiedTime: "Modified Time",
        owner: "Owner",
        create: "Create",
        newFolder: "New Folder",
        newFile: "New File",
        folderName: "Folder name:",
        fileName: "File name:",
        search: "Search",
        searchFiles: "Search Files",
        noMatchingFiles: "No matching files found.",
        moveTo: "Move to",
        cancel: "Cancel",
        confirm: "Confirm",
        goBack: "Go Back",
        refreshDirectory: "Refresh Directory",
        switchTheme: "Switch Theme",
        lightMode: "Light Mode",
        darkMode: "Dark Mode",
        filePreview: "File Preview",
        unableToLoadImage: "Unable to load image:",
        unableToLoadSVG: "Unable to load SVG file:",
        unableToLoadAudio: "Unable to load audio:",
        unableToLoadVideo: "Unable to load video:",
        home: "Home",
        mihomo: "Mihomo",
        singBox: "Sing-box",
        convert: "Convert",
        fileAssistant: "File Assistant",
        errorSavingFile: "Error: Unable to save file.",
        uploadFailed: "Upload failed",
        fileNotExistOrNotReadable: "File does not exist or is not readable.",
        inputFileName: "Input file name",
        search: "Search",
        permissionValue: "Permission value (e.g.: 0644)",
        inputThreeOrFourDigits: "Enter three or four digits, e.g.: 0644 or 0755",
        fontSizeL: "Font Size",
        encodingL: "Encoding",
        save: "Save",
        closeL: "Close",
        confirmCloseEditor: "Are you sure you want to close the editor? Please make sure you have saved your changes.",
        newNameCannotBeEmpty: "New name cannot be empty",
        fileNameCannotContainChars: "File name cannot contain the following characters: < > : \" / \\ | ? *",
        folderNameCannotBeEmpty: "Folder name cannot be empty",
        fileNameCannotBeEmpty: "File name cannot be empty",
        searchError: "Error searching: ",
        encodingChanged: "Encoding changed to {0}. Actual conversion will be done on the server side when saving.",
        errorLoadingFileContent: "Error loading file content: ",
        inputValidPermissionValue: "Please enter a valid permission value (three or four octal digits, e.g.: 644 or 0755)",
        permissionValueCannotExceed: "Permission value cannot exceed 0777",
        goBackTitle: "Go Back",
        rootDirectoryTitle: "Return to Root Directory",
        homeDirectoryTitle: "Return to Home Directory",
        refreshDirectoryTitle: "Refresh Directory Content",
        selectAll: "Select All",
        invertSelection: "Invert Selection",
        deleteSelected: "Delete Selected",
        searchTitle: "Search",
        createTitle: "Create New",
        uploadTitle: "Upload",
        searchInputPlaceholder: "Enter file name",
        confirmRename: "Confirm Rename",
        create: "Create",
        moveTo: "Move to",
        confirmChange: "Confirm Change",
        themeToggleTitle: "Toggle Theme",
        editFile: "Edit File",
        save: "Save",
        advancedEdit: "Advanced Edit",
        line: "Line",
        column: "Column",
        characterCount: "Character Count",
        fontSizeL: "Font Size",
        encodingL: "Encoding",
        gbk: "GBK (Simplified Chinese)",
        big5: "Big5 (Traditional Chinese)",
        shiftJIS: "Shift_JIS (Japanese)",
        eucKR: "EUC-KR (Korean)",
        search: "Search",
        format: "Format",
        validateJSON: "Validate JSON",
        validateYAML: "Validate YAML",
        close: "Close"
    }
};
    let currentLang = localStorage.getItem('preferred_language') || 'en';

function updateLanguage(lang) {
    document.documentElement.lang = lang;
    pageTitle.textContent = translations[lang].pageTitle;
    uploadBtn.title = translations[lang].uploadBtn;

    document.querySelectorAll('th').forEach((th) => {
        const key = th.getAttribute('data-translate');
        if (key && translations[lang][key]) {
            th.textContent = translations[lang][key];
        }
    });

    document.querySelectorAll('[data-translate], [data-translate-title], [data-translate-placeholder]').forEach(el => {
        const translateKey = el.getAttribute('data-translate');
        const titleKey = el.getAttribute('data-translate-title');
        const placeholderKey = el.getAttribute('data-translate-placeholder');

        if (translateKey && translations[lang][translateKey]) {
            if (el.tagName === 'INPUT' && el.type === 'text') {
                el.placeholder = translations[lang][translateKey];
            } else {
                el.textContent = translations[lang][translateKey];
            }
        }

        if (titleKey && translations[lang][titleKey]) {
            el.title = translations[lang][titleKey];
        }

        if (placeholderKey && translations[lang][placeholderKey]) {
            el.placeholder = translations[lang][placeholderKey];
        }
    });

    document.querySelector('.breadcrumb a').textContent = translations[lang].rootDirectory;
    document.querySelector('#renameModal h2').textContent = translations[lang].rename;
    document.querySelector('#editModal h2').textContent = translations[lang].edit;
    document.querySelector('#chmodModal h2').textContent = translations[lang].setPermissions;

    document.getElementById('languageSwitcher').value = lang;
}

updateLanguage(currentLang);

    document.getElementById('languageSwitcher').addEventListener('change', function() {
        currentLang = this.value;
        updateLanguage(currentLang);
        localStorage.setItem('preferred_language', currentLang);
    });

    window.confirmDelete = function(name) {
        return confirm(translations[currentLang].confirmDelete.replace('{0}', name));
    }

    window.showRenameModal = function(oldName, oldPath) {
        document.getElementById('oldPath').value = oldPath;
        document.getElementById('newPath').value = oldName;
        document.querySelector('#renameModal label').textContent = translations[currentLang].newName;
        showModal('renameModal');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
        document.getElementById('searchForm').addEventListener('submit', searchFiles);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('drag-over');
    }

    function unhighlight(e) {
        dropZone.classList.remove('drag-over');
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            fileInput.files = files;
            uploadForm.submit();
        }
    }

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            uploadForm.submit();
        }
    });

    dropZone.addEventListener('click', function() {
        fileInput.click();
    });
});

function showUploadArea() {
    document.getElementById('uploadArea').style.display = 'block';
}

function hideUploadArea() {
    document.getElementById('uploadArea').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', (event) => {
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    const icon = themeToggle.querySelector('i');

    const currentTheme = localStorage.getItem('theme');
    if (currentTheme) {
        body.classList.add(currentTheme);
        if (currentTheme === 'dark-mode') {
            icon.classList.replace('fa-moon', 'fa-sun');
        }
    }

    themeToggle.addEventListener('click', () => {
        if (body.classList.contains('dark-mode')) {
            body.classList.remove('dark-mode');
            icon.classList.replace('fa-sun', 'fa-moon');
            localStorage.setItem('theme', 'light-mode');
        } else {
            body.classList.add('dark-mode');
            icon.classList.replace('fa-moon', 'fa-sun');
            localStorage.setItem('theme', 'dark-mode');
        }
    });
});

if (body.classList.contains('dark-mode')) {
    icon.classList.replace('fa-moon', 'fa-sun');
    themeToggle.style.color = '#fff';
} else {
    icon.classList.replace('fa-sun', 'fa-moon');
    themeToggle.style.color = '#333';
}

function previewFile(path, extension) {
    const previewContainer = document.getElementById('previewContainer');
    previewContainer.innerHTML = '';
    
    let cleanPath = path.replace(/\/+/g, '/');
    if (cleanPath.startsWith('/')) {
        cleanPath = cleanPath.substring(1);
    }
    
    const fullPath = `?preview=1&path=${encodeURIComponent(path)}`;
    console.log('Original path:', path);
    console.log('Cleaned path:', cleanPath);
    console.log('Full path:', fullPath);
    
    switch(extension.toLowerCase()) {
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            const img = document.createElement('img');
            img.src = fullPath;
            img.onerror = function() {
                previewContainer.innerHTML = '无法加载图片: ' + cleanPath;
            };
            previewContainer.appendChild(img);
            break;
            
        case 'svg':
            fetch(fullPath)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.text();
                })
                .then(svgContent => {
                    previewContainer.innerHTML = svgContent;
                })
                .catch(error => {
                    previewContainer.innerHTML = '无法加载SVG文件: ' + error.message;
                    console.error('加载SVG失败:', error);
                });
            break;
            
        case 'mp3':
            const audio = document.createElement('audio');
            audio.controls = true;
            audio.src = fullPath;
            audio.onerror = function() {
                previewContainer.innerHTML = '无法加载音频: ' + cleanPath;
            };
            previewContainer.appendChild(audio);
            break;
            
        case 'mp4':
            const video = document.createElement('video');
            video.controls = true;
            video.style.maxWidth = '100%';
            video.src = fullPath;
            video.onerror = function() {
                previewContainer.innerHTML = '无法加载视频: ' + cleanPath;
            };
            previewContainer.appendChild(video);
            break;
    }
    
    showModal('previewModal');
}

function setupCustomIndent(session, mode) {
    session.setTabSize(2);
    session.setUseSoftTabs(true);
    session.on("change", function(delta) {
        if (delta.action === "insert" && delta.lines.length === 1 && delta.lines[0] === "") {
            var cursor = session.selection.getCursor();
            var line = session.getLine(cursor.row - 1);
            var indent = line.match(/^\s*/)[0];

            if (mode === 'yaml') {
                if (line.trim().endsWith(':')) {
                    indent += "  ";
                } else if (line.trim().startsWith('- ')) {
                    indent = line.match(/^\s*/)[0];
                }
            } else if (mode === 'json') {
                if (line.trim().endsWith('{') || line.trim().endsWith('[')) {
                    indent += "  ";
                }
            }

            session.insert({row: cursor.row, column: 0}, indent);
        }
    });
}

function setupCustomCompletion(session, mode) {
    var langTools = ace.require("ace/ext/language_tools");
    var customCompleter = {
        getCompletions: function(editor, session, pos, prefix, callback) {
            var line = session.getLine(pos.row);
            var completions = [];

            if (mode === 'json') {
                if (line.trim().length === 0 || line.trim().endsWith(',')) {
                    completions = [
                        {caption: "\"\":", snippet: "\"${1:key}\": ${2:value}", meta: "key-value pair"},
                        {caption: "{}", snippet: "{\n  $0\n}", meta: "object"},
                        {caption: "[]", snippet: "[\n  $0\n]", meta: "array"}
                    ];
                }
            } else if (mode === 'yaml') {
                if (line.trim().length === 0) {
                    completions = [
                        {caption: "key:", snippet: "${1:key}: ${2:value}", meta: "key-value pair"},
                        {caption: "- ", snippet: "- ${1:item}", meta: "list item"},
                        {caption: "---", snippet: "---\n$0", meta: "document start"}
                    ];
                }
            }

            callback(null, completions);
        }
    };

    langTools.addCompleter(customCompleter);
}

function createJsonWorker(session) {
    var worker = new Worker(URL.createObjectURL(new Blob([`
        self.onmessage = function(e) {
            var value = e.data;
            try {
                JSON.parse(value);
                self.postMessage({
                    isValid: true
                });
            } catch (e) {
                var match = e.message.match(/at position (\\d+)/);
                var pos = match ? parseInt(match[1], 10) : 0;
                var lines = value.split(/\\n/);
                var total = 0;
                var line = 0;
                var ch;
                for (var i = 0; i < lines.length; i++) {
                    total += lines[i].length + 1;
                    if (total > pos) {
                        line = i;
                        ch = pos - (total - lines[i].length - 1);
                        break;
                    }
                }
                self.postMessage({
                    isValid: false,
                    line: line,
                    ch: ch,
                    message: e.message
                });
            }
        };
    `], { type: "text/javascript" })));

    worker.onmessage = function(e) {
        session.clearAnnotations();
        if (session.$errorMarker) {
            session.removeMarker(session.$errorMarker);
        }
        if (!e.data.isValid) {
            session.$errorMarker = addErrorMarker(session, e.data.line, e.data.message);
        }
    };

    return worker;
}

function addErrorMarker(session, line, message) {
    var Range = ace.require("ace/range").Range;
    var marker = session.addMarker(new Range(line, 0, line, 1), "ace_error-marker", "fullLine");
    session.setAnnotations([{
        row: line,
        column: 0,
        text: message,
        type: "error"
    }]);
    return marker;
}

function addErrorMarker(session, line, message) {
    var Range = ace.require("ace/range").Range;
    var marker = session.addMarker(new Range(line, 0, line, 1), "ace_error-marker", "fullLine");
    session.setAnnotations([{
        row: line,
        column: 0,
        text: message,
        type: "error"
    }]);
    return marker;
}

function createCustomWorker(session, mode) {
    var worker = new Worker(URL.createObjectURL(new Blob([`
        importScripts('https://cdnjs.cloudflare.com/ajax/libs/js-yaml/4.1.0/js-yaml.min.js');
        self.onmessage = function(e) {
            var content = e.data.content;
            var mode = e.data.mode;
            try {
                if (mode === 'json') {
                    JSON.parse(content);
                } else if (mode === 'yaml') {
                    jsyaml.load(content);
                }
                self.postMessage({
                    isValid: true
                });
            } catch (e) {
                var line = 0;
                var column = 0;
                var message = e.message;

                if (mode === 'json') {
                    var match = e.message.match(/at position (\\d+)/);
                    if (match) {
                        var position = parseInt(match[1], 10);
                        var lines = content.split('\\n');
                        var currentLength = 0;
                        for (var i = 0; i < lines.length; i++) {
                            currentLength += lines[i].length + 1; // +1 for newline
                            if (currentLength >= position) {
                                line = i;
                                column = position - (currentLength - lines[i].length - 1);
                                break;
                            }
                        }
                    }
                } else if (mode === 'yaml') {
                    if (e.mark) {
                        line = e.mark.line;
                        column = e.mark.column;
                    }
                }

                self.postMessage({
                    isValid: false,
                    line: line,
                    column: column,
                    message: message
                });
            }
        };
    `], { type: "text/javascript" })));

    worker.onmessage = function(e) {
        session.clearAnnotations();
        if (session.$errorMarker) {
            session.removeMarker(session.$errorMarker);
        }
        if (!e.data.isValid) {
            session.$errorMarker = addErrorMarker(session, e.data.line, e.data.column, e.data.message);
        }
    };

    return worker;
}

function formatCode() {
    const editor = aceEditor;
    const session = editor.getSession();
    const cursorPosition = editor.getCursorPosition();
    
    let content = editor.getValue();
    let formatted;
    
    const mode = session.getMode().$id;
    
    try {
        if (mode.includes('javascript')) {
            formatted = js_beautify(content, {
                indent_size: 2,
                space_in_empty_paren: true
            });
        } else if (mode.includes('json')) {
            JSON.parse(content); 
            formatted = JSON.stringify(JSON.parse(content), null, 2);
        } else if (mode.includes('yaml')) {
            const obj = jsyaml.load(content); 
            formatted = jsyaml.dump(obj, {
                indent: 2,
                lineWidth: -1,
                noRefs: true,
                sortKeys: false
            });
        } else {
            formatted = js_beautify(content, {
                indent_size: 2,
                space_in_empty_paren: true
            });
        }

        editor.setValue(formatted);
        editor.clearSelection();
        editor.moveCursorToPosition(cursorPosition);
        editor.focus();

        session.clearAnnotations();
        if (session.$errorMarker) {
            session.removeMarker(session.$errorMarker);
        }

        showNotification('代码已成功格式化', 'success');

    } catch (e) {
        let errorMessage;
        if (mode.includes('json')) {
            errorMessage = '无法格式化：无效的 JSON 格式';
        } else if (mode.includes('yaml')) {
            errorMessage = '无法格式化：无效的 YAML 格式';
        } else {
            errorMessage = '格式化时发生错误：' + e.message;
        }
        showNotification(errorMessage, 'error');

        if (e.mark) {
            session.$errorMarker = addErrorMarker(session, e.mark.line, e.message);
        }
    }
}

function addErrorMarker(session, line, column, message) {
    var Range = ace.require("ace/range").Range;
    var marker = session.addMarker(new Range(line, 0, line, 1), "ace_error-marker", "fullLine");
    session.setAnnotations([{
        row: line,
        column: column,
        text: message,
        type: "error"
    }]);
    return marker;
}

function showNotification(message, type) {
    if (type === 'error') {
        alert('错误: ' + message);
    } else {
        alert(message);
    }
}

document.getElementById('selectAllCheckbox').addEventListener('change', function() {
    var checkboxes = document.getElementsByClassName('file-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = this.checked;
    }
});

function selectAll() {
    var checkboxes = document.getElementsByClassName('file-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = true;
    }
    document.getElementById('selectAllCheckbox').checked = true;
}

function reverseSelection() {
    var checkboxes = document.getElementsByClassName('file-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = !checkboxes[i].checked;
    }
    updateSelectAllCheckbox();
}

function updateSelectAllCheckbox() {
    var checkboxes = document.getElementsByClassName('file-checkbox');
    var allChecked = true;
    for (var i = 0; i < checkboxes.length; i++) {
        if (!checkboxes[i].checked) {
            allChecked = false;
            break;
        }
    }
    document.getElementById('selectAllCheckbox').checked = allChecked;
}

function deleteSelected() {
    var selectedPaths = [];
    var checkboxes = document.getElementsByClassName('file-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            selectedPaths.push(checkboxes[i].dataset.path);
        }
    }

    if (selectedPaths.length === 0) {
        alert('请至少选择一个文件或文件夹进行删除。');
        return;
    }

    if (confirm('确定要删除选中的 ' + selectedPaths.length + ' 个文件或文件夹吗？这个操作不可撤销。')) {
        var form = document.createElement('form');
        form.method = 'post';
        form.style.display = 'none';

        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_selected';
        form.appendChild(actionInput);

        for (var i = 0; i < selectedPaths.length; i++) {
            var pathInput = document.createElement('input');
            pathInput.type = 'hidden';
            pathInput.name = 'selected_paths[]';
            pathInput.value = selectedPaths[i];
            form.appendChild(pathInput);
        }

        document.body.appendChild(form);
        form.submit();
    }
}

window.addEventListener("load", function() {
    aceEditor = ace.edit("aceEditorContainer");
    aceEditor.setTheme("ace/theme/monokai");
    aceEditor.setFontSize(20);

    aceEditor.getSession().selection.on('changeCursor', updateCursorPosition);
    aceEditor.getSession().on('change', updateCharacterCount);

    aceEditor.spellcheck = true;
    aceEditor.commands.addCommand({
        name: "spellcheck",
        bindKey: { win: "Ctrl-.", mac: "Command-." },
        exec: function(editor) {
            editor.execCommand("showSpellCheckDialog");
        }
    });
});

aceEditor.on("spell_check", function(errors) {
    errors.forEach(function(error) {
        var Range = ace.require("ace/range").Range;
        var marker = aceEditor.getSession().addMarker(
            new Range(error.line, error.column, error.line, error.column + error.length),
            "ace_error-marker",
            "typo"
        );
        aceEditor.getSession().setAnnotations([{
            row: error.line,
            column: error.column,
            text: error.message,
            type: "error"
        }]);

        var suggestions = error.suggestions;
        if (suggestions.length > 0) {
            var correctSpelling = suggestions[0];
            aceEditor.getSession().replace(
                new Range(error.line, error.column, error.line, error.column + error.length),
                correctSpelling
            );
        }
    });
});

</script>
<style>
#fullscreenToggle {
    position: fixed;
    top: 10px;
    right: 10px;
    z-index: 1000;
    background-color: #007bff;
    color: white;
    border: none;
    padding: 3px 10px;
    border-radius: 5px;
    cursor: pointer;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const fullscreenToggle = document.createElement('button');
    fullscreenToggle.id = 'fullscreenToggle';
    fullscreenToggle.textContent = '全屏';
    document.body.appendChild(fullscreenToggle);

    fullscreenToggle.onclick = function() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    };
});
</script>
</body>
</html>
