// Genres Sidebar Widget Component (HiAnime Screenshot 3)

import { allGenres } from '../../data/animeDatabase.js';

export function renderGenreWidget() {
    const displayGenres = allGenres.slice(0, 24);

    return `
    <section class="block_area block_area_sidebar block_area-genres mb-4">
        <div class="block_area-header mb-3">
            <h2 class="cat-heading mb-0" style="color:#ffbade; font-weight:700; font-size:1.35rem;">Genres</h2>
        </div>
        <div class="cbs-content p-3" style="background:#252438; border-radius:10px;">
            <div class="row row-cols-3 g-2" style="row-gap:8px;">
                ${displayGenres.map(g => `
                <div class="col px-1">
                    <a href="browse.html?genre=${encodeURIComponent(g)}" class="btn-tag text-truncate d-block" style="color:#c0c0d8; font-size:12px; padding:5px 8px; border-radius:4px; text-decoration:none; background:rgba(255,255,255,0.03);">
                        ${g}
                    </a>
                </div>
                `).join('')}
            </div>
            <div class="mt-3 text-center">
                <a href="browse.html" class="btn btn-sm btn-block" style="background:rgba(255,255,255,0.06); color:#fff; font-size:12px; border-radius:6px; padding:6px 0;">
                    Show more <i class="fas fa-angle-right ml-1"></i>
                </a>
            </div>
        </div>
    </section>
    `;
}
