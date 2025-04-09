document.addEventListener("DOMContentLoaded", function () {
    let isEnabled = localStorage.getItem('backgroundEnabled') !== 'false';
    let videoTag, bgImages, bgIndex, availableImages, switchInterval;

    const ipContainer = document.querySelector('.ip-container');
    const flag = document.getElementById('flag');
    const ipText = document.querySelector('.ip-text');

    const dIp = document.getElementById('d-ip');
    if (dIp) {
        dIp.style.cssText = `
            color: #09B63F !important;
            font-weight: bold;
            font-size: 15px;
            position: relative;
            left: 1em; 
            margin-bottom: 3px;
            text-indent: -0.7ch; 
        `;
    }

    const ipip = document.getElementById('ipip');
    if (ipip) {
        ipip.style.cssText = `
            color: #FF00FF !important;
            font-weight: bold;
            font-size: 15px;
            display: block;
            margin-top: 3px;
            margin-bottom: 3px;
        `;
    }

    if (ipContainer) {
        ipContainer.style.cssText = `
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
            padding: 15px 20px;
            min-width: 300px;
            transition: all 0.3s ease;
        `;
        
        const shouldHide = localStorage.getItem('hideIP') === 'true';
        ipContainer.style.display = shouldHide ? 'none' : 'flex';
    }

    if (flag) {
        flag.style.cssText = `
            width: 60px;
            height: 40px;
            margin-right: 25px;
            margin-left: 15px;
            flex-shrink: 0;
        `;
    }

    if (ipText) {
        ipText.style.cssText = `
            font-family: Arial, sans-serif;
            line-height: 1.4;
            min-width: 180px;
            transform: translateY(1px); 
        `;
    }

    function getFitButtonText() {
        const savedFit = localStorage.getItem('videoObjectFit') || 'cover';
        const texts = {
            'contain': '正常比例',
            'fill': '拉伸填充', 
            'none': '原始尺寸',
            'scale-down': '智能适应',
            'cover': '默认裁剪'
        };
        return texts[savedFit] || '默认裁剪';
    }

    function updateThemeButton(mode) {
        const btn = document.getElementById('theme-toggle');
        const status = document.getElementById('theme-status');
        if (!btn || !status) return;

        if (mode === "dark") {
            btn.innerHTML = '<i class="bi bi-sun"></i> 切换到亮色模式';
            btn.className = "btn btn-primary light";
            status.innerText = "当前主题: 暗色模式";
        } else {
            btn.innerHTML = '<i class="bi bi-moon"></i> 切换到暗色模式';
            btn.className = "btn btn-primary dark";
            status.innerText = "当前主题: 亮色模式";
        }
    }

    const controlPanel = `
        <div id="settings-icon">⚙️</div>
        <div id="mode-popup">
            <button id="theme-toggle" style="opacity:1 !important;pointer-events:auto !important;background:#2196F3 !important">
                <i class="bi bi-moon"></i> 切换主题模式
                <div id="theme-status" style="margin-left:8px;color:#FFEB3B"></div>
            </button>
            <button id="master-switch">
                <span>${isEnabled ? '已启用 ✅' : '已禁用 ❌'}</span>
                <div class="status-led" style="background:${isEnabled ? '#4CAF50' : '#f44336'}"></div>
            </button>
            <button class="theme-settings-btn">主题设置</button>
            <button data-mode="video">视频模式</button>
            <button data-mode="image">图片模式</button>
            <button data-mode="solid">暗黑模式</button>
            <button data-mode="auto">自动模式</button>
            <button class="sound-toggle">
                <span>背景音效</span>
                <div>${localStorage.getItem('videoMuted') === 'true' ? '🔇' : '🔊'}</div>
            </button>
            <button class="object-fit-btn" style="opacity:1 !important;pointer-events:auto !important">
                <span>显示比例：</span>
                <div>${getFitButtonText()}</div>
            </button>
            <button class="ip-toggle">
                <span>${localStorage.getItem('hideIP') === 'true' ? '显示IP信息' : '隐藏IP信息'}</span>
                <div class="status-led" style="background:${localStorage.getItem('hideIP') !== 'true' ? '#4CAF50' : '#f44336'}"></div>
            </button>
            <button class="info-btn">使用说明</button>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', controlPanel);
    document.querySelector('.theme-settings-btn')?.addEventListener('click', showThemeSettings);
    if (localStorage.getItem('hideIP') === null) {
        localStorage.setItem('hideIP', 'false');
    }

    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('.ip-toggle');
        if (toggleBtn && ipContainer) {
            const currentState = localStorage.getItem('hideIP') === 'true';
            const newState = !currentState;
            
            ipContainer.style.display = newState ? 'none' : 'flex';
            localStorage.setItem('hideIP', newState);
            
            toggleBtn.querySelector('span').textContent = newState ? '显示IP信息' : '隐藏IP信息';
            toggleBtn.querySelector('.status-led').style.background = newState ? '#f44336' : '#4CAF50';
        }
    });

    const styles = `
        #settings-icon {
            position: fixed;
            right: 2%;
            bottom: 20px;
            cursor: pointer;
            z-index: 1001;
            font-size: 24px;
            background: rgba(0,0,0,0.5);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: transform 0.3s ease;
        }

        #mode-popup button.sound-toggle {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #redirect-btn,
        .info-btn {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #settings-icon:hover {
            transform: rotate(90deg);
        }

        #mode-popup {
            position: fixed;
            right: 20px;
            top: 70px;
            background: rgba(0,0,0,0.9);
            border-radius: 5px;
            padding: 10px;
            color: white;
            z-index: 1002;
            display: none;
            min-width: 150px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #mode-popup.show {
            display: block;
            opacity: 1;
        }

        #mode-popup button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 8px;
            margin: 4px 0;
            border: none;
            color: white;
            border-radius: 3px;
            cursor: pointer;
            background: #444;
            opacity: ${isEnabled ? 1 : 0.5};
            pointer-events: ${isEnabled ? 'auto' : 'none'};
            transition: background 0.3s ease, transform 0.3s ease;
        }

        #mode-popup button:hover {
            background: #555;
            transform: scale(1.1);
        }

        #master-switch {
            background: ${isEnabled ? '#4CAF50' : '#f44336'} !important;
            pointer-events: auto !important;
            opacity: 1 !important;
        }

        .status-led {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            box-shadow: 0 0 5px ${isEnabled ? '#4CAF50' : '#f44336'};
        }

        .selected-mode {
            background: #007BFF !important;
        }

        #mode-popup button.object-fit-btn {
            opacity: 1 !important;
            pointer-events: auto !important;
            background: #007BFF !important;
        }
        #mode-popup button.object-fit-btn div {
            color: #FFEB3B;
            margin-left: 8px;
            font-weight: bold;
        }

        #mode-popup button.ip-toggle {
            opacity: 1 !important;      
            pointer-events: auto !important;  
            background: #2196F3 !important;  
        }

        #mode-popup button.theme-settings-btn {
            background: #9C27B0 !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        @media (max-width: 600px) {
            #settings-icon {
                right: 8%;
                bottom: 10px;
                width: 30px;
                height: 30px;
                font-size: 18px;
            }
            #mode-popup {
                right: 10px;
                top: 50px;
                min-width: 120px;
            }
            #mode-popup button {
                padding: 6px;
                font-size: 12px;
            }
        }
    `;

    const styleTag = document.createElement('style');
    styleTag.textContent = styles;
    document.head.appendChild(styleTag);

    document.getElementById('settings-icon').addEventListener('click', function(e) {
        e.stopPropagation();
        const popup = document.getElementById('mode-popup');
        popup.classList.toggle('show');
    });

    document.getElementById('master-switch').addEventListener('click', function(e) {
        e.stopPropagation();
        isEnabled = !isEnabled;
        localStorage.setItem('backgroundEnabled', isEnabled);
        
        this.style.background = isEnabled ? '#4CAF50' : '#f44336';
        this.querySelector('.status-led').style.boxShadow = `0 0 5px ${isEnabled ? '#4CAF50' : '#f44336'}`;
        this.querySelector('span').textContent = isEnabled ? '已启用' : '已禁用';

        document.querySelectorAll('#mode-popup button:not(#master-switch):not(.sound-toggle):not(#redirect-btn):not(.info-btn)').forEach(btn => {
            btn.style.opacity = isEnabled ? 1 : 0.5;
            btn.style.pointerEvents = isEnabled ? 'auto' : 'none';
        });

        if (isEnabled) {
            initBackgroundSystem();
        } else {
            clearBackgroundSystem();
        }
    });

    document.querySelectorAll('[data-mode]').forEach(btn => {
        btn.addEventListener('click', function() {
            setMode(btn.dataset.mode);
            document.querySelectorAll('[data-mode]').forEach(b => b.classList.remove('selected-mode'));
            this.classList.add('selected-mode');
        });
    });

    document.querySelector('.sound-toggle').addEventListener('click', function() {
        const newMuted = localStorage.getItem('videoMuted') !== 'true';
        localStorage.setItem('videoMuted', newMuted);
        
        this.querySelector('div').textContent = newMuted ? '🔇' : '🔊';
        
        if (videoTag) {
            videoTag.muted = newMuted;
        }
    });

    document.getElementById('theme-toggle')?.addEventListener('click', function(e) {
        e.stopPropagation();
        fetch("/luci-static/spectra/bgm/theme-switcher.php", { method: "POST" })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateThemeButton(data.mode);
                } else {
                    console.error("模式切换失败:", data.error);
                }
            })
            .catch(error => {
                console.error("请求出错:", error);
            });
    });

    document.addEventListener("DOMContentLoaded", () => {
        fetch("/luci-static/spectra/bgm/theme-switcher.php")
            .then(res => res.json())
            .then(data => {
                updateThemeButton(data.mode);
            })
            .catch(error => {
                console.error("获取主题模式失败:", error);
            });
    });

    document.querySelector('.info-btn').addEventListener('click', () => {
        showCustomAlert('使用说明', [
            '1. 视频模式：默认名称为「bg.mp4」',
            '2. 图片模式：默认名称为「bg1-20.jpg」',
            '3. 暗黑模式：透明背景+光谱动画',
            '4. 亮色模式：主题设置进行切换，关闭控制开关',
            '5. 主题设置：支持自定义背景，需关闭开关，模式切换需清除背景',
            '6. 项目地址：<a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">点击访问</a>'
        ]);
    });

    function showCustomAlert(title, messages) {
        const existingAlert = document.getElementById('custom-alert');
        if (existingAlert) existingAlert.remove();

        const alertHTML = `
            <div id="custom-alert-overlay">
                <div id="custom-alert">
                    <div class="alert-header">
                        <h3>${title}</h3>
                        <button class="close-btn">&times;</button>
                    </div>
                    <div class="alert-content">
                        ${messages.map(msg => `<p>${msg}</p>`).join('')}
                    </div>
                </div>
            </div>
        `;

    document.body.insertAdjacentHTML('beforeend', alertHTML);

    const style = document.createElement('style');
    style.textContent = `
        #custom-alert-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(3px);
        }

        #custom-alert {
            background: rgba(0,0,0,0.95);
            border: 1px solid #333;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            color: #fff;
        }

        .alert-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .alert-header h3 {
            margin: 0;
            color: #4CAF50;
            font-size: 1.3em;
        }

        .close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            padding: 0 8px;
            transition: color 0.3s;
        }

        .close-btn:hover {
            color: #4CAF50;
        }

        .alert-content {
            max-height: 60vh;
            overflow-y: auto;
        }

        .alert-content p {
            line-height: 1.6;
            margin: 10px 0;
            color: #ddd;
            font-size: 14px;
        }

        @media (max-width: 480px) {
            #custom-alert {
                width: 95%;
                padding: 15px;
            }
            
            .alert-header h3 {
                font-size: 1.1em;
            }
            
            .alert-content p {
                font-size: 13px;
            }
        }
    `;
    document.head.appendChild(style);

    document.querySelector('.close-btn').addEventListener('click', () => {
        document.getElementById('custom-alert-overlay').remove();
        style.remove();
    });

    document.getElementById('custom-alert-overlay').addEventListener('click', (e) => {
        if (e.target.id === 'custom-alert-overlay') {
            document.getElementById('custom-alert-overlay').remove();
            style.remove();
        }
    });
}

    function initBackgroundSystem() {
        bgImages = Array.from({length: 20}, (_, i) => `bg${i + 1}.jpg`);
        bgIndex = 0;
        availableImages = [];
        
        const savedMode = localStorage.getItem('backgroundMode') || 'auto';
        setMode(savedMode);
    }

    function clearBackgroundSystem() {
        if (videoTag) {
            videoTag.remove();
        }
        document.querySelectorAll('#dynamic-style, #video-style').forEach(e => e.remove());
        clearInterval(switchInterval);
        document.body.style.background = '';
    }

    function setMode(mode) {
        if (!isEnabled) return;
        localStorage.setItem('backgroundMode', mode);
        clearExistingBackground();

        switch (mode) {
            case 'video':
                checkFileExists('bg.mp4', exists => exists ? initVideoBackground() : fallbackToAuto());
                break;
            case 'image':
                checkImages(() => availableImages.length > 0 ? initImageBackground() : fallbackToAuto());
                break;
            case 'solid':
                applyCSS(null);
                break;
            case 'auto':
                initAutoBackground();
                break;
        }
    }

    function clearExistingBackground() {
        if (videoTag) {
            videoTag.remove();
            videoTag = null;
        }
        document.querySelectorAll('#dynamic-style, #video-style').forEach(e => e.remove());
        clearInterval(switchInterval);
    }

    function initVideoBackground() {
        insertVideoBackground();
        updateSoundToggle();
    }

    function initImageBackground() {
        bgIndex = Math.floor(Math.random() * availableImages.length);
        applyCSS(availableImages[bgIndex]);
        switchInterval = setInterval(switchBackground, 120000);
    }

    function initAutoBackground() {
        checkFileExists('bg.mp4', exists => {
            if (exists) {
                initVideoBackground();
            } else {
                checkImages(() => {
                    availableImages.length > 0 ? initImageBackground() : applyCSS(null);
                });
            }
        });
    }

    function fallbackToAuto() {
        console.log('资源不存在，已切换至自动模式');
        localStorage.setItem('backgroundMode', 'auto');
        initAutoBackground();
    }

    function checkImages(callback) {
        availableImages = [];
        let checked = 0;
        bgImages.forEach(image => {
            checkFileExists(image, exists => {
                checked++;
                if (exists) availableImages.push(image);
                if (checked === bgImages.length) callback();
            });
        });
    }

    function checkFileExists(file, callback) {
        const xhr = new XMLHttpRequest();
        xhr.open('HEAD', `/luci-static/spectra/bgm/${file}`);
        xhr.onreadystatechange = () => xhr.readyState === 4 && callback(xhr.status === 200);
        xhr.send();
    }

    function applyCSS(image) {
        let styleTag = document.querySelector("#dynamic-style");
        if (!styleTag) {
            styleTag = document.createElement("style");
            styleTag.id = "dynamic-style";
            document.head.appendChild(styleTag);
        }

        if (image) {
            styleTag.innerHTML = `
                body {
                    background: url('/luci-static/spectra/bgm/${image}') no-repeat center center fixed !important;
                    background-size: cover !important;
                    transition: background 1s ease-in-out;
                }
                .wrapper span {
                    display: none !important;
                }
            `;
        } else {
            styleTag.innerHTML = `
                body {
                    background: #111 !important;
                    background-image: none !important;
                    background-size: auto !important;
                }
                .wrapper span {
                    display: block !important;
                }
            `;
        }
    }

    document.querySelector('.object-fit-btn')?.addEventListener('click', function() {
        const videos = document.querySelectorAll('video#background-video');
        if (videos.length === 0) return;

        const currentFit = videos[0].style.objectFit || localStorage.getItem('videoObjectFit') || 'cover';
        const fitOrder = ['cover', 'contain', 'fill', 'none', 'scale-down'];
        const newIndex = (fitOrder.indexOf(currentFit) + 1) % fitOrder.length;
        const newFit = fitOrder[newIndex];

        videos.forEach(video => {
            video.style.objectFit = newFit;
            if (newFit === 'none') {
                video.style.minWidth = 'auto';
                video.style.minHeight = 'auto';
                video.style.width = '100%';
                video.style.height = '100%';
            } else {
                video.style.minWidth = '100%';
                video.style.minHeight = '100%';
            }
        });
    
        localStorage.setItem('videoObjectFit', newFit);
        this.querySelector('div').textContent = getFitButtonText();
    });

    function insertVideoBackground(src = 'bg.mp4') {
        document.querySelectorAll('video#background-video').forEach(v => v.remove());
        videoTag = document.createElement("video");
        videoTag.className = "video-background";
        videoTag.id = "background-video";
        videoTag.autoplay = true;
        videoTag.loop = true;
        videoTag.muted = localStorage.getItem('videoMuted') === 'true';
        videoTag.playsInline = true;
        videoTag.innerHTML = `
            <source src="/luci-static/spectra/bgm/bg.mp4" type="video/mp4">
            Your browser does not support the video tag.
        `;
        const savedFit = localStorage.getItem('videoObjectFit') || 'cover';
        videoTag.style.objectFit = savedFit;
        document.body.prepend(videoTag);
        videoTag.muted = localStorage.getItem('videoMuted') === 'true'; 

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
                z-index: -1;
            }
            .video-background + .wrapper span {
                display: none !important;
            }
        `;
    }

    function updateSoundToggle() {
        const soundState = localStorage.getItem("videoMuted") === "false" ? false : true;
        videoTag.muted = soundState;

        const soundToggle = document.querySelector(".sound-toggle div");
        if (!soundState) {
            soundToggle.textContent = "🔊";
        } else {
            soundToggle.textContent = "🔇";
        }
    }

    function applyPHPBackground() {
        const phpBackgroundSrc = localStorage.getItem('phpBackgroundSrc');
        const phpBackgroundType = localStorage.getItem('phpBackgroundType');
        if (phpBackgroundSrc && phpBackgroundType) {
            if (phpBackgroundType === 'image') {
                clearExistingBackground();
                applyCSS(phpBackgroundSrc);
            } else if (phpBackgroundType === 'video') {
                clearExistingBackground();
                setVideoBackground(phpBackgroundSrc, true);
            }
        }
    }

    if (isEnabled) {
        initBackgroundSystem();
    } else {
        applyPHPBackground();
    }

    document.addEventListener('click', (e) => {
        const popup = document.getElementById('mode-popup');
        if (!popup.contains(e.target) && e.target.id !== 'settings-icon') {
            popup.classList.remove('show');
        }
    });

    if (typeof phpBackgroundSrc !== 'undefined' && typeof phpBackgroundType !== 'undefined') {
        if (phpBackgroundType === 'image') {
            setImageBackground(phpBackgroundSrc);
        } else if (phpBackgroundType === 'video') {
            setVideoBackground(phpBackgroundSrc, true);
        }
    }
});

    function setImageBackground(src) {
        clearExistingBackground();
        document.body.style.background = `url('/luci-static/spectra/bgm/${src}') no-repeat center center fixed`;
        document.body.style.backgroundSize = 'cover';
        localStorage.setItem('phpBackgroundSrc', src);
        localStorage.setItem('phpBackgroundType', 'image');
    }

    function setVideoBackground(src, isPHP = false) {
        clearExistingBackground();
        let existingVideoTag = document.getElementById("background-video");
    
        const savedFit = localStorage.getItem('videoObjectFit') || 'cover'; 

        if (existingVideoTag) {
            existingVideoTag.src = `/luci-static/spectra/bgm/${src}`;
            existingVideoTag.muted = localStorage.getItem('videoMuted') === 'true'; 
            existingVideoTag.style.objectFit = savedFit; 
        } else {
            videoTag = document.createElement("video");
            videoTag.className = "video-background";
            videoTag.id = "background-video";
            videoTag.autoplay = true;
            videoTag.loop = true;
            videoTag.muted = localStorage.getItem('videoMuted') === 'true'; 
            videoTag.playsInline = true;
            videoTag.style.objectFit = savedFit; 
            videoTag.innerHTML = `
                <source src="/luci-static/spectra/bgm/${src}" type="video/mp4">
                Your browser does not support the video tag.
            `;
            document.body.prepend(videoTag);

            videoTag.addEventListener('loadedmetadata', () => {
                videoTag.play().catch(error => {
                    console.log('视频自动播放被阻止:', error);
                });
            });
        }

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
                z-index: -1; 
            }
            .video-background + .wrapper span {
                display: none !important;
            }
        `;

        if (savedFit === 'none') {
            videoTag.style.minWidth = 'auto';
            videoTag.style.minHeight = 'auto';
            videoTag.style.width = '100%';
            videoTag.style.height = '100%';
        } else {
            videoTag.style.minWidth = '100%';
            videoTag.style.minHeight = '100%';
        }

        localStorage.setItem('phpBackgroundSrc', src);
        localStorage.setItem('phpBackgroundType', 'video');
    
        const currentMuted = localStorage.getItem('videoMuted') === 'true';
        document.querySelector('.sound-toggle div').textContent = currentMuted ? '🔇' : '🔊';
    }

    document.querySelector('.object-fit-btn')?.addEventListener('click', function() {
        const videos = document.querySelectorAll('video#background-video');
        if (videos.length === 0) return;

        const currentFit = videos[0].style.objectFit || localStorage.getItem('videoObjectFit') || 'cover';
        const fitOrder = ['cover', 'contain', 'fill', 'none', 'scale-down'];
        const newIndex = (fitOrder.indexOf(currentFit) + 1) % fitOrder.length;
        const newFit = fitOrder[newIndex];

        videos.forEach(video => {
            video.style.objectFit = newFit;
            if (newFit === 'none') {
                video.style.minWidth = 'auto';
                video.style.minHeight = 'auto';
                video.style.width = '100%';
                video.style.height = '100%';
            } else {
                video.style.minWidth = '100%';
                video.style.minHeight = '100%';
            }
        
            if(video.src.includes('bg.mp4') === false) {
                localStorage.setItem('phpBackgroundType', 'video');
            }
        });
    
        localStorage.setItem('videoObjectFit', newFit);
        this.querySelector('div').textContent = getFitButtonText();
    });

    document.querySelector('.sound-toggle').addEventListener('click', function() {
        const newMuted = !(localStorage.getItem('videoMuted') === 'true');
    
        localStorage.setItem('videoMuted', newMuted);
    
        document.querySelectorAll('video#background-video').forEach(video => {
            video.muted = newMuted;
        });
    
        this.querySelector('div').textContent = newMuted ? '🔇' : '🔊';
    });

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

    function showThemeSettings() {
        const existing = document.getElementById('theme-settings-overlay');
        if (existing) return;

        const overlay = document.createElement('div');
        overlay.id = 'theme-settings-overlay';
        overlay.innerHTML = `
            <div id="theme-settings-dialog">
                <div class="dialog-header">
                    <h3>Spectra 主题设置</h3>
                    <button class="close-btn">&times;</button>
                </div>
                <iframe id="theme-iframe" 
                    src="${window.location.protocol}//${window.location.host}/luci-static/spectra/bgm/index.php"
                    style="width: 100%; height: calc(100% - 40px); border: none; border-radius: 0 0 5px 5px;">
                </iframe>
            </div>
        `;

        const style = document.createElement('style');
        style.textContent = `
            #theme-settings-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                padding-top: 5vh;  
                backdrop-filter: blur(3px);
            }
            #theme-settings-dialog {
                background: rgba(0,0,0,0.9);
                width: 70%;
                height: 80vh;
                margin-top: 0;
                border-radius: 8px;
                box-shadow: 0 0 20px rgba(0,0,0,0.5);
                transform: translateY(0);
            }
            .dialog-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px 20px;
                background: linear-gradient(135deg, #6f42c1, #9C27B0);
                border-radius: 8px 8px 0 0;
            }
            .dialog-header h3 {
                margin: 0;
                color: #9C27B0;
                font-size: 1.2em;
            }
            .close-btn {
                background: none;
                border: none;
                color: white;
                font-size: 24px;
                cursor: pointer;
                padding: 0 8px;
            }

            @media (max-width: 768px) {
                #theme-settings-dialog {
                    width: 90%;
                    height: 90vh;
                }
            }
        `;

        document.body.appendChild(overlay);
        document.head.appendChild(style);

        overlay.querySelector('.close-btn').addEventListener('click', () => {
            overlay.remove();
            style.remove();
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.remove();
                style.remove();
            }
        });
    }






