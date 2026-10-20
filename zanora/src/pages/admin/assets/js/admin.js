/* ==========================================================================
   ZANORA ADMIN OS - CORE JAVASCRIPT CONTROLLER
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function() {
    initSidebarToggle();
    initUserDropdown();
    initKeyboardShortcuts();
    refreshHealth();
    setInterval(refreshStats, 20000);
});

/* Sidebar Toggle for Mobile & Desktop */
function initSidebarToggle() {
    const sidebar = document.getElementById('admin-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const mobileBtn = document.getElementById('mobile-sidebar-btn');

    let backdrop = document.getElementById('sidebar-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'sidebar-backdrop';
        backdrop.className = 'sidebar-backdrop';
        document.body.appendChild(backdrop);
    }

    function toggleSidebar(open) {
        if (!sidebar) return;
        if (typeof open === 'boolean') {
            sidebar.classList.toggle('open', open);
            backdrop.classList.toggle('active', open);
        } else {
            const isOpen = sidebar.classList.toggle('open');
            backdrop.classList.toggle('active', isOpen);
        }
    }

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                toggleSidebar(false);
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });
    }

    if (mobileBtn && sidebar) {
        mobileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
        });
    }

    backdrop.addEventListener('click', function() {
        toggleSidebar(false);
    });
}

/* User Profile Dropdown Menu */
function initUserDropdown() {
    const btn = document.getElementById('user-dropdown-btn');
    const menu = document.getElementById('user-dropdown-menu');

    if (btn && menu) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        document.addEventListener('click', function() {
            menu.classList.remove('show');
        });
    }
}

/* Tab Navigation Switcher */
function switchTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

    const btn = document.getElementById('tab-btn-' + tabId);
    const content = document.getElementById('tab-content-' + tabId);

    if (btn) btn.classList.add('active');
    if (content) content.classList.add('active');
}

/* Command Palette (Ctrl + K / Cmd + K) */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            openCommandPalette();
        }
        if (e.key === 'Escape') {
            closeCommandPalette();
        }
    });
}

function openCommandPalette() {
    const backdrop = document.getElementById('cmd-palette-backdrop');
    const input = document.getElementById('cmd-palette-input');
    if (backdrop) {
        backdrop.style.display = 'flex';
        if (input) {
            input.value = '';
            input.focus();
        }
    }
}

function closeCommandPalette(e) {
    const backdrop = document.getElementById('cmd-palette-backdrop');
    if (backdrop) {
        backdrop.style.display = 'none';
    }
}

function handleCommandInput(val) {
    const results = document.getElementById('cmd-palette-results');
    if (!results) return;
    const query = val.toLowerCase().trim();
    const items = results.querySelectorAll('.cmd-item');
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (!query || text.includes(query)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

/* Toast Notifications */
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<i class="fas fa-circle-info"></i> <span>${escapeHtml(message)}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 4000);
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

/* Real-Time Telemetry Polling */
function refreshStats() {
    fetch('/src/pages/admin/api/stats.php?action=get_live_stats')
        .then(r => r.json())
        .then(d => {
            if (document.getElementById('live-visitors')) document.getElementById('live-visitors').textContent = d.live_visitors;
            if (document.getElementById('nav-live-visitors')) document.getElementById('nav-live-visitors').textContent = d.live_visitors + ' Active';
            if (document.getElementById('total-users')) document.getElementById('total-users').textContent = d.total_users;
            if (document.getElementById('today-pageviews')) document.getElementById('today-pageviews').textContent = d.today_pageviews;
            if (document.getElementById('today-unique')) document.getElementById('today-unique').textContent = d.today_unique;
            if (document.getElementById('unread-reports')) document.getElementById('unread-reports').textContent = d.total_pending_alerts !== undefined ? d.total_pending_alerts : 0;
        }).catch(() => {});
    refreshHealth();
}

function refreshHealth() {
    fetch('/src/pages/admin/api/stats.php?action=get_server_health')
        .then(r => r.json())
        .then(d => {
            const cpuText = Math.round(d.cpu_load * 100) + '%';
            if (document.getElementById('health-cpu')) document.getElementById('health-cpu').textContent = cpuText;
            if (document.getElementById('nav-cpu-load')) document.getElementById('nav-cpu-load').textContent = 'CPU ' + cpuText;
            if (document.getElementById('db-size')) document.getElementById('db-size').textContent = d.db_size;
        }).catch(() => {});
}

/* System Action Triggers */
function triggerCacheClear() {
    if (confirm('Flush all cached data in Redis?')) {
        fetch('/src/pages/admin/api/system.php?action=clear_redis_cache')
            .then(r => r.json())
            .then(d => {
                showToast(d.message);
            });
    }
}

function triggerDbOptimize() {
    if (confirm('Optimize MySQL database tables?')) {
        fetch('/src/pages/admin/api/system.php?action=db_optimize')
            .then(r => r.json())
            .then(d => {
                showToast(d.message);
            });
    }
}
