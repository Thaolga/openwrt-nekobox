<?php
include './cfg.php';
include './spectra.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate="openwrt_media_center">OpenWrt Media Center</title>
    <link rel="stylesheet" href="/spectra/css/main.css?v=1.0.2">
</head>

<div class="main-container">
    <div class="content-area" id="contentArea">
        <div class="top-bar">
            <div class="logo">
                <h1>
                    <i class="fas fa-server logo-toggle" onclick="toggleSidebar()" 
                       data-translate-tooltip="toggle_menu" style="cursor: pointer; transition: transform 0.3s;">
                    </i> 
                    <span data-translate="openwrt_media_center">OpenWrt Media Center</span>
                </h1>
            </div>
            
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-value"><?= count($media['music']) ?></span>
                    <span class="stat-label" data-translate="audio">Music</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= count($media['video']) ?></span>
                    <span class="stat-label" data-translate="video">Video</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= count($media['image']) ?></span>
                    <span class="stat-label" data-translate="image">Image</span>
                </div>
                <?php if ($diskInfo): ?>
                <div class="stat-item">
                    <span class="stat-value"><?= $diskInfo['used_percent'] ?>%</span>
                    <span class="stat-label" data-translate="disk_usage">Disk Usage</span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="actions">
                <button class="btn btn-purple" data-bs-toggle="modal" data-bs-target="#playlistModal" data-translate-tooltip="tooltip_playlist"> <i class="fas fa-list"></i> <span data-translate="playlist">Playlist</span></button>
                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#scanModal" data-translate-tooltip="full_scan_tooltip">
                    <i class="fas fa-search"></i>
                    <span data-translate="full_scan">Full Scan</span>
                </button>
                <button id="autoNextToggle" class="btn btn-primary" onclick="toggleAutoNext()">
                    <i class="fas fa-toggle-off"></i>
                    <span data-translate="auto_play">Auto Play</span>
                </button>
                <button class="btn btn-pink d-none d-sm-inline" onclick="refreshMedia()">
                    <i class="fas fa-redo"></i>
                    <span data-translate="refresh">Refresh</span>
                </button>
                <button class="btn btn-teal" onclick="toggleFullscreen()" data-translate-tooltip="toggle_fullscreen">
                    <i class="fas fa-expand"></i>
                    <span data-translate="enter_fullscreen">Fullscreen Play</span>
                </button>
            </div>
        </div>
        
        <div style="display: flex; flex: 1; overflow: hidden;">
            <div class="side-nav" id="sideNav">
                <div class="nav-section">
                    <a href="#" class="nav-item active" onclick="showSection('home')" data-translate-tooltip="home">
                        <span class="nav-icon"><i class="fas fa-home" style="color: oklch(var(--l) var(--c) var(--base-hue-7));"></i></span>
                        <span data-translate="home">Home</span>
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('music')" data-translate-tooltip="audio">
                        <span class="nav-icon"><i class="fas fa-music" style="color: oklch(var(--l) var(--c) var(--base-hue-2));"></i></span>
                        <span data-translate="audio">Music</span>
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('video')" data-translate-tooltip="video">
                        <span class="nav-icon"><i class="fas fa-video" style="color: oklch(var(--l) var(--c) var(--base-hue-3));"></i></span>
                        <span data-translate="video">Video</span>
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('image')" data-translate-tooltip="image">
                        <span class="nav-icon"><i class="fas fa-image" style="color: oklch(var(--l) var(--c) var(--base-hue-4));"></i></span>
                        <span data-translate="image">Image</span>
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('recent')" data-translate-tooltip="recent_play">
                        <span class="nav-icon"><i class="fas fa-history" style="color: oklch(var(--l) var(--c) var(--base-hue-5));"></i></span>
                        <span data-translate="recent_play">Recent Play</span>
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('files')" data-translate-tooltip="fileAssistant">
                        <span class="nav-icon"><i class="fas fa-folder" style="color: oklch(var(--l) var(--c) var(--base-hue-6));"></i></span>
                        <span data-translate="fileAssistant">File Manager</span>
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('recycle_bin')" data-translate-tooltip="recycle_bin">
                        <span class="nav-icon"><i class="fas fa-trash-restore" style="color: oklch(var(--l) var(--c) var(--base-hue-1));"></i></span>
                        <span data-translate="recycle_bin">Recycle Bin</span>
                    </a>
                </div>

                <div class="nav-section" id="fileTreeSection" style="display: none;">
                    <div class="nav-section-title" data-translate="directory_tree">Directory Tree</div>
                    <div id="directoryTree" style="max-height: 300px; overflow-y: auto;">
                    </div>
                </div>
                
                 <div class="system-status" id="systemStatus">
                     <div class="nav-section">
                         <div class="nav-section-title">
                             <span data-translate="system_status">System Status</span>
                         </div>
                         <div style="padding:15px;color:var(--text-primary);font-size:.9rem;">
                             <div id="diskInfoTitle" title="<?= __('used_space') ?> <?= formatFileSize($diskInfo['used']) ?> / <?= __('total') ?> <?= formatFileSize($diskInfo['total']) ?>" style="cursor:help;margin-bottom:10px;" title="">
                                 <div class="d-flex flex-column gap-3 mb-3">
                                     <span class="btn btn-primary btn-sm w-100 text-start">
                                         <i class="fas fa-database me-1"></i>
                                         <span data-translate="total">Total:</span><span id="diskTotal">--</span>
                                     </span>
                                     <span class="btn btn-success btn-sm w-100 text-start">
                                         <i class="fas fa-hdd me-1"></i>
                                         <span data-translate="free">Free:</span><span id="diskFree">--</span>
                                     </span>
                                 </div>
                             </div>
                             <div style="height:6px;background:#fff;border-radius:3px;margin:10px 0;overflow:hidden;">
                                 <div id="diskUsageBar" style="width:0%;height:100%;background:#4CAF50;"></div>
                             </div>
                             <div id="diskInfoFooter" title="<?= __('used_space') ?> <?= formatFileSize($diskInfo['used']) ?> / <?= __('total') ?> <?= formatFileSize($diskInfo['total']) ?>" style="cursor:help;" title="">
                                 <span data-translate="used_space">Used Space:</span> <span id="diskUsed">--</span>
                             </div>
                         </div>
                     </div>
                 </div>

                <div class="lunar-sidebar lunar-collapsible">
                    <div class="nav-section">
                        <div style="padding: 12px 15px;">
                        <div style="text-align: center;">
                            <div id="dateDisplay" style="color: #4CAF50;"></div>
                            <div id="weekDisplay" style="color: var(--text-primary); margin: 3px 0;"></div>
                            <div id="lunarDisplay" style="color: #2196F3; font-size: 1.1rem; margin: 5px 0;"></div>
                            <div id="timeDisplay" style="color: #9C27B0; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px; margin-top: 10px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <div class="resizer" id="resizer" data-translate-tooltip="drag_to_resize_sidebar"></div>          
            <div class="media-grid-container" id="gridContainer">
                <div id="homeSection" class="grid-section">
                    <div class="grid-title">
                        <i class="fas fa-home"></i>
                        <span data-translate="welcome_to_media_center">Welcome to Media Center</span>
                    </div>
                        
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="card bg-dark border-secondary h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-success mb-4" data-translate="media_statistics">Media Statistics</h5>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center" style="height: 110px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                <div class="fs-4 text-success mb-1"><?= count($media['music']) ?></div>
                                                <div class="text-white-50 small" data-translate="music_files">Music Files</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center" style="height: 110px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                <div class="fs-4 text-success mb-1"><?= count($media['video']) ?></div>
                                                <div class="text-white-50 small" data-translate="video_files">Video Files</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center" style="height: 110px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                <div class="fs-4 text-success mb-1"><?= count($media['image']) ?></div>
                                                <div class="text-white-50 small" data-translate="image_files">Image Files</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center" style="height: 110px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                <div class="fs-4 text-success mb-1">
                                                    <?= count($media['music']) + count($media['video']) + count($media['image']) ?>
                                                </div>
                                                <div class="text-white-50" data-translate="total_files">Total Files</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card bg-dark border-secondary h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-success mb-4" data-translate="quick_actions">Quick Actions</h5>
                                    <div class="row g-3">
                                        <?php
                                        $actions = [
                                            ['id' => 'music', 'icon' => 'fa-music', 'color' => 'text-success', 'label' => 'browse_music', 'count' => count($media['music'])],
                                            ['id' => 'video', 'icon' => 'fa-video', 'color' => 'text-primary', 'label' => 'browse_video', 'count' => count($media['video'])],
                                            ['id' => 'image', 'icon' => 'fa-image', 'color' => 'text-info', 'label' => 'browse_images', 'count' => count($media['image'])],
                                            ['id' => 'recent', 'icon' => 'fa-history', 'color' => 'text-warning', 'label' => 'recent_play', 'count' => !empty($recent) ? count($recent) : 0]
                                        ];
                                        foreach ($actions as $action): ?>
                                            <div class="col-6">
                                                <div class="bg-black bg-opacity-25 rounded p-3 text-center quick-action-card"
                                                    onclick="showSection('<?= $action['id'] ?>')"
                                                    style="height: 110px; display: flex; flex-direction: column; justify-content: center; align-items: center; cursor: pointer;">
                                                   <div class="mb-2">
                                                       <i class="fas <?= $action['icon'] ?> <?= $action['color'] ?> fs-4"></i>
                                                   </div>
                                                   <div class="text-white small fw-medium" data-translate="<?= $action['label'] ?>">
                                                       <?= ucfirst(str_replace('_', ' ', $action['label'])) ?>
                                                   </div>
                                                   <div class="small <?= $action['color'] ?> mt-1">
                                                       <?= $action['count'] ?> <span data-translate="items">items</span>
                                                   </div>
                                               </div>
                                           </div>
                                       <?php endforeach; ?>
                                   </div>
                               </div>
                           </div>
                       </div>

                        <div class="col-lg-6">
                            <div class="card bg-dark border-secondary h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-success mb-4" data-translate="system_status">System Status</h5>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center status-tile d-flex flex-column justify-content-center">
                                                <div class="h5 text-info mb-2 text-truncate" id="openwrtVersionDisplay">--</div>
                                                <div class="small text-white-50" data-translate="openwrt_version">OpenWrt Version</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center status-tile d-flex flex-column justify-content-center">
                                                <div class="h5 text-brown mb-2" style="color: oklch(var(--l) var(--c) var(--base-hue-1));" id="kernelVersionDisplay">--</div>
                                                <div class="small text-white-50" data-translate="kernel_version">Kernel Version</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center status-tile d-flex flex-column justify-content-center">
                                                <div class="h5 text-teal mb-2 text-truncate" style="color: oklch(var(--l) var(--c) var(--base-hue-2));" id="boardModelDisplay">--</div>
                                                <div class="small text-white-50" data-translate="board_model">Board Model</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center status-tile d-flex flex-column justify-content-center">
                                                <div class="h5 text-cyan mb-2 text-truncate" style="color: oklch(var(--l) var(--c) var(--base-hue-3));" id="cpuModelDisplay">--</div>
                                                <div class="small text-white-50" data-translate="cpu_model">CPU Model</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center status-tile d-flex flex-column justify-content-center">
                                                <div class="h5 text-cyan mb-2" style="color: oklch(var(--l) var(--c) var(--base-hue-4));" id="timezoneDisplay">--</div>
                                                <div class="small text-white-50" data-translate="timezone">Timezone</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center status-tile d-flex flex-column justify-content-center">
                                                <div class="h5 text-pink mb-2" style="color: oklch(var(--l) var(--c) var(--base-hue-5));" id="loadAvgDisplay">--</div>
                                                <div class="small text-white-50" data-translate="load_average">Load Average</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center status-tile d-flex flex-column justify-content-center">
                                                <div class="h5 text-warning mb-2" id="timeValue">--:--:--</div>
                                                <div class="small text-white-50" data-translate="system_time">System Time</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center status-tile d-flex flex-column justify-content-center">
                                                <div class="h5 text-purple mb-2" style="color: oklch(var(--l) var(--c) var(--base-hue-7));" id="uptimeDisplay">--:--:--</div>
                                                <div class="small text-white-50" data-translate="uptime">Uptime</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center" style="height: 110px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                <div class="fs-4 text-success mb-1" id="cpuUsageDisplay">--%</div>
                                                <div class="text-white-50" data-translate="cpu_usage">CPU Usage</div>
                                                <div class="small text-secondary mt-1">
                                                     <i class="fas fa-microchip me-1"></i>
                                                     <span id="cpuCoresValue">--</span> <span data-translate="cores">cores</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-black bg-opacity-25 rounded p-3 text-center" style="height: 110px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                <div class="fs-4 text-primary mb-1" id="memUsageDisplay">--%</div>
                                                <div class="text-white-50" data-translate="memory_usage">Memory Usage</div>
                                                <div class="small text-secondary mt-1">
                                                     <i class="fas fa-memory me-1"></i>
                                                     <span id="memUsedDisplay">--</span>/<span id="memTotalDisplay">--</span> MB
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                         </div>

                        <div class="col-lg-6">
                            <div class="card bg-dark border-secondary h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-success mb-4" data-translate="system_monitoring">System Monitoring</h5>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <div class="card bg-black bg-opacity-25 border-secondary h-100">
                                                <div class="card-body d-flex flex-column">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="text-white fw-medium" data-translate="cpu_usage">CPU Usage</span>
                                                        <span class="text-success fw-bold" id="cpuUsageValue">
                                                            <?= $systemInfo['cpu_usage'] ?>%
                                                        </span>
                                                    </div>
                                                    <div class="progress mb-3" style="height: 30px;">
                                                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                                             id="cpuUsageBar" 
                                                             style="width: <?= min($systemInfo['cpu_usage'], 100) ?>%">
                                                            <span class="visually-hidden">CPU Usage</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="bg-black rounded p-3 h-100">
                                                            <canvas id="cpuChartCanvas" style="width: 100%; height: 100%;"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="card bg-black bg-opacity-25 border-secondary h-100">
                                                <div class="card-body d-flex flex-column">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="text-white fw-medium" data-translate="memory_usage">Memory Usage</span>
                                                        <span class="text-primary fw-bold" id="memUsageValue">
                                                            <?= $systemInfo['mem_usage'] ?>%
                                                        </span>
                                                    </div>
                                                    <div class="progress mb-3" style="height: 30px;">
                                                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                                                             id="memUsageBar" 
                                                             style="width: <?= min($systemInfo['mem_usage'], 100) ?>%">
                                                            <span class="visually-hidden">Memory Usage</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="bg-black rounded p-3 h-100">
                                                            <canvas id="memChartCanvas" style="width: 100%; height: 100%;"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-6 col-lg-3">
                                            <div class="card bg-black bg-opacity-25 border-secondary h-100">
                                                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-3" style="min-height: 110px;">
                                                    <div class="h5 text-warning mb-2" id="cpuTempDisplay">--°C</div>
                                                    <div class="text-white-50 small" data-translate="cpu_temperature">CPU Temperature</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-lg-3">
                                            <div class="card bg-black bg-opacity-25 border-secondary h-100">
                                                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-3" style="min-height: 110px;">
                                                    <div class="h5 text-purple mb-2"style="color: #9C27B0;" id="processCountDisplay">--</div>
                                                    <div class="text-white-50 small" data-translate="running_processes">Running Processes</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-lg-3">
                                            <div class="card bg-black bg-opacity-25 border-secondary h-100">
                                                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-3" style="min-height: 110px;">
                                                    <div class="h5 text-cyan mb-2" style="color: #00BCD4;" id="cpuFreqDisplay">--</div>
                                                    <div class="text-white-50 small" data-translate="cpu_frequency">CPU Frequency</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-lg-3">
                                            <div class="card bg-black bg-opacity-25 border-secondary h-100">
                                                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-3" style="min-height: 110px;">
                                                    <div class="h5 text-pink mb-2" id="networkSpeedDisplay">0 KB/s</div>
                                                    <div class="text-white-50 small" data-translate="network_speed">Network Speed</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="musicSection" class="grid-section" style="display: none;">
                    <div class="grid-title" style="color: var(--accent-tertiary);">
                        <i class="fas fa-music"></i>
                        <span data-translate="audio">Music</span> (<?= count($media['music']) ?> <span data-translate="items">items</span>)
                    </div>
                    
                    <?php if (empty($media['music'])): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-music"></i>
                        </div>
                        <p style="margin-top: 15px;" data-translate="no_music_files_found">No music files found</p>
                    </div>
                    <?php else: ?>
                    <div class="media-grid">
                        <?php foreach ($media['music'] as $index => $item):  
                            $path = $item['path'];
                            $file = basename($path);
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $size = $item['size'];
                            $duration = $item['duration'] ?? '--:--'; 
                        ?>
                        <div class="media-item hover-playable" 
                             data-type="audio"
                             data-src="?preview=1&path=<?= urlencode($item['path']) ?>"
                             data-filename="<?= htmlspecialchars($item['safe_name']) ?>"
                             data-filesize="<?= formatFileSize($item['size']) ?>"
                             data-duration="<?= $duration ?>"
                             data-bitrate="<?= $bitrate ?>"
                             data-resolution="N/A"
                             data-ext="<?= strtoupper($item['ext']) ?>"
                             onclick="playMedia('<?= $item['safe_path'] ?>')"
                             oncontextmenu="showFileInfoModal('<?= $item['safe_path'] ?>'); return false;">
                                 <span class="media-duration"><?= $duration ?></span>
                            <div class="media-thumb"><i class="fas fa-music"></i></div>
                            <div class="media-info">
                                <div class="media-name" title="<?= htmlspecialchars($item['safe_name'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= ($index + 1) . '. ' . $item['safe_name'] ?>
                                </div>
                                <div class="media-meta">
                                    <span><?= strtoupper($item['ext']) ?></span>
                                    <span><?= formatFileSize($item['size']) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div id="videoSection" class="grid-section" style="display: none;">
                    <div class="grid-title" style="color: var(--accent-tertiary);">
                        <i class="fas fa-video"></i>
                        <span data-translate="video">Video</span> (<?= count($media['video']) ?> <span data-translate="items">items</span>)
                    </div>
                    
                    <?php if (empty($media['video'])): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <p style="margin-top: 15px;" data-translate="no_video_files_found">No video files found</p>
                    </div>
                    <?php else: ?>
                    <div class="media-grid">
                        <?php foreach ($media['video'] as $index => $item): 
                            $path = $item['path'];
                            $file = basename($path);
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $size = $item['size'];
                            $duration = $item['duration'] ?? '--:--'; 
                            $thumbnailUrl = "?action=video_thumbnail&path=" . urlencode($item['path']);
                        ?>
                        <div class="media-item hover-playable" 
                             data-type="video"
                             data-src="?preview=1&path=<?= urlencode($item['path']) ?>"
                             data-filename="<?= htmlspecialchars($item['safe_name']) ?>"
                             data-filesize="<?= formatFileSize($item['size']) ?>"
                             data-duration="<?= $duration ?>"
                             data-bitrate="<?= $bitrate ?>"
                             data-resolution="<?= $resolution ?>"
                             data-ext="<?= strtoupper($item['ext']) ?>"
                             onclick="playMedia('<?= $item['safe_path'] ?>')"
                             oncontextmenu="showFileInfoModal('<?= $item['safe_path'] ?>'); return false;">
                             <div class="media-thumb">
                                 <img src="<?= $thumbnailUrl ?>" 
                                     alt="<?= htmlspecialchars($item['safe_name']) ?>"
                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;"
                                     onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-video\'></i>';">
                                    <span class="media-duration"><?= $duration ?></span>
                             </div>
                            <div class="media-info">
                                <div class="media-name" title="<?= htmlspecialchars($item['safe_name'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= ($index + 1) . '. ' . $item['safe_name'] ?>
                                </div>
                                <div class="media-meta">
                                    <span><?= strtoupper($item['ext']) ?></span>
                                    <span><?= formatFileSize($item['size']) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div id="imageSection" class="grid-section" style="display: none;">
                    <div class="grid-title" style="color: var(--accent-tertiary);">
                        <i class="fas fa-image"></i>
                        <span data-translate="image">Image</span> (<?= count($media['image']) ?> <span data-translate="items">items</span>)
                    </div>
                    
                    <?php if (empty($media['image'])): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <p style="margin-top: 15px;" data-translate="no_image_files_found">No image files found</p>
                    </div>
                    <?php else: ?>
                    <div class="media-grid">
                        <?php foreach ($media['image'] as $index => $item): 
                            $path = $item['path'];
                            $file = basename($path);
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $size = $item['size'];                            
                        ?>
                        <div class="media-item" 
                             data-type="image"
                             data-src="?preview=1&path=<?= urlencode($item['path']) ?>"
                             data-filename="<?= htmlspecialchars($item['safe_name']) ?>"
                             data-filesize="<?= formatFileSize($item['size']) ?>"
                             data-duration="N/A"
                             data-bitrate="N/A"
                             data-resolution="<?= $resolution ?>"
                             data-ext="<?= strtoupper($item['ext']) ?>"
                             onclick="playMedia('<?= $item['safe_path'] ?>')"
                             oncontextmenu="showFileInfoModal('<?= $item['safe_path'] ?>'); return false;">
                            <div class="media-thumb">
                                <img src="?preview=1&path=<?= urlencode($item['path']) ?>" 
                                     alt="<?= $item['safe_name'] ?>"
                                     loading="lazy"
                                     onerror="handleThumbError(this)">
                            </div>
                            <div class="media-info">
                                <div class="media-name" title="<?= htmlspecialchars($item['safe_name'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= ($index + 1) . '. ' . $item['safe_name'] ?>
                                </div>
                                <div class="media-meta">
                                    <span><?= strtoupper($item['ext']) ?></span>
                                    <span><?= formatFileSize($item['size']) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div id="recycleBinSection" class="grid-section" style="display: none;">
                    <div class="grid-title" style="color: var(--accent-tertiary); display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-trash-restore" style="color: #FF9800;"></i>
                            <span data-translate="recycle_bin">Recycle Bin</span>
                            <span class="badge bg-warning ms-2" id="recycleBinCount">0</span>
                        </div>
                        
                        <div>
                            <button class="btn btn-danger" onclick="emptyRecycleBin()">
                                <i class="fas fa-trash-alt"></i>
                                <span data-translate="empty_recycle_bin">Empty Recycle Bin</span>
                            </button>
                        </div>
                    </div>

                    <div class="recycle-bin-toolbar mt-3 mb-3" style="display: flex; align-items: center; gap: 10px; padding: 10px 0;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recycleSelectAll" onchange="toggleRecycleSelectAll()">
                            <label class="form-check-label" for="recycleSelectAll" data-translate="select_all">Select All</label>
                        </div>
                        
                        <span id="recycleSelectedInfo" class="text-muted" style="display: none;"></span>
                        
                        <button class="btn btn-success btn-sm" onclick="restoreSelectedFromRecycle()" id="recycleRestoreBtn" disabled>
                            <i class="fas fa-trash-restore"></i> <span data-translate="restore">Restore</span>
                        </button>
                        
                        <button class="btn btn-danger btn-sm" onclick="deleteSelectedFromRecycle()" id="recycleDeleteBtn" disabled>
                            <i class="fas fa-trash-alt"></i> <span data-translate="delete">Delete</span>
                        </button>

                        <div class="ms-auto d-flex align-items-center gap-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="recycleBinToggle" <?php echo $RECYCLE_BIN_ENABLED ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="recycleBinToggle" data-translate="recycle_bin_enabled">Enable Recycle Bin</label>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <div class="dropdown-menu p-3" style="min-width: 200px;">
                                    <div class="mb-2">
                                        <label class="form-label small" data-translate="auto_clean_days">Auto clean after (days):</label>
                                        <input type="number" class="form-control form-control-sm" id="recycleBinDays" value="<?php echo $RECYCLE_BIN_DAYS; ?>" min="1" max="365">
                                    </div>
                                    <button class="btn btn-sm btn-primary w-100" onclick="saveRecycleBinSettings()">
                                        <i class="fas fa-save"></i> <span data-translate="save">Save</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="file-grid folder-view" id="recycleBinGrid"></div>
                    <div class="empty-state" id="recycleBinEmpty" style="display: none; margin-top: -200px;">
                        <div class="empty-icon">
                            <i class="fas fa-trash-restore"></i>
                        </div>
                        <p style="margin-top: 15px;" data-translate="recycle_bin_empty">Recycle bin is empty</p>
                    </div>
                </div>

                <div id="filesSection" class="grid-section" style="display: none;">
                    <div class="grid-title" style="color: var(--accent-tertiary); display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-folder"></i>
                        <span data-translate="fileAssistant">File Manager</span>
                    </div>

                    <button class="btn btn-purple" onclick="toggleView()" id="viewToggleBtn">
                        <i class="fas fa-edit" id="viewToggleIcon"></i>
                        <span id="viewToggleText" data-translate="editor_view">Editor View</span>
                    </button>
                </div>
                 
                    <div class="file-grid-header">
                        <div class="breadcrumb" id="breadcrumb">
                        </div>

                        <div class="editor-tabs-switcher" id="editorTabsSwitcher" style="display: none;">
                            <div class="editor-tabs-container" style="display: flex; align-items: center; gap: 5px;">
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">
                                    <i class="fas fa-file-edit"></i> <span data-translate="editing">Editing:</span>
                                </span>
                                <div class="editor-tabs-list" id="editorTabsList" style="display: flex; gap: 5px; overflow-x: auto;"></div>
                                <button class="btn btn-sm btn-secondary" onclick="toggleEditorPanel()" style="white-space: nowrap;">
                                    <i class="fas fa-chevron-down" id="editorToggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="view-toggle">
                            <button class="view-toggle-btn active" onclick="changeViewMode('grid')" data-translate-tooltip="grid_view">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button class="view-toggle-btn" onclick="changeViewMode('list')" data-translate-tooltip="list_view">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>

                    <div id="editorPanel" style="display: none; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="display: flex; flex-direction: column; height: 95%; background: var(--bg-container); border: var(--border-strong); border-radius: 8px;">
                            <div style="display: flex; overflow-x: auto; padding: 10px; border-bottom: var(--border-strong); gap: 5px;" id="editorPanelTabsNav"></div>
        
                            <div style="flex: 1; position: relative; overflow: hidden;" id="editorPanelContent">
                            </div>
                        </div>
                    </div>
                    <div class="toolbar">
                        <button class="btn btn-primary" onclick="navigateUp()">
                            <i class="fa fa-level-up-alt"></i>
                            <span data-translate="goToParentDirectoryTitle">Up</span>
                        </button>
                        <button class="btn btn-teal" onclick="refreshFiles()">
                            <i class="fas fa-redo"></i>
                            <span data-translate="refresh">Refresh</span>
                        </button>
                        <button class="btn btn-coral" onclick="deleteSelected()">
                            <i class="fas fa-trash"></i>
                            <span data-translate="batch_delete">Delete</span>
                        </button>
                        <button class="btn btn-purple" data-bs-toggle="modal" data-bs-target="#createTypeModal">
                            <i class="fas fa-folder-plus"></i>
                            <span data-translate="createTitle">New Folder</span>
                        </button>
                        <button class="btn btn-pink" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-upload"></i>
                            <span data-translate="upload">Upload</span>
                        </button>
                        <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#searchModal">
                            <i class="fas fa-search"></i>
                            <span data-translate="search">Search</span>
                        </button>
                    </div>

                      <div class="selection-toolbar-container d-none mt-3" id="selectionToolbar">
                          <div class="alert alert-secondary mb-0">
                              <div class="d-flex justify-content-between flex-column flex-sm-row align-items-center">
                                  <div class="mb-2 mb-sm-0">
                                      <button class="btn btn-outline-primary btn-sm" onclick="selectAllFiles()">
                                        <i class="fas fa-check-square me-1"></i>
                                        <span data-translate="selectAll">Select All</span>
                                      </button>
                                      <span id="selectedInfo" class="ms-2 ms-sm-3"></span>
                                  </div>
                                  <div>
                                      <button class="btn btn-danger btn-sm me-2" onclick="deleteSelected()">
                                        <i class="fas fa-trash me-1"></i>
                                        <span data-translate="batch_delete">Delete Selected Files</span>
                                      </button>
                                      <button class="btn btn-secondary btn-sm" onclick="clearSelection()">
                                        <i class="fas fa-times me-1"></i>
                                        <span data-translate="close">Close</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="file-selection-info" id="selectionInfo">
                        <span id="selectedCount">0 items selected</span>
                        <div class="selection-actions">
                            <button class="btn btn-teal" onclick="clearSelection()" style="padding: 4px 8px; font-size: 0.8rem;">
                                <i class="fas fa-times"></i>
                                <span data-translate="clear">Clear</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="file-grid folder-view" id="fileGrid">
                    </div>
                    
                    <div class="loading-files" id="loadingFiles" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span data-translate="loading_files">Loading files...</span>
                    </div>
                </div>
                
                <div id="recentSection" class="grid-section" style="display: none;">
                    <div class="grid-title">
                        <i class="fas fa-history"></i>
                        <span data-translate="recent_play">Recent Play</span>
                    </div>
                    
                    <div class="recent-list" id="recentList">
                        <?php if (empty($recent)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-play-circle"></i>
                            </div>
                            <p style="margin-top: 15px;" data-translate="no_playback_history">No playback history</p>
                        </div>
                        <?php else: ?>
                            <?php foreach (array_slice($recent, 0, 10) as $file): ?>
                            <?php
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $icon = 'fas fa-image';
                            if (in_array($ext, $TYPE_EXT['music'])) {
                                $icon = 'fas fa-music';
                            } elseif (in_array($ext, $TYPE_EXT['video'])) {
                                $icon = 'fas fa-video';
                            }
                            ?>
                            <div class="recent-item" onclick="playMedia('<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>')">
                                <div class="recent-icon">
                                    <i class="<?= $icon ?>"></i>
                                </div>
                                <div class="recent-info">
                                    <div class="recent-name"><?= htmlspecialchars(basename($file), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="recent-path"><?= htmlspecialchars(dirname($file), ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="resizer" id="playerResizer" data-translate-tooltip="drag_to_resize_player"></div>
            <div class="player-area" id="playerArea">
                <div class="player-header">
                    <div class="player-title" id="playerTitle">
                        <i class="fas fa-play"></i>
                        <span data-translate="media_player">Media Player</span>
                    </div>
                    <div class="player-actions">
                        <button class="player-btn mini-toggle-btn" onclick="toggleMiniPlayer()" data-translate-tooltip="mini_player" id="miniPlayerToggle">
                            <i class="fas fa-window-restore"></i>
                        </button>
                        <button class="player-btn" onclick="togglePictureInPicture()" data-translate-tooltip="picture_in_picture">
                            <i class="fas fa-closed-captioning"></i>
                        </button>
                        <button class="player-btn" onclick="togglePlayerFitMode()" data-translate-tooltip="aspect_ratio_tooltip" id="fitModeBtn">
                            <i class="fas fa-arrows-alt"></i>
                        </button>
                        <button class="player-btn" onclick="toggleFullscreenPlayer()" data-translate-tooltip="toggle_fullscreen">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button class="player-btn" onclick="closePlayer(); return false;" data-translate-tooltip="close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="player-content">
                    <audio id="audioPlayer" controls style="display: none;"></audio>
                    <video id="videoPlayer" controls style="display: none;"></video>
                    <img id="imageViewer" style="display: none;" />
                    <div id="playError" style="display: none; text-align: center; padding: 40px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ff9800; margin-bottom: 20px;"></i>
                        <h3 style="margin-bottom: 10px; color: var(--text-primary);" data-translate="cannot_play_media">Cannot play media file</h3>
                        <p style="color: var(--text-secondary);" data-translate="possible_reasons">Possible reasons:</p>
                        <ul style="color: var(--text-secondary); text-align: left; margin-top: 10px; padding-left: 20px;">
                            <li data-translate="reason_unsupported_format">File format not supported by browser</li>
                            <li data-translate="reason_incorrect_path">File path is incorrect</li>
                            <li data-translate="reason_server_unreachable">Server cannot access the file</li>
                        </ul>
                    </div>
                <div class="player-nav-overlay">
                    <div class="player-nav-btn prev-btn" onclick="playPreviousMedia()">
                        <i class="fas fa-chevron-left"></i>
                    </div>
                        <div class="player-nav-btn next-btn" onclick="playNextMedia()">
                            <i class="fas fa-chevron-right"></i>
                       </div>
                   </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="contextMenuOverlay" class="context-menu-overlay" style="display: none;" onclick="hideFileContextMenu()"></div>

<div id="fileContextMenu" class="context-menu context-menu-file" style="display: none;">
    <div class="d-flex px-3 pt-3 pb-2 gap-2 border-bottom border-secondary">
        <div class="d-flex flex-column align-items-center py-1 flex-fill rounded top-action-item" onclick="copyToClipboard('cut')">
            <div class="mb-1 fs-4">
                <i class="fas fa-cut" style="color:oklch(var(--l) var(--c) var(--base-hue-6));"></i>
            </div>
            <div class="fs-6 text-center" data-translate="menucut">Cut</div>
        </div>
        <div class="d-flex flex-column align-items-center py-1 flex-fill rounded top-action-item" onclick="copyToClipboard('copy')">
            <div class="mb-1 fs-4">
                <i class="fas fa-copy" style="color:oklch(var(--l) var(--c) var(--base-hue-5));"></i>
            </div>
            <div class="fs-6 text-center" data-translate="copy">Copy</div>
        </div>
        <div class="d-flex flex-column align-items-center py-1 flex-fill rounded top-action-item" data-bs-toggle="modal" data-bs-target="#renameModal" onclick="prepareRenameModal()">
            <div class="mb-1 fs-4">
                <i class="fas fa-edit" style="color:oklch(var(--l) var(--c) var(--base-hue-2));"></i>
            </div>
            <div class="fs-6 text-center" data-translate="rename">Rename</div>
        </div>
        <div class="d-flex flex-column align-items-center py-1 flex-fill rounded top-action-item" onclick="contextMenuDelete()">
            <div class="mb-1 fs-4">
                <i class="fas fa-trash" style="color:var(--color-pink);"></i>
            </div>
            <div class="fs-6 text-center" data-translate="delete">Delete</div>
        </div>
    </div>
    
    <div class="context-menu-content">
        <div class="menu-item" id="emptyNewFolderItem" style="display: none;" onclick="showCreateFolderModal()">
            <i class="fas fa-folder-plus me-2" style="color:oklch(var(--l) var(--c) var(--base-hue-1));"></i>
            <span data-translate="create_new_folder">Create a new folder</span>
        </div>
        <div class="menu-item" id="emptyNewFileItem" style="display: none;" onclick="showCreateFileModal()">
            <i class="fas fa-file-circle-plus me-2" style="color:oklch(var(--l) var(--c) var(--base-hue-2));"></i>
            <span data-translate="create_new_file">Create a new file</span>
        </div>
        <div class="menu-item" id="emptyUploadItem" style="display: none;" onclick="document.querySelector('[data-bs-target=\'#uploadModal\']')?.click()">
            <i class="fas fa-upload me-2" style="color:oklch(var(--l) var(--c) var(--base-hue-3));"></i>
            <span data-translate="upload">Upload</span>
        </div>
     
        <div class="menu-item" id="emptyRefreshItem" style="display: none;" onclick="refreshFiles()">
            <i class="fas fa-sync-alt me-2" style="color:oklch(var(--l) var(--c) var(--base-hue-4));"></i>
            <span data-translate="refresh">Refresh</span>
        </div>
        <div class="menu-item" id="emptyRestoreThemeItem" style="display: none;" onclick="clearWallpaper()">
            <i class="fas fa-undo-alt me-2" style="color: oklch(var(--l) var(--c) var(--base-hue-5));"></i>
            <span data-translate="restore_default_theme">Restore default theme</span>
        </div>
        <div class="menu-item" id="emptySelectAllItem" style="display: none;" onclick="toggleEmptySelectAll()">
            <i class="fas fa-check-square me-2" id="emptySelectAllIcon" style="color:oklch(var(--l) var(--c) var(--base-hue-5));"></i>
            <span id="emptySelectAllText">Select All</span>
        </div>
        
        <div class="menu-item" id="globalPasteItem" style="display: none;" onclick="pasteFromClipboard()">
            <i class="fas fa-paste me-2" style="color:oklch(var(--l) var(--c) var(--base-hue));"></i>
            <span data-translate="paste">Paste</span>
            <span id="pasteActionHint" style="margin-left: auto; font-size: 0.8rem; opacity: 0.7;"></span>
        </div>
        <div class="menu-divider" id="globalPasteDivider" style="display: none;"></div>
        
        <div class="menu-item" id="fileOpenItem" onclick="contextMenuOpen()">
            <i class="fas fa-folder-open me-2" style="color:oklch(var(--l) var(--c) var(--base-hue-6));"></i>
            <span data-translate="open">Open</span>
        </div>
        <div class="menu-item" id="filePlayItem" style="display: none;" onclick="contextMenuPlay()">
            <i class="fas fa-play me-2" style="color:oklch(var(--l) var(--c) var(--base-hue-7));"></i>
            <span data-translate="play">Play</span>
        </div>
        <div class="menu-item" id="fileEditItem" style="display: none;" onclick="contextMenuEdit()">
            <i class="fas fa-edit me-2" style="color:var(--lavender-bg);"></i>
            <span data-translate="edit">Edit</span>
        </div>
        <div class="menu-item" id="fileDownloadItem" onclick="contextMenuDownload()">
            <i class="fas fa-download me-2" style="color:var(--accent-tertiary);"></i>
            <span data-translate="download">Download</span>
        </div>
        <div class="menu-item" id="fileCopyPathItem" onclick="copyFilePath()">
            <i class="fas fa-link me-2" style="color: var(--rose-bg);"></i>
            <span data-translate="copy_file_path">Copy File Path</span>
        </div>
        <div class="menu-item" id="filePasteItem" style="display: none;" onclick="pasteFromClipboard()">
            <i class="fas fa-paste me-2" style="color:var(--accent-secondary);"></i>
            <span data-translate="paste">Paste</span>
            <span style="margin-left: auto; font-size: 0.8rem; opacity: 0.7;">Ctrl+V</span>
        </div>
        <div class="menu-item" id="fileBatchRenameItem" onclick="showBatchRenameDialog()">
            <i class="fas fa-i-cursor me-2" style="color:oklch(var(--l) var(--c) var(--base-hue-1))"></i>
            <span data-translate="batch_rename">Batch Rename</span>
        </div>
        <div class="menu-item" id="fileConvertItem" style="display: none;" onclick="showConvertDialog()">
            <i class="fas fa-exchange-alt me-2" style="color: oklch(var(--l) var(--c) var(--base-hue-2));"></i>
            <span data-translate="batch_convert">Batch Format Conversion</span>
        </div>       

        <div class="menu-item archive-menu" id="archiveMenuItem" onclick="toggleArchiveSubmenu(event)">
            <i class="fas fa-file-archive me-2" style="color:oklch(var(--l) var(--c) var(--base-hue-7));"></i>
            <span data-translate="archive_operations">Archive Operations</span>
            <i class="fas fa-chevron-right ms-auto"></i>
        </div>
        <div id="archiveSubmenu" class="archive-submenu" style="display: none; margin-left: 20px;">
            <div class="menu-item" id="archiveCompressItem" onclick="showCompressDialog()">
                <i class="fas fa-compress me-2"></i>
                <span data-translate="compress_to">Compress to...</span>
            </div>
            <div class="menu-item" id="archiveExtractHereItem" onclick="extractArchiveHere(fileContextMenuTarget?.getAttribute('data-path'))">
                <i class="fas fa-expand me-2"></i>
                <span data-translate="extract_here">Extract here</span>
            </div>
            <div class="menu-item" id="archiveExtractToItem" onclick="showExtractDialog()">
                <i class="fas fa-folder-open me-2"></i>
                <span data-translate="extract_to">Extract to...</span>
            </div>
        </div>
   
        <div class="menu-item" id="fileChmodItem" onclick="showChmodDialog()">
            <i class="fas fa-key me-2" style="color:oklch(var(--l) var(--c) var(--base-hue-3));"></i>
            <span data-translate="permissions">Permissions</span>
        </div>
        <div class="menu-item" id="fileSetAsWallpaperItem" style="display: none;" onclick="setAsWallpaper()">
             <i class="fas fa-image me-2" style="color: oklch(var(--l) var(--c) var(--base-hue-7));"></i>
            <span data-translate="set_as_wallpaper">Set as wallpaper</span>
        </div>
        <div class="menu-item" id="fileInstallItem" style="display: none;" onclick="showInstallDialog()">
            <i class="fas fa-box-open me-2" style="color: oklch(var(--l) var(--c) var(--base-hue-4));"></i>
            <span data-translate="install_package">Install Package</span>
            <span style="margin-left: auto; font-size: 0.8rem; opacity: 0.7;">IPK/APK/RUN</span>
        </div>
        <div class="menu-item" id="fileHashItem" style="display: none;" onclick="showFileHashDialog()">
            <i class="fas fa-fingerprint me-2" style="color:oklch(var(--l) var(--c) var(--base-hue-6));"></i>
            <span data-translate="file_hash">File Hash</span>
            <span style="margin-left: auto; font-size: 0.8rem; opacity: 0.7">MD5/SHA1/SHA256</span>
        </div>
        <div class="menu-item" id="filePropertiesItem" onclick="showFileProperties()">
            <i class="fas fa-info-circle me-2" style="color:oklch(var(--l) var(--c) var(--base-hue-5));"></i>
            <span data-translate="properties">Properties</span>
        </div>
        <div class="menu-item" id="fileQrcodeItem" data-bs-toggle="modal" data-bs-target="#qrcodeModal" onclick="prepareQrCode()">
            <i class="fas fa-qrcode me-2" style="color: var(--ocean-bg);"></i>
            <span data-translate="generate_qrcode">Generate QR Code</span>
        </div>
        <div class="menu-item" id="fileTerminalItem" onclick="openTerminal()">
            <i class="fas fa-terminal me-2" style="color:var(--forest-bg);"></i>
            <span data-translate="open_terminal">Open Terminal</span>
        </div>
    </div>
</div>

<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchModalLabel">
                    <i class="fas fa-search me-2"></i>
                    <span data-translate="searchFiles">Search Files</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" 
                               data-translate-placeholder="search_empty_input"
                               placeholder="Enter file name or use * wildcard (e.g.: *.mp3)"
                               autofocus>
                        <button class="btn btn-primary" type="button" onclick="searchFiles()">
                            <i class="fas fa-search"></i>
                            <span data-translate="search">Search</span>
                        </button>
                    </div>
                    <div class="form-text mt-1" data-translate="search_hint">
                        Tip: Use * as a wildcard (e.g., "*.mp3" to search for all MP3 files)
                    </div>
                </div>
                
                <div id="searchResults" style="max-height: 50vh; overflow-y: auto;">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p data-translate="enter_search_term">Enter a search term to start finding files</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="cancel">Cancel</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filePropertiesModal" tabindex="-1" aria-labelledby="filePropertiesModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filePropertiesModalLabel">
                    <i class="fas fa-info-circle me-2"></i>
                    <span data-translate="file_properties">File Properties</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="filePropertiesContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2" data-translate="loading">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="close">Close</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="chmodModal" tabindex="-1" aria-labelledby="chmodModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg">
    <form method="post" onsubmit="return validateChmod()" class="modal-content no-loader">
      <div class="modal-header">
        <h5 class="modal-title" id="chmodModalLabel" data-translate="setPermissions">🔒 Set Permissions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="chmod">
        <input type="hidden" name="path" id="chmodPath">

        <div class="mb-3">
          <label for="permissions" class="form-label" data-translate="permissionValue">
            Permission value (e.g.: 0644)
          </label>
          <input type="text"
                 name="permissions"
                 id="permissions"
                 class="form-control"
                 maxlength="4"
                 data-translate-placeholder="permissionPlaceholder"
                 placeholder="0644"
                 autocomplete="off">
          <div class="form-text mt-1" data-translate="permissionHelp">
            Please enter a valid permission value (three or four octal digits, e.g.: 644 or 0755)
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal"
                data-translate="cancel">Cancel</button>
        <button type="submit"
                class="btn btn-primary"
                data-translate="saveButton">Save</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="createTypeModal" tabindex="-1" aria-labelledby="createTypeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTypeModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    <span data-translate="create_new">Create New</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card text-center h-100 border-primary" 
                             style="cursor: pointer;"
                             data-bs-dismiss="modal"
                             onclick="showCreateFolderModal()">
                            <div class="card-body py-4">
                                <i class="fas fa-folder-plus fa-3x text-primary mb-3"></i>
                                <h5 class="card-title" data-translate="newFolder">Folder</h5>
                                <p class="card-text text-muted small" data-translate="create_new_folder">
                                    Create a new folder
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card text-center h-100 border-success"
                             style="cursor: pointer;"
                             data-bs-dismiss="modal"
                             onclick="showCreateFileModal()">
                            <div class="card-body py-4">
                                <i class="fas fa-file-circle-plus fa-3x text-success mb-3"></i>
                                <h5 class="card-title" data-translate="newFile">File</h5>
                                <p class="card-text text-muted small" data-translate="create_new_file">
                                    Create a new file
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="cancel">Cancel</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createFolderModalLabel">
                    <i class="fas fa-folder-plus me-2"></i>
                    <span data-translate="newFolder">Create_new_folder</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="folderNameInput" class="form-label" data-translate="folderName">Folder Name:</label>
                    <input type="text" class="form-control" id="folderNameInput" data-translate-placeholder="enter_folder_name_placeholder">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="cancel">Cancel</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="createFolder()">
                    <i class="fas fa-check me-1"></i>
                    <span data-translate="create">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createFileModal" tabindex="-1" aria-labelledby="createFileModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createFileModalLabel">
                    <i class="fas fa-file-circle-plus me-2"></i>
                    <span data-translate="newFile" =">Create File</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="fileNameInput" class="form-label" data-translate="file_name">File Name:</label>
                    <input type="text" class="form-control" id="fileNameInput" data-translate-placeholder="enter_file_name_placeholder">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="cancel">Cancel</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="createFile()">
                    <i class="fas fa-check me-1"></i>
                    <span data-translate="create">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="fas fa-upload me-2"></i>
                    <span data-translate="uploadBtn">Upload Files</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="border rounded p-5 text-center upload-drop-area" style="cursor: pointer;" onclick="document.getElementById('fileUploadInput').click()">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <h4 data-translate="click_to_select_files">Click to select files</h4>
                        <p class="text-muted" data-translate="or_drag_and_drop">or drag and drop files here</p>
                    </div>
                    <input type="file" id="fileUploadInput" class="d-none" multiple onchange="handleFileSelect(event)">
                </div>
                <div id="uploadProgressArea" style="display: none;" class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span data-translate="uploading">Uploading...</span>
                        <span id="uploadProgressPercent">0%</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div id="uploadProgressBar" 
                             class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                             role="progressbar"
                             style="width: 0%">0%</div>
                    </div>
                    <div id="uploadFileInfo" class="small text-muted mt-1"></div>
                    <div id="chunkProgressDetail" class="small text-info mt-1"></div>
                </div>
                <div id="fileList" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-teal" id="updatePhpConfig"><i class="fas fa-unlock me-1"></i><span data-translate="unlock_php_upload_limit"></span></button>
                <button type="button" class="btn btn-primary" onclick="startUpload()">
                    <i class="fas fa-upload me-1"></i>
                    <span data-translate="upload">Upload</span>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="cancel">Cancel</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renameModalLabel">
                    <i class="fas fa-edit me-2"></i>
                    <span data-translate="rename">Rename</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="renameInput" class="form-label" data-translate="newName">New Name:</label>
                    <input type="text" class="form-control" id="renameInput" 
                           placeholder="Enter new name" autofocus>
                </div>
                <div class="alert alert-info mb-0">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        <span data-translate="rename_warning">You are renaming: </span>
                        <span id="renameOriginalName" class="fw-bold"></span>
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="cancel">Cancel</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="performRename()">
                    <i class="fas fa-check me-1"></i>
                    <span data-translate="rename">Rename</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="moveModal" tabindex="-1" aria-labelledby="moveModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moveModalLabel">
                    <i class="fas fa-cut me-2"></i>
                    <span data-translate="move">Move</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="movePath" class="form-label" data-translate="destination_path">Destination Path:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-folder"></i></span>
                        <input type="text" class="form-control" id="movePath" 
                               placeholder="/path/to/destination" value="<?php echo $ROOT_DIR; ?>">
                        <button class="btn btn-outline-secondary" type="button" onclick="browseForMovePath()">
                            <i class="fas fa-folder-open"></i>
                        </button>
                    </div>
                    <div class="form-text" data-translate="move_hint">
                        Enter the destination path where you want to move the files/folders.
                    </div>
                </div>
                <div class="mb-3">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong data-translate="moving">Moving:</strong>
                        <div id="moveItemsList" class="mt-1"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="cancel">Cancel</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="performMove()">
                    <i class="fas fa-check me-1"></i>
                    <span data-translate="move">Move</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="copyModal" tabindex="-1" aria-labelledby="copyModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="copyModalLabel">
                    <i class="fas fa-copy me-2"></i>
                    <span data-translate="copy">Copy</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="copyPath" class="form-label" data-translate="destination_path">Destination Path:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-folder"></i></span>
                        <input type="text" class="form-control" id="copyPath" 
                               placeholder="/path/to/destination" value="<?php echo $ROOT_DIR; ?>">
                        <button class="btn btn-outline-secondary" type="button" onclick="browseForCopyPath()">
                            <i class="fas fa-folder-open"></i>
                        </button>
                    </div>
                    <div class="form-text" data-translate="copy_hint">
                        Enter the destination path where you want to copy the files/folders.
                    </div>
                </div>
                <div class="mb-3">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong data-translate="copying">Copying:</strong>
                        <div id="copyItemsList" class="mt-1"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="cancel">Cancel</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="performCopy()">
                    <i class="fas fa-check me-1"></i>
                    <span data-translate="copy">Copy</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="terminalModal" tabindex="-1" aria-labelledby="terminalModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="height: 80vh;">
            <div class="modal-header">
                <h5 class="modal-title" id="terminalModalLabel">
                    <i class="fas fa-terminal me-2"></i>
                    <span data-translate="terminal">Terminal</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe 
                    id="terminalIframe" 
                    src="" 
                    frameborder="0" 
                    style="width: 100%; height: 100%;">
                </iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <span data-translate="close">Close</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="compressModal" tabindex="-1" aria-labelledby="compressModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="compressModalLabel">
                    <i class="fas fa-compress me-2"></i>
                    <span data-translate="compress_files">Compress Files</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="archiveName" class="form-label">
                        <span data-translate="archive_name">Archive Name:</span>
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="archiveName" value="archive" autocomplete="off">
                        <span class="input-group-text" id="archiveExtension">.zip</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <span data-translate="archive_format">Archive Format:</span>
                    </label>
                    <div class="btn-group w-100" role="group" id="formatButtonsGroup">
                        <button type="button" class="btn btn-outline-primary active" data-format="zip">ZIP</button>
                        <button type="button" class="btn btn-outline-primary" data-format="tar">TAR</button>
                        <button type="button" class="btn btn-outline-primary" data-format="gz">GZ</button>
                        <button type="button" class="btn btn-outline-primary" data-format="bz2">BZ2</button>
                        <!-- <button type="button" class="btn btn-outline-primary" data-format="7z">7Z</button> -->
                    </div>
                </div>
                <div class="mb-3">
                    <label for="compressDestination" class="form-label">
                        <span data-translate="destination_path">Destination Path:</span>
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="compressDestination" value="" autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" onclick="browseForCompressPath()">
                            <i class="fas fa-folder-open"></i>
                        </button>
                    </div>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong data-translate="compressing">Compressing:</strong>
                    <div id="compressItemsList" class="mt-2 small">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="cancel">Cancel</span>
                </button>
                <button type="button" class="btn btn-primary" id="compressSubmitBtn" onclick="performCompress()">
                    <i class="fas fa-check me-1"></i>
                    <span data-translate="compress">Compress</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fileHashModal" tabindex="-1" aria-labelledby="fileHashModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileHashModalLabel">
                    <i class="fas fa-fingerprint text-primary me-2"></i>
                    <span data-translate="file_hash">File Hash</span>
                    <small class="text-muted ms-2" id="hashFileName"></small>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div id="hashLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mb-0" data-translate="calculating_hash">Calculating hash...</p>
                </div>
                
                <div id="hashContent" class="d-none">
                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="hashFilePath" class="fw-bold"></span>
                    </div>
                    
                    <!-- MD5 -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">
                                <i class="fas fa-hashtag text-warning me-2"></i>MD5:
                            </span>
                        </div>
                        <code id="hashMd5" class="d-block p-3 bg-light text-white rounded font-monospace"></code>
                    </div>
                    
                    <!-- SHA1 -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">
                                <i class="fas fa-shield-alt text-info me-2"></i>SHA1:
                            </span>
                        </div>
                        <code id="hashSha1" class="d-block p-3 bg-light text-white rounded font-monospace"></code>
                    </div>
                    
                    <!-- SHA256 -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">
                                <i class="fas fa-lock text-success me-2"></i>SHA256:
                            </span>
                        </div>
                        <textarea id="hashSha256" class="form-control bg-light text-white font-monospace" rows="2" readonly style="resize: none;"></textarea>
                    </div>
                    
                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-file me-1"></i>
                                <span data-translate="fileSize">Size</span>: <span id="hashFileSize"></span>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <span data-translate="modifiedTime">Modified</span>: <span id="hashFileMtime"></span>
                            </small>
                        </div>
                    </div>
                </div>
                
                <div id="hashError" class="text-center py-5 d-none">
                    <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                    <p class="text-danger mb-0" id="hashErrorMessage"></p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="exportCurrentHash()">
                    <i class="fas fa-download me-1"></i><span data-translate="export_hash">Export Hash</span>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i><span data-translate="close">Close</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="installModal" tabindex="-1" aria-labelledby="installModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="installModalLabel">
                    <i class="fas fa-box-open me-2"></i>
                    <span data-translate="install_package">Install Package</span>
                    <span id="installPackageName" class="ms-2 text-info"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="alert alert-info" id="installInfo">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="installInfoText" data-translate="install_info">Installing package...</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="installForceCheck">
                        <label class="form-check-label" for="installForceCheck" data-translate="install_force">
                            Force installation (override dependencies/overwrite)
                        </label>
                    </div>
                    <div class="form-check form-switch" id="installUpdateCheckContainer" style="display: block;">
                        <input class="form-check-input" type="checkbox" id="installUpdateCheck" checked>
                        <label class="form-check-label" for="installUpdateCheck" data-translate="install_update">
                            Update package lists before installation
                        </label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="progress" style="height: 30px;">
                        <div id="installProgress" class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%">
                            <span id="installProgressText">0%</span>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold" data-translate="install_output">Installation Output:</label>
                    <div id="installOutput" class="bg-dark text-success p-3 rounded" 
                         style="height: 300px; overflow-y: auto; font-family: monospace; font-size: 13px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="close">Close</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="batchRenameModal" tabindex="-1" aria-labelledby="batchRenameModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batchRenameModalLabel"><i class="fas fa-i-cursor me-2"></i><span data-translate="batch_rename">Batch Rename</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="card shadow-sm">
                        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-folder-open me-2"></i>
                                <span data-translate="selected_files">Selected Files</span>
                            </div>
                            <span class="badge bg-primary" id="selectedFilesCount">0</span>
                        </div>
                        <div class="card-body p-2">
                            <div id="batchRenameFileList" class="list-group list-group-flush" style="max-height:150px; overflow-y:auto;"></div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="renamePattern" class="form-label" data-translate="rename_pattern">Rename Pattern:</label>
                        <input type="text" class="form-control  mb-2" id="renamePattern" placeholder="Prefix_{n}_Suffix" value="File_{n}">
                        <small class="text-muted" data-translate="pattern_hint">Use {n} for number, {name} for original name, {ext} for extension</small>
                    </div>
                    <div class="col-md-3">
                        <label for="startNumber" class="form-label" data-translate="start_number">Start Number:</label>
                        <input type="number" class="form-control" id="startNumber" value="1" min="1" max="9999">
                    </div>
                    <div class="col-md-3">
                        <label for="numberPadding" class="form-label" data-translate="number_padding">Number Padding:</label>
                        <select class="form-select" id="numberPadding">
                            <option value="1">1</option>
                            <option value="2" selected>2 (01, 02...)</option>
                            <option value="3">3 (001, 002...)</option>
                            <option value="4">4 (0001, 0002...)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="keepOriginalName" checked>
                        <label class="form-check-label" for="keepOriginalName" data-translate="keep_original_name">Keep original name part (use {name} in pattern)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="removeSpecialChars" checked>
                        <label class="form-check-label" for="removeSpecialChars" data-translate="remove_special_chars">Remove special characters (#, spaces, emoji) from names</label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="card shadow-sm">
                        <div class="card-header fw-bold">
                            <i class="fas fa-eye me-2"></i>
                            <span data-translate="preview">Preview</span>
                        </div>
                        <div class="card-body p-2">
                            <div id="batchRenamePreview" class="list-group list-group-flush" style="max-height:200px; overflow-y:auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i><span data-translate="cancel">Cancel</span></button>
                <button type="button" class="btn btn-primary" onclick="executeBatchRename()"><i class="fas fa-check me-1"></i><span data-translate="rename">Rename</span></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="convertModal" tabindex="-1" aria-labelledby="convertModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="convertModalLabel">
                    <i class="fas fa-exchange-alt me-2 text-purple"></i>
                    <span data-translate="batch_convert">'Batch Format Conversion</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card bg-dark bg-opacity-25 border-secondary mb-3">
                    <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list me-2"></i>
                            <span data-translate="files_to_convert">Files to Convert</span>
                        </div>
                        <span class="badge bg-primary" id="convertFilesCount">0</span>
                    </div>
                    <div class="card-body p-2">
                        <div id="convertFileList" class="list-group list-group-flush" style="max-height:200px; overflow-y:auto;"></div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="convertFormat" class="form-label" data-translate="output_format">Output Format</label>
                        <select class="form-select" id="convertFormat">
                            <optgroup data-translate="audio_formats">
                                <option value="mp3">MP3</option>
                                <option value="wav">WAV</option>
                                <option value="ogg">OGG</option>
                                <option value="flac">FLAC</option>
                                <option value="aac">AAC</option>
                                <option value="m4a">M4A</option>
                                <option value="mp2">MP2</option>
                                <option value="ac3">AC3</option>
                                <option value="dts">DTS</option>
                            </optgroup>
                            <optgroup data-translate="video_formats">
                                <option value="mp4">MP4</option>
                                <option value="avi">AVI</option>
                                <option value="mkv">MKV</option>
                                <option value="mov">MOV</option>
                                <option value="webm">WEBM</option>
                                <option value="wmv">WMV</option>
                                <option value="flv">FLV</option>
                                <option value="3gp">3GP</option>
                                <option value="hevc">HEVC/H.265</option>
                                <option value="gif">GIF</option>
                            </optgroup>
                            <optgroup data-translate="image_formats">
                                <option value="jpg">JPEG</option>
                                <option value="png">PNG</option>
                                <option value="webp">WebP</option>
                                <option value="bmp">BMP</option>
                                <option value="tiff">TIFF</option>
                                <option value="ico">ICO</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="convertQuality" class="form-label" data-translate="quality">Quality / Bitrate</label>
                        <select class="form-select" id="convertQuality">
                            <option value="high" data-translate="high_quality">High Quality</option>
                            <option value="medium" selected data-translate="medium_quality">Medium Quality</option>
                            <option value="low" data-translate="low_quality">Low Quality</option>
                        </select>
                    </div>
                </div>

                <div id="convertProgressArea" style="display: none;">
                    <div class="mb-2 d-flex justify-content-between">
                        <span data-translate="converting">Converting...</span>
                        <span id="convertProgressText">0/0</span>
                    </div>
                    <div class="progress mb-3" style="height: 25px;">
                        <div id="convertProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                             role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <div id="convertLog" class="bg-dark text-success p-2 rounded" 
                         style="height: 150px; overflow-y: auto; font-family: monospace; font-size: 12px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span data-translate="cancel">Cancel</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="startConvert()">
                    <i class="fas fa-play me-1"></i>
                    <span data-translate="start_convert">Start Conversion</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="playlistModal" tabindex="-1" aria-labelledby="playlistModalLabel"  aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="playlistModalLabel">
                    <i class="fas fa-list me-2 text-success"></i>
                    <span data-translate="playlist">Playlist</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush"
                     id="playlistItems"
                     style="max-height: 60vh; overflow-y: auto;">
                </div>
            </div>
            <div class="modal-footer">
                <span class="me-auto" id="playlistCount"></span>
                <button type="button" 
                        class="btn btn-pink me-2" 
                        onclick="cleanThumbnailCache()"
                        data-translate-tooltip="clean_thumbnail_cache">
                    <i class="fas fa-trash-alt me-1"></i>
                    <span data-translate="clean_thumbnails">Clean Thumbnails</span>
                </button>
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal" data-translate="cancel">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scanModalLabel">
                    <i class="fas fa-search me-2"></i>
                    <span data-translate="full_media_scan">Full Media Scan</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card h-100 text-center border-primary">
                            <div class="card-body">
                                <i class="fas fa-music fa-3x text-primary mb-3"></i>
                                <h6 class="card-subtitle mb-2 text-muted" data-translate="audio">Music</h6>
                                <p class="card-text">
                                    <span class="badge bg-primary" id="musicCount"><?= count($media['music']) ?></span> <span data-translate="files">Files</span>
                                </p>
                                <button class="btn btn-primary w-100" onclick="performFullScan('music')" id="scanMusicBtn">
                                    <i class="fas fa-search me-1"></i>
                                    <span data-translate="scan">Scan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center border-primary">
                            <div class="card-body">
                                <i class="fas fa-video fa-3x text-primary mb-3"></i>
                                <h6 class="card-subtitle mb-2 text-muted" data-translate="video">Video</h6>
                                <p class="card-text">
                                    <span class="badge bg-primary" id="videoCount"><?= count($media['video']) ?></span> <span data-translate="files">Files</span>
                                </p>
                                <button class="btn btn-primary w-100" onclick="performFullScan('video')" id="scanVideoBtn">
                                    <i class="fas fa-search me-1"></i>
                                    <span data-translate="scan">Scan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center border-primary">
                            <div class="card-body">
                                <i class="fas fa-image fa-3x text-primary mb-3"></i>
                                <h6 class="card-subtitle mb-2 text-muted" data-translate="image">Image</h6>
                                <p class="card-text">
                                    <span class="badge bg-primary" id="imageCount"><?= count($media['image']) ?></span> <span data-translate="files">Files</span>
                                </p>
                                <button class="btn btn-primary w-100" onclick="performFullScan('image')" id="scanImageBtn">
                                    <i class="fas fa-search me-1"></i>
                                    <span data-translate="scan">Scan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card h-100 text-center border-danger">
                            <div class="card-body">
                                <i class="fas fa-music fa-3x text-danger mb-3"></i>
                                <h6 class="card-subtitle mb-2 text-muted" data-translate="audio">Music</h6>
                                <p class="card-text">
                                    <span class="badge bg-danger" id="musicCacheCount"><?= count($media['music']) ?></span> <span data-translate="files">Files</span>
                                </p>
                                <button class="btn btn-danger w-100" onclick="clearCacheType('music')" id="clearMusicCacheBtn">
                                    <i class="fas fa-trash me-1"></i>
                                    <span data-translate="clear">Clear</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center border-danger">
                            <div class="card-body">
                                <i class="fas fa-video fa-3x text-danger mb-3"></i>
                                <h6 class="card-subtitle mb-2 text-muted" data-translate="video">Video</h6>
                                <p class="card-text">
                                    <span class="badge bg-danger" id="videoCacheCount"><?= count($media['video']) ?></span> <span data-translate="files">Files</span>
                                </p>
                                <button class="btn btn-danger w-100" onclick="clearCacheType('video')" id="clearVideoCacheBtn">
                                    <i class="fas fa-trash me-1"></i>
                                    <span data-translate="clear">Clear</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center border-danger">
                            <div class="card-body">
                                <i class="fas fa-image fa-3x text-danger mb-3"></i>
                                <h6 class="card-subtitle mb-2 text-muted" data-translate="image">Image</h6>
                                <p class="card-text">
                                    <span class="badge bg-danger" id="imageCacheCount"><?= count($media['image']) ?></span> <span data-translate="files">Files</span>
                                </p>
                                <button class="btn btn-danger w-100" onclick="clearCacheType('image')" id="clearImageCacheBtn">
                                    <i class="fas fa-trash me-1"></i>
                                    <span data-translate="clear">Clear</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <span data-translate="close">Close</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qrcodeModal" tabindex="-1" aria-labelledby="qrcodeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrcodeModalLabel">
                    <i class="fas fa-qrcode text-purple me-2"></i>
                    <span data-translate="file_qrcode">File QR Code</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-break small mb-3" id="qrcodeFileName"></p>
                <div id="qrcodeContainer" class="mb-3 p-3 bg-white d-inline-block rounded"></div>
                <p class="text-break small text-muted" id="qrcodeFileUrl"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <span data-translate="close">Close</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    <span data-translate="confirm_title">Confirm Delete</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmMessage"></p>
                <div class="form-check form-switch mt-3" id="forceDeleteOption">
                    <input class="form-check-input" type="checkbox" id="forceDeleteCheck">
                    <label class="form-check-label" for="forceDeleteCheck" data-translate="force_delete">Skip recycle bin, delete permanently</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <span data-translate="cancel">Cancel</span>
                </button>
                <button type="button" class="btn btn-danger" onclick="executeDelete()">
                    <span data-translate="delete">Delete</span>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
let hoverAudio = null;
let hoverVideo = null;
let hoverVideoContainer = null;
let currentPlaylist = [];
let currentPlaylistIndex = -1;
let autoNextEnabled = true;
let imageSwitchTimer = null; 
let userInteracted = false;
let installEventSource = null;
let batchRenameFiles = [];
let convertFiles = [];
let currentHashPath = null;
let editorTabs = [];
let activeEditorTab = null;
let currentPath = '/';
let selectedFiles = new Set();
let viewMode = 'grid';
let fileContextMenuTarget = null;
let uploadFilesList = [];
let currentView = 'files'; 
let monacoEditor = null;
let currentEditorMode = 'simple';
let monacoLoaded = false;
let monacoLoading = false;
let completionProvidersRegistered = false;
let playlistCache = {};
let sidebarCollapsed = false;
let isResizing = false;
let isPlayerResizing = false;
let startX, startWidth, startPlayerWidth;
let cpuChart = null;
let memChart = null;
let cpuData = [];
let memData = [];
let timeLabels = [];
let maxDataPoints = 30;
let networkHistory = [];
let systemMonitorInterval = null;
let lastNetworkRx = 0;
let lastNetworkTx = 0;
let recycleBinItems = [];
let selectedRecycleItems = new Set();
let isMiniMode = false;
let isDragging = false;
let dragOffsetX, dragOffsetY;
let resizeStartX, resizeStartY, resizeStartWidth, resizeStartHeight;
let resizeDirection = '';
let isMuted = false;
let previousVolume = 1;

let currentMedia = {
    type: null,
    src: null,
    path: null,
    ext: null,
    wasPlaying: false  
};

function showSection(sectionId) {
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    const navItem = document.querySelector(`.nav-item[onclick="showSection('${sectionId}')"]`);
    if (navItem) {
        navItem.classList.add('active');
    }
    
    document.querySelectorAll('.grid-section').forEach(section => {
        section.style.display = 'none';
    });
    
    let targetId = sectionId + 'Section';
    if (sectionId === 'recycle_bin') {
        targetId = 'recycleBinSection';
    }
    
    const targetSection = document.getElementById(targetId);
    if (targetSection) {
        targetSection.style.display = 'block';
    }
    
    document.querySelector('.media-grid-container').scrollTop = 0;
    
    if (sectionId === 'home') {
        startSystemMonitoring();
    } else {
        stopSystemMonitoring();
    }

    if (sectionId === 'recycle_bin') {
        loadRecycleBin();
    }

    if (sectionId === 'music' || sectionId === 'video' || sectionId === 'image') {
        saveSectionPlaylistToCache(sectionId);
    }

    setTimeout(() => {
        restorePlayingHighlight();
    }, 300);
}

async function saveSectionPlaylistToCache(sectionId) {
    const mediaItems = document.querySelectorAll(`#${sectionId}Section .media-item`);
    const playlist = [];
    
    mediaItems.forEach(item => {
        const dataSrc = item.getAttribute('data-src');
        if (dataSrc) {
            const match = dataSrc.match(/path=([^&]+)/);
            if (match) {
                const path = decodeURIComponent(match[1]);
                playlist.push(path);
            }
        }
    });
    
    if (playlist.length === 0) return;
    
    const firstFile = playlist[0];
    const dir = firstFile.substring(0, firstFile.lastIndexOf('/')) || '/';
    try {
        const response = await fetch('?action=save_playlist', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                dir: dir,
                playlist: playlist
            })
        });
        const data = await response.json();
        if (data.success) {
        }
    } catch (error) {
    }
}

function playMedia(filePath) {
    filePath = filePath.trim();
    if (filePath.startsWith('//')) {
        filePath = filePath.substring(1);
    }
    
    const fileName = filePath.split('/').pop();
    const fileExt = fileName.split('.').pop().toLowerCase();
    const nameWithoutExt = fileName.replace(/\.[^/.]+$/, "");
    const fileDir = filePath.substring(0, filePath.lastIndexOf('/')) || '/';

    const audioExts = [
        'mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 'wma', 'opus',
        'ape', 'wv', 'tta', 'tak', 'dts', 'dsf', 'dff', 'sacd',
        'mid', 'midi', 'rmi', 'kar', 'ac3', 'eac3', 'truehd', 'thd',
        'pcm', 'adpcm', 'amr', 'awb', 'sln', 'vox', 'gsm', 'ra',
        'ram', 'au', 'snd', 'voc', 'cda', '8svx', 'aiff', 'aif',
        'aifc', 'afc', 'weba', 'mka', 'spx', 'oga', 'tta', 'm3u',
        'm3u8', 'pls', 'mp2'
    ];
    
    const videoExts = [
        'mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v',
        '3gp', '3g2', 'ogv', 'mpg', 'mpeg', 'mpe', 'mpv', 'm2v',
        'ts', 'm2ts', 'mts', 'm2t', 'tod', 'mod', 'vro', 'vob',
        'ifo', 'bup', 'iso', 'img', 'rm', 'rmvb', 'rv', 'ra',
        'ram', 'qt', 'hdmov', 'moov', 'dv', 'mqv', 'asf', 'asx',
        'wm', 'wmx', 'wvx', 'divx', 'xvid', 'f4v', 'f4p', 'f4a',
        'f4b', 'swf', 'fla', 'avchd', 'mxf', 'gxf', 'lxv', 'nsv',
        'nut', 'nuv', 'ogm', 'ogx', 'bik', 'smk', 'vp6', 'vp7',
        'vp8', 'vp9', 'av1', 'hevc', 'h264', 'h265'
    ];
    
    const imageExts = [
        'jpg', 'jpeg', 'jpe', 'jfif', 'png', 'gif', 'bmp', 'webp',
        'svg', 'svgz', 'ico', 'cur', 'raw', 'cr2', 'cr3', 'crw',
        'nef', 'nrw', 'arw', 'srf', 'sr2', 'raf', 'dng', 'orf',
        'rw2', 'pef', 'ptx', 'x3f', 'erf', 'mrw', 'mef', 'mdc',
        'kdc', 'dcr', 'k25', 'bay', 'bmq', 'ciff', 'psd', 'psb',
        'ai', 'eps', 'epsf', 'epsi', 'tiff', 'tif', 'djvu', 'djv',
        'jxr', 'wdp', 'hdp', 'heic', 'heif', 'heics', 'heifs',
        'avci', 'avcs', 'exr', 'hdr', 'pfm', 'ppm', 'pgm', 'pbm',
        'pnm', 'pcx', 'tga', 'icb', 'vda', 'vst', 'pix', 'pxr',
        'xbm', 'xpm', 'wbmp', 'cals', 'fpx', 'fpx', 'pcd', 'psp',
        'pspimage', 'xcf', 'kra', 'cpt', 'pat', 'abr'
    ];
    
    const directlySupported = [
        'mp4', 'webm', 'ogg', 'mkv', 'mov',
        'mp3', 'wav', 'aac', 'm4a', 'flac', 'opus',
        ...imageExts
    ];

    const isImage = imageExts.includes(fileExt);
    const isAudio = audioExts.includes(fileExt);
    const isVideo = videoExts.includes(fileExt);
    
    const needsTranscoding = !isImage && !directlySupported.includes(fileExt);

    let previewUrl;
    if (needsTranscoding) {
        const format = isVideo ? 'mp4' : 'mp3';
        previewUrl = `?action=transcode&path=${encodeURIComponent(filePath)}&format=${format}`;
        //showLogMessage((translations['transcoding_play'] || 'Transcoding: {format} format').replace('{format}', fileExt.toUpperCase()), 'info');
    } else {
        previewUrl = `?preview=1&path=${encodeURIComponent(filePath)}`;
    }
    
    const audioPlayer = document.getElementById('audioPlayer');
    const videoPlayer = document.getElementById('videoPlayer');
    const imageViewer = document.getElementById('imageViewer');
    const playError = document.getElementById('playError');
    const playerArea = document.getElementById('playerArea');
    const playerTitle = document.getElementById('playerTitle');

    if (!isImage) {
        const playingPrefix = translations['now_playing'] || 'Now playing';
        const playingMessage = `${playingPrefix}：${nameWithoutExt}`;
        logAndSpeak(playingMessage);
    }

    if (imageSwitchTimer) {
        clearInterval(imageSwitchTimer);
        imageSwitchTimer = null;
    }
    
    clearAllHighlights();
    
    audioPlayer.style.display = 'none';
    videoPlayer.style.display = 'none';
    imageViewer.style.display = 'none';
    playError.style.display = 'none';
    
    audioPlayer.pause();
    videoPlayer.pause();
    
    const displayName = fileName.length > 30 ? fileName.substring(0, 27) + '...' : fileName;
    const titleSpan = playerTitle.querySelector('span');
    if (titleSpan) {
        titleSpan.textContent = displayName;
        titleSpan.setAttribute('title', fileName);
        titleSpan.classList.add('text-truncate');
    }
    
    audioPlayer.src = '';
    videoPlayer.src = '';
    imageViewer.src = '';
    
    audioPlayer.onerror = null;
    videoPlayer.onerror = null;
    imageViewer.onerror = null;
    audioPlayer.onended = null;  
    videoPlayer.onended = null;  
    
    function handleMediaError(element, type) {
        return function(e) {
            element.style.display = 'none';
            playError.style.display = 'block';
            playerArea.classList.add('active');
            currentMedia = { type: null, src: null, path: null, ext: null, wasPlaying: false };
            clearAllHighlights();
        };
    }
    
    updatePlaylistAndIndex(filePath, fileDir);
    
    highlightCurrentPlayingFile(filePath);
    
    if (isAudio) {
        audioPlayer.onerror = handleMediaError(audioPlayer, translations['audio'] || 'Audio');
        audioPlayer.onended = function() {
            clearAllHighlights();
            if (autoNextEnabled) {
                playNextMedia();
            }
        };
        audioPlayer.src = previewUrl;
        audioPlayer.load();
        audioPlayer.style.display = 'block';
        audioPlayer.controls = true;
        audioPlayer.play().catch(e => {
            audioPlayer.style.display = 'none';
            playError.style.display = 'block';
            clearAllHighlights();
        });
        currentMedia = { type: 'audio', src: previewUrl, path: filePath, ext: fileExt, wasPlaying: false };
    } 
    else if (isVideo) {
        videoPlayer.onerror = handleMediaError(videoPlayer, translations['video'] || 'Video');
        videoPlayer.onended = function() {
            clearAllHighlights();
            if (autoNextEnabled) {
                playNextMedia();
            }
        };
        videoPlayer.src = previewUrl;
        videoPlayer.load();
        videoPlayer.style.display = 'block';
        videoPlayer.controls = true;
        videoPlayer.style.height = 'auto';
        videoPlayer.style.maxHeight = 'calc(100vh - 200px)';
        videoPlayer.style.width = '100%';
        videoPlayer.style.objectFit = 'contain';
        videoPlayer.play().catch(e => {
            videoPlayer.style.display = 'none';
            playError.style.display = 'block';
            clearAllHighlights();
        });
        currentMedia = { type: 'video', src: previewUrl, path: filePath, ext: fileExt, wasPlaying: false };
    } 
    else if (isImage) {
        imageViewer.onerror = handleMediaError(imageViewer, translations['image'] || 'Image');
        imageViewer.src = previewUrl;
        imageViewer.style.display = 'block';
        imageViewer.style.height = 'auto';
        imageViewer.style.maxHeight = 'calc(100vh - 200px)';
        imageViewer.style.width = '100%';
        imageViewer.style.objectFit = 'contain';
        currentMedia = { type: 'image', src: previewUrl, path: filePath, ext: fileExt, wasPlaying: false };
        
        if (autoNextEnabled) {
            startImageAutoSwitch();
        }
    } else {
        playError.style.display = 'block';
        playerArea.classList.add('active');
        clearAllHighlights();
    }
    
    playerArea.classList.add('active');
    playerArea.style.display = 'flex';
    const miniModeEnabled = localStorage.getItem('miniModeEnabled');
    if (miniModeEnabled === 'true' && !isMiniMode) {
        setTimeout(() => {
            toggleMiniPlayer();
        }, 100);
    } else if (miniModeEnabled === 'false' && isMiniMode) {
        setTimeout(() => {
            toggleMiniPlayer();
        }, 100);
    }
    setTimeout(() => {
        const activeMedia = getActiveMedia();
        if (activeMedia) {
            const savedSpeed = localStorage.getItem('playbackSpeed');
            if (savedSpeed) {
                activeMedia.playbackRate = parseFloat(savedSpeed);
            }
        }
    }, 200);  
    saveToRecent(filePath);
    setTimeout(adjustNavButtons, 200);
    setTimeout(adjustNavButtons, 800);
}

function adjustNavButtons() {
    const playerContent = document.querySelector('.player-content');
    const navOverlay = document.querySelector('.player-nav-overlay');
    const videoPlayer = document.getElementById('videoPlayer');
    const imageViewer = document.getElementById('imageViewer');
    
    if (!playerContent || !navOverlay) return;
    
    let mediaElement = null;
    if (videoPlayer.style.display === 'block') mediaElement = videoPlayer;
    else if (imageViewer.style.display === 'block') mediaElement = imageViewer;
    else return;
    
    if (!mediaElement) return;
    
    requestAnimationFrame(() => {
        const mediaRect = mediaElement.getBoundingClientRect();
        const contentRect = playerContent.getBoundingClientRect();
        
        const relativeTop = mediaRect.top - contentRect.top;
        const relativeHeight = mediaRect.height;
        
        if (relativeTop >= 0 && relativeHeight > 0) {
            navOverlay.style.top = relativeTop + 'px';
            navOverlay.style.height = relativeHeight + 'px';
        }
    });
}

async function loadPlaylistCache() {
    try {
        if (currentPath) {
            const response = await fetch(`?action=get_playlist&dir=${encodeURIComponent(currentPath)}`);
            const data = await response.json();
            if (data.success && data.playlist) {
                playlistCache[currentPath] = data.playlist;
                currentPlaylist = data.playlist;
            }
        }
    } catch (error) {
        //console.error('Failed to load playlist cache:', error);
    }
}

function restorePlayingHighlight() {
    if (currentMedia && currentMedia.path) {
        highlightCurrentPlayingFile(currentMedia.path);
    }
}

async function updatePlaylistAndIndex(filePath, fileDir) {
    try {
        const response = await fetch(`?action=get_playlist&dir=${encodeURIComponent(fileDir)}`);
        const data = await response.json();
        
        if (data.success && data.playlist && data.playlist.length > 0) {
            currentPlaylist = data.playlist;
        } else {
            currentPlaylist = collectMediaFromCurrentView(fileDir);
            if (currentPlaylist.length > 0) {
                await savePlaylistToCache(fileDir, currentPlaylist);
            }
        }
        
        currentPlaylistIndex = currentPlaylist.indexOf(filePath);
        if (currentPlaylistIndex === -1 && currentPlaylist.length > 0) {
            currentPlaylistIndex = 0;
        }
        
        playlistCache[fileDir] = currentPlaylist;
        
    } catch (error) {
        currentPlaylist = collectMediaFromCurrentView(fileDir);
        currentPlaylistIndex = currentPlaylist.indexOf(filePath);
        if (currentPlaylistIndex === -1 && currentPlaylist.length > 0) {
            currentPlaylistIndex = 0;
        }
        playlistCache[fileDir] = currentPlaylist;
    }
}

function collectMediaFromCurrentView(dir) {
    const mediaFiles = [];
    const mediaExts = [
        'mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 'wma', 'opus',
        'ape', 'wv', 'tta', 'tak', 'dts', 'dsf', 'dff', 'sacd',
        'mid', 'midi', 'rmi', 'kar', 'ac3', 'eac3', 'truehd', 'thd',
        'pcm', 'adpcm', 'amr', 'awb', 'sln', 'vox', 'gsm', 'ra',
        'ram', 'au', 'snd', 'voc', 'cda', '8svx', 'aiff', 'aif',
        'aifc', 'afc', 'weba', 'mka', 'spx', 'oga', 'tta', 'm3u', 'm3u8', 'pls',
        'mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v',
        '3gp', '3g2', 'ogv', 'mpg', 'mpeg', 'mpe', 'mpv', 'm2v',
        'ts', 'm2ts', 'mts', 'm2t', 'tod', 'mod', 'vro', 'vob',
        'ifo', 'bup', 'iso', 'img', 'rm', 'rmvb', 'rv', 'qt',
        'hdmov', 'moov', 'dv', 'mqv', 'asf', 'asx', 'wm', 'wmx',
        'wvx', 'divx', 'xvid', 'f4v', 'f4p', 'f4a', 'f4b', 'swf',
        'fla', 'avchd', 'mxf', 'gxf', 'lxv', 'nsv', 'nut', 'nuv',
        'ogm', 'ogx', 'bik', 'smk', 'vp6', 'vp7', 'vp8', 'vp9',
        'av1', 'hevc', 'h264', 'h265',
        'jpg', 'jpeg', 'jpe', 'jfif', 'png', 'gif', 'bmp', 'webp',
        'svg', 'svgz', 'ico', 'cur', 'raw', 'cr2', 'cr3', 'crw',
        'nef', 'nrw', 'arw', 'srf', 'sr2', 'raf', 'dng', 'orf',
        'rw2', 'pef', 'ptx', 'x3f', 'erf', 'mrw', 'mef', 'mdc',
        'kdc', 'dcr', 'k25', 'bay', 'bmq', 'ciff', 'psd', 'psb',
        'ai', 'eps', 'epsf', 'epsi', 'tiff', 'tif', 'djvu', 'djv',
        'jxr', 'wdp', 'hdp', 'heic', 'heif', 'heics', 'heifs',
        'avci', 'avcs', 'exr', 'hdr', 'pfm', 'ppm', 'pgm', 'pbm',
        'pnm', 'pcx', 'tga', 'icb', 'vda', 'vst', 'pix', 'pxr',
        'xbm', 'xpm', 'wbmp', 'cals', 'fpx', 'fpx', 'pcd', 'psp',
        'pspimage', 'xcf', 'kra', 'cpt', 'pat', 'abr'
    ];
    
    const currentSection = document.querySelector('.grid-section:not([style*="display: none"])')?.id || '';
    
    if (currentSection === 'filesSection') {
        document.querySelectorAll('.file-item').forEach(item => {
            const path = item.getAttribute('data-path');
            const isDir = item.getAttribute('data-is-dir') === 'true';
            
            if (!isDir && path) {
                const ext = path.split('.').pop().toLowerCase();
                if (mediaExts.includes(ext)) {
                    mediaFiles.push(path);
                }
            }
        });
    } else {
        const mediaItems = document.querySelectorAll(`#${currentSection} .media-item`);
        mediaItems.forEach(item => {
            const dataSrc = item.getAttribute('data-src');
            if (dataSrc) {
                const match = dataSrc.match(/path=([^&]+)/);
                if (match) {
                    const path = decodeURIComponent(match[1]);
                    mediaFiles.push(path);
                }
            }
        });
    }
    
    return mediaFiles;
}

function playNextMedia() {
    if (!autoNextEnabled) return;
    if (currentPlaylist.length === 0 || currentPlaylistIndex === -1) return;
    
    const nextIndex = (currentPlaylistIndex + 1) % currentPlaylist.length;
    const nextFilePath = currentPlaylist[nextIndex];
    
    if (nextFilePath) {
        playMedia(nextFilePath);
    }
}

function playPreviousMedia() {
    if (!autoNextEnabled) return;
    if (currentPlaylist.length === 0 || currentPlaylistIndex === -1) return;
    
    const prevIndex = (currentPlaylistIndex - 1 + currentPlaylist.length) % currentPlaylist.length;
    const prevFilePath = currentPlaylist[prevIndex];
    
    if (prevFilePath) {
        playMedia(prevFilePath);
    }
}

function startImageAutoSwitch() {
    if (imageSwitchTimer) {
        clearInterval(imageSwitchTimer);
    }
    
    if (!autoNextEnabled || currentPlaylist.length < 2) {
        return;
    }
    
    imageSwitchTimer = setInterval(() => {
        playNextMedia();
    }, 5000);
}

function clearAllHighlights() {
    document.querySelectorAll('.file-item, .media-item, .playlist-card').forEach(item => {
        item.classList.remove('playing');
        item.style.backgroundColor = '';
        item.style.borderColor = '';
        item.style.boxShadow = '';
        item.style.transform = '';
    });
}

function highlightCurrentPlayingFile(filePath) {
    clearAllHighlights();
    
    const fileItem = document.querySelector(`.file-item[data-path="${filePath}"]`);
    if (fileItem) {
        fileItem.classList.add('playing');
        fileItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    const mediaItem = document.querySelector(`.media-item[data-src="?preview=1&path=${encodeURIComponent(filePath)}"]`);
    if (mediaItem) {
        mediaItem.classList.add('playing');
        mediaItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    const playlistCard = document.querySelector(`.playlist-card[data-path="${filePath}"]`);
    if (playlistCard) {
        playlistCard.classList.add('playing');
        setTimeout(() => {
            playlistCard.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'nearest'
            });
        }, 100);
    }
}

async function savePlaylistToCache(dir, playlist) {
    if (!dir || !playlist || playlist.length === 0) return;
    
    try {
        const response = await fetch('?action=save_playlist', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                dir: dir,
                playlist: playlist
            })
        });
        const data = await response.json();
        if (data.success) {
            //console.log('Playlist saved to cache');
        }
    } catch (error) {
        //console.error('Failed to save playlist:', error);
    }
}

function closePlayer() {
    const playerArea = document.getElementById('playerArea');
    const audioPlayer = document.getElementById('audioPlayer');
    const videoPlayer = document.getElementById('videoPlayer');
    const imageViewer = document.getElementById('imageViewer');
    const toggleBtn = document.getElementById('miniPlayerToggle');
    
    if (isMiniMode) {
        removeMiniControls();
        isMiniMode = false;
        if (toggleBtn) {
            const icon = toggleBtn.querySelector('i');
            icon.className = 'fas fa-window-restore';
        }
    }
    
    if (document.fullscreenElement) {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
    
    if (imageSwitchTimer) {
        clearInterval(imageSwitchTimer);
        imageSwitchTimer = null;
    }
    
    clearAllHighlights();
    
    audioPlayer.pause();
    videoPlayer.pause();
    
    playerArea.classList.remove('active', 'mini-mode');
    playerArea.style.display = 'none'; 
    
    audioPlayer.style.display = 'none';
    videoPlayer.style.display = 'none';
    imageViewer.style.display = 'none';
    
    currentMedia = {
        type: null,
        src: null,
        path: null,
        ext: null,
        wasPlaying: false
    };
    
    currentPlaylist = [];
    currentPlaylistIndex = -1;
}
    
function initAutoPlayToggle() {
    const toggleBtn = document.getElementById('autoNextToggle');
    if (toggleBtn) {
        updateAutoPlayToggleButton();
    }
}

function updateAutoPlayToggleButton() {
    const toggleBtn = document.getElementById('autoNextToggle');
    if (toggleBtn) {
        const icon = autoNextEnabled ? 'fa-toggle-on' : 'fa-toggle-off';
        
        const iconElement = toggleBtn.querySelector('i');
        if (iconElement) {
            iconElement.className = `fas ${icon}`;
        }
    }
}

function toggleAutoNext() {
    autoNextEnabled = !autoNextEnabled;
    
    updateAutoPlayToggleButton();
    
    logAndSpeak(autoNextEnabled ? 
        (translations['auto_play_enabled'] || 'Auto play enabled') : 
        (translations['auto_play_disabled'] || 'Auto play disabled'));
    
    const currentSection = document.querySelector('.grid-section:not([style*="display: none"])')?.id || '';
    const isFileManager = currentSection === 'filesSection';
    
    if (currentMedia.type === 'image') {
        if (autoNextEnabled) {
            if (isFileManager) {
                if (currentFileMediaList.length > 1) {
                    startFileImageAutoSwitch();
                }
            } else {
                if (currentMediaList.length > 1) {
                    startImageAutoSwitch();
                }
            }
        } else {
            if (imageSwitchTimer) {
                clearInterval(imageSwitchTimer);
                imageSwitchTimer = null;
            }
            if (fileImageSwitchTimer) {
                clearInterval(fileImageSwitchTimer);
                fileImageSwitchTimer = null;
            }
        }
    }
}
    
function saveToRecent(filePath) {
    try {
        let recent = JSON.parse(localStorage.getItem('recent_media') || '[]');
        
        recent = recent.filter(f => f !== filePath);
        
        recent.unshift(filePath);
        
        if (recent.length > <?= $RECENT_MAX ?>) {
            recent = recent.slice(0, <?= $RECENT_MAX ?>);
        }
        
        localStorage.setItem('recent_media', JSON.stringify(recent));
        
        updateRecentList();
    } catch (e) {}
}
    
function updateRecentList() {
    try {
        const recent = JSON.parse(localStorage.getItem('recent_media') || '[]');
        const recentList = document.getElementById('recentList');
        
        if (!recentList) return;
        
        if (recent.length === 0) {
            recentList.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <p style="margin-top: 15px;">${translations['no_playback_history'] || 'No playback history'}</p>
                </div>`;
            return;
        }
        
        const musicExts = ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac'];
        const videoExts = ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', '3gp', 'ogv', 'mpg', 'mpeg'];
        
        recentList.innerHTML = recent.slice(0, 20).map(file => {
            const ext = file.split('.').pop().toLowerCase();
            let icon = 'fas fa-image';
            if (musicExts.includes(ext)) icon = 'fas fa-music';
            else if (videoExts.includes(ext)) icon = 'fas fa-video';
            
            const safePath = file.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const safeName = file.split('/').pop().replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const safeDir = file.split('/').slice(0, -1).join('/').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            
            return `
            <div class="recent-item" onclick="playMedia('${safePath}')">
                <div class="recent-icon">
                    <i class="${icon}"></i>
                </div>
                <div class="recent-info">
                    <div class="recent-name">${safeName}</div>
                    <div class="recent-path">${safeDir}</div>
                </div>
            </div>`;
        }).join('');
    } catch (e) {}
}
        
function toggleFullscreenPlayer() {
    const videoPlayer = document.getElementById('videoPlayer');
    const audioPlayer = document.getElementById('audioPlayer');
    const imageViewer = document.getElementById('imageViewer');
    
    let mediaElement;
    if (videoPlayer.style.display === 'block') {
        mediaElement = videoPlayer;
    } else if (audioPlayer.style.display === 'block') {
        mediaElement = audioPlayer;
    } else if (imageViewer.style.display === 'block') {
        mediaElement = imageViewer;
    }
    
    if (!mediaElement) return;
    
    if (!document.fullscreenElement) {
        if (mediaElement.requestFullscreen) {
            mediaElement.requestFullscreen();
        } else if (mediaElement.webkitRequestFullscreen) {
            mediaElement.webkitRequestFullscreen();
        } else if (mediaElement.msRequestFullscreen) {
            mediaElement.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
}
   
function refreshMedia() {
    updateRecentList();
    window.location.reload();
}

async function clearCacheType(type) {
    const typeNames = {
        'music': translations['audio'] || 'Music',
        'video': translations['video'] || 'Video', 
        'image': translations['image'] || 'Image'
    };
    
    const confirmMsg = (translations['confirm_clear_cache_type'] || 'Clear {type} cache?').replace('{type}', typeNames[type]);
    
    showConfirmation(
        confirmMsg,
        async () => {
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (translations['clearing'] || 'Clearing...');
            btn.disabled = true;
            
            try {
                const response = await fetch(`?action=clear_cache_type&type=${type}`);
                const data = await response.json();
                
                if (data.success) {
                    const successMsg = (translations['cache_type_cleared'] || '{type} cache cleared')
                        .replace('{type}', typeNames[type]);
                    logAndSpeak(successMsg, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                } else {
                    const errorMsg = (translations['clear_type_failed'] || 'Failed to clear {type} cache')
                        .replace('{type}', typeNames[type]);
                    logAndSpeak(errorMsg, 'error');
                }
            } catch (error) {
                const errorMsg = (translations['clear_error'] || 'Error clearing cache');
                logAndSpeak(errorMsg, 'error');
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    );
}

async function performFullScan(type) {
    const typeNames = {
        'music': translations['audio'] || 'Music',
        'video': translations['video'] || 'Video',
        'image': translations['image'] || 'Image'
    };
    
    const btn = event.currentTarget;
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (translations['scanning'] || 'Scanning...');
    btn.disabled = true;
    
    try {
        const response = await fetch(`?action=full_scan&type=${type}`);
        const data = await response.json();
        
        if (data.success) {
            const successMsg = (translations['scan_type_complete'] || '{type} scan complete: {count} files found')
                .replace('{type}', typeNames[type])
                .replace('{count}', data.count);
            logAndSpeak(successMsg, 'success');
            setTimeout(() => {
                location.reload();
            }, 3500);
        } else {
            const errorMsg = (translations['scan_type_failed'] || '{type} scan failed')
                .replace('{type}', typeNames[type]);
            showLogMessage(errorMsg, 'error');
        }
    } catch (error) {
        const errorMsg = (translations['scan_error'] || 'Scan error');
        showLogMessage(errorMsg, 'error');
    } finally {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
}
    
function toggleFullscreen() {
    const diffModal = document.getElementById('diffEditorModal');
    const isDiffModalOpen = diffModal && diffModal.classList.contains('show');
    
    const editorPanel = document.getElementById('editorPanel');
    const isEditorOpen = editorPanel && editorPanel.style.display === 'block';
    
    let elem;
    
    if (isDiffModalOpen) {
        elem = document.querySelector('#diffEditorModal .modal-content');
        if (!elem) {
            elem = document.documentElement;
        }
    } else if (isEditorOpen) {
        elem = document.querySelector('#editorPanelContent');
        if (!elem) {
            elem = editorPanel;
        }
    } else {
        elem = document.documentElement;
    }
    
    if (!elem) return;
    
    const fullscreenBtn = document.querySelector('.action-btn.primary');
    const icon = fullscreenBtn ? fullscreenBtn.querySelector('i') : null;
    
    if (!document.fullscreenElement) {
        if (icon) {
            icon.className = 'fas fa-compress';
            icon.style.opacity = '0.8';
        }
        
        if (elem.requestFullscreen) {
            elem.requestFullscreen().then(() => {
                if (icon) {
                    icon.style.opacity = '1';
                }
            }).catch(err => {
                if (icon) {
                    icon.className = 'fas fa-expand';
                    icon.style.opacity = '1';
                }
            });
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        }
    } else {
        if (icon) {
            icon.className = 'fas fa-expand';
            icon.style.opacity = '0.8';
        }
        
        if (document.exitFullscreen) {
            document.exitFullscreen().then(() => {
                if (icon) {
                    icon.style.opacity = '1';
                }
            }).catch(err => {
                if (icon) {
                    icon.className = 'fas fa-compress';
                    icon.style.opacity = '1';
                }
            });
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
}

document.addEventListener('click', function() {
    if (!userInteracted) {
        userInteracted = true;
    }
});

function initHoverPlay() {
    const items = document.querySelectorAll('.hover-playable');
    
    items.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const type = this.getAttribute('data-type');
            const src = this.getAttribute('data-src');
            
            stopHoverPlay();
            
            if (type === 'audio') {
                hoverAudio = new Audio(src);
                hoverAudio.volume = 0.9;
                hoverAudio.play().catch(e => {
                });
            } 
            else if (type === 'video' && userInteracted) {
                const thumb = this.querySelector('.media-thumb');
                if (!thumb) return;
                
                hoverVideoContainer = document.createElement('div');
                hoverVideoContainer.className = 'hover-video-container';
                hoverVideoContainer.style.cssText = `
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 10;
                    border-radius: 8px;
                    overflow: hidden;
                    background: #000;
                `;
                
                hoverVideo = document.createElement('video');
                hoverVideo.src = src;
                hoverVideo.controls = false;
                hoverVideo.autoplay = true;
                hoverVideo.muted = true;
                hoverVideo.playsInline = true;
                hoverVideo.style.cssText = `
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                `;
                
                hoverVideoContainer.appendChild(hoverVideo);
                thumb.appendChild(hoverVideoContainer);
                
                const icon = thumb.querySelector('i');
                if (icon) icon.style.opacity = '0.3';
                
                hoverVideo.play().catch(e => {
                    if (hoverVideoContainer && hoverVideoContainer.parentNode) {
                        hoverVideoContainer.parentNode.removeChild(hoverVideoContainer);
                    }
                });
            }
        });
        
        item.addEventListener('mouseleave', function() {
            stopHoverPlay();
        });
    });
}

function stopHoverPlay() {
    if (hoverAudio) {
        hoverAudio.pause();
        hoverAudio.currentTime = 0;
        hoverAudio = null;
    }
    
    if (hoverVideo) {
        hoverVideo.pause();
        hoverVideo.currentTime = 0;
        
        if (hoverVideoContainer) {
            const parent = hoverVideoContainer.parentElement;
            if (parent) {
                const icon = parent.querySelector('i');
                if (icon) icon.style.opacity = '1';
                
                if (parent.contains(hoverVideoContainer)) {
                    parent.removeChild(hoverVideoContainer);
                }
            }
        }
        
        hoverVideo = null;
        hoverVideoContainer = null;
    }
}

async function updateSystemInfo() {
    try {
        const response = await fetch('?ajax=1');
        const data = await response.json();
        
        if (data.success) {
            let cpuUsage = parseFloat(data.cpu_usage) || 0;
            cpuUsage = Math.max(0, Math.min(100, cpuUsage));
            
            updateElementText('cpuUsageDisplay', cpuUsage.toFixed(1) + '%');
            updateElementText('cpuUsageValue', cpuUsage.toFixed(1) + '%');
            updateElementText('cpuModelDisplay', data.cpu_model || 'Unknown');
            
            const cpuBar = document.getElementById('cpuUsageBar');
            if (cpuBar) {
                cpuBar.style.width = Math.min(cpuUsage, 100) + '%';
            }
            
            updateElementText('cpuCoresValue', data.cpu_cores || '--');
            
            updateElementText('cpuFreqDisplay', data.cpu_freq || '--');

            if (data.openwrt_version) {
                const el = document.getElementById('openwrtVersionDisplay');
                el.textContent = data.openwrt_version || 'Unknown';
                el.title = data.openwrt_version || 'Unknown';
            }
            
            if (data.kernel_version) {
                updateElementText('kernelVersionDisplay', data.kernel_version || 'Unknown');
            }
            
            if (data.board_model) {
                const el = document.getElementById('boardModelDisplay');
                el.textContent = data.board_model || 'Unknown';
                el.title = data.board_model || 'Unknown';
            }
            
            const memUsage = parseFloat(data.mem_usage) || 0;
            updateElementText('memUsageDisplay', memUsage.toFixed(1) + '%');
            updateElementText('memUsageValue', memUsage.toFixed(1) + '%');
            
            const memBar = document.getElementById('memUsageBar');
            if (memBar) memBar.style.width = Math.min(memUsage, 100) + '%';
            
            if (data.mem_total !== undefined && data.mem_used !== undefined) {
                const cleanNumber = (str) => {
                    if (typeof str === 'string') {
                        return parseFloat(str.replace(/,/g, ''));
                    }
                    return parseFloat(str || 0);
                };
    
                const memUsed = cleanNumber(data.mem_used);
                const memTotal = cleanNumber(data.mem_total);
    
                updateElementText('memUsedDisplay', memUsed.toFixed(1));
                updateElementText('memTotalDisplay', memTotal.toFixed(1));
            }    
     
            if (cpuChart && memChart) {
                updateChartData(cpuUsage, memUsage);
            }
            
            if (data.cpu_temp && data.cpu_temp !== '--') {
                updateElementText('cpuTempDisplay', data.cpu_temp + '°C');
                const tempElement = document.getElementById('cpuTempDisplay');
                const temp = parseFloat(data.cpu_temp);
                
                if (temp > 70) {
                    tempElement.style.color = '#F44336';
                } else if (temp > 60) {
                    tempElement.style.color = '#FF9800';
                } else {
                    tempElement.style.color = '#4CAF50';
                }
            }
            
            updateElementText('processCountDisplay', data.process_count || '--');
            
            const uptimeElement = document.getElementById('uptimeDisplay');
            if (uptimeElement && data.uptime) {
                let uptimeText = data.uptime;
    
                uptimeText = uptimeText.replace(/days/gi, translations['uptime_days'] || 'days')
                                                  .replace(/hours/gi, translations['uptime_hours'] || 'hours')
                                                  .replace(/minutes/gi, translations['minutes'] || 'minutes')
                                                  .replace(/seconds/gi, translations['seconds'] || 'seconds');
                uptimeElement.textContent = uptimeText;
            }
            
            updateElementText('loadAvgDisplay', data.load_avg || '--');
            
            updateElementText('timeValue', data.system_time || '--:--:--');
            updateElementText('timezoneDisplay', data.timezone || 'UTC');
            
            if (data.network_rx !== undefined && data.network_tx !== undefined) {
                updateNetworkSpeed(data.network_rx, data.network_tx);
            }

            if (data.disk_total !== undefined && data.disk_used !== undefined) {
                const totalEl = document.getElementById('diskTotal');
                const freeEl = document.getElementById('diskFree');
                const usedEl = document.getElementById('diskUsed');
                const usageBarEl = document.getElementById('diskUsageBar');
                const usagePercentEl = document.getElementById('diskUsagePercent');
                
                if (totalEl) totalEl.textContent = data.disk_total.toFixed(2) + ' GB';
                if (freeEl) freeEl.textContent = (data.disk_total - data.disk_used).toFixed(2) + ' GB';
                if (usedEl) usedEl.textContent = data.disk_used.toFixed(2) + ' GB';
                if (usageBarEl) usageBarEl.style.width = data.disk_usage + '%';
                if (usagePercentEl) usagePercentEl.textContent = data.disk_usage + '%';
            }
        }
    } catch (error) {
        showErrorState();
    }
}

function updateElementText(elementId, text) {
    const element = document.getElementById(elementId);
    if (element) element.textContent = text;
}

function showErrorState() {
    updateElementText('cpuUsageDisplay', '--%');
    updateElementText('memUsageDisplay', '--%');
    updateElementText('cpuTempDisplay', '--°C');
    updateElementText('processCountDisplay', '--');
    updateElementText('uptimeDisplay', '--:--:--');
    updateElementText('loadAvgDisplay', '--');
    updateElementText('timeValue', '--:--:--');
    updateElementText('timezoneDisplay', '--');
}

function initCharts() {
    const cpuCtx = document.getElementById('cpuChartCanvas');
    const memCtx = document.getElementById('memChartCanvas');
    
    if (!cpuCtx || !memCtx) {
        return;
    }

    if (cpuChart) {
        cpuChart.destroy();
    }

    if (memChart) {
        memChart.destroy();
    }
    
    const cpuContext = cpuCtx.getContext('2d');
    const memContext = memCtx.getContext('2d');
    cpuContext.clearRect(0, 0, cpuCtx.width, cpuCtx.height);
    memContext.clearRect(0, 0, memCtx.width, memCtx.height);
    
    cpuCtx.width = cpuCtx.offsetWidth;
    cpuCtx.height = cpuCtx.offsetHeight;
    memCtx.width = memCtx.offsetWidth;
    memCtx.height = memCtx.offsetHeight;
    
    cpuData = [];
    memData = [];
    timeLabels = [];
    
    for (let i = 0; i < maxDataPoints; i++) {
        cpuData.push(0);
        memData.push(0);
        timeLabels.push('');
    }
    
    try {
        cpuChart = new Chart(cpuCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'CPU Usage',
                    data: cpuData,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `CPU: ${ctx.parsed.y.toFixed(1)}%`
                        }
                    }
                },
                scales: {
                    x: { display: false },
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            color: '#888',
                            callback: (value) => `${value}%`
                        },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    }
                }
            }
        });
        
        memChart = new Chart(memCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'Memory Usage',
                    data: memData,
                    borderColor: '#2196F3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `Memory: ${ctx.parsed.y.toFixed(1)}%`
                        }
                    }
                },
                scales: {
                    x: { display: false },
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            color: '#888',
                            callback: (value) => `${value}%`
                        },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    }
                }
            }
        });
    } catch (error) {
        //console.error('Error initializing charts:', error);
    }
}

function updateChartData(cpuValue, memValue) {
    if (!cpuChart || !memChart) return;
    
    cpuData.push(cpuValue);
    memData.push(memValue);
    
    if (cpuData.length > maxDataPoints) {
        cpuData.shift();
        memData.shift();
    }
    
    cpuChart.data.datasets[0].data = cpuData;
    memChart.data.datasets[0].data = memData;
    
    cpuChart.update('none');
    memChart.update('none');
}

function updateNetworkSpeed(rx, tx) {
    const networkElement = document.getElementById('networkSpeedDisplay');
    if (!networkElement) return;
    
    const currentTime = Date.now();
    
    if (typeof updateNetworkSpeed.lastTime === 'undefined') {
        updateNetworkSpeed.lastTime = currentTime;
        updateNetworkSpeed.lastRx = rx;
        updateNetworkSpeed.lastTx = tx;
        return;
    }
    
    const timeDiff = (currentTime - updateNetworkSpeed.lastTime) / 1000;
    
    if (timeDiff > 0) {
        const rxSpeed = (rx - updateNetworkSpeed.lastRx) / timeDiff;
        const txSpeed = (tx - updateNetworkSpeed.lastTx) / timeDiff;
        const totalSpeed = rxSpeed + txSpeed;
        
        let displayText, color;
        
        if (totalSpeed < 1024) {
            displayText = totalSpeed.toFixed(1) + ' B/s';
            color = '#4CAF50';
        } else if (totalSpeed < 1024 * 1024) {
            displayText = (totalSpeed / 1024).toFixed(1) + ' KB/s';
            color = '#2196F3';
        } else {
            displayText = (totalSpeed / (1024 * 1024)).toFixed(1) + ' MB/s';
            color = '#E91E63';
        }
        
        networkElement.innerHTML = `${displayText}<br>
                                   <span style="font-size: 0.8rem; color: #888;">
                                   ↓${(rxSpeed < 1024 ? rxSpeed.toFixed(1) + ' B' : 
                                       rxSpeed < 1024 * 1024 ? (rxSpeed / 1024).toFixed(1) + ' KB' : 
                                       (rxSpeed / (1024 * 1024)).toFixed(1) + ' MB')}/s ↑
                                   ${(txSpeed < 1024 ? txSpeed.toFixed(1) + ' B' : 
                                       txSpeed < 1024 * 1024 ? (txSpeed / 1024).toFixed(1) + ' KB' : 
                                       (txSpeed / (1024 * 1024)).toFixed(1) + ' MB')}/s</span>`;
        networkElement.style.color = color;
        
        updateNetworkSpeed.lastTime = currentTime;
        updateNetworkSpeed.lastRx = rx;
        updateNetworkSpeed.lastTx = tx;
    }
}
updateNetworkSpeed.lastTime = undefined;
updateNetworkSpeed.lastRx = 0;
updateNetworkSpeed.lastTx = 0;

function startSystemMonitoring() {
    stopSystemMonitoring();
    
    if (cpuChart) {
        cpuChart.destroy();
        cpuChart = null;
    }
    if (memChart) {
        memChart.destroy();
        memChart = null;
    }
    
    if (document.getElementById('cpuChartCanvas') && typeof Chart !== 'undefined') {
        initCharts();
    } else if (typeof Chart === 'undefined') {
        const script = document.createElement('script');
        script.src = '/luci-static/spectra/js/chart.js';
        script.onload = initCharts;
        document.head.appendChild(script);
    }
    
    updateSystemInfo();
    
    systemMonitorInterval = setInterval(updateSystemInfo, 1000);
}

function stopSystemMonitoring() {
    if (systemMonitorInterval) {
        clearInterval(systemMonitorInterval);
        systemMonitorInterval = null;
    }
}

function initResizer() {
    const resizer = document.getElementById('resizer');
    const playerResizer = document.getElementById('playerResizer');
    const contentArea = document.getElementById('contentArea');
    const playerArea = document.getElementById('playerArea');
    const sideNav = document.getElementById('sideNav');
    
    if (resizer) {
        resizer.addEventListener('mousedown', function(e) {
            e.preventDefault();
            isResizing = true;
            startX = e.clientX;
            startWidth = sideNav.offsetWidth;
            resizer.classList.add('dragging');
            
            document.addEventListener('mousemove', handleSidebarResize);
            document.addEventListener('mouseup', stopResize);
        });
        
        resizer.addEventListener('touchstart', function(e) {
            e.preventDefault();
            isResizing = true;
            startX = e.touches[0].clientX;
            startWidth = sideNav.offsetWidth;
            resizer.classList.add('dragging');
            
            document.addEventListener('touchmove', handleSidebarResizeTouch);
            document.addEventListener('touchend', stopResizeTouch);
        });
    }
    
    if (playerResizer) {
        playerResizer.addEventListener('mousedown', function(e) {
            e.preventDefault();
            if (!playerArea.classList.contains('active')) return;
            
            isPlayerResizing = true;
            startX = e.clientX;
            startPlayerWidth = playerArea.offsetWidth;
            playerResizer.classList.add('dragging');
            
            document.addEventListener('mousemove', handlePlayerResize);
            document.addEventListener('mouseup', stopPlayerResize);
        });
        
        playerResizer.addEventListener('touchstart', function(e) {
            e.preventDefault();
            if (!playerArea.classList.contains('active')) return;
            
            isPlayerResizing = true;
            startX = e.touches[0].clientX;
            startPlayerWidth = playerArea.offsetWidth;
            playerResizer.classList.add('dragging');
            
            document.addEventListener('touchmove', handlePlayerResizeTouch);
            document.addEventListener('touchend', stopPlayerResizeTouch);
        });
    }
}

function handleSidebarResize(e) {
    if (!isResizing) return;
    
    const sideNav = document.getElementById('sideNav');
    const toggleIcon = document.querySelector('.fa-server');
    const deltaX = e.clientX - startX;
    let newWidth = startWidth + deltaX;
    
    newWidth = Math.max(70, Math.min(400, newWidth));
    
    if (sidebarCollapsed && newWidth > 70) {
        if (toggleIcon) {
            toggleIcon.style.transform = 'rotate(0deg)';
            toggleIcon.setAttribute('data-translate-tooltip', 'toggle_menu');
        }
        sidebarCollapsed = false;
        sideNav.classList.remove('collapsed');
    }
    
    if (!sidebarCollapsed) {
        sideNav.style.width = newWidth + 'px';
        sideNav.style.transition = 'none';
    }
}

function handleSidebarResizeTouch(e) {
    if (!isResizing || !e.touches.length) return;
    
    const sideNav = document.getElementById('sideNav');
    const toggleIcon = document.querySelector('.fa-server');
    const deltaX = e.touches[0].clientX - startX;
    let newWidth = startWidth + deltaX;
    
    newWidth = Math.max(70, Math.min(400, newWidth));
    
    if (sidebarCollapsed && newWidth > 70) {
        if (toggleIcon) {
            toggleIcon.style.transform = 'rotate(0deg)';
            toggleIcon.setAttribute('data-translate-tooltip', 'toggle_menu');
        }
        sidebarCollapsed = false;
        sideNav.classList.remove('collapsed');
    }
    
    if (!sidebarCollapsed) {
        sideNav.style.width = newWidth + 'px';
        sideNav.style.transition = 'none';
    }
}

function handlePlayerResize(e) {
    if (!isPlayerResizing) return;
    
    const playerArea = document.getElementById('playerArea');
    const deltaX = startX - e.clientX;
    let newWidth = startPlayerWidth + deltaX;
    
    newWidth = Math.max(300, Math.min(window.innerWidth * 0.8, newWidth));
    
    playerArea.style.width = newWidth + 'px';
    playerArea.style.transition = 'none';
    playerArea.style.flex = 'none';
}

function handlePlayerResizeTouch(e) {
    if (!isPlayerResizing || !e.touches.length) return;
    
    const playerArea = document.getElementById('playerArea');
    const deltaX = startX - e.touches[0].clientX;
    let newWidth = startPlayerWidth + deltaX;
    
    newWidth = Math.max(300, Math.min(window.innerWidth * 0.8, newWidth));
    
    playerArea.style.width = newWidth + 'px';
    playerArea.style.transition = 'none';
    playerArea.style.flex = 'none';
}

function stopResize() {
    isResizing = false;
    const resizer = document.getElementById('resizer');
    if (resizer) {
        resizer.classList.remove('dragging');
    }
    document.removeEventListener('mousemove', handleSidebarResize);
    document.removeEventListener('mouseup', stopResize);
    
    const sideNav = document.getElementById('sideNav');
    if (sideNav && !sidebarCollapsed) {
        localStorage.setItem('sidebarWidth', sideNav.offsetWidth);
        sideNav.style.transition = 'width 0.3s ease';
    }
}

function stopResizeTouch() {
    isResizing = false;
    const resizer = document.getElementById('resizer');
    if (resizer) {
        resizer.classList.remove('dragging');
    }
    document.removeEventListener('touchmove', handleSidebarResizeTouch);
    document.removeEventListener('touchend', stopResizeTouch);
    
    const sideNav = document.getElementById('sideNav');
    if (sideNav && !sidebarCollapsed) {
        localStorage.setItem('sidebarWidth', sideNav.offsetWidth);
        sideNav.style.transition = 'width 0.3s ease';
    }
}

function stopPlayerResize() {
    isPlayerResizing = false;
    const playerResizer = document.getElementById('playerResizer');
    if (playerResizer) {
        playerResizer.classList.remove('dragging');
    }
    document.removeEventListener('mousemove', handlePlayerResize);
    document.removeEventListener('mouseup', stopPlayerResize);
    
    const playerArea = document.getElementById('playerArea');
    if (playerArea) {
        localStorage.setItem('playerWidth', playerArea.offsetWidth);
        playerArea.style.transition = 'width 0.3s ease';
    }
}

function stopPlayerResizeTouch() {
    isPlayerResizing = false;
    const playerResizer = document.getElementById('playerResizer');
    if (playerResizer) {
        playerResizer.classList.remove('dragging');
    }
    document.removeEventListener('touchmove', handlePlayerResizeTouch);
    document.removeEventListener('touchend', stopPlayerResizeTouch);
    
    const playerArea = document.getElementById('playerArea');
    if (playerArea) {
        localStorage.setItem('playerWidth', playerArea.offsetWidth);
        playerArea.style.transition = 'width 0.3s ease';
    }
}

function updateCollapseButton(collapsed) {
    const toggleBtn = document.getElementById('collapseToggle');
    if (!toggleBtn) return;
    
    if (collapsed) {
        toggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        toggleBtn.style.left = '-12px';
        toggleBtn.setAttribute('data-translate-tooltip', 'expand_menu'); 
    } else {
        toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        toggleBtn.style.left = '-12px';
        toggleBtn.setAttribute('data-translate-tooltip', 'toggle_menu');
    }
}

function loadSavedWidths() {
    const savedPlayerWidth = localStorage.getItem('playerWidth');
    const playerArea = document.getElementById('playerArea');
    if (savedPlayerWidth && playerArea) {
        playerArea.style.width = savedPlayerWidth + 'px';
        playerArea.style.flex = 'none';
    }
}

function toggleSidebar() {
    const sideNav = document.getElementById('sideNav');
    const toggleIcon = document.querySelector('.fa-server');
    
    sidebarCollapsed = !sidebarCollapsed;
    
    if (sidebarCollapsed) {
        sideNav.style.width = '70px';
        sideNav.classList.add('collapsed');
        if (toggleIcon) {
            toggleIcon.style.transform = 'rotate(90deg)';
            toggleIcon.setAttribute('data-translate-tooltip', 'expand_menu');
        }
    } else {
        const savedWidth = localStorage.getItem('sidebarWidth');
        if (savedWidth && parseInt(savedWidth) > 70) {
            sideNav.style.width = savedWidth + 'px';
        } else {
            sideNav.style.width = '240px';
        }
        sideNav.classList.remove('collapsed');
        if (toggleIcon) {
            toggleIcon.style.transform = 'rotate(0deg)';
            toggleIcon.setAttribute('data-translate-tooltip', 'toggle_menu');
        }
    }
    
    localStorage.setItem('sidebarCollapsed', sidebarCollapsed ? 'true' : 'false');
}

function initSidebarState() {
    const savedState = localStorage.getItem('sidebarCollapsed');
    const toggleIcon = document.querySelector('.fa-server');
    const sideNav = document.getElementById('sideNav');
    
    if (savedState === 'true') {
        if (sideNav && toggleIcon) {
            sideNav.classList.add('collapsed');
            sideNav.style.width = '70px';
            toggleIcon.style.transform = 'rotate(90deg)';
            toggleIcon.setAttribute('data-translate-tooltip', 'expand_menu');
            sidebarCollapsed = true;
        }
    } else {
        const savedWidth = localStorage.getItem('sidebarWidth');
        if (sideNav) {
            if (savedWidth) {
                sideNav.style.width = savedWidth + 'px';
            } else {
                sideNav.style.width = '240px';
            }
            sideNav.classList.remove('collapsed');
            if (toggleIcon) {
                toggleIcon.style.transform = 'rotate(0deg)';
                toggleIcon.setAttribute('data-translate-tooltip', 'toggle_menu');
            }
            sidebarCollapsed = false;
        }
    }
}

function updateFullscreenIcon() {
    const mediaFullscreenBtn = document.querySelector('.btn.btn-teal[onclick*="toggleFullscreen"]');
    const diffFullscreenBtn = document.querySelector('#diffEditorModal .btn-outline-info');
    const editorFullscreenBtn = document.querySelector('#editorPanel .btn-outline-info[onclick*="toggleFullscreen"]');
    
    if (mediaFullscreenBtn) {
        const icon = mediaFullscreenBtn.querySelector('i');
        const textSpan = mediaFullscreenBtn.querySelector('span');
        
        if (icon && textSpan) {
            if (document.fullscreenElement) {
                icon.className = 'fas fa-compress';
                if (translations['exit_fullscreen']) {
                    textSpan.textContent = translations['exit_fullscreen'];
                } else {
                    textSpan.textContent = 'Exit Fullscreen';
                }
            } else {
                icon.className = 'fas fa-expand';
                if (translations['enter_fullscreen']) {
                    textSpan.textContent = translations['enter_fullscreen'];
                } else {
                    textSpan.textContent = 'Fullscreen Play';
                }
            }
        }
    }
    
    if (diffFullscreenBtn) {
        if (document.fullscreenElement) {
            diffFullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
            diffFullscreenBtn.setAttribute('title', translations['exit_fullscreen'] || 'Exit Fullscreen');
        } else {
            diffFullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
            diffFullscreenBtn.setAttribute('title', translations['enter_fullscreen'] || 'Enter Fullscreen');
        }
    }
    
    if (editorFullscreenBtn) {
        const icon = editorFullscreenBtn.querySelector('i');
        const textSpan = editorFullscreenBtn.querySelector('span');
        
        if (icon && textSpan) {
            if (document.fullscreenElement) {
                icon.className = 'fas fa-compress';
                textSpan.textContent = translations['exit_fullscreen'] || 'Exit Fullscreen';
            } else {
                icon.className = 'fas fa-expand';
                textSpan.textContent = translations['fullscreen'] || 'Fullscreen';
            }
        }
    }
}

async function loadFiles(path) {
    const fileGrid = document.getElementById('fileGrid');
    const loadingEl = document.getElementById('loadingFiles');
    
    if (!fileGrid || !loadingEl) return;
    
    fileGrid.innerHTML = '';
    loadingEl.style.display = 'block';
    
    try {
        const response = await fetch(`?action=list_files&path=${encodeURIComponent(path)}`);
        const data = await response.json();
        
        if (data.success) {
            currentPath = data.path;
            updateBreadcrumb(data.path);
            
            const pathDisplay = document.getElementById('currentPathDisplay');
            if (pathDisplay) {
                pathDisplay.textContent = currentPath;
            }
            
            let folderCount = 0;
            let fileCount = 0;
            let totalSize = 0;
            
            const mediaExts = [
                'mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac',
                'mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm',
                '3gp', '3g2', 'ogv', 'mpg', 'mpeg', 
                'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'
            ];
            
            const dirPlaylist = [];
            data.items.forEach(item => {
                if (!item.is_dir) {
                    const ext = item.ext.toLowerCase();
                    if (mediaExts.includes(ext)) {
                        dirPlaylist.push(item.path);
                    }
                }
            });
            
            playlistCache[currentPath] = dirPlaylist;
            
            if (data.items.length === 0) {
                fileGrid.innerHTML = `
                    <div class="empty-folder" style="grid-column: 1 / -1;">
                        <i class="fas fa-folder-open"></i>
                        <p data-translate="empty_folder">This folder is empty</p>
                    </div>`;
            } else {
                let html = '';
                data.items.forEach(item => {
                    const isDir = item.is_dir;
                    const isSelected = selectedFiles.has(item.path);
                    const safePath = item.path.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                    const safeName = item.name.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    const iconClass = isDir ? 'folder' : item.type;
                    
                    if (isDir) {
                        folderCount++;
                    } else {
                        fileCount++;
                        totalSize += item.size;
                    }
                    
                    html += `
                    <div class="file-item position-relative ${isSelected ? 'selected' : ''}" 
                         data-path="${safePath}"
                         data-type="${item.type}"
                         data-name="${safeName}"
                         data-is-dir="${isDir}"
                         data-size="${item.size}"
                         onclick="handleFileClick(event, '${safePath}')">

                        <div class="form-check position-absolute" style="top: 5px; left: 9px; z-index: 100; ${isSelected ? 'display: block;' : 'display: none;'}">
                            <input class="form-check-input" type="checkbox" 
                                   ${isSelected ? 'checked' : ''}
                                   onclick="event.stopPropagation(); toggleFileSelection('${safePath}', this.checked)">
                        </div>

                        <div class="file-icon mb-2">
                            ${getFileIcon(item.name, item.ext, isDir)}
                        </div>
                        <div class="file-name text-truncate w-100" title="${safeName}">
                            ${truncateFileName(safeName)}
                        </div>
                        <div class="file-size text-muted small mt-1">
                            ${isDir ? `<span data-translate="folder">Folder</span>`  : item.size_formatted}
                        </div>
                    </div>`;
                });
                fileGrid.innerHTML = html;
            }
            
            updateStatistics(folderCount, fileCount, totalSize);
            
        } else {
            showLogMessage(data.error || 'Failed to load files', 'error');
        }
    } catch (error) {
        console.error('Failed to load files:', error);
        showLogMessage('Failed to load files', 'error');
    } finally {
        loadingEl.style.display = 'none';
        updateSelectionInfo();
        updateLanguage(currentLang);
        
        setTimeout(() => {
            const highlightPath = sessionStorage.getItem('highlightFile');
            if (highlightPath) {
                const fileItem = document.querySelector(`[data-path="${highlightPath}"]`);
                if (fileItem) {
                    document.querySelectorAll('.file-item').forEach(el => {
                        el.style.backgroundColor = '';
                        el.style.borderColor = '';
                        el.style.boxShadow = '';
                    });
                    
                    fileItem.style.backgroundColor = 'rgba(76, 175, 80, 0.2)';
                    fileItem.style.borderColor = '#4CAF50';
                    fileItem.style.boxShadow = '0 0 10px rgba(76, 175, 80, 0.5)';
                    fileItem.style.transition = 'all 0.3s ease';
                    
                    fileItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    setTimeout(() => {
                        fileItem.style.backgroundColor = '';
                        fileItem.style.borderColor = '';
                        fileItem.style.boxShadow = '';
                    }, 15000);
                    
                    sessionStorage.removeItem('highlightFile');
                }
            }
        }, 500);
    }
    restorePlayingHighlight();

    initDragSelect();

    setTimeout(() => {
        initRightClick();
    }, 200);
}

function initDragSelect() {
    const fileGrid = document.getElementById('fileGrid');
    if (!fileGrid) return;
    
    let isDragging = false;
    let startX, startY;
    let dragBox = document.querySelector('.drag-select-box');
    
    if (!dragBox) {
        dragBox = document.createElement('div');
        dragBox.className = 'drag-select-box';
        document.body.appendChild(dragBox);
    }
    
    fileGrid.addEventListener('mousedown', function(e) {
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (e.target === fileGrid || e.target.classList.contains('empty-folder') || 
            e.target.parentElement === fileGrid || e.target === dragBox) {
            
            isDragging = true;
            startX = e.clientX + scrollLeft;
            startY = e.clientY + scrollTop;
            
            dragBox.style.left = startX + 'px';
            dragBox.style.top = startY + 'px';
            dragBox.style.width = '0px';
            dragBox.style.height = '0px';
            dragBox.style.display = 'block';
            
            fileGrid.classList.add('dragging');
            
            if (!e.ctrlKey && !e.metaKey) {
                selectedFiles.clear();
                updateFileSelection();
                updateSelectionInfo();
            }
            
            e.preventDefault();
        }
    });
    
    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        
        e.preventDefault();
        
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        const currentX = e.clientX + scrollLeft;
        const currentY = e.clientY + scrollTop;
        
        const left = Math.min(startX, currentX);
        const top = Math.min(startY, currentY);
        const width = Math.abs(startX - currentX);
        const height = Math.abs(startY - currentY);
        
        dragBox.style.left = left + 'px';
        dragBox.style.top = top + 'px';
        dragBox.style.width = width + 'px';
        dragBox.style.height = height + 'px';
        
        const dragRect = {
            left: left - scrollLeft,
            top: top - scrollTop,
            right: left + width - scrollLeft,
            bottom: top + height - scrollTop
        };
        
        const fileItems = fileGrid.querySelectorAll('.file-item');
        fileItems.forEach(item => {
            const rect = item.getBoundingClientRect();
            const isOverlap = !(rect.right < dragRect.left || 
                               rect.left > dragRect.right || 
                               rect.bottom < dragRect.top || 
                               rect.top > dragRect.bottom);
            
            const path = item.getAttribute('data-path');
            
            if (isOverlap) {
                selectedFiles.add(path);
                item.classList.add('drag-selected');
            } else {
                if (!e.ctrlKey && !e.metaKey) {
                    selectedFiles.delete(path);
                    item.classList.remove('drag-selected');
                }
            }
        });
        
        updateFileSelection();
        updateSelectionInfo();
    });
    
    document.addEventListener('mouseup', function(e) {
        if (isDragging) {
            isDragging = false;
            dragBox.style.display = 'none';
            fileGrid.classList.remove('dragging');
            
            document.querySelectorAll('.file-item.drag-selected').forEach(item => {
                item.classList.remove('drag-selected');
            });
        }
    });
}

function initRightClick() {
    const fileGrid = document.getElementById('fileGrid');
    if (!fileGrid) return;
    
    fileGrid.removeEventListener('contextmenu', handleRightClick);
    
    fileGrid.addEventListener('contextmenu', handleRightClick);
}

function handleRightClick(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const menu = document.getElementById('fileContextMenu');
    const overlay = document.getElementById('contextMenuOverlay');
    
    if (!menu || !overlay) return;
    
    hideAllContextMenuItems();

    const fileItem = event.target.closest('.file-item');
    
    if (fileItem) {
        const path = fileItem.getAttribute('data-path');
        const isDir = fileItem.getAttribute('data-is-dir') === 'true';
        const fileName = path.split('/').pop();
        const ext = fileName.toLowerCase().split('.').pop();
        
        if (!selectedFiles.has(path) && !event.ctrlKey && !event.metaKey) {
            selectedFiles.clear();
            selectedFiles.add(path);
        } else if (!selectedFiles.has(path) && (event.ctrlKey || event.metaKey)) {
            selectedFiles.add(path);
        }
        
        updateFileSelection();
        updateSelectionInfo();
        
        showMenuItem('fileOpenItem');
        showMenuItem('fileDownloadItem');
        showMenuItem('fileCutItem');
        showMenuItem('fileCopyItem');
        showMenuItem('fileCopyPathItem');
        showMenuItem('fileRenameItem');
        document.getElementById('fileBatchRenameItem').style.display = 'flex';
        showMenuItem('fileDeleteItem');
        showMenuItem('fileChmodItem');
        showMenuItem('filePropertiesItem');
        showMenuItem('fileTerminalItem');

        const installItem = document.getElementById('fileInstallItem');
        if (installItem) {
            const installExts = ['ipk', 'apk', 'run'];
            if (!isDir && installExts.includes(ext) && selectedFiles.size === 1) {
                installItem.style.display = 'flex';
            } else {
                installItem.style.display = 'none';
            }
        }

        const qrcodeItem = document.getElementById('fileQrcodeItem');
        if (qrcodeItem) {
            if (!isDir && selectedFiles.size === 1) {
                qrcodeItem.style.display = 'flex';
            } else {
                qrcodeItem.style.display = 'none';
            }
        }

        const hashItem = document.getElementById('fileHashItem');
        if (hashItem) {
            if (!isDir && selectedFiles.size === 1) {
                hashItem.style.display = 'flex';
            } else {
                hashItem.style.display = 'none';
            }
        }
        
        const mediaExts = ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 
                           'mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm',
                           '3gp', '3g2', 'ogv', 'mpg', 'mpeg',
                           'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        if (!isDir && mediaExts.includes(ext)) {
            showMenuItem('filePlayItem');
            showMenuItem('fileConvertItem');
            showMenuItem('fileSetAsWallpaperItem'); 
        }
        
        const textExts = ['txt', 'log', 'conf', 'ini', 'json', 'xml', 'html', 
                          'css', 'js', 'php', 'py', 'sh', 'md', 'yml', 'yaml'];
        if (!isDir && textExts.includes(ext)) {
            showMenuItem('fileEditItem');
        }
        
        showMenuItem('archiveMenuItem');
        document.getElementById('archiveCompressItem').style.display = 'flex';
        
        const archiveExts = ['zip', 'tar', 'gz', 'bz2', '7z', 'rar', 'tgz', 'tbz2'];
        if (!isDir && archiveExts.includes(ext)) {
            document.getElementById('archiveExtractHereItem').style.display = 'flex';
            document.getElementById('archiveExtractToItem').style.display = 'flex';
        }

        const globalPasteItem = document.getElementById('globalPasteItem');
        if (globalPasteItem) {
            globalPasteItem.style.display = 'none';
        }
        
    } else {
        selectedFiles.clear();
        updateFileSelection();
        updateSelectionInfo();
        
        showMenuItem('emptyNewFolderItem');
        showMenuItem('emptyNewFileItem');
        showMenuItem('emptyUploadItem');
        showMenuItem('emptyRefreshItem');
        showMenuItem('emptySelectAllItem');

        if (localStorage.getItem('currentWallpaper')) {
            showMenuItem('emptyRestoreThemeItem');
        }

        document.getElementById('fileBatchRenameItem').style.display = 'none';
        updatePasteMenuState();
    }
    
    positionContextMenu(menu, event);
    menu.style.display = 'block';
    overlay.style.display = 'block';
}

function updateStatistics(folderCount, fileCount, totalSize) {
    document.getElementById('totalFolders').textContent = folderCount;
    document.getElementById('totalFiles').textContent = fileCount;
    document.getElementById('totalSize').textContent = formatFileSize(totalSize);
    updateSelectedCount();
}

function updateSelectedCount() {
    const selectedItems = document.getElementById('selectedItems');
    if (selectedItems) {
        selectedItems.textContent = selectedFiles.size;
    }
}

function updateBreadcrumb(path) {
    const breadcrumb = document.getElementById('breadcrumb');
    if (!breadcrumb) return;
    
    const parts = path.split('/').filter(p => p);
    let html = '';
    
    html += `<div class="d-flex flex-wrap align-items-center gap-4">`;
    
    html += `<div class="d-flex align-items-center flex-wrap" style="gap: 8px;">`;
    
    html += `<div class="breadcrumb-item cursor-pointer" onclick="navigateTo('/')">
                <i class="fas fa-home me-1" style="color: oklch(var(--l) var(--c) var(--base-hue-5));"></i>
                <span data-translate="root">Root</span>
            </div>`;
    
    let currentPath = '';
    for (let i = 0; i < parts.length; i++) {
        currentPath += '/' + parts[i];
        const isLast = i === parts.length - 1;
        const safePath = currentPath.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        
        html += `<span class="mx-1 text-muted">/</span>`;
        html += `<div class="breadcrumb-item ${isLast ? 'breadcrumb-current fw-bold' : 'cursor-pointer'}" 
                     onclick="${!isLast ? `navigateTo('${safePath}')` : ''}">
                    <span>${parts[i]}</span>
                </div>`;
    }
    
    html += `</div>`;
    
    html += `<div class="d-flex align-items-center" style="border-left: 1px solid var(--accent-color); padding-left: 1.5rem;">`;
    html += `<div class="files-statistics-bar d-flex align-items-center gap-4 flex-wrap">`;
    html += `<div class="stat-item text-center" style="min-width: 60px;">
                <div class="stat-value fw-bold text-primary" id="totalFolders">0</div>
                <div class="stat-label small text-muted" data-translate="folder">Folders</div>
            </div>
            <div class="stat-item text-center" style="min-width: 60px;">
                <div class="stat-value fw-bold text-success" id="totalFiles">0</div>
                <div class="stat-label small text-muted" data-translate="file">Files</div>
            </div>
            <div class="stat-item text-center" style="min-width: 80px;">
                <div class="stat-value fw-bold text-info" id="totalSize">0 B</div>
                <div class="stat-label small text-muted" data-translate="total_size">Total Size</div>
            </div>
            <div class="stat-item text-center" style="min-width: 60px;">
                <div class="stat-value fw-bold text-warning" id="selectedItems">0</div>
                <div class="stat-label small text-muted" data-translate="selected">Selected</div>
            </div>`;
    html += `</div></div>`;
    
    html += `</div>`;
    
    breadcrumb.innerHTML = html;
    
    const pathDisplay = document.getElementById('currentPathDisplay');
    if (pathDisplay) {
        pathDisplay.innerHTML = `<span data-translate="root">Root</span>`;
    }
    updateLanguage(currentLang);
}

function navigateTo(path) {
    if (!path) return;
    loadFiles(path);
}

function navigateUp() {
    const parent = currentPath.split('/').slice(0, -1).join('/');
    if (parent === '') {
        navigateTo('/');
    } else {
        navigateTo(parent);
    }
}

function refreshFiles() {
    loadFiles(currentPath);
    updateSystemInfo();
    setTimeout(() => {
        initRightClick();
    }, 300);
}

function truncateFileName(name, maxLength = 20) {
    if (name.length <= maxLength) return name;
    return name.substring(0, maxLength - 3) + '...';
}

function handleDoubleClick(path, isDir, type) {
    if (isDir) {
        navigateTo(path);
    } else {
        const fileName = path.split('/').pop();
        const ext = fileName.toLowerCase().split('.');
        const fileExt = ext.length > 1 ? ext.pop() : '';
        
        const mediaExts = [
            'mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac',
            'mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm',
            '3gp', '3g2', 'ogv', 'mpg', 'mpeg',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'
        ];
        
        const textExts = [
            'txt', 'log', 'conf', 'ini', 'json', 'xml', 'html', 'htm',
            'css', 'js', 'php', 'py', 'sh', 'md', 'yaml', 'yml',
            'csv', 'sql', 'bat', 'cmd'
        ];
        
        if (mediaExts.includes(fileExt)) {
            playMedia(path);
        }
        else if (textExts.includes(fileExt) || fileExt === '') {
            editFile(path);
        }
        else {
            editFile(path);
        }
    }
}

function toggleView(viewType = null) {
    const viewToggleBtn = document.getElementById('viewToggleBtn');
    const viewToggleIcon = document.getElementById('viewToggleIcon');
    const viewToggleText = document.getElementById('viewToggleText');
    
    const fileManagerToolbar = document.getElementById('fileManagerToolbar');
    const fileGrid = document.getElementById('fileGrid');
    const selectionInfo = document.getElementById('selectionInfo');
    const breadcrumb = document.getElementById('breadcrumb');
    const viewToggle = document.querySelector('.view-toggle');
    
    const editorToolbar = document.getElementById('editorToolbar');
    const editorPanel = document.getElementById('editorPanel');
    const editorTabsSwitcher = document.getElementById('editorTabsSwitcher');
    const editorTabsList = document.getElementById('editorTabsList');
    const editorPanelContent = document.getElementById('editorPanelContent');
    
    const mainToolbar = document.querySelector('.toolbar');
    const editorPanelDiv = document.getElementById('editorPanel');
    
    if (viewType) {
        currentView = viewType;
    } else {
        currentView = currentView === 'files' ? 'editor' : 'files';
    }
    
    if (currentView === 'files') {
        editorTabs.forEach(tab => {
            if (tab.monacoEditorInstance) {
                const model = tab.monacoEditorInstance.getModel();
                if (model) {
                    tab.viewState = tab.monacoEditorInstance.saveViewState();
                }
            }
        });
    }

    if (currentView === 'editor') {
        viewToggleIcon.className = 'fas fa-folder';
        viewToggleText.textContent = translations['file_view'] || 'File View';
        viewToggleText.setAttribute('data-translate', 'file_view');
        
        if (mainToolbar) mainToolbar.style.display = 'none';
        if (fileManagerToolbar) fileManagerToolbar.style.display = 'none';
        if (fileGrid) fileGrid.style.display = 'none';
        if (selectionInfo) selectionInfo.style.display = 'none';
        if (breadcrumb) breadcrumb.style.display = 'none';
        if (viewToggle) viewToggle.style.display = 'none';
        
        if (editorToolbar) editorToolbar.style.display = 'flex';
        if (editorPanelDiv) {
            editorPanelDiv.style.display = 'block';
            editorPanelDiv.style.maxHeight = '';
        }
        if (editorTabsSwitcher) editorTabsSwitcher.style.display = 'block';
        if (editorTabsList) editorTabsList.style.display = 'flex';
        if (editorPanelContent) editorPanelContent.style.display = 'block';
        
        if (editorPanel) {
            editorPanel.style.maxHeight = '';
        }
        
    } else {
        viewToggleIcon.className = 'fas fa-edit';
        viewToggleText.textContent = translations['editor_view'] || 'Editor View';
        viewToggleText.setAttribute('data-translate', 'editor_view');
        
        if (editorToolbar) editorToolbar.style.display = 'none';
        if (editorPanelDiv) editorPanelDiv.style.display = 'none';
        if (editorTabsSwitcher) editorTabsSwitcher.style.display = 'none';
        if (editorTabsList) editorTabsList.style.display = 'none';
        if (editorPanelContent) editorPanelContent.style.display = 'none';
        
        if (mainToolbar) mainToolbar.style.display = 'flex';
        if (fileManagerToolbar) fileManagerToolbar.style.display = 'flex';
        if (fileGrid) fileGrid.style.display = 'grid';
        if (selectionInfo) selectionInfo.style.display = 'flex';
        if (breadcrumb) breadcrumb.style.display = 'flex';
        if (viewToggle) viewToggle.style.display = 'flex';
    }
}

function isBinaryContent(content) {
    if (!content || content.length === 0) return false;
    
    const checkLength = Math.min(1024, content.length);
    let nullBytes = 0;
    let controlChars = 0;
    let asciiChars = 0;
    
    for (let i = 0; i < checkLength; i++) {
        const charCode = content.charCodeAt(i);
        
        if (charCode === 0) nullBytes++;
        if (charCode < 9 || (charCode > 13 && charCode < 32)) controlChars++;
        if ((charCode >= 32 && charCode <= 126) || charCode === 9 || charCode === 10 || charCode === 13) asciiChars++;
    }
    
    if (nullBytes > 0) return true;
    
    const controlRatio = controlChars / checkLength;
    const asciiRatio = asciiChars / checkLength;
    
    return (controlRatio > 0.3 || asciiRatio < 0.7);
}

function getBinaryPreview(content) {
    if (!content || content.length === 0) return '[Empty file]';
    
    const previewLength = Math.min(200, content.length);
    let preview = '';
    
    for (let i = 0; i < previewLength; i++) {
        const char = content[i];
        if (char >= ' ' && char <= '~') {
            preview += char;
        } else {
            preview += '.';
        }
    }
    
    if (content.length > previewLength) {
        preview += '...';
    }
    
    return preview;
}

function handleFileClick(event, filePath) {
    if (event.target.type === 'checkbox' || 
        event.target.closest('.form-check') ||
        event.target.closest('input[type="checkbox"]')) {
        
        const checkbox = event.target.type === 'checkbox' ? event.target : 
                        event.target.querySelector('input[type="checkbox"]') ||
                        event.target.closest('input[type="checkbox"]');
        
        if (checkbox) {
            event.stopPropagation();
            
            if (checkbox.checked) {
                selectedFiles.add(filePath);
            } else {
                selectedFiles.delete(filePath);
            }
            
            updateFileSelection();
            updateSelectionInfo();
        }
        return;
    }
    
    event.preventDefault();
    event.stopPropagation();
    
    const fileItem = document.querySelector(`.file-item[data-path="${filePath}"]`);
    if (fileItem) {
        const isDir = fileItem.getAttribute('data-is-dir') === 'true';
        const type = fileItem.getAttribute('data-type');
        
        if (isDir) {
            navigateTo(filePath);
        } else {
            const fileName = filePath.split('/').pop();
            const ext = fileName.toLowerCase().split('.').pop();
            
            const audioExts = [
                'mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 'wma', 'opus',
                'ape', 'wv', 'tta', 'tak', 'dts', 'dsf', 'dff', 'sacd',
                'mid', 'midi', 'rmi', 'kar', 'mp2', 'caf', 'm4r', 'xmf',
                'ac3', 'eac3', 'truehd', 'thd', 'pcm', 'adpcm', 'amr',
                'awb', 'sln', 'vox', 'gsm', 'ra', 'ram', 'au', 'snd',
                'voc', 'cda', '8svx', 'aiff', 'aif', 'aifc', 'afc',
                'weba', 'mka', 'spx', 'oga', 'tta', 'm3u', 'm3u8', 'pls'
            ];
            
            const videoExts = [
                'mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v',
                '3gp', '3g2', 'ogv', 'mpg', 'mpeg', 'mpe', 'mpv', 'm2v',
                'ts', 'm2ts', 'mts', 'm2t', 'tod', 'mod', 'vro',
                'vob', 'ifo', 'bup', 'iso', 'img','mj2', 'drc', 'dnxhd', 'prores',
                'rm', 'rmvb', 'rv', 'ra', 'ram',
                'qt', 'hdmov', 'moov', 'dv', 'mqv',
                'asf', 'asx', 'wm', 'wmx', 'wvx',
                'divx', 'xvid', 'f4v', 'f4p', 'f4a', 'f4b',
                'swf', 'fla', 'avchd', 'mxf', 'gxf', 'lxv',
                'nsv', 'nut', 'nuv', 'ogm', 'ogx', 'bik', 'smk',
                'vp6', 'vp7', 'vp8', 'vp9', 'av1', 'hevc', 'h264', 'h265'
            ];
            
            const imageExts = [
                'jpg', 'jpeg', 'jpe', 'jfif', 'png', 'gif', 'bmp', 'webp',
                'svg', 'svgz', 'ico', 'cur',
                'raw', 'cr2', 'cr3', 'crw', 'nef', 'nrw', 'arw', 'srf', 'sr2',
                'raf', 'dng', 'orf', 'rw2', 'pef', 'ptx', 'x3f', 'erf', 'mrw',
                'mef', 'mdc', 'kdc', 'dcr', 'k25', 'bay', 'bmq', 'ciff',
                'psd', 'psb', 'ai', 'eps', 'epsf', 'epsi',
                'tiff', 'tif', 'djvu', 'djv', 'jxr', 'wdp', 'hdp',
                'heic', 'heif', 'heics', 'heifs', 'avci', 'avcs',
                'exr', 'hdr', 'pfm', 'ppm', 'pgm', 'pbm', 'pnm',
                'pcx', 'tga', 'icb', 'vda', 'vst', 'pix', 'pxr',
                'xbm', 'xpm', 'wbmp', 'cals', 'fpx', 'fpx', 'pcd',
                'psp', 'pspimage', 'xcf', 'kra', 'cpt', 'pat', 'abr'
            ];
            
            const textExts = [
                'txt', 'log', 'conf', 'ini', 'cfg', 'config', 'properties',
                'json', 'xml', 'html', 'htm', 'xhtml', 'css', 'scss', 'sass', 'less',
                'js', 'jsx', 'ts', 'tsx', 'vue', 'php', 'php3', 'php4', 'php5', 'php7', 'phtml',
                'py', 'pyw', 'rb', 'pl', 'pm', 'lua', 'go', 'rs', 'swift', 'kt', 'kts', 'scala',
                'sh', 'bash', 'zsh', 'fish', 'ash', 'dash', 'bat', 'cmd', 'ps1', 'psm1',
                'md', 'markdown', 'rst', 'tex', 'latex', 'yaml', 'yml', 'toml',
                'csv', 'tsv', 'sql', 'mysql', 'pgsql', 'plsql',
                'diff', 'patch', 'gitignore', 'gitattributes', 'editorconfig',
                'dockerfile', 'makefile', 'cmake', 'gradle',
                'hosts', 'nginx', 'apache', 'htaccess'
            ];
            
            const installExts = ['ipk', 'apk', 'run'];
            
            if (audioExts.includes(ext) || videoExts.includes(ext) || imageExts.includes(ext)) {
                playMedia(filePath);
            }
            else if (installExts.includes(ext)) {
                selectedFiles.clear();
                selectedFiles.add(filePath);
                updateFileSelection();
                showInstallDialog();
            }
            else if (textExts.includes(ext) || ext === '') {
                editFile(filePath);
            }
            else {
                editFile(filePath);
            }
        }
    }
}

function handleCheckboxClick(event, filePath) {
    event.stopPropagation();
    
    const checkbox = event.target;
    
    if (checkbox.checked) {
        selectedFiles.add(filePath);
    } else {
        selectedFiles.delete(filePath);
    }
    
    updateFileSelection();
    updateSelectionInfo();
}

function toggleFileSelection(filePath) {
    if (selectedFiles.has(filePath)) {
        selectedFiles.delete(filePath);
    } else {
        selectedFiles.add(filePath);
    }
    updateFileSelection();
    updateSelectionInfo();
}

function updateFileSelection() {
    document.querySelectorAll('.file-item').forEach(item => {
        const path = item.getAttribute('data-path');
        const checkbox = item.querySelector('input[type="checkbox"]');
        
        if (selectedFiles.has(path)) {
            item.classList.add('selected');
            if (checkbox) {
                checkbox.checked = true;
            }
            const checkboxContainer = item.querySelector('.file-checkbox');
            if (checkboxContainer) {
                checkboxContainer.style.display = 'flex';
                checkboxContainer.classList.add('visible');
                checkboxContainer.classList.remove('invisible');
            }
        } else {
            item.classList.remove('selected');
            if (checkbox) {
                checkbox.checked = false;
            }
            if (!item.classList.contains('hover') && !item.classList.contains('drag-selected')) {
                const checkboxContainer = item.querySelector('.file-checkbox');
                if (checkboxContainer) {
                    checkboxContainer.style.display = 'none';
                    checkboxContainer.classList.remove('visible');
                    checkboxContainer.classList.add('invisible');
                }
            }
        }
    });
    updateSelectedCount();
}

function updateSelectionInfo() {
    const toolbar = document.getElementById('selectionToolbar');
    const selectedInfo = document.getElementById('selectedInfo');
    
    if (!toolbar || !selectedInfo) return;
    
    const count = selectedFiles.size;
    
    if (count > 0) {
        let totalSize = 0;
        selectedFiles.forEach(path => {
            const fileItem = document.querySelector(`.file-item[data-path="${path}"]`);
            if (fileItem) {
                const size = parseInt(fileItem.getAttribute('data-size') || '0');
                totalSize += size;
            }
        });
        
        const sizeFormatted = formatFileSize(totalSize);
        const selectedText = translations['selected_count'] || 'Selected';
        const itemsText = translations['items'] || 'item(s)';
        const totalText = translations['total_size'] || 'Total';
        
        selectedInfo.textContent = `${selectedText} ${count} ${itemsText}, ${totalText} ${sizeFormatted}`;
        toolbar.classList.remove('d-none');
    } else {
        toolbar.classList.add('d-none');
    }
    updateSelectAllCheckbox();
    updateEmptySelectAllItem();
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (!selectAllCheckbox) return;
    
    const fileItems = document.querySelectorAll('.file-item');
    const label = document.querySelector('label[for="selectAllCheckbox"]');
    
    if (selectAllCheckbox.checked) {
        fileItems.forEach(item => {
            const path = item.getAttribute('data-path');
            if (path) {
                selectedFiles.add(path);
            }
        });
        if (label) {
            label.innerHTML = translations['invertSelection'] || 'Deselect All';
        }
    } else {
        selectedFiles.clear();
        if (label) {
            label.innerHTML = translations['select_all'] || 'Select All';
        }
    }

    updateFileSelection();
    updateSelectionInfo();
}

function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const label = document.querySelector('label[for="selectAllCheckbox"]');
    if (!selectAllCheckbox || !label) return;
    
    const fileItems = document.querySelectorAll('.file-item');
    const checkedCount = selectedFiles.size;
    
    if (fileItems.length === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        label.innerHTML = translations['select_all'] || 'Select All';
    } else if (checkedCount === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        label.innerHTML = translations['select_all'] || 'Select All';
    } else if (checkedCount === fileItems.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
        label.innerHTML = translations['invertSelection'] || 'Deselect All';
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
        label.innerHTML = translations['select_all'] || 'Select All';
    }
}

function toggleEmptySelectAll() {
    const fileItems = document.querySelectorAll('.file-item');
    const allSelected = fileItems.length > 0 && fileItems.length === selectedFiles.size;
    
    if (allSelected) {
        clearSelection();
    } else {
        selectAllFiles();
    }
    hideFileContextMenu();
}

function updateEmptySelectAllItem() {
    const emptySelectAllItem = document.getElementById('emptySelectAllItem');
    if (!emptySelectAllItem) return;
    
    const fileItems = document.querySelectorAll('.file-item');
    const allSelected = fileItems.length > 0 && fileItems.length === selectedFiles.size;
    const icon = document.getElementById('emptySelectAllIcon');
    const text = document.getElementById('emptySelectAllText');
    
    if (allSelected) {
        icon.className = 'fas fa-times-circle me-2';
        text.textContent = translations['deselect_all'] || 'Deselect All';
    } else {
        icon.className = 'fas fa-check-square me-2';
        text.textContent = translations['select_all'] || 'Select All';
    }
}

function changeViewMode(mode) {
    viewMode = mode;
    const fileGrid = document.getElementById('fileGrid');
    if (!fileGrid) return;
    
    const toggleButtons = document.querySelectorAll('.view-toggle-btn');
    toggleButtons.forEach(btn => btn.classList.remove('active'));
    
    if (mode === 'grid') {
        fileGrid.classList.add('folder-view');
        document.querySelector('.view-toggle-btn[onclick*="grid"]').classList.add('active');
    } else {
        fileGrid.classList.remove('folder-view');
        document.querySelector('.view-toggle-btn[onclick*="list"]').classList.add('active');
    }
}

function clearSelection() {
    selectedFiles.clear();
    updateFileSelection();
    updateSelectionInfo();
    
    const toolbar = document.getElementById('selectionToolbar');
    if (toolbar) {
        toolbar.classList.add('d-none');
    }
    
    updateSelectAllCheckbox();
    updateEmptySelectAllItem();
}

function showMultipleFileInfo() {
    const dialog = document.getElementById('fileInfoDialog');
    const content = document.getElementById('fileInfoContent');
    const overlay = document.getElementById('contextMenuOverlay');
    
    if (!dialog || !content || !overlay) return;
    
    let totalSize = 0;
    let folderCount = 0;
    let fileCount = 0;
    
    selectedFiles.forEach(path => {
        if (path.toLowerCase().includes('.')) {
            fileCount++;
        } else {
            folderCount++;
        }
    });
    
    content.innerHTML = `
        <div class="info-row">
            <div class="info-label">
                ${translations['selected_items'] || 'Selected Items'}:
            </div>
            <div class="info-value">${selectedFiles.size}</div>
        </div>
        <div class="info-row">
            <div class="info-label">
                ${translations['files'] || 'Files'}:
            </div>
            <div class="info-value">${fileCount}</div>
        </div>
        <div class="info-row">
            <div class="info-label">
                ${translations['folders'] || 'Folders'}:
            </div>
            <div class="info-value">${folderCount}</div>
        </div>
        <div class="info-row">
            <div class="info-label">
                ${translations['paths'] || 'Paths'}:
            </div>
            <div class="info-value">
                ${Array.from(selectedFiles).map(p => 
                    `<div class="mb-1">${p}</div>`
                ).join('')}
            </div>
        </div>
    `;
    
    dialog.style.display = 'block';
    overlay.style.display = 'block';
}

function positionContextMenu(menu, event) {
    const originalDisplay = menu.style.display;
    menu.style.display = 'block';
    menu.style.visibility = 'hidden';
    
    const menuWidth = menu.offsetWidth || 280;
    const menuHeight = menu.offsetHeight || 400;
    
    let left = event.clientX;
    let top = event.clientY;
    
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;
    
    const spaceRight = windowWidth - left;
    const spaceLeft = left;
    const spaceBottom = windowHeight - top;
    const spaceTop = top;
    
    if (spaceRight >= menuWidth + 10) {
        left = left;
    } else if (spaceLeft >= menuWidth + 10) {
        left = left - menuWidth;
    } else {
        left = (windowWidth - menuWidth) / 2;
    }
    
    top = top + 200;
    
    left = Math.max(10, Math.min(left, windowWidth - menuWidth - 10));
    top = Math.max(10, Math.min(top, windowHeight - menuHeight - 10));
    
    menu.style.position = 'fixed';
    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
    menu.style.visibility = 'visible';
    
    if (originalDisplay === 'none') {
        menu.style.display = originalDisplay;
    }
}

function handleDocumentClickForMenu(event) {
    const menu = document.getElementById('fileContextMenu');
    const overlay = document.getElementById('contextMenuOverlay');
    
    if (menu && menu.style.display === 'block' && !menu.contains(event.target)) {
        hideFileContextMenu();
    }
}

function hideAllContextMenuItems() {
    const allMenuItems = [
        'fileOpenItem', 'filePlayItem', 'fileEditItem', 'fileDownloadItem',
        'fileCutItem', 'fileCopyItem', 'filePasteItem', 'fileRenameItem',
        'fileDeleteItem', 'fileChmodItem', 'filePropertiesItem', 'fileTerminalItem',
        'emptyNewFolderItem', 'emptyNewFileItem', 'emptyUploadItem',
        'emptyRefreshItem', 'emptySelectAllItem', 'fileCopyPathItem',
        'globalPasteItem', 'fileBatchRenameItem',
        'archiveMenuItem', 'fileConvertItem',
        'fileInstallItem', 'fileHashItem', 'fileQrcodeItem',
        'fileSetAsWallpaperItem',
        'emptyRestoreThemeItem' 
    ];
    
    allMenuItems.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.style.display = 'none';
        }
    });
    
    const allDividers = [
        'fileDivider1', 'fileDivider2', 'archiveDivider',
        'emptyDivider1', 'emptyDivider2', 'globalPasteDivider'
    ];
    
    allDividers.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.style.display = 'none';
        }
    });
    
    const submenu = document.getElementById('archiveSubmenu');
    if (submenu) {
        submenu.style.display = 'none';
    }
}

function showFileMenuItems(isDir, ext) {
    showMenuItem('fileOpenItem');
    showMenuItem('fileDownloadItem');
    showMenuItem('fileCutItem');
    showMenuItem('fileCopyItem');
    showMenuItem('fileRenameItem');
    showMenuItem('fileDeleteItem');
    showMenuItem('fileChmodItem');
    showMenuItem('filePropertiesItem');
    showMenuItem('fileTerminalItem');

    const mediaExts = ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 
                       'mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm',
                       '3gp', '3g2', 'ogv', 'mpg', 'mpeg',
                       'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
    if (!isDir && mediaExts.includes(ext)) {
        showMenuItem('filePlayItem');
    }
    
    const textExts = ['txt', 'log', 'conf', 'ini', 'json', 'xml', 'html', 
                      'css', 'js', 'php', 'py', 'sh', 'md', 'yml', 'yaml'];
    if (!isDir && textExts.includes(ext)) {
        showMenuItem('fileEditItem');
    }

    showMenuItem('fileDivider1');
    showMenuItem('fileDivider2');
    
    const archiveMenuItem = document.getElementById('archiveMenuItem');
    const compressItem = document.getElementById('archiveCompressItem');
    const extractHereItem = document.getElementById('archiveExtractHereItem');
    const extractToItem = document.getElementById('archiveExtractToItem');
    
    if (archiveMenuItem) {
        archiveMenuItem.style.display = 'flex';
        showMenuItem('archiveDivider');
    }
    
    if (compressItem) {
        compressItem.style.display = 'flex';
    }
    
    const archiveExts = ['zip', 'tar', 'gz', 'bz2', '7z', 'rar', 'tgz', 'tbz2'];
    if (!isDir && archiveExts.includes(ext)) {
        if (extractHereItem) extractHereItem.style.display = 'flex';
        if (extractToItem) extractToItem.style.display = 'flex';
    }
}

function showEmptyAreaMenuItems() {
    showMenuItem('emptyNewFolderItem');
    showMenuItem('emptyNewFileItem');
    showMenuItem('emptyUploadItem');
    showMenuItem('emptyDivider1');
    showMenuItem('emptyRefreshItem');
    showMenuItem('emptySelectAllItem');
    showMenuItem('emptyDivider2');
    updateEmptySelectAllItem();
}

function showMenuItem(id) {
    const element = document.getElementById(id);
    if (element) {
        element.style.display = 'flex';
    }
}

function updatePasteMenuState() {
    const hasClipboard = clipboardItems && clipboardItems.paths && clipboardItems.paths.size > 0;
    const pasteAction = clipboardItems?.action;
    
    const globalPasteItem = document.getElementById('globalPasteItem');
    const globalPasteDivider = document.getElementById('globalPasteDivider');
    
    if (globalPasteItem) {
        if (hasClipboard) {
            globalPasteItem.style.display = 'flex';
            
            const hintSpan = globalPasteItem.querySelector('#pasteActionHint') || 
                            (() => {
                                const span = document.createElement('span');
                                span.id = 'pasteActionHint';
                                span.style.marginLeft = 'auto';
                                span.style.fontSize = '0.8rem';
                                span.style.opacity = '0.7';
                                globalPasteItem.appendChild(span);
                                return span;
                            })();
            
            const actionText = pasteAction === 'cut' 
                ? (translations['cut'] || 'Cut') 
                : (translations['copy'] || 'Copied');
            const count = clipboardItems.paths.size;
            
            const hintText = (translations['items_with_action'] || '{count}item(s) ({action})')
                .replace('{count}', count)
                .replace('{action}', actionText);
            
            hintSpan.textContent = hintText;
        } else {
            globalPasteItem.style.display = 'none';
        }
    }
    
    if (globalPasteDivider) {
        globalPasteDivider.style.display = hasClipboard ? 'block' : 'none';
    }
    
    const filePasteItem = document.getElementById('filePasteItem');
    if (filePasteItem) {
        const isFileItem = fileContextMenuTarget && 
                          fileContextMenuTarget.classList.contains('file-item');
        filePasteItem.style.display = hasClipboard && isFileItem ? 'flex' : 'none';
    }
}

let clipboardItems = {
    paths: new Set(),
    action: null,
    sourcePath: null
};

function copyToClipboard(action = 'copy') {
    if (selectedFiles.size === 0) {
        const message = translations['select_items_first'] || 'Please select items first';
        logAndSpeak(message, 'warning');
        return false;
    }
    
    clipboardItems = {
        paths: new Set(selectedFiles),
        action: action,
        sourcePath: currentPath,
        timestamp: Date.now()
    };
    
    const actionText = action === 'copy' 
        ? (translations['copied'] || 'Copied') 
        : (translations['cut'] || 'Cut');
    
    const countText = (translations['items_count'] || '{count} item(s)')
        .replace('{count}', selectedFiles.size);
    
    hideFileContextMenu();

    logAndSpeak(`${actionText} ${countText}`, 'success');
    
    if (action === 'cut') {
        document.querySelectorAll('.file-item.selected').forEach(item => {
            item.style.opacity = '0.6';
            item.classList.add('cut-item');
        });
    }
    
    updatePasteMenuState();
    
    try {
        const clipboardData = {
            paths: Array.from(clipboardItems.paths),
            action: clipboardItems.action,
            sourcePath: clipboardItems.sourcePath,
            timestamp: clipboardItems.timestamp
        };
        localStorage.setItem('fileClipboard', JSON.stringify(clipboardData));
    } catch (e) {
        console.error('Failed to save clipboard to localStorage:', e);
    }
    
    return true;
}

async function pasteFromClipboard() {
    if (!clipboardItems || clipboardItems.paths.size === 0) {
        const message = translations['clipboard_empty'] || 'No items to paste';
        logAndSpeak(message, 'warning');
        return;
    }
    
    let targetPath = currentPath;
    
    if (fileContextMenuTarget && fileContextMenuTarget.classList.contains('file-item')) {
        const itemPath = fileContextMenuTarget.getAttribute('data-path');
        const isDir = fileContextMenuTarget.getAttribute('data-is-dir') === 'true';
        
        if (isDir) {
            targetPath = itemPath;
        } else {
            targetPath = itemPath.substring(0, itemPath.lastIndexOf('/')) || '/';
        }
    }
    
    if (clipboardItems.sourcePath === targetPath && clipboardItems.action === 'cut') {
        const message = translations['cannot_paste_same_location'] || 'Cannot paste in the same location';
        logAndSpeak(message, 'warning');
        return;
    }
    
    const operation = clipboardItems.action === 'copy' ? 'copy' : 'move';
    const operationText = operation === 'copy' 
        ? (translations['copy'] || 'Copy') 
        : (translations['move'] || 'Move');
    
    const confirmMessage = (translations['confirm_paste'] || '{operation} {count} item(s) to "{target}"?')
        .replace('{operation}', operationText)
        .replace('{count}', clipboardItems.paths.size)
        .replace('{target}', targetPath);
    
    hideFileContextMenu();

    showConfirmation(confirmMessage, async () => {
        let successCount = 0;
        let errorCount = 0;
        
        for (const sourcePath of clipboardItems.paths) {
            try {
                const apiAction = operation === 'copy' ? 'copy_item' : 'move_item';
                const response = await fetch(`?action=${apiAction}&source=${encodeURIComponent(sourcePath)}&dest=${encodeURIComponent(targetPath)}`);
                const data = await response.json();
                
                if (data.success) {
                    successCount++;
                } else {
                    errorCount++;
                    console.error('Operation failed:', data.error);
                }
            } catch (error) {
                errorCount++;
                console.error('Operation error:', error);
            }
        }
        
        if (clipboardItems.action === 'cut') {
            clearClipboard();
        }
        
        if (successCount > 0) {
            const message = (translations['paste_success'] || 'Successfully {operation} {count} item(s)')
                .replace('{operation}', operationText)
                .replace('{count}', successCount);
            logAndSpeak(message, 'success');
            refreshFiles();
        }
        
        if (errorCount > 0) {
            const message = (translations['paste_failed'] || 'Failed to {operation} {count} item(s)')
                .replace('{operation}', operationText)
                .replace('{count}', errorCount);
            logAndSpeak(message, 'error');
        }        
    });
}

function clearClipboard() {
    document.querySelectorAll('.file-item.cut-item').forEach(item => {
        item.style.opacity = '1';
        item.classList.remove('cut-item');
    });
    
    clipboardItems.paths.clear();
    clipboardItems.action = null;
    clipboardItems.sourcePath = null;
    
    updatePasteMenuState();
}

document.addEventListener('click', function(e) {
    const archiveMenuItem = e.target.closest('.archive-menu');
    if (archiveMenuItem) {
        e.preventDefault();
        e.stopPropagation();
        
        const submenu = document.getElementById('archiveSubmenu');
        const chevron = archiveMenuItem.querySelector('.fa-chevron-down, .fa-chevron-up');
        
        if (submenu) {
            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
                if (chevron) chevron.className = 'fas fa-chevron-down ms-auto';
            } else {
                submenu.style.display = 'block';
                if (chevron) chevron.className = 'fas fa-chevron-up ms-auto';
            }
        }
    }
});

function selectAllFiles() {
    const fileItems = document.querySelectorAll('.file-item');
    selectedFiles.clear();
    
    fileItems.forEach(item => {
        const path = item.getAttribute('data-path');
        if (path) {
            selectedFiles.add(path);
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = true;
            item.classList.add('selected');
        }
    });
    
    updateSelectionInfo();
    updateFileSelection();
    
    const selectedText = translations['select_all_complete'] || 
                        translations['selected_items'] || 
                        `Selected ${selectedFiles.size} items`;
    
    logAndSpeak(`${selectedText} (${selectedFiles.size})`, 'info');
}

function adjustMenuOnResize() {
    const menu = document.getElementById('fileContextMenu');
    const overlay = document.getElementById('contextMenuOverlay');
    
    if (!menu || menu.style.display !== 'block') return;
    
    const menuRect = menu.getBoundingClientRect();
    const menuWidth = menuRect.width;
    const menuHeight = menuRect.height;
    
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    let x = parseInt(menu.style.left) || 0;
    let y = parseInt(menu.style.top) || 0;
    
    if (x + menuWidth > viewportWidth) {
        x = Math.max(10, viewportWidth - menuWidth - 10);
    }
    
    if (y + menuHeight > viewportHeight) {
        y = Math.max(10, viewportHeight - menuHeight - 10);
    }
    
    if (x < 0) x = 10;
    if (y < 0) y = 10;
    
    menu.style.left = x + 'px';
    menu.style.top = y + 'px';
}

window.addEventListener('resize', adjustMenuOnResize);

window.addEventListener('scroll', adjustMenuOnResize);

if (!document.querySelector('#archive-menu-styles')) {
    const style = document.createElement('style');
    style.id = 'archive-menu-styles';
    style.textContent = `
        .archive-submenu .menu-item {
            padding: 8px 15px;
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .archive-submenu .menu-item:hover {
            background: var(--accent-tertiary);
            color: white;
            border-radius: 4px;
        }
        
        .archive-menu {
            cursor: pointer;
        }
        
        .archive-menu:hover {
            background: var(--accent-tertiary);
            color: white;
        }
    `;
    document.head.appendChild(style);
}

function toggleArchiveSubmenu(event) {
    event.stopPropagation();
    event.preventDefault();
    
    const submenu = document.getElementById('archiveSubmenu');
    const chevron = event.currentTarget.querySelector('.fa-chevron-right, .fa-chevron-down');
    
    if (submenu.style.display === 'block') {
        submenu.style.display = 'none';
        if (chevron) {
            chevron.className = 'fas fa-chevron-right ms-auto';
        }
    } else {
        submenu.style.display = 'block';
        if (chevron) {
            chevron.className = 'fas fa-chevron-down ms-auto';
        }
    }
}

function toggleFileSelection(filePath, checked) {
    if (event) {
        event.stopPropagation();
    }
    
    if (checked) {
        selectedFiles.add(filePath);
    } else {
        selectedFiles.delete(filePath);
    }
    updateFileSelection();
    updateSelectionInfo();
}

function showCompressDialog() {
    hideFileContextMenu();
    if (selectedFiles.size === 0) {
        logAndSpeak('Please select files to compress first', 'warning');
        return;
    }

    const paths = Array.from(selectedFiles);
    
    const compressItemsList = document.getElementById('compressItemsList');
    compressItemsList.innerHTML = '';
    
    paths.forEach(p => {
        const name = p.split('/').pop();
        const isDir = document.querySelector(`.file-item[data-path="${p}"]`)?.getAttribute('data-is-dir') === 'true';
        const itemDiv = document.createElement('div');
        itemDiv.className = 'd-flex align-items-center mb-2';
        itemDiv.innerHTML = `
            <i class="fas ${isDir ? 'fa-folder' : 'fa-file'} me-2 text-info"></i>
            <span class="text-info">${escapeHtml(name)}</span>
        `;
        compressItemsList.appendChild(itemDiv);
    });
    
    const archiveName = document.getElementById('archiveName');
    if (paths.length === 1) {
        const singlePath = paths[0];
        const name = singlePath.split('/').pop();
        const baseName = name.lastIndexOf('.') > 0 ? name.substring(0, name.lastIndexOf('.')) : name;
        archiveName.value = baseName;
    } else {
        archiveName.value = 'archive_' + Date.now();
    }
    
    document.getElementById('compressDestination').value = currentPath;
    
    const formatButtons = document.querySelectorAll('#formatButtonsGroup button[data-format]');
    formatButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-format') === 'zip') {
            btn.classList.add('active');
        }
    });
    
    document.getElementById('archiveExtension').textContent = '.zip';
    
    const modal = new bootstrap.Modal(document.getElementById('compressModal'));
    modal.show();
}

function browseForCompressPath() {
    const path = prompt('Enter destination path:', currentPath);
    if (path) {
        document.getElementById('compressDestination').value = path;
    }
}

function browseForExtractPath() {
    const path = prompt('Enter destination path:', currentPath);
    if (path) {
        document.getElementById('extractDestination').value = path;
    }
}

async function performCompress() {
    if (selectedFiles.size === 0) return;
    
    const paths = Array.from(selectedFiles);
    const destination = document.getElementById('compressDestination').value.trim();
    const archiveNameInput = document.getElementById('archiveName').value.trim();
    const formatBtn = document.querySelector('#compressModal [data-format].active');
    const format = formatBtn ? formatBtn.getAttribute('data-format') : 'zip';
    
    if (!destination || !archiveNameInput) {
        const warningMessage = translations['enter_archive_name_and_destination'] || 'Please enter archive name and destination path';
        logAndSpeak(warningMessage, 'warning');
        return;
    }
    
    const archiveName = destination.endsWith('/') 
        ? destination + archiveNameInput + '.' + format
        : destination + '/' + archiveNameInput + '.' + format;
    
    try {
        const formData = new FormData();
        formData.append('action_type', 'compress');
        formData.append('archive_type', format);
        formData.append('destination', archiveName);
        
        if (paths.length === 1) {
            formData.append('path', paths[0]);
        } else {
            paths.forEach((path) => formData.append('paths[]', path));
        }
        
        const response = await fetch('?action=archive_action', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            const successMessage = `${translations['successfully_compressed'] || 'Successfully compressed'} ${paths.length} ${translations['file_s'] || 'file(s)'}`;
            logAndSpeak(successMessage, 'success');
            bootstrap.Modal.getInstance(document.getElementById('compressModal')).hide();
            refreshFiles();
        } else {
            const errorMessage = data.error || translations['failed_to_create_archive'] || 'Failed to create archive';
            logAndSpeak(errorMessage, 'error');
        }
    } catch (error) {
        const errorMessage = `${translations['failed_to_create_archive'] || 'Failed to create archive'}: ${error.message}`;
        logAndSpeak(errorMessage, 'error');
    }
}

async function extractArchiveHere(filePath) {
    if (!filePath) {
        if (selectedFiles.size === 0) {
            logAndSpeak('Please select the file you want to extract first.', 'warning');
            return;
        }
        filePath = Array.from(selectedFiles)[0];
    }
    
    if (!filePath) return;
    
    try {
        const extractToDir = dirname(filePath);
        
        const formData = new FormData();
        formData.append('path', filePath);
        formData.append('action_type', 'extract');
        formData.append('destination', extractToDir);
                
        const response = await fetch('?action=archive_action', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            const successMessage = translations['archive_extracted_successfully'] || 'Archive extracted successfully';
            logAndSpeak(successMessage, 'success');
            
            loadFiles(extractToDir);
        } else {
            const errorMessage = data.error || translations['failed_to_extract_archive'] || 'Failed to extract archive';
            logAndSpeak(errorMessage, 'error');
        }
    } catch (error) {
        const errorMessage = `${translations['failed_to_extract_archive'] || 'Failed to extract archive'}: ${error.message}`;
        logAndSpeak(errorMessage, 'error');
    }
    
    hideFileContextMenu();
}

function dirname(path) {
    return path.substring(0, path.lastIndexOf('/')) || '/';
}

function browseForExtractPath() {
    const path = prompt('Please enter the extraction target path.:', currentPath);
    if (path) {
        document.getElementById('extractDestination').value = path;
    }
}

async function performExtract() {
    if (selectedFiles.size === 0) return;
    
    const path = Array.from(selectedFiles)[0];
    const destination = document.getElementById('extractDestination').value.trim();
    
    if (!destination) {
        return;
    }
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('extractModal'));
    if (modal) modal.hide();

    try {
        const formData = new FormData();
        formData.append('path', path);
        formData.append('action_type', 'extract');
        formData.append('destination', destination);
        
        const response = await fetch('?action=archive_action', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            const successMessage = translations['archive_extracted_successfully'] || 'Archive extracted successfully';
            logAndSpeak(successMessage, 'success');
            
            refreshFiles();
        } else {
            const errorMessage = data.error || translations['failed_to_extract_archive'] || 'Failed to extract archive';
            logAndSpeak(errorMessage, 'error');
        }
    } catch (error) {
        const errorMessage = `${translations['failed_to_extract_archive'] || 'Failed to extract archive'}: ${error.message}`;
        logAndSpeak(errorMessage, 'error');
    }
    
    selectedFiles.clear();
    updateSelectionInfo();
}

function showExtractDialog() {
    if (selectedFiles.size === 0) return;

    const path = Array.from(selectedFiles)[0];
    const name = path.split('/').pop();

    const dialog = document.createElement('div');
    dialog.className = 'modal fade';
    dialog.id = 'extractModal';
    dialog.setAttribute('tabindex', '-1');
    dialog.setAttribute('aria-hidden', 'true');
    dialog.setAttribute('aria-labelledby', 'extractModalLabel');
    dialog.setAttribute('data-bs-backdrop', 'static');
    dialog.setAttribute('data-bs-keyboard', 'false');
    dialog.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="extractModalLabel">
                        <i class="fas fa-expand me-2"></i>
                        ${translations['extract_archive'] || 'Extract Archive'}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="extractDestination" class="form-label">
                            ${translations['destination_path'] || 'Destination Path:'}
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="extractDestination" value="${currentPath}">
                            <button class="btn btn-outline-secondary" type="button" onclick="browseForExtractPath()">
                                <i class="fas fa-folder-open"></i>
                            </button>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        ${translations['extracting'] || 'Extracting:'}
                        <div class="mt-1">${escapeHtml(name)}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        ${translations['cancel'] || 'Cancel'}
                    </button>
                    <button type="button" class="btn btn-primary" onclick="performExtract()">
                        <i class="fas fa-check me-1"></i>
                        ${translations['extract'] || 'Extract'}
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(dialog);
    hideFileContextMenu();
    const modal = new bootstrap.Modal(dialog);
    modal.show();

    dialog.addEventListener('hidden.bs.modal', function() {
        dialog.remove();
    });
}

function hideFileContextMenu() {
    const menu = document.getElementById('fileContextMenu');
    const overlay = document.getElementById('contextMenuOverlay');
    
    if (menu) {
        menu.style.display = 'none';
        menu.style.left = '';
        menu.style.top = '';
    }
    if (overlay) overlay.style.display = 'none';
    
    fileContextMenuTarget = null;
}

function showFileProperties() {
    const path = Array.from(selectedFiles)[0];
    
    if (!path || path.trim() === '') {
         logAndSpeak(translations['invalid_file_path'] || 'Invalid file path', 'error');
        return;
    }
    
    hideFileContextMenu();
    showFileInfoModal(path);
}

function contextMenuOpen() {
    if (selectedFiles.size === 0) {
        logAndSpeak(translations['select_items_first'] || 'Please select items first', 'warning');
        return;
    }
    
    const path = Array.from(selectedFiles)[0];
    const fileItem = document.querySelector(`.file-item[data-path="${path}"]`);
    
    if (fileItem) {
        const isDir = fileItem.getAttribute('data-is-dir') === 'true';
        const type = fileItem.getAttribute('data-type');
        handleDoubleClick(path, isDir, type);
    }
    
    hideFileContextMenu();
}

function contextMenuPlay() {
    if (selectedFiles.size === 0) {
        logAndSpeak(translations['select_items_first'] || 'Please select items first', 'warning');
        return;
    }
    
    const path = Array.from(selectedFiles)[0];
    playMedia(path);
    hideFileContextMenu();
}

function contextMenuDownload() {
    if (selectedFiles.size === 0) {
        logAndSpeak(translations['select_items_first'] || 'Please select items first', 'warning');
        return;
    }
    
    const path = Array.from(selectedFiles)[0];  
    const fileItem = document.querySelector(`.file-item[data-path="${path}"]`);
    const isDir = fileItem ? fileItem.getAttribute('data-is-dir') === 'true' : false;
    
    if (!isDir) {
        const downloadUrl = `?preview=1&path=${encodeURIComponent(path)}`;
        const link = document.createElement('a');
        link.href = downloadUrl;
        const fileName = path.split('/').pop();
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        logAndSpeak(`${translations['starting_download'] || 'Starting download'}: ${fileName}`, 'info');
    } else {
        downloadFolderAsTar(path);
    }
    
    hideFileContextMenu();
}

function downloadFolderAsTar(folderPath) {
    const folderName = folderPath.split('/').pop();
    logAndSpeak(`${translations['packaging_folder'] || 'Packaging folder'}: ${folderName}...`, 'info');
    
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = `?action=download_folder&path=${encodeURIComponent(folderPath)}`;
    
    iframe.onload = function() {
        logAndSpeak(`${translations['download_started'] || 'Download started'}: ${folderName}.tar`, 'success');
        document.body.removeChild(iframe);
    };
    
    iframe.onerror = function() {
        logAndSpeak(`${translations['packaging_failed'] || 'Packaging failed'}`, 'error');
        document.body.removeChild(iframe);
    };
    
    document.body.appendChild(iframe);
}

function contextMenuDelete() {
    if (selectedFiles.size === 0) {
        logAndSpeak('Please select the file you want to delete first.', 'warning');
        return;
    }
    
    deleteSelected();
    hideFileContextMenu();
}

function showCreateFolderModal() {
    hideFileContextMenu(); 
    const input = document.getElementById('folderNameInput');
    if (input) {
        input.value = '';
        input.focus();
    }
    const modal = new bootstrap.Modal(document.getElementById('createFolderModal'));
    modal.show();
}

function showCreateFileModal() {
    hideFileContextMenu(); 
    const input = document.getElementById('fileNameInput');
    if (input) {
        input.value = '';
        input.focus();
    }
    const modal = new bootstrap.Modal(document.getElementById('createFileModal'));
    modal.show();
}

async function createFolder() {
    const input = document.getElementById('folderNameInput');
    if (!input || !input.value.trim()) {
        logAndSpeak(translations['enter_folder_name'] || 'Please enter folder name', 'warning');
        return;
    }

    const folderName = input.value.trim();

    try {
        const response = await fetch(
            `?action=create_folder&path=${encodeURIComponent(currentPath)}&name=${encodeURIComponent(folderName)}`
        );
        const data = await response.json();

        if (data.success) {
            const successMessage = translations['folder_created_success'] || 'Folder created successfully';
            logAndSpeak(successMessage, 'success');

            const modal = bootstrap.Modal.getInstance(
                document.getElementById('createFolderModal')
            );
            if (modal) modal.hide();

            refreshFiles();
        } else {
            const errorMessage = translations['create_folder_failed'] || 'Failed to create folder';
            logAndSpeak(data.error || errorMessage, 'error');
        }
    } catch (error) {
        const errorMessage = translations['create_folder_failed'] || 'Failed to create folder';
        logAndSpeak(errorMessage + ': ' + error.message, 'error');
    }
}

async function createFile() {
    const input = document.getElementById('fileNameInput');
    if (!input || !input.value.trim()) {
        logAndSpeak(translations['enter_file_name'] || 'Please enter file name', 'warning');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('path', currentPath);
        formData.append('name', input.value.trim());

        const response = await fetch('?action=create_file', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            const successMessage = translations['file_created_success'] || 'File created successfully';
            logAndSpeak(successMessage, 'success');
            
            bootstrap.Modal.getInstance(document.getElementById('createFileModal')).hide();

            const ext = input.value.trim().toLowerCase().split('.').pop();
            const textExts = ['txt', 'log', 'conf', 'ini', 'json', 'xml', 'html', 'css', 'js', 'php', 'py', 'sh', 'md'];

            if (textExts.includes(ext)) {
                setTimeout(() => {
                    editFile(currentPath + '/' + input.value.trim());
                }, 500);
            } else {
                refreshFiles();
            }
        } else {
            const errorMessage = translations['create_file_failed'] || 'Failed to create file';
            logAndSpeak(data.error || errorMessage, 'error');
        }
    } catch (error) {
        const errorMessage = translations['create_file_failed'] || 'Failed to create file';
        logAndSpeak(errorMessage + ': ' + error.message, 'error');
    }
}

function prepareRenameModal() {
    if (selectedFiles.size === 0) return;
    
    const path = Array.from(selectedFiles)[0];
    const name = path.split('/').pop();
    
    document.getElementById('renameInput').value = name;
    document.getElementById('renameOriginalName').textContent = name;
    
    hideFileContextMenu();
}

async function performRename() {
    if (selectedFiles.size === 0) {
        logAndSpeak(translations['no_item_selected'] || 'No item selected', 'warning');
        return;
    }

    const oldPath = Array.from(selectedFiles)[0];
    const newName = document.getElementById('renameInput').value.trim();

    if (!newName) {
        logAndSpeak(translations['enter_new_name'] || 'Please enter new name', 'warning');
        return;
    }

    try {
        const response = await fetch(`?action=rename_item&old=${encodeURIComponent(oldPath)}&new=${encodeURIComponent(newName)}`);
        const data = await response.json();

        if (data.success) {
            const Message = translations['rename_success'] || 'Renamed successfully';
            logAndSpeak(Message);
            bootstrap.Modal.getInstance(document.getElementById('renameModal')).hide();
            selectedFiles.clear();
            updateSelectionInfo();
            refreshFiles();
        } else {
            logAndSpeak(data.error || translations['rename_failed'] || 'Failed to rename', 'error');
        }
    } catch (error) {
        logAndSpeak(
            (translations['rename_failed'] || 'Failed to rename') + ': ' + error.message,
            'error'
        );
    }
}

async function deleteSelected() {
    if (selectedFiles.size === 0) {
         logAndSpeak(
            translations['select_files_to_delete'] || 'Please select files to delete first',
            'warning'
        );
        return;
    }

    const recycleEnabled = document.getElementById('recycleBinToggle')?.checked ?? true;
    
    const forceDeleteOption = document.getElementById('forceDeleteOption');
    if (forceDeleteOption) {
        forceDeleteOption.style.display = recycleEnabled ? 'block' : 'none';
    }
    
    const count = selectedFiles.size;
    
    const items = [];
    for (const path of selectedFiles) {
        const fileItem = document.querySelector(`.file-item[data-path="${path}"]`);
        const isDir = fileItem?.getAttribute('data-is-dir') === 'true';
        const name = path.split('/').pop();
        items.push({ name, isDir, path });
    }
    
    let fileListHtml = '<div class="mb-3">' + 
        (translations['confirm_delete_selected'] || 'Are you sure you want to delete {count} item(s)?').replace('{count}', count) + 
        '</div>';
    fileListHtml += '<div class="card bg-dark bg-opacity-25 border-secondary" style="max-height: 250px; overflow-y: auto;">';
    fileListHtml += '<div class="card-body p-2">';
    
    items.forEach(item => {
        const icon = item.isDir ? 'fa-folder' : 'fa-file';
        const color = item.isDir ? '#FFA726' : '#2196F3';
        fileListHtml += `
            <div class="d-flex align-items-center py-1 border-bottom border-secondary">
                <i class="fas ${icon} me-2" style="width: 16px; color: ${color};"></i>
                <span class="small text-truncate" style="max-width: 350px;" title="${item.name}">${item.name}</span>
            </div>
        `;
    });
    
    fileListHtml += '</div></div>';
    
    document.getElementById('deleteConfirmMessage').innerHTML = fileListHtml;
    
    window.pendingDeleteFiles = new Set(selectedFiles);
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

async function showFileInfoModal(path) {
    const content = document.getElementById('filePropertiesContent');
    if (!content) return;

    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">${translations['loading'] || 'Loading...'}</span>
            </div>
            <p class="mt-2">${translations['loading'] || 'Loading...'}</p>
        </div>`;

    try {
        const response = await fetch(`?action=get_file_info&path=${encodeURIComponent(path)}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();

        if (data.success && data.info) {
            const info = data.info;
            const isDir = info.is_dir === true || info.is_dir === 'true';
            const fileName = info.name || '';
            const fileExt = info.extension || '';
            
            const fileIcon = getFileIcon(fileName, fileExt, isDir);

            let html = `
                <div class="container-fluid">
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="card h-100 border shadow-sm">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                                    <div class="mb-4" style="font-size: 3.5rem;">
                                        ${fileIcon.replace('fa-2x', 'fa-3x')}
                                    </div>
                                    <div class="text-center w-100">
                                        <h3 class="card-title mb-3 text-break" style="word-break: break-word;">
                                            ${escapeHtml(fileName || translations['unknown'] || 'Unknown')}
                                        </h3>
                                        <div class="badge mb-4 p-2 ${isDir ? 'bg-warning text-dark' : 'bg-success'}" style="${isDir ? '' : '--bs-badge-color: #fff; color: #fff !important;'}">
                                            <i class="fas ${isDir ? 'fa-folder' : 'fa-file'} me-1" style="${isDir ? '' : 'color: inherit !important;'}"></i>
                                            ${isDir ? translations['folder'] || 'Folder' : translations['file'] || 'File'}
                                        </div>
                                    </div>
                                    <div class="w-100 mt-auto">
                                        <hr class="my-3">
                        `;
            
            if (!isDir && info.extension) {
                html += `
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">${translations['extension'] || 'Extension:'}</span>
                            <strong>${escapeHtml(info.extension)}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">${translations['fileSize'] || 'Size:'}</span>
                            <strong>${info.size_formatted || '0 B'}</strong>
                        </div>`;
                        
            if (info.media_info) {
                if (info.media_info.duration) {
                    html += `
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">${translations['duration'] || 'Duration:'}</span>
                            <strong>${info.media_info.duration}</strong>
                        </div>`;
                }
        
                if (info.media_info.resolution) {
                    html += `
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">${translations['resolution'] || 'Resolution:'}</span>
                            <strong>${info.media_info.resolution}</strong>
                        </div>`;
                } else if (info.media_info.width && info.media_info.height) {
                    html += `
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">${translations['resolution'] || 'Resolution:'}</span>
                            <strong>${info.media_info.width} × ${info.media_info.height}</strong>
                        </div>`;
                }
        
                if (info.media_info.bitrate) {
                    html += `
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">${translations['bitrate'] || 'Bitrate:'}</span>
                            <strong>${info.media_info.bitrate}</strong>
                        </div>`;
                }
        
                if (info.media_info.video_codec) {
                    html += `
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">${translations['video_codec'] || 'Video Codec:'}</span>
                            <strong>${info.media_info.video_codec.toUpperCase()}</strong>
                        </div>`;
                }
        
                if (info.media_info.frame_rate) {
                    html += `
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">${translations['frame_rate'] || 'Frame Rate:'}</span>
                            <strong>${info.media_info.frame_rate}</strong>
                        </div>`;
                }
             
                if (info.media_info.sample_rate) {
                    html += `
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">${translations['sample_rate'] || 'Sample Rate:'}</span>
                            <strong>${info.media_info.sample_rate}</strong>
                        </div>`;
                }
        
                if (info.media_info.channel_layout) {
                    html += `
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">${translations['audio_channels'] || 'Channels:'}</span>
                            <strong>${info.media_info.channel_layout}</strong>
                        </div>`;
                    }
                }
            }
            
            html += `
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-8">
                            <div class="card h-100 border shadow-sm">
                                <div class="card-body p-4">
                                    <h4 class="card-title mb-4">
                                        <i class="fas fa-info-circle me-2 text-primary"></i>
                                        ${translations['details'] || 'Details'}
                                    </h4>
                                    
                                    <div class="mb-4">
                                        <h6 class="text-muted mb-3"><i class="fas fa-cog me-1"></i> ${translations['basic_info'] || 'Basic Information'}</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-light rounded p-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-ruler-combined text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">${translations['fileSize'] || 'Size'}</small>
                                                        <strong>${info.size_formatted || '0 B'}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-light rounded p-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-clock text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">${translations['created_time'] || 'Created'}</small>
                                                        <strong>${info.modified_formatted || translations['unknown'] || 'Unknown'}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-light rounded p-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-calendar-plus text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">${translations['modifiedTime'] || 'Modified'}</small>
                                                        <strong>${info.created_formatted || translations['unknown'] || 'Unknown'}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            ${!isDir && info.mime_type ? `
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-light rounded p-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-file-alt text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">${translations['mime_type'] || 'MIME Type'}</small>
                                                        <strong>${getMimeType(info.extension) || translations['unknown'] || 'Unknown'}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h6 class="text-muted mb-3"><i class="fas fa-shield-alt me-1"></i> ${translations['permissions'] || 'Permissions'}</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-light rounded p-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-lock text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">${translations['permissions'] || 'Permissions'}</small>
                                                        <div class="d-flex align-items-center mt-1">
                                                            <span class="badge bg-dark me-2">${info.permissions || '----'}</span>
                                                            <button class="btn btn-sm btn-outline-primary" onclick="showChmodDialogForPath('${escapeHtml(path)}')">
                                                                <i class="fas fa-edit me-1"></i> ${translations['change'] || 'Change'}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-light rounded p-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-user text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">${translations['owner'] || 'Owner'}</small>
                                                        <strong>${escapeHtml(info.owner || translations['unknown'] || 'Unknown')}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-light rounded p-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-users text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">${translations['group'] || 'Group'}</small>
                                                        <strong>${escapeHtml(info.group || translations['unknown'] || 'Unknown')}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-light rounded p-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-folder-open text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">${translations['file_path'] || 'Path'}</small>
                                                        <div class="mt-1">
                                                            <code class="d-block text-break" style="word-break: break-all;" title="${escapeHtml(info.path || path)}">
                                                                ${escapeHtml(info.path || path)}
                                                            </code>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

            content.innerHTML = html;
            
            const modal = new bootstrap.Modal(document.getElementById('filePropertiesModal'));
            modal.show();

        } else {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${data.error || translations['get_file_info_failed'] || 'Failed to get file info'}
                </div>`;
        }

    } catch (error) {
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${translations['get_file_info_failed'] || 'Failed to get file info'}
            </div>`;
    }
}

function getMimeType(ext) {
    if (!ext) return 'application/octet-stream';
    
    const mimeMap = {
        'jpg': 'image/jpeg', 'jpeg': 'image/jpeg', 'jpe': 'image/jpeg', 'jfif': 'image/jpeg',
        'png': 'image/png', 'gif': 'image/gif', 'bmp': 'image/bmp', 'webp': 'image/webp',
        'svg': 'image/svg+xml', 'svgz': 'image/svg+xml', 'ico': 'image/x-icon', 'cur': 'image/x-icon',
        'tiff': 'image/tiff', 'tif': 'image/tiff', 'psd': 'image/vnd.adobe.photoshop',
        'heic': 'image/heic', 'heif': 'image/heif', 'avif': 'image/avif',
        'raw': 'image/x-raw', 'cr2': 'image/x-canon-cr2', 'nef': 'image/x-nikon-nef',
        'arw': 'image/x-sony-arw', 'dng': 'image/x-adobe-dng', 'orf': 'image/x-olympus-orf',
        
        'mp3': 'audio/mpeg', 'wav': 'audio/wav', 'ogg': 'audio/ogg', 'flac': 'audio/flac',
        'm4a': 'audio/mp4', 'aac': 'audio/aac', 'wma': 'audio/x-ms-wma', 'opus': 'audio/opus',
        'mp2': 'audio/mpeg', 'ac3': 'audio/ac3', 'dts': 'audio/vnd.dts', 'eac3': 'audio/eac3',
        'ape': 'audio/ape', 'wv': 'audio/wavpack', 'tta': 'audio/tta', 'tak': 'audio/tak',
        'dsf': 'audio/dsd', 'dff': 'audio/dsd', 'sacd': 'audio/sacd',
        'mid': 'audio/midi', 'midi': 'audio/midi', 'rmi': 'audio/midi',
        'amr': 'audio/amr', 'awb': 'audio/amr-wb', 'sln': 'audio/speex',
        'ra': 'audio/vnd.rn-realaudio', 'ram': 'audio/vnd.rn-realaudio',
        'aiff': 'audio/aiff', 'aif': 'audio/aiff', 'aifc': 'audio/aiff',
        'caf': 'audio/x-caf', 'm4r': 'audio/mp4', 'xmf': 'audio/mobile-xmf',
        'weba': 'audio/webm', 'mka': 'audio/x-matroska', 'spx': 'audio/speex',
        
        'mp4': 'video/mp4', 'm4v': 'video/x-m4v', 'avi': 'video/x-msvideo',
        'mkv': 'video/x-matroska', 'mov': 'video/quicktime', 'wmv': 'video/x-ms-wmv',
        'flv': 'video/x-flv', 'webm': 'video/webm', '3gp': 'video/3gpp', '3g2': 'video/3gpp2',
        'ogv': 'video/ogg', 'mpg': 'video/mpeg', 'mpeg': 'video/mpeg', 'mpe': 'video/mpeg',
        'ts': 'video/mp2t', 'm2ts': 'video/mp2t', 'mts': 'video/mp2t', 'm2t': 'video/mp2t',
        'vob': 'video/dvd', 'ifo': 'video/dvd', 'bup': 'video/dvd',
        'rm': 'video/vnd.rn-realvideo', 'rmvb': 'video/vnd.rn-realvideo',
        'qt': 'video/quicktime', 'hdmov': 'video/quicktime', 'dv': 'video/dv',
        'asf': 'video/x-ms-asf', 'asx': 'video/x-ms-asf',
        'divx': 'video/divx', 'xvid': 'video/xvid',
        'f4v': 'video/mp4', 'f4p': 'video/mp4', 'f4a': 'video/mp4', 'f4b': 'video/mp4',
        'avchd': 'video/avchd', 'mxf': 'video/mxf', 'gxf': 'video/gxf',
        'mj2': 'video/mj2', 'drc': 'video/vnd.dlna.mpeg-tts',
        'dnxhd': 'video/dnxhd', 'prores': 'video/prores',
        'vp8': 'video/vp8', 'vp9': 'video/vp9', 'av1': 'video/av1',
        'hevc': 'video/hevc', 'h264': 'video/h264', 'h265': 'video/h265',
        
        'pdf': 'application/pdf',
        'doc': 'application/msword',
        'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls': 'application/vnd.ms-excel',
        'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt': 'application/vnd.ms-powerpoint',
        'pptx': 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'odt': 'application/vnd.oasis.opendocument.text',
        'ods': 'application/vnd.oasis.opendocument.spreadsheet',
        'odp': 'application/vnd.oasis.opendocument.presentation',
        'rtf': 'application/rtf',
        'epub': 'application/epub+zip',
        'mobi': 'application/x-mobipocket-ebook',
        
        'zip': 'application/zip',
        'tar': 'application/x-tar',
        'gz': 'application/gzip',
        'bz2': 'application/x-bzip2',
        '7z': 'application/x-7z-compressed',
        'rar': 'application/x-rar-compressed',
        'xz': 'application/x-xz',
        'lz': 'application/x-lzip',
        'lzma': 'application/x-lzma',
        'zst': 'application/zstd',
        'cab': 'application/vnd.ms-cab-compressed',
        'iso': 'application/x-iso9660-image',
        
        'txt': 'text/plain',
        'log': 'text/plain',
        'conf': 'text/plain',
        'ini': 'text/plain',
        'cfg': 'text/plain',
        'json': 'application/json',
        'xml': 'application/xml',
        'html': 'text/html',
        'htm': 'text/html',
        'xhtml': 'application/xhtml+xml',
        'css': 'text/css',
        'js': 'application/javascript',
        'jsx': 'text/jsx',
        'ts': 'application/typescript',
        'tsx': 'text/typescript-jsx',
        'vue': 'text/x-vue',
        'php': 'application/x-httpd-php',
        'py': 'text/x-python',
        'rb': 'text/x-ruby',
        'pl': 'text/x-perl',
        'lua': 'text/x-lua',
        'go': 'text/x-go',
        'rs': 'text/x-rust',
        'swift': 'text/x-swift',
        'kt': 'text/x-kotlin',
        'scala': 'text/x-scala',
        'sh': 'application/x-sh',
        'bash': 'application/x-sh',
        'zsh': 'text/x-script.zsh',
        'fish': 'text/x-script.fish',
        'bat': 'application/x-bat',
        'cmd': 'application/x-bat',
        'ps1': 'application/x-powershell',
        'md': 'text/markdown',
        'markdown': 'text/markdown',
        'yaml': 'application/x-yaml',
        'yml': 'application/x-yaml',
        'toml': 'application/toml',
        'sql': 'application/sql',
        'csv': 'text/csv',
        'tsv': 'text/tab-separated-values',
        
        'ttf': 'font/ttf',
        'otf': 'font/otf',
        'woff': 'font/woff',
        'woff2': 'font/woff2',
        'eot': 'application/vnd.ms-fontobject',
        
        'exe': 'application/x-msdownload',
        'msi': 'application/x-msi',
        'deb': 'application/x-deb',
        'rpm': 'application/x-rpm',
        'apk': 'application/vnd.android.package-archive',
        'ipk': 'application/x-ipk',
        'bin': 'application/octet-stream',
        'dat': 'application/octet-stream',
        'db': 'application/octet-stream',
        'sqlite': 'application/x-sqlite3',
        'pem': 'application/x-pem-file',
        'crt': 'application/x-x509-ca-cert',
        'key': 'application/x-pem-file',
        'torrent': 'application/x-bittorrent'
    };
    
    const lowerExt = ext.toLowerCase();
    return mimeMap[lowerExt] || 'application/octet-stream';
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

function showChmodDialog() {
    hideFileContextMenu();
    
    if (selectedFiles.size === 0) {
        logAndSpeak('Please select the file first.', 'warning');
        return;
    }
    
    const path = Array.from(selectedFiles)[0];
    
    fetch(`?action=get_file_info&path=${encodeURIComponent(path)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.info) {
                document.getElementById('chmodPath').value = path;
                
                const currentPerms = data.info.permissions || '0644';
                const cleanPerms = currentPerms.replace(/^0+/, '') || '644';
                document.getElementById('permissions').value = cleanPerms;
                
                const fileName = path.split('/').pop();
                document.getElementById('chmodModalLabel').innerText =
                    `${translations['chmod_set_permissions'] || 'Set Permissions'}: ${fileName}`;
                
                hideFileContextMenu();
                
                const modal = new bootstrap.Modal(document.getElementById('chmodModal'));
                modal.show();
            } else {
                logAndSpeak(translations['chmod_cannot_get_info'] || 'Cannot get file information', 'error');
            }
        })
        .catch(error => {
            logAndSpeak(translations['chmod_get_info_failed'] || 'Failed to get file information', 'error');
        });
}

function validateChmod() {
    const path = document.getElementById('chmodPath').value;
    const permissions = document.getElementById('permissions').value.trim();
    
    if (!path) {
        logAndSpeak(translations['chmod_invalid_path'] || 'Invalid file path', 'error');
        return false;
    }
    
    if (!/^[0-7]{3,4}$/.test(permissions)) {
        logAndSpeak(translations['chmod_invalid_format'] || 'Please enter 3-4 digit octal number (0-7)', 'error');
        return false;
    }
    
    savePermissions(path, permissions);
    return false;
}

async function savePermissions(path, permissions) {
    try {
        const formData = new FormData();
        formData.append('path', path);
        formData.append('permissions', permissions);
        
        const response = await fetch('?action=change_permissions', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            const Message = translations['chmod_success'] || 'Permissions modified successfully';
            logAndSpeak(Message);
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('chmodModal'));
            if (modal) modal.hide();
            
            refreshFiles();
        } else {
            logAndSpeak(
                `${translations['chmod_failed'] || 'Modification failed'}: ${data.error || translations['unknown_error'] || 'Unknown error'}`,
                'error'
            );
        }
    } catch (error) {
        logAndSpeak(`${translations['chmod_failed'] || 'Modification failed'}: ${error.message}`, 'error');
    }
}

function showChmodDialogForPath(path) {
    document.getElementById('chmodPath').value = path;
    document.getElementById('permissions').value = '';
    
    const propertiesModal = bootstrap.Modal.getInstance(document.getElementById('filePropertiesModal'));
    if (propertiesModal) propertiesModal.hide();
    
    const modal = new bootstrap.Modal(document.getElementById('chmodModal'));
    modal.show();
}

async function searchFiles() {
    const searchTerm = document.getElementById('searchInput').value.trim();
    
    if (!searchTerm) {
        const warningMessage = translations['search_enter_term'] || 'Please enter search term';
        logAndSpeak(warningMessage, 'warning');
        return;
    }
    
    const resultsContainer = document.getElementById('searchResults');
    resultsContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">${translations['searching'] || 'Searching...'}</span>
            </div>
            <p class="mt-2">${translations['searching'] || 'Searching...'}</p>
        </div>`;
    
    try {
        const response = await fetch(`?action=search&term=${encodeURIComponent(searchTerm)}`);
        const data = await response.json();
        
        if (data.success) {
            const results = data.results;
            resultsContainer.innerHTML = '';
            
            if (results.length === 0) {
                const noResultsMessage = translations['search_no_results'] || 'No matching files found';
                const tryDifferentMessage = translations['search_try_diff_keyword'] || 'Try different keywords';
                
                logAndSpeak(noResultsMessage, 'info');
                
                resultsContainer.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <h6>${noResultsMessage}</h6>
                        <p class="small">${tryDifferentMessage}</p>
                    </div>`;
                return;
            }
            
            const foundMessage = `${translations['search_found_matches'] || 'Found'} ${results.length} ${translations['search_matches'] || 'matches'}`;
            logAndSpeak(foundMessage, 'success');
            
            const header = document.createElement('div');
            header.className = 'alert alert-info mb-3';
            header.innerHTML = `
                <i class="fas fa-info-circle me-2"></i>
                <strong>"${escapeHtml(searchTerm)}"</strong> ${translations['search_results_for'] || 'search results for'}
                <span class="badge bg-primary ms-2">${results.length}</span>
            `;
            resultsContainer.appendChild(header);
            
            const list = document.createElement('div');
            list.className = 'list-group';
            
            results.forEach(file => {
                const item = document.createElement('div');
                item.className = 'list-group-item list-group-item-action';
                item.style.cssText = `
                    cursor: pointer;
                    border: none;
                    background: var(--bg-container);
                    margin-bottom: 8px;
                    border-radius: 8px;
                    transition: all 0.2s;
                `;
                
                item.addEventListener('mouseenter', function() {
                    this.style.background = 'color-mix(in oklch, var(--bg-container), transparent 20%)';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.background = 'var(--bg-container)';
                });
                
                let displayPath = file.path;
                if (displayPath.startsWith('//')) displayPath = displayPath.substring(1);
                
                let fileNameDisplay = escapeHtml(file.name);
                if (file.matched_part) {
                    fileNameDisplay = `${escapeHtml(file.matched_part.before)}<span class="bg-warning text-dark">${escapeHtml(file.matched_part.match)}</span>${escapeHtml(file.matched_part.after)}`;
                } else {
                    const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
                    fileNameDisplay = escapeHtml(file.name).replace(regex, '<span class="bg-warning text-dark">$1</span>');
                }

                const mediaExts = [ 'mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 'mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', '3gp', '3g2', 'ogv', 'mpg', 'mpeg', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg' ]
                const fileExt = file.name.split('.').pop().toLowerCase();
                const isMedia = mediaExts.includes(fileExt);
                
                item.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas ${file.is_dir ? 'fa-folder text-warning' : 'fa-file text-primary'} fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">${fileNameDisplay}</div>
                            <div class="small text-muted">
                                <span>${file.size_formatted}</span>
                                <span class="mx-2">•</span>
                                <span>${file.modified_formatted}</span>
                            </div>
                            <div class="small">
                                <code class="text-truncate d-block" style="max-width: 500px; color: var(--text-secondary);">
                                    ${escapeHtml(displayPath)}
                                </code>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex gap-2">
                                ${!file.is_dir ? `
                                    ${isMedia ? `
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="closeSearchModalAndPlay('${escapeHtml(file.path)}')"
                                                style="white-space: nowrap;"
                                                title="${translations['play'] || 'Play'}">
                                            <i class="fas fa-play"></i>
                                            <span class="d-none d-md-inline">${translations['play'] || 'Play'}</span>
                                        </button>
                                    ` : `
                                        <button class="btn btn-sm btn-outline-success" 
                                                onclick="closeSearchModalAndEdit('${escapeHtml(file.path)}')"
                                                style="white-space: nowrap;"
                                                title="${translations['edit_file'] || 'Edit File'}">
                                            <i class="fas fa-edit"></i>
                                            <span class="d-none d-md-inline">${translations['edit'] || 'Edit'}</span>
                                        </button>
                                    `}
                                ` : ''}
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="openFileDirectory('${escapeHtml(file.path)}', ${file.is_dir})"
                                        style="white-space: nowrap;"
                                        title="${translations['search_open_directory'] || 'Open Directory'}">
                                    <i class="fas fa-folder-open"></i>
                                    <span class="d-none d-md-inline">${translations['search_open_directory'] || 'Open Directory'}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                list.appendChild(item);
            });
            
            resultsContainer.appendChild(list);
        } else {
            const errorMessage = `${translations['search_failed'] || 'Search failed'}: ${data.error || translations['unknown_error'] || 'Unknown error'}`;
            logAndSpeak(errorMessage, 'error');
            
            resultsContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${errorMessage}
                </div>`;
        }
    } catch (error) {
        const errorMessage = `${translations['search_error'] || 'Search error'}: ${error.message}`;
        logAndSpeak(errorMessage, 'error');
        
        resultsContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${errorMessage}
            </div>`;
    }
}

function closeSearchModalAndPlay(path) {
    const searchModal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
    if (searchModal) {
        searchModal.hide();
    }
    
    setTimeout(() => {
        playMedia(path);
    }, 300);
}

function closeSearchModalAndEdit(path) {
    const searchModal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
    if (searchModal) {
        searchModal.hide();
    }
    
    setTimeout(() => {
        editFile(path);
    }, 300);
}

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function openFileDirectory(filePath, isDir) {
    let fixedPath = filePath.startsWith('//') ? filePath.substring(1) : filePath;
    const targetDir = isDir ? fixedPath : fixedPath.substring(0, fixedPath.lastIndexOf('/'));
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
    if (modal) modal.hide();
    
    if (!isDir) {
        sessionStorage.setItem('highlightFile', fixedPath);
    }
    
    navigateTo(targetDir);
    
    const successMessage = `${translations['search_navigated_to'] || 'Navigated to'}: ${targetDir}`;
    logAndSpeak(successMessage, 'info');
}

function openEditor(path) {
    if (!path) return;
    
    const existingTab = editorTabs.find(tab => tab.path === path);
    if (existingTab) {
        switchToEditorTab(existingTab.id);
        return;
    }
    
    const tabId = `editor-tab-${Date.now()}`;
    const fileName = path.split('/').pop();
    
    const newTab = {
        id: tabId,
        path: path,
        name: fileName,
        content: '',
        originalContent: '',
        modified: false,
        loading: true,
        editorMode: currentEditorMode,
        monacoEditorInstance: null
    };
    
    editorTabs.push(newTab);
    activeEditorTab = tabId;
    
    updateEditorUI();
    loadFileContent(path, tabId);
    updateEditorTabsSwitcher();
}

async function loadFileContent(path, tabId) {
    try {
        const response = await fetch(`?action=read_file&path=${encodeURIComponent(path)}`);
        const data = await response.json();
        
        const tab = editorTabs.find(t => t.id === tabId);
        if (!tab) return;
        
        if (data.success) {
            let content = '';
            
            if (data.content !== undefined) {
                content = data.content;
            } else {
                content = '';
            }
            
            tab.content = content;
            tab.originalContent = content;
            tab.loading = false;
            
            const editorMode = tab.editorMode || currentEditorMode;
            
            if (editorMode === 'advanced') {
                if (tab.monacoEditorInstance) {
                    tab.monacoEditorInstance.setValue(tab.content);
                }
            } else {
                const simpleEditor = document.getElementById(`${tabId}-simple-editor`);
                if (simpleEditor) {
                    simpleEditor.value = tab.content;
                    simpleEditor.readOnly = false;
                    
                    setTimeout(() => {
                        simpleEditor.style.height = 'auto';
                        simpleEditor.style.height = simpleEditor.scrollHeight + 'px';
                    }, 100);
                }
            }
            
            updateCharCount(tabId);
            
        } else {
            const simpleEditor = document.getElementById(`${tabId}-simple-editor`);
            if (simpleEditor) {
                simpleEditor.value = `Error: ${data.error || 'Unknown error'}`;
                simpleEditor.readOnly = true;
            }
            tab.loading = false;
        }
        
        updateEditorUI();
    } catch (error) {
        const simpleEditor = document.getElementById(`${tabId}-simple-editor`);
        if (simpleEditor) {
            simpleEditor.value = `Error: ${error.message}`;
            simpleEditor.readOnly = true;
        }
        
        const tab = editorTabs.find(t => t.id === tabId);
        if (tab) tab.loading = false;
        
        updateEditorUI();
    }
}

function markEditorAsModified(tabId) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (tab && !tab.loading) {
        const editorMode = tab.editorMode || currentEditorMode;
        
        if (editorMode === 'advanced' && tab.monacoEditorInstance) {
            tab.modified = tab.monacoEditorInstance.getValue() !== tab.originalContent;
        } else {
            const simpleEditor = document.getElementById(`${tabId}-simple-editor`);
            if (simpleEditor && !simpleEditor.readOnly) {
                tab.modified = simpleEditor.value !== tab.originalContent;
            }
        }
        
        updateCharCount(tabId);
        updateEditorTabsUI();
    }
}

async function saveEditorContent(tabId) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab) {
        logAndSpeak(translations['save_failed_tab_missing'] || 'Save failed: Tab does not exist', 'error');
        return;
    }
    
    if (tab.loading) {
        logAndSpeak(translations['save_wait_loading'] || 'File is loading, please save later', 'warning');
        return;
    }
    
    const editorMode = tab.editorMode || currentEditorMode;
    let content = '';
    
    if (editorMode === 'advanced' && tab.monacoEditorInstance) {
        content = tab.monacoEditorInstance.getValue();
        tab.content = content;
    } else {
        const simpleEditor = document.getElementById(`${tabId}-simple-editor`);
        if (simpleEditor && !simpleEditor.readOnly) {
            content = simpleEditor.value;
            tab.content = content;
        } else {
            content = tab.content || '';
        }
    }
    
    if (content === undefined || content === null) {
        logAndSpeak(translations['save_failed_empty'] || 'Save failed: Editor content is empty', 'error');
        return;
    }
    
    try {
        const saveBtn = document.querySelector(`[onclick*="${tabId}"]`);
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${translations['saving'] || 'Saving...'}`;
        }
        
        const formData = new FormData();
        formData.append('path', tab.path);
        
        if (content.length > 0) {
            try {
                const base64Content = btoa(unescape(encodeURIComponent(content)));
                formData.append('content', base64Content);
                formData.append('is_base64', '1');
            } catch (e) {
                formData.append('content', content);
                formData.append('is_base64', '0');
            }
        } else {
            formData.append('content', '');
            formData.append('is_base64', '0');
        }
        
        const response = await fetch('?action=save_file', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            const Message = translations['save_success'] || 'File saved successfully';
            logAndSpeak(Message);
            
            tab.modified = false;
            tab.content = content;
            tab.originalContent = content;
            
            updateEditorUI();
            refreshFiles();
            
        } else {
            logAndSpeak(`${translations['save_failed'] || 'Save failed'}: ${data.error}`, 'error');
        }
        
    } catch (error) {
        logAndSpeak(`${translations['save_failed'] || 'Save failed'}: ${error.message}`, 'error');
    } finally {
        const saveBtn = document.querySelector(`[onclick*="${tabId}"]`);
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = `<i class="fas fa-save"></i> ${translations['save'] || 'Save'}`;
        }
    }
}

function switchToEditorTab(tabId) {
    activeEditorTab = tabId;
    
    const editorPanel = document.getElementById('editorPanel');
    if (editorPanel.style.display === 'block') {
        updateEditorPanelContent();
    }
    
    updateEditorTabsSwitcher();
}

function toggleEditorPanel() {
    const editorPanel = document.getElementById('editorPanel');
    const toggleIcon = document.getElementById('editorToggleIcon');
    
    if (editorPanel.style.display === 'none' || editorPanel.style.display === '') {
        editorPanel.style.display = 'block';
        editorPanel.style.maxHeight = '100%';
        toggleIcon.className = 'fas fa-chevron-up';
        
        updateEditorPanelContent();
    } else {
        editorPanel.style.maxHeight = '0';
        setTimeout(() => {
            editorPanel.style.display = 'none';
        }, 300);
        toggleIcon.className = 'fas fa-chevron-down';
    }
}

function updateActiveEditor() {
    document.querySelectorAll('.editor-container').forEach(container => {
        container.style.display = 'none';
    });
    
    if (activeEditorTab) {
        const activeContainer = document.getElementById(`${activeEditorTab}-container`);
        if (activeContainer) {
            activeContainer.style.display = 'flex';
        }
    }
}

function loadMonacoEditor() {
    if (monacoLoaded) return Promise.resolve();
    if (monacoLoading) return new Promise(resolve => setTimeout(() => resolve(loadMonacoEditor()), 100));
    
    monacoLoading = true;
    
    return new Promise((resolve, reject) => {
        if (!document.querySelector('link[href*="monaco-editor"]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = '/luci-static/spectra/css/editor.main.css';
            document.head.appendChild(link);
        }
        
        if (window.require) {
            if (window.monaco && window.monaco.editor) {
                monacoLoaded = true;
                monacoLoading = false;
                resolve();
                return;
            }
        }
        
        const script = document.createElement('script');
        script.src = '/luci-static/spectra/js/loader.js';
        script.onload = () => {
            require.config({ 
                paths: { 
                    'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.55.1/min/vs'
                } 
            });
            
            require(['vs/editor/editor.main'], () => {
                defineCustomThemes();
                
                monacoLoaded = true;
                monacoLoading = false;
                resolve();
            });
        };
        script.onerror = (error) => {
            monacoLoading = false;
            reject(error);
        };
        document.head.appendChild(script);
    });
}

function defineCustomThemes() {
    monaco.editor.defineTheme('dark-plus', {
        base: 'vs-dark',
        inherit: true,
        rules: [
            { token: 'comment', foreground: '6A9955' },
            { token: 'keyword', foreground: 'C586C0' },
            { token: 'string', foreground: 'CE9178' },
            { token: 'number', foreground: 'B5CEA8' },
            { token: 'type', foreground: '4EC9B0' },
            { token: 'class', foreground: '4EC9B0' },
            { token: 'function', foreground: 'DCDCAA' }
        ],
        colors: {
            'editor.background': '#1E1E1E',
            'editor.foreground': '#D4D4D4',
            'editorCursor.foreground': '#FFFFFF',
            'editor.lineHighlightBackground': '#2D2D30',
            'editorLineNumber.foreground': '#858585',
            'editor.selectionBackground': '#264F78',
            'editor.inactiveSelectionBackground': '#3A3D41'
        }
    });
    
    monaco.editor.defineTheme('github-light', {
        base: 'vs',
        inherit: true,
        rules: [
            { token: 'comment', foreground: '6a737d' },
            { token: 'keyword', foreground: 'd73a49' },
            { token: 'string', foreground: '032f62' },
            { token: 'number', foreground: '005cc5' },
            { token: 'type', foreground: '6f42c1' }
        ],
        colors: {
            'editor.background': '#ffffff',
            'editor.foreground': '#24292e',
            'editor.lineHighlightBackground': '#f6f8fa'
        }
    });

    monaco.editor.defineTheme('my-custom-theme', {
        base: 'vs-dark',
        inherit: true,
        rules: [
            { token: 'comment', foreground: 'ffa500', fontStyle: 'italic' },
            { token: 'keyword', foreground: 'ff79c6' },
            { token: 'string', foreground: '8be9fd' },
            { token: 'keyword.php', foreground: 'ff79c6' },
            { token: 'string.php', foreground: '8be9fd' },
            { token: 'variable.php', foreground: '50fa7b' }
        ],
        colors: {
            'editor.foreground': '#f8f8f2',
            'editor.background': '#282a36',
            'editorCursor.foreground': '#f8f8f0',
            'editor.lineHighlightBackground': '#44475a',
            'editorLineNumber.foreground': '#6272a4',
            'editor.selectionBackground': '#44475a'
        }
    });
    
    monaco.editor.defineTheme('monokai', {
        base: 'vs-dark',
        inherit: true,
        rules: [
            { token: 'comment', foreground: '75715E' },
            { token: 'string', foreground: 'E6DB74' },
            { token: 'keyword', foreground: 'F92672' },
            { token: 'number', foreground: 'AE81FF' },
            { token: 'type', foreground: '66D9EF' },
            { token: 'class', foreground: 'A6E22E' },
            { token: 'function', foreground: 'A6E22E' },
            { token: 'variable', foreground: 'F8F8F2' }
        ],
        colors: {
            'editor.background': '#272822',
            'editor.foreground': '#F8F8F2',
            'editorCursor.foreground': '#F8F8F2',
            'editor.lineHighlightBackground': '#3E3D32',
            'editorLineNumber.foreground': '#90908A'
        }
    });
    
    monaco.editor.defineTheme('solarized-dark', {
        base: 'vs-dark',
        inherit: true,
        rules: [
            { token: 'comment', foreground: '586E75' },
            { token: 'string', foreground: '2AA198' },
            { token: 'keyword', foreground: '859900' },
            { token: 'number', foreground: 'D33682' },
            { token: 'type', foreground: 'B58900' },
            { token: 'class', foreground: 'CB4B16' }
        ],
        colors: {
            'editor.background': '#002B36',
            'editor.foreground': '#839496',
            'editorCursor.foreground': '#93A1A1',
            'editor.lineHighlightBackground': '#073642'
        }
    });
}

function switchEditorMode(mode, tabId) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab) return;
    
    const modeText = document.getElementById(`editorModeText-${tabId}`);
    if (modeText) {
        modeText.textContent = mode === 'simple' 
            ? (translations['switch_to_advanced_editor'] || 'Switch to Advanced Editor') 
            : (translations['switch_to_simple_editor'] || 'Switch to Simple Editor');
    }
    
    if (tab.editorMode === 'advanced' && tab.monacoEditorInstance) {
        const model = tab.monacoEditorInstance.getModel();
        if (model) {
            tab.viewState = tab.monacoEditorInstance.saveViewState();
        }
        tab.monacoEditorInstance.dispose();
        tab.monacoEditorInstance = null;
    }
    
    tab.editorMode = mode;
    
    updateEditorUI();
    
    if (mode === 'advanced') {
        loadMonacoEditor().then(() => {
            setTimeout(() => {
                initMonacoEditor(tabId);
                setTimeout(() => {
                    if (tab.monacoEditorInstance) {
                        tab.monacoEditorInstance.setValue(tab.content || '');
                        
                        if (tab.viewState) {
                            setTimeout(() => {
                                tab.monacoEditorInstance.restoreViewState(tab.viewState);
                                tab.monacoEditorInstance.focus();
                            }, 50);
                        }
                    }
                }, 100);
            }, 50);
        }).catch(error => {
            tab.editorMode = 'simple';
            updateEditorUI();
            logAndSpeak(
                translations['advanced_editor_load_failed'] || 'Failed to load advanced editor, switched back to simple editor', 
                'error'
            );
        });
    } else {
        setTimeout(() => {
            const simpleEditor = document.getElementById(`${tabId}-simple-editor`);
            if (simpleEditor) {
                simpleEditor.value = tab.content || '';
                
                simpleEditor.style.height = 'auto';
                simpleEditor.style.height = (simpleEditor.scrollHeight + 10) + 'px';
                simpleEditor.focus();
                
                setupSimpleEditorEvents(tabId);
            }
            updateCharCount(tabId);
        }, 100);
    }
}

function setupSimpleEditorEvents(tabId) {
    const simpleEditor = document.getElementById(`${tabId}-simple-editor`);
    if (!simpleEditor) return;
    
    const newEditor = simpleEditor.cloneNode(true);
    simpleEditor.parentNode.replaceChild(newEditor, simpleEditor);
    
    function updateCursorPosition() {
        const cursorPos = newEditor.selectionStart;
        const text = newEditor.value.substring(0, cursorPos);
        const lines = text.split('\n');
        const line = lines.length;
        const column = lines[lines.length - 1].length + 1;
        
        const positionElement = document.getElementById(`${tabId}-position-info`);
        if (positionElement) {
            const lineText = translations['line_label'] || 'Line';
            const columnText = translations['column_label'] || 'Column';
            positionElement.textContent = `${lineText}: ${line}, ${columnText}: ${column}`;
        }
    }
    
    newEditor.addEventListener('input', function() {
        const tab = editorTabs.find(t => t.id === tabId);
        if (tab) {
            const isModified = (this.value !== tab.originalContent);
            if (tab.modified !== isModified) {
                tab.modified = isModified;
                updateEditorTabsUI();
            }
        }
        
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight + 10) + 'px';
        
        updateCharCount(tabId);
        updateCursorPosition();
    });
    
    newEditor.addEventListener('click', updateCursorPosition);
    newEditor.addEventListener('keyup', updateCursorPosition);
    newEditor.addEventListener('select', updateCursorPosition);
    newEditor.addEventListener('mouseup', updateCursorPosition);
    
    newEditor.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            e.preventDefault();
            const start = this.selectionStart;
            const end = this.selectionEnd;
            
            this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
            this.selectionStart = this.selectionEnd = start + 4;
            this.dispatchEvent(new Event('input'));
            
            setTimeout(updateCursorPosition, 10);
        }
    });
    
    setTimeout(updateCursorPosition, 100);
}

async function initMonacoEditor(tabId) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab) return;
    
    if (!monacoLoaded) {
        try {
            await loadMonacoEditor();
        } catch (error) {
            console.error('Failed to load Monaco Editor:', error);
            logAndSpeak(translations['load_advanced_editor_failed'] || 'Failed to load advanced editor, switched back to simple editor', 'error');
            switchEditorMode('simple', tabId);
            return;
        }
    }
    
    const container = document.getElementById(`${tabId}-monaco-container`);
    if (!container) return;
    
    if (tab.monacoEditorInstance) {
        tab.monacoEditorInstance.dispose();
        tab.monacoEditorInstance = null;
    }
    
    container.innerHTML = '';
    
    try {

        const savedFontSize = localStorage.getItem('editorFontSize') || '16';
        const detectedLanguage = detectLanguage(tab.name);
        const theme = localStorage.getItem('editorTheme') || 'vs-dark';
        
        const editor = monaco.editor.create(container, {
            value: tab.content || '',
            language: detectedLanguage,
            theme: theme,
            automaticLayout: true,
            fontSize: parseInt(savedFontSize),
            lineNumbers: 'on',
            minimap: { 
                enabled: true,
                scale: 2,
                showSlider: 'always'
            },
            autoIndent: 'full',
            autoClosingBrackets: 'always',
            autoClosingQuotes: 'always',
            autoSurround: 'languageDefined',
            indentSize: 4,
            tabSize: 4,
            insertSpaces: true,
            useTabStops: true,
            scrollBeyondLastLine: true,
            wordWrap: 'on',
            wrappingIndent: 'indent',
            renderLineHighlight: 'all',
            scrollbar: {
                vertical: 'auto',
                horizontal: 'auto',
                verticalHasArrows: true,
                horizontalHasArrows: true,
                verticalScrollbarSize: 12,
                horizontalScrollbarSize: 12,
                arrowSize: 20
            },
            formatOnPaste: true,
            formatOnType: true,
            suggestOnTriggerCharacters: true,
            acceptSuggestionOnEnter: 'on',
            tabCompletion: 'on',
            wordBasedSuggestions: 'allDocuments',
            parameterHints: { 
                enabled: true,
                cycle: true
            },
            hover: { 
                enabled: true,
                delay: 300
            },
            contextmenu: true,
            quickSuggestions: { 
                other: true, 
                comments: true, 
                strings: true 
            },
            suggest: {
                showWords: true,
                showKeywords: true,
                showSnippets: true,
                showClasses: true,
                showFunctions: true,
                showVariables: true,
                showModules: true,
                showReferences: true
            },
            snippetSuggestions: 'bottom',
            inlineSuggest: {
                enabled: true,
                mode: 'prefix'
            },
            guides: {
                bracketPairs: true,
                highlightActiveBracketPair: true,
                indentation: true
            },
            bracketPairColorization: {
                enabled: true
            },
            cursorBlinking: 'blink',
            cursorSmoothCaretAnimation: 'on',
            cursorStyle: 'line',
            folding: true,
            foldingStrategy: 'auto',
            foldingHighlight: true,
            foldingImportsByDefault: true,
            smoothScrolling: true,
            mouseWheelZoom: true,
            multiCursorMergeOverlapping: true,
            overviewRulerLanes: 3,
            fixedOverflowWidgets: true,
            lineDecorationsWidth: 10,
            padding: { top: 10, bottom: 10 },
            renderWhitespace: 'selection',


            rulers: [],
            selectionClipboard: true,
            selectionHighlight: true,
            semanticHighlighting: {
                enabled: true
            },
            showFoldingControls: 'always',
            showUnused: true,
            stickyScroll: {
                enabled: true,
                maxLineCount: 5
            },
            unicodeHighlight: {
                ambiguousCharacters: true,
                invisibleCharacters: true
            },
            wordSeparators: '`~!@#$%^&*()-=+[{]}\\|;:\'",.<>/?',
            wrappingStrategy: 'advanced'
        });
        
        tab.monacoEditorInstance = editor;

        editor.onDidChangeModelContent(() => {
            const currentContent = editor.getValue();
            
            if (tab.editorMode === 'advanced') {
                tab.content = currentContent;
            }
            
            tab.modified = (currentContent !== tab.originalContent);
            updateCharCount(tabId);
            updateEditorTabsUI();
        });
        
        editor.onDidChangeCursorPosition((e) => {
            updateEditorPositionInfo(tabId, e.position.lineNumber, e.position.column);
        });
        
        editor.onDidChangeCursorSelection((e) => {
            const selection = e.selection;
            const selectedText = editor.getModel()?.getValueInRange(selection) || '';
            updateSelectedTextInfo(tabId, selectedText);
        });
        
        setupEditorShortcuts(editor, tabId);
        
        setTimeout(() => {
            const languageSelect = document.getElementById(`${tabId}-language-select`);
            if (languageSelect) {
                languageSelect.value = detectedLanguage;
            }
            
            const fontSizeSelect = document.querySelector(`#${tabId}-panel-container .editor-fontsize-select`);
            if (fontSizeSelect) {
                fontSizeSelect.value = savedFontSize;
            }
            
            const themeSelect = document.querySelector(`#${tabId}-panel-container .editor-theme-select`);
            if (themeSelect) {
                themeSelect.value = theme;
            }
        }, 300);
        
        updateEditorPositionInfo(tabId, 1, 1);
        
    } catch (error) {
        console.error('Failed to initialize Monaco Editor:', error);
        logAndSpeak(`${translations['init_advanced_editor_failed'] || 'Failed to initialize advanced editor'}: ${error.message}`, 'error');
    }
}

function setupEditorShortcuts(editor, tabId) {
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, () => {
        saveEditorContent(tabId);
    });
    
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyF, () => {
        editor.getAction('actions.find').run();
    });
    
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyH, () => {
        editor.getAction('editor.action.startFindReplaceAction').run();
    });
    
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyG, () => {
        editor.getAction('editor.action.gotoLine').run();
    });
    
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.Space, () => {
        editor.trigger('', 'editor.action.triggerSuggest', {});
    });
    
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyMod.Shift | monaco.KeyCode.KeyF, () => {
        editor.getAction('editor.action.formatDocument').run();
    });
    
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyMod.Shift | monaco.KeyCode.KeyK, () => {
        editor.getAction('editor.action.deleteLines').run();
    });
    
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.Slash, () => {
        editor.getAction('editor.action.commentLine').run();
    });
    
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyMod.Shift | monaco.KeyCode.Slash, () => {
        editor.getAction('editor.action.blockComment').run();
    });
    
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.BracketLeft, () => {
        editor.getAction('editor.action.indentLines').run();
    });
    
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.BracketRight, () => {
        editor.getAction('editor.action.outdentLines').run();
    });
    
    editor.addCommand(monaco.KeyMod.Alt | monaco.KeyMod.Shift | monaco.KeyCode.ArrowDown, () => {
        editor.getAction('editor.action.copyLinesDownAction').run();
    });
    
    editor.addCommand(monaco.KeyMod.Alt | monaco.KeyCode.ArrowUp, () => {
        editor.getAction('editor.action.moveLinesUpAction').run();
    });
    
    editor.addCommand(monaco.KeyMod.Alt | monaco.KeyCode.ArrowDown, () => {
        editor.getAction('editor.action.moveLinesDownAction').run();
    });
}

function updateSelectedTextInfo(tabId, selectedText) {
    const selectedTextElement = document.getElementById(`${tabId}-selected-text-info`);
    if (selectedTextElement) {
        if (selectedText) {
            selectedTextElement.textContent = `${translations['selected'] || 'Selected'}: ${selectedText.length} ${translations['characters'] || 'Characters'}`;
        } else {
            selectedTextElement.textContent = '';
        }
    }
}

function detectLanguage(filename) {
    const ext = filename.toLowerCase().split('.').pop();
    const languageMap = {
        'js': 'javascript',
        'jsx': 'javascript',
        'mjs': 'javascript',
        'cjs': 'javascript',
        'ts': 'typescript',
        'tsx': 'typescript',
        'vue': 'vue',
        'svelte': 'html',
        'html': 'html',
        'htm': 'html',
        'xhtml': 'html',
        'css': 'css',
        'scss': 'scss',
        'sass': 'sass',
        'less': 'less',
        'styl': 'stylus',
        'php': 'php',
        'php3': 'php',
        'php4': 'php',
        'php5': 'php',
        'php7': 'php',
        'phtml': 'php',
        'py': 'python',
        'pyw': 'python',
        'pyc': 'python',
        'pyo': 'python',
        'pyz': 'python',
        'java': 'java',
        'class': 'java',
        'jar': 'java',
        'c': 'c',
        'h': 'c',
        'cpp': 'cpp',
        'cc': 'cpp',
        'cxx': 'cpp',
        'hpp': 'cpp',
        'hxx': 'cpp',
        'cs': 'csharp',
        'csx': 'csharp',
        'go': 'go',
        'rs': 'rust',
        'rb': 'ruby',
        'erb': 'ruby',
        'pl': 'perl',
        'pm': 'perl',
        't': 'perl',
        'lua': 'lua',
        'swift': 'swift',
        'kt': 'kotlin',
        'kts': 'kotlin',
        'scala': 'scala',
        'sc': 'scala',
        'dart': 'dart',
        'r': 'r',
        'rmd': 'rmarkdown',
        'jl': 'julia',
        'hs': 'haskell',
        'lhs': 'haskell',
        'elm': 'elm',
        'clj': 'clojure',
        'cljs': 'clojure',
        'cljc': 'clojure',
        'edn': 'clojure',
        'ex': 'elixir',
        'exs': 'elixir',
        'erl': 'erlang',
        'hrl': 'erlang',
        'fs': 'fsharp',
        'fsx': 'fsharp',
        'fsi': 'fsharp',
        'astro': 'javascript',
        'json': 'json',
        'json5': 'javascript',
        'jsonc': 'json',
        'xml': 'xml',
        'xsl': 'xml',
        'xslt': 'xml',
        'xsd': 'xml',
        'yaml': 'yaml',
        'yml': 'yaml',
        'toml': 'toml',
        'ini': 'ini',
        'conf': 'ini',
        'cfg': 'ini',
        'properties': 'properties',
        'env': 'properties',
        'sql': 'sql',
        'mysql': 'sql',
        'pgsql': 'sql',
        'psql': 'sql',
        'plsql': 'sql',
        'ddl': 'sql',
        'dml': 'sql',
        'md': 'markdown',
        'markdown': 'markdown',
        'mdx': 'markdown',
        'txt': 'plaintext',
        'text': 'plaintext',
        'log': 'plaintext',
        'rst': 'restructuredtext',
        'tex': 'latex',
        'sh': 'shell',
        'bash': 'shell',
        'zsh': 'shell',
        'fish': 'shell',
        'ps1': 'powershell',
        'psm1': 'powershell',
        'psd1': 'powershell',
        'bat': 'batch',
        'cmd': 'batch',
        'dockerfile': 'dockerfile',
        'docker': 'dockerfile',
        'makefile': 'makefile',
        'cmake': 'cmake',
        'gradle': 'gradle',
        'groovy': 'groovy',
        'jenkinsfile': 'groovy',
        'j2': 'jinja',
        'jinja': 'jinja',
        'jinja2': 'jinja',
        'twig': 'twig',
        'njk': 'nunjucks',
        'liquid': 'liquid',
        'hbs': 'handlebars',
        'handlebars': 'handlebars',
        'svg': 'xml',
        'graphql': 'graphql',
        'gql': 'graphql',
        'proto': 'protobuf',
        'thrift': 'thrift',
        'avdl': 'avro',
        'avsc': 'json',
        'ipynb': 'json',
        'csv': 'csv',
        'tsv': 'plaintext',
        'diff': 'diff',
        'patch': 'diff',
        'hosts': 'hosts',
        'nginx': 'nginx',
        'apache': 'apache',
        'htaccess': 'apache',
        'htpasswd': 'apache',
        'gitignore': 'gitignore',
        'gitattributes': 'gitattributes',
        'editorconfig': 'editorconfig',
        'eslintrc': 'json',
        'prettierrc': 'json',
        'babelrc': 'json',
        'tsconfig': 'json',
        'package': 'json',
        'lock': 'json',
        'rc': 'shell',
        'profile': 'shell',
        'service': 'shell',
        'timer': 'shell',
        'network': 'shell',
        'wireless': 'shell',
        'firewall': 'shell',
        'dhcp': 'shell',
        'qos': 'shell',
        'uci': 'shell',
        'config': 'shell',
        'ipk': 'plaintext',
        'opk': 'plaintext',
        'list': 'plaintext',
        'status': 'plaintext',
        'state': 'plaintext',
        'cache': 'plaintext',
        'ko': 'plaintext',
        'elf': 'plaintext',
        'bin': 'plaintext',
        'img': 'plaintext',
        'trx': 'plaintext',
        'chk': 'plaintext',
        'factory': 'plaintext',
        'sysupgrade': 'plaintext',
        'ash': 'shell',
        'dash': 'shell',
        'init': 'shell',
        'rules': 'shell',
        'module': 'shell',
        'modprobe': 'shell',
        'fstab': 'shell',
        'mtab': 'plaintext',
        'passwd': 'plaintext',
        'shadow': 'plaintext',
        'group': 'plaintext',
        'route': 'shell',
        'iptables': 'shell',
        'nftables': 'shell',
        'resolv': 'plaintext',
        'inetd': 'shell',
        'xinetd': 'shell',
        'supervisor': 'ini',
        'inc': 'makefile',
        'bb': 'shell',
        'bbclass': 'shell',
        'rrd': 'plaintext',
        'xmlrpc': 'xml',
        'cbi': 'lua',
        'tree': 'lua',
        'pem': 'plaintext',
        'crt': 'plaintext',
        'key': 'plaintext',
        'csr': 'plaintext',
        'pfx': 'plaintext',
        'der': 'plaintext',
        'gz': 'plaintext',
        'bz2': 'plaintext',
        'xz': 'plaintext',
        'lzma': 'plaintext',
        'zst': 'plaintext',
        'db': 'plaintext',
        'sqlite': 'plaintext',
        'sqlite3': 'plaintext',
        'qcow2': 'plaintext',
        'vmdk': 'plaintext',
        'vdi': 'plaintext',
        'dtb': 'plaintext',
        'dts': 'plaintext',
        'hex': 'plaintext',
        'uf2': 'plaintext',
        'pcap': 'plaintext',
        'cap': 'plaintext',
        'psk': 'plaintext',
        'ovpn': 'shell',
        'wg': 'shell',
    };
    
    return languageMap[ext] || 'plaintext';
}

function updateEditorPositionInfo(tabId, line, column) {
    const positionElement = document.getElementById(`${tabId}-position-info`);
    if (positionElement) {
        positionElement.textContent = `${translations['line_label'] || 'Line'}: ${line}, ${translations['column_label'] || 'Column'}: ${column}`;
    }
}

function formatCode(tabId) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab || !tab.monacoEditorInstance) return;

    const editor = tab.monacoEditorInstance;

    try {
        editor.getAction('editor.action.formatDocument').run();
        logAndSpeak(translations['code_format_success'] || 'Code formatted successfully', 'success');
    } catch (error) {
        logAndSpeak(
            `${translations['code_format_error'] || 'Code format error'}: ${error.message}`,
            'error'
        );
    }
}

function changeEditorTheme(tabId, theme) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab) return;
    
    try {
        const themes = {
            'vs-dark': 'vs-dark',
            'vs': 'vs',
            'hc-black': 'hc-black',
            'my-custom-theme': {
                base: 'vs-dark',
                inherit: true,
                rules: [
                    { token: 'comment', foreground: 'ffa500', fontStyle: 'italic' },
                    { token: 'keyword', foreground: 'ff79c6' },
                    { token: 'string', foreground: '8be9fd' },
                    { token: 'keyword.php', foreground: 'ff79c6' },
                    { token: 'string.php', foreground: '8be9fd' },
                    { token: 'variable.php', foreground: '50fa7b' }
                ],
                colors: {
                    'editor.foreground': '#f8f8f2',
                    'editor.background': '#282a36',
                    'editorCursor.foreground': '#f8f8f0',
                    'editor.lineHighlightBackground': '#44475a',
                    'editorLineNumber.foreground': '#6272a4'
                }
            },
            'monokai': {
                base: 'vs-dark',
                inherit: true,
                rules: [
                    { token: 'comment', foreground: '75715E' },
                    { token: 'string', foreground: 'E6DB74' },
                    { token: 'keyword', foreground: 'F92672' },
                    { token: 'number', foreground: 'AE81FF' },
                    { token: 'type', foreground: '66D9EF' },
                    { token: 'class', foreground: 'A6E22E' },
                    { token: 'function', foreground: 'A6E22E' },
                    { token: 'variable', foreground: 'F8F8F2' }
                ],
                colors: {
                    'editor.background': '#272822',
                    'editor.foreground': '#F8F8F2',
                    'editorCursor.foreground': '#F8F8F2',
                    'editor.lineHighlightBackground': '#3E3D32',
                    'editorLineNumber.foreground': '#90908A'
                }
            },
            'solarized-dark': {
                base: 'vs-dark',
                inherit: true,
                rules: [
                    { token: 'comment', foreground: '586E75' },
                    { token: 'string', foreground: '2AA198' },
                    { token: 'keyword', foreground: '859900' },
                    { token: 'number', foreground: 'D33682' },
                    { token: 'type', foreground: 'B58900' },
                    { token: 'class', foreground: 'CB4B16' }
                ],
                colors: {
                    'editor.background': '#002B36',
                    'editor.foreground': '#839496',
                    'editorCursor.foreground': '#93A1A1',
                    'editor.lineHighlightBackground': '#073642'
                }
            }
        };
        
        if (themes[theme]) {
            if (typeof themes[theme] === 'object') {
                monaco.editor.defineTheme('custom-' + theme, themes[theme]);
                monaco.editor.setTheme('custom-' + theme);
            } else {
                monaco.editor.setTheme(themes[theme]);
            }
            
            localStorage.setItem('editorTheme', theme);
            
            const themeSelect = document.querySelector(`#${tabId}-panel-container .editor-theme-select`);
            if (themeSelect) {
                themeSelect.value = theme;
            }
            
            logAndSpeak(
                `${translations['theme_switched_to'] || 'Theme switched to'}: ${theme}`,
                'success'
            );
        }
    } catch (error) {
        logAndSpeak(
            `${translations['theme_change_error'] || 'Theme change error'}: ${error.message}`,
            'error'
        );
    }
}

function changeEditorLanguage(tabId, language) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab || !tab.monacoEditorInstance) return;

    const editor = tab.monacoEditorInstance;
    const model = editor.getModel();

    if (model) {
        monaco.editor.setModelLanguage(model, language);

        const languageSelect = document.getElementById(`${tabId}-language-select`);
        if (languageSelect) {
            languageSelect.value = language;
        }

        logAndSpeak(
            `${translations['language_switched_to'] || 'Language switched to'}: ${getLanguageDisplayName(language)}`,
            'success'
        );
    }
}

function getLanguageDisplayName(language) {
    const displayNames = {
        'plaintext': translations['language_plaintext'] || 'plain text',
        'javascript': 'JavaScript',
        'typescript': 'TypeScript',
        'html': 'HTML',
        'css': 'CSS',
        'php': 'PHP',
        'python': 'Python',
        'java': 'Java',
        'c': 'C',
        'cpp': 'C++',
        'csharp': 'C#',
        'go': 'Go',
        'rust': 'Rust',
        'ruby': 'Ruby',
        'perl': 'Perl',
        'lua': 'Lua',
        'swift': 'Swift',
        'kotlin': 'Kotlin',
        'scala': 'Scala',
        'dart': 'Dart',
        'json': 'JSON',
        'xml': 'XML',
        'yaml': 'YAML',
        'toml': 'TOML',
        'ini': 'INI',
        'csv': 'CSV',
        'shell': 'Shell',
        'powershell': 'PowerShell',
        'batch': 'Batch',
        'sql': 'SQL',
        'mysql': 'MySQL',
        'plsql': 'PL/SQL',
        'postgresql': 'PostgreSQL',
        'jinja': 'Jinja2',
        'twig': 'Twig',
        'handlebars': 'Handlebars',
        'mustache': 'Mustache',
        'dockerfile': 'Dockerfile',
        'makefile': 'Makefile',
        'gradle': 'Gradle',
        'cmake': 'CMake',
        'markdown': 'Markdown',
        'restructuredtext': 'reStructuredText',
        'latex': 'LaTeX',
        'graphql': 'GraphQL',
        'protobuf': 'Protocol Buffers',
        'diff': 'Diff',
        'nginx': 'Nginx',
        'apache': 'Apache',
        'gitignore': '.gitignore',
        'editorconfig': '.editorconfig'
    };
    
    return displayNames[language] || language;
}

function openFindReplace(tabId) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab || !tab.monacoEditorInstance) return;
    
    const editor = tab.monacoEditorInstance;
    editor.getAction('actions.find').run();
}

function autoSaveContent(tabId) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab || !tab.modified) return;
}

function downloadCurrentFile(tabId) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab) return;

    const content = tab.monacoEditorInstance ? 
        tab.monacoEditorInstance.getValue() : 
        document.getElementById(`${tabId}-simple-editor`)?.value || '';

    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = tab.name;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    logAndSpeak(
        `${translations['file_download_started'] || 'File'} ${tab.name} ${translations['download_started'] || 'download started'}`,
        'success'
    );
}

function showKeyboardShortcuts() {
    const shortcuts = [
        { key: 'Ctrl/Cmd + S', desc: translations['save_file'] || 'Save file' },
        { key: 'Ctrl/Cmd + F', desc: translations['search.find'] || 'Find' },
        { key: 'Ctrl/Cmd + H', desc: translations['search.replace'] || 'Replace' },
        { key: 'Ctrl/Cmd + Z', desc: translations['undo'] || 'Undo' },
        { key: 'Ctrl/Cmd + Y', desc: translations['redo'] || 'Redo' },
        { key: 'Ctrl/Cmd + D', desc: translations['duplicate_line'] || 'Duplicate current line' },
        { key: 'Ctrl/Cmd + Shift + K', desc: translations['delete_line'] || 'Delete current line' },
        { key: 'Ctrl/Cmd + /', desc: translations['toggle_comment'] || 'Comment/Uncomment' },
        { key: 'Alt + ↑/↓', desc: translations['move_line'] || 'Move line up/down' },
        { key: 'Ctrl/Cmd + Alt + ↑/↓', desc: translations['add_cursor'] || 'Add multiple cursors' },
        { key: 'F12', desc: translations['go_to_definition'] || 'Go to definition' },
        { key: 'Shift + F12', desc: translations['find_references'] || 'Find references' },
        { key: 'Ctrl/Cmd + Space', desc: translations['trigger_suggestion'] || 'Trigger suggestion' },
        { key: 'Ctrl/Cmd + Shift + F', desc: translations['format_document'] || 'Format document' }
    ];

    let html = `<div class="editor-keyboard-shortcuts">
                    <h6>${translations['keyboard_shortcuts'] || 'Keyboard Shortcuts'}</h6>`;
    
    shortcuts.forEach(shortcut => {
        html += `
            <div class="shortcut-item">
                <span class="shortcut-desc">${shortcut.desc}</span>
                <span class="shortcut-key">${shortcut.key}</span>
            </div>
        `;
    });

    html += '</div>';

    const statusBar = document.querySelector('.editor-status-bar');
    if (statusBar) {
        const existingHelp = document.querySelector('.editor-keyboard-shortcuts');
        if (existingHelp) {
            existingHelp.remove();
        } else {
            statusBar.insertAdjacentHTML('afterend', html);
        }
    }
}

function updateEditorUI() {
    const editorPanel = document.getElementById('editorPanel');
    const editorTabsSwitcher = document.getElementById('editorTabsSwitcher');
    
    if (editorTabs.length > 0) {
        editorTabsSwitcher.style.display = 'block';
        updateEditorTabsSwitcher();
        
        if (editorPanel.style.display === 'block') {
            updateEditorPanelContent();
        }
    } else {
        editorTabsSwitcher.style.display = 'none';
        editorPanel.style.display = 'none';
        editorPanel.style.maxHeight = '0';
    }
}

function updateEditorPanelContent() {
    const tabsNav = document.getElementById('editorPanelTabsNav');
    const contentArea = document.getElementById('editorPanelContent');
    
    if (!tabsNav || !contentArea) return;
    
    tabsNav.innerHTML = '';
    editorTabs.forEach(tab => {
        const tabElement = document.createElement('div');
        tabElement.className = `editor-tab ${tab.id === activeEditorTab ? 'active' : ''}`;
        tabElement.style.cssText = `
            padding: 8px 15px;
            background: ${tab.id === activeEditorTab ? 'var(--accent-tertiary)' : 'var(--card-bg)'};
            color: ${tab.id === activeEditorTab ? 'white' : 'var(--text-primary)'};
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            font-size: 14px;
        `;
        
        tabElement.onclick = () => switchToEditorTab(tab.id);
        
        let tabName = escapeHtml(tab.name);
        if (tab.modified) {
            tabName = `* ${tabName}`;
        }
        
        tabElement.innerHTML = `
            <span>${tabName}</span>
            <span style="margin-left: 8px;" onclick="closeEditorTab('${tab.id}', event)">
                <i class="fas fa-times"></i>
            </span>
        `;
        
        tabsNav.appendChild(tabElement);
    });
    
    contentArea.innerHTML = '';
    
    if (activeEditorTab) {
        const tab = editorTabs.find(t => t.id === activeEditorTab);
        if (tab) {
            const editorContainer = document.createElement('div');
            editorContainer.id = `${tab.id}-panel-container`;
            editorContainer.style.cssText = `
                height: 100%;
                display: flex;
                flex-direction: column;
            `;
            
            const editorMode = tab.editorMode || currentEditorMode;
            
            editorContainer.innerHTML = `
                <div class="editor-toolbar">
                    <div class="editor-toolbar-left">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-file"></i>
                            <span>${escapeHtml(tab.name)}</span>
                            ${tab.modified ? '<span style="color: var(--text-primary);">*</span>' : ''}
                        </div>
                        
                        <button class="btn btn-sm btn-indigo editor-mode-toggle" 
                                onclick="toggleEditorMode('${tab.id}')" 
                                id="editorModeBtn-${tab.id}"
                                data-translate-tooltip="toggle_editor_mode">
                            <i class="fas fa-exchange-alt"></i> 
                            <span id="editorModeText-${tab.id}"
                                data-translate="${editorMode === 'simple' ? 'switch_to_advanced' : 'switch_to_simple'}">
                                ${editorMode === 'simple' ? 'Switch to advanced editor' : 'Switch to simple editor'}
                            </span>
                        </button>

                        ${editorMode === 'advanced' ? `
                        <button class="btn btn-sm btn-purple" onclick="toggleComment('${tab.id}')" 
                                data-translate-tooltip="toggle_comment">
                            <i class="fas fa-comment"></i>
                            <span data-translate="toggle_comment">Toggle comment</span>
                        </button>

                            <select class="editor-language-select" 
                                    onchange="changeEditorLanguage('${tab.id}', this.value)" 
                                    id="${tab.id}-language-select">
                                <option value="plaintext" data-translate="auto_detect">Auto detect</option>
                                <option value="plaintext" data-translate="plain_text">Plain text</option>
                                <option value="html">HTML</option>
                                <option value="css">CSS</option>
                                <option value="javascript">JavaScript</option>
                                <option value="typescript">TypeScript</option>
                                <option value="jsx">JSX</option>
                                <option value="tsx">TSX</option>
                                <option value="vue">Vue</option>
                                <option value="svelte">Svelte</option>
                                <option value="php">PHP</option>
                                <option value="python">Python</option>
                                <option value="java">Java</option>
                                <option value="c">C</option>
                                <option value="cpp">C++</option>
                                <option value="csharp">C#</option>
                                <option value="go">Go</option>
                                <option value="rust">Rust</option>
                                <option value="ruby">Ruby</option>
                                <option value="perl">Perl</option>
                                <option value="lua">Lua</option>
                                <option value="swift">Swift</option>
                                <option value="kotlin">Kotlin</option>
                                <option value="scala">Scala</option>
                                <option value="dart">Dart</option>
                                <option value="json">JSON</option>
                                <option value="xml">XML</option>
                                <option value="yaml">YAML</option>
                                <option value="toml">TOML</option>
                                <option value="ini">INI</option>
                                <option value="csv">CSV</option>
                                <option value="shell">Shell/Bash</option>
                                <option value="powershell">PowerShell</option>
                                <option value="batch">Batch</option>
                                <option value="sql">SQL</option>
                                <option value="mysql">MySQL</option>
                                <option value="plsql">PL/SQL</option>
                                <option value="postgresql">PostgreSQL</option>
                                <option value="jinja">Jinja2</option>
                                <option value="twig">Twig</option>
                                <option value="handlebars">Handlebars</option>
                                <option value="mustache">Mustache</option>
                                <option value="dockerfile">Dockerfile</option>
                                <option value="makefile">Makefile</option>
                                <option value="gradle">Gradle</option>
                                <option value="cmake">CMake</option>
                                <option value="markdown">Markdown</option>
                                <option value="restructuredtext">reStructuredText</option>
                                <option value="latex">LaTeX</option>
                                <option value="graphql">GraphQL</option>
                                <option value="protobuf">Protocol Buffers</option>
                                <option value="diff">Diff/Patch</option>
                                <option value="nginx">Nginx</option>
                                <option value="apache">Apache</option>
                                <option value="gitignore">.gitignore</option>
                                <option value="editorconfig">.editorconfig</option>
                            </select> 
                            <select class="editor-fontsize-select" 
                                    onchange="changeEditorFontSize('${tab.id}', this.value)"
                                    data-translate-tooltip="fontSizeL">
                                <option value="10">10px</option>
                                <option value="11">11px</option>
                                <option value="12">12px</option>
                                <option value="13">13px</option>
                                <option value="14" selected>14px</option>
                                <option value="15">15px</option>
                                <option value="16">16px</option>
                                <option value="17">17px</option>
                                <option value="18">18px</option>
                                <option value="20">20px</option>
                                <option value="22">22px</option>
                                <option value="24">24px</option>
                                <option value="26">26px</option>
                                <option value="28">28px</option>
                                <option value="32">32px</option>
                                <option value="36">36px</option>
                            </select>                            
                            <select class="editor-theme-select" onchange="changeEditorTheme('${tab.id}', this.value)">
                                <option value="vs-dark" data-translate="theme_dark">Dark theme</option>
                                <option value="vs" data-translate="theme_light">Light theme</option>
                                <option value="hc-black" data-translate="theme_high_contrast">High contrast</option>
                                <option value="my-custom-theme" data-translate="theme_custom">Custom Theme</option>
                                <option value="monokai">Monokai</option>
                                <option value="solarized-dark">Solarized Dark</option>
                            </select>
                            
                        <button class="btn btn-sm btn-primary" onclick="formatCode('${tab.id}')">
                            <i class="fas fa-brush"></i>
                            <span data-translate="format">Format</span>
                        </button>

                        <button class="btn btn-sm btn-pink" onclick="toggleFullscreen()" data-translate-tooltip="fullscreen">
                            <i class="fas fa-expand"></i>
                            <span data-translate="fullscreen">Fullscreen</span>
                        </button>   

                        <button class="btn btn-sm btn-orange" onclick="openDiffView('${tab.id}')"
                        data-translate-tooltip="diff_view">
                            <i class="fas fa-code-compare"></i>
                            <span data-translate="diff_view">Diff View</span>
                        </button>

                        <button class="btn btn-sm btn-teal" onclick="openFindReplace('${tab.id}')">
                            <i class="fas fa-search"></i>
                            <span data-translate="search.find">Find</span>
                        </button>
                        ` : ''}
                    </div>

                      <div class="editor-toolbar-right">     
                        <button class="btn btn-sm btn-outline-info" onclick="downloadCurrentFile('${tab.id}')"
                                data-translate-tooltip="download">
                            <i class="fas fa-download"></i>
                        </button>

                        <button class="btn btn-sm btn-outline-warning" onclick="showKeyboardShortcuts()"
                                data-translate-tooltip="keyboard_shortcuts">
                            <i class="fas fa-keyboard"></i>
                        </button>

                        <button class="btn btn-sm btn-success" onclick="saveEditorContent('${tab.id}')">
                            <i class="fas fa-save"></i>
                            <span data-translate="save">Save</span>
                        </button>

                        <button class="btn btn-sm btn-dark-red" onclick="closeEditorTab('${tab.id}')">
                            <i class="fa fa-reply-all"></i>
                            <span data-translate="close">Close</span>
                        </button>
                    </div>
                </div>
                
<div style="flex: 1; overflow: hidden; position: relative;">
                    ${editorMode === 'simple' ? `
                        <div class="simple-editor-container" style="height: calc(100vh - 240px); overflow: auto; border: 1px solid var(--border-color); border-radius: 4px;">
                            <textarea id="${tab.id}-simple-editor" 
                                      class="simple-editor"
                                      placeholder="${tab.loading ? (translations['loading'] || 'Loading...') : (translations['start_editing'] || 'Start editing')}"
                                      oninput="markEditorAsModified('${tab.id}')"
                                      spellcheck="false"
                                      ${tab.loading ? 'readonly' : ''}
                                      wrap="off"
                                      style="width: 100%; min-height: 100%; border: none; padding: 12px; font-family: monospace; font-size: 18px; line-height: 1.5; resize: none; background: var(--bg-container); color: var(--text-primary);">${escapeHtml(tab.content)}</textarea>
                        </div>
                    ` : `
                        <div id="${tab.id}-monaco-container" class="monaco-editor-container">
                            <div class="editor-loading">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                <span data-translate="loading_advanced_editor">Loading advanced editor...</span>
                            </div>
                        </div>
                    `}
                </div>
                
                <div class="editor-status-bar">
                    <div class="editor-position-info">
                        <span id="${tab.id}-position-info">
                            <span data-translate="line">Line</span>: 1,
                            <span data-translate="column">Column</span>: 1
                        </span>
                        <span id="${tab.id}-selected-text-info"></span>
                        <span id="${tab.id}-char-count-info">
                            <span data-translate="char_count_label">Characters</span>: 0
                        </span>
                        <span>${formatFileSize((tab.content || '').length)}</span>
                        <span>UTF-8</span>
                    </div>
                    <div class="editor-encoding-info">
                        <span data-translate="${editorMode === 'advanced' ? 'advanced_editor' : 'simple_editor'}">
                            ${editorMode === 'advanced' ? 'Advanced Editor' : 'Simple Editor'}
                        </span>
                        ${editorMode === 'advanced' ? `<span style="color: #4CAF50;" data-translate="syntax_completion_enabled">Syntax completion enabled</span>` : ''}
                    </div>
                </div>
            `;
            
            contentArea.appendChild(editorContainer);
            
            if (editorMode === 'advanced') {
                setTimeout(() => {
                    initMonacoEditor(tab.id);
                }, 100);
            } else {
                const simpleEditor = document.getElementById(`${tab.id}-simple-editor`);
                if (simpleEditor) {
                    simpleEditor.value = tab.content || '';
                    
                    setTimeout(() => {
                        simpleEditor.style.height = 'auto';
                        simpleEditor.style.height = (simpleEditor.scrollHeight + 10) + 'px';
                        
                        setupSimpleEditorEvents(tab.id);
                        updateCharCount(tab.id);
                        
                        function updateCursorAndCount() {
                            const cursorPos = simpleEditor.selectionStart;
                            const text = simpleEditor.value.substring(0, cursorPos);
                            const lines = text.split('\n');
                            const line = lines.length;
                            const column = lines[lines.length - 1].length + 1;
                            
                            const positionElement = document.getElementById(`${tab.id}-position-info`);
                            if (positionElement) {
                                positionElement.textContent = `${translations['line_label'] || 'Line'}: ${line}, ${translations['column_label'] || 'Column'}: ${column}`;
                            }
                            const selectedText = simpleEditor.value.substring(
                                simpleEditor.selectionStart,
                                simpleEditor.selectionEnd
                            );
                            const selectedElement = document.getElementById(`${tab.id}-selected-text-info`);
                            if (selectedElement) {
                                if (selectedText && selectedText.length > 0) {
                                    selectedElement.textContent = `${translations['selected'] || 'Selected'}: ${selectedText.length} ${translations['characters'] || 'characters'}`;
                                } else {
                                    selectedElement.textContent = '';
                                }
                            }
                            
                            updateCharCount(tab.id);
                        }
                        
                        const events = ['input', 'keyup', 'click', 'mouseup', 'select'];
                        events.forEach(eventName => {
                            simpleEditor.addEventListener(eventName, updateCursorAndCount);
                        });
                        
                        setTimeout(updateCursorAndCount, 50);
                        
                        simpleEditor.addEventListener('input', function() {
                            this.style.height = 'auto';
                            this.style.height = this.scrollHeight + 'px';
                            markEditorAsModified(tab.id);
                        });
                    }, 100);
                }
            }

            if (editorMode === 'advanced') {
                setTimeout(() => {
                    const language = detectLanguage(tab.name);
                    const languageSelect = document.querySelector(`#${tab.id}-panel-container .editor-language-select`);
                    if (languageSelect) {
                        languageSelect.value = language;
                    }
                    
                    const savedTheme = localStorage.getItem('editorTheme') || 'vs-dark';
                    const themeSelect = document.querySelector(`#${tab.id}-panel-container .editor-theme-select`);
                    if (themeSelect) {
                        themeSelect.value = savedTheme;
                    }
                }, 200);
            }
        }
    }
    updateLanguage(currentLang);
}

function changeEditorFontSize(tabId, fontSize) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab) return;

    localStorage.setItem('editorFontSize', fontSize);

    if (tab.monacoEditorInstance) {
        tab.monacoEditorInstance.updateOptions({
            fontSize: parseInt(fontSize)
        });
    }

    const fontSizeSelect = document.querySelector(`#${tabId}-panel-container .editor-fontsize-select`);
    if (fontSizeSelect) {
        fontSizeSelect.value = fontSize;
    }

    logAndSpeak(`${translations['font_size_set_to'] || 'Font size set to'}: ${fontSize}px`,'info');
}

function getFileIcon(filename, ext, isDir) {
    if (isDir) {
        return '<i class="fas fa-folder fa-2x" style="color: #FFA726;"></i>';
    }
    
    const lowerExt = ext.toLowerCase();
    const lowerName = filename.toLowerCase();

    if (['ipk', 'apk', 'run'].includes(lowerExt)) {
        return '<i class="fas fa-box-open fa-2x" style="color: #FF9800;"></i>';
    }
    
    if (['apk'].includes(lowerExt)) {
        return '<i class="fab fa-android fa-2x" style="color: #3DDC84;"></i>';
    }
    
    if (['img', 'trx', 'chk', 'factory', 'sysupgrade'].includes(lowerExt)) {
        return '<i class="fas fa-microchip fa-2x" style="color: #FF5722;"></i>';
    }
    
    if (['ko'].includes(lowerExt)) {
        return '<i class="fas fa-microchip fa-2x" style="color: #9C27B0;"></i>';
    }
    
    if (['service', 'timer'].includes(lowerExt)) {
        return '<i class="fas fa-cogs fa-2x" style="color: #607D8B;"></i>';
    }
    
    if (['rc', 'init', 'ash', 'dash'].includes(lowerExt)) {
        return '<i class="fas fa-terminal fa-2x" style="color: #4CAF50;"></i>';
    }
    
    if (['pem', 'crt', 'key', 'csr', 'pfx', 'der'].includes(lowerExt)) {
        return '<i class="fas fa-lock fa-2x" style="color: #FF9800;"></i>';
    }
    
    if (['list'].includes(lowerExt)) {
        return '<i class="fas fa-list-alt fa-2x" style="color: #2196F3;"></i>';
    }
    
    if (['dtb', 'dts'].includes(lowerExt)) {
        return '<i class="fas fa-microchip fa-2x" style="color: #673AB7;"></i>';
    }
    
    if (['uci', 'config'].includes(lowerExt)) {
        return '<i class="fas fa-cogs fa-2x" style="color: #FF5722;"></i>';
    }
    
    if (['network', 'wireless', 'firewall', 'dhcp', 'system'].includes(lowerExt)) {
        return '<i class="fas fa-cogs fa-2x" style="color: #2196F3;"></i>';
    }
    
    if (['qcow2', 'vmdk', 'vdi'].includes(lowerExt)) {
        return '<i class="fas fa-hdd fa-2x" style="color: #795548;"></i>';
    }
    
    if (['pcap', 'cap'].includes(lowerExt)) {
        return '<i class="fas fa-network-wired fa-2x" style="color: #2196F3;"></i>';
    }
    
    if (['ovpn', 'wg'].includes(lowerExt)) {
        return '<i class="fas fa-shield-alt fa-2x" style="color: #4CAF50;"></i>';
    }

    if (['js', 'jsx', 'mjs', 'cjs'].includes(lowerExt)) return '<i class="fab fa-js-square fa-2x" style="color: #FFD600;"></i>';
    if (['ts', 'tsx'].includes(lowerExt)) return '<i class="fas fa-code fa-2x" style="color: #1976D2;"></i>';
    if (['vue'].includes(lowerExt)) return '<i class="fab fa-vuejs fa-2x" style="color: #4FC08D;"></i>';
    if (['php', 'php3', 'php4', 'php5', 'php7'].includes(lowerExt)) return '<i class="fab fa-php fa-2x" style="color: #777BB4;"></i>';
    if (['py', 'pyw', 'pyc', 'pyo'].includes(lowerExt)) return '<i class="fab fa-python fa-2x" style="color: #3776AB;"></i>';
    if (['java', 'class', 'jar'].includes(lowerExt)) return '<i class="fab fa-java fa-2x" style="color: #007396;"></i>';
    if (['c', 'h'].includes(lowerExt)) return '<i class="fas fa-file-code fa-2x" style="color: #A8B9CC;"></i>';
    if (['cpp', 'cc', 'cxx', 'hpp', 'hxx'].includes(lowerExt)) return '<i class="fas fa-file-code fa-2x" style="color: #00599C;"></i>';
    if (['cs', 'csx'].includes(lowerExt)) return '<i class="fas fa-file-code fa-2x" style="color: #239120;"></i>';
    if (['go'].includes(lowerExt)) return '<i class="fas fa-file-code fa-2x" style="color: #00ADD8;"></i>';
    if (['rs'].includes(lowerExt)) return '<i class="fas fa-file-code fa-2x" style="color: #DEA584;"></i>';
    if (['rb', 'erb'].includes(lowerExt)) return '<i class="fas fa-gem fa-2x" style="color: #CC342D;"></i>';
    if (['swift'].includes(lowerExt)) return '<i class="fab fa-swift fa-2x" style="color: #FA7343;"></i>';
    if (['kt', 'kts'].includes(lowerExt)) return '<i class="fas fa-file-code fa-2x" style="color: #7F52FF;"></i>';
    if (['scala', 'sc'].includes(lowerExt)) return '<i class="fas fa-file-code fa-2x" style="color: #DC322F;"></i>';
    if (['dart'].includes(lowerExt)) return '<i class="fab fa-dart fa-2x" style="color: #0175C2;"></i>';
    if (['r', 'rmd'].includes(lowerExt)) return '<i class="fas fa-file-code fa-2x" style="color: #276DC3;"></i>';
    if (['html', 'htm', 'xhtml'].includes(lowerExt)) return '<i class="fab fa-html5 fa-2x" style="color: #E34F26;"></i>';
    if (['css', 'scss', 'sass', 'less'].includes(lowerExt)) return '<i class="fab fa-css3-alt fa-2x" style="color: #1572B6;"></i>';
    if (['json', 'json5', 'jsonc'].includes(lowerExt)) return '<i class="fas fa-file-code fa-2x" style="color: #F7DF1E;"></i>';
    if (['xml', 'xsl', 'xslt', 'xsd'].includes(lowerExt)) return '<i class="fas fa-file-code fa-2x" style="color: #005A9C;"></i>';
    if (['sql', 'mysql', 'pgsql', 'psql', 'plsql', 'ddl', 'dml'].includes(lowerExt)) return '<i class="fas fa-database fa-2x" style="color: #00758F;"></i>';
    
    if (['pdf'].includes(lowerExt)) return '<i class="fas fa-file-pdf fa-2x" style="color: #F44336;"></i>';
    if (['doc', 'docx', 'odt', 'rtf', 'pages'].includes(lowerExt)) return '<i class="fas fa-file-word fa-2x" style="color: #2196F3;"></i>';
    if (['xls', 'xlsx', 'ods', 'numbers'].includes(lowerExt)) return '<i class="fas fa-file-excel fa-2x" style="color: #4CAF50;"></i>';
    if (['ppt', 'pptx', 'odp', 'key'].includes(lowerExt)) return '<i class="fas fa-file-powerpoint fa-2x" style="color: #FF9800;"></i>';
    if (['txt', 'log', 'conf', 'ini', 'cfg', 'properties', 'env', 'gitignore', 'editorconfig', 'dockerfile', 'makefile'].includes(lowerExt)) return '<i class="fas fa-file-alt fa-2x" style="color: #757575;"></i>';
    if (['md', 'markdown', 'mdx'].includes(lowerExt)) return '<i class="fab fa-markdown fa-2x" style="color: #000000;"></i>';
    
    if (['zip'].includes(lowerExt)) 
        return '<i class="fas fa-file-zipper fa-2x" style="color: #FF9800;"></i>';
    if (['tar'].includes(lowerExt)) 
        return '<i class="fas fa-file-archive fa-2x" style="color: #795548;"></i>';
    if (['gz'].includes(lowerExt)) 
        return '<i class="fas fa-file-archive fa-2x" style="color: #9C27B0;"></i>';
    if (['bz2'].includes(lowerExt)) 
        return '<i class="fas fa-file-archive fa-2x" style="color: #4CAF50;"></i>';
    if (['xz'].includes(lowerExt)) 
        return '<i class="fas fa-file-archive fa-2x" style="color: #00BCD4;"></i>';
    if (['7z'].includes(lowerExt)) 
        return '<i class="fas fa-file-archive fa-2x" style="color: #E91E63;"></i>';
    if (['rar'].includes(lowerExt)) 
        return '<i class="fas fa-file-archive fa-2x" style="color: #F44336;"></i>';
    if (['tgz', 'tbz2'].includes(lowerExt)) 
        return '<i class="fas fa-file-archive fa-2x" style="color: #FF5722;"></i>';
    if (['lz', 'lzma'].includes(lowerExt)) 
        return '<i class="fas fa-file-archive fa-2x" style="color: #673AB7;"></i>';
    if (['cab'].includes(lowerExt)) 
        return '<i class="fas fa-file-archive fa-2x" style="color: #3F51B5;"></i>';
    if (['iso'].includes(lowerExt)) 
        return '<i class="fas fa-compact-disc fa-2x" style="color: #607D8B;"></i>';
    if (['apk'].includes(lowerExt)) 
        return '<i class="fab fa-android fa-2x" style="color: #3DDC84;"></i>';
    if (['deb'].includes(lowerExt)) 
        return '<i class="fab fa-debian fa-2x" style="color: #A81D33;"></i>';
    if (['rpm'].includes(lowerExt)) 
        return '<i class="fab fa-redhat fa-2x" style="color: #EE0000;"></i>';
    if (['dmg'].includes(lowerExt)) 
        return '<i class="fas fa-apple-alt fa-2x" style="color: #999999;"></i>';  

    if (['jpg', 'jpeg'].includes(lowerExt)) 
        return '<i class="fas fa-image fa-2x" style="color: #4CAF50;"></i>';
    if (['png'].includes(lowerExt)) 
        return '<i class="fas fa-image fa-2x" style="color: #2196F3;"></i>';
    if (['gif'].includes(lowerExt)) 
        return '<i class="fas fa-image fa-2x" style="color: #FF9800;"></i>';
    if (['bmp'].includes(lowerExt)) 
        return '<i class="fas fa-image fa-2x" style="color: #9C27B0;"></i>';
    if (['webp'].includes(lowerExt)) 
        return '<i class="fas fa-image fa-2x" style="color: #00BCD4;"></i>';
    if (['svg'].includes(lowerExt)) 
        return '<i class="fas fa-draw-polygon fa-2x" style="color: #FFC107;"></i>';
    if (['ico'].includes(lowerExt)) 
        return '<i class="fas fa-circle fa-2x" style="color: #607D8B;"></i>';

    if (['tiff', 'tif', 'heic', 'heif', 'raw', 'cr2', 'nef', 'psd', 'ai', 'eps'].includes(lowerExt)) 
        return '<i class="fas fa-file-image fa-2x" style="color: #4CAF50;"></i>';

    if (['mp3'].includes(lowerExt)) 
        return '<i class="fas fa-music fa-2x" style="color: #FF6B6B;"></i>';
    if (['wav'].includes(lowerExt)) 
        return '<i class="fas fa-wave-square fa-2x" style="color: #45B7D1;"></i>';
    if (['ogg'].includes(lowerExt)) 
        return '<i class="fas fa-circle fa-2x" style="color: #96CEB4;"></i>';
    if (['flac'].includes(lowerExt)) 
        return '<i class="fas fa-compact-disc fa-2x" style="color: #4ECDC4;"></i>';
    if (['m4a', 'aac'].includes(lowerExt)) 
        return '<i class="fas fa-headphones fa-2x" style="color: #FFA07A;"></i>';
    if (['mp2', 'ac3', 'dts'].includes(lowerExt)) 
        return '<i class="fas fa-music fa-2x" style="color: #FF6B6B;"></i>';

    if (['hevc', 'h265'].includes(lowerExt)) 
        return '<i class="fas fa-film fa-2x" style="color: #2196F3;"></i>';

    if (['wma', 'opus', 'mid', 'midi'].includes(lowerExt)) 
        return '<i class="fas fa-file-audio fa-2x" style="color: #9C27B0;"></i>';

    if (['mp4'].includes(lowerExt)) 
        return '<i class="fas fa-film fa-2x" style="color: #2196F3;"></i>';
    if (['avi'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #4CAF50;"></i>';
    if (['mkv'].includes(lowerExt)) 
        return '<i class="fas fa-film fa-2x" style="color: #9C27B0;"></i>';
    if (['mov'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #FF9800;"></i>';
    if (['wmv'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #607D8B;"></i>';
    if (['flv'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #F44336;"></i>';
    if (['webm'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #00BCD4;"></i>';
    if (['m4v'].includes(lowerExt)) 
        return '<i class="fas fa-film fa-2x" style="color: #FF6B6B;"></i>';
    if (['mpg', 'mpeg'].includes(lowerExt)) 
        return '<i class="fas fa-film fa-2x" style="color: #795548;"></i>';
    if (['ts', 'm2ts'].includes(lowerExt)) 
        return '<i class="fas fa-film fa-2x" style="color: #673AB7;"></i>';
    if (['rmvb'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #E91E63;"></i>';
    if (['3gp'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #3F51B5;"></i>';
    if (['vob'].includes(lowerExt)) 
        return '<i class="fas fa-compact-disc fa-2x" style="color: #FFC107;"></i>';
    if (['ogv'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #4ECDC4;"></i>';
    if (['mts'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #FF5722;"></i>';

    if (['mp4'].includes(lowerExt)) 
        return '<i class="fas fa-film fa-2x" style="color: #2196F3;"></i>';
    if (['avi'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #4CAF50;"></i>';
    if (['mkv'].includes(lowerExt)) 
        return '<i class="fas fa-film fa-2x" style="color: #9C27B0;"></i>';
    if (['mov'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #FF9800;"></i>';
    if (['wmv'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #607D8B;"></i>';
    if (['flv'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #F44336;"></i>';
    if (['webm'].includes(lowerExt)) 
        return '<i class="fas fa-video fa-2x" style="color: #00BCD4;"></i>';
    if (['m4v'].includes(lowerExt)) 
        return '<i class="fas fa-film fa-2x" style="color: #FF6B6B;"></i>';

    if (['mpg', 'mpeg', 'ts', 'm2ts', 'rmvb', '3gp', 'vob', 'ogv', 'mts'].includes(lowerExt)) 
        return '<i class="fas fa-file-video fa-2x" style="color: #2196F3;"></i>'; 
    
    if (['exe', 'msi', 'app', 'bat', 'cmd', 'sh', 'bash', 'zsh', 'fish', 'ps1', 'psm1', 'com'].includes(lowerExt)) return '<i class="fas fa-cog fa-2x" style="color: #795548;"></i>';
    if (['jar'].includes(lowerExt)) return '<i class="fab fa-java fa-2x" style="color: #007396;"></i>';
    
    if (['ttf', 'otf', 'woff', 'woff2', 'eot', 'sfnt'].includes(lowerExt)) return '<i class="fas fa-font fa-2x" style="color: #9C27B0;"></i>';
    
    if (['epub', 'mobi', 'azw', 'azw3', 'fb2', 'djvu'].includes(lowerExt)) return '<i class="fas fa-book fa-2x" style="color: #795548;"></i>';
    
    if (['yml', 'yaml', 'toml', 'xml', 'json', 'ini', 'cfg', 'conf', 'env'].includes(lowerExt)) return '<i class="fas fa-cogs fa-2x" style="color: #607D8B;"></i>';
    
    if (['db', 'sqlite', 'sqlite3', 'mdb', 'accdb', 'frm', 'myd', 'myi'].includes(lowerExt)) return '<i class="fas fa-database fa-2x" style="color: #00758F;"></i>';
    
    if (['vdi', 'vmdk', 'vhd', 'vhdx', 'qcow2', 'img', 'iso', 'bin', 'nrg'].includes(lowerExt)) return '<i class="fas fa-hdd fa-2x" style="color: #757575;"></i>';
    
    if (lowerName === 'dockerfile' || lowerName === 'docker-compose.yml' || lowerName === 'docker-compose.yaml') {
        return '<i class="fab fa-docker fa-2x" style="color: #2496ED;"></i>';
    }
    if (lowerName === 'readme' || lowerName === 'readme.md' || lowerName === 'readme.txt') {
        return '<i class="fas fa-book-open fa-2x" style="color: #2196F3;"></i>';
    }
    if (lowerName === 'license' || lowerName === 'license.txt' || lowerName === 'license.md') {
        return '<i class="fas fa-balance-scale fa-2x" style="color: #FF9800;"></i>';
    }
    if (lowerName === '.gitignore' || lowerName === '.gitattributes' || lowerName === '.gitmodules') {
        return '<i class="fab fa-git-alt fa-2x" style="color: #F05032;"></i>';
    }
    
    return '<i class="fas fa-file fa-2x" style="color: #757575;"></i>';
}

function toggleEditorMode(tabId) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab) return;
    
    const currentMode = tab.editorMode || currentEditorMode;
    
    const newMode = currentMode === 'simple' ? 'advanced' : 'simple';
    
    switchEditorMode(newMode, tabId);
}

function toggleComment(tabId) {
    if (!tabId && activeEditorTab) {
        tabId = activeEditorTab;
    }
    
    if (!tabId) return;
    
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab || tab.editorMode !== 'advanced' || !tab.monacoEditorInstance) return;
    
    tab.monacoEditorInstance.getAction('editor.action.commentLine').run();
}

function updateCharCount(tabId) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab) return;

    let charCount = 0;

    if (tab.monacoEditorInstance) {
        const model = tab.monacoEditorInstance.getModel();
        if (model) {
            charCount = model.getValue().length;
        }
    } else {
        const simpleEditor = document.getElementById(`${tabId}-simple-editor`);
        if (simpleEditor) {
            charCount = simpleEditor.value.length;
        }
    }

    const charCountElement = document.getElementById(`${tabId}-char-count-info`);
    if (charCountElement) {
        const label = translations['char_count_label'] || 'Characters';
        charCountElement.textContent = `${label}: ${charCount}`;
    }
}

function updateEditorTabsSwitcher() {
    const switcher = document.getElementById('editorTabsSwitcher');
    const tabsList = document.getElementById('editorTabsList');
    
    if (!switcher || !tabsList) return;
    
    if (editorTabs.length > 0) {
        switcher.style.display = 'block';
        tabsList.innerHTML = '';
        
        editorTabs.forEach(tab => {
            const tabElement = document.createElement('div');
            tabElement.className = `editor-tab-switch ${tab.id === activeEditorTab ? 'active' : ''}`;
            tabElement.title = tab.path;
            tabElement.onclick = (e) => {
                e.stopPropagation();
                switchToEditorTab(tab.id);
            };
            
            let tabName = escapeHtml(tab.name);
            if (tab.modified) {
                tabName = `* ${tabName}`;
            }
            
            tabElement.innerHTML = `
                <span>${tabName}</span>
                <span class="close-tab-btn" onclick="closeEditorTab('${tab.id}', event)" style="margin-left: 5px; opacity: 0.7;">
                    <i class="fas fa-times" style="font-size: 10px;"></i>
                </span>
            `;
            
            tabsList.appendChild(tabElement);
        });
    } else {
        switcher.style.display = 'none';
    }
}

function updateEditorTabsUI() {
    const tabsNav = document.getElementById('editorTabsNav');
    if (!tabsNav) return;
    
    tabsNav.innerHTML = '';
    
    editorTabs.forEach(tab => {
        const tabElement = document.createElement('div');
        tabElement.className = 'editor-tab';
        tabElement.style.cssText = `
            padding: 8px 15px;
            background: ${tab.id === activeEditorTab ? 'var(--accent-tertiary)' : 'var(--card-bg)'};
            color: ${tab.id === activeEditorTab ? 'white' : 'var(--text-primary)'};
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            font-size: 14px;
        `;
        
        tabElement.onclick = () => switchToEditorTab(tab.id);
        
        let tabName = escapeHtml(tab.name);
        if (tab.modified) {
            tabName = `* ${tabName}`;
        }
        
        tabElement.innerHTML = `
            <span>${tabName}</span>
            <span style="margin-left: 8px;" onclick="closeEditorTab('${tab.id}', event)">
                <i class="fas fa-times"></i>
            </span>
        `;
        
        tabsNav.appendChild(tabElement);
    });
    
    editorTabs.forEach(tab => {
        if (!document.getElementById(`${tab.id}-container`)) {
            createEditorContent(tab.id);
        }
    });
}

function closeEditorTab(tabId, event = null) {
    if (event) event.stopPropagation();

    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab) return;

    const close = () => {
        if (tab.monacoEditorInstance) {
            tab.monacoEditorInstance.dispose();
            tab.monacoEditorInstance = null;
        }

        if (tab._autoSaveTimer) {
            clearTimeout(tab._autoSaveTimer);
        }

        editorTabs = editorTabs.filter(t => t.id !== tabId);

        if (activeEditorTab === tabId) {
            activeEditorTab = editorTabs.length > 0
                ? editorTabs[editorTabs.length - 1].id
                : null;
        }

        updateEditorUI();
        updateEditorTabsSwitcher();
    };

    if (tab.modified) {
        const confirmMessage =
            (translations['confirm_close_unsaved_file']
                || 'The file has unsaved changes. Are you sure you want to close it?')
            .replace('{filename}', tab.name);

        showConfirmation(confirmMessage, close);
        return;
    }

    close();
}

function closeAllEditorTabs() {
    if (editorTabs.length === 0) return;

    const unsavedTabs = editorTabs.filter(tab => tab.modified);
    if (unsavedTabs.length > 0) {
        const fileNames = unsavedTabs.map(tab => tab.name).join(', ');

        const confirmMessage =
            (translations['confirm_close_all_unsaved_files']
                || 'The following files have unsaved changes: {filenames}\nAre you sure you want to close all tabs?')
                .replace('{filenames}', fileNames);

        if (!confirm(confirmMessage)) {
            return;
        }
    }

    editorTabs.forEach(tab => {
        if (tab.monacoEditorInstance) {
            tab.monacoEditorInstance.dispose();
        }
        if (tab._autoSaveTimer) {
            clearTimeout(tab._autoSaveTimer);
        }
    });

    editorTabs = [];
    activeEditorTab = null;
    updateEditorUI();

    if (currentView === 'editor') {
        toggleView('files');
    }
}

function editFile(path) {
    const fileName = path.split('/').pop();
    const ext = fileName.toLowerCase().split('.').pop();
    
    const archiveExts = ['zip', 'tar', 'gz', 'bz2', '7z', 'rar', 'tgz', 'tbz2'];
    
    if (archiveExts.includes(ext)) {
        selectedFiles.clear();
        selectedFiles.add(path);
        updateFileSelection();
        
        showExtractDialog();
        return;
    }
    
    openEditor(path);
    toggleView('editor');
}

function downloadFile(path) {
    window.open(`?preview=1&path=${encodeURIComponent(path)}`, '_blank');
}

function handleFileSelect(event) {
    const files = Array.from(event.target.files);
    
    uploadFilesList = [];
    const folderSet = new Set();
    
    files.forEach(file => {
        if (file.webkitRelativePath) {
            const folderName = file.webkitRelativePath.split('/')[0];
            folderSet.add(folderName);
        }
    });
    
    files.forEach(file => {
        if (file.webkitRelativePath) {
            const folderName = file.webkitRelativePath.split('/')[0];
            file._isFolderFile = true;
            file._folderName = folderName;
            file._fullPath = file.webkitRelativePath;
            uploadFilesList.push(file);
        } else {
            uploadFilesList.push(file);
        }
    });
    
    folderSet.forEach(folderName => {
        const hasFolderFiles = uploadFilesList.some(f => f._folderName === folderName);
        if (hasFolderFiles) {
            const folderObj = {
                _isFolder: true,
                _folderName: folderName,
                _files: uploadFilesList.filter(f => f._folderName === folderName),
                size: uploadFilesList
                    .filter(f => f._folderName === folderName)
                    .reduce((sum, f) => sum + f.size, 0)
            };
            uploadFilesList.unshift(folderObj);
        }
    });
    
    updateUploadFileList();
    event.target.value = '';
}

function initDragAndDrop() {
    const dropArea = document.querySelector('.upload-drop-area');
    const fileInput = document.getElementById('fileUploadInput');

    if (!dropArea || !fileInput) return;

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });

    dropArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            uploadFilesList = uploadFilesList.concat(Array.from(files));
            updateUploadFileList();
        }
    }, false);
}

function removeUploadFile(index) {
    uploadFilesList.splice(index, 1);
    updateUploadFileList();
}

function updateUploadFileList() {
    const fileList = document.getElementById('fileList');
    const fileListCard = document.getElementById('fileListCard');
    
    fileList.innerHTML = '';
    
    if (uploadFilesList.length === 0) {
        if (fileListCard) {
            fileListCard.style.display = 'none';
        }
        return;
    }
    
    if (fileListCard) {
        fileListCard.style.display = 'block';
    }
    
    let totalSize = 0;
    let fileCount = 0;
    let folderCount = 0;
    
    uploadFilesList.forEach(item => {
        if (item._isFolder) {
            folderCount++;
            totalSize += item.size;
            fileCount += item._files.length;
        } else if (!item._isFolderFile) {
            fileCount++;
            totalSize += item.size;
        }
    });
    
    const totalItems = uploadFilesList.length;
    
    const statsCard = document.createElement('div');
    statsCard.className = 'card bg-primary bg-opacity-10 border-primary mb-3';
    statsCard.innerHTML = `
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-3">
                    <div class="stat-value text-primary fs-4 fw-bold">${totalItems}</div>
                    <div class="stat-label small text-muted" data-translate="items">Items</div>
                </div>
                <div class="col-3">
                    <div class="stat-value text-success fs-4 fw-bold">${fileCount}</div>
                    <div class="stat-label small text-muted" data-translate="total_files">Total Files</div>
                </div>
                <div class="col-3">
                    <div class="stat-value text-info fs-4 fw-bold">${folderCount}</div>
                    <div class="stat-label small text-muted" data-translate="folders">Folders</div>
                </div>
                <div class="col-3">
                    <div class="stat-value text-warning fs-4 fw-bold">${formatFileSize(totalSize)}</div>
                    <div class="stat-label small text-muted" data-translate="total_size">Total Size</div>
                </div>
            </div>
        </div>
    `;
    fileList.appendChild(statsCard);
    
    const scrollContainer = document.createElement('div');
    scrollContainer.className = 'upload-scroll-container';
    scrollContainer.style.cssText = `
        max-height: 400px;
        overflow-y: auto;
        padding-right: 5px;
    `;
    
    uploadFilesList.forEach((item, index) => {
        if (item._isFolder) {
            const folderCard = document.createElement('div');
            folderCard.className = 'card bg-warning bg-opacity-10 border-warning mb-2';
            folderCard.innerHTML = `
                <div class="card-body p-2">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-folder fa-2x" style="color: #FFA726;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold">${escapeHtml(item._folderName)}</span>
                                    <span class="badge bg-info ms-2">${item._files.length} files</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="text-warning fw-bold me-3 align-self-center">${formatFileSize(item.size)}</span>
                                    <button class="btn btn-sm btn-link text-danger p-0" 
                                            onclick="removeFolder('${item._folderName}')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="fas fa-layer-group me-1"></i>
                                Folder will be uploaded with structure
                            </div>
                        </div>
                    </div>
                </div>
            `;
            scrollContainer.appendChild(folderCard);
        } else if (!item._isFolderFile) {
            const fileCard = document.createElement('div');
            fileCard.className = 'card bg-dark bg-opacity-25 border-secondary mb-2';
            fileCard.innerHTML = `
                <div class="card-body p-2">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-file fa-lg" style="color: #4CAF50;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div style="word-break: break-word; padding-right: 10px;"  
                                     title="${escapeHtml(item.name)}">
                                    ${escapeHtml(item.name)}
                                </div>
                                <button class="btn btn-sm btn-danger" onclick="removeUploadFile(${index})"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="small text-muted mt-1">
                                <span class="badge bg-secondary me-2">${item.name.split('.').pop().toUpperCase()}</span>
                                <span class="text-success">${formatFileSize(item.size)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            scrollContainer.appendChild(fileCard);
        }
    });
    
    fileList.appendChild(scrollContainer);
    updateLanguage(currentLang);
}

function removeFolder(folderName) {
    uploadFilesList = uploadFilesList.filter(item => {
        if (item._isFolder && item._folderName === folderName) {
            return false;
        }
        if (item._folderName === folderName) {
            return false;
        }
        return true;
    });
    
    updateUploadFileList();
}

function previewSanitizedFilename(filename) {
    let cleanName = filename;
    cleanName = cleanName.split('/').pop();
    cleanName = cleanName.replace(/ /g, '_');
    cleanName = cleanName.replace(/[\/:*?"<>|\s]/g, '_');
    cleanName = cleanName.replace(/_+/g, '_');
    cleanName = cleanName.replace(/^[._-]+|[._-]+$/g, '');
    
    if (!cleanName) {
        cleanName = 'upload_' + Date.now() + 
                   (filename.includes('.') ? filename.substring(filename.lastIndexOf('.')) : '');
    }
    
    return cleanName;
}

async function startUpload() {
    if (uploadFilesList.length === 0) {
        const warningMessage = translations['upload_select_files_warning'] || 'Please select files to upload';
        logAndSpeak(warningMessage, 'warning');
        return;
    }

    const progressArea = document.getElementById('uploadProgressArea');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressPercent = document.getElementById('uploadProgressPercent');
    const uploadFileInfo = document.getElementById('uploadFileInfo');
    const chunkProgressDetail = document.getElementById('chunkProgressDetail');
    
    progressArea.style.display = 'block';
    progressBar.style.width = '0%';
    progressBar.textContent = '0%';
    progressPercent.textContent = '0%';
    if (chunkProgressDetail) chunkProgressDetail.textContent = '';
    
    const chunkSize = 20 * 1024 * 1024;
    
    let totalFiles = 0;
    let totalSize = 0;
    let uploadedSize = 0;
    const files = [];
    
    uploadFilesList.forEach(file => {
        if (file._isFolderFile) {
            files.push({
                file: file,
                path: file._fullPath,
                size: file.size
            });
            totalFiles++;
            totalSize += file.size;
        } else if (!file._isFolder) {
            files.push({
                file: file,
                path: file.name,
                size: file.size
            });
            totalFiles++;
            totalSize += file.size;
        }
    });

    try {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const chunks = Math.ceil(file.size / chunkSize);
            
            uploadFileInfo.textContent = `Uploading ${file.path} (${formatFileSize(file.size)})`;
            
            for (let chunkIndex = 0; chunkIndex < chunks; chunkIndex++) {
                const start = chunkIndex * chunkSize;
                const end = Math.min(start + chunkSize, file.size);
                const chunk = file.file.slice(start, end);
                
                const formData = new FormData();
                formData.append('path', currentPath);
                formData.append('filePath', file.path);
                formData.append('chunk', chunk);
                formData.append('chunkIndex', chunkIndex);
                formData.append('totalChunks', chunks);
                formData.append('fileName', file.file.name);
                formData.append('totalSize', file.size);
                
                if (chunkProgressDetail) {
                    chunkProgressDetail.textContent = (translations['upload_chunk_progress'] || 'Chunk {current}/{total}')
                        .replace('{current}', chunkIndex + 1)
                        .replace('{total}', chunks);
                }
                
                const response = await fetch('?action=upload_chunk', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || `Failed to upload chunk ${chunkIndex + 1}`);
                }
                
                uploadedSize += (end - start);
                const percent = Math.round((uploadedSize / totalSize) * 100);
                progressBar.style.width = percent + '%';
                progressBar.textContent = percent + '%';
                progressPercent.textContent = percent + '%';
                
                const uploadedMB = (uploadedSize / (1024 * 1024)).toFixed(2);
                const totalMB = (totalSize / (1024 * 1024)).toFixed(2);
                uploadFileInfo.textContent = `${uploadedMB} MB / ${totalMB} MB - ${file.path}`;
            }
            
            const completeFormData = new FormData();
            completeFormData.append('path', currentPath);
            completeFormData.append('filePath', file.path);
            completeFormData.append('totalChunks', chunks);
            completeFormData.append('fileName', file.file.name);
            
            await fetch('?action=upload_complete', {
                method: 'POST',
                body: completeFormData
            });
        }
        
        const successMessage = `${translations['upload_success'] || 'Successfully uploaded'} ${totalFiles} ${totalFiles === 1 ? (translations['file'] || 'file') : (translations['files'] || 'files')}`;
        logAndSpeak(successMessage, 'success');
        
        progressBar.style.width = '100%';
        progressBar.textContent = '100%';
        progressPercent.textContent = '100%';
        if (chunkProgressDetail) chunkProgressDetail.textContent = translations['upload_complete'] || 'Complete!';
        
        setTimeout(() => {
            bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
            uploadFilesList = [];
            refreshFiles();
            updateSystemInfo();
            
            setTimeout(() => {
                progressArea.style.display = 'none';
            }, 1000);
        }, 500);
        
    } catch (error) {
        console.error('Upload error:', error);
        logAndSpeak(error.message || translations['uploadFailed'] || 'Upload failed', 'error');
        progressArea.style.display = 'none';
    }
}

function openDiffView(tabId) {
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab) return;
    
    let currentContent = '';
    if (tab.monacoEditorInstance) {
        currentContent = tab.monacoEditorInstance.getValue();
    } else {
        const simpleEditor = document.getElementById(`${tabId}-simple-editor`);
        if (simpleEditor) {
            currentContent = simpleEditor.value;
        }
    }
    
    createDiffEditorDialog(tab.name, currentContent, tabId);
}

function createDiffEditorDialog(filename, content, tabId) {
    const dialog = document.createElement('div');
    dialog.className = 'modal fade';
    dialog.id = 'diffEditorModal';
    dialog.setAttribute('tabindex', '-1');
    dialog.setAttribute('aria-hidden', 'true');
    dialog.setAttribute('aria-labelledby', 'diffEditorModalLabel');
    dialog.setAttribute('data-bs-backdrop', 'static');
    dialog.setAttribute('data-bs-keyboard', 'false');
    dialog.innerHTML = `
        <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
            <div class="modal-content" style="height: 80vh;">
                <div class="modal-header">
                    <h5 class="modal-title" id="diffEditorModalLabel">
                        <i class="fas fa-code-compare me-2"></i>
                        <span data-translate="diff_editor_title">Diff Editor</span> - ${escapeHtml(filename)}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0 d-flex flex-column">
                    <div class="diff-toolbar p-2 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary me-2" data-translate="diff_left_label">Left: Original</span>
                            <span class="badge bg-success me-2" data-translate="diff_right_label">Right: Diff</span>
                        </div>

                        <div class="btn-group">
                            <button class="btn btn-sm btn-orange me-1" onclick="resetRightEditor()">
                                <i class="fas fa-undo"></i>
                                <span data-translate="diff_reset_right">Reset Right</span>
                            </button>
                            <button class="btn btn-sm btn-purple me-1" onclick="copyRightToLeft()">
                                <i class="fas fa-arrow-left"></i>
                                <span data-translate="diff_apply_to_left">Apply to Left</span>
                            </button>
                            <button class="btn btn-sm btn-pink me-1" onclick="toggleFullscreen()" data-translate-tooltip="enter_fullscreen">
                                <i class="fas fa-expand"></i>
                            </button>
                            <button class="btn btn-sm btn-primary me-1" onclick="saveLeftEditor('${tabId}')">
                                <i class="fas fa-save"></i>
                                <span data-translate="save">Save</span>
                            </button>
                        </div>
                    </div>

                    <div id="diffEditorContainer" class="flex-grow-1"></div>

                    <div class="diff-help p-2 small text-muted">
                        <i class="fas fa-lightbulb me-1"></i>
                        <span data-translate="diff_help">Tip: When editing on the right, green = added, red = removed. Ctrl+V works on both sides.</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(dialog);
    updateLanguage(currentLang);
    const modal = new bootstrap.Modal(dialog);
    modal.show();

    setTimeout(() => {
        initMonacoDiffEditor(content, filename, tabId);
    }, 300);

    dialog.addEventListener('hidden.bs.modal', function () {
        if (document.fullscreenElement || 
            document.webkitFullscreenElement || 
            document.mozFullScreenElement || 
            document.msFullscreenElement) {
            exitFullscreen();
        }
        
        dialog.remove();
        if (window.diffEditor) {
            window.diffEditor.dispose();
            window.diffEditor = null;
        }
    });
}

function initMonacoDiffEditor(content, filename, tabId) {
    if (!window.monaco) {
        console.error('Monaco Editor not loaded');
        return;
    }
    
    const language = detectLanguage(filename);
    
    const currentTheme = localStorage.getItem('editorTheme') || 'vs-dark';
    
    const diffContainer = document.getElementById('diffEditorContainer');
    if (!diffContainer) return;
    
    window.diffEditor = monaco.editor.createDiffEditor(diffContainer, {
        theme: currentTheme,
        readOnly: false,
        automaticLayout: true,
        enableSplitViewResizing: true,
        renderSideBySide: true,
        originalEditable: true,
        renderIndicators: true,
        ignoreTrimWhitespace: false,
        renderValidationDecorations: 'on',
        diffAlgorithm: 'advanced',
        diffWordWrap: 'on',
        folding: true,
        minimap: { enabled: true },
        scrollbar: {
            vertical: 'auto',
            horizontal: 'auto'
        }
    });
    
    const originalModel = monaco.editor.createModel(content, language);
    const modifiedModel = monaco.editor.createModel(content, language);
    
    window.diffEditor.setModel({
        original: originalModel,
        modified: modifiedModel
    });
    
    const originalEditor = window.diffEditor.getOriginalEditor();
    const modifiedEditor = window.diffEditor.getModifiedEditor();
    
    originalEditor.updateOptions({
        readOnly: false,
        wordWrap: 'on',
        lineNumbers: 'on',
        suggestOnTriggerCharacters: true,
        formatOnPaste: true,
        formatOnType: true
    });
    
    modifiedEditor.updateOptions({
        readOnly: false,
        wordWrap: 'on',
        lineNumbers: 'on',
        suggestOnTriggerCharacters: true,
        formatOnPaste: true,
        formatOnType: true
    });
    
    window.currentDiffTabId = tabId;
    
    setTimeout(() => {
        modifiedEditor.focus();
    }, 100);
}

function resetRightEditor() {
    if (!window.diffEditor) return;
    
    const originalEditor = window.diffEditor.getOriginalEditor();
    const modifiedEditor = window.diffEditor.getModifiedEditor();
    
    const leftContent = originalEditor.getValue();
    modifiedEditor.setValue(leftContent);
    
    logAndSpeak(
        translations['diff_reset_right_success'] || 'Right content has been reset to match the left',
        'info'
    );
}

function copyRightToLeft() {
    if (!window.diffEditor) return;
    
    const originalEditor = window.diffEditor.getOriginalEditor();
    const modifiedEditor = window.diffEditor.getModifiedEditor();
    
    const rightContent = modifiedEditor.getValue();
    originalEditor.setValue(rightContent);
    
    logAndSpeak(
        translations['diff_apply_to_left_success'] || 'Right content has been applied to the left',
        'success'
    );
}

function saveLeftEditor(tabId) {
    if (!window.diffEditor) return;
    
    const tab = editorTabs.find(t => t.id === tabId);
    if (!tab) return;
    
    const originalEditor = window.diffEditor.getOriginalEditor();
    const content = originalEditor.getValue();
    
    if (tab.monacoEditorInstance) {
        tab.monacoEditorInstance.setValue(content);
        tab.content = content;
        tab.modified = (content !== tab.originalContent);
    } else {
        const simpleEditor = document.getElementById(`${tabId}-simple-editor`);
        if (simpleEditor) {
            simpleEditor.value = content;
            tab.content = content;
            tab.modified = (content !== tab.originalContent);
            
            simpleEditor.style.height = 'auto';
            simpleEditor.style.height = simpleEditor.scrollHeight + 'px';
        }
    }
    
    updateEditorTabsUI();
    
    const modal = bootstrap.Modal.getInstance(
        document.getElementById('diffEditorModal')
    );
    if (modal) {
        modal.hide();
    }
    
    saveEditorContent(tabId);
}

function formatFileSize(bytes) {
    if (bytes === 0) return "0 B";
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + units[i];
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function contextMenuEdit() {
    if (selectedFiles.size === 0) {
        logAndSpeak(translations['select_items_first'] || 'Please select items first', 'warning');
        return;
    }
    
    const path = Array.from(selectedFiles)[0];
    editFile(path);
    hideFileContextMenu();
}

function initEventListeners() {
    document.addEventListener('click', function(e) {
        const contextMenu = document.getElementById('fileContextMenu');
        const overlay = document.getElementById('contextMenuOverlay');

        if (contextMenu && contextMenu.style.display === 'block' &&
            !contextMenu.contains(e.target)) {
            hideFileContextMenu();
        }

        if (overlay && overlay.style.display === 'block' &&
            e.target === overlay) {
            hideFileContextMenu();
            hideFileInfo();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const menuItems = document.querySelectorAll('#fileContextMenu .menu-item');
        menuItems.forEach(item => {
            const onclickAttr = item.getAttribute('onclick');
            if (onclickAttr === 'showChmodDialog()') {
                item.removeAttribute('onclick');
                item.addEventListener('click', function(e) {
                    e.stopPropagation();
                    showChmodDialog();
                });
            }
        });

        const permMenuItem = document.querySelector('#fileContextMenu .menu-item[onclick="showChmodDialog()"]');
        if (permMenuItem) {
            permMenuItem.addEventListener('click', function(e) {
                e.stopPropagation();
                showChmodDialog();
            });
        }

        menuItems.forEach(item => {
            const onclickAttr = item.getAttribute('onclick');
            if (onclickAttr) {
                const funcName = onclickAttr.replace('onclick=', '').replace('()', '').trim();
                item.removeAttribute('onclick');
                item.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (funcName === 'contextMenuOpen()') contextMenuOpen();
                    else if (funcName === 'contextMenuPlay()') contextMenuPlay();
                    else if (funcName === 'contextMenuEdit()') contextMenuEdit();
                    else if (funcName === 'contextMenuDownload()') contextMenuDownload();
                    else if (funcName === 'showFileProperties()') showFileProperties();
                    else if (funcName === 'contextMenuDelete()') contextMenuDelete();
                    else if (funcName.includes('prepare')) {
                        eval(funcName + '()');
                    }
                });
            }
        });
    });
}

function getCurrentIP() {
    const url = window.location.href;
    const match = url.match(/https?:\/\/([^/:]+)/);
    if (match && match[1]) {
        return match[1];
    }
    return window.location.hostname;
}

function openTerminal() {
    const ip = getCurrentIP();
    const terminalUrl = `http://${ip}:7681/`;
    const terminalIframe = document.getElementById('terminalIframe');
    terminalIframe.src = terminalUrl;
    const modal = new bootstrap.Modal(document.getElementById('terminalModal'));
    modal.show();
    
    document.getElementById('terminalModal').addEventListener('hidden.bs.modal', function() {
        terminalIframe.src = '';
    });
    
    hideFileContextMenu();
}

async function showFileHashDialog() {
    if (selectedFiles.size !== 1) {
        logAndSpeak(translations['select_items_first'] || 'Please select one file', 'warning');
        return;
    }
    
    const path = Array.from(selectedFiles)[0];
    const fileItem = document.querySelector(`.file-item[data-path="${path}"]`);
    const isDir = fileItem?.getAttribute('data-is-dir') === 'true';
    
    if (isDir) {
        logAndSpeak(translations['cannot_hash_directory'] || 'Cannot calculate hash for directory', 'warning');
        return;
    }
    
    hideFileContextMenu();
    
    currentHashPath = path;
    
    document.getElementById('hashLoading').classList.remove('d-none');
    document.getElementById('hashContent').classList.add('d-none');
    document.getElementById('hashError').classList.add('d-none');
    
    const fileName = path.replace(/^\/+/, '').split('/').pop();
    document.getElementById('hashFileName').textContent = `- ${fileName}`;
    document.getElementById('hashFilePath').textContent = '/' + path.replace(/^\/+/, '');
    
    document.getElementById('hashMd5').textContent = '';
    document.getElementById('hashSha1').textContent = '';
    document.getElementById('hashSha256').value = '';
    document.getElementById('hashFileSize').textContent = '';
    document.getElementById('hashFileMtime').textContent = '';
    
    const modal = new bootstrap.Modal(document.getElementById('fileHashModal'));
    modal.show();
    
    try {
        const response = await fetch(`?action=file_hash&path=${encodeURIComponent(path)}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('hashLoading').classList.add('d-none');
            document.getElementById('hashContent').classList.remove('d-none');
            
            document.getElementById('hashMd5').textContent = data.md5;
            document.getElementById('hashSha1').textContent = data.sha1;
            document.getElementById('hashSha256').value = data.sha256;
            document.getElementById('hashFileSize').textContent = data.size_formatted;
            document.getElementById('hashFileMtime').textContent = data.modified;
        } else {
            document.getElementById('hashLoading').classList.add('d-none');
            document.getElementById('hashError').classList.remove('d-none');
            document.getElementById('hashErrorMessage').textContent = data.error || 'Calculation failed';
        }
    } catch (error) {
        document.getElementById('hashLoading').classList.add('d-none');
        document.getElementById('hashError').classList.remove('d-none');
        document.getElementById('hashErrorMessage').textContent = `Request failed: ${error.message}`;
    }
}

function exportCurrentHash() {
    if (!currentHashPath) return;

    const cleanPath = currentHashPath.replace(/^\/+/, '/');
    const fileName = cleanPath.split('/').pop();
    const md5 = document.getElementById('hashMd5').textContent;
    const sha1 = document.getElementById('hashSha1').textContent;
    const sha256 = document.getElementById('hashSha256').value;
    const fileSize = document.getElementById('hashFileSize').textContent;
    const fileTime = document.getElementById('hashFileMtime').textContent;
    
    const fileLabel = translations['file'] || 'File';
    const pathLabel = translations['file_path'] || 'Path';
    const sizeLabel = translations['fileSize'] || 'Size';
    const timeLabel = translations['modifiedTime'] || 'Modified';
    const generateLabel = translations['created_time'] || 'Generated';
    const hashTitle = translations['hash_values'] || 'Hash Values';
    const md5Label = 'MD5:';
    const sha1Label = 'SHA1:';
    const sha256Label = 'SHA256:';
    
    const content = `${fileLabel}: ${fileName}
${pathLabel}: ${currentHashPath}
${sizeLabel}: ${fileSize}
${timeLabel}: ${fileTime}
${generateLabel}: ${new Date().toLocaleString()}

========== ${hashTitle} ==========

${md5Label}   ${md5}
${sha1Label}  ${sha1}
${sha256Label} ${sha256}

================================
`;
    
    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${fileName}.hash.txt`;
    a.click();
    URL.revokeObjectURL(url);
    
    const successMsg = translations['hash_exported'] || 'Hash exported successfully';
    logAndSpeak(successMsg);
}

function showInstallDialog() {
    if (selectedFiles.size !== 1) {
        logAndSpeak(translations['select_one_package'] || 'Please select one package file', 'warning');
        return;
    }
    
    const path = Array.from(selectedFiles)[0];
    const fileName = path.split('/').pop();
    const ext = fileName.toLowerCase().split('.').pop();
    
    if (!['ipk', 'apk', 'run'].includes(ext)) {
        logAndSpeak(translations['invalid_package'] || 'Not a valid package file', 'error');
        return;
    }
    
    document.getElementById('installPackageName').textContent = fileName;
    document.getElementById('installProgress').style.width = '0%';
    document.getElementById('installProgressText').textContent = '0%';
    document.getElementById('installOutput').innerHTML = '';
    
    const updateCheckContainer = document.getElementById('installUpdateCheckContainer');
    const forceCheckLabel = document.querySelector('label[for="installForceCheck"]');
    
    if (ext === 'ipk') {
        updateCheckContainer.style.display = 'block';
        if (forceCheckLabel) {
            forceCheckLabel.innerHTML = translations['install_force'] || 'Force installation (override dependencies/overwrite))';
        }
    } else if (ext === 'apk') {
        updateCheckContainer.style.display = 'none';
        if (forceCheckLabel) {
            forceCheckLabel.innerHTML = translations['install_force_apk'] || 'Force installation (-r replace existing)';
        }
    } else if (ext === 'run') {
        updateCheckContainer.style.display = 'none';
        if (forceCheckLabel) {
            forceCheckLabel.innerHTML = translations['install_force_run'] || 'Force execution (add execute permission)';
        }
    }
    
    document.getElementById('installForceCheck').checked = true;
    document.getElementById('installUpdateCheck').checked = true;
    
    document.getElementById('installInfoText').innerHTML = 
        `${translations['installing'] || 'Installing'}: <strong>${escapeHtml(fileName)}</strong>`;
    
    hideFileContextMenu();
    
    const modal = new bootstrap.Modal(document.getElementById('installModal'));
    modal.show();
    
    startPackageInstallation(path, ext);
}

function startPackageInstallation(path, ext) {
    if (installEventSource) {
        installEventSource.close();
    }
    
    const formData = new FormData();
    formData.append('path', path);
    formData.append('force', document.getElementById('installForceCheck').checked ? '1' : '0');
    formData.append('update', document.getElementById('installUpdateCheck').checked ? '1' : '0');
    
    const output = document.getElementById('installOutput');
    output.innerHTML = '';
    
    fetch('?action=install_package', {
        method: 'POST',
        body: formData
    }).then(response => {
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        
        function readStream() {
            reader.read().then(({ done, value }) => {
                if (done) return;
                
                const text = decoder.decode(value);
                const events = text.split('\n\n');
                
                events.forEach(event => {
                    if (!event.trim()) return;
                    
                    const lines = event.split('\n');
                    let eventType = 'message';
                    let eventData = '';
                    
                    lines.forEach(line => {
                        if (line.startsWith('event: ')) {
                            eventType = line.substring(7);
                        } else if (line.startsWith('data: ')) {
                            try {
                                eventData = JSON.parse(line.substring(6));
                            } catch {
                                eventData = { message: line.substring(6) };
                            }
                        }
                    });
                    
                    handleInstallEvent(eventType, eventData);
                });
                
                readStream();
            });
        }
        
        readStream();
    }).catch(error => {
        appendInstallOutput(`Error: ${error.message}`, 'error');
        updateInstallProgress(100, 'error');
    });
}

function handleInstallEvent(eventType, data) {
    const output = document.getElementById('installOutput');
    
    switch(eventType) {
        case 'output':
            appendInstallOutput(data.message);
            break;
            
        case 'start':
            appendInstallOutput('🚀 ' + data.message, 'info');
            break;
            
        case 'complete':
            if (data.success) {
                appendInstallOutput('✅ ' + data.message, 'success');
                updateInstallProgress(100, 'success');
            } else {
                appendInstallOutput('❌ ' + data.message, 'error');
                updateInstallProgress(100, 'error');
            }
            break;
            
        case 'error':
            appendInstallOutput('❌ ' + data.message, 'error');
            updateInstallProgress(100, 'error');
            break;
    }
}

function appendInstallOutput(message, type = 'normal') {
    const output = document.getElementById('installOutput');
    const line = document.createElement('div');
    line.style.marginBottom = '2px';
    line.style.whiteSpace = 'pre-wrap';
    line.style.wordBreak = 'break-all';
    
    let prefix = '';
    let color = '#00ff00';
    
    switch(type) {
        case 'error':
            prefix = '⚠️ ';
            color = '#ff6b6b';
            break;
        case 'success':
            prefix = '✓ ';
            color = '#4CAF50';
            break;
        case 'info':
            prefix = 'ℹ️ ';
            color = '#2196F3';
            break;
        case 'warning':
            prefix = '⚠️ ';
            color = '#ff9800';
            break;
    }
    
    line.innerHTML = `<span style="color: ${color}">${escapeHtml(prefix + message)}</span>`;
    output.appendChild(line);
    output.scrollTop = output.scrollHeight;
    
    const lines = output.children.length;
    if (lines > 20) {
        const progress = Math.min(90, lines);
        updateInstallProgress(progress);
    }
}

function updateInstallProgress(percent, status = 'normal') {
    const progressBar = document.getElementById('installProgress');
    const progressText = document.getElementById('installProgressText');
    
    progressBar.style.width = percent + '%';
    progressText.textContent = percent + '%';
    
    if (status === 'error') {
        progressBar.classList.remove('bg-success');
        progressBar.classList.add('bg-danger');
    } else if (status === 'success') {
        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('bg-success');
    } else {
        progressBar.classList.add('bg-primary');
    }
}

function updateFileContextMenuItems() {
    const path = fileContextMenuTarget?.getAttribute('data-path');
    if (path) {
        const fileName = path.split('/').pop();
        const ext = fileName.toLowerCase().split('.').pop();
        
        const installItem = document.getElementById('fileInstallItem');
        const installDivider = document.getElementById('installDivider');
        
        if (ext === 'ipk' || ext === 'apk') {
            installItem.style.display = 'flex';
            installDivider.style.display = 'block';
        } else {
            installItem.style.display = 'none';
            installDivider.style.display = 'none';
        }
    }
}

function showFileMenuItems(isDir, ext) {
    const installItem = document.getElementById('fileInstallItem');
    const installDivider = document.getElementById('installDivider');
    
    if (!isDir && (ext === 'ipk' || ext === 'apk')) {
        installItem.style.display = 'flex';
        installDivider.style.display = 'block';
    } else {
        installItem.style.display = 'none';
        installDivider.style.display = 'none';
    }
}

function copyFilePath() {
    if (selectedFiles.size === 0) {
        logAndSpeak(translations['select_items_first'] || 'Please select items first', 'warning');
        return;
    }
    
    let path = Array.from(selectedFiles)[0];
    
    path = path.replace(/\/+/g, '/');
    
    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
        navigator.clipboard.writeText(path).then(() => {
            const successMsg = translations['file_path_copied'] || 'File path copied to clipboard';
            logAndSpeak(successMsg, 'success');
        }).catch(err => {
            fallbackCopy(path);
        });
    } else {
        fallbackCopy(path);
    }
    
    hideFileContextMenu();
}

function fallbackCopy(text) {
    try {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        textarea.style.left = '-999999px';
        textarea.style.top = '-999999px';
        document.body.appendChild(textarea);
        
        textarea.focus();
        textarea.select();
        
        const successful = document.execCommand('copy');
        document.body.removeChild(textarea);
        
        if (successful) {
            const successMsg = translations['file_path_copied'] || 'File path copied to clipboard';
            logAndSpeak(successMsg, 'success');
        } else {
            throw new Error('Copy command failed');
        }
    } catch (err) {
        const msg = translations['copy_manually'] || 'Please copy the address manually: ' + text;
        logAndSpeak(msg, 'info');
        
        prompt(translations['copy_file_path'] || 'Copy File Path', text);
    }
}

document.getElementById('installModal').addEventListener('hidden.bs.modal', function() {
    if (installEventSource) {
        installEventSource.close();
        installEventSource = null;
    }
    document.getElementById('installOutput').innerHTML = '';
    document.getElementById('installProgress').style.width = '0%';
    document.getElementById('installProgressText').textContent = '0%';
});

function showBatchRenameDialog() {
    if (selectedFiles.size === 0) {
        logAndSpeak(translations['select_files_to_rename'] || 'Please select files to rename', 'warning');
        return;
    }
    
    batchRenameFiles = Array.from(selectedFiles).map(path => {
        const fileItem = document.querySelector(`.file-item[data-path="${path}"]`);
        const name = path.split('/').pop();
        const isDir = fileItem ? fileItem.getAttribute('data-is-dir') === 'true' : false;
        const ext = name.includes('.') ? name.split('.').pop() : '';
        const nameWithoutExt = name.includes('.') ? name.substring(0, name.lastIndexOf('.')) : name;
        
        return {
            path: path,
            name: name,
            nameWithoutExt: nameWithoutExt,
            ext: ext,
            isDir: isDir,
            dir: path.substring(0, path.lastIndexOf('/')) || '/'
        };
    });
    
    batchRenameFiles.sort((a, b) => a.name.localeCompare(b.name));
    
    updateBatchRenameFileList();
    
    document.getElementById('renamePattern').removeEventListener('input', generateBatchRenamePreview);
    document.getElementById('startNumber').removeEventListener('input', generateBatchRenamePreview);
    document.getElementById('numberPadding').removeEventListener('change', generateBatchRenamePreview);
    document.getElementById('keepOriginalName').removeEventListener('change', generateBatchRenamePreview);
    document.getElementById('removeSpecialChars').removeEventListener('change', generateBatchRenamePreview);
    
    document.getElementById('renamePattern').addEventListener('input', generateBatchRenamePreview);
    document.getElementById('startNumber').addEventListener('input', generateBatchRenamePreview);
    document.getElementById('numberPadding').addEventListener('change', generateBatchRenamePreview);
    document.getElementById('keepOriginalName').addEventListener('change', generateBatchRenamePreview);
    document.getElementById('removeSpecialChars').addEventListener('change', generateBatchRenamePreview);
    
    generateBatchRenamePreview();
    
    hideFileContextMenu();
    
    const modal = new bootstrap.Modal(document.getElementById('batchRenameModal'));
    modal.show();
}

function updateBatchRenameFileList() {
    const listContainer = document.getElementById('batchRenameFileList');
    if (!listContainer) return;
    
    let html = '';
    batchRenameFiles.forEach((file, index) => {
        const icon = file.isDir ? 'fa-folder' : 'fa-file';
        const color = file.isDir ? '#FFA726' : '#2196F3';
        html += `
        <div class="px-2 py-1 d-flex align-items-center" style="border-bottom: var(--border-strong);">
            <span class="badge bg-secondary me-2" style="min-width: 30px;">${index + 1}</span>
            <i class="fas ${icon} me-2" style="color: ${color};"></i>
            <span style="word-break: break-all;" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</span>
        </div>`;
    });
    listContainer.innerHTML = html;
    
    const countBadge = document.getElementById('selectedFilesCount');
    if (countBadge) {
        countBadge.textContent = batchRenameFiles.length;
    }
}

function removeSpecialCharsFromName(name) {
    const emojiRegex = /[\u{1F600}-\u{1F64F}|\u{1F300}-\u{1F5FF}|\u{1F680}-\u{1F6FF}|\u{1F700}-\u{1F77F}|\u{1F780}-\u{1F7FF}|\u{1F800}-\u{1F8FF}|\u{1F900}-\u{1F9FF}|\u{1FA00}-\u{1FA6F}|\u{1FA70}-\u{1FAFF}|\u{2600}-\u{26FF}|\u{2700}-\u{27BF}|\u{2B00}-\u{2BFF}|\u{2E00}-\u{2E7F}]/gu;
    name = name.replace(emojiRegex, '');
    name = name.replace(/[#\s]/g, '_');
    name = name.replace(/_+/g, '_');
    name = name.replace(/^_+|_+$/g, '');
    return name;
}

function generateBatchRenamePreview() {
    const pattern = document.getElementById('renamePattern').value;
    const startNum = parseInt(document.getElementById('startNumber').value) || 1;
    const padding = parseInt(document.getElementById('numberPadding').value);
    const keepOriginalName = document.getElementById('keepOriginalName').checked;
    const removeSpecialChars = document.getElementById('removeSpecialChars').checked;
    
    const previewContainer = document.getElementById('batchRenamePreview');
    if (!previewContainer) return;
    
    let html = '<table class="table table-sm table-borderless table-transparent">';
    html += '<thead><tr><th>#</th><th>' + (translations['original_name'] || 'Original') + '</th><th>→</th><th>' + (translations['new_name'] || 'New') + '</th></tr></thead><tbody>';
    
    batchRenameFiles.forEach((file, index) => {
        const num = startNum + index;
        const paddedNum = num.toString().padStart(padding, '0');
        
        let newName = pattern
            .replace(/{n}/g, paddedNum)
            .replace(/{name}/g, keepOriginalName ? file.nameWithoutExt : '')
            .replace(/{ext}/g, file.ext);
        
        if (!keepOriginalName) {
            newName = newName.replace(/{name}/g, '');
        }
        
        if (removeSpecialChars) {
            newName = removeSpecialCharsFromName(newName);
        }
        
        if (file.ext && !newName.endsWith('.' + file.ext)) {
            newName = newName + (newName ? '.' : '') + file.ext;
        }
        
        newName = newName.replace(/\.+/g, '.').replace(/^\.|\.$/g, '');
        
        if (!newName) {
            newName = 'file_' + paddedNum + (file.ext ? '.' + file.ext : '');
        }
        
        const icon = file.isDir ? 'fa-folder' : 'fa-file';
        const color = file.isDir ? '#FFA726' : '#2196F3';
        
        html += `<tr>
            <td><span class="badge bg-secondary">${index + 1}</span></td>
            <td><i class="fas ${icon} me-1" style="color: ${color};"></i> ${escapeHtml(file.name)}</td>
            <td><i class="fas fa-arrow-right text-success"></i></td>
            <td><i class="fas ${icon} me-1" style="color: ${color};"></i> ${escapeHtml(newName)}</td>
        </tr>`;
    });
    
    html += '</tbody></table>';
    previewContainer.innerHTML = html;
}

async function executeBatchRename() {
    const pattern = document.getElementById('renamePattern').value;
    const startNum = parseInt(document.getElementById('startNumber').value) || 1;
    const padding = parseInt(document.getElementById('numberPadding').value);
    const keepOriginalName = document.getElementById('keepOriginalName').checked;
    const removeSpecialChars = document.getElementById('removeSpecialChars').checked;
    
    if (!pattern) {
        logAndSpeak(translations['enter_rename_pattern'] || 'Please enter rename pattern', 'warning');
        return;
    }
    
    bootstrap.Modal.getInstance(document.getElementById('batchRenameModal')).hide();
    
    logAndSpeak(translations['renaming_files'] || 'Renaming files...', 'info');
    
    let successCount = 0;
    let errorCount = 0;
    
    for (let i = 0; i < batchRenameFiles.length; i++) {
        const file = batchRenameFiles[i];
        const num = startNum + i;
        const paddedNum = num.toString().padStart(padding, '0');
        
        let newName = pattern
            .replace(/{n}/g, paddedNum)
            .replace(/{name}/g, keepOriginalName ? file.nameWithoutExt : '')
            .replace(/{ext}/g, file.ext);
        
        if (!keepOriginalName) {
            newName = newName.replace(/{name}/g, '');
        }
        
        if (removeSpecialChars) {
            newName = removeSpecialCharsFromName(newName);
        }
        
        if (file.ext && !newName.endsWith('.' + file.ext)) {
            newName = newName + (newName ? '.' : '') + file.ext;
        }
        
        newName = newName.replace(/\.+/g, '.').replace(/^\.|\.$/g, '');
        
        if (!newName) {
            newName = 'file_' + paddedNum + (file.ext ? '.' + file.ext : '');
        }
        
        if (newName === file.name) {
            successCount++;
            continue;
        }
        
        try {
            const response = await fetch(`?action=rename_item&old=${encodeURIComponent(file.path)}&new=${encodeURIComponent(newName)}`);
            const data = await response.json();
            
            if (data.success) {
                successCount++;
            } else {
                errorCount++;
            }
        } catch (error) {
            errorCount++;
        }
    }
    
    selectedFiles.clear();
    updateSelectionInfo();
    
    refreshFiles();
    
    let message = '';
    if (successCount > 0) {
        message = (translations['rename_success_count'] || 'Successfully renamed {count} file(s)').replace('{count}', successCount);
        logAndSpeak(message, 'success');
    }
    
    if (errorCount > 0) {
        message = (translations['rename_failed_count'] || 'Failed to rename {count} file(s)').replace('{count}', errorCount);
        logAndSpeak(message, 'error');
    }
}

function showConvertDialog() {
    if (selectedFiles.size === 0) {
        logAndSpeak(translations['select_files_first'] || 'Please select files first', 'warning');
        return;
        return;
    }
    
    convertFiles = Array.from(selectedFiles).filter(path => {
        const ext = path.split('.').pop().toLowerCase();
        const mediaExts = [ 
            'mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 
            'mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 
            '3gp', '3g2', 'ogv', 'mpg', 'mpeg',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'ico'
        ]
        return mediaExts.includes(ext);
    }).map(path => {
        const name = path.split('/').pop();
        return {
            path: path,
            name: name,
            nameWithoutExt: name.includes('.') ? name.substring(0, name.lastIndexOf('.')) : name,
            ext: name.includes('.') ? name.split('.').pop() : ''
        };
    });
    
    updateConvertFileList();
    
    hideFileContextMenu();
    
    const modal = new bootstrap.Modal(document.getElementById('convertModal'));
    modal.show();
}

function updateConvertFileList() {
    const listContainer = document.getElementById('convertFileList');
    const countBadge = document.getElementById('convertFilesCount');
    
    if (!listContainer) return;
    
    listContainer.innerHTML = '';
    
    if (convertFiles.length === 0) {
        listContainer.innerHTML = '<div class="text-center text-muted py-3">' + 
            (translations['no_files_selected'] || 'No files selected') + '</div>';
        if (countBadge) countBadge.textContent = '0';
        return;
    }
    
    convertFiles.forEach((file, index) => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'list-group-item d-flex justify-content-between align-items-center';
        itemDiv.style.background = 'transparent';
        itemDiv.style.borderBottom = '1px solid var(--border-color)';
        
        const icon = file.ext.match(/(mp3|wav|ogg|flac|m4a|aac)/) ? 'fa-music' : 'fa-video';
        const color = file.ext.match(/(mp3|wav|ogg|flac|m4a|aac)/) ? '#9C27B0' : '#2196F3';
        
        itemDiv.innerHTML = `
            <div>
                <i class="fas ${icon} me-2" style="color: ${color}"></i>
                <span>${escapeHtml(file.name)}</span>
            </div>
            <button class="btn btn-sm btn-danger" onclick="removeConvertFile(${index})">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        listContainer.appendChild(itemDiv);
    });
    
    if (countBadge) countBadge.textContent = convertFiles.length;
}

function removeConvertFile(index) {
    convertFiles.splice(index, 1);
    updateConvertFileList();
}

async function startConvert() {
    if (convertFiles.length === 0) {
        logAndSpeak(translations['select_files_first'] || 'Please select files first', 'warning');
        return;
    }
    
    const format = document.getElementById('convertFormat').value;
    const quality = document.getElementById('convertQuality').value;
    
    document.getElementById('convertProgressArea').style.display = 'block';
    document.getElementById('convertLog').innerHTML = '';
    
    let success = 0;
    let failed = 0;
    
    for (let i = 0; i < convertFiles.length; i++) {
        const file = convertFiles[i];
        
        const timestamp = Date.now() + Math.floor(Math.random() * 1000);
        const outputName = `${file.nameWithoutExt}_${timestamp}.${format}`;
        
        const outputPath = currentPath + '/' + outputName;
        
        const progress = ((i) / convertFiles.length * 100).toFixed(0);
        document.getElementById('convertProgressBar').style.width = progress + '%';
        document.getElementById('convertProgressBar').textContent = progress + '%';
        document.getElementById('convertProgressText').textContent = `${i}/${convertFiles.length}`;
        
        appendConvertLog(
            (translations['converting_file'] || 'Converting') + `: ${file.name} -> ${outputName}`
        );
        
        try {
            const response = await fetch('?action=convert_media', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    input: file.path,
                    output: outputPath,
                    format: format,
                    quality: quality
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                success++;
                appendConvertLog(
                    `✅ ${translations['convert_success'] || 'Converted successfully'}: ${outputName}`, 
                    'success'
                );
            } else {
                failed++;
                appendConvertLog(
                    `❌ ${translations['convert_failed'] || 'Conversion failed'}: ${data.error}`, 
                    'error'
                );
            }
        } catch (error) {
            failed++;
            appendConvertLog(
                `❌ ${translations['convert_error'] || 'Conversion error'}: ${error.message}`, 
                'error'
            );
        }
    }
    
    document.getElementById('convertProgressBar').style.width = '100%';
    document.getElementById('convertProgressBar').textContent = '100%';
    document.getElementById('convertProgressText').textContent = `${convertFiles.length}/${convertFiles.length}`;
    
    const completeMsg = translations['convert_complete'] || 'Conversion complete';
    appendConvertLog(
        `\n${completeMsg}: ${success} ${translations['success'] || 'success'}, ${failed} ${translations['failed'] || 'failed'}`, 
        success > 0 ? 'success' : 'error'
    );
    
    refreshFiles();
}

function appendConvertLog(message, type = 'normal') {
    const log = document.getElementById('convertLog');
    const line = document.createElement('div');
    
    let color = '#00ff00';
    if (type === 'error') color = '#ff6b6b';
    else if (type === 'success') color = '#4CAF50';
    
    line.style.color = color;
    line.style.marginBottom = '2px';
    line.textContent = message;
    
    log.appendChild(line);
    log.scrollTop = log.scrollHeight;
}

async function cleanThumbnailCache() {
    const confirmMessage = translations['confirm_clean_thumbnails'] || 
                          'Are you sure you want to clean all video thumbnails?';
    
    showConfirmation(confirmMessage, async () => {
        try {
            const response = await fetch('?action=clean_thumbnail_cache');
            const data = await response.json();
            
            if (data.success) {
                const successMsg = translations['thumbnails_cleaned'] || 'Thumbnail cache cleaned successfully';
                logAndSpeak(successMsg, 'success');
                
                setTimeout(() => {
                    location.reload();
                }, 2500);
            } else {
                const errorMsg = translations['clean_thumbnails_failed'] || 'Failed to clean thumbnail cache';
                logAndSpeak(errorMsg, 'error');
            }
        } catch (error) {
            const errorMsg = (translations['clean_thumbnails_error'] || 'Error cleaning thumbnails') + ': ' + error.message;
            logAndSpeak(errorMsg, 'error');
        }
    });
}

function prepareQrCode() {
    if (selectedFiles.size === 0) return;
    
    const path = Array.from(selectedFiles)[0];
    const fileName = path.split('/').pop();
    
    const baseUrl = window.location.origin + window.location.pathname;
    const fileUrl = `${baseUrl}?preview=1&path=${encodeURIComponent(path)}`;
    
    document.getElementById('qrcodeFileName').textContent = fileName;
    document.getElementById('qrcodeFileUrl').textContent = fileUrl;
    
    const container = document.getElementById('qrcodeContainer');
    container.innerHTML = '';
    
    if (typeof QRCode === 'undefined') {
        const script = document.createElement('script');
        script.src = '/luci-static/spectra/js/qrcode.min.js';
        script.onload = () => {
            new QRCode(container, {
                text: fileUrl,
                width: 200,
                height: 200,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        };
        document.head.appendChild(script);
    } else {
        new QRCode(container, {
            text: fileUrl,
            width: 200,
            height: 200,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    }
    
    hideFileContextMenu();
}

let currentFitMode = localStorage.getItem('playerFitMode') || 'contain';
const fitModes = ['contain', 'fill', 'none', 'scale-down', 'cover'];
const fitIcons = {
    'contain': 'fa-arrows-alt',
    'fill': 'fa-arrows-alt-v',
    'none': 'fa-ban',
    'scale-down': 'fa-compress-arrows-alt',
    'cover': 'fa-crop-alt'
};

function applyFitMode() {
    const videoPlayer = document.getElementById('videoPlayer');
    const imageViewer = document.getElementById('imageViewer');
    const fullscreenVideo = document.getElementById('fullscreenVideo');
    const fullscreenImage = document.getElementById('fullscreenImage');
    
    if (videoPlayer && videoPlayer.style.display !== 'none') {
        videoPlayer.style.objectFit = currentFitMode;
    }
    if (imageViewer && imageViewer.style.display !== 'none') {
        imageViewer.style.objectFit = currentFitMode;
    }
    if (fullscreenVideo && fullscreenVideo.style.display !== 'none') {
        fullscreenVideo.style.objectFit = currentFitMode;
    }
    if (fullscreenImage && fullscreenImage.style.display !== 'none') {
        fullscreenImage.style.objectFit = currentFitMode;
    }
}

const originalPlayMedia = window.playMedia;
window.playMedia = function(filePath) {
    originalPlayMedia(filePath);
    setTimeout(applyFitMode, 300);
    setTimeout(applyFitMode, 600);
    setTimeout(applyFitMode, 1000);
};

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(applyFitMode, 500);
    
    const btn = document.getElementById('fitModeBtn');
    if (btn) {
        const icon = btn.querySelector('i');
        icon.className = `fas ${fitIcons[currentFitMode]}`;
    }
});

function togglePlayerFitMode() {
    const currentIndex = fitModes.indexOf(currentFitMode);
    const nextIndex = (currentIndex + 1) % fitModes.length;
    currentFitMode = fitModes[nextIndex];
    localStorage.setItem('playerFitMode', currentFitMode);
    applyFitMode();
    const btn = document.getElementById('fitModeBtn');
    const icon = btn.querySelector('i');
    icon.className = `fas ${fitIcons[currentFitMode]}`;
    
    const messages = {
        'contain': translations['fit_contain'] || 'Normal scale',
        'fill': translations['fit_fill'] || 'Stretch fill',
        'none': translations['fit_none'] || 'Original size',
        'scale-down': translations['fit_scale-down'] || 'Smart fit',
        'cover': translations['fit_cover'] || 'Crop to fill'
    };
    
    logAndSpeak(messages[currentFitMode], 'info');
}

document.addEventListener('loadeddata', applyFitMode, true);
document.addEventListener('loadedmetadata', applyFitMode, true);

function showRecycleBin() {
    document.querySelectorAll('.grid-section').forEach(s => s.style.display = 'none');
    document.getElementById('recycleBinSection').style.display = 'block';
    loadRecycleBin();
}

async function loadRecycleBin() {
    const grid = document.getElementById('recycleBinGrid');
    const emptyEl = document.getElementById('recycleBinEmpty');
    const countEl = document.getElementById('recycleBinCount');
    
    if (!grid) return;
    
    grid.style.minHeight = '200px';
    
    grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> ' + (translations['loading'] || 'Loading...') + '</div>';
    
    try {
        const response = await fetch('?action=get_recycle_bin');
        const data = await response.json();
        
        if (data.success) {
            recycleBinItems = (data.items || []).filter(item => !item.name.endsWith('.meta.json'));
            
            if (countEl) {
                countEl.textContent = recycleBinItems.length;
            }
            
            if (recycleBinItems.length === 0) {
                grid.innerHTML = '';
                if (emptyEl) emptyEl.style.display = 'block';
                updateRecycleSelectionInfo();
                return;
            }
            
            if (emptyEl) emptyEl.style.display = 'none';
            
            let cardsHtml = '';
            recycleBinItems.forEach((item) => {
                const isSelected = selectedRecycleItems.has(item.path);
                let displayName = item.name;
                displayName = displayName.replace(/^[^_]+_/, '');
                
                const ext = displayName.includes('.') ? displayName.split('.').pop().toLowerCase() : '';
                
                let deleteTime = '';
                if (item.deleted_formatted) {
                    deleteTime = item.deleted_formatted;
                } else if (item.deleted_time) {
                    const date = new Date(item.deleted_time * 1000);
                    deleteTime = date.toLocaleString();
                }
                
                cardsHtml += `
                <div class="file-item position-relative ${isSelected ? 'selected' : ''}" 
                     data-path="${item.path.replace(/'/g, "\\'")}"
                     data-original-path="${item.original_path || ''}"
                     data-name="${displayName}"
                     data-is-dir="${item.is_dir ? 'true' : 'false'}"
                     data-size="${item.size || 0}"
                     data-ext="${ext}"
                     title="${displayName}"
                     onclick="handleRecycleItemClick(event, '${item.path.replace(/'/g, "\\'")}')">
                    
                    <div class="form-check position-absolute" style="top: 5px; left: 9px; z-index: 100;">
                        <input class="form-check-input" type="checkbox" 
                               ${isSelected ? 'checked' : ''}
                               onclick="event.stopPropagation(); toggleRecycleSelection('${item.path.replace(/'/g, "\\'")}', this.checked)">
                    </div>
                    
                    <div class="file-icon mb-2">
                        ${getFileIcon(displayName, ext, item.is_dir)}
                    </div>
                    
                    <div class="file-name text-truncate w-100" title="${displayName}">
                        ${truncateFileName(displayName, 20)}
                    </div>
                    
                    <div class="file-size text-muted small mt-1">
                        ${item.is_dir ? (translations['folder'] || 'Folder') : formatFileSize(item.size || 0)}
                    </div>
                    
                    <div class="small text-warning mt-1" style="font-size: 0.7rem;">
                        <i class="fas fa-clock"></i> ${deleteTime}
                    </div>
                </div>`;
            });
            
            grid.innerHTML = cardsHtml;
            updateRecycleSelectionInfo();
        }
    } catch (error) {
        grid.innerHTML = '<div class="empty-folder" style="grid-column: 1/-1;"><i class="fas fa-exclamation-circle"></i><p>' + (translations['load_error'] || 'Error loading recycle bin') + '</p></div>';
    }
}

function handleRecycleItemClick(event, path) {
    if (event.target.type === 'checkbox' || event.target.closest('.form-check')) {
        return;
    }
    toggleRecycleSelection(path);
}

function toggleRecycleSelection(path, checked) {
    if (checked !== undefined) {
        if (checked) {
            selectedRecycleItems.add(path);
        } else {
            selectedRecycleItems.delete(path);
        }
    } else {
        if (selectedRecycleItems.has(path)) {
            selectedRecycleItems.delete(path);
        } else {
            selectedRecycleItems.add(path);
        }
    }
    
    document.querySelectorAll(`[data-path="${path}"]`).forEach(el => {
        if (selectedRecycleItems.has(path)) {
            el.classList.add('selected');
            const checkbox = el.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = true;
        } else {
            el.classList.remove('selected');
            const checkbox = el.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
        }
    });
    
    updateRecycleSelectionInfo();
}

function updateRecycleSelectionInfo() {
    const selectedInfo = document.getElementById('recycleSelectedInfo');
    const restoreBtn = document.getElementById('recycleRestoreBtn');
    const deleteBtn = document.getElementById('recycleDeleteBtn');
    const selectAllCheckbox = document.getElementById('recycleSelectAll');
    const count = selectedRecycleItems.size;
    
    if (count > 0) {
        if (selectedInfo) {
            selectedInfo.style.display = 'inline';
            selectedInfo.textContent = (translations['selected_items_count'] || '{count} selected').replace('{count}', count);
        }
        if (restoreBtn) restoreBtn.disabled = false;
        if (deleteBtn) deleteBtn.disabled = false;
    } else {
        if (selectedInfo) selectedInfo.style.display = 'none';
        if (restoreBtn) restoreBtn.disabled = true;
        if (deleteBtn) deleteBtn.disabled = true;
    }
    
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = count === recycleBinItems.length && recycleBinItems.length > 0;
        selectAllCheckbox.indeterminate = count > 0 && count < recycleBinItems.length;
    }
}

function toggleRecycleSelectAll() {
    const selectAll = document.getElementById('recycleSelectAll').checked;
    
    if (selectAll) {
        recycleBinItems.forEach(item => selectedRecycleItems.add(item.path));
    } else {
        selectedRecycleItems.clear();
    }
    
    recycleBinItems.forEach(item => {
        const el = document.querySelector(`[data-path="${item.path}"]`);
        if (el) {
            if (selectAll) {
                el.classList.add('selected');
                const checkbox = el.querySelector('input[type="checkbox"]');
                if (checkbox) checkbox.checked = true;
            } else {
                el.classList.remove('selected');
                const checkbox = el.querySelector('input[type="checkbox"]');
                if (checkbox) checkbox.checked = false;
            }
        }
    });
    
    updateRecycleSelectionInfo();
}

async function restoreSelectedFromRecycle() {
    if (selectedRecycleItems.size === 0) return;
    
    let successCount = 0;
    let errorCount = 0;
    
    for (const path of selectedRecycleItems) {
        try {
            const response = await fetch(`?action=restore_from_recycle_bin&path=${encodeURIComponent(path)}`);
            const data = await response.json();
            
            if (data.success) {
                successCount++;
                if (data.restored_path) {
                    sessionStorage.setItem('highlightFile', data.restored_path);
                }
            } else {
                errorCount++;
            }
        } catch (error) {
            errorCount++;
        }
    }
    
    if (successCount > 0) {
        logAndSpeak((translations['restore_success'] || 'Successfully restored {count} item(s)').replace('{count}', successCount), 'success');
        selectedRecycleItems.clear();
        await loadRecycleBin();
        
        showSection('files');
        setTimeout(() => {
            const highlightPath = sessionStorage.getItem('highlightFile');
            if (highlightPath) {
                const dir = highlightPath.substring(0, highlightPath.lastIndexOf('/')) || '/';
                navigateTo(dir);
            }
        }, 500);
    }
    
    if (errorCount > 0) {
        logAndSpeak((translations['restore_failed'] || 'Failed to restore {count} item(s)').replace('{count}', errorCount), 'error');
    }
}

async function deleteSelectedFromRecycle() {
    if (selectedRecycleItems.size === 0) return;
    
    const count = selectedRecycleItems.size;
    const message = (translations['confirm_delete_permanent'] || 'Permanently delete {count} item(s)?')
        .replace('{count}', count);
    
    showConfirmation(encodeURIComponent(message), async () => {
        let successCount = 0;
        let errorCount = 0;
        
        for (const path of selectedRecycleItems) {
            try {
                const response = await fetch(`?action=move_to_recycle_bin&path=${encodeURIComponent(path)}&force=true`);
                const data = await response.json();
                
                if (data.success) {
                    successCount++;
                    const metaFile = path + '.meta.json';
                    await fetch(`?action=move_to_recycle_bin&path=${encodeURIComponent(metaFile)}&force=true`);
                } else {
                    errorCount++;
                }
            } catch (error) {
                errorCount++;
            }
        }
        
        if (successCount > 0) {
            logAndSpeak((translations['delete_success'] || 'Permanently deleted {count} item(s)').replace('{count}', successCount), 'success');
            selectedRecycleItems.clear();
            loadRecycleBin();
        }
        
        if (errorCount > 0) {
            logAndSpeak((translations['delete_failed'] || 'Failed to delete {count} item(s)').replace('{count}', errorCount), 'error');
        }
    });
}

function emptyRecycleBin() {
    const message = translations['confirm_empty_recycle'] || 'Are you sure you want to empty the recycle bin? This cannot be undone.';
    
    showConfirmation(encodeURIComponent(message), () => {
        fetch('?action=empty_recycle_bin')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    logAndSpeak(translations['recycle_bin_cleared'] || 'Recycle bin emptied', 'success');
                    recycleBinItems = [];
                    selectedRecycleItems.clear();
                    loadRecycleBin();
                }
            });
    });
}

function saveRecycleBinSettings() {
    const enabled = document.getElementById('recycleBinToggle').checked;
    const days = document.getElementById('recycleBinDays').value;
    
    const formData = new FormData();
    formData.append('enabled', enabled);
    formData.append('days', days);
    
    fetch('?action=recycle_bin_settings', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            logAndSpeak(translations['settings_saved'] || 'Settings saved', 'success');
        }
    })
    .catch(error => {
        logAndSpeak(translations['settings_save_error'] || 'Error saving settings', 'error');
    });
}

function initRecycleBinToggle() {
    const toggle = document.getElementById('recycleBinToggle');
    if (!toggle) return;
    
    fetch('?action=get_recycle_bin_settings')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toggle.checked = data.settings.enabled;
                const daysInput = document.getElementById('recycleBinDays');
                if (daysInput) {
                    daysInput.value = data.settings.days;
                }
            }
        })
        .catch(error => console.error('Error loading recycle bin settings:', error));
    
    toggle.addEventListener('change', function() {
        saveRecycleBinSettings();
        
        if (this.checked) {
            logAndSpeak(translations['recycle_bin_enabled'] || 'Recycle bin enabled', 'info');
        } else {
            logAndSpeak(translations['recycle_bin_disabled'] || 'Recycle bin disabled', 'info');
        }
    });
}

function showDeleteConfirmModal() {
    const count = selectedFiles.size;
    const fileNames = Array.from(selectedFiles).map(p => p.split('/').pop()).join(', ');
    
    document.getElementById('deleteConfirmMessage').innerHTML = 
        (translations['confirm_delete'] || 'Are you sure you want to delete {count} item(s)?').replace('{count}', count) + 
        '<br><small class="text-muted">' + fileNames + '</small>';
    
    const forceDeleteOption = document.getElementById('forceDeleteOption');
    if (forceDeleteOption) {
        forceDeleteOption.style.display = 'block';
        const forceCheck = document.getElementById('forceDeleteCheck');
        if (forceCheck) {
            forceCheck.checked = false;
            forceCheck.disabled = false;
        }
    }
    
    window.pendingDeleteFiles = new Set(selectedFiles);
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

window.executeDelete = async function() {
    const filesToDelete = window.pendingDeleteFiles;
    if (!filesToDelete || filesToDelete.size === 0) return;
    
    const recycleEnabled = document.getElementById('recycleBinToggle')?.checked ?? true;
    
    let forceDelete;
    if (!recycleEnabled) {
        forceDelete = true;
    } else {
        forceDelete = document.getElementById('forceDeleteCheck')?.checked ?? false;
    }
    
    bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
    
    let successCount = 0;
    let errorCount = 0;
    
    for (const path of filesToDelete) {
        try {
            const response = await fetch(`?action=move_to_recycle_bin&path=${encodeURIComponent(path)}&force=${forceDelete}`);
            const data = await response.json();
            
            if (data.success) {
                successCount++;
            } else {
                errorCount++;
            }
        } catch (error) {
            errorCount++;
        }
    }
    
    if (successCount > 0) {
        const message = forceDelete ? 
            (translations['permanently_deleted'] || 'Permanently deleted {count} item(s)') : 
            (translations['moved_to_recycle'] || 'Moved {count} item(s) to recycle bin');
        logAndSpeak(message.replace('{count}', successCount), 'success');
        
        selectedFiles.clear();
        window.pendingDeleteFiles = null;
        refreshFiles();
        updateSystemInfo(); 
        
        if (document.getElementById('recycleBinCount')) {
            loadRecycleBin();
        }
    }
    
    if (errorCount > 0) {
        logAndSpeak((translations['delete_failed'] || 'Failed to delete {count} item(s)').replace('{count}', errorCount), 'error');
    }
}

async function executePermanentDelete() {
    const filesToDelete = selectedFiles;
    if (!filesToDelete || filesToDelete.size === 0) return;
    
    let successCount = 0;
    let errorCount = 0;
    
    for (const path of filesToDelete) {
        try {
            const response = await fetch(`?action=move_to_recycle_bin&path=${encodeURIComponent(path)}&force=true`);
            const data = await response.json();
            
            if (data.success) {
                successCount++;
            } else {
                errorCount++;
            }
        } catch (error) {
            errorCount++;
        }
    }
    
    if (successCount > 0) {
        logAndSpeak((translations['permanently_deleted'] || 'Permanently deleted {count} item(s)').replace('{count}', successCount), 'success');
        selectedFiles.clear();
        refreshFiles();
    }
    
    if (errorCount > 0) {
        logAndSpeak((translations['delete_failed'] || 'Failed to delete {count} item(s)').replace('{count}', errorCount), 'error');
    }
}

function setAsWallpaper() {
    if (selectedFiles.size === 0) {
        return;
    }
    
    const path = Array.from(selectedFiles)[0];
    const fileName = path.split('/').pop();
    
    hideFileContextMenu();
    
    showConfirmation(
        (translations['confirm_set_wallpaper'] || 'Set "{filename}" as wallpaper?').replace('{filename}', fileName),
        async () => {
            try {
                const wallpaperData = {
                    path: path,
                    timestamp: Date.now()
                };
                localStorage.setItem('currentWallpaper', JSON.stringify(wallpaperData));
                
                applyWallpaper(path);
                logAndSpeak(translations['wallpaper_set_success'] || 'Wallpaper set successfully', 'success');
                
            } catch (error) {
                logAndSpeak(translations['wallpaper_set_error'] || 'Failed to set wallpaper: ' + error.message, 'error');
            }
        }
    );
}

function clearWallpaper() {
    hideFileContextMenu();
    localStorage.removeItem('currentWallpaper');
    
    const styleEl = document.getElementById('wallpaper-style');
    if (styleEl) {
        styleEl.remove();
    }
    
    logAndSpeak(translations['theme_restored'] || 'Default theme restored', 'success');
    hideFileContextMenu();
}

function applyWallpaper(path) {
    let styleEl = document.getElementById('wallpaper-style');
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'wallpaper-style';
        document.head.appendChild(styleEl);
    }
    
    const wallpaperUrl = `?preview=1&path=${encodeURIComponent(path)}&t=${Date.now()}`;
    
    styleEl.textContent = `
        #gridContainer {
            background-image: url('${wallpaperUrl}') !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            background-attachment: fixed !important;
        }
        
        #gridContainer::before {
            background: transparent !important;
        }
    `;
}

function loadSavedWallpaper() {
    const saved = localStorage.getItem('currentWallpaper');
    if (saved) {
        try {
            const data = JSON.parse(saved);
            if (data.path) {
                applyWallpaper(data.path);
            }
        } catch (e) {}
    }
}

function saveMiniPlayerState() {
    if (!isMiniMode) return;
    
    const playerArea = document.getElementById('playerArea');
    const state = {
        width: playerArea.style.width,
        height: playerArea.style.height,
        top: playerArea.style.top,
        left: playerArea.style.left,
        bottom: playerArea.style.bottom,
        right: playerArea.style.right,
        position: playerArea.style.position
    };
    localStorage.setItem('miniPlayerState', JSON.stringify(state));
}

function loadMiniPlayerState() {
    const saved = localStorage.getItem('miniPlayerState');
    if (saved) {
        try {
            return JSON.parse(saved);
        } catch (e) {
            return null;
        }
    }
    return null;
}

function toggleMiniPlayer() {
    const playerArea = document.getElementById('playerArea');
    const toggleBtn = document.getElementById('miniPlayerToggle');
    const icon = toggleBtn.querySelector('i');
    
    if (!isMiniMode) {
        playerArea.classList.add('mini-mode');
        
        const savedState = loadMiniPlayerState();
        if (savedState) {
            playerArea.style.width = savedState.width || '854px';
            playerArea.style.height = savedState.height || '620px';
            playerArea.style.top = savedState.top || '';
            playerArea.style.left = savedState.left || '';
            playerArea.style.bottom = savedState.bottom || '50px';
            playerArea.style.right = savedState.right || '20px';
        } else {
            playerArea.style.width = '854px';
            playerArea.style.height = '620px';
            playerArea.style.bottom = '50px';
            playerArea.style.right = '20px';
        }
        
        addMiniControls();
        initDrag();
        initResize();
        icon.className = 'fas fa-window-maximize';
        isMiniMode = true;
        
        localStorage.setItem('miniModeEnabled', 'true');
    } else {
        saveMiniPlayerState();
        
        playerArea.classList.remove('mini-mode');
        removeMiniControls();
        removeDrag();
        removeResize();
        playerArea.style.width = '';
        playerArea.style.height = '';
        playerArea.style.top = '';
        playerArea.style.left = '';
        playerArea.style.bottom = '';
        playerArea.style.right = '';
        playerArea.style.position = '';
        icon.className = 'fas fa-window-restore';
        isMiniMode = false;
        
        localStorage.setItem('miniModeEnabled', 'false');
    }
    
    setTimeout(() => {
        updateMiniPlayerState();
    }, 100);
}

function initDrag() {
    const playerArea = document.getElementById('playerArea');
    const header = playerArea.querySelector('.player-header');
    header.style.cursor = 'grab';
    header.addEventListener('mousedown', startDrag);
}

function removeDrag() {
    const playerArea = document.getElementById('playerArea');
    const header = playerArea.querySelector('.player-header');
    header.removeEventListener('mousedown', startDrag);
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
}

function startDrag(e) {
    const playerArea = document.getElementById('playerArea');
    if (!playerArea.classList.contains('mini-mode')) return;
    if (e.target.tagName === 'BUTTON' || e.target.closest('button')) return;
    e.preventDefault();
    isDragging = true;
    playerArea.style.cursor = 'grabbing';
    playerArea.style.opacity = '0.95';
    playerArea.style.transform = 'scale(1.02)';
    playerArea.style.boxShadow = '0 15px 50px rgba(0, 0, 0, 0.7)';
    playerArea.style.transition = 'none';
    const rect = playerArea.getBoundingClientRect();
    dragOffsetX = e.clientX - rect.left;
    dragOffsetY = e.clientY - rect.top;
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
}

function onDrag(e) {
    if (!isDragging) return;
    e.preventDefault();
    const playerArea = document.getElementById('playerArea');
    let left = e.clientX - dragOffsetX;
    let top = e.clientY - dragOffsetY;
    left = Math.max(0, Math.min(left, window.innerWidth - playerArea.offsetWidth));
    top = Math.max(0, Math.min(top, window.innerHeight - playerArea.offsetHeight));
    playerArea.style.left = left + 'px';
    playerArea.style.top = top + 'px';
    playerArea.style.bottom = 'auto';
    playerArea.style.right = 'auto';
}

function stopDrag() {
    if (!isDragging) return;
    isDragging = false;
    const playerArea = document.getElementById('playerArea');
    playerArea.style.cursor = '';
    playerArea.style.opacity = '';
    playerArea.style.transform = '';
    playerArea.style.boxShadow = '';
    playerArea.style.transition = 'all 0.3s cubic-bezier(0.2, 0.9, 0.3, 1.1)';
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
}

let resizeStartLeft = 0;
let resizeStartTop = 0;
function initResize() {
    const playerArea = document.getElementById('playerArea');
    const handlePositions = ['nw', 'n', 'ne', 'w', 'e', 'sw', 's', 'se'];
    
    handlePositions.forEach(pos => {
        const handle = document.createElement('div');
        handle.className = `resize-handle ${pos}`;
        handle.addEventListener('mousedown', (e) => startResize(e, pos));
        playerArea.appendChild(handle);
    });
}

function removeResize() {
    const handles = document.querySelectorAll('.resize-handle');
    handles.forEach(handle => handle.remove());
    document.removeEventListener('mousemove', onResize);
    document.removeEventListener('mouseup', stopResize);
}

function startResize(e, direction) {
    const playerArea = document.getElementById('playerArea');
    
    e.preventDefault();
    e.stopPropagation();
    
    isResizing = true;
    resizeDirection = direction;
    
    const rect = playerArea.getBoundingClientRect();
    resizeStartX = e.clientX;
    resizeStartY = e.clientY;
    resizeStartWidth = rect.width;
    resizeStartHeight = rect.height;
    resizeStartLeft = rect.left;
    resizeStartTop = rect.top;
    
    playerArea.style.transition = 'none';
    playerArea.style.opacity = '0.9';
    
    document.addEventListener('mousemove', onResize);
    document.addEventListener('mouseup', stopResize);
}

function onResize(e) {
    if (!isResizing) return;
    
    e.preventDefault();
    
    const playerArea = document.getElementById('playerArea');
    
    const deltaX = e.clientX - resizeStartX;
    const deltaY = e.clientY - resizeStartY;
    
    let newWidth = resizeStartWidth;
    let newHeight = resizeStartHeight;
    let newLeft = resizeStartLeft;
    let newTop = resizeStartTop;
    
    switch(resizeDirection) {
        case 'ne':
            newWidth = Math.max(300, resizeStartWidth + deltaX);
            newHeight = Math.max(300, resizeStartHeight - deltaY);
            newTop = resizeStartTop + (resizeStartHeight - newHeight);
            break;
            
        case 'nw':
            newWidth = Math.max(300, resizeStartWidth - deltaX);
            newHeight = Math.max(300, resizeStartHeight - deltaY);
            newLeft = resizeStartLeft + (resizeStartWidth - newWidth);
            newTop = resizeStartTop + (resizeStartHeight - newHeight);
            break;
            
        case 'sw':
            newWidth = Math.max(300, resizeStartWidth - deltaX);
            newHeight = Math.max(300, resizeStartHeight + deltaY);
            newLeft = resizeStartLeft + (resizeStartWidth - newWidth);
            break;
            
        case 'se':
            newWidth = Math.max(300, resizeStartWidth + deltaX);
            newHeight = Math.max(300, resizeStartHeight + deltaY);
            break;
            
        case 'n':
            newHeight = Math.max(300, resizeStartHeight - deltaY);
            newTop = resizeStartTop + (resizeStartHeight - newHeight);
            break;
            
        case 's':
            newHeight = Math.max(300, resizeStartHeight + deltaY);
            break;
            
        case 'w':
            newWidth = Math.max(300, resizeStartWidth - deltaX);
            newLeft = resizeStartLeft + (resizeStartWidth - newWidth);
            break;
            
        case 'e':
            newWidth = Math.max(300, resizeStartWidth + deltaX);
            break;
    }
    
    newLeft = Math.max(0, Math.min(newLeft, window.innerWidth - newWidth));
    newTop = Math.max(0, Math.min(newTop, window.innerHeight - newHeight));
    
    playerArea.style.width = newWidth + 'px';
    playerArea.style.height = newHeight + 'px';
    playerArea.style.left = newLeft + 'px';
    playerArea.style.top = newTop + 'px';
    playerArea.style.bottom = 'auto';
    playerArea.style.right = 'auto';
}

function stopResize() {
    if (!isResizing) return;
    isResizing = false;
    const playerArea = document.getElementById('playerArea');
    playerArea.style.transition = '';
    playerArea.style.opacity = '';
    saveMiniPlayerState();
    document.removeEventListener('mousemove', onResize);
    document.removeEventListener('mouseup', stopResize);
}

function addMiniControls() {
    const playerArea = document.getElementById('playerArea');
    if (!playerArea) return;
    if (document.querySelector('.mini-controls-panel')) return;
    const controlsPanel = document.createElement('div');
    controlsPanel.className = 'mini-controls-panel';
    controlsPanel.innerHTML = `
        <div class="d-flex align-items-center justify-content-between w-100 mb-2">
            <div class="d-flex align-items-center gap-3 me-3">
                <button class="mini-control-btn" onclick="playPreviousMedia()" data-translate-tooltip="previous_track">
                    <i class="fas fa-step-backward"></i>
                </button>
                <button class="mini-control-btn mini-play-btn" onclick="togglePlayPause()" id="miniPlayPauseBtn">
                    <i class="fas fa-play"></i>
                </button>
                <button class="mini-control-btn" onclick="playNextMedia()" data-translate-tooltip="next_track">
                    <i class="fas fa-step-forward"></i>
                </button>
            </div>

            <div class="mini-thumb-badge" id="miniThumbBadge">
                <img id="miniThumbImage" src="" alt="thumb" class="mini-thumb-image" style="display: none;">
                <i id="miniThumbIcon" class="fas fa-image" style="font-size: 1.4rem; color: var(--accent-color); display: flex;"></i>
            </div>

            <div class="d-flex align-items-center gap-2 flex-grow-1 mx-3">
                <span class="mini-time text-nowrap" id="miniCurrentTime">00:00</span>
                <div class="mini-progress-bar flex-grow-1" onclick="seekMiniPlayer(event)">
                    <div class="mini-progress-fill" id="miniProgressFill" style="width: 0%"></div>
                </div>
                <span class="mini-time text-nowrap" id="miniDuration">00:00</span>
                <button class="mini-speed-btn ms-2" onclick="cyclePlaybackSpeed()" id="miniSpeedBtn" data-translate-tooltip="playback_speed">
                    <span id="miniSpeedText">1.0x</span>
                </button>
            </div>
            
            <div class="d-flex align-items-center gap-2" style="flex: 0 1 150px;">
                <i class="fas fa-volume-up mini-volume-icon" id="miniVolumeIcon" onclick="toggleMute()" style="cursor: pointer;"></i>
                <div class="mini-volume-slider flex-grow-1" onclick="setMiniVolume(event)">
                    <div class="mini-volume-fill" id="miniVolumeFill"></div>
                </div>
                <span class="mini-volume-percent" id="miniVolumePercent">100%</span>
            </div>
        </div>
    `;
    
    playerArea.appendChild(controlsPanel);

    setTimeout(() => {
        const activeMedia = getActiveMedia();
        if (activeMedia) {
            const fill = document.getElementById('miniVolumeFill');
            const percent = document.getElementById('miniVolumePercent');
            if (fill) {
                const volume = activeMedia.volume || 1;
                fill.style.width = (volume * 100) + '%';
                if (percent) percent.textContent = Math.round(volume * 100) + '%';
            }
            updateThumbnail();
        }
    }, 50);
    
    initMiniPlayerEvents();
    updateLanguage(currentLang);
}

function updateThumbnail() {
    const thumbImg = document.getElementById('miniThumbImage');
    const thumbIcon = document.getElementById('miniThumbIcon');
    if (!thumbImg || !thumbIcon) return;
    
    const filePath = currentMedia && currentMedia.path ? currentMedia.path : '';
    
    if (!filePath) {
        thumbImg.style.display = 'none';
        thumbIcon.style.display = 'flex';
        return;
    }
    
    thumbImg.style.display = 'none';
    thumbIcon.style.display = 'flex';
    
    const thumbnailUrl = `?action=video_thumbnail&path=${encodeURIComponent(filePath)}`;
    
    thumbImg.src = thumbnailUrl;
    
    thumbImg.onload = () => {
        thumbImg.style.display = 'block';
        thumbIcon.style.display = 'none';
    };
    
    thumbImg.onerror = () => {
        thumbImg.style.display = 'none';
        thumbIcon.style.display = 'flex';
    };
}

function removeMiniControls() {
    const controls = document.querySelector('.mini-controls-panel');
    if (controls) {
        controls.remove();
    }
}

function initMiniPlayerEvents() {
    const activeMedia = getActiveMedia();
    if (!activeMedia) return;
    initPlaybackSpeed();
    
    const updatePlayPause = () => {
        const btn = document.getElementById('miniPlayPauseBtn');
        if (!btn) return;
        const icon = btn.querySelector('i');
        icon.className = activeMedia.paused ? 'fas fa-play' : 'fas fa-pause';
        const playPauseText = activeMedia.paused ? 
            (translations['play'] || 'Play') : 
            (translations['pause'] || 'Pause');
        btn.setAttribute('title', playPauseText);
    };
    
    const updateVolume = () => {
        const icon = document.getElementById('miniVolumeIcon');
        const fill = document.getElementById('miniVolumeFill');
        const percent = document.getElementById('miniVolumePercent');
        if (!icon || !fill) return;
        const volume = activeMedia.volume || 0;
        const percentValue = Math.round(volume * 100);
        fill.style.width = (volume * 100) + '%';
        if (percent) percent.textContent = percentValue + '%';

        if (volume === 0) {
            icon.className = 'fas fa-volume-mute mini-volume-icon';
            if (!isMuted) isMuted = true;
        } else {
            if (volume < 0.5) {
                icon.className = 'fas fa-volume-down mini-volume-icon';
            } else {
                icon.className = 'fas fa-volume-up mini-volume-icon';
            }
            if (isMuted) isMuted = false;
        }
    };
    
    activeMedia.removeEventListener('timeupdate', updateMiniTime);
    activeMedia.removeEventListener('play', updatePlayPause);
    activeMedia.removeEventListener('pause', updatePlayPause);
    activeMedia.removeEventListener('volumechange', updateVolume);
    activeMedia.removeEventListener('loadedmetadata', updateMiniTime);
    activeMedia.removeEventListener('loadedmetadata', updateThumbnail);
    
    activeMedia.addEventListener('timeupdate', updateMiniTime);
    activeMedia.addEventListener('play', updatePlayPause);
    activeMedia.addEventListener('pause', updatePlayPause);
    activeMedia.addEventListener('volumechange', updateVolume);
    activeMedia.addEventListener('loadedmetadata', () => {
        updateMiniTime();
        updateThumbnail();
    });
    
    setTimeout(() => {
        updatePlayPause();
        updateVolume();
        updateMiniTime();
        updateThumbnail();
    }, 100);
}

function getActiveMedia() {
    const video = document.getElementById('videoPlayer');
    const audio = document.getElementById('audioPlayer');
    const image = document.getElementById('imageViewer');
    if (video.style.display === 'block') return video;
    if (audio.style.display === 'block') return audio;
    if (image.style.display === 'block') return image;
    return null;
}

function toggleMute() {
    const activeMedia = getActiveMedia();
    if (!activeMedia) return;
    
    if (isMuted) {
        activeMedia.volume = previousVolume;
        isMuted = false;
    } else {
        previousVolume = activeMedia.volume;
        activeMedia.volume = 0;
        isMuted = true;
    }
}

function setMiniVolume(event) {
    const activeMedia = getActiveMedia();
    if (!activeMedia) return;
    const volumeSlider = document.querySelector('.mini-volume-slider');
    const rect = volumeSlider.getBoundingClientRect();
    const pos = Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width));
    activeMedia.volume = pos;
    
    if (isMuted && pos > 0) {
        isMuted = false;
    }
    
    if (!isMuted) {
        previousVolume = pos;
    }
    
    const fill = document.getElementById('miniVolumeFill');
    const percent = document.getElementById('miniVolumePercent');
    if (fill) {
        const percentValue = Math.round(pos * 100);
        fill.style.width = (pos * 100) + '%';
        if (percent) percent.textContent = percentValue + '%';
    }
}

function togglePlayPause() {
    const activeMedia = getActiveMedia();
    if (!activeMedia) return;
    
    if (activeMedia.paused) {
        activeMedia.play();
    } else {
        activeMedia.pause();
    }
}

function togglePictureInPicture() {
    const videoPlayer = document.getElementById('videoPlayer');
    if (videoPlayer.style.display !== 'block') {
        return;
    }
    
    if (!document.pictureInPictureEnabled) {
        return;
    }
    
    if (document.pictureInPictureElement) {
        document.exitPictureInPicture();
    } else {
        videoPlayer.requestPictureInPicture().catch(e => {
        });
    }
}

function updateMiniPlayerState() {
    if (!isMiniMode) return;
    const activeMedia = getActiveMedia();
    if (!activeMedia) return;
    const fileName = currentMedia && currentMedia.path ? 
        currentMedia.path.split('/').pop() : 'Media Player';
    const titleSpan = document.querySelector('#playerTitle span');
    if (titleSpan) {
        titleSpan.textContent = fileName;
        titleSpan.setAttribute('title', fileName);
        titleSpan.classList.add('text-truncate');
    }
}

const speedPresets = [0.5, 0.75, 1.0, 1.25, 1.5, 2.0];
let currentSpeedIndex = 2;

function initSpeedFromStorage() {
    const savedSpeed = localStorage.getItem('playbackSpeed');
    if (savedSpeed) {
        const speed = parseFloat(savedSpeed);
        const index = speedPresets.indexOf(speed);
        if (index !== -1) {
            currentSpeedIndex = index;
        }
    }
    return speedPresets[currentSpeedIndex];
}

function cyclePlaybackSpeed() {
    const activeMedia = getActiveMedia();
    if (!activeMedia) return;
    
    currentSpeedIndex = (currentSpeedIndex + 1) % speedPresets.length;
    const newSpeed = speedPresets[currentSpeedIndex];
    
    activeMedia.playbackRate = newSpeed;
    
    localStorage.setItem('playbackSpeed', newSpeed);
    
    const speedText = document.getElementById('miniSpeedText');
    if (speedText) {
        speedText.textContent = newSpeed.toFixed(2) + 'x';
    }
    
    const speedMessage = (translations && translations['playback_speed']) || 'Playback speed';
    const message = `${speedMessage}: ${newSpeed.toFixed(2)}x`;
    logAndSpeak(message, 'info');
}

function initPlaybackSpeed() {
    const activeMedia = getActiveMedia();
    if (!activeMedia) return;
    
    const savedSpeed = localStorage.getItem('playbackSpeed');
    if (savedSpeed) {
        const speed = parseFloat(savedSpeed);
        activeMedia.playbackRate = speed;
        
        const index = speedPresets.indexOf(speed);
        if (index !== -1) {
            currentSpeedIndex = index;
        }
    } else {
        activeMedia.playbackRate = 1.0;
        currentSpeedIndex = 2;
    }
    
    const speedText = document.getElementById('miniSpeedText');
    if (speedText) {
        const currentSpeed = activeMedia.playbackRate || 1.0;
        speedText.textContent = currentSpeed.toFixed(2) + 'x';
    }
}

function updateMiniTime() {
    const activeMedia = getActiveMedia();
    if (!activeMedia) return;
    
    const currentTimeEl = document.getElementById('miniCurrentTime');
    const durationEl = document.getElementById('miniDuration');
    const progressFill = document.getElementById('miniProgressFill');
    
    if (currentTimeEl) {
        currentTimeEl.textContent = formatTime(activeMedia.currentTime);
    }
    if (durationEl) {
        durationEl.textContent = formatTime(activeMedia.duration);
    }
    if (progressFill) {
        const progress = (activeMedia.currentTime / activeMedia.duration) * 100 || 0;
        progressFill.style.width = progress + '%';
    }
}

function seekMiniPlayer(event) {
    const activeMedia = getActiveMedia();
    if (!activeMedia) return;
    
    const progressBar = event.currentTarget;
    const rect = progressBar.getBoundingClientRect();
    const pos = Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width));
    activeMedia.currentTime = pos * activeMedia.duration;
}

function formatTime(seconds) {
    if (isNaN(seconds) || !seconds || seconds === Infinity) return '00:00';
    
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    
    if (h > 0) {
        return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    } else {
        return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    }
}

window.addEventListener('beforeunload', function(e) {
    const unsavedTabs = editorTabs.filter(tab => tab.modified);
    
    if (unsavedTabs.length > 0) {
        e.preventDefault();
        e.returnValue = translations['unsaved_changes_warning'] 
            || 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
    
    editorTabs.forEach(tab => {
        if (tab.monacoEditorInstance) {
            tab.monacoEditorInstance.dispose();
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    updateRecentList();
    initHoverPlay();
    initSidebarState();
    initResizer();
    loadSavedWidths();
    startSystemMonitoring();
    initAutoPlayToggle();
    initDragAndDrop();
    initRecycleBinToggle();
    loadSavedWallpaper();
    initSpeedFromStorage();
    
    if (typeof Chart !== 'undefined') {
        startSystemMonitoring();
    } else {
        const script = document.createElement('script');
        script.src = '/luci-static/spectra/js/chart.js';
        script.onload = function() {
            startSystemMonitoring();
        };
        document.head.appendChild(script);
    }

    document.addEventListener('fullscreenchange', updateFullscreenIcon);
    document.addEventListener('webkitfullscreenchange', updateFullscreenIcon);
    document.addEventListener('mozfullscreenchange', updateFullscreenIcon);
    document.addEventListener('MSFullscreenChange', updateFullscreenIcon);
    updateFullscreenIcon();

    const playerArea = document.getElementById('playerArea');
    const playerResizer = document.getElementById('playerResizer');

    const formatButtonsGroup = document.getElementById('formatButtonsGroup');
    if (formatButtonsGroup) {
        formatButtonsGroup.addEventListener('click', function(e) {
            if (e.target.matches('button[data-format]')) {
                const buttons = this.querySelectorAll('button[data-format]');
                buttons.forEach(btn => btn.classList.remove('active'));
                e.target.classList.add('active');
                
                const format = e.target.getAttribute('data-format');
                document.getElementById('archiveExtension').textContent = '.' + format;
            }
        });
    }
    
    const uploadModal = document.getElementById('uploadModal');
    if (uploadModal) {
        uploadModal.addEventListener('show.bs.modal', function() {
            uploadFilesList = [];
            
            const fileList = document.getElementById('fileList');
            if (fileList) {
                fileList.innerHTML = '';
            }
            
            const fileListCard = document.getElementById('fileListCard');
            if (fileListCard) {
                fileListCard.style.display = 'none';
            }
            
            const fileInput = document.getElementById('fileUploadInput');
            if (fileInput) {
                fileInput.value = '';
            }
        });
    }

    const miniModeEnabled = localStorage.getItem('miniModeEnabled');
    if (miniModeEnabled === 'true') {
    }
    
    setTimeout(() => {
        initDragSelect();

        if (document.getElementById('fileGrid')) {
            loadPlaylistCache();
        }
    }, 500);

    const savedTheme = localStorage.getItem('editorTheme');
    if (!savedTheme) {
        localStorage.setItem('editorTheme', 'vs-dark');
    }
    initEventListeners();

    currentView = 'files';
    
    if (editorTabs && editorTabs.length > 0) {
        toggleView('editor');
    }
    
    const toolbar = document.querySelector('.toolbar');
    if (toolbar) {
        const selectAllDiv = document.createElement('div');
        selectAllDiv.className = 'selection-controls';
        selectAllDiv.innerHTML = `
            <div class="select-all-checkbox">
                <input type="checkbox" class="form-check-input mt-1" id="selectAllCheckbox" onchange="toggleSelectAll()">
                <label for="selectAllCheckbox" data-translate="selectAll">Select All</label>
            </div>
        `;
        toolbar.appendChild(selectAllDiv);
    }
    
    const fileGridHeader = document.querySelector('.file-grid-header');
    if (fileGridHeader) {
        const selectionInfo = document.createElement('div');
        selectionInfo.id = 'selectionInfo';
        selectionInfo.className = 'file-selection-info';
        
        selectionInfo.innerHTML = `
            <span id="selectedCount"></span>
            <div class="selection-actions">
                <button class="btn btn-teal" onclick="clearSelection()" style="padding: 6px 10px; font-size: 0.8rem;">
                    <i class="fas fa-times"></i>
                    <span data-translate="clear">Clear</span>
                </button>
            </div>
        `;
        
        fileGridHeader.parentNode.insertBefore(selectionInfo, fileGridHeader.nextSibling);
    }
    
    if (document.getElementById('fileGrid')) {
        loadFiles('/');
    }

    const events = [
        'resize', 'scroll', 'load', 'loadedmetadata',
        'playing', 'fullscreenchange'
    ];
    
    events.forEach(event => {
        window.addEventListener(event, adjustNavButtons);
    });
    
    setInterval(adjustNavButtons, 1000);
});

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
            logAndSpeak(msg);
        })
        .catch(error => {
            const errMsg = translations['request_failed'] || ("Request failed: " + error.message);
            logAndSpeak(errMsg);
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const playlistModalEl = document.getElementById('playlistModal');
    let hoverVideoTimer = null;
    let currentHoverCard = null;

    playlistModalEl.addEventListener('show.bs.modal', async () => {
        try {
            const playlistContainer = document.getElementById('playlistItems');
            const playlistCount = document.getElementById('playlistCount');
            
            playlistContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading playlist...</p>
                </div>
            `;
            playlistCount.textContent = 'Loading...';
            
            const res = await fetch(`./lib/playlist_cache.json?t=${Date.now()}`);
            const data = await res.json();

            const playlist = Object.values(data)[0] || [];

            playlistContainer.innerHTML = '';
            playlistContainer.className = 'row g-3 p-3';
            
            playlist.forEach((file, index) => {
                const fileName = file.split('/').pop();
                const extIndex = fileName.lastIndexOf('.');
                const nameWithoutExt = extIndex !== -1 ? fileName.substring(0, extIndex) : fileName;
                const fileExt = fileName.substring(extIndex + 1).toLowerCase();
                const isVideo = ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', '3gp', '3g2', 'ogv', 'mpg', 'mpeg', 'm4v', 
                                 'ts', 'm2ts', 'mts', 'vob', 'rm', 'rmvb', 'divx', 'xvid', 'f4v', 'avchd', 'mxf',
                                 'vp8', 'vp9', 'av1', 'hevc', 'h264', 'h265'].includes(fileExt);
    
                const isImage = ['jpg', 'jpeg', 'jpe', 'jfif', 'png', 'gif', 'bmp', 'webp', 'svg', 'svgz', 'ico', 'cur',
                                 'tiff', 'tif', 'psd', 'heic', 'heif', 'avif'].includes(fileExt);
    
                const isAudio = ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 'wma', 'opus', 'mp2', 'ac3', 'dts', 'eac3',
                                 'ape', 'wv', 'tta', 'tak', 'dsf', 'dff', 'sacd', 'mid', 'midi', 'amr',
                                 'aiff', 'aif', 'caf', 'm4r', 'xmf'].includes(fileExt);
    
                let iconClass = 'fa-file';
                let iconColor = '#757575';
    
                if (isAudio) {
                    iconClass = 'fa-music';
                    iconColor = '#9C27B0';
                } else if (isVideo) {
                    iconClass = 'fa-video';
                    iconColor = '#2196F3';
                } else if (isImage) {
                    iconClass = 'fa-image';
                    iconColor = '#4CAF50';
                }
                
                const col = document.createElement('div');
                col.className = 'col-6 col-md-4 col-lg-3';

                const card = document.createElement('div');
                card.className = 'card h-100 playlist-card video-thumb-card';
                card.style.cursor = 'pointer';
                card.style.transition = 'all 0.3s ease';
                card.style.position = 'relative';
                card.style.overflow = 'hidden';
                card.setAttribute('data-path', file);
                card.setAttribute('data-index', index);
                card.setAttribute('data-ext', fileExt);
                card.setAttribute('data-is-video', isVideo);
                card.setAttribute('title', fileName);

                let thumbnailHtml = '';

                if (isVideo) {
                    const thumbnailUrl = `?action=video_thumbnail&path=${encodeURIComponent(file)}&t=${Date.now()}`;
                    const duration = '--:--';

                    thumbnailHtml = `
                        <div class="video-thumb-container hover-video-parent"
                             style="width: 100%; height: 150px; background: #000; position: relative;">
                            <img class="video-thumb-img hover-thumb-img"
                                 src="${thumbnailUrl}"
                                 alt="${escapeHtml(fileName)}"
                                 style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                 onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas ${iconClass}\\' style=\\'font-size: 3rem; color: ${iconColor}; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);\\'></i>';">
                            <div class="play-icon-overlay"
                                 style="position: absolute; top: 50%; left: 50%;
                                        transform: translate(-50%, -50%);
                                        opacity: 0; transition: opacity 0.3s;">
                                 <i class="fas fa-play-circle"
                                     style="font-size: 3rem;
                                         color: rgba(255,255,255,0.9);
                                         filter: drop-shadow(0 2px 5px rgba(0,0,0,0.3));">
                                </i>
                            </div>

                            <div class="duration-badge"
                                 style="position: absolute; bottom: 5px; right: 5px;
                                        background: rgba(0,0,0,0.7);
                                        color: white; padding: 2px 6px;
                                        border-radius: 4px; font-size: 0.8rem;">
                                <i class="fas fa-clock"></i> ${duration}
                            </div>
                        </div>
                    `;
                } else if (isImage) {
                    const imageUrl = `?preview=1&path=${encodeURIComponent(file)}`;
                    thumbnailHtml = `
                        <div class="image-thumb-container"
                             style="width: 100%; height: 150px; background: #f0f0f0; position: relative; overflow: hidden;">
                            <img class="image-thumb-img"
                                 src="${imageUrl}"
                                 alt="${escapeHtml(fileName)}"
                                 style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                 loading="lazy"
                                 onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas ${iconClass}\\' style=\\'font-size: 3rem; color: ${iconColor}; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);\\'></i>';">
                            <div class="play-icon-overlay"
                                 style="position: absolute; top: 50%; left: 50%;
                                        transform: translate(-50%, -50%);
                                        opacity: 0; transition: opacity 0.3s;">
                                <i class="fas fa-search-plus"
                                   style="font-size: 2.5rem;
                                          color: rgba(255,255,255,0.9);
                                          filter: drop-shadow(0 2px 5px rgba(0,0,0,0.3));">
                                </i>
                            </div>
                        </div>
                    `;
                } else if (isAudio) {
                    const duration = '--:--';
                    thumbnailHtml = `
                        <div class="default-thumb"
                             style="height: 150px; display: flex;
                                    align-items: center; justify-content: center;
                                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); position: relative;">
                            <i class="fas ${iconClass} fa-3x"
                               style="color: white;"></i>
                            <div class="duration-badge"
                                 style="position: absolute; bottom: 5px; right: 5px;
                                        background: rgba(0,0,0,0.7);
                                        color: white; padding: 2px 6px;
                                        border-radius: 4px; font-size: 0.8rem;">
                                <i class="fas fa-clock"></i> ${duration}
                            </div>
                        </div>
                    `;
                } else {
                    thumbnailHtml = `
                        <div class="default-thumb"
                             style="height: 150px; display: flex;
                                    align-items: center; justify-content: center;
                                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas ${iconClass} fa-3x"
                               style="color: white;"></i>
                        </div>
                    `;
                }

                card.innerHTML = `
                    <div class="card-body p-0">
                        ${thumbnailHtml}

                        <div class="video-title-overlay"
                             style="position: absolute; bottom: 0; left: 0; right: 0;
                                    background: linear-gradient(transparent, rgba(0,0,0,0.8));
                                    padding: 20px 10px 10px 10px;
                                    opacity: 0; transition: opacity 0.3s;">

                            <div class="text-white small text-truncate"
                                 style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5);"
                                 data-translate-tooltip="${escapeHtml(nameWithoutExt)}">
                                ${truncateFileName(nameWithoutExt, 20)}
                            </div>

                            <div class="d-flex justify-content-between mt-1">
                                <span class="badge bg-primary"
                                      style="font-size: 0.7rem;">
                                    ${fileExt.toUpperCase()}
                                </span>
                            </div>
                        </div>

                        <div class="position-absolute top-0 start-0 m-2"
                             style="z-index: 2;">
                            <span class="badge bg-secondary">
                                ${index + 1}
                            </span>
                        </div>
                    </div>
                `;

                if (isVideo) {
                    let hoverVideo = null;
                    let hoverTimer = null;

                    card.addEventListener('mouseenter', () => {
                        if (currentHoverCard && currentHoverCard !== card) {
                            stopHoverVideo(currentHoverCard);
                        }
                        
                        currentHoverCard = card;
                        
                        hoverTimer = setTimeout(() => {
                            const thumbContainer = card.querySelector('.video-thumb-container');
                            const thumbImg = card.querySelector('.hover-thumb-img');
                            
                            if (thumbContainer && thumbImg) {
                                thumbImg.style.opacity = '0';
                                
                                hoverVideo = document.createElement('video');
                                hoverVideo.src = `?preview=1&path=${encodeURIComponent(file)}`;
                                hoverVideo.muted = true;
                                hoverVideo.loop = true;
                                hoverVideo.playsInline = true;
                                hoverVideo.style.cssText = `
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                    object-fit: cover;
                                    z-index: 10;
                                `;
                
                                const playPromise = hoverVideo.play();
                                if (playPromise !== undefined) {
                                    playPromise.catch(e => {
                                        if (thumbImg) thumbImg.style.opacity = '1';
                                    });
                                }
                                
                                thumbContainer.appendChild(hoverVideo);
                            }
                        }, 500);
                    });

                    card.addEventListener('mouseleave', () => {
                        if (hoverTimer) {
                            clearTimeout(hoverTimer);
                        }
                        
                        if (hoverVideo) {
                            hoverVideo.pause();
                            hoverVideo.currentTime = 0;
                            hoverVideo.remove();
                            hoverVideo = null;
                        }
                        
                        const thumbImg = card.querySelector('.hover-thumb-img');
                        if (thumbImg) {
                            thumbImg.style.opacity = '1';
                        }
                        
                        if (currentHoverCard === card) {
                            currentHoverCard = null;
                        }
                    });
                }

                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px)';
                    card.style.boxShadow = '0 10px 20px rgba(0,0,0,0.3)';

                    const titleOverlay = card.querySelector('.video-title-overlay');
                    const playIcon = card.querySelector('.play-icon-overlay');

                    if (titleOverlay) titleOverlay.style.opacity = '1';
                    if (playIcon) playIcon.style.opacity = '1';
                });

                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                    card.style.boxShadow = 'none';

                    const titleOverlay = card.querySelector('.video-title-overlay');
                    const playIcon = card.querySelector('.play-icon-overlay');

                    if (titleOverlay) titleOverlay.style.opacity = '0';
                    if (playIcon) playIcon.style.opacity = '0';
                });

                card.addEventListener('click', () => {
                    currentMediaList = playlist;
                    playMedia(file);
                });

                getMediaDuration(file).then(duration => {
                    const durationBadge = card.querySelector('.duration-badge');
                    if (durationBadge) {
                        durationBadge.innerHTML = `<i class="fas fa-clock"></i> ${duration}`;
                    }
                });

                col.appendChild(card);
                playlistContainer.appendChild(col);
            });

            playlistCount.textContent = playlist.length + ' ' + (translations['files'] || 'Files');
            
            setTimeout(highlightCurrentPlaylistCard, 200);

        } catch (err) {
            console.error('Failed to load playlist:', err);
            
            const playlistContainer = document.getElementById('playlistItems');
            const playlistCount = document.getElementById('playlistCount');
            
            playlistContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-exclamation-circle text-danger fa-3x mb-3"></i>
                    <p class="text-danger">Failed to load playlist</p>
                </div>
            `;
            playlistCount.textContent = '0 Files';
        }
    });

    function stopHoverVideo(card) {
        if (!card) return;
        
        const existingVideo = card.querySelector('video');
        if (existingVideo) {
            existingVideo.pause();
            existingVideo.currentTime = 0;
            existingVideo.remove();
        }
        
        const thumbImg = card.querySelector('.hover-thumb-img');
        if (thumbImg) {
            thumbImg.style.opacity = '1';
        }
    }

    const audioPlayer = document.getElementById('audioPlayer');
    const videoPlayer = document.getElementById('videoPlayer');

    if (audioPlayer) {
        audioPlayer.addEventListener('ended', function() {
            if (autoNextEnabled && currentPlaylist && currentPlaylist.length > 0 && currentMedia && currentMedia.path) {
                const currentIndex = currentMediaList.indexOf(currentMedia.path);
                if (currentIndex !== -1 && currentIndex < currentMediaList.length - 1) {
                    playMedia(currentMediaList[currentIndex + 1]);
                }
            }
        });
    }

    if (videoPlayer) {
        videoPlayer.addEventListener('ended', function() {
            if (autoNextEnabled && currentPlaylist && currentPlaylist.length > 0 && currentMedia && currentMedia.path) {
                const currentIndex = currentMediaList.indexOf(currentMedia.path);
                if (currentIndex !== -1 && currentIndex < currentMediaList.length - 1) {
                    playMedia(currentMediaList[currentIndex + 1]);
                }
            }
        });
    }
});

async function getMediaDuration(mediaPath) {
    try {
        const response = await fetch(`?action=get_file_info&path=${encodeURIComponent(mediaPath)}`);
        const data = await response.json();
        
        if (data.success && data.info && data.info.media_info && data.info.media_info.duration) {
            let duration = data.info.media_info.duration;
            
            if (duration.startsWith('00:')) {
                duration = duration.substring(3);
            }
            
            return duration;
        }
        
        return '--:--';
    } catch (error) {
        logAndSpeak('Failed to get media duration:', error);
        return '--:--';
    }
}

function truncateFileName(name, maxLength = 15) {
    if (name.length <= maxLength) return name;
    const extIndex = name.lastIndexOf('.');
    if (extIndex === -1) {
        return name.substring(0, maxLength - 3) + '...';
    }
    const nameWithoutExt = name.substring(0, extIndex);
    const ext = name.substring(extIndex);
    if (nameWithoutExt.length <= maxLength - 3) {
        return name;
    }
    return nameWithoutExt.substring(0, maxLength - 3 - ext.length) + '...' + ext;
}

function highlightCurrentPlaylistCard() {
    const playlistCards = document.querySelectorAll('.playlist-card');
    if (!playlistCards.length || !currentMedia || !currentMedia.path) return;
    
    playlistCards.forEach(card => {
        card.classList.remove('playing');
        
        if (card.getAttribute('data-path') === currentMedia.path) {
            card.classList.add('playing');
            
            setTimeout(() => {
                card.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'nearest'
                });
            }, 100);
        }
    });
}

document.addEventListener('play', function(e) {
    if (e.target.id === 'audioPlayer' || e.target.id === 'videoPlayer') {
        setTimeout(highlightCurrentPlaylistCard, 100);
    }
}, true);

document.addEventListener('keydown', function(event) {
    const target = event.target;
    const isTyping = target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable;
    
    if (isTyping) return;
    
    const isMonacoEditor = target.closest('.monaco-editor');
    if (isMonacoEditor && (event.code === 'Space' || event.code === 'KeyF' || event.code === 'Escape')) {
        return;
    }
    
    switch(event.code) {
        case 'Space':
            event.preventDefault();
            const audioPlayer = document.getElementById('audioPlayer');
            const videoPlayer = document.getElementById('videoPlayer');
            
            if (audioPlayer && audioPlayer.style.display === 'block') {
                if (audioPlayer.paused) audioPlayer.play();
                else audioPlayer.pause();
            } else if (videoPlayer && videoPlayer.style.display === 'block') {
                if (videoPlayer.paused) videoPlayer.play();
                else videoPlayer.pause();
            }
            break;
            
        case 'Escape':
            const fileContextMenu = document.getElementById('fileContextMenu');
            const contextMenuOverlay = document.getElementById('contextMenuOverlay');
            
            if (fileContextMenu && fileContextMenu.style.display === 'block') {
                hideFileContextMenu();
                event.preventDefault();
            } else if (contextMenuOverlay && contextMenuOverlay.style.display === 'block') {
                hideFileContextMenu();
                event.preventDefault();
            }
            else if (document.getElementById('playerArea')?.classList.contains('active')) {
                closePlayer();
                event.preventDefault();
            }
            else if (document.getElementById('filePropertiesModal')?.classList.contains('show')) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('filePropertiesModal'));
                if (modal) modal.hide();
                event.preventDefault();
            }
            break;
            
        case 'KeyF':
            event.preventDefault();
            toggleFullscreen();
            break;

        case 'ArrowUp':
            event.preventDefault();
            const playlistBtn = document.querySelector('[data-bs-target="#playlistModal"]');
            if (playlistBtn) {
                playlistBtn.click();
            }
           break;
            
        case 'ArrowRight':
            event.preventDefault();
            playNextMedia();
            break;
            
        case 'ArrowLeft':
            event.preventDefault();
            playPreviousMedia();
            break;

        case 'KeyA':
            if (event.altKey) {
                event.preventDefault();
                toggleAutoNext();
            }
            break;                     
            
        case 'KeyF':
            if (event.ctrlKey || event.metaKey) {
                event.preventDefault();
                const searchInput = document.getElementById('searchInput');
                const searchModal = document.getElementById('searchModal');
                
                if (searchInput) {
                    if (searchModal) {
                        const modal = new bootstrap.Modal(searchModal);
                        modal.show();
                    }
                    setTimeout(() => {
                        searchInput.focus();
                        searchInput.select();
                    }, 100);
                }
            }
            break;
            
        case 'KeyS':
            if ((event.ctrlKey || event.metaKey) && activeEditorTab) {
                event.preventDefault();
                saveEditorContent(activeEditorTab);
            }
            break;
            
        case 'Enter':
            if ((event.ctrlKey || event.metaKey) && document.getElementById('searchInput')?.value.trim()) {
                event.preventDefault();
                searchFiles();
            }
            break;
            
        case 'KeyF':
            if ((event.ctrlKey || event.metaKey) && event.shiftKey && activeEditorTab) {
                event.preventDefault();
                const tab = editorTabs.find(t => t.id === activeEditorTab);
                if (tab && tab.editorMode === 'advanced') {
                    formatCode(activeEditorTab);
                }
            }
            break;

        case 'F7':
            event.preventDefault();
            togglePlayerFitMode();
            break;
            
        case 'F5':
            event.preventDefault();
            refreshFiles();
            break;
            
        case 'Delete':
            if (selectedFiles.size > 0) {
                event.preventDefault();
                deleteSelected();
            }
            break;
            
        case 'KeyE':
            if (event.ctrlKey || event.metaKey) {
                event.preventDefault();
                toggleView();
            }
            break;
            
        case 'KeyE':
            if ((event.ctrlKey || event.metaKey) && event.shiftKey) {
                event.preventDefault();
                toggleView('editor');
            }
            break;
            
        case 'KeyF':
            if ((event.ctrlKey || event.metaKey) && event.shiftKey) {
                event.preventDefault();
                toggleView('files');
            }
            break;
            
        case 'KeyW':
            if ((event.ctrlKey || event.metaKey) && activeEditorTab) {
                event.preventDefault();
                closeEditorTab(activeEditorTab);
            }
            break;
            
        case 'KeyW':
            if ((event.ctrlKey || event.metaKey) && event.shiftKey && editorTabs.length > 0) {
                event.preventDefault();
                closeAllEditorTabs();
            }
            break;
    }
});
</script>
