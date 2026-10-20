<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php';

if (!isset($conn) || $conn->connect_errno) {
    echo json_encode(['status' => false, 'html' => '', 'error' => 'Database connection failed']);
    exit;
}
$conn->set_charset('utf8mb4');

/* -------------------- helpers -------------------- */
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime; $ago = new DateTime($datetime); $diff = $now->diff($ago);
    $weeks = floor($diff->d / 7); $days = $diff->d % 7;
    $string = [
        'y'=>$diff->y?$diff->y.' year'.($diff->y>1?'s':'' ):null,
        'm'=>$diff->m?$diff->m.' month'.($diff->m>1?'s':'' ):null,
        'w'=>$weeks?$weeks.' week'.($weeks>1?'s':'' ):null,
        'd'=>$days?$days.' day'.($days>1?'s':'' ):null,
        'h'=>$diff->h?$diff->h.' hour'.($diff->h>1?'s':'' ):null,
        'i'=>$diff->i?$diff->i.' minute'.($diff->i>1?'s':'' ):null,
        's'=>$diff->s?$diff->s.' second'.($diff->s>1?'s':'' ):null,
    ];
    $string = array_filter($string);
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
/* clickable timecodes */
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
/* Parse /ajax/comment/widget/{slug} */
function getMovieSlugFromPath(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $marker = '/ajax/comment/widget/';
    $pos = strpos($uri, $marker);
    if ($pos === false) return '';
    $rest = substr($uri, $pos + strlen($marker));
    $rest = explode('?', $rest, 2)[0];
    $rest = urldecode(trim($rest, '/'));
    if ($rest === '' || !preg_match('~^[a-z0-9\-]+(?:-[a-z0-9\-]+)*$~i', $rest)) return '';
    return $rest;
}

/* ===== Episode number lookup (APCu cache) ===== */
function getEpisodeNoById(string $animeSlug, string $episodeId, string $zpiBase): ?int {
    if ($animeSlug === '' || $episodeId === '') return null;
    $cacheKey = "epmap:" . $animeSlug;
    $map = function_exists('apcu_fetch') ? apcu_fetch($cacheKey) : null;

    if (!is_array($map)) {
        $url = rtrim($zpiBase ?? '', "/") . "/episodes/" . rawurlencode($animeSlug);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $resp = curl_exec($ch); curl_close($ch);
        $map = [];
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
        if (function_exists('apcu_store') && !empty($map)) apcu_store($cacheKey, $map, 86400);
    }
    return isset($map[(string)$episodeId]) ? (int)$map[(string)$episodeId] : null;
}

/* -------------------- inputs -------------------- */
$loggedIn = !empty($_SESSION['userID']);
$myId = $loggedIn ? (int)$_SESSION['userID'] : 0;

/* fetch self (needed for admin controls) */
$me = null;
$me_role = 0; // 0 = normal, 1 = admin, 2 = mod
$me_is_admin = 0;

if ($loggedIn) {
    $uq = $conn->query("SELECT id, username, image, roles FROM users WHERE id = {$myId} LIMIT 1");
    if ($uq && $ur = $uq->fetch_assoc()) {
        $me = $ur;
        $me_is_admin = ($ur['roles'] == 1) ? 1 : 0;   // <-- using roles now
    }
}

$movieIdFromGet = trim($_GET['movieId'] ?? '');
$movieId = ($movieIdFromGet !== '' && preg_match('~^[a-z0-9\-]+(?:-[a-z0-9\-]+)*$~i',$movieIdFromGet))
    ? $movieIdFromGet
    : getMovieSlugFromPath();

$episodeId = trim($_GET['episodeId'] ?? '');

/* accept both type and c_type */
$typeParam = $_GET['type'] ?? $_GET['c_type'] ?? 'episode';
$sort      = in_array($_GET['sort'] ?? '', ['top','newest','oldest']) ? $_GET['sort'] : 'newest';
$type      = in_array($typeParam, ['episode','all']) ? $typeParam : 'episode';

$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 10;
$limit     = $perPage + 1;
$offset    = ($page - 1) * $perPage;

/* cId hoist: accept c_id or cId */
$cIdParam  = isset($_GET['c_id']) ? (int)$_GET['c_id'] : (isset($_GET['cId']) ? (int)$_GET['cId'] : 0);

/* Option B: return list only */
$partial   = (strtolower(trim($_GET['partial'] ?? '')) === 'list');

if ($movieId === '') {
    echo json_encode(['status'=>true, 'html'=>'', 'gotoId'=>null, 'cParentId'=>null]);
    exit;
}

/* Episode & type labels */
$episodeNo = ($episodeId !== '') ? getEpisodeNoById($movieId, $episodeId, $zpi ?? '') : null;
$episodeLabel = $episodeNo ? ('Episode ' . e($episodeNo)) : 'Episode';
$typeLabel = ($type === 'episode') ? $episodeLabel : 'All Episode';

$epActive  = ($type === 'episode') ? 'active' : '';
$allActive = ($type === 'all')     ? 'active' : '';
$epCheck   = ($type === 'episode') ? '<i class="fas fa-check mt-2"></i>' : '';
$allCheck  = ($type === 'all')     ? '<i class="fas fa-check mt-2"></i>' : '';

/* -------------------- query -------------------- */
$where = "c.movie_id = '" . $conn->real_escape_string($movieId) . "'";
if ($type === 'episode' && $episodeId !== '') {
    $where .= " AND c.episode_id = '" . $conn->real_escape_string($episodeId) . "'";
}
$where .= " AND c.parent_id = 0 AND c.status = 'active'";

/* pin first, then sort */
$primaryOrder = "COALESCE(c.is_pinned, 0) DESC";
$secondary = 'c.created_at DESC';
if ($sort === 'oldest') {
    $secondary = 'c.created_at ASC';
} elseif ($sort === 'top') {
    $secondary = '((IFNULL(cv.upvotes,0) - IFNULL(cv.downvotes,0))) DESC, c.created_at DESC';
}
$order = "ORDER BY {$primaryOrder}, {$secondary}";

/* totals */
$countSql = "SELECT COUNT(*) AS cnt FROM comments c WHERE {$where}";
$countRes = $conn->query($countSql);
$totalComments = ($countRes && $row = $countRes->fetch_assoc()) ? (int)$row['cnt'] : 0;

/* fetch rows */
$sql = "SELECT 
            c.id, c.user_id, c.content, c.is_spoil, c.created_at, c.episode_id,
            COALESCE(c.is_pinned,0) AS is_pinned,
            u.username, u.image, u.points, u.roles,
            IFNULL(cv.upvotes,0) AS upvotes,
            IFNULL(cv.downvotes,0) AS downvotes,
            (SELECT COUNT(*) FROM comments r WHERE r.parent_id = c.id AND r.status = 'active') AS reply_count,
            myv.type AS my_vote
        FROM comments c
        LEFT JOIN users u ON u.id = c.user_id
        LEFT JOIN (
            SELECT comment_id,
                   SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) AS upvotes,
                   SUM(CASE WHEN type = 0 THEN 1 ELSE 0 END) AS downvotes
            FROM comment_votes
            GROUP BY comment_id
        ) cv ON cv.comment_id = c.id
        LEFT JOIN comment_votes myv ON myv.comment_id = c.id AND myv.user_id = {$myId}
        WHERE {$where}
        {$order}
        LIMIT {$limit} OFFSET {$offset}";
$res = $conn->query($sql);

$rows = [];
if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;
$nextPage = (count($rows) > $perPage) ? $page + 1 : 0;
if ($nextPage) array_pop($rows);

/* -------------------- optional HOIST by c_id (no badge) -------------------- */
/*
   If ?c_id=XYZ (or ?cId=...), we hoist that top-level comment (or its parent, if a reply)
   to the very top of the list *without* showing a pinned badge.
   Scope:
   - type=episode : only if the hoisted comment matches current episode_id
   - type=all     : any episode is fine
*/
$gotoId = null;       // The id you'll scroll/highlight when loaded
$cParentId = null;    // The parent (top-level) comment id
$hoistedRow = null;   // The parent row to display on top

if ($cIdParam > 0) {
    // Fetch comment by c_id
    $q = $conn->query("SELECT id, parent_id, movie_id, episode_id FROM comments WHERE id = {$cIdParam} AND status='active' LIMIT 1");
    if ($q && $r0 = $q->fetch_assoc()) {
        $gotoId = (int)$r0['id'];            // The actual requested comment id (reply or top-level)
        $cParentId = (int)$r0['parent_id'];  // If parent_id > 0, it's a reply
        
        // If cParentId > 0, this is a reply → hoist parent
        $hoistId = ($cParentId > 0) ? $cParentId : $gotoId;
        $movieIdFilter = $conn->real_escape_string($r0['movie_id']);
        
        // For episode restriction
        $episodeFilter = $conn->real_escape_string($r0['episode_id']);
        $extraFilter = "c.movie_id='{$movieIdFilter}' AND c.id = {$hoistId} AND c.parent_id = 0 AND c.status='active'";
        if ($type === 'episode' && $episodeId !== '') {
            $extraFilter .= " AND c.episode_id = '{$episodeFilter}'";
        }
        // Fetch the comment row of the parent
        $pq = $conn->query("
            SELECT 
                c.id, c.user_id, c.content, c.is_spoil, c.created_at, c.episode_id,
                COALESCE(c.is_pinned,0) AS is_pinned,
                u.username, u.image, u.points, u.roles,
                IFNULL(cv.upvotes,0) AS upvotes,
                IFNULL(cv.downvotes,0) AS downvotes,
                (SELECT COUNT(*) FROM comments r WHERE r.parent_id = c.id AND r.status='active') AS reply_count,
                myv.type AS my_vote
            FROM comments c
            LEFT JOIN users u ON u.id = c.user_id
            LEFT JOIN (
                SELECT comment_id,
                       SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) AS upvotes,
                       SUM(CASE WHEN type = 0 THEN 1 ELSE 0 END) AS downvotes
                FROM comment_votes
                GROUP BY comment_id
            ) cv ON cv.comment_id = c.id
            LEFT JOIN comment_votes myv ON myv.comment_id = c.id AND myv.user_id = {$myId}
            WHERE {$extraFilter}
            LIMIT 1
        ");
        if ($pq && $pr = $pq->fetch_assoc()) {
            $hoistedRow = $pr;
            // Remove from $rows so not duplicated
            $rows = array_values(array_filter($rows, function($rr) use ($hoistId) {
                return (int)$rr['id'] !== $hoistId;
            }));
        }
    }
}

/* -------------------- builders -------------------- */
$build_one = function(array $r) use ($episodeId, $loggedIn, $me, $myId, $me_is_admin, $type, $movieId, $zpi) {
    $cid = (int)$r['id'];
    $authorId = (int)$r['user_id'];
    $authorName = $r['username'] ?: 'Unknown';
    $authorAvatar = fix_avatar_url($r['image'] ?: '/images/no-avatar.jpeg');
    $authorPoints = (int)($r['points'] ?? 0);
    $utcTime = gmdate('Y-m-d\TH:i:s\Z', strtotime($r['created_at']));
    $humanTime = time_elapsed_string($r['created_at']);
    $content = linkify_player_times(nl2br(e($r['content'])));
    $is_spoil = (int)$r['is_spoil'];
    $is_pinned = (int)$r['is_pinned'];
    $up = (int)$r['upvotes']; 
    $down = (int)$r['downvotes'];
    $replyCnt = (int)$r['reply_count'];
    $authorIsAdmin = (int)($r['roles'] ?? 0);
    
    if ($authorIsAdmin === 1) {
    // Admin
    $rankName   = 'Admin';
    $badgeClass = 'level-b';
    $rankIcon   = '/images/level-b.png';

} elseif ($authorIsAdmin === 2) {
    // Moderator
    $rankName   = 'Mod';
    $badgeClass = 'level-d';
    $rankIcon   = '/images/level-d.png';

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

    $episodeBadge = '';
    $rowEpisodeId = (string)($r['episode_id'] ?? '');
    if ($type === 'all' && $rowEpisodeId !== '') {
        $epNo = getEpisodeNoById($movieId, $rowEpisodeId, $zpi ?? '');
        if ($epNo) $episodeBadge = "<div class=\"eps\"><i class=\"fas fa-caret-left mr-1\"></i>Episode " . e($epNo) . "</div>";
    }

    ob_start(); ?>
    <div class="cw_l-line" id="cm-<?= $cid ?>">
        <a href="/community/user/<?= $authorId ?>" class="user-avatar">
            <img class="user-avatar-img" src="<?= e($authorAvatar) ?>" alt="<?= e($authorName) ?>">
        </a>
        <div class="info">

            <?php if ($is_pinned): ?>
                <div class="ipinned"><span><i class="fas fa-thumbtack mr-2"></i>Pinned</span></div>
            <?php endif; ?>

            <div class="ihead">
                <a href="/community/user/<?= $authorId ?>" target="_blank" class="<?= e($userNameClass) ?>">
                    <?php if (!empty($badgeClass)): ?><i class="badg-level <?= e($badgeClass) ?>"></i><?php endif; ?>
                    <?= e($authorName) ?><?php if (!empty($rankName)): ?> <span><?= e($rankName) ?></span><?php endif; ?>
                </a>
                <div class="time" data-time="<?= e($utcTime) ?>"><?= e($humanTime) ?></div>
                <?= $episodeBadge ?>
            </div>

            <div class="ibody <?= $is_spoil ? 'is-spoil' : '' ?>">
                <p><?= $content ?></p>
                <?php if ($is_spoil): ?>
                    <div class='show-spoil my-3'><button type='button' class='btn btn-sm btn-light'>Show spoil</button></div>
                <?php endif; ?>
            </div>

            <div class="ibottom">
                <div class="ib-li ib-reply" data-id="<?= $cid ?>"><a class="btn"><i class="fas fa-reply mr-1"></i>Reply</a></div>

                <div class="ib-li ib-like">
                    <a class="btn cm-btn-vote <?= (($r['my_vote'] ?? null) === '1') ? 'active' : '' ?>" data-id="<?= $cid ?>" data-type="1">
                        <i class="far fa-thumbs-up mr-1"></i><span class="value"><?= $up ?: '' ?></span>
                    </a>
                </div>
                <div class="ib-li ib-dislike">
                    <a class="btn cm-btn-vote <?= (($r['my_vote'] ?? null) === '0') ? 'active' : '' ?>" data-id="<?= $cid ?>" data-type="0">
                        <i class="far fa-thumbs-down mr-1"></i><span class="value"><?= $down ?: '' ?></span>
                    </a>
                </div>

                <div class="ib-li">
                    <a class="btn more-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-h mr-1"></i>More
                    </a>
                    <!-- user-id carried here for .ac-mute usage -->
                    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list" data-user-id="<?= $authorId ?>">
                        <?php if ($loggedIn && $authorId === $myId): ?>
                            <a class="dropdown-item cm-action" href="javascript:;" data-action="hide" data-id="<?= $cid ?>">Hide</a>
                            <a class="dropdown-item cm-action" href="javascript:;" data-action="spoil" data-id="<?= $cid ?>">Mark Spoil</a>
                        <?php else: ?>
                        <?php if ((int)$authorIsAdmin === 0): ?>
                            <a class="dropdown-item cm-report" href="javascript:;" data-type="1" data-id="<?= $cid ?>">Report Spam</a>
                            <a class="dropdown-item cm-report" href="javascript:;" data-type="2" data-id="<?= $cid ?>">Report Spoil</a>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($me_is_admin): ?>
                            <div class="dropdown-divider"></div>
                            <?php if ($is_pinned): ?>
                                <a class="dropdown-item cm-action" href="javascript:;" data-action="unpin" data-id="<?= $cid ?>">Unpin comment</a>
                            <?php else: ?>
                                <a class="dropdown-item cm-action" href="javascript:;" data-action="pin" data-id="<?= $cid ?>">Pin comment</a>
                            <?php endif; ?>
                        	<a class="dropdown-item cm-action" href="javascript:;" data-action="delete" data-id="<?= $cid ?>">Delete comment</a>
                            <a class="dropdown-item ac-mute" href="javascript:;" data-hours="24">Mute 24h</a>
                            <a class="dropdown-item ac-block" href="javascript:;" data-user-id="<?= $authorId ?>">Block user</a>
                        <?php endif; ?>

                        <a class="dropdown-item cm-cp-link" href="javascript:;" data-id="<?= $cid ?>">Copy Link</a>
                    </div>
                </div>

                <div class="clearfix"></div>
            </div>

            <!-- REPLY FORM (hidden by default) -->
            <div id="reply-<?= $cid ?>" class="comment-input is-reply reply-block" style="display:none;">
                <div class="user-avatar">
                    <img class="user-avatar-img" src="<?= e($loggedIn ? ($me['image'] ?? '/public/no-avatar.jpeg') : '/images/no-avatar.jpeg') ?>" alt="<?= e($loggedIn ? ($me['username'] ?? 'me') : 'guest') ?>">
                </div>
                <div class="ci-form">
                    <form class="preform preform-dark comment-form">
                        <div class="loading-absolute bg-white" style="display: none">
                            <div class="loading"><div class="span1"></div><div class="span2"></div><div class="span3"></div></div>
                        </div>
                        <input type="hidden" name="mention_id" value="<?= e($loggedIn ? ($authorId ?? 0) : 0) ?>">
                        <input type="hidden" name="parent_id" value="<?= $cid ?>">
                        <input type="hidden" name="episode_id" value="<?= e($episodeId) ?>">
                        <textarea class="form-control form-control-textarea comment-subject emo-on cm-input-<?= $cid ?>" name="content" maxlength="3000" placeholder="Add a reply" required></textarea>
                        <div class="ci-emo">
                            <div data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="cb-icon"><i class="fas fa-laugh"></i></div>
                            <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal dr-bottom-right dropdown-menu-emo" style="max-height: 400px !important;">
                                <emoji-picker data-input=".cm-input-<?= $cid ?>"></emoji-picker>
                            </div>
                        </div>
                        <div class="ci-buttons">
                            <div class="alert alert-danger cm-error mt-1" style="display: none;"></div>
                            <div class="ci-b-left"><div class="cb-li"><a class="btn btn-sm btn-spoil"><i class="fas fa-check mr-2"></i>Spoil?</a></div></div>
                            <div class="ci-b-right">
                                <div class="cb-li"><a class="btn btn-sm btn-secondary btn-close-reply" data-id="<?= $cid ?>">Close</a></div>
                                <div class="cb-li"><button class="btn btn-sm btn-primary ml-2">Reply</button></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($replyCnt > 0): ?>
                <div class="replies" id="block-reply-<?= $cid ?>">
                    <div class="rep-more rep-in">
                        <button type="button" class="btn btn-sm cm-btn-show-rep" data-id="<?= $cid ?>">
                            <i class="fas fa-caret-down mr-2"></i><span><?= $replyCnt ?> replies</span>
                        </button>
                    </div>
                    <div class="replies-wrap" id="replies-<?= $cid ?>" style="display:none;"></div>
                </div>
            <?php else: ?>
                <div class="replies" id="replies-<?= $cid ?>"></div>
            <?php endif; ?>

        </div>
    </div>
    <?php
    return ob_get_clean();
};

/* -------------------- PARTIAL (Option B) -------------------- */
if ($partial) {
    $html = '';
    // hoisted first (no pinned badge)
    if ($hoistedRow) {
        $html .= $build_one($hoistedRow);
    }
    foreach ($rows as $r) $html .= $build_one($r);
    if ($nextPage > 0) {
        $html .= "<div class='rep-more mt-3'>
            <button type='button' class='btn btn-sm' id='cm-view-more' data-page='{$nextPage}'>
                <i class='fas fa-caret-down mr-2'></i><span>View more</span>
            </button>
        </div>";
    }

    echo json_encode([
        'status'    => true,
        'html'      => $html,
        'gotoId'    => $gotoId,   // scroll target if you want to highlight
        'cParentId' => $cParentId,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/* -------------------- FULL widget -------------------- */
/* sort checks */
$sortTopCheck    = ($sort==='top')    ? '<i class="fas fa-check mt-2"></i>' : '';
$sortNewestCheck = ($sort==='newest') ? '<i class="fas fa-check mt-2"></i>' : '';
$sortOldestCheck = ($sort==='oldest') ? '<i class="fas fa-check mt-2"></i>' : '';

$html = "<div class=\"sc-header\">
    <div class=\"sc-h-from\">
      <a class=\"btn btn-sm\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">".e($typeLabel)."<i class=\"fas fa-angle-down ml-2\"></i></a>
      <div class=\"dropdown-menu dropdown-menu-model dropdown-menu-normal\" aria-labelledby=\"ssc-list\">
        <a class=\"dropdown-item cm-by {$epActive}\" data-value=\"episode\" href=\"javascript:;\">".e($episodeLabel)." {$epCheck}</a>
        <a class=\"dropdown-item cm-by {$allActive}\" data-value=\"all\" href=\"javascript:;\">All Episode {$allCheck}</a>
      </div>
    </div>
    <div class=\"sc-h-title\"><i class=\"far fa-comment-alt mr-2\"></i>".number_format($totalComments)."<span> Comments</span></div>
    <div class=\"sc-h-sort\">
        <a class=\"btn btn-sm\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">Sort by<i class=\"fas fa-sort ml-2\"></i></a>
        <div class=\"dropdown-menu dropdown-menu-model dropdown-menu-normal\" aria-labelledby=\"ssc-list\">
            <a class=\"dropdown-item cm-sort ".($sort==='top'?'active':'')."\" data-value=\"top\" href=\"javascript:;\">Top {$sortTopCheck}</a>
            <a class=\"dropdown-item cm-sort ".($sort==='newest'?'active':'')."\" data-value=\"newest\" href=\"javascript:;\">Newest {$sortNewestCheck}</a>
            <a class=\"dropdown-item cm-sort ".($sort==='oldest'?'active':'')."\" data-value=\"oldest\" href=\"javascript:;\">Oldest {$sortOldestCheck}</a>
        </div>
    </div>
    <div class=\"clearfix\"></div>
</div>";

/* Comment form */
$html .= "<div class=\"comment-input\">";
if ($loggedIn && $me) {
    $html .= "<div class=\"user-avatar\"><img class=\"user-avatar-img\" src=\"" . e(fix_avatar_url($me['image'] ?: '/images/no-avatar.jpeg')) . "\" alt=\"" . e($me['username']) . "\"></div>";
} else {
    $html .= "<div class=\"user-avatar\"><img class=\"user-avatar-img\" src=\"/images/no-avatar.jpeg\"></div>";
}
$html .= "<div class=\"ci-form\"><div class=\"user-name\">";
if ($loggedIn && $me) {
    $html .= "Comment as <span class=\"link-highlight ml-1\">" . e($me['username']) . "</span></div>
        <form class=\"preform preform-dark comment-form\">
            <div class=\"loading-absolute bg-white\" style=\"display: none;\">
                <div class=\"loading\"><div class=\"span1\"></div><div class=\"span2\"></div><div class=\"span3\"></div></div>
            </div>
            <input type=\"hidden\" name=\"episode_id\" value=\"" . e($episodeId ?: 0) . "\">
            <textarea id=\"df-cm-content\" name=\"content\" class=\"form-control form-control-textarea comment-subject emo-on cm-input-base\" maxlength=\"3000\" placeholder=\"Leave a comment\" required></textarea>
            <div class=\"ci-emo\">
                <div data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\" class=\"cb-icon\"><i class=\"fas fa-laugh\"></i></div>
                <div class=\"dropdown-menu dropdown-menu-model dropdown-menu-normal dr-bottom-right dropdown-menu-emo\" style=\"max-height: 400px !important;\">
                    <emoji-picker data-input=\".cm-input-base\"></emoji-picker>
                </div>
            </div>
            <div class=\"ci-buttons\" id=\"df-cm-buttons\" style=\"display: none;\">
                <div class=\"alert alert-danger cm-error mt-1\" style=\"display: none;\"></div>
                <div class=\"ci-b-left\">
                    <div class=\"cb-li\"><a class=\"btn btn-sm btn-spoil\"><i class=\"fas fa-check mr-2\"></i>Spoil?</a></div>
                </div>
                <div class=\"ci-b-right\">
                    <div class=\"cb-li\"><a class=\"btn btn-sm btn-secondary\" id=\"df-cm-close\">Close</a></div>
                    <div class=\"cb-li\"><button class=\"btn btn-sm btn-primary ml-2\">Comment</button></div>
                </div>
            </div>
            <div class=\"loading-absolute\" style=\"display:none;\"></div>
        </form>";
} else {
    $html .= "You must be <a class=\"link-highlight ml-1 mr-1\" href=\"javascript:;\" data-toggle=\"modal\" data-target=\"#modallogin\">login</a> to post a comment</div> 
    <form class=\"preform preform-dark comment-form\">
        <div class=\"loading-absolute bg-white\" style=\"display: none;\">
            <div class=\"loading\"><div class=\"span1\"></div><div class=\"span2\"></div><div class=\"span3\"></div></div>
            </div>
            <input type=\"hidden\" name=\"episode_id\" value=\"" . e($episodeId ?: 0) . "\">
            <textarea id=\"df-cm-content\" name=\"content\" class=\"form-control form-control-textarea comment-subject emo-on cm-input-base\" maxlength=\"3000\" placeholder=\"Leave a comment\" required></textarea>
            <div class=\"ci-emo\">
                <div data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\" class=\"cb-icon\"><i class=\"fas fa-laugh\"></i></div>
                <div class=\"dropdown-menu dropdown-menu-model dropdown-menu-normal dr-bottom-right dropdown-menu-emo\" style=\"max-height : 400px !important;\">
                    <emoji-picker data-input=\".cm-input-base\"></emoji-picker>
                </div>
            </div>
            <div class=\"ci-buttons\" id=\"df-cm-buttons\" style=\"display: none;\">
                <div class=\"alert alert-danger cm-error mt-1\" style=\"display: none;\"></div>
                <div class=\"ci-b-left\">
                    <div class=\"cb-li\"><a class=\"btn btn-sm btn-spoil\"><i class=\"fas fa-check mr-2\"></i>Spoil?</a></div>
                </div>
                <div class=\"ci-b-right\">
                    <div class=\"cb-li\"><a class=\"btn btn-sm btn-secondary\" id=\"df-cm-close\">Close</a></div>
                    <div class=\"cb-li\"><button class=\"btn btn-sm btn-primary ml-2\">Comment</button></div>
                </div>
            </div>
            <div class=\"loading-absolute\" style=\"display:none;\"></div>
        </form>";
}
$html .= "</div></div>";

/* List */
$html .= "<div class=\"list-comment\"><div class=\"cw_list\">";

/* hoisted first (no badge) */
if ($hoistedRow) {
    $html .= $build_one($hoistedRow);
}

/* rest */
if (!$rows && !$hoistedRow) {
    $html .= "<div class=\"no-comments\">No comments yet. Be the first!</div>";
} else {
    foreach ($rows as $r) $html .= $build_one($r);
}
$html .= "</div>";
if ($nextPage > 0) {
    $html .= "<div class='rep-more mt-3'>
        <button type='button' class='btn btn-sm' id='cm-view-more' data-page='{$nextPage}'>
            <i class='fas fa-caret-down mr-2'></i><span>View more</span>
        </button>
    </div>";
}
$html .= "</div>";

/* Emoji script (full widget only) */
$extras = <<<'HTML'
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<script>
// emoji
$(document).on('emoji-click', 'emoji-picker', function (event) {
    var input = $($(event.target).data('input'));
    if (!input || !input.length) return;
    input.val((input.val() || '') + event.detail.unicode);
    input.focus();
});
</script>
HTML;

$html .= $extras;

echo json_encode([
    'status'=>true,
    'html'=>$html,
    'gotoId'=>$gotoId,     // where to scroll/highlight, if you want
    'cParentId'=>$cParentId,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
