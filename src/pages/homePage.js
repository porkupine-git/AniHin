// Home Page Controller

import { renderHeroSlider, initHeroSlider } from '../components/home/HeroSlider.js';
import { renderTrendingList, initTrendingSlider } from '../components/home/TrendingList.js';
import { renderFeaturedColumns } from '../components/home/FeaturedColumns.js';
import { renderLatestGrid } from '../components/home/LatestGrid.js';
import { renderTop10Widget, initTop10Tabs } from '../components/home/Top10Widget.js';
import { renderGenreWidget } from '../components/home/GenreWidget.js';

export function renderHomePage() {
    const mainWrapper = document.getElementById('main-wrapper');
    if (!mainWrapper) return;

    mainWrapper.innerHTML = `
        ${renderHeroSlider()}
        ${renderTrendingList()}
        ${renderFeaturedColumns()}
        
        <div class="container my-5">
            <div class="row">
                <div class="col-xl-9 col-lg-8 mb-4">
                    ${renderLatestGrid()}
                </div>
                <div class="col-xl-3 col-lg-4">
                    ${renderGenreWidget()}
                    ${renderTop10Widget('today')}
                </div>
            </div>
        </div>
    `;

    // Initialize interactive behaviors
    setTimeout(() => {
        initHeroSlider();
        initTrendingSlider();
        initTop10Tabs();
    }, 50);
}
