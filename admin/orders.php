<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
$pageTitle = 'Orders';
$pageKey = 'orders';
$assetBase = '';

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$whereClauses = [];
if ($statusFilter === 'active') {
    $whereClauses[] = "o.order_status IN ('Pending', 'Confirmed', 'Out for Delivery')";
} elseif ($statusFilter) {
    $whereClauses[] = "o.order_status = '$statusFilter'";
}

if ($search) {
    $whereClauses[] = "(c.name LIKE '%$search%' OR o.order_code LIKE '%$search%')";
}

$whereSql = count($whereClauses) > 0 ? "WHERE " . implode(' AND ', $whereClauses) : '';

$orders = [];
$sql = "SELECT o.id, o.order_code, o.payment_status, o.payment_method, o.order_status, o.shipping_method, o.total,
        o.created_at, c.name, c.address, c.city
        FROM orders o
        INNER JOIN customers c ON c.id = o.customer_id
        $whereSql
        ORDER BY o.created_at DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $result->free();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1><?php echo $statusFilter === 'active' ? 'Active Orders' : 'All Orders'; ?></h1>
        <p>Manage customer orders and fulfillment status.</p>
    </div>

    <section class="table-controls">
        <form action="" method="get" class="search-form">
            <div style="flex: 1; max-width: 320px;">
                <input type="text" name="search" class="form-control" placeholder="Search by order code or name..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <?php if ($statusFilter !== 'active') : ?>
            <div style="width: 180px;">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Confirmed" <?php echo $statusFilter === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="Out for Delivery" <?php echo $statusFilter === 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                    <option value="Delivered" <?php echo $statusFilter === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="Cancelled" <?php echo $statusFilter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="status" value="active">
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <?php if ($search || ($statusFilter && $statusFilter !== 'active')) : ?>
                <a href="orders.php<?php echo $statusFilter === 'active' ? '?status=active' : ''; ?>" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </section>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Delivery Address</th>
                    <th>Total Amount</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$orders) : ?>
                    <tr><td colspan="7">No orders found.</td></tr>
                <?php else : ?>
                    <?php foreach ($orders as $order) : ?>
                        <?php 
                        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $order['order_status']));
                        if ($order['order_status'] === 'Delivered') $statusClass = 'status-completed';
                        if ($order['order_status'] === 'Cancelled') $statusClass = 'status-cancelled';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td>
                                <small><?php echo htmlspecialchars($order['address']); ?>, <?php echo htmlspecialchars($order['city']); ?></small>
                            </td>
                            <td>PHP <?php echo number_format((float) $order['total'], 2); ?></td>
                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at']))); ?></td>
                            <td><span class="status-pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['order_status']); ?></span></td>
                            <td>
                                <div class="icon-actions">
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm" title="View Details">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </a>
                                    <select class="status-select" onchange="updateStatus(<?php echo $order['id']; ?>, this.value)">
                                        <option value="Pending" <?php echo $order['order_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Confirmed" <?php echo $order['order_status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="Out for Delivery" <?php echo $order['order_status'] === 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                        <option value="Delivered" <?php echo $order['order_status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="Cancelled" <?php echo $order['order_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<script>
function updateStatus(orderId, status) {
    if (confirm('Update order status to ' + status + '?')) {
        // Simple fetch call to update status
        fetch('api/update_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'order_id=' + orderId + '&status=' + encodeURIComponent(status)
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
            else alert('Failed to update status: ' + data.message);
        });
    }
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
