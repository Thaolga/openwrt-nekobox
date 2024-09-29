<?php
ob_start();
include './cfg.php';
$subscriptionPath = '/etc/neko/proxy_provider/';
$subscriptionFile = $subscriptionPath . 'subscriptions.json';
$clashFile = $subscriptionPath . 'clash_config.yaml';

$message = "";
$decodedContent = ""; 
$subscriptions = [];

if (!file_exists($subscriptionPath)) {
    mkdir($subscriptionPath, 0755, true);
}

if (!file_exists($subscriptionFile)) {
    file_put_contents($subscriptionFile, json_encode([]));
}

$subscriptions = json_decode(file_get_contents($subscriptionFile), true);
if (!$subscriptions) {
    for ($i = 0; $i < 7; $i++) {
        $subscriptions[$i] = [
            'url' => '',
            'file_name' => "subscription_{$i}.yaml",
        ];
    }
}

if (isset($_POST['update'])) {
    $index = intval($_POST['index']);
    $url = $_POST['subscription_url'] ?? '';
    $customFileName = $_POST['custom_file_name'] ?? "subscription_{$index}.yaml";

    $subscriptions[$index]['url'] = $url;
    $subscriptions[$index]['file_name'] = $customFileName;

    if (!empty($url)) {
        $finalPath = $subscriptionPath . $customFileName;
        $command = "curl -fsSL -o {$finalPath} {$url}";
        exec($command . ' 2>&1', $output, $return_var);

        if ($return_var === 0) {
            $message = "订阅链接 {$url} 更新成功！文件已保存到: {$finalPath}";
        } else {
            $message = "配置更新失败！错误信息: " . implode("\n", $output);
        }
    } else {
        $message = "第" . ($index + 1) . "个订阅链接为空！";
    }

    file_put_contents($subscriptionFile, json_encode($subscriptions));
}

?>
<!DOCTYPE html>
<html lang="zh-CN" data-bs-theme="<?php echo substr($neko_theme, 0, -4) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mihomo订阅程序</title>
    
    <link rel="icon" href="./assets/img/favicon.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet"> 
    <style>
        body.container-bg {
            color: #000;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            padding: 20px;
            margin-top: 0;
        }
        .input-group {
            width: 100%;
            max-width: 1200px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 5px 0;
            padding: 5px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .input-group label {
            margin-bottom: 0;
            font-weight: bold;
            flex: 1;
        }
        .form-control {
            background-color: #fff;
            color: #000;
            border-color: #ccc;
            padding: 5px;
            border-radius: 4px;
            flex: 2;
            margin: 0 3px;
            min-width: 140px;
        }
        .btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            flex: 1;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .form-spacing {
            margin-bottom: 10px;
        }
        .text-center {
            text-align: center;
        }
        h1, h2 {
            color: #00FF7F;
        }
    </style>
</head>
<body class="container-bg">
    <div class="container">
        <h1 class="text-center">Mihomo订阅程序</h1>
        <p class="help-text text-center">
            Mihomo订阅支持所有格式《Base64/clash格式/节点链接》   
        <div class="form-spacing"></div>
        
        <?php if (isset($message) && $message): ?>
            <p><?php echo nl2br(htmlspecialchars($message)); ?></p>
        <?php endif; ?>
        
        <?php if (isset($subscriptions) && is_array($subscriptions)): ?>
            <?php for ($i = 0; $i < count($subscriptions); $i++): ?>
                <form method="post" class="mb-3">
                    <div class="input-group">
                        <label for="subscription_url_<?php echo $i; ?>">订阅链接 <?php echo ($i + 1); ?>:</label>
                        <input type="text" name="subscription_url" id="subscription_url_<?php echo $i; ?>" value="<?php echo htmlspecialchars($subscriptions[$i]['url'] ?? ''); ?>" required class="form-control">
                        <label for="custom_file_name_<?php echo $i; ?>">自定义文件名:</label>
                        <input type="text" name="custom_file_name" id="custom_file_name_<?php echo $i; ?>" value="<?php echo htmlspecialchars($subscriptions[$i]['file_name'] ?? ''); ?>" class="form-control">
                        <input type="hidden" name="index" value="<?php echo $i; ?>">
                        <button type="submit" name="update" class="btn btn-primary">更新配置</button>
                    </div>
                </form>
            <?php endfor; ?>
        <?php else: ?>
            <p>未找到订阅信息。</p>
        <?php endif; ?>
    </div>

    <script type="text/javascript" src="./assets/js/feather.min.js"></script>
    <script type="text/javascript" src="./assets/js/jquery-2.1.3.min.js"></script>
</body>
</html>