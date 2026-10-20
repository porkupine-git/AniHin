// Top 10 Widget Component (HiAnime Screenshot 3)

import { animeApi } from '../../api/animeApi.js';

export function renderTop10Widget(activeTab = 'today') {
    const list = animeApi.getTop10(activeTab);

    return `
    <section class="block_area block_area_sidebar hianime-top10-widget mt-4">
        <div class="block_area-header d-flex justify-content-between align-items-center mb-3">
            <h2 class="cat-heading mb-0" style="color:#ffbade; font-weight:700; font-size:1.35rem;">Top 10</h2>
            <div class="bah-tab-min">
                <ul class="nav nav-pills" id="top10-tabs" style="background:rgba(0,0,0,0.3); border-radius:20px; padding:3px;">
                    <li class="nav-item">
                        <a class="nav-link ${activeTab === 'today' ? 'active' : ''}" data-tab="today" href="javascript:;" style="font-size:12px; padding:4px 12px; border-radius:18px;">Today</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ${activeTab === 'week' ? 'active' : ''}" data-tab="week" href="javascript:;" style="font-size:12px; padding:4px 12px; border-radius:18px;">Week</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ${activeTab === 'month' ? 'active' : ''}" data-tab="month" href="javascript:;" style="font-size:12px; padding:4px 12px; border-radius:18px;">Month</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="top10-content-wrap" id="top10-list-container">
            ${renderTop10ListItems(list)}
        </div>
    </section>
    `;
}

export function renderTop10ListItems(list) {
    return `
    <ul class="list-unstyled mb-0" style="display:flex; flex-direction:column; gap:14px;">
        ${list.map((anime, idx) => `
        <li class="d-flex align-items-center">
            <div class="top-ten-number mr-3" style="font-size:22px; font-weight:800; min-width:32px; text-align:center; color:#fff; position:relative;">
                ${String(idx + 1).padStart(2, '0')}
            </div>
            <a href="watch.html?anime=${anime.slug}" class="film-poster mr-3" style="width:52px; min-width:52px; height:68px; border-radius:6px; overflow:hidden; position:relative; display:block;">
                <img src="${anime.poster}" class="film-poster-img" alt="${anime.title}" style="width:100%; height:100%; object-fit:cover;">
            </a>
            <div class="film-detail flex-grow-1" style="overflow:hidden;">
                <h3 class="film-name mb-1" style="font-size:13.5px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    <a href="watch.html?anime=${anime.slug}" style="color:#fff; text-decoration:none;">${anime.title}</a>
                </h3>
                <div class="fd-infor d-flex align-items-center" style="gap:6px; font-size:11px;">
                    ${anime.subCount ? `<span class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>${anime.subCount}</span>` : ''}
                    ${anime.dubCount ? `<span class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>${anime.dubCount}</span>` : ''}
                    ${anime.totalEpisodes ? `<span class="tick-item tick-eps">${anime.totalEpisodes}</span>` : ''}
                </div>
            </div>
        </li>
        `).join('')}
    </ul>
    `;
}

export function initTop10Tabs() {
    const tabContainer = document.getElementById('top10-tabs');
    const listContainer = document.getElementById('top10-list-container');
    if (tabContainer && listContainer) {
        tabContainer.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                tabContainer.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                const timeframe = this.getAttribute('data-tab');
                const newList = animeApi.getTop10(timeframe);
                listContainer.innerHTML = renderTop10ListItems(newList);
            });
        });
    }
}
