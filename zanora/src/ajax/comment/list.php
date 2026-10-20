<?php
/**
 * /src/ajax/comment/list.php
 * Loads more top-level comments for pagination (infinite scroll).
 * Accepts route: /ajax/comment/list/{slug}?episodeId=&page=&sort=&type=
 * Returns JSON: { status: bool, html: string, nextPage: int }
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php';

if (!isset($conn) || $conn->connect_errno) {
    echo json_encode(['status' => false, 'html' => '', 'nextPage' => 0]);
    exit;
}

/* -------------------- helpers -------------------- */
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/** "25 minutes ago" */
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $weeks = floor($diff->d / 7);
    $days  = $diff->d % 7;

    $string = [
        'y' => $diff->y ? $diff->y . ' year'  . ($diff->y  > 1 ? 's' : '') : null,
        'm' => $diff->m ? $diff->m . ' month' . ($diff->m  > 1 ? 's' : '') : null,
        'w' => $weeks   ? $weeks  . ' week'   . ($weeks   > 1 ? 's' : '') : null,
        'd' => $days    ? $days   . ' day'    . ($days    > 1 ? 's' : '') : null,
        'h' => $diff->h ? $diff->h . ' hour'  . ($diff->h  > 1 ? 's' : '') : null,
        'i' => $diff->i ? $diff->i . ' minute'. ($diff->i  > 1 ? 's' : '') : null,
        's' => $diff->s ? $diff->s . ' second'. ($diff->s  > 1 ? 's' : '') : null,
    ];
    $string = array_filter($string);
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

/** Parse /ajax/comment/list/{slug} */
function getMovieSlugFromPath(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $marker = '/ajax/comment/list/';
    $pos = strpos($uri, $marker);
    if ($pos === false) return '';
    $rest = substr($uri, $pos + strlen($marker));
    $rest = explode('?', $rest, 2)[0];
    $rest = urldecode(trim($rest, '/'));
    if ($rest === '' || !preg_match('~^[a-z0-9\-]+(?:-[a-z0-9\-]+)*$~i', $rest)) return '';
    return $rest;
}

/** Optional: turn 16:48 / 1:02:33 into clickable seek anchors */
function linkify_player_times(string $html): string {
    return preg_replace_callback(
        '/(?<!\\d)(?:(\\d{1,2}):)?([0-5]?\\d):([0-5]\\d)(?!\\d)/',
        function($m){
            $h = isset($m[1]) && $m[1] !== '' ? (int)$m[1] : 0;
            $mm = (int)$m[2];
            $ss = (int)$m[3];
            $secs = $h * 3600 + $mm * 60 + $ss;
            // keep original matched text ($m[0]) as visible label
            return '<a style="color: yellow !important;" href="javascript:;" class="player-time" data-time="' . $secs . '">' . $m[0] . '</a>';
        },
        $html
    );
}

/* ---------- EPISODE NUMBER RESOLUTION WITH DB FIRST, API FALLBACK ---------- */

/** Read full map {episode_id => episode_no} for an anime slug from DB episode_index */
function getEpisodeMapFromDB(mysqli $conn, string $slug): array {
    $map = [];
    $sql = "SELECT episode_id, episode_no FROM episode_index WHERE anime_id = ?";
    if ($st = $conn->prepare($sql)) {
        $st->bind_param('s', $slug);
        if ($st->execute() && ($res = $st->get_result())) {
            while ($row = $res->fetch_assoc()) {
                $eid = (string)$row['episode_id'];
                $eno = (int)$row['episode_no'];
                if ($eid !== '') $map[$eid] = $eno;
            }
        }
        $st->close();
    }
    return $map;
}

/** Upsert full map into DB */
function upsertEpisodeMap(mysqli $conn, string $slug, array $map): void {
    if (!$map) return;
    $sql = "INSERT INTO episode_index (anime_id, episode_no, episode_id) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE episode_id = VALUES(episode_id), updated_at = CURRENT_TIMESTAMP";
    if ($st = $conn->prepare($sql)) {
        foreach ($map as $episodeId => $episodeNo) {
            $eid = (string)$episodeId;
            $eno = (int)$episodeNo;
            $st->bind_param('sis', $slug, $eno, $eid);
            $st->execute(); // ignore per-row errors (best-effort)
        }
        $st->close();
    }
}

/** Fetch full map from API: {episode_id => episode_no} */
function fetchEpisodeMapFromAPI(string $zpiBase, string $slug): array {
    $map = [];
    $base = rtrim($zpiBase ?: '', '/');
    if ($base === '') return $map;
    $url = $base . '/episodes/' . rawurlencode($slug);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    if ($resp) {
        $json = json_decode($resp, true);
        if (!empty($json['success']) && !empty($json['results']['episodes']) && is_array($json['results']['episodes'])) {
            foreach ($json['results']['episodes'] as $ep) {
                if (!empty($ep['episode_id']) && isset($ep['episode_no'])) {
                    $map[(string)$ep['episode_id']] = (int)$ep['episode_no'];
                }
            }
        }
    }
    return $map;
}

/**
 * Ensure we have a full episode map:
 * 1) Try DB
 * 2) If empty → fetch from API, upsert to DB, return it
 */
function ensureEpisodeMap(mysqli $conn, string $slug, string $zpiBase): array {
    $map = getEpisodeMapFromDB($conn, $slug);
    if ($map) return $map;

    $map = fetchEpisodeMapFromAPI($zpiBase, $slug);
    if ($map) upsertEpisodeMap($conn, $slug, $map);
    return $map;
}

/* -------------------- inputs -------------------- */
$loggedIn = !empty($_SESSION['userID']);
$myId     = $loggedIn ? intval($_SESSION['userID']) : 0;

$me = null;
$me_role = 0; // 0 = normal, 1 = admin, 2 = mod

if ($loggedIn) {
    $uq = $conn->query("SELECT id, username, image, roles FROM users WHERE id = {$myId} LIMIT 1");
    if ($uq && $ur = $uq->fetch_assoc()) {
        $me = $ur;
        $me_is_admin = ($ur['roles'] == 1) ? 1 : 0;   // <-- using roles now
    }
}

$movieId  = getMovieSlugFromPath();
if ($movieId === '') $movieId = trim($_GET['movieId'] ?? '');

$episodeId = trim($_GET['episodeId'] ?? '');
$page      = max(1, intval($_GET['page'] ?? 1));
$sortRaw   = $_GET['sort'] ?? '';
$sort      = in_array($sortRaw, ['top','newest','oldest'], true) ? $sortRaw : 'newest';
$type      = ($_GET['type'] ?? 'episode');

if ($movieId === '') {
    echo json_encode(['status' => false, 'html' => '', 'nextPage' => 0]);
    exit;
}

$perPage = 10;
$limit   = $perPage + 1;
$offset  = ($page - 1) * $perPage;

/* -------------------- query -------------------- */
$where = "c.movie_id = '" . $conn->real_escape_string($movieId) . "'";
if ($type === 'episode' && $episodeId !== '') {
    $where .= " AND c.episode_id = '" . $conn->real_escape_string($episodeId) . "'";
}
$where .= " AND c.parent_id = 0 AND c.status = 'active'";

$order = 'ORDER BY c.created_at DESC';
if ($sort === 'oldest') {
    $order = 'ORDER BY c.created_at ASC';
} elseif ($sort === 'top') {
    $order = 'ORDER BY ((IFNULL(cv.upvotes,0) - IFNULL(cv.downvotes,0))) DESC, c.created_at DESC';
}

$sql = "SELECT c.id, c.user_id, c.content, c.is_spoil, c.created_at, c.episode_id,
               COALESCE(c.is_pinned,0) AS is_pinned,
               u.username, u.image, u.points, u.roles,
               IFNULL(cv.upvotes,0) AS upvotes,
               IFNULL(cv.downvotes,0) AS downvotes,
               (SELECT COUNT(*) FROM comments r WHERE r.parent_id = c.id AND r.status = 'active') AS reply_count
        FROM comments c
        LEFT JOIN users u ON u.id = c.user_id
        LEFT JOIN (
            SELECT comment_id,
                   SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) AS upvotes,
                   SUM(CASE WHEN type = 0 THEN 1 ELSE 0 END) AS downvotes
            FROM comment_votes
            GROUP BY comment_id
        ) cv ON cv.comment_id = c.id
        WHERE {$where}
        {$order}
        LIMIT {$limit} OFFSET {$offset}";

$res = $conn->query($sql);
if (!$res) {
    echo json_encode(['status' => false, 'html' => '', 'nextPage' => 0]);
    exit;
}

$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;

$nextPage = 0;
if (count($rows) > $perPage) {
    $nextPage = $page + 1;
    array_pop($rows);
}

/* ------- Load episode map once (only needed for type=all) ------- */
$episodeMap = [];
if ($type === 'all') {
    // Uses DB first; if empty, fetches API, stores to DB, returns map.
    $episodeMap = ensureEpisodeMap($conn, $movieId, $zpi ?? '');
}

/* -------------------- build HTML (items only) -------------------- */
$html = '';
foreach ($rows as $r) {
    $cid          = (int)$r['id'];
    $authorId     = (int)$r['user_id'];
    $username     = $r['username'] ?: 'Unknown';
    $avatar       = fix_avatar_url($r['image'] ?: '/public/images/no-avatar.jpeg');
    $utcTime      = gmdate('Y-m-d\TH:i:s\Z', strtotime($r['created_at']));
    $humanTime    = time_elapsed_string($r['created_at']);
    $contentHtml  = nl2br(e($r['content']));
    $contentHtml  = linkify_player_times($contentHtml);
    $is_spoil     = (int)$r['is_spoil'];
    $is_pinned = (int)$r['is_pinned'];
    $up           = (int)$r['upvotes'];
    $down         = (int)$r['downvotes'];
    $replyCnt = (int)($r['reply_count'] ?? 0);
    $rowEpisodeId = (string)($r['episode_id'] ?? '');
    $authorPoints  = (int)($r['points'] ?? 0);
    $authorIsAdmin = (int)($r['roles'] ?? 0);

    if ($authorIsAdmin === 1) {
    // Admin
    $rankName   = 'Admin';
    $badgeClass = 'level-b';
    $rankIcon   = '/public/images/level-b.png';

} elseif ($authorIsAdmin === 2) {
    // Moderator
    $rankName   = 'Mod';
    $badgeClass = 'level-d';
    $rankIcon   = '/public/images/level-d.png';

} else {
    // Normal user rank from points
    list($rankName, $badgeClass, $rankIcon) = getRank($authorPoints);
}

    if ($authorIsAdmin === 1) {
    $userNameClass = 'user-name is-level-x is-level-b';     // Admin
}
elseif ($authorIsAdmin === 2) {
    $userNameClass = 'user-name is-level-x is-level-d';     // Mod
}
else {
    $userNameClass = 'user-name is-level-x' . ($badgeClass ? ' is-level-a' : '');
}

    // For type=all, show the episode tag like Hianime (“Episode 998”)
    $episodeBadge = '';
    if ($type === 'all' && $rowEpisodeId !== '') {
        $epNo = $episodeMap[$rowEpisodeId] ?? null;
        if ($epNo) {
            $episodeBadge = "<div class=\"eps\"><i class=\"fas fa-caret-left mr-1\"></i>Episode " . e($epNo) . "</div>";
        }
    }

    $html .= "<div class=\"cw_l-line\" id=\"cm-{$cid}\">
        <a href=\"/community/user/{$authorId}\" class=\"user-avatar\">
            <img class=\"user-avatar-img\" src=\"" . e($avatar) . "\" alt=\"" . e($username) . "\">
        </a>
        <div class=\"info\">

            <div class=\"ihead\">
                <a href=\"/community/user/{$authorId}\" target=\"_blank\" class=\"" . e($userNameClass) . "\">";
    if (!empty($badgeClass)) {
        $html .= "<i class=\"badg-level " . e($badgeClass) . "\"></i>";
    }
    $html .= e($username);
    if (!empty($rankName)) {
        $html .= " <span>" . e($rankName) . "</span>";
    }
    $html .= "</a>

                <div class=\"time\">" . e($humanTime) . "</div>
                {$episodeBadge}
            </div>

            <div class=\"ibody " . ($is_spoil ? 'is-spoil' : '') . "\">
                <p>{$contentHtml}</p>";
    if ($is_spoil) {
        $html .= "<div class='show-spoil my-3'><button type='button' class='btn btn-sm btn-light'>Show spoil</button></div>";
    }
    $html .= "</div>

            <div class=\"ibottom\">
                <div class=\"ib-li ib-reply\" data-id=\"{$cid}\"><a class=\"btn\"><i class=\"fas fa-reply mr-1\"></i>Reply</a></div>
                <div class=\"ib-li ib-like\">
                    <a class=\"btn cm-btn-vote " . ($up > 0 ? 'active' : '') . "\" data-id=\"{$cid}\" data-type=\"1\">
                        <i class=\"far fa-thumbs-up mr-1\"></i><span class=\"value\">" . ($up ?: '') . "</span>
                    </a>
                </div>
                <div class=\"ib-li ib-dislike\">
                    <a class=\"btn cm-btn-vote " . ($down > 0 ? 'active' : '') . "\" data-id=\"{$cid}\" data-type=\"0\">
                        <i class=\"far fa-thumbs-down mr-1\"></i><span class=\"value\">" . ($down ?: '') . "</span>
                    </a>
                </div>
                <div class=\"ib-li\">
                    <a class=\"btn\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"fas fa-ellipsis-h mr-1\"></i>More
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-model dropdown-menu-normal\">";
                if ($loggedIn && isset($me['roles']) && $me['roles'] == 1) {
                    if (!empty($is_pinned)) {
                        $html .= "<a class=\"dropdown-item cm-action\" href=\"javascript:;\" data-action=\"unpin\" data-id=\"{$cid}\">Unpin comment</a>";
                    } else {
                        $html .= "<a class=\"dropdown-item cm-action\" href=\"javascript:;\" data-action=\"pin\" data-id=\"{$cid}\">Pin comment</a>";
                    }
                    $html .= "<a class=\"dropdown-item cm-action\" href=\"javascript:;\" data-action=\"delete\" data-id=\"{$cid}\">Delete comment</a>
                    		  <a class=\"dropdown-item ac-mute\" href=\"javascript:;\" data-hours=\"24\">Mute 24h</a>
                              <a class=\"dropdown-item ac-block\" href=\"javascript:;\" data-user-id=\"{$authorId}\">Block user</a>
                              <div class=\"dropdown-divider\"></div>";
                    $html .= "<a class=\"dropdown-item cm-action\" href=\"javascript:;\" data-action=\"hide\" data-id=\"{$cid}\">Hide</a>
                              <a class=\"dropdown-item cm-action\" href=\"javascript:;\" data-action=\"spoil\" data-id=\"{$cid}\">Mark Spoil</a>";
                } elseif ($loggedIn && $authorId === $myId) {
                
                    $html .= "<a class=\"dropdown-item cm-action\" href=\"javascript:;\" data-action=\"hide\" data-id=\"{$cid}\">Hide</a>
                              <a class=\"dropdown-item cm-action\" href=\"javascript:;\" data-action=\"spoil\" data-id=\"{$cid}\">Mark Spoil</a>";
                } else {
                    $html .= "<a class=\"dropdown-item cm-report\" href=\"javascript:;\" data-type=\"1\" data-id=\"{$cid}\">Report Spam</a>
                              <a class=\"dropdown-item cm-report\" href=\"javascript:;\" data-type=\"2\" data-id=\"{$cid}\">Report Spoil</a>";
                }
                $html .= "<a class=\"dropdown-item cm-cp-link\" href=\"javascript:;\" data-id=\"{$cid}\">Copy Link</a>
                    </div>
                </div>
                <div class=\"clearfix\"></div>
            </div>
          </div>";
            	   
			// >>> NEW: replies toggle block <<<
			if ($replyCnt > 0) {
				$html .= "
			       	<div class=\"replies\" id=\"block-reply-{$cid}\">
			            <div class=\"rep-more rep-in\">
			                    <button type=\"button\" class=\"btn btn-sm cm-btn-show-rep\" data-id=\"{$cid}\">
			                        <i class=\"fas fa-caret-down mr-2\"></i><span>{$replyCnt} replies</span>
			                    </button>
			                </div>
			                <div class=\"replies-wrap\" id=\"replies-{$cid}\" style=\"display:none;\"></div>
			            </div>";
			} else {
			    $html .= "
			            <div class=\"replies\" id=\"replies-{$cid}\"></div>";
			}

	$html .= "
    	</div>";

    // Hidden reply form (as before)
    $html .= "<div id='reply-{$cid}' class='comment-input is-reply reply-block' style='display:none;'>
        <div class='user-avatar'>
            <img class='user-avatar-img' src='" . e($loggedIn ? ($avatar ?: '/public/images/no-avatar.jpeg') : '/public/images/no-avatar.jpeg') . "' alt='" . e($loggedIn ? $username : 'guest') . "'>
        </div>
        <div class='ci-form'>
            <form class='preform preform-dark comment-form'>
                <div class='loading-absolute bg-white' style='display: none'>
                    <div class='loading'><div class='span1'></div><div class='span2'></div><div class='span3'></div></div>
                </div>
                <input type='hidden' name='mention_id' value='" . e($loggedIn ? $authorId : 0) . "'>
                <input type='hidden' name='parent_id' value='{$cid}'>
                <input type='hidden' name='episode_id' value='" . e($episodeId) . "'>
                <textarea class='form-control form-control-textarea comment-subject emo-on cm-input-{$cid}' name='content' maxlength='3000' placeholder='Add a reply' required></textarea>
                <div class='ci-emo'>
                    <div data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\" class='cb-icon'><i class='fas fa-laugh'></i></div>
                    <div class='dropdown-menu dropdown-menu-model dropdown-menu-normal dr-bottom-right dropdown-menu-emo' style='max-height: 400px !important;'>
                        <emoji-picker data-input='.cm-input-{$cid}'></emoji-picker>
                    </div>
                </div>
                <div class='ci-buttons'>
                    <div class='alert alert-danger cm-error mt-1' style='display: none;'></div>
                    <div class='ci-b-left'><div class='cb-li'><a class='btn btn-sm btn-spoil'><i class='fas fa-check mr-2'></i>Spoil?</a></div></div>
                    <div class='ci-b-right'>
                        <div class='cb-li'><a class='btn btn-sm btn-secondary btn-close-reply' data-id='{$cid}'>Close</a></div>
                        <div class='cb-li'><button class='btn btn-sm btn-primary ml-2'>Reply</button></div>
                    </div>
                </div>
            </form>
        </div>
    </div>";
    $html .= "</div>"; // safety close
}

/* output */
echo json_encode([
    'status'   => true,
    'html'     => $html,
    'nextPage' => $nextPage
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
