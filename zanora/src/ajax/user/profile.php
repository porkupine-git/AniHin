<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once($_SERVER['DOCUMENT_ROOT'].'/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_csrf_check.php';
header('Content-Type: application/json');
$response = [
    'status' => false,
    'msg' => '',
    'errors' => []
];
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $user_id = $_SESSION['userID'] ?? null;
    if (!$user_id) {
        $response['msg'] = 'Not logged in';
        $response['errors'][] = 'User session missing';
        echo json_encode($response); exit();
    }
    // Fetch current user info
    $stmtUser = $conn->prepare("SELECT username, email, image, is_verified FROM users WHERE id = ?");
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $stmtUser->bind_result($curUsername, $curEmail, $curImage, $is_verified);
    $stmtUser->fetch();
    $stmtUser->close();
    // ==== Avatar mapping block: add your URLs for each ID ====
    $avatar_list = [
    1 => "/avatar/100x100/dragon_ball/av-db-01.jpeg",
    2 => "/avatar/100x100/dragon_ball/av-db-02.jpeg",
    3 => "/avatar/100x100/dragon_ball/av-db-03.jpeg",
    4 => "/avatar/100x100/dragon_ball/av-db-04.jpeg",
    5 => "/avatar/100x100/dragon_ball/av-db-05.jpeg",
    6 => "/avatar/100x100/dragon_ball/av-db-06.jpeg",
    7 => "/avatar/100x100/one_piece/user-00.jpeg",
    8 => "/avatar/100x100/one_piece/user-01.jpeg",
    9 => "/avatar/100x100/one_piece/user-02.jpeg",
    10 => "/avatar/100x100/one_piece/user-03.jpeg",
    11 => "/avatar/100x100/one_piece/user-04.jpeg",
    12 => "/avatar/100x100/one_piece/user-05.jpeg",
    13 => "/avatar/100x100/one_piece/user-06.jpeg",
    14 => "/avatar/100x100/one_piece/user-07.jpeg",
    15 => "/avatar/100x100/one_piece/user-08.jpeg",
    16 => "/avatar/100x100/one_piece/user-09.jpeg",
    17 => "/avatar/100x100/one_piece/user-10.jpeg",
    18 => "/avatar/100x100/one_piece/user-11.jpeg",
    19 => "/avatar/100x100/one_piece/03.jpg",
    20 => "/avatar/100x100/one_piece/04.jpg",
    21 => "/avatar/100x100/one_piece/05.jpg",
    22 => "/avatar/100x100/one_piece/18.jpg",
    23 => "/avatar/100x100/one_piece/File1.png",
    24 => "/avatar/100x100/one_piece/File2.png",
    25 => "/avatar/100x100/one_piece/File3.png",
    26 => "/avatar/100x100/one_piece/File4.png",
    27 => "/avatar/100x100/one_piece/File5.png",
    28 => "/avatar/100x100/one_piece/File6.png",
    29 => "/avatar/100x100/one_piece/File7.png",
    30 => "/avatar/100x100/one_piece/File8.png",
    31 => "/avatar/100x100/zoro_chibi/avatar-02.png",
    32 => "/avatar/100x100/zoro_chibi/avatar-03.png",
    33 => "/avatar/100x100/zoro_chibi/avatar-04.png",
    34 => "/avatar/100x100/zoro_chibi/avatar-05.png",
    35 => "/avatar/100x100/zoro_chibi/avatar-06.png",
    36 => "/avatar/100x100/zoro_chibi/avatar-08.png",
    37 => "/avatar/100x100/zoro_chibi/avatar-09.png",
    38 => "/avatar/100x100/zoro_chibi/avatar-10.png",
    39 => "/avatar/100x100/zoro_chibi/avatar-11.png",
    40 => "/avatar/100x100/zoro_chibi/avatar-12.png",
    41 => "/avatar/100x100/zoro_chibi/avatar2-01.png",
    42 => "/avatar/100x100/zoro_chibi/avatar2-02.png",
    43 => "/avatar/100x100/zoro_chibi/avatar2-03.png",
    44 => "/avatar/100x100/zoro_chibi/avatar2-04.png",
    45 => "/avatar/100x100/zoro_chibi/avatar2-05.png",
    46 => "/avatar/100x100/zoro_chibi/avatar2-06.png",
    47 => "/avatar/100x100/zoro_chibi/avatar2-07.png",
    48 => "/avatar/100x100/zoro_chibi/avatar2-08.png",
    49 => "/avatar/100x100/zoro_chibi/avatar2-09.png",
    50 => "/avatar/100x100/zoro_chibi/avatar2-10.png",
    51 => "/avatar/100x100/zoro_normal/av-zz-01.jpeg",
    52 => "/avatar/100x100/zoro_normal/av-zz-02.jpeg",
    53 => "/avatar/100x100/zoro_normal/av-zz-03.jpeg",
    54 => "/avatar/100x100/zoro_normal/av-zz-04.jpeg",
    55 => "/avatar/100x100/zoro_normal/av-zz-05.jpeg",
    56 => "/avatar/100x100/zoro_normal/av-zz-06.jpeg",
    57 => "/avatar/100x100/zoro_normal/av-zz-07.jpeg",
    58 => "/avatar/100x100/zoro_normal/av-zz-08.jpeg",
    59 => "/avatar/100x100/zoro_normal/av-zz-09.jpeg",
    60 => "/avatar/100x100/zoro_normal/av-zz-10.jpeg",
    61 => "/avatar/100x100/zoro_normal/av-zz-11.jpeg",
    62 => "/avatar/100x100/zoro_normal/av-zz-12.jpeg",
    63 => "/avatar/100x100/mha/avatar-17.png",
    64 => "/avatar/100x100/mha/avatar-18.png",
    65 => "/avatar/100x100/mha/avatar-19.png",
    66 => "/avatar/100x100/mha/avatar-20.png",
    67 => "/avatar/100x100/mha/avatar-21.png",
    68 => "/avatar/100x100/mha/avatar-22.png",
    69 => "/avatar/100x100/mha/avatar-23.png",
    70 => "/avatar/100x100/mha/avatar-24.png",
    71 => "/avatar/100x100/mha/avatar-25.png",
    72 => "/avatar/100x100/dragon_ball_chibi/bulma.png",
    73 => "/avatar/100x100/dragon_ball_chibi/buumap.png",
    74 => "/avatar/100x100/dragon_ball_chibi/cell.png",
    75 => "/avatar/100x100/dragon_ball_chibi/freezer.png",
    76 => "/avatar/100x100/dragon_ball_chibi/gogeta.png",
    77 => "/avatar/100x100/dragon_ball_chibi/gohan.png",
    78 => "/avatar/100x100/dragon_ball_chibi/goku.png",
    79 => "/avatar/100x100/dragon_ball_chibi/goku-saiyan.png",
    80 => "/avatar/100x100/dragon_ball_chibi/gotenk.png",
    81 => "/avatar/100x100/dragon_ball_chibi/kaio.png",
    82 => "/avatar/100x100/dragon_ball_chibi/krillin.png",
    83 => "/avatar/100x100/dragon_ball_chibi/mabu.png",
    84 => "/avatar/100x100/dragon_ball_chibi/mrsatan.png",
    85 => "/avatar/100x100/dragon_ball_chibi/MutenRoshi.png",
    86 => "/avatar/100x100/dragon_ball_chibi/picolo.png",
    87 => "/avatar/100x100/dragon_ball_chibi/tenshihan.png",
    88 => "/avatar/100x100/dragon_ball_chibi/trunks.png",
    89 => "/avatar/100x100/dragon_ball_chibi/vegeta.png",
    90 => "/avatar/100x100/dragon_ball_chibi/videl.png",
    91 => "/avatar/100x100/dragon_ball_chibi/beerus.png",
    92 => "/avatar/100x100/attack_on_titan/20.jpg",
    93 => "/avatar/100x100/attack_on_titan/aot_01.png",
    94 => "/avatar/100x100/attack_on_titan/aot_02.png",
    95 => "/avatar/100x100/attack_on_titan/aot_03.png",
    96 => "/avatar/100x100/attack_on_titan/aot_04.png",
    97 => "/avatar/100x100/attack_on_titan/aot_05.png",
    98 => "/avatar/100x100/attack_on_titan/aot_06.png",
    99 => "/avatar/100x100/attack_on_titan/aot_07.png",
    100 => "/avatar/100x100/attack_on_titan/aot_08.png",
    101 => "/avatar/100x100/attack_on_titan/aot_09.png",
    102 => "/avatar/100x100/attack_on_titan/aot_10.png",
    103 => "/avatar/100x100/conan/06.jpg",
    104 => "/avatar/100x100/conan/07.jpg",
    105 => "/avatar/100x100/conan/8.png",
    106 => "/avatar/100x100/conan/9.png",
    107 => "/avatar/100x100/conan/10.png",
    108 => "/avatar/100x100/conan/11.png",
    109 => "/avatar/100x100/conan/12.png",
    110 => "/avatar/100x100/conan/13.png",
    111 => "/avatar/100x100/conan/14.png",
    112 => "/avatar/100x100/naruto/08.jpg",
    113 => "/avatar/100x100/naruto/09.jpg",
    114 => "/avatar/100x100/naruto/11.jpg",
    115 => "/avatar/100x100/naruto/naruto_01.png",
    116 => "/avatar/100x100/naruto/naruto_02.png",
    117 => "/avatar/100x100/naruto/naruto_03.png",
    118 => "/avatar/100x100/naruto/naruto_04.png",
    119 => "/avatar/100x100/naruto/naruto_05.png",
    120 => "/avatar/100x100/naruto/naruto_06.png",
    121 => "/avatar/100x100/naruto/naruto_07.png",
    122 => "/avatar/100x100/naruto/naruto_08.png",
    123 => "/avatar/100x100/naruto/naruto_09.png",
    124 => "/avatar/100x100/naruto/naruto_010.png",
    125 => "/avatar/100x100/one_punch_man/14.jpg",
    126 => "/avatar/100x100/one_punch_man/opm_01.png",
    127 => "/avatar/100x100/one_punch_man/opm_02.png",
    128 => "/avatar/100x100/one_punch_man/opm_03.png",
    129 => "/avatar/100x100/one_punch_man/opm_04.png",
    130 => "/avatar/100x100/one_punch_man/opm_05.png",
    131 => "/avatar/100x100/one_punch_man/opm_06.png",
    132 => "/avatar/100x100/one_punch_man/opm_07.png",
    133 => "/avatar/100x100/one_punch_man/opm_08.png",
    134 => "/avatar/100x100/one_punch_man/opm_09.png",
    135 => "/avatar/100x100/one_punch_man/opm_010.png",
    136 => "/avatar/100x100/sakura/10.jpg",
    137 => "/avatar/100x100/tha/01.jpg",
    138 => "/avatar/100x100/tha/02.jpg",
    139 => "/avatar/100x100/tha/12.jpg",
    140 => "/avatar/100x100/tha/13.jpg",
    141 => "/avatar/100x100/tha/15.jpg",
    142 => "/avatar/100x100/tha/16.jpg",
    143 => "/avatar/100x100/tha/17.jpg",
    144 => "/avatar/100x100/tha/19.jpg",
    145 => "/avatar/100x100/demon_splayer/File1.jpg",
    146 => "/avatar/100x100/demon_splayer/File2.jpg",
    147 => "/avatar/100x100/demon_splayer/File3.jpg",
    148 => "/avatar/100x100/demon_splayer/File4.jpg",
    149 => "/avatar/100x100/demon_splayer/File5.jpg",
    150 => "/avatar/100x100/demon_splayer/File6.jpg",
    151 => "/avatar/100x100/demon_splayer/File7.jpg",
    152 => "/avatar/100x100/demon_splayer/File8.jpg",
    153 => "/avatar/100x100/demon_splayer/File9.jpg",
    154 => "/avatar/100x100/demon_splayer/File10.jpg",
    155 => "/avatar/100x100/demon_splayer/File11.jpg",
    156 => "/avatar/100x100/demon_splayer/File12.jpg",
    157 => "/avatar/100x100/demon_splayer/File13.jpg",
    158 => "/avatar/100x100/demon_splayer/File14.jpg",
    159 => "/avatar/100x100/demon_splayer/File15.jpg",
    160 => "/avatar/100x100/demon_splayer/File16.jpg",
    161 => "/avatar/100x100/demon_splayer/File17.jpg",
    162 => "/avatar/100x100/demon_splayer/File18.jpg",
    163 => "/avatar/100x100/demon_splayer/File19.jpg",
    164 => "/avatar/100x100/demon_splayer/File20.jpg",
    165 => "/avatar/100x100/jujutsu_kaisen/File1.png",
    166 => "/avatar/100x100/jujutsu_kaisen/File2.png",
    167 => "/avatar/100x100/jujutsu_kaisen/File3.png",
    168 => "/avatar/100x100/jujutsu_kaisen/File4.png",
    169 => "/avatar/100x100/jujutsu_kaisen/File5.png",
    170 => "/avatar/100x100/jujutsu_kaisen/File6.png",
    171 => "/avatar/100x100/jujutsu_kaisen/File7.png",
    172 => "/avatar/100x100/jujutsu_kaisen/File8.png",
    173 => "/avatar/100x100/jujutsu_kaisen/File9.png",
    174 => "/avatar/100x100/jujutsu_kaisen/File10.png",
    175 => "/avatar/100x100/jujutsu_kaisen/File11.png",
    176 => "/avatar/100x100/jujutsu_kaisen/File12.png",
    177 => "/avatar/100x100/jujutsu_kaisen/File13.png",
    178 => "/avatar/100x100/jujutsu_kaisen/File14.png",
    179 => "/avatar/100x100/chainsaw/01.png",
    180 => "/avatar/100x100/chainsaw/02.png",
    181 => "/avatar/100x100/chainsaw/03.png",
    182 => "/avatar/100x100/chainsaw/04.png",
    183 => "/avatar/100x100/chainsaw/05.png",
    184 => "/avatar/100x100/chainsaw/06.png",
    185 => "/avatar/100x100/chainsaw/07.png",
    186 => "/avatar/100x100/chainsaw/08.png",
    187 => "/avatar/100x100/chainsaw/09.png",
    188 => "/avatar/100x100/spy_family/01.png",
    189 => "/avatar/100x100/spy_family/02.png",
    190 => "/avatar/100x100/spy_family/03.png",
    191 => "/avatar/100x100/spy_family/04.png",
    192 => "/avatar/100x100/spy_family/05.png",
    193 => "/avatar/100x100/spy_family/06.png",
    194 => "/avatar/100x100/spy_family/07.png",
    195 => "/avatar/100x100/spy_family/08.png",
    196 => "/avatar/100x100/berserk/berserk_01.png",
    197 => "/avatar/100x100/berserk/berserk_02.png",
    198 => "/avatar/100x100/berserk/berserk_03.png",
    199 => "/avatar/100x100/berserk/berserk_04.png",
    200 => "/avatar/100x100/berserk/berserk_05.png",
    201 => "/avatar/100x100/berserk/berserk_06.png",
    202 => "/avatar/100x100/berserk/berserk_07.png",
    203 => "/avatar/100x100/berserk/berserk_08.png",
    204 => "/avatar/100x100/berserk/berserk_09.png",
    205 => "/avatar/100x100/berserk/berserk_010.png",
    206 => "/avatar/100x100/bleach/bleanch_01.png",
    207 => "/avatar/100x100/bleach/bleanch_02.png",
    208 => "/avatar/100x100/bleach/bleanch_03.png",
    209 => "/avatar/100x100/bleach/bleanch_04.png",
    210 => "/avatar/100x100/bleach/bleanch_05.png",
    211 => "/avatar/100x100/bleach/bleanch_06.png",
    212 => "/avatar/100x100/bleach/bleanch_07.png",
    213 => "/avatar/100x100/bleach/bleanch_08.png",
    214 => "/avatar/100x100/bleach/bleanch_09.png",
    215 => "/avatar/100x100/bleach/bleanch_010.png",
    216 => "/avatar/100x100/deadnote/dead_note_01.png",
    217 => "/avatar/100x100/deadnote/dead_note_02.png",
    218 => "/avatar/100x100/deadnote/dead_note_03.png",
    219 => "/avatar/100x100/deadnote/dead_note_04.png",
    220 => "/avatar/100x100/deadnote/dead_note_05.png",
    221 => "/avatar/100x100/deadnote/dead_note_06.png",
    222 => "/avatar/100x100/deadnote/dead_note_07.png",
    223 => "/avatar/100x100/deadnote/dead_note_08.png",
    224 => "/avatar/100x100/deadnote/dead_note_09.png",
    225 => "/avatar/100x100/deadnote/dead_note_010.png",
    226 => "/avatar/100x100/haikyu/haikyu_01.png",
    227 => "/avatar/100x100/haikyu/haikyu_02.png",
    228 => "/avatar/100x100/haikyu/haikyu_03.png",
    229 => "/avatar/100x100/haikyu/haikyu_04.png",
    230 => "/avatar/100x100/haikyu/haikyu_05.png",
    231 => "/avatar/100x100/haikyu/haikyu_06.png",
    232 => "/avatar/100x100/haikyu/haikyu_07.png",
    233 => "/avatar/100x100/haikyu/haikyu_08.png",
    234 => "/avatar/100x100/haikyu/haikyu_09.png",
    235 => "/avatar/100x100/haikyu/haikyu_010.png",
    236 => "/avatar/100x100/haikyu/haikyu_011.png"
];
    // Form values (match your input names!)
    $username  = !empty($_POST['username']) ? trim(mysqli_real_escape_string($conn, $_POST['username'])) : $curUsername;
    $avatar_id = isset($_POST['avatar_id']) ? intval($_POST['avatar_id']) : null;
    if ($avatar_id && isset($avatar_list[$avatar_id])) {
        $avatar = $avatar_list[$avatar_id];
    } else {
        $avatar = $curImage; // fallback if no new avatar_id sent
    }
    $email    = !empty($_POST['email']) ? trim(mysqli_real_escape_string($conn, $_POST['email'])) : $curEmail;
    // Validate required fields
    if (empty($username)) $response['errors'][] = 'Username is required';
    if (empty($email))    $response['errors'][] = 'Email is required';
    // Email existence check
    if (!$is_verified && !empty($_POST['email'])) {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            $response['errors'][] = 'This email is already registered with another account.';
        }
        $check_stmt->close();
    }
    // Only update if no errors so far
    if (empty($response['errors'])) {
        if (!$is_verified && !empty($_POST['email'])) {
            $update_stmt = $conn->prepare("UPDATE users SET username = ?, image = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("sssi", $username, $avatar, $email, $user_id);
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET username = ?, image = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $username, $avatar, $user_id);
        }
        if ($update_stmt->execute()) {
            $_SESSION['avatar'] = $avatar; // Update session image
            $response['status'] = true;
            $response['msg'] = 'Profile updated successfully!';
            $response['email'] = $email;
        } else {
            $response['errors'][] = 'Failed to update profile';
            $response['msg'] = 'Error updating profile';
        }
        $update_stmt->close();
    } else {
        $response['msg'] = 'Profile update failed';
    }
    // ---- Password section (only if at least one field filled) ----
    $current_password     = $_POST['current_password'] ?? '';
    $new_password         = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';
    if ($current_password || $new_password || $confirm_new_password) {
        if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
            $response['errors'][] = 'Please fill all password fields.';
        } elseif ($new_password !== $confirm_new_password) {
            $response['errors'][] = 'New passwords do not match.';
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            $stmt->close();
            if (!password_verify($current_password, $hashed_password)) {
                $response['errors'][] = 'Current password is incorrect.';
            } else {
                // Update password
                $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $pass_stmt->bind_param("si", $new_hashed, $user_id);
                if ($pass_stmt->execute()) {
                    $response['msg'] .= ($response['msg'] ? "\n" : "") . "Password changed.";
                } else {
                    $response['errors'][] = 'Failed to change password';
                }
                $pass_stmt->close();
            }
        }
    }
    echo json_encode($response); exit();
} else {
    $response['msg'] = 'Invalid request';
    $response['errors'][] = 'Wrong method or headers';
    echo json_encode($response); exit();
}
?>