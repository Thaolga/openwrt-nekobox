<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            overflow: hidden;
            background-color: #87ceeb; 
            transition: background-color 0.3s ease;
        }
        #container {
            text-align: center;
            margin-top: 50px;
        }
        #player {
            width: 320px;
            height: 320px;
            margin: 50px auto;
            padding: 20px;
            background: url('/nekobox/assets/img/3.svg') no-repeat center center;
            background-size: cover;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            border-radius: 50%;
            transform-style: preserve-3d;
            transition: transform 0.5s;
            position: relative;
            animation: rainbow 5s infinite, rotatePlayer 10s linear infinite;
        }
        #player:hover {
            transform: rotateY(360deg) rotateX(360deg);
        }
        #player h2 {
            margin-top: 0;
        }
        #audio-container {
           position: absolute;
            top: 80%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 1); 
            width: 100%;
            height: 100%;
        }
        #audioPlayer {
            position: absolute;
            top: 50%; 
            left: 50%;
            transform: translate(-50%, -50%);
        }

        #audioPlayer::-webkit-media-controls-panel {
            background-color: black;
        }
        #audioPlayer::-webkit-media-controls-current-time-display,
        #audioPlayer::-webkit-media-controls-time-remaining-display {
            color: #fff;
        }
        #audioPlayer::-webkit-media-controls-play-button,
        #audioPlayer::-webkit-media-controls-volume-slider-container,
        #audioPlayer::-webkit-media-controls-mute-button,
        #audioPlayer::-webkit-media-controls-timeline {
            filter: invert(1);
        }
        #controls {
            position: absolute;
            bottom: 80px; 
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            box-shadow: 0 4px #666;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        button:active {
            transform: translateY(4px);
            box-shadow: 0 2px #444;
        }
        @keyframes rotatePlayer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #hidePlayer, #timeDisplay {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            background: linear-gradient(90deg, #FF0000, #FF7F00, #FFFF00, #00FF00, #0000FF, #4B0082, #9400D3);
            -webkit-background-clip: text;
            color: transparent;
            transition: background 1s ease;
        }
        .rounded-button {
            border-radius: 30px 15px;
        }
        #tooltip {
            position: absolute;
            background-color: green;
            color: #fff;
            padding: 5px;
            border-radius: 5px;
            display: none;
        }
        #mobile-controls {
            margin-top: 20px;
            position: relative;
            top: -35px; 
            transition: opacity 1s ease-in-out;
            opacity: 1;
        }
        #mobile-controls.hidden {
            opacity: 0;
            pointer-events: none;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center; 
        }
        #top-center-container {
            display: flex;
            align-items: center; 
            justify-content: center; 
            position: absolute;
            top: 10px;
            width: 100%; 
        }
        #weather-toggle {
            margin-left: 10px; 
        }
        @media (min-width: 768px) {
            #mobile-controls {
                display: none;
            }
        }
        @media (max-width: 767px) {
            #mobile-controls {
                display: block;
            }
        }
    </style>
</head>
<body>
  </div>

 <div id="player" onclick="toggleAnimation()">
        <p id="hidePlayer">Mihomo</p>
        <p id="timeDisplay">00:00</p>
        <audio id="audioPlayer" controls>
            <source src="" type="audio/mpeg">
        </audio>
        <br>
        <div id="controls">
            <button id="prev" class="rounded-button">⏮️</button>
            <button id="orderLoop" class="rounded-button">🔁</button>
            <button id="play" class="rounded-button">⏸️</button>
            <button id="next" class="rounded-button">⏭️</button>
      </div>
    </div>
    <div id="mobile-controls">
        <button id="togglePlay" class="rounded-button">Play/Pause</button>
        <button id="prevMobile" class="rounded-button">Previous</button>
        <button id="nextMobile" class="rounded-button">Next</button>
        <button id="toggleEnable" class="rounded-button">Enable/Disable</button>
    </div>
    <div id="tooltip"></div>

    <script>
        let colors = ['#FF0000', '#FF7F00', '#FFFF00', '#00FF00', '#0000FF', '#4B0082', '#9400D3'];
        let isPlayingAllowed = JSON.parse(localStorage.getItem('isPlayingAllowed')) || false;
        let isLooping = false;
        let isOrdered = false;
        let currentSongIndex = 0;
        let songs = [];
        const audioPlayer = document.getElementById('audioPlayer');

        function speakMessage(message) {
            const utterance = new SpeechSynthesisUtterance(message);
            utterance.lang = 'en-US'; 
            speechSynthesis.speak(utterance);
        }

        function toggleAnimation() {
            const player = document.getElementById('player');
            if (player.style.animationPlayState === 'paused') {
                player.style.animationPlayState = 'running';
            } else {
                player.style.animationPlayState = 'paused';
            }
        }

        var hidePlayerButton = document.getElementById('hidePlayer');
        hidePlayerButton.addEventListener('click', function() {
            var player = document.getElementById('player');
            if (player.style.display === 'none') {
                localStorage.setItem('playerVisible', 'true');
            } else {
                player.style.display = 'none';
                localStorage.setItem('playerVisible', 'false');
            }
        });

        function applyGradient(text, elementId) {
            const element = document.getElementById(elementId);
            element.innerHTML = '';
            for (let i = 0; i < text.length; i++) {
                const span = document.createElement('span');
                span.textContent = text[i];
                span.style.color = colors[i % colors.length];
                element.appendChild(span);
            }
            const firstColor = colors.shift();
            colors.push(firstColor);
        }

        function updateTime() {
            const now = new Date();
            const hours = now.getHours();
            const timeString = now.toLocaleTimeString('en-US', { hour12: false });
            let ancientTime;

          if (hours >= 23 || hours < 1) {
                ancientTime = '子時';
            } else if (hours >= 1 && hours < 3) {
                ancientTime = '丑時';
            } else if (hours >= 3 && hours < 5) {
                ancientTime = '寅時';
            } else if (hours >= 5 && hours < 7) {
                ancientTime = '卯時';
            } else if (hours >= 7 && hours < 9) {
                ancientTime = '辰時';
            } else if (hours >= 9 && hours < 11) {
                ancientTime = '巳時';
            } else if (hours >= 11 && hours < 13) {
                ancientTime = '午時';
            } else if (hours >= 13 && hours < 15) {
                ancientTime = '未時';
            } else if (hours >= 15 && hours < 17) {
                ancientTime = '申時';
            } else if (hours >= 17 && hours < 19) {
                ancientTime = '酉時';
            } else if (hours >= 19 && hours < 21) {
                ancientTime = '戌時';
            } else {
                ancientTime = '亥時';
            }

            const displayString = `${timeString} (${ancientTime})`;
            applyGradient(displayString, 'timeDisplay');
        }

        applyGradient('Mihomo', 'hidePlayer');
        updateTime();
        setInterval(updateTime, 1000);

        function showTooltip(text) {
            const tooltip = document.getElementById('tooltip');
            tooltip.textContent = text;
            tooltip.style.display = 'block';
            tooltip.style.left = (window.innerWidth - tooltip.offsetWidth - 20) + 'px';
            tooltip.style.top = '10px';
            setTimeout(hideTooltip, 5000);
        }

        function hideTooltip() {
            const tooltip = document.getElementById('tooltip');
            tooltip.style.display = 'none';
        }

        function handlePlayPause() {
            const playButton = document.getElementById('play');
            if (isPlayingAllowed) {
                if (audioPlayer.paused) {
                    showTooltip('Playing');
                    audioPlayer.play();
                    playButton.textContent = 'Pause';
                    speakMessage('Playing');
                } else {
                    showTooltip('Paused');
                    audioPlayer.pause();
                    playButton.textContent = 'Play';
                    speakMessage('Paused');
                }
            } else {
                showTooltip('Playback Disabled');
                audioPlayer.pause();
                playButton.textContent = 'Play';
                speakMessage('Playback Disabled');
            }
        }

        function handleOrderLoop() {
            if (isPlayingAllowed) {
                const orderLoopButton = document.getElementById('orderLoop');
                if (isOrdered) {
                    isOrdered = false;
                    isLooping = !isLooping;
                    orderLoopButton.textContent = isLooping ? 'Loop' : '';
                    showTooltip(isLooping ? 'Looping' : 'Looping Off');
                    speakMessage(isLooping ? 'Looping' : 'Looping Off');
                } else {
                    isOrdered = true;
                    isLooping = false;
                    orderLoopButton.textContent = 'Order';
                    showTooltip('Order Play');
                    speakMessage('Order Play');
                }
            } else {
                speakMessage('Playback Disabled');
            }
        }

        document.addEventListener('keydown', function(event) {
            switch (event.key) {
                case 'ArrowLeft':
                    if (isPlayingAllowed) {
                        document.getElementById('prev').click();
                    } else {
                        showTooltip('Playback Disabled');
                        speakMessage('Playback Disabled');
                    }
                    break;
                case 'ArrowRight':
                    if (isPlayingAllowed) {
                        document.getElementById('next').click();
                    } else {
                        showTooltip('Playback Disabled');
                        speakMessage('Playback Disabled');
                    }
                    break;
                case ' ':
                    handlePlayPause();
                    break;
                case 'ArrowUp':
                    handleOrderLoop();
                    break;
                case 'Escape':
                    isPlayingAllowed = !isPlayingAllowed;
                    localStorage.setItem('isPlayingAllowed', isPlayingAllowed); 
                    if (!isPlayingAllowed) {
                        audioPlayer.pause();
                        audioPlayer.src = '';
                        showTooltip('Playback Disabled');
                        speakMessage('Playback Disabled. Press ESC to re-enable playback.');
                    } else {
                        showTooltip('Playback Enabled');
                        speakMessage('Playback Enabled.');
                        if (songs.length > 0) {
                            loadSong(currentSongIndex);
                        }
                    }
                    break;
            }
        });

        document.getElementById('play').addEventListener('click', handlePlayPause);
        document.getElementById('next').addEventListener('click', function() {
            if (isPlayingAllowed) {
                currentSongIndex = (currentSongIndex + 1) % songs.length;
                loadSong(currentSongIndex);
                showTooltip('Next');
                speakMessage('Next');
            } else {
                showTooltip('Playback Disabled');
                speakMessage('Playback Disabled');
            }
        });
        document.getElementById('prev').addEventListener('click', function() {
            if (isPlayingAllowed) {
                currentSongIndex = (currentSongIndex - 1 + songs.length) % songs.length;
                loadSong(currentSongIndex);
                showTooltip('Previous');
                speakMessage('Previous');
            } else {
                showTooltip('Playback Disabled');
                speakMessage('Playback Disabled');
            }
        });
        document.getElementById('orderLoop').addEventListener('click', handleOrderLoop);

        document.getElementById('togglePlay').addEventListener('click', handlePlayPause);
        document.getElementById('prevMobile').addEventListener('click', function() {
            if (isPlayingAllowed) {
                currentSongIndex = (currentSongIndex - 1 + songs.length) % songs.length;
                loadSong(currentSongIndex);
                showTooltip('Previous');
                speakMessage('Previous');
            } else {
                showTooltip('Playback Disabled');
                speakMessage('Playback Disabled. Press ESC to re-enable playback.');
            }
        });
        document.getElementById('nextMobile').addEventListener('click', function() {
            if (isPlayingAllowed) {
                currentSongIndex = (currentSongIndex + 1) % songs.length;
                loadSong(currentSongIndex);
                showTooltip('Next');
                speakMessage('Next');
            } else {
                showTooltip('Playback Disabled');
                speakMessage('Playback Disabled. Press ESC to re-enable playback.');
            }
        });
        document.getElementById('toggleEnable').addEventListener('click', function() {
            isPlayingAllowed = !isPlayingAllowed;
            localStorage.setItem('isPlayingAllowed', isPlayingAllowed); 
            if (!isPlayingAllowed) {
                audioPlayer.pause();
                audioPlayer.src = '';
                showTooltip('Playback Disabled');
                speakMessage('Playback Disabled. Press ESC to re-enable playback.');
            } else {
                showTooltip('Playback Enabled');
                speakMessage('Playback Enabled.');
                if (songs.length > 0) {
                    loadSong(currentSongIndex);
                }
            }
        });

        function loadSong(index) {
            if (isPlayingAllowed && index >= 0 && index < songs.length) {
                audioPlayer.src = songs[index];
                audioPlayer.play();
            } else {
                audioPlayer.pause();
            }
        }

        audioPlayer.addEventListener('ended', function() {
            if (isPlayingAllowed) {
                if (isLooping) {
                    audioPlayer.currentTime = 0;
                    audioPlayer.play();
                } else {
                    currentSongIndex = (currentSongIndex + 1) % songs.length;
                    loadSong(currentSongIndex);
                }
            }
        });

        function initializePlayer() {
            if (songs.length > 0) {
                loadSong(currentSongIndex);
            }
        }

        function loadDefaultPlaylist() {
            fetch('https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/songs.txt')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Default playlist loading failed, network response not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    songs = data.split('\n').filter(url => url.trim() !== '');
                    if (songs.length === 0) {
                        throw new Error('Default playlist has no valid songs');
                    }
                    initializePlayer();
                    console.log('Default playlist loaded:', songs);
                })
                .catch(error => {
                    console.error('Error loading default playlist:', error.message);
                });
        }

        loadDefaultPlaylist();
        document.addEventListener('dblclick', function() {
            var player = document.getElementById('player');
            if (player.style.display === 'none') {
                player.style.display = 'flex'; 
            } else {
                player.style.display = 'none'; 
            }
        });
    </script>
</body>
</html>
