// Browse Page Filter Component

import { allGenres } from '../../data/animeDatabase.js';

export function renderFilterBar(currentFilters = {}) {
    return `
    <div class="category_filter mb-4 p-4" style="background:#201f31; border-radius:10px;">
        <form id="anime-filter-form">
            <div class="row g-3 mb-3">
                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="text-muted mb-1" style="font-size:12px; font-weight:600;">TYPE</label>
                    <select class="custom-select custom-select-sm" name="type" style="background:#191826; border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:6px;">
                        <option value="all">All Types</option>
                        <option value="TV">TV Series</option>
                        <option value="Movie">Movie</option>
                        <option value="OVA">OVA</option>
                        <option value="ONA">ONA</option>
                        <option value="Special">Special</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="text-muted mb-1" style="font-size:12px; font-weight:600;">STATUS</label>
                    <select class="custom-select custom-select-sm" name="status" style="background:#191826; border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:6px;">
                        <option value="all">All Status</option>
                        <option value="Finished">Finished Airing</option>
                        <option value="Currently">Currently Airing</option>
                        <option value="Not Yet">Not Yet Aired</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="text-muted mb-1" style="font-size:12px; font-weight:600;">LANGUAGE</label>
                    <select class="custom-select custom-select-sm" name="language" style="background:#191826; border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:6px;">
                        <option value="all">All Languages</option>
                        <option value="dub">Hindi Dubbed (Featured)</option>
                        <option value="sub">Japanese Subbed</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="text-muted mb-1" style="font-size:12px; font-weight:600;">SORT BY</label>
                    <select class="custom-select custom-select-sm" name="sort" style="background:#191826; border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:6px;">
                        <option value="latest">Latest Updated</option>
                        <option value="score">Score / Rating</option>
                        <option value="name">Name A-Z</option>
                        <option value="popular">Most Popular</option>
                    </select>
                </div>
            </div>

            <div class="genres-filter-box mb-3">
                <label class="text-muted mb-2 d-block" style="font-size:12px; font-weight:600;">GENRES</label>
                <div class="d-flex flex-wrap" style="gap:6px; max-height:160px; overflow-y:auto; padding:8px; background:rgba(0,0,0,0.25); border-radius:6px;">
                    ${allGenres.map(g => `
                    <label class="genre-pill-checkbox m-0" style="cursor:pointer;">
                        <input type="checkbox" name="genre" value="${g}" class="d-none">
                        <span class="badge py-2 px-3" style="background:rgba(255,255,255,0.06); color:#bbb; border-radius:15px; font-weight:500; font-size:11.5px; transition:all 0.2s;">
                            ${g}
                        </span>
                    </label>
                    `).join('')}
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between">
                <div class="text-muted" style="font-size:12px;">
                    <span id="filter-results-count">Showing all anime</span>
                </div>
                <div>
                    <button type="reset" id="reset-filter-btn" class="btn btn-sm btn-dark mr-2" style="font-size:12px; border-radius:20px; padding:6px 18px;">
                        Reset
                    </button>
                    <button type="submit" class="btn btn-sm" style="background:#ffbade; color:#111; font-weight:700; border-radius:20px; padding:6px 24px;">
                        <i class="fas fa-filter mr-1"></i> Apply Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
    `;
}

export function initFilterEvents(onFilterChange) {
    const form = document.getElementById('anime-filter-form');
    if (!form) return;

    // Toggle active appearance on genre pills
    form.querySelectorAll('.genre-pill-checkbox input').forEach(input => {
        input.addEventListener('change', function () {
            const span = this.nextElementSibling;
            if (this.checked) {
                span.style.background = '#ffbade';
                span.style.color = '#111';
                span.style.fontWeight = '700';
            } else {
                span.style.background = 'rgba(255,255,255,0.06)';
                span.style.color = '#bbb';
                span.style.fontWeight = '500';
            }
        });
    });

    const triggerFilter = () => {
        const formData = new FormData(form);
        const criteria = {
            type: formData.get('type'),
            status: formData.get('status'),
            language: formData.get('language'),
            sort: formData.get('sort'),
            genre: formData.getAll('genre')
        };
        if (onFilterChange) onFilterChange(criteria);
    };

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        triggerFilter();
    });

    form.querySelectorAll('select').forEach(sel => {
        sel.addEventListener('change', triggerFilter);
    });

    form.addEventListener('reset', () => {
        setTimeout(() => {
            form.querySelectorAll('.genre-pill-checkbox span').forEach(span => {
                span.style.background = 'rgba(255,255,255,0.06)';
                span.style.color = '#bbb';
                span.style.fontWeight = '500';
            });
            triggerFilter();
        }, 10);
    });
}
