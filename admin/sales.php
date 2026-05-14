<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
$pageTitle = 'Sales';
$pageKey = 'sales';
$assetBase = '';

$totalSales = 0.0;
$totalOrders = 0;
$averageOrder = 0.0;
$newCustomers = 0;

if ($result = $conn->query("SELECT COALESCE(SUM(total), 0) AS total_sales, COUNT(*) AS total_orders FROM orders")) {
    $row = $result->fetch_assoc();
    $totalSales = (float) ($row['total_sales'] ?? 0);
    $totalOrders = (int) ($row['total_orders'] ?? 0);
    $averageOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0.0;
    $result->free();
}

if ($result = $conn->query("SELECT COUNT(*) AS total FROM customers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")) {
    $row = $result->fetch_assoc();
    $newCustomers = (int) ($row['total'] ?? 0);
    $result->free();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Sales</h1>
        <p>Track sales performance and order volume.</p>
    </div>

    <section class="card-grid">
        <div class="card"><h3>Total Sales</h3><strong>PHP <?php echo number_format($totalSales, 2); ?></strong></div>
        <div class="card"><h3>Total Orders</h3><strong><?php echo $totalOrders; ?></strong></div>
        <div class="card"><h3>Average Order Value</h3><strong>PHP <?php echo number_format($averageOrder, 2); ?></strong></div>
        <div class="card"><h3>New Customers</h3><strong><?php echo $newCustomers; ?></strong></div>
    </section>

    <div class="card" style="min-height: 220px;">
        <h3>Sales Overview</h3>
        <p style="color: var(--admin-muted); margin: 0.4rem 0 0;">
            Placeholder chart area. Connect to your reporting pipeline for live data.
        </p>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
