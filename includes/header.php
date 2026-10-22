<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    
    <?php
    $siteName = 'AniXoHindi';
    $siteTagline = 'Watch Hindi Dubbed Anime in 1080p Full HD';
    if (!empty($page_title)) {
        $finalTitle = (strpos($page_title, $siteName) !== false) ? e($page_title) : (e($page_title) . ' | ' . $siteName);
    } else {
        $finalTitle = $siteName . ' – ' . $siteTagline;
    }

    $finalDesc = !empty($page_desc) ? e($page_desc) : 'Watch popular anime in Hindi Dubbed and English Subbed in 1080p Full HD without buffering. Stream free on AniXoHindi.';
    $finalKeywords = !empty($page_keywords) ? e($page_keywords) : 'Hindi Dubbed Anime, Watch Anime in Hindi, Hindi Anime Episodes, Free Anime Online, Solo Leveling Hindi Dub, Naruto Hindi Dub, Bleach Hindi, Death Note Hindi, AniXoHindi';
    $finalRobots = !empty($page_robots) ? e($page_robots) : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

    if (empty($canonicalUrl)) {
        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
        $pathOnly = strtok($rawUri, '?');
        if ($pathOnly === '/' || $pathOnly === '/index.php') {
            $canonicalUrl = 'https://anixohindi.cc/';
        } elseif ($pathOnly === '/browse.php') {
            $allowedParams = [];
            if (!empty($_GET['type'])) $allowedParams['type'] = $_GET['type'];
            if (!empty($_GET['genre'])) $allowedParams['genre'] = $_GET['genre'];
            if (!empty($_GET['status'])) $allowedParams['status'] = $_GET['status'];
            if (!empty($_GET['keyword'])) $allowedParams['keyword'] = $_GET['keyword'];
            $canonicalUrl = 'https://anixohindi.cc/browse.php' . (!empty($allowedParams) ? '?' . http_build_query($allowedParams) : '');
        } else {
            $canonicalUrl = 'https://anixohindi.cc' . $rawUri;
        }
    }
    ?>

    <!-- Primary Title & Meta SEO -->
    <title><?= $finalTitle ?></title>
    <meta name="description" content="<?= $finalDesc ?>">
    <meta name="keywords" content="<?= $finalKeywords ?>">
    <meta name="author" content="AniXoHindi">
    <meta name="theme-color" content="#101716">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <!-- Search Engine Indexing Directives -->
    <meta name="robots" content="<?= $finalRobots ?>">
    <meta name="googlebot" content="<?= $finalRobots ?>">
    <meta name="bingbot" content="<?= $finalRobots ?>">

    <!-- Open Graph Protocol (Facebook, Telegram, Discord, WhatsApp) -->
    <meta property="og:site_name" content="AniXoHindi">
    <meta property="og:type" content="<?= !empty($og_type) ? e($og_type) : 'website' ?>">
    <meta property="og:title" content="<?= $finalTitle ?>">
    <meta property="og:description" content="<?= $finalDesc ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:locale" content="hi_IN">
    <meta property="og:locale:alternate" content="en_US">
    <?php if (!empty($og_image)): ?>
    <meta property="og:image" content="<?= e($og_image) ?>">
    <meta property="og:image:alt" content="<?= $finalTitle ?>">
    <?php endif; ?>

    <!-- Twitter Card for High CTR Previews -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $finalTitle ?>">
    <meta name="twitter:description" content="<?= $finalDesc ?>">
    <?php if (!empty($og_image)): ?>
    <meta name="twitter:image" content="<?= e($og_image) ?>">
    <?php endif; ?>

    <!-- Fonts, Preconnect & Styling Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://embed.anixohindi.cc" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">

    <!-- Google Global Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "WebSite",
          "@id": "https://anixohindi.cc/#website",
          "url": "https://anixohindi.cc/",
          "name": "AniXoHindi",
          "alternateName": ["AniXo Hindi", "AniXo Anime", "अनिक्सो हिंदी"],
          "description": "Watch Free Hindi Dubbed Anime Online in Full HD 1080p",
          "inLanguage": "hi-IN",
          "potentialAction": {
            "@type": "SearchAction",
            "target": {
              "@type": "EntryPoint",
              "urlTemplate": "https://anixohindi.cc/browse.php?keyword={search_term_string}"
            },
            "query-input": "required name=search_term_string"
          }
        },
        {
          "@type": "Organization",
          "@id": "https://anixohindi.cc/#organization",
          "name": "AniXoHindi",
          "url": "https://anixohindi.cc/",
          "logo": {
            "@type": "ImageObject",
            "url": "https://anixohindi.cc/assets/img/logo.png"
          }
        }
      ]
    }
    </script>

    <?php if (!empty($page_schema_json)): ?>
    <!-- Page Specific Structured Data Schema (VideoObject, TVEpisode, Breadcrumbs) -->
    <script type="application/ld+json">
    <?= json_encode($page_schema_json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
    <?php endif; ?>
</head>
<body>

<!-- Site Navbar -->
<header class="site-navbar">
    <div class="container">
        <div class="navbar-inner">
            <!-- Left Group: Brand Logo & Main Navigation -->
            <div class="navbar-left">
                <!-- Brand Logo -->
                <a href="/index.php" class="brand-logo">
                    <span class="brand-icon-wrap">
                        <i class="fa-solid fa-play"></i>
                    </span>
                    <span class="brand-text">Ani<span class="brand-highlight">Xo</span>Hindi</span>
                    <span class="brand-badge">हिंदी</span>
                </a>

                <!-- Navigation Links -->
                <ul class="nav-links">
                    <li><a href="/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a></li>
                    <li><a href="/browse.php?type=dub" class="<?= ($_GET['type'] ?? '') == 'dub' ? 'active' : '' ?>">Hindi Dubbed</a></li>
                    <li><a href="/browse.php?type=sub" class="<?= ($_GET['type'] ?? '') == 'sub' ? 'active' : '' ?>">Subbed</a></li>
                    <li><a href="/browse.php?type=Movie" class="<?= ($_GET['type'] ?? '') == 'Movie' ? 'active' : '' ?>">Movies</a></li>
                    <li><a href="/browse.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'browse.php' && empty($_GET['type'])) ? 'active' : '' ?>">Browse</a></li>
                    <li><a href="/blog.php" class="<?= basename($_SERVER['PHP_SELF']) == 'blog.php' ? 'active' : '' ?>">Blog</a></li>
                </ul>
            </div>

            <!-- Right Group: Live Search & User Account -->
            <div class="navbar-right">
                <!-- Desktop Search Bar with Live Suggest -->
                <div class="nav-search nav-search-desktop">
                    <div class="search-input-wrap">
                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        <input type="text" id="site-search-input" placeholder="Search Hindi anime, Naruto, Solo Leveling..." autocomplete="off">
                    </div>
                    <div class="search-dropdown" id="search-results-dropdown"></div>
                </div>

                <!-- Mobile Search Toggle Button (Visible on mobile only) -->
                <button class="nav-mobile-search-btn" id="navMobileSearchBtn" aria-label="Search Anime">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <!-- Auth Action -->
                <div class="nav-auth-actions">
                    <?php if ($user): ?>
                        <div class="user-avatar-dropdown-wrap" id="userAvatarDropdownWrap">
                            <button type="button" class="user-avatar-btn" id="userAvatarBtn" onclick="toggleUserMenu(event)" aria-label="User Account" title="<?= e($user['username']) ?>">
                                <img src="<?= e($user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['username'])) ?>" alt="<?= e($user['username']) ?>" class="user-avatar-img">
                            </button>
                            <div class="user-dropdown-menu" id="userDropdownMenu">
                                <div class="dropdown-user-header">
                                    <img src="<?= e($user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['username'])) ?>" alt="<?= e($user['username']) ?>" class="dropdown-avatar-thumb">
                                    <div class="dropdown-user-info">
                                        <div class="dropdown-user-name"><?= e($user['username']) ?></div>
                                        <div class="dropdown-user-status"><span class="user-status-dot"></span> Logged In</div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a href="/watchlist.php" class="dropdown-item">
                                    <i class="fa-regular fa-bookmark"></i>
                                    <span>My Watchlist</span>
                                </a>
                                <a href="/auth/logout.php" class="dropdown-item dropdown-logout-item">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    <span>Log Out</span>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <button class="btn-primary-custom" onclick="openAuthModal()">
                            <i class="fa-regular fa-user"></i> <span class="auth-btn-text">Sign In</span>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Mobile Hamburger Toggle -->
                <button class="nav-mobile-toggle" id="navMobileToggle" aria-label="Toggle Navigation Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Expandable Search Bar (Slides down smoothly when search button is tapped) -->
        <div class="nav-mobile-search-bar" id="navMobileSearchBar">
            <div class="mobile-search-input-wrap">
                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                <input type="text" id="mobile-search-input" placeholder="Search Hindi anime, Naruto, Bleach..." autocomplete="off">
                <button class="mobile-search-close-btn" id="mobileSearchCloseBtn" aria-label="Close search"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="mobile-search-dropdown" id="mobile-search-dropdown"></div>
        </div>

        <!-- Mobile Drawer Nav -->
        <div class="nav-mobile-menu" id="navMobileMenu">
            <div class="mobile-drawer-header">
                <span class="mobile-drawer-tagline"><i class="fa-solid fa-headphones text-accent-peach mr-1"></i> Hindi Dubbed Anime</span>
                <span class="mobile-drawer-badge">HD 1080p</span>
            </div>
            <ul class="mobile-nav-links">
                <li><a href="/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><i class="fa-solid fa-house mr-2"></i> Home</a></li>
                <li><a href="/browse.php?type=dub" class="<?= ($_GET['type'] ?? '') == 'dub' ? 'active' : '' ?>"><i class="fa-solid fa-headphones mr-2 text-accent-gold"></i> Hindi Dubbed <span class="nav-pill-hot">HOT</span></a></li>
                <li><a href="/browse.php?status=Currently+Airing" class="<?= ($_GET['status'] ?? '') == 'Currently Airing' ? 'active' : '' ?>"><i class="fa-solid fa-bolt mr-2 text-accent-teal"></i> Currently Airing</a></li>
                <li><a href="/browse.php?type=sub" class="<?= ($_GET['type'] ?? '') == 'sub' ? 'active' : '' ?>"><i class="fa-solid fa-closed-captioning mr-2"></i> Subbed</a></li>
                <li><a href="/browse.php?type=Movie" class="<?= ($_GET['type'] ?? '') == 'Movie' ? 'active' : '' ?>"><i class="fa-solid fa-film mr-2"></i> Movies</a></li>
                <li><a href="/browse.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'browse.php' && empty($_GET['type'])) ? 'active' : '' ?>"><i class="fa-solid fa-compass mr-2"></i> Browse All</a></li>
                <li><a href="/blog.php" class="<?= basename($_SERVER['PHP_SELF']) == 'blog.php' ? 'active' : '' ?>"><i class="fa-solid fa-newspaper mr-2 text-accent-peach"></i> Anime Blog</a></li>
                <?php if ($user): ?>
                    <li><a href="/watchlist.php" class="<?= basename($_SERVER['PHP_SELF']) == 'watchlist.php' ? 'active' : '' ?>"><i class="fa-regular fa-bookmark mr-2"></i> My Watchlist</a></li>
                <?php endif; ?>
            </ul>
            <?php if (!$user): ?>
            <div class="mobile-drawer-footer">
                <button class="btn-primary-custom w-100" onclick="openAuthModal(); closeMobileMenu();" style="width: 100%; justify-content: center; padding: 10px 16px;">
                    <i class="fa-regular fa-user mr-2"></i> Sign In to AniXoHindi
                </button>
            </div>
            <?php else: ?>
            <div class="mobile-drawer-footer">
                <div class="mobile-user-card">
                    <img src="<?= e($user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['username'])) ?>" alt="<?= e($user['username']) ?>" class="mobile-user-avatar">
                    <div class="mobile-user-details">
                        <span class="mobile-user-name"><?= e($user['username']) ?></span>
                        <span class="mobile-user-status-text"><span class="user-status-dot"></span> Logged In</span>
                    </div>
                    <a href="/auth/logout.php" class="btn-mobile-logout" title="Log Out">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Mobile Dark Frosted Backdrop Overlay (Absorbs clicks to dismiss menus smoothly) -->
<div class="mobile-backdrop-overlay" id="mobileBackdropOverlay" aria-hidden="true"></div>

<!-- Auth Modal (AniList + Google OAuth) -->
<div class="auth-modal-overlay" id="authModal">
    <div class="auth-modal-card">
        <button class="auth-modal-close" onclick="closeAuthModal()"><i class="fa-solid fa-xmark"></i></button>
        <h2 class="auth-modal-title">Sign in to AniXoHindi</h2>
        <p class="auth-modal-subtitle">Track your watch history and sync your anime watchlist.</p>
        
        <div class="oauth-login-buttons">
            <a href="/auth/google.php" class="btn-oauth btn-oauth-google">
                <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.65v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.14z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.26v3.15C3.25 21.36 7.33 24 12 24z"/><path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.26C.46 8.17 0 9.97 0 12s.46 3.83 1.26 5.42l4.02-3.15z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.25 2.64 1.26 6.58l4.02 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/></svg>
                Continue with Google
            </a>
            
            <a href="/auth/anilist.php" class="btn-oauth btn-oauth-anilist">
                <i class="fa-solid fa-a mr-1"></i> Continue with AniList
            </a>
        </div>
        
        <p class="text-center text-muted small mt-4" style="font-size: 11px;">
            By signing in, you agree to our Terms of Service & Privacy Policy.
        </p>
    </div>
</div>
