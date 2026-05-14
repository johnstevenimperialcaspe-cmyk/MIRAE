<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch order details with security check (must belong to current user)
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found or access denied.");
}

// Fetch order items
$itemsStmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.image, p.variant FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$items = $itemsStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo $order['order_code']; ?></title>
    <link rel="icon" type="image/png" href="../images/MD.png">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/loader.css">
    <style>
        :root { --primary: #1b7d3f; --primary-light: #ecfdf5; --text-dark: #111827; --text-gray: #6b7280; --border: #e5e7eb; }
        body { font-family: 'DM Sans', sans-serif; background: #f9fafb; color: var(--text-dark); }
        .user-layout { display: flex; min-height: 100vh; }
        .user-sidebar { width: 260px; background: white; border-right: 1px solid var(--border); padding: 30px 20px; }
        .nav-link { display: block; padding: 12px 15px; color: var(--text-dark); text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; transition: all 0.2s; }
        .nav-link:hover { background: var(--primary-light); color: var(--primary); }
        .nav-link.active { background: var(--primary); color: white; }
        .user-main { flex: 1; padding: 40px; }
        
        .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-default { background: #e5e7eb; color: #374151; }

        .detail-card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 25px; margin-bottom: 25px; }
        .item-row { display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #f3f4f6; }
        .item-row:last-child { border-bottom: none; }
        .item-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 15px; background: #f9fafb; }
        .item-info { flex: 1; }
        .item-name { font-weight: 600; margin: 0; }
        .item-variant { font-size: 12px; color: var(--text-gray); }
        .item-price { font-weight: 600; }

        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .summary-total { font-size: 20px; font-weight: 700; color: var(--primary); border-top: 2px solid #f3f4f6; pt-15; margin-top: 15px; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="preloader"><div class="loader"></div></div>
    <div class="user-layout">
        <aside class="user-sidebar">
            <div style="margin-bottom: 40px; padding-left: 10px;">
                <h1 style="font-size: 24px; color: var(--primary); margin: 0;">MIRAE</h1>
            </div>
            <nav>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="orders.php" class="nav-link active">My Orders</a>
                <a href="logout.php" class="nav-link" style="color: #dc2626;">Log Out</a>
            </nav>
        </aside>

        <main class="user-main">
            <div class="order-header">
                <div>
                    <a href="orders.php" style="color: var(--text-gray); text-decoration: none; font-size: 14px;">&larr; Back to Orders</a>
                    <h1 style="margin-top: 10px;">Order <?php echo htmlspecialchars($order['order_code']); ?></h1>
                </div>
                <span class="status-badge <?php 
                    if ($order['order_status'] === 'Pending') echo 'status-pending';
                    elseif ($order['order_status'] === 'Delivered') echo 'status-delivered';
                    elseif ($order['order_status'] === 'Cancelled') echo 'status-cancelled';
                    else echo 'status-default';
                ?>">
                    <?php echo $order['order_status']; ?>
                </span>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="detail-card">
                        <h5 style="margin-bottom: 20px;">Order Items</h5>
                        <?php while($item = $items->fetch_assoc()): ?>
                            <div class="item-row">
                                <img src="../<?php echo $item['image'] ?: 'images/MD.png'; ?>" class="item-img">
                                <div class="item-info">
                                    <p class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                    <span class="item-variant"><?php echo htmlspecialchars($item['variant']); ?></span>
                                </div>
                                <div class="text-right">
                                    <div class="item-price">PHP <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                    <small class="text-muted"><?php echo $item['quantity']; ?> x PHP <?php echo number_format($item['price'], 2); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-card">
                        <h5 style="margin-bottom: 20px;">Order Summary</h5>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>PHP <?php echo number_format($order['total'], 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>FREE</span>
                        </div>
                        <div class="summary-total">
                            <span>Total</span>
                            <span>PHP <?php echo number_format($order['total'], 2); ?></span>
                        </div>
                    </div>

                    <div class="detail-card">
                        <h5>Payment Information</h5>
                        <p style="margin-bottom: 5px;"><strong>Method:</strong> <?php echo $order['payment_method'] ?: 'Not Specified'; ?></p>
                        <p><strong>Status:</strong> <?php echo $order['payment_status']; ?></p>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../js/main.js"></script>
</body>
</html>
