// Exact HiAnime 1:1 Navbar and Mobile Sidebar Drawer

import { animeApi } from '../../api/animeApi.js';
import { debounce } from '../../utils/helpers.js';

export function renderHeader() {
    return `
    <nav id="sidebar_menu" role="navigation" aria-label="Mobile menu">
        <button class="btn btn-radius btn-sm btn-secondary toggle-sidebar" aria-label="Close menu"><i class="fas fa-angle-left mr-2"></i>Close
            menu</button>
        <div class="sb-setting">
            <div class="header-setting">
                <div class="hs-toggles">
                    <a href="https://hianime.at/watch2gather" class="hst-item" title="Watch with friends">
                        <div class="hst-icon"><i class="zicon zicon-20 zicon-live"></i></div>
                        <div class="name"><span>W.2.G</span></div>
                    </a>
                    <a href="https://hianime.at/random" class="hst-item" title="Watch random anime">
                        <div class="hst-icon"><i class="fas fa-random"></i></div>
                        <div class="name"><span>Random</span></div>
                    </a>
                    <div class="hst-item" title="Select language of anime name to display.">
                        <div class="select-anime-name toggle-lang"><span class="en">EN</span><span class="jp">JP</span>
                        </div>
                        <div class="name"><span>Name</span></div>
                    </div>
                    <a href="https://hianime.at/news" class="hst-item" title="HiAnime News">
                        <div class="hst-icon"><i class="fas fa-rss"></i></div>
                        <div class="name"><span>News</span></div>
                    </a>
                </div>
            </div>
        </div>
        <div class="sb-donate">
            <div class="hr-community"><a class="btn btn-sm" href="https://hianime.at/community" title="HiAnime Community"><i
                        class="fas fa-comments mr-2"></i>Community</a></div>
        </div>
        <ul class="nav sidebar_menu-list">
            <li class="nav-item active"><a class="nav-link" href="index.html" title="Home">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="browse.html" title="Subbed Anime">Subbed Anime</a></li>
            <li class="nav-item"><a class="nav-link" href="browse.html" title="Dubbed Anime">Dubbed Anime</a></li>
            <li class="nav-item"><a class="nav-link" href="browse.html" title="Most Popular">Most Popular</a></li>
            <li class="nav-item"><a class="nav-link" href="browse.html" title="Movies">Movies</a></li>
            <li class="nav-item"><a class="nav-link" href="browse.html" title="TV Series">TV Series</a></li>
            <li class="nav-item"><a class="nav-link" href="browse.html" title="OVAs">OVAs</a></li>
            <li class="nav-item"><a class="nav-link" href="browse.html" title="ONAs">ONAs</a></li>
            <li class="nav-item"><a class="nav-link" href="browse.html" title="Specials">Specials</a></li>
            <li class="nav-item">
                <div class="nav-link" title="Genre"><strong>Genre</strong></div>
                <div class="sidebar_menu-sub show" id="sidebar_subs_genre">
                    <ul class="nav color-list sub-menu">
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/action"
                                title="Action">
                                Action
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/action-adventure"
                                title="Action &amp; Adventure">
                                Action &amp; Adventure
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/action-adventure-supernatural"
                                title="Action , Adventure , Supernatural">
                                Action , Adventure , Supernatural
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/adult-cast"
                                title="Adult Cast">
                                Adult Cast
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/adventure"
                                title="Adventure">
                                Adventure
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/animation"
                                title="Animation">
                                Animation
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/anthropomorphic"
                                title="Anthropomorphic">
                                Anthropomorphic
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/avant-garde"
                                title="Avant Garde">
                                Avant Garde
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/award-winning"
                                title="Award Winning">
                                Award Winning
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/boys-love"
                                title="Boys Love">
                                Boys Love
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/cars"
                                title="Cars">
                                Cars
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/cgdct"
                                title="CGDCT">
                                CGDCT
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/childcare"
                                title="Childcare">
                                Childcare
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/combat-sports"
                                title="Combat Sports">
                                Combat Sports
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/comedy"
                                title="Comedy">
                                Comedy
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/crossdressing"
                                title="Crossdressing">
                                Crossdressing
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/delinquents"
                                title="Delinquents">
                                Delinquents
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/dementia"
                                title="Dementia">
                                Dementia
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/demons"
                                title="Demons">
                                Demons
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/detective"
                                title="Detective">
                                Detective
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/drama"
                                title="Drama">
                                Drama
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/drama-action-adventure-animation"
                                title="Drama , Action &amp; Adventure , Animation">
                                Drama , Action &amp; Adventure , Animation
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/ecchi"
                                title="Ecchi">
                                Ecchi
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/educational"
                                title="Educational">
                                Educational
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/erotica"
                                title="Erotica">
                                Erotica
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/fantasy"
                                title="Fantasy">
                                Fantasy
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/gag-humor"
                                title="Gag Humor">
                                Gag Humor
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/game"
                                title="Game">
                                Game
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/girls-love"
                                title="Girls Love">
                                Girls Love
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/gore"
                                title="Gore">
                                Gore
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/gourmet"
                                title="Gourmet">
                                Gourmet
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/harem"
                                title="Harem">
                                Harem
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/hentai"
                                title="Hentai">
                                Hentai
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/high-stakes-game"
                                title="High Stakes Game">
                                High Stakes Game
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/historical"
                                title="Historical">
                                Historical
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/horror"
                                title="Horror">
                                Horror
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/idols-female"
                                title="Idols (Female)">
                                Idols (Female)
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/idols-male"
                                title="Idols (Male)">
                                Idols (Male)
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/isekai"
                                title="Isekai">
                                Isekai
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/iyashikei"
                                title="Iyashikei">
                                Iyashikei
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/josei"
                                title="Josei">
                                Josei
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/kids"
                                title="Kids">
                                Kids
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/love-polygon"
                                title="Love Polygon">
                                Love Polygon
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/love-status-quo"
                                title="Love Status Quo">
                                Love Status Quo
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/magic"
                                title="Magic">
                                Magic
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/magical-sex-shift"
                                title="Magical Sex Shift">
                                Magical Sex Shift
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/mahou-shoujo"
                                title="Mahou Shoujo">
                                Mahou Shoujo
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/martial-arts"
                                title="Martial Arts">
                                Martial Arts
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/mecha"
                                title="Mecha">
                                Mecha
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/medical"
                                title="Medical">
                                Medical
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/military"
                                title="Military">
                                Military
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/music"
                                title="Music">
                                Music
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/mystery"
                                title="Mystery">
                                Mystery
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/mythology"
                                title="Mythology">
                                Mythology
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/organized-crime"
                                title="Organized Crime">
                                Organized Crime
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/otaku-culture"
                                title="Otaku Culture">
                                Otaku Culture
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/parody"
                                title="Parody">
                                Parody
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/performing-arts"
                                title="Performing Arts">
                                Performing Arts
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/pets"
                                title="Pets">
                                Pets
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/police"
                                title="Police">
                                Police
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/psychological"
                                title="Psychological">
                                Psychological
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/racing"
                                title="Racing">
                                Racing
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/reincarnation"
                                title="Reincarnation">
                                Reincarnation
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/reverse-harem"
                                title="Reverse Harem">
                                Reverse Harem
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/romance"
                                title="Romance">
                                Romance
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/samurai"
                                title="Samurai">
                                Samurai
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/school"
                                title="School">
                                School
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/sci-fi"
                                title="Sci-Fi">
                                Sci-Fi
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/sci-fi-fantasy"
                                title="Sci-Fi &amp; Fantasy">
                                Sci-Fi &amp; Fantasy
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/seinen"
                                title="Seinen">
                                Seinen
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/shoujo"
                                title="Shoujo">
                                Shoujo
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/shoujo-ai"
                                title="Shoujo Ai">
                                Shoujo Ai
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/shounen"
                                title="Shounen">
                                Shounen
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/shounen-ai"
                                title="Shounen Ai">
                                Shounen Ai
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/showbiz"
                                title="Showbiz">
                                Showbiz
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/slice-of-life"
                                title="Slice of Life">
                                Slice of Life
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/space"
                                title="Space">
                                Space
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/sports"
                                title="Sports">
                                Sports
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/strategy-game"
                                title="Strategy Game">
                                Strategy Game
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/supernatural"
                                title="Supernatural">
                                Supernatural
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/super-power"
                                title="Super Power">
                                Super Power
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/survival"
                                title="Survival">
                                Survival
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/suspense"
                                title="Suspense">
                                Suspense
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/team-sports"
                                title="Team Sports">
                                Team Sports
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/thriller"
                                title="Thriller">
                                Thriller
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/time-travel"
                                title="Time Travel">
                                Time Travel
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/unknown"
                                title="unknown">
                                unknown
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/urban-fantasy"
                                title="Urban Fantasy">
                                Urban Fantasy
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/vampire"
                                title="Vampire">
                                Vampire
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/video-game"
                                title="Video Game">
                                Video Game
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/villainess"
                                title="Villainess">
                                Villainess
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/visual-arts"
                                title="Visual Arts">
                                Visual Arts
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="https://hianime.at/genres/workplace"
                                title="Workplace">
                                Workplace
                            </a>
                        </li>
                                                                        <li class="nav-item nav-more">
                            <a class="nav-link" href="#" role="button" aria-label="Show more genres"><i class="fas fa-plus mr-2"></i>More</a>
                        </li>
                                            </ul>
                    <div class="clearfix"></div>
                </div>
            </li>
        </ul>
        <div class="clearfix"></div>
    </nav>
    <div id="header" class="header-home" role="banner">
            <div class="container">
                <button id="mobile_menu" aria-label="Toggle mobile menu"><i class="fa fa-bars"></i></button>
                <a href="index.html" id="logo" aria-label="Home">
                    <img src="assets/images/logo.png" alt="HiAnime">
                    <div class="clearfix"></div>
                </a>
                <div id="search">
                    <div class="search-content">
                        <form action="browse.html" autocomplete="off">
                            <a href="browse.html" class="filter-icon" aria-label="Filter anime">Filter</a>
                            <input type="text" class="form-control search-input" name="keyword"
                                placeholder="Search anime..." required aria-label="Search anime" value="">
                            <button type="submit" class="search-icon" aria-label="Search"><i class="fas fa-search"></i></button>
                        </form>
                        <div class="nav search-result-pop" id="search-suggest" style="display: none;">
                            <div class="loading-relative" id="search-loading" style="display: none;">
                                <div class="loading">
                                    <div class="span1"></div>
                                    <div class="span2"></div>
                                    <div class="span3"></div>
                                </div>
                            </div>
                            <div class="result" style="display:none;"></div>
                        </div>
                    </div>
                </div>
                <div class="header-group">
                    <div class="anw-group">
                        <div class="zrg-title"><span class="top">Join now</span><span class="bottom">HiAnime
                                Group</span></div>
                        <div class="zrg-list">
                            <div class="item"><a href="https://discord.com/invite/qCMd54VQUv" target="_blank"
                                    class="zr-social-button dc-btn" aria-label="Discord"><i class="fab fa-discord" aria-hidden="true"></i></a>
                            </div>
                            <div class="item"><a href="https://t.me/hianimeat" target="_blank"
                                    class="zr-social-button tl-btn" aria-label="Telegram"><i class="fab fa-telegram-plane" aria-hidden="true"></i></a>
                            </div>
                            <div class="item"><a href="#" target="_blank"
                                    class="zr-social-button rd-btn" aria-label="Reddit"><i class="fab fa-reddit-alien" aria-hidden="true"></i></a></div>
                            <div class="item"><a href="https://x.com/hianimeat" target="_blank"
                                    class="zr-social-button tw-btn" aria-label="Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a></div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="header-setting">
                    <div class="hs-toggles">
                        <a href="https://hianime.at/watch2gather" class="hst-item" data-toggle="tooltip" title="Watch with friends">
                            <div class="hst-icon"><i class="zicon zicon-20 zicon-live"></i></div>
                            <div class="name"><span>Watch2gether</span></div>
                        </a>
                        <a href="https://hianime.at/random" class="hst-item" data-toggle="tooltip"
                            data-original-title="Watch random anime">
                            <div class="hst-icon"><i class="fas fa-random"></i></div>
                            <div class="name"><span>Random</span></div>
                        </a>
                        <div class="hst-item" data-toggle="tooltip" title="Select language of anime name to display.">
                            <div class="select-anime-name toggle-lang"><span class="en">EN</span><span
                                    class="jp">JP</span></div>
                            <div class="name"><span>Anime Name</span></div>
                        </div>
                        <a href="https://hianime.at/news" class="hst-item" data-toggle="tooltip" title="News">
                            <div class="hst-icon"><i class="fas fa-rss"></i></div>
                            <div class="name"><span>News</span></div>
                        </a>
                        <a href="https://hianime.at/community" class="hst-item" data-toggle="tooltip" title="HiAnime Community">
                            <div class="hst-icon"><i class="fas fa-comments"></i></div>
                            <div class="name"><span>Community</span></div>
                        </a>
                        <div class="clearfix"></div>
                    </div>
                </div>

                <div id="pick_menu">
                    <div class="pick_menu-ul">
                        <ul class="ulclear">
                            <li class="pmu-item pmu-item-home">
                                <a class="pmu-item-icon" href="index.html" title="Home">
                                    <img src="assets/images/pick-home.svg" data-toggle="tooltip" data-placement="right"
                                        title="Home">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-movies">
                                <a class="pmu-item-icon" href="browse.html" title="Movies">
                                    <img src="assets/images/pick-movies.svg" data-toggle="tooltip" data-placement="right"
                                        title="Movies">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-show">
                                <a class="pmu-item-icon" href="browse.html" title="TV Series">
                                    <img src="assets/images/pick-show.svg" data-toggle="tooltip" data-placement="right"
                                        title="TV Series">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-popular">
                                <a class="pmu-item-icon" href="browse.html" title="Most Popular">
                                    <img src="assets/images/pick-popular.svg" data-toggle="tooltip" data-placement="right"
                                        title="Most Popular">
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div id="header_right">
                    
                    <div id="user-slot">
                        <div class="header_right-user">
                            <a href="#modallogin" data-toggle="modal" class="nav-link btn btn-primary text-white">Login</a>
                        </div>
                    </div>
                    
                    <template id="tpl-notifications">
                        <div class="hr-notifications dropdown">
                            <div class="hrn-icon" data-toggle="dropdown" data-noti-bell aria-expanded="false" role="button" aria-label="Notifications">
                                <i class="fas fa-bell"></i>
                                <div class="number" data-noti-count style="display:none">0</div>
                            </div>
                            <div class="dropdown-menu dropdown-menu-noti">
                                
                                <div class="nnl-head">
                                    <strong>Notifications</strong>
                                    <div class="fav-tabs mt-2 mb-0">
                                        <ul class="nav nav-tabs pre-tabs pre-tabs-min">
                                            <li class="nav-item"><a class="nav-link active" href="javascript:;" data-noti-tab="anime">Anime<span class="noti-tab-badge" data-noti-tab-badge="anime" style="display:none">0</span></a></li>
                                            <li class="nav-item"><a class="nav-link" href="javascript:;" data-noti-tab="community">Community<span class="noti-tab-badge" data-noti-tab-badge="community" style="display:none">0</span></a></li>
                                            <li class="nav-item"><a class="nav-link" href="javascript:;" data-noti-tab="system">System<span class="noti-tab-badge" data-noti-tab-badge="system" style="display:none">0</span></a></li>
                                        </ul>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                                <div class="new-noti-list">
                                    <div class="nnl-mark">
                                        <a class="btn btn-xs float-right" href="javascript:;" data-noti-read><i class="fas fa-check-double mr-1"></i>Mark all as read</a>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div data-noti-items>
                                        <div class="nnl-item new text-center">Loading…</div>
                                    </div>
                                    <a class="nnl-item nnl-more text-center" href="https://hianime.at/user/notifications">View all notifications<i class="fas fa-angle-right ml-2"></i></a>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template id="tpl-user-menu">
                        <div class="header_right-user logged">
                            <div class="dropdown">
                                <div class="btn-user-avatar" data-toggle="dropdown" aria-expanded="true" aria-controls="user_menu">
                                    <div class="profile-avatar">
                                        <img data-fill="avatar" data-fallback="assets/images/no-image.jpg" src="assets/images/no-image.jpg" alt="">
                                    </div>
                                </div>
                                <div id="user_menu" class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(-270px, 30px, 0px);">
                                    <div class="dropdown-item dropdown-item-user">
                                        <div class="user-detail">
                                            <div class="name"><strong data-fill="name"></strong></div>
                                            <div class="mail" data-fill="email"></div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="grid-menu">
                                        <a class="dropdown-item" href="https://hianime.at/user/profile"><i class="fas fa-user mr-2"></i>Profile</a>
                                        <a class="dropdown-item" href="https://hianime.at/user/watching"><i class="fas fa-history mr-2"></i>Continue Watching</a>
                                        <a class="dropdown-item" href="https://hianime.at/user/bookmarks"><i class="fas fa-heart mr-2"></i>Watch List</a>
                                        <a class="dropdown-item" href="https://hianime.at/user/import"><i class="fas fa-exchange-alt mr-2"></i>Import/Export</a>
                                        <a class="dropdown-item" href="https://hianime.at/user/settings"><i class="fas fa-cog mr-2"></i>Settings</a>
                                        <div class="clearfix"></div>
                                    </div>
                                    <a class="dropdown-item text-right text-white" href="#" data-action="logout">Logout<i class="fas fa-arrow-right ml-2 mr-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div id="mobile_search"><i class="fa fa-search"></i></div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="clearfix"></div>
    `;
}

export function initHeaderEvents() {
    // 1. Mobile Sidebar Toggle
    const mobileMenuBtn = document.getElementById('mobile_menu');
    const sidebar = document.getElementById('sidebar_menu');
    const sidebarBg = document.getElementById('sidebar_menu_bg');
    const closeBtns = document.querySelectorAll('.toggle-sidebar, .sidebar-close-btn');

    const openMenu = () => {
        if (sidebar) sidebar.classList.add('active');
        if (sidebarBg) {
            sidebarBg.style.display = 'block';
            sidebarBg.classList.add('active');
        }
        document.body.style.overflow = 'hidden';
    };

    const closeMenu = () => {
        if (sidebar) sidebar.classList.remove('active');
        if (sidebarBg) {
            sidebarBg.style.display = 'none';
            sidebarBg.classList.remove('active');
        }
        document.body.style.overflow = '';
    };

    if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMenu);
    closeBtns.forEach(b => b.addEventListener('click', closeMenu));
    if (sidebarBg) sidebarBg.addEventListener('click', closeMenu);

    // 2. Mobile Search Toggle
    const mobileSearchBtn = document.getElementById('mobile_search');
    const searchBlock = document.getElementById('search');
    if (mobileSearchBtn && searchBlock) {
        mobileSearchBtn.addEventListener('click', () => {
            searchBlock.classList.toggle('active');
            const inp = searchBlock.querySelector('input.search-input');
            if (inp && searchBlock.classList.contains('active')) {
                inp.focus();
            }
        });
    }

    // 3. Search auto-suggest
    const searchInput = document.querySelector('.search-input');
    const suggestContainer = document.getElementById('search-suggest');

    if (searchInput && suggestContainer) {
        searchInput.addEventListener('input', debounce((e) => {
            const val = e.target.value.trim();
            if (val.length < 2) {
                suggestContainer.style.display = 'none';
                suggestContainer.innerHTML = '';
                return;
            }

            const results = animeApi.search(val);
            if (results.length === 0) {
                suggestContainer.innerHTML = `<div class="p-3 text-muted text-center" style="background:#252438; border-radius:6px;">No anime found for "${val}"</div>`;
            } else {
                suggestContainer.innerHTML = `
                <div class="search-results-list" style="background:#252438; border:1px solid rgba(255,255,255,0.1); border-radius:8px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.85);">
                    ${results.slice(0, 5).map(anime => `
                    <a href="watch.html?anime=${anime.slug}" class="d-flex align-items-center p-2 text-decoration-none" style="border-bottom:1px solid rgba(255,255,255,0.05); color:#fff;">
                        <img src="${anime.poster}" style="width:38px; height:50px; object-fit:cover; border-radius:4px; margin-right:12px;">
                        <div>
                            <div style="font-weight:600; font-size:13px; color:#fff;">${anime.title}</div>
                            <div style="font-size:11px; color:#aaa; margin-top:2px;">
                                ${anime.type || 'TV'} • <span style="color:#ffbade;">★ ${anime.score || '8.0'}</span>
                                ${anime.dubCount ? '<span class="badge ml-1" style="background:#b9e7ff; color:#111; font-size:9px;">Hindi</span>' : ''}
                            </div>
                        </div>
                    </a>
                    `).join('')}
                    <a href="browse.html?keyword=${encodeURIComponent(val)}" class="d-block p-2 text-center text-decoration-none" style="background:rgba(255,186,222,0.1); color:#ffbade; font-weight:600; font-size:12px;">
                        View all results (${results.length}) <i class="fas fa-angle-right ml-1"></i>
                    </a>
                </div>
                `;
            }
            suggestContainer.style.display = 'block';
        }, 200));

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestContainer.contains(e.target)) {
                suggestContainer.style.display = 'none';
            }
        });
    }
}
