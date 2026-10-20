<?php
// config.php - Global System Configurations & Platform Announcement Module
require_once __DIR__ . '/includes/functions.php';

checkAdminPermission('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_site_config') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }
    
    $res = updateSiteConfig($_POST);
    $_SESSION['message'] = $res['message'];
    $_SESSION['message_type'] = $res['success'] ? 'success' : 'danger';
    header('Location: ?page=config');
    exit;
}

$site_settings = [];
$res = $conn->query("SELECT * FROM site_config");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $site_settings[$row['name']] = $row['value'];
    }
}

$latest_update_title = 'Platform Update';
$latest_update_content = '';
$update_res = $conn->query("SELECT title, content FROM platform_updates ORDER BY id DESC LIMIT 1");
if ($update_res && $update_row = $update_res->fetch_assoc()) {
    $latest_update_title = $update_row['title'];
    $latest_update_content = $update_row['content'];
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">System Configurations</h1>
        <p class="page-subtitle">Configure advertisements, maintenance mode, global announcement alerts, and platform release notes.</p>
    </div>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <?php renderAlertBanner($_SESSION['message'], $_SESSION['message_type'] ?? 'info'); ?>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<div class="form-container">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="action" value="update_site_config">

        <!-- Advertisements & Monetization Management -->
        <div style="border-bottom: 1px solid var(--border-subtle); padding-bottom: 28px; margin-bottom: 28px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0 0 4px 0;">
                        <i class="fas fa-rectangle-ad mr-2" style="color: #60a5fa;"></i>Advertisements & Monetization Engine
                    </h3>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 0;">
                        Control all advertising scripts, popunders, display banners, and frequency policies site-wide without editing code files.
                    </p>
                </div>
            </div>

            <!-- Master Ads Toggle -->
            <div style="padding: 18px; background: rgba(255,255,255,0.025); border-radius: 8px; border: 1px solid var(--border-subtle); margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 15px; font-weight: 700; color: #fff;">
                    <input type="checkbox" name="enable_ads" value="1" <?= ($site_settings['enable_ads']??'1') === '1' ? 'checked' : '' ?> class="toggle-checkbox" style="width: 20px; height: 20px;">
                    Enable Global Advertisements (Master Switch)
                </label>
                <p style="font-size: 12px; color: var(--text-muted); margin: 6px 0 0 32px;">
                    When turned off, all advertisements, popunders, and banners will be completely disabled across the entire site instantly.
                </p>
            </div>

            <!-- Popunder Ad Controller & Policy -->
            <div style="padding: 20px; background: rgba(255,255,255,0.015); border-radius: 8px; border: 1px solid var(--border-subtle); margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 14px;">
                    <input type="checkbox" name="enable_popunder_ads" value="1" <?= ($site_settings['enable_popunder_ads']??'1') === '1' ? 'checked' : '' ?> class="toggle-checkbox">
                    Enable Intelligent Popunder / Popup Ad Controller
                </label>

                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label>Popunder Script Source URL (POPUP_AD_SRC)</label>
                        <input type="text" name="popunder_ad_code" value="<?= htmlspecialchars($site_settings['popunder_ad_code'] ?? '//wz.unledpierced.com/rC94QxHlNRv/143348') ?>" class="form-control" placeholder="//wz.unledpierced.com/rC94QxHlNRv/143348">
                        <small style="color: var(--text-muted); font-size: 11px;">Enter the ad script URL or tag provided by your ad network (Adsterra, Monetag, etc.).</small>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label>Ad Frequency Policy Profile</label>
                        <select name="ad_policy_profile" class="form-control">
                            <option value="balanced" <?= ($site_settings['ad_policy_profile']??'balanced') === 'balanced' ? 'selected' : '' ?>>Balanced (5m CD, Max 3 Ads/Session)</option>
                            <option value="conservative" <?= ($site_settings['ad_policy_profile']??'balanced') === 'conservative' ? 'selected' : '' ?>>Conservative (10m CD, Max 2 Ads/Session)</option>
                            <option value="aggressive" <?= ($site_settings['ad_policy_profile']??'balanced') === 'aggressive' ? 'selected' : '' ?>>Aggressive (2m CD, Max 5 Ads/Session)</option>
                            <option value="custom" <?= ($site_settings['ad_policy_profile']??'balanced') === 'custom' ? 'selected' : '' ?>>Custom Timings (Specify Below)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="margin-top: 12px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 14px;">
                    <div class="form-group">
                        <label>Custom Cooldown (milliseconds - 0 for profile default)</label>
                        <input type="number" name="ad_cooldown_ms" value="<?= htmlspecialchars($site_settings['ad_cooldown_ms'] ?? '0') ?>" class="form-control" placeholder="e.g., 300000 (5 mins)">
                    </div>
                    <div class="form-group">
                        <label>Custom Session Max Ads (0 for profile default)</label>
                        <input type="number" name="ad_session_max" value="<?= htmlspecialchars($site_settings['ad_session_max'] ?? '0') ?>" class="form-control" placeholder="e.g., 3">
                    </div>
                    <div class="form-group">
                        <label>Initial Delay (milliseconds - 0 for default)</label>
                        <input type="number" name="ad_initial_delay_ms" value="<?= htmlspecialchars($site_settings['ad_initial_delay_ms'] ?? '0') ?>" class="form-control" placeholder="e.g., 10000 (10 secs)">
                    </div>
                </div>
            </div>

            <!-- Ad Types Configurations -->
            <div class="form-row" style="margin-bottom: 16px;">
                <!-- Banner / Display Ads -->
                <div class="form-group" style="padding: 16px; background: rgba(255,255,255,0.015); border-radius: 8px; border: 1px solid var(--border-subtle);">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; font-weight: 600; color: #fff; margin-bottom: 8px;">
                        <input type="checkbox" name="enable_banner_ads" value="1" <?= ($site_settings['enable_banner_ads']??'0') === '1' ? 'checked' : '' ?> class="toggle-checkbox">
                        Enable Banner / Display Ads
                    </label>
                    <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 6px; display: block;">Banner Display Ad Tag (728x90 / 300x250 HTML Code)</label>
                    <textarea name="banner_ad_code" class="form-control" rows="3" placeholder="<div id='ad-banner'>...</div>"><?= htmlspecialchars($site_settings['banner_ad_code']??'') ?></textarea>
                </div>

                <!-- Video Player Ads -->
                <div class="form-group" style="padding: 16px; background: rgba(255,255,255,0.015); border-radius: 8px; border: 1px solid var(--border-subtle);">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; font-weight: 600; color: #fff; margin-bottom: 8px;">
                        <input type="checkbox" name="enable_video_ads" value="1" <?= ($site_settings['enable_video_ads']??'0') === '1' ? 'checked' : '' ?> class="toggle-checkbox">
                        Enable Video Player Overlay / Preroll Ads
                    </label>
                    <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 6px; display: block;">Video Player Ad Code / VAST URL</label>
                    <textarea name="video_ad_code" class="form-control" rows="3" placeholder="VAST tag URL or player overlay script..."><?= htmlspecialchars($site_settings['video_ad_code']??'') ?></textarea>
                </div>
            </div>

            <!-- Anti-Adblock Warning -->
            <div style="padding: 16px; background: rgba(255,255,255,0.015); border-radius: 8px; border: 1px solid var(--border-subtle);">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; font-weight: 600; color: #fff; margin-bottom: 6px;">
                    <input type="checkbox" name="enable_anti_adblock" value="1" <?= ($site_settings['enable_anti_adblock']??'0') === '1' ? 'checked' : '' ?> class="toggle-checkbox">
                    Enable Anti-Adblocker Reminder Dialog
                </label>
                <p style="font-size: 12px; color: var(--text-muted); margin: 0;">
                    Politely requests visitors using AdBlock to whitelist the site to support streaming servers.
                </p>
            </div>
        </div>

        <!-- Global Maintenance Mode -->
        <div style="border-bottom: 1px solid var(--border-subtle); padding-bottom: 20px; margin-bottom: 24px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 15px; font-weight: 600; color: #fff;">
                <input type="checkbox" name="maintenance_mode" value="1" <?= ($site_settings['maintenance_mode']??'0') === '1' ? 'checked' : '' ?> class="toggle-checkbox" style="width: 18px; height: 18px;">
                Enable Global Maintenance Mode
            </label>
            <p style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">Redirect all non-whitelisted traffic to animated maintenance screen.</p>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Maintenance Whitelisted IPs (comma-separated)</label>
                <input type="text" name="maintenance_ip_whitelist" value="<?= htmlspecialchars($site_settings['maintenance_ip_whitelist']??'127.0.0.1') ?>" class="form-control" placeholder="127.0.0.1, 192.168.1.1">
            </div>
            <div class="form-group">
                <label>Estimated Return Date/Time (GMT+0)</label>
                <input type="text" name="maintenance_countdown" value="<?= htmlspecialchars($site_settings['maintenance_countdown']??'') ?>" class="form-control" placeholder="2026-08-10 18:00:00">
                <small style="color: var(--text-muted); font-size: 11px;">Format: YYYY-MM-DD HH:MM:SS</small>
            </div>
        </div>

        <div class="form-group">
            <label>Maintenance Message</label>
            <textarea name="maintenance_message" class="form-control" rows="2"><?= htmlspecialchars($site_settings['maintenance_message']??'') ?></textarea>
        </div>

        <!-- Global Announcements -->
        <div style="border-top: 1px solid var(--border-subtle); padding-top: 24px; margin-top: 24px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 24px; margin-bottom: 24px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 16px;">
                <input type="checkbox" name="announcement_active" value="1" <?= ($site_settings['announcement_active']??'0') === '1' ? 'checked' : '' ?> class="toggle-checkbox" style="width: 18px; height: 18px;">
                Enable Global Site Announcement
            </label>

            <div class="form-group">
                <label>Announcement Type</label>
                <select name="announcement_type" class="form-control" style="max-width: 360px;">
                    <option value="banner" <?= ($site_settings['announcement_type']??'banner') === 'banner' ? 'selected' : '' ?>>Top Sticky Alert Bar (Banner)</option>
                    <option value="modal" <?= ($site_settings['announcement_type']??'banner') === 'modal' ? 'selected' : '' ?>>Floating Popup Dialog (Modal)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Announcement Content (HTML Allowed)</label>
                <textarea name="announcement_text" class="form-control" rows="3" placeholder="Enter announcement text or HTML anchor tags..."><?= htmlspecialchars($site_settings['announcement_text']??'') ?></textarea>
            </div>
        </div>

        <!-- Platform Update Release Notes -->
        <div style="border-bottom: 1px solid var(--border-subtle); padding-bottom: 24px; margin-bottom: 24px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 16px;">
                <input type="checkbox" name="update_active" value="1" <?= ($site_settings['update_active']??'0') === '1' ? 'checked' : '' ?> class="toggle-checkbox" style="width: 18px; height: 18px;">
                Enable Platform Update Release Popup
            </label>

            <div class="form-group">
                <label>Update Title</label>
                <input type="text" name="update_title" value="<?= htmlspecialchars($latest_update_title) ?>" class="form-control" style="max-width: 360px;">
            </div>

            <div class="form-group">
                <label>Release Notes (Markdown Supported)</label>
                <textarea name="update_text" class="form-control" rows="8" placeholder="Enter markdown release logs..."><?= htmlspecialchars($latest_update_content) ?></textarea>
            </div>

            <!-- Markdown Guide Card -->
            <div style="padding: 14px 18px; background: rgba(255,255,255,0.02); border-radius: 8px; border: 1px solid var(--border-subtle); margin-top: 12px;">
                <div style="font-size: 12px; font-weight: 700; color: #fff; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-circle-info" style="color: #60a5fa;"></i> Markdown Formatting Cheat Sheet:
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 11.5px; color: var(--text-muted);">
                    <div>
                        <div style="margin-bottom: 4px;"><code style="color:#fff;"># Heading 1</code> &nbsp;&bull;&nbsp; Large Section Title</div>
                        <div style="margin-bottom: 4px;"><code style="color:#fff;">## Heading 2</code> &nbsp;&bull;&nbsp; Subheading</div>
                        <div style="margin-bottom: 4px;"><code style="color:#fff;">**Bold Text**</code> &nbsp;&bull;&nbsp; <strong>Bold emphasis</strong></div>
                        <div style="margin-bottom: 4px;"><code style="color:#fff;">*Italic Text*</code> &nbsp;&bull;&nbsp; <em>Italic text</em></div>
                        <div><code style="color:#fff;">[NEW]</code>, <code style="color:#fff;">[FIXED]</code>, <code style="color:#fff;">[IMPROVED]</code> &nbsp;&bull;&nbsp; Badges</div>
                    </div>
                    <div>
                        <div style="margin-bottom: 4px;"><code style="color:#fff;">- Bullet Item</code> or <code style="color:#fff;">* Item</code> &nbsp;&bull;&nbsp; Bullet list</div>
                        <div style="margin-bottom: 4px;"><code style="color:#fff;">1. First Item</code> &nbsp;&bull;&nbsp; Numbered list</div>
                        <div style="margin-bottom: 4px;"><code style="color:#fff;">`inline code`</code> &nbsp;&bull;&nbsp; Code highlight</div>
                        <div style="margin-bottom: 4px;"><code style="color:#fff;">[Click Here](https://...)</code> &nbsp;&bull;&nbsp; Web link</div>
                        <div><code style="color:#fff;">---</code> &nbsp;&bull;&nbsp; Horizontal divider line</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Website Title</label>
            <input type="text" name="website_title" value="<?= htmlspecialchars($site_settings['website_title']??'Zanora') ?>" class="form-control" style="max-width: 360px;">
        </div>

        <div style="margin-top: 28px;">
            <button type="submit" class="btn-primary"><i class="fas fa-floppy-disk mr-1"></i> Save Configurations</button>
        </div>
    </form>
</div>
