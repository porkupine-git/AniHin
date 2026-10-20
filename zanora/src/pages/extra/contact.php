<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';

$loggedInEmail = '';
$loggedInName = '';
if (isset($_SESSION['userID']) && isset($conn) && $conn instanceof mysqli) {
    $uStmt = $conn->prepare("SELECT username, email FROM users WHERE id = ? LIMIT 1");
    if ($uStmt) {
        $uStmt->bind_param("i", $_SESSION['userID']);
        $uStmt->execute();
        $uRes = $uStmt->get_result()->fetch_assoc();
        $uStmt->close();
        if ($uRes) {
            $loggedInEmail = $uRes['email'] ?? '';
            $loggedInName = $uRes['username'] ?? '';
        }
    }
}
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-language" content="en" />

    <!-- SEO Meta Tags -->
    <title>Contact Us - <?= $websiteTitle ?></title>
    <meta name="description"
        content="Stream thousands of anime series and movies in English sub and dub. Watch free anime online in HD with fast streaming on <?= $websiteTitle ?>. No registration required!">
    <meta name="keywords"
        content="anime to watch, watch anime, anime online, free anime online, online anime, anime streaming, stream anime online, english anime, english dubbed anime, <?= strtolower($websiteTitle) ?>">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?= WEBSITE_URL ?>/contact" />

    <!-- Performance Optimization: DNS Prefetch & Preconnect -->
    <link rel="dns-prefetch" href="https://s4.anilist.co">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="preconnect" href="https://s4.anilist.co" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Open Graph (Facebook / Discord) -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= WEBSITE_URL ?>/contact">
    <meta property="og:title" content="Contact Us - <?= $websiteTitle ?>">
    <meta property="og:description"
        content="Stream thousands of anime series and movies in English sub and dub. Watch free anime online in HD with fast streaming on <?= $websiteTitle ?>.">
    <meta property="og:image" content="<?= $banner ?>" />
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">

    <!-- Twitter Card Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Contact Us - <?= $websiteTitle ?>">
    <meta name="twitter:description"
        content="Stream thousands of anime series and movies in English sub and dub. Watch free anime online in HD with fast streaming on <?= $websiteTitle ?>.">
    <meta name="twitter:image" content="<?= $banner ?>">

    <!-- Favicons & App Manifests -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg?v=<?= $version ?>" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico?v=<?= $version ?>" sizes="any" />
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=<?= $version ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=<?= $version ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icons-192.png?v=<?= $version ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/icons-512.png?v=<?= $version ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png?v=<?= $version ?>">
    <link rel="mask-icon" href="/images/safari-pinned-tab.svg?v=<?= $version ?>" color="#5bbad5">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#202125">
    <meta name="msapplication-TileColor" content="#da532c">

    <!-- Schema.org WebSite Structured Data -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "url": "<?= WEBSITE_URL ?>/",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "<?= WEBSITE_URL ?>/search?keyword={keyword}",
                "query-input": "required name=keyword"
            }
        }
    </script>


    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/styles.min.css?v=<?= $versionCss ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons&display=swap">
    </noscript>

    <style>
        .pizza {
            margin: 2rem auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 1.5rem;
            border-radius: .6rem;
            text-align: center;
            color: #000;
            font-size: 16px;
            font-weight: 400;
            background-color: #FAACA8;
            background-image: linear-gradient(19deg, #FAACA8 0%, #DDD6F3 100%);
        }

        .pizza a {
            color: #000;
            font-weight: 500;
            text-shadow: 0 1px 0 #fff;
        }

        .pizza a:hover {
            text-shadow: 0 1px 3px #fff;
        }

        .pizza-x {
            max-width: 800px;
            width: calc(100% - 32px);
            min-height: 90px;
        }

        .pizza-y {
            max-width: 400px;
            min-height: 300px;
        }

        .pizza .in-text {
            font-size: 1.5em;
            font-weight: 600;
        }

        .pizza .in-contact {
            font-size: 1em;
        }

        @media screen and (max-width: 479px) {
            .pizza {
                font-size: 13px;
                gap: .6rem
            }

            .pizza-y {
                min-height: 200px;
            }
        }
    </style>
    <!--End: Stylesheet-->
</head>

<body>
    <div id="sidebar_menu_bg"></div>
    <div id="sidebar_menu">
        <button class="btn btn-radius btn-sm btn-secondary toggle-sidebar"><i class="fas fa-angle-left mr-2"></i>Close
            menu</button>
        <div class="sb-setting">
            <div class="header-setting">
                <div class="hs-toggles">
                    <a href="/most-popular" class="hst-item" data-toggle="tooltip" title="Popular Anime List">
                        <div class="hst-icon"><i class="fa-solid fa-fire"></i></div>
                        <div class="name"><span>Popular</span></div>
                    </a>
                    <a href="/random" class="hst-item" data-toggle="tooltip" title="Select Random Anime">
                        <div class="hst-icon"><i class="fas fa-random"></i></div>
                        <div class="name"><span>Random</span></div>
                    </a>
                    <div class="hst-item" data-bs-toggle="tooltip" title="Select language of anime name to display.">
                        <div class="select-anime-name toggle-lang"><span class="en">EN</span><span class="jp">JP</span>
                        </div>
                        <div class="name"><span>Name</span></div>
                    </div>
                    <a href="/news" class="hst-item" title="<?= $websiteTitle ?> News">
                        <div class="hst-icon"><i class="fas fa-rss"></i></div>
                        <div class="name"><span>News</span></div>
                    </a>
                </div>
            </div>
        </div>
        <div class="sb-donate">
            <div class="hr-community"><a class="btn btn-sm" href="/community/board"
                    title="<?= $websiteTitle ?> Connect"><i class="fas fa-comments mr-2"></i>Community</a></div>
        </div>
        <ul class="nav sidebar_menu-list">
            <li class="nav-item active"><a class="nav-link" href="/home" title="Home">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="/subbed-anime" title="Subbed Anime">Subbed Anime</a>
            </li>
            <li class="nav-item"><a class="nav-link" href="/dubbed-anime" title="Dubbed Anime">Dubbed Anime</a>
            </li>
            <li class="nav-item"><a class="nav-link" href="/most-popular" title="Most Popular">Most Popular</a>
            </li>
            <li class="nav-item"><a class="nav-link" href="/movie" title="Movies">Movies</a></li>
            <li class="nav-item"><a class="nav-link" href="/tv" title="TV Series">TV Series</a></li>
            <li class="nav-item"><a class="nav-link" href="/ova" title="OVAs">OVAs</a></li>
            <li class="nav-item"><a class="nav-link" href="/ona" title="ONAs">ONAs</a></li>
            <li class="nav-item"><a class="nav-link" href="/special" title="Specials">Specials</a></li>
            <li class="nav-item"><a class="nav-link" href="/events" title="Events">Events</a></li>
            <li class="nav-item">
                <div class="nav-link" title="Genre"><strong>Genre</strong></div>
                <div class="sidebar_menu-sub show" id="sidebar_subs_genre">
                    <ul class="nav color-list sub-menu">
                        <li class="nav-item"><a class="nav-link" href="/genre/action">Action</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/adventure">Adventure</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/cars">Cars</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/comedy">Comedy</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/dementia">Dementia</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/demons">Demons</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/drama">Drama</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/ecchi">Ecchi</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/fantasy">Fantasy</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/game">Game</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/harem">Harem</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/historical">Historical</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/horror">Horror</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/isekai">Isekai</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/josei">Josei</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/kids">Kids</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/magic">Magic</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/marial-arts">Martial Arts</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/mecha">Mecha</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/military">Military</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/music">Music</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/mystery">Mystery</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/parody">Parody</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/police">Police</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/psychological">Psychological</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/romance">Romance</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/samurai">Samurai</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/school">School</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/sci-fi">Sci-Fi</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/seinen">Seinen</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/shoujo">Shoujo</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/shoujo-ai">Shoujo Ai</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/shounen">Shounen</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/shounen-ai">Shounen Ai</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/slice-of-life">Slice of Life</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/space">Space</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/sports">Sports</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/super-power">Super Power</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/supernatural">Supernatural</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/thriller">Thriller</a></li>
                        <li class="nav-item"><a class="nav-link" href="/genre/vampire">Vampire</a></li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>
    <div id="wrapper">
        <div id="header">
            <div class="container">
                <div id="mobile_menu"><i class="fa fa-bars"></i></div>
                <a href="/" id="logo">
                    <img src="<?= $websiteLogo ?>" alt="<?= $websiteTitle ?>" width="165" height="40" decoding="async">
                    <div class="clearfix"></div>
                </a>
                <div id="search">
                    <div class="search-content">
                        <form action="/search" autocomplete="off">
                            <a href="/filter" class="filter-icon">Filter</a>
                            <input type="text" class="form-control search-input" name="keyword"
                                placeholder="Search anime..." required>
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
                        <div class="zrg-title"><span class="top">Join now</span><span
                                class="bottom"><?= $websiteTitle ?>
                                Group</span></div>
                        <div class="zrg-list">
                            <div class="item"><a href="<?= $discord ?>" target="_blank"
                                    class="zr-social-button dc-btn"><i class="fa-brands fa-discord"></i></a></div>
                            <div class="item"><a href="<?= $telegram ?>" target="_blank"
                                    class="zr-social-button tl-btn"><i class="fab fa-telegram-plane"></i></a></div>
                            <div class="item"><a href="<?= $reddit ?>" target="_blank"
                                    class="zr-social-button rd-btn"><i class="fab fa-reddit-alien"></i></a></div>
                            <div class="item"><a href="<?= $instagram ?>" target="_blank"
                                    class="zr-social-button in-btn"><i class="fa-brands fa-instagram"></i></a></div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="header-setting">
                    <div class="hs-toggles">
                        <a href="/completed" class="hst-item" data-toggle="tooltip" title="Completed Anime">
                            <div class="hst-icon"><i class="fa-solid fa-hourglass-end"></i></div>
                            <div class="name"><span>Completed</span></div>
                        </a>
                        <a href="/random" class="hst-item" data-toggle="tooltip"
                            data-original-title="Watch random anime">
                            <div class="hst-icon"><i class="fas fa-random"></i></div>
                            <div class="name"><span>Random</span></div>
                        </a>
                        <div class="hst-item" data-toggle="tooltip" title="Select language of anime name to display.">
                            <div class="select-anime-name toggle-lang"><span class="en">EN</span><span
                                    class="jp">JP</span></div>
                            <div class="name"><span>Anime Name</span></div>
                        </div>
                        <a href="/news" class="hst-item" data-toggle="tooltip" title="News">
                            <div class="hst-icon"><i class="fas fa-rss"></i></div>
                            <div class="name"><span>News</span></div>
                        </a>
                        <a href="/community/board" class="hst-item" data-toggle="tooltip"
                            title="<?= $websiteTitle ?> Connect">
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
                                <a class="pmu-item-icon" href="/home" title="Home">
                                    <img src="/images/pick-home.svg" width="20" height="20" alt="Home" data-toggle="tooltip" data-placement="right"
                                        title="Home">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-movies">
                                <a class="pmu-item-icon" href="/movie" title="Movies">
                                    <img src="/images/pick-movies.svg" width="20" height="20" alt="Movies" data-toggle="tooltip" data-placement="right"
                                        title="Movies">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-show">
                                <a class="pmu-item-icon" href="/tv" title="TV Series">
                                    <img src="/images/pick-show.svg" width="20" height="20" alt="TV Series" data-toggle="tooltip" data-placement="right"
                                        title="TV Series">
                                </a>
                            </li>
                            <li class="pmu-item pmu-item-popular">
                                <a class="pmu-item-icon" href="/most-popular" title="Most Popular">
                                    <img src="/images/pick-popular.svg" width="20" height="20" alt="Most Popular" data-toggle="tooltip" data-placement="right"
                                        title="Most Popular">
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div id="header_right"></div>
                <div id="mobile_search"><i class="fa fa-search"></i></div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div id="main-wrapper" class="layout-page layout-contact page-az" role="main">
            <div class="container">
                <div class="profile-box makeup" style="max-width:700px;margin:20px auto 60px;">
                    <h2 class="h2-heading mb-3"><i class="fas fa-envelope mr-3"></i>Contact Us</h2>
                    <div class="mb-3">Please submit your inquiry using the form below and we will get in touch with you
                        shortly.</div>

                    <div id="contact-alert" class="alert" style="display:none; border-radius: 8px; margin-bottom: 20px;"></div>

                    <form id="contact-form" class="preform" method="post" autocomplete="off" action="/ajax/contact/submit">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="prelabel">Your Name <small class="text-muted">(Optional)</small></label>
                                <input type="text" class="form-control" name="name" placeholder="Your name or username" value="<?= htmlspecialchars($loggedInName) ?>">
                            </div>
                            <div class="col-md-6 form-group email">
                                <label class="prelabel">Email address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required="" placeholder="name@example.com" value="<?= htmlspecialchars($loggedInEmail) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="prelabel">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" required="" placeholder="What is your inquiry, feedback, or report regarding?">
                        </div>
                        <div class="form-group">
                            <label class="prelabel">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="message" rows="5" maxlength="3000" required=""
                                placeholder="Describe your issue, feedback, or inquiry in detail..."></textarea>
                        </div>
                        <button type="submit" id="contact-submit"
                            class="btn btn-block btn-primary mt-3" style="font-weight: 700; height: 46px;">
                            <span class="btn-text"><i class="fas fa-paper-plane mr-2"></i><strong>Send Message</strong></span>
                            <span class="btn-loading d-none"><i class="fas fa-spinner fa-spin mr-2"></i><strong>Sending message...</strong></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php include('./src/component/footer.php'); ?>
    </div>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . "/src/component/auth-modal.php"; ?>
    <script>
        const TURNSTILE_SITE_KEY = "0x4AAAAAABnnkP1T73YyqkG4"
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
    <script type='text/javascript'
        src='https://platform-api.sharethis.com/js/sharethis.js#property=6973d5aa6473557d10606f1e&product=sop'
        async='async'></script>
    <script type="text/javascript" src="/js/app.min.js?v=<?= $versionJs ?>"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js?v=<?= $versionJs2 ?>');
            });
        }

        // Contact Form Submission Handler (Zero Dependencies, Fully Resilient)
        var isContactSubmitting = false;

        function handleContactSubmit(e) {
            if (e && e.preventDefault) {
                e.preventDefault();
            }
            if (e && e.stopPropagation) {
                e.stopPropagation();
            }

            if (isContactSubmitting) {
                return false;
            }

            var form = document.getElementById('contact-form');
            var btn = document.getElementById('contact-submit');
            var alertBox = document.getElementById('contact-alert');
            if (!form || !btn) return false;

            isContactSubmitting = true;

            var btnText = btn.querySelector('.btn-text');
            var btnLoading = btn.querySelector('.btn-loading');

            btn.disabled = true;
            if (btnText) btnText.classList.add('d-none');
            if (btnLoading) btnLoading.classList.remove('d-none');
            if (alertBox) {
                alertBox.style.display = 'none';
                alertBox.className = 'alert';
            }

            var formData = new FormData(form);
            formData.append('is_ajax', '1');

            fetch('/ajax/contact/submit', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(data) {
                if (alertBox) {
                    alertBox.style.display = 'block';
                    if (data && data.status) {
                        alertBox.className = 'alert alert-success';
                        alertBox.innerHTML = '<i class="fas fa-circle-check mr-2"></i>' + (data.msg || 'Thank you! Your message has been received.');
                        var subj = form.querySelector('input[name="subject"]');
                        var msg = form.querySelector('textarea[name="message"]');
                        if (subj) subj.value = '';
                        if (msg) msg.value = '';
                    } else {
                        var errMsg = (data && data.msg) ? data.msg : 'Unable to send message. Please try again.';
                        alertBox.className = 'alert alert-danger';
                        alertBox.innerHTML = '<i class="fas fa-circle-exclamation mr-2"></i>' + errMsg;
                    }
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            })
            .catch(function(err) {
                if (alertBox) {
                    alertBox.style.display = 'block';
                    alertBox.className = 'alert alert-danger';
                    alertBox.innerHTML = '<i class="fas fa-circle-exclamation mr-2"></i>A network error occurred. Please try again.';
                }
            })
            .finally(function() {
                isContactSubmitting = false;
                btn.disabled = false;
                if (btnText) btnText.classList.remove('d-none');
                if (btnLoading) btnLoading.classList.add('d-none');
            });

            return false;
        }

        var contactFormEl = document.getElementById('contact-form');
        if (contactFormEl) {
            contactFormEl.addEventListener('submit', handleContactSubmit);
        }
    </script>
    <script defer src='https://static.cloudflareinsights.com/beacon.min.js'
        data-cf-beacon='{"token": "d26e23605f3b46eba58b0d8833d9f276"}'></script>
    <?php echo GLOBAL_AD_CODE; ?>
</body>

</html>