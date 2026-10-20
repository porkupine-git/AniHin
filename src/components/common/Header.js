// Upgraded Responsive Header Component with Sliding Mobile Drawer & Search

import { animeApi } from '../../api/animeApi.js';
import { debounce } from '../../utils/helpers.js';

export function renderHeader() {
    return `
    <!-- Sliding Mobile Drawer -->
    <div id="sidebar_menu_bg"></div>
    <div id="sidebar_menu">
        <button class="sidebar-close-btn" id="close-sidebar-btn">
            <i class="fas fa-times"></i> Close menu
        </button>

        <ul class="sidebar-nav-list">
            <li><a href="index.html"><i class="fas fa-home text-primary"></i> Home</a></li>
            <li><a href="browse.html?filter=dub"><i class="fas fa-microphone text-info"></i> Hindi Dubbed Anime</a></li>
            <li><a href="browse.html?type=tv"><i class="fas fa-tv text-warning"></i> TV Series</a></li>
            <li><a href="browse.html?type=movie"><i class="fas fa-film text-danger"></i> Movies</a></li>
            <li><a href="browse.html?sort=popular"><i class="fas fa-fire text-danger"></i> Most Popular</a></li>
            <li><a href="browse.html?status=currently"><i class="fas fa-signal text-success"></i> Top Airing</a></li>
            <li><a href="browse.html"><i class="fas fa-filter text-primary"></i> Filter & Browse</a></li>
            <li><a href="watch.html"><i class="fas fa-broadcast-tower text-danger"></i> Watch2gether</a></li>
            <li><a href="watch.html?anime=random"><i class="fas fa-random text-warning"></i> Random Anime</a></li>
        </ul>

        <div class="sidebar-community pt-3" style="border-top:1px solid rgba(255,255,255,0.08);">
            <div class="text-muted mb-2" style="font-size:12px; font-weight:600;">COMMUNITY</div>
            <div class="d-flex" style="gap:10px;">
                <a href="https://discord.com" target="_blank" class="btn btn-sm btn-dark" style="background:#5865F2; color:#fff; border-radius:6px;"><i class="fab fa-discord"></i> Discord</a>
                <a href="https://t.me" target="_blank" class="btn btn-sm btn-dark" style="background:#0088cc; color:#fff; border-radius:6px;"><i class="fab fa-telegram"></i> Telegram</a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header id="header">
        <div class="header-container">
            <div id="header_left">
                <div id="sidebar_menu_toggle" style="font-size:20px; color:#fff; cursor:pointer; padding:6px 10px 6px 0;">
                    <i class="fas fa-bars"></i>
                </div>
                <a href="index.html" id="logo">
                    <img src="assets/images/logo.png" alt="HiAnime">
                </a>
                
                <div class="search-content d-none d-lg-block">
                    <form action="browse.html" method="get" class="position-relative">
                        <input type="text" class="search-input" name="keyword" placeholder="Search anime..." autocomplete="off">
                        <button class="search-icon" type="submit"><i class="fas fa-search"></i></button>
                        <div id="search-suggest" class="search-suggest" style="display:none;"></div>
                    </form>
                </div>
                
                <div class="header-social-cluster d-none d-xl-flex align-items-center ml-4">
                    <span class="text-muted mr-2" style="font-size:12px; font-weight:600;">Join now</span>
                    <a href="https://discord.com" target="_blank" class="mr-2 text-primary" style="font-size:16px;"><i class="fab fa-discord"></i></a>
                    <a href="https://t.me" target="_blank" class="mr-2 text-info" style="font-size:16px;"><i class="fab fa-telegram"></i></a>
                    <a href="https://reddit.com" target="_blank" class="mr-2 text-danger" style="font-size:16px;"><i class="fab fa-reddit"></i></a>
                    <a href="https://twitter.com" target="_blank" class="text-info" style="font-size:16px;"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <div id="header_center" class="d-none d-lg-flex">
                <a href="watch.html" class="nav-item-btn"><i class="fas fa-broadcast-tower mr-1 text-danger"></i> Watch2gether</a>
                <a href="watch.html?anime=random" class="nav-item-btn"><i class="fas fa-random mr-1 text-warning"></i> Random</a>
                <div class="nav-item-btn anime-name-toggle">
                    <span class="badge py-1 px-2" style="font-size:10.5px; border-radius:12px; background:rgba(255,255,255,0.15); color:#fff;">EN | JP</span>
                    <span class="ml-1" style="font-size:12px;">Anime Name</span>
                </div>
                <a href="browse.html" class="nav-item-btn"><i class="fas fa-rss mr-1 text-info"></i> News</a>
                <a href="browse.html" class="nav-item-btn"><i class="fas fa-comments mr-1 text-success"></i> Community</a>
            </div>

            <div id="header_right">
                <div id="mobile-search-toggle" class="btn text-light d-lg-none" style="font-size:18px; cursor:pointer; padding:6px 8px;">
                    <i class="fas fa-search"></i>
                </div>
                <a href="browse.html" class="btn-filter">
                    <i class="fas fa-filter mr-1"></i> Filter
                </a>
                <a href="javascript:;" class="btn-login">Login</a>
            </div>
        </div>
    </header>

    <!-- Mobile Search Bar Dropdown -->
    <div id="mobile-search-bar">
        <form action="browse.html" method="get" class="position-relative">
            <input type="text" id="mobile-search-input" class="form-control" name="keyword" placeholder="Search anime..." autocomplete="off" style="background:#252438; border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:20px; padding:6px 45px 6px 18px; font-size:13.5px;">
            <button type="submit" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:#ffbade; font-size:16px;">
                <i class="fas fa-search"></i>
            </button>
            <div id="mobile-search-suggest" class="position-absolute" style="top:44px; left:0; right:0; z-index:1001; display:none;"></div>
        </form>
    </div>
    `;
}

export function initHeaderEvents() {
    // 1. Mobile Drawer Toggle
    const sidebar = document.getElementById('sidebar_menu');
    const sidebarBg = document.getElementById('sidebar_menu_bg');
    const toggleBtn = document.getElementById('sidebar_menu_toggle');
    const closeBtn = document.getElementById('close-sidebar-btn');

    const openDrawer = () => {
        if (sidebar) sidebar.classList.add('active');
        if (sidebarBg) sidebarBg.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    const closeDrawer = () => {
        if (sidebar) sidebar.classList.remove('active');
        if (sidebarBg) sidebarBg.classList.remove('active');
        document.body.style.overflow = '';
    };

    if (toggleBtn) toggleBtn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (sidebarBg) sidebarBg.addEventListener('click', closeDrawer);

    // 2. Mobile Search Bar Toggle
    const mobileSearchToggle = document.getElementById('mobile-search-toggle');
    const mobileSearchBar = document.getElementById('mobile-search-bar');
    if (mobileSearchToggle && mobileSearchBar) {
        mobileSearchToggle.addEventListener('click', () => {
            mobileSearchBar.classList.toggle('active');
            const inp = mobileSearchBar.querySelector('input');
            if (inp && mobileSearchBar.classList.contains('active')) {
                inp.focus();
            }
        });
    }

    // 3. Desktop & Mobile Live Search Suggestions
    const bindSearch = (inputSelector, suggestSelector) => {
        const input = document.querySelector(inputSelector);
        const suggest = document.querySelector(suggestSelector);
        if (!input || !suggest) return;

        const handleSearch = debounce((e) => {
            const query = e.target.value.trim();
            if (query.length < 2) {
                suggest.style.display = 'none';
                suggest.innerHTML = '';
                return;
            }

            const results = animeApi.search(query);
            if (results.length === 0) {
                suggest.innerHTML = `<div class="p-3 text-muted text-center" style="background:#252438; border-radius:8px;">No anime found for "${query}"</div>`;
            } else {
                suggest.innerHTML = `
                <div class="search-results-list" style="background:#252438; border:1px solid rgba(255,255,255,0.1); border-radius:8px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.85);">
                    ${results.slice(0, 5).map(anime => `
                    <a href="watch.html?anime=${anime.slug}" class="d-flex align-items-center p-2 text-decoration-none" style="border-bottom:1px solid rgba(255,255,255,0.05); color:#fff;">
                        <img src="${anime.poster}" style="width:40px; height:54px; object-fit:cover; border-radius:4px; margin-right:12px;">
                        <div>
                            <div style="font-weight:600; font-size:13px; color:#fff; line-height:1.2;">${anime.title}</div>
                            <div style="font-size:11px; color:#aaa; margin-top:3px;">
                                ${anime.type || 'TV'} • <span style="color:#ffbade;">★ ${anime.score || '8.0'}</span>
                                ${anime.dubCount ? '<span class="badge ml-1" style="background:#b9e7ff; color:#111; font-size:9.5px;">Hindi</span>' : ''}
                            </div>
                        </div>
                    </a>
                    `).join('')}
                    <a href="browse.html?keyword=${encodeURIComponent(query)}" class="d-block p-2 text-center text-decoration-none" style="background:rgba(255,186,222,0.1); color:#ffbade; font-weight:600; font-size:12px;">
                        View all results (${results.length}) <i class="fas fa-angle-right ml-1"></i>
                    </a>
                </div>
                `;
            }
            suggest.style.display = 'block';
        }, 200);

        input.addEventListener('input', handleSearch);

        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !suggest.contains(e.target)) {
                suggest.style.display = 'none';
            }
        });
    };

    bindSearch('.search-input', '#search-suggest');
    bindSearch('#mobile-search-input', '#mobile-search-suggest');
}
