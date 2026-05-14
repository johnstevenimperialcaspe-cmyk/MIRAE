<?php
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json');

$pageKey = isset($_GET['page']) ? trim($_GET['page']) : '';
$allowed = ['home', 'product', 'data', 'about', 'faqs', 'contacts'];

if (!in_array($pageKey, $allowed, true)) {
    echo json_encode([
        'page' => $pageKey,
        'content' => '',
        'error' => 'Invalid page key.'
    ]);
    exit();
}

$content = '';
$stmt = $conn->prepare("SELECT content FROM content_pages WHERE page_key = ? LIMIT 1");
$stmt->bind_param('s', $pageKey);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $content = $row['content'] ?? '';
}
$stmt->close();

echo json_encode([
    'page' => $pageKey,
    'content' => $content
]);
