// 4-Column Featured Highlights Component (HiAnime Screenshot 2)

import { animeApi } from '../../api/animeApi.js';

export function renderFeaturedColumns() {
    const cols = animeApi.getFeaturedColumns();

    const renderColumn = (title, list) => `
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-4">
        <div class="anif-block" style="background:#201f31; border-radius:8px;">
            <div class="anif-block-header" style="color:#ffbade; font-weight:700; font-size:1.15rem; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.08); margin-bottom:15px;">
                ${title}
            </div>
            <div class="anif-block-ul">
                <ul class="list-unstyled mb-0" style="display:flex; flex-direction:column; gap:12px;">
                    ${list.map(anime => `
                    <li class="d-flex align-items-center">
                        <a href="watch.html?anime=${anime.slug}" class="film-poster mr-3" style="width:55px; min-width:55px; height:72px; border-radius:6px; overflow:hidden; position:relative; display:block;">
                            <img src="${anime.poster}" class="film-poster-img" alt="${anime.title}" style="width:100%; height:100%; object-fit:cover;">
                        </a>
                        <div class="film-detail flex-grow-1" style="overflow:hidden;">
                            <h3 class="film-name mb-1" style="font-size:13.5px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <a href="watch.html?anime=${anime.slug}" style="color:#fff; text-decoration:none;">${anime.title}</a>
                            </h3>
                            <div class="fd-infor d-flex align-items-center" style="gap:6px; font-size:11px;">
                                ${anime.subCount ? `<span class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>${anime.subCount}</span>` : ''}
                                ${anime.dubCount ? `<span class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>${anime.dubCount}</span>` : ''}
                                <span class="dot text-muted">•</span>
                                <span class="fdi-item text-muted">${anime.type || 'TV'}</span>
                            </div>
                        </div>
                    </li>
                    `).join('')}
                </ul>
            </div>
            <div class="anif-block-footer mt-3 pt-2" style="border-top:1px solid rgba(255,255,255,0.05);">
                <a href="browse.html" class="btn-more text-decoration-none" style="color:#aaa; font-size:12px; font-weight:500; display:flex; align-items:center;">
                    View more <i class="fas fa-angle-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
    `;

    return `
    <section class="block_area block_area_featured py-4">
        <div class="container">
            <div class="row">
                ${renderColumn('Top Airing', cols.topAiring)}
                ${renderColumn('Most Popular', cols.mostPopular)}
                ${renderColumn('Most Favorite', cols.mostFavorite)}
                ${renderColumn('Latest Completed', cols.latestCompleted)}
            </div>
        </div>
    </section>
    `;
}
