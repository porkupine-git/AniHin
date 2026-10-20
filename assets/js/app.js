/**
 * AniXoHindi - Client Application Logic
 * Ultra-smooth, lightweight, and hardware-accelerated.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Live Search with Debounce & Smooth Dropdown
    const searchInput = document.getElementById('site-search-input');
    const searchDropdown = document.getElementById('search-results-dropdown');
    let debounceTimer;

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

                    if (!data || data.length === 0) {
                        searchDropdown.innerHTML = '<div style="padding: 14px; text-align: center; color: var(--text-muted); font-size: 13px;"><i class="fa-solid fa-circle-notch fa-spin mr-1"></i> No anime found</div>';
                    } else {
                        searchDropdown.innerHTML = data.map(item => `
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
                    }
                    searchDropdown.style.display = 'block';
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
