<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$messageId = (int) ($_POST['message_id'] ?? 0);
$status = $_POST['status'] ?? '';

$allowedStatuses = ['Unread', 'Read', 'Replied'];

if (!$messageId || !in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid message ID or status.']);
    exit();
}

$stmt = $conn->prepare("UPDATE messages SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $messageId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
