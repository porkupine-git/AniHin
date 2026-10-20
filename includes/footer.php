<!-- Site Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-top-row">
            <div class="brand-logo">
                <i class="fa-solid fa-play text-accent"></i>
                <span>Ani<span class="brand-highlight">Xo</span>Hindi</span>
            </div>
            
            <ul class="footer-links-list">
                <li><a href="/index.php">Home</a></li>
                <li><a href="/filter.php?type=dub">Hindi Dubbed</a></li>
                <li><a href="/filter.php">Catalog</a></li>
                <li><a href="/filter.php?dmca=1">DMCA</a></li>
                <li><a href="/filter.php?contact=1">Contact</a></li>
            </ul>
        </div>
        
        <p class="footer-disclaimer">
            Disclaimer: <?= SITE_NAME ?> does not store or host any video files on its servers. All video streams are embedded via third-party iframe providers and services. For any copyright inquiries, please contact the respective media hosts.
        </p>
        
        <div class="footer-copyright">
            © <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved. Designed for Hindi Anime Community.
        </div>
    </div>
</footer>

<!-- App-Style Floating Mobile Bottom Navigation Bar (Active on <= 768px screens) -->
<nav class="mobile-bottom-nav" id="mobileBottomNav" aria-label="Mobile Navigation">
    <a href="/index.php" class="bottom-nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>
    <a href="/filter.php?type=dub" class="bottom-nav-item <?= ($_GET['type'] ?? '') == 'dub' ? 'active' : '' ?>">
        <i class="fa-solid fa-headphones"></i>
        <span>Hindi Dub</span>
    </a>
    <button type="button" class="bottom-nav-item bottom-nav-search-trigger" id="bottomNavSearchTrigger" aria-label="Search">
        <div class="bottom-nav-orb">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <span>Search</span>
    </button>
    <a href="/filter.php?status=Currently+Airing" class="bottom-nav-item <?= ($_GET['status'] ?? '') == 'Currently Airing' ? 'active' : '' ?>">
        <i class="fa-solid fa-bolt"></i>
        <span>Airing</span>
    </a>
    <a href="/filter.php" class="bottom-nav-item <?= basename($_SERVER['PHP_SELF']) == 'filter.php' && empty($_GET['type']) && empty($_GET['status']) ? 'active' : '' ?>">
        <i class="fa-solid fa-compass"></i>
        <span>Explore</span>
    </a>
</nav>

<!-- Core Vanilla JS App -->
<script src="/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../assets/js/app.js') ?>"></script>
</body>
</html>
