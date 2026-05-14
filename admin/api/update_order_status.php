<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$orderId = (int) ($_POST['order_id'] ?? 0);
$status = $_POST['status'] ?? '';

$allowedStatuses = ['Pending', 'Confirmed', 'Out for Delivery', 'Delivered', 'Cancelled'];

if (!$orderId || !in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID or status.']);
    exit();
}

$stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $orderId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
