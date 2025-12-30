function getCurrentTheme(callback) {
    fetch("/spectra/theme-switcher.php")
        .then(response => response.json())
        .then(data => {
            callback(data.mode || 'dark');
        })
        .catch(error => {
            callback('dark');
        });
}

function getModeDisplayName(mode) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    const modeNames = {
        'video': translations['mode_video'] || 'Video',
        'image': translations['mode_image'] || 'Image', 
        'solid': translations['mode_solid'] || 'Dark',
        'auto': translations['mode_auto'] || 'Auto'
    };
    return modeNames[mode] || mode;
}

document.addEventListener("DOMContentLoaded", function () {
    getCurrentTheme(function(currentMode) {
        if (currentMode === 'light') {
            document.body.classList.remove('dark');
        } else {
            document.body.classList.add('dark');
        }
   
        let isEnabled = localStorage.getItem('backgroundEnabled') === 'true';
        
        if (localStorage.getItem('backgroundEnabled') === null) {
            localStorage.setItem('backgroundEnabled', 'false');
            isEnabled = false;
        }

        if (localStorage.getItem('backgroundMode') === null) {
            localStorage.setItem('backgroundMode', 'auto');
        }

        const savedFont = localStorage.getItem('selectedFont') || 'default';
        applyFont(savedFont);
        
        let videoTag, bgImages, bgIndex, availableImages, switchInterval;
        const savedMode = localStorage.getItem('backgroundMode');
        
        if (isEnabled) {
            initBackgroundSystem();
        } else {
            applyPHPBackground();
        }

        updateSwitchState();
        updateThemeButton();
        updateSoundToggle();
        updateFontToggle();
        updateFitButton();
        updateModeSelection(localStorage.getItem('backgroundMode') || 'auto');
        updateModeSelection(savedMode);

        bindControlPanelEvents();

        function updateModeSelection(selectedMode) {
            document.querySelectorAll('[data-mode]').forEach(btn => {
                const mode = btn.getAttribute('data-mode');

                if (mode === selectedMode) {
                    btn.classList.add('selected');
                    btn.classList.remove('uk-card-default');
                } else {
                    btn.classList.remove('selected');
                    btn.classList.add('uk-card-default');
                }

                const disableModes = !isEnabled || localStorage.getItem('disableColorSettings') === 'true';
                if (disableModes) {
                    btn.classList.add('disabled');
                } else {
                    btn.classList.remove('disabled');
                }
            });
        }

        function updateSwitchState() {
            const translations = languageTranslations[currentLang] || languageTranslations['en'];
            const switchText = document.getElementById('switch-text');
            const masterSwitch = document.getElementById('master-switch');
            const powerIcon = document.getElementById('power-icon');

            if (switchText) {
                switchText.textContent = isEnabled ? 
                    (translations['status_enabled'] || 'Enabled') : 
                    (translations['status_disabled'] || 'Disabled');
            }

            if (masterSwitch) {
                if (isEnabled) {
                    masterSwitch.classList.remove('uk-card-secondary');
                    masterSwitch.classList.add('uk-card-success');
                } else {
                    masterSwitch.classList.remove('uk-card-success');
                    masterSwitch.classList.add('uk-card-secondary');
                }
            }

            if (powerIcon) {
                powerIcon.className = isEnabled ? "bi bi-toggle-on" : "bi bi-toggle-off";
            }
        }

        function updateThemeButton() {
            const translations = languageTranslations[currentLang] || languageTranslations['en'];
            getCurrentTheme(function(currentMode) {
                const themeText = document.getElementById('theme-text');
                const themeIcon = document.getElementById('theme-icon');
                const themeToggle = document.getElementById('spectra-theme-toggle');

                if (themeText) themeText.textContent = currentMode === 'dark' ? 
                    (translations['theme_dark'] || 'Dark Mode') : 
                    (translations['theme_light'] || 'Light Mode');
                if (themeIcon) themeIcon.className = currentMode === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';

                if (themeToggle) {
                    themeToggle.classList.toggle('uk-card-primary', currentMode !== 'dark');
                    themeToggle.classList.toggle('uk-card-default', currentMode === 'dark');
                }
            });
        }

        function updateSoundToggle() {
            const soundIcon = document.getElementById('sound-icon');
            const soundMuted = localStorage.getItem('videoMuted') === 'true';

            if (soundIcon) {
                soundIcon.className = soundMuted ? "bi bi-volume-mute" : "bi bi-volume-up";
            }
        }

        function updateFontToggle() {
            const translations = languageTranslations[currentLang] || languageTranslations['en'];
            const fontText = document.getElementById('font-text');
            const currentFont = localStorage.getItem('selectedFont') || 'default';
            let fontDisplayText = translations['font_default'] || 'Default Font';
            
            switch (currentFont) {
                case 'fredoka': fontDisplayText = translations['font_rounded'] || 'Rounded Font'; break;
                case 'dmserif': fontDisplayText = translations['font_serif'] || 'Serif Font'; break;
                case 'notoserif': fontDisplayText = translations['font_notoserif'] || 'Noto Serif'; break;
                case 'comicneue': fontDisplayText = translations['font_comic'] || 'Comic Font'; break;
                case 'notosans': fontDisplayText = translations['font_notosans'] || 'Noto Sans'; break;
                case 'cinzeldecorative': fontDisplayText = translations['font_cinzel'] || 'Cinzel Decorative'; break;
            }
            
            if (fontText) {
                fontText.textContent = fontDisplayText;
            }
        }

        function updateFitButton() {
            const translations = languageTranslations[currentLang] || languageTranslations['en'];
            const fitText = document.getElementById('fit-text');
            const savedFit = localStorage.getItem('videoObjectFit') || 'cover';
            const fitTexts = {
                'contain': translations['fit_contain'] || 'Normal Ratio',
                'fill': translations['fit_fill'] || 'Stretch Fill', 
                'none': translations['fit_none'] || 'Original Size',
                'scale-down': translations['fit_scale_down'] || 'Smart Fit',
                'cover': translations['fit_cover'] || 'Default Crop'
            };
            
            if (fitText) {
                fitText.textContent = fitTexts[savedFit] || translations['fit_cover'] || 'Default Crop';
            }
        }

        function bindControlPanelEvents() {
            document.getElementById('spectra-theme-toggle')?.addEventListener('click', function() {
                const translations = languageTranslations[currentLang] || languageTranslations['en'];
                getCurrentTheme(function(currentMode) {
                    const switchingToLight = currentMode === 'dark';
                    const themeText = document.getElementById('theme-text');
                    const themeIcon = document.getElementById('theme-icon');
                    const themeToggle = document.getElementById('spectra-theme-toggle');

                    const message = switchingToLight ? 
                        (translations['message_switch_to_light'] || 'Switching to light mode') : 
                        (translations['message_switch_to_dark'] || 'Switching to dark mode');
                    showLogMessage(message);
                    speakMessage(message);

                    if (themeText) themeText.textContent = switchingToLight ? 
                        (translations['theme_light'] || 'Light Mode') : 
                        (translations['theme_dark'] || 'Dark Mode');
                    if (themeIcon) themeIcon.className = switchingToLight ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
                    if (themeToggle) {
                        themeToggle.classList.toggle('uk-card-primary', switchingToLight);
                        themeToggle.classList.toggle('uk-card-default', !switchingToLight);
                    }

                    fetch("/spectra/theme-switcher.php", { method: "POST" })
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success) return;

                            if (switchingToLight) {
                                isEnabled = false;
                                localStorage.setItem('backgroundEnabled', 'false');
                                localStorage.setItem('disableColorSettings', 'true');
                                clearBackgroundSystem();
                                updateSwitchState();
                                document.querySelectorAll('[data-mode]').forEach(btn => btn.classList.add('disabled'));
                            } else {
                                localStorage.removeItem('disableColorSettings');
                                document.querySelectorAll('[data-mode]').forEach(btn => btn.classList.remove('disabled'));
                            }

                            setTimeout(() => location.reload(), 3000);
                        })
                        .catch(err => console.error('Theme switch failed', err));
                });
            });

            document.querySelector('.theme-settings-btn')?.addEventListener('click', function() {
                const translations = languageTranslations[currentLang] || languageTranslations['en'];
                const message = translations['message_open_theme_settings'] || 'Opening theme settings';
                showLogMessage(message);
                speakMessage(message);
                updateUIText();
                UIkit.modal('#theme-settings-modal').show();
            });

            document.getElementById('master-switch')?.addEventListener('click', function() {
                const translations = languageTranslations[currentLang] || languageTranslations['en'];
                isEnabled = !isEnabled;
                localStorage.setItem('backgroundEnabled', isEnabled);
                updateSwitchState();
                updateModeSelection(localStorage.getItem('backgroundMode') || 'auto');
                const message = isEnabled ? 
                    (translations['message_background_on'] || 'Background on') : 
                    (translations['message_background_off'] || 'Background off');
                showLogMessage(message);
                speakMessage(message);
               
                if (isEnabled) {
                    initBackgroundSystem();
                } else {
                    clearBackgroundSystem();
                }
            });

            document.querySelector('.sound-toggle')?.addEventListener('click', function() {
                const translations = languageTranslations[currentLang] || languageTranslations['en'];
                const newMuted = localStorage.getItem('videoMuted') !== 'true';
                localStorage.setItem('videoMuted', newMuted);
                updateSoundToggle();

                const message = newMuted ? 
                    (translations['message_sound_off'] || 'Sound off') : 
                    (translations['message_sound_on'] || 'Sound on');
                showLogMessage(message);
                speakMessage(message);
                
                if (videoTag) {
                    videoTag.muted = newMuted;
                }
            });

            document.querySelectorAll('[data-mode]').forEach(btn => {
                btn.addEventListener('click', function() {
                    const translations = languageTranslations[currentLang] || languageTranslations['en'];
                    const mode = this.getAttribute('data-mode');
                    setMode(mode);
                    const modeName = getModeDisplayName(mode);
                    const message = `${modeName}`;
                    showLogMessage(message);
                    speakMessage(message);
                });
            });

            document.querySelector('.object-fit-btn')?.addEventListener('click', function() {
                const translations = languageTranslations[currentLang] || languageTranslations['en'];
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
                updateFitButton();

                const fitTexts = {
                    'contain': translations['fit_contain'] || 'Normal Ratio',
                    'fill': translations['fit_fill'] || 'Stretch Fill',
                    'none': translations['fit_none'] || 'Original Size',
                    'scale-down': translations['fit_scale_down'] || 'Smart Fit',
                    'cover': translations['fit_cover'] || 'Default Crop'
                };
                const message = `${translations['message_fit_adjusted'] || 'Display ratio adjusted to'} ${fitTexts[newFit] || newFit}`;
                showLogMessage(message);
                speakMessage(message);
            });

            document.getElementById('font-toggle')?.addEventListener('click', function() {
                const translations = languageTranslations[currentLang] || languageTranslations['en'];
                const currentFont = localStorage.getItem('selectedFont') || 'default';
                let nextFont;
                
                switch (currentFont) {
                    case 'default': nextFont = 'fredoka'; break;
                    case 'fredoka': nextFont = 'dmserif'; break;
                    case 'dmserif': nextFont = 'notoserif'; break;
                    case 'notoserif': nextFont = 'comicneue'; break;
                    case 'comicneue': nextFont = 'notosans'; break;
                    case 'notosans': nextFont = 'cinzeldecorative'; break;
                    case 'cinzeldecorative': nextFont = 'default'; break;
                    default: nextFont = 'default';
                }
                
                localStorage.setItem('selectedFont', nextFont);
                updateFontToggle();
                applyFont(nextFont);
                const fontNames = {
                    'default': translations['font_default'] || 'Default Font',
                    'fredoka': translations['font_rounded'] || 'Rounded Font',
                    'dmserif': translations['font_serif'] || 'Serif Font',
                    'notoserif': translations['font_notoserif'] || 'Noto Serif',
                    'comicneue': translations['font_comic'] || 'Comic Font',
                    'notosans': translations['font_notosans'] || 'Noto Sans',
                    'cinzeldecorative': translations['font_cinzel'] || 'Cinzel Decorative'
                };
                const message = `${translations['message_font_changed'] || 'Font changed to'} ${fontNames[nextFont] || nextFont}`;
                showLogMessage(message);
                speakMessage(message);
            });

            document.querySelector('.info-btn')?.addEventListener('click', function() {
                const translations = languageTranslations[currentLang] || languageTranslations['en'];
                const message = translations['message_open_usage_guide'] || 'Opening usage guide';
                showLogMessage(message);
                speakMessage(message);
                updateUIText();
                UIkit.modal('#usage-guide-modal').show();
            });
        }

        function initBackgroundSystem() {
            bgImages = Array.from({length: 20}, (_, i) => `bg${i + 1}.jpg`);
            bgIndex = 0;
            availableImages = [];
        
            const savedMode = localStorage.getItem('backgroundMode') || 'auto';
            const phpBackgroundSrc = localStorage.getItem('phpBackgroundSrc');
            const phpBackgroundType = localStorage.getItem('phpBackgroundType');
        
            if (phpBackgroundSrc && phpBackgroundType) {
                if (phpBackgroundType === 'image') {
                    setImageBackground(phpBackgroundSrc);
                } else if (phpBackgroundType === 'video') {
                    setVideoBackground(phpBackgroundSrc);
                }
            } else {
                setMode(savedMode);
            }
        
            updateModeSelection(savedMode);
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
        
            updateModeSelection(currentMode);
        }

        function setMode(mode) {
            if (!isEnabled && mode !== 'solid') return;
        
            localStorage.setItem('backgroundMode', mode);
            localStorage.setItem('selectedMode', mode);
        
            clearExistingBackground();

            updateModeSelection(mode);

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
                videoTag.pause();
                videoTag.remove();
                videoTag = null;
            }
        
            document.querySelectorAll('#dynamic-style, #video-style').forEach(e => e.remove());
            clearInterval(switchInterval);
        
            const currentMode = localStorage.getItem('backgroundMode');
            if (currentMode !== 'color') {
                document.body.style.background = '';
                document.body.style.backgroundImage = '';
            }
        
            const existingVideoTag = document.getElementById("background-video");
            if (existingVideoTag && existingVideoTag !== videoTag) {
                existingVideoTag.remove();
            }
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
            localStorage.setItem('backgroundMode', 'auto');
            initAutoBackground();
        }

        function checkImages(callback) {
            availableImages = [];
            let checked = 0;

            const totalImages = bgImages.length;
    
            if (totalImages === 0) {
                callback();
                return;
            }
            bgImages.forEach(image => {
                checkFileExists(image, exists => {
                    checked++;
                    if (exists) availableImages.push(image);
                    if (checked === totalImages) callback();
                });
            });
        }

        function checkFileExists(file, callback) {
            fetch(`/spectra/file-checker.php?file=${file}`)
                .then(response => response.json())
                .then(data => callback(data.exists))
                .catch(() => callback(false));
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
                        background: url('/spectra/stream/${image}') no-repeat center center fixed !important;
                        background-size: cover !important;
                        transition: background 1s ease-in-out;
                    }
                `;
            } else {
                styleTag.innerHTML = `
                    body {
                        background: #111 !important;
                        background-image: none !important;
                        background-size: auto !important;
                    }
                `;
            }
        }

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
                <source src="/spectra/stream/bg.mp4" type="video/mp4">
                Your browser does not support the video tag.
            `;
            const savedFit = localStorage.getItem('videoObjectFit') || 'cover';
            videoTag.style.objectFit = savedFit;
            document.body.prepend(videoTag);

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
            `;
        }

        function applyPHPBackground() {
            const phpBackgroundSrc = localStorage.getItem('phpBackgroundSrc');
            const phpBackgroundType = localStorage.getItem('phpBackgroundType');
        
            if (phpBackgroundSrc && phpBackgroundType) {
                if (phpBackgroundType === 'image') {
                    setImageBackground(phpBackgroundSrc);
                } else if (phpBackgroundType === 'video') {
                    setVideoBackground(phpBackgroundSrc);
                }
            } else {
                const savedMode = localStorage.getItem('backgroundMode') || 'auto';
                setMode(savedMode);
            }
        }

        function setImageBackground(src) {
            clearExistingBackground();
            
            document.body.style.background = '';
            document.body.style.backgroundColor = '';
            document.body.style.backgroundImage = '';
            
            document.body.classList.remove('default-background');
            
            let styleTag = document.querySelector("#dynamic-style");
            if (!styleTag) {
                styleTag = document.createElement("style");
                styleTag.id = "dynamic-style";
                document.head.appendChild(styleTag);
            }
            
            styleTag.innerHTML = `
                body {
                    background: url('/spectra/stream/${src}') no-repeat center center fixed !important;
                    background-size: cover !important;
                    background-color: transparent !important;
                    transition: background 1s ease-in-out;
                }
                body:before {
                    display: none !important;
                }
            `;
            
            localStorage.setItem('phpBackgroundSrc', src);
            localStorage.setItem('phpBackgroundType', 'image');
            localStorage.setItem('backgroundMode', 'image');
        }
 
        function setVideoBackground(src, isPHP = false) {
            const wasEnabled = isEnabled;
            clearExistingBackground();
            isEnabled = wasEnabled;
            
            document.body.style.background = '';
            document.body.style.backgroundColor = '';
            document.body.style.backgroundImage = '';
            
            const dynamicStyle = document.querySelector("#dynamic-style");
            if (dynamicStyle) dynamicStyle.remove();
            
            let existingVideoTag = document.getElementById("background-video");
            const savedFit = localStorage.getItem('videoObjectFit') || 'cover';

            if (existingVideoTag) {
                existingVideoTag.src = `/spectra/stream/${src}`;
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
                    <source src="/spectra/stream/${src}" type="video/mp4">
                    Your browser does not support the video tag.
                `;
                document.body.prepend(videoTag);
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
                    background-image: none !important;
                    background-color: transparent !important;
                    margin: 0;
                    padding: 0;
                    height: 100vh;
                    overflow: hidden;
                }
                body:before {
                    display: none !important;
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

            localStorage.setItem('phpBackgroundSrc', src);
            localStorage.setItem('phpBackgroundType', 'video');
            localStorage.setItem('backgroundMode', 'video');
            updateSoundToggle();
        }
 
        function toggleAnimationSpans(enabled) {
            const animationContainer = document.querySelector('.animation-spans');
            if (animationContainer) {
                if (enabled) {
                    animationContainer.classList.add('animation-enabled');
                } else {
                    animationContainer.classList.remove('animation-enabled');
                }
            }
        }

        function applyFont(font) {
            const styleTag = document.querySelector("#font-style") || document.createElement("style");
            styleTag.id = "font-style";
        
            let fontFamily;
            switch (font) {
                case 'fredoka': 
                    fontFamily = "'Fredoka One', cursive";
                    break;
                case 'dmserif':
                    fontFamily = "'DM Serif Display', serif";
                    break;
                case 'notoserif':
                    fontFamily = "'Noto Serif SC', serif";
                    break;
                case 'comicneue':
                    fontFamily = "'Comic Neue', cursive";
                    break;
                case 'notosans':
                    fontFamily = "'Noto Sans', sans-serif";
                    break;
                case 'cinzeldecorative':
                    fontFamily = "'Cinzel Decorative', cursive";
                    break;
                default:
                    fontFamily = "-apple-system, BlinkMacSystemFont, sans-serif";
            }

            styleTag.textContent = `
                body,
                #control-panel-modal,
                #usage-guide-modal,
                #theme-settings-modal,
                #control-panel-modal *,
                #usage-guide-modal *,
                #theme-settings-modal * {
                    font-family: ${fontFamily} !important;
                }
            `;
        
            const oldStyle = document.getElementById('font-style');
            if (oldStyle) oldStyle.remove();
            document.head.appendChild(styleTag);
        }

        function switchBackground() {
            if (availableImages.length > 0) {
                bgIndex = (bgIndex + 1) % availableImages.length;
                applyCSS(availableImages[bgIndex]);
            }
        }
    });
});