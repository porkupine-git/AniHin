// Latest Episodes Grid Component (Responsive 2 to 4 Columns)

import { animeApi } from '../../api/animeApi.js';
import { renderAnimeCard } from '../common/AnimeCard.js';

export function renderLatestGrid() {
    const animeList = animeApi.getLatestEpisodes();

    return `
    <section class="block_area block_area_home">
        <div class="block_area-header d-flex justify-content-between align-items-center mb-3">
            <h2 class="cat-heading mb-0" style="color:#ffbade; font-weight:700; font-size:1.35rem;">Latest Episode</h2>
            <div class="viewmore">
                <a class="btn text-decoration-none" href="browse.html" style="color:#aaa; font-size:13px; font-weight:500;">
                    View more <i class="fas fa-angle-right ml-1"></i>
                </a>
            </div>
        </div>

        <div class="block_area-content film_list film_list-grid">
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 g-md-3" style="row-gap: 20px;">
                ${animeList.map(anime => `
                <div class="col">
                    ${renderAnimeCard(anime)}
                </div>
                `).join('')}
            </div>
        </div>
    </section>
    `;
}
