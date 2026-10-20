import re
import json

def clean_links(html):
    # Normalize links
    html = re.sub(r'href="https://hianime\.at/home"', 'href="index.html"', html)
    html = re.sub(r'href="/home"', 'href="index.html"', html)
    html = re.sub(r'href="https://hianime\.at/"', 'href="index.html"', html)
    html = re.sub(r'href="/"', 'href="index.html"', html)
    
    # Watch links
    def fix_watch(m):
        full = m.group(1)
        slug = full.rstrip('/').split('/')[-1]
        return f'href="watch.html?anime={slug}"'
    
    html = re.sub(r'href="https://hianime\.at/watch/([^"]+)"', fix_watch, html)
    html = re.sub(r'href="/watch/([^"]+)"', fix_watch, html)

    # Filter / Browse categories
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

    # Local images
    html = re.sub(r'https://hianime\.at/theme/images/', 'assets/images/', html)
    html = re.sub(r'/theme/images/', 'assets/images/', html)

    # Action in forms
    html = re.sub(r'action="https://hianime\.at/filter"', 'action="browse.html"', html)
    html = re.sub(r'action="/filter"', 'action="browse.html"', html)
    html = re.sub(r'action="https://hianime\.at/search"', 'action="browse.html"', html)
    html = re.sub(r'action="/search"', 'action="browse.html"', html)

    return html

# 1. Process Header & Sidebar
with open('tools/exact_header.html', 'r', encoding='utf-8') as f:
    header_raw = f.read()

with open('tools/exact_sidebar.html', 'r', encoding='utf-8') as f:
    sidebar_raw = f.read()

header_clean = clean_links(header_raw)
sidebar_clean = clean_links(sidebar_raw)

# Write to src/components/common/Header.js
header_js = f'''// Exact HiAnime 1:1 Navbar and Mobile Sidebar Drawer

import {{ animeApi }} from '../../api/animeApi.js';
import {{ debounce }} from '../../utils/helpers.js';

export function renderHeader() {{
    return `
    {sidebar_clean}
    {header_clean}
    `;
}}

export function initHeaderEvents() {{
    // 1. Mobile Sidebar Toggle
    const mobileMenuBtn = document.getElementById('mobile_menu');
    const sidebar = document.getElementById('sidebar_menu');
    const sidebarBg = document.getElementById('sidebar_menu_bg');
    const closeBtns = document.querySelectorAll('.toggle-sidebar, .sidebar-close-btn');

    const openMenu = () => {{
        if (sidebar) sidebar.classList.add('active');
        if (sidebarBg) {{
            sidebarBg.style.display = 'block';
            sidebarBg.classList.add('active');
        }}
        document.body.style.overflow = 'hidden';
    }};

    const closeMenu = () => {{
        if (sidebar) sidebar.classList.remove('active');
        if (sidebarBg) {{
            sidebarBg.style.display = 'none';
            sidebarBg.classList.remove('active');
        }}
        document.body.style.overflow = '';
    }};

    if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMenu);
    closeBtns.forEach(b => b.addEventListener('click', closeMenu));
    if (sidebarBg) sidebarBg.addEventListener('click', closeMenu);

    // 2. Mobile Search Toggle
    const mobileSearchBtn = document.getElementById('mobile_search');
    const searchBlock = document.getElementById('search');
    if (mobileSearchBtn && searchBlock) {{
        mobileSearchBtn.addEventListener('click', () => {{
            searchBlock.classList.toggle('active');
            const inp = searchBlock.querySelector('input.search-input');
            if (inp && searchBlock.classList.contains('active')) {{
                inp.focus();
            }}
        }});
    }}

    // 3. Search auto-suggest
    const searchInput = document.querySelector('.search-input');
    const suggestContainer = document.getElementById('search-suggest');

    if (searchInput && suggestContainer) {{
        searchInput.addEventListener('input', debounce((e) => {{
            const val = e.target.value.trim();
            if (val.length < 2) {{
                suggestContainer.style.display = 'none';
                suggestContainer.innerHTML = '';
                return;
            }}

            const results = animeApi.search(val);
            if (results.length === 0) {{
                suggestContainer.innerHTML = `<div class="p-3 text-muted text-center" style="background:#252438; border-radius:6px;">No anime found for "${{val}}"</div>`;
            }} else {{
                suggestContainer.innerHTML = `
                <div class="search-results-list" style="background:#252438; border:1px solid rgba(255,255,255,0.1); border-radius:8px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.85);">
                    ${{results.slice(0, 5).map(anime => `
                    <a href="watch.html?anime=${{anime.slug}}" class="d-flex align-items-center p-2 text-decoration-none" style="border-bottom:1px solid rgba(255,255,255,0.05); color:#fff;">
                        <img src="${{anime.poster}}" style="width:38px; height:50px; object-fit:cover; border-radius:4px; margin-right:12px;">
                        <div>
                            <div style="font-weight:600; font-size:13px; color:#fff;">${{anime.title}}</div>
                            <div style="font-size:11px; color:#aaa; margin-top:2px;">
                                ${{anime.type || 'TV'}} • <span style="color:#ffbade;">★ ${{anime.score || '8.0'}}</span>
                                ${{anime.dubCount ? '<span class="badge ml-1" style="background:#b9e7ff; color:#111; font-size:9px;">Hindi</span>' : ''}}
                            </div>
                        </div>
                    </a>
                    `).join('')}}
                    <a href="browse.html?keyword=${{encodeURIComponent(val)}}" class="d-block p-2 text-center text-decoration-none" style="background:rgba(255,186,222,0.1); color:#ffbade; font-weight:600; font-size:12px;">
                        View all results (${{results.length}}) <i class="fas fa-angle-right ml-1"></i>
                    </a>
                </div>
                `;
            }}
            suggestContainer.style.display = 'block';
        }}, 200));

        document.addEventListener('click', (e) => {{
            if (!searchInput.contains(e.target) && !suggestContainer.contains(e.target)) {{
                suggestContainer.style.display = 'none';
            }}
        }});
    }}
}}
'''

with open('src/components/common/Header.js', 'w', encoding='utf-8') as f:
    f.write(header_js)
print("Updated src/components/common/Header.js with 100% exact HiAnime Navbar!")

# 2. Process Watch Main
with open('tools/exact_watch_main.html', 'r', encoding='utf-8') as f:
    watch_main_raw = f.read()

watch_clean = clean_links(watch_main_raw)

# Embed player & servers in watch_clean
watch_player_iframe = '''
<div class="video-player-container" style="width:100%; aspect-ratio:16/9; background:#000; border-radius:8px; overflow:hidden;">
    <iframe id="anime-video-player" src="https://www.youtube-nocookie.com/embed/fJ9rUzIMcZQ?autoplay=0&rel=0&modestbranding=1" 
        style="width:100%; height:100%; border:none;" 
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
    </iframe>
</div>
'''

watch_clean = watch_clean.replace('<div class="loading-relative loading-box" id="embed-loading">', watch_player_iframe + '<div class="loading-relative loading-box d-none" id="embed-loading">')

servers_html = '''
<div class="ps_-block ps_-block-sub">
    <div class="ps__-title"><i class="fas fa-closed-captioning mr-1 text-success"></i> SUB:</div>
    <div class="ps__-list">
        <div class="item"><a class="btn btn-server active" href="javascript:;" data-server="hd-1">HD-1</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="hd-2">HD-2</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="megacloud">MegaCloud</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="streamsb">StreamSB</a></div>
    </div>
</div>
<div class="ps_-block ps_-block-dub" style="border-top: 1px solid rgba(255,255,255,0.06); padding-top: 12px; margin-top: 8px;">
    <div class="ps__-title"><i class="fas fa-microphone mr-1 text-info"></i> DUB (Hindi Featured):</div>
    <div class="ps__-list">
        <div class="item"><a class="btn btn-server active" href="javascript:;" data-server="hindi-1" style="background:#ffbade; color:#111; font-weight:700;"><i class="fas fa-play mr-1"></i> Hindi HD-1</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="hindi-2">Hindi HD-2</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="eng-dub">English Dub</a></div>
    </div>
</div>
'''
watch_clean = watch_clean.replace('<div id="servers-content"></div>', f'<div id="servers-content">{servers_html}</div>')

# Episodes drawer
ep_items = ""
for ep in range(1, 25):
    active_cls = " active" if ep == 1 else ""
    ep_items += f'''
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item{active_cls}" href="javascript:;" data-number="{ep}" title="Episode {ep}" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">{ep}</a>
    </div>
    '''

episodes_html = f'''
<div class="episodes-list" style="background: #252438; border-radius: 8px; padding: 18px; margin-top: 20px;">
    <div class="episodes-list-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="cat-heading mb-0" style="color:#ffbade; font-size:16px; font-weight:600;">List of Episodes: (Playing: Ep <span id="current-ep-num">1</span>)</h4>
        </div>
        <div class="ep-search" style="max-width: 180px;">
            <input type="text" class="form-control form-control-sm" placeholder="Number of Ep" id="search-ep" style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:20px; padding: 4px 14px;">
        </div>
    </div>
    <div class="episodes-ul-wrapper" style="max-height: 250px; overflow-y: auto;">
        <div class="episodes-ul">
            {ep_items}
        </div>
    </div>
</div>
'''
watch_clean = watch_clean.replace('<div id="episodes-content">\n                        </div>', f'<div id="episodes-content">{episodes_html}</div>')

# Discussion
comments_html = '''
<div class="comments-section mt-4" style="background: #252438; border-radius: 8px; padding: 22px;">
    <h3 class="cat-heading mb-3" style="color:#ffbade; font-size:18px;"><i class="fas fa-comments mr-2"></i>Community Discussion (Hindi & Sub)</h3>
    <div class="comment-input-box mb-4">
        <textarea id="comment-text-box" class="form-control mb-2" rows="3" placeholder="Join the discussion... (Share your thoughts on this episode!)" style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:8px;"></textarea>
        <button id="post-comment-btn" class="btn btn-sm" style="background:#ffbade; color:#111; font-weight:600; border-radius:20px; padding:6px 20px;"><i class="fas fa-paper-plane mr-1"></i> Post Comment</button>
    </div>
    <div class="comments-list" id="comments-list-box">
        <div class="comment-item d-flex mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
            <div class="avatar mr-3" style="width:42px; height:42px; border-radius:50%; background:#ffbade; color:#111; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">R</div>
            <div class="content flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong style="color:#fff; font-size:14px;">Rohit Sharma <span class="badge badge-pill badge-primary ml-2" style="background:#ffbade; color:#111; font-size:10px;">Hindi Fan</span></strong>
                    <span class="text-muted" style="font-size:12px;">15 mins ago</span>
                </div>
                <p class="mb-1 text-light" style="font-size:13.5px;">Bhai Hindi dubbing next level hai! Audio quality and voice acting ekdum mast hai.</p>
                <div class="comment-actions text-muted" style="font-size:12px;">
                    <a href="javascript:;" class="text-muted mr-3"><i class="fas fa-thumbs-up mr-1"></i> 24</a>
                    <a href="javascript:;" class="text-muted"><i class="fas fa-reply mr-1"></i> Reply</a>
                </div>
            </div>
        </div>
    </div>
</div>
'''
watch_clean = watch_clean.replace('<div id="comment-widget"></div>', f'<div id="comment-widget">{comments_html}</div>')

# Write to src/pages/watchPage.js
# Escape backticks in watch_clean
watch_escaped = watch_clean.replace('`', '\\`').replace('${', '\\${')
watch_js = f'''// Exact 1:1 HiAnime Watch Page Controller

import {{ animeApi }} from '../api/animeApi.js';
import {{ getQueryParams }} from '../utils/helpers.js';

export function renderWatchPage() {{
    const mainWrapper = document.getElementById('main-wrapper');
    if (!mainWrapper) return;

    const params = getQueryParams();
    const anime = animeApi.getAnimeBySlug(params.anime || 'black-clover-season-2');
    const currentEp = parseInt(params.ep, 10) || 1;

    document.title = `Watch ${{anime.title}} Episode ${{currentEp}} in HD | HiAnime`;

    mainWrapper.outerHTML = `{watch_escaped}`;

    setTimeout(() => {{
        initWatchInteractivity(anime, currentEp);
    }}, 50);
}}

function initWatchInteractivity(anime, currentEp) {{
    // 1. Dynamic Anime Info update
    const titleEl = document.querySelector('.anisc-detail .film-name');
    if (titleEl) titleEl.innerText = anime.title;

    const bannerCover = document.querySelector('.anis-cover');
    if (bannerCover && anime.banner) {{
        bannerCover.style.backgroundImage = `url(${{anime.banner}})`;
    }}

    const posterImg = document.querySelector('.anisc-poster .film-poster img');
    if (posterImg && anime.poster) {{
        posterImg.src = anime.poster;
    }}

    const descEl = document.querySelector('.item-description');
    if (descEl && anime.synopsis) {{
        descEl.innerText = anime.synopsis;
    }}

    // 2. Server selection
    document.querySelectorAll('.btn-server').forEach(btn => {{
        btn.addEventListener('click', function() {{
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

    // 3. Episode selection
    const epBtns = document.querySelectorAll('.ep-item');
    const epDisplay = document.getElementById('current-ep-num');
    epBtns.forEach(btn => {{
        btn.addEventListener('click', function() {{
            epBtns.forEach(b => {{
                b.classList.remove('active');
                b.style.background = 'rgba(255,255,255,0.07)';
                b.style.color = '#fff';
            }});
            this.classList.add('active');
            this.style.background = '#ffbade';
            this.style.color = '#111';

            const ep = this.getAttribute('data-number');
            if (epDisplay) epDisplay.innerText = ep;

            const url = new URL(window.location);
            url.searchParams.set('ep', ep);
            window.history.replaceState({{}}, '', url);
        }});
    }});

    // 4. Episode search input
    const epSearch = document.getElementById('search-ep');
    if (epSearch) {{
        epSearch.addEventListener('input', function() {{
            const val = this.value.trim().toLowerCase();
            epBtns.forEach(btn => {{
                const num = btn.getAttribute('data-number');
                const parent = btn.closest('.item');
                if (!val || num.includes(val)) {{
                    if (parent) parent.style.display = 'inline-block';
                    else btn.style.display = 'inline-block';
                }} else {{
                    if (parent) parent.style.display = 'none';
                    else btn.style.display = 'none';
                }}
            }});
        }});
    }}

    // 5. Discussion comment posting
    const postBtn = document.getElementById('post-comment-btn');
    const commentBox = document.getElementById('comment-text-box');
    const commentsList = document.getElementById('comments-list-box');
    if (postBtn && commentBox && commentsList) {{
        postBtn.addEventListener('click', () => {{
            const text = commentBox.value.trim();
            if (!text) return;
            const newDiv = document.createElement('div');
            newDiv.className = 'comment-item d-flex mb-3 pb-3';
            newDiv.style.borderBottom = '1px solid rgba(255,255,255,0.06)';
            newDiv.innerHTML = `
                <div class="avatar mr-3" style="width:42px; height:42px; border-radius:50%; background:#ffbade; color:#111; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">Y</div>
                <div class="content flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong style="color:#fff; font-size:14px;">You <span class="badge badge-pill badge-primary ml-2" style="background:#ffbade; color:#111; font-size:10px;">Just Now</span></strong>
                        <span class="text-muted" style="font-size:12px;">Moments ago</span>
                    </div>
                    <p class="mb-1 text-light" style="font-size:13.5px;">${{text}}</p>
                </div>
            `;
            commentsList.prepend(newDiv);
            commentBox.value = '';
        }});
    }}
}}
'''

with open('src/pages/watchPage.js', 'w', encoding='utf-8') as f:
    f.write(watch_js)
print("Updated src/pages/watchPage.js with 100% exact HiAnime Watch page!")

# 3. Process Filter / Browse Main
with open('tools/exact_filter_main.html', 'r', encoding='utf-8') as f:
    filter_main_raw = f.read()

filter_clean = clean_links(filter_main_raw)

# Write to src/pages/browsePage.js
filter_escaped = filter_clean.replace('`', '\\`').replace('${', '\\${')
browse_js = f'''// Exact 1:1 HiAnime Filter / Browse Page Controller

import {{ animeApi }} from '../api/animeApi.js';
import {{ getQueryParams }} from '../utils/helpers.js';

export function renderBrowsePage() {{
    const mainWrapper = document.getElementById('main-wrapper');
    if (!mainWrapper) return;

    document.title = 'Filter & Browse Anime - HiAnime Hindi';

    mainWrapper.outerHTML = `{filter_escaped}`;

    setTimeout(() => {{
        initFilterInteractivity();
    }}, 50);
}}

function initFilterInteractivity() {{
    // 1. Advanced search toggle button
    const toggleBtn = document.getElementById('toggle-filter');
    const cateFilter = document.getElementById('cate-filter');
    if (toggleBtn && cateFilter) {{
        toggleBtn.addEventListener('click', (e) => {{
            e.preventDefault();
            cateFilter.classList.toggle('d-none');
        }});
    }}

    // 2. Active genre checkbox visual toggle
    document.querySelectorAll('.f-genre-item input[type="checkbox"]').forEach(chk => {{
        chk.addEventListener('change', function() {{
            const parent = this.closest('.f-genre-item');
            if (parent) {{
                if (this.checked) parent.classList.add('active');
                else parent.classList.remove('active');
            }}
        }});
    }});
}}
'''

with open('src/pages/browsePage.js', 'w', encoding='utf-8') as f:
    f.write(browse_js)
print("Updated src/pages/browsePage.js with 100% exact HiAnime Filter & Browse page!")
