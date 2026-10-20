// Spotlight Hero Slider Component (HiAnime Screenshot 1)

import { animeApi } from '../../api/animeApi.js';

export function renderHeroSlider() {
    const spotlights = animeApi.getSpotlightAnime();

    return `
    <div class="deslide-wrap">
        <div class="container-fluid px-0">
            <div id="slider" class="swiper-container position-relative">
                <div class="swiper-wrapper">
                    ${spotlights.map((anime, idx) => `
                    <div class="swiper-slide deslide-item" data-id="${anime.id}">
                        <div class="deslide-cover" style="position:absolute; inset:0; z-index:1; overflow:hidden;">
                            <img src="${anime.banner}" alt="${anime.title}" style="position:absolute; right:0; top:0; width:65%; height:100%; object-fit:cover; filter: drop-shadow(0 0 50px rgba(0,0,0,0.8));">
                            <div class="deslide-cover-overlay" style="position:absolute; inset:0; background: linear-gradient(90deg, #191826 35%, rgba(25,24,38,0.7) 65%, rgba(25,24,38,0.2) 100%);"></div>
                        </div>

                        <div class="container position-relative" style="z-index:2; padding-top:60px; padding-bottom:70px;">
                            <div class="deslide-item-content" style="max-width: 600px;">
                                <div class="desi-sub-text mb-2" style="color:#ffbade; font-weight:600; font-size:14px;">
                                    #${idx + 1} Spotlight
                                </div>
                                <h2 class="desi-head-title mb-3" style="font-size:38px; font-weight:800; color:#fff; line-height:1.2;">
                                    ${anime.title}
                                </h2>
                                
                                <div class="sc-detail d-flex align-items-center flex-wrap mb-3" style="gap:10px; font-size:13px; color:#ddd;">
                                    <span class="scd-item"><i class="fas fa-play-circle mr-1 text-light"></i> ${anime.type}</span>
                                    <span class="scd-item"><i class="fas fa-calendar-alt mr-1 text-light"></i> ${anime.releaseDate}</span>
                                    <span class="tick-item tick-rate" style="background:#ffbade; color:#111; font-weight:700; border-radius:4px; padding:1px 6px; font-size:11px;">HD</span>
                                    ${anime.subCount ? `<span class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>${anime.subCount}</span>` : ''}
                                    ${anime.dubCount ? `<span class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>${anime.dubCount}</span>` : ''}
                                </div>

                                <div class="desi-description mb-4" style="color:#bbb; font-size:14px; line-height:1.6; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                                    ${anime.synopsis}
                                </div>

                                <div class="desi-buttons d-flex align-items-center">
                                    <a href="watch.html?anime=${anime.slug}" class="btn btn-play" style="background:#ffbade; color:#111; font-weight:700; border-radius:30px; padding:10px 24px; border:none; text-decoration:none;">
                                        <i class="fas fa-play mr-2"></i> Watch Now
                                    </a>
                                    <a href="watch.html?anime=${anime.slug}#details" class="btn btn-detail ml-3" style="background:rgba(255,255,255,0.12); color:#fff; font-weight:500; border-radius:30px; padding:10px 22px; border:1px solid rgba(255,255,255,0.15); text-decoration:none;">
                                        Detail <i class="fas fa-angle-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    `).join('')}
                </div>

                <!-- Right vertical slider navigation -->
                <div class="deslide-controls" style="position:absolute; right:30px; bottom:40px; z-index:10; display:flex; flex-direction:column; gap:8px;">
                    <div class="swiper-button-next" style="position:static; margin:0; width:36px; height:36px; background:rgba(255,255,255,0.1); border-radius:6px; color:#fff;"></div>
                    <div class="swiper-button-prev" style="position:static; margin:0; width:36px; height:36px; background:rgba(255,255,255,0.1); border-radius:6px; color:#fff;"></div>
                </div>
            </div>
        </div>
    </div>
    `;
}

export function initHeroSlider() {
    if (typeof Swiper !== 'undefined') {
        new Swiper('#slider', {
            loop: true,
            speed: 600,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false
            },
            navigation: {
                nextEl: '.deslide-controls .swiper-button-next',
                prevEl: '.deslide-controls .swiper-button-prev'
            }
        });
    }
}
