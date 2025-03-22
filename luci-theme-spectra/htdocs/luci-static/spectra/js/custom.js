document.addEventListener("DOMContentLoaded", function () {
    let isEnabled = localStorage.getItem('backgroundEnabled') !== 'false';
    let videoTag, bgImages, bgIndex, availableImages, switchInterval;

    const controlPanel = `
        <div id="settings-icon">⚙️</div>
        <div id="mode-popup">
            <button id="master-switch">
                <span>${isEnabled ? '已启用 ✅' : '已禁用 ❌'}</span>
                <div class="status-led" style="background:${isEnabled ? '#4CAF50' : '#f44336'}"></div>
            </button>
            <button data-mode="video">视频模式</button>
            <button data-mode="image">图片模式</button>
            <button data-mode="solid">暗黑模式</button>
            <button data-mode="auto">自动模式</button>
            <button class="sound-toggle">
                <span>背景音效</span>
                <div>${localStorage.getItem('videoMuted') === 'true' ? '🔇' : '🔊'}</div>
            </button>
            <button id="redirect-btn">文件管理</button>
            <button class="info-btn">使用说明</button>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', controlPanel);

document.getElementById('redirect-btn').addEventListener('click', function(e) {
    e.preventDefault();
    window.open('/luci-static/spectra/bgm/spectra.php', '_blank');
});

    const styles = `
        #settings-icon {
            position: fixed;
            right: 20px;
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

        @media (max-width: 600px) {
            #settings-icon {
                right: 10px;
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

        document.querySelectorAll('#mode-popup button:not(#master-switch):not(.sound-toggle)').forEach(btn => {
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

    document.querySelector('.info-btn').addEventListener('click', () => {
        alert('使用说明：\n1. 视频模式：默认名称为「bg.mp4」\n2. 图片模式：默认名称为「bg1-5.jpg」\n3. 暗黑模式：透明背景+光谱动画\n4. 纯白模式：需到文件管理进行切换，关闭控制开关\n5. 文件路径：/www/luci-static/spectra/bgm');
    });

    function initBackgroundSystem() {
        bgImages = Array.from({length: 5}, (_, i) => `bg${i + 1}.jpg`);
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

    function insertVideoBackground() {
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
                object-fit: cover;
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
    
        if (existingVideoTag) {
            existingVideoTag.src = `/luci-static/spectra/bgm/${src}`;
            existingVideoTag.muted = localStorage.getItem('videoMuted') === 'true'; 
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
                object-fit: cover;
                z-index: -1;
            }
            .video-background + .wrapper span {
                display: none !important;
            }
        `;

        localStorage.setItem('phpBackgroundSrc', src);
        localStorage.setItem('phpBackgroundType', 'video');
    
        const currentMuted = localStorage.getItem('videoMuted') === 'true';
        document.querySelector('.sound-toggle div').textContent = currentMuted ? '🔇' : '🔊';
    
        checkAndReload();
    }

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


