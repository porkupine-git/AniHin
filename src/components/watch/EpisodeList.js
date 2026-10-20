// Episode List Component for Watch Page with Live Search

export function renderEpisodeList(anime, activeEpisode = 1) {
    const totalEps = anime.totalEpisodes || (anime.subCount || 12);
    const episodes = Array.from({ length: totalEps }, (_, i) => i + 1);

    return `
    <div class="episodes-list p-3 mb-3" style="background:#201f31; border-radius:8px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="cat-heading mb-0" style="color:#ffbade; font-size:15px; font-weight:700;">
                List of episodes: (<span id="ep-total-count">${totalEps}</span> eps)
            </h4>
            <div class="ep-search" style="max-width: 170px;">
                <input type="text" class="form-control form-control-sm" placeholder="Number of Ep" id="search-ep-input" style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:20px; padding:4px 12px; font-size:12px;">
            </div>
        </div>

        <div class="episodes-ul-wrapper" style="max-height:220px; overflow-y:auto; padding-right:5px;">
            <div class="episodes-ul d-flex flex-wrap" style="gap:6px;">
                ${episodes.map(ep => `
                <div class="ep-item-wrapper">
                    <a href="javascript:;" class="btn btn-sm ep-item ${ep === activeEpisode ? 'active' : ''}" data-number="${ep}" style="min-width:44px; padding:5px 10px; border-radius:4px; font-weight:600; font-size:12px; ${ep === activeEpisode ? 'background:#ffbade; color:#111;' : 'background:rgba(255,255,255,0.06); color:#ddd;'} text-decoration:none;">
                        ${ep}
                    </a>
                </div>
                `).join('')}
            </div>
        </div>
    </div>
    `;
}

export function initEpisodeListEvents(onSelectEpisode) {
    const epButtons = document.querySelectorAll('.ep-item');
    const currentEpDisplay = document.getElementById('current-ep-display');

    epButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            epButtons.forEach(b => {
                b.classList.remove('active');
                b.style.background = 'rgba(255,255,255,0.06)';
                b.style.color = '#ddd';
            });
            this.classList.add('active');
            this.style.background = '#ffbade';
            this.style.color = '#111';

            const epNum = parseInt(this.getAttribute('data-number'), 10);
            if (currentEpDisplay) {
                currentEpDisplay.innerText = epNum;
            }
            if (onSelectEpisode) onSelectEpisode(epNum);
        });
    });

    const searchInput = document.getElementById('search-ep-input');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const val = this.value.trim().toLowerCase();
            document.querySelectorAll('.ep-item-wrapper').forEach(wrap => {
                const ep = wrap.querySelector('.ep-item').getAttribute('data-number');
                if (!val || ep.includes(val)) {
                    wrap.style.display = 'block';
                } else {
                    wrap.style.display = 'none';
                }
            });
        });
    }
}
