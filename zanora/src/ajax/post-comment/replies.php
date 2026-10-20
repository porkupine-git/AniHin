<?php
// /src/ajax/post-comment/replies.php
header('Content-Type: application/json; charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/community/rank.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ----------- READ PARENT COMMENT ID -----------
$parentId = 0;
if (!empty($_GET['id'])) {
    $parentId = (int)$_GET['id'];
} else {
    $base = basename($_SERVER['REQUEST_URI'] ?? '');
    if (ctype_digit($base)) $parentId = (int)$base;
}

if ($parentId <= 0) {
    echo json_encode(['status' => false, 'msg' => 'Invalid parent id']);
    exit;
}

// ----------- USER/AUTH ROLES -----------
$loggedIn = !empty($_SESSION['userID']);
$myId     = $loggedIn ? (int)$_SESSION['userID'] : 0;
$me = null; $me_is_admin = 0;
if ($loggedIn) {
    $m = $conn->query("SELECT id, username, image, roles FROM users WHERE id={$myId} LIMIT 1");
    if ($m && $meRow = $m->fetch_assoc()) { $me = $meRow; $me_is_admin = ($meRow['roles'] == 1) ? 1 : 0; }
}
$meAvatar = '/images/no-avatar.jpeg';
$meName = 'You';
if ($loggedIn && $me) {
    $meAvatar = htmlspecialchars($me['image'] ?? '/images/no-avatar.jpeg');
    $meName = htmlspecialchars($me['username'] ?? 'You');
}
// ----------- FETCH REPLIES -----------
$sql = "
    SELECT c.*, 
           u.username, u.image AS avatar, u.points, u.roles,
           pu.username AS parent_username,
           IFNULL(cv.upvotes,0) AS upvotes,
           IFNULL(cv.downvotes,0) AS downvotes
    FROM post_comments c
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN users pu ON pu.id = c.parent_user_id
    LEFT JOIN (
        SELECT comment_id,
               SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) AS upvotes,
               SUM(CASE WHEN type = 0 THEN 1 ELSE 0 END) AS downvotes
        FROM comment_votes
        GROUP BY comment_id
    ) cv ON cv.comment_id = c.id
    WHERE c.parent_id = ?
      AND c.status = 'visible'
    ORDER BY c.created_at ASC
    LIMIT 200
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $parentId);
$stmt->execute();
$res = $stmt->get_result();

// ----------- BUILD HTML -----------
$repliesHtml = '';

while ($c = $res->fetch_assoc()) {
    // --- Roles ---
    $authorRoles = (int)($c['roles'] ?? 0);
    if ($authorRoles === 1) {
        $rankName = "Admin";
        $badgeClass = "level-b";
    } elseif ($authorRoles === 2) {
        $rankName = "Mod";
        $badgeClass = "level-d";
    } else {
        list($rankName, $badgeClass) = getRank((int)$c['points']);
    }

    // --- User ---
    $avatar  = htmlspecialchars($c['avatar'] ?: "/images/no-avatar.jpeg");
    $username = htmlspecialchars($c['username'] ?? '');
    $timeAgo = timeAgo($c['created_at']);
    $content = nl2br(htmlspecialchars($c['content'] ?? ''));

    // --- Votes ---
    $up = (int)$c['upvotes'];
    $down = (int)$c['downvotes'];

    // --- Mention (@user) ---
    $mentionHtml = "";
    if (!empty($c['parent_username'])) {
        $mentionHtml = '<a class="tag-name">@'.htmlspecialchars($c['parent_username']).'</a> ';
    }

    // --- Pin state ---
    $isPinned = (int)($c['is_pinned'] ?? 0);

    // --- HTML OUTPUT ---
    $repliesHtml .= '
    <div class="cw_l-line" id="cm-'.$c['id'].'" data-parent-id="'.$c['parent_id'].'">
        <a href="/community/user/'.$c['user_id'].'" class="user-avatar">
            <img class="user-avatar-img" src="'.$avatar.'" alt="'.$username.'">
        </a>
        <div class="info">
            <div class="ihead">
                <a href="/community/user/'.$c['user_id'].'" class="user-name is-level-x '.$badgeClass.'">
                    '.($badgeClass ? '<i class="badg-level '.$badgeClass.'"></i>' : '').'
                    '.$username.'
                    '.($rankName ? '<span>'.$rankName.'</span>' : '').'
                </a>
                <div class="time">'.$timeAgo.'</div>
            </div>
            <div class="ibody"><p>'.$mentionHtml.' '.$content.'</p></div>
            <div class="ibottom">
            	<div class="ib-li ib-reply" data-id="'.$c['id'].'">
            		<a class="btn"><i class="fas fa-reply mr-1"></i>Reply</a>
        		</div>
                <div class="ib-li ib-like">
                    <a class="btn cm-btn-vote" data-id="'.$c['id'].'" data-type="1">
                        <i class="far fa-thumbs-up mr-1"></i>
                        <span class="value">'.($up ?: "").'</span>
                    </a>
                </div>
                <div class="ib-li ib-dislike">
                    <a class="btn cm-btn-vote" data-id="'.$c['id'].'" data-type="0">
                        <i class="far fa-thumbs-down mr-1"></i>
                        <span class="value">'.($down ?: "").'</span>
                    </a>
                </div>
                <div class="ib-li">
                    <a class="btn more-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-h mr-1"></i>More
                    </a>
                    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" data-user-id="'.$c['user_id'].'">';
                        // Own reply: Hide only
                        if ($loggedIn && $myId == $c['user_id']) {
                            $repliesHtml .= '
                            <a class="dropdown-item cm-action" href="javascript:;" data-action="hide" data-id="'.$c['id'].'">Hide</a>';
                        }
                        // Report for normal users only (not admin/mod)
                        else if ((int)$authorRoles === 0) {
                            $repliesHtml .= '
                            <a class="dropdown-item cm-report" href="javascript:;" data-type="1" data-id="'.$c['id'].'">Report Spam</a>';
                        }
                        // Admin controls
                        if ($me_is_admin) {
                            $repliesHtml .= '<div class="dropdown-divider"></div>';
                            if ($isPinned) {
                                $repliesHtml .= '<a class="dropdown-item cm-action" href="javascript:;" data-action="unpin" data-id="'.$c['id'].'">Unpin comment</a>';
                            } else {
                                $repliesHtml .= '<a class="dropdown-item cm-action" href="javascript:;" data-action="pin" data-id="'.$c['id'].'">Pin comment</a>';
                            }
                            $repliesHtml .= '
                            <a class="dropdown-item cm-action" href="javascript:;" data-action="delete" data-id="'.$c['id'].'">Delete comment</a>
                            <a class="dropdown-item ac-mute" href="javascript:;" data-hours="24">Mute 24h</a>
                            <a class="dropdown-item ac-block" href="javascript:;" data-user-id="'.$c['user_id'].'">Block user</a>
                            ';
                        }
                    $repliesHtml .= '
                        <a class="dropdown-item post-cm-cp-link" href="javascript:;" data-id="'.$c['id'].'">Copy Link</a>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>';
            if ($loggedIn) {
			    $repliesHtml .= '
			        <div id="reply-' . (int)$c['id'] . '" class="comment-input is-reply reply-block" style="display: none;">
			            <div class="user-avatar">
			                <img class="user-avatar-img" src="'.$meAvatar.'" alt="'.$meName.'">
                        </div>
                        <div class="ci-form">
                            <form class="preform preform-dark post-comment-form">
                                <div class="loading-absolute bg-white" style="display:none;">
                                    <div class="loading"><div class="span1"></div><div class="span2"></div><div class="span3"></div></div>
                                </div>
                                <input type="hidden" name="mention_id" value="'.(int)$c['user_id'].'">
                                <input type="hidden" name="parent_id" value="'.(int)$c['id'].'">
                                <textarea class="form-control form-control-textarea comment-subject emo-on cm-input-'.(int)$c['id'].'" name="content" maxlength="3000" placeholder="Add a reply" required></textarea>
                                <div class="ci-emo">
                                    <div data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="cb-icon"><i class="fas fa-laugh"></i></div>
                                    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal dr-bottom-right dropdown-menu-emo" style="max-height:400px !important;">
                                        <emoji-picker data-input=".cm-input-'.(int)$c['id'].'"></emoji-picker>
                                    </div>
                                </div>
                                <div class="ci-buttons">
                                    <div class="alert alert-danger cm-error mt-1" style="display:none;"></div>
                                    <div class="ci-b-right">
                                        <div class="cb-li"><a class="btn btn-sm btn-secondary btn-close-reply" data-id="'.(int)$c['id'].'">Close</a></div>
                                        <div class="cb-li"><button class="btn btn-sm btn-primary ml-2">Reply</button></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                ';
            }
        $repliesHtml .= '</div>
    </div>';
}

// ----------- JSON RESPONSE -----------
echo json_encode([
    'status' => true,
    'html'   => $repliesHtml
]);

// ----------- TIME AGO FUNCTION -----------
function timeAgo($datetime){
    $t = time() - strtotime($datetime);
    if ($t < 60) return "$t seconds ago";
    if ($t < 3600) return floor($t/60)." minutes ago";
    if ($t < 86400) return floor($t/3600)." hours ago";
    return floor($t/86400)." days ago";
}
