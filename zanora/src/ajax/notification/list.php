<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/user/bootstrap_session.php');
$userId = ensureUserSession($conn);
require_once('src/component/anime/qtip.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

function out($arr, $code = 200) {
    http_response_code($code);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($_SESSION['userID'])) {
    out([
        'status'     => true,
        'html'       => '',
        'hasMore'    => false,
        'nextCursor' => null,
    ]);
}

$type   = isset($_GET['type'])   ? (int)$_GET['type']   : 0; // 0 = All, 1 = Anime, 2 = Community
$cursor = isset($_GET['cursor']) ? (int)$_GET['cursor'] : 0;
$limit  = 10;
$leadingWhitespace = "\n    \n\n    \n\n    \n\n    \n\n    \n\n    \n";

// ---------------------------------------------
// Anime Notifications (type=1)
// ---------------------------------------------
if ($type === 1 || $type === 0) {
    $whereAnime = "user_id = " . (int)$userId . " AND type = 1";
    if ($cursor > 0) $whereAnime .= " AND id < $cursor";

    $sqlAnime = "SELECT id, message, link, is_read, created_at, poster, title
                 FROM notifications
                 WHERE $whereAnime
                 ORDER BY id DESC
                 LIMIT $limit";

    $resAnime = $conn->query($sqlAnime);
    $htmlAnime = '';
    $animeRowCount = 0; $animeNextCursor = null;

    if ($resAnime && $resAnime->num_rows > 0) {
        $buffer = '';
        while ($row = $resAnime->fetch_assoc()) {
            $readClass = $row['is_read'] ? '' : 'new';
            $posterUrl = htmlspecialchars($row['poster'] ?? '');
            $title     = htmlspecialchars(!empty($row['title']) ? $row['title'] : 'Untitled');
            $link      = !empty($row['link']) ? $row['link'] : '#';
            $message   = htmlspecialchars($row['message'] ?? '');
            $time      = time_elapsed_string($row['created_at']);

            $buffer .= "    <div class=\"inbox-item with-poster $readClass\">\n";
            $buffer .= "        <a href=\"$link\" class=\"film-poster\">\n";
            $buffer .= "            <img src=\"$posterUrl\" class=\"film-poster-img\" alt=\"$title\">\n";
            $buffer .= "        </a>\n";
            $buffer .= "        <div class=\"small ii-time mb-1\">$time</div>\n";
            $buffer .= "        <div class=\"ii-title\">\n";
            $buffer .= "            <a href=\"$link\"><span class=\"highlight-text\">$title</span> - $message</a>\n";
            $buffer .= "        </div>\n";
            $buffer .= "        <div class=\"ii-content\">Click <a href=\"$link\">here</a> to watch it now.</div>\n";
            $buffer .= "        <div class=\"clearfix\"></div>\n";
            $buffer .= "    </div>\n";
            $animeNextCursor = (int)$row['id'];
            $animeRowCount++;
        }
        $htmlAnime = $leadingWhitespace . $buffer;
    } else {
        $htmlAnime = '';
    }
}

// ---------------------------------------------
// Community Notifications (type=2), fetch anime title from API!
// ---------------------------------------------
if ($type === 2 || $type === 0) {
    $whereComm = "user_id = " . (int)$userId . " AND type = 2";
    if ($cursor > 0) $whereComm .= " AND id < $cursor";

    $sqlComm = "SELECT id, message, link, is_read, created_at, from_user_id, anime_id
                FROM notifications
                WHERE $whereComm
                ORDER BY id DESC
                LIMIT $limit";

    $resComm = $conn->query($sqlComm);
    $htmlComm = '';
    $commRowCount = 0; $commNextCursor = null;

    if ($resComm && $resComm->num_rows > 0) {
	    $buffer = '';
    	while ($row = $resComm->fetch_assoc()) {
        	$readClass = $row['is_read'] ? '' : 'new';
	        $link = !empty($row['link']) ? $row['link'] : '#';
	        $fromUserId = (int)$row['from_user_id'];

	        // get user details (who replied)
	        $userRes = $conn->query("SELECT username, image FROM users WHERE id={$fromUserId} LIMIT 1");
	        $userRow = $userRes && $userRes->num_rows > 0 ? $userRes->fetch_assoc() : [];
	        $username = !empty($userRow['username']) ? htmlspecialchars($userRow['username']) : 'Unknown';
	        $avatar = !empty($userRow['image']) ? htmlspecialchars($userRow['image']) : '/public/images/no-avatar.jpeg';

	        // get anime title from API
	        $animeTitle = htmlspecialchars($row['anime_id']);
	        if (!empty($row['anime_id'])) {
	            $animeData = fetchAnimeData($row['anime_id']);
	            if ($animeData && !empty($animeData['title'])) {
	                $animeTitle = htmlspecialchars($animeData['title']);
	            }
	        }

	        $time = time_elapsed_string($row['created_at']);

	        // 100% match the requested structure
	        $buffer .= '<div class="inbox-item with-avatar ' . $readClass . '">';
	        $buffer .= '    <a class="user-avatar" target="_blank" href="/community/user/' . $fromUserId . '">';
	        $buffer .= '        <img src="' . $avatar . '" class="user-avatar-img" alt="' . $username . '">';
	        $buffer .= '    </a>';
	        $buffer .= '    <div class="small ii-time mb-1">' . $time . '</div>';
	        $buffer .= '    <div class="ii-title">';
	        $buffer .= '        <a href="' . htmlspecialchars($link) . '">'
                  . '<span class="highlight-text">' . $username . '</span> replied to your ';
	        $buffer .= '          comment on Anime <strong class="highlight-text">' . $animeTitle . '</strong></a>';
	        $buffer .= '    </div>';
	        $buffer .= '    <div class="ii-content">Click <a href="' . htmlspecialchars($link) . '">here</a> to see it now.';
	        $buffer .= '    </div>';
	        $buffer .= '    <div class="clearfix"></div>';
	        $buffer .= '</div>';
	        $commNextCursor = (int)$row['id'];
	        $commRowCount++;
	    }
	    $htmlComm = $leadingWhitespace . $buffer;
	} else {
	    $htmlComm = '';
	}
}

// ---------------------------------------------
// Compose Tabs for type=0 (All), or single list for type=1/type=2
// ---------------------------------------------

if ($type === 1) {
    $html = $htmlAnime;
    $hasMore = ($animeRowCount === $limit);
    $nextCursor = $animeRowCount ? $animeNextCursor : null;
} elseif ($type === 2) {
    $html = $htmlComm;
    $hasMore = ($commRowCount === $limit);
    $nextCursor = $commRowCount ? $commNextCursor : null;
} else { // type=0/all
    if (empty($htmlAnime) && empty($htmlComm)) {
        $html = '';
    } else {
        $html = '<div class="notify-tabs">';
        $html .= '<div class="notify-tab anime-tab"><h4>Anime Notifications</h4>' . $htmlAnime . '</div>';
        $html .= '<div class="notify-tab community-tab"><h4>Community Notifications</h4>' . $htmlComm . '</div>';
        $html .= '</div>';
    }
    $hasMore = ($animeRowCount === $limit) || ($commRowCount === $limit);
    $nextCursor = null;
}

// In case type=1 or type=2, return '' if no notifications
if (($type === 1 && empty($htmlAnime)) || ($type === 2 && empty($htmlComm))) {
    $html = '';
}

out([
    'status'     => true,
    'html'       => $html,
    'hasMore'    => $hasMore,
    'nextCursor' => $nextCursor,
]);

// --------------------------------------------
// Helper: "time ago"
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = (int)floor($diff->d / 7);
    $days  = $diff->d - ($weeks * 7);

    $units = [
        'y' => ['val' => $diff->y, 'txt' => 'year'],
        'm' => ['val' => $diff->m, 'txt' => 'month'],
        'w' => ['val' => $weeks,   'txt' => 'week'],
        'd' => ['val' => $days,    'txt' => 'day'],
        'h' => ['val' => $diff->h, 'txt' => 'hour'],
        'i' => ['val' => $diff->i, 'txt' => 'minute'],
        's' => ['val' => $diff->s, 'txt' => 'second'],
    ];

    $parts = [];
    foreach ($units as $u) {
        if ($u['val']) $parts[] = $u['val'].' '.$u['txt'].($u['val']>1?'s':'');
    }
    if (!$full) $parts = array_slice($parts, 0, 1);
    return $parts ? implode(', ', $parts) . ' ago' : 'just now';
}
