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
$zash_link = $neko_cfg['ctrl_host'] . ':' . $neko_cfg['ctrl_port'] . '/ui/zashboard?hostname=' . $neko_cfg['ctrl_host'] . '&port=' . $neko_cfg['ctrl_port'] . '&secret=' . $neko_cfg['secret'];
$meta_link = $neko_cfg['ctrl_host'] . ':' . $neko_cfg['ctrl_port'] . '/ui/metacubexd?hostname=' . $neko_cfg['ctrl_host'] . '&port=' . $neko_cfg['ctrl_port'] . '&secret=' . $neko_cfg['secret'];
$dash_link = $neko_cfg['ctrl_host'] . ':' . $neko_cfg['ctrl_port'] . '/ui/dashboard?hostname=' . $neko_cfg['ctrl_host'] . '&port=' . $neko_cfg['ctrl_port'] . '&secret=' . $neko_cfg['secret'];
?>
<!doctype html>
<html lang="en" data-bs-theme="<?php echo substr($neko_theme,0,-4) ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Nekobox</title>
    <link rel="icon" href="./assets/img/nekobox.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">
    <link href="./assets/bootstrap/bootstrap-icons.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <script type="text/javascript" src="./assets/js/feather.min.js"></script>
    <script type="text/javascript" src="./assets/js/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="./assets/js/bootstrap.min.js"></script>
    <?php include './ping.php'; ?>
    <style>
        #fullscreenToggle {
            position: fixed;  
            top: 10px;        
            right: 10px;     
            z-index: 1000;    
            padding: 5px 15px; 
            background-color: #007bff;
            color: white;     
            border: none;      
            border-radius: 5px; 
            font-size: 14px;    
            cursor: pointer;  
            transition: background-color 0.3s ease;
        }

        #fullscreenToggle:hover {
            background-color: #0056b3; 
        }

        #iframeMeta {
            transition: height 0.3s ease; 
            height: 70vh; 
        }

        body.fullscreen #iframeMeta {
            height: 100vh; 
        }

        @media (max-width: 767px) {
            #fullscreenToggle {
                display: none; 
            }
        }
    </style>
  </head>
  <body>
    <button id="fullscreenToggle" class="btn btn-primary mb-2">全屏</button>
<head>
<div class="container-sm container-bg text-center callout border border-3 rounded-4 col-11">
    <div class="row">
            <a href="./index.php" class="col btn btn-lg"><i class="bi bi-house-door"></i> 首页</a>
            <a href="./dashboard.php" class="col btn btn-lg"><i class="bi bi-bar-chart"></i> 面板</a>
            <a href="./singbox.php" class="col btn btn-lg"><i class="bi bi-box"></i> 订阅</a> 
            <a href="./settings.php" class="col btn btn-lg"><i class="bi bi-gear"></i> 设定</a>
    </div>
<div class="container text-left p-3">
        <div class="container h-100 mb-5">
            <iframe id="iframeMeta" class="border border-3 rounded-4 w-100" style="height: 70vh;" src="http://<?php echo $zash_link; ?>" title="zash" allowfullscreen></iframe>   
            <div class="mb-3 mt-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#panelModal">
                    面板设置
                </button>
            </div>
            <div class="modal fade" id="panelModal" tabindex="-1" aria-labelledby="panelModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="panelModalLabel">选择面板</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div>
                                <label for="panelSelect" class="form-label">选择面板</label>
                                <select id="panelSelect" class="form-select" onchange="changeIframe(this.value)">
                                    <option value="http://<?php echo $zash_link; ?>">ZASHBOARD 面板</option>
                                    <option value="http://<?php echo $yacd_link; ?>">YACD-META 面板</option>
                                    <option value="http://<?php echo $dash_link; ?>">DASHBOARD 面板</option>
                                    <option value="http://<?php echo $meta_link; ?>">METACUBEXD 面板</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-around mt-3">
                                <a class="btn btn-info btn-sm text-white" target="_blank" href="http://<?php echo $yacd_link; ?>">YACD-META 面板</a>
                                <a class="btn btn-info btn-sm text-white" target="_blank" href="http://<?php echo $dash_link; ?>">DASHBOARD 面板</a>
                                <a class="btn btn-info btn-sm text-white" target="_blank" href="http://<?php echo $meta_link; ?>">METACUBEXD 面板</a>
                                <a class="btn btn-info btn-sm text-white" target="_blank" href="http://<?php echo $zash_link; ?>">ZASHBOARD 面板</a>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
       </tbody>
            </table>
        </div>
    <footer class="text-center">
        <p><?php echo $footer; ?></p>
    </footer>
</div>
<script>
    const panelSelect = document.getElementById('panelSelect');
    const iframeMeta = document.getElementById('iframeMeta');
    const savedPanel = localStorage.getItem('selectedPanel');

    if (savedPanel) {
        iframeMeta.src = savedPanel; 
        panelSelect.value = savedPanel; 
    }

    panelSelect.addEventListener('change', function() {
        iframeMeta.src = panelSelect.value;          
        localStorage.setItem('selectedPanel', panelSelect.value);
    });

    document.getElementById('confirmPanelSelection').addEventListener('click', function() {
        var selectedPanel = panelSelect.value;
        iframeMeta.src = selectedPanel;
        var myModal = new bootstrap.Modal(document.getElementById('panelModal'));
        myModal.hide();
        localStorage.setItem('selectedPanel', selectedPanel);
    });
</script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const fullscreenToggle = document.getElementById('fullscreenToggle');
        const iframe = document.getElementById('iframeMeta');
        const iframeContainer = iframe.closest('div'); 
        let isFullscreen = false; 
        fullscreenToggle.addEventListener('click', function() {
            if (!isFullscreen) {
                if (iframeContainer.requestFullscreen) {
                    iframeContainer.requestFullscreen();
                } else if (iframeContainer.mozRequestFullScreen) { 
                    iframeContainer.mozRequestFullScreen();
                } else if (iframeContainer.webkitRequestFullscreen) {
                    iframeContainer.webkitRequestFullscreen();
                } else if (iframeContainer.msRequestFullscreen) {
                    iframeContainer.msRequestFullscreen();
                }
                fullscreenToggle.textContent = '退出全屏';  
                isFullscreen = true;  
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) { 
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) { 
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                fullscreenToggle.textContent = '全屏'; 
                isFullscreen = false;  
                }
            });

            document.addEventListener('fullscreenchange', function() {
                if (document.fullscreenElement) {
                    iframeMeta.style.height = '100vh';
                } else {
                    iframeMeta.style.height = '70vh';
                }
            });
        });
    </script>
  </body>
</html>