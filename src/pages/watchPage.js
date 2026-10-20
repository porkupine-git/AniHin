// Exact 1:1 HiAnime Watch Page Controller

import { animeApi } from '../api/animeApi.js';
import { getQueryParams } from '../utils/helpers.js';

export function renderWatchPage() {
    const mainWrapper = document.getElementById('main-wrapper');
    if (!mainWrapper) return;

    const params = getQueryParams();
    const anime = animeApi.getAnimeBySlug(params.anime || 'black-clover-season-2');
    const currentEp = parseInt(params.ep, 10) || 1;

    document.title = `Watch ${anime.title} Episode ${currentEp} in HD | HiAnime`;

    mainWrapper.outerHTML = `<div id="main-wrapper" class="layout-page layout-page-detail layout-page-watchtv" role="main">
    <div id="ani_detail" data-anime-id="100" data-id="2928" data-episode-number="1">
        <div class="ani_detail-stage">
            <div class="container">
                <div class="anis-cover-wrap">
                    <div class="anis-cover" style="background-image: url(https://hianime.at/storage/media/2026/08/the-ramparts-of-ice-banner.webp)"></div>
                </div>
                <div class="anis-watch-wrap">
                    <div class="prebreadcrumb">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html" title="Home">Home</a></li>
                                                                <li class="breadcrumb-item"><a href="browse.html">TV</a></li>
                                                                <li class="breadcrumb-item dynamic-name active" data-jname="Koori no Jouheki" style="opacity: 1;">Watching The Ramparts of Ice</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="anis-watch anis-watch-tv">
                        <div class="watch-player" style="position: relative;">
                                                                                    
                            <style>.player-frame:fullscreen,.player-frame:-webkit-full-screen{padding:0!important;background:#000}</style>
                            <div class="player-frame">
                                
<div class="video-player-container" style="width:100%; aspect-ratio:16/9; background:#000; border-radius:8px; overflow:hidden;">
    <iframe id="anime-video-player" src="https://www.youtube-nocookie.com/embed/fJ9rUzIMcZQ?autoplay=0&rel=0&modestbranding=1" 
        style="width:100%; height:100%; border:none;" 
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
    </iframe>
</div>
<div class="loading-relative loading-box d-none" id="embed-loading">
                                    <div class="loading">
                                        <div class="span1"></div>
                                        <div class="span2"></div>
                                        <div class="span3"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="player-controls">
                                <div class="pc-item pc-resize">
                                    <a href="javascript:;" id="media-resize" class="btn btn-sm"><i class="fas fa-expand mr-1"></i>Expand</a>
                                </div>
                                <div class="pc-item pc-toggle pc-light">
                                    <div id="turn-off-light" class="toggle-basic">
                                        <span class="tb-name"><i class="fas fa-lightbulb mr-2"></i>Light</span>
                                        <span class="tb-result"></span>
                                    </div>
                                </div>
                                <div class="pc-item pc-toggle pc-autoplay">
                                    <div class="toggle-basic quick-settings" data-option="auto_play">
                                        <span class="tb-name">Auto Play</span>
                                        <span class="tb-result"></span>
                                    </div>
                                </div>
                                <div class="pc-item pc-toggle pc-autonext">
                                    <div class="toggle-basic quick-settings off" data-option="auto_next">
                                        <span class="tb-name">Auto Next</span>
                                        <span class="tb-result"></span>
                                    </div>
                                </div>
                                <div class="pc-item pc-toggle pc-autoskipintro">
                                    <div class="toggle-basic quick-settings off" data-option="auto_skip_intro">
                                        <span class="tb-name">Auto Skip Intro</span>
                                        <span class="tb-result"></span>
                                    </div>
                                </div>
                                <div class="pc-right">
                                    
                                    <div class="dr-fav pc-item pc-fav" id="watch-list-content">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-sm"><i class="fas fa-plus"></i> Add to List</a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="wl-item dropdown-item"
            data-type="1"
            data-animeid="100"
             data-page="watch"             href="javascript:;">Watching</a>
                <a class="wl-item dropdown-item"
            data-type="2"
            data-animeid="100"
             data-page="watch"             href="javascript:;">On-Hold</a>
                <a class="wl-item dropdown-item"
            data-type="3"
            data-animeid="100"
             data-page="watch"             href="javascript:;">Plan to Watch</a>
                <a class="wl-item dropdown-item"
            data-type="4"
            data-animeid="100"
             data-page="watch"             href="javascript:;">Dropped</a>
                <a class="wl-item dropdown-item"
            data-type="5"
            data-animeid="100"
             data-page="watch"             href="javascript:;">Completed</a>
                    </div>
</div>
                                    <div class="pc-item pc-live">
                                        <a href="https://hianime.at/watch2gather" class="btn btn-sm btn-watch2gether"><i class="zicon zicon-20 zicon-live mr-2" style="display: flex;"></i><span class="m-hide">Watch2gether</span><span class="w-hide">Live</span></a>
                                    </div>
                                    <div class="pc-item pc-control block-prev" style="">
                                        <a class="btn btn-sm btn-prev" href="javascript:;" onclick="prevEpisode()"><i class="fas fa-backward mr-2"></i>Prev</a>
                                    </div>
                                    <div class="pc-item pc-control block-next" style="">
                                        <a class="btn btn-sm btn-next" href="javascript:;" onclick="nextEpisode()">Next<i class="fas fa-forward ml-2"></i></a>
                                    </div>

                                                                        <div class="pc-item pc-report">
                                        <a href="javascript:;" class="btn btn-sm" data-toggle="modal" data-target="#reportmodal"><i class="fas fa-exclamation-triangle mr-1"></i>Report</a>
                                    </div>
                                                                    </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="player-servers">
                            <div id="servers-content">
<div class="ps_-block ps_-block-sub">
    <div class="ps__-title"><i class="fas fa-closed-captioning mr-1 text-success"></i> SUB:</div>
    <div class="ps__-list">
        <div class="item"><a class="btn btn-server active" href="javascript:;" data-server="hd-1">HD-1</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="hd-2">HD-2</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="megacloud">MegaCloud</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="streamsb">StreamSB</a></div>
    </div>
</div>
<div class="ps_-block ps_-block-dub" style="border-top: 1px solid rgba(255,255,255,0.06); padding-top: 12px; margin-top: 8px;">
    <div class="ps__-title"><i class="fas fa-microphone mr-1 text-info"></i> DUB (Hindi Featured):</div>
    <div class="ps__-list">
        <div class="item"><a class="btn btn-server active" href="javascript:;" data-server="hindi-1" style="background:#ffbade; color:#111; font-weight:700;"><i class="fas fa-play mr-1"></i> Hindi HD-1</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="hindi-2">Hindi HD-2</a></div>
        <div class="item"><a class="btn btn-server" href="javascript:;" data-server="eng-dub">English Dub</a></div>
    </div>
</div>
</div>
                            
                                                        <div id="downloads-content"></div>
                                                    </div>
                        
                        
                        <div id="episodes-content">
<div class="episodes-list" style="background: #252438; border-radius: 8px; padding: 18px; margin-top: 20px;">
    <div class="episodes-list-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="cat-heading mb-0" style="color:#ffbade; font-size:16px; font-weight:600;">List of Episodes: (Playing: Ep <span id="current-ep-num">1</span>)</h4>
        </div>
        <div class="ep-search" style="max-width: 180px;">
            <input type="text" class="form-control form-control-sm" placeholder="Number of Ep" id="search-ep" style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:20px; padding: 4px 14px;">
        </div>
    </div>
    <div class="episodes-ul-wrapper" style="max-height: 250px; overflow-y: auto;">
        <div class="episodes-ul">
            
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item active" href="javascript:;" data-number="1" title="Episode 1" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">1</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="2" title="Episode 2" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">2</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="3" title="Episode 3" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">3</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="4" title="Episode 4" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">4</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="5" title="Episode 5" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">5</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="6" title="Episode 6" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">6</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="7" title="Episode 7" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">7</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="8" title="Episode 8" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">8</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="9" title="Episode 9" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">9</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="10" title="Episode 10" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">10</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="11" title="Episode 11" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">11</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="12" title="Episode 12" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">12</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="13" title="Episode 13" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">13</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="14" title="Episode 14" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">14</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="15" title="Episode 15" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">15</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="16" title="Episode 16" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">16</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="17" title="Episode 17" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">17</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="18" title="Episode 18" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">18</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="19" title="Episode 19" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">19</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="20" title="Episode 20" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">20</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="21" title="Episode 21" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">21</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="22" title="Episode 22" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">22</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="23" title="Episode 23" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">23</a>
    </div>
    
    <div class="item" style="display:inline-block; margin: 4px;">
        <a class="btn ep-item" href="javascript:;" data-number="24" title="Episode 24" style="min-width:44px; padding:6px 12px; border-radius:4px; font-weight:600; font-size:13px; background:rgba(255,255,255,0.07); color:#fff;">24</a>
    </div>
    
        </div>
    </div>
</div>
</div>
                    </div>
                    <div class="anis-watch-detail">
                        <div class="anis-content" data-anime-id="100">
                            <div class="anisc-poster">
                                <div class="film-poster">
                                                                        <img src="https://hianime.at/storage/media/2026/08/the-ramparts-of-ice.webp" class="film-poster-img" alt="The Ramparts of Ice">
                                </div>
                            </div>
                            <div class="anisc-detail">
                                <h2 class="film-name">
                                    <a href="watch.html?anime=the-ramparts-of-ice-100" class="text-white dynamic-name" title="The Ramparts of Ice" data-jname="Koori no Jouheki" style="opacity: 1;">The Ramparts of Ice</a>
                                </h2>
                                <div class="film-stats">
                                    <div class="tick">
                                                                                <div class="tick-item tick-pg">PG-13</div>
                                                                                <div class="tick-item tick-quality">HD</div>
                                                                                <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>14</div>
                                                                                                                        <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>14</div>
                                                                                                                        <div class="tick-item tick-eps">14</div>
                                                                                <span class="dot"></span>
                                        <span class="item">TV</span>
                                                                                <span class="dot"></span>
                                        <span class="item">24m</span>
                                                                                <div class="clearfix"></div>
                                    </div>
                                </div>
                                <div class="film-description m-hide">
                                    <div class="text">
                                        Inept at dealing with people, Koyuki Hikawa maintains a wall between herself and others. She spends her time in high school away from others, all alone. At least until she encounters Minato Amamiya who keeps closing the distance between them for some reason. The aloof Koyuki, the popular Miki, the boundary-less Minato,...
                                    </div>
                                </div>
                                <div class="film-text m-hide mb-3">
                                    HiAnime is the best site to watch <strong>The Ramparts of Ice</strong> SUB online, or you can even watch <strong>The Ramparts of Ice</strong> DUB in HD quality.
                                                                        You can also find <strong><a href="https://hianime.at/studios/studio-kai">Studio KAI</a></strong> anime on HiAnime website.
                                                                    </div>
                                <div class="block"><a href="https://hianime.at/the-ramparts-of-ice-100" class="btn btn-xs btn-light">View detail</a></div>
                                                                                                <div class="dt-rate">
                                    <div id="vote-info" data-anime-id="100">
                                        <div class="block-rating ">
                                            <div class="rating-result">
                                                <div class="rr-mark float-left">
                                                    <strong><i class="fas fa-star text-warning mr-2"></i><span id="rr-score">9.6</span></strong>
                                                </div>
                                                <div class="rr-title float-right">Vote now</div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="description">What do you think about this anime?</div>
                                            <div class="button-rate">
                                                <button type="button" class="btn btn-emo rate-bad btn-vote " data-mark="1" data-id="100">😩<span class="ml-2">Boring</span></button>
                                                <button type="button" class="btn btn-emo rate-normal btn-vote " data-mark="5" data-id="100">😃<span class="ml-2">Great</span></button>
                                                <button type="button" class="btn btn-emo rate-good btn-vote " data-mark="10" data-id="100">🤩<span class="ml-2">Amazing</span></button>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                </div>
                                                            </div>
                            <a href="javascript:;" class="dt-comment">
                                <div class="icon"><i class="fas fa-comment"></i></div>
                                <div class="number">4</div>
                                <div class="text">Comments</div>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
.st-inline-share-buttons{font-family:'Helvetica Neue',Verdana,Helvetica,Arial,sans-serif;direction:ltr;display:flex;flex-wrap:wrap;align-items:center;gap:8px;text-align:left}
.st-inline-share-buttons .st-btn{box-sizing:border-box;transition:opacity .2s ease-in,top .2s ease-in;border-radius:20px;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;font-size:11px;height:32px;padding:0 12px;position:relative;top:0;white-space:nowrap}
.st-inline-share-buttons .st-btn>svg{height:16px;width:16px;flex:none;color:#fff}
.st-inline-share-buttons .st-btn>span{color:#fff;font-weight:500;letter-spacing:.5px;line-height:1}
.st-inline-share-buttons.st-has-labels .st-btn{min-width:100px}
.st-inline-share-buttons.st-has-labels .st-btn.st-remove-label{min-width:0}
.st-inline-share-buttons.st-has-labels .st-btn.st-remove-label>span{display:none}
.st-inline-share-buttons .st-btn:hover{opacity:.8;top:-4px}
.st-inline-share-buttons .st-btn:focus-visible{outline:2px solid var(--accent-color,#ffbade);outline-offset:2px}
.st-inline-share-buttons .st-total{color:#d2d2d3;display:inline-block;font-weight:500;line-height:12px;max-width:80px;padding:4px 8px;text-align:center}
.st-inline-share-buttons .st-total>span{font-size:16px;line-height:17px;display:block;padding:0}
.st-inline-share-buttons .st-total>span.st-shares{font-size:9px;line-height:9px;text-transform:uppercase}
.st-inline-share-buttons .st-btn[data-network='telegram']{background-color:#0088cc}
.st-inline-share-buttons .st-btn[data-network='twitter']{background-color:#000}
.st-inline-share-buttons .st-btn[data-network='facebook']{background-color:#4267b2}
.st-inline-share-buttons .st-btn[data-network='reddit']{background-color:#ff4500}
.st-inline-share-buttons .st-btn[data-network='native']{background-color:#95d03a}
@media (prefers-reduced-motion:reduce){.st-inline-share-buttons .st-btn{transition:none}.st-inline-share-buttons .st-btn:hover{top:0}}
/* Progressive label shedding: as the row runs out of room the labels drop from
   the right inwards, so the leading Telegram button keeps its text longest and
   the buttons collapse to icon-only pills instead of wrapping to a new line. */
@media (max-width:1250px){
    .st-inline-share-buttons.st-has-labels .st-btn:not(.st-first)>span{display:none}
    .st-inline-share-buttons.st-has-labels .st-btn:not(.st-first){min-width:0}
}
@media (max-width:750px){
    .st-inline-share-buttons.st-has-labels .st-btn>span{display:none}
    .st-inline-share-buttons.st-has-labels .st-btn{min-width:0;padding:0 10px}
    .st-inline-share-buttons{gap:6px}
}
/* Below the icon-only stage the networks themselves drop, from the middle of
   the row inwards. Telegram (.st-first) and the native sheet (.st-last) always
   survive: the native button opens the OS share sheet, which offers every app
   the user actually has, so it is the fallback for anything hidden here. */
@media (max-width:560px){
    .st-inline-share-buttons .st-btn[data-network='reddit']{display:none}
}
@media (max-width:460px){
    .st-inline-share-buttons .st-btn[data-network='twitter']{display:none}
}
@media (max-width:380px){
    .st-inline-share-buttons .st-btn[data-network='facebook']{display:none}
    .st-inline-share-buttons .st-total{max-width:none;padding:4px 6px}
}
</style>
<script data-cfasync="false">
(function () {
    var W = 600, H = 520;

    function target(network, url, title) {
        var u = encodeURIComponent(url), t = encodeURIComponent(title);
        if (network === 'telegram') return 'https://t.me/share/url?url=' + u + '&text=' + t;
        if (network === 'twitter') return 'https://twitter.com/intent/tweet?url=' + u + '&text=' + t;
        if (network === 'facebook') return 'https://www.facebook.com/sharer/sharer.php?u=' + u;
        if (network === 'reddit') return 'https://www.reddit.com/submit?url=' + u + '&title=' + t;
        return null;
    }

    function popup(url) {
        var left = window.screenX + Math.max(0, (window.outerWidth - W) / 2),
            top = window.screenY + Math.max(0, (window.outerHeight - H) / 2),
            win = window.open(url, 'hianime-share',
                'width=' + W + ',height=' + H + ',left=' + left + ',top=' + top +
                ',toolbar=0,menubar=0,scrollbars=1,resizable=1');

        // Blocked as a popup — fall back to a tab so the share still happens.
        if (!win || win.closed) window.open(url, '_blank', 'noopener,noreferrer');
        else win.focus();
    }

    function flash(btn, msg) {
        var el = btn.querySelector('.st-label');
        if (!el || btn.getAttribute('data-flashing') === '1') return;
        var original = el.textContent;
        btn.setAttribute('data-flashing', '1');
        el.textContent = msg;
        setTimeout(function () {
            el.textContent = original;
            btn.removeAttribute('data-flashing');
        }, 1600);
    }

    function copy(btn, url) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function () {
                flash(btn, 'Copied!');
            }, function () {
                window.prompt('Copy this link', url);
            });
        } else {
            window.prompt('Copy this link', url);
        }
    }

    function native(btn, url, title) {
        if (navigator.share) {
            navigator.share({ title: title, url: url })['catch'](function (err) {
                // AbortError = user dismissed the sheet; not a failure.
                if (!err || err.name !== 'AbortError') copy(btn, url);
            });
        } else {
            copy(btn, url);
        }
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.st-btn') : null;
        if (!btn || !btn.closest('[data-share-buttons]')) return;

        // Prefer the canonical URL so shares of a link carrying tracking
        // params still point at the real page.
        var canonical = document.querySelector('link[rel="canonical"]'),
            url = (canonical && canonical.href) || window.location.href,
            network = btn.getAttribute('data-network');

        if (network === 'native') native(btn, url, document.title);
        else {
            var t = target(network, url, document.title);
            if (t) popup(t);
        }
    });
})();
</script>
<div class="share-buttons share-buttons-detail share-buttons-page">
    <div class="container">    <div class="share-buttons-block">
        <div class="share-icon" ></div>
        <div class="sbb-title mr-3">
            <span>Share Anime</span>
            <p class="mb-0">to your friends</p>
        </div>
        <div class="sbb-social st-inline-share-buttons st-has-labels" data-share-buttons>
                            <div class="st-total">
                    <span class="st-label">544K</span>
                    <span class="st-shares">Shares</span>
                </div>
                        <button type="button" class="st-btn st-first" data-network="telegram" aria-label="Share on Telegram">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg>
                <span class="st-label">Share</span>
            </button>
            <button type="button" class="st-btn" data-network="twitter" aria-label="Post on X">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.66l-5.21-6.82-5.97 6.82H1.66l7.73-8.84L1.25 2.25h6.83l4.71 6.23zm-1.16 17.52h1.83L7.08 4.13H5.11z"/></svg>
                <span class="st-label">Post</span>
            </button>
            <button type="button" class="st-btn" data-network="facebook" aria-label="Share on Facebook">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
                <span class="st-label">Share</span>
            </button>
            <button type="button" class="st-btn" data-network="reddit" aria-label="Share on Reddit">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M24 11.78a2.6 2.6 0 00-4.4-1.86 12.78 12.78 0 00-6.96-2.23l1.18-5.56 3.85.82a1.86 1.86 0 103.7-.24 1.86 1.86 0 00-3.53-.82l-4.3-.91a.45.45 0 00-.54.35L11.68 7.7a12.8 12.8 0 00-7.08 2.22 2.6 2.6 0 10-2.87 4.26 5.2 5.2 0 000 .8c0 4.06 4.73 7.36 10.56 7.36s10.56-3.3 10.56-7.36a5.2 5.2 0 000-.8A2.6 2.6 0 0024 11.78zM6 13.64a1.86 1.86 0 113.72 0 1.86 1.86 0 01-3.72 0zm10.4 4.92a6.88 6.88 0 01-4.4 1.36 6.88 6.88 0 01-4.4-1.36.45.45 0 11.6-.67 6 6 0 003.8 1.13 6 6 0 003.8-1.13.45.45 0 11.6.67zm-.27-3.06a1.86 1.86 0 111.86-1.86 1.86 1.86 0 01-1.86 1.86z"/></svg>
                <span class="st-label">Share</span>
            </button>
            <button type="button" class="st-btn st-last st-remove-label" data-network="native" aria-label="More sharing options">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81a3 3 0 100-6 3 3 0 00-3 3c0 .24.04.47.09.7L8.04 9.81A2.99 2.99 0 003 12a3 3 0 005.04 2.19l7.12 4.16c-.05.21-.08.43-.08.65a2.92 2.92 0 105.92 0 2.92 2.92 0 00-2.92-2.92z"/></svg>
                <span class="st-label">Share</span>
            </button>
        </div>
        <div class="clearfix"></div>
    </div>
    </div></div>
    <div class="container">
        <div id="main-content">

            
            
            <section class="block_area block_area-comment" id="comment-block">
                <div class="block_area-header justify-content-start">
                    <div class="bah-heading mr-4">
                        <h2 class="cat-heading">Comments</h2>
                    </div>
                                        <a class="guidelines" href="https://hianime.at/index.php/news/rules-our-code-of-conduct" target="_blank">
                        Community Guidelines <span>NEW</span>
                    </a>
                                    </div>
                <div class="show-comments">
                    
                                        <div data-react-widget="comments" data-widget-lazy="viewport" data-props="{&quot;animeId&quot;:100,&quot;episodeId&quot;:2928,&quot;episodeNumber&quot;:1,&quot;restUrl&quot;:&quot;https:\/\/hianime.at\/api\/theme\/&quot;,&quot;commentsDisabled&quot;:false}">
                        <div class="loading-relative">
                            <div class="loading">
                                <div class="span1"></div>
                                <div class="span2"></div>
                                <div class="span3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

                        <section class="block_area block_area_home">
                <div class="block_area-header">
                    <div class="float-left bah-heading mr-4">
                        <h2 class="cat-heading">Recommended For You</h2>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="tab-content">
                    <div class="block_area-content block_area-list film_list film_list-grid">
                        <div class="film_list-wrap">
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>12</div>
                                                <div class="tick-item tick-eps">12</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/08/nora-princess-and-stray-cat.webp"
            class="film-poster-img"
            alt="Nora, Princess, and Stray Cat"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=nora-princess-and-stray-cat-1489"
            class="film-poster-ahref item-qtip"
            title="Nora, Princess, and Stray Cat"
            aria-label="Watch Nora, Princess, and Stray Cat"
            data-id="1489">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/nora-princess-and-stray-cat-1489"
                title="Nora, Princess, and Stray Cat"
                class="dynamic-name"
                data-jname="Nora to Oujo to Noraneko Heart">
                Nora, Princess, and Stray Cat
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">TV</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">3m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>1</div>
                                                <div class="tick-item tick-eps">1</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/08/stand-my-heroes-warmth-of-memories.webp"
            class="film-poster-img"
            alt="Stand My Heroes: Warmth of Memories"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=stand-my-heroes-warmth-of-memories-4790"
            class="film-poster-ahref item-qtip"
            title="Stand My Heroes: Warmth of Memories"
            aria-label="Watch Stand My Heroes: Warmth of Memories"
            data-id="4790">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/stand-my-heroes-warmth-of-memories-4790"
                title="Stand My Heroes: Warmth of Memories"
                class="dynamic-name"
                data-jname="Stand My Heroes: Warmth of Memories">
                Stand My Heroes: Warmth of Memories
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">OVA</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">60m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>12</div>
                                                <div class="tick-item tick-eps">3</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/08/bongchon-bride.webp"
            class="film-poster-img"
            alt="Bongchon Bride"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=bongchon-bride-5761"
            class="film-poster-ahref item-qtip"
            title="Bongchon Bride"
            aria-label="Watch Bongchon Bride"
            data-id="5761">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/bongchon-bride-5761"
                title="Bongchon Bride"
                class="dynamic-name"
                data-jname="Bongchon Gaksi">
                Bongchon Bride
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">ONA</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">5m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>26</div>
                                    <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>26</div>
                                    <div class="tick-item tick-eps">26</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/09/saber-marionette-j-to-x.webp"
            class="film-poster-img"
            alt="Saber Marionette J to X"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=saber-marionette-j-to-x-7662"
            class="film-poster-ahref item-qtip"
            title="Saber Marionette J to X"
            aria-label="Watch Saber Marionette J to X"
            data-id="7662">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/saber-marionette-j-to-x-7662"
                title="Saber Marionette J to X"
                class="dynamic-name"
                data-jname="Saber Marionette J to X">
                Saber Marionette J to X
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">TV</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">25m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>1</div>
                                                <div class="tick-item tick-eps">1</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/08/detective-conan-movie-09-strategy-above-the-depths-recap.webp"
            class="film-poster-img"
            alt="Detective Conan Movie 09: Promo Special"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=detective-conan-movie-09-strategy-above-the-depths-recap-5259"
            class="film-poster-ahref item-qtip"
            title="Detective Conan Movie 09: Promo Special"
            aria-label="Watch Detective Conan Movie 09: Promo Special"
            data-id="5259">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/detective-conan-movie-09-strategy-above-the-depths-recap-5259"
                title="Detective Conan Movie 09: Promo Special"
                class="dynamic-name"
                data-jname="Detective Conan Movie 09: Promo Special">
                Detective Conan Movie 09: Promo Special
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">SPECIAL</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">23m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>10</div>
                                    <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>10</div>
                                    <div class="tick-item tick-eps">10</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/08/aggretsuko-ona-4th-season.webp"
            class="film-poster-img"
            alt="Aggretsuko (ONA) 4th Season"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=aggretsuko-ona-4th-season-4326"
            class="film-poster-ahref item-qtip"
            title="Aggretsuko (ONA) 4th Season"
            aria-label="Watch Aggretsuko (ONA) 4th Season"
            data-id="4326">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/aggretsuko-ona-4th-season-4326"
                title="Aggretsuko (ONA) 4th Season"
                class="dynamic-name"
                data-jname="Aggressive Retsuko (ONA) 4th Season">
                Aggretsuko (ONA) 4th Season
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">ONA</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">18m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>3</div>
                                                <div class="tick-item tick-eps">3</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/08/fire-emblem-heroes-chibi-playhouse.webp"
            class="film-poster-img"
            alt="Fire Emblem Heroes: Chibi Playhouse"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=fire-emblem-heroes-chibi-playhouse-5697"
            class="film-poster-ahref item-qtip"
            title="Fire Emblem Heroes: Chibi Playhouse"
            aria-label="Watch Fire Emblem Heroes: Chibi Playhouse"
            data-id="5697">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/fire-emblem-heroes-chibi-playhouse-5697"
                title="Fire Emblem Heroes: Chibi Playhouse"
                class="dynamic-name"
                data-jname="Fire Emblem Heroes: Chibi Playhouse">
                Fire Emblem Heroes: Chibi Playhouse
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">ONA</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">4m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>12</div>
                                    <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>12</div>
                                    <div class="tick-item tick-eps">12</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/08/hundred.webp"
            class="film-poster-img"
            alt="Hundred"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=hundred-1750"
            class="film-poster-ahref item-qtip"
            title="Hundred"
            aria-label="Watch Hundred"
            data-id="1750">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/hundred-1750"
                title="Hundred"
                class="dynamic-name"
                data-jname="Hundred">
                Hundred
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">TV</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">24m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>12</div>
                                                <div class="tick-item tick-eps">12</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/08/tearmoon-empire.webp"
            class="film-poster-img"
            alt="Tearmoon Empire"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=tearmoon-empire-4321"
            class="film-poster-ahref item-qtip"
            title="Tearmoon Empire"
            aria-label="Watch Tearmoon Empire"
            data-id="4321">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/tearmoon-empire-4321"
                title="Tearmoon Empire"
                class="dynamic-name"
                data-jname="Tearmoon Teikoku Monogatari: Dantoudai kara Hajimaru, Hime no Tensei Gyakuten Story">
                Tearmoon Empire
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">TV</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">23m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>1</div>
                                                <div class="tick-item tick-eps">1</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/08/chillin-in-my-30s-after-getting-fired-from-the-demon-kings-army-mini-anime-episode-0.webp"
            class="film-poster-img"
            alt="Chillin&#039; in My 30s after Getting Fired from the Demon King&#039;s Army Mini Anime Episode 0"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=chillin-in-my-30s-after-getting-fired-from-the-demon-kings-army-mini-anime-episode-0-5230"
            class="film-poster-ahref item-qtip"
            title="Chillin&#039; in My 30s after Getting Fired from the Demon King&#039;s Army Mini Anime Episode 0"
            aria-label="Watch Chillin&#039; in My 30s after Getting Fired from the Demon King&#039;s Army Mini Anime Episode 0"
            data-id="5230">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/chillin-in-my-30s-after-getting-fired-from-the-demon-kings-army-mini-anime-episode-0-5230"
                title="Chillin&#039; in My 30s after Getting Fired from the Demon King&#039;s Army Mini Anime Episode 0"
                class="dynamic-name"
                data-jname="Kaiko sareta Ankoku Heishi (30-dai) no Slow na Second Life Mini Anime Episode 0">
                Chillin&#039; in My 30s after Getting Fired from the Demon King&#039;s Army Mini Anime Episode 0
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">ONA</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">2m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick tick-rate">18+</div>
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>3</div>
                                    <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>3</div>
                                    <div class="tick-item tick-eps">3</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/09/hyper-speed-grandoll.webp"
            class="film-poster-img"
            alt="Hyper Speed GranDoll"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=hyper-speed-grandoll-9126"
            class="film-poster-ahref item-qtip"
            title="Hyper Speed GranDoll"
            aria-label="Watch Hyper Speed GranDoll"
            data-id="9126">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/hyper-speed-grandoll-9126"
                title="Hyper Speed GranDoll"
                class="dynamic-name"
                data-jname="Chou Kousoku Gran Doll">
                Hyper Speed GranDoll
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">OVA</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">29m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                        <div class="flw-item ">
    <div class="film-poster">
                <div class="tick ltr">
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>1</div>
                                    <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>1</div>
                                    <div class="tick-item tick-eps">1</div>
                    </div>
        
        <img src="https://hianime.at/storage/media/2026/08/doraemon-the-movie-nobita-and-the-birth-of-japan-2016.webp"
            class="film-poster-img"
            alt="Doraemon the Movie: Nobita and the Birth of Japan 2016"
            loading="lazy"
            decoding="async">
        <a href="watch.html?anime=doraemon-the-movie-nobita-and-the-birth-of-japan-2016-6114"
            class="film-poster-ahref item-qtip"
            title="Doraemon the Movie: Nobita and the Birth of Japan 2016"
            aria-label="Watch Doraemon the Movie: Nobita and the Birth of Japan 2016"
            data-id="6114">
            <i class="fas fa-play" aria-hidden="true"></i>
        </a>
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="https://hianime.at/doraemon-the-movie-nobita-and-the-birth-of-japan-2016-6114"
                title="Doraemon the Movie: Nobita and the Birth of Japan 2016"
                class="dynamic-name"
                data-jname="Doraemon Movie 36: Shin Nobita no Nippon Tanjou">
                Doraemon the Movie: Nobita and the Birth of Japan 2016
            </a>
        </h3>
                <div class="fd-infor">
            <span class="fdi-item">MOVIE</span>
                        <span class="dot"></span>
            <span class="fdi-item fdi-duration">104m</span>
                    </div>
    </div>
    <div class="clearfix"></div>
</div>
                                                    </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </section>
            <div class="clearfix"></div>
                    </div>
        <div id="main-sidebar">
            <section class="block_area block_area_sidebar block_area-realtime">
    <div class="block_area-header">
        <div class="float-left bah-heading mr-4">
            <h2 class="cat-heading">Most Popular</h2>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="block_area-content">
        <div class="cbox cbox-list cbox-realtime">
            <div class="cbox-content">
                <div class="anif-block-ul">
                    <ul class="ulclear">
                                                <li>
    <div class="film-poster item-qtip" data-id="240">
        <img src="https://hianime.at/storage/media/2026/08/attack-on-titan.webp" class="film-poster-img" alt="Attack on Titan" loading="lazy" decoding="async">
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="watch.html?anime=attack-on-titan-240" title="Attack on Titan" class="dynamic-name" data-jname="Shingeki no Kyojin">Attack on Titan</a>
        </h3>
        <div class="fd-infor mt-2">
            <div class="tick">
                                <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>25</div>
                                                <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>25</div>
                                                <div class="tick-item tick-eps">25</div>
                                <div class="dot"></div>
                TV
            </div>
        </div>
    </div>
    <div class="film-fav" id="wl-list-240">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="film-fav-toggle"><i class="fas fa-plus"></i></a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="wl-item dropdown-item"
            data-type="1"
            data-animeid="240"
                        href="javascript:;">Watching</a>
                <a class="wl-item dropdown-item"
            data-type="2"
            data-animeid="240"
                        href="javascript:;">On-Hold</a>
                <a class="wl-item dropdown-item"
            data-type="3"
            data-animeid="240"
                        href="javascript:;">Plan to Watch</a>
                <a class="wl-item dropdown-item"
            data-type="4"
            data-animeid="240"
                        href="javascript:;">Dropped</a>
                <a class="wl-item dropdown-item"
            data-type="5"
            data-animeid="240"
                        href="javascript:;">Completed</a>
                    </div>
</div>
    <div class="clearfix"></div>
</li>
                                                <li>
    <div class="film-poster item-qtip" data-id="1064">
        <img src="https://hianime.at/storage/media/2026/08/death-note.webp" class="film-poster-img" alt="Death Note" loading="lazy" decoding="async">
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="watch.html?anime=death-note-1064" title="Death Note" class="dynamic-name" data-jname="Death Note">Death Note</a>
        </h3>
        <div class="fd-infor mt-2">
            <div class="tick">
                                <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>37</div>
                                                <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>37</div>
                                                <div class="tick-item tick-eps">37</div>
                                <div class="dot"></div>
                TV
            </div>
        </div>
    </div>
    <div class="film-fav" id="wl-list-1064">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="film-fav-toggle"><i class="fas fa-plus"></i></a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="wl-item dropdown-item"
            data-type="1"
            data-animeid="1064"
                        href="javascript:;">Watching</a>
                <a class="wl-item dropdown-item"
            data-type="2"
            data-animeid="1064"
                        href="javascript:;">On-Hold</a>
                <a class="wl-item dropdown-item"
            data-type="3"
            data-animeid="1064"
                        href="javascript:;">Plan to Watch</a>
                <a class="wl-item dropdown-item"
            data-type="4"
            data-animeid="1064"
                        href="javascript:;">Dropped</a>
                <a class="wl-item dropdown-item"
            data-type="5"
            data-animeid="1064"
                        href="javascript:;">Completed</a>
                    </div>
</div>
    <div class="clearfix"></div>
</li>
                                                <li>
    <div class="film-poster item-qtip" data-id="1140">
        <img src="https://hianime.at/storage/media/2026/08/fullmetal-alchemist-brotherhood.webp" class="film-poster-img" alt="Fullmetal Alchemist: Brotherhood" loading="lazy" decoding="async">
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="watch.html?anime=fullmetal-alchemist-brotherhood-1140" title="Fullmetal Alchemist: Brotherhood" class="dynamic-name" data-jname="Fullmetal Alchemist: Brotherhood">Fullmetal Alchemist: Brotherhood</a>
        </h3>
        <div class="fd-infor mt-2">
            <div class="tick">
                                <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>64</div>
                                                <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>64</div>
                                                <div class="tick-item tick-eps">64</div>
                                <div class="dot"></div>
                TV
            </div>
        </div>
    </div>
    <div class="film-fav" id="wl-list-1140">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="film-fav-toggle"><i class="fas fa-plus"></i></a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="wl-item dropdown-item"
            data-type="1"
            data-animeid="1140"
                        href="javascript:;">Watching</a>
                <a class="wl-item dropdown-item"
            data-type="2"
            data-animeid="1140"
                        href="javascript:;">On-Hold</a>
                <a class="wl-item dropdown-item"
            data-type="3"
            data-animeid="1140"
                        href="javascript:;">Plan to Watch</a>
                <a class="wl-item dropdown-item"
            data-type="4"
            data-animeid="1140"
                        href="javascript:;">Dropped</a>
                <a class="wl-item dropdown-item"
            data-type="5"
            data-animeid="1140"
                        href="javascript:;">Completed</a>
                    </div>
</div>
    <div class="clearfix"></div>
</li>
                                                <li>
    <div class="film-poster item-qtip" data-id="1386">
        <img src="https://hianime.at/storage/media/2026/08/one-punch-man.webp" class="film-poster-img" alt="One Punch Man" loading="lazy" decoding="async">
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="watch.html?anime=one-punch-man-1386" title="One Punch Man" class="dynamic-name" data-jname="One Punch Man">One Punch Man</a>
        </h3>
        <div class="fd-infor mt-2">
            <div class="tick">
                                <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>12</div>
                                                <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>12</div>
                                                <div class="tick-item tick-eps">12</div>
                                <div class="dot"></div>
                TV
            </div>
        </div>
    </div>
    <div class="film-fav" id="wl-list-1386">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="film-fav-toggle"><i class="fas fa-plus"></i></a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="wl-item dropdown-item"
            data-type="1"
            data-animeid="1386"
                        href="javascript:;">Watching</a>
                <a class="wl-item dropdown-item"
            data-type="2"
            data-animeid="1386"
                        href="javascript:;">On-Hold</a>
                <a class="wl-item dropdown-item"
            data-type="3"
            data-animeid="1386"
                        href="javascript:;">Plan to Watch</a>
                <a class="wl-item dropdown-item"
            data-type="4"
            data-animeid="1386"
                        href="javascript:;">Dropped</a>
                <a class="wl-item dropdown-item"
            data-type="5"
            data-animeid="1386"
                        href="javascript:;">Completed</a>
                    </div>
</div>
    <div class="clearfix"></div>
</li>
                                                <li>
    <div class="film-poster item-qtip" data-id="843">
        <img src="https://hianime.at/storage/media/2026/08/demon-slayer-kimetsu-no-yaiba.webp" class="film-poster-img" alt="Demon Slayer: Kimetsu no Yaiba" loading="lazy" decoding="async">
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="watch.html?anime=demon-slayer-kimetsu-no-yaiba-843" title="Demon Slayer: Kimetsu no Yaiba" class="dynamic-name" data-jname="Kimetsu no Yaiba">Demon Slayer: Kimetsu no Yaiba</a>
        </h3>
        <div class="fd-infor mt-2">
            <div class="tick">
                                <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>26</div>
                                                <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>26</div>
                                                <div class="tick-item tick-eps">26</div>
                                <div class="dot"></div>
                TV
            </div>
        </div>
    </div>
    <div class="film-fav" id="wl-list-843">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="film-fav-toggle"><i class="fas fa-plus"></i></a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="wl-item dropdown-item"
            data-type="1"
            data-animeid="843"
                        href="javascript:;">Watching</a>
                <a class="wl-item dropdown-item"
            data-type="2"
            data-animeid="843"
                        href="javascript:;">On-Hold</a>
                <a class="wl-item dropdown-item"
            data-type="3"
            data-animeid="843"
                        href="javascript:;">Plan to Watch</a>
                <a class="wl-item dropdown-item"
            data-type="4"
            data-animeid="843"
                        href="javascript:;">Dropped</a>
                <a class="wl-item dropdown-item"
            data-type="5"
            data-animeid="843"
                        href="javascript:;">Completed</a>
                    </div>
</div>
    <div class="clearfix"></div>
</li>
                                                <li>
    <div class="film-poster item-qtip" data-id="1395">
        <img src="https://hianime.at/storage/media/2026/08/my-hero-academia.webp" class="film-poster-img" alt="My Hero Academia" loading="lazy" decoding="async">
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="watch.html?anime=my-hero-academia-1395" title="My Hero Academia" class="dynamic-name" data-jname="Boku no Hero Academia">My Hero Academia</a>
        </h3>
        <div class="fd-infor mt-2">
            <div class="tick">
                                <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>13</div>
                                                <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>13</div>
                                                <div class="tick-item tick-eps">13</div>
                                <div class="dot"></div>
                TV
            </div>
        </div>
    </div>
    <div class="film-fav" id="wl-list-1395">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="film-fav-toggle"><i class="fas fa-plus"></i></a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="wl-item dropdown-item"
            data-type="1"
            data-animeid="1395"
                        href="javascript:;">Watching</a>
                <a class="wl-item dropdown-item"
            data-type="2"
            data-animeid="1395"
                        href="javascript:;">On-Hold</a>
                <a class="wl-item dropdown-item"
            data-type="3"
            data-animeid="1395"
                        href="javascript:;">Plan to Watch</a>
                <a class="wl-item dropdown-item"
            data-type="4"
            data-animeid="1395"
                        href="javascript:;">Dropped</a>
                <a class="wl-item dropdown-item"
            data-type="5"
            data-animeid="1395"
                        href="javascript:;">Completed</a>
                    </div>
</div>
    <div class="clearfix"></div>
</li>
                                                <li>
    <div class="film-poster item-qtip" data-id="874">
        <img src="https://hianime.at/storage/media/2026/08/sword-art-online.webp" class="film-poster-img" alt="Sword Art Online" loading="lazy" decoding="async">
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="watch.html?anime=sword-art-online-874" title="Sword Art Online" class="dynamic-name" data-jname="Sword Art Online">Sword Art Online</a>
        </h3>
        <div class="fd-infor mt-2">
            <div class="tick">
                                <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>25</div>
                                                <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>25</div>
                                                <div class="tick-item tick-eps">25</div>
                                <div class="dot"></div>
                TV
            </div>
        </div>
    </div>
    <div class="film-fav" id="wl-list-874">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="film-fav-toggle"><i class="fas fa-plus"></i></a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="wl-item dropdown-item"
            data-type="1"
            data-animeid="874"
                        href="javascript:;">Watching</a>
                <a class="wl-item dropdown-item"
            data-type="2"
            data-animeid="874"
                        href="javascript:;">On-Hold</a>
                <a class="wl-item dropdown-item"
            data-type="3"
            data-animeid="874"
                        href="javascript:;">Plan to Watch</a>
                <a class="wl-item dropdown-item"
            data-type="4"
            data-animeid="874"
                        href="javascript:;">Dropped</a>
                <a class="wl-item dropdown-item"
            data-type="5"
            data-animeid="874"
                        href="javascript:;">Completed</a>
                    </div>
</div>
    <div class="clearfix"></div>
</li>
                                                <li>
    <div class="film-poster item-qtip" data-id="1393">
        <img src="https://hianime.at/storage/media/2026/08/hunter-x-hunter-1.webp" class="film-poster-img" alt="Hunter x Hunter" loading="lazy" decoding="async">
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="watch.html?anime=hunter-x-hunter-1-1393" title="Hunter x Hunter" class="dynamic-name" data-jname="Hunter x Hunter (2011)">Hunter x Hunter</a>
        </h3>
        <div class="fd-infor mt-2">
            <div class="tick">
                                <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>148</div>
                                                <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>148</div>
                                                <div class="tick-item tick-eps">148</div>
                                <div class="dot"></div>
                TV
            </div>
        </div>
    </div>
    <div class="film-fav" id="wl-list-1393">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="film-fav-toggle"><i class="fas fa-plus"></i></a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="wl-item dropdown-item"
            data-type="1"
            data-animeid="1393"
                        href="javascript:;">Watching</a>
                <a class="wl-item dropdown-item"
            data-type="2"
            data-animeid="1393"
                        href="javascript:;">On-Hold</a>
                <a class="wl-item dropdown-item"
            data-type="3"
            data-animeid="1393"
                        href="javascript:;">Plan to Watch</a>
                <a class="wl-item dropdown-item"
            data-type="4"
            data-animeid="1393"
                        href="javascript:;">Dropped</a>
                <a class="wl-item dropdown-item"
            data-type="5"
            data-animeid="1393"
                        href="javascript:;">Completed</a>
                    </div>
</div>
    <div class="clearfix"></div>
</li>
                                                <li>
    <div class="film-poster item-qtip" data-id="1335">
        <img src="https://hianime.at/storage/media/2026/08/naruto.webp" class="film-poster-img" alt="Naruto" loading="lazy" decoding="async">
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="watch.html?anime=naruto-1335" title="Naruto" class="dynamic-name" data-jname="Naruto">Naruto</a>
        </h3>
        <div class="fd-infor mt-2">
            <div class="tick">
                                <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>220</div>
                                                <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>220</div>
                                                <div class="tick-item tick-eps">220</div>
                                <div class="dot"></div>
                TV
            </div>
        </div>
    </div>
    <div class="film-fav" id="wl-list-1335">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="film-fav-toggle"><i class="fas fa-plus"></i></a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="wl-item dropdown-item"
            data-type="1"
            data-animeid="1335"
                        href="javascript:;">Watching</a>
                <a class="wl-item dropdown-item"
            data-type="2"
            data-animeid="1335"
                        href="javascript:;">On-Hold</a>
                <a class="wl-item dropdown-item"
            data-type="3"
            data-animeid="1335"
                        href="javascript:;">Plan to Watch</a>
                <a class="wl-item dropdown-item"
            data-type="4"
            data-animeid="1335"
                        href="javascript:;">Dropped</a>
                <a class="wl-item dropdown-item"
            data-type="5"
            data-animeid="1335"
                        href="javascript:;">Completed</a>
                    </div>
</div>
    <div class="clearfix"></div>
</li>
                                                <li>
    <div class="film-poster item-qtip" data-id="237">
        <img src="https://hianime.at/storage/media/2026/08/jujutsu-kaisen.webp" class="film-poster-img" alt="Jujutsu Kaisen (TV)" loading="lazy" decoding="async">
    </div>
    <div class="film-detail">
        <h3 class="film-name">
            <a href="watch.html?anime=jujutsu-kaisen-237" title="Jujutsu Kaisen (TV)" class="dynamic-name" data-jname="Jujutsu Kaisen (TV)">Jujutsu Kaisen (TV)</a>
        </h3>
        <div class="fd-infor mt-2">
            <div class="tick">
                                <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i>24</div>
                                                <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i>24</div>
                                                <div class="tick-item tick-eps">24</div>
                                <div class="dot"></div>
                TV
            </div>
        </div>
    </div>
    <div class="film-fav" id="wl-list-237">
    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="film-fav-toggle"><i class="fas fa-plus"></i></a>
    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="wl-item dropdown-item"
            data-type="1"
            data-animeid="237"
                        href="javascript:;">Watching</a>
                <a class="wl-item dropdown-item"
            data-type="2"
            data-animeid="237"
                        href="javascript:;">On-Hold</a>
                <a class="wl-item dropdown-item"
            data-type="3"
            data-animeid="237"
                        href="javascript:;">Plan to Watch</a>
                <a class="wl-item dropdown-item"
            data-type="4"
            data-animeid="237"
                        href="javascript:;">Dropped</a>
                <a class="wl-item dropdown-item"
            data-type="5"
            data-animeid="237"
                        href="javascript:;">Completed</a>
                    </div>
</div>
    <div class="clearfix"></div>
</li>
                                            </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</section>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

<div class="modal fade premodal" id="reportmodal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report an issue</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="preform" id="report-form">
                    <div class="form-group">
                        <label class="prelabel">What is wrong?</label>
                                                <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="report-video_broken" name="issues[]" value="video_broken">
                            <label class="custom-control-label" for="report-video_broken">Video not playing / broken</label>
                        </div>
                                                <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="report-audio_sync" name="issues[]" value="audio_sync">
                            <label class="custom-control-label" for="report-audio_sync">Audio out of sync</label>
                        </div>
                                                <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="report-sub_sync" name="issues[]" value="sub_sync">
                            <label class="custom-control-label" for="report-sub_sync">Subtitles out of sync</label>
                        </div>
                                                <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="report-skiptime" name="issues[]" value="skiptime">
                            <label class="custom-control-label" for="report-skiptime">Wrong intro / outro skip time</label>
                        </div>
                                                <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="report-other" name="issues[]" value="other">
                            <label class="custom-control-label" for="report-other">Something else</label>
                        </div>
                                            </div>
                    <div class="form-group">
                        <label class="prelabel">Details</label>
                        <textarea class="form-control" name="message" rows="3" maxlength="1000" placeholder="Which server, quality or timestamp is affected?"></textarea>
                    </div>
                    <div id="report-captcha" data-captcha-visible="1"></div>
                    <div class="form-group login-btn mb-0">
                        <button class="btn btn-primary btn-block" id="btn-report">Send Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

        <div class="container">
                    </div>`;

    setTimeout(() => {
        initWatchInteractivity(anime, currentEp);
    }, 50);
}

function initWatchInteractivity(anime, currentEp) {
    // 1. Dynamic Anime Info update
    const titleEl = document.querySelector('.anisc-detail .film-name');
    if (titleEl) titleEl.innerText = anime.title;

    const bannerCover = document.querySelector('.anis-cover');
    if (bannerCover && anime.banner) {
        bannerCover.style.backgroundImage = `url(${anime.banner})`;
    }

    const posterImg = document.querySelector('.anisc-poster .film-poster img');
    if (posterImg && anime.poster) {
        posterImg.src = anime.poster;
    }

    const descEl = document.querySelector('.item-description');
    if (descEl && anime.synopsis) {
        descEl.innerText = anime.synopsis;
    }

    // 2. Server selection
    document.querySelectorAll('.btn-server').forEach(btn => {
        btn.addEventListener('click', function() {
            const group = this.closest('.ps__-list');
            if (group) {
                group.querySelectorAll('.btn-server').forEach(b => {
                    b.classList.remove('active');
                    if (b.innerText.includes('Hindi')) {
                        b.style.background = 'rgba(255,255,255,0.08)';
                        b.style.color = '#fff';
                    }
                });
            }
            this.classList.add('active');
            this.style.background = '#ffbade';
            this.style.color = '#111';
        });
    });

    // 3. Episode selection
    const epBtns = document.querySelectorAll('.ep-item');
    const epDisplay = document.getElementById('current-ep-num');
    epBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            epBtns.forEach(b => {
                b.classList.remove('active');
                b.style.background = 'rgba(255,255,255,0.07)';
                b.style.color = '#fff';
            });
            this.classList.add('active');
            this.style.background = '#ffbade';
            this.style.color = '#111';

            const ep = this.getAttribute('data-number');
            if (epDisplay) epDisplay.innerText = ep;

            const url = new URL(window.location);
            url.searchParams.set('ep', ep);
            window.history.replaceState({}, '', url);
        });
    });

    // 4. Episode search input
    const epSearch = document.getElementById('search-ep');
    if (epSearch) {
        epSearch.addEventListener('input', function() {
            const val = this.value.trim().toLowerCase();
            epBtns.forEach(btn => {
                const num = btn.getAttribute('data-number');
                const parent = btn.closest('.item');
                if (!val || num.includes(val)) {
                    if (parent) parent.style.display = 'inline-block';
                    else btn.style.display = 'inline-block';
                } else {
                    if (parent) parent.style.display = 'none';
                    else btn.style.display = 'none';
                }
            });
        });
    }

    // 5. Discussion comment posting
    const postBtn = document.getElementById('post-comment-btn');
    const commentBox = document.getElementById('comment-text-box');
    const commentsList = document.getElementById('comments-list-box');
    if (postBtn && commentBox && commentsList) {
        postBtn.addEventListener('click', () => {
            const text = commentBox.value.trim();
            if (!text) return;
            const newDiv = document.createElement('div');
            newDiv.className = 'comment-item d-flex mb-3 pb-3';
            newDiv.style.borderBottom = '1px solid rgba(255,255,255,0.06)';
            newDiv.innerHTML = `
                <div class="avatar mr-3" style="width:42px; height:42px; border-radius:50%; background:#ffbade; color:#111; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">Y</div>
                <div class="content flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong style="color:#fff; font-size:14px;">You <span class="badge badge-pill badge-primary ml-2" style="background:#ffbade; color:#111; font-size:10px;">Just Now</span></strong>
                        <span class="text-muted" style="font-size:12px;">Moments ago</span>
                    </div>
                    <p class="mb-1 text-light" style="font-size:13.5px;">${text}</p>
                </div>
            `;
            commentsList.prepend(newDiv);
            commentBox.value = '';
        });
    }
}
