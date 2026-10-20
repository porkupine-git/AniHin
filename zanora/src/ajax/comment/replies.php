<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/community/rank.php');

$parentId = 0;
if (!empty($_GET['id'])) {
    $parentId = (int)$_GET['id'];
} else {
    $base = basename($_SERVER['REQUEST_URI'] ?? '');
    if (ctype_digit($base)) $parentId = (int)$base;
}
if ($parentId <= 0) { echo json_encode(['html'=>'']); exit; }

$conn->set_charset('utf8mb4');

/* ---------- helpers ---------- */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function time_elapsed_string($datetime,$full=false){
    $now=new DateTime; $ago=new DateTime($datetime); $diff=$now->diff($ago);
    $weeks=floor($diff->d/7); $days=$diff->d%7;
    $string=[
        'y'=>$diff->y?$diff->y.' year'.($diff->y>1?'s':''):null,
        'm'=>$diff->m?$diff->m.' month'.($diff->m>1?'s':''):null,
        'w'=>$weeks?$weeks.' week'.($weeks>1?'s':''):null,
        'd'=>$days?$days.' day'.($days>1?'s':''):null,
        'h'=>$diff->h?$diff->h.' hour'.($diff->h>1?'s':''):null,
        'i'=>$diff->i?$diff->i.' minute'.($diff->i>1?'s':''):null,
        's'=>$diff->s?$diff->s.' second'.($diff->s>1?'s':''):null,
    ];
    $string=array_filter($string);
    if(!$full) $string=array_slice($string,0,1);
    return $string?implode(', ',$string).' ago':'just now';
}
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

/* ---------- viewer ---------- */
$loggedIn = !empty($_SESSION['userID']);
$myId = $loggedIn ? (int)$_SESSION['userID'] : 0;

$me = null; $me_is_admin = 0;
if ($loggedIn) {
    $m = $conn->query("SELECT id, username, image, roles FROM users WHERE id={$myId} LIMIT 1");
    if ($m && $meRow = $m->fetch_assoc()) { $me = $meRow; $me_is_admin = ($meRow['roles'] == 1) ? 1 : 0; }
}

/* ---------- load root context (movie_id, episode_id) ---------- */
$rootMovieId = ''; $rootEpisodeId = '';
$ctx = $conn->query("SELECT movie_id, episode_id, user_id FROM comments WHERE id={$parentId} LIMIT 1");
if ($ctx && $cRow = $ctx->fetch_assoc()) {
    $rootMovieId   = (string)($cRow['movie_id'] ?? '');
    $rootEpisodeId = (string)($cRow['episode_id'] ?? '');
}

/* ---------- query replies; include parent_user for @mention ---------- */
$sql = "SELECT 
            c.id,
            c.user_id,
            c.parent_user_id,
            COALESCE(c.is_pinned,0) AS is_pinned,
            c.content,
            c.is_spoil,
            c.created_at,
            u.username,
            u.image,
            u.points,
            u.roles,
            pu.username AS parent_username,
            IFNULL(cv.upvotes,0)   AS upvotes,
            IFNULL(cv.downvotes,0) AS downvotes,
            myv.type               AS my_vote
        FROM comments c
        LEFT JOIN users u   ON u.id = c.user_id
        LEFT JOIN users pu  ON pu.id = c.parent_user_id
        LEFT JOIN (
            SELECT comment_id,
                   SUM(CASE WHEN type=1 THEN 1 ELSE 0 END) AS upvotes,
                   SUM(CASE WHEN type=0 THEN 1 ELSE 0 END) AS downvotes
            FROM comment_votes
            GROUP BY comment_id
        ) cv ON cv.comment_id = c.id
        LEFT JOIN comment_votes myv
               ON myv.comment_id = c.id AND myv.user_id = {$myId}
        WHERE c.parent_id = {$parentId} AND c.status='active'
        ORDER BY c.created_at ASC";

$res = $conn->query($sql);
if (!$res) { echo json_encode(['html'=>'']); exit; }

/* ---------- build ---------- */
$html = '';
while ($r = $res->fetch_assoc()) {
    $cid            = (int)$r['id'];
    $authorId       = (int)$r['user_id'];
    $authorName     = $r['username'] ?: 'Unknown';
    $authorAvatar   = fix_avatar_url($r['image'] ?: '/public/images/no-avatar.jpeg');
    $authorPoints   = (int)($r['points'] ?? 0);
    $replyToId      = (int)($r['parent_user_id'] ?? 0);
    $replyToName    = trim((string)($r['parent_username'] ?? ''));
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

    $utcTime   = gmdate('Y-m-d\TH:i:s\Z', strtotime($r['created_at']));
    $humanTime = time_elapsed_string($r['created_at']);
    $content   = linkify_player_times(nl2br(e($r['content'])));
    $is_spoil  = (int)$r['is_spoil'];
    $is_pinned = (int)$r['is_pinned'];
    $up        = (int)$r['upvotes'];
    $down      = (int)$r['downvotes'];
    $my_vote   = (string)($r['my_vote'] ?? '');

    // inline @mention ONLY for the user you're replying to (not the root)
    $mentionHtml = '';
    if ($replyToId > 0 && $replyToName !== '') {
        $mentionHtml = '<a class="tag-name">@'.e($replyToName).'</a> ';
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

    ob_start(); ?>
    <div class="cw_l-line reply" id="cm-<?= $cid ?>">
        <a href="/community/user/<?= $authorId ?>" class="user-avatar">
            <img class="user-avatar-img" src="<?= e($authorAvatar) ?>" alt="<?= e($authorName) ?>">
        </a>
        <div class="info">
            <div class="ihead">
                <a href="/community/user/<?= $authorId ?>" target="_blank" class="<?= e($userNameClass) ?>">
                    <?php if (!empty($badgeClass)): ?><i class="badg-level <?= e($badgeClass) ?>"></i><?php endif; ?>
                    <?= e($authorName) ?><?php if (!empty($rankName)): ?> <span><?= e($rankName) ?></span><?php endif; ?>
                </a>
                <div class="time" data-time="<?= e($utcTime) ?>"><?= e($humanTime) ?></div>
            </div>

            <div class="ibody <?= $is_spoil ? 'is-spoil' : '' ?>">
                <p><?= $mentionHtml ?><?= $content ?></p>
                <?php if ($is_spoil): ?>
                    <div class="show-spoil my-3"><button type="button" class="btn btn-sm btn-light">Show spoil</button></div>
                <?php endif; ?>
            </div>

            <div class="ibottom">
                <div class="ib-li ib-reply" data-id="<?= $cid ?>"><a class="btn"><i class="fas fa-reply mr-1"></i>Reply</a></div>
                <div class="ib-li ib-like">
                    <a class="btn cm-btn-vote <?= ($my_vote === '1') ? 'active' : '' ?>" data-id="<?= $cid ?>" data-type="1">
                        <i class="far fa-thumbs-up mr-1"></i><span class="value"><?= $up ?: '' ?></span>
                    </a>
                </div>
                <div class="ib-li ib-dislike">
                    <a class="btn cm-btn-vote <?= ($my_vote === '0') ? 'active' : '' ?>" data-id="<?= $cid ?>" data-type="0">
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
                            <a class="dropdown-item cm-report" href="javascript:;" data-type="1" data-id="<?= $cid ?>">Report Spam</a>
                            <a class="dropdown-item cm-report" href="javascript:;" data-type="2" data-id="<?= $cid ?>">Report Spoil</a>
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

            <!-- nested reply form (for replying to a reply) -->
            <div id="reply-<?= $cid ?>" class="comment-input is-reply reply-block" style="display:none;">
                <div class="user-avatar">
                    <img class="user-avatar-img" src="<?= e(fix_avatar_url($loggedIn ? ($me['image'] ?? '/public/images/no-avatar.jpeg') : '/public/images/no-avatar.jpeg')) ?>" alt="<?= e($loggedIn ? ($me['username'] ?? 'me') : 'guest') ?>">
                </div>
                <div class="ci-form">
                    <form class="preform preform-dark comment-form">
                        <div class="loading-absolute bg-white" style="display: none">
                            <div class="loading"><div class="span1"></div><div class="span2"></div><div class="span3"></div></div>
                        </div>
                        <input type="hidden" name="mention_id" value="<?= $authorId ?>">
                        <input type="hidden" name="parent_id" value="<?= $cid ?>">
                        <input type="hidden" name="episode_id" value="<?= e($rootEpisodeId) ?>">
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

        </div>
    </div>
    <?php
    $html .= ob_get_clean();
}

echo json_encode(['html' => $html]);
