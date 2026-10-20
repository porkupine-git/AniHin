/**
 * AniXoHindi - Client Application Logic
 * Ultra-smooth, lightweight, and hardware-accelerated.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Live Search with Debounce & Smooth Dropdown (Desktop & Mobile)
    const searchInput = document.getElementById('site-search-input');
    const searchDropdown = document.getElementById('search-results-dropdown');
    const mobileSearchInput = document.getElementById('mobile-search-input');
    const mobileSearchDropdown = document.getElementById('mobile-search-dropdown');
    const mobileSearchBar = document.getElementById('navMobileSearchBar');
    const mobileSearchBtn = document.getElementById('navMobileSearchBtn');
    const mobileSearchCloseBtn = document.getElementById('mobileSearchCloseBtn');
    const bottomNavSearchTrigger = document.getElementById('bottomNavSearchTrigger');
    let debounceTimer;

    // Helper to render search result items
    function renderSearchResults(data, targetDropdown, currentQuery = '') {
        if (!data || data.length === 0) {
            targetDropdown.innerHTML = '<div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: 13px;"><i class="fa-solid fa-circle-notch fa-spin mr-1"></i> No anime found</div>';
        } else {
            let html = data.map(item => `
                <a href="/watch.php?anime=${encodeURIComponent(item.slug || item.id)}&ep=1" class="search-result-item">
                    <img src="${item.poster}" alt="${item.title}" class="search-result-thumb" loading="lazy">
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-weight: 700; font-size: 13px; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.title}</div>
                        ${item.hindi_title ? `<div style="font-size: 11px; color: var(--badge-dub-text); margin-top: 1px; font-weight: 600;">${item.hindi_title}</div>` : ''}
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                            ${item.type} • ★ ${item.score} ${item.is_dub ? '• <span style="color:var(--accent-peach); font-weight:700;">HINDI DUB</span>' : ''}
                        </div>
                    </div>
                </a>
            `).join('');

            if (currentQuery && currentQuery.length >= 2) {
                html += `
                    <a href="/filter.php?q=${encodeURIComponent(currentQuery)}" class="search-view-all-link">
                        <span>View all results for "<strong>${currentQuery}</strong>" in Catalog</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                `;
            }
            targetDropdown.innerHTML = html;
        }
        targetDropdown.style.display = 'block';
    }

    // Desktop search input listener
    if (searchInput && searchDropdown) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();

            if (query.length < 2) {
                searchDropdown.style.display = 'none';
                searchDropdown.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`/api/search.php?q=${encodeURIComponent(query)}`);
                    const data = await res.json();
                    renderSearchResults(data, searchDropdown, query);
                } catch (err) {
                    console.error('Search error:', err);
                }
            }, 200);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.style.display = 'none';
            }
        });
    }

    // Mobile Search Bar & Hamburger Menu Controls
    const mobileBackdrop = document.getElementById('mobileBackdropOverlay');
    const siteNavbar = document.querySelector('.site-navbar');
    const heroSection = document.getElementById('heroSpotlightDeck') || document.querySelector('.hero-spotlight-deck');

    function openMobileSearch() {
        if (!mobileSearchBar) return;
        closeMobileMenu();
        mobileSearchBar.classList.add('open');
        siteNavbar?.classList.add('search-active');
        mobileBackdrop?.classList.add('active');
        updateNavbarBackground();
        if (mobileSearchInput) {
            setTimeout(() => mobileSearchInput.focus(), 150);
        }
    }

    function closeMobileSearch() {
        if (!mobileSearchBar) return;
        mobileSearchBar.classList.remove('open');
        siteNavbar?.classList.remove('search-active');
        if (!mobileMenu?.classList.contains('open')) {
            mobileBackdrop?.classList.remove('active');
        }
        if (mobileSearchDropdown) mobileSearchDropdown.style.display = 'none';
        if (mobileSearchInput) mobileSearchInput.value = '';
        updateNavbarBackground();
    }

    function openMobileMenu() {
        if (!mobileMenu) return;
        closeMobileSearch();
        mobileMenu.classList.add('open');
        siteNavbar?.classList.add('menu-active');
        mobileBackdrop?.classList.add('active');
        if (mobileToggle) mobileToggle.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        updateNavbarBackground();
    }

    function closeMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.remove('open');
        siteNavbar?.classList.remove('menu-active');
        if (!mobileSearchBar?.classList.contains('open')) {
            mobileBackdrop?.classList.remove('active');
        }
        if (mobileToggle) mobileToggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
        updateNavbarBackground();
    }

    window.closeMobileMenu = closeMobileMenu;
    window.closeMobileSearch = closeMobileSearch;

    if (mobileSearchBtn) {
        mobileSearchBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (mobileSearchBar.classList.contains('open')) {
                closeMobileSearch();
            } else {
                openMobileSearch();
            }
        });
    }

    if (bottomNavSearchTrigger) {
        bottomNavSearchTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            if (mobileSearchBar && mobileSearchBar.classList.contains('open')) {
                closeMobileSearch();
            } else {
                openMobileSearch();
            }
        });
    }

    if (mobileSearchCloseBtn) {
        mobileSearchCloseBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            closeMobileSearch();
        });
    }

    if (mobileSearchInput && mobileSearchDropdown) {
        mobileSearchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();

            if (query.length < 2) {
                mobileSearchDropdown.style.display = 'none';
                mobileSearchDropdown.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`/api/search.php?q=${encodeURIComponent(query)}`);
                    const data = await res.json();
                    renderSearchResults(data, mobileSearchDropdown, query);
                } catch (err) {
                    console.error('Mobile search error:', err);
                }
            }, 200);
        });
    }

    // 1b. Mobile Navigation Drawer Toggle
    const mobileToggle = document.getElementById('navMobileToggle');
    const mobileMenu = document.getElementById('navMobileMenu');
    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            if (mobileMenu.classList.contains('open')) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        // Close when clicking any nav link in the drawer
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                closeMobileMenu();
            });
        });
    }

    // Tap on mobile backdrop overlay closes both menus smoothly
    if (mobileBackdrop) {
        mobileBackdrop.addEventListener('click', () => {
            closeMobileSearch();
            closeMobileMenu();
        });
    }

    // 1c. Navbar Scroll Behavior
    function updateNavbarBackground() {
        if (!siteNavbar) return;
        if (siteNavbar.classList.contains('menu-active') || siteNavbar.classList.contains('search-active')) {
            siteNavbar.classList.add('scrolled');
            return;
        }
        const scrollThreshold = heroSection ? 25 : 10;
        if (window.scrollY > scrollThreshold) {
            siteNavbar.classList.add('scrolled');
        } else {
            siteNavbar.classList.remove('scrolled');
        }
    }

    window.addEventListener('scroll', updateNavbarBackground, { passive: true });
    window.addEventListener('resize', updateNavbarBackground, { passive: true });
    updateNavbarBackground();

    // 2. Episode drawer search
    const epSearch = document.getElementById('episode-search-input');
    if (epSearch) {
        epSearch.addEventListener('input', (e) => {
            const val = e.target.value.trim().toLowerCase();
            const items = document.querySelectorAll('.ep-box-item');
            items.forEach(item => {
                const epNum = item.getAttribute('data-ep');
                if (epNum.includes(val)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // 3. Global Escape key closes Modals & Search
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAuthModal();
            if (searchDropdown) searchDropdown.style.display = 'none';
        }
    });

    // 4. Modal overlay backdrop click closes modal
    const modalOverlay = document.getElementById('authModal');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeAuthModal();
            }
        });
    }

    // 5. Initialize 3D Infinite Fan-Deck Hero Showcase
    initSpotlightDeck();
});


// Auth Modal Helpers
function openAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Watch Page Episode Switching
function switchEpisode(epNumber, embedUrl, title) {
    const frame = document.getElementById('anime-embed-frame');
    const notice = document.getElementById('current-ep-badge');
    const epTitleEl = document.getElementById('current-ep-title');

    // Update iframe embed source
    if (frame && embedUrl) {
        frame.src = embedUrl;
    }

    if (notice) notice.innerText = `Episode ${epNumber}`;
    if (epTitleEl) epTitleEl.innerText = title || `Episode ${epNumber}`;

    // Update active episode box
    document.querySelectorAll('.ep-box-item').forEach(el => el.classList.remove('active'));
    const activeBtn = document.querySelector(`.ep-box-item[data-ep="${epNumber}"]`);
    if (activeBtn) activeBtn.classList.add('active');

    // Update browser URL without reload
    const url = new URL(window.location);
    url.searchParams.set('ep', epNumber);
    window.history.pushState({}, '', url);
}

// Switch server
function switchServer(serverName, embedUrl, btn) {
    document.querySelectorAll('.server-pill-btn').forEach(b => b.classList.remove('active'));
    const target = btn || (window.event ? window.event.currentTarget || window.event.target : null);
    if (target) target.classList.add('active');

    const frame = document.getElementById('anime-embed-frame');
    if (frame && embedUrl) {
        frame.src = embedUrl;
    }
}

// Theater mode expand
function toggleTheaterMode() {
    const layout = document.querySelector('.watch-layout');
    const btn = document.getElementById('theater-mode-btn');
    if (layout) {
        layout.classList.toggle('theater-mode');
        if (layout.classList.contains('theater-mode')) {
            btn.innerHTML = '<i class="fa-solid fa-compress mr-1"></i> Normal';
        } else {
            btn.innerHTML = '<i class="fa-solid fa-expand mr-1"></i> Expand';
        }
    }
}

// ==========================================================================
// 3D Infinite Curved Fan-Deck Hero Showcase Controller
// ==========================================================================
function initSpotlightDeck() {
    const deckStage = document.getElementById('heroSpotlightDeck');
    const stageWrapper = document.querySelector('.deck-stage-wrapper');
    const track = document.getElementById('deckCardsTrack');
    if (!deckStage || !track || !window.SPOTLIGHT_DECK_DATA || window.SPOTLIGHT_DECK_DATA.length === 0) {
        return;
    }

    const data = window.SPOTLIGHT_DECK_DATA;
    const cards = Array.from(track.querySelectorAll('.deck-card-item'));
    const total = data.length;

    // Elements
    const backdrop1 = document.getElementById('deckBackdrop1');
    const backdrop2 = document.getElementById('deckBackdrop2');
    let activeBackdrop = 1;

    const activeNumEl = document.getElementById('deckActiveNum');
    const watermarkEl = document.getElementById('deckWatermark');
    const titleEl = document.getElementById('deckTitle');
    const titleLogoEl = document.getElementById('deckTitleLogo');
    const hindiTitleEl = document.getElementById('deckHindiTitle');
    const metaFormatEl = document.getElementById('deckMetaFormat');
    const metaScoreEl = document.getElementById('deckMetaScore');
    const metaEpsEl = document.getElementById('deckMetaEpisodes');
    const metaYearEl = document.getElementById('deckMetaYear');
    const metaDubEl = document.getElementById('deckMetaDub');
    const genresEl = document.getElementById('deckGenres');
    const watchBtn = document.getElementById('deckWatchBtn');

    const prevBtn = document.getElementById('deckPrevBtn');
    const nextBtn = document.getElementById('deckNextBtn');

    // Mathematical canonical modulo (handles negative numbers flawlessly)
    function canonicalMod(n, m) {
        return ((n % m) + m) % m;
    }

    // Responsive metrics dynamically tuned for all 6 desktop & mobile tiers
    // 1025px-1200px, 1280px, 1366px-1440px, 1536px, 1920px FHD, 2560px-3840px 2K/4K/Ultrawide
    function getDeckMetrics() {
        const w = window.innerWidth;
        const h = window.innerHeight;
        let base;

        if (w <= 600) {
            // Mobile (<=600px)
            base = {
                stepX: 68,
                centerScale: 1.15,
                scaleStep: 0.16,
                maxOffset: 3.2,
                tyPeak: -12,
                tyFactor: 14,
                tzBase: 80,
                tzStep: 30,
                rotZFactor: 4.8,
                rotYFactor: 10
            };
        } else if (w <= 1024) {
            // Tablet Portrait / Compact (601px - 1024px)
            base = {
                stepX: 78,
                centerScale: 1.18,
                scaleStep: 0.145,
                maxOffset: 3.8,
                tyPeak: -16,
                tyFactor: 15,
                tzBase: 95,
                tzStep: 34,
                rotZFactor: 5.2,
                rotYFactor: 11
            };
        } else if (w <= 1200) {
            // Tier 1: 1025px – 1200px (Large Tablets Landscape, Netbooks, Small Laptops)
            base = {
                stepX: 84,
                centerScale: 1.20,
                scaleStep: 0.13,
                maxOffset: 4.3,
                tyPeak: -18,
                tyFactor: 16,
                tzBase: 110,
                tzStep: 38,
                rotZFactor: 5.4,
                rotYFactor: 12
            };
        } else if (w <= 1365) {
            // Tier 2: 1201px – 1365px (Standard Laptops & Compact Desktops - 1280px)
            base = {
                stepX: 88,
                centerScale: 1.22,
                scaleStep: 0.125,
                maxOffset: 4.4,
                tyPeak: -20,
                tyFactor: 17,
                tzBase: 120,
                tzStep: 40,
                rotZFactor: 5.6,
                rotYFactor: 12.5
            };
        } else if (w <= 1535) {
            // Tier 3: 1366px – 1535px (Widescreen Laptops & Standard Desktops - 1366px / 1440px)
            base = {
                stepX: 94,
                centerScale: 1.24,
                scaleStep: 0.12,
                maxOffset: 4.5,
                tyPeak: -22,
                tyFactor: 18,
                tzBase: 125,
                tzStep: 42,
                rotZFactor: 5.8,
                rotYFactor: 13
            };
        } else if (w <= 1919) {
            // Tier 4: 1536px – 1919px (Modern High-Density Laptops - 1536px / 1600px)
            base = {
                stepX: 102,
                centerScale: 1.25,
                scaleStep: 0.115,
                maxOffset: 4.6,
                tyPeak: -24,
                tyFactor: 19,
                tzBase: 130,
                tzStep: 44,
                rotZFactor: 6.0,
                rotYFactor: 13.5
            };
        } else if (w <= 2559) {
            // Tier 5: 1920px – 2559px (Full HD 1080p Desktop Monitors - 1920px)
            base = {
                stepX: 114,
                centerScale: 1.26,
                scaleStep: 0.11,
                maxOffset: 4.7,
                tyPeak: -26,
                tyFactor: 20,
                tzBase: 140,
                tzStep: 46,
                rotZFactor: 6.2,
                rotYFactor: 14
            };
        } else if (w <= 3199) {
            // Tier 6: 2560px – 3199px (2K QHD, Ultra-Wide 3440)
            base = {
                stepX: 135,
                centerScale: 1.28,
                scaleStep: 0.105,
                maxOffset: 5.0,
                tyPeak: -30,
                tyFactor: 22,
                tzBase: 155,
                tzStep: 50,
                rotZFactor: 6.5,
                rotYFactor: 14.5
            };
        } else {
            // Tier 7: 3200px+ (4K UHD Displays & 50% / 33% Zoom)
            base = {
                stepX: 160,
                centerScale: 1.30,
                scaleStep: 0.10,
                maxOffset: 5.2,
                tyPeak: -34,
                tyFactor: 24,
                tzBase: 170,
                tzStep: 55,
                rotZFactor: 6.8,
                rotYFactor: 15
            };
        }

        // Adaptive height scaling (Adapts seamlessly when Chrome is zoomed in or on short viewports)
        if (h < 520) {
            base.tyPeak = Math.max(-6, base.tyPeak + 20);
            base.tyFactor = base.tyFactor * 0.52;
            base.stepX = Math.round(base.stepX * 0.78);
            base.centerScale = Math.max(1.05, base.centerScale * 0.85);
        } else if (h < 620) {
            base.tyPeak = Math.max(-10, base.tyPeak + 14);
            base.tyFactor = base.tyFactor * 0.70;
            base.stepX = Math.round(base.stepX * 0.88);
            base.centerScale = Math.max(1.08, base.centerScale * 0.92);
        } else if (h < 730) {
            base.tyPeak = Math.max(-14, base.tyPeak + 8);
            base.tyFactor = base.tyFactor * 0.85;
            base.stepX = Math.round(base.stepX * 0.94);
        }

        return base;
    }

    function getStepX() {
        return getDeckMetrics().stepX;
    }

    // Physics Engine State
    let currentPos = 0;       // Continuous floating position (e.g. 0.0, 1.45, -2.1)
    let animId = null;        // Active requestAnimationFrame ID
    let autoPlayTimer = null;
    let lastRenderedInfoIndex = -1;

    // ----------------------------------------------------------------------
    // Continuous 3D Parabolic Fan Deck Rendering
    // ----------------------------------------------------------------------
    function renderDeck(pos) {
        const m = getDeckMetrics();
        const stepX = m.stepX;
        const maxOffset = m.maxOffset;

        const nearestCenter = canonicalMod(Math.round(pos), total);

        cards.forEach((card, i) => {
            let diff = canonicalMod(i - pos, total);
            if (diff > total / 2) diff -= total;
            const absOffset = Math.abs(diff);

            if (absOffset > maxOffset) {
                card.style.opacity = '0';
                card.style.pointerEvents = 'none';
                card.style.transform = `translate3d(${diff * (stepX * 1.2)}px, 80px, -150px) scale(0.4) rotateY(${diff > 0 ? -45 : 45}deg)`;
                card.classList.remove('active');
                return;
            }

            card.style.pointerEvents = 'auto';

            // Exactly ONE card is active at all times - the one nearest to center
            const isCenterCard = (i === nearestCenter);
            if (isCenterCard) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }

            // Iconic "Ulta U" (Inverted U Arch) Parabolic Drop:
            const ty = Math.pow(absOffset, 1.48) * m.tyFactor + m.tyPeak;

            // Fan tilt (rotZ) along the inverted U dome:
            const sign = diff >= 0 ? 1 : -1;
            const rotZ = sign * Math.min(26, absOffset * m.rotZFactor);

            // 3D perspective turn (rotY) facing the center stage:
            const rotY = -sign * Math.min(40, absOffset * m.rotYFactor);

            // Scale along the arch:
            let scale = Math.max(0.48, m.centerScale - (absOffset * m.scaleStep));

            // Z-Depth along the arch:
            let tz = m.tzBase - (absOffset * m.tzStep);

            // Progressive aesthetic opacity falloff matching reference image:
            // Center card: 1.0 -> Card 1: ~0.86 -> Card 2: ~0.64 -> Card 3: ~0.38 -> Card 4: ~0.10 -> 0.0
            let opacity = 0;
            if (absOffset <= maxOffset) {
                const norm = absOffset / (maxOffset - 0.2);
                opacity = Math.max(0, 1 - Math.pow(Math.min(1, norm), 1.35) * 0.98);
            }

            // Strict Depth Layering:
            let zIndex = Math.round(50 - absOffset * 10);
            if (isCenterCard) {
                tz += 25;
                scale += 0.04;
                zIndex = 75;
            }

            const tx = diff * stepX;

            card.style.zIndex = zIndex;
            card.style.opacity = opacity;
            card.style.transform = `translate3d(${tx}px, ${ty}px, ${tz}px) rotateY(${rotY}deg) rotateZ(${rotZ}deg) scale(${scale})`;
        });

        // Real-time Top Indicators
        const padNum = String(nearestCenter + 1).padStart(2, '0');
        if (activeNumEl) activeNumEl.textContent = padNum;
        if (watermarkEl) watermarkEl.textContent = padNum;

        // Dynamic Info Update (updates smoothly when nearest center card changes)
        if (nearestCenter !== lastRenderedInfoIndex) {
            updateMetadata(nearestCenter);
        }
    }

    // ----------------------------------------------------------------------
    // Update Anime Info & Background Crossfade
    // ----------------------------------------------------------------------
    let backdropDebounceTimer = null;
    function updateMetadata(index) {
        lastRenderedInfoIndex = index;
        const currentData = data[index];
        if (!currentData) return;

        // Title and Official TMDB Anime Logo
        if (currentData.logo) {
            if (titleLogoEl) {
                titleLogoEl.src = currentData.logo;
                titleLogoEl.alt = currentData.title;
                titleLogoEl.style.display = 'block';
            }
            if (titleEl) {
                titleEl.style.display = 'none';
            }
        } else {
            if (titleLogoEl) {
                titleLogoEl.style.display = 'none';
            }
            if (titleEl) {
                titleEl.textContent = currentData.title;
                titleEl.style.display = 'block';
            }
        }

        if (hindiTitleEl) {
            if (currentData.hindi_title) {
                hindiTitleEl.innerHTML = `<i class="fa-solid fa-language mr-1"></i> ${currentData.hindi_title}`;
            } else {
                hindiTitleEl.innerHTML = '';
            }
        }

        // Meta Bar
        if (metaFormatEl) metaFormatEl.textContent = `• ${currentData.format}`;
        if (metaScoreEl) metaScoreEl.textContent = currentData.score_pct;
        if (metaEpsEl) metaEpsEl.textContent = currentData.episodes;
        if (metaYearEl) metaYearEl.textContent = currentData.year;
        if (metaDubEl) {
            metaDubEl.style.display = currentData.is_dub ? 'inline-flex' : 'none';
        }

        // Genres
        if (genresEl) genresEl.textContent = currentData.genres;

        // Watch CTA Button Link
        if (watchBtn) {
            watchBtn.href = `/watch.php?anime=${encodeURIComponent(currentData.slug)}&ep=1`;
        }

        // Debounced Backdrop Crossfade (Eliminates texture upload stalls during drag & momentum glide)
        clearTimeout(backdropDebounceTimer);
        backdropDebounceTimer = setTimeout(() => {
            const newBackdropUrl = currentData.banner || currentData.poster;
            if (backdrop1 && backdrop2) {
                if (activeBackdrop === 1) {
                    backdrop2.style.backgroundImage = `url('${newBackdropUrl}')`;
                    backdrop2.classList.add('active');
                    backdrop1.classList.remove('active');
                    activeBackdrop = 2;
                } else {
                    backdrop1.style.backgroundImage = `url('${newBackdropUrl}')`;
                    backdrop1.classList.add('active');
                    backdrop2.classList.remove('active');
                    activeBackdrop = 1;
                }
            }
        }, isDragging ? 160 : 70);
    }

    // ----------------------------------------------------------------------
    // Critically Damped Spring Animation (For Buttons, Keys & Card Clicks)
    // ----------------------------------------------------------------------
    function startSpringAnimation(target, initialVelocity = 0) {
        if (animId) {
            cancelAnimationFrame(animId);
            animId = null;
        }

        let pos = currentPos;
        let vel = initialVelocity; // in cards / second
        let lastT = performance.now();

        const stiffness = 85;
        const damping = 18.5;

        function step(now) {
            let dt = (now - lastT) / 1000;
            if (dt > 0.04) dt = 0.04;
            lastT = now;

            const springForce = -stiffness * (pos - target);
            const dampingForce = -damping * vel;
            const accel = springForce + dampingForce;

            vel += accel * dt;
            pos += vel * dt;

            currentPos = pos;
            renderDeck(currentPos);

            // Settle threshold: snap cleanly when sufficiently close and slow
            if (Math.abs(pos - target) < 0.002 && Math.abs(vel) < 0.03) {
                currentPos = target;
                renderDeck(currentPos);
                animId = null;
                return;
            }

            animId = requestAnimationFrame(step);
        }

        animId = requestAnimationFrame(step);
    }

    // ----------------------------------------------------------------------
    // High-Fidelity Momentum Inertia & Magnetic Snap Physics Animation
    // Glides according to release speed ("thoda dair tak scroll hona chaiye")
    // ----------------------------------------------------------------------
    function startMomentumAnimation(initialVelocity = 0) {
        if (animId) {
            cancelAnimationFrame(animId);
            animId = null;
        }

        let pos = currentPos;
        let vel = initialVelocity; // in cards / second
        let lastT = performance.now();
        const absVel = Math.abs(vel);

        // Low speed or static release: snap directly to nearest integer card
        if (absVel < 0.45) {
            startSpringAnimation(Math.round(pos), vel);
            return;
        }

        // Project natural landing position based on release speed:
        // Lower friction decay gives sustained coasting glide (~0.9s - 1.4s based on throw speed)
        const frictionDecay = 1.95;
        const rawFling = vel / frictionDecay;
        const maxFling = 5.0; // Max 5 cards continuous throw
        const clampedFling = Math.sign(rawFling) * Math.min(maxFling, Math.abs(rawFling));
        const finalTarget = Math.round(pos + clampedFling);

        // Rescale initial velocity to match clamped fling distance if needed
        if (Math.abs(rawFling) > maxFling) {
            vel = Math.sign(vel) * (maxFling * frictionDecay);
        }

        let isCoasting = true;

        function step(now) {
            let dt = (now - lastT) / 1000;
            if (dt > 0.035) dt = 0.035;
            lastT = now;

            if (isCoasting) {
                // Free momentum deceleration based on initial speed
                vel -= vel * frictionDecay * dt;
                pos += vel * dt;

                // When speed drops below 0.85 or distance to target is small, hand over to magnetic spring
                const distToTarget = Math.abs(pos - finalTarget);
                if (Math.abs(vel) < 0.85 || distToTarget < 0.38) {
                    isCoasting = false;
                }
            } else {
                // Critically damped magnetic spring settles smoothly onto finalTarget
                const springForce = -42 * (pos - finalTarget);
                const dampingForce = -13.0 * vel;
                const accel = springForce + dampingForce;

                vel += accel * dt;
                pos += vel * dt;

                if (Math.abs(pos - finalTarget) < 0.002 && Math.abs(vel) < 0.02) {
                    currentPos = finalTarget;
                    renderDeck(currentPos);
                    animId = null;
                    return;
                }
            }

            currentPos = pos;
            renderDeck(currentPos);
            animId = requestAnimationFrame(step);
        }

        animId = requestAnimationFrame(step);
    }

    // ----------------------------------------------------------------------
    // Direct 1-to-1 Interactive Dragging Engine with Momentum Inertia
    // ----------------------------------------------------------------------
    let isPointerDown = false;
    let isDragging = false;
    let dragStartX = 0;
    let dragStartPos = 0;
    let lastMoveX = 0;
    let lastMoveTime = 0;
    let dragVelocity = 0; // Smoothed cards per second

    // Disable native HTML5 image dragging
    track.querySelectorAll('img').forEach(img => {
        img.ondragstart = () => false;
    });

    function handlePointerDown(e) {
        // Ignore clicks on control buttons or links
        if (e.target.closest('.deck-nav-btn') || e.target.closest('a') || e.target.closest('button')) {
            return;
        }

        // Interrupt any ongoing animation immediately for seamless handoff
        if (animId) {
            cancelAnimationFrame(animId);
            animId = null;
        }

        isPointerDown = true;
        isDragging = false;
        dragStartX = e.clientX;
        dragStartPos = currentPos;
        lastMoveX = e.clientX;
        lastMoveTime = performance.now();
        dragVelocity = 0;

        stopAutoplay();

        try {
            track.setPointerCapture(e.pointerId);
        } catch (err) {}

        track.classList.add('grabbing');
        if (stageWrapper) stageWrapper.classList.add('grabbing');
    }

    function handlePointerMove(e) {
        if (!isPointerDown) return;

        const now = performance.now();
        const diffX = e.clientX - dragStartX;
        const cardSpacing = getStepX();

        // Calculate smooth velocity using Exponential Moving Average (EMA)
        const dt = (now - lastMoveTime) / 1000;
        if (dt > 0.006) {
            const dx = e.clientX - lastMoveX;
            const instantVel = - (dx / cardSpacing) / dt; // cards per second
            dragVelocity = dragVelocity * 0.45 + instantVel * 0.55;
            lastMoveX = e.clientX;
            lastMoveTime = now;
        }

        // Threshold of 4px to distinguish deliberate drag from micro-jitter
        if (!isDragging && Math.abs(diffX) > 4) {
            isDragging = true;
        }

        if (isDragging) {
            // Direct 1-to-1 scrubbing: movement matches physical card spacing exactly!
            currentPos = dragStartPos - (diffX / cardSpacing);
            renderDeck(currentPos);
        }
    }

    function handlePointerUp(e) {
        if (!isPointerDown) return;
        isPointerDown = false;

        track.classList.remove('grabbing');
        if (stageWrapper) stageWrapper.classList.remove('grabbing');

        try {
            track.releasePointerCapture(e.pointerId);
        } catch (err) {}

        // Decay velocity if held still before releasing
        const timeSinceMove = performance.now() - lastMoveTime;
        if (timeSinceMove > 90) {
            dragVelocity = 0;
        }

        if (isDragging) {
            startMomentumAnimation(dragVelocity);
        } else {
            // No drag occurred, ensure locked to nearest integer card
            startSpringAnimation(Math.round(currentPos), 0);
        }

        startAutoplay();

        // Delay resetting isDragging so card click listeners can ignore the drag release
        setTimeout(() => {
            isDragging = false;
        }, 120);
    }

    // Attach pointer events to track
    track.addEventListener('pointerdown', handlePointerDown);
    track.addEventListener('pointermove', handlePointerMove);
    track.addEventListener('pointerup', handlePointerUp);
    track.addEventListener('pointercancel', handlePointerUp);

    // Fallback window pointer listeners in case pointer capture is unsupported
    window.addEventListener('pointermove', (e) => {
        if (isPointerDown && !track.hasPointerCapture?.(e.pointerId)) {
            handlePointerMove(e);
        }
    });
    window.addEventListener('pointerup', (e) => {
        if (isPointerDown && !track.hasPointerCapture?.(e.pointerId)) {
            handlePointerUp(e);
        }
    });

    // Mobile Direct Touch Swipe Gestures with Direction Lock (Swipe left/right anywhere on hero)
    let touchStartX = 0;
    let touchStartY = 0;
    let touchStartTime = 0;
    let isHorizontalSwipe = false;

    deckStage.addEventListener('touchstart', (e) => {
        if (e.target.closest('a') || e.target.closest('button')) return;
        const touch = e.touches[0];
        touchStartX = touch.clientX;
        touchStartY = touch.clientY;
        touchStartTime = performance.now();
        isHorizontalSwipe = false;
        stopAutoplay();
    }, { passive: true });

    deckStage.addEventListener('touchmove', (e) => {
        if (e.touches.length !== 1) return;
        const touch = e.touches[0];
        const dx = touch.clientX - touchStartX;
        const dy = touch.clientY - touchStartY;

        if (!isHorizontalSwipe) {
            // Direction lock: check if horizontal intent exceeds vertical intent
            if (Math.abs(dx) > 10 && Math.abs(dx) > Math.abs(dy)) {
                isHorizontalSwipe = true;
                isDragging = true;
            }
        }

        if (isHorizontalSwipe) {
            isDragging = true;
            if (e.cancelable) e.preventDefault();
        }
    }, { passive: false });

    deckStage.addEventListener('touchend', (e) => {
        if (!isHorizontalSwipe) {
            startAutoplay();
            return;
        }
        const touch = e.changedTouches[0];
        const dx = touch.clientX - touchStartX;
        const dt = performance.now() - touchStartTime;

        if (Math.abs(dx) > 35 || (Math.abs(dx) > 22 && dt < 260)) {
            if (dx < 0) {
                // Swipe Left -> Next Anime
                startSpringAnimation(Math.round(currentPos) + 1, -1.8);
            } else {
                // Swipe Right -> Previous Anime
                startSpringAnimation(Math.round(currentPos) - 1, 1.8);
            }
        } else {
            startSpringAnimation(Math.round(currentPos), 0);
        }
        startAutoplay();

        // Lock isDragging for 250ms to swallow any trailing synthetic click on mobile
        setTimeout(() => {
            isDragging = false;
            isHorizontalSwipe = false;
        }, 250);
    }, { passive: true });

    // Card click event (tap side card to center it, click center card to watch)
    cards.forEach((card, idx) => {
        card.addEventListener('click', (e) => {
            if (isDragging) {
                e.preventDefault();
                e.stopPropagation();
                return;
            }

            const activeIndex = canonicalMod(Math.round(currentPos), total);
            if (activeIndex !== idx) {
                // Shortest modular circular step to clicked card
                let diff = canonicalMod(idx - Math.round(currentPos), total);
                if (diff > total / 2) diff -= total;
                startSpringAnimation(Math.round(currentPos) + diff, 0);
            } else {
                const cur = data[activeIndex];
                if (cur && cur.slug) {
                    window.location.href = `/watch.php?anime=${encodeURIComponent(cur.slug)}&ep=1`;
                }
            }
        });
    });

    // Arrow navigation
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            startSpringAnimation(Math.round(currentPos) - 1, 0);
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            startSpringAnimation(Math.round(currentPos) + 1, 0);
        });
    }

    // Keyboard arrow navigation
    document.addEventListener('keydown', (e) => {
        if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') return;
        if (e.key === 'ArrowLeft') startSpringAnimation(Math.round(currentPos) - 1, 0);
        if (e.key === 'ArrowRight') startSpringAnimation(Math.round(currentPos) + 1, 0);
    });

    // Trackpad / Horizontal wheel scrubbing
    let wheelTimer = null;
    deckStage.addEventListener('wheel', (e) => {
        if (Math.abs(e.deltaX) > 15 || (e.shiftKey && Math.abs(e.deltaY) > 15)) {
            const delta = Math.abs(e.deltaX) > 15 ? e.deltaX : e.deltaY;
            if (animId) {
                cancelAnimationFrame(animId);
                animId = null;
            }
            stopAutoplay();

            const cardSpacing = getStepX();
            currentPos += (delta / cardSpacing) * 0.35;
            renderDeck(currentPos);

            clearTimeout(wheelTimer);
            wheelTimer = setTimeout(() => {
                startSpringAnimation(Math.round(currentPos), 0);
                startAutoplay();
            }, 100);
        }
    }, { passive: true });

    // Autoplay Timer (Every 5 seconds, pauses on hover / interaction)
    function startAutoplay() {
        stopAutoplay();
        autoPlayTimer = setInterval(() => {
            startSpringAnimation(Math.round(currentPos) + 1, 0);
        }, 5000);
    }

    function stopAutoplay() {
        if (autoPlayTimer) clearInterval(autoPlayTimer);
    }

    deckStage.addEventListener('mouseenter', stopAutoplay);
    deckStage.addEventListener('mouseleave', startAutoplay);

    // Window resize debouncer
    window.addEventListener('resize', () => {
        renderDeck(currentPos);
    });

    // Initialize first state
    renderDeck(0);
    startAutoplay();
}

