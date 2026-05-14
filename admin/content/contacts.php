<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
$pageTitle = 'Content - Contacts';
$pageKey = 'content-contacts';
$assetBase = '../';

$notice = '';
$pageDbKey = 'contacts';
$pageName = 'Contacts';
$introValue = '';
$subtitleValue = '';
$htmlValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $introValue = trim($_POST['intro'] ?? '');
    $subtitleValue = trim($_POST['subtitle'] ?? '');
    $htmlValue = trim($_POST['html'] ?? '');
    $contentValue = json_encode([
        'intro' => $introValue,
        'subtitle' => $subtitleValue,
        'html' => $htmlValue
    ]);

    $stmt = $conn->prepare(
        "INSERT INTO content_pages (page_key, page_name, content)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE page_name = VALUES(page_name), content = VALUES(content)"
    );
    $stmt->bind_param('sss', $pageDbKey, $pageName, $contentValue);
    $stmt->execute();
    $stmt->close();
    $notice = 'Contacts content updated.';
}

$stmt = $conn->prepare("SELECT content FROM content_pages WHERE page_key = ? LIMIT 1");
$stmt->bind_param('s', $pageDbKey);
$stmt->execute();
$result = $stmt->get_result();
$rawContent = '';
if ($row = $result->fetch_assoc()) {
    $rawContent = $row['content'] ?? '';
}
$stmt->close();

if ($rawContent !== '') {
    $decoded = json_decode($rawContent, true);
    if (is_array($decoded)) {
        $introValue = $decoded['intro'] ?? '';
        $subtitleValue = $decoded['subtitle'] ?? '';
        $htmlValue = $decoded['html'] ?? '';
    } else {
        $htmlValue = $rawContent;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Contacts Content</h1>
        <p>Manage contact details shown on the Contacts page.</p>
    </div>

    <?php if ($notice) : ?>
        <div class="card" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($notice); ?></div>
    <?php endif; ?>

    <form class="form-card" action="contacts.php" method="post">
        <div class="form-group">
            <label for="contacts-intro">Intro Text</label>
            <input id="contacts-intro" name="intro" type="text" class="form-control" value="<?php echo htmlspecialchars($introValue); ?>" />
        </div>
        <div class="form-group">
            <label for="contacts-subtitle">Subtitle</label>
            <input id="contacts-subtitle" name="subtitle" type="text" class="form-control" value="<?php echo htmlspecialchars($subtitleValue); ?>" />
        </div>
        <div class="form-group">
            <label for="contacts-html">HTML Content</label>
            <textarea id="contacts-html" name="html" class="form-control" rows="8"><?php echo htmlspecialchars($htmlValue); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
