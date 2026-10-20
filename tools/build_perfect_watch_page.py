import re

with open('tools/exact_watch_main.html', 'r', encoding='utf-8') as f:
    raw = f.read()

def clean_links(html):
    html = re.sub(r'href="https://hianime\.at/home"', 'href="index.html"', html)
    html = re.sub(r'href="/home"', 'href="index.html"', html)
    html = re.sub(r'href="https://hianime\.at/"', 'href="index.html"', html)
    html = re.sub(r'href="/"', 'href="index.html"', html)
    
    def fix_watch(m):
        full = m.group(1)
        slug = full.rstrip('/').split('/')[-1]
        return f'href="watch.html?anime={slug}"'
    
    html = re.sub(r'href="https://hianime\.at/watch/([^"]+)"', fix_watch, html)
    html = re.sub(r'href="/watch/([^"]+)"', fix_watch, html)

    cats = [
        'recently-updated', 'recently-added', 'top-airing', 'most-popular', 'most-favorite', 
        'completed', 'filter', 'subbed-anime', 'dubbed-anime', 'movie', 'tv', 'ova', 'ona', 'special',
        'az-list'
    ]
    for c in cats:
        html = re.sub(rf'href="https://hianime\.at/{c}"', 'href="browse.html"', html)
        html = re.sub(rf'href="/{c}"', 'href="browse.html"', html)
    
    html = re.sub(r'href="https://hianime\.at/genre/([^"]+)"', r'href="browse.html?genre=\1"', html)
    html = re.sub(r'href="/genre/([^"]+)"', r'href="browse.html?genre=\1"', html)

    html = re.sub(r'https://hianime\.at/theme/images/', 'assets/images/', html)
    html = re.sub(r'/theme/images/', 'assets/images/', html)
    return html

watch_clean = clean_links(raw)

# 1. Clean player-frame: Insert authentic HTML5 / embed video player
player_markup = '''
<div class="player-frame position-relative" style="background:#000; overflow:hidden;">
    <video id="player-video" controls autoplay playsinline style="position:absolute; inset:0; width:100%; height:100%; object-fit:contain; background:#000;">
        <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4" type="video/mp4">
    </video>
</div>
'''
watch_clean = re.sub(r'<div class="player-frame">.*?</div>\s*</div>\s*<div class="player-controls">', 
                    player_markup + '</div>\n<div class="player-controls">', watch_clean, flags=re.DOTALL)

# 2. Populate servers-content
servers_markup = '''
<div id="servers-content">
    <div class="ps_-block ps_-block-sub">
        <div class="ps__-title"><i class="fas fa-closed-captioning mr-1 text-success"></i> SUB:</div>
        <div class="ps__-list">
            <div class="item"><a class="btn btn-server active" href="javascript:;" data-server="HD-1">HD-1</a></div>
            <div class="item"><a class="btn btn-server" href="javascript:;" data-server="HD-2">HD-2</a></div>
            <div class="item"><a class="btn btn-server" href="javascript:;" data-server="MegaCloud">MegaCloud</a></div>
            <div class="item"><a class="btn btn-server" href="javascript:;" data-server="StreamSB">StreamSB</a></div>
        </div>
    </div>
    <div class="ps_-block ps_-block-dub" style="border-top: 1px solid rgba(255,255,255,0.06); padding-top: 8px; margin-top: 6px;">
        <div class="ps__-title"><i class="fas fa-microphone mr-1 text-info"></i> DUB (Hindi):</div>
        <div class="ps__-list">
            <div class="item"><a class="btn btn-server active" href="javascript:;" data-server="Hindi HD-1" style="background:#ffbade; color:#111; font-weight:700;"><i class="fas fa-play mr-1"></i> Hindi HD-1</a></div>
            <div class="item"><a class="btn btn-server" href="javascript:;" data-server="Hindi HD-2">Hindi HD-2</a></div>
            <div class="item"><a class="btn btn-server" href="javascript:;" data-server="English Dub">English Dub</a></div>
        </div>
    </div>
</div>
'''
watch_clean = watch_clean.replace('<div id="servers-content"></div>', servers_markup)

# 3. Populate authentic HiAnime seasons-block inside #episodes-content
def generate_episodes_markup(count=24):
    items = []
    for ep in range(1, count + 1):
        num_str = String = str(ep).zfill(2)
        act = " active" if ep == 1 else ""
        items.append(f'''
        <a href="javascript:;" class="ssl-item ep-item{act}" data-number="{ep}" title="Episode {ep}">
            <div class="ssli-order" style="min-width:32px; font-weight:700; color:#fff; text-align:center;">{num_str}</div>
            <div class="ssli-detail">
                <div class="ep-name dynamic-name" title="Episode {ep}" style="font-size:12.5px; color:#ddd;">Episode {ep}</div>
            </div>
            <div class="clearfix"></div>
        </a>
        ''')
    
    return f'''
    <div id="episodes-content">
        <div class="seasons-block position-relative" style="height:100%; min-height:480px; background:#191826;">
            <div class="ss-choice" style="background:#11101a; border-bottom:1px solid rgba(255,255,255,0.08); padding:10px 14px; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:700; color:#ffbade; font-size:13px;">
                    <i class="fas fa-list mr-1"></i> Episodes (<span id="ep-count-badge">{count}</span>)
                </div>
                <div style="max-width:90px;">
                    <input type="text" id="search-ep" placeholder="Ep #" style="width:100%; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.1); border-radius:12px; color:#fff; font-size:11px; padding:2px 8px; outline:none;">
                </div>
            </div>
            <div class="ss-list" id="episodes-scroll-list" style="position:absolute; top:46px; left:0; right:0; bottom:0; overflow-y:auto; padding:4px 0;">
                {"".join(items)}
            </div>
        </div>
    </div>
    '''

watch_clean = re.sub(r'<div id="episodes-content">\s*</div>', generate_episodes_markup(24), watch_clean)

# 4. Comments Section
comments_markup = '''
<div id="comment-widget">
    <div class="comments-section mt-4" style="background: #201f31; border-radius: 8px; padding: 22px;">
        <h3 class="cat-heading mb-3" style="color:#ffbade; font-size:18px;"><i class="fas fa-comments mr-2"></i>Community Discussion (Hindi Dub & Sub)</h3>
        <div class="comment-input-box mb-4">
            <textarea id="comment-text-box" class="form-control mb-2" rows="3" placeholder="Join the discussion... (Share your thoughts on this episode!)" style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:8px;"></textarea>
            <button id="post-comment-btn" class="btn btn-sm" style="background:#ffbade; color:#111; font-weight:700; border-radius:20px; padding:6px 20px;"><i class="fas fa-paper-plane mr-1"></i> Post Comment</button>
        </div>
        <div class="comments-list" id="comments-list-box">
            <div class="comment-item d-flex mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <div class="avatar mr-3" style="width:40px; height:40px; border-radius:50%; background:#ffbade; color:#111; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">R</div>
                <div class="content flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong style="color:#fff; font-size:14px;">Rohit Sharma <span class="badge badge-pill badge-primary ml-2" style="background:#ffbade; color:#111; font-size:10px;">Hindi Fan</span></strong>
                        <span class="text-muted" style="font-size:12px;">15 mins ago</span>
                    </div>
                    <p class="mb-1 text-light" style="font-size:13.5px;">Bhai Hindi dubbing next level hai! Audio quality and voice acting ekdum mast hai.</p>
                </div>
            </div>
            <div class="comment-item d-flex mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <div class="avatar mr-3" style="width:40px; height:40px; border-radius:50%; background:#b9ffc6; color:#111; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">A</div>
                <div class="content flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong style="color:#fff; font-size:14px;">Aarav Sen</strong>
                        <span class="text-muted" style="font-size:12px;">1 hour ago</span>
                    </div>
                    <p class="mb-1 text-light" style="font-size:13.5px;">Animation and action scenes are looking so clean! Can't wait for the next episode.</p>
                </div>
            </div>
        </div>
    </div>
</div>
'''
watch_clean = watch_clean.replace('<div id="comment-widget"></div>', comments_markup)

# Escape backticks and ${}
escaped_watch = watch_clean.replace('`', '\\`').replace('${', '\\${')

# Generate src/pages/watchPage.js
watch_page_code = f'''// Authentic 1:1 HiAnime Watch Page Controller (Pixel-Perfect Stage Layout)

import {{ animeApi }} from '../api/animeApi.js';
import {{ getQueryParams }} from '../utils/helpers.js';

export function renderWatchPage() {{
    const mainWrapper = document.getElementById('main-wrapper');
    if (!mainWrapper) return;

    const params = getQueryParams();
    const anime = animeApi.getAnimeBySlug(params.anime || 'black-clover-season-2');
    const currentEp = parseInt(params.ep, 10) || 1;

    document.title = `Watch ${{anime.title}} Episode ${{currentEp}} in HD | HiAnime`;

    // Render Authentic HiAnime Watch DOM
    mainWrapper.outerHTML = `{escaped_watch}`;

    // Dynamically Hydrate with Active Anime's Data
    setTimeout(() => {{
        hydrateWatchData(anime, currentEp);
    }}, 40);
}}

function hydrateWatchData(anime, currentEp) {{
    // 1. Breadcrumbs
    const bcTitle = document.querySelector('.anis-watch-wrap .breadcrumb-item.active');
    if (bcTitle) {{
        bcTitle.innerHTML = `Watching <span style="color:#ffbade; font-weight:600;">${{anime.title}}</span> (Ep <span id="current-ep-badge">${{currentEp}}</span>)`;
    }}

    // 2. Cover Banner
    const coverBanner = document.querySelector('.anis-cover');
    if (coverBanner && anime.banner) {{
        coverBanner.style.backgroundImage = `url(${{anime.banner}})`;
    }}

    // 3. Poster Image
    const poster = document.querySelector('.anisc-poster .film-poster img');
    if (poster && anime.poster) {{
        poster.src = anime.poster;
        poster.alt = anime.title;
    }}

    // 4. Video Poster
    const video = document.getElementById('player-video');
    if (video && anime.banner) {{
        video.poster = anime.banner;
    }}

    // 5. Film Detail Titles & Synopsis
    const filmNameLink = document.querySelector('.anisc-detail .film-name a');
    if (filmNameLink) {{
        filmNameLink.innerText = anime.title;
        filmNameLink.href = `watch.html?anime=${{anime.slug}}`;
    }}

    const descText = document.querySelector('.film-description .text');
    if (descText && anime.synopsis) {{
        descText.innerText = anime.synopsis;
    }}

    // 6. Sub / Dub Badges in Detail card
    const tickSub = document.querySelector('.anisc-detail .tick-item.tick-sub');
    if (tickSub && anime.subCount) tickSub.innerHTML = `<i class="fas fa-closed-captioning mr-1"></i>${{anime.subCount}}`;
    
    const tickDub = document.querySelector('.anisc-detail .tick-item.tick-dub');
    if (tickDub && anime.dubCount) tickDub.innerHTML = `<i class="fas fa-microphone mr-1"></i>${{anime.dubCount}}`;

    // 7. Dynamic Episode List Generation for the anime
    const totalEps = anime.totalEpisodes || (anime.subCount || 24);
    const epCountBadge = document.getElementById('ep-count-badge');
    if (epCountBadge) epCountBadge.innerText = totalEps;

    const listContainer = document.getElementById('episodes-scroll-list');
    if (listContainer) {{
        const items = [];
        for (let i = 1; i <= totalEps; i++) {{
            const numStr = String(i).padStart(2, '0');
            const isActive = i === currentEp ? ' active' : '';
            items.push(`
                <a href="javascript:;" class="ssl-item ep-item${{isActive}}" data-number="${{i}}" title="Episode ${{i}}">
                    <div class="ssli-order" style="min-width:32px; font-weight:700; color:#fff; text-align:center;">${{numStr}}</div>
                    <div class="ssli-detail">
                        <div class="ep-name dynamic-name" title="Episode ${{i}}" style="font-size:12.5px; color:#ddd;">Episode ${{i}}</div>
                    </div>
                    <div class="clearfix"></div>
                </a>
            `);
        }}
        listContainer.innerHTML = items.join('');
    }}

    // 8. Bind Episode Clicking & Switching
    const epButtons = document.querySelectorAll('.ssl-item.ep-item');
    const epBadge = document.getElementById('current-ep-badge');
    epButtons.forEach(btn => {{
        btn.addEventListener('click', function(e) {{
            e.preventDefault();
            epButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const epNum = this.getAttribute('data-number');
            if (epBadge) epBadge.innerText = epNum;

            const url = new URL(window.location);
            url.searchParams.set('ep', epNum);
            window.history.replaceState({{}}, '', url);

            // Restart video
            if (video) {{
                video.currentTime = 0;
                video.play().catch(() => {{}});
            }}
        }});
    }});

    // 9. Episode Filter Search
    const searchEp = document.getElementById('search-ep');
    if (searchEp) {{
        searchEp.addEventListener('input', function() {{
            const val = this.value.trim().toLowerCase();
            document.querySelectorAll('.ssl-item.ep-item').forEach(item => {{
                const num = item.getAttribute('data-number');
                if (!val || num.includes(val)) {{
                    item.style.display = 'block';
                }} else {{
                    item.style.display = 'none';
                }}
            }});
        }});
    }}

    // 10. Server Switching
    document.querySelectorAll('.btn-server').forEach(btn => {{
        btn.addEventListener('click', function(e) {{
            e.preventDefault();
            const group = this.closest('.ps__-list');
            if (group) {{
                group.querySelectorAll('.btn-server').forEach(b => {{
                    b.classList.remove('active');
                    if (b.innerText.includes('Hindi')) {{
                        b.style.background = 'rgba(255,255,255,0.08)';
                        b.style.color = '#fff';
                    }}
                }});
            }}
            this.classList.add('active');
            this.style.background = '#ffbade';
            this.style.color = '#111';
        }});
    }});

    // 11. Light toggle (theater mode)
    const lightBtn = document.getElementById('turn-off-light');
    if (lightBtn) {{
        lightBtn.addEventListener('click', () => {{
            document.body.classList.toggle('theater-mode');
        }});
    }}

    // 12. Fullscreen / Resize toggle
    const resizeBtn = document.getElementById('media-resize');
    const playerFrame = document.querySelector('.player-frame');
    if (resizeBtn && playerFrame) {{
        resizeBtn.addEventListener('click', () => {{
            if (!document.fullscreenElement) {{
                playerFrame.requestFullscreen().catch(() => {{}});
            }} else {{
                document.exitFullscreen().catch(() => {{}});
            }}
        }});
    }}

    // 13. Discussion Comment posting
    const postBtn = document.getElementById('post-comment-btn');
    const commentBox = document.getElementById('comment-text-box');
    const commentList = document.getElementById('comments-list-box');
    if (postBtn && commentBox && commentList) {{
        postBtn.addEventListener('click', () => {{
            const txt = commentBox.value.trim();
            if (!txt) return;

            const div = document.createElement('div');
            div.className = 'comment-item d-flex mb-3 pb-3';
            div.style.borderBottom = '1px solid rgba(255,255,255,0.06)';
            div.innerHTML = `
                <div class="avatar mr-3" style="width:40px; height:40px; border-radius:50%; background:#ffbade; color:#111; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">Y</div>
                <div class="content flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong style="color:#fff; font-size:14px;">You <span class="badge badge-pill badge-primary ml-2" style="background:#ffbade; color:#111; font-size:10px;">Just Now</span></strong>
                        <span class="text-muted" style="font-size:12px;">Moments ago</span>
                    </div>
                    <p class="mb-1 text-light" style="font-size:13.5px;">${{txt}}</p>
                </div>
            `;
            commentList.prepend(div);
            commentBox.value = '';
        }});
    }}
}}
'''

with open('src/pages/watchPage.js', 'w', encoding='utf-8') as f:
    f.write(watch_page_code)
print("Updated src/pages/watchPage.js with authentic 1:1 HiAnime Watch Page Layout!")
