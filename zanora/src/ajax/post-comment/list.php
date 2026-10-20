<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function json_response($data = [], $status = 200){
    header("Content-Type: application/json; charset=utf-8");
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/* --- Helpers --- */
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function timeAgo($datetime){
    $t = time() - strtotime($datetime);
    if ($t < 60)    return $t.' seconds ago';
    if ($t < 3600)  return floor($t/60).' minutes ago';
    if ($t < 86400) return floor($t/3600).' hours ago';
    return floor($t/86400).' days ago';
}

/* --- Inputs --- */
$path  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', trim($path, '/'));

$postId = 0;

// /ajax/post-comment/list/3  => ['ajax','post-comment','list','3']
if (isset($parts[3]) && ctype_digit($parts[3])) {
    $postId = (int)$parts[3];
}

// fallback to GET param if needed
if ($postId <= 0 && isset($_GET['post_id'])) {
    $postId = (int)$_GET['post_id'];
}

$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$sort   = $_GET['sort'] ?? 'newest';

if ($postId <= 0 || $page < 1) {
    json_response(['status'=>false, 'msg'=>'Invalid parameters']);
}

/* --- Pagination --- */
$perPage = 10;              // how many comments to SHOW
$limit   = $perPage + 1;    // how many to FETCH (one extra to detect next page)
$offset  = ($page - 1) * $perPage;  // skip 10, 20, 30... not 11, 22, 33

/* --- Login state / user info --- */
$loggedIn = !empty($_SESSION['userID']);
$meId     = $loggedIn ? (int)$_SESSION['userID'] : 0;

$meAvatar = 'https://cdn.noitatnemucod.net/avatar/100x100/demon_splayer/File1.jpg';
$meName   = 'You';
$meRole   = 0;
$isAdminOrMod = false;

if ($loggedIn && $meId > 0) {
    $uq = $conn->prepare("SELECT username, image AS avatar, roles FROM users WHERE id = ? LIMIT 1");
    $uq->bind_param("i", $meId);
    $uq->execute();
    $urow = $uq->get_result()->fetch_assoc();
    $uq->close();
    if (!empty($urow)) {
        $meAvatar = e($urow['avatar'] ?? $meAvatar);
        $meName   = e($urow['username'] ?? 'You');
        $meRole   = (int)($urow['roles'] ?? 0);
        $isAdminOrMod = ($meRole == 1 || $meRole == 2);
    }
}

/* --- Order by --- */
$orderBy = 'c.created_at DESC';
if ($sort === 'oldest') {
    $orderBy = 'c.created_at ASC';
} elseif ($sort === 'top') {
    // sort by net votes, then newest
    $orderBy = '(IFNULL(cv.upvotes,0) - IFNULL(cv.downvotes,0)) DESC, c.created_at DESC';
}

/* --- Query comments with votes + rank data --- */
$sql = "SELECT
            c.*,
            u.username,
            u.image AS avatar,
            u.points,
            u.roles,
            IFNULL(cv.upvotes,0)   AS upvotes,
            IFNULL(cv.downvotes,0) AS downvotes,
            (
                SELECT COUNT(*)
                FROM post_comments r
                WHERE r.parent_id = c.id AND r.status='visible'
            ) AS reply_count,
            myv.type AS my_vote
        FROM post_comments c
        LEFT JOIN users u ON u.id = c.user_id
        LEFT JOIN (
            SELECT
                comment_id,
                SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) AS upvotes,
                SUM(CASE WHEN type = 0 THEN 1 ELSE 0 END) AS downvotes
            FROM post_comment_votes
            GROUP BY comment_id
        ) cv ON cv.comment_id = c.id
        LEFT JOIN post_comment_votes myv
               ON myv.comment_id = c.id
              AND myv.user_id   = {$meId}
        WHERE c.post_id = ?
          AND (c.parent_id IS NULL OR c.parent_id = 0)
          AND c.status = 'visible'
        ORDER BY {$orderBy}
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $postId, $limit, $offset);
$stmt->execute();
$res = $stmt->get_result();

$comments = [];
while ($row = $res->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();

$nextPage = 0;
if (count($comments) > $perPage) {
    $nextPage = $page + 1;
    array_pop($comments); // remove the extra fetched row
}

/* --- Single comment renderer (same structure as widget.php) --- */
function render_comment($c, $meId, $meAvatar, $meName, $isAdminOrMod, $loggedIn) {
    $authorRoles  = (int)($c['roles'] ?? 0);
    $authorPoints = (int)($c['points'] ?? 0);

    if ($authorRoles === 1) {
        $rankName   = 'Admin';
        $badgeClass = 'level-b';
    } elseif ($authorRoles === 2) {
        $rankName   = 'Mod';
        $badgeClass = 'level-d';
    } else {
        // from rank.php – can return something like ['Dolphin','level-a up-4']
        list($rankName, $badgeClass) = getRank($authorPoints);
    }

    $commentAvatar = e($c['avatar'] ?: '/images/no-avatar.jpeg');
    $commentUser   = e($c['username'] ?: 'Unknown');
    $timeAgo       = timeAgo($c['created_at']);

    $up   = (int)($c['upvotes'] ?? 0);
    $down = (int)($c['downvotes'] ?? 0);
    $myVote = isset($c['my_vote']) ? (string)$c['my_vote'] : null;
	$likeActive    = ($myVote === '1') ? 'active' : '';
	$dislikeActive = ($myVote === '0') ? 'active' : '';

    $replyCnt   = (int)$c['reply_count'];
    $hasReplies = $replyCnt > 0;
    $isPinned   = (isset($c['is_pinned']) && $c['is_pinned']) ? true : false;

    ob_start(); ?>
    <div class="cw_l-line" id="cm-<?= (int)$c['id'] ?>">
        <a href="/community/user/<?= (int)$c['user_id'] ?>" class="user-avatar">
            <img class="user-avatar-img" src="<?= $commentAvatar ?>"
                 alt="<?= $commentUser ?>">
        </a>
        <div class="info">
            <?php if ($isPinned): ?>
                <div class="ipinned"><span><i class="fas fa-thumbtack mr-2"></i>Pinned</span></div>
            <?php endif; ?>

            <div class="ihead">
                <a href="/community/user/<?= (int)$c['user_id'] ?>" target="_blank"
                   class="user-name is-level-x <?= e($badgeClass) ?>">
                    <?= $badgeClass ? '<i class="badg-level '.e($badgeClass).'"></i>' : '' ?>
                    <?= $commentUser ?>
                    <?= $rankName ? '<span>'.e($rankName).'</span>' : '' ?>
                </a>

                <div class="time"><?= e($timeAgo) ?></div>
            </div>

            <div class="ibody">
                <p><?= nl2br(e($c['content'])) ?></p>
            </div>

            <div class="ibottom">
                <div class="ib-li ib-reply" data-id="<?= (int)$c['id'] ?>">
                    <a class="btn"><i class="fas fa-reply mr-1"></i>Reply</a>
                </div>
                <div class="ib-li ib-like">
                    <a class="btn cm-btn-vote  <?= $likeActive ?>"
                       data-id="<?= (int)$c['id'] ?>" data-type="1">
                        <i class="far fa-thumbs-up mr-1"></i>
                        <span class="value"><?= $up ?: '' ?></span>
                    </a>
                </div>
                <div class="ib-li ib-dislike">
                    <a class="btn cm-btn-vote <?= $dislikeActive ?>"
                       data-id="<?= (int)$c['id'] ?>" data-type="0">
                        <i class="far fa-thumbs-down mr-1"></i>
                        <span class="value"><?= $down ?: '' ?></span>
                    </a>
                </div>
                <div class="ib-li">
                    <a class="btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-h mr-1"></i>More
                    </a>
                    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal"
                         aria-labelledby="ssc-list" data-user-id="<?= (int)$c['user_id'] ?>">
                        <?php if ($loggedIn && $meId == (int)$c['user_id']): ?>
                            <a class="dropdown-item cm-action" href="javascript:;"
                               data-action="hide" data-id="<?= (int)$c['id'] ?>">Hide</a>
                        <?php else: ?>
                            <?php if ((int)$c['roles'] === 0): ?>
                                <a class="dropdown-item cm-report" href="javascript:;"

                                   data-type="1" data-id="<?= (int)$c['id'] ?>">Report Spam</a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($isAdminOrMod): ?>
                            <div class="dropdown-divider"></div>
                            <?php if ($isPinned): ?>
                                <a class="dropdown-item cm-action" href="javascript:;"
                                   data-action="unpin" data-id="<?= (int)$c['id'] ?>">Unpin comment</a>
                            <?php else: ?>
                                <a class="dropdown-item cm-action" href="javascript:;"
                                   data-action="pin" data-id="<?= (int)$c['id'] ?>">Pin comment</a>
                            <?php endif; ?>
                            <a class="dropdown-item cm-action" href="javascript:;"
                               data-action="delete" data-id="<?= (int)$c['id'] ?>">Delete comment</a>
                            <a class="dropdown-item ac-mute" href="javascript:;" data-hours="24">Mute 24h</a>
                            <a class="dropdown-item ac-block" href="javascript:;"
                               data-user-id="<?= (int)$c['user_id'] ?>">Block user</a>
                        <?php endif; ?>

                        <a class="dropdown-item post-cm-cp-link" href="javascript:;"
                           data-id="<?= (int)$c['id'] ?>">Copy Link</a>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>

            <?php if ($loggedIn): ?>
                <div id="reply-<?= (int)$c['id'] ?>" class="comment-input is-reply reply-block"
                     style="display: none;">
                    <div class="user-avatar">
                        <img class="user-avatar-img" src="<?= e($meAvatar) ?>"
                             alt="<?= e($meName) ?>">
                    </div>
                    <div class="ci-form">
                        <form class="preform preform-dark post-comment-form">
                            <div class="loading-absolute bg-white" style="display: none">
                                <div class="loading">
                                    <div class="span1"></div>
                                    <div class="span2"></div>
                                    <div class="span3"></div>
                                </div>
                            </div>
                            <input type="hidden" value="<?= (int)$c['user_id'] ?>" name="mention_id">
                            <input type="hidden" value="<?= (int)$c['id'] ?>" name="parent_id">
                            <textarea class="form-control form-control-textarea comment-subject emo-on cm-input-<?= (int)$c['id'] ?>"

                                      name="content" maxlength="3000"
                                      placeholder="Add a reply" required></textarea>
                            <div class="ci-emo">
                                <div data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                     class="cb-icon"><i class="fas fa-laugh"></i></div>
                                <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal dr-bottom-right dropdown-menu-emo"
                                     style="max-height: 400px !important;">
                                    <emoji-picker data-input=".cm-input-<?= (int)$c['id'] ?>"></emoji-picker>
                                </div>
                            </div>
                            <div class="ci-buttons">
                                <div class="alert alert-danger cm-error mt-1" style="display: none;"></div>
                                <div class="ci-b-right">
                                    <div class="cb-li">
                                        <a class="btn btn-sm btn-secondary btn-close-reply"
                                           data-id="<?= (int)$c['id'] ?>">Close</a>
                                    </div>
                                    <div class="cb-li">
                                        <button class="btn btn-sm btn-primary ml-2">Reply</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($hasReplies): ?>
                <div class="replies" id="block-reply-<?= (int)$c['id'] ?>">
                    <div class="rep-more rep-in">
                        <button type="button" class="btn btn-sm cm-btn-show-rep"
                                data-id="<?= (int)$c['id'] ?>">
                            <i class="fas fa-caret-down mr-2"></i>
                            <span><?= $replyCnt ?> replies</span>
                        </button>
                    </div>
                    <div class="replies-wrap" id="replies-<?= (int)$c['id'] ?>" style="display: none;"></div>
                </div>
            <?php else: ?>
                <div class="replies" id="replies-<?= (int)$c['id'] ?>"></div>
            <?php endif; ?>
    </div>

    <?php
    return ob_get_clean();
}

/* --- Build HTML (only list items, no header, no JS) --- */
$commentsHtml = '';
foreach ($comments as $row) {
    $commentsHtml .= render_comment($row, $meId, $meAvatar, $meName, $isAdminOrMod, $loggedIn);
}

/* --- Response --- */
json_response([
    'status'   => true,
    'html'     => $commentsHtml,  // only <div class="cw_l-line">...</div> blocks
    'nextPage' => $nextPage,
]);
