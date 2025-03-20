document.addEventListener("DOMContentLoaded", function () {
    const bgImages = Array.from({ length: 5 }, (_, i) => `bg${i + 1}.jpg`);
    let bgIndex = 0;
    let availableImages = [];
    let videoExists = false;
    let videoTag;
    let switchInterval;

    const settingsIcon = document.createElement('div');
    settingsIcon.innerHTML = '⚙️';
    settingsIcon.id = 'settings-icon'; 
    Object.assign(settingsIcon.style, {
        position: 'fixed',
        right: '20px',
        bottom: '20px',
        cursor: 'pointer',
        zIndex: 1001,
        fontSize: '24px',
        color: '#fff',
        background: 'rgba(0,0,0,0.5)',
        borderRadius: '50%',
        width: '40px',
        height: '40px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
    });

    const modePopup = document.createElement('div');
    modePopup.id = 'mode-popup'; 
    modePopup.style.cssText = `
        position: fixed;
        right: 20px;
        top: 70px;
        background: rgba(0,0,0,0.9);
        border-radius: 5px;
        padding: 10px;
        color: #fff;
        z-index: 1002;
        display: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.5);
    `;

    ['video', 'image', 'solid', 'auto'].forEach(mode => {
        const btn = document.createElement('button');
        btn.textContent = `${mode.charAt(0).toUpperCase() + mode.slice(1)} 模式`;
        btn.onclick = () => setMode(mode);
        btn.style.cssText = `
            display: block;
            width: 100%;
            padding: 8px;
            margin: 4px 0;
            background: #444;
            border: none;
            color: white;
            border-radius: 3px;
            cursor: pointer;
        `;
        modePopup.appendChild(btn);
    });

    const soundToggle = document.createElement('button');
    soundToggle.className = 'sound-toggle';
    soundToggle.id = 'sound-toggle';
    soundToggle.style.cssText = `
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 1000;
        transition: background 0.3s;
        padding: 8px;
        margin: 4px 0;
        background: #444;
        border: none;
        color: white;
        border-radius: 3px;
    `;
    modePopup.appendChild(soundToggle);

    soundToggle.onclick = function () {
        if (videoTag.muted) {
            videoTag.muted = false;
            videoTag.volume = 1.0;
            soundToggle.classList.add("sound-on");
            soundToggle.textContent = "🔊 关闭音量";
            localStorage.setItem("videoMuted", "false");
        } else {
            videoTag.muted = true;
            soundToggle.classList.remove("sound-on");
            soundToggle.textContent = "🔇 开启音量";
            localStorage.setItem("videoMuted", "true");
        }
    };

    const infoButton = document.createElement('button');
    infoButton.textContent = '使用说明';
    infoButton.onclick = () => alert('视频模式：默认名称为「bg.mp4」\n图片模式：默认名称为「bg1-5.jpg」\n暗黑模式：透明背景+光谱动画\n文件路径：/www/luci-static/resources/background');
    infoButton.style.cssText = `
        display: block;
        width: 100%;
        padding: 8px;
        margin: 4px 0;
        background: #444;
        border: none;
        color: white;
        border-radius: 3px;
        cursor: pointer;
    `;

    modePopup.appendChild(infoButton);

    document.body.appendChild(settingsIcon);
    document.body.appendChild(modePopup);

    function setMode(mode) {
        localStorage.setItem('backgroundMode', mode);
        clearExistingBackground();

        switch (mode) {
            case 'video':
                checkFileExists('bg.mp4', exists => {
                    exists ? initVideoBackground() : fallbackToAuto();
                });
                break;
            case 'image':
                checkImages(() => {
                    if (availableImages.length > 0) {
                        initImageBackground();
                    } else {
                        fallbackToAuto();
                    }
                });
                break;
            case 'solid':
                applyCSS(null);
                break;
            case 'auto':
                initAutoBackground();
                break;
        }
        modePopup.style.display = 'none';
    }

    function clearExistingBackground() {
        if (videoTag) videoTag.remove();
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
        xhr.open('HEAD', `/luci-static/resources/background/${file}`);
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
                    background: url('/luci-static/resources/background/${image}') no-repeat center center fixed !important;
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
        console.log("Using video background...");
        videoTag = document.createElement("video");
        videoTag.className = "video-background";
        videoTag.id = "background-video";
        videoTag.autoplay = true;
        videoTag.loop = true;
        videoTag.muted = true;
        videoTag.playsInline = true;
        videoTag.innerHTML = `
            <source src="/luci-static/resources/background/bg.mp4" type="video/mp4">
            Your browser does not support the video tag.
        `;

        document.body.prepend(videoTag);

        let styleTag = document.createElement("style");
        styleTag.id = "video-style";
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
        document.head.appendChild(styleTag);

        updateSoundToggle();

        videoTag.addEventListener("ended", function () {
            this.currentTime = 0;
            this.play();
        });
    }

    function updateSoundToggle() {
        const soundState = localStorage.getItem("videoMuted") === "false" ? false : true;
        videoTag.muted = soundState;

        if (!soundState) {
            soundToggle.classList.add("sound-on");
            soundToggle.textContent = "🔊 关闭音量";
        } else {
            soundToggle.classList.remove("sound-on");
            soundToggle.textContent = "🔇 开启音量";
        }
    }

    settingsIcon.addEventListener('click', e => {
        e.stopPropagation();
        modePopup.style.display = modePopup.style.display === 'none' ? 'block' : 'none';
    });

    document.addEventListener('click', e => {
        if (!modePopup.contains(e.target) && e.target !== settingsIcon) {
            modePopup.style.display = 'none';
        }
    });

    const savedMode = localStorage.getItem('backgroundMode') || 'auto';
    setMode(savedMode);
    
    const mediaStyle = document.createElement("style");
    mediaStyle.innerHTML = `
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
            }
        }
    `;
    document.head.appendChild(mediaStyle);
});