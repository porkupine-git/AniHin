// Watch Page Controller

import { animeApi } from '../api/animeApi.js';
import { getQueryParams } from '../utils/helpers.js';
import { renderVideoPlayer, initPlayerControls } from '../components/watch/VideoPlayer.js';
import { renderServerSelector, initServerEvents } from '../components/watch/ServerSelector.js';
import { renderEpisodeList, initEpisodeListEvents } from '../components/watch/EpisodeList.js';
import { renderAnimeDetails, renderDiscussion, initDiscussionEvents } from '../components/watch/AnimeDetails.js';
import { renderTop10Widget, initTop10Tabs } from '../components/home/Top10Widget.js';

export function renderWatchPage() {
    const mainWrapper = document.getElementById('main-wrapper');
    if (!mainWrapper) return;

    const params = getQueryParams();
    const anime = animeApi.getAnimeBySlug(params.anime || 'black-clover-season-2');
    const currentEp = parseInt(params.ep, 10) || 1;

    document.title = `Watch ${anime.title} Episode ${currentEp} in HD | HiAnime`;

    mainWrapper.innerHTML = `
        <div class="container my-4">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb py-2 px-3" style="background:#191826; border-radius:6px; font-size:12px;">
                    <li class="breadcrumb-item"><a href="index.html" style="color:#aaa;">Home</a></li>
                    <li class="breadcrumb-item"><a href="browse.html" style="color:#aaa;">Anime</a></li>
                    <li class="breadcrumb-item active" style="color:#ffbade;" aria-current="page">${anime.title}</li>
                </ol>
            </nav>

            <div class="row">
                <div class="col-xl-9 col-lg-8">
                    ${renderVideoPlayer(anime, currentEp)}
                    ${renderServerSelector(anime)}
                    ${renderEpisodeList(anime, currentEp)}
                    ${renderAnimeDetails(anime)}
                    ${renderDiscussion()}
                </div>

                <div class="col-xl-3 col-lg-4">
                    <div class="sidebar-watch">
                        <div class="p-3 mb-4" style="background:#201f31; border-radius:10px;">
                            <h4 class="cat-heading mb-3" style="color:#ffbade; font-size:15px; font-weight:700;">
                                <i class="fas fa-fire mr-1"></i> Quick Hindi Dubs
                            </h4>
                            <div class="quick-anime-list d-flex flex-column" style="gap:10px;">
                                ${animeApi.getTrendingAnime().slice(0, 5).map(a => `
                                <a href="watch.html?anime=${a.slug}" class="d-flex align-items-center text-decoration-none" style="color:#fff;">
                                    <img src="${a.poster}" style="width:48px; height:64px; object-fit:cover; border-radius:6px; margin-right:12px;">
                                    <div>
                                        <div style="font-weight:600; font-size:13px; line-height:1.3;">${a.title}</div>
                                        <div class="mt-1" style="font-size:11px;">
                                            <span class="badge py-1 px-2" style="background:#b9e7ff; color:#111; font-weight:700;">Hindi Dub</span>
                                        </div>
                                    </div>
                                </a>
                                `).join('')}
                            </div>
                        </div>

                        ${renderTop10Widget('today')}
                    </div>
                </div>
            </div>
        </div>
    `;

    setTimeout(() => {
        initPlayerControls((newEp) => {
            const url = new URL(window.location);
            url.searchParams.set('ep', newEp);
            window.history.replaceState({}, '', url);
        });

        initServerEvents((server) => {
            console.log('Switched to server:', server);
        });

        initEpisodeListEvents((ep) => {
            const url = new URL(window.location);
            url.searchParams.set('ep', ep);
            window.history.replaceState({}, '', url);
        });

        initDiscussionEvents();
        initTop10Tabs();
    }, 50);
}
