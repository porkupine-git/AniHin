// Video Player Component for Watch Page

export function renderVideoPlayer(anime, currentEp = 1) {
    return `
    <div class="player-wrapper mb-3" style="background:#14131d; border-radius:10px; overflow:hidden;">
        <div class="video-player-container position-relative" style="width:100%; aspect-ratio:16/9; background:#000;">
            <iframe id="anime-video-player" 
                src="${anime.videoEmbedUrl || 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'}" 
                style="width:100%; height:100%; border:none;" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
            </iframe>
        </div>

        <div class="player-controls d-flex align-items-center justify-content-between p-2 flex-wrap" style="background:#191826; border-top:1px solid rgba(255,255,255,0.06); gap:8px;">
            <div class="pc-left d-flex align-items-center" style="gap:10px;">
                <button id="turn-off-light" class="btn btn-sm btn-dark" style="font-size:12px; background:rgba(255,255,255,0.08); border:none;">
                    <i class="fas fa-lightbulb mr-1"></i> Light <span class="tb-result text-warning"></span>
                </button>
                <button id="auto-play-toggle" class="btn btn-sm btn-dark" style="font-size:12px; background:rgba(255,255,255,0.08); border:none;">
                    Auto Play <span class="badge badge-success ml-1">ON</span>
                </button>
                <button id="auto-next-toggle" class="btn btn-sm btn-dark" style="font-size:12px; background:rgba(255,255,255,0.08); border:none;">
                    Auto Next <span class="badge badge-success ml-1">ON</span>
                </button>
            </div>

            <div class="pc-right d-flex align-items-center" style="gap:10px;">
                <button id="prev-ep-btn" class="btn btn-sm btn-dark" style="font-size:12px; background:rgba(255,255,255,0.08); border:none;">
                    <i class="fas fa-backward mr-1"></i> Prev
                </button>
                <span class="badge badge-pill badge-primary" style="background:#ffbade; color:#111; font-weight:700; padding:6px 12px; font-size:12px;">
                    Ep <span id="current-ep-display">${currentEp}</span>
                </span>
                <button id="next-ep-btn" class="btn btn-sm btn-dark" style="font-size:12px; background:rgba(255,255,255,0.08); border:none;">
                    Next <i class="fas fa-forward ml-1"></i>
                </button>
                <button id="fullscreen-btn" class="btn btn-sm btn-dark" style="font-size:12px; background:rgba(255,255,255,0.08); border:none;">
                    <i class="fas fa-expand mr-1"></i> Expand
                </button>
            </div>
        </div>
    </div>
    `;
}

export function initPlayerControls(onEpisodeChange) {
    const lightBtn = document.getElementById('turn-off-light');
    if (lightBtn) {
        lightBtn.addEventListener('click', () => {
            document.body.classList.toggle('theater-mode');
            const isActive = document.body.classList.contains('theater-mode');
            lightBtn.querySelector('.tb-result').innerText = isActive ? '(ON)' : '';
        });
    }

    const fullscreenBtn = document.getElementById('fullscreen-btn');
    const playerContainer = document.querySelector('.video-player-container');
    if (fullscreenBtn && playerContainer) {
        fullscreenBtn.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                playerContainer.requestFullscreen().catch(err => console.warn(err));
            } else {
                document.exitFullscreen().catch(err => console.warn(err));
            }
        });
    }

    const prevBtn = document.getElementById('prev-ep-btn');
    const nextBtn = document.getElementById('next-ep-btn');
    const currentEpDisplay = document.getElementById('current-ep-display');

    if (prevBtn && nextBtn && currentEpDisplay) {
        prevBtn.addEventListener('click', () => {
            let current = parseInt(currentEpDisplay.innerText, 10) || 1;
            if (current > 1) {
                current--;
                currentEpDisplay.innerText = current;
                if (onEpisodeChange) onEpisodeChange(current);
            }
        });

        nextBtn.addEventListener('click', () => {
            let current = parseInt(currentEpDisplay.innerText, 10) || 1;
            current++;
            currentEpDisplay.innerText = current;
            if (onEpisodeChange) onEpisodeChange(current);
        });
    }
}
