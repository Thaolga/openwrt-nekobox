<?php

ob_start();
include './cfg.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./assets/img/nekobox.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <script type="text/javascript" src="./assets/js/feather.min.js"></script>
    <script type="text/javascript" src="./assets/js/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="./assets/js/neko.js"></script>
</head>
<body>
    <style>
        .controls {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .controls label {
            margin-right: 10px;
            font-weight: bold;
            color: #FF5733;
        }
        .controls input {
            margin-right: 20px;
        }
    </style>
</head>
<body>

<div class="container my-3 p-3 border border-3 rounded-4" style="background-color: transparent;">
    <div class="controls">
        <label for="main-toggle">System Toggle</label>
        <input type="checkbox" id="main-toggle">
       
        <label for="website-toggle">Website Check</label>
        <input type="checkbox" id="website-toggle">     
            <label for="timezone-select">Timezone</label>
            <select id="timezone-select">
                <option value="Asia/Shanghai">Shanghai (UTC+8)</option>
                <option value="America/New_York">New York (UTC-5)</option>
                <option value="Europe/London">London (UTC+0)</option>
                <option value="Australia/Sydney">Sydney (UTC+10)</option>
                <option value="Europe/Paris">Paris (UTC+1)</option>
                <option value="Asia/Tokyo">Tokyo (UTC+9)</option>
                <option value="America/Los_Angeles">Los Angeles (UTC-8)</option>
                <option value="America/Chicago">Chicago (UTC-6)</option>
                <option value="Africa/Johannesburg">Johannesburg (UTC+2)</option>
                <option value="Asia/Dubai">Dubai (UTC+4)</option>
                <option value="Europe/Moscow">Moscow (UTC+3)</option>
                <option value="America/Chicago">Chicago (UTC-6)</option>
                <option value="America/Denver">Denver (UTC-7)</option>
                <option value="Asia/Kolkata">Kolkata (UTC+5:30)</option>
                <option value="Asia/Singapore">Singapore (UTC+8)</option>
                <option value="America/Argentina/Buenos_Aires">Buenos Aires (UTC-3)</option>
                <option value="Africa/Nairobi">Nairobi (UTC+3)</option>
                <option value="Pacific/Auckland">Auckland (UTC+13)</option>
                <option value="Europe/Oslo">Oslo (UTC+1)</option>
                <option value="Asia/Seoul">Seoul (UTC+9)</option>
                <option value="Europe/Berlin">Berlin (UTC+1)</option>
                <option value="America/Toronto">Toronto (UTC-5)</option>
                <option value="Asia/Manila">Manila (UTC+8)</option>
                <option value="Africa/Lagos">Lagos (UTC+1)</option>
                <option value="Asia/Ho_Chi_Minh">Ho Chi Minh City (UTC+7)</option>
                <option value="Pacific/Honolulu">Honolulu (UTC-10)</option>
                <option value="America/Vancouver">Vancouver (UTC-8)</option>
                <option value="Asia/Bangkok">Bangkok (UTC+7)</option>
                <option value="Europe/Zurich">Zurich (UTC+1)</option>
                <option value="Asia/Kuala_Lumpur">Kuala Lumpur (UTC+8)</option>
                <option value="Africa/Accra">Accra (UTC+0)</option>
                <option value="America/Sao_Paulo">São Paulo (UTC-3)</option>
                <option value="Europe/Brussels">Brussels (UTC+1)</option>
                <option value="America/Lima">Lima (UTC-5)</option>
                <option value="Asia/Chennai">Chennai (UTC+5:30)</option>
                <option value="Europe/Rome">Rome (UTC+1)</option>
                <option value="Asia/Baghdad">Baghdad (UTC+3)</option>
                <option value="Asia/Jakarta">Jakarta (UTC+7)</option>
                <option value="America/Caracas">Caracas (UTC-4)</option>
                <option value="Asia/Dhaka">Dhaka (UTC+6)</option>
                <option value="Europe/Helsinki">Helsinki (UTC+2)</option>
                <option value="Africa/Cairo">Cairo (UTC+2)</option>
            </select>
        </div>

    <div id="player" onclick="toggleAnimation()" class="rounded-circle" style="background-color: transparent;">
        <p id="hidePlayer">NeKoBox</p>
        <p id="timeDisplay">00:00</p>
        <audio id="audioPlayer" controls>
            <source src="" type="audio/mpeg">
        </audio>
        <div id="controls">
            <button id="prev" class="rounded-button">⏮️</button>
            <button id="orderLoop" class="rounded-button">🔁</button>
            <button id="play" class="rounded-button">⏸️</button>
            <button id="next" class="rounded-button">⏭️</button>
        </div>
    </div>

    <div id="mobile-controls" style="display: flex; justify-content: center; gap: 10px;">
            <button id="togglePlay" class="rounded-button">Play/Pause</button>
            <button id="prevMobile" class="rounded-button">Previous</button>
            <button id="nextMobile" class="rounded-button">Next</button>
            <button id="toggleEnable" class="rounded-button">Enable/Disable</button>
    </div>

    <div id="tooltip"></div>
</div>

        <script>
        let systemEnabled = true; 
        let websiteCheckEnabled = true;
        let lastHour = -1;
        let selectedTimezone = 'Asia/Shanghai';

        function speakMessage(message) {
            const utterance = new SpeechSynthesisUtterance(message);
            utterance.lang = 'en-US';
            speechSynthesis.speak(utterance);
        }

        function getGreeting() {
            const hours = new Date().getHours();
            if (hours >= 5 && hours < 12) return 'Good morning!';
            if (hours >= 12 && hours < 18) return 'Good afternoon!';
            if (hours >= 18 && hours < 22) return 'Good evening!';
            return 'It\'s late, take a rest!';
        }

        function speakCurrentTime() {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            const currentTime = `${hours}:${minutes}:${seconds}`;

            const timeOfDay = (hours >= 5 && hours < 8) ? 'early morning'
                              : (hours >= 8 && hours < 11) ? 'morning'
                              : (hours >= 11 && hours < 13) ? 'noon'
                              : (hours >= 13 && hours < 18) ? 'afternoon'
                              : (hours >= 18 && hours < 20) ? 'evening'
                              : (hours >= 20 && hours < 24) ? 'night'
                              : 'midnight';

            speakMessage(`${getGreeting()} The current time in ${selectedTimezone} is: ${timeOfDay} ${currentTime}`);
        }

        function updateHourlyTime() {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes();
            const seconds = now.getSeconds();

            if (minutes === 0 && seconds === 0 && hours !== lastHour) {
                lastHour = hours;
                const timeOfDay = (hours >= 5 && hours < 8) ? 'early morning'
                                  : (hours >= 8 && hours < 11) ? 'morning'
                                  : (hours >= 11 && hours < 13) ? 'noon'
                                  : (hours >= 13 && hours < 18) ? 'afternoon'
                                  : (hours >= 18 && hours < 20) ? 'evening'
                                  : (hours >= 20 && hours < 24) ? 'night'
                                  : 'midnight';
                speakMessage(`It's the top of the hour, now it's ${timeOfDay} ${hours} o'clock.`);
            }
        }

        const websites = [
            'https://www.youtube.com/',
            'https://www.google.com/',
            'https://www.facebook.com/',
            'https://www.twitter.com/',
            'https://www.github.com/'
        ];

        function getWebsiteStatusMessage(url, status) {
            const statusMessages = {
                'https://www.youtube.com/': status ? 'YouTube is accessible.' : 'Unable to access YouTube, please check your network connection.',
                'https://www.google.com/': status ? 'Google is accessible.' : 'Unable to access Google, please check your network connection.',
                'https://www.facebook.com/': status ? 'Facebook is accessible.' : 'Unable to access Facebook, please check your network connection.',
                'https://www.twitter.com/': status ? 'Twitter is accessible.' : 'Unable to access Twitter, please check your network connection.',
                'https://www.github.com/': status ? 'GitHub is accessible.' : 'Unable to access GitHub, please check your network connection.',
            };

            return statusMessages[url] || (status ? `${url} is accessible.` : `Unable to access ${url}, please check your network connection.`);
        }

        function checkWebsiteAccess(urls) {
            const statusMessages = [];
            let requestsCompleted = 0;

            urls.forEach(url => {
                fetch(url, { mode: 'no-cors' })
                    .then(response => {
                        const isAccessible = response.type === 'opaque';
                        statusMessages.push(getWebsiteStatusMessage(url, isAccessible));
                        
                        if (!isAccessible && url === 'https://www.youtube.com/') {
                            speakMessage('Unable to access YouTube, please check your network connection.');
                        }
                    })
                    .catch(() => {
                        statusMessages.push(getWebsiteStatusMessage(url, false));
                        
                        if (url === 'https://www.youtube.com/') {
                            speakMessage('Unable to access YouTube, please check your network connection.');
                        }
                    })
                    .finally(() => {
                        requestsCompleted++;
                        if (requestsCompleted === urls.length) {
                            speakMessage(statusMessages.join(' '));
                        }
                    });
            });
        }

        document.getElementById('main-toggle').addEventListener('change', (event) => {
            systemEnabled = event.target.checked;
            localStorage.setItem('systemEnabled', systemEnabled); 
            if (systemEnabled) {
                speakMessage('System is enabled.');
                speakCurrentTime();
                if (websiteCheckEnabled) checkWebsiteAccess(websites); 
            } else {
                speakMessage('System is disabled.');
            }
        });

        document.getElementById('website-toggle').addEventListener('change', (event) => {
            websiteCheckEnabled = event.target.checked;
            localStorage.setItem('websiteCheckEnabled', websiteCheckEnabled); 
            if (systemEnabled && websiteCheckEnabled) {
                speakMessage('Website check is enabled.');
                checkWebsiteAccess(websites);
            } else {
                speakMessage('Website check is disabled.');
            }
        });

        document.getElementById('timezone-select').addEventListener('change', (event) => {
            selectedTimezone = event.target.value;
            localStorage.setItem('selectedTimezone', selectedTimezone);
            speakMessage(`Timezone is set to ${selectedTimezone}.`);
            speakCurrentTime();
        });

        window.onload = function() {
            const savedSystemState = localStorage.getItem('systemEnabled');
            if (savedSystemState !== null) {
                systemEnabled = JSON.parse(savedSystemState);
                document.getElementById('main-toggle').checked = systemEnabled;
            }

            const savedWebsiteState = localStorage.getItem('websiteCheckEnabled');
            if (savedWebsiteState !== null) {
                websiteCheckEnabled = JSON.parse(savedWebsiteState);
                document.getElementById('website-toggle').checked = websiteCheckEnabled;
            }

            const savedTimezone = localStorage.getItem('selectedTimezone');
            if (savedTimezone !== null) {
                selectedTimezone = savedTimezone;
                document.getElementById('timezone-select').value = selectedTimezone;
            }

            if (systemEnabled) {
                speakMessage('System is enabled.');
                speakCurrentTime();
                if (websiteCheckEnabled) checkWebsiteAccess(websites);
            }
        };
        </script>
    </div>
</body>
</html>

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

        applyGradient('NekoBox', 'hidePlayer');
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
                speakMessage('Playback Disabled. ');
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
                speakMessage('Playback Disabled. ');
            }
        }

        document.addEventListener('keydown', function(event) {
            switch (event.key) {
                case 'ArrowLeft':
                    if (isPlayingAllowed) {
                        document.getElementById('prev').click();
                    } else {
                        showTooltip('Playback Disabled');
                        speakMessage('Playback Disabled. ');
                    }
                    break;
                case 'ArrowRight':
                    if (isPlayingAllowed) {
                        document.getElementById('next').click();
                    } else {
                        showTooltip('Playback Disabled');
                        speakMessage('Playback Disabled. ');
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
                        speakMessage('Playback Disabled. ');
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
                speakMessage('Playback Disabled. ');
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
                speakMessage('Playback Disabled. ');
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
                speakMessage('Playback Disabled. ');
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
                speakMessage('Playback Disabled. ');
            }
        });
        document.getElementById('toggleEnable').addEventListener('click', function() {
            isPlayingAllowed = !isPlayingAllowed;
            localStorage.setItem('isPlayingAllowed', isPlayingAllowed); 
            if (!isPlayingAllowed) {
                audioPlayer.pause();
                audioPlayer.src = '';
                showTooltip('Playback Disabled');
                speakMessage('Playback Disabled. ');
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
