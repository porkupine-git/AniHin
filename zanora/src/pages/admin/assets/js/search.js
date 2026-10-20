/* ==========================================================================
   ZANORA ADMIN OS - SEARCH, FILTER & EXPORT UTILITIES
   ========================================================================== */

/* Client-Side Data Table Filtering */
function filterTable(tableId, query) {
    const table = document.getElementById(tableId);
    if (!table) return;
    const q = query.toLowerCase().trim();
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (!q || text.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

/* User Account AJAX Live Search */
function searchUsersLive(query) {
    if (query.length < 2) return;
    fetch('/src/pages/admin/api/users.php?action=search_users&query=' + encodeURIComponent(query))
        .then(r => r.json())
        .then(users => {
            const tbody = document.getElementById('users-table-body');
            if (!tbody) return;
            tbody.innerHTML = '';

            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#71717a;">No users found matching query.</td></tr>';
                return;
            }

            users.forEach(user => {
                let badges = '';
                if (user.is_verified) badges += '<span class="admin-badge badge-verified"><i class="fas fa-check-circle"></i> Verified</span> ';
                if (user.is_admin) badges += '<span class="admin-badge badge-admin"><i class="fas fa-crown"></i> Admin</span> ';
                if (user.is_blocked) badges += '<span class="admin-badge badge-blocked"><i class="fas fa-ban"></i> Blocked</span>';

                const avatarSrc = user.avatar || 'https://via.placeholder.com/40';
                const createdDate = new Date(user.created_at).toLocaleDateString();

                const row = `<tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="${avatarSrc}" style="width:32px; height:32px; border-radius:50%; border:1px solid rgba(255,255,255,0.1);">
                            <div>
                                <strong style="color:#fff;">${escapeHtml(user.username)}</strong>
                                <div style="font-size:11px; color:#71717a;">Rank: ${escapeHtml(user.rank || 'Member')} (${user.points} pts)</div>
                            </div>
                        </div>
                    </td>
                    <td>${escapeHtml(user.email)}</td>
                    <td>${createdDate}</td>
                    <td>${badges}</td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="?page=users&edit_user=${user.id}" class="btn-icon-action" title="Edit User"><i class="fas fa-gear"></i></a>
                            <button onclick="confirmDeleteUser(${user.id})" class="btn-icon-action danger" title="Delete User"><i class="fas fa-trash-can"></i></button>
                        </div>
                    </td>
                </tr>`;
                tbody.innerHTML += row;
            });
        });
}

/* Data Table Export Tools */
function exportTableToCSV(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        let cols = row.querySelectorAll('td, th');
        let rowData = [];
        cols.forEach(col => {
            // Strip HTML tags
            let text = col.innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/"/g, '""');
            rowData.push('"' + text + '"');
        });
        csv.push(rowData.join(','));
    });

    const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', tableId + '_export.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportTableToJSON(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const headers = [];
    const headCols = table.querySelectorAll('thead th');
    headCols.forEach(col => headers.push(col.innerText.trim()));

    const data = [];
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const rowData = {};
        const cols = row.querySelectorAll('td');
        cols.forEach((col, idx) => {
            if (headers[idx]) {
                rowData[headers[idx]] = col.innerText.trim();
            }
        });
        data.push(rowData);
    });

    const jsonStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(data, null, 2));
    const link = document.createElement('a');
    link.setAttribute('href', jsonStr);
    link.setAttribute('download', tableId + '_export.json');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function copyTableToClipboard(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let text = table.innerText;
    navigator.clipboard.writeText(text).then(() => {
        showToast('Table contents copied to clipboard!');
    });
}
