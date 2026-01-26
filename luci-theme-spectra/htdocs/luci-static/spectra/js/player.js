const STORAGE_PREFIX = 'musicPlayer_';
let translations = languageTranslations[currentLang] || languageTranslations['en'];
const audioPlayer = new Audio();
let songs = JSON.parse(localStorage.getItem(`${STORAGE_PREFIX}cachedPlaylist`) || '[]');
let currentTrackIndex = 0;
let isPlaying = false;
let repeatMode = 0;
let isHovering = false;
let isManualScroll = false;
let artistImageLoaded = false;
let isSmallScreen = window.innerWidth < 768;
let currentPlaylistUrl = 'https://raw.githubusercontent.com/Thaolga/Rules/main/music/songs.txt';

class AudioVisualizer {
    constructor() {
        this.canvas = document.getElementById('audioVisualizer');
        this.ctx = null;
        this.isVisualizing = false;
        this.animationId = null;
        this.mockData = new Uint8Array(32);
        this.isEnabled = false;
        this.debugCounter = 0;
        this.isVisible = false;
        
        if (this.canvas) {
            this.ctx = this.canvas.getContext('2d');
            //console.log('Audio visualizer created');
        } else {
            console.error('Canvas element not found!');
        }
    }
    
    enable() {
        if (this.isEnabled || !this.canvas) return;
        this.isEnabled = true;
        this.isVisible = true;
        this.init();
    }
    
    disable() {
        this.isEnabled = false;
        this.isVisible = false;
        this.stop();
        if (this.ctx && this.canvas) {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        }
    }
    
    init() {
        if (!this.isEnabled || !this.canvas) return;
        
        this.resizeCanvas();
        window.addEventListener('resize', () => this.resizeCanvas());
    }
    
    resizeCanvas() {
        if (this.canvas && this.canvas.parentElement && this.isEnabled) {
            const width = this.canvas.parentElement.clientWidth;
            const height = this.canvas.parentElement.clientHeight;
            this.canvas.width = width;
            this.canvas.height = height;
        }
    }
    
    generateMockData() {
        if (!this.isEnabled) return;
        
        this.debugCounter++;
        const time = Date.now() / 150;
        
        for (let i = 0; i < 32; i++) {
            const wave1 = Math.sin(time * 1.5 + i * 0.25) * 60;
            const wave2 = Math.cos(time * 0.8 + i * 0.15) * 40;
            const wave3 = Math.sin(time * 2.2 + i * 0.1) * 20;
            const wave4 = Math.sin(time * 0.5 + i * 0.05) * 30;
            
            let value = 50 + wave1 + wave2 + wave3 + wave4;
            value += Math.random() * 25;
            
            const curve = 1 - Math.pow((i - 16) / 16, 4);
            value *= (0.3 + 0.7 * curve);
            
            this.mockData[i] = Math.max(10, Math.min(255, value));
        }
    }
    
    start() {
        if (this.isVisualizing || !this.isEnabled || !this.isVisible) return;
        
        this.isVisualizing = true;
        this.draw();
    }
    
    stop() {
        this.isVisualizing = false;
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
            this.animationId = null;
        }
        if (this.ctx && this.canvas) {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        }
    }
    
    draw() {
        if (!this.isVisualizing || !this.isEnabled || !this.isVisible || !this.ctx) return;
        
        this.animationId = requestAnimationFrame(() => this.draw());
        this.generateMockData();
        
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        //this.ctx.fillStyle = 'rgba(30, 30, 60, 0.3)';
        //this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        
        this.drawBars();
    }
    
    drawBars() {
        const barCount = 32;
        const barWidth = this.canvas.width / barCount;
        
        for (let i = 0; i < barCount; i++) {
            const barHeight = (this.mockData[i] / 255) * this.canvas.height;
            const x = i * barWidth;
            const y = this.canvas.height - barHeight;
            
            this.ctx.fillStyle = `hsl(${i * 10}, 100%, 60%)`;
            this.ctx.fillRect(x + 1, y, barWidth - 2, barHeight);
            
            this.ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
            this.ctx.fillRect(x + 1, y, barWidth - 2, 3);
        }
    }
}

let audioVisualizer = null;

function initVisualizer() {
    const container = document.getElementById('visualizerContainer');
    if (!container) {
        return;
    }
    
    if (!audioVisualizer) {
        audioVisualizer = new AudioVisualizer();
    }
    container.style.display = 'none';
}

function toggleVisualizer() {
    const container = document.getElementById('visualizerContainer');
    const button = document.querySelector('[data-tooltip-title="toggle_visualizer"]');
    
    if (!container || !button) {
        return;
    }
    
    if (!audioVisualizer) {
        initVisualizer();
    }
    
    const isCurrentlyHidden = container.style.display === 'none' || 
                             container.style.display === '';
    
    if (isCurrentlyHidden) {
        container.style.display = 'block';
        
        setTimeout(() => {
            if (audioVisualizer) {
                audioVisualizer.enable();
                setTimeout(() => {
                    audioVisualizer.resizeCanvas();
                    if (isPlaying) {
                        audioVisualizer.start();
                    }
                }, 100);
            }
        }, 50);
        
        button.innerHTML = '<i class="bi bi-bar-chart-fill"></i>';
        button.classList.add('visualizer-active');
        
    } else {
        container.style.display = 'none';
        
        if (audioVisualizer) {
            audioVisualizer.disable();
        }
        
        button.innerHTML = '<i class="bi bi-bar-chart"></i>';
        button.classList.remove('visualizer-active');
    }
    
    //savePlayerState();
}

function throttle(func, limit) {
    let lastFunc;
    let lastRan;
    return function(...args) {
        const context = this;
        if (!lastRan) {
            func.apply(context, args);
            lastRan = Date.now();
        } else {
            clearTimeout(lastFunc);
            lastFunc = setTimeout(function() {
                if (Date.now() - lastRan >= limit) {
                    func.apply(context, args);
                    lastRan = Date.now();
                }
            }, limit - (Date.now() - lastRan));
        }
    };
}

function saveCurrentTime(currentTrack, currentTime) {
    const playbackData = {
        currentTrack: currentTrack,
        currentTime: currentTime
    };
    localStorage.setItem(`${STORAGE_PREFIX}currentPlayback`, JSON.stringify(playbackData));
}

function loadCurrentTime(currentTrack) {
    const playbackData = JSON.parse(localStorage.getItem(`${STORAGE_PREFIX}currentPlayback`) || '{}');
    return playbackData.currentTrack === currentTrack ? playbackData.currentTime || 0 : 0;
}

document.addEventListener('DOMContentLoaded', () => {
    loadPlayerState();
    loadPlaylistUrl();
    initializePlayer();
    
    setTimeout(() => {
        initVisualizer();
    }, 1000);
    
    audioPlayer.addEventListener('timeupdate', throttle(() => {
        if (songs[currentTrackIndex]) {
            saveCurrentTime(songs[currentTrackIndex], audioPlayer.currentTime);
        }
    }, 1000));
});

function loadPlaylistUrl() {
    fetch('/spectra/playlist_config.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            currentPlaylistUrl = data.url || 'https://raw.githubusercontent.com/Thaolga/Rules/main/music/songs.txt';
            loadDefaultPlaylist();
        })
        .catch(error => {
            console.error('Failed to load playlist URL from PHP:', error);
            currentPlaylistUrl = 'https://raw.githubusercontent.com/Thaolga/Rules/main/music/songs.txt';
            loadDefaultPlaylist();
        });
}

function openUrlModal() {
    const modal = document.getElementById('urlModal');
    modal.style.display = 'block';
    fetch('/spectra/playlist_config.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            document.getElementById('playlistUrl').value = data.url || '';
        })
        .catch(error => {
            console.error('Failed to load current URL for modal:', error);
            document.getElementById('playlistUrl').value = currentPlaylistUrl;
        });
    if (typeof updateUIText === 'function') {
        updateUIText();
    }
}

function closeUrlModal() {
    const modal = document.getElementById('urlModal');
    modal.style.display = 'none';
}

function initLayoutState() {
    const isExpanded = localStorage.getItem('lyricsExpanded') === 'true';
    
    if (isExpanded) {
        applyExpandedLayout();
    } else {
        applyNormalLayout();
    }
}

function createLyricsControls() {
    if (document.querySelector('.lyrics-controls')) return;
    
    const lyricsSection = document.querySelector('.lyrics-section');
    if (!lyricsSection) return;
    
    const controls = document.createElement('div');
    controls.className = 'lyrics-controls';
    
    const sourceBtn = document.createElement('div');
    sourceBtn.className = 'lyrics-source-btn';
    sourceBtn.innerHTML = '<i class="fas fa-database"></i>';
    sourceBtn.setAttribute('data-tooltip-title', 'lyrics_source');

    const imageSourceBtn = document.createElement('div');
    imageSourceBtn.className = 'image-source-btn';
    imageSourceBtn.innerHTML = '<i class="bi bi-person-circle"></i>';
    imageSourceBtn.setAttribute('data-tooltip-title', 'image_source');
    
    const speedBtn = document.createElement('div');
    speedBtn.className = 'lyrics-speed-btn';
    speedBtn.innerHTML = '<i class="fas fa-tachometer-alt"></i>';
    speedBtn.setAttribute('data-tooltip-title', 'lyrics_speed');
    
    const colorBtn = document.createElement('div');
    colorBtn.className = 'lyrics-color-btn';
    colorBtn.innerHTML = '<i class="fas fa-font"></i>';
    colorBtn.setAttribute('data-tooltip-title', 'font_color');

    const downloadBtn = document.createElement('div');
    downloadBtn.className = 'lyrics-download-btn';
    downloadBtn.innerHTML = '<i class="fas fa-download"></i>';
    downloadBtn.setAttribute('data-tooltip-title', 'download_options');
    
    const bgToggle = document.createElement('div');
    bgToggle.className = 'bg-toggle';
    bgToggle.id = 'bgToggle';
    bgToggle.innerHTML = '<i class="bi bi-palette"></i>';
    bgToggle.setAttribute('data-tooltip-title', 'background_toggle');
    
    controls.appendChild(sourceBtn);
    controls.appendChild(imageSourceBtn);
    controls.appendChild(speedBtn);
    controls.appendChild(colorBtn);
    controls.appendChild(downloadBtn);
    controls.appendChild(bgToggle);
    
    const lyricsTitle = document.querySelector('.lyrics-title');
    if (lyricsTitle && lyricsTitle.parentNode) {
        lyricsTitle.parentNode.insertBefore(controls, lyricsTitle.nextSibling);
    }
    
    let speedOffset = parseInt(localStorage.getItem('lyricsSpeedOffset')) || 0;
    
    sourceBtn.addEventListener('click', function(event) {
        event.stopPropagation();
        showLyricsSourceOptions(this);
    });
    
    speedBtn.addEventListener('click', function(event) {
        event.stopPropagation();
        showSpeedOptions(this);
    });
    
    imageSourceBtn.addEventListener('click', function(event) {
        event.stopPropagation();
        showImageSourceOptions(this);
    });

    colorBtn.addEventListener('click', function(event) {
        event.stopPropagation();
        showColorOptions(this);
    });

    downloadBtn.addEventListener('click', function(event) {
        event.stopPropagation();
        showDownloadOptions(this);
    });
    
    bgToggle.addEventListener('click', function(event) {
        event.stopPropagation();
        const bgEnabled = this.classList.toggle('active');
        localStorage.setItem('bgEnabled', bgEnabled);

        const translations = languageTranslations[currentLang] || languageTranslations['zh'];
        const successMsg = bgEnabled 
            ? (translations['background_enabled'] || 'Background enabled')
            : (translations['background_disabled'] || 'Background disabled');
    
        if (typeof showLogMessage === 'function') {
            showLogMessage(successMsg);
        }
        if (colorVoiceEnabled) {
            speakMessage(successMsg);
        }

        updateLyricsBackground();
    });
    
    const savedBgState = localStorage.getItem('bgEnabled');
    if (savedBgState === null) {
        localStorage.setItem('bgEnabled', 'true');
        bgToggle.classList.add('active');
    } else {
        if (savedBgState === 'true') {
            bgToggle.classList.add('active');
        }
    }
}

function showDownloadOptions(button) {
    const existingPanel = document.querySelector('.download-panel');
    if (existingPanel) {
        existingPanel.remove();
        return;
    }
    
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    const currentSong = songs[currentTrackIndex];
    
    if (!currentSong) {
        const errorMsg = translations['no_song_playing'] || 'No song currently playing';
        if (typeof showLogMessage === 'function') {
            showLogMessage(errorMsg);
        }
        return;
    }
    
    const songName = decodeURIComponent(currentSong.split('/').pop().replace(/\.\w+$/, ''));
    const { artist, title } = parseSongInfo(songName);
    
    const panel = document.createElement('div');
    panel.className = 'download-panel';
    panel.innerHTML = `
        <div class="source-title">${translations['download_options'] || 'Download Options'}</div>
        <div class="download-info">
            <div class="download-song-title">${title || 'Unknown Title'}</div>
            <div class="download-song-artist">${artist || 'Unknown Artist'}</div>
        </div>
        <div class="download-options">
            <button class="download-option-btn" data-type="audio">
                <i class="fas fa-music"></i>
                <span>${translations['download_audio'] || 'Download Audio'}</span>
            </button>
            <button class="download-option-btn" data-type="lyrics">
                <i class="fas fa-file-text"></i>
                <span>${translations['download_lyrics'] || 'Download Lyrics'}</span>
            </button>
        </div>
        <div class="download-actions">
            <button class="download-cancel">${translations['cancel'] || 'Cancel'}</button>
        </div>
    `;
    
    document.body.appendChild(panel);
    
    panel.querySelectorAll('.download-option-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const downloadType = this.dataset.type;
            
            switch (downloadType) {
                case 'audio':
                    downloadAudio();
                    break;
                case 'lyrics':
                    downloadLyrics();
                    break;
            }
            
            panel.remove();
        });
    });
    
    panel.querySelector('.download-cancel').addEventListener('click', function() {
        panel.remove();
    });
    
    setTimeout(() => {
        const closeHandler = (e) => {
            if (!panel.contains(e.target) && e.target !== button) {
                panel.remove();
                document.removeEventListener('click', closeHandler);
            }
        };
        document.addEventListener('click', closeHandler);
    }, 100);
}

function downloadAudio() {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    
    const currentSong = songs[currentTrackIndex];
    if (!currentSong) {
        const errorMsg = translations['no_song_playing'] || 'No song currently playing';
        if (typeof showLogMessage === 'function') {
            showLogMessage(errorMsg);
        }
        return;
    }
    
    try {

        const songName = decodeURIComponent(currentSong.split('/').pop().replace(/\.\w+$/, ''));
        const { artist, title } = parseSongInfo(songName);
        
        let downloadUrl = currentSong;
        if (downloadUrl.startsWith('/')) {
            downloadUrl = window.location.origin + downloadUrl;
        } else if (!downloadUrl.startsWith('http')) {
            const baseUrl = currentPlaylistUrl.substring(0, currentPlaylistUrl.lastIndexOf('/') + 1);
            downloadUrl = baseUrl + downloadUrl;
        }
                
        window.open(downloadUrl, '_blank');
        
        const successMsg = translations['audio_downloaded'] || 'Audio download started in new tab';
        if (typeof showLogMessage === 'function') {
            showLogMessage(successMsg);
        }
        if (colorVoiceEnabled) {
            speakMessage(successMsg);
        }
        
    } catch (error) {
        console.error('Download audio error:', error);
        const errorMsg = translations['download_failed'] || 'Download failed';
        if (typeof showLogMessage === 'function') {
            showLogMessage(errorMsg);
        }
    }
}

function downloadLyrics() {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    
    if (!window.lyrics || Object.keys(window.lyrics).length === 0) {
        const errorMsg = translations['no_lyrics_available'] || 'No lyrics available to download';
        if (typeof showLogMessage === 'function') {
            showLogMessage(errorMsg);
        }
        if (colorVoiceEnabled) {
            speakMessage(errorMsg);
        }
        return;
    }
    
    const currentSong = songs[currentTrackIndex];
    if (!currentSong) {
        const errorMsg = translations['no_song_playing'] || 'No song currently playing';
        if (typeof showLogMessage === 'function') {
            showLogMessage(errorMsg);
        }
        if (colorVoiceEnabled) {
            speakMessage(errorMsg);
        }
        return;
    }
    
    const songName = decodeURIComponent(currentSong.split('/').pop().replace(/\.\w+$/, ''));
    const { artist, title } = parseSongInfo(songName);
    
    let lyricsContent = '';
    
    lyricsContent += `Title: ${title || 'Unknown'}\n`;
    lyricsContent += `Artist: ${artist || 'Unknown'}\n`;
    lyricsContent += `Download Time: ${new Date().toLocaleString()}\n`;
    lyricsContent += '='.repeat(40) + '\n\n';
    
    const lyricsArray = Object.entries(window.lyrics)
        .sort((a, b) => parseFloat(a[0]) - parseFloat(b[0]));
    
    for (const [time, text] of lyricsArray) {
        if (text.trim()) {
            const minutes = Math.floor(time / 60);
            const seconds = (time % 60).toFixed(2).padStart(5, '0');
            lyricsContent += `[${minutes}:${seconds}] ${text}\n`;
        }
    }
    
    const blob = new Blob([lyricsContent], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const filename = `${artist || 'Unknown'} - ${title || 'Unknown'}.lrc`.replace(/[<>:"/\\|?*]/g, '_');
    
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.style.display = 'none';
    
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    
    URL.revokeObjectURL(url);
    
    const successMsg = translations['lyrics_downloaded'] || 'Lyrics downloaded successfully';
    if (typeof showLogMessage === 'function') {
        showLogMessage(successMsg);
    }
    if (colorVoiceEnabled) {
        speakMessage(successMsg);
    }
}

function showLyricsSourceOptions(button) {
    const existingPanel = document.querySelector('.source-panel');
    if (existingPanel) {
        existingPanel.remove();
        return;
    }
    
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    const currentSource = localStorage.getItem('lyricsSource') || 'auto';
    
    const panel = document.createElement('div');
    panel.className = 'source-panel';
    panel.innerHTML = `
        <div class="source-title">${translations['lyrics_source'] || 'Lyrics Source'}</div>
        <div class="source-options">
            <label class="source-option">
                <input type="radio" name="lyricsSource" value="auto" ${currentSource === 'auto' ? 'checked' : ''}>
                <span>${translations['auto_select'] || 'Auto Select'}</span>
            </label>
            <label class="source-option">
                <input type="radio" name="lyricsSource" value="netease" ${currentSource === 'netease' ? 'checked' : ''}>
                <span>${translations['netease_music'] || 'Netease Music'}</span>
            </label>
            <label class="source-option">
                <input type="radio" name="lyricsSource" value="kugou" ${currentSource === 'kugou' ? 'checked' : ''}>
                <span>${translations['kugou_music'] || 'Kugou Music'}</span>
            </label>
            <label class="source-option">
                <input type="radio" name="lyricsSource" value="lrclib" ${currentSource === 'lrclib' ? 'checked' : ''}>
                <span>LRCLib</span>
            </label>
        </div>
        <div class="source-actions">
            <button class="source-confirm">${translations['modal_confirm'] || 'Confirm'}</button>
        </div>
    `;
    
    document.body.appendChild(panel);
    
    panel.querySelector('.source-confirm').addEventListener('click', function() {
        const selectedSource = panel.querySelector('input[name="lyricsSource"]:checked').value;
        localStorage.setItem('lyricsSource', selectedSource);
        
        const sourceNames = {
            'auto': translations['auto_select'] || 'Auto Select',
            'netease': translations['netease_music'] || 'Netease Music',
            'kugou': translations['kugou_music'] || 'Kugou Music',
            'lrclib': 'LRCLib'
        };
        const successMsg = `${translations['lyrics_source_set'] || 'Lyrics source set to'}: ${sourceNames[selectedSource]}`;
        
        if (typeof showLogMessage === 'function') {
            showLogMessage(successMsg);
        }
        if (colorVoiceEnabled) {
            speakMessage(successMsg);
        }
        
        if (songs[currentTrackIndex]) {
            loadLyrics(songs[currentTrackIndex]);
        }
        
        panel.remove();
    });
    
    setTimeout(() => {
        const closeHandler = (e) => {
            if (!panel.contains(e.target) && e.target !== button) {
                panel.remove();
                document.removeEventListener('click', closeHandler);
            }
        };
        document.addEventListener('click', closeHandler);
    }, 100);
}

function showImageSourceOptions(button) {
    const existingPanel = document.querySelector('.image-source-panel');
    if (existingPanel) {
        existingPanel.remove();
        return;
    }
    
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    const currentSource = localStorage.getItem('imageSource') || 'auto';
    
    const panel = document.createElement('div');
    panel.className = 'image-source-panel';
    panel.innerHTML = `
        <div class="source-title">${translations['image_source'] || 'Image Source'}</div>
        <div class="source-options">
            <label class="source-option">
                <input type="radio" name="imageSource" value="auto" ${currentSource === 'auto' ? 'checked' : ''}>
                <span>${translations['auto_select'] || 'Auto Select'}</span>
            </label>
            <label class="source-option">
                <input type="radio" name="imageSource" value="netease" ${currentSource === 'netease' ? 'checked' : ''}>
                <span>${translations['netease_music'] || 'Netease Music'}</span>
            </label>
            <label class="source-option">
                <input type="radio" name="imageSource" value="itunes" ${currentSource === 'itunes' ? 'checked' : ''}>
                <span>iTunes</span>
            </label>
            <label class="source-option">
                <input type="radio" name="imageSource" value="lastfm" ${currentSource === 'lastfm' ? 'checked' : ''}>
                <span>Last.fm</span>
            </label>
            <label class="source-option">
                <input type="radio" name="imageSource" value="deezer" ${currentSource === 'deezer' ? 'checked' : ''}>
                <span>Deezer</span>
            </label>
        </div>
        <div class="source-actions">
            <button class="source-confirm">${translations['modal_confirm'] || 'Confirm'}</button>
        </div>
    `;
    
    document.body.appendChild(panel);
    
    panel.querySelector('.source-confirm').addEventListener('click', function() {
        const selectedSource = panel.querySelector('input[name="imageSource"]:checked').value;
        localStorage.setItem('imageSource', selectedSource);
        
        const sourceNames = {
            'auto': translations['auto_select'] || 'Auto Select',
            'netease': translations['netease_music'] || 'Netease Music',
            'itunes': 'iTunes',
            'lastfm': 'Last.fm',
            'deezer': 'Deezer'
        };
        const successMsg = `${translations['image_source_set'] || 'Image source set to'}: ${sourceNames[selectedSource]}`;
        
        if (typeof showLogMessage === 'function') {
            showLogMessage(successMsg);
        }
        if (colorVoiceEnabled) {
            speakMessage(successMsg);
        }
        
        if (songs[currentTrackIndex]) {
            const songName = decodeURIComponent(songs[currentTrackIndex].split('/').pop().replace(/\.\w+$/, ''));
            const { artist, title } = parseSongInfo(songName);
            if (artist) {
                fetchArtistImage(artist, title);
            }
        }
        
        panel.remove();
    });
    
    setTimeout(() => {
        const closeHandler = (e) => {
            if (!panel.contains(e.target) && e.target !== button) {
                panel.remove();
                document.removeEventListener('click', closeHandler);
            }
        };
        document.addEventListener('click', closeHandler);
    }, 100);
}

function showSpeedOptions(button) {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];  
    const existingPanel = document.querySelector('.speed-panel');
    if (existingPanel) {
        existingPanel.remove();
        return;
    }
    
    const currentOffset = parseInt(localStorage.getItem('lyricsSpeedOffset')) || 0;
    
    const panel = document.createElement('div');
    panel.className = 'speed-panel';
    panel.innerHTML = `
        <div class="speed-title">${translations['lyrics_time_offset'] || 'Lyrics Time Offset'}</div>
        <div class="speed-value">${currentOffset > 0 ? '+' : ''}${currentOffset}${translations['seconds'] || 's'}</div>
        <div class="speed-controls">
            <button class="speed-btn" data-change="-1">-1${translations['seconds'] || 's'}</button>
            <button class="speed-btn" data-change="-0.5">-0.5${translations['seconds'] || 's'}</button>
            <button class="speed-btn reset" data-change="0">${translations['reset'] || 'Reset'}</button>
            <button class="speed-btn" data-change="0.5">+0.5${translations['seconds'] || 's'}</button>
            <button class="speed-btn" data-change="1">+1${translations['seconds'] || 's'}</button>
        </div>
    `;
    
    document.body.appendChild(panel);
    
    panel.querySelectorAll('.speed-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const change = parseFloat(this.dataset.change);
            const newOffset = change === 0 ? 0 : (parseInt(localStorage.getItem('lyricsSpeedOffset')) || 0) + change;
            
            localStorage.setItem('lyricsSpeedOffset', newOffset.toString());
            
            panel.querySelector('.speed-value').textContent = 
                `${newOffset > 0 ? '+' : ''}${newOffset}${translations['seconds'] || 's'}`;
            
            let successMsg = '';
            if (change === 0) {
                successMsg = translations['lyrics_offset_reset'] || 'Lyrics time offset reset';
            } else {
                const offsetText = `${newOffset > 0 ? '+' : ''}${newOffset}${translations['seconds'] || 's'}`;
                successMsg = `${translations['lyrics_offset_set'] || 'Lyrics time offset set to'} ${offsetText}`;
            }
            
            if (typeof showLogMessage === 'function') {
                showLogMessage(successMsg);
            }
            if (colorVoiceEnabled) {
                speakMessage(successMsg);
            }
            
            if (window.lyrics && Object.keys(window.lyrics).length > 0) {
                syncLyrics();
            }
        });
    });
    
    setTimeout(() => {
        const closeHandler = (e) => {
            if (!panel.contains(e.target) && e.target !== button) {
                panel.remove();
                document.removeEventListener('click', closeHandler);
            }
        };
        document.addEventListener('click', closeHandler);
    }, 100);
}

function showColorOptions(button) {
    const existingPanel = document.querySelector('.color-panel');
    if (existingPanel) {
        existingPanel.remove();
        return;
    }
    
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    const currentHue = localStorage.getItem('lyricsColorHue') || '250';
    const currentLightness = localStorage.getItem('lyricsLightness') || '98%';
    const currentChroma = localStorage.getItem('lyricsChroma') || '0';

    const panel = document.createElement('div');
    panel.className = 'color-panel';
    panel.innerHTML = `
        <div class="source-title">${translations['font_color']}</div>
        <div class="color-options">
            <label class="color-option" data-hue="0" data-lightness="85%" data-chroma="0.18">
                <div class="color-preview" style="background: oklch(85% 0.18 0)"></div>
                <span>${translations['red']}</span>
            </label>
            <label class="color-option" data-hue="30" data-lightness="85%" data-chroma="0.18">
                <div class="color-preview" style="background: oklch(85% 0.18 30)"></div>
                <span>${translations['orange']}</span>
            </label>
            <label class="color-option" data-hue="60" data-lightness="85%" data-chroma="0.18">
                <div class="color-preview" style="background: oklch(85% 0.18 60)"></div>
                <span>${translations['yellow']}</span>
            </label>
            <label class="color-option" data-hue="120" data-lightness="85%" data-chroma="0.18">
                <div class="color-preview" style="background: oklch(85% 0.18 120)"></div>
                <span>${translations['green']}</span>
            </label>
            <label class="color-option" data-hue="180" data-lightness="85%" data-chroma="0.18">
                <div class="color-preview" style="background: oklch(85% 0.18 180)"></div>
                <span>${translations['cyan']}</span>
            </label>
            <label class="color-option" data-hue="240" data-lightness="85%" data-chroma="0.18">
                <div class="color-preview" style="background: oklch(85% 0.18 240)"></div>
                <span>${translations['blue']}</span>
            </label>
            <label class="color-option" data-hue="300" data-lightness="85%" data-chroma="0.18">
                <div class="color-preview" style="background: oklch(85% 0.18 300)"></div>
                <span>${translations['purple']}</span>
            </label>
            <label class="color-option" data-hue="250" data-lightness="98%" data-chroma="0">
                <div class="color-preview" style="background: oklch(98% 0 250)"></div>
                <span>${translations['white']}</span>
            </label>
            <label class="color-option" data-hue="0" data-lightness="75%" data-chroma="0.03">
                <div class="color-preview" style="background: oklch(75% 0.03 0)"></div>
                <span>${translations['light_gray']}</span>
            </label>
            <label class="color-option" data-hue="0" data-lightness="50%" data-chroma="0.03">
                <div class="color-preview" style="background: oklch(50% 0.03 0)"></div>
                <span>${translations['gray']}</span>
            </label>
            <label class="color-option" data-hue="0" data-lightness="25%" data-chroma="0.03">
                <div class="color-preview" style="background: oklch(25% 0.03 0)"></div>
                <span>${translations['dark_gray']}</span>
            </label>
            <label class="color-option" data-hue="0" data-lightness="15%" data-chroma="0.03">
                <div class="color-preview" style="background: oklch(15% 0.03 0)"></div>
                <span>${translations['black']}</span>
            </label>
        </div>
        <div class="source-actions">
            <button class="source-confirm">${translations['modal_confirm']}</button>
        </div>
    `;
    
    document.body.appendChild(panel);

    const currentOption = panel.querySelector(
        `.color-option[data-hue="${currentHue}"][data-lightness="${currentLightness}"][data-chroma="${currentChroma}"]`
    );
    if (currentOption) currentOption.classList.add('selected');

    panel.querySelectorAll('.color-option').forEach(option => {
        option.addEventListener('click', function() {
            panel.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    panel.querySelector('.source-confirm').addEventListener('click', function() {
        const selectedOption = panel.querySelector('.color-option.selected');
        if (selectedOption) {
            const hueValue = selectedOption.getAttribute('data-hue');
            const lightnessValue = selectedOption.getAttribute('data-lightness');
            const chromaValue = selectedOption.getAttribute('data-chroma');

            localStorage.setItem('lyricsColorHue', hueValue);
            localStorage.setItem('lyricsLightness', lightnessValue);
            localStorage.setItem('lyricsChroma', chromaValue);

            applyLyricsColor(hueValue, lightnessValue, chromaValue);
            
            const colorNames = {
                '0_85%_0.18': translations['red'],
                '30_85%_0.18': translations['orange'],
                '60_85%_0.18': translations['yellow'],
                '120_85%_0.18': translations['green'],
                '180_85%_0.18': translations['cyan'],
                '240_85%_0.18': translations['blue'],
                '300_85%_0.18': translations['purple'],
                '250_98%_0': translations['white'],
                '0_75%_0.03': translations['light_gray'],
                '0_50%_0.03': translations['gray'],
                '0_25%_0.03': translations['dark_gray'],
                '0_15%_0.03': translations['black']
            };
            
            const colorKey = `${hueValue}_${lightnessValue}_${chromaValue}`;
            const successMsg = `${translations['font_color_set']}: ${colorNames[colorKey]}`;
            
            if (typeof showLogMessage === 'function') {
                showLogMessage(successMsg);
            }
            if (colorVoiceEnabled) {
                speakMessage(successMsg);
            }
        }
        panel.remove();
    });

    setTimeout(() => {
        const closeHandler = (e) => {
            if (!panel.contains(e.target) && e.target !== button) {
                panel.remove();
                document.removeEventListener('click', closeHandler);
            }
        };
        document.addEventListener('click', closeHandler);
    }, 100);
}

function applyLyricsColor(h, l, c) {
    document.documentElement.style.setProperty('--lyrics-hue', h);
    document.documentElement.style.setProperty('--lyrics-lightness', l);
    document.documentElement.style.setProperty('--lyrics-chroma', c);
}

function initLyricsColor() {
    const savedHue = localStorage.getItem('lyricsColorHue') || '250';
    const savedLightness = localStorage.getItem('lyricsLightness') || '98%';
    const savedChroma = localStorage.getItem('lyricsChroma') || '0';

    applyLyricsColor(savedHue, savedLightness, savedChroma);
}

function applyExpandedLayout() {
    const playlistSection = document.querySelector('.playlist-section');
    const lyricsSection = document.querySelector('.lyrics-section');
    const lyricsTitle = document.querySelector('.lyrics-title');
    
    playlistSection.classList.add('hidden');
    lyricsSection.classList.add('expanded');
    lyricsTitle.classList.add('hidden');

    const lyricsControls = document.querySelector('.lyrics-controls');
    if (lyricsControls) {
        lyricsControls.style.display = 'flex';
    }
    
    localStorage.setItem('lyricsExpanded', 'true');
    forceUpdateBackground();
}

function applyNormalLayout() {
    const playlistSection = document.querySelector('.playlist-section');
    const lyricsSection = document.querySelector('.lyrics-section');
    const lyricsTitle = document.querySelector('.lyrics-title');
    
    playlistSection.classList.remove('hidden');
    lyricsSection.classList.remove('expanded');
    lyricsTitle.classList.remove('hidden');

    const lyricsControls = document.querySelector('.lyrics-controls');
    if (lyricsControls) {
        lyricsControls.style.display = 'none';
    }

    localStorage.setItem('lyricsExpanded', 'false');
    forceUpdateBackground();
}

function ensureOverlay(lyricsContent) {
    if (!lyricsContent) return null;
    let overlay = lyricsContent.querySelector('.bg-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'bg-overlay';
        lyricsContent.appendChild(overlay);
    }
    overlay.style.display = 'block';
    overlay.style.opacity = '1';
    return overlay;
}

function removeOverlay(lyricsContent) {
    if (!lyricsContent) return;
    const overlay = lyricsContent.querySelector('.bg-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 200);
    }
}

function forceUpdateBackground() {
    const lyricsContent = document.querySelector('.lyrics-content');
    const isExpanded = localStorage.getItem('lyricsExpanded') === 'true';
    const bgToggle = document.getElementById('bgToggle');
    const bgEnabled = bgToggle && bgToggle.classList.contains('active');
    
    removeLyricsBackground();
    
    if (isExpanded && bgEnabled) {
        const artistImg = document.getElementById('artistImg');
        if (artistImg && artistImg.src && artistImg.style.display !== 'none') {
            lyricsContent.style.backgroundImage = `url('${artistImg.src}')`;
            lyricsContent.style.backgroundSize = 'contain';
            lyricsContent.style.backgroundPosition = 'center';
            lyricsContent.classList.add('has-background-image');
            ensureOverlay(lyricsContent);
        }
    }
}

function initArtistImageToggle() {
    const artistImage = document.getElementById('artistImage');
    
    if (artistImage) {
        artistImage.addEventListener('click', function() {
            const isExpanded = localStorage.getItem('lyricsExpanded') === 'true';
            
            if (isExpanded) {
                applyNormalLayout();
            } else {
                applyExpandedLayout();
            }
        });
    }
}

function updateLyricsBackground() {
    forceUpdateBackground();
}

function removeLyricsBackground() {
    const lyricsContent = document.querySelector('.lyrics-content');
    if (lyricsContent) {
        lyricsContent.style.backgroundImage = '';
        lyricsContent.style.backgroundSize = '';
        lyricsContent.style.backgroundPosition = '';
        lyricsContent.classList.remove('has-background-image'); 

        removeOverlay(lyricsContent);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    createLyricsControls();
    initLayoutState();
    initLyricsColor();
    initArtistImageToggle();
});

function savePlaylistUrl() {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];  
    const urlInput = document.getElementById('playlistUrl');
    const newUrl = urlInput.value.trim();
    if (newUrl) {
        fetch('/spectra/playlist_config.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ url: newUrl })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentPlaylistUrl = newUrl;
                    loadDefaultPlaylist();
                    showLogMessage(translations['playlist_url_saved'] || 'Playlist URL saved successfully');
                    speakMessage(translations['playlist_url_saved'] || 'Playlist URL saved successfully');
                } else {
                    showLogMessage(translations['save_fail'] || data.message || 'Failed to save playlist URL', 'error');
                    speakMessage(translations['save_fail'] || data.message || 'Failed to save playlist URL');
                }
            })
            .catch(error => {
                console.error('Failed to save playlist URL:', error);
                showLogMessage(translations['save_fail'] || 'Failed to save playlist URL', 'error');
                speakMessage(translations['save_fail'] || 'Failed to save playlist URL');
            });
    } else {
        showLogMessage(translations['invalid_url'] || 'Please enter a valid URL', 'error');
        speakMessage(translations['invalid_url'] || 'Please enter a valid URL');
    }
    closeUrlModal();
}

function resetToDefault() {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];  
    const defaultUrl = 'https://raw.githubusercontent.com/Thaolga/Rules/main/music/songs.txt';
    fetch('/spectra/playlist_config.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ url: defaultUrl })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentPlaylistUrl = defaultUrl;
                document.getElementById('playlistUrl').value = defaultUrl;
                loadDefaultPlaylist();
                showLogMessage(translations['reset_to_default'] || 'Reset to default playlist');
                speakMessage(translations['reset_to_default'] || 'Reset to default playlist');
            } else {
                showLogMessage(translations['reset_failed'] || data.message || 'Failed to reset to default playlist', 'error');
                speakMessage(translations['reset_failed'] || data.message || 'Failed to reset to default playlist');
            }
        })
        .catch(error => {
            console.error('Failed to reset playlist URL:', error);
            showLogMessage(translations['reset_failed'] || 'Failed to reset to default playlist', 'error');
            speakMessage(translations['reset_failed'] || 'Failed to reset to default playlist');
        });
    closeUrlModal();
}

function openMusicModal() {
    const controlPanel = document.getElementById('control-panel-modal');
    if (controlPanel) {
        try {
            UIkit.modal(controlPanel).hide();
        } catch (e) {}
    }
    const modal = document.getElementById('musicModal');
    modal.style.display = 'block';
    updatePlayButton();
    updatePlaylistUI();
}

function closeMusicModal() {
    const modal = document.getElementById('musicModal');
    modal.style.display = 'none';
}

function updatePlayerTranslations() {
    if (typeof updateUIText === 'function') {
        updateUIText();
    }
}

window.addEventListener('click', function (event) {
    const modal = document.getElementById('musicModal');
    if (modal.style.display === 'block' && event.target === modal) {
        closeMusicModal();
    }
});

function togglePlay() {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];    
    if (isPlaying) {
        audioPlayer.pause();
        const pauseMessage = translations['pause_playing'] || 'Pause_Playing';
        showLogMessage(pauseMessage);
        speakMessage(pauseMessage);
        
        if (audioVisualizer && audioVisualizer.isEnabled && audioVisualizer.isVisible) {
            audioVisualizer.stop();
        }
    } else {
        audioPlayer.play().then(() => {
            const playMessage = translations['start_playing'] || 'Start_Playing';
            showLogMessage(playMessage);
            speakMessage(playMessage);
            
            if (audioVisualizer && audioVisualizer.isEnabled && audioVisualizer.isVisible) {
                audioVisualizer.start();
            }
        }).catch((error) => {
            console.log(`${translations['autoplay_blocked'] || 'Autoplay Blocked'}:`, error);
            showLogMessage(translations['click_to_play'] || 'Click play button to start');
        });
    }
    isPlaying = !isPlaying;
    updatePlayButton();
    savePlayerState();

    const btn = event.target.closest('button');
    if (btn) {
        btn.classList.add('clicked');
        setTimeout(() => btn.classList.remove('clicked'), 200);
    }
}

function updatePlayButton() {
    const btn = document.getElementById('playPauseBtn');
    const floatingBtn = document.getElementById('floatingPlayBtn');
    const icon = isPlaying ? 'bi-pause-fill' : 'bi-play-fill';
    if (btn) btn.innerHTML = `<i class="bi ${icon}"></i>`;
    if (floatingBtn) floatingBtn.innerHTML = `<i class="bi ${icon}"></i>`;
}

function changeTrack(direction, isManual = false) {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];    
    const oldSong = songs[currentTrackIndex];
    if (repeatMode === 2 && !isManual) { 
        currentTrackIndex = Math.floor(Math.random() * songs.length);
    } else {
        currentTrackIndex = (currentTrackIndex + direction + songs.length) % songs.length;
    }

    const songName = decodeURIComponent(
        songs[currentTrackIndex].split('/').pop().replace(/\.\w+$/, '')
    );

    if (isManual) {
        const action = direction === -1
            ? translations['previous_track'] || 'Previous Track'
            : translations['next_track'] || 'Next Track';
        showLogMessage(`${translations['manual_switch'] || 'Manual Switch'}${action}：${songName}`);
        speakMessage(`${translations['switch_to'] || 'Switch to'}${action}：${songName}`);
    } else {
        showLogMessage(`${translations['auto_switch'] || 'Auto Switch to'}：${songName}`);
        speakMessage(`${translations['auto_play'] || 'Auto Play'}：${songName}`);
    }

    loadTrack(songs[currentTrackIndex]);
}

function toggleRepeat() {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    repeatMode = (repeatMode + 1) % 3;

    const mainBtn = document.getElementById('repeatBtn');
    const floatingBtn = document.getElementById('floatingRepeatBtn');

    const tooltipKeys = ['order_play', 'single_loop', 'shuffle_play'];
    const iconClasses = ['bi bi-arrow-repeat', 'bi bi-repeat-1', 'bi bi-shuffle'];

    [mainBtn, floatingBtn].forEach(btn => {
        if (!btn) return;

        btn.classList.remove('btn-success', 'btn-warning');

        btn.setAttribute('data-tooltip-title', tooltipKeys[repeatMode]);

        btn.removeAttribute('title');

        let icon = btn.querySelector('i');
        if (!icon) {
            icon = document.createElement('i');
            btn.prepend(icon);
        }
        icon.className = iconClasses[repeatMode];

        if (repeatMode === 1) btn.classList.add('btn-success');
        if (repeatMode === 2) btn.classList.add('btn-warning');
    });

    updateUIText();

    const msg = translations[tooltipKeys[repeatMode]] || tooltipKeys[repeatMode];
    showLogMessage(msg);
    speakMessage(msg);
    savePlayerState();
}

function updatePlaylistUI() {
    const playlist = document.getElementById('playlist');
    if (playlist) {
        playlist.innerHTML = songs.map((url, index) => `
            <div class="playlist-item ${index === currentTrackIndex ? 'active' : ''} ${!isPlaying && index === currentTrackIndex ? 'paused' : ''}" 
                 onclick="playTrack(${index})">
                ${decodeURIComponent(url.split('/').pop().replace(/\.\w+$/, ''))}
            </div>
        `).join('');
        setTimeout(() => scrollToCurrentTrack(), 100);
    }
}

function fetchArtistImage(artist, title) {
    const artistImage = document.getElementById('artistImage');
    const artistImg = document.getElementById('artistImg');
    
    if (!artist) {
        resetArtistImage();
        return;
    }

    if (artistImageLoaded) {
        artistImage.classList.remove('loading');
        return;
    }
    
    artistImage.classList.add('loading');
    fetchArtistImageViaProxy(artist, title);
}

function fetchArtistImageViaProxy(artist, title) {
    const proxyUrl = '/spectra/artist_proxy.php';
    const imageSource = localStorage.getItem('imageSource') || 'auto';
    
    fetch(proxyUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            artist: artist,
            title: title,
            source: imageSource
        })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success && data.imageUrl) {
            updateArtistImage(data.imageUrl);
        } else {
            resetArtistImage();
        }
    })
    .catch(error => {
        console.error('Artist image proxy failed:', error);
        resetArtistImage();
    });
}

function updateArtistImage(imageUrl) {
    const artistImage = document.getElementById('artistImage');
    const artistImg = document.getElementById('artistImg');
    const defaultAvatar = artistImage.querySelector('.default-avatar');
    
    const img = new Image();
    img.onload = function() {
        artistImg.src = imageUrl;
        artistImg.style.display = 'block';
        if (defaultAvatar) {
            defaultAvatar.style.display = 'none';
        }
        artistImage.classList.remove('loading');
        updateLyricsBackground();
    };
    img.onerror = function() {
        resetArtistImage();
    };
    img.src = imageUrl;
}

function resetArtistImage() {
    const artistImage = document.getElementById('artistImage');
    const artistImg = document.getElementById('artistImg');
    const defaultAvatar = artistImage.querySelector('.default-avatar');
    
    artistImg.style.display = 'none';
    artistImg.src = '';
    if (defaultAvatar) {
        defaultAvatar.style.display = 'flex';
    }
    artistImage.classList.remove('loading');
    updateLyricsBackground();
}

function updatePlaylist() {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];  
    const btn = document.getElementById('updatePlaylistBtn');
    if (btn) {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-arrow-repeat spinning"></i>';
        
        fetch(currentPlaylistUrl)
            .then(response => response.text())
            .then(data => {
                const newSongs = data.split('\n').filter(url => url.trim());
                songs = newSongs;
                localStorage.setItem(`${STORAGE_PREFIX}cachedPlaylist`, JSON.stringify(songs));
                if (currentTrackIndex >= songs.length) {
                    currentTrackIndex = 0;
                    savePlayerState();
                }
                updatePlaylistUI();
                if (songs.length > 0 && songs[currentTrackIndex]) {
                    loadTrack(songs[currentTrackIndex]);
                }
                btn.innerHTML = originalHTML;
                const successMsg = translations['playlist_updated'] || 'Playlist updated successfully';
                const songCountMsg = translations['song_count']?.replace('{count}', songs.length) || `Total ${songs.length} songs`;
                showLogMessage(`${successMsg}，${songCountMsg}`);
                speakMessage(`${successMsg}，${songCountMsg}`);
            })
            .catch(error => {
                console.error('Playlist update failed:', error);
                btn.innerHTML = '<i class="bi bi-x-circle"></i>';
                setTimeout(() => btn.innerHTML = originalHTML, 1000);
                const errorMsg = translations['update_failed'] || 'Playlist update failed';
                showLogMessage(errorMsg, 'error');
                speakMessage(errorMsg);
            });
    }
}

function playTrack(index) {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];    
    const songName = decodeURIComponent(songs[index].split('/').pop().replace(/\.\w+$/, ''));
    const message = `${translations['playlist_click'] || 'Playlist Click'}：${translations['index'] || 'Index'}：${index + 1}，${translations['song_name'] || 'Song Name'}：${songName}`;
    
    if (audioVisualizer && audioVisualizer.isEnabled && audioVisualizer.isVisible) {
        audioVisualizer.stop();
    }
    
    audioPlayer.pause();
    audioPlayer.removeEventListener('timeupdate', syncLyrics);
    audioPlayer.removeEventListener('timeupdate', syncPlainLyrics);
    
    currentTrackIndex = index;
    loadTrack(songs[index]);
    isPlaying = true;
    
    audioPlayer.play().catch((error) => {
        isPlaying = false;
        console.error('Play failed:', error);
    });
    
    updatePlayButton();
    savePlayerState();
    showLogMessage(message);
    speakMessage(message);
    updateCurrentSong(songs[index]);
    
    // if (event && event.target) {
    //     event.target.classList.add('clicked');
    //     setTimeout(() => {
    //         if (event.target) {
    //             event.target.classList.remove('clicked');
    //         }
    //     }, 200);
    // }
}

function scrollToCurrentTrack() {
    const playlist = document.getElementById('playlist');
    const activeItem = playlist.querySelector('.playlist-item.active');
    if (activeItem) {
        activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function parseSongInfo(filename) {
    const cleanName = filename.replace(/\.(mp3|flac|wav|m4a|aac|webm)$/i, '');
    
    const separators = [' - ', ' – ', ' — ', ' _ ', ' | ', ' by '];
    
    let artist = '';
    let title = cleanName;
    
    for (const sep of separators) {
        if (cleanName.includes(sep)) {
            const parts = cleanName.split(sep);
            if (parts.length >= 2) {
                artist = parts[0].trim();
                title = parts.slice(1).join(sep).trim();
                break;
            }
        }
    }
    
    if (!artist && cleanName.includes('-') && !cleanName.includes(' - ')) {
        const parts = cleanName.split('-');
        if (parts.length === 2) {
            artist = parts[0].trim();
            title = parts[1].trim();
        }
    }
    
    artist = artist.replace(/\[.*?\]|\(.*?\)/g, '').trim();
    title = title.replace(/\[.*?\]|\(.*?\)/g, '').trim();
    
    return { artist, title };
}

function loadLyrics(songUrl) {
    const lyricsUrl = songUrl.replace(/\.\w+$/, '.lrc');
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    
    window.lyrics = {};
    window.lyricTimes = [];
    
    const containers = [
        document.getElementById('lyricsContainer'),
        document.querySelector('#floatingLyrics .vertical-lyrics')
    ];
    
    containers.forEach(container => {
        if (container) {
            const message = translations['loading_lyrics'] || 'Loading Lyrics...';
            const verticalText = message.split('').map(char => `<span class="char">${char}</span>`).join('');
            const statusMsg = container.id === 'lyricsContainer' 
                ? `<div id="no-lyrics">${message}</div>`
                : `<div id="noLyricsFloating" class="vertical-message">${verticalText}</div>`;
            container.innerHTML = statusMsg;
        }
    });

    const lyricsSource = localStorage.getItem('lyricsSource') || 'auto';
    
    if (lyricsSource === 'auto') {
        loadGitHubLyricsSilently(lyricsUrl, songUrl);
    } else {
        fetchLyricsFromAPI(songUrl);
    }
}

function loadGitHubLyricsSilently(lyricsUrl, songUrl) {
    const isCurrentSong = songs[currentTrackIndex] === songUrl;
    const shouldLoadLyrics = isCurrentSong && (isPlaying || audioPlayer.src === songUrl);
    
    if (!shouldLoadLyrics) {
        return;
    }
    
    const xhr = new XMLHttpRequest();
    xhr.open('GET', lyricsUrl, true);
    xhr.timeout = 5000;
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    parseLyrics(xhr.responseText);
                    displayLyrics();
                    document.dispatchEvent(new Event('lyricsLoaded'));
                } catch (error) {
                    if (isCurrentSong && isPlaying) {
                        fetchLyricsFromAPI(songUrl);
                    }
                }
            } else {
                if (isCurrentSong && isPlaying) {
                    fetchLyricsFromAPI(songUrl);
                }
            }
        }
    };
    
    xhr.ontimeout = function() {
        if (isCurrentSong && isPlaying) {
            fetchLyricsFromAPI(songUrl);
        }
    };
    
    xhr.onerror = function() {
        if (isCurrentSong && isPlaying) {
            fetchLyricsFromAPI(songUrl);
        }
    };
    
    try {
        xhr.send();
    } catch (e) {
        if (isCurrentSong && isPlaying) {
            fetchLyricsFromAPI(songUrl);
        }
    }
}

function fetchLyricsFromAPI(songUrl) {
    const songName = decodeURIComponent(songUrl.split('/').pop().replace(/\.\w+$/, ''));
    const { artist, title } = parseSongInfo(songName);
    
    // console.log('Lyrics search parameters:', { 
    //     songName, 
    //     artist, 
    //     title,
    //     preferredSource: localStorage.getItem('lyricsSource') || 'auto'
    // });

    const translations = languageTranslations[currentLang] || languageTranslations['zh'];
    const containers = [
        document.getElementById('lyricsContainer'),
        document.querySelector('#floatingLyrics .vertical-lyrics')
    ];

    containers.forEach(container => {
        if (container) {
            const message = translations['searching_lyrics'] || 'Searching for lyrics online...';
            const verticalText = message.split('').map(char => `<span class="char">${char}</span>`).join('');
            const statusMsg = container.id === 'lyricsContainer' 
                ? `<div id="no-lyrics">${message}</div>`
                : `<div id="noLyricsFloating" class="vertical-message">${verticalText}</div>`;
            container.innerHTML = statusMsg;
        }
    });

    const preferredSource = localStorage.getItem('lyricsSource') || 'auto';
    const proxyUrl = '/spectra/lyrics_proxy.php';
    
    // console.log('Sending request to proxy:', { preferredSource, artist, title, songName });
    
    fetch(proxyUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            source: preferredSource,
            artist: artist,
            title: title,
            songName: songName
        })
    })
    .then(response => {
        // console.log('Proxy response status:', response.status);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        // console.log('Proxy returned data:', data);
        if (data.success && data.lyrics) {
            handleLyricsResult(data);
        } else {
            // console.log('Failed to get lyrics:', data.message);
            showNoLyrics(containers, songName, translations);
        }
    })
    .catch(error => {
        // console.error('Lyrics proxy request failed:', error);
        showNoLyrics(containers, songName, translations);
    });
}

function handleLyricsResult(lyricsData) {
    const containers = [
        document.getElementById('lyricsContainer'),
        document.querySelector('#floatingLyrics .vertical-lyrics')
    ];
    
    if (lyricsData.hasTimestamps) {
        processLrcLyrics(lyricsData.lyrics);
    } else {
        displayPlainLyrics(lyricsData.lyrics, containers);
    }
}

function processLrcLyrics(lyricsText) {
    window.lyrics = {};
    window.lyricTimes = [];
    
    audioPlayer.removeEventListener('timeupdate', syncLyrics);
    audioPlayer.removeEventListener('timeupdate', syncPlainLyrics);
    
    parseLyrics(lyricsText);
    displayLyrics();
    document.dispatchEvent(new Event('lyricsLoaded'));
    
    audioPlayer.addEventListener('timeupdate', syncLyrics);
}

function showNoLyrics(containers, songName, translations) {
    containers.forEach(container => {
        if (container) {
            const message = translations['lyrics_not_found'] || 'Lyrics not found online';
            if (container.id === 'lyricsContainer') {
                container.innerHTML = `<div id="no-lyrics">${message}</div>`;
            } else {
                const verticalText = message.split('').map(char => `<span class="char">${char}</span>`).join('');
                container.innerHTML = `<div id="noLyricsFloating" class="vertical-message">${verticalText}</div>`;
            }
        }
    });
}

function displayPlainLyrics(lyricsText, containers) {
    window.lyrics = {};
    window.lyricTimes = [];
    
    audioPlayer.removeEventListener('timeupdate', syncLyrics);
    audioPlayer.removeEventListener('timeupdate', syncPlainLyrics);
    
    const lines = lyricsText.split('\n').filter(line => line.trim() !== '');
    
    if (!containers) {
        containers = [
            document.getElementById('lyricsContainer'),
            document.querySelector('#floatingLyrics .vertical-lyrics')
        ];
    }
    
    containers.forEach(container => {
        if (container) {
            container.innerHTML = '';
            
            if (lines.length === 0) {
                const message = 'No lyrics content';
                if (container.id === 'lyricsContainer') {
                    container.innerHTML = `<div id="no-lyrics">${message}</div>`;
                } else {
                    const verticalText = message.split('').map(char => `<span class="char">${char}</span>`).join('');
                    container.innerHTML = `<div id="noLyricsFloating" class="vertical-message">${verticalText}</div>`;
                }
                return;
            }
            
            lines.forEach((lineText, index) => {
                const line = document.createElement('div');
                line.className = 'lyric-line plain-line';
                line.dataset.lineIndex = index;
                line.textContent = lineText;

                line.addEventListener('click', function() {
                    const lineIndex = parseInt(this.dataset.lineIndex);
                    const duration = audioPlayer.duration || 60;
                    const lineDuration = duration / lines.length;
                    const targetTime = lineIndex * lineDuration;
                    audioPlayer.currentTime = targetTime;
                    
                    if (audioPlayer.paused) {
                        audioPlayer.play();
                        isPlaying = true;
                        updatePlayButton();
                    }
                });
                
                if (container.id === 'lyricsContainer') {
                    container.appendChild(line);
                } else if (index === 0) {
                    const floatingLine = document.createElement('div');
                    floatingLine.className = 'lyric-line plain-line';
                    floatingLine.textContent = lineText;
                    container.appendChild(floatingLine);
                }
            });
        }
    });
    
    audioPlayer.addEventListener('timeupdate', debounce(syncPlainLyrics, 100));
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function syncPlainLyrics() {
    if (!audioPlayer.src || audioPlayer.paused) {
        return;
    }
    
    const currentTime = audioPlayer.currentTime;
    const duration = audioPlayer.duration || 60;
    const lyricsContainer = document.getElementById('lyricsContainer');
    
    if (!lyricsContainer) return;
    
    const lines = lyricsContainer.querySelectorAll('.plain-line');
    if (lines.length === 0) return;
    
    const lineDuration = duration / lines.length;
    const currentLineIndex = Math.min(Math.floor(currentTime / lineDuration), lines.length - 1);
    
    let hasActiveLine = false;
    
    lines.forEach((line, index) => {
        line.classList.remove('highlight', 'active');
        if (index === currentLineIndex) {
            line.classList.add('highlight', 'active');
            hasActiveLine = true;
            
            if (!isHovering && !isManualScroll) {
                const lineRect = line.getBoundingClientRect();
                const containerRect = lyricsContainer.getBoundingClientRect();
                
                if (lineRect.top < containerRect.top + 50 || 
                    lineRect.bottom > containerRect.bottom - 50) {
                    line.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }
    });
    
    const floatingContainer = document.querySelector('#floatingLyrics .vertical-lyrics');
    if (floatingContainer && lines[currentLineIndex]) {
        const currentText = lines[currentLineIndex].textContent;
        if (floatingContainer.textContent !== currentText) {
            floatingContainer.textContent = currentText;
        }
    }
    
    if (!hasActiveLine && currentTime < 5 && lyricsContainer.scrollTop !== 0) {
        lyricsContainer.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function parseLyrics(text) {
    window.lyrics = {};
    window.lyricTimes = [];
    const regex = /\[(\d{1,2}):(\d{1,2})(?:\.(\d{1,3}))?\](.*)/g;
    let match;
    
    while ((match = regex.exec(text)) !== null) {
        const time = parseInt(match[1]) * 60 + parseInt(match[2]) + parseInt(match[3])/1000;
        const content = match[4].replace(/\[\d+:\d+\.\d+\]/g, '').trim();
        
        if (content !== '' && !lyrics[time]) {
            lyrics[time] = content;
            lyricTimes.push(time);
        }
    }
    
    lyricTimes.sort((a, b) => a - b);
}

function isEnglishWord(text) {
    return /^[a-zA-Z']+$/.test(text);
}

function createCharSpans(text, startTime, endTime) {
    const elements = [];
    const words = text.split(/(\s+)/); 
    let currentTime = startTime;
    const totalDuration = endTime - startTime;
    const validWords = words.filter(word => word.trim().length > 0);
    const durationPerWord = totalDuration / validWords.length;

    const hasChinese = /[\u4e00-\u9fff]/.test(text);
    const hasEnglish = /[a-zA-Z]/.test(text);
    const hasProductionMark = /[\/:：|｜]/g.test(text);
    
    let langClass = '';
    if (hasProductionMark) {
        if (hasChinese && hasEnglish) {
            langClass = 'lang-mix-production';
        } else if (hasChinese) {
            langClass = 'lang-zh-production';
        } else {
            langClass = 'lang-other-production';
        }
    } else {
        if (hasChinese && hasEnglish) {
            langClass = 'lang-mix';
        } else if (hasChinese) {
            langClass = 'lang-zh';
        } else {
            langClass = 'lang-other';
        }
    }

    words.forEach(word => {
        if (word.trim().length === 0) return;
        const isEnglish = isEnglishWord(word.replace(/[^a-zA-Z']/g, ''));
        const span = document.createElement('span');
        span.className = 'char ' + langClass;
        if (isEnglish) {
            span.textContent = word;
            span.dataset.start = currentTime;
            span.dataset.end = currentTime + durationPerWord;
            currentTime += durationPerWord;
        } else {
            const chars = word.split('');
            const charDuration = durationPerWord / chars.length;
            chars.forEach((char, index) => {
                const charSpan = document.createElement('span');
                charSpan.className = 'char ' + langClass;
                charSpan.textContent = char;
                charSpan.dataset.start = currentTime + index * charDuration;
                charSpan.dataset.end = currentTime + (index + 1) * charDuration;
                elements.push(charSpan);
            });
            currentTime += durationPerWord;
            return;
        }
        elements.push(span);
    });
    return elements;
}

function displayLyrics() {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];    
    const lyricsContainer = document.getElementById('lyricsContainer');
    const floatingLyrics = document.querySelector('#floatingLyrics .vertical-lyrics');
    if (lyricsContainer) lyricsContainer.innerHTML = '';
    if (floatingLyrics) floatingLyrics.innerHTML = '';

    if (Object.keys(window.lyrics).length === 0) {
        const message = translations['no_lyrics'] || 'No Lyrics Available';
        const verticalText = message.split('').map(char => `<span class="char">${char}</span>`).join('');
        if (lyricsContainer) lyricsContainer.innerHTML = `<div id="no-lyrics">${message}</div>`;
        if (floatingLyrics) floatingLyrics.innerHTML = `<div id="noLyricsFloating" class="vertical-message">${verticalText}</div>`;
        return;
    }

    lyricTimes.forEach((time, index) => {
        const line = document.createElement('div');
        line.className = 'lyric-line';
        line.dataset.time = time;
        const endTime = index < lyricTimes.length - 1 ? lyricTimes[index + 1] : time + 3; 
        const elements = createCharSpans(lyrics[time], time, endTime);
        elements.forEach(element => line.appendChild(element));
        line.addEventListener('click', function() {
            const lineTime = parseFloat(this.dataset.time);
            const speedOffset = parseFloat(localStorage.getItem('lyricsSpeedOffset')) || 0;
            const adjustedTime = lineTime - speedOffset;
            
            audioPlayer.currentTime = Math.max(0, adjustedTime);
            
            if (audioPlayer.paused) {
                audioPlayer.play();
                isPlaying = true;
                updatePlayButton();
            }
        });
        
        if (lyricsContainer) lyricsContainer.appendChild(line);
    });

    audioPlayer.addEventListener('timeupdate', syncLyrics);
}

function syncLyrics() {
    const currentTime = audioPlayer.currentTime;
    const speedOffset = parseFloat(localStorage.getItem('lyricsSpeedOffset')) || 0;
    const adjustedTime = currentTime + speedOffset;
    
    const lyricsContainer = document.getElementById('lyricsContainer');
    if (!lyricsContainer) return;
    const lines = lyricsContainer.querySelectorAll('.lyric-line');
    let currentLine = null;
    let hasActiveLine = false;

    lines.forEach(line => {
        line.classList.remove('highlight', 'played');
        line.style.color = 'white'; 
        
        const chars = line.querySelectorAll('.char');
        chars.forEach(char => {
            char.classList.remove('active', 'played');
            char.style.transform = '';
            char.style.opacity = '';
            char.style.color = '';
            char.style.background = '';
            char.style.textShadow = '';
            char.style.animation = '';
        });
    });

    for (let i = lines.length - 1; i >= 0; i--) {
        const line = lines[i];
        const lineTime = parseFloat(line.dataset.time);
        if (adjustedTime >= lineTime) {
            line.classList.add('highlight');
            currentLine = line;
            hasActiveLine = true;
            break;
        }
    }

    if (currentLine) {
        const chars = currentLine.querySelectorAll('.char');
        
        chars.forEach(char => {
            char.classList.remove('active');
        });
        
        chars.forEach(char => {
            const start = parseFloat(char.dataset.start);
            const end = parseFloat(char.dataset.end);
            if (adjustedTime >= start && adjustedTime <= end) {
                char.classList.add('active');
            } else if (adjustedTime > end) {
                char.classList.add('played');
                if (!char.classList.contains('played')) {
                    spawnHeartAbove(char); 
                }
            } else {
                char.classList.remove('active', 'played');
                char.style.transform = '';
                char.style.opacity = '';
                char.style.color = '';
                char.style.background = '';
                char.style.textShadow = '';
                char.style.animation = '';
            }
        });

        const floatingContainer = document.getElementById('floatingLyrics');
        if (floatingContainer) {
            const floatingLyrics = floatingContainer.querySelector('.vertical-lyrics');
            if (!floatingLyrics.innerHTML || currentLine.dataset.time !== floatingLyrics.dataset.time) {
                floatingLyrics.innerHTML = currentLine.innerHTML;
                floatingLyrics.dataset.time = currentLine.dataset.time;
                floatingLyrics.classList.add('enter-active');
                setTimeout(() => floatingLyrics.classList.remove('enter-active'), 500);
            }

            const floatingChars = floatingLyrics.querySelectorAll('.char');
            floatingChars.forEach(floatingChar => {
                floatingChar.classList.remove('active');
                floatingChar.style.transform = '';
            });
            
            chars.forEach((char, index) => {
                const floatingChar = floatingChars[index];
                if (!floatingChar) return;
                const start = parseFloat(char.dataset.start);
                const end = parseFloat(char.dataset.end);
                if (adjustedTime >= start && adjustedTime <= end) {
                    floatingChar.classList.add('active');
                    const progress = (adjustedTime - start) / (end - start);
                    floatingChar.style.transform = `scale(${1 + progress * 0.2})`;
                } else {
                    floatingChar.classList.remove('active');
                    floatingChar.style.transform = '';
                }
            });
        }

        if (!isSmallScreen && !isHovering && !isManualScroll) {
            const lineRect = currentLine.getBoundingClientRect();
            const containerRect = lyricsContainer.getBoundingClientRect();
            const targetPosition = lineRect.top - containerRect.top + lyricsContainer.scrollTop - (lyricsContainer.clientHeight / 2) + (lineRect.height / 2);
            const buffer = 50;
            if (lineRect.top < containerRect.top + buffer || 
                lineRect.bottom > containerRect.bottom - buffer) {
                lyricsContainer.scrollTo({ top: targetPosition, behavior: 'smooth' });
            }
        }

        if (!hasActiveLine && lyricsContainer.scrollTop !== 0) {
            lyricsContainer.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
}

function spawnHeartAbove(char) {
    const heart = document.createElement('span');
    heart.className = 'heart';
    heart.textContent = '💖';
    const rect = char.getBoundingClientRect();
    const offsetTop = rect.top + window.scrollY;
    const offsetLeft = rect.left + window.scrollX;
    heart.style.left = `${offsetLeft + char.offsetWidth / 2}px`;
    heart.style.top = `${offsetTop - 30}px`; 
    document.body.appendChild(heart);
    requestAnimationFrame(() => {
        heart.classList.add('pop');
    });
    setTimeout(() => {
        heart.remove(); 
    }, 1000);
}

function loadTrack(url) {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];  
    window.lyrics = {};
    window.lyricTimes = [];
    removeLyricsBackground(); 
    audioPlayer.removeEventListener('timeupdate', syncLyrics);
    audioPlayer.removeEventListener('timeupdate', syncPlainLyrics);
    
    const lyricsContainers = [
        document.getElementById('lyricsContainer'),
        document.querySelector('#floatingLyrics .vertical-lyrics')
    ];
    lyricsContainers.forEach(container => {
        if (container) {
            container.innerHTML = `<div class="no-lyrics">${translations['loading_lyrics'] || 'Loading Lyrics...'}</div>`;
        }
    });
    
    audioPlayer.pause();
    
    if (audioVisualizer && audioVisualizer.isEnabled && audioVisualizer.isVisible) {
        audioVisualizer.stop();
    }
    
    audioPlayer.src = url;
    audioPlayer.load();
    
    const canPlayHandler = () => {
        audioPlayer.currentTime = loadCurrentTime(url);
        audioPlayer.play().then(() => {
            isPlaying = true;
            updatePlayButton();
            
            if (audioVisualizer && audioVisualizer.isEnabled && audioVisualizer.isVisible) {
                audioVisualizer.start();
            }
        }).catch((error) => {
            console.log(`${translations['autoplay_blocked'] || 'Autoplay Blocked'}:`, error);
            showLogMessage(translations['click_to_play'] || 'Click play button to start');
            isPlaying = false;
            updatePlayButton();
        });
    };
    
    audioPlayer.addEventListener('canplaythrough', canPlayHandler, { once: true });
    
    updatePlayButton(); 
    updatePlaylistUI();
    loadLyrics(url);
    updateCurrentSong(url);
    updateTimeDisplay();
    savePlayerState();
}

function initializePlayer() {
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];    
    if (songs.length > 0 && songs[currentTrackIndex]) {
        audioPlayer.src = songs[currentTrackIndex];
        audioPlayer.currentTime = loadCurrentTime(songs[currentTrackIndex]);
        updateCurrentSong(songs[currentTrackIndex]);
        updatePlayButton();
        setRepeatButtonState();
        updateTimeDisplay();
        if (isPlaying) {
            audioPlayer.play().then(() => {
                if (audioVisualizer && audioVisualizer.isEnabled && audioVisualizer.isVisible) {
                    setTimeout(() => {
                        audioVisualizer.start();
                    }, 100);
                }
            }).catch((error) => {
                console.log(`${translations['autoplay_blocked'] || 'Autoplay Blocked'}:`, error);
                isPlaying = false;
                savePlayerState();
                updatePlayButton();
            });
        }
    }
}

function updateCurrentSong(url) {
    const songName = decodeURIComponent(url.split('/').pop().replace(/\.\w+$/, ''));
    const currentSongElement = document.getElementById('currentSong');
    if (currentSongElement) currentSongElement.textContent = songName;
    const floatingTitle = document.querySelector('#floatingLyrics #floatingCurrentSong');
    if (floatingTitle) floatingTitle.textContent = songName;
    const modalTitle = document.querySelector('#musicModal #currentSong');
    if (modalTitle) modalTitle.textContent = songName;
    
    const cleanName = songName.replace(/\.(mp3|flac|wav|m4a|aac|webm)$/i, '');
    let artist = '';
    let title = cleanName;
    
    const separators = [' - ', ' – ', ' — ', ' _ ', ' | ', '-', '–', ' '];
    for (const sep of separators) {
        if (cleanName.includes(sep)) {
            const parts = cleanName.split(sep);
            if (parts.length >= 2) {
                artist = parts[0].trim();
                title = parts.slice(1).join(sep).trim();
                artist = artist.replace(/\[.*?\]|\(.*?\)/g, '').trim();
                title = title.replace(/\[.*?\]|\(.*?\)/g, '').trim();
                break;
            }
        }
    }
    
    if (artist) {
        fetchArtistImage(artist, title);
    } else {
        resetArtistImage();
        removeLyricsBackground();
    }
}

function updateTimeDisplay() {
    const currentTimeElement = document.getElementById('currentTime');
    const durationElement = document.getElementById('duration');
    const progressBar = document.getElementById('progressBar');

    if (currentTimeElement && durationElement && progressBar) {
        audioPlayer.addEventListener('timeupdate', () => {
            const currentTime = audioPlayer.currentTime;
            const duration = audioPlayer.duration || 0;
            const progress = (currentTime / duration) * 100 || 0;
            currentTimeElement.textContent = formatTime(currentTime);
            durationElement.textContent = formatTime(duration);
            progressBar.style.width = `${progress}%`;
            progressBar.setAttribute('aria-valuenow', progress);
        });

        progressBar.parentElement.addEventListener('click', (e) => {
            const rect = progressBar.parentElement.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const clickedPercent = (x / rect.width) * 100;
            const newTime = (clickedPercent / 100) * audioPlayer.duration;
            audioPlayer.currentTime = newTime;
        });
    }
}

function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);
    return `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
}

audioPlayer.addEventListener('ended', () => {
    if (repeatMode === 1) {
        audioPlayer.play();
    } else {
        changeTrack(1); 
    }
});

function savePlayerState() {
    const visualizerState = audioVisualizer ? {
        visualizerEnabled: audioVisualizer.isEnabled,
        visualizerVisible: audioVisualizer.isVisible,
        visualizerContainerVisible: document.getElementById('visualizerContainer')?.style.display !== 'none'
    } : {};
    
    localStorage.setItem(`${STORAGE_PREFIX}playerState`, JSON.stringify({
        isPlaying: isPlaying,
        repeatMode: repeatMode,
        currentTrackIndex: currentTrackIndex,
        currentTrack: songs[currentTrackIndex] || '',
        ...visualizerState
    }));
}

function loadPlayerState() {
    const savedState = localStorage.getItem(`${STORAGE_PREFIX}playerState`);
    if (savedState) {
        const state = JSON.parse(savedState);
        isPlaying = state.isPlaying || false;
        repeatMode = state.repeatMode || 0;
        currentTrackIndex = state.currentTrackIndex || 0;
        
        if (state.visualizerEnabled && state.visualizerVisible) {
            setTimeout(() => {
                if (!audioVisualizer) {
                    initVisualizer();
                }
                if (audioVisualizer) {
                    const container = document.getElementById('visualizerContainer');
                    const button = document.querySelector('[data-tooltip-title="toggle_visualizer"]');
                    if (container && button) {
                        container.style.display = 'block';
                        audioVisualizer.enable();
                        button.innerHTML = '<i class="bi bi-bar-chart-fill"></i>';
                        button.classList.add('visualizer-active');
                        
                        if (isPlaying) {
                            setTimeout(() => {
                                audioVisualizer.start();
                            }, 500);
                        }
                    }
                }
            }, 500);
        }
        
        if (songs.length > 0 && currentTrackIndex >= songs.length) {
            currentTrackIndex = 0;
        }
        setRepeatButtonState();
    }
}

function setRepeatButtonState() {
    const mainBtn = document.getElementById('repeatBtn');
    const floatingBtn = document.getElementById('floatingRepeatBtn');
    const translations = languageTranslations[currentLang] || languageTranslations['zh'];    

    const tooltipKeys = ['order_play', 'single_loop', 'shuffle_play'];
    const iconClasses = ['bi bi-arrow-repeat', 'bi bi-repeat-1', 'bi bi-shuffle'];

    [mainBtn, floatingBtn].forEach(btn => {
        if (!btn) return;

        btn.classList.remove('btn-success', 'btn-warning');

        btn.setAttribute('data-tooltip-title', tooltipKeys[repeatMode]);

        let icon = btn.querySelector('i');
        if (!icon) {
            icon = document.createElement('i');
            btn.prepend(icon);
        }
        icon.className = iconClasses[repeatMode];

        if (repeatMode === 1) btn.classList.add('btn-success');
        if (repeatMode === 2) btn.classList.add('btn-warning');
    });
}

function loadDefaultPlaylist() {
    fetch(currentPlaylistUrl)
        .then(response => response.text())
        .then(data => {
            const newSongs = data.split('\n').filter(url => url.trim());
            if (JSON.stringify(songs) !== JSON.stringify(newSongs)) {
                songs = [...new Set([...newSongs])];
                localStorage.setItem(`${STORAGE_PREFIX}cachedPlaylist`, JSON.stringify(songs));
            }
            if (currentTrackIndex >= songs.length) {
                currentTrackIndex = 0;
                savePlayerState();
            }
            updatePlaylistUI();
            initializePlayer();
            if (songs[currentTrackIndex]) {
                loadLyrics(songs[currentTrackIndex]);
                updateCurrentSong(songs[currentTrackIndex]);
            }
        })
        .catch(error => console.error('Playlist loading failed:', error));
}

window.addEventListener('resize', () => {
    isSmallScreen = window.innerWidth < 768;
});

document.addEventListener('DOMContentLoaded', () => {
    const lyricsContainer = document.getElementById('lyricsContainer');
    if (lyricsContainer) {
        lyricsContainer.addEventListener('mouseenter', () => {
            isHovering = true;
        });
        lyricsContainer.addEventListener('mouseleave', () => {
            isHovering = false;
            isManualScroll = false;
        });
        lyricsContainer.addEventListener('scroll', () => {
            if (isHovering) {
                isManualScroll = true;
                setTimeout(() => {
                    isManualScroll = false;
                }, 3000); 
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const volumeToggle = document.getElementById('volumeToggle');
    const volumePanel = document.getElementById('volumePanel');
    const volumeSlider = document.getElementById('volumeSlider');
    const volumePercentage = document.getElementById('volumePercentage');
    const volumeIconEl = volumeToggle.querySelector('i');

    let lastVolume = 1;
    let hideVolumePanelTimeout = null;

    const savedVolume = localStorage.getItem('audioVolume');
    const savedMuted = localStorage.getItem('audioMuted');
    
    if (savedVolume !== null) {
        lastVolume = parseFloat(savedVolume);
    }
    
    if (typeof audioPlayer !== 'undefined') {
        audioPlayer.volume = lastVolume;
        volumeSlider.value = lastVolume;
        
        updateVolumePercentage(lastVolume);
        
        if (savedMuted !== null) {
            audioPlayer.muted = (savedMuted === 'true');
        }
        
        updateVolumeIcon();
    }

    function updateVolumeIcon() {
        if (!volumeIconEl || typeof audioPlayer === 'undefined') return;
        
        let cls;
        if (audioPlayer.muted || audioPlayer.volume === 0) {
            cls = 'bi bi-volume-mute-fill';
        } else if (audioPlayer.volume < 0.5) {
            cls = 'bi bi-volume-down-fill';
        } else {
            cls = 'bi bi-volume-up-fill';
        }
        volumeIconEl.className = cls;

        if (!audioPlayer.muted) {
            lastVolume = audioPlayer.volume;
            localStorage.setItem('audioVolume', lastVolume);
        }
    }

    function updateVolumePercentage(volume) {
        if (volumePercentage) {
            const percentage = Math.round(volume * 100);
            volumePercentage.textContent = `${percentage}%`;
        }
    }

    function toggleMute() {
        if (typeof audioPlayer === 'undefined') return;
        
        const translations = languageTranslations[currentLang] || languageTranslations['zh'];    
        audioPlayer.muted = !audioPlayer.muted;
        
        if (!audioPlayer.muted && audioPlayer.volume === 0) {
            audioPlayer.volume = lastVolume;
            volumeSlider.value = lastVolume;
            updateVolumePercentage(lastVolume);
        }
        
        localStorage.setItem('audioMuted', audioPlayer.muted);
        updateVolumeIcon();

        const muteMessage = audioPlayer.muted
            ? (translations['mute_on'] || 'Audio muted')
            : (translations['mute_off'] || 'Audio unmuted');
        
        if (typeof showLogMessage !== 'undefined') {
            showLogMessage(muteMessage);
        }
        if (typeof speakMessage !== 'undefined') {
            speakMessage(muteMessage);
        }
    }

    function showVolumePanel() {
        clearTimeout(hideVolumePanelTimeout);
        volumePanel.style.display = 'flex';
    }

    function hideVolumePanel() {
        hideVolumePanelTimeout = setTimeout(() => {
            volumePanel.style.display = 'none';
        }, 300);
    }

    volumeToggle.addEventListener('mouseenter', showVolumePanel);
    volumeToggle.addEventListener('mouseleave', hideVolumePanel);
    
    volumePanel.addEventListener('mouseenter', () => {
        clearTimeout(hideVolumePanelTimeout);
    });
    
    volumePanel.addEventListener('mouseleave', hideVolumePanel);

    volumeToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleMute();
    });

    document.addEventListener('click', function() {
        if (volumePanel.style.display === 'flex') {
            volumePanel.style.display = 'none';
        }
    });

    volumePanel.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    volumeSlider.addEventListener('input', function(e) {
        if (typeof audioPlayer === 'undefined') return;
        
        const translations = languageTranslations[currentLang] || languageTranslations['zh'];    
        const vol = Math.round(parseFloat(e.target.value) * 100);

        audioPlayer.volume = e.target.value;
        updateVolumePercentage(e.target.value);
        
        if (audioPlayer.muted) {
            audioPlayer.muted = false;
            localStorage.setItem('audioMuted', 'false');
        }
        
        updateVolumeIcon();
        
        const volumeMessage = translations['volume_change']
            ? translations['volume_change'].replace('{vol}', vol)
            : `Volume adjusted to ${vol}%`;
            
        if (typeof showLogMessage !== 'undefined') {
            showLogMessage(volumeMessage);
        }
        if (typeof speakMessage !== 'undefined') {
            speakMessage(volumeMessage);
        }
    });

    const speedToggle = document.getElementById('speedToggle');
    const speedLabel = document.getElementById('speedLabel');
    const speeds = [0.75, 1, 1.25, 1.5, 1.75, 2];
    let speedIndex = 1;

    const savedSpeed = localStorage.getItem('audioSpeed');
    if (savedSpeed !== null) {
        const idx = speeds.indexOf(parseFloat(savedSpeed));
        if (idx !== -1) {
            speedIndex = idx;
        }
    }

    if (typeof audioPlayer !== 'undefined') {
        audioPlayer.playbackRate = speeds[speedIndex];
    }
    speedLabel.textContent = speeds[speedIndex] + '×';

    function toggleSpeed() {
        if (typeof audioPlayer === 'undefined') return;
        
        const translations = languageTranslations[currentLang] || languageTranslations['zh'];    
        speedIndex = (speedIndex + 1) % speeds.length;

        const rate = speeds[speedIndex];
        audioPlayer.playbackRate = rate;
        speedLabel.textContent = rate + '×';
        localStorage.setItem('audioSpeed', rate);

        const speedMessage = translations['speed_change']
            ? translations['speed_change'].replace('{rate}', rate)
            : `Playback speed changed to ${rate}x`;
            
        if (typeof showLogMessage !== 'undefined') {
            showLogMessage(speedMessage);
        }
        if (typeof speakMessage !== 'undefined') {
            speakMessage(speedMessage);
        }
    }

    if (speedToggle) {
        speedToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSpeed();
        });
    }
});

function initAudioControls() {
    if (typeof audioPlayer !== 'undefined') {
        const savedVolume = localStorage.getItem('audioVolume');
        const savedSpeed = localStorage.getItem('audioSpeed');
        
        if (savedVolume !== null) {
            audioPlayer.volume = parseFloat(savedVolume);
            const volumeSlider = document.getElementById('volumeSlider');
            if (volumeSlider) {
                volumeSlider.value = audioPlayer.volume;
                const volumePercentage = document.getElementById('volumePercentage');
                if (volumePercentage) {
                    const percentage = Math.round(audioPlayer.volume * 100);
                    volumePercentage.textContent = `${percentage}%`;
                }
            }
        }
        
        if (savedSpeed !== null) {
            audioPlayer.playbackRate = parseFloat(savedSpeed);
            const speedLabel = document.getElementById('speedLabel');
            if (speedLabel) speedLabel.textContent = audioPlayer.playbackRate + '×';
        }
        
        const volumeIconEl = document.querySelector('#volumeToggle i');
        if (volumeIconEl) {
            let cls;
            if (audioPlayer.muted || audioPlayer.volume === 0) {
                cls = 'bi bi-volume-mute-fill';
            } else if (audioPlayer.volume < 0.5) {
                cls = 'bi bi-volume-down-fill';
            } else {
                cls = 'bi bi-volume-up-fill';
            }
            volumeIconEl.className = cls;
        }
    }
}

if (typeof audioPlayer !== 'undefined') {
    audioPlayer.addEventListener('loadedmetadata', initAudioControls);
}