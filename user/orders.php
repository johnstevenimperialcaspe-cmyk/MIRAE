<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Handle Order Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $orderId = (int)$_POST['cancel_order_id'];
    
    // Security check: Ensure order belongs to user and is still Pending
    $stmt = $conn->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE id = ? AND customer_id = ? AND order_status = 'Pending'");
    $stmt->bind_param("ii", $orderId, $userId);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        // Success: Redirect to avoid double-submit and show updated status
        header("Location: orders.php?tab=" . urlencode($statusTab) . "&msg=cancelled");
        exit();
    } else {
        $error = "Order could not be cancelled. It may have already been processed or cancelled.";
    }
    $stmt->close();
}

if (isset($_GET['msg']) && $_GET['msg'] === 'cancelled') {
    $msg = "Order cancelled successfully.";
}

$pageKey = 'orders';
$statusTab = isset($_GET['tab']) ? $_GET['tab'] : 'All';

$statusMap = [
    'Pending' => 'Pending',
    'Confirmed' => 'Confirmed',
    'Out' => 'Out for Delivery',
    'Delivered' => 'Delivered',
    'Cancelled' => 'Cancelled'
];

$whereSql = "WHERE customer_id = $userId";
if ($statusTab !== 'All' && isset($statusMap[$statusTab])) {
    $whereSql .= " AND order_status = '" . $statusMap[$statusTab] . "'";
}

$orders = [];
$sql = "SELECT id, order_code, total, order_status, created_at FROM orders $whereSql ORDER BY created_at DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) $orders[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - MIRAE</title>
    <link rel="icon" type="image/png" href="../images/MD.png">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/loader.css">
    <style>
        :root { --primary: #1b7d3f; --primary-light: #ecfdf5; --text-dark: #111827; --text-gray: #6b7280; --border: #e5e7eb; }
        body { font-family: 'DM Sans', sans-serif; background: #f9fafb; margin: 0; color: var(--text-dark); }
        .user-layout { display: flex; min-height: 100vh; }
        .user-sidebar { width: 260px; background: white; border-right: 1px solid var(--border); padding: 30px 20px; }
        .nav-link { display: block; padding: 12px 15px; color: var(--text-dark); text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; transition: all 0.2s; }
        .nav-link:hover { background: var(--primary-light); color: var(--primary); }
        .nav-link.active { background: var(--primary); color: white; }
        .user-main { flex: 1; padding: 40px; }
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 28px; margin: 0; }
        
        /* Tabs */
        .order-tabs { display: flex; background: white; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 30px; overflow: hidden; }
        .tab-item { flex: 1; padding: 15px; text-align: center; color: var(--text-gray); text-decoration: none; font-size: 14px; font-weight: 500; border-right: 1px solid #f3f4f6; }
        .tab-item:last-child { border-right: none; }
        .tab-item:hover { background: #f9fafb; }
        .tab-item.active { color: var(--primary); border-bottom: 2px solid var(--primary); background: var(--primary-light); }
        
        /* Order Card */
        .order-card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 25px; margin-bottom: 20px; }
        .order-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; padding-bottom: 15px; margin-bottom: 15px; }
        .order-id { font-weight: 700; color: var(--primary); }
        .order-date { font-size: 13px; color: var(--text-gray); }
        .order-status { font-weight: 600; font-size: 13px; text-transform: uppercase; }
        .order-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 15px; padding-top: 15px; border-top: 1px solid #f3f4f6; }
        .order-total { font-size: 18px; font-weight: 700; }
        .btn-detail { padding: 8px 16px; border: 1px solid var(--primary); color: var(--primary); background: transparent; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 13px; }
        
        .empty-state { text-align: center; padding: 60px 0; color: var(--text-gray); }
    </style>
</head>
<body class="orders-page">
    <div class="preloader">
        <div class="loader"></div>
    </div>
    <div class="user-layout">
        <aside class="user-sidebar">
            <div style="margin-bottom: 40px; padding-left: 10px;">
                <h1 style="font-size: 24px; color: var(--primary); margin: 0;">MIRAE</h1>
                <span style="font-size: 12px; color: var(--text-gray);">My Account</span>
            </div>
            <nav>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="profile.php" class="nav-link">My Profile</a>
                <a href="addresses.php" class="nav-link">My Addresses</a>
                <a href="orders.php" class="nav-link active">My Orders</a>
                <a href="messages.php" class="nav-link">My Messages</a>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
                <a href="logout.php" class="nav-link" style="color: #dc2626;">Log Out</a>
            </nav>
        </aside>
        
        <main class="user-main">
            <div class="page-header">
                <h1>My Orders</h1>
            </div>

            <?php if (isset($msg)): ?>
                <div class="alert alert-success"><?php echo $msg; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="order-tabs">
                <a href="?tab=All" class="tab-item <?php echo $statusTab === 'All' ? 'active' : ''; ?>">All</a>
                <a href="?tab=Pending" class="tab-item <?php echo $statusTab === 'Pending' ? 'active' : ''; ?>">Pending</a>
                <a href="?tab=Confirmed" class="tab-item <?php echo $statusTab === 'Confirmed' ? 'active' : ''; ?>">Confirmed</a>
                <a href="?tab=Out" class="tab-item <?php echo $statusTab === 'Out' ? 'active' : ''; ?>">Out for Delivery</a>
                <a href="?tab=Delivered" class="tab-item <?php echo $statusTab === 'Delivered' ? 'active' : ''; ?>">Delivered</a>
                <a href="?tab=Cancelled" class="tab-item <?php echo $statusTab === 'Cancelled' ? 'active' : ''; ?>">Cancelled</a>
            </div>

            <div class="orders-list">
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <p>No orders found in this category.</p>
                        <a href="../product.html" class="btn btn-sm btn-outline-success">Go Shopping</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <span class="order-id"><?php echo htmlspecialchars($order['order_code']); ?></span>
                                    <span class="order-date">Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="order-status" style="color: <?php 
                                    if ($order['order_status'] === 'Pending') echo '#92400e';
                                    elseif ($order['order_status'] === 'Delivered') echo '#065f46';
                                    elseif ($order['order_status'] === 'Cancelled') echo '#991b1b';
                                    else echo '#1b7d3f';
                                ?>;">
                                    <?php echo $order['order_status']; ?>
                                </div>
                            </div>
                            <div class="order-body">
                                <p style="margin:0; font-size: 14px; color: var(--text-gray);">Order Details Summary...</p>
                            </div>
                            <div class="order-footer">
                                <div>
                                    <small style="color: var(--text-gray);">Total Amount</small>
                                    <div class="order-total">PHP <?php echo number_format($order['total'], 2); ?></div>
                                </div>
                                <div style="display:flex; gap: 10px;">
                                    <?php if ($order['order_status'] === 'Pending'): ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                            <input type="hidden" name="cancel_order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Cancel Order</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">Order Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="../js/main.js"></script>
</body>
</html>
