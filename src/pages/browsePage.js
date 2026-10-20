// Browse / Filter Page Controller

import { animeApi } from '../api/animeApi.js';
import { getQueryParams } from '../utils/helpers.js';
import { renderFilterBar, initFilterEvents } from '../components/browse/FilterBar.js';
import { renderAnimeCard } from '../components/common/AnimeCard.js';

export function renderBrowsePage() {
    const mainWrapper = document.getElementById('main-wrapper');
    if (!mainWrapper) return;

    const params = getQueryParams();
    let currentResults = animeApi.getLatestEpisodes();

    if (params.keyword) {
        currentResults = animeApi.search(params.keyword);
    } else if (params.genre) {
        currentResults = animeApi.filter({ genre: [params.genre] });
    } else if (params.filter === 'dub') {
        currentResults = animeApi.filter({ language: 'dub' });
    }

    const renderGrid = (list) => {
        if (!list || list.length === 0) {
            return `
            <div class="p-5 text-center my-4" style="background:#201f31; border-radius:10px;">
                <i class="fas fa-search mb-3 text-muted" style="font-size:42px;"></i>
                <h4 class="text-white">No Anime Found</h4>
                <p class="text-muted" style="font-size:13px;">Try adjusting your filter or search criteria to find what you're looking for.</p>
                <a href="browse.html" class="btn btn-sm mt-2" style="background:#ffbade; color:#111; font-weight:700; border-radius:20px; padding:6px 20px;">Clear Filter</a>
            </div>
            `;
        }

        return `
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3" style="row-gap:24px;">
            ${list.map(anime => `
            <div class="col">
                ${renderAnimeCard(anime)}
            </div>
            `).join('')}
        </div>
        `;
    };

    mainWrapper.innerHTML = `
        <div class="container my-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="cat-heading mb-0" style="color:#ffbade; font-weight:700; font-size:1.4rem;">
                    <i class="fas fa-filter mr-2"></i> Browse & Filter Anime
                </h2>
                <div class="text-muted" style="font-size:12px;">
                    <span id="results-count-badge" class="badge badge-secondary py-1 px-2" style="background:rgba(255,255,255,0.1); font-size:11px;">
                        ${currentResults.length} Titles Found
                    </span>
                </div>
            </div>

            ${renderFilterBar(params)}

            <div id="browse-results-grid">
                ${renderGrid(currentResults)}
            </div>
        </div>
    `;

    setTimeout(() => {
        initFilterEvents((criteria) => {
            const filtered = animeApi.filter(criteria);
            const gridContainer = document.getElementById('browse-results-grid');
            const badge = document.getElementById('results-count-badge');
            const countLabel = document.getElementById('filter-results-count');

            if (gridContainer) gridContainer.innerHTML = renderGrid(filtered);
            if (badge) badge.innerText = `${filtered.length} Titles Found`;
            if (countLabel) countLabel.innerText = `Showing ${filtered.length} anime results`;
        });
    }, 50);
}
