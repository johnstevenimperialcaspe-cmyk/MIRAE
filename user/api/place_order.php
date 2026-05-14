<?php
header('Content-Type: application/json');
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data.']);
    exit();
}

$addressId = (int) $data['address_id'];
$paymentMethod = $data['payment_method'];
$items = $data['items'];

$conn->begin_transaction();

try {
    // 1. Calculate total
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['unitPrice'] * $item['qty'];
    }
    $total = $subtotal + 50; // Including 50 PHP shipping fee

    // 2. Create Order
    $orderCode = 'ORD-' . strtoupper(bin2hex(random_bytes(3)));
    $stmt = $conn->prepare("INSERT INTO orders (order_code, customer_id, total, payment_method, order_status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->bind_param('sids', $orderCode, $userId, $total, $paymentMethod);
    $stmt->execute();
    $orderId = $conn->insert_id;
    $stmt->close();

    // 3. Create Order Items and Update Stock
    $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) SELECT ?, id, ?, ? FROM products WHERE sku = ? LIMIT 1");
    if (!$stmtItem) throw new Exception("Prepare failed: " . $conn->error);
    
    $stmtStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE sku = ? AND stock >= ?");
    if (!$stmtStock) throw new Exception("Prepare failed: " . $conn->error);
    
    foreach ($items as $item) {
        $qty = (int) $item['qty'];
        $price = (float) $item['unitPrice'];
        $sku = $item['id']; // We use cartId as SKU

        // Check if product exists and reduce stock
        $stmtStock->bind_param('isi', $qty, $sku, $qty);
        if (!$stmtStock->execute()) throw new Exception("Stock update failed: " . $stmtStock->error);
        
        if ($stmtStock->affected_rows === 0) {
            // Check if it's because of insufficient stock or invalid SKU
            $chk = $conn->prepare("SELECT stock FROM products WHERE sku = ?");
            $chk->bind_param('s', $sku);
            $chk->execute();
            $res = $chk->get_result()->fetch_assoc();
            $chk->close();
            
            if (!$res) {
                throw new Exception("Product SKU $sku not found in database.");
            } else {
                throw new Exception("Insufficient stock for $sku. Available: " . $res['stock']);
            }
        }

        // Insert order item
        $stmtItem->bind_param('iids', $orderId, $qty, $price, $sku);
        if (!$stmtItem->execute()) throw new Exception("Item insert failed: " . $stmtItem->error);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'order_code' => $orderCode]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
