<?php
// includes/footer.php - Closing Scripts, Modals, Command Palette & Toast Notifications
?>
        <!-- Command Palette Modal -->
        <div class="cmd-palette-backdrop" id="cmd-palette-backdrop" style="display:none;" onclick="closeCommandPalette(event)">
            <div class="cmd-palette-modal" onclick="event.stopPropagation()">
                <div class="cmd-palette-header">
                    <i class="fas fa-search cmd-search-icon"></i>
                    <input type="text" id="cmd-palette-input" placeholder="Type a command or search users, settings, logs..." oninput="handleCommandInput(this.value)">
                    <kbd class="esc-kbd">ESC</kbd>
                </div>
                <div class="cmd-palette-results" id="cmd-palette-results">
                    <div class="cmd-group-title">QUICK NAVIGATION</div>
                    <a href="?page=dashboard" class="cmd-item"><i class="fas fa-grid-2"></i> Dashboard Overview</a>
                    <a href="?page=analytics" class="cmd-item"><i class="fas fa-chart-line"></i> Analytics & Traffic</a>
                    <a href="?page=users" class="cmd-item"><i class="fas fa-users"></i> Users Administration</a>
                    <a href="?page=roles" class="cmd-item"><i class="fas fa-shield-halved"></i> Roles & Permissions</a>
                    <a href="?page=admins" class="cmd-item"><i class="fas fa-user-gear"></i> System Admins</a>
                    <a href="?page=moderation" class="cmd-item"><i class="fas fa-comment-slash"></i> Content Moderation</a>
                    <a href="?page=config" class="cmd-item"><i class="fas fa-sliders"></i> Site Configuration</a>
                    <a href="?page=database" class="cmd-item"><i class="fas fa-database"></i> Database & Redis Tools</a>
                    <a href="?page=logs" class="cmd-item"><i class="fas fa-list-check"></i> Audit Logs & Events</a>

                    <div class="cmd-group-title">QUICK ACTIONS</div>
                    <button class="cmd-item" onclick="triggerCacheClear(); closeCommandPalette();"><i class="fas fa-trash-can"></i> Flush Redis Cache</button>
                    <button class="cmd-item" onclick="triggerDbOptimize(); closeCommandPalette();"><i class="fas fa-gears"></i> Optimize MySQL Tables</button>
                    <a href="?page=db_backup" class="cmd-item"><i class="fas fa-download"></i> Download SQL Database Backup</a>
                </div>
            </div>
        </div>

        <!-- Toast Notifications Container -->
        <div class="toast-container" id="toast-container"></div>

        <!-- Scripts -->
        <script src="/src/pages/admin/assets/js/admin.js"></script>
        <script src="/src/pages/admin/assets/js/search.js"></script>
    </body>
</html>
