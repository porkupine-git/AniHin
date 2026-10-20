<div id="sidebar_menu">
    <button class="btn btn-radius btn-sm btn-secondary toggle-sidebar"><i class="fas fa-angle-left mr-2"></i>Close
        menu</button>
    <div class="sb-setting">
        <div class="header-setting">
            <div class="hs-toggles">
                <a href="<?= $websiteUrl ?>/anime/most-popular" class="hst-item" data-toggle="tooltip"
                    data-original-title="Popular Anime List">
                    <div class="hst-icon"><i class="fa-solid fa-fire"></i></div>
                    <div class="name"><span>Popular</span></div>
                </a>
                <div class="hst-item" data-bs-toggle="tooltip" data-bs-placement="bottom"
                    title="Select language of anime name to display.">
                    <div class="select-anime-name toggle-lang"><span class="en">EN</span><span class="jp">JP</span>
                    </div>
                    <div class="name"><span class="lang-display">English</span></div>
                </div>
                <a href="<?= $websiteUrl ?>/random" class="hst-item" data-toggle="tooltip"
                    data-original-title="Select Random Anime">
                    <div class="hst-icon"><i class="fas fa-random"></i></div>
                    <div class="name"><span>Random</span></div>
                </a>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <ul class="nav sidebar_menu-list">
        <li class="nav-item active"><a class="nav-link" href="/home" title="Home">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/anime/subbed-anime" title="Subbed Anime">Subbed Anime</a></li>
        <li class="nav-item"><a class="nav-link" href="/anime/dubbed-anime" title="Dubbed Anime">Dubbed Anime</a></li>
        <li class="nav-item"><a class="nav-link" href="/anime/most-popular" title="Most Popular">Most Popular</a></li>
        <li class="nav-item"><a class="nav-link" href="/anime/movie" title="Movies">Movies</a></li>
        <li class="nav-item"><a class="nav-link" href="/anime/tv" title="TV Series">TV Series</a></li>
        <li class="nav-item"><a class="nav-link" href="/anime/ova" title="OVAs">OVAs</a></li>
        <li class="nav-item"><a class="nav-link" href="/anime/ona" title="ONAs">ONAs</a></li>
        <li class="nav-item"><a class="nav-link" href="/anime/special" title="Specials">Specials</a></li>
        <!-- <li class="nav-item"><a class="nav-link" href="https://anipaca-app.netlify.app" title="Events"><?= $websiteTitle ?> App</a></li> -->
        <li class="nav-item">
            <div class="nav-link" title="Genre"><strong><i class="fa fa-angle-down"></i> Genre</strong></div>
            <div class="sidebar_menu-sub show" id="sidebar_subs_genre">
                <ul class="nav color-list sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/action"
                            title="Action">Action</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/adventure"
                            title="Adventure">Adventure</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/cars" title="Cars">Cars</a>
                    </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/comedy"
                            title="Comedy">Comedy</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/dementia"
                            title="Dementia">Dementia</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/demons"
                            title="Demons">Demons</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/drama"
                            title="Drama">Drama</a></li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/ecchi"
                            title="Ecchi">Ecchi</a></li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/fantasy"
                            title="Fantasy">Fantasy</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/game" title="Game">Game</a>
                    </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/harem"
                            title="Harem">Harem</a></li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/historical"
                            title="Historical">Historical</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/Horror"
                            title="Horror">Horror</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/Isekai"
                            title="Isekai">Isekai</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/josei"
                            title="Josei">Josei</a></li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/kids" title="Kids">Kids</a>
                    </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/magic"
                            title="Magic">Magic</a></li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/martial-arts"
                            title="Martial Arts">Martial Arts</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/mecha"
                            title="Mecha">Mecha</a></li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/military"
                            title="Military">Military</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/music"
                            title="Music">Music</a></li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/mystery"
                            title="Mystery">Mystery</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/parody"
                            Title="Parody">Parody</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/police"
                            title="Police">Police</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/psychological"
                            title="Psychological">Psychological</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/romance"
                            title="Romance">Romance</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/samurai"
                            title="Samurai">Samurai</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/school"
                            title="School">School</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/sci-fi"
                            title="Sci-Fi">Sci-Fi</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/seinen"
                            title="Seinen">Seinen</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/shoujo"
                            title="Shoujo">Shoujo</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/shoujo-ai"
                            title="Shoujo Ai">Shoujo Ai</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/shounen"
                            title="Shounen">Shounen</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/shounen-Ai"
                            title="Shounen Ai">Shounen Ai</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/slice-of-life"
                            title="Slice of Life">Slice of Life</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/space"
                            title="Space">Space</a></li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/sports"
                            title="Sports">Sports</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/super-power"
                            title="Super Power">Super Power</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/supernatural"
                            title="Supernatural">Supernatural</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/thriller"
                            title="Thriller">Thriller</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="<?= $websiteUrl ?>/genre/vampire"
                            title="Vampire">Vampire</a> </li>
                    <li class="nav-item nav-more">
                        <a class="nav-link"><i class="fas fa-plus mr-2"></i>More</a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
    <div class="clearfix"></div>
</div>

<!-- Add CSS for animation -->
<style>
@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.7;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.live-users-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(76, 175, 80, 0.1);
    padding: 5px 12px;
    border-radius: 20px;
    margin-left: 15px;
    font-size: 0.9em;
    border: 1px solid rgba(76, 175, 80, 0.3);
    transition: all 0.3s ease;
}

.live-users-badge:hover {
    background: rgba(76, 175, 80, 0.2);
    transform: translateY(-1px);
}

.live-users-badge i {
    color: #4CAF50;
    margin-right: 6px;
    font-size: 1em;
}

.live-users-badge .user-count {
    font-weight: bold;
    color: #4CAF50;
    margin-right: 4px;
}

.live-users-badge .user-text {
    color: #666;
    margin-right: 6px;
}

.live-users-badge .live-indicator {
    width: 8px;
    height: 8px;
    background: #4CAF50;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .live-users-badge {
        margin-left: 0;
        margin-top: 10px;
        width: 100%;
        justify-content: center;
    }
    
    .live-users-badge .user-text {
        display: none;
    }
}
</style>

<script>
window.userId = '<?= $user_id ?>';
window.myLoginFlag = <?= $isLoggedIn ? 'true' : 'false' ?>;

// Live Users Counter Function
function updateLiveUserCount() {
    fetch('/active-users.php')
        .then(response => response.json())
        .then(data => {
            if (data.count !== undefined) {
                const countElements = document.querySelectorAll('.live-user-count');
                const oldCount = parseInt(countElements[0]?.textContent || '0');
                const newCount = data.count;
                
                countElements.forEach(element => {
                    // Animate count change
                    if (oldCount !== newCount) {
                        element.style.transform = 'scale(1.2)';
                        setTimeout(() => {
                            element.style.transform = 'scale(1)';
                        }, 200);
                    }
                    
                    element.textContent = newCount;
                });
            }
        })
        .catch(error => console.error('Error fetching live user count:', error));
}

// Update user activity on page load
function updateUserActivity() {
    if (navigator.sendBeacon) {
        navigator.sendBeacon('/active-users.php');
    } else {
        fetch('/active-users.php', { method: 'POST', keepalive: true });
    }
}

// Update count every 30 seconds
setInterval(updateLiveUserCount, 30000);

// Update on page load and when user returns to tab
document.addEventListener('DOMContentLoaded', function() {
    updateLiveUserCount();
    updateUserActivity();
});

// Update when page becomes visible again
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        updateLiveUserCount();
        updateUserActivity();
    }
});

// Update before page unload
window.addEventListener('beforeunload', function() {
    if (navigator.sendBeacon) {
        navigator.sendBeacon('/active-users.php?unload=1');
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const genreToggle = document.querySelector('.nav-item .nav-link[title="Genre"]');
    const genreSubmenu = document.getElementById('sidebar_subs_genre');
    if (genreToggle && genreSubmenu) {
        genreToggle.addEventListener('click', function(e) {
            e.preventDefault();
            genreSubmenu.classList.toggle('show');
            const icon = this.querySelector('i.fa');
            if (genreSubmenu.classList.contains('show')) {
                icon.classList.remove('fa-angle-down');
                icon.classList.add('fa-angle-up');
            } else {
                icon.classList.remove('fa-angle-up');
                icon.classList.add('fa-angle-down');
            }
        });
    }
    const moreButton = document.querySelector('.nav-more .nav-link');
    if (moreButton) {
        moreButton.addEventListener('click', function(e) {
            e.preventDefault();
            const genreItems = document.querySelectorAll(
                '#sidebar_subs_genre .nav-item:not(.nav-more)');
            let hiddenItemsShown = false;
            genreItems.forEach(item => {
                if (item.style.display === 'none') {
                    hiddenItemsShown = true;
                }
            });
            genreItems.forEach((item, index) => {
                if (index >= 10) {
                    if (hiddenItemsShown) {
                        item.style.display = 'none';
                        moreButton.innerHTML = '<i class="fas fa-plus mr-2"></i>More';
                    } else {
                        item.style.display = 'block';
                        moreButton.innerHTML = '<i class="fas fa-minus mr-2"></i>Less';
                    }
                }
            });
        });
        const genreItems = document.querySelectorAll('#sidebar_subs_genre .nav-item:not(.nav-more)');
        genreItems.forEach((item, index) => {
            if (index >= 10) {
                item.style.display = 'none';
            }
        });
    }
});
</script>

<div id="header" class="header-home">
    <div class="container">
        <div id="mobile_menu"><i class="fa fa-bars"></i></div>
        <a href="<?= $websiteUrl ?>" id="logo" title="<?= $websiteTitle ?>">
            <img src="<?= $websiteLogo ?>" width="165" height="40" alt="<?= $websiteTitle ?>" decoding="async">
            <div class="clearfix"></div>
        </a>
        <div id="search" class="">
            <div class="search-content">
                <form action="/search" autocomplete="off">
                    <a href="/filter" class="filter-icon">Filter</a>
                    <input type="text" class="form-control search-input" name="keyword" placeholder="Search anime..."
                        required="">
                    <button type="submit" class="search-icon"><i class="fas fa-search"></i></button>
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
                <div class="zrg-title"><span class="top">Join now</span><span class="bottom"><?= $websiteTitle ?>
                        Group</span></div>
                <div class="zrg-list">
                    <div class="item"><a href="<?= $discord ?>" target="_blank" class="zr-social-button dc-btn"><i
                                class="fa-brands fa-discord"></i></a></div>
                    <div class="item"><a href="<?= $telegram ?>" target="_blank" class="zr-social-button tl-btn"><i
                                class="fab fa-telegram-plane"></i></a></div>
                    <div class="item"><a href="<?= $instagram ?>" target="_blank" class="zr-social-button in-btn"><i
                                class="fa-brands fa-instagram"></i></a></div>
                </div>
            </div>
            <!-- Live Users Badge - Now next to community buttons -->
            <div class="live-users-badge">
                <i class="fas fa-users"></i>
                <span class="live-user-count user-count">0</span>
                <span class="user-text">online</span>
                <span class="live-indicator"></span>
            </div>
        </div>
        <div class="header-setting">
            <div class="hs-toggles">
                <a href="<?= $websiteUrl ?>/anime/most-popular" class="hst-item" data-toggle="tooltip"
                    data-original-title="Popular Anime List">
                    <div class="hst-icon"><i class="fa-solid fa-fire"></i></div>
                    <div class="name"><span>Popular</span></div>
                </a>
                <a href="<?= $websiteUrl ?>/random" class="hst-item" data-toggle="tooltip"
                    data-original-title="Select Random Anime">
                    <div class="hst-icon"><i class="fas fa-random"></i></div>
                    <div class="name"><span>Random</span></div>
                </a>
                <div class="hst-item" data-bs-toggle="tooltip" data-bs-placement="bottom"
                    title="Select language of anime name to display.">
                    <div class="select-anime-name toggle-lang"><span class="en">EN</span><span class="jp">JP</span>
                    </div>
                    <div class="name"><span class="lang-display">English</span></div>
                </div>
                <a href="<?= $websiteUrl ?>/anime/movie" class="hst-item" data-toggle="tooltip"
                    data-original-title="Anime Movies">
                    <div class="hst-icon"><i class="fa-solid fa-clapperboard"></i></div>
                    <div class="name"><span>Movie</span></div>
                </a>
                <a href="<?= $websiteUrl ?>/anime/completed" class="hst-item" data-toggle="tooltip"
                    data-original-title="Anime Movies">
                    <div class="hst-icon"><i class="fa-solid fa-hourglass-end"></i></div>
                    <div class="name"><span>Completed</span></div>
                </a>
                <div class="clearfix"></div>
            </div>
        </div>
        <div id="header_right"></div>
        <div id="mobile_search" class="mobile-search"><i class="fa fa-search"></i></div>
        <div class="clearfix"></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const interval = setInterval(function() {
            if (typeof jQuery !== 'undefined') {
                clearInterval(interval);
                jQuery.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? "" ?>'
                    }
                });
            }
        }, 50);
    });
</script>