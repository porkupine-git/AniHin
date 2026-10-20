// Reusable Anime Card Component (Pixel-perfect HiAnime Layout)

export function renderAnimeCard(anime, options = {}) {
    if (!anime) return '';

    const showDetails = options.showDetails !== false;
    const watchUrl = `watch.html?anime=${anime.slug}`;

    return `
    <div class="flw-item" data-id="${anime.id}">
        <div class="film-poster">
            <div class="tick ltr">
                ${anime.subCount ? `<div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>${anime.subCount}</div>` : ''}
                ${anime.dubCount ? `<div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>${anime.dubCount}</div>` : ''}
                ${anime.totalEpisodes ? `<div class="tick-item tick-eps">${anime.totalEpisodes}</div>` : ''}
            </div>
            
            <img src="${anime.poster}"
                class="film-poster-img"
                alt="${anime.title}"
                loading="lazy"
                decoding="async">

            <a href="${watchUrl}"
                class="film-poster-ahref"
                title="${anime.title}"
                aria-label="Watch ${anime.title}">
                <i class="fas fa-play" aria-hidden="true"></i>
            </a>
        </div>
        
        ${showDetails ? `
        <div class="film-detail">
            <h3 class="film-name">
                <a href="${watchUrl}"
                    title="${anime.title}"
                    class="dynamic-name"
                    data-jname="${anime.jname || anime.title}">
                    ${anime.title}
                </a>
            </h3>
            <div class="fd-infor">
                <span class="fdi-item">${anime.type || 'TV'}</span>
                ${anime.duration ? `<span class="dot"></span><span class="fdi-item fdi-duration">${anime.duration}</span>` : ''}
            </div>
        </div>
        ` : ''}
        <div class="clearfix"></div>
    </div>
    `;
}
