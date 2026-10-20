<?php
header('Content-Type: application/json; charset=utf-8');
// /src/ajax/post-comment/widget.php
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php';
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/user/bootstrap_session.php');
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/points.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/points.php');
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* --- Time ago helper (use unified format) --- */
function timeAgo($datetime){
    $t = time() - strtotime($datetime);
    if ($t < 60)    return $t.' seconds ago';
    if ($t < 3600)  return floor($t/60).' minutes ago';
    if ($t < 86400) return floor($t/3600).' hours ago';
    return floor($t/86400).' days ago';
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/* --- c_id hoisting for highlight/scroll --- */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriParts = explode('/', trim($path, '/'));

$postId = 0;
if (isset($uriParts[3]) && is_numeric($uriParts[3])) $postId = (int)$uriParts[3];
if (isset($_GET['post_id']) && (int)$_GET['post_id'] > 0) $postId = (int)$_GET['post_id'];

$sort    = $_GET['sort']    ?? 'newest';
$partial = $_GET['partial'] ?? ''; // if 'list', return only list HTML fragment
$isListOnly = (strtolower($partial) === 'list');

$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 10;
$limit     = $perPage + 1;
$offset    = ($page - 1) * $perPage;

if ($postId <= 0) echo json_encode(['status' => false, 'msg' => 'Invalid post']);

/* --- Login state / user info --- */
$loggedIn = !empty($_SESSION['userID']);
$meId     = $loggedIn ? (int)$_SESSION['userID'] : 0;

$meAvatar = '/images/no-avatar.jpeg';
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
        $meAvatar = e(fix_avatar_url($urow['avatar'] ?? '/images/no-avatar.jpeg'));
        $meName   = e($urow['username'] ?? 'You');
        $meRole   = (int)($urow['roles'] ?? 0);
        $isAdminOrMod = ($meRole == 1 || $meRole == 2);
    }
}

/* --- Query comments --- */
// primary: pinned first
$primaryOrder = 'COALESCE(c.is_pinned, 0) DESC';

// secondary: depends on sort
$secondary = 'c.created_at DESC';
if ($sort === 'oldest') {
    $secondary = 'c.created_at ASC';
} elseif ($sort === 'top') {
    $secondary = '(IFNULL(cv.upvotes,0) - IFNULL(cv.downvotes,0)) DESC, c.created_at DESC';
}

$orderBy = $primaryOrder . ', ' . $secondary;

/* --- Total comments count --- */
$totalComments = 0;
if (!$isListOnly) {
    $countSql = "SELECT COUNT(*) AS cnt
                 FROM post_comments
                 WHERE post_id = ?
                   AND status = 'visible'";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("i", $postId);
    $countStmt->execute();
    $countRes = $countStmt->get_result()->fetch_assoc();
    $countStmt->close();
    $totalComments = (int)($countRes['cnt'] ?? 0);
}

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
            SELECT comment_id,
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
while ($r = $res->fetch_assoc()) $comments[] = $r;
$stmt->close();

/* Detect next page */
$nextPage = (count($comments) > $perPage) ? $page + 1 : 0;
if ($nextPage) {
    // remove the extra row
    array_pop($comments);
}

/* ---- c_id Hoisting logic ---- */
$cIdParam  = (isset($_GET['c_id']) ? (int)$_GET['c_id'] : (isset($_GET['cId']) ? (int)$_GET['cId'] : 0));
$gotoId = null;
$cParentId = null;
$hoistedRow = null;

if ($cIdParam > 0) {
    // Try to find comment in the top-level set FIRST
    foreach ($comments as $k => $row) {
        if ((int)$row['id'] === $cIdParam) {
            $hoistedRow = $row; // Top-level comment
            $gotoId = $row['id'];
            $cParentId = 0;
            unset($comments[$k]);
            break;
        }
    }

    // If not found, look in replies!
    if (!$hoistedRow) {
        // Query for the reply by ID
        $stmt2 = $conn->prepare("SELECT id, parent_id FROM post_comments WHERE id = ? AND status='visible' LIMIT 1");
        $stmt2->bind_param("i", $cIdParam);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        if ($result2 && $reply = $result2->fetch_assoc()) {
            $gotoId = (int)$reply['id'];
            $cParentId = (int)$reply['parent_id'];
        }
        $stmt2->close();
        // Optionally, hoist the parent comment to the top for highlight
        foreach ($comments as $pk => $prow) {
            if ((int)$prow['id'] === $cParentId) {
                $hoistedRow = $prow;
                unset($comments[$pk]);
                break;
            }
        }
    }
}

/* --- Top comment input --- */
if ($loggedIn) {
    $commentInputHtml = '
<div class="comment-input">
    <div class="user-avatar"><img class="user-avatar-img" src="'.$meAvatar.'" alt="'.$meName.'"></div>
    <div class="ci-form">
        <div class="user-name">
            Comment as <span class="link-highlight ml-1">'.$meName.'</span>
        </div>
        <form class="preform preform-dark post-comment-form">
            <div class="loading-absolute bg-white" style="display:none;">
                <div class="loading"><div class="span1"></div><div class="span2"></div><div class="span3"></div></div>
            </div>
            <textarea class="form-control form-control-textarea comment-subject emo-on cm-input-base"
                      id="df-cm-content"
                      name="content"
                      maxlength="3000"
                      placeholder="Leave a comment" required></textarea>
            <div class="ci-emo">
                <div data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="cb-icon"><i class="fas fa-laugh"></i></div>
                <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal dr-bottom-right dropdown-menu-emo" style="max-height:400px;">
                    <emoji-picker data-input=".cm-input-base"></emoji-picker>
                </div>
            </div>
            <div class="ci-buttons" id="df-cm-buttons" style="display:none;">
                <div class="alert alert-danger cm-error mt-1" style="display:none;"></div>
                <div class="ci-b-right">
                    <div class="cb-li"><a class="btn btn-sm btn-secondary" id="df-cm-close">Close</a></div>
                    <div class="cb-li"><button class="btn btn-sm btn-primary ml-2">Comment</button></div>
                </div>
            </div>
        </form>
    </div>
</div>';
} else {
    $commentInputHtml = '
<div class="comment-input">
    <div class="user-avatar"><img class="user-avatar-img" src="/images/no-avatar.jpeg"></div>
    <div class="ci-form">
        <div class="user-name">
            You must be <a class="link-highlight mr-1 ml-1" href="javascript:;" data-toggle="modal" data-target="#modallogin">login</a> to post a comment
        </div>
        <form class="preform preform-dark post-comment-form">
            <div class="loading-absolute bg-white" style="display:none;">
                <div class="loading"><div class="span1"></div><div class="span2"></div><div class="span3"></div></div>
            </div>
            <textarea class="form-control form-control-textarea comment-subject emo-on cm-input-base"
                      id="df-cm-content"
                      name="content"
                      maxlength="3000"
                      placeholder="Leave a comment" required></textarea>
            <div class="ci-emo">
                <div data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="cb-icon"><i class="fas fa-laugh"></i></div>
                <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal dr-bottom-right dropdown-menu-emo" style="max-height:400px;">
                    <emoji-picker data-input=".cm-input-base"></emoji-picker>
                </div>
            </div>
            <div class="ci-buttons" id="df-cm-buttons" style="display:none;">
                <div class="alert alert-danger cm-error mt-1" style="display:none;"></div>
                <div class="ci-b-right">
                    <div class="cb-li"><a class="btn btn-sm btn-secondary" id="df-cm-close">Close</a></div>
                    <div class="cb-li"><button class="btn btn-sm btn-primary ml-2">Comment</button></div>
                </div>
            </div>
        </form>
    </div>
</div>';
}

/* --- Comment List rendering, with all features --- */
function build_one($c, $meId, $meAvatar, $meName, $isAdminOrMod, $loggedIn) {
    $authorRoles  = (int)($c['roles'] ?? 0);
    $authorPoints = (int)($c['points'] ?? 0);

    if ($authorRoles === 1) {
        $rankName   = 'Admin';
        $badgeClass = 'level-b';
    } elseif ($authorRoles === 2) {
        $rankName   = 'Mod';
        $badgeClass = 'level-d';
    } else {
        list($rankName, $badgeClass) = getRank($authorPoints);
    }

    $commentAvatar = e(fix_avatar_url($c['avatar'] ?: '/images/no-avatar.jpeg'));
    $commentUser   = e($c['username'] ?: 'Unknown');
    $timeAgo       = timeAgo($c['created_at']);

    $up   = (int)($c['upvotes'] ?? 0);
    $down = (int)($c['downvotes'] ?? 0);
    $myVote = isset($c['my_vote']) ? (string)$c['my_vote'] : null;
	$likeActive    = ($myVote === '1') ? 'active' : '';
	$dislikeActive = ($myVote === '0') ? 'active' : '';

    $replyCnt = (int)$c['reply_count'];
    $hasReplies = $replyCnt > 0;
    $isPinned = (isset($c['is_pinned']) && $c['is_pinned']) ? true : false;

    ob_start(); ?>
    <div class="cw_l-line" id="cm-<?= (int)$c['id'] ?>">
        <a href="/community/user/<?= (int)$c['user_id'] ?>" class="user-avatar">
            <img class="user-avatar-img" src="<?= $commentAvatar ?>" alt="<?= $commentUser ?>">
        </a>
        <div class="info">
            <?php if ($isPinned): ?>
                <div class="ipinned"><span><i class="fas fa-thumbtack mr-2"></i>Pinned</span></div>
            <?php endif; ?>
            <div class="ihead">
                <a href="/community/user/<?= (int)$c['user_id'] ?>" target="_blank" class="user-name is-level-x <?= $badgeClass ?>">
                    <?= $badgeClass ? '<i class="badg-level '.$badgeClass.'"></i>' : '' ?>
                    <?= $commentUser ?>
                    <?= $rankName ? '<span>'.$rankName.'</span>' : '' ?>
                </a>
                <div class="time"><?= $timeAgo ?></div>
            </div>
            <div class="ibody">
			    <p><?= nl2br(e($c['content'])) ?></p>
			</div>
            <div class="ibottom">
                <div class="ib-li ib-reply" data-id="<?= (int)$c['id'] ?>">
                    <a class="btn"><i class="fas fa-reply mr-1"></i>Reply</a>
                </div>
                <div class="ib-li ib-like">
                    <a class="btn cm-btn-vote <?= $likeActive ?>" data-id="<?= (int)$c['id'] ?>" data-type="1">
                        <i class="far fa-thumbs-up mr-1"></i>
                        <span class="value"><?= $up ?: '' ?></span>
                    </a>
                </div>
                <div class="ib-li ib-dislike">
                    <a class="btn cm-btn-vote <?= $dislikeActive ?>" data-id="<?= (int)$c['id'] ?>" data-type="0">
                        <i class="far fa-thumbs-down mr-1"></i>
                        <span class="value"><?= $down ?: '' ?></span>
                    </a>
                </div>
                <div class="ib-li">
                    <a class="btn more-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-h mr-1"></i>More
                    </a>
                    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" data-user-id="<?= (int)$c['user_id'] ?>">
                    <?php if ($loggedIn && $meId == $c['user_id']) { ?>
                        <a class="dropdown-item cm-action" href="javascript:;" data-action="hide" data-id="<?= (int)$c['id'] ?>">Hide</a>
                    <?php } else if ((int)$c['roles'] === 0) { ?>
                        <a class="dropdown-item cm-report" href="javascript:;" data-type="1" data-id="<?= (int)$c['id'] ?>">Report Spam</a>
                    <?php }
                    if ($isAdminOrMod) {
                        if ($isPinned) { ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item cm-action" href="javascript:;" data-action="unpin" data-id="<?= (int)$c['id'] ?>">Unpin comment</a>
                        <?php } else { ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item cm-action" href="javascript:;" data-action="pin" data-id="<?= (int)$c['id'] ?>">Pin comment</a>
                        <?php } ?>
                        <a class="dropdown-item cm-action" href="javascript:;" data-action="delete" data-id="<?= (int)$c['id'] ?>">Delete comment</a>
                        <a class="dropdown-item ac-mute" href="javascript:;" data-hours="24">Mute 24h</a>
                        <a class="dropdown-item ac-block" href="javascript:;" data-user-id="<?= (int)$c['user_id'] ?>">Block user</a>
                    <?php }
                    ?>
                        <a class="dropdown-item post-cm-cp-link" href="javascript:;" data-id="<?= (int)$c['id'] ?>">Copy Link</a>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <?php /* Reply form */ if ($loggedIn) { ?>
            <div id="reply-<?= (int)$c['id'] ?>" class="comment-input is-reply reply-block" style="display: none;">
                <div class="user-avatar">
                    <img class="user-avatar-img" src="<?= $meAvatar ?>" alt="<?= $meName ?>">
                </div>
                <div class="ci-form">
                    <form class="preform preform-dark post-comment-form">
                        <div class="loading-absolute bg-white" style="display:none;">
                            <div class="loading"><div class="span1"></div><div class="span2"></div><div class="span3"></div></div>
                        </div>
                        <input type="hidden" name="mention_id" value="<?= (int)$c['user_id'] ?>">
                        <input type="hidden" name="parent_id" value="<?= (int)$c['id'] ?>">
                        <textarea class="form-control form-control-textarea comment-subject emo-on cm-input-<?= (int)$c['id'] ?>"
                                  name="content" maxlength="3000"
                                  placeholder="Add a reply" required></textarea>
                        <div class="ci-emo">
                            <div data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="cb-icon">
                                <i class="fas fa-laugh"></i>
                            </div>
                            <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal dr-bottom-right dropdown-menu-emo" style="max-height:400px !important;">
                                <emoji-picker data-input=".cm-input-<?= (int)$c['id'] ?>"></emoji-picker>
                            </div>
                        </div>
                        <div class="ci-buttons">
                            <div class="alert alert-danger cm-error mt-1" style="display:none;"></div>
                            <div class="ci-b-right">
                                <div class="cb-li"><a class="btn btn-sm btn-secondary btn-close-reply" data-id="<?= (int)$c['id'] ?>">Close</a></div>
                                <div class="cb-li"><button class="btn btn-sm btn-primary ml-2">Reply</button></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php }
            if ($hasReplies) { ?>
                <div class="replies" id="block-reply-<?= (int)$c['id'] ?>">
                    <div class="rep-more rep-in">
                    	<button type="button" class="btn btn-sm cm-btn-show-rep" data-id="<?= (int)$c['id'] ?>">
                        	<i class="fas fa-caret-down mr-2"></i>
                        	<span><?= $replyCnt ?> replies</span>
                    	</button>
                    </div>
                    <div class="replies-wrap" id="replies-<?= (int)$c['id'] ?>" style="display: none;"></div>
                </div>
            <?php } else {
                echo '<div class="replies" id="replies-'.(int)$c['id'].'"></div>';
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/* ---- Render all ---- */
/* ---- Render all ---- */
$listInner = '';
if ($hoistedRow) {
    $listInner .= build_one($hoistedRow, $meId, $meAvatar, $meName, $isAdminOrMod, $loggedIn);
}
foreach ($comments as $c) {
    $listInner .= build_one($c, $meId, $meAvatar, $meName, $isAdminOrMod, $loggedIn);
}

// View more button if there is a next page
$viewMoreHtml = '';
if ($nextPage > 0 && !$isListOnly) {
    $viewMoreHtml = "
    <div class='rep-more mt-3'>
        <button type='button' class='btn btn-sm' id='cm-view-more' data-page='{$nextPage}'>
            <i class='fas fa-caret-down mr-2'></i><span>View more</span>
        </button>
    </div>";
}

// Full list (for full widget)
$listHtml = '<div class="cw_list">'.$listInner.'</div>'.$viewMoreHtml;

/* ---- Widget HTML ---- */
$widgetHtml = '
<div class="comment-widget">
    <div class="sc-header">
        <div class="sc-h-title">
            <i class="far fa-comment-alt mr-2"></i>'.number_format($totalComments).'<span> Comments</span>
        </div>
        <div class="sc-h-sort">
            <a class="btn btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Sort by<i class="fas fa-sort ml-2"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                <a class="dropdown-item cm-sort '.($sort==="top"?"active":"").'" data-value="top" href="javascript:;">Top '.($sort==="top"?"<i class=\"fas fa-check mt-2\"></i>":"").'</a>
                <a class="dropdown-item cm-sort '.($sort==="newest"?"active":"").'" data-value="newest" href="javascript:;">Newest '.($sort==="newest"?"<i class=\"fas fa-check mt-2\"></i>":"").'</a>
                <a class="dropdown-item cm-sort '.($sort==="oldest"?"active":"").'" data-value="oldest" href="javascript:;">Oldest '.($sort==="oldest"?"<i class=\"fas fa-check mt-2\"></i>":"").'</a>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    '.$commentInputHtml.'
    <div class="list-comment">'.$listHtml.'</div>
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <script>
        $(document).on("emoji-click","emoji-picker",function(e){
            var input = $($(e.target).data("input"));
            input.val(input.val() + e.detail.unicode);
            input.focus();
        });
    </script>
</div>';

/* --- Output --- */
$out = [
    'status'   => true,
    'html' => $partial === 'list' ? $listHtml : $widgetHtml,
    'gotoId'   => $gotoId !== null ? strval($gotoId) : null,
    'cParentId'=> $cParentId,
];
echo json_encode($out);
exit;
