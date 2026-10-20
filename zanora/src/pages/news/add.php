<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/_config.php');
if (session_status() === PHP_SESSION_NONE) session_start();

// Enable error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$DEBUG_MODE = false;

// ========= DEBUG HEADER =========
if ($DEBUG_MODE) {
    echo "<pre style='background:#111;color:#0f0;padding:10px;'>";
    echo "FILE: " . __FILE__ . "\n";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n";
    echo "SESSION:\n";
    var_dump($_SESSION);
    echo "</pre>";
}
// ================================


// USER PERMISSION CHECK
if (!isset($_SESSION['roles']) || $_SESSION['roles'] < 1) {
    if ($DEBUG_MODE) {
        echo "<div style='color:red;padding:10px;'>PERMISSION CHECK FAILED (roles missing or &lt; 1)</div>";
        exit;
    }
    header("Location: /404");
    exit;
}

// Make sure DB connection exists
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("<div style='color:red;padding:10px;'>\$conn is not a valid mysqli connection. Check _config.php.</div>");
}

$userId = isset($_SESSION['userID']) ? (int)$_SESSION['userID'] : 0;

$error   = '';
$success = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($DEBUG_MODE) {
        echo "<pre style='background:#111;color:#0ff;padding:10px;'>POST RECEIVED:\n";
        var_dump($_POST);
        echo "</pre>";
    }

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $thumbnail   = trim($_POST['thumbnail'] ?? '');
    $content     = $_POST['content'] ?? ''; // full HTML

    if ($title === '' || $description === '' || $content === '') {
        $error = "Please fill all required fields!";
    } else {

        // Create slug
        $slugBase = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $slugBase = trim($slugBase, '-');

        if ($DEBUG_MODE) {
            echo "<pre style='background:#111;color:#ff0;padding:10px;'>
SLUG GENERATED: {$slugBase}
</pre>";
        }

        // Correct column names (thumb_url)
        $sql = "INSERT INTO news (title, slug, description, thumb_url, content)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("sssss", $title, $slugBase, $description, $thumbnail, $content);

            if ($stmt->execute()) {
                $id = $stmt->insert_id;
                $success = "Inserted OK with ID = {$id}";

                if ($DEBUG_MODE) {
                    echo "<pre style='background:#020;color:#0f0;padding:10px;'>
INSERT SUCCESS!
ID: {$id}
REDIRECT WOULD BE: /news/{$slugBase}-{$id}
</pre>";
                    // When you're sure it's working, uncomment:
                    // header("Location: /news/{$slugBase}-{$id}");
                    // exit;
                } else {
                    header("Location: /news/{$slugBase}-{$id}");
                    exit;
                }
            } else {
                $error = "Execute failed: " . $stmt->error;
            }

            $stmt->close();
        }
    }

    if ($DEBUG_MODE && $error !== '') {
        echo "<pre style='background:#200;color:#f66;padding:10px;'>
ERROR: {$error}
</pre>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add News</title>
<style>
body {
    background: #0f0f0f;
    font-family: Arial, sans-serif;
    color: #fff;
    margin: 0;
    padding: 0;
}
.page-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: #1b1b1b;
    border-radius: 10px;
    border: 1px solid #2d2d2d;
}
.page-title {
    font-size: 26px;
    font-weight: bold;
    color: #b66bf9;
    margin-bottom: 20px;
}
label {
    font-weight: bold;
    margin-top: 15px;
    display: block;
    color: #b66bf9;
}
input[type=text], textarea {
    width: 100%;
    padding: 10px;
    background: #121212;
    border: 1px solid #2f2f2f;
    border-radius: 6px;
    color: #fff;
    margin-top: 5px;
}
textarea {
    height: 120px;
}
.btn-submit {
    background: #b66bf9;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    margin-top: 20px;
    border-radius: 6px;
    cursor: pointer;
    color: #fff;
    transition: 0.2s;
}
.btn-submit:hover {
    background: #a353e5;
}
.error-box {
    background: #501a1a;
    padding: 10px;
    border-radius: 6px;
    color: #ff9f9f;
    margin-bottom: 20px;
    border: 1px solid #932525;
}
.success-box {
    background: #1f4d1f;
    padding: 10px;
    border-radius: 6px;
    color: #b5ffb5;
    margin-bottom: 20px;
    border: 1px solid #2f8f2f;
}
</style>

<script src="https://cdn.tiny.cloud/1/f4rfenm0to5itcswq81mi713l1ewvwj87r441dudlgckyi76/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
<script>
  tinymce.init({
    selector: '#content', // only for article content, not all textareas
    plugins: [
      'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists',
      'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
      'checklist', 'mediaembed', 'casechange', 'formatpainter', 'pageembed',
      'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste',
      'advtable', 'advcode', 'advtemplate', 'ai', 'uploadcare', 'mentions',
      'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography',
      'inlinecss', 'markdown','importword', 'exportword', 'exportpdf'
    ],
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
    tinycomments_mode: 'embedded',
    tinycomments_author: 'Author name',
    mergetags_list: [
      { value: 'First.Name', title: 'First Name' },
      { value: 'Email', title: 'Email' },
    ],
    ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
    uploadcare_public_key: '361461d7c93e8a9c358c',
  });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="/css/styles.min.css?v=<?= $versionCss ?? '1.0' ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" media="print" onload="this.media='all'">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" media="print" onload="this.media='all'">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons&display=swap" media="print" onload="this.media='all'">
<noscript>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons&display=swap">
</noscript>
</head>

<body>
<div class="page-container">

    <div class="page-title">Create News Article</div>

    <?php if (!empty($error)): ?>
        <div class="error-box"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success) && $DEBUG_MODE): ?>
        <div class="success-box"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">

        <label>Title *</label>
        <input type="text" name="title" required>

        <label>Short Description *</label>
        <textarea name="description" required></textarea>

        <label>Thumbnail Image URL (optional)</label>
        <input type="text" name="thumbnail" placeholder="https://example.com/image.jpg">

        <label>Full Article Content *</label>
        <textarea id="content" name="content"></textarea>

        <button class="btn-submit" type="submit">Publish Article</button>
    </form>
</div>

<script>
const TURNSTILE_SITE_KEY = "0x4AAAAAABnnkP1T73YyqkG4";
</script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
<script type="text/javascript" src="/js/app.min.js?v=<?= $versionJs ?? '1.0' ?>"></script>
</body>
</html>
