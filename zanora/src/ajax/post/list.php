<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/user/bootstrap_session.php');
$userId = ensureUserSession($conn);
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php';

if (!function_exists("json_response")) {
    function json_response($data = [], $status = 200) {
        header("Content-Type: application/json; charset=utf-8");
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}

$userId = $_SESSION['userID'] ?? 0;

$type   = $_GET['type']   ?? 'all';
$cursor = $_GET['cursor'] ?? '';
$tags   = strtolower($_GET['tags'] ?? '');
$sort   = $_GET['sort']   ?? 'newest';

$limit = 10;

/*
---------------------------------------------------
 TAG STRING → TAG ID
---------------------------------------------------
*/
$tagMap = [
    'suggestion' => 1,
    'question'   => 2,
    'discussion' => 3,
    'general'    => 4,
    'updates'    => 5,
    'feedback'   => 6,
];

$tagId = 0;
if ($tags && isset($tagMap[$tags])) {
    $tagId = $tagMap[$tags];  // numeric id for SQL
}

/*
---------------------------------------------------
 SMALL HELPERS
---------------------------------------------------
*/
function timeAgo($time) {
    $t = time() - strtotime($time);
    if ($t < 60) return $t . "s ago";
    if ($t < 3600) return floor($t/60) . "m ago";
    if ($t < 86400) return floor($t/3600) . "h ago";
    return floor($t/86400) . "d ago";
}

/**
 * Build one post item HTML
 *
 * @param mysqli $conn
 * @param array  $row
 * @param int    $userId
 * @param bool   $isPinned
 * @return string
 */
function buildPostItemHtml(mysqli $conn, array $row, int $userId, bool $isPinned = false): string
{
    $postId    = (int)$row['id'];
    $slug      = $row['slug'];
    $username  = htmlspecialchars($row['username']);
    $image     = htmlspecialchars($row['avatar']);
    $title     = htmlspecialchars($row['title']);
    $body      = nl2br(htmlspecialchars($row['content']));
    $likes     = (int)$row['like_count'];
    $dislikes  = (int)$row['dislike_count'];
    $authorPoints  = (int)($row['points'] ?? 0);
    $authorRoles   = (int)($row['roles'] ?? 0);

    // Rank & Badge
    if ($authorRoles === 1) {            // Admin
        $rankName   = 'Admin';
        $badgeClass = 'level-b';
    } elseif ($authorRoles === 2) {      // Mod
        $rankName   = 'Mod';
        $badgeClass = 'level-d';
    } else {                             // Normal user
        list($rankName, $badgeClass) = getRank($authorPoints);
    }

    // Username CSS class
    if ($authorRoles === 1) {
        $userNameClass = 'user-name is-level-x is-level-b';
    } elseif ($authorRoles === 2) {
        $userNameClass = 'user-name is-level-x is-level-d';
    } else {
        $userNameClass = 'user-name is-level-x' . ($badgeClass ? ' is-level-a' : '');
    }

    // Badge HTML
    $badgeHtml = $badgeClass ? "<i class='badg-level {$badgeClass}'></i>" : '';

    /* Comment Count */
    $comments = 0;
    $c = $conn->query("SELECT COUNT(*) AS c FROM post_comments 
                       WHERE post_id={$postId} AND status='visible'");
    if ($c && $r = $c->fetch_assoc()) {
        $comments = (int)$r['c'];
    }

    /* TAG NAME + COLOR */
    $tagId    = (int)$row['tags_id'];
    $tagName  = "";
    $tagClass = "";

    if     ($tagId == 1) { $tagName = "Suggestion"; $tagClass = "zt-green"; }
    elseif ($tagId == 2) { $tagName = "Question";   $tagClass = "zt-yellow"; }
    elseif ($tagId == 3) { $tagName = "Discussion"; $tagClass = "zt-purple"; }
    elseif ($tagId == 4) { $tagName = "General";    $tagClass = "zt-blue"; }
    elseif ($tagId == 5) { $tagName = "Updates";    $tagClass = "zt-red"; }
    elseif ($tagId == 6) { $tagName = "Feedback";   $tagClass = "zt-orange"; }

    /* MY VOTE */
    $myVote = null;
    if ($userId) {
        $v = $conn->query("SELECT type FROM post_votes 
                           WHERE user_id={$userId} AND post_id={$postId} LIMIT 1");
        if ($v && $vr = $v->fetch_assoc()) {
            $myVote = (int)$vr['type'];
        }
    }

    $upActive   = $myVote === 1 ? "active" : "";
    $downActive = $myVote === 0 ? "active" : "";

    /* TIME AGO */
    $timeAgo = timeAgo($row['created_at']);

    // Pinned badge (for pinned posts)
    $pinHtml = '';
    if ($isPinned) {
        $pinHtml = "<div class=\"is-pin\"><i class=\"fas fa-thumbtack\"></i><span class=\"ml-2\">Pinned</span></div>";
    }

    // Add an extra CSS class if you want special styling for pinned items
    $itemExtraClass = $isPinned ? '' : ''; // you can add ' pinned-item' if needed

    $html = "
    <div class='item{$itemExtraClass}' id='post-{$postId}'>

        <div class='is-vote is-vote-new'>
            <a class='is-up lp-btn-vote {$upActive}' href='javascript:;' data-type='1' data-id='{$postId}'>
                <i class='far fa-thumbs-up'></i>
            </a>
            <div class='is-point like-value'>{$likes}</div>

            <a class='is-down lp-btn-vote {$downActive}' href='javascript:;' data-type='0' data-id='{$postId}'>
                <i class='far fa-thumbs-down'></i>
            </a>
            <div class='is-point dislike-value'>{$dislikes}</div>
        </div>

        <div class='is-head'>
            {$pinHtml}
            <div class='ztag'>
                <span class='{$tagClass}'>#{$tagName}</span>
            </div>
            <div class='is-dot mx-2'><i class='dot'></i></div>
            <div class='is-time'>{$timeAgo}</div>
        </div>

        <div class='is-body'>
            <h4 class='item-name'>
                <a href='/community/post/{$slug}' title='{$title}'>{$title}</a>
            </h4>
            <p>{$body}</p>
        </div>

        <div class='is-bottom'>
            <div class='cn-owner'>
                <div class='profile-avatar'>
                    <img src='{$image}' alt='{$username}'>
                </div>
                <a href='/community/user/{$row['user_id']}' target='_blank' class='{$userNameClass}'>
                    {$badgeHtml} {$username}" . (!empty($rankName) ? " <span>{$rankName}</span>" : "") . "
                </a>
            </div>

            <div class='is-stats'>
                <div><i class='fas fa-comment mr-1'></i>{$comments}</div>
            </div>
        </div>

    </div>";

    return $html;
}

/*
---------------------------------------------------
 BUILD HTML (PINNED + NORMAL)
---------------------------------------------------
*/

$html = "";
$nextCursor = "";

/* ---------------------------------
   1) PINNED POSTS (FIRST PAGE ONLY)
--------------------------------- */
if ($cursor === '') {
    $sqlPinned = "SELECT p.*, u.username, u.image AS avatar, u.points, u.roles
                  FROM posts p
                  JOIN users u ON u.id = p.user_id
                  WHERE p.status = 'published'
                    AND p.is_pinned = 1";

    $paramsPinned = [];
    $typesPinned  = "";

    // Filter: my posts
    if ($type === 'my_posts' && $userId) {
        $sqlPinned   .= " AND p.user_id = ?";
        $paramsPinned[] = $userId;
        $typesPinned  .= "i";
    }

    // Filter: tag
    if ($tagId > 0) {
        $sqlPinned   .= " AND p.tags_id = ?";
        $paramsPinned[] = $tagId;
        $typesPinned  .= "i";
    }

    // Order pinned (newest first)
    $sqlPinned .= " ORDER BY p.id DESC";

    $stmtPinned = $conn->prepare($sqlPinned);
    if (!empty($paramsPinned)) {
        $stmtPinned->bind_param($typesPinned, ...$paramsPinned);
    }
    $stmtPinned->execute();
    $resPinned = $stmtPinned->get_result();

    $pinnedGroupHtml = "";
    while ($rowPinned = $resPinned->fetch_assoc()) {
        $pinnedGroupHtml .= buildPostItemHtml($conn, $rowPinned, (int)$userId, true) . "\n";
    }
    $stmtPinned->close();

    if ($pinnedGroupHtml !== "") {
        $html .= "<div class=\"pin-group\">\n{$pinnedGroupHtml}\n</div>\n";
    }
}

/* ---------------------------------
   2) NORMAL POSTS (EXCLUDE PINNED)
--------------------------------- */

$sql = "SELECT p.*, u.username, u.image AS avatar, u.points, u.roles
        FROM posts p
        JOIN users u ON u.id = p.user_id
        WHERE p.status = 'published'
          AND p.is_pinned = 0"; // <-- exclude pinned from normal list

$params = [];
$types  = "";

/* FILTER: My Posts */
if ($type === 'my_posts' && $userId) {
    $sql .= " AND p.user_id = ?";
    $params[] = $userId;
    $types   .= "i";
}

/* FILTER: TAG (numeric) */
if ($tagId > 0) {
    $sql .= " AND p.tags_id = ?";
    $params[] = $tagId;
    $types   .= "i";
}

/* CURSOR Pagination */
if ($cursor !== '') {
    $sql .= " AND p.id < ?";
    $params[] = (int)$cursor;
    $types   .= "i";
}

/* SORT */
$allowedSort = ['newest', 'oldest', 'top'];
if (!in_array($sort, $allowedSort, true)) {
    $sort = 'newest';
}

if ($sort === 'top') {
    $sql .= " ORDER BY (p.like_count - p.dislike_count) DESC, p.id DESC";
} elseif ($sort === 'oldest') {
    $sql .= " ORDER BY p.id ASC";
} else {
    $sql .= " ORDER BY p.id DESC";
}

$sql .= " LIMIT {$limit}";

/* RUN QUERY */
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $html .= buildPostItemHtml($conn, $row, (int)$userId, false) . "\n";
    $nextCursor = (int)$row['id'];
}

$hasMore = ($result->num_rows == $limit);
$stmt->close();

/* JSON RESPONSE */
json_response([
    "hasMore"    => $hasMore,
    "nextCursor" => $hasMore ? $nextCursor : "",
    "html"       => $html
]);
