// Server Selector Component with Hindi Dub & English Sub Options

export function renderServerSelector(anime) {
    const subServers = (anime.servers && anime.servers.sub) || ["HD-1", "HD-2", "MegaCloud"];
    const dubServers = (anime.servers && anime.servers.dub) || ["Hindi HD-1", "Hindi HD-2", "English Dub"];

    return `
    <div class="player-servers p-3 mb-3" style="background:#201f31; border-radius:8px;">
        <div class="ps_-block ps_-block-sub mb-3 pb-3" style="border-bottom:1px solid rgba(255,255,255,0.06);">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-closed-captioning mr-2 text-success" style="font-size:16px;"></i>
                <strong style="font-size:13px; color:#fff;">SUB:</strong>
                <span class="text-muted ml-2" style="font-size:11.5px;">If current server doesn't work, switch to another!</span>
            </div>
            <div class="ps__-list d-flex flex-wrap" style="gap:8px;">
                ${subServers.map((s, idx) => `
                <a href="javascript:;" class="btn btn-sm btn-server ${idx === 0 ? 'active' : ''}" data-server="${s}" style="font-size:12px; font-weight:600; padding:5px 14px; border-radius:6px; background:rgba(255,255,255,0.08); color:#fff; text-decoration:none;">
                    ${s}
                </a>
                `).join('')}
            </div>
        </div>

        <div class="ps_-block ps_-block-dub">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-microphone mr-2 text-info" style="font-size:16px;"></i>
                <strong style="font-size:13px; color:#fff;">DUB (Hindi Featured):</strong>
            </div>
            <div class="ps__-list d-flex flex-wrap" style="gap:8px;">
                ${dubServers.map((s, idx) => `
                <a href="javascript:;" class="btn btn-sm btn-server ${s.includes('Hindi') && idx === 0 ? 'active' : ''}" data-server="${s}" style="font-size:12px; font-weight:700; padding:5px 14px; border-radius:6px; ${s.includes('Hindi') ? 'background:#ffbade; color:#111;' : 'background:rgba(255,255,255,0.08); color:#fff;'} text-decoration:none;">
                    ${s.includes('Hindi') ? '<i class="fas fa-play mr-1"></i> ' : ''}${s}
                </a>
                `).join('')}
            </div>
        </div>
    </div>
    `;
}

export function initServerEvents(onServerChange) {
    const serverButtons = document.querySelectorAll('.btn-server');
    serverButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const group = this.closest('.ps__-list');
            if (group) {
                group.querySelectorAll('.btn-server').forEach(b => {
                    b.classList.remove('active');
                    if (b.innerText.includes('Hindi')) {
                        b.style.background = 'rgba(255,255,255,0.08)';
                        b.style.color = '#fff';
                    }
                });
            }
            this.classList.add('active');
            this.style.background = '#ffbade';
            this.style.color = '#111';

            const serverName = this.getAttribute('data-server');
            if (onServerChange) onServerChange(serverName);
        });
    });
}
