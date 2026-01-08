<%+header%>

<div class="spectra-map" id="spectra-search">
    <h2 name="content" data-translate="online_music_search">Online Music Search</h2>
    
    <div class="spectra-section">
        <div class="search-box">
            <div class="search-input-group">
                <input type="text" id="searchInput" class="search-input" 
                       placeholder="Search for songs, artists, albums..." data-tooltip-title="search_placeholder" data-placeholder="search_placeholder">
                <button id="searchButton" class="btn btn-primary">
                    <i class="bi bi-search"></i> <span data-translate="search_button">Search</span>
                </button>
            </div>
            
            <div class="search-options">
                <select id="searchType" class="cbi-input-select">
                    <option value="song" data-translate="song_type">Songs</option>
                    <option value="artist" data-translate="artist_type">Artists</option>
                    <option value="album" data-translate="album_type">Albums</option>
                    <option value="playlist" data-translate="playlist_type">Playlists</option>
                </select>
                
                <select id="searchSource" class="cbi-input-select">
                    <option value="itunes">iTunes/Apple Music</option>
                    <option value="spotify">Spotify</option>
                    <option value="youtube">YouTube</option>
                    <option value="soundcloud">SoundCloud</option>
                </select>

                <button id="spectraClearCacheBtn" class="btn cbi-button cbi-button-remove" type="button" data-tooltip-title="clear_button">
                    <i class="bi bi-trash"></i> <span data-translate="clear_button">Clear Cache</span>
                </button>

                <button id="apiKeyConfigBtn" class="btn btn-default" type="button" 
                    onclick="showApiKeyConfigPage()"
                    data-tooltip-title="configure_api_keys">
                        <i class="bi bi-key"></i> <span data-translate="configure_api_keys">Configure API Keys</span>
                </button>
            </div>
        </div>
        
        <div class="search-results">
            <div class="results-header">
                <div class="results-info">
                    <span id="resultsCount">0 results</span>
                </div>
                <div class="results-actions">
                    <button id="playAllBtn" class="btn cbi-button cbi-button-apply" style="display: none;">
                        <i class="bi bi-play-circle"></i> <span data-translate="play_all">Play All</span>
                    </button>
                </div>
            </div>
            
            <div id="resultsContainer" class="results-grid">
                <div class="no-results">
                    <i class="bi bi-music-note-beamed"></i>
                    <p data-translate="search_prompt">Search for music to get started</p>
                </div>
            </div>
            
            <div id="loadMoreContainer" style="display: none;">
                <button id="loadMoreButton" class="btn btn-success">
                    <i class="bi bi-arrow-down-circle"></i> <span data-translate="load_more">Load More</span>
                </button>
            </div>
        </div>
        
        <div class="current-playback">
            <div class="now-playing">
                <div class="now-playing-info">
                    <div class="playing-cover">
                        <div id="playingCoverIcon" class="playing-cover-icon">
                            <i class="bi bi-music-note-beamed"></i>
                        </div>
                            <img id="playingCoverImage" class="playing-cover-image" src="" alt="Cover" style="display: none;">
                    </div>
                    <div class="playing-details">
                        <div id="playingTitle" class="playing-title" data-translate="not_playing">Not Playing</div>
                        <div id="playingArtist" class="playing-artist" data-translate="select_song_prompt">Select a song to play</div>
                    </div>
                </div>
                
                <div class="playing-time-container">
                    <div class="playing-time-wrapper">
                        <span id="playingCurrentTime" class="time-text">0:00</span>
                        <div class="time-progress-bar">
                            <div class="time-progress-bg"></div>
                            <div id="playingTimeProgress" class="time-progress"></div>
                            <div id="playingTimeThumb" class="time-thumb"></div>
                            <input type="range" id="playingTimeSeek" class="time-seek" min="0" max="100" value="0">
                        </div>
                        <span id="playingTotalTime" class="time-text">0:30</span>
                    </div>
                </div>
                
                <div class="playing-controls">
                    <button id="prevBtn" class="btn-default" disabled data-tooltip-title="previous_track">
                        <i class="bi bi-skip-backward-fill"></i>
                    </button>
                    
                    <button id="playSearchBtn" class="btn-primary" disabled data-tooltip-title="play_pause">
                        <i class="bi bi-play-fill"></i>
                    </button>
                    
                    <button id="nextBtn" class="btn-default" disabled data-tooltip-title="next_track">
                        <i class="bi bi-skip-forward-fill"></i>
                    </button>
                    
                    <button id="stopSearchBtn" class="btn-default" disabled>
                        <i class="bi bi-stop-fill"></i>
                    </button>
                    <button id="downloadSearchBtn" class="btn-download" data-tooltip-title="download_options">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="apiKeyConfigModal" uk-modal>
    <div class="uk-modal-dialog  modal-lg">

        <div class="uk-modal-header">
            <h2 class="uk-modal-title" data-translate="configure_api_keys">
                Configure API Keys
            </h2>
            <button class="uk-modal-close-default" type="button" uk-close></button>
        </div>

        <div class="uk-modal-body">
            <p class="uk-text-muted" data-translate="api_key_config_description">
                Configure API keys for music services. These keys will be saved to the server configuration file.
            </p>

            <div class="uk-margin-bottom">
                <div class="uk-flex uk-flex-middle uk-flex-between">
                    <div class="uk-text-small uk-text-muted">
                        <i class="bi bi-shield-check uk-margin-small-right"></i>
                        <span data-translate="keys_backup_tip">Export/Import your API keys configuration</span>
                    </div>
                    <div class="uk-button-group">
                        <button class="uk-button uk-margin-small-left uk-button-primary uk-button-small" type="button"
                                onclick="exportApiKeys()" data-tooltip-title="export_keys">
                            <i class="bi bi-download uk-margin-small-right"></i>
                            <span data-translate="export_keys">Export Keys</span>
                        </button>
                        <button class="uk-button uk-margin-small-left uk-button-success uk-button-small" type="button"
                                onclick="document.getElementById('importFileInput').click()" data-tooltip-title="import_keys">
                            <i class="bi bi-upload uk-margin-small-right"></i>
                            <span data-translate="import_keys">Import Keys</span>
                        </button>
                        <input type="file" id="importFileInput" accept=".json" style="display: none;" onchange="importApiKeys(this)">
                    </div>
                </div>
            </div>

            <form class="uk-form-stacked">
                <fieldset class="uk-fieldset uk-margin">
                    <legend class="uk-legend">
                        <i class="bi bi-spotify uk-margin-small-right" style="font-size: 0.9em;"></i>
                        <span data-translate="spotify_api_keys">Spotify API Keys</span>
                    </legend>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="spotifyClientId" data-translate="client_id">
                            Client ID
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" id="spotifyClientId"
                                   placeholder="Enter Spotify Client ID">
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="spotifyClientSecret" data-translate="client_secret">
                            Client Secret
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="password" id="spotifyClientSecret"
                                   placeholder="Enter Spotify Client Secret">
                        </div>
                    </div>

                    <p class="uk-text-small uk-text-muted">
                        <span data-translate="get_from">Get from:</span>
                        <a href="https://developer.spotify.com/dashboard" target="_blank" rel="noopener noreferrer" class="uk-link uk-link-muted">
                             https://developer.spotify.com/dashboard
                         </a>
                    </p>

                </fieldset>

                <fieldset class="uk-fieldset uk-margin">
                    <legend class="uk-legend">
                        <i class="bi bi-youtube uk-margin-small-right" style="font-size: 1em;"></i>
                        <span data-translate="youtube_api_key">YouTube API Key</span>
                    </legend>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="youtubeApiKey" data-translate="api_key">
                            API Key
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="password" id="youtubeApiKey"
                                   placeholder="Enter YouTube API Key">
                        </div>
                    </div>

                    <p class="uk-text-small uk-text-muted">
                        <span data-translate="get_from">Get from:</span>
                        <a href="https://console.cloud.google.com/apis" target="_blank" rel="noopener noreferrer" class="uk-link uk-link-muted">
                            https://console.cloud.google.com/apis
                        </a>
                    </p>
                </fieldset>

                <fieldset class="uk-fieldset uk-margin">
                    <legend class="uk-legend">
                        <span uk-icon="icon: soundcloud; ratio: 1.5" class="uk-margin-small-right"></span>
                        <span data-translate="soundcloud_client_id">SoundCloud Client ID</span>
                    </legend>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="soundcloudClientId" data-translate="client_id">
                            Client ID
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" id="soundcloudClientId"
                                   placeholder="Enter SoundCloud Client ID">
                        </div>
                    </div>

                    <p class="uk-text-small uk-text-muted" data-translate="soundcloud_key_hint">
                        SoundCloud API requires OAuth authentication
                    </p>
                </fieldset>
            </form>
        </div>

        <div class="uk-modal-footer uk-text-right">
            <div class="uk-button-group">
                <button class="uk-button uk-margin-small-left uk-button-success uk-hidden" type="button"
                        onclick="loadCurrentKeys()">
                    <span uk-icon="refresh"></span>
                    <span data-translate="load_current_keys">Load Current Keys</span>
                </button>

                <button class="uk-button uk-margin-small-left uk-button-primary" type="button"
                        onclick="saveApiKeys()">
                    <span uk-icon="save"></span>
                    <span data-translate="save_keys">Save Keys</span>
                </button>

                <button class="uk-button uk-margin-small-left uk-button-info" type="button"
                        onclick="checkApiStatus()">

                    <span data-translate="test_api">Test API</span>
                </button>

                <button class="uk-button uk-margin-small-left uk-button-gray uk-modal-close" type="button"
                        data-translate="close">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
<link href="/luci-static/spectra/css/main.css" rel="stylesheet">
<script src="/luci-static/spectra/js/plugin.js"></script>
<%+footer%>

<script>
function exportApiKeys() {
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
        },
        exported_at: new Date().toISOString(),
        tool: 'Spectra Music Search'
    };
    
    const jsonStr = JSON.stringify(keys, null, 2);
    const blob = new Blob([jsonStr], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = `spectra-api-keys-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    showLogMessage(translations['keys_exported'] || 'API keys exported successfully');
}

function importApiKeys(input) {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    
    const file = input.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const keys = JSON.parse(e.target.result);
            
            if (!keys.spotify || !keys.youtube || !keys.soundcloud) {
                throw new Error('Invalid backup file format');
            }
            
            if (keys.spotify.client_id) {
                document.getElementById('spotifyClientId').value = keys.spotify.client_id;
            }
            if (keys.spotify.client_secret) {
                document.getElementById('spotifyClientSecret').value = keys.spotify.client_secret;
            }
            if (keys.youtube.api_key) {
                document.getElementById('youtubeApiKey').value = keys.youtube.api_key;
            }
            if (keys.soundcloud.client_id) {
                document.getElementById('soundcloudClientId').value = keys.soundcloud.client_id;
            }
            
            showLogMessage(translations['keys_imported'] || 'API keys imported successfully');
            
        } catch (error) {
            showLogMessage(translations['import_error'] || 'Error importing backup file: ' + error.message);
        }
        
        input.value = '';
    };
    
    reader.readAsText(file);
}

document.getElementById('spectraClearCacheBtn').onclick = function() {
    const translations = languageTranslations[currentLang] || languageTranslations['en'];
    showGlobalModal({
        defaultText: translations['cache_clear_confirm'] || 'Are you sure you want to clear the search cache?',
        onConfirm: () => {
            fetch('/spectra/clear_cache.php')
                .then(r => r.json())
                .then(data => {
                    showLogMessage(data.success ? 
                        (translations['cache_cleared'] || 'Cache cleared successfully') : 
                        (translations['clear_cache_failed'] || 'Failed to clear cache')
                    );
                    setTimeout(() => location.reload(), 2000);
                })
                .catch(() => {
                    showLogMessage(translations['connection_error'] || 'Connection error');
                });
        }
    });
};

document.addEventListener('keydown', function(event) {
    const target = event.target;
    const isTyping = target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable;
    
    if (isTyping) return;
    
    switch(event.code) {
        case 'KeyA':
            event.preventDefault();
            const youtubePrevBtn = document.querySelector('[onclick*="playPrevYouTubeVideo"]');
            if (youtubePrevBtn) {
                youtubePrevBtn.click();
            } else {
                document.getElementById('prevBtn')?.click();
            }
            break;
            
        case 'KeyS':
            event.preventDefault();
            const youtubePlayBtn = document.querySelector('.youtube-playpause-btn');
            if (youtubePlayBtn) {
                youtubePlayBtn.click();
            } else {
                document.getElementById('playSearchBtn')?.click();
            }
            break;
            
        case 'KeyD':
            event.preventDefault();
            const youtubeNextBtn = document.querySelector('[onclick*="playNextYouTubeVideo"]');
            if (youtubeNextBtn) {
                youtubeNextBtn.click();
            } else {
                document.getElementById('nextBtn')?.click();
            }
            break;
            
        case 'KeyW':
            event.preventDefault();
            const pipBtn = document.querySelector('.pip-btn');
            if (pipBtn) {
                pipBtn.click();
            }
            break;
            
        case 'KeyQ':
            event.preventDefault();
            const playlistIndicator = document.querySelector('.youtube-smart-indicator');
            if (playlistIndicator) {
                playlistIndicator.click();
            }
            break;

        case 'KeyE':
            event.preventDefault();
            const searchBtn = document.querySelector('.playlist-search-btn');
            if (searchBtn) {
                searchBtn.click();
            }
            break;
    }
});
</script>
