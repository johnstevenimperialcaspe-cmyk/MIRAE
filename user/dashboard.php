<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$pageKey = 'dashboard';

// Get recent orders
$recentOrders = [];
$stmt = $conn->prepare("SELECT id, order_code, total, order_status, created_at FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recentOrders[] = $row;
}
$stmt->close();

// Get default address
$defaultAddress = null;
$stmt = $conn->prepare("SELECT * FROM customer_addresses WHERE customer_id = ? AND is_default = 1 LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$defaultAddress = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - MIRAE</title>
    <link rel="icon" type="image/png" href="../images/MD.png">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/loader.css">
    <style>
        :root { --primary: #1b7d3f; --primary-light: #ecfdf5; --text-dark: #111827; --text-gray: #6b7280; --border: #e5e7eb; }
        body { font-family: 'DM Sans', sans-serif; background: #f9fafb; margin: 0; color: var(--text-dark); }
        .user-layout { display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .user-sidebar { width: 260px; background: white; border-right: 1px solid var(--border); padding: 30px 20px; }
        .user-sidebar h2 { font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-gray); margin-bottom: 20px; padding-left: 10px; }
        .nav-link { display: block; padding: 12px 15px; color: var(--text-dark); text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; transition: all 0.2s; }
        .nav-link:hover { background: var(--primary-light); color: var(--primary); }
        .nav-link.active { background: var(--primary); color: white; }
        
        /* Main Content */
        .user-main { flex: 1; padding: 40px; }
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 28px; margin: 0 0 8px 0; }
        .page-header p { color: var(--text-gray); margin: 0; }
        
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 25px; margin-bottom: 30px; }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; display: flex; justify-content: space-between; align-items: center; }
        .card-link { font-size: 14px; color: var(--primary); text-decoration: none; font-weight: 600; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-gray); padding: 12px 0; border-bottom: 1px solid var(--border); }
        td { padding: 15px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .status-pill { padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        /* Address Card */
        .address-box { background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 8px; padding: 15px; margin-top: 10px; }
        .address-box p { margin: 5px 0; font-size: 14px; }
        
        @media (max-width: 992px) { .dashboard-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="dashboard-page">
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
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <a href="profile.php" class="nav-link">My Profile</a>
                <a href="addresses.php" class="nav-link">My Addresses</a>
                <a href="orders.php" class="nav-link">My Orders</a>
                <a href="messages.php" class="nav-link">My Messages</a>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
                <a href="logout.php" class="nav-link" style="color: #dc2626;">Log Out</a>
            </nav>
        </aside>
        
        <main class="user-main">
            <div class="page-header">
                <h1>Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
                <p>Welcome back to your dashboard. Here's what's happening with your account.</p>
            </div>
            
            <div class="dashboard-grid">
                <div class="left-col">
                    <div class="card">
                        <h3>Recent Orders <a href="orders.php" class="btn btn-sm btn-outline-success">View All</a></h3>
                        <?php if (empty($recentOrders)): ?>
                            <p style="color: var(--text-gray); font-size: 14px;">You haven't placed any orders yet.</p>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td>PHP <?php echo number_format($order['total'], 2); ?></td>
                                            <td>
                                                <?php 
                                                $statusClass = 'status-' . strtolower(str_replace(' ', '-', $order['order_status']));
                                                if ($order['order_status'] === 'Delivered') $statusClass = 'status-delivered';
                                                if ($order['order_status'] === 'Cancelled') $statusClass = 'status-cancelled';
                                                if ($order['order_status'] === 'Pending') $statusClass = 'status-pending';
                                                ?>
                                                <span class="status-pill <?php echo $statusClass; ?>">
                                                    <?php echo $order['order_status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="right-col">
                    <div class="card">
                        <h3>Default Address <a href="addresses.php" class="btn btn-sm btn-outline-success">Manage</a></h3>
                        <?php if ($defaultAddress): ?>
                            <div class="address-box">
                                <p><strong><?php echo htmlspecialchars($defaultAddress['recipient_name']); ?></strong></p>
                                <p><?php echo htmlspecialchars($defaultAddress['mobile_number']); ?></p>
                                <p>
                                    <?php echo htmlspecialchars($defaultAddress['house_unit_no']); ?> <?php echo htmlspecialchars($defaultAddress['street']); ?>,<br>
                                    <?php echo htmlspecialchars($defaultAddress['barangay']); ?>,<br>
                                    <?php echo htmlspecialchars($defaultAddress['city_municipality']); ?>, <?php echo htmlspecialchars($defaultAddress['province']); ?><br>
                                    <?php echo htmlspecialchars($defaultAddress['zip_code']); ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-gray); font-size: 14px;">No default address set.</p>
                            <a href="addresses.php" class="btn btn-success btn-sm btn-block">Add Address</a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card">
                        <h3>Quick Actions</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <a href="../product.html" class="btn btn-outline-secondary btn-block text-left">Continue Shopping</a>
                            <a href="messages.php" class="btn btn-outline-secondary btn-block text-left">Contact Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../js/main.js"></script>
</body>
</html>
