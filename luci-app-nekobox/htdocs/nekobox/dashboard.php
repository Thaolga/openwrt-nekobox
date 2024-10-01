<?php

include './cfg.php';

$neko_cfg['ctrl_host'] = $_SERVER['SERVER_NAME'];

$command = "cat $selected_config | grep external-c | awk '{print $2}' | cut -d: -f2";
$port_output = shell_exec($command);

if ($port_output === null) {
    $neko_cfg['ctrl_port'] = 'default_port'; 
} else {
    $neko_cfg['ctrl_port'] = trim($port_output);
}

$yacd_link = $neko_cfg['ctrl_host'] . ':' . $neko_cfg['ctrl_port'] . '/ui/meta?hostname=' . $neko_cfg['ctrl_host'] . '&port=' . $neko_cfg['ctrl_port'] . '&secret=' . $neko_cfg['secret'];
$meta_link = $neko_cfg['ctrl_host'] . ':' . $neko_cfg['ctrl_port'] . '/ui/metacubexd?hostname=' . $neko_cfg['ctrl_host'] . '&port=' . $neko_cfg['ctrl_port'] . '&secret=' . $neko_cfg['secret'];
$dashboard_link = $neko_cfg['ctrl_host'] . ':' . $neko_cfg['ctrl_port'] . '/ui/dashboard?hostname=' . $neko_cfg['ctrl_host'] . '&port=' . $neko_cfg['ctrl_port'] . '&secret=' . $neko_cfg['secret'];

?>
<!doctype html>
<html lang="en" data-bs-theme="<?php echo substr($neko_theme,0,-4) ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Neko</title>
    <link rel="icon" href="./assets/img/nekobox.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <script type="text/javascript" src="./assets/js/feather.min.js"></script>
    <script type="text/javascript" src="./assets/js/jquery-2.1.3.min.js"></script>
  </head>
  <body>
<head>
    <div class="container-sm container-bg text-center callout border border-3 rounded-4 col-11">
        <div class="row">
            <a href="./" class="col btn btn-lg">🏠 首页</a>
            <a href="#" class="col btn btn-lg">📊 面板</a>
            <a href="./configs.php" class="col btn btn-lg">⚙️ 配置</a>
            <a href="/nekobox/mon.php" class="col btn btn-lg d-flex align-items-center justify-content-center"></i>📦 订阅</a> 
            <a href="./settings.php" class="col btn btn-lg">🛠️ 设定</a>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .text-center {
            text-align: center;
        }

        .btn-outline-info {
            height: 50px;
            line-height: 50px; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0;
            text-align: center;
            color: #fff;
            border: 3px solid;
            transition: background-color 0.3s, border-color 0.3s;
            width: 100%;
            box-sizing: border-box;
            text-decoration: none;
        }

        .btn-yacd {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-yacd:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-dashboard {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-dashboard:hover {
            background-color: #1e7e34;
            border-color: #1e7e34;
        }

        .btn-meta {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-meta:hover {
            background-color: #c82333;
            border-color: #c82333;
        }

        .btn-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: stretch;
            width: 100%;
            position: relative;
            top: -15px;
            margin-top: 20px;
        }

        .btn-container a {
            margin: 5px 0;
        }

        @media (min-width: 768px) {
            .btn-container {
                flex-direction: row;
                justify-content: space-between;
            }
            .btn-container a {
                flex: 1;
                margin: 0 5px;
            }
        }

        iframe {
            border: 3px solid #ddd;
            width: 100%;
            height: auto;
            min-height: 700px;
        }

        footer {
            margin-top: 20px;
        }
    </style>
</head>
<body>
        <h2 class="text-center p-2">Meta面板</h2>
        <div class="container h-100 mb-5">
            <iframe class="border border-3 rounded-4 w-100" height="700" src="http://<?=$yacd_link ?>" title="yacd" allowfullscreen></iframe>
        </div>
        <div class="btn-container">
            <a class="btn btn-outline-info btn-yacd" target="_blank" href="http://<?=$yacd_link ?>">打开YACD-META面板</a>
            <a class="btn btn-outline-info btn-dashboard" target="_blank" href="http://<?=$dashboard_link ?>">打开DASHBOARD面板</a>
            <a class="btn btn-outline-info btn-meta" target="_blank" href="http://<?=$meta_link ?>">打开METACUBEXD面板</a>
        </div>
    </div>
    <footer class="text-center">
        <p><?php echo $footer ?></p>
    </footer>
</body>
</html>
