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
            $message = "Subscription link {$url} updated successfully! File saved to: {$finalPath}";
        } else {
            $message = "Configuration update failed! Error message: " . implode("\n", $output);
        }
    } else {
        $message = "The subscription link #" . ($index + 1) . " is empty!";
    }

    file_put_contents($subscriptionFile, json_encode($subscriptions));
}

?>
<!DOCTYPE html>
<html lang="zh-EN" data-bs-theme="<?php echo substr($neko_theme, 0, -4) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mihomo Subscription Program</title>
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
            padding: 1px;
            margin-top: 0;
        }
        .input-group {
            max-width: 1200px;
            display: flex;
            align-items: center;
            justify-content: flex-start; 
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
            white-space: nowrap; 
        }
        .form-control {
            background-color: #fff;
            color: #000;
            border-color: #ccc;
            padding: 5px;
            border-radius: 4px;
            flex: 3; 
            min-width: 200px; 
            margin: 0 3px;
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
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
            margin-bottom: 1px;
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
        <h1 class="text-center">Mihomo Subscription Program</h1>
        <p class="help-text text-center">Mihomo subscription supports all formats: Base64/clash format/node link</p>
        
        <div class="form-spacing"></div>
        <?php if (isset($message) && $message): ?>
            <p><?php echo nl2br(htmlspecialchars($message)); ?></p>
        <?php endif; ?>
        <?php if (isset($subscriptions) && is_array($subscriptions)): ?>
            <?php for ($i = 0; $i < count($subscriptions); $i++): ?>
                <form method="post" class="mb-3">
                    <div class="input-group">
                        <label for="subscription_url_<?php echo $i; ?>">Subscription URL <?php echo ($i + 1); ?></label>
                        <input type="text" name="subscription_url" id="subscription_url_<?php echo $i; ?>" value="<?php echo htmlspecialchars($subscriptions[$i]['url'] ?? ''); ?>" required class="form-control">
                        <label for="custom_file_name_<?php echo $i; ?>">Custom File Name</label>
                        <input type="text" name="custom_file_name" id="custom_file_name_<?php echo $i; ?>" value="<?php echo htmlspecialchars($subscriptions[$i]['file_name'] ?? ''); ?>" class="form-control">
                        <input type="hidden" name="index" value="<?php echo $i; ?>">
                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            <?php endfor; ?>
        <?php else: ?>
            <p>No subscription information found.</p>
        <?php endif; ?>
    </div>
    <script type="text/javascript" src="./assets/js/feather.min.js"></script>
    <script type="text/javascript" src="./assets/js/jquery-2.1.3.min.js"></script>
</body>
</html>