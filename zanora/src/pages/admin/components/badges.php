<?php
// components/badges.php - Reusable Status Badges & Role Pills

function renderStatusBadge($type, $label = '') {
    $classes = [
        'verified'  => 'badge-verified',
        'admin'     => 'badge-admin',
        'blocked'   => 'badge-blocked',
        'active'    => 'badge-active',
        'deactive'  => 'badge-deactive',
        'hidden'    => 'badge-hidden'
    ];

    $icons = [
        'verified'  => 'fa-check-circle',
        'admin'     => 'fa-crown',
        'blocked'   => 'fa-ban',
        'active'    => 'fa-circle-check',
        'deactive'  => 'fa-circle-xmark',
        'hidden'    => 'fa-eye-slash'
    ];

    $cls = $classes[$type] ?? 'badge-default';
    $icon = $icons[$type] ?? 'fa-circle';
    $text = $label ?: ucfirst($type);

    echo "<span class=\"admin-badge $cls\"><i class=\"fas $icon\"></i> " . htmlspecialchars($text) . "</span>";
}

function renderRoleTag($name, $color = '#ffffff') {
    $rgb = hex2rgb($color);
    echo "<span class=\"user-role-tag\" style=\"background: rgba($rgb, 0.08); color: $color; border: 1px solid rgba($rgb, 0.2);\">" . htmlspecialchars($name) . "</span>";
}
