<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' - ' . SITE_NAME : SITE_NAME . ' – ' . SITE_TAGLINE ?></title>
    <meta name="description" content="<?= isset($page_desc) ? e($page_desc) : 'Watch popular anime in Hindi Dubbed and English Subbed in 1080p Full HD without buffering. Stream free on ' . SITE_NAME . '.' ?>">
    
    <!-- Open Graph for Social Previews & SEO -->
    <meta property="og:title" content="<?= isset($page_title) ? e($page_title) : SITE_NAME ?>">
    <meta property="og:description" content="<?= isset($page_desc) ? e($page_desc) : SITE_TAGLINE ?>">
    <meta property="og:type" content="website">
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Professional Design System Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
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
                    <li><a href="/filter.php?type=dub" class="<?= ($_GET['type'] ?? '') == 'dub' ? 'active' : '' ?>">Hindi Dubbed</a></li>
                    <li><a href="/filter.php?type=sub" class="<?= ($_GET['type'] ?? '') == 'sub' ? 'active' : '' ?>">Subbed</a></li>
                    <li><a href="/filter.php?type=Movie">Movies</a></li>
                    <li><a href="/filter.php">All Anime</a></li>
                </ul>
            </div>

            <!-- Right Group: Live Search & User Account -->
            <div class="navbar-right">
                <!-- Search Bar with Live Suggest -->
                <div class="nav-search">
                    <div class="search-input-wrap">
                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        <input type="text" id="site-search-input" placeholder="Search Hindi anime, Naruto, Solo Leveling..." autocomplete="off">
                    </div>
                    <div class="search-dropdown" id="search-results-dropdown"></div>
                </div>

                <!-- Auth Action -->
                <div class="nav-auth-actions">
                    <?php if ($user): ?>
                        <div class="user-profile-badge" onclick="toggleUserMenu()">
                            <img src="<?= e($user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['username'])) ?>" alt="User" class="user-profile-avatar">
                            <span class="small font-weight-bold"><?= e($user['username']) ?></span>
                            <a href="/auth/logout.php" title="Logout" class="text-muted ml-2"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
                        </div>
                    <?php else: ?>
                        <button class="btn-primary-custom" onclick="openAuthModal()">
                            <i class="fa-regular fa-user"></i> Sign In
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Mobile Hamburger Toggle -->
                <button class="nav-mobile-toggle" id="navMobileToggle" aria-label="Toggle Navigation Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Nav -->
        <div class="nav-mobile-menu" id="navMobileMenu">
            <ul class="mobile-nav-links">
                <li><a href="/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><i class="fa-solid fa-house mr-2"></i> Home</a></li>
                <li><a href="/filter.php?type=dub" class="<?= ($_GET['type'] ?? '') == 'dub' ? 'active' : '' ?>"><i class="fa-solid fa-headphones mr-2"></i> Hindi Dubbed</a></li>
                <li><a href="/filter.php?type=sub" class="<?= ($_GET['type'] ?? '') == 'sub' ? 'active' : '' ?>"><i class="fa-solid fa-closed-captioning mr-2"></i> Subbed</a></li>
                <li><a href="/filter.php?type=Movie"><i class="fa-solid fa-film mr-2"></i> Movies</a></li>
                <li><a href="/filter.php"><i class="fa-solid fa-compass mr-2"></i> All Anime</a></li>
            </ul>
        </div>
    </div>
</header>

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
