// Reusable HiAnime Header Component with Dynamic Search Suggestions

import { animeApi } from '../../api/animeApi.js';
import { debounce } from '../../utils/helpers.js';

export function renderHeader() {
    return `
    <header id="header" class="header-home">
        <div class="container d-flex align-items-center justify-content-between">
            <div id="header_left" class="d-flex align-items-center">
                <div id="sidebar_menu_toggle" class="btn btn-sm mr-3 text-light" style="font-size: 22px; cursor: pointer;">
                    <i class="fas fa-bars"></i>
                </div>
                <a href="index.html" id="logo" class="mr-4">
                    <img src="assets/images/logo.png" alt="HiAnime" style="height: 38px;">
                </a>
                
                <div id="search" class="search-content">
                    <form action="browse.html" method="get" class="search-from position-relative">
                        <input type="text" class="form-control search-input" name="keyword" placeholder="Search anime..." autocomplete="off">
                        <button class="search-icon" type="submit"><i class="fas fa-search"></i></button>
                        <div id="search-suggest" class="search-suggest position-absolute" style="display: none;"></div>
                    </form>
                </div>
                
                <div class="header-social-cluster d-none d-xl-flex align-items-center ml-4">
                    <span class="join-now-text text-muted mr-2">Join now</span>
                    <a href="https://discord.com" target="_blank" class="mr-2 text-primary" style="font-size: 18px;"><i class="fab fa-discord"></i></a>
                    <a href="https://t.me" target="_blank" class="mr-2 text-info" style="font-size: 18px;"><i class="fab fa-telegram"></i></a>
                    <a href="https://reddit.com" target="_blank" class="mr-2 text-danger" style="font-size: 18px;"><i class="fab fa-reddit"></i></a>
                    <a href="https://twitter.com" target="_blank" class="text-info" style="font-size: 18px;"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <div id="header_center" class="d-none d-lg-flex align-items-center">
                <a href="watch.html" class="nav-item-btn mr-3"><i class="fas fa-broadcast-tower mr-1 text-danger"></i> Watch2gether</a>
                <a href="watch.html?anime=random" class="nav-item-btn mr-3"><i class="fas fa-random mr-1 text-warning"></i> Random</a>
                <div class="nav-item-btn anime-name-toggle mr-3">
                    <span class="badge badge-secondary py-1 px-2" style="font-size: 11px; border-radius: 12px; background: rgba(255,255,255,0.15);">EN | JP</span>
                    <span class="ml-1" style="font-size: 12px;">Anime Name</span>
                </div>
                <a href="browse.html" class="nav-item-btn mr-3"><i class="fas fa-rss mr-1 text-info"></i> News</a>
                <a href="browse.html" class="nav-item-btn"><i class="fas fa-comments mr-1 text-success"></i> Community</a>
            </div>

            <div id="header_right" class="d-flex align-items-center">
                <a href="browse.html" class="btn btn-circle mr-3 text-light d-lg-none" style="font-size: 18px;"><i class="fas fa-search"></i></a>
                <a href="browse.html" class="btn btn-filter mr-3 text-light"><i class="fas fa-filter mr-1"></i> Filter</a>
                <a href="javascript:;" class="btn btn-login" style="background:#ffbade; color:#111; font-weight:700; border-radius:20px; padding:6px 20px;">Login</a>
            </div>
        </div>
    </header>
    `;
}

export function initHeaderEvents() {
    const searchInput = document.querySelector('.search-input');
    const searchSuggest = document.getElementById('search-suggest');

    if (searchInput && searchSuggest) {
        const handleSearch = debounce((e) => {
            const query = e.target.value.trim();
            if (query.length < 2) {
                searchSuggest.style.display = 'none';
                searchSuggest.innerHTML = '';
                return;
            }

            const results = animeApi.search(query);
            if (results.length === 0) {
                searchSuggest.innerHTML = `<div class="p-3 text-muted text-center">No anime found for "${query}"</div>`;
            } else {
                searchSuggest.innerHTML = `
                <div class="search-results-list" style="background:#252438; border:1px solid rgba(255,255,255,0.1); border-radius:8px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.8);">
                    ${results.slice(0, 6).map(anime => `
                    <a href="watch.html?anime=${anime.slug}" class="d-flex align-items-center p-2 text-decoration-none" style="border-bottom:1px solid rgba(255,255,255,0.05); color:#fff; transition:background 0.2s;">
                        <img src="${anime.poster}" style="width:42px; height:56px; object-fit:cover; border-radius:4px; margin-right:12px;">
                        <div>
                            <div style="font-weight:600; font-size:13.5px; color:#fff;">${anime.title}</div>
                            <div style="font-size:11.5px; color:#aaa;">${anime.type || 'TV'} • ${anime.releaseDate || ''} • <span style="color:#ffbade;">★ ${anime.score || '8.0'}</span></div>
                        </div>
                    </a>
                    `).join('')}
                    <a href="browse.html?keyword=${encodeURIComponent(query)}" class="d-block p-2 text-center text-decoration-none" style="background:rgba(255,186,222,0.1); color:#ffbade; font-weight:600; font-size:12px;">
                        View all results (${results.length}) <i class="fas fa-angle-right ml-1"></i>
                    </a>
                </div>
                `;
            }
            searchSuggest.style.display = 'block';
        }, 200);

        searchInput.addEventListener('input', handleSearch);

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchSuggest.contains(e.target)) {
                searchSuggest.style.display = 'none';
            }
        });
    }
}
