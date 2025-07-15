document.addEventListener("DOMContentLoaded", function () {
    let isEnabled = localStorage.getItem('backgroundEnabled') !== 'false';
    let videoTag, bgImages, bgIndex, availableImages, switchInterval;

    const isAnimationEnabled = localStorage.getItem('animationEnabled') !== 'false';
    toggleAnimation(isAnimationEnabled);

    const savedFont = localStorage.getItem('selectedFont') || 'default';
    applyFont(savedFont);

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

    const savedColor = localStorage.getItem('customBackgroundColor');
    const savedMode = localStorage.getItem('backgroundMode');
    
    if (savedMode === 'color' && savedColor) {
        document.body.style.background = savedColor;
        document.body.style.backgroundSize = 'auto';
    } else if (isEnabled) {
        initBackgroundSystem();
    } else {
        applyPHPBackground();
    }

    const languages = {
        'en': {
            'enabled': 'Enabled',
            'disabled': 'Disabled',
            'currentTheme': 'Current Theme: ',
            'darkMode': 'Dark Mode',
            'lightMode': 'Light Mode',
            'themeSettings': 'Theme Settings',
            'videoMode': 'Video Mode',
            'imageMode': 'Image Mode',
            'solidMode': 'Solid Mode',
            'autoMode': 'Auto Mode',
            'backgroundSound': 'Background Sound',
            'displayRatio': 'Display Ratio',
            'normalRatio': 'Normal Ratio',
            'stretchFill': 'Stretch Fill',
            'originalSize': 'Original Size',
            'smartFit': 'Smart Fit',
            'defaultCrop': 'Default Crop',
            'showIP': 'Show IP Information',
            'hideIP': 'Hide IP Information',
            'usageGuide': 'Usage Guide',
            'colorPanel': 'Color Panel',
            'apply': 'Apply',
            'reset': 'Reset',
            'enableAnimation': 'Enable Animation',
            'disableAnimation': 'Disable Animation',
            'fontToggle': 'Font Style',
            'fontDefault': 'Default',
            'fontFredoka': 'Fredoka',
            'fontDMSerif': 'DM Serif',
            'fontNotoSerif': 'Noto Serif',
            'fontComicNeue': 'Comic Neue',
            'fontSettings': 'Font Settings', 
            'fontSize': 'Font Size',
            'fontColor': 'Font Color',
            'black': 'Black',
            'white': 'White',
            'red': 'Red',
            'blue': 'Blue',
            'green': 'Green',
            'purple': 'Purple',
            'customColor': 'Custom Color',
            'guide1': '1. Video Mode: Default name is "bg.mp4"',
            'guide2': '2. Image Mode: Default names are "bg1-20.jpg"',
            'guide3': '3. Solid Mode: Transparent background + spectrum animation',
            'guide4': '4. Light Mode: Switch in theme settings, will automatically turn off the control switch',
            'guide5': '5. Theme Settings: Supports custom backgrounds, mode switching requires clearing the background',
            'guide6': '6. Menu Settings: Press Ctrl + Alt + S to open the settings menu',
            'guide7': '7. Project Address: <a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">Click to visit</a>',
            'themeTitle': 'Spectra Theme Settings'
        },
        'zh': {
            'enabled': '已启用',
            'disabled': '已禁用',
            'currentTheme': '当前主题: ',
            'darkMode': '暗色模式',
            'lightMode': '亮色模式',
            'themeSettings': '主题设置',
            'videoMode': '视频模式',
            'imageMode': '图片模式',
            'solidMode': '暗黑模式',
            'autoMode': '自动模式',
            'backgroundSound': '背景音效',
            'displayRatio': '显示比例',
            'normalRatio': '正常比例',
            'stretchFill': '拉伸填充',
            'originalSize': '原始尺寸',
            'smartFit': '智能适应',
            'defaultCrop': '默认裁剪',
            'showIP': '显示IP信息',
            'hideIP': '隐藏IP信息',
            'colorPanel': '颜色面板',
            'apply': '应用',
            'reset': '重置',
            'enableAnimation': '开启动画',  
            'disableAnimation': '关闭动画',
            'fontToggle': '字体切换',
            'fontDefault': '默认字体',
            'fontFredoka': '圆润字体',
            'fontDMSerif': '衬线字体',
            'fontNotoSerif': '思源宋体',
            'fontComicNeue': '漫画字体',
            'fontSettings': '字体设置', 
            'fontSize': '字体大小',
            'fontColor': '字体颜色',
            'black': '黑色',
            'white': '白色',
            'red': '红色',
            'blue': '蓝色',
            'green': '绿色',
            'purple': '紫色',
            'customColor': '自定义颜色',
            'usageGuide': '使用说明',
            'guide1': '1. 视频模式：默认名称为「bg.mp4」',
            'guide2': '2. 图片模式：默认名称为「bg1-20.jpg」',
            'guide3': '3. 暗黑模式：透明背景+光谱动画',
            'guide4': '4. 亮色模式：主题设置进行切换，会自动关闭控制开关',
            'guide5': '5. 主题设置：支持自定义背景，模式切换需清除背景',
            'guide6': '6. 菜单设置：Ctrl + Alt + S 打开设置菜单',
            'guide7': '7. 项目地址：<a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">点击访问</a>',
            'themeTitle': 'Spectra 主题设置'
        },
        'zh-tw': {
            'enabled': '已啟用',
            'disabled': '已停用',
            'currentTheme': '當前主題: ',
            'darkMode': '暗色模式',
            'lightMode': '亮色模式',
            'themeSettings': '主題設定',
            'videoMode': '影片模式',
            'imageMode': '圖片模式',
            'solidMode': '暗黑模式',
            'autoMode': '自動模式',
            'backgroundSound': '背景音效',
            'displayRatio': '顯示比例',
            'normalRatio': '正常比例',
            'stretchFill': '拉伸填充',
            'originalSize': '原始尺寸',
            'smartFit': '智能適應',
            'defaultCrop': '預設裁剪',
            'showIP': '顯示IP資訊',
            'hideIP': '隱藏IP資訊',
            'colorPanel': '顏色面板',
            'apply': '套用',
            'reset': '重置',
            'enableAnimation': '啟用動畫',  
            'disableAnimation': '關閉動畫',
            'fontToggle': '字體切換',
            'fontDefault': '預設字體',
            'fontFredoka': '圓潤字體',
            'fontDMSerif': '襯線字體',
            'fontNotoSerif': '思源宋體',
            'fontComicNeue': '漫畫字體',
            'fontSettings': '字型設定', 
            'fontSize': '字體大小',
            'fontColor': '字體顏色',
            'black': '黑色',
            'white': '白色',
            'red': '紅色',
            'blue': '藍色',
            'green': '綠色',
            'purple': '紫色',
            'customColor': '自定義顏色',
            'usageGuide': '使用說明',
            'guide1': '1. 影片模式：預設名稱為「bg.mp4」',
            'guide2': '2. 圖片模式：預設名稱為「bg1-20.jpg」',
            'guide3': '3. 暗黑模式：透明背景+光譜動畫',
            'guide4': '4. 亮色模式：主題設定進行切換，會自動關閉控制開關',
            'guide5': '5. 主題設定：支援自訂背景，模式切換需清除背景',
            'guide6': '6. 功能選單設定：Ctrl + Alt + S 開啟設定選單',
            'guide7': '7. 專案地址：<a class="github-link" href="https://github.com/Thaolga/openwrt-nekobox" target="_blank">點擊訪問</a>',
            'themeTitle': 'Spectra 主題設定'
        }
    };

    let currentLanguage = localStorage.getItem('currentLanguage') || 'zh';

    function getLanguageButtonText() {
        switch(currentLanguage) {
            case 'zh': return 'English';
            case 'en': return '繁體中文';
            case 'zh-tw': return '简体中文';
            default: return 'English';
        }
    }

    function getLanguageButtonColor() {
        switch(currentLanguage) {
            case 'zh': return '#f44336';
            case 'en': return '#4CAF50';
            case 'zh-tw': return '#2196F3';
            default: return '#4CAF50';
        }
    }

    function translateText(key) {
        return languages[currentLanguage][key] || key;
    }

    function getFitButtonText() {
        const savedFit = localStorage.getItem('videoObjectFit') || 'cover';
        const texts = {
            'contain': translateText('normalRatio'),
            'fill': translateText('stretchFill'), 
            'none': translateText('originalSize'),
            'scale-down': translateText('smartFit'),
            'cover': translateText('defaultCrop')
        };
        return texts[savedFit] || translateText('defaultCrop');
    }

    function updateThemeButton(mode) {
        const btn = document.getElementById('theme-toggle');
        if (!btn) return;
    
        if (mode === "dark") {
            btn.innerHTML = `${translateText('darkMode')}&nbsp;&nbsp;<i class="bi bi-moon-fill"></i>`;
        } else {
            btn.innerHTML = `${translateText('lightMode')}&nbsp;&nbsp;<i class="bi bi-sun-fill"></i>`;
        }
    }

    fetch("/luci-static/spectra/bgm/theme-switcher.php")
        .then(res => res.json())
        .then(data => {
            updateThemeButton(data.mode);

            const masterSwitch = document.getElementById('master-switch');

            if (data.mode === "light") {
                isEnabled = false;
                localStorage.setItem('backgroundEnabled', 'false');

                if (masterSwitch) {
                    masterSwitch.style.background = '#f44336';
                    masterSwitch.querySelector('.status-led').style.background = '#f44336';
                    masterSwitch.querySelector('span').textContent = translateText('disabled') + ' ❌';
                }
                applyPHPBackground();
            } else {
                const enabled = localStorage.getItem('backgroundEnabled') === 'true';
                isEnabled = enabled;

                if (masterSwitch) {
                    masterSwitch.style.background = enabled ? '#4CAF50' : '#f44336';
                    masterSwitch.querySelector('.status-led').style.background = enabled ? '#4CAF50' : '#f44336';
                    masterSwitch.querySelector('span').textContent = enabled ? translateText('enabled') + ' ✅' : translateText('disabled') + ' ❌';
                }
                if (enabled) {
                    initBackgroundSystem();
                } else {
                    applyPHPBackground();
                }
            }
            updateUIText();
        })
        .catch(error => {
            console.error("获取主题模式失败:", error);
    });

    function generateControlPanel() {
        const isAnimationEnabled = localStorage.getItem('animationEnabled') !== 'false';
        const animationText = isAnimationEnabled ? translateText('disableAnimation') : translateText('enableAnimation');

        const currentFont = localStorage.getItem('selectedFont') || 'default';
        let fontText;
    
        switch (currentFont) {
            case 'fredoka':
                fontText = translateText('fontFredoka');
                break;
            case 'dmserif':
                fontText = translateText('fontDMSerif');
                break;
            case 'notoserif':
                fontText = translateText('fontNotoSerif');
                break;
            case 'comicneue':
                fontText = translateText('fontComicNeue');
                break;
            default:
                fontText = translateText('fontDefault');
        }

        return `
            <div id="settings-icon">⚙️</div>
            <div id="mode-popup">
                <button id="theme-toggle" class="always-visible" style="background:#2196F3 !important">
                    ${document.body.classList.contains('dark') ? 
                        `${translateText('darkMode')}&nbsp;&nbsp;<i class="bi bi-moon-fill"></i>` : 
                        `${translateText('lightMode')}&nbsp;&nbsp;<i class="bi bi-sun-fill"></i>`}
                </button>
                <button class="theme-settings-btn">
                    <span>${translateText('themeSettings')}</span>
                    <div><i class="bi bi-brush"></i></div>
                </button>
                <button id="master-switch">
                    <span>${translateText(isEnabled ? 'enabled' : 'disabled')}</span>
                    <div>${isEnabled ? '<i class="bi bi-power" style="color:#4CAF50"></i>' : '<i class="bi bi-power-off" style="color:#f44336"></i>'}</div>
                </button>
                <button data-mode="video">
                    <span>${translateText('videoMode')}</span>
                    <div><i class="bi bi-camera-video"></i></div>
                </button>
                <button data-mode="image">
                    <span>${translateText('imageMode')}</span>
                    <div><i class="bi bi-image"></i></div>
                </button>
                <button data-mode="solid">
                    <span>${translateText('solidMode')}</span>
                    <div><i class="bi bi-moon"></i></div>
                </button>
                <button data-mode="auto">
                    <span>${translateText('autoMode')}</span>
                    <div><i class="bi bi-stars"></i></div>
                </button>
                <button class="sound-toggle">
                    <span>${translateText('backgroundSound')}</span>
                    <div>${localStorage.getItem('videoMuted') === 'true' ? '🔇' : '🔊'}</div>
                </button>
                <button class="object-fit-btn" style="opacity:1 !important;pointer-events:auto !important">
                    <span>${translateText('displayRatio')}</span>
                    <div>${getFitButtonText()}</div>
                </button>
                <button class="ip-toggle">
                    <span>${localStorage.getItem('hideIP') === 'true' ? translateText('showIP') : translateText('hideIP')}</span>
                    <div class="status-led" style="background:${localStorage.getItem('hideIP') !== 'true' ? '#4CAF50' : '#f44336'}"></div>
                </button>
                <button id="language-toggle">
                    <span>${getLanguageButtonText()}</span>
                    <div class="status-led" style="background:${getLanguageButtonColor()}"></div>
                </button>
                <button id="animation-toggle" class="always-visible">
                    <span>${animationText}</span>
                    <div class="status-led" style="background:${isAnimationEnabled ? '#4CAF50' : '#f44336'}"></div>
                </button>
                <button id="font-toggle" class="object-fit-btn">
                    <span>${translateText('fontToggle')}</span>
                    <div>${getFontButtonText()}</div>
                </button>
                <button id="color-panel-btn">
                    <span>${translateText('colorPanel')}</span>
                    <div><i class="bi bi-palette"></i></div>
                </button>
                <button id="font-settings-btn" class="always-visible">
                    <span>${translateText('fontSettings')}</span>
                    <div><i class="bi bi-textarea-t"></i></div>
                </button>
                <button class="info-btn">
                    <span>${translateText('usageGuide')}</span>
                    <div><i class="bi bi-info-circle"></i></div>
                </button>
            </div>
        `;
    }

    function bindControlPanelEvents() {
        document.querySelector('.theme-settings-btn')?.addEventListener('click', showThemeSettings);
        document.querySelectorAll('[data-mode]').forEach(btn => {
            btn.addEventListener('click', function() {
                setMode(btn.dataset.mode);
                document.querySelectorAll('[data-mode]').forEach(b => b.classList.remove('selected-mode'));
                this.classList.add('selected-mode');
                localStorage.setItem('selectedMode', btn.dataset.mode);
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

        document.getElementById('color-panel-btn')?.addEventListener('click', function() {
            openColorPicker();
        });

        document.getElementById('font-settings-btn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            showFontSettings();
        });

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.altKey && e.key.toLowerCase() === 's') {
                e.preventDefault();
                const popup = document.getElementById('mode-popup');
                popup.classList.toggle('show');
            }
        });

        document.getElementById('font-toggle')?.addEventListener('click', function(e) {
            e.stopPropagation();
    
            const currentFont = localStorage.getItem('selectedFont') || 'default';
            let nextFont, nextFontName;
    
            switch (currentFont) {
                case 'default': nextFont = 'fredoka'; break;
                case 'fredoka': nextFont = 'dmserif'; break;
                case 'dmserif': nextFont = 'notoserif'; break;
                case 'notoserif': nextFont = 'comicneue'; break;
                default: nextFont = 'default';
            }
    
            localStorage.setItem('selectedFont', nextFont);
            this.querySelector('div').textContent = getFontButtonText();
            applyFont(nextFont);
        });

        document.getElementById('animation-toggle')?.addEventListener('click', function(e) {
            e.stopPropagation();
        
            const isAnimationEnabled = localStorage.getItem('animationEnabled') !== 'false';
            const newState = !isAnimationEnabled;
            localStorage.setItem('animationEnabled', newState);

            this.querySelector('.status-led').style.background = newState ? '#4CAF50' : '#f44336';
        
            this.querySelector('span').textContent = newState ? 
                translateText('disableAnimation') :
                translateText('enableAnimation');
        
            toggleAnimation(newState);
        });

        document.getElementById('theme-toggle')?.addEventListener('click', function(e) {
            e.stopPropagation();

            const switchingToLight = document.body.classList.contains('dark');

            fetch("/luci-static/spectra/bgm/theme-switcher.php", { method: "POST" })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {

                        if (switchingToLight) {
                            localStorage.setItem('backgroundEnabled', 'false');
                            clearBackgroundSystem();
                        }

                        updateThemeButton(data.mode);
                        setTimeout(() => location.reload(), 300);
                    }
                })
        });

        document.querySelector('.info-btn').addEventListener('click', () => {
            showCustomAlert(translateText('usageGuide'), [
                translateText('guide1'),
                translateText('guide2'),
                translateText('guide3'),
                translateText('guide4'),
                translateText('guide5'),
                translateText('guide6'),
                translateText('guide7')
            ]);
        });

        document.getElementById('language-toggle').addEventListener('click', function() {
            switch(currentLanguage) {
                case 'zh': currentLanguage = 'en'; break;
                case 'en': currentLanguage = 'zh-tw'; break;
                case 'zh-tw': currentLanguage = 'zh'; break;
                default: currentLanguage = 'zh';
            }
            localStorage.setItem('currentLanguage', currentLanguage);
            
            this.querySelector('span').textContent = getLanguageButtonText();
            this.querySelector('.status-led').style.background = getLanguageButtonColor();
    
            updateControlPanelText();
    
            const fontSettingsOverlay = document.getElementById('font-settings-overlay');
            if (fontSettingsOverlay) {
                updateFontSettingsText();
            }
        });

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
            this.querySelector('span').textContent = translateText(isEnabled ? 'enabled' : 'disabled');
            this.querySelector('div').innerHTML = isEnabled ? 
                '<i class="bi bi-power" style="color:#4CAF50"></i>' : 
                '<i class="bi bi-power-off" style="color:#f44336"></i>';
            document.querySelectorAll('#mode-popup button:not(.always-visible):not(#master-switch):not(.sound-toggle):not(#redirect-btn):not(.info-btn):not(#language-toggle)').forEach(btn => {
                btn.style.opacity = isEnabled ? 1 : 0.5;
                btn.style.pointerEvents = isEnabled ? 'auto' : 'none';
            });

            if (isEnabled) {
                initBackgroundSystem();
            } else {
                clearBackgroundSystem();
            }
        });

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

        document.addEventListener('click', function(e) {
            const toggleBtn = e.target.closest('.ip-toggle');
            if (toggleBtn && ipContainer) {
                const currentState = localStorage.getItem('hideIP') === 'true';
                const newState = !currentState;
                
                ipContainer.style.display = newState ? 'none' : 'flex';
                localStorage.setItem('hideIP', newState);
                
                toggleBtn.querySelector('span').textContent = newState ? translateText('showIP') : translateText('hideIP');
                toggleBtn.querySelector('.status-led').style.background = newState ? '#f44336' : '#4CAF50';
            }
        });
    }

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
            background: ${isEnabled ? '#4CAF50' : '#f44336'};
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
            background: #00A497 !important;  
        }

        #mode-popup button.theme-settings-btn {
            background: #EB6EA5 !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #mode-popup button#language-toggle {
            background: #FF9800 !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #mode-popup button#color-panel-btn {
            background: #795548 !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        #mode-popup button.always-visible {
            opacity: 1 !important;
            pointer-events: auto !important;
            background: #673AB7 !important;
        }

        #mode-popup button#font-toggle {
            opacity: 1 !important;
            pointer-events: auto !important;
            background: #9C27B0 !important;
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

    document.body.insertAdjacentHTML('beforeend', generateControlPanel());
    bindControlPanelEvents();

    if (localStorage.getItem('hideIP') === null) {
        localStorage.setItem('hideIP', 'false');
    }

    function showCustomAlert(title, messages) {
        const existingAlert = document.getElementById('custom-alert');
        if (existingAlert) existingAlert.remove();

        const alertHTML = `
            <div id="custom-alert-overlay">
                <div id="custom-alert">
                    <div class="alert-header">
                        <h3>${translateText(title)}</h3>
                        <button class="close-btn">&times;</button>
                    </div>
                    <div class="alert-content">
                        ${messages.map(msg => `<p>${translateText(msg)}</p>`).join('')}
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
        document.querySelectorAll('[data-mode]').forEach(btn => {
            btn.classList.toggle('selected-mode', btn.dataset.mode === savedMode);
        });

        if (savedMode === 'color') {
            const savedColor = localStorage.getItem('customBackgroundColor');
        if (savedColor) {
            document.body.style.background = savedColor;
            document.body.style.backgroundSize = 'auto';
            return;
        }
    }
        setMode(savedMode);
    }

    function clearBackgroundSystem() {
        const currentMode = localStorage.getItem('backgroundMode') || 'auto';
    
        if (videoTag) {
            videoTag.pause();
            videoTag.remove();
            videoTag = null;
        }
    
        document.querySelectorAll('#dynamic-style, #video-style').forEach(e => e.remove());
        clearInterval(switchInterval);
        document.body.style.background = '';
        document.body.style.backgroundImage = '';
        document.body.style.display = 'none';
        document.body.offsetHeight;
        document.body.style.display = '';
    
        localStorage.setItem('backgroundMode', currentMode);
    
        document.querySelectorAll('[data-mode]').forEach(btn => {
            btn.classList.toggle('selected-mode', btn.dataset.mode === currentMode);
        });
    }

    function toggleAnimation(enabled) {
        const wrapper = document.querySelector('.wrapper');
        if (wrapper) {
            wrapper.style.display = enabled ? 'block' : 'none';
        }
    }

    function applyFont(font) {
        const styleTag = document.querySelector("#font-style") || document.createElement("style");
        styleTag.id = "font-style";
    
        let fontCSS;
        switch (font) {
            case 'fredoka':
                fontCSS = `body { font-family: 'Fredoka One', cursive !important; }`;
                break;
            case 'dmserif':
                fontCSS = `body { font-family: 'DM Serif Display', serif !important; }`;
                break;
            case 'notoserif':
                fontCSS = `body { font-family: 'Noto Serif SC', serif !important; }`;
                break;
            case 'comicneue':
                fontCSS = `body { font-family: 'Comic Neue', cursive !important; }`;
                break;
            default:
                fontCSS = `body { font-family: -apple-system, BlinkMacSystemFont, sans-serif !important; }`;
        }
    
        styleTag.textContent = fontCSS;
        document.head.appendChild(styleTag);
    }

    function getFontButtonText() {
        const currentFont = localStorage.getItem('selectedFont') || 'default';
        switch (currentFont) {
            case 'fredoka': return translateText('fontFredoka');
            case 'dmserif': return translateText('fontDMSerif');
            case 'notoserif': return translateText('fontNotoSerif');
            case 'comicneue': return translateText('fontComicNeue');
            default: return translateText('fontDefault');
        }
    }

    function setMode(mode) {
        if (!isEnabled) return;
        localStorage.setItem('backgroundMode', mode);
        localStorage.setItem('selectedMode', mode); 
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
            case 'color':
                const savedColor = localStorage.getItem('customBackgroundColor') || '#333333';
                document.body.style.background = savedColor;
                document.body.style.backgroundSize = 'auto';
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

    function openColorPicker() {
        const overlay = document.createElement('div');
        overlay.id = 'color-picker-overlay';
        overlay.innerHTML = `
            <div id="color-picker-dialog">
                <div class="dialog-header">
                    <h3>${translateText('colorPanel')}</h3>
                    <button class="close-btn">&times;</button>
                </div>
                <div class="color-preview" id="color-preview"></div>
                <div class="color-controls">
                    <div class="color-input-group">
                        <input type="color" id="color-selector">
                        <input type="text" id="color-input" placeholder="#RRGGBB">
                    </div>
                    <div class="button-group">
                        <button id="apply-color">${translateText('apply')}</button>
                        <button id="reset-color">${translateText('reset')}</button>
                    </div>
                </div>
                <div class="preset-colors">
                    ${generateColorPresets()}
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        const style = document.createElement('style');
        style.textContent = `
            #color-picker-overlay {
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
            
            #color-picker-dialog {
                background: rgba(0,0,0,0.9);
                width: 400px;
                padding: 25px;
                border-radius: 10px;
                box-shadow: 0 0 25px rgba(0,0,0,0.6);
                border: 1px solid #444;
            }
            
            .dialog-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 1px solid #444;
            }
            
            .dialog-header h3 {
                margin: 0;
                color: #9C27B0;
                font-size: 1.2em;
            }
            
            .close-btn {
                background: none;
                border: none;
                color: #fff;
                font-size: 24px;
                cursor: pointer;
            }
            
            .color-preview {
                width: 100%;
                height: 100px;
                border-radius: 5px;
                margin-bottom: 15px;
                border: 1px solid #555;
                transition: background 0.3s;
            }
            
            .color-controls {
                display: flex;
                flex-direction: column;
                gap: 15px;
                margin-bottom: 20px;
                align-items: center; 
            }

            .color-input-group {
                display: flex;
                gap: 15px;
                width: 100%;
            }
               
            #color-selector {
                height: 50px;
                min-width: 80px;
                flex: none;
            }
            
            #color-input {
                flex: 1;
                padding: 12px;
                font-size: 16px;
                background: #222;
                border: 1px solid #444;
                color: #fff;
                border-radius: 4px;
                min-width: 0;
            }

            .button-group {
                display: flex;
                gap: 10px;
                width: 100%;
            }
            
            #apply-color, #reset-color {
                flex: 1;
                background: #4CAF50;
                border: none;
                color: white;
                border-radius: 4px;
                cursor: pointer;
                transition: background 0.3s;
                padding: 12px;
                font-size: 16px;
                white-space: nowrap; 
            }
            
            #reset-color {
                background: #f44336;
            }
            
            #apply-color:hover {
                background: #45a049;
            }
            
            #reset-color:hover {
                background: #d32f2f;
            }
            
            .preset-colors {
                display: grid;
                grid-template-columns: repeat(8, 1fr);
                gap: 8px;
            }
            
            .color-preset {
                height: 35px;
                border-radius: 4px;
                cursor: pointer;
                border: 2px solid transparent;
                transition: transform 0.2s, border-color 0.2s;
            }
            
            .color-preset:hover {
                transform: scale(1.1);
            }
            
            @media (max-width: 480px) {
                #color-picker-dialog {
                    width: 90%;
                    padding: 15px;
                }
            
                .preset-colors {
                    grid-template-columns: repeat(5, 1fr);
                }
            }
        `;
        document.head.appendChild(style);
        
        const savedColor = localStorage.getItem('customBackgroundColor') || '#333333';
        document.getElementById('color-preview').style.background = savedColor;
        document.getElementById('color-selector').value = savedColor;
        document.getElementById('color-input').value = savedColor;
        
        document.getElementById('color-selector').addEventListener('input', function(e) {
            const color = e.target.value;
            document.getElementById('color-preview').style.background = color;
            document.getElementById('color-input').value = color;
        });
        
        document.getElementById('color-input').addEventListener('input', function(e) {
            const color = e.target.value;
            if (isValidColor(color)) {
                document.getElementById('color-preview').style.background = color;
                document.getElementById('color-selector').value = color;
            }
        });
        
        document.getElementById('apply-color').addEventListener('click', function() {
            const color = document.getElementById('color-input').value;
            if (isValidColor(color)) {
                applyCustomBackgroundColor(color);
            }
        });
        
        document.getElementById('reset-color').addEventListener('click', function() {
            resetCustomBackgroundColor();
            setTimeout(() => location.reload(), 300); 
        });
        
        document.querySelector('.close-btn').addEventListener('click', function() {
            overlay.remove();
            style.remove();
        });
        
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.remove();
                style.remove();
            }
        });
        
        document.querySelectorAll('.color-preset').forEach(preset => {
            preset.addEventListener('click', function() {
                const color = this.getAttribute('data-color');
                document.getElementById('color-preview').style.background = color;
                document.getElementById('color-selector').value = color;
                document.getElementById('color-input').value = color;
                applyCustomBackgroundColor(color);
            });
        });
    }

    function generateColorPresets() {
        const presets = [
            '#1a1a2e', '#16213e', '#0f3460', '#1f4068', '#1b1b2f',
            '#e94560', '#f67280', '#ff7c7c', '#ff9a76', '#ffb997',
            '#00b4d8', '#90e0ef', '#00cec9', '#55efc4', '#81ecec',
            '#6a89cc', '#82ccdd', '#60a3bc', '#4a69bd', '#1e3799',
            '#222f3e', '#1e272e', '#485460', '#808e9b', '#d2dae2',
            '#ff9f1a', '#ffd32a', '#fbc531', '#e1b12c', '#f9ca24'
        ];
        
        return presets.map(color => `
            <div class="color-preset" data-color="${color}" style="background: ${color}"></div>
        `).join('');
    }

    function showFontSettings() {
        const existing = document.getElementById('font-settings-overlay');
        if (existing) return;

        const currentSize = localStorage.getItem('fontSize') || '16';
        const currentColor = localStorage.getItem('fontColor') || '#ffffff';

        const overlay = document.createElement('div');
        overlay.id = 'font-settings-overlay';
        overlay.innerHTML = `
            <div id="font-settings-dialog">
                <div class="dialog-header">
                    <h3>${translateText('fontSettings')}</h3>
                    <button class="close-btn">&times;</button>
                </div>
                <div class="font-controls">
                    <div class="font-size-control">
                        <label style="color: white">${translateText('fontSize')}: <span id="font-size-value" style="color: inherit">${currentSize}px</span></label>
                        <input type="range" id="font-size-slider" min="10" max="30" value="${currentSize}" step="1">
                    </div>
                    <div class="font-color-control">
                        <label>${translateText('fontColor')}:</label>
                        <div class="color-presets">
                            <button data-color="#000000" class="color-preset black">${translateText('black')}</button>
                            <button data-color="#ffffff" class="color-preset white">${translateText('white')}</button>
                            <button data-color="#ff0000" class="color-preset red">${translateText('red')}</button>
                            <button data-color="#0000ff" class="color-preset blue">${translateText('blue')}</button>
                            <button data-color="#00ff00" class="color-preset green">${translateText('green')}</button>
                            <button data-color="#800080" class="color-preset purple">${translateText('purple')}</button>
                            <button id="custom-color-btn" class="color-preset">${translateText('customColor')}</button>
                        </div>
                    </div>
                    <div class="font-preview" style="font-size: ${currentSize}px; color: ${currentColor}">
                        ${currentSize}px | ${currentColor}
                    </div>
                    <div class="button-group">
                        <button id="apply-font">${translateText('apply')}</button>
                        <button id="reset-font">${translateText('reset')}</button>
                    </div>
                </div>
            </div>
        `;

        const style = document.createElement('style');
        style.textContent = `
            #font-settings-overlay {
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
            
            #font-settings-dialog {
                background: rgba(0,0,0,0.9);
                width: 400px;
                padding: 25px;
                border-radius: 10px;
                box-shadow: 0 0 25px rgba(0,0,0,0.6);
                border: 1px solid #444;
            }
            
            .dialog-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 1px solid #444;
            }
            
            .dialog-header h3 {
                margin: 0;
                color: #9C27B0;
                font-size: 1.2em;
            }
            
            .close-btn {
                background: none;
                border: none;
                color: #fff;
                font-size: 24px;
                cursor: pointer;
            }
            
            .font-controls {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            
            .font-size-control {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            #font-size-slider {
                width: 100%;
                height: 8px;
                -webkit-appearance: none;
                background: #444;
                border-radius: 4px;
                outline: none;
            }
            
            #font-size-slider::-webkit-slider-thumb {
                -webkit-appearance: none;
                width: 20px;
                height: 20px;
                background: #9C27B0;
                border-radius: 50%;
                cursor: pointer;
            }
            
            .font-color-control {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .color-presets {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }
            
            .color-preset {
                padding: 8px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                transition: transform 0.2s;
                color: white;
                text-align: center;
            }
            
            .color-preset:hover {
                transform: scale(1.05);
            }
            
            .black { background: #000000; }
            .white { background: #ffffff; color: #000 !important; }
            .red { background: #ff0000; }
            .blue { background: #0000ff; }
            .green { background: #00ff00; color: #000 !important; }
            .purple { background: #800080; }
            
            #custom-color-btn {
                background: #555;
                grid-column: span 3;
            }
            
            .font-preview {
                padding: 15px;
                border: 1px solid #444;
                border-radius: 4px;
                text-align: center;
                margin-top: 10px;
            }
            
            .button-group {
                display: flex;
                gap: 10px;
                margin-top: 15px;
            }
            
            #apply-font, #reset-font {
                flex: 1;
                padding: 10px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
            }
            
            #apply-font {
                background: #4CAF50;
                color: white;
            }
            
            #reset-font {
                background: #f44336;
                color: white;
            }

            .font-size-control label, 
            .font-color-control label {
                color: white !important;
            }
           
            @media (max-width: 480px) {
                #font-settings-dialog {
                    width: 90%;
                    padding: 15px;
                }
                
                .color-presets {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                #custom-color-btn {
                    grid-column: span 2;
                }
            }
        `;

        document.body.appendChild(overlay);
        document.head.appendChild(style);

        const fontSizeSlider = document.getElementById('font-size-slider');
        const fontSizeValue = document.getElementById('font-size-value');
        const fontPreview = document.querySelector('.font-preview');

        fontSizeSlider.addEventListener('input', function() {
            const size = this.value;
            fontSizeValue.textContent = `${size}px`;
            fontPreview.style.fontSize = `${size}px`;
            const currentColor = fontPreview.style.color || '#ffffff';
            fontPreview.textContent = `${size}px | ${currentColor}`;
        });

        document.querySelectorAll('.color-preset:not(#custom-color-btn)').forEach(btn => {
            btn.addEventListener('click', function() {
                const color = this.getAttribute('data-color');
                fontPreview.style.color = color;
                const currentSize = fontSizeSlider.value;
                fontPreview.textContent = `${currentSize}px | ${color}`;
            });
        });

        document.getElementById('custom-color-btn').addEventListener('click', function() {
            const colorInput = document.createElement('input');
            colorInput.type = 'color';
            colorInput.value = fontPreview.style.color || '#ffffff';
            colorInput.click();
            
            colorInput.addEventListener('input', function() {
                fontPreview.style.color = this.value;
                const currentSize = fontSizeSlider.value;
                fontPreview.textContent = `${currentSize}px | ${this.value}`;
            });
        });

        document.getElementById('apply-font').addEventListener('click', function() {
            const fontSize = fontSizeSlider.value;
            const fontColor = fontPreview.style.color || currentColor;
            
            localStorage.setItem('fontSize', fontSize);
            localStorage.setItem('fontColor', fontColor);
            
            applyFontSettings(fontSize, fontColor);
            
            overlay.remove();
            style.remove();
        });

        document.getElementById('reset-font').addEventListener('click', function() {
            localStorage.removeItem('fontSize');
            localStorage.removeItem('fontColor');
            
            applyFontSettings('16', '#ffffff');
            
            overlay.remove();
            style.remove();
            location.reload();
        });

        overlay.querySelector('.close-btn').addEventListener('click', function() {
            overlay.remove();
            style.remove();
        });

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.remove();
                style.remove();
            }
        });
    }

    function applyFontSettings(size, color) {
        let styleTag = document.querySelector("#font-size-color-style") || document.createElement("style");
        styleTag.id = "font-size-color-style";
        styleTag.textContent = `
            body {
                font-size: ${size}px !important;
                color: ${color} !important;
            }
        `;
        document.head.appendChild(styleTag);
    }

    const savedFontSize = localStorage.getItem('fontSize');
    const savedFontColor = localStorage.getItem('fontColor');
    if (savedFontSize || savedFontColor) {
        applyFontSettings(savedFontSize || '16', savedFontColor || '#ffffff');
    }

    function updateControlPanelText() {
        const popup = document.getElementById('mode-popup');
        if (!popup) return;
    
        const masterSwitch = popup.querySelector('#master-switch');
        if (masterSwitch) {
            masterSwitch.querySelector('span').textContent = translateText(isEnabled ? 'enabled' : 'disabled');
            masterSwitch.querySelector('div').innerHTML = isEnabled ? 
                '<i class="bi bi-power" style="color:#4CAF50"></i>' : 
                '<i class="bi bi-power-off" style="color:#f44336"></i>';
        }

        const themeToggle = popup.querySelector('#theme-toggle');
        if (themeToggle) {
            themeToggle.innerHTML = document.body.classList.contains('dark') ? 
                `${translateText('darkMode')}&nbsp;&nbsp;<i class="bi bi-moon-fill"></i>` : 
                `${translateText('lightMode')}&nbsp;&nbsp;<i class="bi bi-sun-fill"></i>`;
        }
    
        const themeStatus = popup.querySelector('#theme-status');
        if (themeStatus) {
            themeStatus.textContent = `${translateText('currentTheme')}${document.body.classList.contains('dark') ? translateText('darkMode') : translateText('lightMode')}`;
        }
    
        popup.querySelector('.theme-settings-btn span').textContent = translateText('themeSettings');
        popup.querySelector('[data-mode="video"] span').textContent = translateText('videoMode');
        popup.querySelector('[data-mode="image"] span').textContent = translateText('imageMode');
        popup.querySelector('[data-mode="solid"] span').textContent = translateText('solidMode');
        popup.querySelector('[data-mode="auto"] span').textContent = translateText('autoMode');
        popup.querySelector('.sound-toggle span').textContent = translateText('backgroundSound');
        popup.querySelector('.object-fit-btn span').textContent = translateText('displayRatio');
        popup.querySelector('.object-fit-btn div').textContent = getFitButtonText();
        popup.querySelector('.ip-toggle span').textContent = localStorage.getItem('hideIP') === 'true' ? translateText('showIP') : translateText('hideIP');
        popup.querySelector('.info-btn span').textContent = translateText('usageGuide');
        popup.querySelector('#font-settings-btn span').textContent = `${translateText('fontSize')} & ${translateText('fontColor')}`;
        popup.querySelector('#font-settings-btn span').textContent = translateText('fontSettings');

        popup.querySelector('#color-panel-btn span').textContent = translateText('colorPanel');
    
        const isAnimationEnabled = localStorage.getItem('animationEnabled') !== 'false';
        popup.querySelector('#animation-toggle span').textContent = isAnimationEnabled ? translateText('disableAnimation') : translateText('enableAnimation');
    
        const currentFont = localStorage.getItem('selectedFont') || 'default';
        let fontText;
        switch (currentFont) {
            case 'fredoka': fontText = translateText('fontFredoka'); break;
            case 'dmserif': fontText = translateText('fontDMSerif'); break;
            case 'notoserif': fontText = translateText('fontNotoSerif'); break;
            case 'comicneue': fontText = translateText('fontComicNeue'); break;
            default: fontText = translateText('fontDefault');
        }

        const fontToggle = popup.querySelector('#font-toggle');
        if (fontToggle) {
            fontToggle.querySelector('span').textContent = translateText('fontToggle');
            fontToggle.querySelector('div').textContent = fontText;
        }
    }

    function updateFontSettingsText() {
        const overlay = document.getElementById('font-settings-dialog');
        if (!overlay) return;
    
        overlay.querySelector('h3').textContent = `${translateText('fontSize')} & ${translateText('fontColor')}`;
        overlay.querySelector('.font-size-control label').innerHTML = `${translateText('fontSize')}: <span id="font-size-value" style="color: inherit">${localStorage.getItem('fontSize') || '16'}px</span>`;
        overlay.querySelector('.font-color-control label').textContent = `${translateText('fontColor')}:`;
        overlay.querySelector('.color-preset.black').textContent = translateText('black');
        overlay.querySelector('.color-preset.white').textContent = translateText('white');
        overlay.querySelector('.color-preset.red').textContent = translateText('red');
        overlay.querySelector('.color-preset.blue').textContent = translateText('blue');
        overlay.querySelector('.color-preset.green').textContent = translateText('green');
        overlay.querySelector('.color-preset.purple').textContent = translateText('purple');
        overlay.querySelector('#custom-color-btn').textContent = translateText('customColor');
        overlay.querySelector('#apply-font').textContent = translateText('apply');
        overlay.querySelector('#reset-font').textContent = translateText('reset');
    }

    function applyCustomBackgroundColor(color) {
        clearExistingBackground();
    
        document.body.style.background = color;
        document.body.style.backgroundSize = 'auto';
    
        localStorage.setItem('customBackgroundColor', color);
        localStorage.setItem('backgroundMode', 'color');
    
        const overlay = document.getElementById('color-picker-overlay');
        if (overlay) overlay.remove();
    }

    function resetCustomBackgroundColor() {
        localStorage.removeItem('customBackgroundColor');
        localStorage.setItem('backgroundMode', 'auto');
        localStorage.removeItem('backgroundMode');
    
        const overlay = document.getElementById('color-picker-overlay');
        if (overlay) overlay.remove();
    
        const savedMode = localStorage.getItem('backgroundMode') || 'auto';
        setMode(savedMode);
    }

    function isValidColor(strColor) {
        const s = new Option().style;
        s.color = strColor;
        return s.color !== '';
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

    currentLanguage = localStorage.getItem('currentLanguage') || 'zh';
    updateUIText();

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
        const currentMode = localStorage.getItem('backgroundMode');
        if (currentMode !== 'color') {
            document.body.style.background = '';
        }

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
                    <h3>${translateText('themeTitle')}</h3>
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

    function switchBackground() {
        if (availableImages.length > 0) {
            bgIndex = (bgIndex + 1) % availableImages.length;
            applyCSS(availableImages[bgIndex]);
        }
    }
});


