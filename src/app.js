// Main Application Entry & Controller

import { renderHeader, initHeaderEvents } from './components/common/Header.js';
import { renderFooter } from './components/common/Footer.js';
import { renderHomePage } from './pages/homePage.js';
import { renderWatchPage } from './pages/watchPage.js';
import { renderBrowsePage } from './pages/browsePage.js';

export function initApp() {
    console.log('🚀 HiAnime / AnixoHindi Application Bootstrapped.');

    // 1. Mount Header & Sidebar
    const headerContainer = document.getElementById('header-mount');
    if (headerContainer) {
        headerContainer.innerHTML = renderHeader();
        initHeaderEvents();
    }

    // 2. Mount Footer
    const footerContainer = document.getElementById('footer-mount');
    if (footerContainer) {
        footerContainer.innerHTML = renderFooter();
    }

    // 3. Route to Page based on pathname
    const path = window.location.pathname.toLowerCase();

    if (path.includes('watch.html')) {
        renderWatchPage();
    } else if (path.includes('browse.html') || path.includes('filter.html')) {
        renderBrowsePage();
    } else {
        renderHomePage();
    }

    // 4. Global UI features: Back to Top button
    setupGlobalUI();
}

function setupGlobalUI() {
    let backToTop = document.getElementById('back-to-top');
    if (!backToTop) {
        backToTop = document.createElement('div');
        backToTop.id = 'back-to-top';
        backToTop.className = 'btn-scroll-top';
        backToTop.innerHTML = '<i class="fas fa-chevron-up"></i>';
        document.body.appendChild(backToTop);
    }

    window.addEventListener('scroll', () => {
        if (window.scrollY > 400) {
            backToTop.style.display = 'flex';
        } else {
            backToTop.style.display = 'none';
        }
    });

    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

// Auto-run when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
