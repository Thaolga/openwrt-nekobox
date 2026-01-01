let currentPlayingCard = null;
let currentAudio = null;
let currentYouTubePlayer = null; 
let isManualStopping = false;
let isAudioPlaylistMode = false;
let searchResults = [];
let currentPage = 0;
const itemsPerPage = 50;

let currentPlaylist = [];
let currentPlaylistIndex = -1;
let isPlaylistMode = false;

document.addEventListener('DOMContentLoaded', function() {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    const playingCoverIcon = document.getElementById('playingCoverIcon');
    const playingCoverImage = document.getElementById('playingCoverImage');
    if (playingCoverIcon && playingCoverImage) {
        playingCoverIcon.style.display = 'flex';
        playingCoverImage.style.display = 'none';
    }

    document.getElementById('searchButton').addEventListener('click', function() {
        performSearch(false);
    });
    
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch(false);
        }
    });

    document.getElementById('downloadSearchBtn').addEventListener('click', function() {
        if (currentPlayingCard && currentPlayingCard.dataset.previewUrl) {
            downloadTrack(currentPlayingCard);
        }
    });
    
    document.getElementById('loadMoreButton').addEventListener('click', loadMoreResults);
    
    document.getElementById('playSearchBtn').addEventListener('click', function() {
        if (currentAudio) {
            if (currentAudio.paused) {
                currentAudio.play();
                this.innerHTML = '<i class="bi bi-pause-fill"></i>';
            } else {
                currentAudio.pause();
                this.innerHTML = '<i class="bi bi-play-fill"></i>';
            }
        } else if (window.hiddenYTPlayer) {
            const state = window.hiddenYTPlayer.getPlayerState();
            if (state === YT.PlayerState.PLAYING) {
                window.hiddenYTPlayer.pauseVideo();
                this.innerHTML = '<i class="bi bi-play-fill"></i>';
            } else {
                window.hiddenYTPlayer.playVideo();
                this.innerHTML = '<i class="bi bi-pause-fill"></i>';
            }
        } else if (currentPlayingCard && currentPlayingCard.dataset.source === 'youtube') {
            const translations = languageTranslations[currentLang] || languageTranslations['en'];
            const message = translations['youtube_video_instruction'] || 'YouTube videos are played in the video player';
            showLogMessage(message);
        }
    });

    setTimeout(() => {
        loadLatestCache().then(cacheData => {
            if (cacheData && cacheData.results && cacheData.results.length > 0) {
                document.getElementById('searchInput').value = cacheData.query || '';
                document.getElementById('searchType').value = cacheData.type || 'song';
                document.getElementById('searchSource').value = cacheData.source || 'itunes';
                
                searchResults = cacheData.results;
                setTimeout(() => {
                    displayResults(cacheData.results, cacheData.source, false, cacheData.type, true);
                }, 50);
            }
        });
    }, 100);

    setTimeout(() => {
        const translations = languageTranslations[currentLang] || languageTranslations['en'];
        
        const savedPlaylistState = loadPlaylistFromLocalStorage();
        
        if (savedPlaylistState && savedPlaylistState.items_count > 0) {
            loadPlaylistFromSearchCache(
                savedPlaylistState.query,
                savedPlaylistState.type,
                savedPlaylistState.source
            ).then(playlistItems => {
                if (playlistItems.length > 0) {
                    isPlaylistMode = true;
                    currentPlaylist = playlistItems;
                    
                    currentPlaylistIndex = Math.min(
                        savedPlaylistState.current_index || 0,
                        playlistItems.length - 1
                    );
                    
                    if (savedPlaylistState.current_index >= 0 && 
                        savedPlaylistState.current_index < playlistItems.length) {
                        
                        const playAllBtn = document.getElementById('playAllBtn');
                        if (playAllBtn) {
                            const stopAllText = translations['stop_all'] || 'Stop All';
                            playAllBtn.className = 'btn cbi-button cbi-button-remove';
                            playAllBtn.innerHTML = `<i class="bi bi-stop-circle"></i> ${stopAllText}`;
                            playAllBtn.classList.add('playing');
                            playAllBtn.onclick = stopPlaylistMode;
                        }
                        
                        showPlaylistUI(playlistItems);
                        updatePlaylistProgress();
                        
                        playFromPlaylist(currentPlaylistIndex);
                    }
                }
            }).catch(error => {
            });
        }
    }, 1000);
    
    document.getElementById('stopSearchBtn').addEventListener('click', function() {
        stopPlayback();
    });
    
    document.getElementById('prevBtn').addEventListener('click', playPrevSong);
    document.getElementById('nextBtn').addEventListener('click', playNextSong);
    
    setTimeout(() => {
        const seekBar = document.getElementById('playingTimeSeek');
        if (seekBar) {
            seekBar.addEventListener('input', handleTimeSeek);
            seekBar.addEventListener('change', handleTimeSeek);
        }
    }, 50);
    setTimeout(setupYouTubeHoverPreview, 1000);
    document.getElementById('playAllBtn').addEventListener('click', playAllSongs);
    
    if (typeof updateUIText === 'function') {
        updateUIText();
    }
});

const CACHE_API = '/spectra/search_cache.php';

function saveToPHPCache(query, type, source, results) {
    const formData = new FormData();
    formData.append('query', query);
    formData.append('type', type);
    formData.append('source', source);
    formData.append('results', JSON.stringify(results));
    
    fetch(CACHE_API, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
        }
    })
    .catch(error => {
    });
}

function loadFromPHPCache(query, type, source) {
    return fetch(`${CACHE_API}?query=${encodeURIComponent(query)}&type=${type}&source=${source}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data.data;
            }
            return null;
        })
        .catch(error => {
            return null;
        });
}

function loadLatestCache() {
    return fetch(`${CACHE_API}?get_latest=1`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data.data;
            }
            return null;
        })
        .catch(error => {
            return null;
        });
}

function playPrevSong() {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    if (isPlaylistMode && currentPlaylist.length > 0) {
        const prevIndex = currentPlaylistIndex - 1;
        if (prevIndex >= 0) {
            playFromPlaylist(prevIndex);
        } else {
            playFromPlaylist(currentPlaylist.length - 1);
        }
    } else {
        if (currentPlayingCard) {
            const allCards = Array.from(document.querySelectorAll('.music-card'));
            const playableCards = allCards.filter(card => {
                return card.dataset.isArtist !== 'true' && card.dataset.isAlbum !== 'true';
            });
            
            if (playableCards.length > 1) {
                const currentIndex = playableCards.findIndex(card => card === currentPlayingCard);
                if (currentIndex > 0) {
                    playMusic(playableCards[currentIndex - 1]);
                } else if (currentIndex === 0) {
                    playMusic(playableCards[playableCards.length - 1]);
                }
            } else {
                const message = translations['no_previous_song'] || 'No previous song available';
                showLogMessage(message);
                speakMessage(message);
            }
        }
    }
}

function playNextSong() {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    if (isPlaylistMode && currentPlaylist.length > 0) {
        const nextIndex = currentPlaylistIndex + 1;
        if (nextIndex < currentPlaylist.length) {
            playFromPlaylist(nextIndex);
        } else {
            playFromPlaylist(0);
        }
    } else {
        if (currentPlayingCard) {
            const allCards = Array.from(document.querySelectorAll('.music-card'));
            const playableCards = allCards.filter(card => {
                return card.dataset.isArtist !== 'true' && card.dataset.isAlbum !== 'true';
            });
            
            if (playableCards.length > 1) {
                const currentIndex = playableCards.findIndex(card => card === currentPlayingCard);
                if (currentIndex < playableCards.length - 1) {
                    playMusic(playableCards[currentIndex + 1]);
                } else if (currentIndex === playableCards.length - 1) {
                    playMusic(playableCards[0]);
                }
            } else {
                const message = translations['no_next_song'] || 'No next song available';
                showLogMessage(message);
                speakMessage(message);
            }
        }
    }
}

function stopPlayback() {
    if (window.timeUpdateLoopId) {
        cancelAnimationFrame(window.timeUpdateLoopId);
        window.timeUpdateLoopId = null;
    }
    
    if (currentAudio) {
        try {
            currentAudio.pause();
            currentAudio.playingCurrentTime = 0;
            currentAudio.removeEventListener('timeupdate', updateTimeProgress);
        } catch (e) {}
        currentAudio = null;
    }

    if (window.hiddenYTPlayer) {
        try {
            window.hiddenYTPlayer.stopVideo();
            window.hiddenYTPlayer.destroy();
        } catch (e) {}
        window.hiddenYTPlayer = null;
    }
    
    const audioPlayer = document.getElementById('youtube-audio-player');
    if (audioPlayer) {
        audioPlayer.remove();
    }
    
    if (window.currentYouTubeModal) {
        window.currentYouTubeModal.hide();
        window.currentYouTubeModal = null;
    }
    
    isAudioPlaylistMode = false;
    
    const currentTimeElement = document.getElementById('playingCurrentTime');
    const totalTimeElement = document.getElementById('playingTotalTime');
    const progressElement = document.getElementById('playingTimeProgress');
    const thumbElement = document.getElementById('playingTimeThumb');
    const seekBar = document.getElementById('playingTimeSeek');
    
    if (currentTimeElement) currentTimeElement.textContent = '0:00';
    if (totalTimeElement) totalTimeElement.textContent = '0:30';
    if (progressElement) progressElement.style.width = '0%';
    if (thumbElement) thumbElement.style.left = '0%';
    if (seekBar) seekBar.value = 0;
    
    const playingTitle = document.getElementById('playingTitle');
    const playingArtist = document.getElementById('playingArtist');
    
    if (playingTitle) {
        playingTitle.textContent = 'Select a song to play';
        playingTitle.setAttribute('data-translate', 'select_song');
    }
    
    if (playingArtist) {
        playingArtist.textContent = 'Artist';
        playingArtist.setAttribute('data-translate', 'artist');
    }
    
    const playingCoverIcon = document.getElementById('playingCoverIcon');
    const playingCoverImage = document.getElementById('playingCoverImage');
    
    if (playingCoverIcon) playingCoverIcon.style.display = 'flex';
    if (playingCoverImage) {
        playingCoverImage.style.display = 'none';
        playingCoverImage.src = '';
    }
    
    document.querySelectorAll('.music-card.active').forEach(card => {
        card.classList.remove('active');
    });
    currentPlayingCard = null;
    
    if (typeof updateUIText === 'function') {
        updateUIText();
    }
}

function handleTimeSeek(e) {
    if (!currentAudio && !window.hiddenYTPlayer) return;
    
    const value = parseInt(e.target.value);
    
    if (currentAudio && currentAudio.duration) {
        const seekTime = (currentAudio.duration * value) / 100;
        currentAudio.playingCurrentTime = seekTime;
    } else if (window.hiddenYTPlayer) {
        try {
            const duration = window.hiddenYTPlayer.getDuration();
            if (duration) {
                const seekTime = (duration * value) / 100;
                window.hiddenYTPlayer.seekTo(seekTime, true);
            }
        } catch (e) {
        }
    }
    
    updateTimeProgress();
}

function updateTimeProgress() {
    let hasValidAudio = false;
    let playingCurrentTime = 0;
    let totalDuration = 0;
    let isPlaying = false;
    
    if (currentAudio) {
        if (currentAudio.duration && !isNaN(currentAudio.duration)) {
            hasValidAudio = true;
            playingCurrentTime = currentAudio.currentTime || 0;
            totalDuration = currentAudio.duration || 0;
            isPlaying = !currentAudio.paused;
        }
    } 
    else if (window.hiddenYTPlayer) {
        try {
            const playerState = window.hiddenYTPlayer.getPlayerState();
            
            if (playerState === YT.PlayerState.PLAYING || playerState === YT.PlayerState.PAUSED) {
                hasValidAudio = true;
                playingCurrentTime = window.hiddenYTPlayer.getCurrentTime() || 0;
                isPlaying = (playerState === YT.PlayerState.PLAYING);
                
                try {
                    totalDuration = window.hiddenYTPlayer.getDuration() || 0;
                } catch (e) {
                    const context = window.currentYouTubeAudioContext;
                    if (context && context.playlistItem && context.playlistItem.duration) {
                        totalDuration = parseDurationString(context.playlistItem.duration);
                    } else {
                        totalDuration = 30;
                    }
                }
            }
        } catch (e) {
        }
    }
    
    if (!hasValidAudio) {
        return;
    }
    
    const currentTimeElement = document.getElementById('playingCurrentTime');
    const totalTimeElement = document.getElementById('playingTotalTime');
    const progressElement = document.getElementById('playingTimeProgress');
    const thumbElement = document.getElementById('playingTimeThumb');
    const seekBar = document.getElementById('playingTimeSeek');
    
    if (!currentTimeElement || !totalTimeElement || !progressElement || !thumbElement || !seekBar) {
        return;
    }
    
    const percentage = totalDuration > 0 ? (playingCurrentTime / totalDuration) * 100 : 0;
    const clampedPercentage = Math.max(0, Math.min(100, percentage));
    
    currentTimeElement.textContent = formatTime(playingCurrentTime);
    
    if (totalDuration > 0 && !isNaN(totalDuration)) {
        totalTimeElement.textContent = formatTime(totalDuration);
    }
    
    progressElement.style.width = `${clampedPercentage}%`;
    thumbElement.style.left = `${clampedPercentage}%`;
    seekBar.value = clampedPercentage;
    
    if (isPlaying) {
        requestAnimationFrame(updateTimeProgress);
    }
}

function startTimeUpdateLoop() {
    if (window.timeUpdateLoopId) {
        cancelAnimationFrame(window.timeUpdateLoopId);
        window.timeUpdateLoopId = null;
    }
    
    function updateLoop() {
        updateTimeProgress();
        
        const isPlaying = (currentAudio && !currentAudio.paused) || 
                         (window.hiddenYTPlayer && 
                          window.hiddenYTPlayer.getPlayerState && 
                          window.hiddenYTPlayer.getPlayerState() === YT.PlayerState.PLAYING);
        
        if (isPlaying) {
            window.timeUpdateLoopId = requestAnimationFrame(updateLoop);
        } else {
            window.timeUpdateLoopId = null;
        }
    }
    
    updateLoop();
}

function parseDurationString(durationStr) {
    if (!durationStr || durationStr === '--:--') {
        return 30;
    }
    
    try {
        const parts = durationStr.split(':');
        
        if (parts.length === 3) {
            const hours = parseInt(parts[0]) || 0;
            const minutes = parseInt(parts[1]) || 0;
            const seconds = parseInt(parts[2]) || 0;
            return hours * 3600 + minutes * 60 + seconds;
        } else if (parts.length === 2) {
            const minutes = parseInt(parts[0]) || 0;
            const seconds = parseInt(parts[1]) || 0;
            return minutes * 60 + seconds;
        } else if (parts.length === 1) {
            return parseInt(parts[0]) || 30;
        }
    } catch (e) {
    }
    
    return 30;
}

function formatTime(seconds) {
    if (!seconds || isNaN(seconds)) {
        return '0:00';
    }
    
    seconds = Number(seconds);
    if (isNaN(seconds) || !isFinite(seconds)) {
        return '0:00';
    }
    
    if (seconds < 0) seconds = 0;
    
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

function getYouTubeVideoId(url) {
    if (!url) return null;
    
    try {
        const urlObj = new URL(url);
        const videoId = urlObj.searchParams.get('v');
        return videoId || null;
    } catch (e) {
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s?]+)/,
            /v=([^&\s?]+)/,
            /^([A-Za-z0-9_-]{11})$/
        ];
        
        for (const pattern of patterns) {
            const match = url.match(pattern);
            if (match && match[1]) {
                return match[1];
            }
        }
        return null;
    }
}

async function performSearch(isLoadMore = false) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    const query = document.getElementById('searchInput').value.trim();
    const type = document.getElementById('searchType').value;
    const source = document.getElementById('searchSource').value;
    
    if (!query) {
        const message = translations['enter_search_keywords'] || 'Please enter search keywords';
        showLogMessage(message);
        speakMessage(message);
        return;
    }

    if (!isLoadMore) {
        currentPage = 0;
        searchResults = [];
        if (isPlaylistMode) {
            stopPlaylistMode();
        }

        const cacheData = await loadFromPHPCache(query, type, source);
        if (cacheData && cacheData.results && cacheData.results.length > 0) {
            searchResults = cacheData.results;
            displayResults(cacheData.results, source, false, type, true);
            
            executeSearch(query, type, source, false, true);
            return;
        }
    }
    
    const searchTypeMap = {
        'song': translations['song_type'] || 'Songs',
        'artist': translations['artist_type'] || 'Artists',
        'album': translations['album_type'] || 'Albums',
        'playlist': translations['playlist_type'] || 'Playlists'
    };
    
    const searchTypeText = searchTypeMap[type] || type;
    //showLogMessage(`${searchTypeText}: ${query}`);
    const resultsContainer = document.getElementById('resultsContainer');
    if (!isLoadMore) {
        resultsContainer.innerHTML = `
            <div class="loading">
                <div class="loading-spinner"></div>
            </div>
        `;
    }
    
    if (!isLoadMore) {
        document.getElementById('loadMoreContainer').style.display = 'none';
        document.getElementById('playAllBtn').style.display = 'none';
    }
    
    executeSearch(query, type, source, isLoadMore);
}

function executeSearch(query, type, source, isLoadMore = false) {
    switch(source) {
        case 'itunes':
            searchITunes(query, type, isLoadMore);
            break;
        case 'spotify':
            searchSpotify(query, type, isLoadMore);
            break;
        case 'youtube':
            searchYouTube(query, type, isLoadMore);
            break;
        case 'soundcloud':
            searchSoundCloud(query, type, isLoadMore);
            break;
        default:
            searchITunes(query, type, isLoadMore);
    }
}

function searchITunes(query, type, isLoadMore = false) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    const offset = currentPage * itemsPerPage;
    
    fetch('/spectra/search_proxy.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            source: 'itunes',
            query: query,
            type: type,
            limit: itemsPerPage,
            offset: offset
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.results && data.results.length > 0) {
            if (!isLoadMore) {
                searchResults = data.results;
                
                saveToPHPCache(query, type, 'itunes', data.results);
            } else {
                searchResults = searchResults.concat(data.results);
            }
            
            displayResults(data.results, 'itunes', isLoadMore, type, false);
            
        } else {
            if (isLoadMore) {
                showLogMessage(translations['no_more_results'] || 'No more results');
            } else {
                showNoResults();
            }
        }
    })
    .catch(error => {
        showLogMessage(translations['load_failed'] || 'Failed to load more results');
    });
}

function searchSpotify(query, type, isLoadMore = false) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    const offset = currentPage * itemsPerPage;
    
    fetch('/spectra/search_proxy.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            source: 'spotify',
            query: query,
            type: type,
            limit: itemsPerPage,
            offset: offset
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.results && data.results.length > 0) {
            if (!isLoadMore) {
                searchResults = data.results;
                saveToPHPCache(query, type, 'spotify', data.results);
            } else {
                searchResults = searchResults.concat(data.results);
            }
            
            displayResults(data.results, 'spotify', isLoadMore, type, false);
            
        } else {
            if (isLoadMore) {
                showLogMessage(translations['no_more_results'] || 'No more results');
            } else {
                showNoResults();
            }
        }
    })
    .catch(error => {
        if (isLoadMore) {
            showLogMessage(translations['load_failed'] || 'Failed to load more results');
        } else {
            showNoResults();
        }
    });
}

let youtubeNextPageToken = null;
let youtubePrevPageToken = null;

function searchYouTube(query, type, isLoadMore = false) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    if (!isLoadMore) {
        youtubeNextPageToken = null;
        youtubePrevPageToken = null;
    }

    fetch('/spectra/search_proxy.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            source: 'youtube',
            query: query,
            type: type,
            limit: itemsPerPage,
            pageToken: isLoadMore ? youtubeNextPageToken : null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.results && data.results.length > 0) {
            youtubeNextPageToken = data.nextPageToken || null;
            youtubePrevPageToken = data.prevPageToken || null;

            if (!isLoadMore) {
                searchResults = data.results;
                saveToPHPCache(query, type, 'youtube', data.results);
            } else {
                searchResults = searchResults.concat(data.results);
            }
            
            displayResults(data.results, 'youtube', isLoadMore, type, false);
            
        } else {
            if (isLoadMore) {
                showLogMessage(translations['no_more_results'] || 'No more results');
            } else {
                showNoResults();
            }
        }
    })
    .catch(error => {
        if (isLoadMore) {
            showLogMessage(translations['load_failed'] || 'Failed to load more results');
        } else {
            showNoResults();
        }
    });
}

function searchSoundCloud(query, type, isLoadMore = false) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    fetch('/spectra/search_proxy.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            source: 'soundcloud',
            query: query,
            type: type,
            limit: itemsPerPage
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.results && data.results.length > 0) {
            if (!isLoadMore) {
                searchResults = data.results;
                saveToPHPCache(query, type, 'soundcloud', data.results);
            } else {
                searchResults = searchResults.concat(data.results);
            }
            
            displayResults(data.results, 'soundcloud', isLoadMore, type, false);
            
        } else {
            if (isLoadMore) {
                const message = translations['no_results_found'] || `No results found`;
                showLogMessage(message);
            } else {
                showNoResults();
            }
        }
    })
    .catch(error => {
        if (isLoadMore) {
            const message = translations['load_failed'] || `Failed to load more results`;
            showLogMessage(message);
        } else {
            showNoResults();
        }
    });
}

function displayResults(results, source, isLoadMore = false, searchType, fromCache = false) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    const container = document.getElementById('resultsContainer');

    if (source === 'youtube') {
        setTimeout(() => {
            setupYouTubeHoverPreview();
        }, 100);
    }

    if (!isLoadMore && results.length === 0) {
        showNoResults();
        return;
    }
    
    if (!isLoadMore) {
        container.innerHTML = '';
    }
    
    const resultsCountElement = document.getElementById('resultsCount');
    if (!isLoadMore) {
        const totalResults = results.length;
        const countMessageTemplate = translations['results_count'] || '{count} results';
        resultsCountElement.textContent = countMessageTemplate.replace('{count}', totalResults);
    } else {
        const currentCount = document.querySelectorAll('.music-card').length + results.length;
        const countMessageTemplate = translations['results_count'] || '{count} results';
        resultsCountElement.textContent = countMessageTemplate.replace('{count}', currentCount);
    }

    if (!fromCache && !isLoadMore) {
        const countMessageTemplate = translations['search_results_count'] || 'Found {count} results';
        const countMessage = countMessageTemplate.replace('{count}', results.length);
        showLogMessage(countMessage);
        speakMessage(countMessage);
    }
    
    results.forEach((item, index) => {
        const cardIndex = isLoadMore ? searchResults.length + index : index;
        const card = createMusicCard(item, source, cardIndex, searchType);
        if (card) {
            container.appendChild(card);
        }
    });
    
    if (!isLoadMore) {
        searchResults = results;
    } else {
        searchResults = searchResults.concat(results);
    }
    
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    if (results.length >= itemsPerPage) {
        loadMoreContainer.style.display = 'block';
    } else {
        loadMoreContainer.style.display = 'none';
    }
    
    updatePlayAllButton();
}

function updatePlayAllButton() {
    const playAllBtn = document.getElementById('playAllBtn');
    const allCards = document.querySelectorAll('.music-card');
    
    const playableCards = Array.from(allCards).filter(card => {
        return card.dataset.isArtist !== 'true' && card.dataset.isAlbum !== 'true';
    });
    
    if (playableCards.length > 0) {
        playAllBtn.style.display = 'flex';
        playAllBtn.disabled = false;
    } else {
        playAllBtn.style.display = 'none';
        playAllBtn.disabled = true;
    }
}

function playAllSongs() {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    const currentQuery = document.getElementById('searchInput').value;
    const currentType = document.getElementById('searchType').value;
    const currentSource = document.getElementById('searchSource').value;
    
    const playlistItems = convertSearchResultsToPlaylist(searchResults, currentSource);
    
    const validPlaylistItems = playlistItems.filter(item => {
        return item.previewUrl && item.previewUrl.trim() !== '';
    });
    
    if (validPlaylistItems.length === 0) {
        const message = translations['no_playable_songs'] || 'No playable songs found';
        showLogMessage(message);
        speakMessage(message);
        return;
    }
    
    stopPlayback();
    
    isAudioPlaylistMode = true;
    
    savePlaylistToLocalStorage(validPlaylistItems, 0);
    
    isPlaylistMode = true;
    currentPlaylist = validPlaylistItems;
    currentPlaylistIndex = 0;
    
    const playAllBtn = document.getElementById('playAllBtn');
    if (playAllBtn) {
        const stopAllText = translations['stop_all'] || 'Stop All';
        playAllBtn.className = 'btn cbi-button cbi-button-remove';
        playAllBtn.innerHTML = `<i class="bi bi-stop-circle"></i> ${stopAllText}`;
        playAllBtn.classList.add('playing');
        playAllBtn.onclick = stopPlaylistMode;
    }
    
    showPlaylistUI(validPlaylistItems);
    
    if (validPlaylistItems.length > 0) {
        playFromPlaylist(0);
    }
}

function savePlaylistToLocalStorage(items, startIndex = 0) {
    try {
        const currentQuery = document.getElementById('searchInput').value;
        const currentType = document.getElementById('searchType').value;
        const currentSource = document.getElementById('searchSource').value;
        
        const playlistState = {
            query: currentQuery,
            type: currentType,
            source: currentSource,
            current_index: startIndex,
            items_count: items.length,
            timestamp: Date.now()
        };
        
        localStorage.setItem('spectra_playlist_state', JSON.stringify(playlistState));
        
        saveSearchCacheAsPlaylist(currentQuery, currentType, currentSource, items);
    } catch (error) {
    }
}

function loadPlaylistFromLocalStorage() {
    try {
        const savedState = localStorage.getItem('spectra_playlist_state');
        if (savedState) {
            const state = JSON.parse(savedState);
            return {
                query: state.query || '',
                type: state.type || 'song',
                source: state.source || 'itunes',
                current_index: state.current_index || 0,
                items_count: state.items_count || 0,
                timestamp: state.timestamp || 0
            };
        }
    } catch (error) {
    }
    return null;
}

function updateCurrentIndexToLocalStorage(index) {
    try {
        const savedState = localStorage.getItem('spectra_playlist_state');
        if (savedState) {
            const state = JSON.parse(savedState);
            state.current_index = index;
            state.timestamp = Date.now();
            localStorage.setItem('spectra_playlist_state', JSON.stringify(state));
        }
    } catch (error) {
    }
}

async function loadPlaylistFromSearchCache(query, type, source) {
    try {
        const response = await fetch(`${CACHE_API}?get_playlist=1&query=${encodeURIComponent(query)}&type=${type}&source=${source}`);
        const data = await response.json();
        
        if (data.success && data.data && data.data.results) {
            return convertSearchResultsToPlaylist(data.data.results, source);
        }
        return [];
    } catch (error) {
        return [];
    }
}

function convertSearchResultsToPlaylist(results, source) {
    return results
        .filter(item => {
            if (source === 'itunes') {
                return item.trackName && item.previewUrl;
            } else if (source === 'spotify') {
                return item.name && item.preview_url;
            } else if (source === 'youtube') {
                return item.title && item.id;
            } else if (source === 'soundcloud') {
                return item.title && (item.stream_url || item.permalink_url);
            }
            return false;
        })
        .map((item, index) => {
            let playlistItem = {
                title: '',
                artist: '',
                cover: '',
                previewUrl: '',
                duration: '',
                source: source,
                originalIndex: index
            };
            
            switch(source) {
                case 'itunes':
                    playlistItem.title = item.trackName || item.collectionName || 'Unknown Track';
                    playlistItem.artist = item.artistName || 'Unknown Artist';
                    playlistItem.cover = item.artworkUrl100?.replace('100x100bb', '300x300bb') || '/luci-static/resources/icons/cover.svg';
                    playlistItem.previewUrl = item.previewUrl || '';
                    playlistItem.duration = item.trackTimeMillis ? formatDuration(item.trackTimeMillis) : '--:--';
                    break;
                    
                case 'spotify':
                    playlistItem.title = item.name || 'Unknown Track';
                    playlistItem.artist = item.artists?.map(a => a.name).join(', ') || 'Unknown Artist';
                    playlistItem.cover = item.album?.images?.[0]?.url || item.images?.[0]?.url || '/luci-static/resources/icons/cover.svg';
                    playlistItem.previewUrl = item.preview_url || '';
                    playlistItem.duration = item.duration_ms ? formatDuration(item.duration_ms) : '--:--';
                    break;
                    
                case 'youtube':
                    playlistItem.title = item.title || 'Unknown Video';
                    playlistItem.artist = item.channelTitle || 'YouTube';
                    playlistItem.cover = item.thumbnails?.medium?.url || item.thumbnails?.default?.url || '/luci-static/resources/icons/cover.svg';
                    playlistItem.previewUrl = item.id ? `https://www.youtube.com/watch?v=${item.id}` : '';
                    playlistItem.duration = item.duration ? formatYouTubeDuration(item.duration) : '--:--';
                    playlistItem.isYouTube = true;
                    playlistItem.videoId = item.id || '';
                    break;
                    
                case 'soundcloud':
                    playlistItem.title = item.title || 'Unknown Track';
                    playlistItem.artist = item.user?.username || 'SoundCloud';
                    playlistItem.cover = item.artwork_url || '/luci-static/resources/icons/cover.svg';
                    playlistItem.previewUrl = item.stream_url || item.permalink_url || '';
                    playlistItem.duration = item.duration ? formatDuration(item.duration) : '--:--';
                    break;
            }
            
            return playlistItem;
        });
}

function saveSearchCacheAsPlaylist(query, type, source, playlistItems) {
    try {
        const currentQuery = document.getElementById('searchInput').value;
        const currentType = document.getElementById('searchType').value;
        const currentSource = document.getElementById('searchSource').value;
        
        saveToPHPCache(currentQuery, currentType, currentSource, searchResults);
        
        const playlistState = {
            query: currentQuery,
            type: currentType,
            source: currentSource,
            current_index: 0,
            items_count: playlistItems.length,
            timestamp: Date.now()
        };
        
        localStorage.setItem('spectra_playlist_state', JSON.stringify(playlistState));
        
    } catch (error) {
    }
}

function parseDurationToMillis(durationStr) {
    if (!durationStr || durationStr === '--:--') return null;
    
    const parts = durationStr.split(':');
    if (parts.length === 2) {
        const minutes = parseInt(parts[0]) || 0;
        const seconds = parseInt(parts[1]) || 0;
        return (minutes * 60 + seconds) * 1000;
    } else if (parts.length === 3) {
        const hours = parseInt(parts[0]) || 0;
        const minutes = parseInt(parts[1]) || 0;
        const seconds = parseInt(parts[2]) || 0;
        return (hours * 3600 + minutes * 60 + seconds) * 1000;
    }
    
    return null;
}

function removeCurrentFromPlaylist() {
    if (currentPlaylistIndex >= 0 && currentPlaylistIndex < currentPlaylist.length) {
        currentPlaylist.splice(currentPlaylistIndex, 1);
        
        if (currentPlaylistIndex >= currentPlaylist.length && currentPlaylist.length > 0) {
            currentPlaylistIndex = 0;
        } else if (currentPlaylist.length === 0) {
            currentPlaylistIndex = -1;
        }
        
        savePlaylistToLocalStorage(currentPlaylist, currentPlaylistIndex);
        
        showPlaylistUI(currentPlaylist);
        
        if (currentPlaylistIndex >= 0 && currentPlaylistIndex < currentPlaylist.length) {
            playFromPlaylist(currentPlaylistIndex);
        } else if (currentPlaylist.length > 0) {
            playFromPlaylist(0);
        } else {
            const translations = languageTranslations[currentLang] || languageTranslations['en'];
            const message = translations['playlist_completed'] || 'Playlist completed';
            showLogMessage(message);
            speakMessage(message);
            stopPlaylistMode();
        }
    }
}

function findNextValidTrack(currentIndex) {
    if (!currentPlaylist || currentPlaylist.length === 0) {
        return -1;
    }
    
    for (let i = currentIndex + 1; i < currentPlaylist.length; i++) {
        const track = currentPlaylist[i];
        if (track.previewUrl && track.previewUrl.trim() !== '') {
            return i;
        }
    }
    
    for (let i = 0; i < currentIndex; i++) {
        const track = currentPlaylist[i];
        if (track.previewUrl && track.previewUrl.trim() !== '') {
            return i;
        }
    }
    
    return -1;
}

function playFromPlaylist(index, retryCount = 0) {
    if (!currentPlaylist || index < 0 || index >= currentPlaylist.length) {
        const translations = languageTranslations[currentLang] || languageTranslations['en'];
        const message = translations['playlist_completed'] || 'Playlist completed';
        showLogMessage(message);
        speakMessage(message);
        stopPlaylistMode();
        return;
    }
    
    const item = currentPlaylist[index];
    currentPlaylistIndex = index;
    
    updateCurrentIndexToLocalStorage(index);
    updateCardActiveState(item);
    
    if (item.source === 'youtube') {
        playYouTubeAudioFromPlaylist(item, index);
    } else {
        playMusicFromPlaylist(item, index, retryCount);
    }
    
    updatePlaylistHighlight(index);
    updatePlaylistProgress();
}

function playYouTubeAudio(card) {
    const videoId = getYouTubeVideoId(card.dataset.previewUrl);
    
    if (!videoId) {
        return;
    }
    
    const playlistItem = {
        title: card.dataset.title,
        artist: card.dataset.artist,
        cover: card.dataset.cover,
        previewUrl: card.dataset.previewUrl,
        duration: card.dataset.duration || '--:--',
        source: 'youtube',
        cardIndex: card.dataset.index,
        isYouTube: true,
        videoId: videoId,
        audioMode: true
    };
    
    updatePlayingUIFromCard(card);
    
    createYouTubeAudioPlayer(videoId, card, playlistItem);
}

function playYouTubeAudioFromPlaylist(item, index) {
    const videoId = getYouTubeVideoId(item.previewUrl);
    
    if (!videoId) return;
    
    updatePlayingUIFromItem(item);
    
    const playingTitle = document.getElementById('playingTitle');
    const playingArtist = document.getElementById('playingArtist');
    
    if (playingTitle) {
        playingTitle.textContent = item.title;
        playingTitle.removeAttribute('data-translate');
    }
    
    if (playingArtist) {
        playingArtist.textContent = item.artist;
        playingArtist.removeAttribute('data-translate');
    }
    
    if (item.duration && item.duration !== '--:--') {
        const totalTimeElement = document.getElementById('playingTotalTime');
        if (totalTimeElement) {
            totalTimeElement.textContent = item.duration;
        }
    }
     
    createYouTubeAudioPlayer(videoId, null, item);
    
    currentPlaylistIndex = index;
    updatePlaylistHighlight(index);
    
    const playBtn = document.getElementById('playSearchBtn');
    if (playBtn) {
        playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
    }
}

function updateCardActiveState(item) {
    document.querySelectorAll('.music-card.active').forEach(card => {
        card.classList.remove('active');
    });
    
    const allCards = Array.from(document.querySelectorAll('.music-card'));
    let foundCard = null;
    
    if (item.cardIndex !== undefined) {
        foundCard = allCards.find(card => 
            parseInt(card.dataset.index) === parseInt(item.cardIndex)
        );
    }
    
    if (!foundCard && item.title && item.artist) {
        foundCard = allCards.find(card => 
            card.dataset.title === item.title && 
            card.dataset.artist === item.artist
        );
    }
    
    if (!foundCard && item.source === 'youtube') {
        const videoId = getYouTubeVideoId(item.previewUrl);
        if (videoId) {
            foundCard = allCards.find(card => {
                const cardVideoId = getYouTubeVideoId(card.dataset.previewUrl);
                return cardVideoId === videoId;
            });
        }
    }
    
    if (foundCard) {
        foundCard.classList.add('active');
        currentPlayingCard = foundCard;
    } else {
        currentPlayingCard = null;
    }
}

function createYouTubeAudioPlayer(videoId, card, playlistItem = null) {
    const existingPlayer = document.getElementById('youtube-audio-player');
    if (existingPlayer) {
        existingPlayer.remove();
    }
    
    if (window.hiddenYTPlayer) {
        try {
            window.hiddenYTPlayer.destroy();
        } catch (e) {}
        window.hiddenYTPlayer = null;
    }
    
    const playerDiv = document.createElement('div');
    playerDiv.id = 'youtube-audio-player';
    playerDiv.style.cssText = `
        position: fixed;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
        z-index: -9999;
        left: -9999px;
        top: -9999px;
    `;
    
    const iframe = document.createElement('iframe');
    iframe.id = 'youtube-audio-iframe';
    iframe.width = '1';
    iframe.height = '1';
    iframe.src = getYouTubeAudioEmbedUrl(videoId);
    
    iframe.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
    iframe.allowFullscreen = false;
    
    playerDiv.appendChild(iframe);
    document.body.appendChild(playerDiv);
    
    window.currentYouTubeAudioContext = {
        card: card,
        playlistItem: playlistItem,
        videoId: videoId,
        duration: playlistItem ? playlistItem.duration : null
    };
    
    setTimeout(() => {
        initYouTubeAudioPlayer(videoId, card, playlistItem);
    }, 100);
}

function getYouTubeAudioEmbedUrl(videoId) {
    const params = new URLSearchParams({
        'autoplay': '1',
        'controls': '0',
        'showinfo': '0',
        'rel': '0',
        'modestbranding': '1',
        'fs': '0',
        'iv_load_policy': '3',
        'disablekb': '1',
        'playsinline': '1',
        'enablejsapi': '1',
        'origin': window.location.origin,
        'widget_referrer': window.location.href,
        'autohide': '1',
        'color': 'white',
        'theme': 'dark',
        'hl': currentLang || 'en',
        'loop': '0',
        'playlist': videoId,
        'mute': '0'
    });
    
    return `https://www.youtube.com/embed/${videoId}?${params.toString()}`;
}

function initYouTubeAudioPlayer(videoId, card, playlistItem) {
    if (!window.YT) {
        const tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        
        let checkCount = 0;
        const checkYT = setInterval(() => {
            checkCount++;
            if (window.YT) {
                clearInterval(checkYT);
                createYTPlayer();
            } else if (checkCount > 50) {
                clearInterval(checkYT);
            }
        }, 200);
        
        function createYTPlayer() {
            window.hiddenYTPlayer = new YT.Player('youtube-audio-iframe', {
                events: {
                    'onReady': onYouTubeAudioReady,
                    'onStateChange': onYouTubeAudioStateChange,
                    'onError': onYouTubeAudioError
                },
                playerVars: {
                    autoplay: 1,
                    controls: 0,
                    disablekb: 1,
                    fs: 0,
                    iv_load_policy: 3,
                    modestbranding: 1,
                    playsinline: 1,
                    rel: 0,
                    showinfo: 0,
                    enablejsapi: 1,
                    origin: window.location.origin
                }
            });
        }
    } else {
        window.hiddenYTPlayer = new YT.Player('youtube-audio-iframe', {
            events: {
                'onReady': onYouTubeAudioReady,
                'onStateChange': onYouTubeAudioStateChange,
                'onError': onYouTubeAudioError
            },
            playerVars: {
                autoplay: 1,
                controls: 0,
                disablekb: 1,
                fs: 0,
                iv_load_policy: 3,
                modestbranding: 1,
                playsinline: 1,
                rel: 0,
                showinfo: 0,
                enablejsapi: 1,
                origin: window.location.origin
            }
        });
    }
    
    window.currentYouTubeAudioContext = {
        card: card,
        playlistItem: playlistItem,
        videoId: videoId,
        startTime: Date.now()
    };
}

function onYouTubeAudioReady(event) {
    const playBtn = document.getElementById('playSearchBtn');
    
    try {
        event.target.playVideo();
        
        if (playBtn) {
            playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
            playBtn.onclick = function() {
                if (window.hiddenYTPlayer) {
                    const state = window.hiddenYTPlayer.getPlayerState();
                    if (state === YT.PlayerState.PLAYING) {
                        window.hiddenYTPlayer.pauseVideo();
                        this.innerHTML = '<i class="bi bi-play-fill"></i>';
                    } else {
                        window.hiddenYTPlayer.playVideo();
                        this.innerHTML = '<i class="bi bi-pause-fill"></i>';
                    }
                }
            };
        }
        
        const context = window.currentYouTubeAudioContext;
        if (context && context.playlistItem && context.playlistItem.duration) {
            const totalTimeElement = document.getElementById('playingTotalTime');
            const translations = languageTranslations[currentLang] || languageTranslations['en'];
            const playingText = translations['playing'] || 'Playing';
            const playMessage = `${playingText}: ${context.playlistItem.title} - ${context.playlistItem.artist}`;
            showLogMessage(playMessage);
            if (totalTimeElement) {
                totalTimeElement.textContent = context.playlistItem.duration;
            }
        }
        
        setTimeout(() => {
            updateTimeProgress();
        }, 50);
             startTimeUpdateLoop();   
    } catch (e) {
    }
}

function onYouTubeAudioStateChange(event) {
    const playBtn = document.getElementById('playSearchBtn');
    
    switch(event.data) {
        case YT.PlayerState.PLAYING:
            if (playBtn) playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
            updateTimeProgress();
            break;
            
        case YT.PlayerState.PAUSED:
            if (playBtn) playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
            break;
            
        case YT.PlayerState.ENDED:
            if (playBtn) playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
            
            if (isPlaylistMode && currentPlaylist.length > 0) {
                if (currentPlaylistIndex < currentPlaylist.length - 1) {
                    setTimeout(() => {
                        playFromPlaylist(currentPlaylistIndex + 1);
                    }, 1000);
                } else {
                    const translations = languageTranslations[currentLang] || languageTranslations['en'];
                    const message = translations['playlist_completed'] || 'Playlist completed';
                    showLogMessage(message);
                    speakMessage(message);
                    stopPlaylistMode();
                }
            }
            break;
    }
}

function onYouTubeAudioError(event) {  
    const context = window.currentYouTubeAudioContext;
    
    if (isPlaylistMode && currentPlaylist.length > 0) {
        const nextIndex = findNextValidTrack(currentPlaylistIndex);
        if (nextIndex !== -1) {
            const translations = languageTranslations[currentLang] || languageTranslations['en'];
            showLogMessage(translations['error_skip_next'] || 'Error playing track, skipping to next');
            setTimeout(() => {
                playFromPlaylist(nextIndex);
            }, 1000);
            return;
        } else {
            stopPlaylistMode();
            return;
        }
    }
    
    if (context && context.card) {
        playYouTubeVideoInModal(
            `https://www.youtube.com/watch?v=${context.videoId}`,
            context.card.dataset.title,
            context.card
        );
    }
}

function playMusicFromPlaylist(item, index, retryCount = 0) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    if (currentAudio) {
        currentAudio.pause();
        currentAudio.removeEventListener('timeupdate', updateTimeProgress);
        currentAudio = null;
    }
    
    updatePlayingUIFromItem(item);
    
    const playBtn = document.getElementById('playSearchBtn');
    const stopBtn = document.getElementById('stopSearchBtn');
    playBtn.disabled = false;
    stopBtn.disabled = false;
    playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
    
    const previewUrl = item.previewUrl;
    
    if (!previewUrl || previewUrl.trim() === '') {
        const message = translations['no_preview_available'] || 'No preview available for this track';
        showLogMessage(message);
        
        setTimeout(() => {
            const nextIndex = findNextValidTrack(index);
            if (nextIndex !== -1) {
                playFromPlaylist(nextIndex);
            } else {
                const endMessage = translations['playlist_completed'] || 'Playlist completed';
                showLogMessage(endMessage);
                stopPlaylistMode();
            }
        }, 1000);
        return;
    }
    
    const timeContainer = document.querySelector('.playing-time-container');
    if (timeContainer) {
        timeContainer.style.display = 'flex';
    }
    
    const currentTimeElement = document.getElementById('playingCurrentTime');
    const totalTimeElement = document.getElementById('playingTotalTime');
    
    if (currentTimeElement) {
        currentTimeElement.textContent = '0:00';
    }
    
    if (totalTimeElement && item.duration) {
        totalTimeElement.textContent = item.duration;
    }
    
    const progressElement = document.getElementById('playingTimeProgress');
    const thumbElement = document.getElementById('playingTimeThumb');
    const seekBar = document.getElementById('playingTimeSeek');
    
    if (progressElement) progressElement.style.width = '0%';
    if (thumbElement) thumbElement.style.left = '0%';
    if (seekBar) seekBar.value = 0;
    
    const playingText = translations['playing'] || 'Playing';
    const playMessage = `${playingText}: ${item.title} - ${item.artist}`;
    showLogMessage(playMessage);
    speakMessage(playMessage);
    
    currentAudio = new Audio(previewUrl);
    
    currentAudio.addEventListener('loadedmetadata', function() {
        const totalTimeElement = document.getElementById('playingTotalTime');
        if (totalTimeElement && currentAudio.duration && !isNaN(currentAudio.duration)) {
            totalTimeElement.textContent = formatTime(currentAudio.duration);
        }
    });
    
    currentAudio.addEventListener('canplaythrough', function() {
        playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
        updateTimeProgress();
        updatePlaylistProgress();
    });
    
    currentAudio.addEventListener('play', function() {
        playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
        startTimeUpdateLoop();
    });
    
    currentAudio.addEventListener('pause', function() {
        playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
        updateTimeProgress();
    });
    
    currentAudio.addEventListener('timeupdate', function() {
        updateTimeProgress();
    });
    
    currentAudio.addEventListener('error', function() {
        if (retryCount < 1) {
            setTimeout(() => {
                playMusicFromPlaylist(item, index, retryCount + 1);
            }, 1000);
            return;
        }
        
        const errorMessage = translations['cannot_play_track'] || 'Cannot play this track';
        showLogMessage(errorMessage);
        
        setTimeout(() => {
            const nextIndex = findNextValidTrack(index);
            if (nextIndex !== -1) {
                playFromPlaylist(nextIndex);
            } else {
                const endMessage = translations['playlist_completed'] || 'Playlist completed';
                showLogMessage(endMessage);
                stopPlaylistMode();
            }
        }, 1000);
    });
    
    currentAudio.addEventListener('ended', function() {
        playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
        
        if (isPlaylistMode && currentPlaylist.length > 0) {
            setTimeout(() => {
                const nextIndex = findNextValidTrack(index);
                if (nextIndex !== -1) {
                    playFromPlaylist(nextIndex);
                } else {
                    const message = translations['playlist_completed'] || 'Playlist completed';
                    showLogMessage(message);
                    stopPlaylistMode();
                }
            }, 1000);
        }
    });
    
    currentAudio.play().catch(error => {
        const errorMessage = translations['cannot_play_track'] || 'Cannot play this track';
        showLogMessage(errorMessage);
        
        setTimeout(() => {
            const nextIndex = findNextValidTrack(index);
            if (nextIndex !== -1) {
                playFromPlaylist(nextIndex);
            } else {
                stopPlaylistMode();
            }
        }, 1000);
    });
}

function startYouTubeTimeUpdate() {
    if (!window.hiddenYTPlayer) return;
    
    const updateYouTubeTime = () => {
        try {
            const playerState = window.hiddenYTPlayer.getPlayerState();
            if (playerState === YT.PlayerState.PLAYING) {
                updateTimeProgress();
                requestAnimationFrame(updateYouTubeTime);
            }
        } catch (e) {
        }
    };
    
    updateYouTubeTime();
}

function saveSearchState() {
    const searchState = {
        query: document.getElementById('searchInput').value,
        type: document.getElementById('searchType').value,
        source: document.getElementById('searchSource').value
    };
    localStorage.setItem('spectra_search_state', JSON.stringify(searchState));
}

function loadSearchState() {
    try {
        const saved = localStorage.getItem('spectra_search_state');
        if (saved) {
            const state = JSON.parse(saved);
            if (state.query) document.getElementById('searchInput').value = state.query;
            if (state.type) document.getElementById('searchType').value = state.type;
            if (state.source) document.getElementById('searchSource').value = state.source;
        }
    } catch (e) {
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadSearchState();
    
    const searchInput = document.getElementById('searchInput');
    const searchType = document.getElementById('searchType');
    const searchSource = document.getElementById('searchSource');
    
    let saveTimeout;
    function debounceSave() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveSearchState, 500);
    }
    
    searchInput.addEventListener('input', debounceSave);
    searchType.addEventListener('change', saveSearchState);
    searchSource.addEventListener('change', saveSearchState);
});

function playYouTubeVideoInModal(videoUrl, title, card) {
    let videoId;
    try {
        const urlObj = new URL(videoUrl);
        videoId = urlObj.searchParams.get('v');
    } catch (e) {
        return;
    }
    if (!videoId) return;

    if (window.currentYouTubeModal) {
        window.currentYouTubeModal.hide();
    }

    window.currentYouTubeCard = card;

    const modalId = 'youtube-modal-' + Date.now();
    const modalHTML = `
<div id="${modalId}" class="uk-modal" uk-modal="bg-close: false">
    <div class="uk-modal-dialog youtube-modal-dialog">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <div class="uk-modal-header youtube-modal-header">
            <h3 class="uk-modal-title" style="color: var(--accent-color);">
                <span class="uk-icon uk-margin-small-right" uk-icon="icon: youtube; ratio: 1.5"></span>
                YouTube
            </h3>
        </div>
        <div class="uk-modal-body youtube-modal-body">
            <div id="player-container-${modalId}" class="youtube-player-container"></div>
        </div>
        <div class="uk-modal-footer youtube-modal-footer">
            <div class="uk-flex uk-flex-between uk-flex-middle">
                <div class="uk-button-group">
                    <button class="uk-button uk-margin-small-left uk-button-success" 
                            data-tooltip-title="previous_track" onclick="playPrevYouTubeVideo()"> 
                        <i class="bi bi-skip-backward-fill"></i>
                    </button>
                    <button class="uk-button uk-margin-small-left uk-button-primary youtube-playpause-btn" 
                            data-tooltip-title="play_pause" onclick="toggleYouTubePlayPause()">
                        <i class="bi bi-pause-fill"></i>
                    </button>
                    <button class="uk-button uk-margin-small-left uk-button-success" 
                            data-tooltip-title="next_track" onclick="playNextYouTubeVideo()">
                        <i class="bi bi-skip-forward-fill"></i>
                    </button>
                </div>
                <div class="uk-button-group">
                    <button class="uk-button uk-button-success" onclick="toggleModalFullscreen('${modalId}')" title="fullscreen">
                        <i class="bi bi-fullscreen"></i>
                    </button>
                    <a href="${videoUrl}" target="_blank" class="uk-button uk-margin-small-left uk-button-primary" data-tooltip-title="open_on_youtube">
                        <i class="bi bi-youtube"></i>
                    </a>
                    <button id="close-btn-${modalId}" class="uk-button uk-button-danger uk-margin-small-left" data-tooltip-title="close">
                        <i class="bi bi-x-circle-fill"></i> 
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>`;

    let modalContainer = document.getElementById('youtube-modals-container');
    if (!modalContainer) {
        modalContainer = document.createElement('div');
        modalContainer.id = 'youtube-modals-container';
        document.body.appendChild(modalContainer);
    }

    modalContainer.innerHTML = '';
    modalContainer.innerHTML = modalHTML;

    const modalElement = document.getElementById(modalId);
    if (!modalElement) return;

    const modal = UIkit.modal(modalElement, {
        bgclose: false,
        keyboard: true,
        stack: true
    });

    const closeButton = document.getElementById(`close-btn-${modalId}`);
    if (closeButton) {
        closeButton.addEventListener('click', () => {
            modal.hide();
        });
    }

    window.currentYouTubeModal = modal;
    window.currentYouTubeModalId = modalId;
    window.currentYouTubeVideoId = videoId;
    updateUIText();
    modal.show();
    initModalDrag(modalId);

    UIkit.util.on(modalElement, 'shown', function() {
        setTimeout(() => {
            createYouTubePlayer(modalId, videoId, title);
            checkAndRestoreFullscreen(modalId);
        }, 100);
    });

    UIkit.util.on(modalElement, 'hidden', function() {
        cleanupYouTubeModal(modalId, modal, card);
    });
}

function initModalDrag(modalId) {
    const modalElement = document.getElementById(modalId);
    if (!modalElement) return;
    
    const dialog = modalElement.querySelector('.uk-modal-dialog');
    const header = modalElement.querySelector('.uk-modal-header');
    
    if (!dialog || !header) return;
    
    let isDragging = false;
    let startX, startY, startLeft, startTop;
    
    header.addEventListener('mousedown', startDrag);
    header.addEventListener('touchstart', startDragTouch);
    
    function startDrag(e) {
        e.preventDefault();
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        
        const rect = dialog.getBoundingClientRect();
        startLeft = rect.left;
        startTop = rect.top;
        
        document.addEventListener('mousemove', onDrag);
        document.addEventListener('mouseup', stopDrag);
    }
    
    function startDragTouch(e) {
        if (e.touches.length !== 1) return;
        e.preventDefault();
        isDragging = true;
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        
        const rect = dialog.getBoundingClientRect();
        startLeft = rect.left;
        startTop = rect.top;
        
        document.addEventListener('touchmove', onDragTouch);
        document.addEventListener('touchend', stopDragTouch);
    }
    
    function onDrag(e) {
        if (!isDragging) return;
        e.preventDefault();
        
        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;
        
        dialog.style.position = 'fixed';
        dialog.style.left = (startLeft + deltaX) + 'px';
        dialog.style.top = (startTop + deltaY) + 'px';
        dialog.style.margin = '0';
        dialog.style.transform = 'none';
    }
    
    function onDragTouch(e) {
        if (!isDragging || e.touches.length !== 1) return;
        e.preventDefault();
        
        const deltaX = e.touches[0].clientX - startX;
        const deltaY = e.touches[0].clientY - startY;
        
        dialog.style.position = 'fixed';
        dialog.style.left = (startLeft + deltaX) + 'px';
        dialog.style.top = (startTop + deltaY) + 'px';
        dialog.style.margin = '0';
        dialog.style.transform = 'none';
    }
    
    function stopDrag() {
        isDragging = false;
        document.removeEventListener('mousemove', onDrag);
        document.removeEventListener('mouseup', stopDrag);
    }
    
    function stopDragTouch() {
        isDragging = false;
        document.removeEventListener('touchmove', onDragTouch);
        document.removeEventListener('touchend', stopDragTouch);
    }
    
    header.addEventListener('dblclick', function() {
        dialog.style.position = '';
        dialog.style.left = '';
        dialog.style.top = '';
        dialog.style.margin = '';
        dialog.style.transform = '';
    });
}

function createYouTubePlayer(modalId, videoId, title) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    const container = document.getElementById(`player-container-${modalId}`);
    if (!container) return;

    container.innerHTML = '';

    const iframe = document.createElement('iframe');
    iframe.id = `youtube-iframe-${modalId}`;

    const params = new URLSearchParams({
        'autoplay': '1',
        'rel': '0',
        'modestbranding': '1',
        'playsinline': '1',
        'enablejsapi': '1',
        'widgetid': '1',
        'fs': '1',
        'origin': window.location.origin,
        'iv_load_policy': '3',
        'disablekb': '0',
        'controls': '1',
        'showinfo': '0',
        'mute': '0',
        'cc_load_policy': '1',
        'cc_lang_pref': 'zh-TW',
        'hl': 'zh-TW',
        'color': 'red',
        'theme': 'dark',
        'autohide': '1',
        'wmode': 'transparent',
        'vq': 'hd1080',
        'playlist': videoId,
    });
    
    iframe.src = `https://www.youtube.com/embed/${videoId}?${params.toString()}`;

    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen';
      
    const playingText = translations['playing'] || 'Playing';
    iframe.title = `${playingText}: ${title}`;
    
    iframe.style.width = '100%';
    iframe.style.height = '100%';
    iframe.style.minHeight = '400px';
    iframe.style.border = 'none';
    iframe.style.background = '#000';
    iframe.style.display = 'block';

    container.appendChild(iframe);

    window.currentYouTubeIframe = iframe;

    let hasStartedPlaying = false;
    let playCheckTimer;

    playCheckTimer = setTimeout(() => {
        if (!hasStartedPlaying) {
            if (window.currentYouTubeModal) {
                window.currentYouTubeModal.hide();
            }
            setTimeout(() => {
                playNextYouTubeVideo();
            }, 300);
        }
    }, 5000);

    iframe.addEventListener('load', function() {
        try {
            const listenMessage = {
                event: 'listening',
                id: 1,
                channel: 'widget'
            };
            iframe.contentWindow.postMessage(JSON.stringify(listenMessage), '*');

            const messageHandler = function(event) {
                if (event.origin !== 'https://www.youtube.com' && 
                    event.origin !== 'https://www.youtube-nocookie.com') return;

                try {
                    let data;
                    if (typeof event.data === 'string') {
                        data = JSON.parse(event.data);
                    } else if (typeof event.data === 'object') {
                        data = event.data;
                    } else {
                        return;
                    }

                    if ((data.event === 'onStateChange' && data.info === 1) || 
                        (data.event === 'infoDelivery' && data.info && data.info.playerState === 1)) {
                        hasStartedPlaying = true;
                        clearTimeout(playCheckTimer);
                    }
                } catch (e) {}
            };

            window.addEventListener('message', messageHandler);
            
            iframe._messageHandler = messageHandler;

            setTimeout(() => {
                const getInfoMessage = {
                    event: 'command',
                    func: 'getPlayerState',
                    args: '',
                    id: 1,
                    channel: 'widget'
                };
                iframe.contentWindow.postMessage(JSON.stringify(getInfoMessage), '*');
            }, 1000);
        } catch (e) {}

        setupYouTubeMessageListener(modalId);
    });

    iframe.addEventListener('unload', function() {
        if (playCheckTimer) {
            clearTimeout(playCheckTimer);
        }
        if (iframe._messageHandler) {
            window.removeEventListener('message', iframe._messageHandler);
        }
    });
}

function toggleModalFullscreen(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    const dialog = modal.querySelector('.uk-modal-dialog');
    const playerContainer = modal.querySelector('.youtube-player-container');
    const iframe = playerContainer.querySelector('iframe');
    const controls = modal.querySelector('.uk-modal-footer');
    
    if (!dialog || !iframe) return;
    
    if (dialog.classList.contains('youtube-fullscreen-mode')) {
        exitFullscreenMode(modalId, dialog, iframe, controls);
    } else {
        enterFullscreenMode(modalId, dialog, iframe, controls);
    }
}

function enterFullscreenMode(modalId, dialog, iframe, controls) {
    const originalStyles = {
        dialog: dialog.getAttribute('style') || '',
        iframe: iframe.getAttribute('style') || '',
        container: iframe.parentElement.getAttribute('style') || ''
    };
    window.youtubeOriginalStyles = originalStyles;

    const closeBtn = dialog.querySelector('.uk-modal-close-default');
    if (closeBtn) {
        closeBtn.style.display = 'none';
    }
    
    dialog.classList.add('youtube-fullscreen-mode');
    dialog.style.cssText = `
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 0 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 9999 !important;
        background: #000 !important;
        overflow: hidden !important;
    `;
    
    const header = dialog.querySelector('.uk-modal-header');
    if (header) header.style.display = 'none';
    
    const playerContainer = iframe.parentElement;
    playerContainer.style.cssText = `
        width: 100vw !important;
        height: 100vh !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 1 !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #000 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    `;
    
    iframe.style.cssText = `
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        z-index: 2 !important;
        border: none !important;
        background: #000 !important;
        margin: 0 !important;
        padding: 0 !important;
        object-fit: contain !important;
    `;
    
    try {
        const fullscreenMessage = {
            event: 'command',
            func: 'setSize',
            args: [window.innerWidth, window.innerHeight],
            id: 1,
            channel: 'widget'
        };
        iframe.contentWindow.postMessage(JSON.stringify(fullscreenMessage), '*');
        
        const setFullscreenMessage = {
            event: 'command',
            func: 'playVideo',
            args: '',
            id: 1,
            channel: 'widget'
        };
        iframe.contentWindow.postMessage(JSON.stringify(setFullscreenMessage), '*');
    } catch (e) {
    }
    
    updateIframeSize();
    
    const safeAreaInsets = window.safeAreaInsets || { bottom: '20px' };
    
    if (controls) {
        controls.classList.add('youtube-fullscreen-controls');
        controls.style.cssText = `
            position: fixed !important;
            bottom: 60px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            z-index: 10000 !important;
            background: rgba(0, 0, 0, 0.6) !important;
            padding: 12px 24px !important;
            border-radius: 50px !important;
            opacity: 1 !important;
            transition: opacity 0.3s ease !important;
            margin: 0 !important;
            border: none !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5) !important;
            min-width: 300px !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            pointer-events: auto !important;
        `;
        
        setTimeout(() => {
            controls.style.opacity = '0';
        }, 10);
        
        const fullscreenBtn = controls.querySelector('[onclick*="toggleModalFullscreen"] i');
        if (fullscreenBtn) {
            fullscreenBtn.className = 'bi bi-fullscreen-exit';
        }
        
        const controlsInner = controls.querySelector('.uk-flex');
        if (controlsInner) {
            controlsInner.style.cssText = `
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                gap: 25px !important;
                margin: 0 !important;
            `;
            
        const buttons = controls.querySelectorAll('.uk-button');
            buttons.forEach(btn => {
                btn.style.cssText = `
                    padding: 10px 20px !important;
                    border-radius: 25px !important;
                    transition: all 0.2s ease !important;
                    margin: 0 !important;
                    min-width: 40px !important;
                    min-height: 40px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    background: rgba(255, 255, 255, 0.1) !important;
                    border: 1px solid rgba(255, 255, 255, 0.2) !important;
                    cursor: pointer !important;
                    backdrop-filter: blur(6px) !important;
                    -webkit-backdrop-filter: blur(6px) !important;
                `;

                const originalBackground = 'rgba(255, 255, 255, 0.1)';
                const hoverBackground = 'rgba(255, 255, 255, 0.2)';
                
                const mouseEnterHandler = function() {
                    this.style.background = hoverBackground;
                    this.style.boxShadow = '0 4px 10px rgba(0, 0, 0, 0.3)';
                };
                
                const mouseLeaveHandler = function() {
                    this.style.background = originalBackground;
                    this.style.boxShadow = '';
                };
                
                btn.addEventListener('mouseenter', mouseEnterHandler);
                btn.addEventListener('mouseleave', mouseLeaveHandler);
                
                btn._mouseenterHandler = mouseEnterHandler;
                btn._mouseleaveHandler = mouseLeaveHandler;
            });
        }
    }
    
    
    setupFullscreenHover(dialog);
    window.addEventListener('resize', handleFullscreenResize);
    window.isYouTubeFullscreen = true;
    window.fullscreenModalId = modalId;
    localStorage.setItem('youtube_fullscreen_state', '1');
    
    document.addEventListener('keydown', handleFullscreenEscape);
}

function handleFullscreenResize() {
    if (!window.isYouTubeFullscreen) return;
    
    const modalId = window.fullscreenModalId;
    if (!modalId) return;
    
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    const iframe = modal.querySelector('iframe');
    if (!iframe) return;
    
    try {
        const resizeMessage = {
            event: 'command',
            func: 'setSize',
            args: [window.innerWidth, window.innerHeight],
            id: 1,
            channel: 'widget'
        };
        iframe.contentWindow.postMessage(JSON.stringify(resizeMessage), '*');
    } catch (e) {
    }
}

function handleFullscreenEscape(e) {
    if (e.key === 'Escape' && window.isYouTubeFullscreen) {
        const modalId = window.fullscreenModalId;
        if (modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                const dialog = modal.querySelector('.uk-modal-dialog');
                const iframe = modal.querySelector('iframe');
                const controls = modal.querySelector('.uk-modal-footer');
                
                if (dialog && iframe && controls) {
                    exitFullscreenMode(modalId, dialog, iframe, controls);
                }
            }
        }
    }
}

function exitFullscreenMode(modalId, dialog, iframe, controls) {
    dialog.classList.remove('youtube-fullscreen-mode');
    
    dialog.style.cssText = window.youtubeOriginalStyles?.dialog || '';
    
    const header = dialog.querySelector('.uk-modal-header');
    if (header) header.style.display = '';
    
    const closeBtn = dialog.querySelector('.uk-modal-close-default');
    if (closeBtn) {
        closeBtn.style.display = '';
    }
    
    const playerContainer = iframe.parentElement;
    if (playerContainer && window.youtubeOriginalStyles?.container) {
        playerContainer.style.cssText = window.youtubeOriginalStyles.container;
    } else if (playerContainer) {
        playerContainer.style.cssText = '';
    }
    
    iframe.style.cssText = window.youtubeOriginalStyles?.iframe || '';
    
    if (controls) {
        controls.classList.remove('youtube-fullscreen-controls');
        controls.style.cssText = '';
        
        const controlsInner = controls.querySelector('.uk-flex');
        if (controlsInner) {
            controlsInner.style.cssText = '';
        }
        
        const buttons = controls.querySelectorAll('.uk-button');
        buttons.forEach(btn => {
            btn.style.cssText = '';
            
            if (btn._mouseenterHandler) {
                btn.removeEventListener('mouseenter', btn._mouseenterHandler);
                delete btn._mouseenterHandler;
            }
            if (btn._mouseleaveHandler) {
                btn.removeEventListener('mouseleave', btn._mouseleaveHandler);
                delete btn._mouseleaveHandler;
            }
            
            btn.style.background = '';
            btn.style.boxShadow = '';
        });
        
        const fullscreenBtn = controls.querySelector('[onclick*="toggleModalFullscreen"] i');
        if (fullscreenBtn) {
            fullscreenBtn.className = 'bi bi-fullscreen';
        }
    }
    
    if (dialog._hoverListeners) {
        dialog.removeEventListener('mousemove', dialog._hoverListeners.mousemove);
        dialog.removeEventListener('mouseleave', dialog._hoverListeners.mouseleave);
        delete dialog._hoverListeners;
    }
    
    window.removeEventListener('resize', handleFullscreenResize);
    
    document.removeEventListener('keydown', handleFullscreenEscape);
    
    window.isYouTubeFullscreen = false;
    window.fullscreenModalId = null;
    localStorage.setItem('youtube_fullscreen_state', '0');
    
    try {
        const resizeMessage = {
            event: 'command',
            func: 'setSize',
            args: [800, 450],
            id: 1,
            channel: 'widget'
        };
        iframe.contentWindow.postMessage(JSON.stringify(resizeMessage), '*');
    } catch (e) {
    }
}

function setupFullscreenHover(dialog) {
    let hideTimeout;
    
    const showControls = () => {
        const controls = dialog.querySelector('.youtube-fullscreen-controls');
        if (controls) {
            controls.style.opacity = '1';
        }
        clearTimeout(hideTimeout);
    };
    
    const hideControls = () => {
        const controls = dialog.querySelector('.youtube-fullscreen-controls');
        if (controls) {
            controls.style.opacity = '0';
        }
    };
    
    const handleMouseMove = (e) => {
        const rect = dialog.getBoundingClientRect();
        if (e.clientX >= rect.left && e.clientX <= rect.right &&
            e.clientY >= rect.top && e.clientY <= rect.bottom) {
            showControls();
            hideTimeout = setTimeout(hideControls, 2000);
        }
    };
    
    const handleMouseLeave = () => {
        hideControls();
        clearTimeout(hideTimeout);
    };
    
    dialog.addEventListener('mousemove', handleMouseMove);
    dialog.addEventListener('mouseleave', handleMouseLeave);
    
    dialog._hoverListeners = {
        mousemove: handleMouseMove,
        mouseleave: handleMouseLeave
    };
    
    setTimeout(() => {
        hideControls();
    }, 1000);
}

function checkAndRestoreFullscreen(modalId) {
    const fullscreenState = localStorage.getItem('youtube_fullscreen_state');
    
    if (fullscreenState === '1') {
        setTimeout(() => {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            
            const dialog = modal.querySelector('.uk-modal-dialog');
            const playerContainer = modal.querySelector('.youtube-player-container');
            const iframe = playerContainer?.querySelector('iframe');
            const controls = modal.querySelector('.uk-modal-footer');
            
            if (dialog && iframe && controls) {
                enterFullscreenMode(modalId, dialog, iframe, controls);
            }
        }, 100);
    }
}

function updateIframeSize() {
    if (!window.isYouTubeFullscreen) return;
    
    const modalId = window.fullscreenModalId;
    if (!modalId) return;
    
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    const iframe = modal.querySelector('iframe');
    if (!iframe) return;
    
    try {
        const resizeMessage = {
            event: 'command',
            func: 'setSize',
            args: [window.innerWidth, window.innerHeight],
            id: 1,
            channel: 'widget'
        };
        iframe.contentWindow.postMessage(JSON.stringify(resizeMessage), '*');
    } catch (e) {
    }
}

function setupYouTubeMessageListener(modalId) {
    if (window.youtubeMessageHandler) {
        window.removeEventListener('message', window.youtubeMessageHandler);
    }

    const messageHandler = function(event) {
        if (event.origin !== 'https://www.youtube.com' && 
            event.origin !== 'https://www.youtube-nocookie.com') return;

        try {
            let data;
            if (typeof event.data === 'string') {
                data = JSON.parse(event.data);
            } else if (typeof event.data === 'object') {
                data = event.data;
            } else {
                return;
            }

            if (data.event === 'infoDelivery' && data.info && data.info.playerState !== undefined) {
                const playerState = data.info.playerState;

                const button = document.querySelector(`#youtube-modal-${window.currentYouTubeModalId} .youtube-playpause-btn i`);
                if (button) {
                    if (playerState === 1) {
                        button.className = 'bi bi-pause-fill';
                    } else if (playerState === 2 || playerState === 0) {
                        button.className = 'bi bi-play-fill';
                    }
                }

                if (playerState === 0) {
                    setTimeout(() => {
                        playNextYouTubeVideo();
                    }, 1000);
                }
            }

            if (data.event === 'onStateChange') {
                const button = document.querySelector(`#youtube-modal-${window.currentYouTubeModalId} .youtube-playpause-btn i`);
                if (button) {
                    if (data.info === 1) {
                        button.className = 'bi bi-pause-fill';
                    } else if (data.info === 2 || data.info === 0) {
                        button.className = 'bi bi-play-fill';
                    }
                }

                if (data.info === 0) {
                    setTimeout(() => {
                        playNextYouTubeVideo();
                    }, 1000);
                }
            }
        } catch (e) {}
    };

    window.addEventListener('message', messageHandler);
    window.youtubeMessageHandler = messageHandler;
}

function toggleYouTubePlayPause() {
    const button = document.querySelector('.youtube-playpause-btn i');
    
    if (!button) return;
    
    const iframes = document.querySelectorAll('iframe[src*="youtube.com/embed"]');
    const iframe = iframes[iframes.length - 1];
    
    if (!iframe) return;
    
    if (button.classList.contains('bi-pause-fill')) {
        try {
            iframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
        } catch (e) {
            iframe.src = iframe.src.replace('autoplay=1', 'autoplay=0');
        }
        button.className = 'bi bi-play-fill';
    } else {
        try {
            iframe.contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
        } catch (e) {
            let src = iframe.src;
            if (!src.includes('autoplay=1')) {
                src = src.includes('?') ? src + '&autoplay=1' : src + '?autoplay=1';
                iframe.src = src;
            }
        }
        button.className = 'bi bi-pause-fill';
    }
}

function playPrevYouTubeVideo() {
    if (window.currentYouTubeCard) {
        const allCards = Array.from(document.querySelectorAll('.music-card[data-source="youtube"]'));
        const currentIndex = allCards.findIndex(card => card === window.currentYouTubeCard);
        
        if (currentIndex > 0) {
            const prevCard = allCards[currentIndex - 1];
            playMusic(prevCard);
        } else if (allCards.length > 0) {
            playMusic(allCards[allCards.length - 1]);
        }
    }
}

function playNextYouTubeVideo() {
    if (window.currentYouTubeCard) {
        const allCards = Array.from(document.querySelectorAll('.music-card[data-source="youtube"]'));
        const currentIndex = allCards.findIndex(card => card === window.currentYouTubeCard);
        
        if (currentIndex < allCards.length - 1) {
            const nextCard = allCards[currentIndex + 1];
            playMusic(nextCard);
        } else if (allCards.length > 0) {
            playMusic(allCards[0]);
        }
    }
}

function cleanupYouTubeModal(modalId, modal, card) {
    const iframe = window.currentYouTubeIframe;
    if (iframe) {
        try {
            iframe.contentWindow.postMessage('{"event":"command","func":"stopVideo","args":""}', '*');
        } catch (e) { }
        iframe.src = '';
        window.currentYouTubeIframe = null;
    }
    
    if (window.youtubeMessageHandler) {
        window.removeEventListener('message', window.youtubeMessageHandler);
        window.youtubeMessageHandler = null;
    }
    
    const modalElement = document.getElementById(modalId);
    if (modalElement && modalElement.parentNode) {
        modalElement.parentNode.removeChild(modalElement);
    }
    
    if (window.currentYouTubeModal === modal) {
        window.currentYouTubeModal = null;
        window.currentYouTubeModalId = null;
        window.currentYouTubeCard = null;
        window.currentYouTubeVideoId = null;
    }
    
    if (currentPlayingCard === card) {
        card.classList.remove('active');
        currentPlayingCard = null;
    }
}

let hoverPreviewTimer = null;
let hoverPreviewModal = null;
let currentHoverCard = null;

function setupYouTubeHoverPreview() {
    document.querySelectorAll('.music-card[data-source="youtube"]').forEach(card => {
        card.addEventListener('mouseenter', () => startHoverPreview(card));
        card.addEventListener('mouseleave', stopHoverPreview);
    });
}

function startHoverPreview(card) {
    if (currentHoverCard === card && hoverPreviewModal) return;
    
    stopHoverPreview();
    
    currentHoverCard = card;
    
    hoverPreviewTimer = setTimeout(() => {
        const videoId = getYouTubeVideoId(card.dataset.previewUrl);
        if (videoId) showYouTubePreview(card, videoId);
    }, 500);
}

function getYouTubeVideoId(url) {
    try {
        return new URL(url).searchParams.get('v');
    } catch {
        return null;
    }
}

function showYouTubePreview(card, videoId) {
    const cardRect = card.getBoundingClientRect();
    
    const previewHTML = `
        <div class="youtube-preview-modal">
            <div class="preview-container">
                <iframe 
                    width="400" 
                    height="225" 
                    src="https://www.youtube.com/embed/${videoId}?autoplay=1&controls=1&modestbranding=1&rel=0&playsinline=1"
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    title="${card.dataset.title}">
                </iframe>
            </div>
        </div>`;
    
    const temp = document.createElement('div');
    temp.innerHTML = previewHTML;
    hoverPreviewModal = temp.firstElementChild;
    
    positionPreview(cardRect);
    
    document.body.appendChild(hoverPreviewModal);
    
    hoverPreviewModal.addEventListener('mouseenter', () => {
        if (hoverPreviewTimer) {
            clearTimeout(hoverPreviewTimer);
            hoverPreviewTimer = null;
        }
    });
    
    hoverPreviewModal.addEventListener('mouseleave', () => {
        stopHoverPreview();
    });
}

function positionPreview(cardRect) {
    const modalWidth = 400;
    const modalHeight = 225;
    const viewport = {
        width: window.innerWidth,
        height: window.innerHeight
    };
    
    let left = cardRect.right + 10;
    let top = cardRect.bottom - modalHeight;
    
    if (left + modalWidth > viewport.width) {
        left = cardRect.left - modalWidth - 10;
    }
    
    if (top + modalHeight > viewport.height) {
        top = viewport.height - modalHeight - 10;
    }
    
    if (top < 10) {
        top = cardRect.bottom + 10;
    }
    
    hoverPreviewModal.style.cssText = `
        position: fixed;
        left: ${Math.max(10, left)}px;
        top: ${Math.max(10, top)}px;
        z-index: 9999;
        pointer-events: auto;
    `;
}

function stopHoverPreview() {
    if (hoverPreviewTimer) {
        clearTimeout(hoverPreviewTimer);
        hoverPreviewTimer = null;
    }
    
    if (hoverPreviewModal) {
        const iframe = hoverPreviewModal.querySelector('iframe');
        if (iframe) {
            try {
                iframe.src = '';
            } catch (e) {}
        }
        
        hoverPreviewModal.remove();
        hoverPreviewModal = null;
    }
    
    currentHoverCard = null;
}

function stopPlaylistMode() {   
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    isAudioPlaylistMode = false;
    isPlaylistMode = false;
    isManualStopping = true;
    
    if (currentAudio) {
        try {
            currentAudio.pause();
            currentAudio.playingCurrentTime = 0;
            currentAudio = null;
        } catch (error) {
        }
    }
    
    if (window.hiddenYTPlayer) {
        try {
            window.hiddenYTPlayer.stopVideo();
            window.hiddenYTPlayer.destroy();
        } catch (e) {}
        window.hiddenYTPlayer = null;
    }
    
    stopPlayback();
    isPlaylistMode = false;
    currentPlaylist = [];
    currentPlaylistIndex = -1;
    
    const playAllBtn = document.getElementById('playAllBtn');
    if (playAllBtn) {
        const playAllText = translations['play_all'] || 'Play All';
        playAllBtn.innerHTML = `<i class="bi bi-play-circle"></i> ${playAllText}`;
        playAllBtn.classList.remove('playing');
        playAllBtn.onclick = playAllSongs;
    }
    
    const playlistContainer = document.querySelector('.spectra-playlist-container');
    if (playlistContainer) {
        playlistContainer.remove();
    }
    
    localStorage.removeItem('spectra_playlist_state');
    
    setTimeout(() => {
        isManualStopping = false;
    }, 100);
}

function showPlaylistUI(playlistItems) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    const existingPlaylist = document.querySelector('.spectra-playlist-container');
    if (existingPlaylist) {
        existingPlaylist.remove();
    }
    
    const resultsContainer = document.getElementById('resultsContainer');
    const playlistContainer = document.createElement('div');
    playlistContainer.className = 'spectra-playlist-container';

    const playlistText = translations['playlist'] || 'Playlist';
    const playingText = translations['playing'] || 'Playing';
    
    const playlistHeader = document.createElement('div');
    playlistHeader.className = 'spectra-playlist-header';
    playlistHeader.innerHTML = `
        <h4 data-translate="playlist">Playlist</h4>
        <div class="spectra-playall-progress">
            <div class="spectra-progress-info">
                <span data-translate="playing">Playing</span>
                <span>${currentPlaylistIndex + 1} / ${playlistItems.length}</span>
            </div>
            <div class="spectra-progress-bar">
                <div class="spectra-progress-fill" id="playAllProgress"></div>
            </div>
        </div>
    `;
    
    const playlistList = document.createElement('div');
    playlistList.className = 'spectra-playlist-list';
    
    playlistItems.forEach((item, index) => {
        const playlistItem = document.createElement('div');
        playlistItem.className = 'spectra-playlist-item';
        playlistItem.dataset.index = index;
        
        playlistItem.innerHTML = `
            <div class="spectra-playlist-item-index">${index + 1}</div>
            <div class="spectra-playlist-item-info">
                <div class="spectra-playlist-item-title" title="${item.title}">${item.title}</div>
                <div class="spectra-playlist-item-artist" title="${item.artist}">${item.artist}</div>
            </div>
            <div class="spectra-playlist-item-duration">${item.duration || '--:--'}</div>
            <div class="spectra-playlist-item-actions">
                <button class="btn-icon" onclick="playFromPlaylist(${index})">
                    <i class="bi bi-play-fill"></i>
                </button>
            </div>
        `;
        
        playlistItem.addEventListener('click', function(e) {
            if (!e.target.closest('.btn-icon')) {
                playFromPlaylist(index);
            }
        });
        
        playlistList.appendChild(playlistItem);
    });
    
    playlistContainer.appendChild(playlistHeader);
    playlistContainer.appendChild(playlistList);
    resultsContainer.parentNode.insertBefore(playlistContainer, resultsContainer.nextSibling);
    updatePlaylistHighlight(currentPlaylistIndex);
    updateUIText();
}

function scrollToPlaylistItem(index) {
    const playlistItem = document.querySelector(`.spectra-playlist-item[data-index="${index}"]`);
    
    if (playlistItem) {
        try {
            playlistItem.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest'
            });
        } catch (error) {
            playlistItem.scrollIntoView({ block: 'center' });
        }
    }
}

function updatePlaylistHighlight(index) {
    const playlistItems = document.querySelectorAll('.spectra-playlist-item');
    playlistItems.forEach((item, i) => {
        if (i === index) {
            item.classList.add('playing');
            scrollToPlaylistItem(index);
        } else {
            item.classList.remove('playing');
        }
    });
}

function updatePlaylistProgress() {
    const progressInfo = document.querySelector('.spectra-progress-info');
    const progressFill = document.getElementById('playAllProgress');
    
    if (progressInfo && currentPlaylist && currentPlaylist.length > 0) {
        const playingText = (languageTranslations[currentLang] || languageTranslations['en'])['playing'] || 'Playing';
        progressInfo.innerHTML = `
            <span data-translate="playing">${playingText}</span>
            <span>${currentPlaylistIndex + 1} / ${currentPlaylist.length}</span>
        `;
        
        if (progressFill) {
            const progressPercent = ((currentPlaylistIndex + 1) / currentPlaylist.length) * 100;
            progressFill.style.width = `${progressPercent}%`;
        }
        
        if (typeof updateUIText === 'function') {
            updateUIText();
        }
    }
}

function formatYouTubeDuration(isoDuration) {
    if (!isoDuration) return '--:--';
    
    try {
        let timeStr = isoDuration.replace('PT', '');
        
        let hours = 0, minutes = 0, seconds = 0;
        
        const hourMatch = timeStr.match(/(\d+)H/);
        if (hourMatch) {
            hours = parseInt(hourMatch[1]) || 0;
            timeStr = timeStr.replace(hourMatch[0], '');
        }
        
        const minuteMatch = timeStr.match(/(\d+)M/);
        if (minuteMatch) {
            minutes = parseInt(minuteMatch[1]) || 0;
            timeStr = timeStr.replace(minuteMatch[0], '');
        }
        
        const secondMatch = timeStr.match(/(\d+)S/);
        if (secondMatch) {
            seconds = parseInt(secondMatch[1]) || 0;
            timeStr = timeStr.replace(secondMatch[0], '');
        }
        
        if (hours > 0) {
            return `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        } else if (minutes > 0) {
            return `${minutes}:${seconds.toString().padStart(2, '0')}`;
        } else {
            return `0:${seconds.toString().padStart(2, '0')}`;
        }
    } catch (error) {
        return '--:--';
    }
}

function createMusicCard(item, source, index, searchType) {
    const card = document.createElement('div');
    card.className = 'music-card';
    card.dataset.index = index;
    card.dataset.source = source;
    card.dataset.resultType = searchType;
    
    let title, artist, album, cover, duration, previewUrl, isArtistCard = false, isAlbumCard = false;
    
    try {
        switch (source) {
            case 'itunes':
                if (searchType === 'artist') {
                    title = item.artistName || item.collectionArtistName || 'Unknown Artist';
                    artist = '';
                    album = '';
                    cover = item.artworkUrl100?.replace('100x100bb', '300x300bb') || 
                            '/luci-static/resources/icons/artist.svg';
                    duration = null;
                    previewUrl = '';
                    isArtistCard = true;
                } else if (searchType === 'album') {
                    title = item.collectionName || 'Unknown Album';
                    artist = item.artistName || 'Unknown Artist';
                    album = '';
                    cover = item.artworkUrl100?.replace('100x100bb', '300x300bb') || 
                            '/luci-static/resources/icons/album.svg';
                    duration = null;
                    previewUrl = '';
                    isAlbumCard = true;
                } else {
                    title = item.trackName || item.collectionName || 'Unknown Track';
                    artist = item.artistName || 'Unknown Artist';
                    album = item.collectionName || '';
                    cover = item.artworkUrl100?.replace('100x100bb', '300x300bb') || 
                            item.artworkUrl60?.replace('60x60bb', '300x300bb') ||
                            '/luci-static/resources/icons/cover.svg';
                    duration = item.trackTimeMillis ? formatDuration(item.trackTimeMillis) : null;
                    previewUrl = item.previewUrl || '';
                }
                break;
                
            case 'spotify':
                if (searchType === 'artist') {
                    title = item.name || 'Unknown Artist';
                    artist = '';
                    album = '';
                    cover = item.images?.[0]?.url || '/luci-static/resources/icons/artist.svg';
                    duration = null;
                    previewUrl = '';
                    isArtistCard = true;
                } else if (searchType === 'album') {
                    title = item.name || 'Unknown Album';
                    artist = item.artists?.map(a => a.name).join(', ') || 'Unknown Artist';
                    album = '';
                    cover = item.images?.[0]?.url || '/luci-static/resources/icons/album.svg';
                    duration = null;
                    previewUrl = '';
                    isAlbumCard = true;
                } else {
                    title = item.name || 'Unknown Track';
                    artist = item.artists?.map(a => a.name).join(', ') || 'Unknown Artist';
                    album = item.album?.name || '';
                    cover = item.album?.images?.[0]?.url || 
                            item.images?.[0]?.url ||
                            '/luci-static/resources/icons/cover.svg';
                    duration = item.duration_ms ? formatDuration(item.duration_ms) : null;
                    previewUrl = item.preview_url || '';
                }
                break;
                
            case 'youtube':
                title = item.title || 'Unknown Video';
                artist = item.channelTitle || 'YouTube';
                album = '';
                cover = item.thumbnails?.medium?.url || 
                        item.thumbnails?.default?.url ||
                        '/luci-static/resources/icons/cover.svg';
                duration = item.duration || '--:--';
                if (duration && duration.startsWith('PT')) {
                    duration = formatYouTubeDuration(duration);
                }
                previewUrl = item.id ? `https://www.youtube.com/watch?v=${item.id}` : '';
                break;
                
            case 'soundcloud':
                title = item.title || 'Unknown Track';
                artist = item.user?.username || 'SoundCloud';
                album = '';
                cover = item.artwork_url || '/luci-static/resources/icons/cover.svg';
                duration = item.duration ? formatDuration(item.duration) : null;
                previewUrl = item.stream_url || item.permalink_url || '';
                break;
                
            default:
                return null;
        }
    } catch (error) {
        return null;
    }
    
    card.dataset.title = title || 'Unknown Title';
    card.dataset.artist = artist || 'Unknown Artist';
    card.dataset.album = album || '';
    card.dataset.cover = cover;
    card.dataset.previewUrl = previewUrl || '';
    card.dataset.duration = duration || '';
    card.dataset.source = source;
    card.dataset.isArtist = isArtistCard ? 'true' : 'false';
    card.dataset.isAlbum = isAlbumCard ? 'true' : 'false';
    
    if (isArtistCard) {
        card.classList.add('artist-card');
        card.innerHTML = `
            <div class="card-cover">
                <img src="${cover}" alt="${title}" 
                     onerror="this.src='/luci-static/resources/icons/artist.svg'">
                <div class="card-overlay">
                    <div class="play-overlay">
                        <i class="bi bi-person-fill"></i>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div class="card-title" title="${title}">${title}</div>
                <div class="card-artist" data-translate="artist">Artist</div>
            </div>
        `;
        
        card.addEventListener('click', function(e) {
            e.stopPropagation();
            searchArtistSongs(title);
        });
        
        return card;
    }
    
    if (isAlbumCard) {
        card.classList.add('album-card');
        card.innerHTML = `
            <div class="card-cover">
                <img src="${cover}" alt="${title}" 
                     onerror="this.src='/luci-static/resources/icons/album.svg'">
                <div class="card-overlay">
                    <div class="play-overlay">
                        <i class="bi bi-disc-fill"></i>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div class="card-title" title="${title}">${title}</div>
                <div class="card-artist" title="${artist}">${artist}</div>
                <div class="card-album" data-translate="album">Album</div>
            </div>
        `;
        
        card.addEventListener('click', function(e) {
            e.stopPropagation();
            searchAlbumSongs(title, artist);
        });
        
        return card;
    }
    
    card.innerHTML = `
        <div class="card-cover">
            <img src="${cover}" alt="${title}" 
                 onerror="this.src='/luci-static/resources/icons/cover.svg'">
            <div class="card-overlay">
                <div class="play-overlay">
                    <i class="bi bi-play-fill"></i>
                </div>
            </div>
            ${duration ? `<div class="card-duration">${duration}</div>` : ''}
        </div>
        <div class="card-content">
            <div class="card-title" title="${title}">${title}</div>
            <div class="card-artist" title="${artist}">${artist}</div>
            ${album ? `<div class="card-album" title="${album}">${album}</div>` : ''}
        </div>
    `;
    
    card.addEventListener('click', function(e) {
        if (!e.target.closest('.play-overlay')) {
            playMusic(this);
        }
    });
    
    const playOverlay = card.querySelector('.play-overlay');
    if (playOverlay) {
        playOverlay.addEventListener('click', function(e) {
            e.stopPropagation();
            playMusic(card);
        });
    }
    
    return card;
}

function searchArtistSongs(artistName) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    document.getElementById('searchInput').value = artistName;
    document.getElementById('searchType').value = 'song';
    
    currentPage = 0;
    searchResults = [];
    performSearch(false);
    
    const messageTemplate = translations['searching_artist_songs'] || `Searching songs by {artist}`;
    const message = messageTemplate.replace('{artist}', artistName);
    showLogMessage(message);
}

function searchAlbumSongs(albumName, artistName) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    let searchQuery = albumName;
    if (artistName) {
        searchQuery = `${artistName} ${albumName}`;
    }
    
    document.getElementById('searchInput').value = searchQuery;
    document.getElementById('searchType').value = 'song';
    
    currentPage = 0;
    searchResults = [];
    performSearch(false);
    
    const messageTemplate = translations['searching_album_songs'] || `Searching album: {album}`;
    const message = messageTemplate.replace('{album}', albumName);
    showLogMessage(message);
}

function playMusic(card) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];

    if (card.dataset.isArtist === 'true') {
        searchArtistSongs(card.dataset.title);
        return;
    }
    if (card.dataset.isAlbum === 'true') {
        searchAlbumSongs(card.dataset.title, card.dataset.artist);
        return;
    }

    if (isPlaylistMode) {
        stopPlaylistMode();
    }
    
    stopPlayback();
    
    if (currentPlayingCard) {
        currentPlayingCard.classList.remove('active');
    }
    card.classList.add('active');
    currentPlayingCard = card;
    
    const previewUrl = card.dataset.previewUrl;
    const source = card.dataset.source;
    
    if (!previewUrl || previewUrl.trim() === '') {
        const message = translations['no_preview_available'] || 'No preview available for this track';
        showLogMessage(message);
        speakMessage(message);
        return;
    }

    const playingText = translations['playing'] || 'Playing';
    const playMessage = `${playingText}: ${card.dataset.title} - ${card.dataset.artist}`;
    showLogMessage(playMessage);
    speakMessage(playMessage);

    if (source === 'youtube' && !isAudioPlaylistMode) {
        playYouTubeVideoInModal(previewUrl, card.dataset.title, card);
        return;
    }
    
    updatePlayingUIFromCard(card);
    
    if (source === 'youtube') {
        playYouTubeAudio(card);
    } else {
        playRegularAudio(card);
    }
}

function updatePlayingUIFromCard(card) {
    const source = card.dataset.source;    
    const playingCoverIcon = document.getElementById('playingCoverIcon');
    const playingCoverImage = document.getElementById('playingCoverImage');
    const coverUrl = card.dataset.cover;
    
    if (coverUrl && coverUrl.trim() !== '' && !coverUrl.startsWith('data:image/svg+xml')) {
        playingCoverImage.src = coverUrl;
        playingCoverImage.onerror = function() {
            this.style.display = 'none';
            playingCoverIcon.style.display = 'flex';
        };
        playingCoverImage.onload = function() {
            this.style.display = 'block';
            playingCoverIcon.style.display = 'none';
        };
        playingCoverImage.style.display = 'block';
        playingCoverIcon.style.display = 'none';
    } else {
        playingCoverImage.style.display = 'none';
        playingCoverIcon.style.display = 'flex';
    }
    
    const playingTitle = document.getElementById('playingTitle');
    const playingArtist = document.getElementById('playingArtist');
    
    if (playingTitle) {
        playingTitle.textContent = card.dataset.title;
        playingTitle.setAttribute('title', card.dataset.title);
        playingTitle.removeAttribute('data-translate');
    }
    
    if (playingArtist) {
        playingArtist.textContent = card.dataset.artist;
        playingArtist.removeAttribute('data-translate');
    }
    
    const downloadBtn = document.getElementById('downloadSearchBtn');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (downloadBtn) downloadBtn.disabled = false;
    if (prevBtn) prevBtn.disabled = false;
    if (nextBtn) nextBtn.disabled = false;
    
    const timeContainer = document.querySelector('.playing-time-container');
    if (timeContainer) {
        timeContainer.style.display = 'flex';
        
        const wrapper = timeContainer.querySelector('.playing-time-wrapper');
        if (wrapper) {
            wrapper.style.display = 'flex';
        }
    }
    
    const totalTimeElement = document.getElementById('playingTotalTime');
    if (totalTimeElement) {
        if (card.dataset.duration && card.dataset.duration !== '--:--') {
            totalTimeElement.textContent = card.dataset.duration;
        } else {
            if (source === 'youtube') {
                totalTimeElement.textContent = '0:30';
            } else {
                totalTimeElement.textContent = '0:30';
            }
        }
    }
    
    const currentTimeElement = document.getElementById('playingCurrentTime');
    if (currentTimeElement) {
        currentTimeElement.textContent = '0:00';
    }
    
    const progressElement = document.getElementById('playingTimeProgress');
    const thumbElement = document.getElementById('playingTimeThumb');
    const seekBar = document.getElementById('playingTimeSeek');
    
    if (progressElement) progressElement.style.width = '0%';
    if (thumbElement) thumbElement.style.left = '0%';
    if (seekBar) seekBar.value = 0;
    
    if (source === 'youtube' && downloadBtn) {
        downloadBtn.disabled = true;
    }
    
    const playBtn = document.getElementById('playSearchBtn');
    const stopBtn = document.getElementById('stopSearchBtn');
    
    if (playBtn) {
        playBtn.disabled = false;
        playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
    }
    
    if (stopBtn) {
        stopBtn.disabled = false;
    }
}

function updatePlayingUIFromItem(item) {
    const source = item.source;
    
    const playingCoverIcon = document.getElementById('playingCoverIcon');
    const playingCoverImage = document.getElementById('playingCoverImage');
    
    if (item.cover && item.cover.trim() !== '' && !item.cover.startsWith('data:image/svg+xml')) {
        playingCoverImage.src = item.cover;
        playingCoverImage.onerror = function() {
            this.style.display = 'none';
            playingCoverIcon.style.display = 'flex';
        };
        playingCoverImage.onload = function() {
            this.style.display = 'block';
            playingCoverIcon.style.display = 'none';
        };
        playingCoverImage.style.display = 'block';
        playingCoverIcon.style.display = 'none';
    } else {
        playingCoverImage.style.display = 'none';
        playingCoverIcon.style.display = 'flex';
    }
    
    const playingTitle = document.getElementById('playingTitle');
    const playingArtist = document.getElementById('playingArtist');
    
    if (playingTitle && item.title) {
        playingTitle.textContent = item.title;
        playingTitle.setAttribute('title', item.title); 
        playingTitle.removeAttribute('data-translate');
    }
    
    if (playingArtist && item.artist) {
        playingArtist.textContent = item.artist;
        playingArtist.removeAttribute('data-translate');
    }
    
    const downloadBtn = document.getElementById('downloadSearchBtn');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (downloadBtn) downloadBtn.disabled = false;
    if (prevBtn) prevBtn.disabled = false;
    if (nextBtn) nextBtn.disabled = false;
    
    const timeContainer = document.querySelector('.playing-time-container');
    if (timeContainer) {
        timeContainer.style.display = 'flex';
        
        const wrapper = timeContainer.querySelector('.playing-time-wrapper');
        if (wrapper) {
            wrapper.style.display = 'flex';
        }
    }
    
    if (item.duration && item.duration !== '--:--') {
        const totalTimeElement = document.getElementById('playingTotalTime');
        if (totalTimeElement) {
            totalTimeElement.textContent = item.duration;
        }
    } else {
        const totalTimeElement = document.getElementById('playingTotalTime');
        if (totalTimeElement) {
            totalTimeElement.textContent = '0:30';
        }
    }
    
    const currentTimeElement = document.getElementById('playingCurrentTime');
    if (currentTimeElement) {
        currentTimeElement.textContent = '0:00';
    }
    
    const progressElement = document.getElementById('playingTimeProgress');
    const thumbElement = document.getElementById('playingTimeThumb');
    const seekBar = document.getElementById('playingTimeSeek');
    
    if (progressElement) progressElement.style.width = '0%';
    if (thumbElement) thumbElement.style.left = '0%';
    if (seekBar) seekBar.value = 0;
    
    if (item.source === 'youtube' && downloadBtn) {
        downloadBtn.disabled = true;
    }
    
    const playBtn = document.getElementById('playSearchBtn');
    const stopBtn = document.getElementById('stopSearchBtn');
    
    if (playBtn) {
        playBtn.disabled = false;
        playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
    }
    
    if (stopBtn) {
        stopBtn.disabled = false;
    }
}

function playRegularAudio(card) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    if (currentAudio) {
        try {
            currentAudio.pause();
            currentAudio.removeEventListener('timeupdate', updateTimeProgress);
        } catch (e) {}
        currentAudio = null;
    }
    
    const previewUrl = card.dataset.previewUrl;
    
    if (!previewUrl || previewUrl.trim() === '') {
        const message = translations['no_preview_available'] || 'No preview available for this track';
        showLogMessage(message);
        return;
    }
    
    const timeContainer = document.querySelector('.playing-time-container');
    if (timeContainer) {
        timeContainer.style.display = 'flex';
    }
    
    const currentTimeElement = document.getElementById('playingCurrentTime');
    const totalTimeElement = document.getElementById('playingTotalTime');
    
    if (currentTimeElement) {
        currentTimeElement.textContent = '0:00';
    }
    
    if (totalTimeElement) {
        if (card.dataset.duration && card.dataset.duration !== '--:--') {
            totalTimeElement.textContent = card.dataset.duration;
        } else {
            totalTimeElement.textContent = '0:30';
        }
    }
    
    const progressElement = document.getElementById('playingTimeProgress');
    const thumbElement = document.getElementById('playingTimeThumb');
    const seekBar = document.getElementById('playingTimeSeek');
    
    if (progressElement) progressElement.style.width = '0%';
    if (thumbElement) thumbElement.style.left = '0%';
    if (seekBar) seekBar.value = 0;
    
    currentAudio = new Audio(previewUrl);
    
    const playBtn = document.getElementById('playSearchBtn');
    playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
    
    playBtn.onclick = function() {
        if (currentAudio && currentAudio.paused) {
            currentAudio.play();
            this.innerHTML = '<i class="bi bi-pause-fill"></i>';
            updateTimeProgress();
        } else if (currentAudio && !currentAudio.paused) {
            currentAudio.pause();
            this.innerHTML = '<i class="bi bi-play-fill"></i>';
        }
    };
    
    currentAudio.addEventListener('loadedmetadata', function() {
        const totalTimeElement = document.getElementById('playingTotalTime');
        if (totalTimeElement && currentAudio.duration && !isNaN(currentAudio.duration)) {
            totalTimeElement.textContent = formatTime(currentAudio.duration);
        }
    });
    
    currentAudio.addEventListener('play', function() {
        playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
        updateTimeProgress();
    });
    
    currentAudio.addEventListener('pause', function() {
        playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
    });
    
    currentAudio.addEventListener('timeupdate', function() {
        updateTimeProgress();
    });
    
    currentAudio.addEventListener('ended', function() {
        playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
        
        if (isPlaylistMode && currentPlaylist.length > 0) {
            setTimeout(() => {
                const nextIndex = findNextValidTrack(currentPlaylistIndex);
                if (nextIndex !== -1) {
                    playFromPlaylist(nextIndex);
                } else {
                    const translations = languageTranslations[currentLang] || languageTranslations['en'];
                    const message = translations['playlist_completed'] || 'Playlist completed';
                    showLogMessage(message);
                    stopPlaylistMode();
                }
            }, 1000);
        }
    });
    
    currentAudio.addEventListener('error', function(e) {
        const message = translations['no_preview_available'] || 'No preview available for this track';
        showLogMessage(message);
        playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
        
        if (isPlaylistMode && currentPlaylist.length > 0) {
            setTimeout(() => {
                const nextIndex = findNextValidTrack(currentPlaylistIndex);
                if (nextIndex !== -1) {
                    playFromPlaylist(nextIndex);
                }
            }, 1000);
        }
    });
    
    currentAudio.play().catch(error => {
        const message = translations['no_preview_available'] || 'No preview available for this track';
        showLogMessage(message);
        playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
        
        if (isPlaylistMode && currentPlaylist.length > 0) {
            setTimeout(() => {
                const nextIndex = findNextValidTrack(currentPlaylistIndex);
                if (nextIndex !== -1) {
                    playFromPlaylist(nextIndex);
                }
            }, 1000);
        }
    });
}

function loadMoreResults() {
    currentPage++;
    performSearch(true);
}

function showNoResults() {
    const container = document.getElementById('resultsContainer');
    container.innerHTML = `
        <div class="no-results">
            <i class="bi bi-music-note-slash"></i>
            <p data-translate="no_results_found">No results found</p>
        </div>
    `;
    document.getElementById('loadMoreContainer').style.display = 'none';
    document.getElementById('playAllBtn').style.display = 'none';    
    document.getElementById('prevBtn').disabled = true;
    document.getElementById('nextBtn').disabled = true;
    
    if (typeof updateUIText === 'function') {
        updateUIText();
    }
}

function downloadTrack(card) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    const previewUrl = card.dataset.previewUrl;
    const title = card.dataset.title || 'music_track';
    const artist = card.dataset.artist || 'unknown_artist';
    
    if (!previewUrl) {
        const message = translations['no_download_available'] || 'No download available for this track';
        showLogMessage(message);
        speakMessage(message);
        return;
    }
    
    const link = document.createElement('a');
    link.href = previewUrl;
    
    let filename = `${artist} - ${title}`;
    
    filename = filename.replace(/[<>:"/\\|?*]/g, '_');
    filename = filename.replace(/\s+/g, '_');
    
    const urlParts = previewUrl.split('?')[0].split('.');
    const extension = urlParts.length > 1 ? urlParts[urlParts.length - 1] : 'mp3';
    
    const allowedExtensions = ['mp3', 'm4a', 'aac', 'ogg', 'wav', 'flac', 'mp4'];
    const fileExt = allowedExtensions.includes(extension.toLowerCase()) ? extension : 'mp3';
    
    link.download = `${filename}.${fileExt}`;
    
    const downloadBtn = document.getElementById('downloadSearchBtn');
    const originalContent = downloadBtn.innerHTML;
    downloadBtn.innerHTML = '<i class="bi bi-download"></i>';
    downloadBtn.style.backgroundColor = 'var(--color-success)';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => {
        downloadBtn.innerHTML = '<i class="fas fa-download"></i>';
        downloadBtn.style.backgroundColor = '';
    }, 3000);
}

function formatDuration(milliseconds) {
    if (!milliseconds) return '';
    
    const totalSeconds = Math.floor(milliseconds / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
}

function showApiKeyConfigPage() {
    const uk = UIkit || ui;
    if (!uk || !uk.modal) {
        return;
    }
    
    const modalElement = document.getElementById('apiKeyConfigModal');
    if (!modalElement) {
        return;
    }
    
    if (typeof updateUIText === 'function') {
        updateUIText();
    }
    
    loadCurrentKeys();
    
    uk.modal(modalElement).show();
}

function loadCurrentKeys() {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    //showLogMessage(translations['loading_keys'] || 'Loading current API keys...');
    
    fetch('/spectra/api_keys_proxy.php?action=get')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.keys.spotify) {
                    document.getElementById('spotifyClientId').value = data.keys.spotify.client_id || '';
                    document.getElementById('spotifyClientSecret').value = data.keys.spotify.client_secret || '';
                }
                if (data.keys.youtube) {
                    document.getElementById('youtubeApiKey').value = data.keys.youtube.api_key || '';
                }
                if (data.keys.soundcloud) {
                    document.getElementById('soundcloudClientId').value = data.keys.soundcloud.client_id || '';
                }
                //showLogMessage(translations['keys_loaded'] || 'API keys loaded successfully');
            } else {
                showLogMessage(data.message || translations['failed_to_load_keys'] || 'Failed to load API keys');
            }
        })
        .catch(error => {
            showLogMessage(translations['connection_error'] || 'Connection error');
        });
}

function saveApiKeys() {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    const keys = {
        spotify: {
            client_id: document.getElementById('spotifyClientId').value.trim(),
            client_secret: document.getElementById('spotifyClientSecret').value.trim()
        },
        youtube: {
            api_key: document.getElementById('youtubeApiKey').value.trim()
        },
        soundcloud: {
            client_id: document.getElementById('soundcloudClientId').value.trim()
        }
    };
    
    if (!keys.spotify.client_id && !keys.spotify.client_secret && 
        !keys.youtube.api_key && !keys.soundcloud.client_id) {
        showLogMessage(translations['no_keys_to_save'] || 'No keys to save');
        return;
    }
    
    showLogMessage(translations['saving_keys'] || 'Saving API keys...');
    
    fetch('/spectra/api_keys_proxy.php?action=save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ keys: keys })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP erro: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showLogMessage(translations['keys_saved_successfully'] || 'API keys saved successfully');
            
            setTimeout(() => {
                const uk = UIkit || ui;
                const modal = document.getElementById('apiKeyConfigModal');
                if (uk && uk.modal && modal) {
                    uk.modal(modal).hide();
                }
            }, 3000);
        } else {
            showLogMessage(translations['connection_error'] || 'Connection error');
        }
    })
    .catch(error => {
        showLogMessage('Connection error: ' + error.message);
    });
}

function checkApiStatus() {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    showLogMessage(translations['testing_api_connections'] || 'Testing API connections...');
    
    const testBtn = document.querySelector('button[onclick="checkApiStatus()"]');
    const originalBtn = testBtn ? testBtn.innerHTML : null;
    if (testBtn) {
        testBtn.innerHTML = '<span uk-icon="spinner" class="uk-icon-spin"></span>';
        testBtn.disabled = true;
    }
    
    const results = [];
    const apis = [
        { name: 'Spotify', key: 'spotify' },
        { name: 'YouTube', key: 'youtube' },
        { name: 'SoundCloud', key: 'soundcloud' }
    ];
    
    const testPromises = apis.map(api => {
        return fetch('/spectra/search_proxy.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                source: api.key,
                query: 'test',
                type: 'song',
                limit: 1
            })
        })
        .then(response => response.json())
        .then(data => ({
            name: api.name,
            success: data.success,
            message: data.message
        }))
        .catch(error => ({
            name: api.name,
            success: false,
            message: translations['connection_failed'] || 'Connection failed'
        }));
    });
    
    Promise.all(testPromises).then(testResults => {
        testResults.forEach(result => {
            if (result.success) {
                const successText = translations['api_working'] || 'API is working';
                showLogMessage(`✅ ${result.name}: ${successText}`);
            } else {
                const failedText = result.message || translations['api_not_configured'] || 'API not configured';
                showLogMessage(`❌ ${result.name}: ${failedText}`);
            }
        });
        
        const successCount = testResults.filter(r => r.success).length;
        
        setTimeout(() => {
            const summaryTemplate = translations['api_test_complete'] || 'API test complete';
            const successTemplate = translations['success_count'] || 'Success: {count}/{total}';
            
            const formattedResult = successTemplate
                .replace('{count}', successCount)
                .replace('{total}', apis.length);
            
            const finalMessage = `${summaryTemplate} - ${formattedResult}`;
            showLogMessage(finalMessage);
            
            if (testBtn) {
                testBtn.innerHTML = originalBtn;
                testBtn.disabled = false;
            }
        }, 500);
    });
}

function testApi(source) {
    return new Promise(resolve => {
        const timeout = setTimeout(() => {
            resolve({
                source: source,
                success: false,
                message: 'Request timeout'
            });
        }, 10000);
        
        fetch('/spectra/search_proxy.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                source: source,
                query: 'test',
                type: 'song',
                limit: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            clearTimeout(timeout);
            resolve({
                source: source,
                success: data.success || false,
                message: data.message || (data.success ? 'API is working' : 'API not configured')
            });
        })
        .catch(error => {
            clearTimeout(timeout);
            resolve({
                source: source,
                success: false,
                message: 'Connection failed'
            });
        });
    });
}

