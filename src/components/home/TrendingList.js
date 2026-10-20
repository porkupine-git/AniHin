// Trending Section Component (HiAnime Screenshot 1)

import { animeApi } from '../../api/animeApi.js';

export function renderTrendingList() {
    const trendings = animeApi.getTrendingAnime();

    return `
    <section class="block_area block_area_trending py-4">
        <div class="container">
            <div class="block_area-header d-flex justify-content-between align-items-center mb-3">
                <h2 class="cat-heading mb-0" style="color:#ffbade; font-weight:700; font-size:1.35rem;">Trending</h2>
                <div class="trending-navi d-flex" style="gap:8px;">
                    <div class="navi-prev" style="width:34px; height:34px; background:rgba(255,255,255,0.08); border-radius:6px; display:flex; align-items:center; justify-content:center; color:#fff; cursor:pointer;"><i class="fas fa-angle-left"></i></div>
                    <div class="navi-next" style="width:34px; height:34px; background:rgba(255,255,255,0.08); border-radius:6px; display:flex; align-items:center; justify-content:center; color:#fff; cursor:pointer;"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>

            <div class="trending-list" id="trending-home">
                <div class="swiper-container">
                    <div class="swiper-wrapper">
                        ${trendings.map((anime, idx) => `
                        <div class="swiper-slide">
                            <div class="item">
                                <div class="number">
                                    <span>${String(idx + 1).padStart(2, '0')}</span>
                                </div>
                                <a href="watch.html?anime=${anime.slug}" class="film-poster" title="${anime.title}">
                                    <img src="${anime.poster}" class="film-poster-img" alt="${anime.title}" loading="lazy">
                                    <div class="film-poster-ahref">
                                        <i class="fas fa-play"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>
    </section>
    `;
}

export function initTrendingSlider() {
    if (typeof Swiper !== 'undefined') {
        new Swiper('#trending-home .swiper-container', {
            slidesPerView: 'auto',
            spaceBetween: 20,
            navigation: {
                nextEl: '.trending-navi .navi-next',
                prevEl: '.trending-navi .navi-prev'
            },
            autoplay: {
                delay: 3500,
                disableOnInteraction: false
            }
        });
    }
}
