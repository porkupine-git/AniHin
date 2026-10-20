// Anime Details & Discussion Components for Watch Page

export function renderAnimeDetails(anime) {
    return `
    <div class="anis-watch-detail p-4 mb-4" style="background:#201f31; border-radius:10px;">
        <div class="row">
            <div class="col-md-3 col-sm-4 text-center mb-3 mb-sm-0">
                <div class="film-poster" style="width:100%; border-radius:8px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.5);">
                    <img src="${anime.poster}" alt="${anime.title}" style="width:100%; height:auto; display:block;">
                </div>
            </div>
            <div class="col-md-9 col-sm-8">
                <h1 class="anime-title text-white mb-1" style="font-size:24px; font-weight:800;">${anime.title}</h1>
                <div class="text-muted mb-3" style="font-size:13px;">${anime.jname || anime.title}</div>

                <div class="d-flex align-items-center flex-wrap mb-3" style="gap:8px;">
                    <span class="badge badge-light py-1 px-2" style="font-size:11px; font-weight:700; background:#ffbade; color:#111;">${anime.quality || 'HD'}</span>
                    ${anime.subCount ? `<span class="badge py-1 px-2" style="font-size:11px; background:#b9ffc6; color:#111; font-weight:700;"><i class="fas fa-closed-captioning mr-1"></i>${anime.subCount}</span>` : ''}
                    ${anime.dubCount ? `<span class="badge py-1 px-2" style="font-size:11px; background:#b9e7ff; color:#111; font-weight:700;"><i class="fas fa-microphone mr-1"></i>${anime.dubCount}</span>` : ''}
                    <span class="text-muted" style="font-size:12px;">• ${anime.type || 'TV'} • ${anime.duration || '24m'}</span>
                </div>

                <p class="anime-synopsis text-light mb-3" style="font-size:13.5px; line-height:1.6; color:#ccc;">
                    ${anime.synopsis}
                </p>

                <div class="anime-meta-grid" style="font-size:12.5px; display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:8px;">
                    <div><span class="text-muted">Type:</span> <span class="text-white">${anime.type || 'TV'}</span></div>
                    <div><span class="text-muted">Status:</span> <span class="text-white">${anime.status || 'Finished Airing'}</span></div>
                    <div><span class="text-muted">Studios:</span> <span class="text-white">${anime.studio || 'Animation Studio'}</span></div>
                    <div><span class="text-muted">Score:</span> <span class="text-white" style="color:#ffbade !important;">★ ${anime.score || '8.5'}</span></div>
                    <div><span class="text-muted">Rating:</span> <span class="text-white">${anime.rating || 'PG-13'}</span></div>
                    <div><span class="text-muted">Premiered:</span> <span class="text-white">${anime.releaseDate || '2026'}</span></div>
                </div>

                <div class="anime-genres mt-3 d-flex flex-wrap" style="gap:6px;">
                    <span class="text-muted mr-1" style="font-size:12px; align-self:center;">Genres:</span>
                    ${(anime.genres || []).map(g => `
                    <a href="browse.html?genre=${encodeURIComponent(g)}" class="btn btn-sm" style="font-size:11px; padding:2px 8px; border-radius:12px; background:rgba(255,255,255,0.06); color:#ddd; text-decoration:none;">
                        ${g}
                    </a>
                    `).join('')}
                </div>
            </div>
        </div>
    </div>
    `;
}

export function renderDiscussion() {
    return `
    <div class="comments-section p-4" style="background:#201f31; border-radius:10px;">
        <h3 class="cat-heading mb-3" style="color:#ffbade; font-size:18px; font-weight:700;">
            <i class="fas fa-comments mr-2"></i>Community Discussion (Hindi Dub & Sub)
        </h3>

        <div class="comment-input-box mb-4">
            <textarea id="new-comment-input" class="form-control mb-2" rows="3" placeholder="Join the discussion... (Share your review on this episode!)" style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:8px; font-size:13.5px;"></textarea>
            <button id="post-comment-btn" class="btn btn-sm" style="background:#ffbade; color:#111; font-weight:700; border-radius:20px; padding:6px 22px;">
                <i class="fas fa-paper-plane mr-1"></i> Post Comment
            </button>
        </div>

        <div class="comments-list" id="comments-container">
            <div class="comment-item d-flex mb-3 pb-3" style="border-bottom:1px solid rgba(255,255,255,0.06);">
                <div class="avatar mr-3" style="width:42px; height:42px; min-width:42px; border-radius:50%; background:#ffbade; color:#111; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px;">R</div>
                <div class="content flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong style="color:#fff; font-size:14px;">Rohit Sharma <span class="badge badge-pill ml-2" style="background:#ffbade; color:#111; font-size:10px;">Hindi Fan</span></strong>
                        <span class="text-muted" style="font-size:11.5px;">15 mins ago</span>
                    </div>
                    <p class="mb-1 text-light" style="font-size:13.5px;">Bhai Hindi dubbing next level hai! Audio quality and voice acting ekdum mast hai.</p>
                    <div class="comment-actions text-muted" style="font-size:12px;">
                        <a href="javascript:;" class="text-muted mr-3"><i class="fas fa-thumbs-up mr-1"></i> 24</a>
                        <a href="javascript:;" class="text-muted"><i class="fas fa-reply mr-1"></i> Reply</a>
                    </div>
                </div>
            </div>

            <div class="comment-item d-flex mb-3 pb-3" style="border-bottom:1px solid rgba(255,255,255,0.06);">
                <div class="avatar mr-3" style="width:42px; height:42px; min-width:42px; border-radius:50%; background:#b9ffc6; color:#111; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px;">A</div>
                <div class="content flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong style="color:#fff; font-size:14px;">Aarav Sen</strong>
                        <span class="text-muted" style="font-size:11.5px;">1 hour ago</span>
                    </div>
                    <p class="mb-1 text-light" style="font-size:13.5px;">The animation and pacing in episode 1 is phenomenal. Can't wait for the next episode!</p>
                    <div class="comment-actions text-muted" style="font-size:12px;">
                        <a href="javascript:;" class="text-muted mr-3"><i class="fas fa-thumbs-up mr-1"></i> 18</a>
                        <a href="javascript:;" class="text-muted"><i class="fas fa-reply mr-1"></i> Reply</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    `;
}

export function initDiscussionEvents() {
    const postBtn = document.getElementById('post-comment-btn');
    const input = document.getElementById('new-comment-input');
    const container = document.getElementById('comments-container');

    if (postBtn && input && container) {
        postBtn.addEventListener('click', () => {
            const text = input.value.trim();
            if (!text) return;

            const newComment = document.createElement('div');
            newComment.className = 'comment-item d-flex mb-3 pb-3';
            newComment.style.borderBottom = '1px solid rgba(255,255,255,0.06)';
            newComment.innerHTML = `
                <div class="avatar mr-3" style="width:42px; height:42px; min-width:42px; border-radius:50%; background:#ffbade; color:#111; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px;">Y</div>
                <div class="content flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong style="color:#fff; font-size:14px;">You <span class="badge badge-pill ml-2" style="background:#ffbade; color:#111; font-size:10px;">Just Now</span></strong>
                        <span class="text-muted" style="font-size:11.5px;">Moments ago</span>
                    </div>
                    <p class="mb-1 text-light" style="font-size:13.5px;">${text}</p>
                    <div class="comment-actions text-muted" style="font-size:12px;">
                        <a href="javascript:;" class="text-muted mr-3"><i class="fas fa-thumbs-up mr-1"></i> 1</a>
                        <a href="javascript:;" class="text-muted"><i class="fas fa-reply mr-1"></i> Reply</a>
                    </div>
                </div>
            `;
            container.prepend(newComment);
            input.value = '';
        });
    }
}
