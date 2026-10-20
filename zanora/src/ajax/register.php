<?php
if (
    empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_origin_check.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/_functions.php');
header("Content-Type: application/json; charset=utf-8");

// Enable error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$cf_token = $_POST['cf-turnstile-response'] ?? '';
if (!verify_turnstile($cf_token)) {
    echo json_encode(['status'=>false,'msg'=>'Turnstile verification failed']);
    exit;
}

$avatarFiles = array(
    "/avatar/100x100/dragon_ball/av-db-01.jpeg",
    "/avatar/100x100/dragon_ball/av-db-02.jpeg",
    "/avatar/100x100/dragon_ball/av-db-03.jpeg",
    "/avatar/100x100/dragon_ball/av-db-04.jpeg",
    "/avatar/100x100/dragon_ball/av-db-05.jpeg",
    "/avatar/100x100/dragon_ball/av-db-06.jpeg",
    "/avatar/100x100/one_piece/user-00.jpeg",
    "/avatar/100x100/one_piece/user-01.jpeg",
    "/avatar/100x100/one_piece/user-02.jpeg",
    "/avatar/100x100/one_piece/user-03.jpeg",
    "/avatar/100x100/one_piece/user-04.jpeg",
    "/avatar/100x100/one_piece/user-05.jpeg",
    "/avatar/100x100/one_piece/user-06.jpeg",
    "/avatar/100x100/one_piece/user-07.jpeg",
    "/avatar/100x100/one_piece/user-08.jpeg",
    "/avatar/100x100/one_piece/user-09.jpeg",
    "/avatar/100x100/one_piece/user-10.jpeg",
    "/avatar/100x100/one_piece/user-11.jpeg",
    "/avatar/100x100/one_piece/03.jpg",
    "/avatar/100x100/one_piece/04.jpg",
    "/avatar/100x100/one_piece/05.jpg",
    "/avatar/100x100/one_piece/18.jpg",
    "/avatar/100x100/one_piece/File1.png",
    "/avatar/100x100/one_piece/File2.png",
    "/avatar/100x100/one_piece/File3.png",
    "/avatar/100x100/one_piece/File4.png",
    "/avatar/100x100/one_piece/File5.png",
    "/avatar/100x100/one_piece/File6.png",
    "/avatar/100x100/one_piece/File7.png",
    "/avatar/100x100/one_piece/File8.png",
    "/avatar/100x100/zoro_chibi/avatar-02.png",
    "/avatar/100x100/zoro_chibi/avatar-03.png",
    "/avatar/100x100/zoro_chibi/avatar-04.png",
    "/avatar/100x100/zoro_chibi/avatar-05.png",
    "/avatar/100x100/zoro_chibi/avatar-06.png",
    "/avatar/100x100/zoro_chibi/avatar-08.png",
    "/avatar/100x100/zoro_chibi/avatar-09.png",
    "/avatar/100x100/zoro_chibi/avatar-10.png",
    "/avatar/100x100/zoro_chibi/avatar-11.png",
    "/avatar/100x100/zoro_chibi/avatar-12.png",
    "/avatar/100x100/zoro_chibi/avatar2-01.png",
    "/avatar/100x100/zoro_chibi/avatar2-02.png",
    "/avatar/100x100/zoro_chibi/avatar2-03.png",
    "/avatar/100x100/zoro_chibi/avatar2-04.png",
    "/avatar/100x100/zoro_chibi/avatar2-05.png",
    "/avatar/100x100/zoro_chibi/avatar2-06.png",
    "/avatar/100x100/zoro_chibi/avatar2-07.png",
    "/avatar/100x100/zoro_chibi/avatar2-08.png",
    "/avatar/100x100/zoro_chibi/avatar2-09.png",
    "/avatar/100x100/zoro_chibi/avatar2-10.png",
    "/avatar/100x100/zoro_normal/av-zz-01.jpeg",
    "/avatar/100x100/zoro_normal/av-zz-02.jpeg",
    "/avatar/100x100/zoro_normal/av-zz-03.jpeg",
    "/avatar/100x100/zoro_normal/av-zz-04.jpeg",
    "/avatar/100x100/zoro_normal/av-zz-05.jpeg",
    "/avatar/100x100/zoro_normal/av-zz-06.jpeg",
    "/avatar/100x100/zoro_normal/av-zz-07.jpeg",
    "/avatar/100x100/zoro_normal/av-zz-08.jpeg",
    "/avatar/100x100/zoro_normal/av-zz-09.jpeg",
    "/avatar/100x100/zoro_normal/av-zz-10.jpeg",
    "/avatar/100x100/zoro_normal/av-zz-11.jpeg",
    "/avatar/100x100/zoro_normal/av-zz-12.jpeg",
    "/avatar/100x100/mha/avatar-17.png",
    "/avatar/100x100/mha/avatar-18.png",
    "/avatar/100x100/mha/avatar-19.png",
    "/avatar/100x100/mha/avatar-20.png",
    "/avatar/100x100/mha/avatar-21.png",
    "/avatar/100x100/mha/avatar-22.png",
    "/avatar/100x100/mha/avatar-23.png",
    "/avatar/100x100/mha/avatar-24.png",
    "/avatar/100x100/mha/avatar-25.png",
    "/avatar/100x100/dragon_ball_chibi/bulma.png",
    "/avatar/100x100/dragon_ball_chibi/buumap.png",
    "/avatar/100x100/dragon_ball_chibi/cell.png",
    "/avatar/100x100/dragon_ball_chibi/freezer.png",
    "/avatar/100x100/dragon_ball_chibi/gogeta.png",
    "/avatar/100x100/dragon_ball_chibi/gohan.png",
    "/avatar/100x100/dragon_ball_chibi/goku.png",
    "/avatar/100x100/dragon_ball_chibi/goku-saiyan.png",
    "/avatar/100x100/dragon_ball_chibi/gotenk.png",
    "/avatar/100x100/dragon_ball_chibi/kaio.png",
    "/avatar/100x100/dragon_ball_chibi/krillin.png",
    "/avatar/100x100/dragon_ball_chibi/mabu.png",
    "/avatar/100x100/dragon_ball_chibi/mrsatan.png",
    "/avatar/100x100/dragon_ball_chibi/MutenRoshi.png",
    "/avatar/100x100/dragon_ball_chibi/picolo.png",
    "/avatar/100x100/dragon_ball_chibi/tenshihan.png",
    "/avatar/100x100/dragon_ball_chibi/trunks.png",
    "/avatar/100x100/dragon_ball_chibi/vegeta.png",
    "/avatar/100x100/dragon_ball_chibi/videl.png",
    "/avatar/100x100/dragon_ball_chibi/beerus.png",
    "/avatar/100x100/attack_on_titan/20.jpg",
    "/avatar/100x100/attack_on_titan/aot_01.png",
    "/avatar/100x100/attack_on_titan/aot_02.png",
    "/avatar/100x100/attack_on_titan/aot_03.png",
    "/avatar/100x100/attack_on_titan/aot_04.png",
    "/avatar/100x100/attack_on_titan/aot_05.png",
    "/avatar/100x100/attack_on_titan/aot_06.png",
    "/avatar/100x100/attack_on_titan/aot_07.png",
    "/avatar/100x100/attack_on_titan/aot_08.png",
    "/avatar/100x100/attack_on_titan/aot_09.png",
    "/avatar/100x100/attack_on_titan/aot_10.png",
    "/avatar/100x100/conan/06.jpg",
    "/avatar/100x100/conan/07.jpg",
    "/avatar/100x100/conan/8.png",
    "/avatar/100x100/conan/9.png",
    "/avatar/100x100/conan/10.png",
    "/avatar/100x100/conan/11.png",
    "/avatar/100x100/conan/12.png",
    "/avatar/100x100/conan/13.png",
    "/avatar/100x100/conan/14.png",
    "/avatar/100x100/naruto/08.jpg",
    "/avatar/100x100/naruto/09.jpg",
    "/avatar/100x100/naruto/11.jpg",
    "/avatar/100x100/naruto/naruto_01.png",
    "/avatar/100x100/naruto/naruto_02.png",
    "/avatar/100x100/naruto/naruto_03.png",
    "/avatar/100x100/naruto/naruto_04.png",
    "/avatar/100x100/naruto/naruto_05.png",
    "/avatar/100x100/naruto/naruto_06.png",
    "/avatar/100x100/naruto/naruto_07.png",
    "/avatar/100x100/naruto/naruto_08.png",
    "/avatar/100x100/naruto/naruto_09.png",
    "/avatar/100x100/naruto/naruto_010.png",
    "/avatar/100x100/one_punch_man/14.jpg",
    "/avatar/100x100/one_punch_man/opm_01.png",
    "/avatar/100x100/one_punch_man/opm_02.png",
    "/avatar/100x100/one_punch_man/opm_03.png",
    "/avatar/100x100/one_punch_man/opm_04.png",
    "/avatar/100x100/one_punch_man/opm_05.png",
    "/avatar/100x100/one_punch_man/opm_06.png",
    "/avatar/100x100/one_punch_man/opm_07.png",
    "/avatar/100x100/one_punch_man/opm_08.png",
    "/avatar/100x100/one_punch_man/opm_09.png",
    "/avatar/100x100/one_punch_man/opm_010.png",
    "/avatar/100x100/sakura/10.jpg",
    "/avatar/100x100/tha/01.jpg",
    "/avatar/100x100/tha/02.jpg",
    "/avatar/100x100/tha/12.jpg",
    "/avatar/100x100/tha/13.jpg",
    "/avatar/100x100/tha/15.jpg",
    "/avatar/100x100/tha/16.jpg",
    "/avatar/100x100/tha/17.jpg",
    "/avatar/100x100/tha/19.jpg",
    "/avatar/100x100/demon_splayer/File1.jpg",
    "/avatar/100x100/demon_splayer/File2.jpg",
    "/avatar/100x100/demon_splayer/File3.jpg",
    "/avatar/100x100/demon_splayer/File4.jpg",
    "/avatar/100x100/demon_splayer/File5.jpg",
    "/avatar/100x100/demon_splayer/File6.jpg",
    "/avatar/100x100/demon_splayer/File7.jpg",
    "/avatar/100x100/demon_splayer/File8.jpg",
    "/avatar/100x100/demon_splayer/File9.jpg",
    "/avatar/100x100/demon_splayer/File10.jpg",
    "/avatar/100x100/demon_splayer/File11.jpg",
    "/avatar/100x100/demon_splayer/File12.jpg",
    "/avatar/100x100/demon_splayer/File13.jpg",
    "/avatar/100x100/demon_splayer/File14.jpg",
    "/avatar/100x100/demon_splayer/File15.jpg",
    "/avatar/100x100/demon_splayer/File16.jpg",
    "/avatar/100x100/demon_splayer/File17.jpg",
    "/avatar/100x100/demon_splayer/File18.jpg",
    "/avatar/100x100/demon_splayer/File19.jpg",
    "/avatar/100x100/demon_splayer/File20.jpg",
    "/avatar/100x100/jujutsu_kaisen/File1.png",
    "/avatar/100x100/jujutsu_kaisen/File2.png",
    "/avatar/100x100/jujutsu_kaisen/File3.png",
    "/avatar/100x100/jujutsu_kaisen/File4.png",
    "/avatar/100x100/jujutsu_kaisen/File5.png",
    "/avatar/100x100/jujutsu_kaisen/File6.png",
    "/avatar/100x100/jujutsu_kaisen/File7.png",
    "/avatar/100x100/jujutsu_kaisen/File8.png",
    "/avatar/100x100/jujutsu_kaisen/File9.png",
    "/avatar/100x100/jujutsu_kaisen/File10.png",
    "/avatar/100x100/jujutsu_kaisen/File11.png",
    "/avatar/100x100/jujutsu_kaisen/File12.png",
    "/avatar/100x100/jujutsu_kaisen/File13.png",
    "/avatar/100x100/jujutsu_kaisen/File14.png",
    "/avatar/100x100/chainsaw/01.png",
    "/avatar/100x100/chainsaw/02.png",
    "/avatar/100x100/chainsaw/03.png",
    "/avatar/100x100/chainsaw/04.png",
    "/avatar/100x100/chainsaw/05.png",
    "/avatar/100x100/chainsaw/06.png",
    "/avatar/100x100/chainsaw/07.png",
    "/avatar/100x100/chainsaw/08.png",
    "/avatar/100x100/chainsaw/09.png",
    "/avatar/100x100/spy_family/01.png",
    "/avatar/100x100/spy_family/02.png",
    "/avatar/100x100/spy_family/03.png",
    "/avatar/100x100/spy_family/04.png",
    "/avatar/100x100/spy_family/05.png",
    "/avatar/100x100/spy_family/06.png",
    "/avatar/100x100/spy_family/07.png",
    "/avatar/100x100/spy_family/08.png",
    "/avatar/100x100/berserk/berserk_01.png",
    "/avatar/100x100/berserk/berserk_02.png",
    "/avatar/100x100/berserk/berserk_03.png",
    "/avatar/100x100/berserk/berserk_04.png",
    "/avatar/100x100/berserk/berserk_05.png",
    "/avatar/100x100/berserk/berserk_06.png",
    "/avatar/100x100/berserk/berserk_07.png",
    "/avatar/100x100/berserk/berserk_08.png",
    "/avatar/100x100/berserk/berserk_09.png",
    "/avatar/100x100/berserk/berserk_010.png",
    "/avatar/100x100/bleach/bleanch_01.png",
    "/avatar/100x100/bleach/bleanch_02.png",
    "/avatar/100x100/bleach/bleanch_03.png",
    "/avatar/100x100/bleach/bleanch_04.png",
    "/avatar/100x100/bleach/bleanch_05.png",
    "/avatar/100x100/bleach/bleanch_06.png",
    "/avatar/100x100/bleach/bleanch_07.png",
    "/avatar/100x100/bleach/bleanch_08.png",
    "/avatar/100x100/bleach/bleanch_09.png",
    "/avatar/100x100/bleach/bleanch_010.png",
    "/avatar/100x100/deadnote/dead_note_01.png",
    "/avatar/100x100/deadnote/dead_note_02.png",
    "/avatar/100x100/deadnote/dead_note_03.png",
    "/avatar/100x100/deadnote/dead_note_04.png",
    "/avatar/100x100/deadnote/dead_note_05.png",
    "/avatar/100x100/deadnote/dead_note_06.png",
    "/avatar/100x100/deadnote/dead_note_07.png",
    "/avatar/100x100/deadnote/dead_note_08.png",
    "/avatar/100x100/deadnote/dead_note_09.png",
    "/avatar/100x100/deadnote/dead_note_010.png",
    "/avatar/100x100/haikyu/haikyu_01.png",
    "/avatar/100x100/haikyu/haikyu_02.png",
    "/avatar/100x100/haikyu/haikyu_03.png",
    "/avatar/100x100/haikyu/haikyu_04.png",
    "/avatar/100x100/haikyu/haikyu_05.png",
    "/avatar/100x100/haikyu/haikyu_06.png",
    "/avatar/100x100/haikyu/haikyu_07.png",
    "/avatar/100x100/haikyu/haikyu_08.png",
    "/avatar/100x100/haikyu/haikyu_09.png",
    "/avatar/100x100/haikyu/haikyu_010.png",
    "/avatar/100x100/haikyu/haikyu_011.png"
);

// Input sanitization
$username = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['re-confirmpassword'] ?? '');
$errors   = [];

// Basic validations
if (!$username || !$email || !$password || !$confirm) {
    $errors[] = 'All fields are required';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address';
}
if ($password !== $confirm) {
    $errors[] = 'Passwords do not match';
}
if (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
}

if (!empty($errors)) {
    echo json_encode(['status' => false, 'errors' => $errors]);
    exit;
}

// ✅ Check for duplicate email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['status' => false, 'msg' => 'Email already registered']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// ✅ Register user
$hashed = password_hash($password, PASSWORD_DEFAULT);
$avatar_url = $avatarFiles[array_rand($avatarFiles)];
// ✅ Generate independent encryption token (not related to user data)
$public_token_plain  = bin2hex(random_bytes(32));   // 64 chars
$public_token_hashed = hash('sha256', $public_token_plain);

$stmt = $conn->prepare("
    INSERT INTO users (username, email, password, image, public_token, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("sssss", $username, $email, $hashed, $avatar_url, $public_token_hashed);

if ($stmt->execute()) {
    $newUserId = $stmt->insert_id;

    // ✅ Auto-login via secure token
    $token_plain = bin2hex(random_bytes(32));
    $token_hashed = hash('sha256', $token_plain);
    $expires = time() + (86400 * 30); // 30 days

    // ✅ Save session record (multi-device support)
    $stmt2 = $conn->prepare("
        INSERT INTO user_sessions (user_id, token_hash, expires_at, created_at, ip, user_agent)
        VALUES (?, ?, FROM_UNIXTIME(?), NOW(), ?, ?)
    ");
    $ip = getClientIP();
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt2->bind_param("isiss", $newUserId, $token_hashed, $expires, $ip, $agent);
    $stmt2->execute();
    $stmt2->close();

    // ✅ Secure cookie
    setcookie('session_token', $token_plain, [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => true,   // HTTPS only
        'httponly' => true,   // No JS access
        'samesite' => 'Strict'
    ]);

    // ✅ Start PHP session
    session_regenerate_id(true);
    $_SESSION['userID'] = $newUserId;
    $_SESSION['username'] = $username;

    echo json_encode([
        'status' => true,
        'msg' => 'Registration successful.',
        'reload' => true,
        'info' => [
            'id' => $newUserId,
            'username' => $username
        ]
    ]);
} else {
    echo json_encode(['status' => false, 'msg' => 'Something went wrong. Please try again']);
}

$stmt->close();
$conn->close();
?>
